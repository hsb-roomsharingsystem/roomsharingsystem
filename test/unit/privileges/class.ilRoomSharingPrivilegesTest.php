<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

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

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingPrivileges;
		$test = new ilRoomSharingPrivilegesTest();

		self::$privileges = new ilRoomSharingPrivileges();
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
		$this->method('getClasses')->willreturn(0);
		$returnarray = $this->getPrivilegesMatrix;
		assertEquals(0, count($returnarray));
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAllPrivileges
	 * @todo   Implement testGetAllPrivileges().
	 */
	public function testGetAllPrivileges()
	{
		//I don't know if this test makes sense, but to make the testsuite complete...
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

		$this->assertEquals($priv, $this->object->getAllClassPrivileges());
	}

	/**
	 * @covers ilRoomSharingPrivileges::getClasses
	 * @todo   Implement testGetClasses().
	 */
	public function testGetClasses()
	{
		//if there are no classes, return must be null
		$stub = $this->getMockBuilder('ilRoomSharingDatabase')
			->getMock();
		$stub->method('getClasses')->willReturn(null);
		assertEquals($this->getClasses, null);
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
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
		$stub = $this->getMockBuilder('ilRoomSharingDatabase')
			->getMock();

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
			array($this->object->getOwner(), '10'),
		);
		$stub->method('getUserPriority')
			->will($this->returnValueMap($map));
		$this->assertEquals('1', $stub->GetPriorityOfUser('42'));
		$this->assertEquals('2', $stub->GetPriorityOfUser('43'));
		$this->assertEquals('3', $stub->GetPriorityOfUser('44'));
		$this->assertEquals('4', $stub->GetPriorityOfUser('45'));
		$this->assertEquals('5', $stub->GetPriorityOfUser('46'));
		$this->assertEquals('6', $stub->GetPriorityOfUser('47'));
		$this->assertEquals('7', $stub->GetPriorityOfUser('48'));
		$this->assertEquals('8', $stub->GetPriorityOfUser('49'));
		$this->assertEquals('9', $stub->GetPriorityOfUser('50'));
		$this->assertEquals('10', $stub->GetPriorityOfUser($this->object->getOwner()));
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
	 * @todo   Implement testGetLockedClasses().
	 */
	public function testGetLockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getUnlockedClasses
	 * @todo   Implement testGetUnlockedClasses().
	 */
	public function testGetUnlockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAllClassPrivileges
	 * @todo   Implement testGetAllClassPrivileges().
	 */
	public function testGetAllClassPrivileges()
	{
		$stub = $this->getMockBuilder('ilRoomSharingDatabase')
			->getMock();
		$mockclasses = array();
		$mockclasses[0] = array();
	}

}
