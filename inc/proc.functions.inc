<?php

/**
 * @file
 * proc.functions.inc
 */

/**
 * Helper function to check if user agent in unsuported.
 *
 * @return bool
 *   A boolean indicating whether the failling browser has yellow flag.
 */
function _proc_browser_supported() {
  $path = drupal_get_path('module', 'proc') . '/proc.info';
  $info = drupal_parse_info_file($path);
  $unsuported_browsers_array = str_getcsv($info['yellow_flag_browsers_csv']);

  foreach ($unsuported_browsers_array as $unsuported_browser) {
    if (strpos($_SERVER['HTTP_USER_AGENT'], $unsuported_browser) !== FALSE) {
      return FALSE;
    }
  }
  return TRUE;
}

/**
 * Helper function for retrieval of pubkeys.
 *
 * @param array $users
 *   UIDs.
 *
 * @return array
 *   Array containing pubkeys.
 */
function _proc_get_pubkeys(array $users) {
  $pubkeys = array();
  foreach ($users as $recipient) {
    $pubkeys[] = _proc_get_keys($recipient)['public_key'];
  }
  return $pubkeys;
}

/**
 * Helper function to create fields.
 *
 * @param array $fields
 *   Fields definitions.
 */
function _proc_field_create_fields(array $fields) {
  foreach ($fields as $field) {
    if (!field_read_field($field['field_name'])) {
      field_create_field($field);
    }
  }
}

/**
 * Helper function to create field instances.
 *
 * @param array $instances
 *   Intances definitions.
 */
function _proc_field_create_instances(array $instances) {
  foreach ($instances as $instance) {
    if (!field_read_instance('proc', $instance['field_name'], $instance['bundle'])) {
      field_attach_create_bundle('proc', $instance['bundle']);
      field_create_instance($instance);
    }
  }
}

/**
 * Helper to load the cryptographic library settings from info file.
 *
 * @return array
 *   Array containing cryptographic library settings defined on .info.
 */
function _proc_get_cryptolib_settings() {
  $crypto_lib_settings = array();

  $path = drupal_get_path('module', 'proc') . '/proc.info';
  $info = drupal_parse_info_file($path);
  $crypto_lib_settings['versionstring'] = $info['crypto_library_versionstring'];
  $crypto_lib_settings['url'] = $info['crypto_library_url'];
  $crypto_lib_settings['folder'] = $info['crypto_library_folder'];
  $crypto_lib_settings['vendor'] = $info['crypto_library_vendor'];
  $crypto_lib_settings['file'] = $info['crypto_library_file'];

  return $crypto_lib_settings;
}

/**
 * Helper to load the cryptographic library.
 *
 * @return array
 *   Array containing loaded cryptographic library.
 */
function _proc_lib_load() {
  if (function_exists('libraries_load')) {
    $lib = libraries_load(_proc_get_cryptolib_settings()['folder']);
  }
  return $lib;
}

/**
 * Helper function to get keyring data.
 *
 * @param string $user_id
 *   User ID.
 *
 * @return array
 *   Array containing pubkey and encrypted privkey.
 */
function _proc_get_keys($user_id) {

  $query = new EntityFieldQuery();
  $query
    ->entityCondition('entity_type', 'proc')
    ->entityCondition('bundle', 'proc_keyring')
    ->propertyCondition('uid', $user_id)
    ->propertyOrderBy('pid', 'DESC')
    ->range(0, 1);
  $result = $query->execute();

  if (isset($result['proc'])) {
    $keyring = entity_load('proc', array_keys($result['proc']));
    $keyring_created = $keyring[key($keyring)]->created;
    $keyring_changed = $keyring[key($keyring)]->changed;
    $keyring = unserialize($keyring[key($keyring)]->proc_armored[LANGUAGE_NONE][0]['value']);
    $keyring_keys = array();
    $keyring_keys['encrypted_private_key'] = $keyring['encrypted_private_key'];
    $keyring_keys['public_key'] = $keyring['public_key'];
    $keyring_keys['created'] = $keyring_created;
    $keyring_keys['changed'] = $keyring_changed;
    return $keyring_keys;
  }
}

