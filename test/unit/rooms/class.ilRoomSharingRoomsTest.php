<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Services/Database/classes/class.ilDBMySQL.php");

/**
 * Class ilRoomSharingRoomsTest
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @group unit
 */
class ilRoomSharingRoomsTest extends PHPUnit_Framework_TestCase
{
    private static $rooms;

    public static function setUpBeforeClass()
    {
        global $ilDB;

        $test = new ilRoomSharingRoomsTest();
        $ilDB = $test->getMockBuilder("ilDBMySQL")
            ->setMethods(array("quote", "query", "fetchAssoc"))
            ->getMock();
        $ilDB->method("query")->willReturn("1");
        $ilDB->method("quote")->willReturn("1");

        self::$rooms = new ilRoomSharingRooms();
    }

    public function testGetRoomName()
    {
        global $ilDB;
        $expected = "012";
        $key = "name";
        $array = array($key => $expected);
        $ilDB->method("fetchAssoc")->willReturn($array);

        $actual = self::$rooms->getRoomName($key);
        $this->assertEquals($expected, $actual);
    }

    public function testGetRoomsBookedInDateTimeRange()
    {
        global $ilDB;
        $key = "room_id";
        $value = "1";
        $expected[] = $value;
        $array = array($key => $value);
        $a_date_from = "2014-10-27 09:00:00";
        $a_date_to = "2014-10-27 10:20:00";
        $ilDB->method("fetchAssoc")->will($this->onConsecutiveCalls($array, null));

        $actual = self::$rooms->getRoomsBookedInDateTimeRange($a_date_from, $a_date_to);
        $this->assertEquals($expected, $actual);
    }

    public function testGetRoomsBookedInDateTimeRangeWithRoomId()
    {
        global $ilDB;
        $key = "room_id";
        $value = "1";
        $expected[] = $value;
        $array = array($key => $value);
        $a_date_from = "2014-10-27 09:00:00";
        $a_date_to = "2014-10-27 10:20:00";
        $a_room_id = "1";
        $ilDB->method("fetchAssoc")->will($this->onConsecutiveCalls($array, null));

        $actual = self::$rooms->getRoomsBookedInDateTimeRange($a_date_from, $a_date_to, $a_room_id);
        $this->assertEquals($expected, $actual);
    }

    public function testGetMaxCountForAttribute()
    {
        global $ilDB;
        $key = "value";
        $expected = 1;
        $array = array($key => $expected);
        $a_attribute = "Beamer";
        $ilDB->method("fetchAssoc")->will($this->onConsecutiveCalls(null, $array));

        $actual = self::$rooms->getMaxCountForAttribute($a_attribute);
        $this->assertEquals($expected, $actual);
    }

    public function testGetMaxSeatCount()
    {
        global $ilDB;
        $key = "value";
        $expected = 100;
        $array = array($key => $expected);
        $ilDB->method("fetchAssoc")->willReturn($array);

        $actual = self::$rooms->getMaxSeatCount();
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllAttributes()
    {
        global $ilDB;
        $name = "name";
        $beamer = "Beamer";
        $whiteboard = "Whiteboard";
        $assoc_beamer = array($name => $beamer);
        $assoc_whiteboard = array($name => $whiteboard);
        $expected = array($beamer, $whiteboard);
        $ilDB->method("fetchAssoc")
            ->will($this->onConsecutiveCalls($assoc_beamer, $assoc_whiteboard, null));

        $actual = self::$rooms->getAllAttributes();
        $this->assertEquals($expected, $actual);
    }

    public function testGetList()
    {
        $this->markTestIncomplete();
    }

}
?>