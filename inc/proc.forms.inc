<?php

/**
 * @file
 * Forms for Protected Content module.
 */

/**
 * Definition of the form and metada for decryption.
 */
function _proc_decrypt_to_file($form, &$form_state, $cids) {
  global $user;
  // Once we will fetch the ciphertexts with ajax we want now only to
  // send changed timestamps (for verifying if cached version may be used)
  // and metadata as file size (for verifying decryption sucess) and
  // file name (for recreting the file).
  $cids         = _proc_get_csv_argument($cids);
  $ciphers_data = _proc_common_get_ciphers($cids, TRUE);
  $privkey      = $ciphers_data['privkey'];
  $form['pass'] = $ciphers_data['form_pass'];
  $cids_ciphers = $ciphers_data['ciphers'];

  $form['#attached']['js'] = _proc_common_inject_lib('decrypt_protected_to_file');

  $proc_sources_file_names = array();
  $proc_sources_file_sizes = array();
  $proc_ids                = array();
  $procs_changed           = array();

  foreach ($cids_ciphers as $cid_cipher) {
    $proc_sources_file_names[] = $cid_cipher['source_file_name'];
    $proc_sources_file_sizes[] = $cid_cipher['source_file_size'];
    $proc_ids[]                = $cid_cipher['cipher_cid'];
    $procs_changed[]           = $cid_cipher['changed'];
  }

  $multiple_decryption_form_elements = array(
    'proc_sources_file_names' => $proc_sources_file_names,
    'proc_sources_file_sizes' => $proc_sources_file_sizes,
    'proc_ids'                => $proc_ids,
    'procs_changed'           => $procs_changed,
  );

  $form['#attached']['js'][] = array(
    'data' => array('proc' => _proc_common_get_decryption_form_data($privkey)),
    'type' => 'setting',
  );

  $form['#attached']['js'][0]['data']['proc'] += $multiple_decryption_form_elements;

  $get_proc_link_fragment = md5(drupal_random_bytes(32));
  $get_proc_link = l(
        t('Decrypt'),
        current_path(),
        array(
          'attributes' => array(
            'id' => 'decryption-link',
            'class' => array(
              'btn',
              'btn-default',
            ),
          ),
          'fragment' => $get_proc_link_fragment,
        )
    );

  $form['pc_access_link'] = array(
    '#markup' => $get_proc_link,
  );
  return $form;
}

/**
 * Update proc cipher text entity.
 */
function _proc_update($form, &$form_state, $cids, $uids) {
  global $user;

  // Sanitize and preprocess arguments:
  $csvs     = _proc_get_csv_arguments(array(
    'cids_csv' => $cids,
    'uids_csv' => $uids,
  ));
  $uids     = $csvs['uids_csv']['array'];
  $uids_csv = $csvs['uids_csv']['string'];
  $cids     = $csvs['cids_csv']['array'];
  $cids_csv = $csvs['cids_csv']['string'];

  $ciphers_data = _proc_common_get_ciphers($cids);
  $privkey      = $ciphers_data['privkey'];
  $cid_cipher   = $ciphers_data['ciphers'];
  $form['pass'] = $ciphers_data['form_pass'];

  // If some recipient is being removed of some content, allow update to proceed
  // only if current user is also the author of the content:
  $recipients = array();
  foreach ($ciphers_data['ciphers'] as $cipher_data) {
    if ($cipher_data['proc_owner_uid'] != $user->uid) {
      // Curent recipients:
      foreach ($cipher_data['proc_recipients'] as $recipient) {
        $recipients[] = $recipient->uid;
      }
    }
  }
  if (!empty($recipients)) {
    $all_current_recipients = array_unique($recipients);
    if (array_diff($all_current_recipients, $uids)) {
      drupal_access_denied();
    }
  }

  $pubkeys_data        = _proc_common_get_pubkeys($uids);
  $recipients_pubkeys  = $pubkeys_data['recipients_pubkeys'];
  $post_max_size_bytes = $pubkeys_data['post_max_size_bytes'];

  $form['#attached']['js'] = _proc_common_inject_lib('update_protected');

  $encryption_form_elements = array(
    'proc_ciphers'             => $cid_cipher,
    'proc_ciphers_index'       => $cids,
    'proc_recipients_pubkeys'  => drupal_json_encode($recipients_pubkeys),
    'proc_post_max_size_bytes' => $post_max_size_bytes,
  );

  $form['#attached']['js'][] = array(
    'data' => array('proc' => _proc_common_get_decryption_form_data($privkey)),
    'type' => 'setting',
  );
  $form['#attached']['js'][0]['data']['proc'] += $encryption_form_elements;

  $update_field_form_elemement_prefixes = array(
    'cipher_text_cid_',
    'generation_timestamp_cid_',
    'generation_timespan_cid_',
    'browser_fingerprint_cid_',
    'source_file_name_cid_',
    'source_file_size_cid_',
    'source_file_type_cid_',
    'source_file_last_change_cid_',
  );

  foreach ($cids as $cid) {
    foreach ($update_field_form_elemement_prefixes as $update_field_form_elemement_prefix) {
      $form[$update_field_form_elemement_prefix . $cid] = array('#type' => 'hidden');
    }
  }

  $form['#submit'][] = '_proc_update_submit';
  $form['actions']['submit']['#submit'][] = '_proc_update_submit';

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update'),
    '#attributes' => array(
      'class' => array('proc-update-submit'),
      'onclick' => array(
        _proc_get_onclick_saving(),
      ),
    ),
  );
  return $form;
}

