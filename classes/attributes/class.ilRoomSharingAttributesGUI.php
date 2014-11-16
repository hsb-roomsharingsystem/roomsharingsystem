<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/attributes/room/class.ilRoomSharingRoomAttributesGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/attributes/booking/class.ilRoomSharingBookingAttributesGUI.php");

/**
 * Class ilRoomSharingAttributesGUI
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 *
 * @ilCtrl_Calls ilRoomSharingAttributesGUI: ilRoomSharingRoomAttributesGUI, ilRoomSharingBookingAttributesGUI, ilCommonActionDispatcherGUI
 *
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 */
class ilRoomSharingAttributesGUI
{
	public $ref_id;
	protected $ctrl;
	protected $lng;
	private $pool_id;

	/**
	 * Constructor of ilRoomSharingAttributesGUI
	 *
	 * @param object $a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		global $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return Returns always true.
	 */
	function executeCommand()
	{
		// default cmd if none provided
		$cmd = $this->ctrl->getCmd("showRoomAttributes");

		switch ($cmd)
		{
			case 'render':
			case 'showContent':
				$cmd = 'showRoomAttributes';
				break;
			case 'saveRoomAttributes':
				$rooms_attributes_gui = & new ilRoomSharingRoomAttributesGUI($this);
				$this->ctrl->forwardCommand($rooms_attributes_gui);
				break;
			case 'saveBookingAttributes':
				$bookings_attributes_gui = & new ilRoomSharingBookingAttributesGUI($this);
				$this->ctrl->forwardCommand($bookings_attributes_gui);
				break;
			default:
				break;
		}
		$this->$cmd();
		return true;
	}

	/**
	 * Adds SubTabs for the MainTab "attributes".
	 *
	 * @param type $a_active
	 *        	SubTab which should be activated after method call.
	 */
	protected function setSubTabs($a_active)
	{
		global $ilTabs;
		$ilTabs->setTabActive('attributes');
		// Room attributes
		$ilTabs->addSubTab('roomAttributes', $this->lng->txt('rep_robj_xrs_attributes_for_rooms'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingroomattributesgui', 'showRoomAttributes'));
		// Booking attributes
		$ilTabs->addSubTab('bookingAttributes', $this->lng->txt('rep_robj_xrs_attributes_for_bookings'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingbookingattributesgui', 'showBookingAttributes'));
		$ilTabs->activateSubTab($a_active);
	}

	/**
	 * Shows all room attributes.
	 */
	function showRoomAttributes()
	{
		$this->setSubTabs('roomAttributes');
		$attributes_gui = & new ilRoomSharingRoomAttributesGUI($this);
		$this->ctrl->forwardCommand($attributes_gui);
	}

	/**
	 * Show all booking attributes.
	 */
	function showBookingAttributes()
	{
		$this->setSubTabs('bookingAttributes');
		$attributes_gui = & new ilRoomSharingBookingAttributesGUI($this);
		$this->ctrl->forwardCommand($attributes_gui);
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return Room-ID
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
