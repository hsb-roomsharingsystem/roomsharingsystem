<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

/**
 * Util-Class for day generator functions for sequence bookings
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingSequenceBookingUtils
{
	public static function generateDailyDaysWithEndDate($a_startday, $a_untilday, $a_every_x_days,
		$a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$days['from'] = array($a_startday . $time);
		$days['to'] = array();
		if ($a_startday < $a_untilday)
		{
			$nextday = $a_startday;
			$i = 0;
			while ($nextday != $a_untilday && $i < 2000)
			{
				$nextday = date('Y-m-d' . $time_format, strtotime($nextday . ' + ' . $a_every_x_days . ' day'));
				$days[] = $nextday;
				$i++;
			}
			$days['from'][] = $a_untilday;
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateDailyDaysWithCount($a_startday, $a_count, $a_every_x_days,
		$a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$days['from'] = array($a_startday . $time);
		$days['to'] = array();
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_count))
		{
			$nextday = $days[0];
			for ($i = 0; $i < $a_count; $i++)
			{
				$nextday = date('Y-m-d' . $time_format, strtotime($nextday . ' + ' . $a_every_x_days . '  day'));
				$days['from'][] = $nextday;
			}
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateWeeklyDaysWithEndDate($a_startday, $a_enddate, $a_weekdays,
		$a_every_x_weeks, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;

		$days['from'] = array();
		$days['to'] = array();
		$i = 0;
		while (true)
		{
			$days['from'] = self::getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $time_format,
					$days['from']);

			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));

			if ($startday > $a_enddate || $i++ > 2000)
			{
				break;
			}
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);
		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateWeeklyDaysWithCount($a_startday, $a_count, $a_weekdays,
		$a_every_x_weeks, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;

		$days['from'] = array();
		$days['to'] = array();

		for ($i = 0; $i < $a_count; $i++)
		{
			$days['from'] = self::getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $time_format,
					$days['from']);

			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateMonthlyDaysAtVariableDateWithEndDate($a_startday,
		$a_variable_number, $a_each_day, $a_enddate, $a_every_x_months, $a_time_from = null,
		$a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;
		$variable_name = self::getEnumerationName($a_variable_number);
		$each_day_name = self::getFullDayNameByShortName($a_each_day);

		$days['from'] = array();
		$days['to'] = array();

		$i = 0;
		while (true)
		{
			$monthname_with_year_of_startday = date("F Y" . $time_format, strtotime($startday));

			$days['from'][] = date('Y-m-d' . $time_format,
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days['from'] = self::convertSelectedDayOfMonthWithoutStrtotime($days['from'], $each_day_name,
					$variable_name, $monthname_with_year_of_startday, $time);

			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate || $i++ > 500)
			{
				break;
			}
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);
		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateMonthlyDaysAtVariableDateWithCount($a_startday, $a_variable_number,
		$a_each_day, $a_count, $a_every_x_months, $a_time_from = null, $a_time_to = null,
		$a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;
		$variable_name = self::getEnumerationName($a_variable_number);
		$each_day_name = self::getFullDayNameByShortName($a_each_day);

		$days['from'] = array();
		$days['to'] = array();

		for ($i = 0; $i < $a_count; $i++)
		{
			$monthname_with_year_of_startday = date("F Y" . $time_format, strtotime($startday));

			$days['from'][] = date('Y-m-d' . $time_format,
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days['from'] = self::convertSelectedDayOfMonthWithoutStrtotime($days['from'], $each_day_name,
					$variable_name, $monthname_with_year_of_startday, $time);

			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}
		return $days;
	}

	public static function generateMonthlyDaysAtFixedDateWithEndDate($a_startday, $a_monthday,
		$a_enddate, $a_every_x_months, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;
		$day_of_startday = date("d", strtotime($startday));

		//Is the startday in the future? Then skip the month of the startday!
		if ($day_of_startday > $a_monthday)
		{
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days['from'] = array();
		$days['to'] = array();

		$i = 0;
		while (true)
		{
			$startmonth = date('m', strtotime($startday));
			$startyear = date('Y', strtotime($startday));
			$starttime = date($time_format, strtotime($startday));
			if (checkdate($startmonth, $a_monthday, $startyear))
			{
				$days['from'][] = $startyear . "-" . $startmonth . "-" . $a_monthday . $starttime;
			}
			else
			{
				//If the day is not valid (e.g. 31 February), what to do?
				//--> Calendar does nothing! So here nothing, too!
			}

			//Set the startday to the next X month (depending on user choice)
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate || $i++ > 500)
			{
				break;
			}
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);
		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function generateMonthlyDaysAtFixedDateWithCount($a_startday, $a_monthday, $a_count,
		$a_every_x_months, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_day_difference, true))
		{
			$day_difference_to_end_day = $a_day_difference;
		}
		else
		{
			$day_difference_to_end_day = 0;
		}

		if ($a_time_from != null)
		{
			$time = " " . $a_time_from;
			$time_format = " H:i:s";
		}
		else
		{
			$time = "";
			$time_format = "";
		}

		$startday = $a_startday . $time;
		$day_of_startday = date("d", strtotime($startday));

		//Is the startday in the future? Then skip the month of the startday!
		if ($day_of_startday > $a_monthday)
		{
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days['from'] = array();
		$days['to'] = array();
		for ($i = 0; $i < $a_count; $i++)
		{
			$startmonth = date('m', strtotime($startday));
			$startyear = date('Y', strtotime($startday));
			$starttime = date($time_format, strtotime($startday));
			if (checkdate($startmonth, $a_monthday, $startyear))
			{
				$days['from'][] = $startyear . "-" . $startmonth . "-" . $a_monthday . $starttime;
			}
			else
			{
				//If the day is not valid (e.g. 31 February), what to do?
				//--> Calendar does nothing! So here nothing, too!
			}

			//Set the startday to the next X month (depending on user choice)
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days['from'] = self::removeDatesBeforeNow($days['from'], $time_format);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $day_difference_to_end_day . ' day'));
			if ($a_time_to != null)
			{
				$days['to'][] = $to_date . " " . $a_time_to;
			}
			else
			{
				$days['to'][] = $to_date;
			}
		}

		return $days;
	}

	public static function getMonthlyFilteredData($a_date, $a_repeat_type, $a_repeat_amount,
		$a_repeat_until, $a_start_type, $a_monthday, $a_weekday_1, $a_weekday_2, $a_time_from = null,
		$a_time_to = null, $a_day_difference = null)
	{
		$days = array();
		if ($a_start_type == "weekday")
		{
			if ($a_repeat_type == "max_date")
			{
				$days = self::generateMonthlyDaysAtVariableDateWithEndDate($a_date, $a_weekday_1, $a_weekday_2,
						$a_repeat_until, $a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
			}
			elseif ($a_repeat_type == "max_amount")
			{
				$days = self::generateMonthlyDaysAtVariableDateWithCount($a_date, $a_weekday_1, $a_weekday_2,
						$a_repeat_until, $a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
			}
		}
		elseif ($a_start_type == "monthday")
		{
			if ($a_repeat_type == "max_date")
			{
				$days = self::generateMonthlyDaysAtFixedDateWithEndDate($a_date, $a_monthday, $a_repeat_until,
						$a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
			}
			elseif ($a_repeat_type == "max_amount")
			{
				$days = self::generateMonthlyDaysAtFixedDateWithCount($a_date, $a_monthday, $a_repeat_until,
						$a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
			}
		}
		return $days;
	}

	public static function getWeeklyFilteredData($a_date, $a_repeat_type, $a_repeat_amount,
		$a_repeat_until, $a_weekdays, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if ($a_repeat_type == "max_date")
		{
			$a_repeat_until = date('Y-m-d',
				mktime(23, 59, 59, $a_repeat_until['date']['m'], $a_repeat_until['date']['d'],
					$a_repeat_until['date']['y']));
			$days = self::generateWeeklyDaysWithEndDate($a_date, $a_repeat_until, $a_weekdays,
					$a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
		}
		elseif ($a_repeat_type == "max_amount")
		{
			$days = self::generateWeeklyDaysWithCount($a_date, $a_repeat_until, $a_weekdays,
					$a_repeat_amount, $a_time_from, $a_time_to, $a_day_difference);
		}

		return $days;
	}

	public static function getDailyFilteredData($a_date_from, $a_repeat_type, $a_repeat_amount,
		$a_repeat_until, $a_time_from = null, $a_time_to = null, $a_day_difference = null)
	{
		if ($a_repeat_type == "max_date")
		{
			$a_repeat_until = date('Y-m-d',
				mktime(23, 59, 59, $a_repeat_until['date']['m'], $a_repeat_until['date']['d'],
					$a_repeat_until['date']['y']));
			$days = self::generateDailyDaysWithEndDate($a_date_from, $a_repeat_until, $a_repeat_amount,
					$a_time_from, $a_time_to, $a_day_difference);
		}
		elseif ($a_repeat_type == "max_amount")
		{
			$days = self::generateDailyDaysWithCount($a_date_from, $a_repeat_until, $a_repeat_amount,
					$a_time_from, $a_time_to, $a_day_difference);
		}

		return $days;
	}

	private static function convertSelectedDayOfMonthWithoutStrtotime($a_days, $a_each_day_name,
		$a_variable_name, $a_monthname_with_year, $a_time = null)
	{
		$days = $a_days;
		if ($a_each_day_name == "Day")
		{
			switch ($a_variable_name)
			{
				case "first":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$day = $month_year . "-1";
					if ($a_time != null)
					{
						$day = $day . " " . $a_time;
					}
					$days[] = $day;
					break;
				case "second":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$day = $month_year . "-2";
					if ($a_time != null)
					{
						$day = $day . " " . $a_time;
					}
					$days[] = $day;
					break;
				case "third":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$day = $month_year . "-3";
					if ($a_time != null)
					{
						$day = $day . " " . $a_time;
					}
					$days[] = $day;
					break;
				case "fourth":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$day = $month_year . "-4";
					if ($a_time != null)
					{
						$day = $day . " " . $a_time;
					}
					$days[] = $day;
					break;
				case "fifth":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$day = $month_year . "-5";
					if ($a_time != null)
					{
						$day = $day . " " . $a_time;
					}

					$days[] = $day;
					break;
			}
		}
		return $days;
	}

	private static function removeDatesAfterDay($a_dates, $a_enddate)
	{
		$filtered_days = array();
		foreach ($a_dates as $day)
		{
			if ($day <= $a_enddate)
			{
				$filtered_days[] = $day;
			}
		}
		return $filtered_days;
	}

	private static function removeDatesBeforeNow($a_dates, $a_time_format = "")
	{
		$filtered_days = array();
		if (substr($a_time_format, 0, 1) == " ")
		{
			$a_time_format = substr($a_time_format, 1);
		}
		foreach ($a_dates as $day)
		{
			if ($day >= date('Y-m-d ' . $a_time_format))
			{
				$filtered_days[] = $day;
			}
		}
		return $filtered_days;
	}

	private static function getEnumerationName($a_variable_number)
	{
		$variable_name = "";
		switch ($a_variable_number)
		{
			case 1:
				$variable_name = "first";
				break;
			case 2:
				$variable_name = "second";
				break;
			case 3:
				$variable_name = "third";
				break;
			case 4:
				$variable_name = "fourth";
				break;
			case 5:
				$variable_name = "fifth";
				break;
			case -1: $variable_name = "last";
				break;
		}
		return $variable_name;
	}

	private static function getFullDayNameByShortName($a_shortDayName)
	{
		$dayname = "";
		switch ($a_shortDayName)
		{
			case "MO":
				$dayname = "Monday";
				break;
			case "TU":
				$dayname = "Tuesday";
				break;
			case "WE":
				$dayname = "Wednesday";
				break;
			case "TH":
				$dayname = "Thursday";
				break;
			case "FR":
				$dayname = "Friday";
				break;
			case "SA":
				$dayname = "Saturday";
				break;
			case "SU":
				$dayname = "Sunday";
				break;
			case 8:
				$dayname = "Weekday";
				break;
			case 9:
				$dayname = "Day";
				break;
		}
		return $dayname;
	}

	private function getFollowingWeekdaysByWeekdayNames($a_startday, $a_weekday_shortnames,
		$a_time_format, $a_append_array = array())
	{
		if (is_array($a_weekday_shortnames))
		{
			if (in_array("MO", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next monday'));
			}
			if (in_array("TU", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next tuesday'));
			}
			if (in_array("WE", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next wednesday'));
			}
			if (in_array("TH", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next thursday'));
			}
			if (in_array("FR", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next friday'));
			}
			if (in_array("SA", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next saturday'));
			}
			if (in_array("SU", $a_weekday_shortnames))
			{
				$a_append_array[] = date('Y-m-d' . $a_time_format, strtotime($a_startday . ' next sunday'));
			}
		}
		return $a_append_array;
	}

}
