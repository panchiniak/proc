<?php

/**
 * @file
 * Forms for Protected Content module.
 */

/**
 * Settings form callback.
 */
function _proc_storage_settings_form($form, &$form_state) {
  $form['proc_storage_global_stream_wrapper'] = array(
    '#type' => 'textfield',
    '#title' => t('Local stream wrapper'),
    '#description' => t('Fill in a comma-separated values list to define alternative wrappers for redundancy. The first in the list will be the primary one.'),
    '#default_value' => variable_get('proc_storage_global_stream_wrapper', 'private://'),
    '#size' => 40,
  );
  $form['proc_storage_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable stream wrapper for saving'),
    '#description' => t('Read will be automatically switched according to current state of the content.'),
    '#default_value' => variable_get('proc_storage_enabled', 0),
  );
  return system_settings_form($form);
}
