<?php

namespace Drupal\proc\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'field_proc_example_default' formatter.
 *
 * @FieldFormatter(
 *   id = "field_proc_example_default",
 *   label = @Translation("Default"),
 *   field_types = {"field_proc_example"}
 * )
 */
class ExampleDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {

      if ($item->value_1) {
        $element[$delta]['value_1'] = [
          '#type' => 'item',
          '#title' => $this->t('Value 1'),
          '#markup' => $item->value_1,
        ];
      }

      if ($item->value_2) {
        $element[$delta]['value_2'] = [
          '#type' => 'item',
          '#title' => $this->t('Value 2'),
          '#markup' => $item->value_2,
        ];
      }

      if ($item->value_3) {
        $element[$delta]['value_3'] = [
          '#type' => 'item',
          '#title' => $this->t('Value 3'),
          '#markup' => $item->value_3,
        ];
      }

    }

    return $element;
  }

}