/**
 * Update proc cipher text entity submit callback.
 */
function _proc_update_submit($form, &$form_state) {
  $recipients_uids_new = str_getcsv($form_state['build_info']['args'][1]);
  asort($recipients_uids_new);
  $recipients_uids_new = array_values($recipients_uids_new);

  foreach ($form['#attached']['js'][0]['data']['proc']['proc_ciphers_index'] as $cid) {
    _proc_update_ciphertext($cid, $form, $form_state, $recipients_uids_new);
  }
}

/**
 * Helper function for encryption of files.
 */
function _proc_encrypt_file($form, &$form_state, $uids) {
  global $user;

  // Sanitize and preprocess argument:
  $csv      = _proc_get_csv_arguments(array('uids_csv' => $uids));
  $uids     = $csv['uids_csv']['array'];
  $uids_csv = $csv['uids_csv']['string'];

  $pubkeys_data = _proc_common_get_pubkeys($uids);
  $recipients_pubkeys = $pubkeys_data['recipients_pubkeys'];
  $post_max_size_bytes = $pubkeys_data['post_max_size_bytes'];

  foreach ($recipients_pubkeys as $recipients_pubkey_key => $recipients_pubkey) {
    // Send only the changed timestamp:
    unset($recipients_pubkeys[$recipients_pubkey_key]['key']);
  }

  $form['#attached']['js'] = _proc_common_inject_lib('encrypt_file');

  $uids_keys_changed = array_flip($uids);
  foreach ($uids_keys_changed as $uid_fliped_key => $uid_fliped) {
    $uids_keys_changed[$uid_fliped_key] = $recipients_pubkeys[$uid_fliped]['changed'];
  }

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_recipients_pubkeys_changed'  => drupal_json_encode($uids_keys_changed),
        'proc_recipients_uids'      => drupal_json_encode($uids),
        'proc_post_max_size_bytes' => $post_max_size_bytes,
      ),
    ),
    'type' => 'setting',
  );

  $form['#attached']['js'][0]['data']['proc'] += _proc_common_get_decryption_form_data(NULL);

  $form['upload'] = array(
    '#name' => 'files[]',
    '#type' => 'file',
    '#disabled' => FALSE,
  );

  $form['pc-upload-description'] = array(
    '#type' => 'item',
    '#title' => t('Choose a file'),
  );

  $hidden_fields = array(
    'cipher_text',
    'generation_timestamp',
    'generation_timespan',
    'browser_fingerprint',
    'source_file_name',
    'source_file_size',
    'source_file_type',
    'source_file_last_change',
  );

  foreach ($hidden_fields as $hidden_field) {
    $form[$hidden_field] = array('#type' => 'hidden');
  }

  $form['button'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#attributes' => array(
      'onclick' => array(
        _proc_get_onclick_saving(),
      ),
    ),
  );

  $form['button']['#ajax'] = array(
    'callback' => '_proc_encrypt_file_submit_callback',
    'wrapper'  => '-proc-encrypt-file-form',
    'method'   => 'replace',
    'effect'   => 'fade',
  );

  $form_state['build_info']['args'][0] = $uids_csv;

  return $form;
}

