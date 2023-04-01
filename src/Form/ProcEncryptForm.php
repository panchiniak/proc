<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proc;
use \Drupal\Core\Link;
use \Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\File\FileSystemInterface;
use \Drupal\Component\Utility\Crypt;

/**
 * Encrypt content.
 */
class ProcEncryptForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_encrypt_form';
  }

  /**
   * Build the form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $proc_hidden_fields_key_generation = [
			'cipher_text',
			'source_file_name',
			'source_file_size',
			'source_file_type',
			'source_file_last_change',
			'browser_fingerprint',
			'generation_timestamp',
			'generation_timespan',
			'signed',
    ];

    foreach ($proc_hidden_fields_key_generation as $hidden_field) {
      $form[$hidden_field] = ['#type' => 'hidden'];
    }

    $form['file'] = [
			'#type' => 'file',
			'#description' => $this->t('Select a file for encryption.'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo: Add validation for the syntax of armored cipher text.
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $base_url;
    
    $recipients_set_ids = $form_state->get('storage');

    $request = \Drupal::request();

    $destination = FALSE;
    
    $current_url = \Drupal::request()->headers->get('referer');
    $parse_result = \Drupal\Component\Utility\UrlHelper::parse($current_url);
    
    if (isset($parse_result['query']['destination'])) {
      $destination = $parse_result['query']['destination'];
    }

    $cipher = ['cipher' => $form_state->getValue('cipher_text')];
    $meta = [
      'source_file_name' => $form_state->getValue('source_file_name'),
      'source_file_size' => $form_state->getValue('source_file_size'),
      'source_file_type' => $form_state->getValue('source_file_type'),
      'source_file_last_change' => $form_state->getValue('source_file_last_change'),
      'browser_fingerprint' => $form_state->getValue('browser_fingerprint'),
      'generation_timestamp' => $form_state->getValue('generation_timestamp'),
      'generation_timespan' => $form_state->getValue('generation_timespan'),
      'signed' => $form_state->getValue('signed'),
    ];

    $proc = \Drupal\proc\Entity\Proc::create();
    
    $recipient_users = [];
    foreach ($recipients_set_ids as $recipient_id) {
      $recipient_users[] = ['target_id' => $recipient_id];
    }
    
    $json_content = $cipher['cipher'];
    $fragment = Crypt::hashBase64(Crypt::randomBytesBase64(32));
    $json_filename = $fragment . '.json';
    
    $config = \Drupal::config('proc.settings');
    $enable_stream_wrapper = $config->get('proc-enable-stream-wrapper');
    $stream_wrapper = $config->get('proc-stream-wrapper');
    $block_size = $config->get('proc-file-block-size');
    $blocks_split_enabled = $config->get('proc-enable-block-size');

    $file_id = FALSE;
    if ($enable_stream_wrapper === 1 && !empty($stream_wrapper)) {

      $json_dest = $stream_wrapper;
      if (!is_dir($json_dest)) {
        \Drupal::service('file_system')->mkdir($json_dest, NULL, TRUE);
      }
      
      if ($json_content) {

        if ($blocks_split_enabled && !empty($block_size)) {

          // $content_lines_number = substr_count($json_content, "\n");
          $lines = explode("\n", $json_content);
          $content_lines_number = count($lines);

          $lines_size_ratio = $content_lines_number / $block_size;
          $blocks = intval($lines_size_ratio);
          
          $remaining = $content_lines_number % $block_size;
          if ($remaining > 0) {
            $blocks++;
          }
          $blocks_index = 0;
          
          $blocks_lines = [];
          $content_line_index = 0;
          while ($blocks_index < $blocks) {
            $line_in_block_index = 0;
            while ($line_in_block_index < $block_size) {
              $blocks_lines[$blocks_index][] = $lines[$content_line_index];
              $content_line_index++;
              $line_in_block_index++;
            }
            $blocks_index++;
          }
          
          $blocks_texts = [];
          foreach ($blocks_lines as $block_index => $block_lines) {
            foreach ($block_lines as $block_line) {
              $blocks_texts[$block_index] = $blocks_texts[$block_index] . "\n" . $block_line;
            }
          }
    
          $json_fids = [];
          foreach ($blocks_texts as $block_text) {
            
            $fragment = Crypt::hashBase64(Crypt::randomBytesBase64(32));
            $json_filename = $fragment . '.json';
            
            $jsonFid = \Drupal::service('file.repository')
              ->writeData(
                $block_text,
                "$json_dest/$json_filename",
                FileSystemInterface::EXISTS_REPLACE
              );
            if ($jsonFid->id()) {
              $json_fids[] = $jsonFid->id();
            }
          }
        }
        else {
          $jsonFid = \Drupal::service('file.repository')
            ->writeData(
              $json_content,
              "$json_dest/$json_filename",
              FileSystemInterface::EXISTS_REPLACE
            );
  
          if ($jsonFid->id()) {
            $file_id = $jsonFid->id();
          }
        }
      }
    }
    
    if ($file_id) {
      $cipher = ['cipher_fid' => $file_id];
    }
    if (!empty($json_fids)) {
      $cipher = ['cipher_fid' => $json_fids];
    }

    $proc->set('armored', $cipher)
      ->set('meta', $meta)
      ->set('langcode', 'en')
      ->set('label', $meta['source_file_name'])
      ->set('type', 'cipher')
      ->set('field_recipients_set', $recipient_users)
      ->save();

    $proc_id = $proc->id();
    if (is_numeric($proc_id)) {
      $link_text = $base_url . '/proc/' . $proc_id;
      $url = Url::fromUri('internal:/proc/' . $proc_id);
      $link = Link::fromTextAndUrl($base_url . '/proc/' . $proc_id, $url)
        ->toString()
        ->getGeneratedLink();

      $this->messenger()->addMessage(
        $this->t(
          'Encryption is completed. Decryption link: %link', ['%link' => Markup::create($link)]
        )
      );
    }
    else {
      $this->messenger()->addMessage($this->t('Error'), TYPE_ERROR);
    }
    if ($destination) {
      $url = \Drupal\Core\Url::fromUri('internal:/' . $destination);
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url->toString());
      $response->send();
    }
  }
}
