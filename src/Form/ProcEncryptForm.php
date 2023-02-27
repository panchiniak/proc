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
    
    $entity_type_manager = \Drupal::service('entity_field.manager');
    $entity_type_manager->clearCachedFieldDefinitions();
    
    // Add users 1 and 2 to the entity reference field.
    $recipient_users_field[] = ['target_id' => 1];
    // $recipient_users_field[] = ['target_id' => 2];
    
    // $proc->set('proc_recipients', $recipient_users_field);
    
    $proc->set('armored', $cipher)
      ->set('meta', $meta)
      ->set('langcode', 'en')
      ->set('label', $meta['source_file_name'])
      ->set('type', 'cipher')
      // ->set('proc_recipients', $recipient_users_field)
      ->save();

    // Get the entity reference field value object.
    // $recipient_users_field = $proc->get('proc_recipients');
    // ksm($recipient_users_field);
    
    // Checks whether an entity has a certain field.
    ksm($proc);
    ksm($proc->getFields());
    ksm($proc->hasField('field_proc_recipients'));


      
    $proc_id = $proc->id();
    if (is_numeric($proc_id)) {
      $this->messenger()->addMessage(
        $this->t(
          'Encryption is completed. Decryption link:'
        )
      );
      $link_text = $base_url . '/proc/' . $proc_id;
      $url = Url::fromUri('internal:/proc/' . $proc_id);
      
      $link = Link::fromTextAndUrl($base_url . '/proc/' . $proc_id, $url)
        ->toString()
        ->getGeneratedLink();
      
      $this->messenger()->addMessage(
        Markup::create($link)
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
