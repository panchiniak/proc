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

  $cid_cipher = _proc_get_cipher((int) $cid);

  _proc_lib_load();

  // Get current user encrypted privkey.
  $privkey = _proc_get_keys($user->uid)['encrypted_private_key'];

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'proc') . '/js/proc.decrypt_protected_to_file.js' => array(
      'type' => 'file',
    ),
  );

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_uid' => $user->uid,
        'proc_pass' => drupal_hash_base64(drupal_get_hash_salt() . $user->uid),
        'proc_mail' => $user->mail,
        'proc_name' => $user->name,
        'proc_privkey' => $privkey,
        'proc_cipher' => $cid_cipher['cipher_text'],
        'proc_source_file_name' => $cid_cipher['source_file_name'],
        'proc_source_file_size' => $cid_cipher['source_file_size'],

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
 * Helper function for encryption of files.
 */
function _proc_encrypt_file($form, &$form_state, $uids) {
  global $user;

  // @TODO: allow other modules to alter selected recipients based on required
  // criteria

  if (isset($uids)) {
    // Remove query string from the path if any.
    $uids = preg_replace('/\?.+/','',$uids);
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

  // If some UID does not return keys:
  if (empty($recipients_pubkeys) || (count($uids) != count($recipients_pubkeys))) {
    drupal_not_found();
  }

  // Get post_max_size to set dynamically maximum file size.
  $post_max_size = substr_replace(ini_get('post_max_size'), "", -1);
  $post_max_size_bytes = $post_max_size * 1000000;

  // @TODO: add error handling to _proc_lib_load()
  _proc_lib_load();

  $form['#attached']['js'] = array(
    drupal_get_path('module', 'proc') . '/js/proc.encrypt_file.js' => array(
      'type' => 'file',
    ),
  );

  $form['#attached']['js'][] = array(
    'data' => array(
      'proc' => array(
        'proc_uid' => $user->uid,
        'proc_pass' => drupal_hash_base64(drupal_get_hash_salt() . $user->uid),
        'proc_mail' => $user->mail,
        'proc_name' => $user->name,
        'proc_recipients_pubkeys' => drupal_json_encode($recipients_pubkeys),
        'proc_post_max_size_bytes' => $post_max_size_bytes,
      ),
    ),
    'type' => 'setting',
  );

  $form['upload'] = array(
    '#name' => 'files[]',
    '#type' => 'file',
    '#disabled' => FALSE,
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
  $form['button'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#attributes' => array(
      'onclick' => array(
         // @TODO: make this in a JS file
        'javascript:var s=this;setTimeout(function(){s.value="Saving...";s.classList.add("ajax-progress-throbber");s.disabled=true;},1);',
      ),
    ),
  );
  $form['button']['#ajax'] = array(
    'callback' => '_proc_encrypt_file_submit_callback',
    'wrapper' => '-proc-encrypt-file-form',
    'method' => 'replace',
    'effect' => 'fade',
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
    // If current has not yet registered a keyring.
    if (!empty(_proc_get_pubkeys(array($user->uid))[0])) {
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
        'proc_pass' => drupal_hash_base64(drupal_get_hash_salt() . $user->uid),
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
 * Submit function for public key submission.
 */
function _proc_generate_keys_submit($form, &$form_state) {
  global $user;
  // Prepare the form values for submission.
  $keyring_data = array(
    'public_key' => check_plain($form_state['values']['public_key']),
    'encrypted_private_key' => check_plain($form_state['values']['encrypted_private_key']),
    'generation_timestamp' => check_plain($form_state['values']['generation_timestamp']),
    'generation_timespan' => check_plain($form_state['values']['generation_timespan']),
    'browser_fingerprint' => check_plain($form_state['values']['browser_fingerprint']),
  );

  $entity = entity_create('proc', array('type' => 'proc_keyring'));
  $wrapper = entity_metadata_wrapper('proc', $entity);
  // @TODO: deprecate property uid in favor of proc_armoredown field.
  $wrapper->uid = $user->uid;
  $wrapper->proc_armoredown->set($user->uid);
  $wrapper->proc_armored = serialize($keyring_data);
  // @TODO: implment full cipher indentifier:
  // client side source domain name:user email.
  $wrapper->title = $user->mail;
  $wrapper->created = time();
  $wrapper->save();

  // Just soppose not being 'undefined' is enough to say a pubkey has been
  // generated.
  // @TODO: improve heuristics of server side pubkey validation.
  if ((isset($entity->pid)) && (!empty($keyring_data['public_key'])) && ($keyring_data['public_key'] != 'undefined')) {
    // @TODO: add keyring generation success hook
    drupal_set_message(t('Encryption keys generated successfully.'));
    drupal_set_message(t("Your password can't be recovered. You must remember it. If you create new keys all previously Protected Content sent to you will be forever lost (and there is nothing we can do about that)."), 'info');
  }
  else {
    // @TODO: add keyring generation failure hook
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
 * Submit function for cipher text submission.
 */
function _proc_encrypt_file_submit_callback($form, &$form_state) {
  global $user;
  global $base_url;
  global $language;

  $recipients_uids = array();
  $recipients_uids = str_getcsv($form_state['build_info']['args'][0]);
  $recipients_uids_new = str_getcsv($form_state['build_info']['args'][0]);
  // Sorting UIDs just because sorting is beautiful.
  asort($recipients_uids_new);
  $recipients_uids_new = array_values($recipients_uids_new);

  // At this moment UIDs have been alredy checked.
  // @TODO: consider using drupal_check_token to assure form data integrity.
  $recipients_uids = serialize($recipients_uids);

  // Prepare the form values for submission.
  $ciphertxt_data = array(
    'source_file_size' => check_plain($form_state['values']['source_file_size']),
    'cipher_text' => check_plain($form_state['values']['cipher_text']),
    'generation_timespan' => check_plain($form_state['values']['generation_timespan']),
    'source_file_type' => check_plain($form_state['values']['source_file_type']),
    'browser_fingerprint' => check_plain($form_state['values']['browser_fingerprint']),
    'generation_timestamp' => check_plain($form_state['values']['generation_timestamp']),
    'source_file_name' => check_plain($form_state['values']['source_file_name']),
  );

  $entity = entity_create('proc', array('type' => 'proc_ciphertxt'));

  if (!empty($ciphertxt_data)){
    $wrapper = entity_metadata_wrapper('proc', $entity);
    // @TODO: deprecate property uid in favor of proc_armoredown field.
    $wrapper->uid = $user->uid;
    $wrapper->proc_armoredown->set($user->uid);
    $wrapper->proc_armored = serialize($ciphertxt_data);
    $wrapper->proc_recipient->set($recipients_uids_new);
    $wrapper->language($language->language);
    $wrapper->title = $ciphertxt_data['source_file_name'];
    $wrapper->created = time();
    $wrapper->save();
  }

  // @TODO: add option to turn off cipher post save default messages.
  if (isset($entity->pid)) {
    $success_encryption_message = t(
      'Success. Please share with the recipient the Exclusive Access Link for your Protected Content: <a href="@exclusive-access-link">@full-exclusive-access-link</a>',
      array(
         // @TODO: replace hardcoded part of address by a CONSTANT.
        '@exclusive-access-link' => url('proc/' . $entity->pid),
        '@full-exclusive-access-link' => url($base_url . '/proc/' . $entity->pid),
      )
    );
    drupal_set_message($success_encryption_message);
    $proc_link = l(t('decrypt'), 'proc/' . $entity->pid);
    $watchdog_args = array('@type' => 'proc_ciphertxt', '%pid' => $entity->pid);
    watchdog('proc', '@type: added %pid.', $watchdog_args, WATCHDOG_NOTICE, $proc_link);

    drupal_set_message(t('Only the recipient chosen will be able to access the Protected Content.'), 'info');
  }
  else {
    // @TODO: add ciphertext failure hook
    // @TODO: add watchdog on cipher generation errors.
    drupal_set_message(t('Sorry. Cipher text of your file has not been saved. Close other tabs in your browser to release some memory and try again.'), 'error');
  }
  cache_clear_all(NULL, 'cache_form', TRUE);

  module_invoke_all('cipher_postsave', $entity->pid, $form_state);

  $commands[] = ajax_command_remove('div.messages');
  $commands[] = ajax_command_before('#main-content', theme('status_messages'));
  ajax_deliver(array('#type' => 'ajax', '#commands' => $commands));

}
