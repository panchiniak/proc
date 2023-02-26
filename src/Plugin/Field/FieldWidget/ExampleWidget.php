<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Defines the 'field_proc_example' field widget.
 *
 * @FieldWidget(
 *   id = "field_proc_example",
 *   label = @Translation("Example"),
 *   field_types = {"field_proc_example"},
 * )
 */
class ExampleWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value 1'),
      '#default_value' => isset($items[$delta]->value_1) ? $items[$delta]->value_1 : NULL,
      '#size' => 20,
    ];

    $element['value_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value 2'),
      '#default_value' => isset($items[$delta]->value_2) ? $items[$delta]->value_2 : NULL,
      '#size' => 20,
    ];

    $element['value_3'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value 3'),
      '#default_value' => isset($items[$delta]->value_3) ? $items[$delta]->value_3 : NULL,
      '#size' => 20,
    ];

    $element['#theme_wrappers'] = ['container', 'form_element'];
    $element['#attributes']['class'][] = 'container-inline';
    $element['#attributes']['class'][] = 'field-proc-example-elements';
    $element['#attached']['library'][] = 'proc/field_proc_example';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return isset($violation->arrayPropertyPath[0]) ? $element[$violation->arrayPropertyPath[0]] : $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if ($value['value_1'] === '') {
        $values[$delta]['value_1'] = NULL;
      }
      if ($value['value_2'] === '') {
        $values[$delta]['value_2'] = NULL;
      }
      if ($value['value_3'] === '') {
        $values[$delta]['value_3'] = NULL;
      }
    }
    return $values;
  }

}
