<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proc;
use \Drupal\Core\Link;
use \Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use \Drupal\Component\Utility\Crypt;


/**
 * Generate PGP asymmetric keys.
 */
class ProcDecryptForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_decrypt_form';
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

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Protected Content Password'),
      '#description' => $this->t('You must type in the password used on registering your Protected Content Key.'),
    ];

    // $form['actions']['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Decrypt'),
    // ];
    $decryption_link_classes = [
      'button--primary',
      'button',
    ];
    // \Drupal::moduleHandler()->alter('decryption_link_classes', [&$decryption_link_classes]);
    \Drupal::moduleHandler()->alter('decryption_link_classes', $decryption_link_classes);
    // \Drupal::moduleHandler()->alter('test', $decryption_link_classes);
    
    $fragment = Crypt::hashBase64(Crypt::randomBytesBase64(32));
    $form['decrypt'] = [
      '#type' => 'link',
      '#title' => $this->t('Decrypt'),
      '#url' => Url::fromUserInput('#' . $fragment),
      '#attributes' => [
        'id' => 'decryption-link',
        // 'class' => ['ecl-button', 'ecl-button--primary'],
        'class' => ['button--primary', 'button'],
      ],
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
    // Decrypt does not submit and therefore it does not valiate.
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
    // Decrypt does not submit!
    // @todo: add an ajax submission for registering a history of decryption
    // per file and user.
  }
  public function denyAccess() {
      throw new AccessDeniedHttpException();
  }

}
