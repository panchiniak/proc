<?php

namespace Drupal\proc\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'field_proc_example' field type.
 *
 * @FieldType(
 *   id = "field_proc_example",
 *   label = @Translation("Example"),
 *   category = @Translation("General"),
 *   default_widget = "field_proc_example",
 *   default_formatter = "field_proc_example_default"
 * )
 */
class ExampleItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->value_1 !== NULL) {
      return FALSE;
    }
    elseif ($this->value_2 !== NULL) {
      return FALSE;
    }
    elseif ($this->value_3 !== NULL) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['value_1'] = DataDefinition::create('string')
      ->setLabel(t('Value 1'));
    $properties['value_2'] = DataDefinition::create('string')
      ->setLabel(t('Value 2'));
    $properties['value_3'] = DataDefinition::create('string')
      ->setLabel(t('Value 3'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    // @todo Add more constraints here.
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $columns = [
      'value_1' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'value_2' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'value_3' => [
        'type' => 'varchar',
        'length' => 255,
      ],
    ];

    $schema = [
      'columns' => $columns,
      // @DCG Add indexes here if necessary.
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {

    $random = new Random();

    $values['value_1'] = $random->word(mt_rand(1, 255));

    $values['value_2'] = $random->word(mt_rand(1, 255));

    $values['value_3'] = $random->word(mt_rand(1, 255));

    return $values;
  }

}
