<?php
/**
 * @file
 * Forms for Protected Content module.
 */

/**
 * Retrieval of file from its cipher text.
 */
function _proc_decrypt_to_file($form, &$form_state) {
  global $user;

  $cid_arg = arg(3);

  // @TODO: make it READABLE
  if (isset($cid_arg) and check_url($cid_arg)) {
    // Check URL and plain text.
    $cid_csv = check_plain($cid_arg);
    if (isset($cid_csv)) {
      $cids = str_getcsv($cid_csv);
      if (isset($cids)) {
        $cid_ciphers = array();
        foreach ($cids as $cid) {
          $cid = (int) $cid;
          if (($cid != 0) and is_int($cid)) {
            // @TODO: allow multiple recipients
            $cid_cipher = _proc_get_cipher($cid)[0];
            if (isset($cid_cipher)) {
              // $recipient_pubkeys[$uid] = $recipient_pubkey;.
              $cid_ciphers[] = $cid_cipher;
            }
          }
        }
      }
    }
  }

  _proc_lib_load();
  drupal_add_js(drupal_get_path('module', 'proc') . '/js/proc.decrypt_protected_to_file.js');

  // Get current user encrypted privkey.
  $privkey = _proc_get_keys($user->uid)[0]->encrypted_private_key;

  drupal_add_js(
        array(
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
        ), 'setting'
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
 * Encryption form for files.
 */
function _proc_encrypt_file($form, &$form_state) {
  global $user;
  $uids_arg = arg(3);

  // Get post_max_size to set dynamically maximum file size.
  $post_max_size = substr_replace(ini_get('post_max_size'), "", -1);
  $post_max_size_bytes = $post_max_size * 1000000;

  if (!isset($uids_arg)) {
    drupal_set_message(t("You must chose an enabled recipient by filling in the URL the recipient user's identification"), 'error');
    // @TODO: send user back to recipient's list if any.
    return;
  }

  $recipient_pubkeys = array();
  // If there is no user id defined encrypt the sender her/himself.
  if (!isset($uids_arg)) {
    $recipient_pubkeys[$user->uid] = _proc_get_keys($user->uid)[0]->public_key;
  }
  if (isset($uids_arg) and check_url($uids_arg)) {

    // Check URL and plain text.
    $uids_csv = check_plain($uids_arg);
    if (isset($uids_csv)) {
      $uids = str_getcsv($uids_csv);
      if (isset($uids)) {
        foreach ($uids as $uid) {
          $uid = (int) $uid;
          if (($uid != 0) and is_int($uid)) {
            $recipient_pubkey = _proc_get_keys($uid)[0]->public_key;
            if (isset($recipient_pubkey)) {
              // $recipient_pubkeys[$uid] = $recipient_pubkey;.
              $recipient_pubkeys[] = $recipient_pubkey;
            }
          }
        }
      }
    }
  }
  _proc_lib_load();
  drupal_add_js(drupal_get_path('module', 'proc') . '/js/proc.encrypt_file.js');

  if (empty($recipient_pubkeys)) {
    drupal_set_message(t('Sorry. The slected recipient is not registered to receive Protected Content.'), 'error');
    drupal_goto('protected-content/people/send-file');
  }

  drupal_add_js(
        array(
          'proc' => array(
            'proc_uid' => $user->uid,
            'proc_pass' => $user->pass,
            'proc_mail' => $user->mail,
            'proc_name' => $user->name,
            'proc_recipient_pubkey' => $recipient_pubkeys,
            'proc_post_max_size_bytes' => $post_max_size_bytes,
          ),
        ), 'setting'
    );

  $form['upload'] = array(
    '#name' => 'files[]',
    '#type' => 'file',
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
  return $form;
}

/**
 * Generation form for the keys.
 */
function _proc_gnerate_keys($form, &$form_state) {
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

  drupal_add_js(drupal_get_path('module', 'proc') . '/js/proc.create_keys_protected.js');

  drupal_add_js(
        array(
          'proc' => array(
            'proc_uid' => $user->uid,
            'proc_pass' => $user->pass,
            'proc_mail' => $user->mail,
            'proc_name' => $user->name,
          ),
        ), 'setting'
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
function _proc_browser_supported(){
  $path = drupal_get_path('module', 'proc') . '/' . 'proc' . '.info';
  $info = drupal_parse_info_file($path);
  $unsuported_browsers_array = str_getcsv($info['unsuported_browsers_csv']);

  foreach ($unsuported_browsers_array as $unsuported_browser){
    if (strpos($_SERVER['HTTP_USER_AGENT'], $unsuported_browser) !== false){
      return FALSE;
    }
  }
  return TRUE;
}


/**
 * Submit function for public key submission.
 */
function _proc_gnerate_keys_submit($form, &$form_state) {
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
    if (!_proc_browser_supported()){
      drupal_set_message(t('Key generation error. It looks like you are using some unsupported browser. Please try again using another one.'), 'error');
    }
    else{
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

  // Prepare the form values for submission.
  $record = new stdClass();
  $record->sender_uid = $user->uid;
  $record->recipient_uid = $recipient_uid;
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

  // @TODO: replace hardcoded part of address by a CONSTANT.
  $exclusive_access_link = $base_url . '/' . 'protected-content/file/open/' . $record->cid;

  if ($record_result == 1) {
    // @TODO: remove concatenation in message
    drupal_set_message(t('Success. Please share with the recipient the Exclusive Access Link for your Protected Content: ') . l($exclusive_access_link, $exclusive_access_link));
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
