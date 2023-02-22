<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
    /*
     * This would normally be replaced by code that actually does something
     * with the title.
     */
    // $title = $form_state->getValue('title');
    // $this->messenger()->addMessage($this->t('You specified a title of %title.', ['%title' => $title]));
    $this->messenger()->addMessage($this->t('Done'));
    ksm($form_state->getValue('cipher_text'));
    ksm($form_state->getValue('source_file_name'));
    // ksm($form_state->getValue('generation_timestamp'));
    ksm($form_state->getValue('generation_timespan'));
    // ksm($form_state->getValue('browser_fingerprint'));


    

  }

}
