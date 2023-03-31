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
// use Drupal\Core\Field\FieldType\EntityReferenceItem;


/**
 * Defines the 'proc_proc_entity_reference_field' field type.
 * 
 * @FieldType(
 *   id = "proc_proc_entity_reference_field",
 *   label = @Translation("Proc Entity Reference Field"),
 *   description = @Translation("An entity field containing an proc enabled entity reference."),
 *   category = @Translation("Reference"),
 *   default_widget = "proc_proc_entity_reference_field_widget",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 * 
 * @DCG
 * If you are implementing a single value field type you may want to inherit
 * this class form some of the field type classes provided by Drupal core.
 * Check out /core/lib/Drupal/Core/Field/Plugin/Field/FieldType directory for a
 * list of available field type implementations.
 */
class ProcEntityReferenceFieldItem extends EntityReferenceItem {

// * @FieldType(
// *   id = "proc_proc_entity_reference_field",
// *   label = @Translation("Proc Entity Reference Field"),
// *   description = @Translation("An entity field containing an proc enabled entity reference."),
// *   category = @Translation("Reference"),
// *   default_widget = "proc_proc_entity_reference_field_widget",
// *   default_formatter = "entity_reference_label",
// *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
// * )




  /**
   * {@inheritdoc}
   */
  // public static function defaultFieldSettings() {
  //   $settings = parent::defaultFieldSettings();
  //   // Add any custom field settings here.
  //   return $settings;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function schema() {
  //   $schema = parent::schema();
  //   // Add any custom schema definitions here.
  //   return $schema;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
  //   $element = parent::fieldSettingsForm($form, $form_state);
  //   // Add any custom field settings form elements here.
  //   return $element;
  // }

  
  // Annotation suggested:
  // * @FieldWidget(
  // *   id = "my_entity_reference",
  // *   label = @Translation("My Entity Reference"),
  // *   description = @Translation("An entity reference field that refers to my custom entity."),
  // *   field_types = {
  // *     "entity_reference"
  // *   }
  // * )  

  // /**
  // * {@inheritdoc}
  // */
  // public static function defaultFieldSettings() {
  //   $settings = ['bar' => 'beer'];
  //   return $settings + parent::defaultFieldSettings();
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function fieldSettingsForm(array $form, FormStateInterface $form_state) {

  //   $element['bar'] = [
  //     '#type' => 'textfield',
  //     '#title' => $this->t('Bar'),
  //     '#default_value' => $this->getSetting('bar'),
  //   ];

  //   return $element;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function isEmpty() {
  //   $value = $this->get('value')->getValue();
  //   return $value === NULL || $value === '';
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

  //   // @DCG
  //   // See /core/lib/Drupal/Core/TypedData/Plugin/DataType directory for
  //   // available data types.
  //   $properties['value'] = DataDefinition::create('string')
  //     ->setLabel(t('Text value'))
  //     ->setRequired(TRUE);

  //   return $properties;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public function getConstraints() {
  //   $constraints = parent::getConstraints();

  //   $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();

  //   // @DCG Suppose our value must not be longer than 10 characters.
  //   $options['value']['Length']['max'] = 10;

  //   // @DCG
  //   // See /core/lib/Drupal/Core/Validation/Plugin/Validation/Constraint
  //   // directory for available constraints.
  //   $constraints[] = $constraint_manager->create('ComplexData', $options);
  //   return $constraints;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public static function schema(FieldStorageDefinitionInterface $field_definition) {

  //   $columns = [
  //     'value' => [
  //       'type' => 'varchar',
  //       'not null' => FALSE,
  //       'description' => 'Column description.',
  //       'length' => 255,
  //     ],
  //   ];

  //   $schema = [
  //     'columns' => $columns,
  //     // @DCG Add indexes here if necessary.
  //   ];

  //   return $schema;
  // }

  // /**
  // * {@inheritdoc}
  // */
  // public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
  //   $random = new Random();
  //   $values['value'] = $random->word(mt_rand(1, 50));
  //   return $values;
  // }
  
  // Implement any additional methods that your custom field type needs, such as viewElements(), 
  // formElement(), and propertyDefinitions().  

}
