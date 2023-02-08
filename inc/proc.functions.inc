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
