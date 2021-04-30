<?php

/**
 * @file
 * Hooks provided by the proc_storage module.
 */

/**
 * Allow modules to alter cipher placement settings.
 *
 * @param array $proc_storage_placement_settings
 *   Alterable array of cipher storage placement settings.
 * @param array $context
 *   Unalterable cipher text context.
 */
function hook_cipher_storage_placement_alter(array $proc_storage_placement_settings, array $context) {
}
