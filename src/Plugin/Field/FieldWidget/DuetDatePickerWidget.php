<?php

namespace Drupal\duet_date_picker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'duet_date_picker' field widget.
 *
 * @FieldWidget(
 *   id = "duet_date_picker",
 *   label = @Translation("Duet Date Picker"),
 *   field_types = {"datetime"},
 * )
 */
class DuetDatePickerWidget extends DateTimeDefaultWidget {

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
      $form_state->setValue($form_element_name, $date_value);
    }
    $values = parent::massageFormValues($values, $form, $form_state);
    return $values;
  }

}
