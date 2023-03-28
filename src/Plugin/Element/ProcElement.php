<?php

namespace Drupal\proc\Plugin\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Element;

/**
 * Provides a custom form element.
 *
 * @RenderElement("proc_element")
 */
class ProcElement extends FormElement {

  public static function process(&$element, &$form_state, &$complete_form) {
    // Render the element.
    return [
      '#markup' => '<div>This is my custom form element!</div>',
    ];
  }

}
