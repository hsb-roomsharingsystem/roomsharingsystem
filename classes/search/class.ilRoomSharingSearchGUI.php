<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/search/class.ilRoomSharingSearchQuickGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingPermissionUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");

use ilRoomSharingPrivilegesConstants as PRIVC;

/**
 * Class ilRoomSharingSearchGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingSearchGUI: ilRoomSharingSearchQuickGUI
 * @ilCtrl_Calls ilRoomSharingSearchGUI: ilRoomSharingSearchAdvancedGUI
 *
 * @property ilRoomSharingPermissionUtils $permission
 */
class ilRoomSharingSearchGUI
{
	private $permission;
	private $ctrl;
	private $lng;
	private $tpl;

	/**
	 * Constructor of ilRoomSharingSearchGUI
	 * @param	object	$a_parent_obj
	 */
	public function __construct(ilObjRoomSharingGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl, $rssPermission;

		$this->parent_obj = $a_parent_obj;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->permission = $rssPermission;
	}

	/**
	 * Main switch for command execution.
	 * @return Returns true if command was successful
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd("showSearchQuick");

		if ($cmd == "showSearchResults" || $cmd == "applySearch" || $cmd == "resetSearch")
		{
			$next_class = "ilroomsharingsearchquickgui";
		}

		switch ($next_class)
		{
			// Quick Search
			case 'ilroomsharingsearchquickgui':
				$this->showSearchQuickObject();
				break;

			// Advanced Search
			case 'ilroomsharingsearchadvancedgui':
				$this->showSearchAdvancedObject();
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
		$ilTabs->addSubTab('quick_search', $this->lng->txt('rep_robj_xrs_quick_search'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingsearchquickgui', 'showSearchQuick'));

		// Advanced Search
		$ilTabs->addSubTab('advanced_search', $this->lng->txt('rep_robj_xrs_advanced_search'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingsearchadvancedgui', 'showSearchAdvanced'));

		$ilTabs->activateSubTab($a_active);
	}

	/**
	 * Display the quick search GUI.
	 */
	public function showSearchQuickObject()
	{
		if (!$this->permission->checkPrivilege(PRIVC::ACCESS_SEARCH))
		{
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_no_permission_for_action"));
			$this->ctrl->redirectByClass('ilinfoscreengui', 'showSummary', 'showSummary');
			return FALSE;
		}
		$this->setSubTabs('quick_search');
		$object_gui = & new ilRoomSharingSearchQuickGUI($this);
		$this->ctrl->forwardCommand($object_gui);
	}

	/**
	 * Display the GUI for the avanced search form.
	 */
	public function showSearchAdvancedObject()
	{
		$this->setSubTabs('advanced_search');
		$this->tpl->setContent($this->lng->txt("rep_robj_xrs_not_yet_implemented"));
	}

	/**
	 * Returns roomsharing pool id.
	 * @return returns poolid
	 */
	public function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 * @params int poolid
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
