<?php

/**
 * @file
 * Forms for Protected Content module.
 */

/**
 * Retrieval of a file from its cipher text.
 */
function _proc_decrypt_to_file($form, &$form_state, $cid) {
  global $user;

  $cid_cipher = _proc_get_cipher((int) $cid)[0];

  _proc_lib_load();

  // Get current user encrypted privkey.
  $privkey = _proc_get_keys($user->uid)[0]->encrypted_private_key;

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'proc') . '/js/proc.decrypt_protected_to_file.js' => array(
      'type' => 'file',
    ),
  );

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_uid' => $user->uid,
        'proc_pass' => $user->pass,
        'proc_mail' => $user->mail,
        'proc_name' => $user->name,
        'proc_privkey' => $privkey,
        'proc_cipher' => $cid_cipher->cipher_text,
        'proc_source_file_name' => $cid_cipher->source_file_name,
        'proc_source_file_size' => $cid_cipher->source_file_size,
      ),
    ),
    'type' => 'setting',
  );

  $form['pass'] = array(
    '#type' => 'password',
    '#title' => t('Protected Content Password'),
    '#description' => t('You must type in the password you have used when registering your Protected Content Key.'),
    '#required' => TRUE,
  );

  $form['pc_access_link'] = array(
    '#markup' => t('<a id="decryption-link" href="#@random_id">Get it</a>', array(
      '@random_id' => md5(drupal_random_bytes(32)),
    )
    ),
  );

  return $form;
}

/**
 * Helper function for retrieval of pubkeys.
 */
function _proc_get_pubkeys($users) {
  $pubkeys = array();
  foreach ($users as $recipient) {
    $pubkeys[] = _proc_get_keys($recipient)[0]->public_key;
  }
  return $pubkeys;
}

/**
 * Helper function for encryption of files.
 */
function _proc_encrypt_file($form, &$form_state, $uids) {
  global $user;

  if (isset($uids)) {
    $uids = check_plain($uids);
    $csv_values = str_getcsv($uids);
    $uids = array();
    foreach ($csv_values as $csv_value) {
      if (is_numeric($csv_value) && $csv_value = (int) $csv_value) {
        $uids[] = $csv_value;
      }
    }
  }
  // Check uniqueness.
  $uids = array_unique($uids);
  $uids_csv = implode(',', $uids);

  $recipients_pubkeys = array_filter(_proc_get_pubkeys($uids));

  // Get post_max_size to set dynamically maximum file size.
  $post_max_size = substr_replace(ini_get('post_max_size'), "", -1);
  $post_max_size_bytes = $post_max_size * 1000000;

  $disable_upload = FALSE;
  if (!isset($uids)) {
    drupal_set_message(t("You must chose an enabled recipient by filling in the URL the recipient user's identification. Recipient must have had registered a Protected Content Key."), 'error');
    // @TODO: allow drupal_goto user back to recipient's list if any.
    $disable_upload = TRUE;
  }
  // @TODO: add error handling to _proc_lib_load()
  _proc_lib_load();

  // @TODO: change this to allow multiple pubkeys.
  $recipient_pubkeys = array_pop(array_reverse($recipients_pubkeys));

  if (empty($recipient_pubkeys)) {
    drupal_set_message(t('Sorry. A slected recipient is not registered to receive Protected Content.'), 'error');
    // @TODO: also here allow drupal_goto user back to recipient's list if any.
    $disable_upload = TRUE;
  }

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'proc') . '/js/proc.encrypt_file.js' => array(
      'type' => 'file',
    ),
  );

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_uid' => $user->uid,
        'proc_pass' => $user->pass,
        'proc_mail' => $user->mail,
        'proc_name' => $user->name,
        'proc_recipient_pubkey' => $recipient_pubkeys,
        'proc_recipients_pubkeys' => drupal_json_encode($recipients_pubkeys),
        'proc_post_max_size_bytes' => $post_max_size_bytes,
      ),
    ),
    'type' => 'setting',
  );

  $form['upload'] = array(
    '#name' => 'files[]',
    '#type' => 'file',
    '#disabled' => $disable_upload,
  );

  $form['pc-upload-description'] = array(
    '#type' => 'item',
    '#title' => t('Choose a file'),
  );

  $form['cipher_text'] = array(
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
  $form['source_file_name'] = array(
    '#type' => 'hidden',
  );
  $form['source_file_size'] = array(
    '#type' => 'hidden',
  );
  $form['source_file_type'] = array(
    '#type' => 'hidden',
  );
  $form['source_file_last_change'] = array(
    '#type' => 'hidden',
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#attributes' => array(
      'onclick' => array(
         // @TODO: make this in a JS file
        'javascript:var s=this;setTimeout(function(){s.value="Saving...";s.disabled=true;},1);',
      ),
    ),
  );

  // Sanitize form state.
  $form_state['build_info']['args'][0] = $uids_csv;

  return $form;
}

/**
 * Generation form for the keys.
 */
