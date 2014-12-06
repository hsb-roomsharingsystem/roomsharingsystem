<?php

include_once 'Services/Mail/classes/class.ilMailNotification.php';
include_once('Services/Calendar/classes/class.ilDate.php');
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * This class is used for generating mails to inform users about the bookings.
 * The ilSystemNotification-Class does not work with our language files, so we
 * have to implement our own mailer.
 *
 *
 * @author Fabian Müller <famueller@stud.hs-bremen.de>
 */
class ilRoomSharingMailer extends ilMailNotification
{

    
    protected $s_roomname;
    protected $datestart;
    protected $dateend;
    protected $reason;
    protected $lng;
    protected $bookings;
    protected $ilRoomSharingDatabase;
    
    /**
     * Constructor with language option.
     * 
     * @param object language object from plugin
     * @param type $a_is_personal_workspace
     */
    public function __construct($lng = false, $pool_id = 0, $a_is_personal_workspace = false)
    {
        parent::__construct($a_is_personal_workspace);
        $this->lng = $lng;
        $this->ilRoomSharingDatabase = new ilRoomsharingDatabase($pool_id);
    }
    /**
     * Set room name.
     * 
     * @param string $s_roomname
     */

    public function setRoomname($s_roomname)
    {
        $this->s_roomname = (string) $s_roomname;
    }

    /**
     * Set starting date
     * 
     * @param string $s_datestart
     */
    public function setDateStart($s_datestart)
    {
        $this->datestart = new ilDateTime((string) $s_datestart ,IL_CAL_DATETIME);
    }
    
    /**
     * Set end date
     * 
     * @param string $s_dateend
     */
    public function setDateEnd($s_dateend)
    {
        $this->dateend = new ilDateTime((string) $s_dateend, IL_CAL_DATETIME);
    }
    
    /**
     * Set reason why booking was cancelled.
     * 
     * @param string $s_reason Reason for cancellation. 
     *                          Used by sendCancellationMailWithReason()
     */
    public function setReason($s_reason)
    {
        $this->reason = $s_reason;
    }

    /**
     * Compose notification of booking for the creator of that booking.
     * 
     * @param array $a_user_id The user who will get the mail.
     * Returns nothing.
     */
    private function composeBookingMailForCreator($a_user_id)
    {
        $lng = $this->lng;
                
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->lng->txt('rep_robj_xrs_mail_booking_creator_subject'));
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($lng->txt('rep_robj_xrs_mail_booking_creator_message') . "\n");
        $this->appendBody($lng->txt('rep_robj_xrs_room'). " ");
        $this->appendBody($this->s_roomname . " ");
        $this->appendBody($lng->txt('rep_robj_xrs_from') . " ");
        $this->appendBody($this->datestart->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " ");
        $this->appendBody($lng->txt('rep_robj_xrs_to') . " ");
        $this->appendBody($this->dateend->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'));
    }
    
