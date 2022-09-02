<?php

namespace Drupal\duet_date_picker\Plugin\Validation\Constraint;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NoPastDates constraint.
 */
class NoPastDatesConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      // Get field date type.
      $date_value_type = $item->getFieldDefinition()->getSetting('datetime_type');
      $date_value = $item->getValue();
      // Check if the value is in the past.
      if (!empty($date_value['value']) and $this->isPastDate($date_value['value'], $date_value_type)) {
        $this->context->buildViolation($constraint->dateIsPast)->atPath($this->context->getPropertyPath('value'))->addViolation();
      }
      if (!empty($date_value['end_value']) and $this->isPastDate($date_value['end_value'], $date_value_type)) {
        $this->context->buildViolation($constraint->dateIsPast)->atPath($this->context->getPropertyPath('end_value'))->addViolation();
      }
    }
  }

  /**
   * Is the date in the past?
   *
   * @param mixed $value
   *   Datetime value.
   *
   * @param string $date_value_type
   *   Date value type.
   */
  private function isPastDate($value, $date_value_type) {
    // Ensure we check 'now' time using the same storage time zone as the field.
    $storage_timezone = new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE);
    $value_date = new \DateTime($value, $storage_timezone);
    $time_constraint = ('datetime' == $date_value_type) ? 'now' : 'today';
    $now_date = new \DateTime($time_constraint, $storage_timezone);
    return $value_date->getTimestamp() < $now_date->getTimestamp();
  }

}
