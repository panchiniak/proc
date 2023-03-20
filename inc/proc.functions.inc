<?php

/**
 * @file
 * proc.functions.inc
 */

/**
 * Helper function for defining labels used in JS.
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
    // Visible as data-drupal-selector=password-strength-text
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
    'proc_pass_weak'                  => t('Your password must be stronger.'),
    'proc_caches_unsupported'         => t('Error: this browser does not suport cache API. Please use an updated browser.'),
  ];
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
function _proc_common_get_decryption_form_data($privkey = NULL) {
  $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  $user_uid = $user->id();
  $hash = \Drupal\Core\Site\Settings::getHashSalt();
  $hash_base64 = \Drupal\Component\Utility\Crypt::hashBase64($hash . $user_uid);


  return [
    'proc_uid'     => $user_uid,
    'proc_pass'    => $hash_base64,
    'proc_email'   => $user->getEmail(),
    'proc_name'    => $user->getAccountName(),
    'proc_privkey' => $privkey,
    // 'proc_labels' => _proc_js_labels(),
    // 'proc_skip_size_mismatch' => \Drupal::config('proc.settings')->get('proc_skip_size_mismatch'),
  ];

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
// function _proc_get_csv_arguments(array $csvs) {
//   $csvs_strings = [];
//   foreach ($csvs as $csvs_key => $csv_data) {
//     $csv_data = _proc_get_csv_argument($csv_data);
//     if (is_array($csv_data)) {
//       $csvs_strings[$csvs_key]['array'] = $csv_data;
//       $csvs_strings[$csvs_key]['string'] = implode(',', $csv_data);
//     }
//   }
//   if (!empty($csvs_strings)) {
//     return $csvs_strings;
//   }
//   return NULL;

// }


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
    $csv_data = \Drupal\Component\Utility\Html::escape($csv_data);
    // ksm($csv_data);
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
 * Helper common function to get ciphers for decryption, signature.
 *
 * @param array $cids
 *   List of cids.
 *
 * @return array
 *   Array containing cihpers, encrypted priv key and pass form element.
 */
function _proc_common_get_ciphers(array $cids, $metadata_only = NULL) {
  $user = \Drupal::currentUser();
  $user_id = $user->id();

  $keyring = _proc_get_keys($user_id, 'uid');

  $privkey = $keyring['encrypted_private_key'];
  $pubkey  = $keyring['public_key'];  
  
  $cid_cipher = [];
  foreach($cids as $cid) {
    $cid_cipher[$cid] = _proc_get_cipher((int) $cid, NULL, NULL, $metadata_only);
  }
  
  return [
    'privkey'     => $privkey,
    'keyring_cid' => $keyring['keyring_cid'],
    'ciphers'     => $cid_cipher,
    // 'form_pass'   => $form_pass,
    'pubkey'      => $pubkey,
  ];
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
  
  $query = \Drupal::entityQuery('proc')
    ->condition('type', 'keyring')
    ->condition('status', 1)
    ->condition('user_id', $item_id)
    ->sort('id' , 'DESC')
    ->range(0, 1);
    
  $key_id = $query->execute();
  $key_id = array_values($key_id)[0];
  $entity = \Drupal::entityTypeManager()->getStorage('proc')->load($key_id);
  // Private key:
  $keyring_keys['encrypted_private_key'] = $entity->get('armored')->getValue()[0]['privkey'];
  $keyring_keys['public_key']            = $entity->get('armored')->getValue()[0]['pubkey'];
  $keyring_keys['created']               = $entity->get('created')->getValue()[0]['value'];
  $keyring_keys['changed']               = $entity->get('changed')->getValue()[0]['value'];
  $keyring_keys['keyring_cid']           = $key_id;

  return $keyring_keys;
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
  $entity = \Drupal::entityTypeManager()->getStorage('proc')->load($cid);

  $recipients = $entity->get('field_recipients_set')->getValue();
  $recipient_id = [];
  foreach ($recipients as $recipient) {
    $recipient_id[] = $recipient['target_id'];
  }
  $user = \Drupal::currentUser();
  $user_id = $user->id();


  if ($metadata_only != TRUE) {
    
    $cipher_text_data['cipher_text'] = $entity->get('armored')->getValue()[0]['cipher'];
    
    
    if ($entity->get('armored')->getValue()[0]['cipher_fid'] && !is_array($entity->get('armored')->getValue()[0]['cipher_fid'])) {
      // Stream wrapper storage on blocks:
      $storage = \Drupal::entityTypeManager()->getStorage('file');
      $file = $storage->load($entity->get('armored')->getValue()[0]['cipher_fid']);
      $cipher_text_data['cipher_text'] = file_get_contents($file->getFileUri());
    }
    else {
      // Database storage:
      $cipher_text_data['cipher_text'] = $entity->get('armored')->getValue()[0]['cipher'];
    }
  };

  // $cipher_text_data = FALSE;
  if (in_array($user_id, $recipient_id) || $metadata_only == TRUE) {
    $cipher_text_data['source_file_name']   = \Drupal\Component\Utility\Html::escape($entity->get('meta')->getValue()[0]['source_file_name']);
    $cipher_text_data['source_input_mode']  = \Drupal\Component\Utility\Html::escape($entity->get('meta')->getValue()[0]['source_input_mode']);
    $cipher_text_data['source_file_size']   = \Drupal\Component\Utility\Html::escape($entity->get('meta')->getValue()[0]['source_file_size']);
    $cipher_text_data['cipher_cid']         = $cid;
    $cipher_text_data['proc_owner_uid']     = $entity->get('user_id')->getValue()[0]['target_id'];
    $cipher_text_data['proc_recipients']    = $recipient_id;
    $cipher_text_data['changed']            = $entity->get('changed')->getValue()[0]['value'];
  }
  return $cipher_text_data;
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
    \Drupal\proc\Form\ProcDecryptForm::denyAccess();
  }
  $post_max_size_bytes = _proc_get_post_max_size_bytes();

  return [
    'recipients_pubkeys'  => $recipients_pubkeys,
    'post_max_size_bytes' => $post_max_size_bytes,
  ];
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
