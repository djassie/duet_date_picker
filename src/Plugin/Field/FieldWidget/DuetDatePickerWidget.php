<?php

namespace Drupal\duet_date_picker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Defines the 'duet_date_picker' field widget.
 *
 * @FieldWidget(
 *   id = "duet_date_picker",
 *   label = @Translation("Duet Date Picker"),
 *   field_types = {"datetime"},
 * )
 */
class DuetDatePickerWidget extends DateTimeDefaultWidget implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['dateDateCallback'];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Attach Duet Date Picker library.
    $element['#attached'] = [
      'library' => [
        'duet_date_picker/duet-date-picker',
      ],
    ];
    if (!empty($element['value']['#date_time_element'])) {
      // This field is configured to accept a date and time value.
      // Create a separate element to use Duet Date Picker for date, but leave
      // the time element/input alone.
      $element['date_value'] = $element['value'];
      $element['date_value']['#theme'] = 'duet_date_picker';
      $element['date_value']['#date_time_element'] = 'none';
      $element['date_value']['#date_time_format'] = '';
      // Set callback to process date value on submit.
      $element['date_value']['#date_date_callbacks'][] = [$this, 'dateDateCallback'];
      // Remove the date element info from the time element.
      $element['value']['#date_date_element'] = 'none';
      $element['value']['#date_date_format'] = '';
    }
    else {
      // This is just a plain old date (no time) field.
      $element['value']['#theme'] = 'duet_date_picker';
      // Set callback to process date value on submit.
      $element['value']['#date_date_callbacks'][] = [$this, 'dateDateCallback'];
    }
    // Theme the widget as a Duet date picker.
    // Prevent any additional blank fields for multi-value fields.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (empty($items[$delta]->getValue()) and $cardinality != 1 and $delta > 0) {
      $element = [];
    }
    // Pass widget settings to the rendering template.
    $settings = $this->getSettings();
    if (!empty($element['date_value'])) {
      $element['date_value']['settings'] = $settings;
    }
    else {
      $element['value']['settings'] = $settings;
    }

    return $element;
  }

  /**
   * Process callback to set correct date value in form_state on submit.
   */
  public function dateDateCallback(&$element, FormStateInterface $form_state, $date) {
    // Get the form element name and element delta.
    $form_element_name = $this->fieldDefinition->getFieldStorageDefinition()->getName();
    // @todo there must be a better way to get the element delta?
    $form_element_delta = $element['#parents'][1];
    $form_input = $form_state->getUserInput($form_element_name);
    if (!empty($form_input[$form_element_name][$form_element_delta])) {
      $date_value = $form_input[$form_element_name][$form_element_delta];
      if (!empty($date_value['date_value'] and !empty($date_value['value']))) {
        // We are dealing with a datetime field (not just date).
        $date_object = new DrupalDateTime($date_value['date_value'] . 'T' . $date_value['value']['time']);
        $value = [
          'date' => $date_value['date_value'],
          'time' => $date_value['value']['time'],
          'object' => $date_object,
        ];
      }
      elseif (!array_key_exists('date_value', $date_value) and !empty($date_value['value'])) {
        $date_object = new DrupalDateTime($date_value['value']);
        $value = [
          'date' => $date_value['value'],
          'object' => $date_object,
        ];
      }
      else {
        $value = NULL;
        unset($form_input[$form_element_name][$form_element_delta]);
        $form_state->setUserInput($form_input);
      }
      // Set the value for the element so that it is correctly
      // handled by the Datetime element validator.
      $form_state->setValueForElement($element, $value);
    }
    else {
      $form_state->setValueForElement($element, NULL);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $form_element_name = $this->fieldDefinition->getFieldStorageDefinition()->getName();
    $form_input = $form_state->getUserInput();
    if (!empty($form_input[$form_element_name])) {
      $date_value = $form_input[$form_element_name];
      if (!empty($date_value)) {
        foreach ($values as $delta => $value) {
          if (!empty($date_value[$delta]['date_value'] and !empty($date_value[$delta]['value']))) {
            $date_object = new DrupalDateTime($date_value[$delta]['date_value'] . 'T' . $date_value[$delta]['value']['time']);
          }
          else {
            $date_object = new DrupalDateTime($date_value[$delta]['value']);
          }
          $values[$delta]['value'] = $date_object;
        }
      }
    }
    $values = parent::massageFormValues($values, $form, $form_state);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Add setting to disallow dates in the past.
      'no_past_dates' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['no_past_dates'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disallow past dates'),
      '#default_value' => $this->getSetting('no_past_dates'),
      '#required' => FALSE,
    ];

    return $element;
  }

}
