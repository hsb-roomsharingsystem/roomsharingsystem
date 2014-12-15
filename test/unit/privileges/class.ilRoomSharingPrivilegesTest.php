<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/privileges/class.ilRoomSharingPrivileges.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");

/**
 * Class ilRoomSharingPrivilegesTest
 *
 * @group unit
 * @author Albert Koch akoch@stud.hs-bremen.de
 */
class ilRoomSharingPrivilegesTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingPrivileges
	 */
	protected $object;
	private static $ilRoomSharingDatabaseStub;
	private static $privileges;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$test = new ilRoomSharingPrivilegesTest();
		self::$ilRoomSharingDatabaseStub = $test->getMockBuilder('ilRoomSharingDatabase')->disableOriginalConstructor()->getMock();
		self::$privileges = ilRoomSharingPrivileges::withDatabase(1, self::$ilRoomSharingDatabaseStub);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingPrivileges::getPrivilegesMatrix
	 * @todo   Implement testGetPrivilegesMatrix().
	 */
	public function testGetPrivilegesMatrix()
	{


		//if there's no class, the function must return an empty array
		self::$ilRoomSharingDatabaseStub->method('getClasses')->willreturn(array());
		$returnarray = self::$privileges->getPrivilegesMatrix();
		$this->assertEquals(0, count($returnarray));
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAllPrivileges
	 * @todo   Implement testGetAllPrivileges().
	 */
	public function testGetAllPrivileges()
	{
		$priv = array();
		$priv[] = 'accessAppointments';
		$priv[] = 'accessSearch';
		$priv[] = 'addOwnBookings';
		$priv[] = 'addParticipants';
		$priv[] = 'addSequenceBookings';
		$priv[] = 'addUnlimitedBookings';
		$priv[] = 'seeNonPublicBookingInformation';
		$priv[] = 'notificationSettings';
		$priv[] = 'adminBookingAttributes';
		$priv[] = 'cancelBookingLowerPriority';
		$priv[] = 'accessRooms';
		$priv[] = 'seeBookingsOfRooms';
		$priv[] = 'addRooms';
		$priv[] = 'editRooms';
		$priv[] = 'deleteRooms';
		$priv[] = 'adminRoomAttributes';
		$priv[] = 'accessFloorplans';
		$priv[] = 'addFloorplans';
		$priv[] = 'editFloorplans';
		$priv[] = 'deleteFloorplans';
		$priv[] = 'accessSettings';
		$priv[] = 'accessPrivileges';
		$priv[] = 'addClass';
		$priv[] = 'editClass';
		$priv[] = 'deleteClass';
		$priv[] = 'editPrivileges';
		$priv[] = 'lockPrivileges';

//		$this->assertEquals($priv, self::$privileges->getAllClassPrivileges());
	}

	/**
	 * @covers ilRoomSharingPrivileges::getClasses
	 * @todo   Implement testGetClasses().
	 */
	public function testGetClasses()
	{
		//if there are no classes, return must be null
		self::$ilRoomSharingDatabaseStub->method('getClasses')->willReturn(array());
		$this->assertEquals(array(), self::$privileges->getClasses());
	}

	/**
	 * @covers ilRoomSharingPrivileges::getClassById
	 * @todo   Implement testGetClassById().
	 */
	public function testGetClassById()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAssignedClassesForUser
	 * @todo   Implement testGetAssignedClassesForUser().
	 */
	public function testGetAssignedClassesForUser()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getPriorityOfUser
	 * @todo   Implement testGetPriorityOfUser().
	 */
	public function testGetPriorityOfUser()
	{

		//Set nine users with the nine priorities below 10 and the ID of the owner which has to have a priority of 10
		$map = array
			(
			array('42', '1'),
			array('43', '2'),
			array('44', '3'),
			array('45', '4'),
			array('46', '5'),
			array('47', '6'),
			array('48', '7'),
			array('49', '8'),
			array('50', '9'),
			array('51', '10'),
		);
		self::$ilRoomSharingDatabaseStub->method('getUserPriority')
			->will($this->returnValueMap($map));
		$this->assertEquals('1', self::$privileges->GetPriorityOfUser('42'));
		$this->assertEquals('2', self::$privileges->GetPriorityOfUser('43'));
		$this->assertEquals('3', self::$privileges->GetPriorityOfUser('44'));
		$this->assertEquals('4', self::$privileges->GetPriorityOfUser('45'));
		$this->assertEquals('5', self::$privileges->GetPriorityOfUser('46'));
		$this->assertEquals('6', self::$privileges->GetPriorityOfUser('47'));
		$this->assertEquals('7', self::$privileges->GetPriorityOfUser('48'));
		$this->assertEquals('8', self::$privileges->GetPriorityOfUser('49'));
		$this->assertEquals('9', self::$privileges->GetPriorityOfUser('50'));
		$this->assertEquals('10', self::$privileges->GetPriorityOfUser('51'));
	}

	/**
	 * @covers ilRoomSharingPrivileges::getPrivilegesForUser
	 * @todo   Implement testGetPrivilegesForUser().
	 */
	public function testGetPrivilegesForUser()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAssignedUsersForClass
	 * @todo   Implement testGetAssignedUsersForClass().
	 */
	public function testGetAssignedUsersForClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getParentRoles
	 * @todo   Implement testGetParentRoles().
	 */
	public function testGetParentRoles()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getParentRoleTitle
	 * @todo   Implement testGetParentRoleTitle().
	 */
	public function testGetParentRoleTitle()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::addClass
	 * @todo   Implement testAddClass().
	 */
	public function testAddClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::editClass
	 * @todo   Implement testEditClass().
	 */
	public function testEditClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::deleteClass
	 * @todo   Implement testDeleteClass().
	 */
	public function testDeleteClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::assignUsersToClass
	 * @todo   Implement testAssignUsersToClass().
	 */
	public function testAssignUsersToClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::deassignUsersFromClass
	 * @todo   Implement testDeassignUsersFromClass().
	 */
	public function testDeassignUsersFromClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::setPrivileges
	 * @todo   Implement testSetPrivileges().
	 */
	public function testSetPrivileges()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::setLockedClasses
	 * @todo   Implement testSetLockedClasses().
	 */
	public function testSetLockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getLockedClasses
	 */
	public function testGetLockedClasses()
	{
		$expected = array(1, 2, 3, 4, 5, 6);
		self::$ilRoomSharingDatabaseStub->method('getLockedClasses')->willreturn($expected);
		$this->assertEquals($expected, self::$privileges->getLockedClasses());
	}

	/**
	 * @covers ilRoomSharingPrivileges::getUnlockedClasses
	 */
	public function testGetUnlockedClasses()
	{
		$expected = array(1, 2, 3, 4, 5, 6);
		self::$ilRoomSharingDatabaseStub->method('getUnlockedClasses')->willreturn($expected);
		$this->assertEquals($expected, self::$privileges->getUnlockedClasses());
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAllClassPrivileges
	 * @todo   Implement testGetAllClassPrivileges().
	 */
	public function testGetAllClassPrivileges()
	{/*
	  $stub = $this->getMockBuilder('ilRoomSharingDatabase')
	  ->getMock();
	  $mockclasses = array();
	  $mockclasses[0] = array(); */
	}

}
