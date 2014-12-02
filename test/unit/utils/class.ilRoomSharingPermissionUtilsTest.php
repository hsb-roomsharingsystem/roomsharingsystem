<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingPermissionUtilsTest
 *
 * @group unit
 */
class ilRoomSharingPermissionUtilsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingPermissionUtils
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingPermissionUtils;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingPermissionUtils::checkPrivilege
	 * @todo   Implement testCheckPrivilege().
	 */
	public function testCheckPrivilege()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPermissionUtils::getUserPriority
	 * @todo   Implement testGetUserPriority().
	 */
	public function testGetUserPriority()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPermissionUtils::checkForHigherPriority
	 * @todo   Implement testCheckForHigherPriority().
	 */
	public function testCheckForHigherPriority()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingPermissionUtils::getAllUserPrivileges
	 * @todo   Implement testGetAllUserPrivileges().
	 */
	public function testGetAllUserPrivileges()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
