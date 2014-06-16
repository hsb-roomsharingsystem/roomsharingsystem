<?php

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
 * Class ilRoomSharingSearchFormGUI
 * 
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 */
class ilRoomSharingSearchFormGUI extends ilPropertyFormGUI
{
    /**
	 * Constructor of ilRoomSharingSearchFormGUI. This form is needed to write
     * the inputs of the form inputs into the SESSION, rather than POST.
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
	}
    
    /**
     * Writes the provided form inputs into the session. This way we make sure
     * that the displayed search results are always the same if interactions
     * on those search results are made. As a little bonus search inputs from
     * the user are kept.
     */
	public function writeInputsToSession() {
        $items = $this->getInputItemsRecursive();
        foreach ($items as $item)
        {
            $field_id = $item->getFieldId();
            
            // the time field is an ilCombinationInputGUI with two time inputs
            // and therefore needs special treatment
            if ($field_id == "time") 
            {
                $time_from_value = $this->getInput("time_from", false);
                $this->writeSingleInputToSession("time_from", $time_from_value);
                
                $time_to_value = $this->getInput("time_to", false);
                $this->writeSingleInputToSession("time_to", $time_to_value);
            }
            else 
            {
                $value = $this->getInput($field_id, false);
                $this->writeSingleInputToSession($field_id, $value);
            }
        }
    }
    
   /**
	* Reset the form inputs in order to start off with a fresh search form.
	*/
	public function resetFormInputs()
	{
		$items = $this->getInputItemsRecursive();

		foreach ($items as $item)
		{
			if ($item->checkInput())
			{
				$item->clearFromSession();
			}
		}
    }
    
    /**
     * Writes a single input into SESSION.
     * @param type $a_id the id of the input
     * @param type $a_value and the corresponding value
     */
    public function writeSingleInputToSession($a_id, $a_value) {
        $_SESSION["form_".$this->getId()][$a_id] = $this->serializeData($a_value);
    }
    
    /**
     * Serializes the given value for SESSION.
     * @param type $a_value the value that needs to be serialized
     * @return type the serialized value
     */
    protected function serializeData($a_value) 
    {
		return serialize($a_value);
    }
    
    /**
     * Returns the value of the provided input variable.
     * @param type $a_session_var the variable for which the value should be returned
     * @return type the value of the variable
     */
    public function getInputFromSession($a_session_var)
    {
        return unserialize($_SESSION["form_".$this->getId()][$a_session_var]);
    }
      
}

?>