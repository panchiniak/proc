<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Defines the 'proc_entity_reference_widget' field widget.
 *
 * @FieldWidget(
 *   id = "proc_entity_reference_widget",
 *   label = @Translation("Proc Entity Reference Field Widget"),
 *   field_types = {"proc_entity_reference_field"},
 * )
 */
class ProcEntityReferenceWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    // @todo load the direct fetcher by field field name
    // from proc entity reference field settings.
    $proc_field_name = $items->getName();

    // Will be used for default values.
    $referenced_entities = $items->referencedEntities();
    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + [
      'match_operator' => $this->getSetting('match_operator'),
      'match_limit' => $this->getSetting('match_limit'),
    ];
    // Add extra javascript library.
    $library = [
      'library' => [
        0 => 'proc/field',
      ],
    ];

    $element += [
      '#type' => 'entity_autocomplete',
      '#target_type' => $this->getFieldSetting('target_type'),
      '#selection_handler' => $this->getFieldSetting('handler'),
      '#selection_settings' => $selection_settings,
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => $referenced_entities[$delta] ?? NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
    ];

    if ($bundle = $this->getAutocreateBundle()) {
      $element['#autocreate'] = [
        'bundle' => $bundle,
        'uid' => ($entity instanceof EntityOwnerInterface) ? $entity->getOwnerId() : \Drupal::currentUser()
          ->id(),
      ];
    }

    // Recipients field name:
    if (isset($selection_settings['proc']['direct_fetcher']['proc_field_recipients_to_field'])) {
      $to_recipients_field_name = $selection_settings['proc']['direct_fetcher']['proc_field_recipients_to_field'];
    }
    if (isset($selection_settings['proc']['direct_fetcher']['proc_field_recipients_cc_field'])) {
      $carbon_copy_recipients_field_name = $selection_settings['proc']['direct_fetcher']['proc_field_recipients_cc_field'];
    }

    $direct_fetcher_js_prefix = '
      let to_recipients_collection = [];
      let cc_recipients_collection = [];
      let recipients = [];
    ';

    $direct_fetcher_to = '';
    if (!empty($to_recipients_field_name)) {
      $direct_fetcher_to = '
        to_recipients_collection = Object.values(document.querySelectorAll("input[name^=\'' . $to_recipients_field_name . '\']"));
      ';
    }
    $direct_fetcher_cc = '';
    if (!empty($carbon_copy_recipients_field_name)) {
      $direct_fetcher_cc = '
        cc_recipients_collection = Object.values(document.querySelectorAll("input[name^=\'' . $carbon_copy_recipients_field_name . '\']"));
      ';
    }

    $direct_fetcher_js = '
      let recipients_length = to_recipients_collection.length + cc_recipients_collection.length;
      if (recipients_length > 0) {
        let recipients_collection = to_recipients_collection.concat(cc_recipients_collection);
        recipients_collection.forEach(function (recipient, index) {
          if (index < recipients_length) {
            if (recipient.value.match(/\(\d+\)/)) {
              let id_parenthesis = recipient.value.match(/\(\d+\)/)[0];
              recipients.push(id_parenthesis.substring(1, id_parenthesis.length - 1));
            }
          }
        });
      }

      let proc_path_prefix_add = window.location.origin + drupalSettings.path.baseUrl + "proc/add";
      let ids_csv = recipients.join();
      let parent_selector = jQuery(this).parent();
      let parent_id = parent_selector[0].getAttribute("id").slice(0, -13);

      let proc_path_suffix = "?proc_standalone_mode=FALSE&proc_parent_id=" + parent_id + "&proc_field_name=' . $proc_field_name . '";
      let proc_path = proc_path_prefix_add + "/" + ids_csv + proc_path_suffix;
      jQuery(this).attr("href", "#");

      let ajaxSettings = {
        url: proc_path,
        dialogType: "dialog",
        dialog: { width: 400 },
      };
      let myAjaxObject = Drupal.ajax(ajaxSettings);
      myAjaxObject.execute();
      return false;
    ';

    $direct_fetcher_js = $direct_fetcher_js_prefix . $direct_fetcher_to . $direct_fetcher_cc . $direct_fetcher_js;

    $url = Url::fromUserInput(
    // URL with recipients IDs will be defined
    // by the direct fetcher.
      '#',
      [
        'attributes' => [
          'onclick' => $direct_fetcher_js,
        ],
      ]
    );

    $link = [
      '#title' => $this->t('Encrypt'),
      '#type' => 'link',
      '#url' => $url,
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    $element['#attached']['library'][] = 'proc/proc-field';
    $decryption_link = [];

    // If there is a default value, add also the Decrypt button:
    if ($element['#default_value']) {
      $proc_id = $element['#default_value']->get('id')->getValue()[0]['value'];

      $decryption_url = Url::fromUserInput(
        '/proc/' . $proc_id,
        [
          'attributes' => [
            'class' => ['button'],
          ],
        ]
      );

      $decryption_link = Link::fromTextAndUrl(t('Decrypt'), $decryption_url);
      $decryption_link = $decryption_link->toRenderable();
    }
    $element['#description'] = [$link, $decryption_link];
    return ['target_id' => $element];
  }

}
