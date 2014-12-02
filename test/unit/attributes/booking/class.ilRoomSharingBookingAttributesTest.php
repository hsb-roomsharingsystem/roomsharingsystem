<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomSharingBookingAttributesTest
 *
 * @group unit
 */
class ilRoomSharingBookingAttributesTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingBookingAttributes
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomSharingBookingAttributes;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomSharingBookingAttributes::getAllAvailableAttributesNames
	 * @todo   Implement testGetAllAvailableAttributesNames().
	 */
	public function testGetAllAvailableAttributesNames()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::getAllAvailableAttributesWithIdAndName
	 * @todo   Implement testGetAllAvailableAttributesWithIdAndName().
	 */
	public function testGetAllAvailableAttributesWithIdAndName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::renameAttribute
	 * @todo   Implement testRenameAttribute().
	 */
	public function testRenameAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::deleteAttribute
	 * @todo   Implement testDeleteAttribute().
	 */
	public function testDeleteAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::createAttribute
	 * @todo   Implement testCreateAttribute().
	 */
	public function testCreateAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::setPoolId
	 * @todo   Implement testSetPoolId().
	 */
	public function testSetPoolId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingBookingAttributes::getPoolId
	 * @todo   Implement testGetPoolId().
	 */
	public function testGetPoolId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
