<?php

/**
 * @file
 * proc_storage.functions.inc
 */

/**
 * Helper function for getting placement settings.
 */
function _proc_storage_get_placement_settings($cipher, $cipher_context) {
  $proc_storage_placement_settings = [
    'proc_storage_global_stream_wrapper'    => variable_get('proc_storage_global_stream_wrapper', 'private://'),
    'proc_storage_enabled'                  => variable_get('proc_storage_enabled', 0),
    'proc_storage_file_name_prefix'         => variable_get('proc_storage_file_name_prefix', ''),
  ];

  $context = [
    'unalterable_cipher'         => $cipher,
    'unalterable_cipher_context' => $cipher_context,
  ];
  drupal_alter('cipher_storage_placement', $proc_storage_placement_settings, $context);

  return $proc_storage_placement_settings;
}
