<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proc;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Routing;
use Drupal\Component\Utility\UrlHelper;

/**
 * Generate PGP asymmetric keys.
 */
class ProcKeysGenerationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_keys_generation_form';
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
      // @todo: move this to static property of ProcKeys class 
      'public_key',
      'encrypted_private_key',
      // @todo: move this to static property of Proc class 
      'generation_timestamp',
      'generation_timespan',
      'browser_fingerprint',
      'proc_email',
    ];

    foreach ($proc_hidden_fields_key_generation as $hidden_field) {
      $form[$hidden_field] = ['#type' => 'hidden'];
    }

    // Password Confirm.
    $form['password_confirm'] = [
      '#type' => 'password_confirm',
      '#required' => TRUE,
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
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
    // @todo: add validation to be sure the cipher text is not empty
    // Check if the generated keys look like as PGP keys
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

    $destination = FALSE;
    $key_created = FALSE;
    $success_message = 'Your key is saved.';

    $current_url = \Drupal::request()->headers->get('referer');
    $parse_result = \Drupal\Component\Utility\UrlHelper::parse($current_url);
    if (isset($parse_result)) {
      $destination = $parse_result['query']['destination'];
    }
    
    if (!empty($form_state->getValue('encrypted_private_key'))) {
      $keyring = [
        'privkey' => $form_state->getValue('encrypted_private_key'),
        'pubkey'  => $form_state->getValue('public_key'),
      ];
      $meta = [
        'generation_timestamp' => $form_state->getValue('generation_timestamp'),
        'generation_timespan' => $form_state->getValue('generation_timespan'),
        'browser_fingerprint' => $form_state->getValue('browser_fingerprint'),
        'proc_email'          => $form_state->getValue('proc_email'),
      ];
  
      $proc = \Drupal\proc\Entity\Proc::create();
      $proc->set('armored', $keyring)
        ->set('meta', $meta)
        ->set('label', $meta['proc_email'])
        ->save();
  
      $key_created = TRUE;
      if (!$destination) {
        $this->messenger()->addMessage($this->t($success_message));
      }
    }
    else {
      $this->messenger()->addMessage($this->t('Unknown error on the generation of the key. Please try again.'), 'error');
    }

    if ($destination && $key_created) {
      $url = \Drupal\Core\Url::fromUri('internal:/' . $destination);     
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url->toString());
      $response->send();
      \Drupal::messenger()->addStatus($this->t($success_message));
    }
  }
}
