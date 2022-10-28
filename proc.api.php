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
 * @param array $entity
 *   Complete protected content entity object.
 */
function hook_cipher_postsave(string $pid, array $form_state, array $entity) {
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

/**
 * Allow modules to alter cipher serialization.
 *
 * @param array $cipher
 *   Alterable array containing a ciphertext or URI.
 * @param array $context
 *   Unalterable cipher serialization context containing cid.
 */
function hook_cipher_serialize_alter(array $cipher, array $context) {
}

/**
 * Allow modules to alter cipher unserialization.
 *
 * @param array $cipher
 *   Alterable array containing a ciphertext or URI.
 * @param arrayun $context
 *   Unalterable cipher serialization context.
 */
function hook_cipher_unserialize_alter(array $cipher, array $context) {
}

/**
 * Allow modules to act on update post save.
 *
 * @param array $entity
 *   Protected Content entity.
 * @param string $type
 *   The type of entity being updated (i.e. node, user, comment).
 */
function hook_cipher_update_postsave(array $entity, string $type) {
}

// @todo: add cipher_presave_failure_encryption_text
// @todo: add cipher_postsave_text