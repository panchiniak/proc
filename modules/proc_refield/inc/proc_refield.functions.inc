<?php

/**
 * @file
 * proc_refield.functions.inc
 */

/**
 * Helper function to create fields.
 *
 * @param array $fields
 *   Fields definitions.
 */
function _proc_refield_field_create_fields(array $fields) {
  foreach ($fields as $field) {
    if (!field_info_field($field['field_name'])) {
      field_create_field($field);
    }
  }
}

/**
 * Helper function to create field instances.
 *
 * @param array $instances
 *   Intances definitions.
 */
function _proc_refield_field_create_instances(array $instances) {
  foreach ($instances as $instance) {
    if (!field_read_instance('proc', $instance['field_name'], $instance['bundle'])) {
      field_attach_create_bundle('proc', $instance['bundle']);
      field_create_instance($instance);
    }
  }
}
