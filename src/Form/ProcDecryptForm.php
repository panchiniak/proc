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
    // global $base_url;
    // ksm($base_url);
    
    // $request = \Drupal::request();
    // ksm($request);

    // $destination = FALSE;
    
    // $current_url = \Drupal::request()->headers->get('referer');
    // $parse_result = \Drupal\Component\Utility\UrlHelper::parse($current_url);
    // if (isset($parse_result)) {
    //   $destination = $parse_result['query']['destination'];
    // }

    // $cipher = ['cipher' => $form_state->getValue('cipher_text')];
    // $meta = [
    //   'source_file_name' => $form_state->getValue('source_file_name'),
    //   'source_file_size' => $form_state->getValue('source_file_size'),
    //   'source_file_type' => $form_state->getValue('source_file_type'),
    //   'source_file_last_change' => $form_state->getValue('source_file_last_change'),
    //   'browser_fingerprint' => $form_state->getValue('browser_fingerprint'),
    //   'generation_timestamp' => $form_state->getValue('generation_timestamp'),
    //   'generation_timespan' => $form_state->getValue('generation_timespan'),
    //   'signed' => $form_state->getValue('signed'),
    // ];

    // $proc = \Drupal\proc\Entity\Proc::create();
    
    // // ksm($proc);
    
    // $proc->set('armored', $cipher)
    //   ->set('meta', $meta)
    //   ->set('label', $meta['source_file_name'])
    //   ->set('type', 'cipher')
    //   ->save();
    
    // $proc_id = $proc->id();
    
    

    // if (is_numeric($proc_id)) {
    //   // $this->messenger()->addMessage($this->t('Done'));
    //   $this->messenger()->addMessage(
    //     $this->t(
    //       'Encryption is done. Access link: %proc_access_link.', 
    //       ['%proc_access_link' => $base_url . '/proc/' . $proc_id]
    //     )
    //   );
      
  
    //   $link_text = $base_url . '/proc/' . $proc_id;
      
    //   $link = Link::fromTextAndUrl($base_url . '/proc/' . $proc_id, 'example.route')
    //     ->toString()
    //     ->getGeneratedLink();    
  
  
      
      
      
    // }
    // else {
    //   $this->messenger()->addMessage($this->t('Error'), TYPE_ERROR);
    // }
    // if ($destination) {
    //   $url = \Drupal\Core\Url::fromUri('internal:/' . $destination);
    //   $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url->toString());
    //   $response->send();
    // }
    
    
  }
  public function denyAccess() {
      throw new AccessDeniedHttpException();
  }

}
