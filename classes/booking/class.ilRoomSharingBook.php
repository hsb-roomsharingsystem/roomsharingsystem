<?php
include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingMailer.php");


/**
 * Backend-Class for booking-mask
 * @author Robert Heimsoth
 */
class ilRoomSharingBook
{
	protected $pool_id;
	private $ilRoomsharingDatabase;
	private $date_from;
	private $date_to;
	private $room_id;

	const BOOKING_IN_THE_PAST = - 4;
	const INVALID_DATE_CONDITION = - 3;
	const ROOM_ALREADY_BOOKED = - 2;

	/**
	 * Method to add a new booking into the database
	 *
	 * @param array $booking_values
	 *        	Array with the values of the booking
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @param $ilRoomSharingRooms Object of ilRoomSharingRooms
	 * @return type
	 */
	public function addBooking($booking_values, $booking_attr_values, $ilRoomSharingRooms)
	{
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
		$this->date_from = $booking_values ['from'] ['date'] . " " . $booking_values ['from'] ['time'];
		$this->date_to = $booking_values ['to'] ['date'] . " " . $booking_values ['to'] ['time'];
		$this->room_id = $booking_values ['room'];

		if ($this->isBookingInPast())
		{
			return self::BOOKING_IN_THE_PAST;
		}
		if ($this->checkForInvalidDateConditions())
		{
			return self::INVALID_DATE_CONDITION;
		}
		if ($this->isAlreadyBooked())
		{
			return self::ROOM_ALREADY_BOOKED;
		}
                $status = $this->insertBooking($booking_attr_values, $booking_values);
                if ($status)
                {
                    $this->sendAcknowledment();
                }
		return $status;
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
	 * @param $ilRoomSharingRooms Object of ilRoomSharingRooms
	 */
	private function isAlreadyBooked()
	{
		$temp = $this->ilRoomsharingDatabase->getRoomsBookedInDateTimeRange($this->date_from,
			$this->date_to, $this->room_id);
		return ($temp !== array());
	}

	/**
	 * Method to insert the booking
	 *
	 * @param array $booking_attr_values
	 *        	Array with the values of the booking-attributes
	 * @return type -1 failed insert, 1 successful insert
	 */
	private function insertBooking($booking_attr_values, $booking_values)
	{
		return $this->ilRoomsharingDatabase->insertBooking($booking_attr_values, $booking_values);
	}

	/**
	 * Sets the pool-id
	 *
	 * @param integer $pool_id
	 *        	The pool id which should be set
	 */
	public function setPoolId($pool_id)
	{
		$this->pool_id = $pool_id;
	}
        
        
        /**
         * Generate a booking acknowledgement via mail.
         * 
         * @return type -1 failed to send mail, 1 success
         * 
         *
         */
        private function sendAcknowledment()
        {

            global $lng, $ilUser;
            
            $mailer = new ilRoomSharingMailer();
            $mailer->setRawSubject($this->lng->txt('rep_robj_xrs_mail_booking_creator_subject'));
	    $mailer->setRawMessage($this->lng->txt('rep_robj_xrs_mail_booking_creator_message'));
            $mailer->sendMail(array($ilUser->getId()));
            
        }

}