function _proc_generate_keys($form, &$form_state) {
  global $user;

  if (empty($form_state['values'])) {
    $has_key = db_select('proc_keys', 'k')
      ->fields('k')
      ->fields('k', array("uid"))
      ->condition('uid', $user->uid, '=')
      ->execute()
      ->fetchAssoc();
    // If user has keys.
    if (!empty($has_key)) {
      drupal_set_message(t("You have already registered Protected Content keys and you don't need to register new ones unless you have lost your Protected Content password."), 'warning', FALSE);
      drupal_set_message(t('Warning: if you register a new password you will lose access to all encrypted data within this site previously sent to you.'), 'warning', FALSE);
    }
  };
  _proc_lib_load();

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'proc') . '/js/proc.create_keys_protected.js' => array(
      'type' => 'file',
    ),
  );

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_uid' => $user->uid,
        'proc_pass' => $user->pass,
        'proc_mail' => $user->mail,
        'proc_name' => $user->name,
      ),
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
    '#value' => t('Generate new keys'),
    '#attributes' => array(
      'onclick' => array(
         // @TODO: make this in a JS file
        'javascript:var s=this;setTimeout(function(){s.value="Saving...";s.disabled=true;},1);',
      ),
    ),
  );
  return $form;
}

/**
 * Helper function to check if user agent in unsuported.
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
 * Submit function for public key submission.
 */
function _proc_generate_keys_submit($form, &$form_state) {
  global $user;
  // Prepare the form values for submission.
  $record = new stdClass();
  $record->uid = $user->uid;
  $record->public_key = $form_state['values']['public_key'];
  $record->encrypted_private_key = $form_state['values']['encrypted_private_key'];
  $record->generation_timestamp = $form_state['values']['generation_timestamp'];
  $record->generation_timespan = $form_state['values']['generation_timespan'];
  $record->browser_fingerprint = $form_state['values']['browser_fingerprint'];
  $record->created = time();

  $record_result = 0;

  if (!empty($record->public_key)) {
    $record_result = drupal_write_record('proc_keys', $record);
  }

  if ($record_result == 1) {
    drupal_set_message(t('Encryption keys generated successfully.'));
    drupal_set_message(t("Your password can't be recovered. You must remember it. If you create new keys all previously Protected Content sent to you will be forever lost (and there is nothing we can do about that)."), 'info');
  }
  else {
    if (!_proc_browser_supported()) {
      drupal_set_message(t('Key generation error. It looks like you are using some unsupported browser. Please try again using another one.'), 'error');
    }
    else {
      drupal_set_message(t('Sorry. Your public key has not been saved to the database. You might need to close tabs in your browser to release memory.'), 'error');
    }
    watchdog('proc', 'Error on key generation/storage. User agent: %user-agent', array('%user-agent' => $_SERVER['HTTP_USER_AGENT']), WATCHDOG_ERROR);
  }

  cache_clear_all(NULL, 'cache_form', TRUE);
}

/**
 * Submit function for public key submission.
 */
function _proc_encrypt_file_submit($form, &$form_state) {
  global $user;
  global $base_url;

  // @TODO: add support for multiple recipients
  $recipient_uid = check_plain($form_state['build_info']['args'][0]);

  $recipients_uids = array();
  $recipients_uids = str_getcsv($form_state['build_info']['args'][0]);
  // At this moment UIDs have been alredy checked.
  // @TODO: consider to drupal_check_token to assure form data integrity.
  $recipients_uids = serialize($recipients_uids);

  // Prepare the form values for submission.
  $record = new stdClass();
  $record->sender_uid = $user->uid;
  $record->recipient_uid = $recipient_uid;

  $record->recipients_uids = $recipients_uids;

  $record->created = time();
  $record->cipher_text = $form_state['values']['cipher_text'];
  $record->cipher_generation_timestamp = $form_state['values']['generation_timestamp'];
  $record->cipher_generation_timespan = $form_state['values']['generation_timespan'];
  $record->cipher_browser_fingerprint = $form_state['values']['browser_fingerprint'];
  $record->source_file_name = $form_state['values']['source_file_name'];
  $record->source_file_size = $form_state['values']['source_file_size'];
  $record->source_file_type = $form_state['values']['source_file_type'];
  // @TODO: adjust database schema to comply with source_file_last_change format.
  if (!empty($record->cipher_text)) {
    $record_result = drupal_write_record('proc_cipher_texts', $record);
  }

  if ($record_result == 1) {
    $success_encryption_message = t(
      'Success. Please share with the recipient the Exclusive Access Link for your Protected Content: <a href="@exclusive-access-link">@full-exclusive-access-link</a>',
      array(
         // @TODO: replace hardcoded part of address by a CONSTANT.
        '@exclusive-access-link' => url('protected-content/file/open/' . $record->cid),
        '@full-exclusive-access-link' => url($base_url . '/protected-content/file/open/' . $record->cid),
      )
    );
    drupal_set_message($success_encryption_message);

    drupal_set_message(t('Only the recipient chosen will be able to access this Protected Content.'), 'info');
    // @TODO: add here a protectected file post creation hook to allow other module to send notifications
    // make visible the recipient and sender users in the hook.
  }
  else {
    drupal_set_message(t('Sorry. Cipher text of your file has not been saved. Close other tabs in your browser to release some memory and try again.'), 'error');
    // @TODO: add watchdog on cipher generation errors.
  }
  cache_clear_all(NULL, 'cache_form', TRUE);
}
