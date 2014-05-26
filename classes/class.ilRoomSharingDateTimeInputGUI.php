<?php
include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");

/**
* Class ilRoomSharingDateTimeInputGUI
* 
* @author Alexander Keller <a.k3ll3r@gmail.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilRoomSharingDateTimeInputGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
*
*/

class ilRoomSharingDateTimeInputGUI extends ilDateTimeInputGUI
{ 
    /**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
	}
    
    /**
	* Check input, strip slashes etc. for one time input.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $ilUser, $lng;
		
        // use the UNIX time stamp "0", since we don't care about the date
        $dt['mday'] = 1;
        $dt['mon'] = 1;
        $dt['year'] = 1970;
        
		$post = $_POST[$this->getPostVar()];
		// empty time is valid with input field
		if($this->getMode() == self::MODE_INPUT && $post["time"] == "")
		{
			return true;
		}

		$post["time"] = ilUtil::stripSlashes($post["time"]);  
        if($post["time"] && $this->isTime($post["time"]))
        {
			$time = explode(":", $post["time"]);
			$dt['hours'] = (int)$time[0];
			$dt['minutes'] = (int)$time[1];
			$dt['seconds'] = (int)$time[2];
		}
        else
        {
           $dt = false; 
        }
		
        // validate for exceedings
		if(($dt['hours'] > 23 || $dt['minutes'] > 59 || $dt['seconds'] > 59))
		{
			$dt = false;
		}    
		
		$date = new ilDateTime($dt, IL_CAL_FKT_GETDATE, $ilUser->getTimeZone());
		$this->setDate($dt ? $date : null);
		
		// overwrite the post values
		$_POST[$this->getPostVar()]['date'] = $date->get(IL_CAL_FKT_DATE, 'Y-m-d', $ilUser->getTimeZone());
		$_POST[$this->getPostVar()]['time'] = $date->get(IL_CAL_FKT_DATE, 'H:i:s', $ilUser->getTimeZone());
		
		return (bool)$dt;
    }   
    
    /**
    * Validates the time in 24 hour format (e.g. 18:13).
    *
    * @param string  $time  the time that needs to be checked.
    */
    function isTime($time)
    {
        return preg_match("#([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}#", $time);
    }
    
}
?>
