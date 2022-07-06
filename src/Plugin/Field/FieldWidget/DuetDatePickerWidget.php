<?php

namespace Drupal\duet_date_picker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
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
class DuetDatePickerWidget extends DateTimeWidgetBase {

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
    // Add the date picker markup.
    $element['duet_date_picker'] = [
      '#markup' => '<label for="date">Choose a date</label></duet-date-picker identifier="date"></duet-date-picker>',
    ];
    return $element;
  }

}