/**
 * Generation form for the keys.
 */
function _proc_generate_keys($form, &$form_state) {
  global $user;

  // If form has not just being submited.
  if (empty($form_state['values'])) {
    // If current user has already registered a keyring:
    if (!empty(_proc_get_pubkeys(array($user->uid))[0]['changed'])) {
      drupal_set_message(t("Your encryption keys are already generated. You don't need to generate new ones unless you have lost your password."), 'warning', FALSE);
      drupal_set_message(t('Warning: if you generate new encryption keys, you will lose access to all encrypted files which were previously sent to you.'), 'warning', FALSE);
    }
  };

  $form['#attached']['js'] = _proc_common_inject_lib('create_keys_protected');

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => _proc_common_get_decryption_form_data(NULL),
    ),
    'type' => 'setting',
  );

  $form['pass_fields'] = array(
    '#type' => 'password_confirm',
    '#description' => t("Enter the same password in both fields. Warning: this action can't be undone and newly generated keys can't be used for accessing previously encrypted data."),
    '#required' => TRUE,
  );
  $form['public_key'] = array(
    '#type' => 'hidden',
  );
  $form['encrypted_private_key'] = array(
    '#type' => 'hidden',
  );
  $form['generation_timestamp'] = array(
    '#type' => 'hidden',
  );
  $form['generation_timespan'] = array(
    '#type' => 'hidden',
  );
  $form['browser_fingerprint'] = array(
    '#type' => 'hidden',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Generate encryption keys'),
  );
  return $form;
}

/**
 * Submit function for public key submission.
 */
function _proc_generate_keys_submit($form, &$form_state) {
  global $user;
  // Prepare the form values for submission.
  $keyring_data = array(
    'public_key'            => check_plain($form_state['values']['public_key']),
    'encrypted_private_key' => check_plain($form_state['values']['encrypted_private_key']),
  );
  $keyring_metadata = array(
    'generation_timestamp' => check_plain($form_state['values']['generation_timestamp']),
    'generation_timespan'  => check_plain($form_state['values']['generation_timespan']),
    'browser_fingerprint'  => check_plain($form_state['values']['browser_fingerprint']),
  );

  $entity = entity_create('proc', array('type' => 'proc_keyring'));

  // Save only if key has been effectively generated:
  if (substr($keyring_data['public_key'], 5, 26) == 'BEGIN PGP PUBLIC KEY BLOCK') {
    $wrapper = entity_metadata_wrapper('proc', $entity);
    $wrapper->uid = $user->uid;
    $wrapper->proc_armoredown->set($user->uid);
    $wrapper->proc_armored = serialize($keyring_data);
    $wrapper->meta = serialize($keyring_metadata);
    // @todo implement full cipher indentifier as triplet domain-name:user-name:email:
    $wrapper->label->set($user->mail);
    $current_timestamp = time();
    $wrapper->created = $current_timestamp;
    $wrapper->changed = $current_timestamp;
    $wrapper->save();
  }

  if (isset($entity->pid)) {
    module_invoke_all('keyring_postsave', $entity->pid, $form_state, $user->uid);
    drupal_set_message(t('Encryption keys generated successfully.'));
    drupal_set_message(t("Your password can't be recovered. You must remember it. If you create new keys all previously Protected Content sent to you will be forever lost (and there is nothing we can do about that)."), 'info');
  }
  else {
    // @todo add keyring generation failure hook
    if (!_proc_browser_supported()) {
      drupal_set_message(t('Key generation error. It looks like you are using some unsupported browser. Please try it again using another one.'), 'error');
    }
    else {
      drupal_set_message(t('Sorry. Your public key has not been saved to the database. You might need to close tabs in your browser to release memory, or do it again using another browser.'), 'error');
    }
    watchdog('proc', 'Error on key generation/storage. User agent: %user-agent', array('%user-agent' => $_SERVER['HTTP_USER_AGENT']), WATCHDOG_ERROR);
  }
  cache_clear_all(NULL, 'cache_form', TRUE);
}

