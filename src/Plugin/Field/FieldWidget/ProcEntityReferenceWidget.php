<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\proc\Entity\Element\ProcEntityAutocomplete;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Url;
use Drupal\Core\Link;

// use Drupal\proc\Plugin\Field\FieldWidget\Settings;

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
        0 => 'proc/field'
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
        'uid' => ($entity instanceof EntityOwnerInterface) ? $entity->getOwnerId() : \Drupal::currentUser()->id(),
      ];
    }

    // Recipients field name:
    $recipients_field_name = 'field_to_private_message';

    $direct_fetcher_js = '
      let recipients_colection = document.querySelectorAll("input[name^=\'' . $recipients_field_name . '\']");
      
      let recipients = [];
      let recipients_length = recipients_colection.length - 1;
      console.log(recipients_length);
      if (recipients_length > 0) {
        recipients_colection.forEach(function (recipient, index) {
          if (index < recipients_length) {
            if (recipient.value) {
              let id_parenthesis = recipient.value.match(/\(\d+\)/)[0];
              recipients.push(id_parenthesis.substring(1, id_parenthesis.length - 1));
            }
          }
        });
      }
      let proc_path_prefix_add = window.location.origin + "/proclab/web" + "/proc/add";
      let ids_csv = recipients.join();
      let proc_path_sufix_standalone_mode_query_string = "?proc_standalone_mode=FALSE";
      let proc_path = proc_path_prefix_add + "/" + ids_csv + proc_path_sufix_standalone_mode_query_string;
      jQuery(this).attr("href", "");
      //this.href = "";
      jQuery(this).attr("href", proc_path);
      // jQuery(this).attr("class", "use-ajax");
      // jQuery(this).attr("data-dialog-type", "dialog");


    ';

    $url = Url::fromUserInput(
      '#', 
      [
        // 'query' => ['proc_standalone_mode' => 'FALSE'],
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'id' => 'proc-dialog-encrypt',
          'onclick' => $direct_fetcher_js,
        ]
      ]
    );

    $link = [
      '#title' => $this->t('Encrypt'),
      '#type' => 'link',
      '#url' => $url,
      '#attributes' => [
        'class' => ['button', 'use-ajax'],
        'data-dialog-type' => 'dialog',
      ]
    ];

    $encryption_link = Link::fromTextAndUrl(t('Encrypt'), $url);
    $encryption_link = $encryption_link->toRenderable();
    // // If you need some attributes.
    // $encryption_link['#attributes'] = array('class' => array('button', 'button-action', 'button--primary', 'button--small'));
    // print render($project_link);


    $element['#attached']['library'][] = 'proc/proc-field';
    $element['#attached']['drupalSettings']['proc']['proc_labels'] = ['test1', 'test2'];
    $element['#attached']['drupalSettings']['proc']['proc_data'] = ['test1', 'test2'];

    // If there is a default value, add also the Decrypt button:
    if ($element['#default_value']) {
      $proc_id = $element['#default_value']->get('id')->getValue()[0]['value'];
      $element['#description'] = $element['#description'] . "<a class='use-ajax' data-dialog-type='modal' href='./../../proc/" . $proc_id . "'><div class='button'>" . $this->t('Decrypt') . "</div></a></p>";
    }
    else {
      $element['#description'] = $element['#description'] . '</p>';
    }
    $element['#description'] = $link;

    return ['target_id' => $element];
  }
  
  /**
  * {@inheritdoc}
  */
  public function settingsSummary() {
    $summary[] = $this->t('Foo: @foo', ['@foo' => $this->getSetting('foo')]);
    return $summary;
  }
  /**
  * {@inheritdoc}
  */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['foo'] = 'bar';
    $settings['panchiniak'] = 'sim';

    return $settings;
  }
  /**
  * {@inheritdoc}
  */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foo'),
      '#default_value' => $this->getSetting('foo'),
    ];

    $form['panchiniak'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Panchiniak'),
      '#default_value' => $this->getSetting('panchiniak'),
    ];
    return $form;
  }
}
