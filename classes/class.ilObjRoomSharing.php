<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Mainclass for roomsharing system module. Pool id is the object id and will be stored in the db table "rep_robj_xrs_pools".
 *
 * @author tmatern
 * @author mdazjuk
 * @author troehrig
 * $Id$
*/
class ilObjRoomSharing extends ilObjectPlugin
{   
	
	protected $pool_id;
    protected $online; // bool
    
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id); 
		$this->pool_id = $this->getId();
		$this->doRead();
	}
	
	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xrs");
	}
	
	/**
	 * Parse properties for sql statements
	 */
	protected function getDBFields()
	{
		$fields = array(
				"pool_online" => array("integer", $this->isOnline()));
		return $fields;
	}
	
	/**
	* Create object
	*/
	function doCreate()
	{
		global $ilDB;
		parent::doCreate();
		$this->pool_id = $this->getId();
		$fields = $this->getDBFields();
		$fields["id"] = array("integer", $this->pool_id);
		$ilDB->insert("rep_robj_xrs_pools", $fields);
// 		return $this->getId(); vorerst direkte parent-Ansprache weil getID überschrieben
		return parent::getId();
		
	}
	
	/**
	* Read data from db
	*/
	function doRead()
	{
		global $ilDB;
		
		$objId = $this->getId();
		
		if ($objId)
		{
			$this->pool_id = $objId;
			$set = $ilDB->query('SELECT * FROM rep_robj_xrs_pools' .
					' WHERE id = ' . $ilDB->quote($this->getId(), 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setOnline($row['pool_online']);
		}
	}
	
	/**
	* Update data;
	*	called when Object is updated
	*/
	function doUpdate()
	{
		global $ilDB;

		// Put here object specific stuff.
		if ($this->getId())
		{
			$ilDB->update("rep_robj_xrs_pools", $this->getDBFields(), array("id" => array("integer", $this->getId())));
		}
		return true;
	}
	
	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB;
		$id = $this->getId();
		// always call parent delete function first!!

		// put here your module specific stuff
		// TODO: Discuss about data deletion..
		// example - delete old db data
		return true;
	}
	
	/**
	* Do Cloning
	* @param type $a_target_id
    * @param type $a_copy_id
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj)
	{
		$new_obj->setOnline($this->isOnline());
	}
	
	/**
	 * Check object status
	 *
	 * @param int $a_obj_id
	 * @return boolean
	 */
	public static function _lookupOnline($a_obj_id)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT pool_online" .
				" FROM rep_robj_xrs_pools" .
				" WHERE id = " . $ilDB->quote($a_obj_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return (bool) $row["pool_online"];
	}
	
	/**
	 * Get online property
	 *
	 * @return bool
	 */
	function isOnline()
	{
		return (bool) $this->online;
	}
	
	/**
	 * Toggle online property.
	 *
	 * @param bool $a_value
	 */
	function setOnline($a_value = true)
	{
		$this->online = (bool) $a_value;
	}
	
	/**
	 * Returns roomsharing pool id.
	 */
	function getPoolId()
	{
		return 1; //$pool_id;
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
