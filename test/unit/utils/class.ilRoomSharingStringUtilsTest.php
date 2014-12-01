<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingStringUtilsTest
 *
 * @group unit
 */
class ilRoomSharingStringUtilsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingStringUtils
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingStringUtils;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingStringUtils::startsWith
	 * @todo   Implement testStartsWith().
	 */
	public function testStartsWith()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
