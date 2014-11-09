<?php

include_once './acceptance/php-webdriver/__init__.php';
include_once './acceptance/tests/SeleniumHelper.php';

/**
 * This class represents the gui-testing for the RoomSharing System
 *
 * @group selenium-search
 * @property WebDriver $webDriver
 *
 * created by: Thomas Wolscht
 */
class SerchGUITest extends PHPUnit_Framework_TestCase {

	private static $webDriver;
	private static $url = 'http://localhost/roomsharingsystem'; // URL to local RoomSharing
	private static $rssObjectName; // name of RoomSharing pool
	private static $login_user = 'root';
	private static $login_pass = 'homer';
	private static $helper;

	public static function setUpBeforeClass() {
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

	public function setUp() {
		self::$helper->login(self::$login_user, self::$login_pass);  // login
		self::$helper->toRSS();
	}

	/**
	 * Test valid search.
	 * @test
	 */
	public function testValidSearch() {
		// Search for room "123". Verify result.
		self::$helper->searchForRoomByName("123");
		$this->assertEquals("1", self::$helper->getNoOfResults());
		if (self::$helper->getNoOfResults() == 1) {
			$this->assertEquals("123", self::$helper->getFirstResult());
		}
		// Search for room "032a". Verify result.
		self::$helper->searchForRoomByName("032a");
		if (self::$helper->getNoOfResults() == 1) {
			$this->assertEquals("032A", self::$helper->getFirstResult());
		}
		// Search for room "123" with date (today).
		self::$helper->searchForRoomByAll("123", "", date("d"), self::$helper->getCurrentMonth(), date("Y"), date("H"), (date("i") + (60 * 5)), "23", "55", "", "", "", "");
		if (self::$helper->getNoOfResults() == 1) {
			$this->assertEquals("123", self::$helper->getFirstResult());
		}
	}

	/**
	 * 	Test invalid search.
	 * @test
	 */
	public function testInvalidSearch() {
		// Search for room "\';SELECT * FROM usr;--". Verify result.
		// Test SQL injection.
		self::$helper->searchForRoomByName("\';SELECT * FROM usr;--");
		$this->assertEquals("0", self::$helper->getNoOfResults());
		// Search for room with invalid date
		self::$helper->searchForRoomByAll("123", "", date("d"), self::$helper->getCurrentMonth(), date("Y"), date("H"), date("i"), "01", "00", "", "", "", "");
		$this->assertContains("unvollständig oder ungültig", self::$helper->getErrMessage());
		// Search for room with invalid search parameter
		self::$helper->searchForRoomByAll("123", "", date("d"), self::$helper->getCurrentMonth(), date("Y"), date("H"), date("i"), "23", "55", "999", "999", "999", "");
		$this->assertContains("unvollständig oder ungültig", self::$helper->getErrMessage());
	}

	/**
	 * Closes web browser.
	 */
	public static function tearDownAfterClass() {
		self::$webDriver->quit();
	}

	public function tearDown() {
		self::$webDriver->findElement(WebDriverBy::linkText('Abmelden'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Bei ILIAS anmelden'))->click();
	}

}

?>