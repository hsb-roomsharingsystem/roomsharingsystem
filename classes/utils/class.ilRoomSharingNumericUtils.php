<?php

/**
 * Util-Class for numeric operations
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingNumericUtils
{
	/**
	 * Checks if a Number is positive
	 *
	 * @param double $a_number Double/Float/Int Number which should be checked
	 * @param boolean $with_zero
	 * 				Default: false - Set true if zero should be interpreted as a positive number
	 * @return boolean true if positive, else false
	 */
	public static function isPositiveNumber($a_number, $with_zero = false)
	{
		if ($with_zero)
		{
			return (is_numeric($booking_participant_id) && $booking_participant_id >= 0);
		}
		else
		{
			return (is_numeric($booking_participant_id) && $booking_participant_id > 0);
		}
	}

}
