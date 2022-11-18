<?php

/**
 * @file
 * Hooks provided by the proc_refield module.
 */

/**
 * Allow modules to alter recipients fetcher argument.
 *
 * @param array $recipients_argument
 *   Alterable array of recipients fetcher arguments (criteria and path).
 * @param array $context
 *   Unalterable context enriched by field instance and form element.
 */
function hook_recipients_fetcher_argument_alter(array $recipients_argument, array $context) {
}

/**
 * Allow modules to alter direct fetcher snippet.
 *
 * @param string $direct_fetcher
 *   Direct fetcher code as JS snippet.
 * @param array $context
 *   Unalterable context enriched by field instance and form element.
 */
function hook_direct_recipients_fetcher_alter(string $direct_fetcher, array $context) {
}

/**
 * Allow modules to alter view recipients fetcher argument snippet.
 *
 * @param string $view_recipients_fetcher_argument
 *   Views fetcher code as JS snippet.
 * @param array $context
 *   Unalterable context enriched by field instance and form element.
 */
function hook_view_recipients_fetcher_argument_alter(string $view_recipients_fetcher_argument, array $context) {
}

/**
 * Allow modules to alter add file link.
 *
 * @param string $add_file_link
 *   Alterable array of cipher text data.
 * @param array $context
 *   Unalterable context enriched by field instance and form element.
 */
function hook_add_proc_file_link_alter(string $add_file_link, array $context) {
}

/**
 * Allow modules to alter decrypt file link.
 *
 * @param string $decrypt_file_link
 *   Alterable string of decryption link.
 * @param array $context
 *   Unalterable field widget context enriched by field instance and form
 *   element objects.
 */
function hook_decrypt_proc_file_link_alter(string $decrypt_file_link, array $context) {
}

/**
 * Allow modules to alter proc refield widget list.
 *
 * @param array $proc_refield_widget
 *   List of available entity reference field widgets.
 */
function hook_proc_refield_get_widget_alter(array $proc_refield_widget) {
}
