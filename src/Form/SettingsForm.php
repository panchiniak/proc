<?php

namespace Drupal\proc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Protected Content settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proc_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['proc.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['proc-stream-wrapper'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Global stream wrapper'),
      '#description' => $this->t('Set a stream wrapper for the storage of cipher texts.'),
      '#default_value' => $this->config('proc.settings')->get('proc-stream-wrapper'),
    ];
    $form['proc-enable-stream-wrapper'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable stream wrapper globaly'),
      '#description' => $this->t('Enable stream wrapper storage of cipher texts.'),
      '#default_value' => $this->config('proc.settings')->get('proc-enable-stream-wrapper'),
    ];
    $form['proc-rsa-key-size'] = [
      '#type' => 'select',
      '#title' => $this->t('RSA keys size'),
      '#options' => [
        '2048' => $this->t('2048'),
        '4096' => $this->t('4096'),
      ],
      '#empty_option' => $this->t('-select-'),
      '#description' => $this->t('Set the RSA key size.'),
      '#default_value' => $this->config('proc.settings')->get('proc-rsa-key-size'),
    ];
    $form['proc-file-entity-max-filesize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum size for encryption in bytes'),
      '#description' => $this->t('Set the maximum size in bytes allowed for encryption. Default if left empty: 10000000 bytes (10 megabytes).'),
      '#default_value' => $this->config('proc.settings')->get('proc-file-entity-max-filesize'),
    ];
    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if ($form_state->getValue('example') != 'example') {
    //   $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    // }
    // parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('proc.settings')
      ->set('proc-stream-wrapper', $form_state->getValue('proc-stream-wrapper'))
      ->set('proc-enable-stream-wrapper', $form_state->getValue('proc-enable-stream-wrapper'))
      ->set('proc-rsa-key-size', $form_state->getValue('proc-rsa-key-size'))
      ->set('proc-file-entity-max-filesize', $form_state->getValue('proc-file-entity-max-filesize'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
