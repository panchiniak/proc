<?php

/**
 * @file
 * Hooks provided by the proc_refield module.
 */

/**
 * Allow modules to alter proc_refield form element.
 *
 * @param array $element
 *   Form element of proc reference field.
 */
function hook_proc_refield_widget_process_alter(array $element) {
}

/**
 * Allow modules to alter proc refield widget list.
 *
 * @param array $proc_refield_widget
 *   List of available entity reference field widgets.
 */
function hook_proc_refield_get_widget_alter(array $proc_refield_widget) {
}

/**
 * Allow modules to alter add file link.
 *
 * @param string $add_file_link
 *   Alterable array of cipher text data.
 * @param array $context
 *   Unalterable field instance and form element.
 */
function hook_proc_refield_add_file_link_alter(string $add_file_link, array $context) {
}
