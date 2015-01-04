<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/utils/class.ilRoomSharingBookingUtils.php");

use ilRoomSharingBookingUtils as UTILS;

/**
 * Class ilRoomSharingBookingUtilsTest
 *
 * @group unit
 */
class ilRoomSharingBookingUtilsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		global $lng;

		$lng = $this->getMockBuilder("lng")->setMethods(array("txt"))->getMock();
		$lng->method("txt")->willReturn("test");
	}

	/**
	 * @covers ilRoomSharingBookingUtils::readBookingDate
	 */
	public function testReadBookingDate()
	{
		$bookingData = array(
			"date_from" => "2010-12-31 14:00:00",
			"date_to" => "2010-12-31 15:00:00"
		);
		$expected = "test., 31. test 2010, 14:00 - 15:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);



		$bookingData = array(
			"date_from" => "2015-02-29 15:00:00",
			"date_to" => "2015-02-29 16:00:00"
		);
		$expected = "test., 01. test 2015, 15:00 - 16:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);



		$bookingData = array(
			"date_from" => "2016-02-29 16:00:00",
			"date_to" => "2016-02-29 17:00:00"
		);
		$expected = "test., 29. test 2016, 16:00 - 17:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);




		$bookingData = array(
			"date_from" => "2015-02-29 17:00:00",
			"date_to" => "2015-03-01 18:00:00"
		);
		$expected = "test., 01. test 2015, 17:00 - 18:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ilRoomSharingBookingUtils::readBookingDate
	 */
	public function testReadBookingDateMultiDate()
	{
		$bookingData = array(
			"date_from" => "2010-12-31 14:00:00",
			"date_to" => "2011-01-03 15:00:00"
		);
		$expected = "test., 31. test 2010, 14:00 - <br>test., 03. test 2011, 15:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);



		$bookingData = array(
			"date_from" => "2015-02-29 15:00:00",
			"date_to" => "2015-03-02 16:00:00"
		);
		$expected = "test., 01. test 2015, 15:00 - <br>test., 02. test 2015, 16:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);



		$bookingData = array(
			"date_from" => "2016-02-29 16:00:00",
			"date_to" => "2016-03-02 17:00:00"
		);
		$expected = "test., 29. test 2016, 16:00 - <br>test., 02. test 2016, 17:00";
		$actual = UTILS::readBookingDate($bookingData);
		$this->assertEquals($expected, $actual);
	}

}
