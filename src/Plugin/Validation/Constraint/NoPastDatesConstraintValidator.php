<?php

namespace Drupal\duet_date_picker\Plugin\Validation\Constraint;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
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
    foreach ($items as $item) {
      // Check if the value is in the past.
      if ($this->isPastDate($item->date->getTimestamp())) {
        $this->context->addViolation($constraint->dateIsPast, ['%value' => $item->value]);
      }
    }
  }

  /**
   * Is the date in the past?
   *
   * @param mixed $value
   *   Datetime value.
   */
  private function isPastDate($value) {
    // Ensure we check 'now' time using the same storage time zone as the field.
    $storage_timezone = new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE);
    $now_date = new \DateTime('now', $storage_timezone);
    $now = $now_date->getTimestamp();
    return $value < $now;
  }

}
