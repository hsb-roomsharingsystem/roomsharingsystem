<?php

include_once './acceptance/php-webdriver/__init__.php';
include_once './acceptance/tests/ilRoomSharingAcceptanceSeleniumHelper.php';

/**
 * This class represents the gui-testing for the RoomSharing System
 *
 * @group selenium-floorplans
 * @property WebDriver $webDriver
 *
 * created by: Thomas Wolscht
 */
class ilRoomSharingAcceptanceFloorPlansTest extends PHPUnit_Framework_TestCase
{
	private static $webDriver;
	private static $url = 'http://localhost/roomsharingsystem'; // URL to local RoomSharing
	private static $rssObjectName; // name of RoomSharing pool
	private static $login_user = 'root';
	private static $login_pass = 'homer';
	/**
	 * Must contain:
	 * - sucess.jpg with size > 0
	 * - sucess.bmp with size > 0
	 * - fail.pdf with size > 0
	 * - fail.txt with size > 0
	 * - empty.txt with size = 0
	 * - big.jpg with size bigger than upload limit
	 * @var string
	 */
	private static $test_file_absolut_path = 'C:\Users\Dan\Desktop\\';
	private static $helper;

	public static function setUpBeforeClass()
	{
		global $rssObjectName;
		self::$rssObjectName = $rssObjectName;
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::firefox();
		self::$webDriver = RemoteWebDriver::create($host, $capabilities, 5000);
		self::$webDriver->manage()->timeouts()->implicitlyWait(3); // implicitly wait time => 3 sec.
		self::$webDriver->manage()->window()->maximize();  // maxize browser window
		self::$webDriver->get(self::$url); // go to RoomSharing System
		self::$helper = new ilRoomSharingAcceptanceSeleniumHelper(self::$webDriver, self::$rssObjectName);
	}

	public function setUp()
	{
		self::$helper->login(self::$login_user, self::$login_pass);  // login
		self::$helper->toRSS();
	}

	/**
	 * Test functions for adding new floorplans
	 * @test
	 */
	public function testAddingFloorPlans()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Gebäudeplan'))->click();

		//#1 Test for Back Links from Adding a floor plan
		self::$webDriver->findElement(WebDriverBy::linkText(' Gebäudeplan hinzufügen '))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		try
		{
			self::$webDriver->findElement(WebDriverBy::linkText('Gebäudepläne anzeigen'));
		}
		catch (Exception $ex)
		{
			$this->fail("#1.1: Return from adding floorplans to floorplans overview via backtab does not work.");
		}
		self::$webDriver->findElement(WebDriverBy::linkText(' Gebäudeplan hinzufügen '))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();
		try
		{
			self::$webDriver->findElement(WebDriverBy::linkText('Gebäudepläne anzeigen'));
		}
		catch (Exception $ex)
		{
			$this->fail("#1.2: Return from adding floorplans to floorplans overview via backtab does not work.");
		}

