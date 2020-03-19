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
 *   Form element of proc reference field.
 */
function hook_proc_refield_get_widget_alter(array $proc_refield_widget) {
}
