<?php

include_once './acceptance/php-webdriver/__init__.php';
include_once './acceptance/tests/SeleniumHelper.php';

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
		self::$helper = new SeleniumHelper(self::$webDriver, self::$rssObjectName);
	}

	public function setUp()
	{
		self::$helper->login(self::$login_user, self::$login_pass);  // login
		self::$helper->toRSS();
	}

	/**
	 * GUI tests for floorplans.
	 *
	 * These tests are not the ones from "User Story (Gebäudeplan)" because
	 * the tests from user story use functionality which is not yet implemented.
	 * @test
	 */
	public function testGebaeudePlaeneExplorativ()
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