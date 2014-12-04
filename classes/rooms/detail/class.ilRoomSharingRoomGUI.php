<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingStringUtils.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/class.ilRoomSharingRoom.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRoomsGUI.php");
require_once('Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
require_once("Services/MediaObjects/classes/class.ilObjMediaObject.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingRoomException.php");

/**
 * Class ilRoomSharingRoomGUI.
 *
 * The caller must implement method getPoolId().
 * Second argument of the constructor is a room id.
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @author Thomas Wolscht <twolscht@stud.hs-bremen.de>
 *
 * @property ilCtrl $ctrl;
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 * @property ilPropertyFormGUI $form_gui
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 */
class ilRoomSharingRoomGUI
{
	protected $ref_id;
	private $parent_obj;
	private $ctrl;
	private $lng;
	private $tpl;
	private $room_id;
	private $pool_id;
	private $form_gui;
	private $room_obj;
	private $ilRoomsharingDatabase;
	private $room_floorplan;

	const SESSION_ROOM_ID = 'xrs_room_id';
	const ATTRIBUTE_ID_PREFIX = 'room_attr_id_';

	/**
	 * Constructor of the ilRoomSharingRoomGUI.
	 *
	 * @param object $a_parent_obj Object of the caller
	 * @param integer $a_room_id Room-ID
	 */
	public function __construct($a_parent_obj, $a_room_id)
	{
		global $ilCtrl, $lng, $tpl;

		if (!empty($a_room_id))
		{
			$this->room_id = $a_room_id;
			$this->setSessRoomId($a_room_id);
		}
		else
		{
			$this->room_id = $this->getSessRoomId();
		}
		$this->ref_id = $a_parent_obj->ref_id;
		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;

		$this->room_obj = & new IlRoomSharingRoom($this->pool_id, $this->room_id);
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Main switch for command execution.
	 * @return true
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd('showRoom');
		$this->$cmd();
		return true;
	}

	/**
	 * Adds SubTabs for the MainTab "Rooms".
	 *
	 * @param type $a_active
	 *        	SubTab which should be activated after method call.
	 */
	protected function setSubTabs($a_active)
	{
		global $ilTabs;
		$ilTabs->setTabActive('rooms');

		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $this->room_id);

		// Roominfo
		$ilTabs->addSubTab('room', $this->lng->txt('rep_robj_xrs_room'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingroomgui', 'showRoom'));

		// week-view
		$ilTabs->addSubTab('weekview', $this->lng->txt('rep_robj_xrs_room_occupation'),
			$this->ctrl->getLinkTargetByClass('ilRoomSharingCalendarWeekGUI', 'show'));
		$ilTabs->activateSubTab($a_active);
	}

	/**
	 * Show room.
	 */
	public function showRoom()
	{
		$this->setSubTabs('room');

		global $ilAccess;

		$this->room_obj = new IlRoomSharingRoom($this->pool_id, $this->room_id);

		$toolbar = new ilToolbarGUI();
		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_room_edit'),
				$this->ctrl->getLinkTarget($this, "editRoom"));
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_add_room'),
				$this->ctrl->getLinkTarget($this, "addRoom"));
		}
		$this->form_gui = $this->initForm("show");
		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	public function addRoom()
	{
		$this->room_obj = & new IlRoomSharingRoom($this->pool_id, "", true);

		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
		$this->form_gui = $this->initForm("create");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	public function editRoom()
	{
		$this->room_obj = new IlRoomSharingRoom($this->pool_id, (int) $_GET['room_id']);
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));

		$this->form_gui = $this->initForm("edit");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("saveRoom", $this->lng->txt("save"));

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Build property form.
	 *
	 * @param string $a_mode Mode of the form
	 * @return ilPropertyFormGUI GUI for a room.
	 */
	private function initForm($a_mode = "show")
	{
		$form_gui = & new ilPropertyFormGUI();
		$form_gui->setMultipart(true);
		$form_gui->setTitle($this->lng->txt("rep_robj_xrs_room_properties"));
		$form_gui->setDescription(
			$this->lng->txt("rep_robj_xrs_room_prop_description"));

		$name = new ilRoomSharingTextInputGUI(
			$this->lng->txt("rep_robj_xrs_room_name"), "name");
		$name->setDisabled(true);
		$form_gui->addItem($name);

		$type = new ilRoomSharingTextInputGUI(
			$this->lng->txt("rep_robj_xrs_room_type"), "type");
		$type->setDisabled(true);
		$form_gui->addItem($type);

		$minAlloc = new ilRoomSharingNumberInputGUI(
			$this->lng->txt("rep_robj_xrs_room_min_alloc"), "min_alloc");
		$minAlloc->setDisabled(true);
		$form_gui->addItem($minAlloc);

		$maxAlloc = new ilRoomSharingNumberInputGUI(
			$this->lng->txt("rep_robj_xrs_room_max_alloc"), "max_alloc");
		$maxAlloc->setDisabled(true);
		$form_gui->addItem($maxAlloc);

		$buildingId = new ilSelectInputGUI(
			$this->lng->txt("rep_robj_xrs_room_floor_plans"), "building_id");
		$buildingId->setOptions($this->room_obj->getAllFloorplans());
		$buildingId->setDisabled(true);
		$form_gui->addItem($buildingId);

		$attributesHeader = new ilFormSectionHeaderGUI();
		// Ilias FormSectionHeader has a bug. Info is not setable.
		$info = '<div class=' . "'ilFormInfo'" . "style='font-weight: normal !important;'>"
			. $this->lng->txt("rep_robj_xrs_room_attributes_info") . '</div>';
		$attributesHeader->setTitle($this->lng->txt("rep_robj_xrs_room_attributes") . $info);
		$form_gui->addItem($attributesHeader);

		foreach ($this->room_obj->getAllAvailableAttributes() as $attr)
		{
			$attrField = new ilRoomSharingNumberInputGUI($attr['name'],
				self::ATTRIBUTE_ID_PREFIX . $attr['id']);
			$attrField->setValue($this->room_obj->findAttributeAmount($attr['id']));
			$attrField->setDisabled(($a_mode == "show"));
			$form_gui->addItem($attrField);
		}

		if ($a_mode == "edit" || $a_mode == "create")
		{
			$name->setDisabled(false);
			$name->setRequired(true);
			$type->setDisabled(false);
			$minAlloc->setDisabled(false);
			$minAlloc->setRequired(true);
			$minAlloc->setMinValue(0);
			$maxAlloc->setDisabled(false);
			$maxAlloc->setRequired(true);
			$maxAlloc->setMinValue(0);
			$buildingId->setDisabled(false);

			if ($a_mode == "create")
			{
				$form_gui->addCommandButton($this->ctrl->getLinkTarget($this, "addRoom"
					), $this->lng->txt("rep_robj_xrs_add_room"));
			}
			else
			{
				$form_gui->addCommandButton("saveRoom", $this->lng->txt("rep_robj_xrs_save_room"));
			}
		}
		if ($a_mode == "edit" || $a_mode == "show")
		{
			$name->setValue($this->room_obj->getName());
			$type->setValue($this->room_obj->getType());
			$minAlloc->setValue($this->room_obj->getMinAlloc());
			$maxAlloc->setValue($this->room_obj->getMaxAlloc());
			$buildingId->setValue($this->room_obj->getBuildingId());
			if ($a_mode == "show")
			{
				$buildingId->setDisabled(true);
				$mobj = new ilObjMediaObject($this->room_obj->getBuildingId());
				$mitems = $mobj->getMediaItems();
				if (!empty($mitems))
				{
					$med = $mobj->getMediaItem("Standard");
					$target = $med->getThumbnailTarget();
					$imageWithLink = "<br><a target='_blank' href='" . $mobj->getDataDirectory() . "/" . $med->getLocation() . "'>" . ilUtil::img($target) . "</a>";
					$buildingId->setInfo($imageWithLink);
				}
			}
		}

		$form_gui->setFormAction($this->ctrl->getFormAction($this));

		return $form_gui;
	}

	/**
	 * Handle the save command in creation room.
	 */
	public function createRoom()
	{

		$this->form_gui = $this->initForm("create");
		$this->form_gui->setValuesByPost();
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));
		if ($this->form_gui->checkInput())
		{
			try
			{
				$this->room_obj = new ilRoomSharingRoom($this->pool_id, "", true);
				$this->room_obj->setPoolId($this->pool_id);
				$this->room_obj->setName($this->form_gui->getInput("name"));
				$this->room_obj->setType($this->form_gui->getInput("type"));
				$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
				$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
				$this->room_obj->setBuildingId($this->form_gui->getInput("building_id"));

				foreach ($this->getUserWishedAttributes() as $userWishedAttribute)
				{
					$this->room_obj->addAttribute($userWishedAttribute['id'], $userWishedAttribute['count']);
				}

				$newRoomId = $this->room_obj->create();
			}
			catch (ilRoomSharingRoomException $exc)
			{
				ilUtil::sendFailure($this->lng->txt($exc->getMessage()));
				$this->form_gui->setValuesByPost();
				$this->tpl->setContent($this->form_gui->getHTML());
			}

			if (ilRoomSharingNumericUtils::isPositiveNumber($newRoomId))
			{
				ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_room_added"));
				$this->room_obj->setId($newRoomId);
				$this->setSessRoomId($newRoomId);
				$this->room_obj = new ilRoomSharingRoom($this->pool_id, $newRoomId);

				$toolbar = new ilToolbarGUI();
				$toolbar->addButton(
					$this->lng->txt('rep_robj_xrs_back_to_rooms'),
					$this->ctrl->getLinkTargetByClass(
						'ilroomsharingroomsgui', "showRooms"));
				$this->tpl->setContent($toolbar->getHTML());
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_wrong_input"));
				$this->form_gui->setValuesByPost();
				$this->tpl->setContent($this->form_gui->getHTML());
			}
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui
					->getHTML());
		}
	}

	/**
	 * Handle save command in edit form.
	 */
	public function saveRoom()
	{
		$this->form_gui = $this->initForm("edit");
		$this->form_gui->setValuesByPost();
		if ($this->form_gui->checkInput())
		{
			try
			{
				$this->room_obj->setName($this->form_gui->getInput("name"));
				$this->room_obj->setType($this->form_gui->getInput("type"));
				$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
				$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
				$this->room_obj->setBuildingId($this->form_gui->getInput("building_id"));

				$this->room_obj->resetAttributes();

				foreach ($this->getUserWishedAttributes() as $userWishedAttribute)
				{
					$this->room_obj->addAttribute($userWishedAttribute['id'], $userWishedAttribute['count']);
				}

				$this->room_obj->save();
				$this->showRoom();
			}
			catch (ilRoomSharingRoomException $exc)
			{
				ilUtil::sendFailure($this->lng->txt($exc->getMessage()));
				$this->form_gui->setValuesByPost();
				$this->tpl->setContent($this->form_gui->getHTML());
			}
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui
					->getHTML());
		}
	}

	/**
	 * Execute deletion of an room after cofirmation.
	 */
	public function deleteRoom()
	{
		try
		{
			$this->room_obj->delete();
			ilUtil::sendSuccess($this->lng->txt('rep_robj_xrs_room_delete_success'), true);
		}
		catch (ilRoomSharingRoomException $exc)
		{
			ilUtil::sendFailure($this->lng->txt($exc->getMessage()));
			$this->ctrl->redirectByClass('ilroomsharingroomsgui', 'showRooms');
		}
		$this->ctrl->redirectByClass('ilroomsharingroomsgui', 'showRooms');
	}

	/**
	 * 	Show confirmation dialog, before deleting a room.
	 */
	public function confirmDeleteRoom()
	{
		global $ilTabs;
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('rep_robj_xrs_back_to_rooms'),
			$this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "showRooms");
		$cgui->setConfirm($this->lng->txt("confirm"), 'deleteRoom');
		$amountOfBookings = $this->room_obj->getAffectedAmountBeforeDelete();
		if ($amountOfBookings > 0)
		{
			$cgui->setHeaderText($this->lng->txt('rep_robj_xrs_room_delete'));
			ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_room_delete_booking') . " <b>" . $amountOfBookings . "</b>");
		}
		else
		{
			$cgui->setHeaderText($this->lng->txt('rep_robj_xrs_room_delete'));
		}
		$cgui->addItem('booking_id', $this->room_id, $this->room_obj->getName());
		$this->tpl->setContent($cgui->getHTML());
	}

	/**
	 * Returns an associative array with ids and uesr wished amounts.
	 *
	 * @return assoc array with attributes ids and amounts.
	 */
	private function getUserWishedAttributes()
	{

		$allInputItems = $this->form_gui->getInputItemsRecursive();
		$userWishedAttributes = array();

		foreach ($allInputItems as $inputItem)
		{
			if ($this->isUserWishedAttribute($inputItem))
			{
				$userWishedAttributes[] = array(
					'id' => $this->getAttributeIdFromInput($inputItem),
					'count' => $inputItem->getValue());
			}
		}
		return $userWishedAttributes;
	}

	private function showRooms()
	{
		$this->ctrl->redirectByClass('ilroomsharingroomsgui', 'showRooms');
	}

	/**
	 * Returns true if the input field is an attribute and has a valid amount.
	 *
	 * @param $a_inputItem (a form input field)
	 * @return boolean
	 */
	private function isUserWishedAttribute($a_inputItem)
	{
		$rVal = FALSE;
		if (!empty($a_inputItem))
		{
			$postVar = $a_inputItem->getPostVar();
			if (!empty($postVar) && ilRoomSharingStringUtils::startsWith($postVar, self::ATTRIBUTE_ID_PREFIX))
			{
				$rVal = ilRoomSharingNumericUtils::isPositiveNumber($a_inputItem->getValue(), true);
			}
		}
		return $rVal;
	}

	/**
	 * Retrieves the attribute id from the attribute input field.
	 *
	 * @param $a_inputItem (a form input field)
	 * @return integer
	 */
	private function getAttributeIdFromInput($a_inputItem)
	{
		return substr($a_inputItem->getPostVar(), strlen(self::ATTRIBUTE_ID_PREFIX));
	}

	/**
	 * Returns the room id which was saved in the session.
	 *
	 * @return integer
	 */
	private function getSessRoomId()
	{
		return unserialize($_SESSION[
			self::SESSION_ROOM_ID]);
	}

	/**
	 * Saves the room id in the session.
	 *
	 * @param integer  $a_room_id
	 */
	private function setSessRoomId(
	$a_room_id)
	{
		$_SESSION[self
			::SESSION_ROOM_ID
			] = serialize($a_room_id);
	}

	/**
	 * Returns roomsharing pool id.
	 * @returns integer Pool-ID
	 */
	public function getPoolId()
	{
		return $this->pool_id
		;
	}

	/**
	 * Sets roomsharing pool id.
	 * @param integer Pool-ID
	 */
	public function setPoolId($a_pool_id)
	{
		$this->
			pool_id = $a_pool_id;
	}

}

?>
