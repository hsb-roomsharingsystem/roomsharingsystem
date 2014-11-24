<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingClassPrivilegesTableGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingClassGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivilegesConstants.php");
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Search/classes/class.ilRepositorySearchGUI.php");

use ilRoomSharingPrivilegesConstants as PRIVC;

/**
 * Class ilRoomSharingPrivilegesGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilCtrl $ctrl
 * @property ilLanguage $lng
 * @property ilTemplate $tpl
 *
 * @ilCtrl_Calls ilRoomSharingPrivilegesGUI: ilRoomSharingClassGUI, ilRepositorySearchGUI
 */
class ilRoomSharingPrivilegesGUI
{
	protected $ref_id;
	protected $pool_id;
	private $parent;
	private $ctrl;
	private $lng;
	private $tpl;
	private $tabs;
	private $privileges;
	private $user;
	private $permission;

	CONST SELECT_INPUT_NONE_OFFSET = 1;

	/**
	 * Constructor of ilRoomSharingPrivilegesGUI
	 *
	 * @global type $ilCtrl for navigating through GUI classes
	 * @global type $lng for translations
	 * @global type $tpl used for setting HTML content
	 * @global type $ilTabs for setting tabs
	 * @global type $ilUser used for determining user information
	 * @global type $rssPermission for retrieving user privilege information
	 * @param ilObjRoomSharingGUI $a_parent_obj needed for the pool id
	 */
	public function __construct(ilObjRoomSharingGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl, $ilTabs, $ilUser, $rssPermission;

		$this->parent = $a_parent_obj;
		$this->ref_id = $this->parent->ref_id;
		$this->pool_id = $this->parent->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->user = $ilUser;
		$this->permission = $rssPermission;
		$this->privileges = new ilRoomSharingPrivileges();
	}

	/**
	 * Executes the command given by ilCtrl.
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		if ($next_class == "ilroomsharingclassgui")
		{
			$this->renderClassGui();
		}
		else
		{
			$this->executeDefaultCommand();
		}
	}

	/**
	 * Renders the GUI of the class the users wants to edit.
	 */
	private function renderClassGui()
	{
		$class_id = (int) $_GET["class_id"];
		$this->ctrl->setReturn($this, "showPrivileges");
		$this->class_gui = new ilRoomSharingClassGUI($this->parent, $class_id);
		$this->ctrl->forwardCommand($this->class_gui);
	}

	private function executeDefaultCommand()
	{
		$cmd = $this->ctrl->getCmd("showPrivileges");
		$this->$cmd();
	}

	/**
	 * Displays a toolbar for adding new classes and a table consisting of the exsisting classes and
	 * its corresponding privileges.
	 */
	public function showPrivileges()
	{
		$toolbar = $this->createToolbar();
		$class_privileges_table = new ilRoomSharingClassPrivilegesTableGUI($this, "showPrivileges",
			$this->ref_id);

		$this->tpl->setContent($toolbar->getHTML() . $class_privileges_table->getHTML());
	}

	/**
	 * The toolbar basically consists a button for adding new classes to the table. The button is
	 * only disabled if the creation of new classes is granted.
	 * @return \ilToolbarGUI
	 */
	private function createToolbar()
	{
		$toolbar = new ilToolbarGUI();

		if ($this->permission->checkPrivilege(PRIVC::ADD_CLASS))
		{
			$target = $this->ctrl->getLinkTarget($this, "renderAddClassForm");
			$toolbar->addButton($this->lng->txt("rep_robj_xrs_privileges_class_new"), $target);
		}

		return $toolbar;
	}

	/**
	 * Renders a form for adding a new class.
	 */
	private function renderAddClassForm()
	{
		$this->tabs->clearTargets();
		$class_form = $this->createAddClassForm();
		$this->tpl->setContent($class_form->getHTML());
	}

