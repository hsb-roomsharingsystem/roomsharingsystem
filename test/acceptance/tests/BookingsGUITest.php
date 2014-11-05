<?php

include_once './acceptance/php-webdriver/__init__.php';

/**
 * This class represents the gui-testing for the RoomSharing System
 *
 * @group bookings
 * @property WebDriver $webDriver
 */
class BookingsGUITest extends PHPUnit_Framework_TestCase {

	private static $webDriver;
	private static $url = 'http://localhost/roomsharingsystem'; // URL to local RoomSharing
	private static $rssObjectName; // name of RoomSharing pool
	private static $login_user = 'root';
	private static $login_pass = 'homer';

	public static function setUpBeforeClass() {
		global $rssObjectName;
		self::$rssObjectName = $rssObjectName;
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::firefox();
		self::$webDriver = RemoteWebDriver::create($host, $capabilities, 5000);
		self::$webDriver->manage()->timeouts()->implicitlyWait(3); // implicitly wait time => 3 sec.
		self::$webDriver->manage()->window()->maximize();  // maxize browser window
		self::$webDriver->get(self::$url); // go to RoomSharing System
	}

	public function setUp() {
		$this->login(self::$login_user, self::$login_pass);  // login
		$this->toRSS();
	}

	/**
	 * Create an invalid booking (time is in the past)
	 *
	 */
	public function invalidBooking() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Test Buchung", "12", "12", "2013", "10", "00", "12", "12", "2013", "11", "30", "true");
		$this->assertContains("Vergangenheit", $this->getErrMessage());
	}

	/**
	 * Create a valid booking and delete it after success.
	 * @test
	 */
	public function bookAndDelete() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", $this->getSuccMessage());
		$this->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", $this->getSuccMessage());
	}

	/**
	 * Create a valid booking. Try to create same once again. Delete after success.
	 * @test
	 */
	public function bookAndDelete2() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", $this->getSuccMessage());
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Der Raum ist in dem Zeitraum bereits gebucht", $this->getErrMessage());
		self::$webDriver->findElement(WebDriverBy::linkText('Zurück zu den Suchresultaten'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		$this->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", $this->getSuccMessage());
	}

	/**
	 * Create an invalid booking (time (from) same as time (to)).
	 * @test
	 */
	public function invalidBooking2() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "12", "12", "2017", "10", "00", "12", "12", "2017", "10", "00", "true");
		$this->assertContains("ist später oder gleich", $this->getErrMessage());
	}

	/**
	 * Create an invalid booking (too less informations given).
	 * @test
	 */
	public function invalidBooking3() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "00", "true");
		$this->assertContains("unvollständig", $this->getErrMessage());
	}

	/**
	 * Create an invalid booking (time to is earlier than time from).
	 * @test
	 */
	public function invalidBooking4() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "09", "00", "true");
		$this->assertContains("Vergangenheit", $this->getErrMessage());
	}

	// -------------  private methods  --------------------

	/**
	 * Search for room by room name.
	 * @param type $roomName Room name
	 */
	private function searchForRoomByName($roomName) {
		self::$webDriver->findElement(WebDriverBy::linkText('Suche'))->click();
		self::$webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		self::$webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);
		self::$webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
	}

	/**
	 * This method creates a booking.
	 */
	private function doABooking($subject, $f_d, $f_m, $f_y, $f_h, $f_m, $t_d, $t_m, $t_y, $t_h, $t_m, $acc) {
		self::$webDriver->findElement(WebDriverBy::id('subject'))->clear();
		self::$webDriver->findElement(WebDriverBy::id('subject'))->sendKeys($subject);
		self::$webDriver->findElement(WebDriverBy::id('from[date]_d'))->sendKeys($f_d);
		self::$webDriver->findElement(WebDriverBy::id('from[date]_m'))->sendKeys($f_m);
		self::$webDriver->findElement(WebDriverBy::id('from[date]_y'))->sendKeys($f_y);
		self::$webDriver->findElement(WebDriverBy::id('from[time]_h'))->sendKeys($f_h);
		self::$webDriver->findElement(WebDriverBy::id('from[time]_m'))->sendKeys($f_m);
		self::$webDriver->findElement(WebDriverBy::id('to[date]_d'))->sendKeys($t_d);
		self::$webDriver->findElement(WebDriverBy::id('to[date]_m'))->sendKeys($t_m);
		self::$webDriver->findElement(WebDriverBy::id('to[date]_y'))->sendKeys($t_y);
		self::$webDriver->findElement(WebDriverBy::id('to[time]_h'))->sendKeys($t_h);
		self::$webDriver->findElement(WebDriverBy::id('to[time]_m'))->sendKeys($t_m);
		//if ($acc == true) {
		//	self::$webDriver->findElement(WebDriverBy::id('accept_room_rules'))->click();
		//}
		self::$webDriver->findElement(WebDriverBy::name('cmd[book]'))->click();
	}

	public function login($user, $pass) {
		self::$webDriver->findElement(WebDriverBy::id('username'))->sendKeys($user);
		self::$webDriver->findElement(WebDriverBy::id('password'))->sendKeys($pass)->submit();
		self::$webDriver->findElement(WebDriverBy::id('mm_rep_tr'))->click();
	}

	public function toRSS() {
		self::$webDriver->findElement(WebDriverBy::linkText('Magazin - Einstiegsseite'))->click();
		self::$webDriver->findElement(WebDriverBy::xpath("(//a[contains(text(),'" . self::$rssObjectName . "')])[2]"))->click();
		$this->assertContains(self::$rssObjectName, self::$webDriver->getTitle());
	}

	private function getNoOfResults() {
		try {
			$result = self::$webDriver->findElement(WebDriverBy::cssSelector('span.ilTableFootLight'))->getText();
			return substr($result, strripos($result, " ") + 1, -1);
		} catch (WebDriverException $exception) {
			return 0;
		}
	}

	private function deleteBooking($subject) {
		$row = self::$webDriver->findElement(WebDriverBy::xpath("//tr[contains(text(), " . $subject . ")]/td[7]"));
		$row->findElement(WebDriverBy::linkText('Stornieren'))->click();
		self::$webDriver->findElement(WebDriverBy::name('cmd[cancelBooking]'))->click();
	}

	private function getFirstResult() {
		return self::$webDriver->findElement(WebDriverBy::cssSelector('td.std'))->getText();
	}

	private function getSuccMessage() {
		return self::$webDriver->findElement(WebDriverBy::cssSelector('div.ilSuccessMessage'))->getText();
	}

	private function getErrMessage() {
		return self::$webDriver->findElement(WebDriverBy::cssSelector('div.ilFailureMessage'))->getText();
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