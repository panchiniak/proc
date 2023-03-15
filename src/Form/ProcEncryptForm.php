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
 * Generate PGP asymmetric keys.
 */
class ProcEncryptForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_encrypt_form';
  }

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
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
    // Check if the generated keys look like as PGP keys
    // ksm($form_state);
    // $title = $form_state->getValue('title');
    // if (strlen($title) < 5) {
    //   // Set an error for the form element with a key of "title".
    //   $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    // }
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
    
    $json_dest = 'public://proc';
    if (!is_dir($json_dest)) {
      \Drupal::service('file_system')->mkdir($json_dest, NULL, TRUE);
    }
    
    if ($json_content) {
      // D9.x
      $jsonFid = \Drupal::service('file.repository')
        ->writeData(
          $json_content,
          "$json_dest/$json_filename",
          FileSystemInterface::EXISTS_REPLACE
        );
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
