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
      // @todo: move this to static property of ProcKeys class 
      'public_key',
      'encrypted_private_key',
      // @todo: move this to static property of Proc class 
      'generation_timestamp',
      'generation_timespan',
      'browser_fingerprint'
    ];

    foreach ($proc_hidden_fields_key_generation as $hidden_field) {
      $form[$hidden_field] = ['#type' => 'hidden'];
    }

    // Password Confirm.
    // $form['password_confirm'] = [
    //   '#type' => 'password_confirm',
    //   '#required' => TRUE,
    // ];

    // File.
    $form['file'] = [
			'#type' => 'file',
			// '#title' => 'File',
			'#description' => $this->t(''),
    ];
  

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.

    // $form['actions'] = [
    //   '#type' => 'actions',
    // ];


    // Button.
    // $form['button'] = [
    //   '#type' => 'button',
		// 	'#enabled' => true,
    //   '#value' => 'Encrypt',
    //   '#description' => $this->t('Button, #type = button'),
    // ];


    $form['actions']['encrypt'] = [
      '#type' => 'button',
      '#value' => $this->t('Encrypt'),
    ];

		

    // $form['actions']['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Submit'),
    // ];

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
    ksm($form_state->getValue('public_key'));
    ksm($form_state->getValue('encrypted_private_key'));
    // ksm($form_state->getValue('generation_timestamp'));
    ksm($form_state->getValue('generation_timespan'));
    // ksm($form_state->getValue('browser_fingerprint'));

    

  }

}
