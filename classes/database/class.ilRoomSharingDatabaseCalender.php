<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

use ilRoomSharingDBConstants as dbc;

/**
 * Class for database queries.
 *
 * @author Malte Ahlering
 *
 * @property ilDB $ilDB
 */
class ilRoomSharingDatabaseCalendar
{
	private $pool_id;
	private $ilDB;
	private $ilRoomSharingDatabase;

	/**
	 * constructor ilRoomsharingDatabaseCalendar
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id, $ilRoomSharingDatabase)
	{
		global $ilDB; // Database-Access-Class
		$this->ilDB = $ilDB;
		$this->pool_id = $a_pool_id;
		$this->ilRoomSharingDatabase = $ilRoomSharingDatabase;
	}

	/**
	 * Gets the calendar-id of the current RoomSharing-Pool
	 *
	 * @return integer calendar-id
	 */
	public function getCalendarId()
	{
		$set = $this->ilDB->query('SELECT calendar_id FROM ' . dbc::POOLS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
		$row = $this->ilDB->fetchAssoc($set);
		return $row["calendar_id"];
	}

	/**
	 * Updates rep_robj_xrs_pools with an new calendar-id.
	 *
	 * Typically only called once per pool.
	 *
	 * @param type $a_cal_id
	 * @return type
	 */
	public function setCalendarId($a_cal_id)
	{
		return $this->ilDB->manipulate('UPDATE ' . dbc::POOLS_TABLE .
				' SET calendar_id = ' . $this->ilDB->quote($a_cal_id, 'integer') .
				' WHERE id = ' . $this->ilDB->quote($this->pool_id, 'integer'));
	}

	/**
	 * Delete calendar entries of a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function deleteCalendarEntryOfBooking($a_booking_id)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		$set = $this->ilDB->query('SELECT calendar_entry_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

		while ($a_entry_id = $this->ilDB->fetchAssoc($set))
		{
			ilCalendarEntry::_delete($a_entry_id['calendar_entry_id']);
		}
	}

	/**
	 * Delete calendar entries of a booking from the database.
	 *
	 * @param integer $a_booking_id
	 */
	public function updatingCalendarEntryOfBooking($a_booking_id)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		$set = $this->ilDB->query('SELECT calendar_entry_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE id = ' . $this->ilDB->quote($a_booking_id, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

		while ($a_entry_id = $this->ilDB->fetchAssoc($set))
		{
			ilCalendarEntry::_delete($a_entry_id['calendar_entry_id']);
		}
	}

	/** Delete calendar of current pool.
	 *
	 * @param int $cal_id
	 */
	public function deleteCalendar($cal_id)
	{
		//Deletes the calendar
		$this->ilDB->manipulate('DELETE FROM cal_categories' .
			' WHERE cat_id = ' . $this->ilDB->quote($cal_id, 'integer'));

		//Deletes the calendar-entry-links
		$this->ilDB->manipulate('DELETE FROM cal_cat_assignments' .
			' WHERE cat_id = ' . $this->ilDB->quote($cal_id, 'integer'));
	}

	/**
	 * Delete calendar entries of bookings from the database.
	 *
	 * @param array $a_booking_ids
	 */
	public function deleteCalendarEntriesOfBookings($a_booking_ids)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		$set = $this->ilDB->prepare('SELECT calendar_entry_id FROM ' . dbc::BOOKINGS_TABLE .
			' WHERE ' . $this->ilDB->in("id", $a_booking_ids) .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));

		$result = $this->ilDB->execute($set, $a_booking_ids);
		while ($a_entry_id = $this->ilDB->fetchAssoc($result))
		{
			ilCalendarEntry::_delete($a_entry_id['calendar_entry_id']);
		}
	}

	/**
	 * Update an appointment in the RoomSharing-Calendar and save id in booking-table.
	 * This methode delete first all existings calendarEntrys of the given booking id
	 * and then it create new one with the given booking id.
	 *
	 * @param type $a_booking_id
	 * @param type $a_booking_values
	 */
	public function updateBookingAppointment($a_booking_id, $a_booking_values)
	{
		//deleting the old appointment first
		$this->deleteCalendarEntryOfBooking($a_booking_id);

		//creating a new one
		$this->insertBookingAppointment($a_booking_id, $a_booking_values);
	}

	/*
	 * Creates an appointment in the RoomSharing-Calendar and save id in booking-table.
	 *
	 * @param $title string appointment-title
	 * @param $time_start start-time
	 * @param $time_end end-time
	 */
	public function insertBookingAppointment($insertedId, $a_booking_values, $from, $to)
	{
		//create appointment first
		include_once('Services/Calendar/classes/class.ilDate.php');
		$time_start = new ilDateTime($from, 1);
		$time_end = new ilDateTime($to, 1);
		$title = $a_booking_values['subject'];

		$room_name = $this->ilRoomSharingDatabase->getRoomName($a_booking_values ['room']);

		$cal_cat_id = $a_booking_values['cal_id'];

		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		//use original ilCalendarEntry and let ILIAS do the work
		$app = new ilCalendarEntry();
		$app->setStart($time_start);
		$app->setEnd($time_end);
		$app->setFullday(false);
		$app->setTitle($title);
		$app->setDescription($a_booking_values ['comment']);
		$app->setAutoGenerated(true);
		$app->enableNotification(false);
		$app->setLocation($room_name);
		$app->validate();
		$app->save();

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$ass = new ilCalendarCategoryAssignments($app->getEntryId());
		$ass->addAssignment($cal_cat_id);

		//update bookings-table afterwards
		$this->ilDB->manipulate('UPDATE ' . dbc::BOOKINGS_TABLE .
			' SET calendar_entry_id = ' . $this->ilDB->quote($app->getEntryId(), 'integer') .
			' WHERE id = ' . $this->ilDB->quote($insertedId, 'integer') .
			' AND pool_id =' . $this->ilDB->quote($this->pool_id, 'integer'));
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
