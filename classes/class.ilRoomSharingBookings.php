<?php

include_once 'Database/class.ilRoomSharingDatabase.php';

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
     * @global type $ilDB, $ilUser
     */
    public function removeBooking($a_booking_id, $a_seq = false)
    {
	global $ilDB, $ilUser, $lng;

	if (!empty($a_booking_id) && is_numeric($a_booking_id))
	{
	    $set = $this->ilRoomsharingDatabase->getSequenceAndUserForBooking($a_booking_id);
	    $row = $ilDB->fetchAssoc($set);

	    // Check if there is a result (so the booking with the ID exists)
	    if ($ilDB->numRows($set) > 0)
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
			$seq_set = $this->ilRoomsharingDatabase->getAllBookingIdsForSequence($row ['seq']);
			while ($seq_row = $ilDB->fetchAssoc($seq_set))
			{
			    $this->ilRoomsharingDatabase->deleteBooking($a_booking_id);
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
     * @global type $ilDB, $ilUser, $lng
     * @return type
     */
    public function getList()
    {
	global $ilDB, $ilUser, $lng;
	$set = $this->ilRoomsharingDatabase->getBookingsForUser($ilUser->getId());
	$res = array();
	while ($row = $ilDB->fetchAssoc($set))
	{
	    $one_booking = array();
	    if (is_numeric($row ['seq_id'])) // Is it a recurring appointment?
	    {
		$one_booking ['recurrence'] = true;
	    }
	    else
	    {
		$date_from = DateTime::createFromFormat("Y-m-d H:i:s", $row ['date_from']);
		$date_to = DateTime::createFromFormat("Y-m-d H:i:s", $row ['date_to']);
		$date = $date_from->format('d') . '. ' . $lng->txt('month_' .
				$date_from->format('m') . '_short') . ' ' .
			$date_from->format('Y') . ', ' . $date_from->format('H:i');
		$date .= " - ";
		// Check whether the date_from differs from the date_to
		if ($date_from->format('dmY') !== $date_to->format('dmY'))
		{
		    $date .= '<br>' . $date_to->format('d') . '. ' .
			    $lng->txt('month_' . $date_to->format('m') . '_short') . ' ' .
			    $date_to->format('Y') . ', ';
		}
		$date .= $date_to->format('H:i');
	    }
	    $one_booking ['date'] = $date;

	    // Get the name of the booked room
	    $one_booking ['room'] = $this->ilRoomsharingDatabase->getRoomName($row ['room_id']);
	    $one_booking ['room_id'] = $row ['room_id'];
	    $participants = array();
	    $participants_ids = array();

	    // Get the participants
	    $participantSet = $this->ilRoomsharingDatabase->getParticipantsForBooking($row ['id']);
	    while ($participantRow = $ilDB->fetchAssoc($participantSet))
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
	    $one_booking ['participants_id'] = $participants_ids;
	    $one_booking ['subject'] = $row ['subject'];

	    // Get variable attributes of a booking
	    $attributesSet = $this->ilRoomsharingDatabase->getAttributesForBooking($row ['id']);
	    while ($attributesRow = $ilDB->fetchAssoc($attributesSet))
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
	global $ilDB;
	$cols = array();
	$attributesSet = $this->ilRoomsharingDatabase->getAllBookingAttributes();
	while ($attributesRow = $ilDB->fetchAssoc($attributesSet))
	{
	    $cols [$attributesRow ['name']] = array(
		"txt" => $attributesRow ['name'],
		"id" => $attributesRow ['id']
	    );
	}

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