/**
 * Helper function to get given ciphertext that belongs to current user.
 *
 * @param string $cid
 *   Ciphertext ID (proc entity).
 *
 * @return array
 *   Array containing ciphertext data.
 */
function _proc_get_cipher($cid, $form = NULL, $form_state = NULL) {
  global $user;

  $query = new EntityFieldQuery();
  $query
    ->entityCondition('entity_type', 'proc')
    ->entityCondition('bundle', 'proc_ciphertxt')
    ->propertyCondition('pid', $cid)
    ->range(0, 1);
  $result = $query->execute();

  if (empty($result)) {
    // Entity not found also return access denied:
    return array();
  }
  $proc_wrapper = entity_metadata_wrapper('proc', $cid);
  $is_recipient = FALSE;
  // Check if cid matches a cipher text.
  if (isset($proc_wrapper->proc_recipient)) {
    foreach ($proc_wrapper->proc_recipient->value() as $cipher_recipient) {
      if ($cipher_recipient->uid == $user->uid) {
        $is_recipient = TRUE;
        break;
      }
    }
  }

  $cipher_text_data = array();
  $cipher_text_data['cipher_text'] = check_plain(unserialize($proc_wrapper->proc_armored->value())['cipher_text']);
  $cipher_text_data['source_file_name'] = check_plain(unserialize($proc_wrapper->proc_armored->value())['source_file_name']);
  $cipher_text_data['source_file_size'] = check_plain(unserialize($proc_wrapper->proc_armored->value())['source_file_size']);
  $cipher_text_data['cipher_cid'] = check_plain($cid);

  if (!$form) {
    $form = new stdClass();
  }
  if (!is_object($form_state)) {
    $form_state = new stdClass();
  }

  $context = array(
    'unalterable_cipher_object' => clone $proc_wrapper,
    'unalterable_form' => clone $form,
    'unalterable_form_state' => clone $form_state,
  );
  drupal_alter('get_cipher_text', $cipher_text_data, $context);

  if (empty($cipher_text_data) || !$is_recipient) {
    // Return empty if current user does not belong to recipients list.
    return array();
  }
  return $cipher_text_data;
}

/**
 * Helper function to check if current user is recipient of cipher text.
 *
 * @param string $cid
 *   Ciphertext ID (proc entity).
 *
 * @return bool
 *   A boolean indicating whether current user is recipient of the ciphertext.
 */
