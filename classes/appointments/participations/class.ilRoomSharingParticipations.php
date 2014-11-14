<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingDateUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingBookingUtils.php");

/**
 * Class ilRoomSharingParticipations
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @property ilUser $ilUser
 */
class ilRoomSharingParticipations
{
	private $pool_id;
	protected $ilRoomsharingDatabase;
	private $ilUser;

	/**
	 * Construct of ilRoomSharingParticipations.
	 *
	 * @param integer $pool_id
	 */
	function __construct($pool_id = 1)
	{
		global $ilUser;
		$this->ilUser = $ilUser;
		$this->pool_id = $pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Remove a participation.
	 *
	 * @param integer $booking_id The booking id of the participation.
	 * @global type $lng, $ilUser
	 */
	public function removeParticipation($a_booking_id)
	{
		global $lng;

		if (ilRoomSharingNumericUtils::isPositiveNumber($a_booking_id))
		{
			$this->ilRoomsharingDatabase->deleteParticipation($this->ilUser->getId(), $a_booking_id);
		}
		else
		{
			ilUtil::sendFailure($lng->txt("rep_robj_xrs_no_id_submitted"), true);
		}
	}

	/**
	 * Get the participations from the database.
	 *
	 * @global type $ilUser
	 * @return array with the participation details.
	 */
	public function getList()
	{
		$participations = $this->ilRoomsharingDatabase->getParticipationsForUser($this->ilUser->getId());
		$result = array();
		foreach ($participations as $participation)
		{
			$bookingDatas = $this->ilRoomsharingDatabase->getBooking($participation['booking_id']);
			foreach ($bookingDatas as $bookingData)
			{
				$result[] = $this->readBookingData($bookingData);
			}
		}
		return $result;
	}

	/**
	 * Reads a booking
	 *
	 * @param array $a_bookingData
	 * @param integer $a_participation_id
	 * @return array Booking-Information
	 */
	private function readBookingData($a_bookingData)
	{
		$one_booking = array();
		$one_booking['recurrence'] = ilRoomSharingNumericUtils::isPositiveNumber($a_bookingData['seq_id']);

		$one_booking['date'] = ilRoomSharingBookingUtils::readBookingDate($a_bookingData);

		// Get the name of the booked room
		$one_booking['room'] = $this->ilRoomsharingDatabase->getRoomName($a_bookingData['room_id']);
		$one_booking['room_id'] = $a_bookingData['room_id'];

		$one_booking['subject'] = $a_bookingData['subject'];

		$one_booking['person_responsible'] = $this->readBookingResponsiblePerson($a_bookingData['user_id']);
		$one_booking['person_responsible_id'] = $a_bookingData['user_id'];

		// The booking id
		//$one_booking['id'] = $a_participation_id;
		$one_booking['booking_id'] = $a_bookingData['id'];
		return $one_booking;
	}

	/**
	 * Reads the data of the responsible person
	 *
	 * @param integer $a_user_id
	 * @return string Login-Name or Fullname (if exists)
	 */
	private function readBookingResponsiblePerson($a_user_id)
	{
		$userData = $this->ilRoomsharingDatabase->getUserById($a_user_id);

		// Check whether the user has a firstname and a lastname
		if (!empty($userData['firstname']) && !empty($userData['lastname']))
		{
			$result = $userData['firstname'] .
				' ' . $userData['lastname'];
		} // ...if not, use the username
		else
		{
			$result = $userData['login'];
		}
		return $result;
	}

	/**
	 * Returns all the additional information that can be displayed in the
	 * bookings table.
	 *
	 * @return array (associative) with additional information.
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
	 * Returns roomsharing pool id.
	 *
	 * @return int pool id
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer $a_pool_id current pool id.
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}
