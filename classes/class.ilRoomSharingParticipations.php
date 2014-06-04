<?php

/**
 * Class ilRoomSharingParticipations
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 */
class ilRoomSharingParticipations
{
    /**
     * Remove a participation
     * @param type $booking_id The booking id of the participation
     * @global type $ilDB, $ilUser
     */
    public function removeParticipation($a_booking_id) 
    {
        global $ilDB, $ilUser;
        
        if(!empty($a_booking_id) && is_numeric($a_booking_id)) 
        {
            $ilDB->query('DELETE FROM rep_robj_xrs_book_user'.
                    ' WHERE user_id = '.$ilDB->quote($ilUser->getId(), 'integer').
                    ' AND booking_id = '.$ilDB->quote($a_booking_id, 'integer'));
        }
        else
        {
            ilUtil::sendFailure($lng->txt("Keine oder nicht numerische ID angegeben!"), true);
        }
    }
    
    /**
     * Get the participations from the database
     * @global type $ilDB, $ilUser, $lng
     * @return type array with the participation-details
     */
    public function getList()
    {
        global $ilDB, $ilUser, $lng;

		$set = $ilDB->query('SELECT booking_id'.
                        ' FROM rep_robj_xrs_book_user'.
                        ' WHERE user_id = '.$ilDB->quote($ilUser->getId(), 'integer'));
        $res = array();
        while($row = $ilDB->fetchAssoc($set))
        {
            $one_booking = array();
            $bookingSet = $ilDB->query('SELECT *'.
                        ' FROM rep_robj_xrs_bookings'.
                        ' WHERE id = '.$ilDB->quote($row['booking_id'], 'integer').
                        //Eigentlich gibt es bei MySQL hierfür "NOW()",
                        //aber das würde halt nicht für etwa ORACLE gelten.
                        ' AND (date_from >= "'.date('Y-m-d H:i:s').'"'.
                        ' OR date_to >= "'.date('Y-m-d H:i:s').'"'.
                        ' OR seq_id IS NOT NULL)'.
                        ' ORDER BY date_from ASC');
            while($bookingRow = $ilDB->fetchAssoc($bookingSet)) 
            {
                if(is_numeric($bookingRow['seq_id'])) 
                {
                    $one_booking['recurrence'] = true;
                } 
                else 
                {
                    $date_from = DateTime::createFromFormat("Y-m-d H:i:s", $bookingRow['date_from']);
                    $date_to = DateTime::createFromFormat("Y-m-d H:i:s", $bookingRow['date_to']);
                    $date = '<br>'.$date_from->format('d').'.'.
                            ' '.$lng->txt('month_'.$date_from->format('m').'_short').
                            ' '.$date_from->format('Y').','.
                            ' '.$date_from->format('H:i');
                    $date .= " - ";

                    //Check whether the date_from differs from the date_to
                    if($date_from->format('dmY') !== $date_to->format('dmY'))
                    {
                       $date .= $date_to->format('d').'.'.
                            ' '.$lng->txt('month_'.$date_to->format('m').'_short').
                            ' '.$date_to->format('Y').', ';
                    }

                    $date .= $date_to->format('H:i');
                }

                $one_booking['date'] = $date;

                //Get the name of the booked room
                $roomSet = $ilDB->query('SELECT name FROM rep_robj_xrs_rooms'.
                            ' WHERE id = '.$ilDB->quote($bookingRow['room_id'], 'integer'));
                $roomRow = $ilDB->fetchAssoc($roomSet);
                $one_booking['room'] = $roomRow['name'];
                $one_booking['room_id'] = $bookingRow['room_id'];

                $one_booking['subject'] = $bookingRow['subject'];

                $userSet = $ilDB->query('SELECT firstname, lastname, login'.
                        ' FROM usr_data'.
                        ' WHERE usr_id = '.$ilDB->quote($bookingRow['user_id'], 'integer'));
                $userRow = $ilDB->fetchAssoc($userSet);
                //Check if the user has a firstname and lastname
                if(empty($userRow['firstname']) || empty($userRow['lastname'])) 
                {
                    $one_booking['person_responsible'] = $userRow['firstname'].' '.
                                                        $userRow['lastname'];
                }
                //...if not, use the username
                else 
                {
                    $one_booking['person_responsible'] = $userRow['login'];
                }
                $one_booking['person_responsible_id'] = $bookingRow['user_id'];
                
                //The booking id
                $one_booking['id'] = $row['id'];
                
                $res[] = $one_booking;
            }
        }
	
        // Dummy-Daten
        /*$res[] =  array('recurrence' => true, 
                      'date'   => "3. März 2014, 11:30 - 15:00", 
                      'module'  => "COMARCH",
                      'subject' => "Vorlesung",
                      'course' => "Technische Informatik (TI Bsc.)",
                      'semester' => "4, 6",
                      'room' => "116",
                      'person_responsible' => "Prof. Dr. Thomas Risse");*/
        
        return $res;
    }
}