    /**
     * Compose notification of booking for participants.
     * 
     * @param type $a_user_id The participant who will get the mail.
     * Returns nothing.
     */
    private function composeBookingMailForParticipant($a_user_id)
    {
        $lng = $this->lng;
        
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->lng->txt('rep_robj_xrs_mail_booking_participant_subject'));
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($lng->txt('rep_robj_xrs_mail_booking_participant_message') . "\n");
        $this->appendBody($lng->txt('rep_robj_xrs_room'). " ");
        $this->appendBody($this->s_roomname . " ");
        $this->appendBody($lng->txt('rep_robj_xrs_from') . " ");
        $this->appendBody($this->datestart->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " ");
        $this->appendBody($lng->txt('rep_robj_xrs_to') . " ");
        $this->appendBody($this->dateend->get(IL_CAL_FKT_DATE, 'd.m.Y H:s')); 
        
    }
    
    /**
     * Compose a cancellation mail for the creator of the booking.
     * @param type $a_user_id The user who will get the cancellation mail.
     */
    private function composeCancellationMailForCreator($a_user_id, $reason = "")
    {
        $lng = $this->lng;
        
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->lng->txt('rep_robj_xrs_mail_cancellation_creator_subject'));
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($lng->txt('rep_robj_xrs_mail_cancellation_creator_message'). "\n");
        $this->appendBody($lng->txt('rep_robj_xrs_room'). " ");
        $this->appendBody($this->s_roomname . " ");
        $this->appendBody($lng->txt('rep_robj_xrs_from') . " ");
        $this->appendBody($this->datestart->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " ");
        $this->appendBody($lng->txt('rep_robj_xrs_to') . " ");
        $this->appendBody($this->dateend->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " \n");
        if ($reason !== '')
        {
            $this->appendBody($lng->txt('rep_robj_xrs_mail_cancellation_reason_prefix'). "\n");
            $this->appendBody($reason . "\n");
            $this->appendBody("\n");
                    
        }         
    }
    
    /**
     * Compose a cancellation mail for the participants of the booking.
     * @param type $a_user_id The user who will get the cancellation mail.
     */
    private function composeCancellationMailForParticipant($a_user_id, $reason = "")
    {
        $lng = $this->lng;
        
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->lng->txt('rep_robj_xrs_mail_cancellation_participant_subject'));
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($lng->txt('rep_robj_xrs_mail_cancellation_participant_message') . "\n");
        $this->appendBody($lng->txt('rep_robj_xrs_room'). " ");
        $this->appendBody($this->s_roomname . " ");
        $this->appendBody($lng->txt('rep_robj_xrs_from') . " ");
        $this->appendBody($this->datestart->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " ");
        $this->appendBody($lng->txt('rep_robj_xrs_to') . " ");
        $this->appendBody($this->dateend->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " \n");
        if ($reason !== '')
        {
            $this->appendBody($lng->txt('rep_robj_xrs_mail_cancellation_reason_prefix'). "\n");
            $this->appendBody($reason . "\n");
            $this->appendBody("\n");
                    
        }        
    }
    /**
     * Compose a mail that informs user about a change of room attributes.
     * @param integer $a_user_id ID of user
     */
    protected function composeRoomChangeMail($a_user_id)
    {
        $lng = $this->lng;
        
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->lng->txt('rep_robj_xrs_mail_room_changed_subject'));
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody($lng->txt('rep_robj_xrs_mail_room_changed_message') . "\n");
        foreach ($this->bookings as $booking)
        {
            if($booking['user_id'] != $a_user_id)
            { continue;
            }
            else
            {
                $roomname = $this->ilRoomSharingDatabase->getRoomName($booking['room_id']);
                $datestart = new ilDateTime($booking['date_from'] ,IL_CAL_DATETIME);
                $dateend = new ilDateTime($booking['date_to'] ,IL_CAL_DATETIME);
                
                $this->appendBody($lng->txt('rep_robj_xrs_room'). " ");
                $this->appendBody($roomname . " ");
                $this->appendBody($lng->txt('rep_robj_xrs_from') . " ");
                $this->appendBody($datestart->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). " ");
                $this->appendBody($lng->txt('rep_robj_xrs_to') . " ");
                $this->appendBody($dateend->get(IL_CAL_FKT_DATE, 'd.m.Y H:s'). "\n");
            }
            
        }
    }


    /**
     * Send notification to creator of booking
     * @param array $a_user_id user-id who will get the mail
     * Returns nothing.
     */
    protected function composeAndSendBookingMailToCreator($a_user_id)
    {
        $this->composeBookingMailForCreator($a_user_id);
        parent::sendMail(array($a_user_id), array('system'), is_numeric($a_user_id));
    }

    /**
     * Send notification to participants of booking
     * @param array $a_participant_id user-id who will get the mail
     * Returns nothing.
     */
    protected function composeAndSendBookingMailToParticipant($a_participant_id)
    {
        $this->composeBookingMailForParticipant($a_participant_id);
        parent::sendMail(array($a_participant_id), array('system'), is_numeric($a_participant_id));
    }
    
    /**
     * Send cancelling notification to creator of booking
     * @param array $a_user_id user-id who will get the mail
     * Returns nothing.
     */
    protected function composeAndSendCancellationMailToCreator($a_user_id, $s_reason)
    {
        $this->composeCancellationMailForCreator($a_user_id, $s_reason);
        parent::sendMail(array($a_user_id), array('system'), is_numeric($a_user_id));
    }
    
    /**
     * Send cancellation notification to participant of booking
     * @param array $a_participant_id user-id who will get the mail
     */
    protected function composeAndSendCancellationMailToParticipant($a_participant_id, $s_reason)
    {
        $this->composeCancellationMailForParticipant($a_participant_id, $s_reason);
        parent::sendMail(array($a_participant_id), array('system'), is_numeric($a_participant_id));
    }
    
    /**
     * Send room change notification to user.
     * @param integer $a_userid ID of user who gets the mail
     */
    protected function composeAndSendRoomChangeMail($a_userid)
    {
        $this->composeRoomChangeMail($a_userid);
        parent::sendMail(array($a_userid), array('system'), is_numeric($a_userid));
    }


    /**
     * Send booking notification to creator and participants
     * @param array $a_user_id userid of creator
     * @param array $a_participants_ids userids of participants
     * 
     * Returns nothing
     */
    public function sendBookingMail($a_user_id, array $a_participants_ids)
    {
        $this->composeAndSendBookingMailToCreator($a_user_id);
        foreach (array_unique($a_participants_ids) as $participant_id)
        {
            $this->composeAndSendBookingMailToParticipant($participant_id);
        }
    }
    
    /**
     * Send cancellation notification to creator and participants.
     * @param array $a_user_id userid of creator
     * @param array $a_participants_ids userids of participants
     * 
     * Returns nothing.
     */
    public function sendCancellationMail($a_user_id, array $a_participants_ids)
    {
        $this->composeAndSendCancellationMailToCreator($a_user_id);
        foreach (array_unique($a_participants_ids) as $participant_id)
        {
            $this->composeAndSendCancellationMailToParticipant($participant_id);
        }
    }
    
    /**
     * Send cancellation notification to creator and participants.
     * This also includes a reason, why the booking was cancelled.
     * The reason has to be set via the setReason() function.
     * @param array $a_user_id userid of creator
     * @param array $a_participants_ids userids of participants
     * 
     * Returns nothing.
     */
    public function sendCancellationMailWithReason($a_user_id, array $a_participants_ids)
    {
        $this->composeAndSendCancellationMailToCreator($a_user_id, $this->reason);
        foreach (array_unique($a_participants_ids) as $participant_id)
        {
            $this->composeAndSendCancellationMailToParticipant($participant_id, $this->reason);
        }
    }
    
    /**
     * Send a mail to every user when a room has been edited.
     * 
     * @param integer $a_roomid id of the room.
     */
    public function sendRoomChangeMail($a_roomid)
    {
        $this->bookings = $this->ilRoomSharingDatabase->getBookingsForRoomThatAreValid($a_roomid);
        foreach ($this->bookings as $booking)
        {
            $user_ids[] = $booking['user_id'];
        }
        foreach (array_unique($user_ids) as $userid)
        {
            $this->composeAndSendRoomChangeMail($userid);
        }
    }
    
    

}
