<?php

/**
 * @file
 * Hooks provided by the proc module.
 */

/**
 * Allow modules to act on cipher post save.
 *
 * @param int $pid
 *   Protected Content entity ID of created cipher text.
 * @param array $form_state
 *   Form state from cipher text submit.
 */
function hook_cipher_postsave(int $pid, array $form_state) {
}

/**
 * Allow modules to alter cipher text retrieved.
 *
 * @param array $cipher_text_data
 *   Alterable array of cipher text data.
 * @param object $context
 *   Unalterable full cipher text object.
 */
function hook_get_cipher_text_alter(array $cipher_text_data, array $context) {
}
