<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;

/**
 * Defines the 'proc_proc_entity_reference_field_widget' field widget.
 *
 * @FieldWidget(
 *   id = "proc_proc_entity_reference_field_widget",
 *   label = @Translation("Proc Entity Reference Field Widget"),
 *   field_types = {"string"},
 * )
 */
class ProcEntityReferenceFieldWidgetWidget extends EntityReferenceAutocompleteWidget {

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
