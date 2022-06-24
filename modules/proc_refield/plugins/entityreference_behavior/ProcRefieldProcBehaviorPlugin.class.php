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
    $all_user_views = [
      '0' => ' - None - ',
    ];
    foreach ($all_views as $view) {
      if ($view->base_table == 'users') {
        $all_user_views[$view->name] = $view->human_name . ' - ' . $view->name;
      }
    }

    $form['onclick'] = [
      '#type' => 'fieldset',
      '#title' => t('Protected content API'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $crypto_mode_default = 0;
    if (isset($instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable'])) {
      if (isset($instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable'][1])) {
        $crypto_mode_default = $instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable'][1];
      }
      else {
        $crypto_mode_default = $instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['enable'];
      }
    }

    $form['onclick']['enable'] = [
      '#title' => t('Encryption operation'),
      '#description' => t('Choose the encryption operation. Signing requires encryption keys from the author.'),
      '#type' => 'radios',
      '#options' => [
        '0' => t('Disabled'),
        '1' => t('Simple encryption'),
        '2' => t('Encryption and signature'),
      ],
      '#default_value' => $crypto_mode_default,
    ];
    $form['onclick']['fetcher'] = [
      '#type' => 'select',
      '#title' => t('Recipients fetcher view'),
      '#options' => $all_user_views,
      '#default_value' => isset($instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['fetcher']) ? $instance['settings']['behaviors']['proc_behavior_plugin']['onclick']['fetcher'] : '- None -',
      '#description' => t('Choose a user reference view as recipient fecther. User IDs returned will be used to select available recipients for encryption. Users listed must be holders of a protected content keyring.'),
    ];

    return $form;
  }

}
