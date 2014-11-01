<?php

include_once 'Services/Mail/classes/class.ilMailNotification.php';


/**
 * This class is used for generating mails to inform users about the bookings.
 * The ilSystemNotification-Class does not work with our language files, so we
 * have to implement our own mailer.
 *
 * @author Fabian Müller <fmu@never-afk.de>
 * @version $Id$
 */

class ilRoomSharingMailer extends ilMailNotification
{
    protected $subjectraw; // string
    
    public function setRawSubject($s_subject)
    {
        $this->subjectraw = (string)$s_subject;
    }

    /**
     * Compose notification to single recipient
     * 
     * @return bool
     */
    public function compose($a_user_id)
    {
        $this->initLanguage($a_user_id);
        $this->initMail();
        $this->setSubject($this->subjectraw);
        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody("Du hast nen Raum gebucht.");
        
        return true;
        
    }
    
    /**
     * Send notification to single recipient
     * 
     * @return bool
     */
    protected function composeAndSendMail($a_user_id)
    {						
            if($this->compose($a_user_id))
            {
                    parent::sendMail(array($a_user_id), array('system'), is_numeric($a_user_id));								
                    return true;
            }
            return false;
    }
    
    /**
     * Send notification(s)
     * 
     * @param array $a_user_ids
     * @return array recipient ids
     */
    public function sendMail(array $a_user_ids) {

        $recipient_ids = array();
        foreach(array_unique($a_user_ids) as $user_id)
        {				
                // author of change should not get notification
                if($this->changed_by == $user_id)
                {
                        continue;
                }

                if($this->composeAndSendMail($user_id))
                {
                        $recipient_ids[] = $user_id;
                }
        }	

        return $recipient_ids;
    }

}
