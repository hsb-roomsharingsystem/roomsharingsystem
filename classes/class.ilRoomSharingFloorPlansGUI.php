<?php

/**
 * Class ilRoomSharingFloorPlansGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 */
class ilRoomSharingFloorPlansGUI {

    protected $ref_id;
    protected $pool_id;

    /**
     * Konstruktor der Klasse ilRoomSharingFloorPlansGUI
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
     * Kommando ausführen.
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
     * Render list of booking objects
     *
     * uses ilBookingObjectsTableGUI
     */
    function renderObject() {
        global $tpl, $ilCtrl, $ilAccess;
        
        echo "hi ".$this->getPoolId();
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
            $bar = new ilToolbarGUI;
            $bar->addButton($this->lng->txt('rep_robj_xrs_floor_plans_add'), $ilCtrl->getLinkTarget($this, 'create'));
            $bar = $bar->getHTML();
        }

//        include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
//        $plink = new ilPermanentLinkGUI('book', $this->ref_id);
//include_once 'Modules/BookingManager/classes/class.ilBookingObjectsTableGUI.php';
//$table = new ilBookingObjectsTableGUI($this, 'render', $this->ref_id, $this->pool_id, $this->pool_has_schedule);
        include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlansTableGUI.php';
        $table = new ilRoomSharingFloorPlansTableGUI($this, 'showFloorplans', 1);
        $table->setPoolId($this->pool_id);
//$tpl->setContent("hh");

        $tpl->setContent($bar . $table->getHTML());

// $this->showFloorPlansObject();
    }

    /**
     * Gebäudepläne anzeigen.
     */
    function showFloorPlansObject() {
        global $tpl;
        $tpl->setContent("Gebäudeplan");
// Set Sub-Tasks
//   $this->setSubTabs('bookable_rooms');
//	include_once("Modules/RoomSharing/classes/class.ilRoomSharingBookableRoomsGUI.php");
//	$object_gui =& new ilRoomSharingBookableRoomsGUI($this);
//	$this->ctrl->forwardCommand($object_gui);
// Show 'not_implemented' message
//ilUtil::sendInfo($this->lng->txt("room_not_yet_implemented"), false);
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Formular zum hinzufuegen von Gebaeudeplaene
     */
    function initForm($a_mode = "create", $id = NULL) {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form_gui = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($lng->txt("title"), "title");
        $title->setRequired(true);
        $title->setSize(40);
        $title->setMaxLength(120);
        $form_gui->addItem($title);

        $desc = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $desc->setCols(70);
        $desc->setRows(8);
        $form_gui->addItem($desc);

        $file = new ilFileInputGUI($lng->txt("rep_robj_xrs_room_floor_plans"), "upload_file");
        $file->setRequired(true);
        $file->setALlowDeletion(true);
        $form_gui->addItem($file);

//		if ($a_mode == "edit")
//		{
//			$form_gui->setTitle($lng->txt("book_edit_object"));
//
//			$item = new ilHiddenInputGUI('object_id');
//			$item->setValue($id);
//			$form_gui->addItem($item);
//
//			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
//			$obj = new ilBookingObject($id);
//			$title->setValue($obj->getTitle());
//			$desc->setValue($obj->getDescription());
//			$nr->setValue($obj->getNrOfItems());
//			$pdesc->setValue($obj->getPostText());
//			$file->setValue($obj->getFile());
//			$pfile->setValue($obj->getPostFile());
//			
//			if(isset($schedule))
//			{
//				$schedule->setValue($obj->getScheduleId());
//			}
//			
//			$form_gui->addCommandButton("update", $lng->txt("save"));
//		}
//		else
//		{
        $form_gui->setTitle($lng->txt("rep_robj_xrs_floor_plans_add"));
        $form_gui->addCommandButton("save", $lng->txt("save"));
        $form_gui->addCommandButton("render", $lng->txt("cancel"));
//		}
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
     * Save Funktion mit File_Upload vom ILIAS-Service
     */
    function saveObject() {
        global $tpl;
        $form = $this->initForm();



        if ($form->checkInput()) {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Services/MediaObjects/classes/class.ilMediaItem.php");
            $title = $form->getInput("title");
            $description = $form->getInput("description");
            $upload_file = $form->getInput("upload_file");
            $mediaObj = new ilObjMediaObject();
            $mediaObj->setTitle($title);
            $mediaObj->setDescription($description);
            $mediaObj->create();
            $mob_dir = ilObjMediaObject::_getDirectory($mediaObj->getId());
            if (!is_dir($mob_dir)) {
                $mediaObj->createDirectory();
            }
            $media_item = new ilMediaItem();
            $mediaObj->addMediaItem($media_item);
            $media_item->setPurpose("Standard");


            $file_name = ilUtil::getASCIIFilename($upload_file["name"]);
            $file_name = str_replace(" ", "_", $file_name);
            $file = $mob_dir . "/" . $file_name;
            ilUtil::moveUploadedFile($upload_file["tmp_name"], $file_name, $file);
            ilUtil::renameExecutables($mob_dir);
            $format = ilObjMediaObject::getMimeType($file);
            $media_item->setFormat($format);
            $media_item->setLocation($file_name);
            $media_item->setLocationType("LocalFile");
            $mediaObj->update();
            include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/class.ilRoomSharingFloorPlans.php");
            $roomplan = new ilRoomSharingFloorPlans();
            $roomplan->setPoolID($this->getPoolId());
            $result = $roomplan->fileToDatabase($mediaObj->getId());
            if ($result == 1) {
                ilUtil::sendSuccess($this->lng->txt("file_added"), true);
            } else {
                ilUtil::sendFailure("Fehler beim hochladen des Plans", true);
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
     * confirm remove of floorplan
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

        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "render");
        $cgui->setConfirm($this->lng->txt("confirm"), "remove");

//        foreach ($_POST["id"] as $obj_id) {
//            $type = ilMediaPoolItem::lookupType($obj_id);
//            $title = ilMediaPoolItem::lookupTitle($obj_id);
//
//            // check whether page can be removed
//            $add = "";
//
//            $caption = ilUtil::getImageTagByType($type, $this->tpl->tplPath) .
//                    " " . $title . $add;
//
//            $cgui->addItem("id[]", $obj_id, $caption);
//        }

        $this->tpl->setContent($cgui->getHTML());
    }

}

?>
