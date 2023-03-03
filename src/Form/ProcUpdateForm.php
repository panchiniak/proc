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
class ProcUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_update_form';
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

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Protected Content Password'),
      '#description' => $this->t('You must type in the password used on registering your Protected Content Key.'),
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
    
    
  /**
   * Update proc cipher text entity submit callback.
   */
  // function _proc_update_submit($form, &$form_state) {
  //   $destination = drupal_get_destination();
  
  //   if (isset($destination['destination'])) {
  //     $form_state['redirect'] = $destination['destination'];
  //   }
  
  //   $recipients_uids = _proc_get_csv_argument($form_state['build_info']['args'][1]);
  //   asort($recipients_uids);
  //   $recipients_uids = array_values($recipients_uids);
  
  //   foreach ($form['#attached']['js'][0]['data']['proc']['proc_ciphers_index'] as $cid) {
  //     _proc_update_ciphertext($cid, $form, $form_state, $recipients_uids);
  //   }
  // }


  /**
   * Helper function for updating ciphertext.
   */
  // function _proc_update_ciphertext($cid, $form, $form_state, $recipients_uids_new) {
  //   $proc_wrapper = entity_metadata_wrapper('proc', $cid);
  //   $file_name = check_plain(unserialize($proc_wrapper->meta->value())['source_file_name']);
  //   $file_size = check_plain(unserialize($proc_wrapper->meta->value())['source_file_size']);
  //   $cid_cipher_field_id = 'cipher_text_cid_' . $cid;
  //   $generation_timespan_field_id = 'generation_timespan_cid_' . $cid;
  //   $browser_fingerprint_field_id = 'browser_fingerprint_cid_' . $cid;
  //   $generation_timestamp_field_id = 'generation_timestamp_cid_' . $cid;
  //   // Merge current and new data and metadata:
  //   $ciphertxt_data = [];
  //   $ciphertxt_data[$cid] = [
  //     // Reuse unchanged values on encryption update:
  //     'source_file_size'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_size']),
  //     'source_file_name'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_name']),
  //     'source_file_type'     => check_plain(unserialize($proc_wrapper->meta->value())['source_file_type']),
  //   ];
  //   // If a new cryptograpgic update happened:
  //   if ($form_state['values'][$generation_timespan_field_id]) {
  //     $ciphertxt_data[$cid] = [
  //       // Set new changed values on encryption update:
  //       'generation_timespan'  => check_plain($form_state['values'][$generation_timespan_field_id]),
  //       'generation_timestamp' => check_plain($form_state['values'][$generation_timestamp_field_id]),
  //       'browser_fingerprint'  => check_plain($form_state['values'][$browser_fingerprint_field_id]),
  //       'cipher_text'          => check_plain($form_state['values'][$cid_cipher_field_id]),
  //     ];
  //     $proc_wrapper->proc_recipient->set($recipients_uids_new);
  
  //   }
  //   // This is an non-cryptographic update:
  //   else {
  //     // We want only the ciphertext stored in database:
  //     if ($cipher_data = check_plain(unserialize($proc_wrapper->proc_armored->value())['cipher_text'])) {
  //       $ciphertxt_data[$cid] = ['cipher_text' => $cipher_data];
  //     }
  //     else {
  //       // We want only the ciphertext possibly stored elsewhere:
  //       $cipher_data = _proc_cipher_unserialize($proc_wrapper->proc_armored->value(), $cid, $form, $form_state);
  //       $ciphertxt_data[$cid] = $cipher_data;
  //     }
  //   }
  
  //   $changed = $proc_wrapper->changed->value();
  
  //   $proc_wrapper->proc_armored = _proc_cipher_serialize($ciphertxt_data[$cid], $cid, $changed, $form, $form_state);
  //   $proc_wrapper->changed = time();
  //   $proc_wrapper->save();
  // }




    
    
    
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
