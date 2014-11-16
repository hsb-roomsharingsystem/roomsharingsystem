<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingNumericUtils.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/exceptions/class.ilRoomSharingAttributesException.php");

/**
 * Class ilRoomSharingBookingAttributes for booking attributes administration.
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 *
 * @version $Id$
 * @property ilRoomsharingDatabase $ilRoomsharingDatabase
 * @property ilDB $ilDB
 */
class ilRoomSharingBookingAttributes
{
	private $pool_id;
	private $allAvailableAttributes = array();
	private $ilRoomsharingDatabase;
	private $ilDB;

	/**
	 * Constructor of ilRoomSharingBookingAttributes
	 *
	 * @param integer $a_pool_id
	 */
	public function __construct($a_pool_id = 1)
	{
		global $ilDB;
		$this->ilDB = $ilDB;
		$this->pool_id = $a_pool_id;
		$this->ilRoomsharingDatabase = new ilRoomsharingDatabase($a_pool_id);
		$this->allAvailableAttributes = $this->ilRoomsharingDatabase->getAllBookingAttributes();
	}

	/**
	 * Returns all available attributes as names.
	 *
	 * @return array with names
	 */
	public function getAllAvailableAttributesNames()
	{
		$roomAttributesNames = array();
		foreach ($this->allAvailableAttributes as $attribute)
		{
			$roomAttributesNames[] = $attribute['name'];
		}
		return $roomAttributesNames;
	}

	/**
	 * Updates room attributes and returns an associative array
	 * with 'deleted', 'deletedAssigments' and 'inserted' integer values.
	 *
	 * @throws ilRoomSharingAttributesException
	 *
	 * @param array $a_user_given_attributes_names
	 * @return assoc array with affections count
	 */
	public function updateAttributes($a_user_given_attributes_names)
	{
		$this->checkUserPrivileges();

		$filteredAttributesNames = array_unique($a_user_given_attributes_names);

		$attributesToDelete = $this->findAttributesForDeletion($filteredAttributesNames);
		$affections['deletedAssigments'] = $this->deleteAttributes($attributesToDelete);
		$affections['deleted'] = count($attributesToDelete);

		$attributesToInsert = $this->findAttributesForInsertion($filteredAttributesNames);
		$this->insertAttributes($attributesToInsert);
		$affections['inserted'] = count($attributesToInsert);

		return $affections;
	}

	/**
	 * Returns attributes which are no more wished by the user.
	 *
	 * @param array $a_user_given_attributes_names
	 * @return array with attributes should be deleted
	 */
	private function findAttributesForDeletion($a_user_given_attributes_names)
	{
		return array_diff($this->getAllAvailableAttributesNames(), $a_user_given_attributes_names);
	}

	/**
	 * Returns attributes which are wished by the user but does not exists in the database.
	 *
	 * @param array $a_user_given_attributes_names
	 * @return array with attributes should be inserted
	 */
	private function findAttributesForInsertion($a_user_given_attributes_names)
	{
		return array_diff($a_user_given_attributes_names, $this->getAllAvailableAttributesNames());
	}

	/**
	 * Deletes given attributes and associations/assignments to it (rooms - attributes).
	 *
	 * @param array $a_attributesNames
	 * @return assoc array with number of deleted assignments
	 */
	private function deleteAttributes($a_attributesNames)
	{
		$deletedAssignments = 0;
		foreach ($a_attributesNames as $attributeName)
		{
			$attributeId = $this->getAttributeId($attributeName);
			if ($attributeId)
			{
				$deletedAssignments += $this->ilRoomsharingDatabase->deleteAttributeBookingAssign($attributeId);
				$this->ilRoomsharingDatabase->deleteBookingAttribute($attributeId);
			}
		}
		return $deletedAssignments;
	}

	/**
	 * Inserts new room attributes with given names.
	 *
	 * @param array $a_attributeNames
	 */
	private function insertAttributes($a_attributeNames)
	{
		foreach ($a_attributeNames as $attributeName)
		{
			$this->ilRoomsharingDatabase->insertBookingAttribut($attributeName);
		}
	}

	/**
	 * Returns the id of an attribute with the given name.
	 * If such doesn't exists, FALSE will be returned.
	 *
	 * @param string $a_attribute_name
	 * @return boolean or integer (id)
	 */
	private function getAttributeId($a_attribute_name)
	{
		$rVal = FALSE;
		foreach ($this->allAvailableAttributes as $attribute)
		{
			if ($attribute['name'] == $a_attribute_name)
			{
				$rVal = $attribute['id'];
				break;
			}
		}
		return $rVal;
	}

	/**
	 * Checks privileges of the current user.
	 * If the user is not allowed to change booking attributes, an exception will be thrown.
	 *
	 * @throws ilRoomSharingAttributesException
	 */
	private function checkUserPrivileges()
	{
		// TODO Change this after privileges functions are implemented!
		if (false)
		{
			throw new ilRoomSharingAttributesException('rep_robj_xrs_attributes_change_not_allowed');
		}
	}

	/**
	 * Sets the pool id.
	 *
	 * @param integer $a_pool_id
	 */
	public function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

	/**
	 * Returns the pool id.
	 *
	 * @return integer pool id
	 */
	public function getPoolId()
	{
		return (int) $this->pool_id;
	}

}
