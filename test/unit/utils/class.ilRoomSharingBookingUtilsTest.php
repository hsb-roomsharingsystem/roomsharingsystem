<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingBookingUtilsTest
 *
 * @group unit
 */
class ilRoomSharingBookingUtilsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingBookingUtils
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingBookingUtils;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingBookingUtils::readBookingDate
	 * @todo   Implement testReadBookingDate().
	 */
	public function testReadBookingDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
