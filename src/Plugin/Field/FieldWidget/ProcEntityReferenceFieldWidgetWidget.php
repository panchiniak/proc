<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\proc\Entity\Element\ProcEntityAutocomplete;

/**
 * Defines the 'proc_proc_entity_reference_field_widget' field widget.
 *
 * @FieldWidget(
 *   id = "proc_proc_entity_reference_field_widget",
 *   label = @Translation("Proc Entity Reference Field Widget"),
 *   field_types = {"proc_proc_entity_reference_field"},
 * )
 */
class ProcEntityReferenceFieldWidgetWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    $referenced_entities = $items->referencedEntities();

    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + [
      'match_operator' => $this->getSetting('match_operator'),
      'match_limit' => $this->getSetting('match_limit'),
    ];
    
    
    $library = [
      'library' => [
        0 => 'proc/field'
      ],
    ];

    $element += [
      '#type' => 'entity_autocomplete',
      // '#type' => 'proc_entity_autocomplete',
      // '#type' => 'button',
      // '#value' => $this->t('Encrypt'),
      '#target_type' => $this->getFieldSetting('target_type'),
      '#selection_handler' => $this->getFieldSetting('handler'),
      '#selection_settings' => $selection_settings,
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => $referenced_entities[$delta] ?? NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      // '#description' => $this->t('Test bla'),
    ];



    if ($bundle = $this->getAutocreateBundle()) {
      $element['#autocreate'] = [
        'bundle' => $bundle,
        'uid' => ($entity instanceof EntityOwnerInterface) ? $entity->getOwnerId() : \Drupal::currentUser()->id(),
      ];
    }
    
    // $element
    $element['#attached']['library'][] = 'proc/proc-field';
    $element['#attached']['drupalSettings']['proc']['proc_labels'] = ['test1', 'test2'];
    $element['#attached']['drupalSettings']['proc']['proc_data'] = ['test1', 'test2'];
    $element['#description'] = $element['#description'] . "<p><a class='use-ajax' data-dialog-type='modal' href='./../../proc/add/1'><div class='button'>" . $this->t('Encrypt') . "</div></a></p>";
    
    
    // $element['test'] = [
    //   '#type' => 'button',
    //   '#value' => $this->t('Encrypt'),
    // ];

    return ['target_id' => $element];
  }



  // protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
  //   return 'test';
  // }
  
  // public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
  //   $entity = $items->getEntity();
  //   $referenced_entities = $items->referencedEntities();

  //   // Append the match operation to the selection settings.
  //   $selection_settings = $this->getFieldSetting('handler_settings') + [
  //     'match_operator' => $this->getSetting('match_operator'),
  //     'match_limit' => $this->getSetting('match_limit'),
  //   ];
    

  //   $element += [
  //     '#type' => 'proc_element',
  //     '#title' => t('My Custom Field'),
  //     // '#target_type' => $this->getFieldSetting('target_type'),
  //     // '#selection_handler' => $this->getFieldSetting('handler'),
  //     // '#selection_settings' => $selection_settings,
  //     // // Entity reference field items are handling validation themselves via
  //     // // the 'ValidReference' constraint.
  //     // '#validate_reference' => FALSE,
  //     // '#maxlength' => 1024,
  //     // '#default_value' => $referenced_entities[$delta] ?? NULL,
  //     // '#size' => $this->getSetting('size'),
  //     // '#placeholder' => $this->getSetting('placeholder'),
  //   ];


    

  //   // $element += [
  //   //   '#type' => 'entity_autocomplete',
  //   //   '#target_type' => $this->getFieldSetting('target_type'),
  //   //   '#selection_handler' => $this->getFieldSetting('handler'),
  //   //   '#selection_settings' => $selection_settings,
  //   //   // Entity reference field items are handling validation themselves via
  //   //   // the 'ValidReference' constraint.
  //   //   '#validate_reference' => FALSE,
  //   //   '#maxlength' => 1024,
  //   //   '#default_value' => $referenced_entities[$delta] ?? NULL,
  //   //   '#size' => $this->getSetting('size'),
  //   //   '#placeholder' => $this->getSetting('placeholder'),
  //   // ];

  //   // $element += [
  //   //   '#type' => 'button',
  //   //   '#value' => $this->t('Encrypt'),
  //   //   // '#target_type' => $this->getFieldSetting('target_type'),
  //   //   // '#selection_handler' => $this->getFieldSetting('handler'),
  //   //   // '#selection_settings' => $selection_settings,
  //   //   // // Entity reference field items are handling validation themselves via
  //   //   // // the 'ValidReference' constraint.
  //   //   // '#validate_reference' => FALSE,
  //   //   // '#maxlength' => 1024,
  //   //   // '#default_value' => $referenced_entities[$delta] ?? NULL,
  //   //   // '#size' => $this->getSetting('size'),
  //   //   // '#placeholder' => $this->getSetting('placeholder'),
  //   // ];



  //   // if ($bundle = $this->getAutocreateBundle()) {
  //   //   $element['#autocreate'] = [
  //   //     'bundle' => $bundle,
  //   //     'uid' => ($entity instanceof EntityOwnerInterface) ? $entity->getOwnerId() : \Drupal::currentUser()->id(),
  //   //   ];
  //   // }

  //   return ['target_id' => $element];

  // }

  /**
   * {@inheritdoc}
   */
  // public static function defaultSettings() {
  //   return [
  //     'foo' => 'bar',
  //   ] + parent::defaultSettings();
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function settingsForm(array $form, FormStateInterface $form_state) {

  //   $element['foo'] = [
  //     '#type' => 'textfield',
  //     '#title' => $this->t('Foo'),
  //     '#default_value' => $this->getSetting('foo'),
  //   ];

  //   return $element;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function settingsSummary() {
  //   $summary[] = $this->t('Foo: @foo', ['@foo' => $this->getSetting('foo')]);
  //   return $summary;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

  //   $element['value'] = $element + [
  //     '#type' => 'textfield',
  //     '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
  //   ];

  //   return $element;
  // }
  
  
  // /**
  // * {@inheritdoc}
  // */
  // public static function defaultSettings() {
  //   $settings = parent::defaultSettings();
  //   // Add any custom settings here.
  //   return $settings;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function settingsForm(array $form, FormStateInterface $form_state) {
  //   $element = parent::settingsForm($form, $form_state);
  //   // Add any custom widget settings form elements here.
  //   return $element;
  // }  

}