	/**
	 * Creates the form for adding new classes, appends items to it and returns it for
	 * displaying.
	 *
	 * @return \ilPropertyFormGUI
	 */
	private function createAddClassForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("rep_robj_xrs_privileges_class_new"));
		$form->addCommandButton("addClass", $this->lng->txt("rep_robj_xrs_privileges_class_new"));
		$form->addCommandButton("showPrivileges", $this->lng->txt("cancel"));
		$form_items = $this->createAddClassFormItems();

		foreach ($form_items as $item)
		{
			$form->addItem($item);
		}

		return $form;
	}

	/**
	 * Creates and collects all form items and returns them so that they can be added to the form.
	 */
	private function createAddClassFormItems()
	{
		$form_items = array();
		$form_items[] = $this->createClassNameTextInput();
		$form_items[] = $this->createClassDescriptionTextArea();
		$form_items[] = $this->createClassRoleAssignmentSelection();
		$form_items[] = $this->createClassPrioritySelection();
		$form_items[] = $this->createClassCopyPrivilegesRadioGroupIfClassesExist();

		return array_filter($form_items);
	}

	private function createClassNameTextInput()
	{
		$name_input = new ilTextInputGUI($this->lng->txt("name"), "name");
		$name_input->setSize(45);
		$name_input->setMaxLength(70);
		$name_input->setRequired(true);

		return $name_input;
	}

	private function createClassDescriptionTextArea()
	{
		$description_area = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		$description_area->setCols(40);
		$description_area->setRows(3);

		return $description_area;
	}

	private function createClassRoleAssignmentSelection()
	{
		$role_assignment_selection = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_privileges_role_assignment"),
			"role_assignment");
		$role_options = $this->createRoleAssignmentOptions();
		$role_assignment_selection->setOptions($role_options);

		return $role_assignment_selection;
	}

	private function createRoleAssignmentOptions()
	{
		$role_names = array($this->lng->txt("none"));
		$parent_roles = $this->privileges->getParentRoles();

		foreach ($parent_roles as $role_info)
		{
			$role_names[] = $role_info["title"];
		}

		return $role_names;
	}

	private function createClassPrioritySelection()
	{
		$priority_selection = new ilSelectInputGUI($this->lng->txt("rep_robj_xrs_class_priority"),
			"priority");
		$priority_levels = range(0, 9);
		$priority_selection->setOptions($priority_levels);

		return $priority_selection;
	}

	/**
	 * In case classes exist, a form input will be created, which allows the user to copy the
	 * privileges of the class of choice.
	 *
	 * @return ilRadioGroupInputGUI the input GUI for copying class privileges
	 */
	private function createClassCopyPrivilegesRadioGroupIfClassesExist()
	{
		$classes = $this->privileges->getClasses();

		if (!empty($classes))
		{
			return $this->createClassCopyPrivilegesRadioGroupForClasses($classes);
		}
	}

	private function createClassCopyPrivilegesRadioGroupForClasses($a_class_array)
	{
		$class_to_copy = new ilRadioGroupInputGUI($this->lng->txt("rep_robj_xrs_privileges_copy_privileges"),
			"copied_class_privileges");
		$empty_option = new ilRadioOption($this->lng->txt("none"), 0);
		$class_to_copy->addOption($empty_option);

		foreach ($a_class_array as $class_row)
		{
			$copy_option = new ilRadioOption($class_row["name"], $class_row["id"], $class_row["description"]);
			$class_to_copy->addOption($copy_option);
		}

		return $class_to_copy;
	}

	/**
	 * Tries to save the inputs of the class form and acts accordingly.
	 */
	private function addClass()
	{
		$class_form = $this->createAddClassForm();
		if ($class_form->checkInput())
		{
			$this->handleValidAddClassForm($class_form);
		}
		else
		{
			$this->handleInvalidAddClassForm($class_form);
		}
	}

	/**
	 * Saves the new properties of the class if the form inputs were valid and displays the
	 * privileges with the newly created class.
	 *
	 * @param $a_class_form the class form for which the values should be saved.
	 */
	private function handleValidAddClassForm($a_class_form)
	{
		$class_form_entries = $this->getClassFormEntries($a_class_form);
		$this->saveFormEntries($class_form_entries);
		$this->showPrivileges();
	}

	/**
	 * In case the form input for the name of the class was empty an error message and the class
	 * form with its non erroneous entries will be displayed.
	 *
	 * @param ilPropertyFormGUI $a_class_form the class for which the invalid input should be handled
	 */
	private function handleInvalidAddClassForm($a_class_form)
	{
		$this->tabs->clearTargets();
		$a_class_form->setValuesByPost();
		$this->tpl->setContent($a_class_form->getHTML());
	}

	/**
	 * Gathers and returns the inputs of the form.
	 *
	 * @param $a_class_form the class form for which the inputs should be gathered
	 * @return array the entries as an associative array
	 */
	private function getClassFormEntries($a_class_form)
	{
		$entries = array();
		$entries["name"] = $a_class_form->getInput("name");
		$entries["description"] = $a_class_form->getInput("description");
		$entries["priority"] = $a_class_form->getInput("priority");
		$entries["role_id"] = $this->getRoleIdFromSelectionInput($a_class_form->getInput("role_assignment"));
		$entries["copied_class_privileges"] = $a_class_form->getInput("copied_class_privileges");

		return $entries;
	}

	/**
	 * Since the selection index of the selection input is off by 1, the real index of the selection
	 * and thus the id of the role must be determined.
	 *
	 * @param string $a_role_assignment_selection index of the selection
	 * @return string the id of the role that was selected
	 */
	private function getRoleIdFromSelectionInput($a_role_assignment_selection)
	{
		$global_roles = $this->privileges->getParentRoles();
		$role_array_index = $a_role_assignment_selection - self::SELECT_INPUT_NONE_OFFSET;

		return $global_roles[$role_array_index]["id"];
	}

	/**
	 * Tries to save the form entries and displays an error message if this was not possible.
	 *
	 * @param array $a_entries the entries that need to be saved
	 */
	private function saveFormEntries($a_entries)
	{
		try
		{
			$this->privileges->addClass($a_entries);
			ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_class_added_successfully"), true);
		}
		catch (ilRoomSharingPrivilegesException $ex)
		{
			ilUtil::sendFailure($this->lng->txt($ex->getMessage()), true);
		}
	}

	private function savePrivilegeSettings()
	{
		$this->savePrivileges();

		$classes_with_ticked_locks = $_POST["lock"];
		$class_ids_of_ticked_locks = $this->getClassIdsOfTickedLocks($classes_with_ticked_locks);
		if ($this->isConfirmationRequired($class_ids_of_ticked_locks))
		{
			$this->renderConfirmPrivilegeLock($class_ids_of_ticked_locks);
		}
		else
		{
			$this->savePrivilegeLocksWithoutConfirmation($classes_with_ticked_locks);
		}
	}

	private function savePrivileges()
	{
		$classes_with_ticked_privileges = $_POST["priv"];
		$this->privileges->setPrivileges($classes_with_ticked_privileges);
	}

	private function getClassIdsOfTickedLocks($a_classes_with_ticked_locks)
	{
		if (empty($a_classes_with_ticked_locks))
		{
			return array();
		}
		else
		{
			return array_keys($a_classes_with_ticked_locks);
		}
	}

	private function isConfirmationRequired($a_class_ids_of_ticked_locks)
	{
		return !empty($a_class_ids_of_ticked_locks) && $this->areNewLocksSet($a_class_ids_of_ticked_locks);
	}

	private function areNewLocksSet($a_class_ids_of_ticked_locks)
	{
		$new_locked_class_ids = $this->getNewLockedClassIds($a_class_ids_of_ticked_locks);

		return !empty($new_locked_class_ids);
	}

	private function savePrivilegeLocksWithoutConfirmation($a_classes_with_ticked_locks)
	{
		$this->privileges->setLockedClasses($a_classes_with_ticked_locks);
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this);
	}

	private function renderConfirmPrivilegeLock($a_class_ids_of_ticked_locks)
	{
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->lng->txt("rep_robj_xrs_privileges_lock_confirm_back"),
			$this->ctrl->getLinkTarget($this, "showPrivileges"));

		$confirmation_dialog = $this->createPrivilegeLockConfirmationDialog($a_class_ids_of_ticked_locks);
		$this->displayPrivilegeLockConfirmationMessages($a_class_ids_of_ticked_locks);
		$this->tpl->setContent($confirmation_dialog->getHTML());
	}

	private function createPrivilegeLockConfirmationDialog($a_class_ids_of_ticked_locks)
	{
		$confirmation_dialog = new ilConfirmationGUI();
		$confirmation_dialog->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_dialog->setHeaderText($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock_question"));
		$new_locked_class_ids = $this->getNewLockedClassIds($a_class_ids_of_ticked_locks);

		foreach ($new_locked_class_ids as $class_id)
		{
			$confirmation_dialog->addItem("new_locked_class_ids", "",
				$this->privileges->getClassById($class_id)["name"]);
		}
		$confirmation_dialog->addHiddenItem("locked_classes", json_encode($a_class_ids_of_ticked_locks));

		$confirmation_dialog->setConfirm($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock"),
			"lockClassesAfterConfirmation");
		$confirmation_dialog->setCancel($this->lng->txt("cancel"), "showPrivileges");

		return $confirmation_dialog;
	}

	private function getNewLockedClassIds($a_class_ids_of_ticked_locks)
	{
		$unlocked_class_ids = $this->privileges->getUnlockedClasses();
		$new_locked_class_ids = array_intersect($a_class_ids_of_ticked_locks, $unlocked_class_ids);

		return $new_locked_class_ids;
	}

	private function displayPrivilegeLockConfirmationMessages($a_class_ids_of_ticked_locks)
	{
		ilUtil::sendInfo($this->lng->txt("rep_robj_xrs_privileges_confirm_class_lock_info"));

		if ($this->areOwnClassesToBeLocked($a_class_ids_of_ticked_locks))
		{
			$own_class_names_to_be_locked = $this->getOwnClassNamesToBeLocked($a_class_ids_of_ticked_locks);
			ilUtil::sendFailure($this->lng->txt("rep_robj_xrs_privileges_confirm_class_self_lock_of")
				. " " . implode(", ", $own_class_names_to_be_locked));
		}
	}

	private function areOwnClassesToBeLocked($a_class_ids_of_ticked_locks)
	{
		$own_class_ids_to_be_locked = $this->getOwnClassIdsToBeLocked($a_class_ids_of_ticked_locks);
		return !empty($own_class_ids_to_be_locked);
	}

	private function getOwnClassIdsToBeLocked($a_class_ids_of_ticked_locks)
	{
		$new_locked_class_ids = $this->getNewLockedClassIds($a_class_ids_of_ticked_locks);
		$own_class_ids = $this->privileges->getAssignedClassesForUser($this->user->getId());
		$own_class_ids_to_be_locked = array_intersect($new_locked_class_ids, $own_class_ids);

		return $own_class_ids_to_be_locked;
	}

	private function getOwnClassNamesToBeLocked($a_class_ids_of_ticked_locks)
	{
		$own_class_ids_to_be_locked = $this->getOwnClassIdsToBeLocked($a_class_ids_of_ticked_locks);
		$own_class_names_to_be_locked = array();

		foreach ($own_class_ids_to_be_locked as $class_id)
		{
			$own_class_names_to_be_locked[] = $this->privileges->getClassById($class_id)["name"];
		}

		return $own_class_names_to_be_locked;
	}

	/**
	 * Used for locking classes after the corresponding confirmation dialog has been confirmed.
	 */
	public function lockClassesAfterConfirmation()
	{
		$locked_class_ids = json_decode($_POST["locked_classes"]);
		$class_ids_with_ticked_locks = array_flip($locked_class_ids);
		$this->privileges->setLockedClasses($class_ids_with_ticked_locks);
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);

		$this->ctrl->redirect($this);
	}

	/**
	 * Displays a success message and the privileges table after the deletion of a class.
	 */
	public function showConfirmedClassDeletion()
	{
		ilUtil::sendSuccess($this->lng->txt("rep_robj_xrs_class_deletion_successful"));
		$this->showPrivileges();
	}

	/**
	 * Returns the RoomSharing Pool Id.
	 *
	 * @return integer Pool-ID
	 */
	public function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets the RoomSharing Pool Id.
	 *
	 * @param integer Pool-ID
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
