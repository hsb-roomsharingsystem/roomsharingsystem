<?php

chdir("../../../../../../../../");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/class.ilRoomSharingRooms.php");
require_once("Services/Database/classes/class.ilDBMySQL.php");

/**
 * @group unit
 */
class ilRoomSharingRoomsTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        global $ilDB;
        $ilDB = $this->getMock("ilDBMySQL", array("quote", "query", "fetchAssoc"));
        $ilDB->method("query")->will($this->returnValue("1"));
        $ilDB->method("quote")->will($this->returnValue("1"));
    }

    /**
     * @covers ilRoomSharingRooms::getRoomName
     * @todo   Implement testGetRoomName().
     */
    public function testGetRoomName()
    {
        global $ilDB;

        $key = "name";
        $expected = "123";
        $array = array($key => $expected);
        $ilDB->method("fetchAssoc")->will($this->returnValue($array));
        $roomsharing = new ilRoomSharingRooms();
        $actual = $roomsharing->getRoomName($key);
        $this->assertEquals($expected, $actual);
    }

}