function _proc_recipient($cid) {
  global $user;

  if (isset($cid) && is_numeric($cid)) {
    $cid = (int) $cid;
    // @todo check if empty arguments are needed
    $cipher = _proc_get_cipher($cid, NULL, NULL);
    // @todo add 'bypass cipher access control' restricted permission
    if (!empty($cipher) or ($user->uid == 1)) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Helper function to get unique CSV arguments.
 *
 * @param string $csv_data
 *   CVS data sent as URL argument.
 *
 * @return array
 *   An array containing the unique entries of the CSV
 */
function _proc_get_csv_argument($csv_data) {
  if (isset($csv_data)) {
    // Remove query string from the path if any.
    $csv_data = preg_replace('/\?.+/', '', $csv_data);
    $csv_data = check_plain($csv_data);
    $csv_values = str_getcsv($csv_data);
    $csv_data = array();
    foreach ($csv_values as $csv_value) {
      if (is_numeric($csv_value) && $csv_value = (int) $csv_value) {
        $csv_data[] = $csv_value;
      }
    }
    return array_unique($csv_data);
  }
  return NULL;
}

/**
 * Helper function to check if current user is recipient of cipher texts.
 *
 * @param string $cids_csv
 *   CSV list of Ciphertext IDs (proc entities).
 *
 * @return bool
 *   A boolean indicating whether current user is recipient of all defined ciphertexts.
 */
function _proc_recipient_multiple($cids_csv) {
  $cids = _proc_get_csv_argument($cids_csv);
  foreach ($cids as $cid) {
    if (!_proc_recipient($cid)) {
      return FALSE;
    }
  }
  return TRUE;
}

/**
 * Helper function to get max file size for encryptio in bytes.
 *
 * @return string
 *   Dimicaly deined max file size.
 */
function _proc_get_post_max_size_bytes() {
  $post_max_size = substr_replace(ini_get('post_max_size'), "", -1);
  $post_max_size_bytes = $post_max_size * 1000000;
  return $post_max_size_bytes;
}

/**
 * Helper function to get multiple unique CSV arguments.
 *
 * @param array $csvs
 *   List of csvs.
 *
 * @return array
 *   Array containing sanitized CSVs strings and arrays
 */
function _proc_get_csv_arguments($csvs) {
  $csvs_strings = array();
  foreach ($csvs as $csvs_key => $csv_data) {
    $csv_data = _proc_get_csv_argument($csv_data);
    if (is_array($csv_data)) {
      // dpm($csv_data);
      $csvs_strings[$csvs_key]['array'] = $csv_data;
      $csvs_strings[$csvs_key]['string'] = implode(',', $csv_data);
    }
  }
  if (!empty($csvs_strings)) {
    return $csvs_strings;
  }
  return NULL;

}

/**
 * Helper function to get saving behaviour.
 */
function _proc_get_onclick_saving() {
  return 'javascript:var s = this; setTimeout( function () { s.value = "' . t('Saving...') . '"; s.disabled = true; jQuery(":focus").blur();}, 1);';
}

/**
 * Helper function to get file API error message.
 */
function _proc_get_fileapi_err_msg() {
  return t('The File APIs are not fully supported in this browser.');
}

/**
 * Helper common function to get ciphers for decryption.
 *
 * @param array $cids
 *   List of cids.
 *
 * @return array
 *   Array containing cihpers, encrypted priv key and pass form element.
 */
function _proc_common_get_ciphers($cids) {
  global $user;

  $privkey = _proc_get_keys($user->uid)['encrypted_private_key'];
  foreach ($cids as $cid) {
    $cid_cipher[$cid] = _proc_get_cipher((int) $cid, $form, $form_state);
  }
  $form_pass = array(
    '#type' => 'password',
    '#title' => t('Protected Content Password'),
    '#description' => t('You must type in the password you have used when registering your Protected Content Key.'),
    '#required' => TRUE,
  );
  return [
    'privkey' => $privkey,
    'ciphers' => $cid_cipher,
    'form_pass' => $form_pass,
  ];
}

/**
 * Helper common function to get pubkeys for encryption.
 *
 * @param array $uids
 *   List of uids.
 *
 * @return array
 *   Array containing pubkeys and max file size.
 */
function _proc_common_get_pubkeys($uids) {
  $recipients_pubkeys = array_filter(_proc_get_pubkeys($uids));
  // If some UID does not return keys:
  if (empty($recipients_pubkeys) || (count($uids) != count($recipients_pubkeys))) {
    drupal_not_found();
  }
  $post_max_size_bytes = _proc_get_post_max_size_bytes();

  return [
    'recipients_pubkeys'  => $recipients_pubkeys,
    'post_max_size_bytes' => $post_max_size_bytes,
  ];
}

/**
 * Helper common function to inject crypto lib.
 *
 * @param string $op
 *   Cryptographic operation
 *   encrypt_file, decrypt_protected_to_file, etc.
 *
 * @return array
 *   Array containing js form attachment.
 */
function _proc_common_inject_lib($op) {
  _proc_lib_load();
  $form_attachment = array(
    drupal_get_path('module', 'proc') . '/js/proc.' . $op . '.js' => array(
      'type' => 'file',
    ),
  );
  return $form_attachment;
}

/**
 * Helper common to get proc decryption form data.
 *
 * @param string $privkey
 *   Current user encrypted privkey.
 *
 * @return array
 *   Array containing proc form data.
 */
function _proc_common_get_decryption_form_data($privkey) {
  global $user;
  return [
    'proc_uid' => $user->uid,
    'proc_pass' => drupal_hash_base64(drupal_get_hash_salt() . $user->uid),
    'proc_mail' => $user->mail,
    'proc_name' => $user->name,
    'proc_privkey' => $privkey,
  ];

}
