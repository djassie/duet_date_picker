<?php

namespace Drupal\duet_date_picker\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted date is not in the past.
 *
 * @Constraint(
 *   id = "NoPastDates",
 *   label = @Translation("No past dates.", context = "Validation"),
 *   type = "datetime"
 * )
 */
class NoPastDatesConstraint extends Constraint {

  /**
   * @var dateIsPast
   *   The message that will be shown if the date is in the past.
   */
  public $dateIsPast = '%value is in the past. Please select a date later than now.';

}
