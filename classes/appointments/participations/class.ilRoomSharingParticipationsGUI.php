<?php

require_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/participations/class.ilRoomSharingParticipationsTableGUI.php");

/**
 * Class ilRoomSharingParticipationsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @author Robert Heimsoth <rheimsoth@stud.hs-bremen.de>
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilLanguage $lng
 * @property ilCtrl $ctrl
 * @property ilTemplate $tpl
 */
class ilRoomSharingParticipationsGUI
{
	protected $ref_id;
	protected $pool_id;
	private $ctrl;
	private $lng;
	private $tpl;

	/**
	 * Constructor of ilRoomSharingParticipationsGUI.
	 *
	 * @param ilRoomSharingAppointmentsGUI $a_parent_obj
	 */
	function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
	}

	/**
	 * Main switch for command execution.
	 *
	 * @return bool whether the command execution was successful.
	 */
	function executeCommand()
	{
		$this->showParticipations();
		return true;
	}

	/**
	 * Show all participations.
	 */
	function showParticipations()
	{
		$participationsTable = new ilRoomSharingParticipationsTableGUI($this, 'showParticipations',
			$this->ref_id);
		$this->tpl->setContent($participationsTable->getHTML());
	}

	/**
	 * Returns roomsharing pool id.
	 *
	 * @return int pool id
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Sets roomsharing pool id.
	 *
	 * @param integer $a_pool_id current pool id.
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

}

?>
