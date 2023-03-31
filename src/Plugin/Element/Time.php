<?php

namespace Drupal\settings\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides a time element.
 *
 * @FormElement("time")
 */
class Time extends Element\FormElement {

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
}