		//#2 Test empty Title
		self::$helper->createFloorPlan('', self::$test_file_absolut_path . 'sucess.jpg', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#2 Add an no-title floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		//#3 Test empty Plan
		self::$helper->createFloorPlan('Test_A', '', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#3 Add an no-plan floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		//#4 Test empty.txt
		self::$helper->createFloorPlan('Test_A', self::$test_file_absolut_path . 'empty.jpg', 'Test');
		try
		{
			//self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#4 Add an empty picture as floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		//self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();
		//#5 Test big.jpg
		self::$helper->createFloorPlan('Test_A', self::$test_file_absolut_path . 'big.jpg', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#5 Add an too big picture as floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		//#6 Test fail.txt
		self::$helper->createFloorPlan('Test_A', self::$test_file_absolut_path . 'fail.txt', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#6 Add an txt as floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		//#7 Test fail.pdf
		self::$helper->createFloorPlan('Test_A', self::$test_file_absolut_path . 'fail.pdf', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#7 Add an pdf as floorplan seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		//#8 Test sucess.jpg
		self::$helper->createFloorPlan('Test_A', self::$test_file_absolut_path . 'sucess.jpg', 'Test');
		try
		{
			self::$webDriver->findElement(WebDriverBy::linkText(" Test_A"));
		}
		catch (Exception $ex)
		{
			$this->fail("#8 Adding a jpg as floorplan seems not to work");
		}

		//#9 Test sucess.bmp
		self::$helper->createFloorPlan('Test_B', self::$test_file_absolut_path . 'sucess.bmp', 'Test2');
		try
		{
			self::$webDriver->findElement(WebDriverBy::linkText(" Test_B"));
		}
		catch (Exception $ex)
		{
			$this->fail("#9 Adding a bmp as floorplan seems not to work");
		}

		//#10 Add existing Title
		self::$helper->createFloorPlan('Test_B', self::$test_file_absolut_path . 'sucess.bmp', 'Test2');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className("ilFailureMessage"));
		}
		catch (Exception $ex)
		{
			$this->fail("#10 Adding an existing title seems to work");
		}
		//self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Gebäudeplänen'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[render]'))->click();

		self::$helper->deleteAllFloorPlans();
	}

	/**
	 * @test
	 */
	public function testEditAndDeleteFloorPlans()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Gebäudeplan'))->click();
		self::$helper->deleteAllFloorPlans();
	}

	/**
	 *
	 */
	public function tstAssignmentEffectsOfFloorplans()
	{

	}

	/**
	 * GUI tests for floorplans.
	 *
	 * These tests are not the ones from "User Story (Gebäudeplan)" because
	 * the tests from user story use functionality which is not yet implemented.
	 */
	public function tstGebaeudePlaeneExplorativ()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Gebäudeplan'))->click();
		/**
		 *  Check editing of an existing floorplan
		 */
		if (self::$helper->getNoOfResults() >= 1)
		{
			$desc = self::$webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[3]"))->getText();
			$menu = self::$webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[4]"));
			$menu->findElement(WebDriverBy::linkText('Aktionen'))->click();
			$menu->findElement(WebDriverBy::linkText('Bearbeiten'))->click();
			$desc1 = self::$webDriver->findElement(WebDriverBy::id("description"));
			$desc1->clear();
			$desc1->sendKeys($desc . "test");
			self::$webDriver->findElement(WebDriverBy::name("cmd[update]"))->click();
			$succ_mess = self::$webDriver->findElement(WebDriverBy::cssSelector("div.ilSuccessMessage"))->getText();
			$new_desc = self::$webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[3]"))->getText();
			// assert that editing was successful
			$this->assertEquals("Gebäudeplan erfolgreich aktualisiert", $succ_mess);
			// assert that new description is correct
			$this->assertEquals($desc . "test", $new_desc);
			// undo changes
			$menu2 = self::$webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[4]"));
			$menu2->findElement(WebDriverBy::linkText('Aktionen'))->click();
			$menu2->findElement(WebDriverBy::linkText('Bearbeiten'))->click();
			$desc2 = self::$webDriver->findElement(WebDriverBy::id("description"));
			$desc2->clear();
			$desc2->sendKeys($desc);
			self::$webDriver->findElement(WebDriverBy::name("cmd[update]"))->click();
			$succ_mess = self::$webDriver->findElement(WebDriverBy::cssSelector("div.ilSuccessMessage"))->getText();
		}
		// check adding floorplan with insufficient informations
		self::$webDriver->findElement(WebDriverBy::linkText(" Gebäudeplan hinzufügen "))->click();
		self::$webDriver->findElement(WebDriverBy::id("title"))->sendKeys("Mein Titel");
		self::$webDriver->findElement(WebDriverBy::id("description"))->sendKeys("Meine Beschreibung");
		self::$webDriver->findElement(WebDriverBy::name("cmd[save]"))->click();
		$error_mess = self::$webDriver->findElement(WebDriverBy::cssSelector("div.ilFailureMessage"))->getText();
		// assert error message
		$this->assertEquals("Einige Angaben sind unvollständig oder ungültig. Bitte korrigieren Sie Ihre Eingabe.",
			$error_mess);
		//self::$webDriver->quit();
	}

	/**
	 * Closes web browser.
	 */
	public static function tearDownAfterClass()
	{
		self::$webDriver->quit();
	}

	public function tearDown()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Abmelden'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Bei ILIAS anmelden'))->click();
	}

}

?>