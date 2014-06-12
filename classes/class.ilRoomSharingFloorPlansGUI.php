<?php

/**
 * Class ilRoomSharingFloorPlansGUI
 *
 * This class represents the view of adding, editing
 * and removing a floorplan.
 * 
 * 
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 *         Thomas Wolscht <t.wolscht@googlemail.com>
 * @version $Id$
 */
class ilRoomSharingFloorPlansGUI {

    protected $ref_id;
    protected $pool_id;
    protected $remove_id;
    protected $file_id1;

    /**
     * constructor ilRoomSharingFloorPlansGUI
     * @param	object	$a_parent_obj
     */
    function __construct(ilObjRoomSharingGUI $a_parent_obj) {
        global $ilCtrl, $lng, $tpl;

        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    /**
     * execute command.
     */
    function executeCommand() {
        global $ilCtrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("render");

        switch ($next_class) {
            default:
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Render list of floorplans
     *
     * uses ilBookingObjectsTableGUI
     */
    function renderObject() {
        global $tpl, $ilCtrl, $ilAccess;

        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $bar = new ilToolbarGUI;
            $bar->addButton($this->lng->txt('rep_robj_xrs_floor_plans_add'), $ilCtrl->getLinkTarget($this, 'create'));
            $bar = $bar->getHTML();
        }
        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
        $plink = new ilPermanentLinkGUI('book', $this->ref_id);
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlansTableGUI.php';
        $table = new ilRoomSharingFloorPlansTableGUI($this, 'showFloorplans', 1);
        $table->setPoolId($this->pool_id);
        $tpl->setContent($bar . $table->getHTML() . $plink->getHTML());
    }

    /**
     * set Content to show Floorplans
     */
    function showFloorPlansObject() {
        global $tpl;
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Form to add or edit a floorplan.
     * 
     * @global type $lng
     * @global type $ilCtrl
     * @param type $a_mode
     * @param type $id
     * @return \ilPropertyFormGUI
     */
    function initForm($a_mode = "create", $id = NULL) {
        global $lng, $ilCtrl;
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form_gui = new ilPropertyFormGUI();
        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $desc = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $desc->setCols(70);
        $desc->setRows(8);
        $file = new ilFileInputGUI($lng->txt("rep_robj_xrs_room_floor_plans"), "upload_file");

        if ($a_mode != "edit") {
            $form_gui->addItem($title);
            $form_gui->addItem($desc);
            $form_gui->addItem($file);
            $form_gui->setTitle($lng->txt("rep_robj_xrs_floor_plans_add"));
            $form_gui->addCommandButton("save", $lng->txt("save"));
            $form_gui->addCommandButton("render", $lng->txt("cancel"));
            $file->setRequired(true);
            $file->setALlowDeletion(true);
        } else {
            $item = new ilHiddenInputGUI('file_id');
            $item->setValue($id);
            $form_gui->addItem($item);
            $radg = new ilRadioGroupInputGUI($this->lng->txt(""), "file_mode");
            $op1 = new ilRadioOption($this->lng->txt("rep_robj_xrs_floor_plans_keep"), "keep", $this->lng->txt("rep_robj_xrs_floor_plans_keep_info"));
            $radg->addOption($op1);
            $op2 = new ilRadioOption($this->lng->txt("rep_robj_xrs_floor_plans_replace"), "replace", $this->lng->txt("rep_robj_xrs_floor_plans_replace_info"));
            $file->setRequired(true);
            $file->setALlowDeletion(true);
            $op2->addSubItem($file);
            $radg->addOption($op2);
            $radg->setValue("keep"); //set standard option (keep floorplan)
            $form_gui->setTitle($lng->txt("rep_robj_xrs_floor_plans_edit"));
            include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php");
            $fplan = new ilRoomSharingFloorPlans();
            $fplaninfo = $fplan->getFloorPlan($id);
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            if (count($fplaninfo) == 1) {
                $mobj = new ilObjMediaObject($fplaninfo[0]["file_id"]);
                $title->setValue($mobj->getTitle());
                $desc->setValue($mobj->getDescription());
            }
            $form_gui->addItem($title);
            $form_gui->addItem($desc);
            $form_gui->addItem($radg);
            $form_gui->addCommandButton("update", $lng->txt("save"));
        }
        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        return $form_gui;
    }

    /**
     * Render creation form
     */
    function createObject() {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('rep_robj_xrs_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * This function is used, if the upload of a file will be done manually
     * and not by using the ILIAS-Service
     * 
     * Save Floor-Plan
     */
//	function saveObject()
//	{
//		global $ilCtrl, $tpl, $lng, $ilTabs;
//
//		$form = $this->initForm();
//		if($form->checkInput())
//		{
//			include_once 'Modules/RoomSharing/classes/class.ilRoomSharingFloorPlans.php';
//			$obj = new ilRoomSharingFloorPlans;
//			$obj->setTitle($form->getInput("title"));
//			//$obj->setDescription($form->getInput("desc"));
//			$obj->setDescription($form->getInput("description"));
//			
//			$file = $form->getItemByPostVar("file");						
//			if($_FILES["file"]["tmp_name"]) 
//			{
//				$obj->uploadFile($_FILES["file"]);
//			}
//			else if($file->getDeletionFlag())
//			{
//				$obj->deleteFile();
//			}		
//			//$obj->save();
//			//$obj->update();
//
//			ilUtil::sendSuccess($lng->txt("room_floor_plans_added"));
//			$this->renderObject();
//		}
//		else
//		{			
//			//$ilTabs->clearTargets();
//			//$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
//
//			$form->setValuesByPost();
//			$tpl->setContent($form->getHTML());
//		}
//	}

    /**
     * Save a new floorplan
     */
    function saveObject() {
        global $tpl;
        $form = $this->initForm();

        if ($form->checkInput()) {
            include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php");
            $fplan = new ilRoomSharingFloorPlans();
            $title_new = $form->getInput("title");
            $desc_new = $form->getInput("description");
            $file_new = $form->getInput("upload_file");
            $result = $fplan->addFloorPlan($title_new, $desc_new, $file_new);
            if ($result == 1) {
                ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_floor_plans_added"), true);
            } else {
                ilUtil::sendFailure($this->lng->txt('rep_robj_xrs_floor_plans_upload_error'), true);
            }
            $this->renderObject();
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Returns roomsharing pool id.
     */
    function getPoolId() {
        return $this->pool_id;
    }

    /**
     * Sets roomsharing pool id.
     */
    function setPoolId($a_pool_id) {
        $this->pool_id = $a_pool_id;
    }

    /**
     * display confirmation message to remove a floorplan
     */
    function confirmDeleteObject() {
        global $ilAccess, $ilCtrl, $lng;
//        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
//            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
//        }
//
//        if (!isset($_POST["id"])) {
//            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
//        }

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "render");
        $cgui->setConfirm($this->lng->txt("confirm"), "removeFloorplan");
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $f = new ilObjMediaObject((int) $_GET['file_id']);
        $cgui->addItem('file_id', (int) $_GET['file_id'], $f->getTitle());
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * execute remove_function to delete the selected floorplan
     */
    function removeFloorplanObject() {
        include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php");
        $fplan = new ilRoomSharingFloorPlans();
        $result = $fplan->deleteFloorPlan((int) $_POST['file_id']);
        if ($result == 1) {
            ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_floor_plans_deleted"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_floor_plans_deleted_error"), true);
        }
        $this->renderObject();
    }

    function editFloorplanObject() {
        global $tpl, $ilCtrl, $tpl, $lng, $ilTabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('rep_robj_xrs_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));
        $fid = (int) $_GET['file_id'];
        $this->setFileId($fid);
        $form2 = $this->initForm($a_mode = "edit", $fid);
        $id = (int) $_GET['file_id'];
        $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'file_id', $id);
        $tpl->setContent($form2->getHTML());
    }

    function setFileId($id) {
        $this->file_id1 = $id;
    }

    function updateObject() {
        global $tpl;
        $file_id = (int) $_POST['file_id'];
        $form = $this->initForm($a_mode = "edit", $file_id);
        if ($form->checkInput()) {
            include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php");
            $fplan = new ilRoomSharingFloorPlans();
            $title_new = $form->getInput("title");
            $desc_new = $form->getInput("description");
            if ($form->getInput("file_mode") == "keep") {
                $fplan->updateFpInfos($file_id, $title_new, $desc_new);
            } else {
                $file_new = $form->getInput("upload_file");
                $fplan->updateFpInfosAndFile($file_id, $title_new, $desc_new, $file_new);
            }
            //echo $form->getInput("upload_file");
            //echo $form->getInput("file_mode");

            $this->renderObject();
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

}

?>
