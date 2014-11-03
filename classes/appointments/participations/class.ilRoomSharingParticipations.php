<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingDateUtils.php");

/**
 * Class ilRoomSharingParticipations
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingParticipations
{
    private $pool_id;
    protected $ilRoomsharingDatabase;

    /**
     * Construct of ilRoomSharingParticipations.
     *
     * @param integer $pool_id
     */
    function __construct($pool_id = 1)
    {
        $this->pool_id = $pool_id;
        $this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
    }

    /**
     * Remove a participation.
     *
     * @param integer $booking_id The booking id of the participation.
     * @global type $ilDB, $ilUser
     */
    public function removeParticipation($a_booking_id)
    {
        global $ilUser, $lng;

        if (ilRoomSharingNumericUtils::isPositiveNumber($a_booking_id))
        {
            $this->ilRoomsharingDatabase->deleteParticipation($ilUser->getId(), $a_booking_id);
        }
        else
        {
            ilUtil::sendFailure($lng->txt("rep_robj_xrs_no_id_submitted"), true);
        }
    }

    /**
     * Get the participations from the database.
     *
     * @global type $ilDB, $ilUser, $lng
     * @return array with the participation details.
     */
    public function getList()
    {
        global $ilDB, $ilUser, $lng;

        $set = $this->ilRoomsharingDatabase->getParticipationsForUser($ilUser->getId());
        $res = array();
        while ($row = $ilDB->fetchAssoc($set))
        {
            $one_booking = array();
            $bookingSet = $this->ilRoomsharingDatabase->getBooking($row['booking_id']);
            while ($bookingRow = $ilDB->fetchAssoc($bookingSet))
            {
                if (ilRoomSharingNumericUtils::isPositiveNumber($bookingRow['seq_id']))
                {
                    $one_booking['recurrence'] = true;
                }

                $date_from = DateTime::createFromFormat("Y-m-d H:i:s", $bookingRow['date_from']);
                $date_to = DateTime::createFromFormat("Y-m-d H:i:s", $bookingRow['date_to']);

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

                $one_booking['date'] = $date;

                // Get the name of the booked room
                $one_booking['room'] = $this->ilRoomsharingDatabase->getRoomName($bookingRow['room_id']);
                $one_booking['room_id'] = $bookingRow['room_id'];

                $one_booking['subject'] = $bookingRow['subject'];

                $userSet = $this->ilRoomsharingDatabase->getUserById($bookingRow['user_id']);
                $userRow = $ilDB->fetchAssoc($userSet);

                // Check whether the user has a firstname and a lastname
                if (empty($userRow['firstname']) && empty($userRow['lastname']))
                {
                    $one_booking['person_responsible'] = $userRow['firstname'] .
                        ' ' . $userRow['lastname'];
                } // ...if not, use the username
                else
                {
                    $one_booking['person_responsible'] = $userRow['login'];
                }
                $one_booking['person_responsible_id'] = $bookingRow['user_id'];

                // The booking id
                $one_booking['id'] = $row['id'];

                $res[] = $one_booking;
            }
        }

        // Dummy-Daten
        $res[] = array(
            'recurrence' => true,
            'date' => "3. März 2014, 11:30 - 15:00",
            'modul' => "COMARCH",
            'subject' => "HARDKODIERT Vorlesung",
            'kurs' => "Technische Informatik (TI Bsc.)",
            'semester' => "4, 6",
            'room' => "116",
            'person_responsible' => "Prof. Dr. Thomas Risse"
        );

        return $res;
    }

    /**
     * Returns all the additional information that can be displayed in the
     * bookings table.
     *
     * @return array (associative) with additional information.
     */
    public function getAdditionalBookingInfos()
    {
        global $ilDB;
        $cols = array();
        $attributesSet = $this->ilRoomsharingDatabase->getAllBookingAttributes();
        while ($attributesRow = $ilDB->fetchAssoc($attributesSet))
        {
            $cols[$attributesRow['name']] = array(
                "txt" => $attributesRow['name']
            );
        }

        // Dummy-Data
        $cols["Modul"] = array(
            "txt" => "Modul"
        );
        $cols["Kurs"] = array(
            "txt" => "Kurs"
        );
        $cols["Semester"] = array(
            "txt" => "Semester"
        );

        return $cols;
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