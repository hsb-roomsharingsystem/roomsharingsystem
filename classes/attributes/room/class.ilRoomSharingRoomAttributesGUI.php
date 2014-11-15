<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/attributes/room/class.ilRoomSharingRoomAttributes.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingAttributesException.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Class ilRoomSharingRoomAttributesGUI
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 * @property ilPropertyFormGUI $attributesForm
 */
class ilRoomSharingRoomAttributesGUI
{
	protected $ref_id;
	private $pool_id;
	private $attributesForm;
	private $ctrl;
	private $lng;
	private $tpl;

	/**
	 * Constructor of ilRoomSharingRoomAttributesGUI
	 *
	 * @global ilCtrl $ilCtrl
	 * @global ilLanguage $lng
	 * @global ilTemplate $tpl
	 * @param ilRoomSharingAttributesGUI $a_parent_obj
	 */
	function __construct(ilRoomSharingAttributesGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;

		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}

	/**
	 * Command execution.
	 *
	 * @return Returns always true.
	 */
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd("showRoomAttributes");
		if ($cmd == 'render')
		{
			$cmd = 'showRoomAttributes';
		}
		$this->$cmd();
		return true;
	}

	/**
	 * Shows all available attributes.
	 */
	public function showRoomAttributes()
	{
		$this->createAttributesForm();
		$this->setAttributesFormValues();
		$this->tpl->setContent($this->attributesForm->getHTML());
	}

	/**
	 * Save attributes provided by the user.
	 */
	public function saveRoomAttributes()
	{
		$this->createAttributesForm();
		if ($this->attributesForm->checkInput())
		{
			$attributes = $this->attributesForm->getInput('attributes');
			$roomSharingRoomAttributes = new ilRoomSharingRoomAttributes($this->pool_id);
			try
			{
				$affected = $roomSharingRoomAttributes->updateAttributes($attributes);
			}
			catch (ilRoomSharingAttributesException $exc)
			{
				ilUtil::sendFailure($this->lng->txt($exc->getMessage()), true);
				$this->ctrl->redirect($this, 'showRoomAttributes');
			}
			ilUtil::sendSuccess($this->createUpdateMessage($affected), true);
			$this->ctrl->redirect($this, 'showRoomAttributes');
		}
		$this->settingsForm->setValuesByPost();
		$this->tpl->setContent($this->settingsForm->getHtml());
	}

	/**
	 * Creates an update message for the user.
	 *
	 * @param array $a_affected with 'deleted', 'deletedAssigments' and 'inserted' of type integer
	 */
	private function createUpdateMessage($a_affected)
	{
		$message[] = $this->lng->txt('msg_obj_modified');
		$message[] = '. ';
		$message[] = ' ';
		$message[] = $a_affected['inserted'] ? $a_affected['inserted'] : 0;
		$message[] = ' ';
		$message[] = $this->lng->txt('rep_robj_xrs_attributes_inserted');
		$message[] = ', ';
		$message[] = $a_affected['deleted'] ? $a_affected['deleted'] : 0;
		$message[] = ' ';
		$message[] = $this->lng->txt('rep_robj_xrs_attributes_deleted');
		$message[] = ' ';
		$message[] = $this->lng->txt('rep_robj_xrs_and');
		$message[] = ' ';
		$message[] = $a_affected['deletedAssigments'] ? $a_affected['deletedAssigments'] : 0;
		$message[] = ' ';
		$message[] = $this->lng->txt('rep_robj_xrs_attribute_room_assigns_deleted');
		$message[] = ' ';
		return implode($message);
	}

	/**
	 * Creates the attributes form.
	 * It contains only an an multiple field which represents all available attributes.
	 */
	private function createAttributesForm()
	{
		$this->attributesForm = new ilPropertyFormGUI();
		$this->attributesForm->setDescription($this->lng->txt('rep_robj_xrs_attributes_for_rooms_desc'));

		$attributesFields = new ilTextInputGUI(
			$this->lng->txt('rep_robj_xrs_actual_attributes_for_rooms'), 'attributes');
		$attributesFields->setMulti(true, false, true);
		$attributesFields->setRequired(true);
		$attributesFields->setMaxLength(45);
		$this->attributesForm->addItem($attributesFields);

		$this->attributesForm->addCommandButton('saveRoomAttributes', $this->lng->txt('save'));
		$this->attributesForm->setTitle($this->lng->txt('rep_robj_xrs_edit_attributes'));
		$this->attributesForm->setFormAction($this->ctrl->getFormAction($this));
	}

	/**
	 * Sets available attributes data in the attributes form.
	 */
	private function setAttributesFormValues()
	{
		$roomSharingRoomAttributes = new ilRoomSharingRoomAttributes($this->pool_id);
		$formData['attributes'] = $roomSharingRoomAttributes->getAllAvailableAttributesNames();
		$this->attributesForm->setValuesByArray($formData);
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return integer Pool-ID
	 */
	public function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer Pool-ID
	 *
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
