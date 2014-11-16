<?php

include_once './acceptance/php-webdriver/__init__.php';
include_once './acceptance/tests/SeleniumHelper.php';

/**
 * This class represents the gui-testing for the RoomSharing System
 *
 * @group selenium-bookings
 * @property WebDriver $webDriver
 * 
 * created by: Thomas Wolscht
 */
class BookingsGUITest extends PHPUnit_Framework_TestCase {

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
	 * Test invalid booking
	 * @test
	 */
	public function testInvalidBooking() {
		// Create an invalid booking (time is in the past)
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Test Buchung", "12", "12", "2013", "10", "00", "12", "12", "2013", "11", "30", "true");
		$this->assertContains("Vergangenheit", self::$helper->getErrMessage());
		self::$webDriver->findElement(
				WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
		// Create an invalid booking (time (from) same as time (to)).
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "10", "00", "true");
		$this->assertContains("ist später oder gleich", self::$helper->getErrMessage());
		self::$webDriver->findElement(
				WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
		// Create an invalid booking (too less informations given).
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "00", "true");
		$this->assertContains("unvollständig", self::$helper->getErrMessage());
		self::$webDriver->findElement(
				WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
		// Create an invalid booking (time to is earlier than time from).
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "09", "00", "true");
		$this->assertContains("Vergangenheit", self::$helper->getErrMessage());
		self::$webDriver->findElement(
				WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
	}

	/**
	 * Test invalid booking.
	 * @test
	 */
	public function testValidBooking() {
		// Create a valid booking and delete it after success.
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", self::$helper->getSuccMessage());
		self::$helper->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", self::$helper->getSuccMessage());
		// Create a valid booking. Try to create same once again. Delete after success.
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", self::$helper->getSuccMessage());
		self::$helper->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		self::$helper->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Der Raum ist in dem Zeitraum bereits gebucht", self::$helper->getErrMessage());
		self::$webDriver->findElement(
				WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		self::$helper->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", self::$helper->getSuccMessage());
	}

	/**
	 * Closes web browser.
	 */
	public static function tearDownAfterClass() {
		self::$webDriver->quit();
	}

	/**
	 * Log out after each test case.
	 */
	public function tearDown() {
		self::$webDriver->findElement(WebDriverBy::linkText('Abmelden'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Bei ILIAS anmelden'))->click();
	}

}

?>