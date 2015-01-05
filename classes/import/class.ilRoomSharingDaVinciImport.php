<?php

require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/class.ilRoomSharingRoom.php';
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/booking/class.ilRoomSharingBook.php");

/**
 * Description of class
 *
 * @author MartinDoser
 */
class ilRoomSharingDaVinciImport {

        private $parent_obj;
        private $pool_id;
        private $ilRoomSharingDatabase;
        private $db_rooms;
        
        private $startingDate;
        private $blocks;
        private $untis;
        private $mins;
        private $startingTimes;
        private $appointments_info;
        private $appointments;
        private $rooms;
        private $bookings;
        private $currentCourse;
        private $activeWeeks;
        private $current_weekly_rotation;
        private $current_classes;
        
        /**
	 * Constructor of ilRoomSharingDaVinciImport.
	 *
	 * @param type $a_pool_id the pool id of the plugin instance
	 * @param type $a_ilRoomsharingDatabase the Database
	 */
	public function __construct($a_parent_obj, $a_pool_id, $a_ilRoomsharingDatabase)
	{
                $this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_pool_id;
		$this->ilRoomSharingDatabase = $a_ilRoomsharingDatabase;
                $this->db_rooms = array();
                $room_ids = $this->ilRoomSharingDatabase->getAllRoomIds();
                
                foreach($room_ids as $id)
                {
                    array_push($this->db_rooms, array($id, $this->ilRoomSharingDatabase->getRoomName($id)));
                }
                
                $this->appointments_info = array();
                $this->appointments = array();
                $this->rooms = array();
                $this->bookings = array();
	}
        
