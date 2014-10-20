<?php

/**
 * Class ilRoomSharingParticipationsGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 * 
 */
class ilRoomSharingParticipationsGUI
{
	protected $ref_id;
	protected $pool_id;

	/**
	 * Constructor of ilRoomSharingParticipationsGUI.
	 *
	 * @param ilRoomSharingAppointmentsGUI $a_parent_obj
	 */
	function __construct(ilRoomSharingAppointmentsGUI $a_parent_obj)
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->getPoolId();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
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
		global $tpl;
		
		include_once ("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/appointments/participations/class.ilRoomSharingParticipationsTableGUI.php");
		$participationsTable = new ilRoomSharingParticipationsTableGUI($this, 
				'showParticipations', $this_ref_id);
		
		$tpl->setContent($participationsTable->getHTML());
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
