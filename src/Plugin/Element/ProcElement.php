<?php

namespace Drupal\proc\Plugin\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides a custom form element.
 *
 * @FormElement("proc_element")
 */
class ProcElement extends Element\FormElement {

  public function getInfo() {
    $time_format = '';
    if (!defined('MAINTENANCE_MODE')) {
      if ($time_format_entity = DateFormat::load('html_time')) {
        $time_format = $time_format_entity->getPattern();
      }
    }

    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#element_validate' => [
        [$class, 'validateTime'],
      ],
      '#process' => [
        [$class, 'processTime'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderTime'],
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'input__textfield',
      '#theme_wrappers' => ['form_element'],
      '#time_format' => $time_format,
      '#time_callbacks' => [],
      '#step' => 60 * 15,
    ];
  }


  public static function processTime(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['time'] = [
      '#name' => $element['#name'],
      '#title' => t('Time'),
      '#title_display' => 'invisible',
      '#default_value' => $element['#default_value'],
      '#attributes' => $element['#attributes'],
      '#required' => $element['#required'],
      '#size' => 12,
      '#error_no_message' => TRUE,
    ];

    return $element;
  }

  public static function preRenderTime($element) {
    $element['#attributes']['type'] = 'time';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'step']);
    // Sets the necessary attributes, such as the error class for validation.
    // Without this line the field will not be hightlighted, if an error occurred
    static::setAttributes($element, ['form-text']);
    return $element;
  }


  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE) {
      $format = isset($element['#time_format']) && $element['#time_format'] ? $element['#time_format'] : 'html_time';
      $time_format =  DateFormat::load($format)->getPattern();

      try {
        DrupalDateTime::createFromFormat($time_format, $input, NULL);
      }
      catch (\Exception $e) {
        $input = NULL;
      }
    }
    else {
      $input = $element['#default_value'];
    }
    return $input;
  }


  public static function validateTime(&$element, FormStateInterface $form_state, &$complete_form) {
    $format = isset($element['#time_format']) && $element['#time_format'] ? $element['#time_format'] : 'html_time';
    $time_format =  DateFormat::load($format)->getPattern();
    $title = !empty($element['#title']) ? $element['#title'] : '';
    $input_exists = FALSE;
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents'], $input_exists);

    if ($input_exists) {
      if (empty($input) && !$element['#required']) {
        $form_state->setValueForElement($element, NULL);
      }

      elseif (empty($input) && $element['#required']) {
        $form_state->setError($element, t('The %field is required. Please enter time in the format %format.', ['%field' => $title, '%format' => $time_format]));
      }
      else {
        try {
          DrupalDateTime::createFromFormat($time_format, $input, NULL);
          $form_state->setValueForElement($element, $input);
        }
        catch (\Exception $e) {
          $form_state->setError($element, t('The %field is required. Please enter time in the format %format.', ['%field' => $title, '%format' => $time_format]));
        }
      }
    }
  }




}


// class ProcElement extends EntityAutocomplete {

//   public static function process(&$element, &$form_state, &$complete_form) {
//     // Render the element.
//     return [
//       '#type' => 'button',
//       '#value' => $this->t('Encrypt'),
//       // '#markup' => '<div>This is my custom form element!</div>',
//     ];
//   }

// }
