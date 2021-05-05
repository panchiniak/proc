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
    '#required' => TRUE,
    '#title' => t('Stream wrapper definition'),
    '#description' => t('Fill in a comma-separated values list to define alternative wrappers for redundancy. The first in the list will be the primary one for reading and all of them will be used for writing. You have to fill at least one.'),
    '#default_value' => variable_get('proc_storage_global_stream_wrapper', 'private://'),
    '#size' => 40,
  );
  $form['proc_storage_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable stream wrapper for saving or updating'),
    '#description' => t('Reading will be automatically switched according to current placement (database or stream wrapper) of each content.'),
    '#default_value' => variable_get('proc_storage_enabled', 0),
  );
  // @todo Add form validation.
  return system_settings_form($form);
}