<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/floorplans/class.ilRoomSharingFloorPlans.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/database/class.ilRoomSharingDatabase.php");
require_once("Services/UICore/classes/class.ilCtrl.php");
require_once("Services/Language/classes/class.ilLanguage.php");
require_once("Services/Init/classes/class.ilias.php");
require_once("Services/Utilities/classes/class.ilBenchmark.php");
require_once("Services/Database/classes/class.ilDB.php");
require_once("Services/User/classes/class.ilObjUser.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/Object/classes/class.ilObjectDataCache.php");
require_once("Services/Logging/classes/class.ilLog.php");

//require_once("Services/Object/classes/class.ilObjectDefinition.php");
//require_once("Services/Xml/classes/class.ilSaxParser.php");

class ilObjectDefinition
{
	public function isRBACObject($a_val)
	{
		return false;
	}

	public function getTranslationType($a_val)
	{
		return $a_val;
	}

}

/**
 * Class ilRoomSharingFloorPlansTest
 *
 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @group unit
 */
class ilRoomSharingFloorPlansTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomSharingFloorPlans
	 */
	private static $floorPlans;
	private static $DBMock;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$test = new self();
		self::$DBMock = $test->getMockBuilder('ilRoomSharingDatabase')->disableOriginalConstructor()->getMock();

		$allFloorPlans = array(
			array(
				'file_id' => 230,
				'title' => 'Green plan'
			),
			array(
				'file_id' => 234,
				'title' => 'Red plan'
			)
		);
		self::$DBMock->method("getAllFloorplans")->willReturn($allFloorPlans);

