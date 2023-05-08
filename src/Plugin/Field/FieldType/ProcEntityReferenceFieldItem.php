<?php

namespace Drupal\proc\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Defines the 'proc_entity_reference_field' field type.
 *
 * @FieldType(
 *   id = "proc_entity_reference_field",
 *   label = @Translation("Proc Entity Reference Field"),
 *   description = @Translation("An entity field containing a proc enabled entity reference."),
 *   category = @Translation("Reference"),
 *   default_widget = "proc_entity_reference_widget",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class ProcEntityReferenceFieldItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
//  public static function schema(\Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition) {
//    $schema = parent::schema($field_definition);
//
//    // Add the 'proc' column to the schema.
//    $schema['columns']['proc'] = [
//      'type' => 'blob',
//      'size' => 'big',
//      'serialize' => TRUE,
//      'not null' => FALSE,
//    ];
//
//    return $schema;
//  }



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
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();


    $element['proc'] = [
      '#type' => 'details',
      '#title' => t('Protected Content Settings for this field'),
      '#open' => TRUE,
    ];

    $element['proc']['proc_field_recipients_fetcher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint for fetching user IDs of recipients'),
      '#description' => $this->t('Leave it empty for direct fetcher'),
      '#default_value' => $settings['proc_field_recipients_fetcher_endpoint'],
    ];

    $element['proc']['proc_field_recipients_manual_fetcher'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Define the user IDs of recipients in a CSV list'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $element['proc']['proc_field_recipients_to_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing the user IDs of recipients'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $element['proc']['proc_field_recipients_cc_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing the user IDs of CC recipients'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $element['proc']['proc_field_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of operation'),
      '#default_value' => $this->getSetting('proc_field_mode'),
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Only encryption'),
        2 => $this->t('Encryption and signature'),
      ],
    ];

    $element['proc']['proc_field_input_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of input'),
      '#default_value' => $this->getSetting('proc_field_input_mode'),
      '#options' => [
        0 => $this->t('File'),
        1 => $this->t('Text area'),
        2 => $this->t('Text field'),
      ],
    ];

    return $element;
  }

}
