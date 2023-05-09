<?php

namespace Drupal\proc\Plugin\Field\FieldType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Component\Utility\Bytes;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Environment;
use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'proc_entity_reference_field' field type.
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
    $form = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();

    //ksm($settings);


    $form['handler']['handler_settings']['proc'] = [
      '#type' => 'details',
      '#title' => t('Protected Content Settings for this field'),
      '#open' => TRUE,
    ];

    $form['handler']['handler_settings']['proc']['proc_field_recipients_fetcher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint for fetching user IDs of recipients'),
      '#description' => $this->t('Leave it empty for direct fetcher'),
      '#default_value' => $settings['handler_settings']['proc']['proc_field_recipients_fetcher_endpoint'] ?? '',
    ];

    $form['handler']['handler_settings']['proc']['proc_field_recipients_manual_fetcher'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Define the user IDs of recipients in a CSV list'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $form['handler']['handler_settings']['proc']['proc_field_recipients_to_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing the user IDs of recipients'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $form['handler']['handler_settings']['proc']['proc_field_recipients_cc_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Machine name of the field containing the user IDs of CC recipients'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
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
    ];

    return $form;
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
  public function submitSettingsForm(array &$form, FormStateInterface $form_state) {
    parent::submitSettingsForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->setSetting('proc_field_recipients_fetcher_endpoint', $values['proc_field_recipients_fetcher_endpoint']);
  }


}
