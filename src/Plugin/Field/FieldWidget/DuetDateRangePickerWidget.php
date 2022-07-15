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
    // Theme the widget as a Duet date picker.
    $element['value']['#theme'] = 'duet_date_picker';
    $element['end_value']['#theme'] = 'duet_date_picker';
    // Set callback to process date value on submit.
    $element['value']['#date_date_callbacks'][] = [$this, 'dateDateCallback'];
    $element['end_value']['#date_date_callbacks'][] = [$this, 'dateDateCallback'];
    // Prevent any additional blank fields for multi-value fields.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (empty($items[$delta]->getValue()) and $cardinality != 1 and $delta > 0) {
      $element = [];
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
      if (!empty($date_value['value'])) {
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
          $date_object = new DrupalDateTime($date_value[$delta]['value']);
          $end_date_object = new DrupalDateTime($date_value[$delta]['end_value']);
          $values[$delta]['value'] = $date_object;
          $values[$delta]['end_value'] = $end_date_object;
        }
      }
    }
    $values = parent::massageFormValues($values, $form, $form_state);
    return $values;
  }

}
