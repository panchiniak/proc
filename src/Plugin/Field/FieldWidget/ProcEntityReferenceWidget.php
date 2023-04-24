<?php

namespace Drupal\proc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\proc\Entity\Element\ProcEntityAutocomplete;
use Drupal\Component\Utility\Crypt;
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
    $referenced_entities = $items->referencedEntities();

    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + [
      'match_operator' => $this->getSetting('match_operator'),
      'match_limit' => $this->getSetting('match_limit'),
    ];
    
    
    $library = [
      'library' => [
        0 => 'proc/field'
      ],
    ];

    $element += [
      // @todo: create a form element called proc_entity_autocomplete
      '#type' => 'entity_autocomplete',
      // '#type' => 'autocomplete_flexible',
      // '#autocomplete_route_parameters' => [
      //   'target_type' => $this->getFieldSetting('target_type'),
      //   'selection_handler' => $this->getFieldSetting('handler'),
      //   'selection_settings_key' => Crypt::hmacBase64($data, Settings::getHashSalt())
      // ],
      '#target_type' => $this->getFieldSetting('target_type'),
      '#selection_handler' => $this->getFieldSetting('handler'),
      '#selection_settings' => $selection_settings,
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
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

    $element['#attached']['library'][] = 'proc/proc-field';
    $element['#attached']['drupalSettings']['proc']['proc_labels'] = ['test1', 'test2'];
    $element['#attached']['drupalSettings']['proc']['proc_data'] = ['test1', 'test2'];
    $element['#description'] = $element['#description'] . "<p><a class='use-ajax' id='proc-dialog-encrypt' data-dialog-type='dialog' href='./../../proc/add/1?proc_standalone_mode=FALSE'><div class='button'>" . $this->t('Encrypt') . "</div></a>";
    
    // If there is a default value, add also the Decrypt button:
    if ($element['#default_value']) {
      $proc_id = $element['#default_value']->get('id')->getValue()[0]['value'];
      $element['#description'] = $element['#description'] . "<a class='use-ajax' data-dialog-type='modal' href='./../../proc/" . $proc_id . "'><div class='button'>" . $this->t('Decrypt') . "</div></a></p>";
    }
    else {
      $element['#description'] = $element['#description'] . '</p>';
    }

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
