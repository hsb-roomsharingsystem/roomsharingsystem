<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

/**
 * Util-Class for day generator functions for sequence bookings
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingSequenceBookingUtils
{
	// Diese Methode dient dazu, die Tage in täglichem Abstand zu generieren
	// bis zu einem Endtermin
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

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
			//Ausgewählte Wochentage für die Woche, in der $startday ist werden
			//in $days gepackt, wobei $days mit übergeben werden muss
			//damit schon generierte vorherige Werte nicht überschrieben werden
			//Abhängig von der Auswahl wie "Mo", "Di", "Do", "Sa"
			$days['from'] = self::getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $days['from']);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));

			//Wiederholung beenden, wenn das neue Wochendatum
			//größer als das gewählte Enddatum ist
			if ($startday > $a_enddate || $i++ > 2000)
			{
				break;
			}
		}

		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
		//Packe den Starttag als Start in das Array, sonst würde er verloren gehen
		$days['from'] = array();
		$days['to'] = array();
		//Solange durchlaufen, wie Wiederholungen vorhanden sind
		for ($i = 0; $i < $a_count; $i++)
		{
			//Ausgewählte Wochentage für die Woche, in der $startday ist werden
			//in $days gepackt, wobei $days mit übergeben werden muss
			//damit schon generierte vorherige Werte nicht überschrieben werden
			//Abhängig von der Auswahl wie "Mo", "Di", "Do", "Sa"
			$days['from'] = self::getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $days['from']);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));
		}

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
			$monthname_with_year_of_startday = date("F Y", strtotime($startday));

			//Ein Beispiel wäre hier: strtotime("fourth friday of january 2015")
			//Wird z.B. fifth monday genommen und den gibt es nicht, wie z.B.
			//im Januar, dann nimmt php den ersten vom Februar,
			//ich denke das ist ok
			$days['from'][] = date('Y-m-d' . $time_format,
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days['from'] = self::convertSelectedDayOfMonthWithoutStrtotime($days['from'], $each_day_name,
					$variable_name, $monthname_with_year_of_startday);

			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate || $i++ > 500)
			{
				break;
			}
		}

		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
			$monthname_with_year_of_startday = date("F Y", strtotime($startday));

			$days['from'][] = date('Y-m-d' . $time_format,
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days['from'] = self::convertSelectedDayOfMonthWithoutStrtotime($days['from'], $each_day_name,
					$variable_name, $monthname_with_year_of_startday);

			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d' . $time_format,
				strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
			$startyear = date('m', strtotime($startday));
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

		$days['from'] = self::removeDatesAfterDay($days['from'], $a_enddate);

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
			$startyear = date('m', strtotime($startday));
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

		foreach ($days['from'] as $start_day)
		{
			$to_date = date('Y-m-d', strtotime($start_day . ' + ' . $a_day_difference . ' day'));
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
		$a_variable_name, $a_monthname_with_year)
	{
		$days = $a_days;
		if ($a_each_day_name == "Day")
		{
			switch ($a_variable_name)
			{
				case "first":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$days[] = $month_year . "-1";
					break;
				case "second":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$days[] = $month_year . "-2";
					break;
				case "third":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$days[] = $month_year . "-3";
					break;
				case "fourth":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$days[] = $month_year . "-4";
					break;
				case "fifth":
					array_pop($days);
					$month_year = date('Y-m', strtotime($a_monthname_with_year));
					$days[] = $month_year . "-5";
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

	// Diese Methode wandelt die Keys für z.B. "ersten" oder "letzten"
	//jeden Monats in den entsprechend Text um, der von strtotime verarbeitet
	//werden kann
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
			case -1:
				$variable_name = "last";
				break;
		}
		return $variable_name;
	}

	// Diese Methode wandelt die Kürzel der Tage in voll ausgeschriebene
	// Tage um, da nur die von strtotime genutzt werden können
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

	//Packt Wochentage zu dem übergebenen Array
	//jedoch nur die, dessen Kürzel in dem $a_weekday_shortnames array drinstehen
	private function getFollowingWeekdaysByWeekdayNames($a_startday, $a_weekday_shortnames,
		$append_array = array())
	{
		if (is_array($a_weekday_shortnames))
		{
			if (in_array("MO", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next monday'));
			}
			if (in_array("TU", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next tuesday'));
			}
			if (in_array("WE", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next wednesday'));
			}
			if (in_array("TH", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next thursday'));
			}
			if (in_array("FR", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next friday'));
			}
			if (in_array("SA", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next saturday'));
			}
			if (in_array("SU", $a_weekday_shortnames))
			{
				$append_array[] = date('Y-m-d', strtotime($a_startday . ' next sunday'));
			}
		}
		return $append_array;
	}

}
