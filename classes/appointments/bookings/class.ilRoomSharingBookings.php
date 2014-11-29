<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingDateUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingBookingsException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingBookingUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");

/**
 * Class ilRoomSharingBookings
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 * @property ilDB $ilDB
 * @property ilUser $ilUser
 * @property ilLanguage $lng
 */
class ilRoomSharingBookings
{
	protected $pool_id;
	protected $ilRoomsharingDatabase;
	private $ilDB;
	private $ilUser;
	private $lng;

	/**
	 * constructor ilRoomSharingBookings
	 *
	 * @param integer $pool_id
	 */
	function __construct($pool_id = 1)
	{
		global $ilDB, $ilUser, $lng;
		$this->ilDB = $ilDB;
		$this->ilUser = $ilUser;
		$this->lng = $lng;
		$this->pool_id = $pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Remove a booking
	 *
	 * @param int $a_booking_id
	 *        	The id of the booking
	 * @param bool $a_seq
	 *        	True if the all sequence bookings should be deleted
	 * @global ilLanguage $lng
	 */
	public function removeBooking($a_booking_id, $a_seq = false)
	{
		$this->checkBookingId($a_booking_id);
		$row = $this->ilRoomsharingDatabase->getSequenceAndUserForBooking($a_booking_id);
		$booking_details = $this->ilRoomsharingDatabase->getBooking($a_booking_id);
		$participants = $this->ilRoomsharingDatabase->getParticipantsForBookingShort($a_booking_id);


		$this->checkResultNotEmpty($row);
		$this->checkDeletePermission($row ['user_id']);

		// Check whether only the specific booking should be deleted
		if (!$a_seq || ilRoomSharingNumericUtils::isPositiveNumber($row ['seq_id']))
		{
			$this->ilRoomsharingDatabase->deleteCalendarEntryOfBooking($a_booking_id);
			$this->ilRoomsharingDatabase->deleteBooking($a_booking_id);
			ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_bookings_deleted'), true);
		}
		else //delete every booking in the sequence
		{
			$this->deleteBookingSequence($row['seq']);
			ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_booking_sequence_deleted'), true);
		}
		$this->sendCancellationNotification($booking_details, $participants);
	}

	/**
	 * Removes muliple Bookings from the Database. Accepts only legal ids that are greater or equal 1 and exists as booking ID.
         * Sends all participants a cancellation notice.
	 * @param array $a_booking_ids nummerical array of booking_ids to delete
	 */
	public function removeMultipleBookings(array $a_booking_ids)
	{
		foreach ($a_booking_ids as $booking_id)
		{
			$this->checkBookingId($booking_id);
                        $booking_details = $this->ilRoomsharingDatabase->getBooking($booking_id);
                        $participants = $this->ilRoomsharingDatabase->getParticipantsForBookingShort($booking_id);
                        $this->sendCancellationNotification($booking_details, $participants); 
		}
		$this->ilRoomsharingDatabase->deleteCalendarEntriesOfBookings($a_booking_ids);
		$this->ilRoomsharingDatabase->deleteBookings($a_booking_ids);
		ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_booking_deleted'), true);
	}

	/**
	 * Checks the permission of this user to delete this booking
	 *
	 * @param integer $a_userId
	 * @throws ilRoomSharingBookingsException
	 */
	private function checkDeletePermission($a_userId)
	{
		if ($a_userId != $this->ilUser->getId())
		{
			throw new ilRoomSharingBookingsException("rep_robj_xrs_no_delete_permission");
		}
	}

	/**
	 * Checks if a resultset has results
	 *
	 * @param array $a_row
	 * @throws ilRoomSharingBookingsException
	 */
	private function checkResultNotEmpty($a_row)
	{
		if (!ilRoomSharingNumericUtils::isPositiveNumber(count($a_row)))
		{
			throw new ilRoomSharingBookingsException("rep_robj_xrs_booking_doesnt_exist");
		}
	}

	/**
	 * Checks if the booking id is valid
	 *
	 * @param integer $a_booking_id
	 * @throws ilRoomSharingBookingsException
	 */
	private function checkBookingId($a_booking_id)
	{
		if (!ilRoomSharingNumericUtils::isPositiveNumber($a_booking_id))
		{
			throw new ilRoomSharingBookingsException("rep_robj_xrs_no_id_submitted");
		}
	}

	/**
	 * Deletes all bookings of the given sequence-id
	 *
	 * @param integer $a_seq_id
	 */
	private function deleteBookingSequence($a_seq_id)
	{
		$seq_rows = $this->ilRoomsharingDatabase->getAllBookingIdsForSequence($a_seq_id);
		foreach ($seq_rows as $seq_row)
		{
			$this->ilRoomsharingDatabase->deleteBooking($seq_row['id']);
		}
	}

	/**
	 * Gets the bookings from the database.
	 *
	 * @return array with bookings
	 */
	public function getList()
	{
		$bookingDatas = $this->ilRoomsharingDatabase->getBookingsForUser($this->ilUser->getId());
		$allBookings = array();
		foreach ($bookingDatas as $bookingData)
		{
			$allBookings [] = $this->readBookingData($bookingData);
		}
		return $allBookings;
	}

	/**
	 * Reads a booking
	 *
	 * @param array $a_bookingData
	 * @return array Booking-Information
	 */
	private function readBookingData($a_bookingData)
	{
		$one_booking = array();

		$one_booking ['recurrence'] = ilRoomSharingNumericUtils::isPositiveNumber($a_bookingData ['seq_id']);
		$one_booking ['date'] = ilRoomSharingBookingUtils::readBookingDate($a_bookingData);

		$one_booking ['room'] = $this->ilRoomsharingDatabase->getRoomName($a_bookingData ['room_id']);
		$one_booking ['room_id'] = $a_bookingData ['room_id'];

		$participants = $this->readBookingParticipants($a_bookingData);

		$one_booking ['participants'] = $participants['names'];
		$one_booking ['participants_ids'] = $participants['ids'];

		$one_booking ['subject'] = $a_bookingData ['subject'];
		$one_booking ['comment'] = $a_bookingData ['bookingcomment'];

		$attributes = $this->readBookingAttributes($a_bookingData);
		foreach ($attributes as $attribute_name => $attribute_value)
		{
			$one_booking[$attribute_name] = $attribute_value;
		}

		$one_booking ['id'] = $a_bookingData ['id'];
		return $one_booking;
	}

	/**
	 * Reads attributes of a booking
	 *
	 * @param array $a_bookingData
	 * @return array with attributes
	 */
	private function readBookingAttributes($a_bookingData)
	{
		$attributes = array();

		$attributesRows = $this->ilRoomsharingDatabase->getAttributesForBooking($a_bookingData ['id']);
		foreach ($attributesRows as $attributeRow)
		{
			$attributes [$attributeRow ['name']] = $attributeRow ['value'];
		}

		return $attributes;
	}

	/**
	 * 	Reads the participants (names and ids) for a booking
	 *
	 * @param array $a_bookingData
	 * @return array with names and ids
	 */
	private function readBookingParticipants($a_bookingData)
	{
		$participantsData = array();

		$participantRows = $this->ilRoomsharingDatabase->getParticipantsForBooking($a_bookingData ['id']);
		foreach ($participantRows as $participantRow)
		{
			// Check if the user has a firstname and lastname
			if (!empty($participantRow ['firstname']) && !empty($participantRow ['lastname']))
			{
				$participants [] = $participantRow ['firstname'] . ' ' . $participantRow ['lastname'];
			}
			else // ...if not, use the username
			{
				$participants [] = $participantRow ['login'];
			}
			$participants_ids [] = $participantRow ['id'];
		}

		$participantsData ['names'] = $participants;
		$participantsData ['ids'] = $participants_ids;
		return $participantsData;
	}

	/**
	 * Returns all the additional information that can be displayed in the
	 * bookings table.
	 *
	 * @return array $cols
	 */
	public function getAdditionalBookingInfos()
	{
		$attributes = array();
		$attributesRows = $this->ilRoomsharingDatabase->getAllBookingAttributes();

		foreach ($attributesRows as $attributesRow)
		{
			$attributes [$attributesRow ['name']] = array(
				"txt" => $attributesRow ['name'],
				"id" => $attributesRow ['id']
			);
		}
		return $attributes;
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

	/**
	 * Send cancellation email.
	 */
	public function sendCancellationNotification($booking_details, $participants)
	{
		$room_id = $booking_details[0]['room_id'];
		$room_name = $this->ilRoomsharingDatabase->getRoomName($room_id);
		$user_id = $booking_details[0]['user_id'];
		$date_from = $booking_details[0]['date_from'];
		$date_to = $booking_details[0]['date_to'];

		$mailer = new ilRoomSharingMailer($this->lng);
		$mailer->setRoomname($room_name);
		$mailer->setDateStart($date_from);
		$mailer->setDateEnd($date_to);
		$mailer->sendCancellationMail($user_id, $participants);
	}

}
