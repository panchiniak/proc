<?php

namespace Drupal\proc\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonApiProcController
 * @package Drupal\mymodule\Controller
 */
class JsonApiProcController {

  /**
   * @return JsonResponse
   */
  public function index() {
    return new JsonResponse([ 'pubkey' => $this->getData()]);
  }

  /**
   * @return array
   */
  public function getData() {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_array = explode('/', $current_path);
    $user_ids_string = $path_array[4];
    $user_ids = explode(',', $user_ids_string);
    
    $search_by = $path_array[5];

    $proc_ids = [];
    foreach ($user_ids as $user_id) {
      $query = \Drupal::entityQuery('proc')
        ->condition($search_by, $user_id)
        ->condition('type', 'keyring')
        ->sort('id', 'DESC')
        ->range(0, 1);
        $proc_ids[] = key($query->execute());
    }

    $result = [];

    foreach ($proc_ids as $proc_id) {
      $proc = \Drupal\proc\Entity\Proc::load($proc_id);
      $result[] = [
        'key' => $proc->get('armored')->getValue()[0]['pubkey'],
        'changed' => $proc->get('created')->getValue()[0]['value']
      ];
    }

    return $result;
  }
}