<?php

namespace Drupal\duet_date_picker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Defines the 'duet_daterange_picker' field widget.
 *
 * @FieldWidget(
 *   id = "duet_daterange_picker",
 *   label = @Translation("Duet Date Range Picker"),
 *   field_types = {"daterange"},
 * )
 */
class DuetDateRangePickerWidget extends DateRangeDefaultWidget implements TrustedCallbackInterface {

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
    $start_date_picker_element_name = 'value';
    $end_date_picker_element_name = 'end_value';
    // Get any input values from the form state.
    $form_element_name = $this->fieldDefinition->getFieldStorageDefinition()->getName();
    $input = $form_state->getUserInput()[$form_element_name][$delta];
    if (!empty($element['value']['#date_time_element']) and 'none' != $element['value']['#date_time_element']) {
      // This field is configured to accept a date and time value.
      // Create a separate element to use Duet Date Picker for date, but leave
      // the time element/input alone.
      $start_date_picker_element_name = 'date_value';
      $end_date_picker_element_name = 'end_date_value';
      $element[$start_date_picker_element_name]['#date_time_element'] = 'none';
      $element[$start_date_picker_element_name]['#date_time_format'] = '';
      $element[$end_date_picker_element_name] = $element['end_value'];
      $element = [
        $start_date_picker_element_name => $element['value'],
        $end_date_picker_element_name => $element['end_value'],
      ] + $element;
      $element[$end_date_picker_element_name]['#date_time_element'] = 'none';
      $element[$end_date_picker_element_name]['#date_time_format'] = '';
      // Remove the date element info from the time element.
      $element['value']['#date_date_element'] = 'none';
      $element['value']['#date_date_format'] = '';
      $element['end_value']['#date_date_element'] = 'none';
      $element['end_value']['#date_date_format'] = '';
    }

    $element[$start_date_picker_element_name]['#theme'] = 'duet_date_picker';
    $element[$end_date_picker_element_name]['#theme'] = 'duet_date_picker';
    // Set callback to process date value on submit.
    $element[$start_date_picker_element_name]['#date_date_callbacks'][] = [$this, 'dateDateCallback'];
    $element[$end_date_picker_element_name]['#date_date_callbacks'][] = [$this, 'dateDateCallback'];

    // Set correct default values for date and time.
    if (!empty($input[$start_date_picker_element_name])) {
      if (!empty($input['value']['time'])) {
        $default_date_obj = new DrupalDateTime($input[$start_date_picker_element_name] . $input['value']['time']);
        $element[$start_date_picker_element_name]['#default_value'] = $default_date_obj;
        $element['value']['#default_value'] = $default_date_obj;
      }
      else {
        $default_date_obj = new DrupalDateTime($input[$start_date_picker_element_name]);
        $element[$start_date_picker_element_name]['#default_value'] = $default_date_obj;
      }
    }
    if (!empty($input[$end_date_picker_element_name])) {
      if (!empty($input['end_value']['time'])) {
        $default_date_obj = new DrupalDateTime($input[$end_date_picker_element_name] . $input['end_value']['time']);
        $element[$end_date_picker_element_name]['#default_value'] = $default_date_obj;
        $element['end_value']['#default_value'] = $default_date_obj;
      }
      else {
        $default_date_obj = new DrupalDateTime($input[$end_date_picker_element_name]);
        $element[$end_date_picker_element_name]['#default_value'] = $default_date_obj;
      }
    }

    // Prevent any additional blank fields for multi-value fields.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (empty($items[$delta]->getValue()) and $cardinality != 1 and $delta > 0) {
      $element = [];
    }
    // Pass widget settings to the rendering template.
    $settings = $this->getSettings();
    if (!empty($element[$start_date_picker_element_name])) {
      $element[$start_date_picker_element_name]['#no_past_dates'] = $settings['no_past_dates'];
      $element[$start_date_picker_element_name]['#label'] = $this->t($settings['start_label']);
      // Unset the default form element title.
      unset($element[$start_date_picker_element_name]['#title']);
    }
    if (!empty($element[$end_date_picker_element_name])) {
      $element[$end_date_picker_element_name]['#no_past_dates'] = $settings['no_past_dates'];
      $element[$end_date_picker_element_name]['#label'] = $this->t($settings['end_label']);
      unset($element[$end_date_picker_element_name]['#title']);
    }
    if ($settings['no_past_dates']) {
      // Add the NoPastDates constraint validation.
      $this->fieldDefinition->addConstraint('NoPastDates');
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
            $values[$delta]['value'] = $date_object;
          }
          elseif (!array_key_exists('date_value', $date_value[$delta]) and !empty($date_value[$delta]['value'])) {
            $date_object = new DrupalDateTime($date_value[$delta]['value']);
            $values[$delta]['value'] = $date_object;
          }
          if (!empty($date_value[$delta]['end_date_value'] and !empty($date_value[$delta]['end_value']))) {
            $end_date_object = new DrupalDateTime($date_value[$delta]['end_date_value'] . 'T' . $date_value[$delta]['end_value']['time']);
            $values[$delta]['end_value'] = $end_date_object;
          }
          elseif (!array_key_exists('end_date_value', $date_value[$delta]) and !empty($date_value[$delta]['end_value'])) {
            $end_date_object = new DrupalDateTime($date_value[$delta]['end_value']);
            $values[$delta]['end_value'] = $end_date_object;
          }
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
      // Start date label.
      'start_label' => 'Choose a start date',
      // End date label.
      'end_label' => 'Choose an end date',
      // Add setting to disallow dates in the past.
      'no_past_dates' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['start_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start date label'),
      '#default_value' => $this->t($this->getSetting('start_label')),
      '#required' => TRUE,
    ];
    $element['end_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End date label'),
      '#default_value' => $this->t($this->getSetting('end_label')),
      '#required' => TRUE,
    ];
    $element['no_past_dates'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disallow past dates'),
      '#default_value' => $this->getSetting('no_past_dates'),
      '#required' => FALSE,
    ];

    return $element;
  }

}
