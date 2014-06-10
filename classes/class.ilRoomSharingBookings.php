<?php

/**
 * Class ilRoomSharingBookings
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @version $Id$
 */
class ilRoomSharingBookings
{   
    private $pool_id = 1;

    function __construct($pool_id = 1)
    {
        $this->pool_id = $pool_id;
    }

    /**
     * Remove a booking
     * @param int $booking_id The id of the booking
     * @param bool $seq True if the all sequence bookings should be deleted
     * @global type $ilDB, $ilUser
     */
    public function removeBooking($a_booking_id, $a_seq = false) 
    {
        global $ilDB, $ilUser, $ilCtrl, $lng;
        
        if(!empty($a_booking_id) && is_numeric($a_booking_id)) 
        {
            $set = $ilDB->query('SELECT seq_id, user_id'.
                    ' FROM rep_robj_xrs_bookings'.
                    ' WHERE id = '.$ilDB->quote($a_booking_id, 'integer'));
            $row = $ilDB->fetchAssoc($set);
            
            //Check if there is a result (so the booking with the ID exists)
            if($ilDB->numRows($set) > 0)
            {
                //Check if the current user is the author of the booking
                if($row['user_id'] == $ilUser->getId()) 
                {
                    //Check whether only the specific booking should be deleted
                    if(!$a_seq || $row['seq_id'] == NULL || !is_numeric($row['seq_id'])) {
                        $ilDB->query('DELETE FROM rep_robj_xrs_bookings'.
                                ' WHERE id = '.$ilDB->quote($booking_id, 'integer'));
                        $ilDB->query('DELETE FROM rep_robj_xrs_book_user'.
                                ' WHERE booking_id = '.$ilDB->quote($booking_id, 'integer'));
                            ilUtil::sendSuccess($lng->txt("Dieser Termin wurde gelöscht! (folgende Serientermine nicht betroffen)"), true);
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
							$ilDB->query('DELETE FROM rep_robj_xrs_bookings'.
									' WHERE id = '.$ilDB->quote($a_booking_id, 'integer'));
							$ilDB->query('DELETE FROM rep_robj_xrs_book_user'.
									' WHERE booking_id = '.$ilDB->quote($a_booking_id, 'integer'));
							ilUtil::sendSuccess($lng->txt("Dieser Termin und alle folgenden Serientermine wurden gelöscht!"), true);
						}
					}
				}
                else
                {
                    ilUtil::sendFailure($lng->txt("Keine Berechtigung, diese Buchung zu löschen!"), true);
                }
            }
            else
            {
                ilUtil::sendFailure($lng->txt("Diese Buchung existiert nicht (mehr)!"), true);
            }
        }
        else
        {
            ilUtil::sendFailure($lng->txt("Keine oder nicht numerische ID angegeben!"), true);
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
                        ' WHERE pool_id = '.$ilDB->quote($pool_id, 'integer').
                        ' AND user_id = '.$ilDB->quote($ilUser->getId(), 'integer').
                        ' AND (date_from >= "'.date('Y-m-d H:i:s').'"'.
                        ' OR date_to >= "'.date('Y-m-d H:i:s').'")'.
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
            $one_booking['room_id'] = $row['room_id'];
            
            $participants = array();
            $participants_ids = array();
            
            //Get the participants
            $participantSet = $ilDB->query('SELECT users.firstname AS firstname,'.
            		' users.lastname AS lastname, users.login AS login,'.
            		' users.usr_id AS id FROM rep_robj_xrs_book_user'.
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
                else 
                {
                    $participants[] = $participantRow['login'];
                }
                $participants_ids[] = $participantRow['id'];
            }
            $one_booking['participants'] = $participants;
            $one_booking['participants_id'] = $participants_id;
            $one_booking['subject'] = $row['subject'];
            
            //Get variable attributes of a booking
            $attributesSet = $ilDB->query('SELECT value, attr.name AS name'.
                        ' FROM rep_robj_xrs_book_attr'.
                        ' LEFT JOIN rep_robj_xrs_battr AS attr'.
                        ' ON attr.id = rep_robj_xrs_book_attr.attr_id'.
                        ' WHERE booking_id = '.$ilDB->quote($row['id'], 'integer'));
            while($attributesRow = $ilDB->fetchAssoc($attributesSet))
            {
                $one_booking[$attributesRow['name']] = "1".$attributesRow['value'];
            }

            //The booking id
            $one_booking['id'] = $row['id'];

            $res[] = $one_booking;
        }

        // Dummy-Data
        $res[] =  array('recurrence' => true, 
                      'date'   => "7. März 2014, 9:00 - 13:00", 
                      'id'     => 1,
                      'room' => "117",
                      'room_id' => 3,
                      'subject' => "Tutorium",
                      'participants' => array("Tim Lehr"),
                      'participants_ids' => array("6"),
                      'Modul'  => "MATHE2",
                      'Kurs' => "Technische Informatik (TI Bsc.)");
        
        $res[] =  array('recurrence' => false, 
                      'date'   => "3. April 2014, 15:00 - 17:00", 
                      'id'     => 2,
                      'room' => "118",
                      'room_id' => 4,
                      'subject' => "Vorbereitung Präsentation",
                      'Semester' => "6");
        return $res;
        
    }
    
    /**
     * Returns all the additional information that can be displayed in the
     * bookings table.
     */
    public function getAdditionalBookingInfos() 
    {
        global $ilDB;
        $cols = array();
        $attributesSet = $ilDB->query('SELECT *'.
                ' FROM rep_robj_xrs_battr'.
                ' WHERE pool_id = '.$ilDB->quote($pool_id, 'integer'));
        while($attributesRow = $ilDB->fetchAssoc($attributesSet))
        {
            $cols[$attributesRow['name']] = array("txt" => $attributesRow['name']);
        }
        
        // Dummy-Data
        $cols["Modul"] = array("txt" => "Modul");
        $cols["Kurs"] = array("txt" => "Kurs");
        $cols["Semester"] = array("txt" => "Semester");
        
        return $cols;
    }
}
