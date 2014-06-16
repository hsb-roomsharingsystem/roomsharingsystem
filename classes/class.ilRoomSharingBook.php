<?php

/**
 * Backend-Class for booking-mask
 * @author Robert Heimsoth
 */
class ilRoomSharingBook {
  protected $pool_id;
  
  /**
   * Method to add a new booking into the database
   * 
   * @global type $ilDB
   * @global type $ilUser
   * @global type $pool_id
   * @param array $booking_values Array with the values of the booking
   * @param array $booking_attr_values Array with the values of the booking-attributes
   * @param ilRoomSharingRooms Object of ilRoomSharingRooms
   * @return type
   */
  public function addBooking($booking_values, $booking_attr_values, $ilRoomSharingRooms) {
      global $ilDB, $ilUser;
      $subject = $booking_values['subject'];
      $date_from = $booking_values['from']['date']." ".
              $booking_values['from']['time'];
      $date_to = $booking_values['to']['date']." ".
              $booking_values['to']['time'];
      if($date_from >= $date_to) {
          return -3;
      }
      
      $room_id = $booking_values['room'];
      $user_id = $ilUser->getId();
      if($ilRoomSharingRooms->getRoomsBookedInDateTimeRange($date_from, $date_to, $room_id) != array()) {
          return -2;
      }
      $nextId = $ilDB->nextID('rep_robj_xrs_bookings');
      $addBookingQuery = "INSERT INTO rep_robj_xrs_bookings"
              . " (id,date_from, date_to, room_id, pool_id, user_id, subject)"
              . " VALUES (".$nextId.","
              . " ".$ilDB->quote($date_from, 'timestamp').","
              . " ".$ilDB->quote($date_to, 'timestamp').","
              . " ".$ilDB->quote($room_id, 'integer').","
              . " ".$ilDB->quote($this->pool_id, 'integer').","
              . " ".$ilDB->quote($user_id, 'integer').","
              . " ".$ilDB->quote($subject, 'text').")";
      if($ilDB->manipulate($addBookingQuery) == -1) {
          return -1;
      }
      
      $insertedId = $nextId;
      foreach($booking_attr_values as $booking_attr_key => $booking_attr_value) {
          //Only insert the attribute value, if a value was submitted by the user
          if($booking_attr_value != "") {
            $ilDB->query("INSERT INTO rep_robj_xrs_book_attr"
                    . " (booking_id, attr_id, value)"
                    . " VALUES (".$ilDB->quote($insertedId, 'integer').","
                    . " ".$ilDB->quote($booking_attr_key, 'integer').","
                    . " ".$ilDB->quote($booking_attr_value, 'text').")");
          }
      }
      return 1;
  }
  
  public function setPoolId($pool_id) {
      $this->pool_id = $pool_id;
  }
}
