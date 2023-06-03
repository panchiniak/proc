<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Component\Utility\Crypt;

/**
 * Decrypt content.
 */
class ProcDecryptForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_decrypt_form';
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
    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Protected Content Password'),
      '#description' => $this->t('You must type in the password used on registering your Protected Content Key.'),
    ];

    $decryption_link_classes = [
      'button--primary',
      'button',
    ];
    \Drupal::moduleHandler()
      ->alter('decryption_link_classes', $decryption_link_classes);

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
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Implements a form submit handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Deny access.
   */
  public function denyAccess() {
    throw new AccessDeniedHttpException();
  }

}
