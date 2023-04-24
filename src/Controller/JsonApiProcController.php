<?php

namespace Drupal\proc\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonApiProcController
 * @package Drupal\proc\Controller
 */
class JsonApiProcController {

  /**
   * @return JsonResponse
   */
  public function index() {
    return new JsonResponse([ 'pubkey' => $this->getData()]);
  }

  /**
   * @return JsonResponse
   */
  public function cipher() {
    return new JsonResponse([ 'pubkey' => $this->getCihper()]);
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
        ->accessCheck(TRUE)
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
  
  /**
   * @return array
   */
  public function getCihper() {

    $current_path = \Drupal::service('path.current')->getPath();
    $path_array = explode('/', $current_path);
    
    $cipher_ids_string = $path_array[4];
    $proc_ids = explode(',', $cipher_ids_string);
    
    $cipher_data = [];
    foreach ($proc_ids as $proc_index => $proc_id) {
      $proc = \Drupal\proc\Entity\Proc::load($proc_id);

      if (
        // If cipher_fid is key in armored field, proc is using stream 
        isset($proc->get('armored')->getValue()[0]['cipher_fid']) && 
        // If cipher_fid is not an array, there is one single file:
        !is_array($proc->get('armored')->getValue()[0]['cipher_fid'])
      ) {
        $storage = \Drupal::entityTypeManager()->getStorage('file');
        $file = $storage->load($proc->get('armored')->getValue()[0]['cipher_fid']);
        $armored = file_get_contents($file->getFileUri());
      }
      // If 'cipher' is key at armored field:
      if (isset($proc->get('armored')->getValue()[0]['cipher'])) {
        // Database storage:
        $armored = $proc->get('armored')->getValue()[0]['cipher'];
      }
      
      // If cipher_fid key is an array, there are multiple files for the 
      // storage of the cipher:
      if (is_array($proc->get('armored')->getValue()[0]['cipher_fid'])) {
        // Concatenate the pieces of the cipher in a single variable:
        $armored = '';
        foreach ($proc->get('armored')->getValue()[0]['cipher_fid'] as $fid) {
          // $armored = $armored . $fid;
          $storage = \Drupal::entityTypeManager()->getStorage('file');
          $file = $storage->load($fid);
          $armored = $armored . file_get_contents($file->getFileUri());
        }
      }

      $cipher_data[$proc_index]['armored']            = $armored;
      $cipher_data[$proc_index]['source_file_name']   = $proc->get('meta')->getValue()[0]['source_file_name'];
      $cipher_data[$proc_index]['source_file_size']   = $proc->get('meta')->getValue()[0]['source_file_size'];
      if (isset($proc->get('meta')->getValue()[0]['source_input_mode'])){
        $cipher_data[$proc_index]['source_input_mode'] = $proc->get('meta')->getValue()[0]['source_input_mode'];
      }
      $cipher_data[$proc_index]['cipher_cid']         = $proc_id;
      $cipher_data[$proc_index]['proc_owner_uid']     = $proc->get('user_id')->getValue()[0]['target_id'];
      $cipher_data[$proc_index]['proc_recipients']    = $proc->get('field_recipients_set')->getValue();
      $cipher_data[$proc_index]['changed']            = $proc->get('changed')->getValue()[0]['value'];
      // @todo: add signed field
    }
    return $cipher_data;
  }
}