/**
 * Submit function for cipher text submission.
 */
function _proc_encrypt_file_submit_callback($form, &$form_state) {
  global $user;
  global $base_url;
  global $language;

  $recipients_uids = str_getcsv($form_state['build_info']['args'][0]);
  $recipients_uids_new = str_getcsv($form_state['build_info']['args'][0]);
  // Sorting UIDs just because sorting is beautiful.
  asort($recipients_uids_new);
  $recipients_uids_new = array_values($recipients_uids_new);

  // At this moment UIDs have been alredy checked.
  // @todo consider using drupal_check_token to assure form data integrity.
  $recipients_uids = serialize($recipients_uids);

  // Prepare the form values for submission.
  $ciphertxt_data = array(
    'cipher_text' => check_plain($form_state['values']['cipher_text']),
  );

  $ciphertxt_metadata = array(
    'source_file_size'     => check_plain($form_state['values']['source_file_size']),
    'generation_timespan'  => check_plain($form_state['values']['generation_timespan']),
    'source_file_type'     => check_plain($form_state['values']['source_file_type']),
    'browser_fingerprint'  => check_plain($form_state['values']['browser_fingerprint']),
    'generation_timestamp' => check_plain($form_state['values']['generation_timestamp']),
    'source_file_name'     => check_plain($form_state['values']['source_file_name']),
  );

  $entity = entity_create('proc', array('type' => 'proc_ciphertxt'));

  if (!empty($ciphertxt_metadata['source_file_name'])) {
    $wrapper = entity_metadata_wrapper('proc', $entity);
    // @todo deprecate property uid in favor of proc_armoredown field.
    $wrapper->uid = $user->uid;
    $wrapper->proc_armoredown->set($user->uid);

    $wrapper->proc_armored = _proc_cipher_serialize($ciphertxt_data, $form, $form_state);

    $wrapper->meta = serialize($ciphertxt_metadata);
    $wrapper->proc_recipient->set($recipients_uids_new);
    $wrapper->language($language->language);
    $wrapper->label = $ciphertxt_metadata['source_file_name'];
    $current_timestamp = time();
    $wrapper->created = $current_timestamp;
    $wrapper->changed = $current_timestamp;
    $wrapper->save();
  }
  // @todo add option to turn off cipher post save default messages.
  if (isset($entity->pid)) {
    $success_encryption_message = t(
      'Success. Please share with the recipient the Exclusive Access Link for your Protected Content: <a href="@exclusive-access-link">@full-exclusive-access-link</a>',
      array(
        '@exclusive-access-link' => url('proc/' . $entity->pid),
        '@full-exclusive-access-link' => url($base_url . '/proc/' . $entity->pid),
      )
    );
    $proc_link = l(t('decrypt'), 'proc/' . $entity->pid);
    $watchdog_args = array('@type' => 'proc_ciphertxt', '%pid' => $entity->pid);
    watchdog('proc', '@type: added %pid.', $watchdog_args, WATCHDOG_NOTICE, $proc_link);
  }
  else {
    $failure_encryption_message = t('Sorry. Cipher text of your file has not been saved. Close other tabs in your browser to release some memory and try again.');
    $context = array(
      'unalterable_encryption_source_form' => $form,
    );
    drupal_alter('cipher_postsave_failure_encryption_message', $failure_encryption_message, $context);
    if (!empty($failure_encryption_message)) {
      drupal_set_message($failure_encryption_message, 'error');
    }
  }
  $context = array(
    'unalterable_encryption_source_form' => $form,
    'unalterable_pid' => $entity->pid,
  );
  drupal_alter('cipher_postsave_success_encryption_message', $success_encryption_message, $context);
  if (!empty($success_encryption_message)) {
    drupal_set_message($success_encryption_message);
  }

  cache_clear_all(NULL, 'cache_form', TRUE);
  module_invoke_all('cipher_postsave', $entity->pid, $form_state);
  $commands[] = ajax_command_remove('div.messages');
  $commands[] = ajax_command_before('#main-content', theme('status_messages'));
  ajax_deliver(array('#type' => 'ajax', '#commands' => $commands));
}
