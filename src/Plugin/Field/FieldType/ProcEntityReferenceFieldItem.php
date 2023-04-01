<?php

namespace Drupal\proc\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\proc\Plugin\Field\FieldWidget;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\Validation\Plugin\Validation\Constraint\AllowedValuesConstraint;

/**
 * Defines the 'proc_entity_reference_field' field type.
 * 
 * @FieldType(
 *   id = "proc_entity_reference_field",
 *   label = @Translation("Proc Entity Reference Field"),
 *   description = @Translation("An entity field containing an proc enabled entity reference."),
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
  public static function defaultFieldSettings() {
    $settings = ['target_type' => 'proc'];
    return $settings + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    
    // $form = parent::fieldSettingsForm($form, $form_state);
    
    
    $form['proc'] = [
      '#type' => 'details',
      '#title' => t('Protected Content Settings for this field'),
      '#open' => TRUE,
    ];
    
    $form['proc']['proc_field_recipients_fetcher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint for fetching user IDs of recipients'),
      '#description' => $this->t('Leave it empty for direct fetcher'),
      '#default_value' => $this->getSetting('proc_field_recipients_fetcher_endpoint'),
    ];

    $form['proc']['proc_field_recipients_manual_fetcher'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Define the user IDs of recipients in a CSV list'),
      '#default_value' => $this->getSetting('proc_field_recipients_manual_fetcher'),
    ];

    $form['proc']['proc_field_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of operation'),
      '#default_value' => $this->getSetting('proc_field_mode'),
      '#options' => [
        0 => $this->t('Disabled'),
        1 => $this->t('Only encryption'),
        2 => $this->t('Encryption and signature'),
      ],
    ];

    $form['proc']['proc_field_input_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Modes of input'),
      '#default_value' => $this->getSetting('proc_field_input_mode'),
      '#options' => [
        0 => $this->t('File'),
        1 => $this->t('Text area'),
        2 => $this->t('Text field'),
      ],
    ];

    $form += parent::fieldSettingsForm($form, $form_state);

    return $form;
  }

}
