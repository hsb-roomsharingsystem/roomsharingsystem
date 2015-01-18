<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabaseBookingAttribute.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDBConstants.php");
require_once("Services/Database/classes/class.ilDBMySQL.php");

use ilRoomSharingDBConstants as dbc;

/**
 * @author Christopher Marks
 * @group unit
 */
class ilRoomSharingDatabaseBookingAttributeTest extends PHPUnit_Framework_TestCase
{
	private static $bookingAttributeDb;
	private static $pool_id = 1;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$test = new ilRoomSharingDatabaseBookingAttributeTest();
		global $ilDB;

		$ilDB = $test->getMock('db',
			array('quote', 'query', 'fetchAssoc', 'in', 'prepare', 'execute', 'insert', 'nextId', 'getLastInsertId',
			'update', 'manipulate'), array(), '', false);
		self::$bookingAttributeDb = new ilRoomSharingDatabaseBookingAttribute(self::$pool_id);
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::insertBookingAttributes
	 */
	public function testInsertBookingAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::insertBookingAttributeAssign
	 */
	public function testInsertBookingAttributeAssign()
	{
		global $ilDB;
		$expected1 = dbc::BOOKING_TO_ATTRIBUTE_TABLE;
		$expected2 = array(
			'booking_id' => array('integer', '1'),
			'attr_id' => array('integer', '2'),
			'value' => array('text', '3'));
		$ilDB->expects($this->once())->method('insert')->with($this->equalTo($expected1),
			$this->equalTo($expected2));
		self::$bookingAttributeDb->insertBookingAttributeAssign('1', '2', '3');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::getAttributesForBooking
	 */
	public function testGetAttributesForBooking()
	{
		global $ilDB;
		$expected = ('SELECT value, attr.name AS name' .
			' FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE . ' bta ' .
			' LEFT JOIN ' . dbc::BOOKING_ATTRIBUTES_TABLE . ' attr ' .
			' ON attr.id = bta.attr_id' . ' WHERE booking_id = ' .
			$ilDB->quote('1', 'integer') .
			' AND pool_id =' . $ilDB->quote($this->pool_id, 'integer'));
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$bookingAttributeDb->getAttributesForBooking('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::getAllBookingAttributes
	 */
	public function testGetAllBookingAttributes()
	{
		global $ilDB;
		$expected = ('SELECT * FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $ilDB->quote(self::$pool_id, 'integer')
			. ' ORDER BY name ASC');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$bookingAttributeDb->getAllBookingAttributes();
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::getAllBookingAttributeNames
	 */
	public function testGetAllBookingAttributeNames()
	{
		global $ilDB;
		$expected = ('SELECT name FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
			' WHERE pool_id = ' . $ilDB->quote(self::$pool_id, 'integer') . ' ORDER BY name');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$bookingAttributeDb->getAllBookingAttributeNames();
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::getBookingAttributeValues
	 */
	public function testGetBookingAttributeValues()
	{
		global $ilDB;
		$expected = ('SELECT *' . ' FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE . ' WHERE booking_id = ' .
			$ilDB->quote('1', 'integer') . ' ORDER BY attr_id ASC');
		$ilDB->expects($this->once())->method('query')->with($this->equalTo($expected));
		self::$bookingAttributeDb->getBookingAttributeValues('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::updateBookingAttributeAssign
	 */
	public function testUpdateBookingAttributeAssign()
	{
		global $ilDB;
		$expected1 = dbc::BOOKING_TO_ATTRIBUTE_TABLE;
		$expected2 = array('value' => array('text', '3'));
		$expected3 = array(
			'booking_id' => array('integer', '1'),
			'attr_id' => array('integer', '2'));
		$ilDB->expects($this->once())->method('update')->with($this->equalTo($expected1),
			$this->equalTo($expected2), $this->equalTo($expected3));
		self::$bookingAttributeDb->updateBookingAttributeAssign('1', '2', '3');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::updateBookingAttributes
	 */
	public function testUpdateBookingAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::updateDelBookingAttributeAssign
	 */
	public function testUpdateDelBookingAttributeAssign()
	{
		global $ilDB;
		$expected1 = dbc::BOOKING_TO_ATTRIBUTE_TABLE;
		$expected2 = array('value' => array('text', 0));
		$expected3 = array(
			'booking_id' => array('integer', '1'),
			'attr_id' => array('integer', '2'));
		$ilDB->expects($this->once())->method('update')->with($this->equalTo($expected1),
			$this->equalTo($expected2), $this->equalTo($expected3));
		self::$bookingAttributeDb->updateDelBookingAttributeAssign('1', '2');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::deleteAttributeBookingAssig
	 */
	public function testDeleteAttributeBookingAssign()
	{
		global $ilDB;
		$expected = ('DELETE FROM ' . dbc::BOOKING_TO_ATTRIBUTE_TABLE .
			' WHERE attr_id = ' . $ilDB->quote('1', 'integer'));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected));
		self::$bookingAttributeDb->deleteAttributeBookingAssign('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::deleteBookingAttribute
	 */
	public function testDeleteBookingAttribute()
	{
		global $ilDB;
		$expected = ('DELETE FROM ' . dbc::BOOKING_ATTRIBUTES_TABLE .
			' WHERE id = ' . $ilDB->quote('1', 'integer'));
		$ilDB->expects($this->once())->method('manipulate')->with($this->equalTo($expected));
		self::$bookingAttributeDb->deleteBookingAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::insertBookingAttribute
	 */
	public function testInsertBookingAttribute()
	{
		global $ilDB;
		$expected1 = dbc::BOOKING_ATTRIBUTES_TABLE;
		$expected2 = array(
			'id' => array('integer', $ilDB->nextID(dbc::BOOKING_ATTRIBUTES_TABLE)),
			'name' => array('text', '1'),
			'pool_id' => array('integer', self::$pool_id));
		$ilDB->expects($this->once())->method('insert')->with($this->equalTo($expected1),
			$this->equalTo($expected2));
		self::$bookingAttributeDb->insertBookingAttribute('1');
	}

	/**
	 * @covers ilRoomSharingDatabaseBookingAttribute::renameBookingAttribute
	 */
	public function testRenameBookingAttribute()
	{
		global $ilDB;
		$expected1 = dbc::BOOKING_ATTRIBUTES_TABLE;
		$expected2 = array('name' => array('text', '2'));
		$expected3 = array(
			'id' => array("integer", '1'),
			'pool_id' => array("integer", self::$pool_id));
		$ilDB->expects($this->once())->method('update')->with($this->equalTo($expected1),
			$this->equalTo($expected2), $this->equalTo($expected3));

		self::$bookingAttributeDb->renameBookingAttribute('1', '2');
	}

}
