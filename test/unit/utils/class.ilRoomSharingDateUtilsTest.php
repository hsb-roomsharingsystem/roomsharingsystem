<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingDateUtilsTest
 *
 * @group unit
 */
class ilRoomSharingDateUtilsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingDateUtils
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingDateUtils;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingDateUtils::getPrintedDateTime
	 * @todo   Implement testGetPrintedDateTime().
	 */
	public function testGetPrintedDateTime()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingDateUtils::getPrintedDate
	 * @todo   Implement testGetPrintedDate().
	 */
	public function testGetPrintedDate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingDateUtils::getPrintedTime
	 * @todo   Implement testGetPrintedTime().
	 */
	public function testGetPrintedTime()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingDateUtils::isEqualDay
	 * @todo   Implement testIsEqualDay().
	 */
	public function testIsEqualDay()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
