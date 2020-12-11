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

/**
 * Helper function to retrieve entity reference widget identifiers.
 *
 * @return array
 *   Array containing entity reference widget identifiers.
 */
function _proc_refield_get_proc_refield_widget() {
  $proc_refield_widget[] = 'entityreference_autocomplete';
  drupal_alter('proc_refield_get_widget', $proc_refield_widget);
  return $proc_refield_widget;
}

/**
 * Custom menu callback for retrieval of encryption recipients.
 *
 * @param string $view
 *   View for retrieval of users.
 *
 * @param string $filter
 *   Filter parameters.
 */
function _proc_refield_recipients_menu_callback($view) {

  $filter_arguments = drupal_get_query_parameters();
  if (is_array($filter_arguments)) {
    foreach ($filter_arguments as $filter_arguments) {
      $filter_arguments_set[] = $filter_arguments;
    }
  }

  $view_sanitized = check_plain($view);
  $recipients_view = views_get_view($view_sanitized, TRUE);

  if ($recipients_view) {
    $recipients_view->set_arguments($filter_arguments_set);
    $recipients_view->execute();
    $selected_user_ids = array();
    foreach ($recipients_view->result as $row_key => $row) {
      $selected_user_ids[] = $row->uid;
    }
    $uids = array_unique($selected_user_ids);
    asort($uids);

    // @todo create a hook to allow definition of extra filters
    $uids_csv = implode(',', $uids);
    drupal_json_output($uids_csv);
  }
  else {
    drupal_json_output('Error: view not found: ' . $view_sanitized);
  }
}