// We assume that we have all privileges.
		global $rssPermission;
		$rssPermission = $test->getMockBuilder('ilRoomSharingPermissionUtils')->disableOriginalConstructor()->getMock();
		$rssPermission->method("checkPrivilege")->willReturn(true);

		global $ilCtrl;
		$ilCtrl = $test->getMockBuilder('ilCtrl')->disableOriginalConstructor()->getMock();

		global $lng;
		$lng = $test->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock();

		self::$floorPlans = new ilRoomSharingFloorPlans(1, self::$DBMock);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::getAllFloorPlans
	 */
	public function testGetAllFloorPlans()
	{
		$allFloorPlans = self::$floorPlans->getAllFloorPlans();

		self::assertEquals(2, count($allFloorPlans));

		self::assertContains(array(
			'file_id' => 230,
			'title' => 'Green plan'
			), $allFloorPlans);

		self::assertContains(array(
			'file_id' => 234,
			'title' => 'Red plan'
			), $allFloorPlans);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::getFloorPlanInfo
	 */
	public function testGetFloorPlanInfo()
	{
		$floorPlanInfo = array(
			'bla' => 'blu'
		);
		self::$DBMock->method("getFloorplan")->willReturn($floorPlanInfo);

		$info = self::$floorPlans->getFloorPlanInfo(234);

		self::$DBMock->expects($this->once())->method('getFloorplan')->with($this->equalTo(234));

		self::assertEquals(1, count($info));
		self::assertArrayHasKey('bla', $info);
		self::assertContains('blu', $info);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::fileToDatabase
	 */
	public function testFileToDatabase()
	{
		self::$DBMock->method("insertFloorplan")->willReturn(3);

		$rtn = self::$floorPlans->fileToDatabase(235);

		self::$DBMock->expects($this->once())->method('insertFloorplan')->with($this->equalTo(235));
		self::assertEquals(3, $rtn);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::deleteFloorPlan
	 */
	public function testDeleteFloorPlan()
	{
		define('DEBUG', 0);
		define('MAXLENGTH_OBJ_TITLE', 100);
		define('MAXLENGTH_OBJ_DESC', 100);
		define('MDB2_AUTOQUERY_INSERT', 'ins');

		global $ilias, $ilBench, $ilDB, $objDefinition, $ilObjDataCache;
		$ilias = self::getMockBuilder('ilias')->disableOriginalConstructor()->getMock();
		$ilBench = self::getMockBuilder('ilBenchmark')->disableOriginalConstructor()->getMock();
		$ilDB = self::getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$objDefinition = new ilObjectDefinition();

		$ilObjDataCache = self::getMockBuilder('ilObjectDataCache')->disableOriginalConstructor()->getMock();
		$ilObjDataCache->method("lookupType")->willReturn('xxx');

		$objData = array(
			'obj_id' => 235,
			'type' => 'xxx',
			'title' => 'Red floorplan',
			'description' => 'Newest plan',
			'owner' => 1,
			'create_date' => '2015',
			'last_update' => '2015',
			'import_id' => 0
		);
		$objUsages = array("mep_id" => "a");
		$ilDB->method("numRows")->willReturn(1);
		$ilDB->method("fetchAssoc")->will(
			self::onConsecutiveCalls($objData, false, false, $objUsages));

		self::$DBMock->method("deleteFloorPlan")->willReturn(1);

		self::$floorPlans->deleteFloorPlan(235);

		self::$DBMock->expects($this->once())->method('deleteFloorPlan')->with($this->equalTo(235));
		self::$DBMock->expects($this->once())->method('deleteFloorplanRoomAssociation')->with($this->equalTo(235));
	}

	/**
	 * @covers ilRoomSharingFloorPlans::getRoomsWithFloorplan
	 */
	public function testGetRoomsWithFloorplan()
	{
		$roomIDs = array(1, 2, 3);
		self::$DBMock->method("getRoomsWithFloorplan")->willReturn($roomIDs);

		self::assertEquals($roomIDs, self::$floorPlans->getRoomsWithFloorplan(342));

		self::$DBMock->expects($this->once())->method('getRoomsWithFloorplan')->with($this->equalTo(342));
	}

	/**
	 * @covers ilRoomSharingFloorPlans::updateFloorPlanInfos
	 * @todo   Implement testUpdateFloorPlanInfos().
	 */
	public function testUpdateFloorPlanInfos()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::updateFloorPlanInfosAndFile
	 * @todo   Implement testUpdateFloorPlanInfosAndFile().
	 */
	public function testUpdateFloorPlanInfosAndFile()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::addFloorPlan
	 * @todo   Implement testAddFloorPlan().
	 */
	public function testAddFloorPlan()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomSharingFloorPlans::checkImageType
	 */
	public function testCheckImageType()
	{
		self::assertTrue(self::$floorPlans->checkImageType("image/bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-bitmap"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-xbitmap"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-win-bitmap"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-windows-bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-ms-bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("application/bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("application/x-bmp"));
		self::assertTrue(self::$floorPlans->checkImageType("application/x-win-bitmap"));
		//Formats for type ".png"
		self::assertTrue(self::$floorPlans->checkImageType("image/png"));
		self::assertTrue(self::$floorPlans->checkImageType("application/png"));
		self::assertTrue(self::$floorPlans->checkImageType("application/x-png"));
		//Formats for type ".jpg/.jpeg"
		self::assertTrue(self::$floorPlans->checkImageType("image/jpeg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/jpg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/jp_"));
		self::assertTrue(self::$floorPlans->checkImageType("application/jpg"));
		self::assertTrue(self::$floorPlans->checkImageType("application/x-jpg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/pjpeg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/pipeg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/vnd.swiftview-jpeg"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-xbitmap"));
		//Formats for type ".gif"
		self::assertTrue(self::$floorPlans->checkImageType("image/gif"));
		self::assertTrue(self::$floorPlans->checkImageType("image/x-xbitmap"));
		self::assertTrue(self::$floorPlans->checkImageType("image/gi_"));

		// Negative cases
		self::assertFalse(self::$floorPlans->checkImageType("file/pdf"));
		self::assertFalse(self::$floorPlans->checkImageType(""));
		self::assertFalse(self::$floorPlans->checkImageType(12));
		self::assertFalse(self::$floorPlans->checkImageType(NULL));
		self::assertFalse(self::$floorPlans->checkImageType(1));
	}

}
