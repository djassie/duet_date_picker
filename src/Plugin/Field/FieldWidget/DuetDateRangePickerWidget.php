<?php

namespace Drupal\duet_date_picker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'duet_daterange_picker' field widget.
 *
 * @FieldWidget(
 *   id = "duet_daterange_picker",
 *   label = @Translation("Duet Date Range Picker"),
 *   field_types = {"daterange"},
 * )
 */
class DuetDateRangePickerWidget extends DateRangeDefaultWidget {

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
    return $element;
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
