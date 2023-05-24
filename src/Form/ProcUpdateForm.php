<?php

namespace Drupal\proc\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\proc;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\File\FileSystemInterface;

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
    $current_path = Drupal::service('path.current')->getPath();
    $path_array = explode('/', $current_path);

    // Sanitize and preprocess arguments:
    $csvs = _proc_get_csv_arguments([
      'cids_csv' => $path_array[3],
      'uids_csv' => $path_array[4],
    ]);

    // If there is at least one selected uid:
    if (is_numeric($csvs['uids_csv']['array'][0])) {
      $time_value = Drupal::time()->getCurrentTime();

      // Get the data of cihpers for updating their proc entities:
      $procs_data = [];
      $proc_ids = $csvs['cids_csv']['array'];
      foreach ($proc_ids as $proc_id) {
        $procs_data[] = [
          'cipher_text' => $form_state->getValue('cipher_text_cid_' . $proc_id, $default = NULL),
          'browser_fingerprint' => $form_state->getValue('browser_fingerprint_cid_' . $proc_id, $default = NULL),
          'generation_timestamp' => $form_state->getValue('generation_timestamp_cid_' . $proc_id, $default = NULL),
          'generation_timespan' => $form_state->getValue('generation_timespan_cid_' . $proc_id, $default = NULL),
          // 'signed'                  => $form_state->getValue('signed_cid_' . $proc_id, $default = null),
          'proc_id' => $proc_id,
        ];
      }
      $recipient_users = [];
      foreach ($csvs['uids_csv']['array'] as $recipient_id) {
        $recipient_users[] = ['target_id' => $recipient_id];
      }
      foreach ($procs_data as $proc_data) {
        $proc = $proc = proc\Entity\Proc::load($proc_data['proc_id']);
        $meta = $proc->get('meta')->getValue()[0];
        $meta['browser_fingerprint'] = $proc_data['browser_fingerprint'];
        $meta['generation_timestamp'] = $proc_data['generation_timestamp'];
        $meta['generation_timespan'] = $proc_data['generation_timespan'];

        $proc->set('meta', $meta);

        // If file storage is set:
        $config = Drupal::config('proc.settings');
        $enable_stream_wrapper = $config->get('proc-enable-stream-wrapper');
        $stream_wrapper = $config->get('proc-stream-wrapper');
        $block_size = $config->get('proc-file-block-size');
        $blocks_split_enabled = $config->get('proc-enable-block-size');

        $file_id = FALSE;
        if ($enable_stream_wrapper === 1 && !empty($stream_wrapper) && !($blocks_split_enabled)) {
          $json_dest = $stream_wrapper;
          if (!is_dir($json_dest)) {
            Drupal::service('file_system')->mkdir($json_dest, NULL, TRUE);
          }

          if ($proc_data['cipher_text']) {
            $jsonFid = Drupal::service('file.repository')
              ->writeData(
                $proc_data['cipher_text'],
                "$json_dest/$json_filename",
                FileSystemInterface::EXISTS_REPLACE
              );

            if ($jsonFid->id()) {
              $file_id = $jsonFid->id();
            }
          }
        }

        if ($enable_stream_wrapper === 1 && !empty($stream_wrapper) && !($blocks_split_enabled) && !empty($block_size)) {
          // @todo: add split storage mode for update.
          // @todo implement this:
          $lines = explode("\n", $json_content);
          $content_lines_number = count($lines);

          $lines_size_ratio = $content_lines_number / $block_size;
          $blocks = intval($lines_size_ratio);

          $remaining = $content_lines_number % $block_size;
          if ($remaining > 0) {
            $blocks++;
          }
          $blocks_index = 0;

          $blocks_lines = [];
          $content_line_index = 0;
          while ($blocks_index < $blocks) {
            $line_in_block_index = 0;
            while ($line_in_block_index < $block_size) {
              $blocks_lines[$blocks_index][] = $lines[$content_line_index];
              $content_line_index++;
              $line_in_block_index++;
            }
            $blocks_index++;
          }

          $blocks_texts = [];
          foreach ($blocks_lines as $block_index => $block_lines) {
            foreach ($block_lines as $block_line) {
              $blocks_texts[$block_index] = $blocks_texts[$block_index] . "\n" . $block_line;
            }
          }

          $json_fids = [];
          foreach ($blocks_texts as $block_text) {
            $fragment = Crypt::hashBase64(Crypt::randomBytesBase64(32));
            $json_filename = $fragment . '.json';

            $jsonFid = Drupal::service('file.repository')
              ->writeData(
                $block_text,
                "$json_dest/$json_filename",
                FileSystemInterface::EXISTS_REPLACE
              );
            if ($jsonFid->id()) {
              $json_fids[] = $jsonFid->id();
            }
          }
        }

        if ($file_id) {
          // $cipher = ['cipher_fid' => $file_id];
          $proc->set('armored', ['cipher_fid' => $file_id]);
        }

        if (!$file_id && !$json_fids) {
          // Database storage:
          $proc->set('armored', ['cipher' => $proc_data['cipher_text']]);
        }

        if (!empty($json_fids)) {
          // $cipher = ['cipher_fid' => $json_fids];
          $proc->set('armored', ['cipher_fid' => $json_fids]);
        }

        $proc->set('armored', ['cipher' => $proc_data['cipher_text']]);
        $proc->set('field_recipients_set', $recipient_users);
        $proc->set('changed', $time_value);
        $proc->save();
      }
      $this->messenger()->addMessage(
        $this->t(
          'Update is completed'
        )
      );
    }
  }

}
