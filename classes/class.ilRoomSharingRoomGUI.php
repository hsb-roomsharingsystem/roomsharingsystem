<?php

/**
 * Class ilRoomSharingRoomGUI. 
 * The caller must have implemented method getPoolId().
 * Second argument of the constructor is a room id.
 *
 * @author Thomas Matern
 */
class ilRoomSharingRoomGUI
{

	protected $room_id;
	protected $ref_id;
	protected $pool_id;
	protected $form_gui;
	protected $room_obj;
	protected $parent_obj;
	protected $ctrl;
	protected $lng;
	protected $tpl;

	/**
	 * Constructor of the ilRoomSharingRoomGUI.
	 *
	 * @param object $a_parent_obj Object of the caller
	 * @param integer $a_room_id Room-ID
	 */
	function __construct($a_parent_obj, $a_room_id)
	{
		global $ilCtrl, $lng, $tpl;

		$this->room_id = $a_room_id;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->parent_obj = $a_parent_obj;
		$this->pool_id = $a_parent_obj->getPoolId();

		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}

	/**
	 * Main switch for command execution.
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		switch ($next_class)
		{
			default:
			$cmd = $this->ctrl->getCmd('showRoom');
			$cmd .= 'Object';
			$this->$cmd();
			break;
		}
		return true;
	}

	/**
	 * Show room.
	 */
	function showRoomObject()
	{
		global $ilAccess;

		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoom.php");
		$this->room_obj = new IlRoomSharingRoom($this->room_id);

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$toolbar = new ilToolbarGUI();
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_room_edit'), $this->ctrl->getLinkTarget($this, "editRoom"));
			$toolbar->addButton($this->lng->txt('rep_robj_xrs_add_room'), $this->ctrl->getLinkTarget($this, "addRoom"));
		}
		$this->form_gui = $this->initForm("show");

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	function addRoomObject()
	{
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoom.php");
		$this->room_obj = new IlRoomSharingRoom("", true);

		include_once ('Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
		$this->form_gui = $this->initForm("create", true);
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));

		$this->tpl->setContent($toolbar->getHTML() . $this->form_gui->getHTML());
	}

	/**
	 * Create new form to add an room.
	 */
	function editRoomObject()
	{
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoom.php");
		$this->room_obj = new IlRoomSharingRoom((int) $_GET['room_id']);

		include_once ('Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ctrl->getLinkTargetByClass('ilroomsharingroomsgui', "showRooms"));
		ilUtil::sendInfo($this->lng->txt('rep_robj_xrs_not_yet_implemented'));
		$this->tpl->setContent($toolbar->getHTML());
	}

	/**
	 * Build property form.
	 *
	 * @param string $a_mode Mode of the form
	 * @return ilPropertyFormGUI GUI for a room.
	 */
	function initForm($a_mode = "show")
	{
		include_once ("Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingTextInputGUI.php");
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingNumberInputGUI.php");
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoom.php");

		$form_gui = new ilPropertyFormGUI();
		$form_gui->setTitle($this->lng->txt("rep_robj_xrs_room_properties"));
		$form_gui->setDescription(
			$this->lng->txt("rep_robj_xrs_room_prop_description"));

		$hiddenId = new ilHiddenInputGUI('room_id');
		if (!empty($this->room_id))
		{
			$hiddenId->setValue($this->room_id);
		}
		else
		{
			$hiddenId->setValue((int) $_GET['room_id']);
		}
		$form_gui->addItem($hiddenId);

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

		$file_id = new ilRoomSharingNumberInputGUI(
			$this->lng->txt("rep_robj_xrs_room_file_id"), "file_id");
		$file_id->setDisabled(true);
		$form_gui->addItem($file_id);
		
		$building_id = new ilRoomSharingNumberInputGUI(
			$this->lng->txt("rep_robj_xrs_room_floor_plans"), "building_id");
		$building_id->setDisabled(true);
		$form_gui->addItem($building_id);

		if ($a_mode == "edit" || $a_mode == "create")
		{
			$name->setDisabled(false);
			$name->setRequired(true);
			$type->setDisabled(false);
			$min_alloc->setDisabled(false);
			$min_alloc->setRequired(true);
			$max_alloc->setDisabled(false);
			$max_alloc->setRequired(true);
			$file_id->setDisabled(false);
			$building_id->setDisabled(false);
			
			if ($a_mode == "create")
			{
			$form_gui->addCommandButton($this->ctrl->getLinkTarget($this, "addRoom"), $this->lng->txt("rep_robj_xrs_add_room"));
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
			$min_alloc->setValue($this->room_obj->getMinAlloc());
			$max_alloc->setValue($this->room_obj->getMaxAlloc());
			$file_id->setValue($this->room_obj->getFileId());
			$building_id->setValue($this->room_obj->getBuildingId());

			$post = new ilFormSectionHeaderGUI();
			$post->setTitle($this->lng->txt("rep_robj_xrs_room_attributes"));
			$form_gui->addItem($post);
			$attributes = $this->room_obj->getAttributes();
			foreach ($attributes as $attr)
			{
			$attrField = new ilRoomSharingNumberInputGUI($attr['name'], $attr['name']);
			$attrField->setValue($attr['count']);
			$attrField->setDisabled(true);
			$form_gui->addItem($attrField);
			}
		}
		
		$form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		return $form_gui;
	}

	/**
	 * Handle the save command in creation room.
	 */
	function createRoomObject()
	{
		$this->form_gui = $this->initForm("create");
		$this->form_gui->clearCommandButtons();
		$this->form_gui->addCommandButton("createRoom", $this->lng->txt("save"));
		$this->form_gui->setValuesByPost();
		if ($this->form_gui->checkInput())
		{
			include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingRoom.php");
			$this->room_obj = new ilRoomSharingRoom("", true);
			$this->room_obj->setPoolId($this->pool_id);
			$this->room_obj->setName($this->form_gui->getInput("name"));
			$this->room_obj->setType($this->form_gui->getInput("type"));
			$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
			$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
			$this->room_obj->setFileId($this->form_gui->getInput("file_id"));
			$this->room_obj->setBuildingId(
				$this->form_gui->getInput("building_id"));

			$newRoomId = $this->room_obj->create();
			if (!empty($newRoomId) && is_numeric($newRoomId))
			{
			ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_room_added"));
			$this->room_obj->setId($newRoomId);
			$this->room_obj = new ilRoomSharingRoom($newRoomId);
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$toolbar = new ilToolbarGUI();
			$toolbar->addButton(
				$this->lng->txt('rep_robj_xrs_back_to_rooms'), $this->ctrl->getLinkTargetByClass(
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
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	/**
	 * Handle save command in edit form.
	 */
	function saveRoomObject()
	{
		$this->form_gui = $this->initForm("edit");
		if ($this->form_gui->checkInput())
		{
			$this->room_obj->setName($this->form_gui->getInput("name"));
			$this->room_obj->setType($this->form_gui->getInput("type"));
			$this->room_obj->setMinAlloc($this->form_gui->getInput("min_alloc"));
			$this->room_obj->setMaxAlloc($this->form_gui->getInput("max_alloc"));
			$this->room_obj->setFileId($this->form_gui->getInput("file_id"));
			$this->room_obj->setBuildingId(
				$this->form_gui->getInput("building_id"));

			$this->room_obj->save();
			$this->showRoomObject();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	/**
	 * Returns roomsharing pool id.
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>