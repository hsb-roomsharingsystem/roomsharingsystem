<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");

/**
 * Backend-Class for the booking form.
 *
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Christopher Marks <deamp_marks@yahoo.de>
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 */
class ilRoomSharingBook
{
	private $pool_id;
	private $ilRoomsharingDatabase;
	private $date_from;
	private $date_to;
	private $room_id;
	private $participants;

	/**
	 * Constructor
	 *
	 * @global type $lng
	 * @global type $ilUser
	 * @param type $a_pool_id
	 */
	public function __construct($a_pool_id)
	{
		global $lng, $ilUser;

		$this->lng = $lng;
		$this->user = $ilUser;
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Method to add a new booking into the database
	 *
	 * @param type $a_booking_values Array with the values of the booking
	 * @param type $a_booking_attr_values Array with the values of the booking-attributes
	 * @param type $a_booking_participants Array with the values of the participants
	 * @throws ilRoomSharingBookException
	 */
	public function addBooking($a_booking_values, $a_booking_attr_values, $a_booking_participants)
	{
		$this->date_from = $a_booking_values ['from'] ['date'] . " " . $a_booking_values ['from'] ['time'];
		$this->date_to = $a_booking_values ['to'] ['date'] . " " . $a_booking_values ['to'] ['time'];
		$this->room_id = $a_booking_values ['room'];
		$this->participants = $a_booking_participants;

		$this->validateBookingInput();
		$success = $this->insertBooking($a_booking_attr_values, $a_booking_values, $a_booking_participants);

		if ($success)
		{
			$this->sendBookingNotification();
		}
		else
		{
			throw new ilRoomSharingBookException($this->lng->txt('rep_robj_xrs_booking_add_error'));
		}
	}

	// Diese Methode dient dazu, die Tage in täglichem Abstand zu generieren
	// bis zu einem Endtermin
	public function generateDailyDaysWithEndDate($a_startday, $a_untilday, $a_every_x_days)
	{
		$days = array($a_startday);
		if ($a_startday < $a_untilday)
		{
			$nextday = $a_startday;
			while ($nextday != $a_untilday)
			{
				$nextday = date('Y-m-d', strtotime($nextday . ' + ' . $a_every_x_days . ' day'));
				$days[] = $nextday;
			}
			$days[] = $until;
		}
		return $days;
	}

	// Diese Methode dient dazu, die Tage in täglichem Abstand zu generieren
	// bei einer festen Anzahl an Wiederholungen
	public function generateDailyDaysWithCount($a_startday, $a_count, $a_every_x_days)
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
	// bei einer festen Anzahl an Wiederholungen
	public function generateWeeklyDaysWithCount($a_startday, $a_count, $a_weekdays, $a_every_x_weeks)
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
			$days = $this->getFollowingWeekdaysByWeekdayNames($a_startday, $a_weekdays, $days);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d', strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));
		}
		return $days;
	}

	// Diese Methode dient dazu, die Tage in wöchentlichem Abstand zu generieren
	// bis zu einem festen Datum
	public function generateWeeklyDaysWithEndDate($a_startday, $a_enddate, $a_weekdays,
		$a_every_x_weeks)
	{
		$startday = $a_startday;
		//Packe den Starttag als Start in das Array, sonst würde er verloren gehen
		$days = array($startday);
		while (true)
		{
			//Ausgewählte Wochentage für die Woche, in der $startday ist werden
			//in $days gepackt, wobei $days mit übergeben werden muss
			//damit schon generierte vorherige Werte nicht überschrieben werden
			//Abhängig von der Auswahl wie "Mo", "Di", "Do", "Sa"
			$days = $this->getFollowingWeekdaysByWeekdayNames($startday, $a_weekdays, $days);

			//Für die nächste Wiederholung X Wochen vorspringen
			//Abhängig von Auswahl bei "Alle X Wochen"
			$startday = date('Y-m-d', strtotime($startday . ' + ' . $a_every_x_weeks . ' week'));

			//Wiederholung beenden, wenn das neue Wochendatum
			//größer als das gewählte Enddatum ist
			if ($startday > $a_enddate)
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
	public function generateMonthlyDaysAtVariableDateWithCount($a_startday, $a_variable_number,
		$a_each_day, $a_count, $a_every_x_months)
	{
		$startday = $a_startday;
		$variable_name = $this->getEnumerationName($a_variable_number);
		$each_day_name = $this->getFullDayNameByShortName($a_each_day);

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

			//Fifth day of january 2015 z.B. geht nicht! Nur last day of..
			//da muss noch ne Lösung her!
			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));
		}

		return $days;
	}

	// Diese Methode dient dazu, die Tage in monatlichen Abstand zu generieren
	// bis zu einem End-Datum
	public function generateMonthlyDaysAtVariableDateWithEndDate($a_startday, $a_variable_number,
		$a_each_day, $a_enddate, $a_every_x_months)
	{
		$startday = $a_startday;
		$variable_name = $this->getEnumerationName($a_variable_number);
		$each_day_name = $this->getFullDayNameByShortName($a_each_day);

		$days = array();
		//Solange durchlaufen, wie Wiederholungen vorhanden sind
		while (true)
		{
			$monthname_with_year_of_startday = date("F Y", strtotime($startday));

			//Ein Beispiel wäre hier: strtotime("fourth friday of january 2015")
			//Wird z.B. fifth monday genommen und den gibt es nicht, wie z.B.
			//im Januar, dann nimmt php den ersten vom Februar,
			//ich denke das ist ok
			$days[] = date('Y-m-d',
				strtotime($variable_name . " " . $each_day_name . " of " . $monthname_with_year_of_startday));

			//Fifth day of january 2015 z.B. geht nicht! Nur last day of..
			//da muss noch ne Lösung her!
			//Für die nächste Wiederholung X Monate vorspringen
			//Abhängig von Auswahl bei "Alle X Monate"
			$startday = date('Y-m-d', strtotime($startday . " + " . $a_every_x_months . " month"));

			if ($startday > $a_enddate)
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

	// Diese Methode wandelt die Keys für z.B. "ersten" oder "letzten"
	//jeden Monats in den entsprechend Text um, der von strtotime verarbeitet
	//werden kann
	private function getEnumerationName($a_variable_number)
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
	private function getFullDayNameByShortName($a_shortDayName)
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
		return $append_array;
	}

	/**
	 * Checks if the given booking input is valid (e.g. valid dates, already booked rooms, ...)
	 *
	 * @throws ilRoomSharingBookException
	 */
	private function validateBookingInput()
	{
		if ($this->isBookingInPast())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_is_earlier_than_now"));
		}
		if ($this->checkForInvalidDateConditions())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_datefrom_bigger_dateto"));
		}
		if ($this->isAlreadyBooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_room_already_booked"));
		}
		if ($this->isRoomOverbooked())
		{
			throw new ilRoomSharingBookException($this->lng->txt("rep_robj_xrs_room_max_allocation_exceeded"));
		}
	}

	/**
	 * Method to check whether the booking date is in the past
	 */
	private function isBookingInPast()
	{
		return (strtotime($this->date_from) <= time());
	}

	/**
	 * Method to check whether the date is valid
	 * date_to must be higher or equal than the date_from
	 */
	private function checkForInvalidDateConditions()
	{
		return ($this->date_from >= $this->date_to);
	}

	/**
	 * Method to check if the selected room is already booked in the given time range
	 *
	 */
	private function isAlreadyBooked()
	{
		$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($this->date_from,
			$this->date_to, $this->room_id);
		return ($temp !== array());
	}

	/**
	 * Method that checks if the max allocation of a room is exceeded.
	 */
	private function isRoomOverbooked()
	{
		$room = new ilRoomSharingRoom($this->pool_id, $this->room_id);
		$max_alloc = $room->getMaxAlloc();
		$filtered_participants = array_filter($this->participants, array($this, "filterValidParticipants"));
		$overbooked = count($filtered_participants) >= $max_alloc;

		return $overbooked;
	}

	/**
	 * Callback function which is used for existing and therefore valid participants.
	 * Also it filters out the booker itself, if he is in the list of participants.
	 *
	 * @param string $a_participant
	 * return boolean/integer id of the participant if participant exists; false otherwise
	 */
	private function filterValidParticipants($a_participant)
	{
		return (empty($a_participant) || $this->user->getLogin() === $a_participant) ? false : ilObjUser::_lookupId($a_participant);
	}

	/**
	 * Method to insert the booking
	 *
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @return type -1 failed insert, 1 successful insert
	 */
	private function insertBooking($booking_attr_values, $booking_values, $booking_participants)
	{
		return $this->ilRoomsharingDatabase->insertBooking($booking_attr_values, $booking_values,
				$booking_participants);
	}

	/**
	 * Generates a booking acknowledgement via mail.
	 *
	 * @return array $recipient_ids List of recipients.
	 */
	private function sendBookingNotification()
	{
		$room_name = $this->ilRoomsharingDatabase->getRoomName($this->room_id);

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($this->date_from);
		$mailer->setDateEnd($this->date_to);
		$mailer->sendBookingMail($this->user->getId(), $this->participants);
	}

	/**
	 * Returns the room user agreement file id.
	 */
	public function getRoomAgreementFileId()
	{
		$agreement_file_id = $this->ilRoomsharingDatabase->getRoomAgreementId();

		return $agreement_file_id;
	}

	/**
	 * Set the poolID of bookings
	 *
	 * @param integer $pool_id
	 *        	poolID
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}

	/**
	 * Get the PoolID of bookings
	 *
	 * @return integer PoolID
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}

?>
