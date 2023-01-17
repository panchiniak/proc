<?php

namespace Drupal\proc\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Protected Content routes.
 */
class ProcController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
