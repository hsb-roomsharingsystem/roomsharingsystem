<?php

 /**
 * Class ilRoomSharingSearchGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilRoomSharingSearchGUI: ilRoomSharingQuickSearchGUI
 * @ilCtrl_Calls ilRoomSharingSearchGUI: ilRoomSharingAdvancedSearchGUI
 */
class ilRoomSharingSearchGUI
{
	/**
	 * Constructor of ilRoomSharingSearchGUI
	 * @param	object	$a_parent_obj
	 */
	public function __construct(ilObjRoomSharingGUI $a_parent_obj)
	{   
        global $ilCtrl, $lng, $tpl;
        
        $this->parent_obj = $a_parent_obj;
        $this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->object->getId();	
        
        $this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;	
	}

	/**
	 * Main switch for command execution.
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showQuickSearch");
		
		switch($next_class)
		{
			// Quick Search
            case 'ilroomsharingquicksearchgui':
				$this->showQuickSearchObject();
				break;
            // Advanced Search
            case 'ilroomsharingquicksearchgui':
				$this->showAdvancedSearchObject();
				break;
            default:
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}
    
    /**
     * Adds subtabs to the search tab.
     * @param type $a_active subtab which should be activated after method call
     */
    protected function setSubTabs($a_active) 
    {
        global $ilTabs;
        
        $ilTabs->setTabActive('search');
        // Quick Search
        $ilTabs->addSubTab('quick_search',
			$this->lng->txt('rep_robj_xrs_quick_search'),
				$this->ctrl->getLinkTargetByClass('ilroomsharingquicksearchgui', 'showQuickSearch'));
        
        // Advanced Search
        $ilTabs->addSubTab('advanced_search',
			$this->lng->txt('rep_robj_xrs_advanced_search'),
				$this->ctrl->getLinkTargetByClass('ilroomsharingadvancedsearchgui', 'showAdvancedSearch'));
        
        $ilTabs->activateSubTab($a_active);
    }    
    
    
	/**
	 * Display the quick search GUI.
	 */
	public function showQuickSearchObject()
	{
        $this->setSubTabs('quick_search');
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingQuickSearchGUI.php");
		$object_gui =& new ilRoomSharingQuickSearchGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}
    
	/**
	 * Display the GUI for the avanced search form.
	 */
	public function showAdvancedSearchObject()
	{
        $this->setSubTabs('advanced_search');
		include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingAdvancedSearchGUI.php");
		$object_gui =& new ilRoomSharingAdvancedSearchGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

    /**
     * Returns roomsharing pool id.
     */
    public function getPoolId() 
    {
        return $this->pool_id;
    }
    
    /**
     * Sets roomsharing pool id.
     */
    public function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }
}
?>
