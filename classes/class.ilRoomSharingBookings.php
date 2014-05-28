<?php

/**
 * Class ilRoomSharingBookings
 *
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
 */
class ilRoomSharingBookings
{   
    /**
     * Remove a booking
     * @param int $booking_id The id of the booking
     * @param bool $seq True if the all sequence bookings should be deleted
     * @global type $ilDB, $ilUser
     */
    /*
     * TODO: Error handling fehlt noch, Fehler werden "ignoriert"
     */
    public function removeBooking($booking_id, $seq = false) 
    {
        global $ilDB, $ilUser;
        
        if(!empty($id) && is_numeric($id)) 
        {
            $set = $ilDB->query('SELECT seq, user_id'.
                    ' FROM rep_robj_xrs_bookings'.
                    ' AND id = '.$ilDB->quote($booking_id, 'integer'));
            $row = $ilDB->fetchAssoc($set);
            
            //Check if the current user is the author of the booking
            if($row['user_id'] == $ilUser->getId()) 
            {
                //Check whether only the specific booking should be deleted
                if(!$seq || $row['seq'] == NULL || !is_numeric($row['seq'])) 
                {
                    $ilDB->query('DELETE FROM rep_robj_xrs_bookings'.
                            ' WHERE id = '.$ilDB->quote($booking_id, 'integer'));
                    $ilDB->query('DELETE FROM rep_robj_xrs_book_user'.
                            ' WHERE booking_id = '.$ilDB->quote($booking_id, 'integer'));
                } 
                //else delete every booking in the sequence
                else 
                {
                    //Get every booking which is in the specific sequence
                    $seq_set = $ilDB->query('SELECT id FROM rep_robj_xrs_bookings'.
                            ' WHERE seq = '.$ilDB->quote($row['seq'], 'integer').
                            ' AND pool_id = '.$ilDB->quote(1, 'integer'));
                    while($seq_row = $ilDB->fetchAssoc($seq_set)) 
                    {
                        //Delete the booking, which is part of the sequence
                        $ilDB->query('DELETE FROM rep_robj_xrs_bookings'.
                            ' WHERE id = '.$ilDB->quote($seq_row['id'], 'integer'));
                        $ilDB->query('DELETE FROM rep_robj_xrs_book_user'.
                            ' WHERE booking_id = '.$ilDB->quote($seq_row['id'], 'integer'));
                    }
                }
            }
        }
    }
    
    /**
     * Get's the bookings from the database
     * @global type $ilDB
     * @return type
     */
    public function getList()
    {
        global $ilDB, $ilUser, $lng;
        
        $set = $ilDB->query('SELECT *'.
                        ' FROM rep_robj_xrs_bookings'.
                        ' WHERE pool_id = '.$ilDB->quote(1, 'integer').
                        ' AND user_id = '.$ilDB->quote($ilUser->getId(), 'integer').
                        ' AND (date_from >= "'.date('Y-m-d H:i:s').'"'.
                        ' OR date_to >= "'.date('Y-m-d H:i:s').'"'.
                        ' OR seq_id IS NOT NULL)'.
                        ' ORDER BY date_from ASC');
        $res = array();
        while($row = $ilDB->fetchAssoc($set))
        {
            $one_booking = array();
            //Is it a recurring appointment?
            if(is_numeric($row['seq_id'])) 
            {
                $one_booking['recurrence'] = true;
            } 
            else 
            {
                $date_from = DateTime::createFromFormat("Y-m-d H:i:s", $row['date_from']);
                $date_to = DateTime::createFromFormat("Y-m-d H:i:s", $row['date_to']);
                $date = $date_from->format('d').'.'.
                        ' '.$lng->txt('month_'.$date_from->format('m').'_short').
                        ' '.$date_from->format('Y').','.
                        ' '.$date_from->format('H:i');
                $date .= " - ";
                
                //Check whether the date_from differs from the date_to
                if($date_from->format('dmY') !== $date_to->format('dmY'))
                {
                   $date .= '<br>'.$date_to->format('d').'.'.
                        ' '.$lng->txt('month_'.$date_to->format('m').'_short').
                        ' '.$date_to->format('Y').', ';
                }
                
                $date .= $date_to->format('H:i');
            }
            
            $one_booking['date'] = $date;
            
            //Get the name of the booked room
            $roomSet = $ilDB->query('SELECT name FROM rep_robj_xrs_rooms'.
                        ' WHERE id = '.$ilDB->quote($row['room_id'], 'integer'));
            $roomRow = $ilDB->fetchAssoc($roomSet);
            $one_booking['room'] = $roomRow['name'];
            
            $participants = array();
            
            //Get the participants
            $participantSet = $ilDB->query('SELECT users.firstname AS firstname,'.
                        ' users.lastname AS lastname, users.login AS login'.
                        ' FROM rep_robj_xrs_book_user'.
			' LEFT JOIN usr_data AS users ON users.usr_id = rep_robj_xrs_book_user.user_id'.
			' WHERE booking_id = '.$ilDB->quote($row['id'], 'integer').' ORDER BY users.lastname, users.firstname ASC');
            while($participantRow = $ilDB->fetchAssoc($participantSet))
            {
                //Check if the user has a firstname and lastname
                if(empty($userRow['firstname']) || empty($userRow['lastname'])) {
                    $participants[] = $participantRow['firstname'].' '
                            .$participantRow['lastname'];
                }
                //...if not, use the username
                else {
                    $participants[] = $participantRow['login'];
                }
            }
            $one_booking['participants'] = $participants;
            $one_booking['subject'] = $row['subject'];
            
            $res[] = $one_booking;
        }

        // Dummy-Daten
        /*$res[] =  array('recurrence' => true, 
                      'date'   => "7. März 2014, 9:00 - 13:00", 
                      'module'  => "MATHE2",
                      'subject' => "Tutorium",
                      'course' => "Technische Informatik (TI Bsc.)",
                      'semester' => "2, 4",
                      'room' => "117",
                      'participants' => array("Axel Herbst", "Tim Lehr"));
        
         $res[] =  array('recurrence' => false, 
                      'date'   => "3. April 2014, 15:00 - 17:00", 
                      'subject' => "Vorbereitung Präsentation",
                      'course' => "",
                      'semester' => "",
                      'room' => "118",
                      'participants' => array(""));*/
        
		return $res;
    }
    
    /**
     * Returns all the additional information that can be displayed in the
     * bookings table.
     */
    public function getBookingAddenda() 
    {
        // Pattern: $cols["column_heading"] = array("txt" => "column_js_entry")
        $cols["Modul"] = array("txt" => "Modul");
        $cols["Kurs"] = array("txt" => "Kurs");
        $cols["Semester"] = array("txt" => "Semester");
       return $cols;
    }
}
