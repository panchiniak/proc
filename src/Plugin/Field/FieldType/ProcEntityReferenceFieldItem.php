<?php

namespace Drupal\proc\Plugin\Field\FieldType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'proc_entity_reference_field' field type.
 *
 * @FieldType(
 *   id = "proc_entity_reference_field",
 *   label = @Translation("Proc Entity Reference Field"),
 *   description = @Translation("An entity field containing a proc enabled
 *   entity reference."), category = @Translation("Reference"), default_widget
 *   = "proc_entity_reference_widget", default_formatter =
 *   "entity_reference_label", list_class =
 *   "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class ProcEntityReferenceFieldItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
        // The default target type is 'proc' because the default widget is
        // 'proc_entity_reference_widget'.
      'target_type' => 'proc',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'proc_field_recipients_fetcher_endpoint' => '',
      'proc_field_recipients_manual_fetcher' => '',
      'proc_field_recipients_to_field' => '',
      'proc_field_recipients_cc_field' => '',
      'proc_field_mode' => 1,
      'proc_field_input_mode' => 0,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Define properties for the field type.
    $properties = parent::propertyDefinitions($field_definition);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();

    $form['handler']['handler_settings']['proc'] = [
      '#type' => 'details',
      '#title' => t('Protected Content Settings for this field'),
      '#open' => TRUE,
    ];

    $form['handler']['handler_settings']['proc']['direct_fetcher'] = [
      '#type' => 'details',
      '#title' => t('Direct fetcher settings'),
      '#open' => TRUE,
    ];

    $form['handler']['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_to_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing the user IDs of recipients'),
      '#description' => $this->t('It must be a user reference field visible in the same form as this field.'),
      '#default_value' => $settings['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_to_field'] ?? '',
    ];

    $form['handler']['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_cc_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing user IDs of CC recipients'),
      '#description' => $this->t('It must be a user reference field visible in the same form as this field.'),
      '#default_value' => $settings['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_cc_field'] ?? '',
    ];

    $form['handler']['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_manual_fetcher'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Define the user IDs of recipients in a CSV list'),
      '#description' => $this->t('Example: 1,2,3'),
      '#default_value' => $settings['handler_settings']['proc']['direct_fetcher']['proc_field_recipients_manual_fetcher'] ?? '',
      '#disabled' => TRUE,
    ];

    $form['handler']['handler_settings']['proc']['proc_field_recipients_fetcher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint for fetching user IDs of recipients'),
      '#description' => $this->t('Example: https://example.com/api/get/users/param-a/param-b/param-c?param-d=1&param-e=2'),
      '#default_value' => $settings['handler_settings']['proc']['proc_field_recipients_fetcher_endpoint'] ?? '',
      '#disabled' => TRUE,
    ];

    $form['handler']['handler_settings']['proc']['proc_field_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of operation'),
      '#default_value' => $this->getSetting('proc_field_mode'),
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Only encryption'),
        2 => $this->t('Encryption and signature'),
      ],
      2 => [
        '#disabled' => TRUE,
      ],
    ];

    $form['handler']['handler_settings']['proc']['proc_field_input_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of input'),
      '#default_value' => $this->getSetting('proc_field_input_mode'),
      '#options' => [
        0 => $this->t('File'),
        1 => $this->t('Text area'),
        2 => $this->t('Text field'),
      ],
      1 => [
        '#disabled' => TRUE,
      ],
      2 => [
        '#disabled' => TRUE,
      ],
    ];

    return $form;
  }

}
