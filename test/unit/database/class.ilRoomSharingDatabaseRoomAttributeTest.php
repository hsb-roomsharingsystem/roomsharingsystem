<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabaseRoomAttribute.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
require_once("Services/Database/classes/class.ilDBMySQL.php");

use ilRoomSharingDBConstants as dbc;

/**
 * @author Christopher Marks
 * @group unit
 */
class ilRoomSharingDatabaseRoomAttributeTest extends PHPUnit_Framework_TestCase
{
	private static $roomAttributeDb;
	private static $pool_id = 1;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$test = new ilRoomSharingDatabaseRoomAttributeTest();
		global $ilDB;

		$ilDB = $test->getMock('db',
			array('quote', 'query', 'fetchAssoc', 'in', 'prepare', 'execute', 'insert', 'nextId', 'getLastInsertId',
			'update', 'manipulate'), array(), '', false);
		self::$roomAttributeDb = new ilRoomSharingDatabaseRoomAttribute(self::$pool_id);
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getAttributesForRooms
	 */
	public function testGetAttributesForRooms()
	{
		global $ilDB;
		$expected = ('SELECT room_id, att.name, count FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' as rta LEFT JOIN ' .
			dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id WHERE '
			. $ilDB->in("room_id", '1') . ' AND pool_id = ' .
			$ilDB->quote(self::$pool_id, 'integer') . ' ORDER BY room_id, att.name');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$roomAttributeDb->getAttributesForRooms('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getAllAttributeNames
	 */
	public function testGetAllAttributeNames()
	{
		global $ilDB;
		$expected = ('SELECT name FROM ' . dbc::ROOM_ATTRIBUTES_TABLE . ' WHERE pool_id = ' .
			$ilDB->quote(self::$pool_id, 'integer') . ' ORDER BY name');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$roomAttributeDb->getAllAttributeNames();
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getMaxCountForAttribute
	 */
	public function testGetMaxCountForAttribute()
	{
		global $ilDB;
		$expected1 = ('SELECT id FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE name =' . $ilDB->quote('', 'text') . ' AND pool_id = ' .
			$ilDB->quote(self::$pool_id, 'integer'));
		$expected2 = ('SELECT MAX(count) AS value FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dbc::ROOMS_TABLE . ' as room ON room.id = rta.room_id ' .
			' WHERE att_id =' . $ilDB->quote('1', 'integer') .
			' AND pool_id =' . $ilDB->quote(self::$pool_id, 'integer'));
		$ilDB->expects($this->at(0))->method('query')->with($this->equalTo($expected1));
		$ilDB->expects($this->at(1))->method('query')->with($this->equalTo($expected2));
		self::$roomAttributeDb->getMaxCountForAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getRoomAttribute
	 */
	public function testGetRoomAttribute()
	{
		global $ilDB;
		$expected = ('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE id = ' . $ilDB->quote('1', 'integer') .
			' AND pool_id =' . $ilDB->quote(self::$pool_id, 'integer'));
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$roomAttributeDb->getRoomAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getAttributesForRoom
	 */
	public function testGetAttributesForRoom()
	{
		global $ilDB;
		$expected = ('SELECT id, att.name, count FROM ' .
			dbc::ROOM_TO_ATTRIBUTE_TABLE . ' rta LEFT JOIN ' .
			dbc::ROOM_ATTRIBUTES_TABLE . ' as att ON att.id = rta.att_id' .
			' WHERE room_id = ' . $ilDB->quote('1', 'integer') .
			' AND pool_id =' . $ilDB->quote(self::$pool_id, 'integer') . ' ORDER BY att.name');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$roomAttributeDb->getAttributesForRoom('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::deleteAllAttributesForRoom
	 */
	public function testDeleteAllAttributesForRoom()
	{
		global $ilDB;
		$expected = ('DELETE FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE .
			' WHERE room_id = ' . $ilDB->quote('1', 'integer'));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected));
		self::$roomAttributeDb->deleteAllAttributesForRoom('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::insertAttributeForRoom
	 */
	public function testInsertAttributeForRoom()
	{
		global $ilDB;
		$expected1 = dbc::ROOM_TO_ATTRIBUTE_TABLE;
		$expected2 = array(
			'room_id' => array('integer', 1),
			'att_id' => array('integer', 2),
			'count' => array('integer', 3));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected1),
			$this->equalTo($expected2));
		self::$roomAttributeDb->insertAttributeForRoom(1, 2, 3);
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::getAllRoomAttributes
	 */
	public function testGetAllRoomAttributes()
	{
		global $ilDB;
		$expected = ('SELECT * FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $ilDB->quote(self::$pool_id, 'integer')
			. ' ORDER BY name ASC');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$roomAttributeDb->getAllRoomAttributes();
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::deleteRoomAttribute
	 */
	public function testDeleteRoomAttribute()
	{
		global $ilDB;
		$expected = ('DELETE FROM ' . dbc::ROOM_ATTRIBUTES_TABLE .
			' WHERE id = ' . $ilDB->quote('1', 'integer') .
			' AND pool_id =' . $ilDB->quote(self::$pool_id, 'integer'));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected));
		self::$roomAttributeDb->deleteRoomAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::insertRoomAttribute
	 */
	public function testInsertRoomAttribute()
	{
		global $ilDB;
		$expected1 = dbc::ROOM_ATTRIBUTES_TABLE;
		$expected2 = array(
			'id' => array('integer', 0),
			'name' => array('text', '1'),
			'pool_id' => array('integer', self::$pool_id));
		$ilDB->expects($this->once())->method('insert')->with($this->equalTo($expected1),
			$this->equalTo($expected2));
		self::$roomAttributeDb->insertRoomAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::deleteAttributeRoomAssign
	 */
	public function testDeleteAttributeRoomAssign()
	{
		global $ilDB;
		$expected = ('DELETE FROM ' . dbc::ROOM_TO_ATTRIBUTE_TABLE .
			' WHERE att_id = ' . $ilDB->quote('1', 'integer'));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected));
		self::$roomAttributeDb->deleteAttributeRoomAssign('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseRoomAttribute::renameRoomAttribute
	 */
	public function testRenameRoomAttribute()
	{
		global $ilDB;
		$expected1 = dbc::ROOM_ATTRIBUTES_TABLE;
		$expected2 = array(
			'name' => array('text', '2'));
		$expected3 = array(
			'id' => array("integer", '1'),
			'pool_id' => array("integer", self::$pool_id));

		$ilDB->expects($this->once())->method('update')->with($this->equalTo($expected1),
			$this->equalTo($expected2), $this->equalTo($expected3));
		self::$roomAttributeDb->renameRoomAttribute('1', '2');
	}

}
