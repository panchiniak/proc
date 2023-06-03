<?php

/**
 * @file
 * Primary module hooks for procalab_proc module.
 */

declare(strict_types=1);

/**
 * Allow modules to alter decrypt link classes.
 *
 * @param array $cipher_text_data
 *   Alterable array of cipher text data.
 * @param array $context
 *   Unalterable full cipher text object.
 */
function hook_proc_alter_decryption_link_classes(array &$decryption_link_classes): void {
  // This might be needed for adjusting to different themes.
}
