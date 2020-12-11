<?php

/**
 * @file
 */

/**
 * Implements hook_views_default_views().
 */
function proc_refield_views_default_views() {
  // This view lists cipher texts sent to and by the current user and is used to
  // populate the available items in the entity reference fields.
  $view = new view();
  $view->name = 'my_proc_ciphers_ls';
  $view->description = 'My proc ciphers, lato sensu - sent to me and by me';
  $view->tag = 'default';
  $view->base_table = 'proc';
  $view->human_name = 'my proc ciphers';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */
  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'My protected content';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'none';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'pid' => 'pid',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'pid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Relationship: Protected Content: Proc Armored author */
  $handler->display->display_options['relationships']['uid']['id'] = 'uid';
  $handler->display->display_options['relationships']['uid']['table'] = 'proc';
  $handler->display->display_options['relationships']['uid']['field'] = 'uid';
  /* Field: Protected Content: Proc Armored entity ID */
  $handler->display->display_options['fields']['pid']['id'] = 'pid';
  $handler->display->display_options['fields']['pid']['table'] = 'proc';
  $handler->display->display_options['fields']['pid']['field'] = 'pid';
  $handler->display->display_options['fields']['pid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['pid']['separator'] = '';
  /* Field: Protected Content: Ciphertext recipient user reference */
  $handler->display->display_options['fields']['proc_recipient']['id'] = 'proc_recipient';
  $handler->display->display_options['fields']['proc_recipient']['table'] = 'field_data_proc_recipient';
  $handler->display->display_options['fields']['proc_recipient']['field'] = 'proc_recipient';
  $handler->display->display_options['fields']['proc_recipient']['label'] = '';
  $handler->display->display_options['fields']['proc_recipient']['exclude'] = TRUE;
  $handler->display->display_options['fields']['proc_recipient']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['proc_recipient']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['proc_recipient']['hide_alter_empty'] = FALSE;
  $handler->display->display_options['fields']['proc_recipient']['click_sort_column'] = 'target_id';
  $handler->display->display_options['fields']['proc_recipient']['settings'] = array(
    'bypass_access' => 0,
    'link' => 0,
  );
  $handler->display->display_options['fields']['proc_recipient']['delta_offset'] = '0';
  $handler->display->display_options['fields']['proc_recipient']['separator'] = ' - ';
  /* Field: Protected Content: Protected content label */
  $handler->display->display_options['fields']['label']['id'] = 'label';
  $handler->display->display_options['fields']['label']['table'] = 'proc';
  $handler->display->display_options['fields']['label']['field'] = 'label';
  $handler->display->display_options['fields']['label']['label'] = '';
  $handler->display->display_options['fields']['label']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['label']['alter']['text'] = '([pid]) [label] <b>Encrypted for:</b> [proc_recipient]';
  $handler->display->display_options['fields']['label']['alter']['max_length'] = '1000';
  $handler->display->display_options['fields']['label']['alter']['trim'] = TRUE;
  $handler->display->display_options['fields']['label']['element_label_colon'] = FALSE;
  /* Sort criterion: Protected Content: Proc Armored entity ID */
  $handler->display->display_options['sorts']['pid']['id'] = 'pid';
  $handler->display->display_options['sorts']['pid']['table'] = 'proc';
  $handler->display->display_options['sorts']['pid']['field'] = 'pid';
  $handler->display->display_options['sorts']['pid']['order'] = 'DESC';
  /* Filter criterion: Protected Content: Proc Armored entity type (bundle) */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'proc';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = 'proc_ciphertxt';
  /* Filter criterion: User: Current */
  $handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
  $handler->display->display_options['filters']['uid_current']['table'] = 'users';
  $handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
  $handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
  $handler->display->display_options['filters']['uid_current']['value'] = '1';
  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'my-proc-ciphers';
  /* Display: Entity Reference */
  $handler = $view->new_display('entityreference', 'Entity Reference', 'entityreference_1');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'entityreference_style';
  $handler->display->display_options['style_options']['search_fields'] = array(
    'pid' => 'pid',
    'label' => 'label',
    'proc_recipient' => 0,
  );
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['row_plugin'] = 'entityreference_fields';
  $handler->display->display_options['row_options']['inline'] = array(
    'pid' => 'pid',
    'title' => 'title',
  );
  $handler->display->display_options['row_options']['default_field_elements'] = FALSE;
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $views[$view->name] = $view;

  $view = new view();
  $view->name = 'proc_key_holders';
  $view->description = 'Retrieval of proc key holders.';
  $view->tag = 'default';
  $view->base_table = 'users';
  $view->human_name = 'Proc Key Holders';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */
  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['disable_sql_rewrite'] = TRUE;
  $handler->display->display_options['query']['options']['distinct'] = TRUE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  $handler->display->display_options['row_options']['default_field_elements'] = FALSE;
  /* Relationship: User: Protected Content */
  $handler->display->display_options['relationships']['proc']['id'] = 'proc';
  $handler->display->display_options['relationships']['proc']['table'] = 'users';
  $handler->display->display_options['relationships']['proc']['field'] = 'proc';
  $handler->display->display_options['relationships']['proc']['required'] = TRUE;
  /* Relationship: Entity Reference: Referenced Entity */
  $handler->display->display_options['relationships']['proc_armoredown_target_id']['id'] = 'proc_armoredown_target_id';
  $handler->display->display_options['relationships']['proc_armoredown_target_id']['table'] = 'field_data_proc_armoredown';
  $handler->display->display_options['relationships']['proc_armoredown_target_id']['field'] = 'proc_armoredown_target_id';
  $handler->display->display_options['relationships']['proc_armoredown_target_id']['relationship'] = 'proc';
  $handler->display->display_options['relationships']['proc_armoredown_target_id']['required'] = TRUE;
  /* Field: User: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'users';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['label'] = '';
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = FALSE;
  /* Contextual filter: Global: Null */
  $handler->display->display_options['arguments']['null']['id'] = 'null';
  $handler->display->display_options['arguments']['null']['table'] = 'views';
  $handler->display->display_options['arguments']['null']['field'] = 'null';
  $handler->display->display_options['arguments']['null']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['null']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['null']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['null']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['filter_groups']['groups'] = array(
    1 => 'AND',
    2 => 'OR',
  );
  /* Filter criterion: User: Active */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'users';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = '1';
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;

  return $views;
}
