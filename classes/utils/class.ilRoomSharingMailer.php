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
    
    
}
