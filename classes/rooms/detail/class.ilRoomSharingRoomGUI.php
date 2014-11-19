<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilCombinationInputGUI.php");
require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingTextInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumberInputGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/class.ilRoomSharingRoom.php");
require_once('Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
require_once("Services/MediaObjects/classes/class.ilObjMediaObject.php");

/**
 * Class ilRoomSharingRoomGUI.
 * The caller must have implemented method getPoolId().
 * Second argument of the constructor is a room id.
 *
 * @author Thomas Matern
 *
 * @property ilCtrl $ilCtrl;
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 * @property ilPropertyFormGUI $form_gui
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 */
class ilRoomSharingRoomGUI {

	protected $ref_id;
	private $parent_obj;
	private $ilCtrl;
	private $lng;
	private $tpl;
	private $room_id;
	private $pool_id;
	private $form_gui;
	private $room_obj;
	private $ilRoomsharingDatabase;

	const SESSION_ROOM_ID = 'xrs_room_id';

	/**
	 * Constructor of the ilRoomSharingRoomGUI.
	 *
	 * @param object $a_parent_obj Object of the caller
	 * @param integer $a_room_id Room-ID
	 */
	function __construct($a_parent_obj, $a_room_id) {
		global $ilCtrl, $lng, $tpl;

		if (!empty($a_room_id)) {
			$this->room_id = $a_room_id;
			$this->setSessRoomId($a_room_id);
		} else {
			$this->room_id = $this->getSessRoomId();
		}
		$this->ref_id = $a_parent_obj->ref_id;
		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();

		$this->ilCtrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;

		$this->room_obj = & new IlRoomSharingRoom($this->pool_id, $this->room_id);
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($this->pool_id);
	}

	/**
	 * Main switch for command execution.
	 * @return true
	 */
	function executeCommand() {
		$cmd = $this->ilCtrl->getCmd('showRoom');
		$cmd .= 'Object';
		$this->$cmd();
		return true;
	}

	/**
	 * Show room.
	 */
	function showRoomObject() {
		global $ilAccess;

		if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
			$toolbar = new ilToolbarGUI();
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_room_edit'), $this->ilCtrl->getLinkTarget($this, "editRoom"));
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_add_room'), $this->ilCtrl->getLinkTarget($this, "addRoom"));
		}
		$this->form_gui = $this->initForm("show");

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui
						->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	function addRoomObject() {
		$this->room_obj = & new IlRoomSharingRoom($this->pool_id, "", true);

		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ilCtrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
		$this->form_gui = $this->initForm("create");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	function editRoomObject() {
		$this->room_obj = new IlRoomSharingRoom($this->pool_id, $this->room_id);
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ilCtrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
//		ilUtil::sendInfo($this->lng->txt('rep_robj_xrs_not_yet_implemented'));
		$this->form_gui = $this->initForm("edit");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("saveRoom", $this->lng->txt("save"));

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Build property form.
	 *
	 * @param string $a_mode
	  Mode of the form
	 * @return ilPropertyFormGUI GUI for a room.
	 */
	function initForm($a_mode = "show") {
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

		$min_alloc = new ilRoomSharingNumberInputGUI(
				$this->lng->txt("rep_robj_xrs_room_min_alloc"), "min_alloc");
		$min_alloc->setDisabled(true);
		$form_gui->addItem($min_alloc);

		$max_alloc = new ilRoomSharingNumberInputGUI(
				$this->lng->txt("rep_robj_xrs_room_max_alloc"), "max_alloc");
		$max_alloc->setDisabled(true);
		$form_gui->addItem($max_alloc);
		//$building_id = new ilRoomSharingNumberInputGUI(
		//		$this->lng->txt("rep_robj_xrs_room_floor_plans"), "building_id");
		//$building_id->setDisabled(true);
		//$form_gui->addItem($building_id);
		$options = array();
		$options["title"] = " - ohne Zuordnung - ";
		foreach ($this->ilRoomsharingDatabase->getAllFloorplans() as $fplans) {
			$options[$fplans["title"]] = $fplans["title"]; // . $fplans["file_id"];
		}
		$building_id = new ilSelectInputGUI(
				$this->lng->txt("rep_robj_xrs_room_floor_plans"), "building_id");
		$building_id->setOptions($options);
		$building_id->setDisabled(true);

		$mobj = new ilObjMediaObject(295);
		$med = $mobj->getMediaItem("Standard");
		$target = $med->getThumbnailTarget();
		$imageWithLink = "<a target='_blank' href='" . $mobj->getDataDirectory() . "/" . $med->getLocation() . "'>" . ilUtil::img($target) . "</a>";

		$building_id->setInfo($imageWithLink);
		$form_gui->addItem($building_id);

		if ($a_mode == "edit" || $a_mode == "create") {
			$name->setDisabled(false);
			$name->setRequired(true);
			$type->setDisabled(false);
			$min_alloc->setDisabled(false);
			$min_alloc->setRequired(true);
			$max_alloc->setDisabled(false);
			$max_alloc->setRequired(true);
			$building_id->setDisabled(false);

			if ($a_mode == "create") {
				$form_gui->addCommandButton($this->ilCtrl->getLinkTarget($this, "addRoom"
						), $this->lng->txt("rep_robj_xrs_add_room"));
			} else {
				$form_gui->addCommandButton("saveRoom", $this->lng->txt("rep_robj_xrs_save_room"));
			}
		}
		if ($a_mode == "edit" || $a_mode == "show") {
			$name->setValue($this->room_obj->getName());
			$type->setValue($this->room_obj->getType());
			$min_alloc->setValue($this->room_obj->getMinAlloc());
			$max_alloc->setValue($this->room_obj->getMaxAlloc());
			$building_id->setValue($this->room_obj->getBuildingId());

			$post = new ilFormSectionHeaderGUI();
			$post->setTitle($this->lng->txt("rep_robj_xrs_room_attributes"));
			$form_gui->addItem($post);

			$attributes = $this->room_obj->getAttributes();

			$allAvailable = $this->room_obj->getAllAvailableAttributes();

			foreach ($allAvailable as $attr) {
				$attrField = new ilRoomSharingNumberInputGUI($attr['name'], 'attr_' . $attr['id']);
				$attrField->setValue($this->findAttributeAmount($attr['id'], $attributes));
				$attrField->setDisabled(($a_mode == "show"));
				$form_gui->addItem($attrField);
			}
		}

		$form_gui->setFormAction($this->ilCtrl->getFormAction($this));

		return $form_gui;
	}

	/**
	 * 	Searches for an attribute in already defined attributes of an room and returns its amount.
	 *
	 * @param integer $a_attribute_id
	 * @param array with assoc arrays $a_room_attributes
	 * @return integer amount
	 */
	private function findAttributeAmount($a_attribute_id, $a_room_attributes) {
		foreach ($a_room_attributes as $attr) {
			if ($attr['id'] == $a_attribute_id) {
				$rVal = $attr['id'];
				break;
			}
		}
		return $rVal;
	}

	/**
	 * Handle the save command in creation room.
	 */
	public function createRoomObject() {
		$this->form_gui = $this->initForm("create");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));
		$this->form_gui->setValuesByPost();
		if ($this->form_gui->checkInput()) {
			$this->room_obj = new ilRoomSharingRoom($this->pool_id, "", true);
			$this->room_obj->setPoolId($this->pool_id);
			$this->room_obj->setName($this->form_gui->getInput("name"));
			$this->room_obj->setType($this->form_gui->getInput("type"));
			$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
			$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
			$this->room_obj->setBuildingId(
					$this->form_gui->getInput("building_id"));

			$newRoomId = $this->room_obj->create();
			if (ilRoomSharingNumericUtils::isPositiveNumber($newRoomId)) {
				ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_room_added"));
				$this->room_obj->setId($newRoomId);
				$this->room_obj = new ilRoomSharingRoom($this->pool_id, $newRoomId);

				$toolbar = new ilToolbarGUI();
				$toolbar->addButton(
						$this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ilCtrl->getLinkTargetByClass(
								'ilroomsharingroomsgui', "showRooms"));
				$this->tpl->setContent($toolbar->getHTML());
			} else {
				ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_wrong_input"));
				$this->form_gui->setValuesByPost();
				$this->tpl->setContent($this->form_gui->getHTML());
			}
		} else {
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	/**
	 * Handle save command in edit form.
	 */
	function saveRoomObject() {
		$this->form_gui = $this->initForm("edit");
		if ($this->form_gui->checkInput()) {
			$this->room_obj->setName($this->form_gui->getInput("name"));
			$this->room_obj->setType($this->form_gui->getInput("type"));
			$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
			$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
			$this->room_obj->setBuildingId($this->form_gui->getInput("building_id"));

			$this->room_obj->save();
			$this->showRoomObject();
		} else {
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	private function getSessRoomId() {
		return unserialize($_SESSION[self::SESSION_ROOM_ID]);
	}

	private function setSessRoomId($a_room_id) {
		return $_SESSION[self::SESSION_ROOM_ID] = serialize($a_room_id);
	}

	/**
	 * Returns roomsharing pool id.
	 * @returns integer Pool-ID
	 */
	public function getPoolId() {
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 * @param integer Pool-ID
	 */
	public function setPoolId(
	$a_pool_id) {
		$this->pool_id = $a_pool_id;
	}

}

?>