        public function importBookingsFromDaVinciFile($file, $import_rooms, $import_bookings, $default_cap)
        {
                $file_name = ilUtil::getASCIIFilename($file["name"]);
		$file_name_mod = str_replace(" ", "_", $file_name);
		$file_path = "templates" . "/" . $file_name_mod; // construct file path
		ilUtil::moveUploadedFile($file["tmp_name"], $file_name_mod, $file_path);
                
                $fileAsString = file_get_contents($file_path);
                
                foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileAsString)as$line)
                {
                    $this->checkForKey($line);
                }
                
                if($import_rooms == "1");
                {
                    foreach ($this->rooms as $room) {
                        if(!($this->ilRoomSharingDatabase->getRoomWithName($room['name']) !== array()))
                        {
                            if($room['cap'] == 0)
                            {
                                $room['cap'] = (int)$default_cap;
                            }
                            //$a_name, $a_type, $a_min_alloc, $a_max_alloc, $a_file_id, $a_building_id
                            $this->ilRoomSharingDatabase->insertRoom($room['name'],$room['type'],1,$room['cap'],array(),array());
                        }
                    }
                }
                
                if($import_bookings == "1")
                {
                    foreach($this->appointments as $booking)
                    {  
                        if($booking['day'] != 0)
                        {
                            $usedWeek = clone($this->startingDate);
                            for($i = 0; $i < strlen($this->activeWeeks); $i++)
                            {
                                if($booking['week'] != null)
                                {
                                    if($booking['week'][$i] === 'X')                            
                                    {
                                       $this->addDaVinciBooking($booking['day'], $booking['start'], $booking['end'], $booking['room'], $booking['prof'], $booking['subject'], $booking['classes'], $usedWeek);
                                    }
                                }
                                else
                                {
                                    if($this->activeWeeks[$i] === 'X')                            
                                    {
                                        $this->addDaVinciBooking($booking['day'], $booking['start'], $booking['end'], $booking['room'], $booking['prof'], $booking['subject'], $booking['classes'], $usedWeek);
                                    }
                                }


                                $usedWeek->add(new DateInterval('P7D'));
                            }
                        }
                    }
                }
                
                
        }
        
        private function checkForKey($line)
        {
            $params = preg_split('/;/', $line);
            
            if(strncmp($params[0], "R1", 2) == 0)
            {
                $this->interpretR1Line($params);
            }
            if(strncmp($params[0], "U0", 2) == 0)
            {
                $this->interpretU0Line($params);
            }
            if(strncmp($params[0], "U1", 2) == 0)
            {
                $this->interpretU1Line($params);
            }
            if(strncmp($params[0], "U5", 2) == 0)
            {
                $this->interpretU5Line($params);
            }
            if(strncmp($params[0], "U6", 2) == 0)
            {
                $this->interpretU6Line($params);
            }
            if(strncmp($params[0], "U2", 2) == 0)
            {
                $this->interpretU2Line($params);
            }
            if(strncmp($params[0], "U8", 2) == 0)
            {
                $this->interpretU8Line($params);
            }
        }
        
        private function interpretR1Line($params)
        {
            array_push($this->rooms, array('name'=>$this->alterString($params[2]),'full_name'=>$params[3],'cap'=>$params[5],'type'=>$params[8]));
        }
        
        
        private function interpretU0Line($params)
        {
            $dateStr = substr($params[1], 0, 4) . '-' . substr($params[1], 4, 2) . '-' . substr($params[1], 6,2);
            $this->startingDate = new DateTime($dateStr);
            $day_off_set = $this->startingDate->format('N');
            $this->startingDate->sub(new DateInterval('P'. $day_off_set . 'D'));
            $this->blocks = $params[4];
            $this->units = $params[5];
            $this->mins = $params[8];

            $tmpArray = array();
            for($i = 9; $i < (count($params))-1;$i++)
            {
                array_push($tmpArray, date_create($params[$i]));
            }
            $this->startingTimes = $tmpArray;
            
            $this->activeWeeks = $params[3];
        }
    
        
        private function interpretU1Line($params)
        {
            $this->current_weekly_rotation = array();
            $this->current_classes = array();
            $this->currentCourse = $this->alterString($params[6]);
            array_push($this->appointments_info, array('id'=>$params[1],'course'=>$params[2],'prof'=>$params[3],'identifier'=>$params[6]));
        }
        
        private function interpretU5Line($params)
        {
            $this->current_weekly_rotation = $params[2];
        }
        
        private function interpretU6Line($params)
        {
            for($i = 3; $i < ($params[2]+2); $i++)
            {
                if($this->current_classes == null)
                {
                    $this->current_classes = $params[$i];
                }
                else{
                    $this->current_classes = $this->current_classes . ' ' . $params[$i];
                }
            }
        }
        
        private function interpretU2Line($params)
        {
         
            for($i = 0;$i < ($params[2]);$i++)
            {
                $n=($i*6+3);
                $day = $params[$n];
                if(($params[$n+1])!= null)
                {
                    $startTime = clone($this->startingTimes[($params[$n+1])-1]);
                    $endTime = clone($this->startingTimes[($params[$n+1])-1]);
                    $endTime->add(new DateInterval('PT'.$this->mins.'M'));
                }
                $roomShrt = $params[$n+3];
                $profShrt = $params[$n+4];
                
                array_push($this->appointments, array('id'=>$params[1],'day'=>$day,'start'=>$startTime,'end'=>$endTime,'room'=>$this->alterString($roomShrt),
                    'prof'=>$this->alterString($profShrt),'subject'=>$this->currentCourse,'classes'=>$this->current_classes,'week'=>  $this->current_weekly_rotation));
            }
        }
        
        private function interpretU8Line($params)
        {
            //used for daVinci6
            for($i = 0;$i < ($params[2]);$i++)
            {
                $n=($i*10+3);
                $day = $params[$n+2];
                if($params[$n] != null && $params[$n+1] != null)
                {
                    $startTime = clone($this->startingTimes[($params[$n+5])-1]);
                    $endTime = clone($this->startingTimes[($params[$n+1])-1]);
                    $endTime->add(new DateInterval('PT'.$params[$n+4].'M'));
                }
                $roomShrt = $params[$n+7];
                $profShrt = $params[$n+6];
                
                array_push($this->appointments, array('id'=>$params[1],'day'=>$day,'start'=>$startTime,'end'=>$endTime,'room'=>$this->alterString($roomShrt),
                    'prof'=>$this->alterString($profShrt),'subject'=>$this->currentCourse,'classes'=>$this->current_classes,'week'=>  $this->current_weekly_rotation));
            }
        }

        
        private function alterString($aString)
        {
            if($aString[0] == '"' && $aString[strlen($aString)-1] == '"' )
            {
                $aString = substr($aString, 1, strlen($aString)-2);
            }
            
            return $aString;
        }
        
        private function editDateString($aString)
        {
            $year = substr($aString, 0,4);
            $month = substr($aString,4,2);
            $day = substr($aString,6,2);
            $hour = substr($aString,8,2);
            $minute = substr($aString,10,2);
            
            $aString = $year . '-' . $month . '-' . $day . ' ' . $hour .  ':' . $minute;
           
            return $aString;
        }
        
          
        private function addDaVinciBooking($day, $start, $end, $room, $prof, $subject, $classes, $usedWeek)
        {
            $date_diff = clone($usedWeek);
            $interval = $date_diff->diff(new DateTime(date('Y-m-d')));

            if(($interval->format('%R'))=== '-')
            {
                $entry = array();

                if($classes == null)
                {
                    $entry['subject'] = ($subject . " " . $prof);
                }
                else
                {
                    $entry['subject'] = ($classes . " " . $subject . " " . $prof);
                }

                $tmpDate = clone($usedWeek);
                $tmpDate->add(new DateInterval('P'. (string)$day . 'D'));
                $entry['from']['date']=  date_format($tmpDate,'Y-m-d');
                $entry['from']['time']= date_format($start, 'H:i:s');
                $entry['to']['date']= date_format($tmpDate,'Y-m-d');
                $entry['to']['time']=  date_format($end, 'H:i:s');
                $entry['book_public'] = '0';
                $entry['accept_room_rules'] = '1';
                
                $entry['room'] = $this->ilRoomSharingDatabase->getRoomWithName($room)[0]['id'];
                $entry['comment'] = 'daVinci Booking';
                $entry['cal_id'] = $this->parent_obj->getCalendarId();


                
                $this->book = new ilRoomSharingBook($this->pool_id);
                
                if($this->ilRoomSharingDatabase->getRoomWithName($room) !== array())
                {
                    try
                    {
                        $this->book->addBooking($entry,array(),array());
                    } catch (Exception $ex) {
                        $aBooking = $this->ilRoomSharingDatabase->getBookingIdForRoomInDateTimeRange($entry['from']['date'] . " "  . $entry['from']['time'], $entry['to']['date'] . " "  . $entry['to']['time'], $entry['room'],
                            0);
                        if($aBooking !== array() && $this->ilRoomSharingDatabase->getBooking($aBooking[0])['comment'] === 'daVinci Booking')
                        {
                            $newBookingValues = $this->ilRoomSharingDatabase->getBooking($aBooking[0]);
                            $newBookingValues['subject'] = $newBookingValues['subject'] . ' & ' . $subject . ' ' . $prof;
                            $newBookingValues['from'] = $entry['from'];
                            $newBookingValues['to'] = $entry['to'];
                            $newBookingValues['room'] = $entry['room'];
                            $newBookingValues['cal_id'] = $entry['cal_id'];

                            try
                            {
                                $this->book->updateEditBooking(
                                    $aBooking[0], 
                                    $this->ilRoomSharingDatabase->getBooking($aBooking[0]),
                                    $this->ilRoomSharingDatabase->getAttributesForBooking($aBooking[0]),
                                    $this->ilRoomSharingDatabase->getParticipantsForBooking($aBooking[0]), 
                                    $newBookingValues,
                                    $this->ilRoomSharingDatabase->getAttributesForBooking($aBooking[0]),
                                    $this->ilRoomSharingDatabase->getParticipantsForBooking($aBooking[0]));
                            } catch (Exception $ex) {
                                
                            }

                        }

                    }
                }
                
            }
        }
}
