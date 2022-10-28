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
 * @param array $items
 *   UIDs | PIDs.
 * @param string $type
 *   Uid (default) | pid.
 *
 * @return array
 *   Array containing pubkeys.
 */
function _proc_get_pubkeys(array $items, string $type = NULL) {
  if (!isset($type)) {
    $type = 'uid';
  }
  $pubkeys = [];
  foreach ($items as $item) {
    $key_data = _proc_get_keys($item, $type);
    $pubkeys[] = [
      'key' => $key_data['public_key'],
      'changed' => $key_data['changed'],
    ];
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
  $crypto_lib_settings = [];

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
 * @param string $item_id
 *   User ID | Proc ID.
 * @param string $type
 *   Uid (default) | pid.
 *
 * @return array
 *   Array containing pubkey and encrypted privkey.
 */
function _proc_get_keys(string $item_id, string $type = NULL) {
  if (!isset($type)) {
    $type = 'uid';
  }

  $query = new EntityFieldQuery();
  $query
    ->entityCondition('entity_type', 'proc')
    ->entityCondition('bundle', 'proc_keyring')
    ->propertyCondition($type, $item_id)
    ->propertyOrderBy('pid', 'DESC')
    ->range(0, 1);
  $result = $query->execute();

  if (isset($result['proc'])) {
    $keyring = entity_load('proc', array_keys($result['proc']));
    $keyring_created = $keyring[key($keyring)]->created;
    $keyring_changed = $keyring[key($keyring)]->changed;
    $keyring = unserialize($keyring[key($keyring)]->proc_armored[LANGUAGE_NONE][0]['value']);
    $keyring_keys = [];
    $keyring_keys['encrypted_private_key'] = $keyring['encrypted_private_key'];
    $keyring_keys['public_key'] = $keyring['public_key'];
    $keyring_keys['created'] = $keyring_created;
    $keyring_keys['changed'] = $keyring_changed;
    $keyring_keys['keyring_cid'] = array_keys($result['proc'])[0];
    return $keyring_keys;
  }
}

/**
 * Helper function to get given ciphertext decryptable by the current user.
 *
 * @param string $cid
 *   Ciphertext ID (proc entity).
 *
 * @return array
 *   Array containing ciphertext data.
 */
function _proc_get_cipher($cid, $form = NULL, $form_state = NULL, $metadata_only = NULL) {
  global $user;

  $query = new EntityFieldQuery();
  $query
    ->entityCondition('entity_type', 'proc')
    ->entityCondition('bundle', 'proc_ciphertxt')
    ->propertyCondition('pid', $cid)
    ->range(0, 1);
  $result = $query->execute();

  if (empty($result)) {
    // Entity not found also returns access denied:
    return [];
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

  $cipher_text_data = [];

  // Skip cipher if it is defined the retrieval of metadata only.
  if ($metadata_only != TRUE) {
    $cipher_text_data['cipher_text'] = check_plain(
      _proc_cipher_unserialize(
        $proc_wrapper->proc_armored->value(),
        $cid,
        $form,
        $form_state
      )['cipher_text']
    );
  };

  $cipher_text_data['source_file_name']  = check_plain(unserialize($proc_wrapper->meta->value())['source_file_name']);
  $cipher_text_data['source_file_size']  = check_plain(unserialize($proc_wrapper->meta->value())['source_file_size']);
  $cipher_text_data['source_input_mode'] = check_plain(unserialize($proc_wrapper->meta->value())['input_mode']);
  $cipher_text_data['cipher_cid']        = check_plain($cid);
  $cipher_text_data['proc_owner_uid']    = check_plain($proc_wrapper->proc_armoredown->value()->uid);
  $cipher_text_data['proc_recipients']   = $proc_wrapper->proc_recipient->value();
  $cipher_text_data['changed']           = $proc_wrapper->changed->value();
  if (check_plain(unserialize($proc_wrapper->meta->value())['signed'])) {
    $cipher_text_data['signed'] = check_plain(unserialize($proc_wrapper->meta->value())['signed']);
  }
  else {
    $cipher_text_data['signed'] = 0;
  }

  if (!$form) {
    $form = new stdClass();
  }
  if (!is_object($form_state)) {
    $form_state = new stdClass();
  }

  $context = [
    'unalterable_cipher_object' => clone $proc_wrapper,
    'unalterable_form' => clone $form,
    'unalterable_form_state' => clone $form_state,
  ];
  drupal_alter('get_cipher_text', $cipher_text_data, $context);

  if (empty($cipher_text_data) || !$is_recipient) {
    // Return empty if current user does not belong to recipients list.
    return [];
  }
  return $cipher_text_data;
}

/**
 * Helper function to check if current user is recipient of cipher text.
 *
 * @param string $cids
 *   Ciphertext IDs (proc entities) CSV.
 *
 * @return bool
 *   A boolean indicating whether current user is recipient of the ciphertext.
 */
function _proc_recipient($cids) {
  global $user;
  $cids_argument = _proc_get_csv_argument($cids);
  foreach ($cids_argument as $cipher_id) {
    if (isset($cipher_id) && is_numeric($cipher_id)) {
      $cipher_id = (int) $cipher_id;
      $cipher = _proc_get_cipher($cipher_id, NULL, NULL);
      if (empty($cipher) and ($user->uid != 1)) {
        return FALSE;
      }
    }
  }
  return TRUE;
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
    $csv_data = [];
    foreach ($csv_values as $csv_value) {
      if (is_numeric($csv_value) && $csv_value = (int) $csv_value) {
        $csv_data[] = $csv_value;
      }
    }
    if (!empty($csv_data[0])) {
      return array_unique($csv_data);
    }
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
 *   A boolean indicating whether current user is recipient of all
 *   defined ciphertexts.
 */
function _proc_recipient_multiple($cids_csv) {
  $cids = _proc_get_csv_argument($cids_csv);
  $result = FALSE;
  foreach ($cids as $cid) {
    if (!_proc_recipient($cid)) {
      return FALSE;
    }
    else {
      $result = TRUE;
    }
  }
  return $result;
}

/**
 * Helper function to get max file size for encryption in bytes.
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
function _proc_get_csv_arguments(array $csvs) {
  $csvs_strings = [];
  foreach ($csvs as $csvs_key => $csv_data) {
    $csv_data = _proc_get_csv_argument($csv_data);
    if (is_array($csv_data)) {
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
  $message_txt = t('Saving...');
  return 'javascript:var s = this; setTimeout( function () { s.value = "' . $message_txt . '"; s.disabled = true; jQuery(":focus").blur();}, 1);';
}

/**
 * Helper function to get file API error message.
 */
function _proc_get_fileapi_err_msg() {
  return t('The File APIs are not fully supported in this browser.');
}

/**
 * Helper common function to get ciphers for decryption, signature.
 *
 * @param array $cids
 *   List of cids.
 *
 * @return array
 *   Array containing cihpers, encrypted priv key and pass form element.
 */
function _proc_common_get_ciphers(array $cids, $metadata_only = NULL) {
  global $user;

  $keyring = _proc_get_keys($user->uid, 'uid');

  $privkey = $keyring['encrypted_private_key'];
  $pubkey  = $keyring['public_key'];

  foreach ($cids as $cid) {
    $cid_cipher[$cid] = _proc_get_cipher((int) $cid, NULL, NULL, $metadata_only);
  }
  $form_pass = [
    '#type'        => 'password',
    '#title'       => t('Protected Content Password'),
    '#description' => t('You must type in the password you have used when registering your Protected Content Key.'),
    '#required'    => TRUE,
  ];
  return [
    'privkey'     => $privkey,
    'keyring_cid' => $keyring['keyring_cid'],
    'ciphers'     => $cid_cipher,
    'form_pass'   => $form_pass,
    'pubkey'      => $pubkey,
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
function _proc_common_get_pubkeys(array $uids) {
  $recipients_pubkeys = array_filter(_proc_get_pubkeys($uids, 'uid'));
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
  $form_attachment = [
    drupal_get_path('module', 'proc') . '/js/proc.' . $op . '.js' => [
      'type' => 'file',
    ],
  ];
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
    'proc_labels' => _proc_js_labels(),
    'proc_skip_size_mismatch' => variable_get('proc_skip_size_mismatch', FALSE),
  ];

}

/**
 * Helper callback for getting proc on ajax.
 *
 * @param string $pids
 *   String containnig proc IDs CSV.
 */
function _proc_get_proc_menu_callback($pids) {
  $pids = _proc_get_csv_argument($pids);
  $ciphers = &drupal_static(__FUNCTION__, []);
  if (!isset($ciphers[0])) {
    foreach ($pids as $pid) {
      $ciphers[] = _proc_get_cipher((int) $pid, NULL, NULL)['cipher_text'];
    }
  }
  drupal_json_output(['ciphers' => $ciphers]);
}

/**
 * Helper callback for getting pubkeys on ajax.
 *
 * @param string $items
 *   String containnig user IDs or proc IDs as csv.
 * @param string $type
 *   Uid (default) | pid.
 */
function _proc_get_pubkey_menu_callback($items, $type) {
  $type = check_plain($type);
  if (!isset($type)) {
    $type = 'uid';
  }
  $items_array = _proc_get_csv_argument($items);
  $pubkeys = _proc_get_pubkeys($items_array, $type);
  drupal_json_output(['pubkey' => $pubkeys]);
}

/**
 * Helper function for defining js labels.
 */
function _proc_js_labels() {
  return [
    'proc_fileapi_err_msg'            => t('Your browser does not support file API.'),
    'proc_button_state_processing'    => t('Processing...'),
    'proc_max_encryption_size'        => t('Error. Maximum file size allowed:'),
    'proc_max_encryption_size_unit'   => t('bytes'),
    'proc_save_button_label'          => t('Save'),
    'proc_size'                       => t('Size:'),
    'proc_type'                       => t('Type:'),
    'proc_last_modified'              => t('Last modified:'),
    'proc_minimal_password_strenght'  => t('Strong'),
    'proc_generate_keys_submit_label' => t('Generate encryption keys'),
    'proc_submit_saving_state'        => t('Saving...'),
    'proc_password_match'             => t('You must type in both password fields the same password'),
    'proc_password_required'          => t('Password is required.'),
    'proc_introducing_decryption'     => t('Indroducing key passphrase for decryption...'),
    'proc_open_file_state'            => t('Open'),
    'proc_decryption_success'         => t('Decryption successful.'),
    'proc_decryption_size_mismatch'   => t('Error: size mismatch.'),
    'proc_button_update_label'        => t('Update'),
    'proc_introducing_signature'      => t('Indroducing key passphrase for signature...'),
  ];
}

/**
 * Helper function for serializing ciphertext.
 */
function _proc_cipher_serialize($cipher, $cid, $changed, $form, $form_state) {
  $cipher_serialized = serialize($cipher);
  if (!isset($cid)) {
    $cid = '';
  }
  $context = [
    'unalterable_serialization_source_form'       => $form,
    'unalterable_serialization_source_form_state' => $form_state,
    'unalterable_serialization_source_cid'        => $cid,
    'unalterable_serialization_source_changed'    => $changed,
  ];
  drupal_alter('cipher_serialize', $cipher_serialized, $context);
  return $cipher_serialized;
}

/**
 * Helper function for unserializing ciphertext.
 */
function _proc_cipher_unserialize($cipher, $cid, $form, $form_state) {
  $cipher_unserialized = unserialize($cipher);
  $context = [
    'unalterable_unserialization_source_cid'        => $cid,
    'unalterable_unserialization_source_form'       => $form,
    'unalterable_unserialization_source_form_state' => $form_state,
  ];
  drupal_alter('cipher_unserialize', $cipher_unserialized, $context);
  return $cipher_unserialized;
}

/**
 * Helper function for updating ciphertext.
 */
function _proc_update_ciphertext($cid, $form, $form_state, $recipients_uids_new) {
  $proc_wrapper = entity_metadata_wrapper('proc', $cid);
  $file_name = check_plain(unserialize($proc_wrapper->meta->value())['source_file_name']);
  $file_size = check_plain(unserialize($proc_wrapper->meta->value())['source_file_size']);
  $cid_cipher_field_id = 'cipher_text_cid_' . $cid;
  $generation_timespan_field_id = 'generation_timespan_cid_' . $cid;
  $browser_fingerprint_field_id = 'browser_fingerprint_cid_' . $cid;
  $generation_timestamp_field_id = 'generation_timestamp_cid_' . $cid;
  // Merge current and new data and metadata:
  $ciphertxt_data[$cid] = [
    // Reuse unchanged values on encryption update:
    'source_file_size'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_size']),
    'source_file_name'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_name']),
    'source_file_type'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_type']),
  ];
  // If a new cryptograpgic update happened:
  if ($form_state['values'][$generation_timespan_field_id]) {
    $ciphertxt_data[$cid] = [
      // Set new changed values on encryption update:
      'generation_timespan'  => check_plain($form_state['values'][$generation_timespan_field_id]),
      'generation_timestamp' => check_plain($form_state['values'][$generation_timestamp_field_id]),
      'browser_fingerprint'  => check_plain($form_state['values'][$browser_fingerprint_field_id]),
      'cipher_text'          => check_plain($form_state['values'][$cid_cipher_field_id]),
    ];
    $proc_wrapper->proc_recipient->set($recipients_uids_new);

  }
  // This is an non-cryptographic update:
  else {
    // We want only the ciphertext stored in database:
    if ($cipher_data = check_plain(unserialize($proc_wrapper->proc_armored->value())['cipher_text'])) {
      $ciphertxt_data[$cid] = ['cipher_text' => $cipher_data];
    }
    else {
      // We want only the ciphertext possibly stored elsewhere:
      $cipher_data = _proc_cipher_unserialize($proc_wrapper->proc_armored->value(), $cid, $form, $form_state);
      $ciphertxt_data[$cid] = $cipher_data;
    }
  }

  $changed = $proc_wrapper->changed->value();

  $proc_wrapper->proc_armored = _proc_cipher_serialize($ciphertxt_data[$cid], $cid, $changed, $form, $form_state);
  $proc_wrapper->changed = time();
  $proc_wrapper->save();
}
