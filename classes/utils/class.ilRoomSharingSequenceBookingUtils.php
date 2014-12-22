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
	public static function generateDailyDaysWithEndDate($a_startday, $a_untilday, $a_every_x_days)
	{
		$days = array($a_startday);
		if ($a_startday < $a_untilday)
		{
			$nextday = $a_startday;
			$i = 0;
			while ($nextday != $a_untilday && $i < 2000)
			{
				$nextday = date('Y-m-d', strtotime($nextday . ' + ' . $a_every_x_days . ' day'));
				$days[] = $nextday;
				$i++;
			}
			$days[] = $a_untilday;
		}
		return $days;
	}

	// Diese Methode dient dazu, die Tage in täglichem Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public static function generateDailyDaysWithCount($a_startday, $a_count, $a_every_x_days)
	{
		$days = array($a_startday);
		if (ilRoomSharingNumericUtils::isPositiveNumber($a_count))
		{
			$nextday = $a_startday;
			for ($i = 0; $i < $a_count; $i++)
			{
				$nextday = date('Y-m-d', strtotime($nextday . ' + ' . $a_every_x_days . '  day'));
				$days[] = $nextday;
			}
		}
		return $days;
	}

	// Diese Methode dient dazu, die Tage in wöchentlichem Abstand zu generieren
	// bis zu einem festen Datum
	public static function generateWeeklyDaysWithEndDate($a_startday, $a_enddate, $a_weekdays,
		$a_every_x_weeks)
	{
		$startday = $a_startday;
		//Packe den Starttag als Start in das Array, sonst würde er verloren gehen
		$days = array($startday);
		$i = 0;
		while (true)
		{
			//Ausgewählte Wochentage für die Woche, in der $startday ist werden
			//in $days gepackt, wobei $days mit übergeben werden muss
			//damit schon generierte vorherige Werte nicht überschrieben werden
			//Abhängig von der Auswahl wie "Mo", "Di", "Do", "Sa"
			$days = self::getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $days);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d', strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));

			//Wiederholung beenden, wenn das neue Wochendatum
			//größer als das gewählte Enddatum ist
			if ($startday > $a_enddate || $i++ > 2000)
			{
				break;
			}
		}

		$return_days = array();

		//Es kann noch sein, dass ein paar Termine über dem Enddatum liegen
		//die werden dann nochmal gefiltert
		for ($i = 0; $i < count($days); $i++)
		{
			if ($days[$i] <= $a_enddate)
			{
				$return_days[] = $days[$i];
			}
		}

		return $return_days;
	}

	// Diese Methode dient dazu, die Tage in wöchentlichem Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public static function generateWeeklyDaysWithCount($a_startday, $a_count, $a_weekdays,
		$a_every_x_weeks)
	{
		$startday = $a_startday;
		//Packe den Starttag als Start in das Array, sonst würde er verloren gehen
		$days = array($a_startday);
		//Solange durchlaufen, wie Wiederholungen vorhanden sind
		for ($i = 0; $i < $a_count; $i++)
		{
			//Ausgewählte Wochentage für die Woche, in der $startday ist werden
			//in $days gepackt, wobei $days mit übergeben werden muss
			//damit schon generierte vorherige Werte nicht überschrieben werden
			//Abhängig von der Auswahl wie "Mo", "Di", "Do", "Sa"
			$days = self::getFollowingWeekdaysByWeekdayNames($a_startday, $a_weekdays, $days);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d', strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));
		}
		return $days;
	}

	// Diese Methode dient dazu, die Tage in monatlichen Abstand zu generieren
	// bis zu einem End-Datum
	public static function generateMonthlyDaysAtVariableDateWithEndDate($a_startday,
		$a_variable_number, $a_each_day, $a_enddate, $a_every_x_months)
	{
		$startday = $a_startday;
		$variable_name = self::getEnumerationName($a_variable_number);
		$each_day_name = self::getFullDayNameByShortName($a_each_day);

		$days = array();

		$i = 0;
		while (true)
		{
			$monthname_with_year_of_startday = date("F Y", strtotime($startday));

			//Ein Beispiel wäre hier: strtotime("fourth friday of january 2015")
			//Wird z.B. fifth monday genommen und den gibt es nicht, wie z.B.
			//im Januar, dann nimmt php den ersten vom Februar,
			//ich denke das ist ok
			$days[] = date('Y-m-d',
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days = self::convertSelectedDayOfMonthWithoutStrtotime($days, $each_day_name, $variable_name,
					$monthname_with_year_of_startday);

			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate || $i++ > 500)
			{
				break;
			}
		}

		$return_days = array();

		//Es kann noch sein, dass ein paar Termine über dem Enddatum liegen
		//die werden dann nochmal gefiltert
		for ($i = 0; $i < count($days); $i++)
		{
			if ($days[$i] <= $a_enddate)
			{
				$return_days[] = $days[$i];
			}
		}
		return $return_days;
	}

	// Diese Methode dient dazu, die Tage in monatlichen Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public static function generateMonthlyDaysAtVariableDateWithCount($a_startday, $a_variable_number,
		$a_each_day, $a_count, $a_every_x_months)
	{
		$startday = $a_startday;
		$variable_name = self::getEnumerationName($a_variable_number);
		$each_day_name = self::getFullDayNameByShortName($a_each_day);

		$days = array();
		//Solange durchlaufen, wie Wiederholungen vorhanden sind
		for ($i = 0; $i < $a_count; $i++)
		{
			$monthname_with_year_of_startday = date("F Y", strtotime($startday));

			//Ein Beispiel wäre hier: strtotime("fourth friday of january 2015")
			//Wird z.B. fifth monday genommen und den gibt es nicht, wie im
			//Januar halt,dann nimmt der den ersten vom Februar,
			//ich denke das ist ok
			$days[] = date('Y-m-d',
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			$days = self::convertSelectedDayOfMonthWithoutStrtotime($days, $each_day_name, $variable_name,
					$monthname_with_year_of_startday);

			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		return $days;
	}

	// Diese Methode dient dazu, die Tage in monatlichen Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public static function generateMonthlyDaysAtFixedDateWithEndDate($a_startday, $a_monthday,
		$a_enddate, $a_every_x_months)
	{
		$startday = $a_startday;
		$day_of_startday = date("d", strtotime($startday));
		//Is the startday in the future? Then skip the month of the startday!
		if ($day_of_startday > $a_monthday)
		{
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days = array();
		$i = 0;
		while (true)
		{
			$startmonth = date('m', strtotime($startday));
			$startyear = date('m', strtotime($startday));
			if (checkdate($startmonth, $a_monthday, $startyear))
			{
				$days[] = $startyear . "-" . $startmonth . "-" . $a_monthday;
			}
			else
			{
				//If the day is not valid (e.g. 31 February), what to do?
			}

			//Set the startday to the next X month (depending on user choice)
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate || $i++ > 500)
			{
				break;
			}
		}

		$return_days = array();

		//Es kann noch sein, dass ein paar Termine über dem Enddatum liegen
		//die werden dann nochmal gefiltert
		for ($i = 0; $i < count($days); $i++)
		{
			if ($days[$i] <= $a_enddate)
			{
				$return_days[] = $days[$i];
			}
		}
		return $return_days;

		return $days;
	}

	// Diese Methode dient dazu, die Tage in monatlichen Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public static function generateMonthlyDaysAtFixedDateWithCount($a_startday, $a_monthday, $a_count,
		$a_every_x_months)
	{
		$startday = $a_startday;
		$day_of_startday = date("d", strtotime($startday));
		//Is the startday in the future? Then skip the month of the startday!
		if ($day_of_startday > $a_monthday)
		{
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		$days = array();
		for ($i = 0; $i < $a_count; $i++)
		{
			$startmonth = date('m', strtotime($startday));
			$startyear = date('m', strtotime($startday));
			if (checkdate($startmonth, $a_monthday, $startyear))
			{
				$days[] = $startyear . "-" . $startmonth . "-" . $a_monthday;
			}
			else
			{
				//If the day is not valid (e.g. 31 February), what to do?
			}

			//Set the startday to the next X month (depending on user choice)
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));
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
