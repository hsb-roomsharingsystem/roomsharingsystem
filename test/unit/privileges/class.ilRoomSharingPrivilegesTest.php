<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingPrivilegesTest
 *
 * @group unit
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
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getAllPrivileges
	 * @todo   Implement testGetAllPrivileges().
	 */
	public function testGetAllPrivileges()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPrivileges::getClasses
	 * @todo   Implement testGetClasses().
	 */
	public function testGetClasses()
	{
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
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
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
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
