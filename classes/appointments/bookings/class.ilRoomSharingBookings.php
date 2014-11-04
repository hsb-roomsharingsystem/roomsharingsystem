<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingDateUtils.php");

/**
 * Class ilRoomSharingBookings
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @version $Id$
 */
class ilRoomSharingBookings
{
	protected $pool_id;
	protected $ilRoomsharingDatabase;

	/**
	 * constructor ilRoomSharingBookings
	 *
	 * @param integer $pool_id
	 */
	function __construct($pool_id = 1)
	{
		$this->pool_id = $pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Remove a booking
	 *
	 * @param int $booking_id
	 *        	The id of the booking
	 * @param bool $seq
	 *        	True if the all sequence bookings should be deleted
	 * @global type $ilUser
	 */
	public function removeBooking($a_booking_id, $a_seq = false)
	{
		global $ilUser, $lng;

		if (ilRoomSharingNumericUtils::isPositiveNumber($a_booking_id))
		{
			$row = $this->ilRoomsharingDatabase->getSequenceAndUserForBooking($a_booking_id);

			// Check if there is a result (so the booking with the ID exists)
			if ($row != NULL)
			{
				// Check if the current user is the author of the booking
				if ($row ['user_id'] == $ilUser->getId())
				{
					// Check whether only the specific booking should be deleted
					if (!$a_seq || $row ['seq_id'] == NULL || !is_numeric($row ['seq_id']))
					{
						$this->ilRoomsharingDatabase->deleteBooking($a_booking_id);
						ilUtil::sendSuccess($lng->txt('rep_robj_xrs_booking_deleted'), true);
					}
					else //delete every booking in the sequence
					{
						// Get every booking which is in the specific sequence
						$booking_ids = $this->ilRoomsharingDatabase->getAllBookingIdsForSequence($row ['seq']);
						foreach ($booking_ids as $booking_id)
						{
							$this->ilRoomsharingDatabase->deleteBooking($booking_id);
							ilUtil::sendSuccess($lng->txt('rep_robj_xrs_booking_sequence_deleted'), true);
						}
					}
				}
				else
				{
					ilUtil::sendFailure($lng->txt("rep_robj_xrs_no_delete_permission"), true);
				}
			}
			else
			{
				ilUtil::sendFailure($lng->txt("rep_robj_xrs_booking_doesnt_exist"), true);
			}
		}
		else
		{
			ilUtil::sendFailure($lng->txt("rep_robj_xrs_no_id_submitted"), true);
		}
	}

	/**
	 * Get's the bookings from the database
	 *
	 * @global type $ilUser
	 * @return type
	 */
	public function getList()
	{
		global $ilUser;
		$all_bookings = $this->ilRoomsharingDatabase->getBookingsForUser($ilUser->getId());
		$res = array();
		foreach ($all_bookings as $row)
		{
			$one_booking = array();
			if (ilRoomSharingNumericUtils::isPositiveNumber($row ['seq_id'])) // Is it a recurring appointment?
			{
				$one_booking ['recurrence'] = true;
			}
			else
			{
				$date_from = DateTime::createFromFormat("Y-m-d H:i:s", $row ['date_from']);
				$date_to = DateTime::createFromFormat("Y-m-d H:i:s", $row ['date_to']);

				$date = ilRoomSharingDateUtils::getPrintedDateTime($date_from);

				$date .= " - ";

				// Check whether the date_from differs from the date_to
				if (ilRoomSharingDateUtils::isEqualDay($date_from, $date_to))
				{
					//Display the date_to in the next line
					$date .= '<br>';

					$date .= ilRoomSharingDateUtils::getPrintedDate($date_to);

					$date .= ', ';
				}
				$date .= ilRoomSharingDateUtils::getPrintedTime($date_to);
			}
			$one_booking ['date'] = $date;

			// Get the name of the booked room
			$one_booking ['room'] = $this->ilRoomsharingDatabase->getRoomName($row ['room_id']);
			$one_booking ['room_id'] = $row ['room_id'];
			$participants = array();
			$participants_ids = array();

			// Get the participants
			$participantsRows = $this->ilRoomsharingDatabase->getParticipantsForBooking($row ['id']);
			foreach ($participantsRows as $participantRow)
			{// Check if the user has a firstname and lastname
				if (empty($userRow ['firstname']) || empty($userRow ['lastname']))
				{
					$participants [] = $participantRow ['firstname'] . ' ' . $participantRow ['lastname'];
				}
				else // ...if not, use the username
				{
					$participants [] = $participantRow ['login'];
				}
				$participants_ids [] = $participantRow ['id'];
			}
			$one_booking ['participants'] = $participants;
			$one_booking ['participants_ids'] = $participants_ids;
			$one_booking ['subject'] = $row ['subject'];

			// Get variable attributes of a booking
			$attributes = $this->ilRoomsharingDatabase->getAttributesForBooking($row ['id']);
			foreach ($attributes as $attributesRow)
			{
				$one_booking [$attributesRow ['name']] = $attributesRow ['value'];
			}

			// The booking id
			$one_booking ['id'] = $row ['id'];
			$res [] = $one_booking;
		}

		// Dummy-Data
		$res [] = array(
			'recurrence' => true, 'date' => "7. März 2014, 9:00 - 13:00",
			'id' => 1, 'room' => "117", 'room_id' => 3,
			'subject' => "HARDKODIERT Tutorium",
			'participants' => array("Tim Lehr", "Philipp Hörmann"),
			'participants_ids' => array("6"),
			'Modul' => "MATHE2",
			'Kurs' => "Technische Informatik (TI Bsc.)"
		);

		$res [] = array(
			'recurrence' => false, 'date' => "3. April 2014, 15:00 - 17:00",
			'id' => 2, 'room' => "118", 'room_id' => 4,
			'subject' => "HARDKODIERT Vorbereitung Präsentation",
			'Semester' => "6"
		);
		return $res;
	}

	/**
	 * Returns all the additional information that can be displayed in the
	 * bookings table.
	 *
	 * @return array $cols
	 */
	public function getAdditionalBookingInfos()
	{
		$cols = $this->ilRoomsharingDatabase->getAllBookingAttributes();

		// Dummy-Data
		$cols ["Modul"] = array(
			"txt" => "Modul",
			"id" => 1
		);
		$cols ["Kurs"] = array(
			"txt" => "Kurs",
			"id" => 2
		);
		$cols ["Semester"] = array(
			"txt" => "Semester",
			"id" => 3
		);

		return $cols;
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
