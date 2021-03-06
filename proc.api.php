<?php

/**
 * @file
 * Hooks provided by the proc module.
 */

/**
 * Allow modules to act on cipher post save.
 *
 * @param string $pid
 *   Protected Content entity ID of created cipher text.
 * @param array $form_state
 *   Form state from cipher text submit.
 */
function hook_cipher_postsave(string $pid, array $form_state) {
}

/**
 * Allow modules to alter cipher text retrieved.
 *
 * @param array $cipher_text_data
 *   Alterable array of cipher text data.
 * @param array $context
 *   Unalterable full cipher text object.
 */
function hook_get_cipher_text_alter(array $cipher_text_data, array $context) {
}

/**
 * Allow modules to alter successful encryption message.
 *
 * @param array $success_encryption_message
 *   Alterable array containing message definition.
 * @param array $context
 *   Unalterable encryption form and pid as a means of context.
 */
function hook_cipher_postsave_success_encryption_message_alter(array $success_encryption_message, array $context) {
}

/**
 * Allow modules to alter failure encryption message.
 *
 * @param array $failure_encryption_message
 *   Alterable array containing message text.
 * @param array $context
 *   Unalterable encryption form as a means of context.
 */
function hook_cipher_postsave_failure_encryption_message_alter(array $failure_encryption_message, array $context) {
}

/**
 * Allow modules to act on keyring post save.
 *
 * @param string $pid
 *   Protected Content entity ID of created keyring.
 * @param array $form_state
 *   Form state from keyring submit.
 * @param string $uid
 *   User ID of keyring owner.
 */
function hook_keyring_postsave(string $pid, array $form_state, string $uid) {
}
