<?php

/**
 * Class extension.
 */
class ProcRefieldProcBehaviorPlugin extends EntityReference_BehaviorHandler_Abstract {

  /**
   * Generate a settings form.
   */
  public function settingsForm($field, $instance) {
    $all_views = views_get_all_views();
    $all_user_views = array(
      '0' => ' - None - ',
    );
    foreach ($all_views as $view) {
      if ($view->base_table == 'users') {
        $all_user_views[$view->name] = $view->human_name . ' - ' . $view->name;
      }
    }

    $form['onclick'] = array(
      '#type' => 'fieldset',
      '#title' => t('Protected content API'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );
    $form['onclick']['enable'] = array(
      '#type' => 'checkboxes',
      '#options' => array('1' => t('Enabled')),
      '#default_value' => isset($instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable']) ? $instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable'] : 0,
    );
    $form['onclick']['fetcher'] = array(
      '#type' => 'select',
      '#title' => t('Recipients fetcher view'),
      '#options' => $all_user_views,
      '#default_value' => isset($instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['fetcher']) ? $instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['fetcher'] : '- None -',
      '#description' => t('Choose a user reference view as recipient fecther. User IDs returned will be used to select available recipients for encryption. Users listed must be holders of a protected content keyring.'),
    );

    return $form;
  }

}
