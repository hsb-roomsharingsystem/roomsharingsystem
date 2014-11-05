<?php

include_once './acceptance/php-webdriver/__init__.php';

/**
 * This class represents the gui-testing for the RoomSharing System
 *
 * @group search
 * @property WebDriver $webDriver
 */
class SerchGUITest extends PHPUnit_Framework_TestCase {

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
	 * GUI tests for 'User Story 4 - Suche als Student - Variante 1'
	 *
	 * @test
	 */
	public function testUserStory4() {


		/**
		 *  TC#2
		 * 	Search for room "123". Verify result.
		 */
		$this->searchForRoomByName("123");
		$this->assertEquals("1", $this->getNoOfResults());
		if ($this->getNoOfResults() == 1) {
			$this->assertEquals("123", $this->getFirstResult());
		}
		/**
		 *  TC#3
		 * 	Search for room "032a". Verify result.
		 */
		$this->searchForRoomByName("032a");
		if ($this->getNoOfResults() == 1) {
			$this->assertEquals("032A", $this->getFirstResult());
		}
		/**
		 *  TC#4
		 * 	Search for room "\';SELECT * FROM usr;--". Verify result.
		 * 	Test SQL injection.
		 */
		$this->searchForRoomByName("\';SELECT * FROM usr;--");
		$this->assertEquals("0", $this->getNoOfResults());
		/**
		 *  TC#5
		 * 	Search for room "123" with date (today).
		 */
		$this->searchForRoomByAll("123", "", date("d"), $this->getCurrentMonth(), date("Y"), date("H"), (date("i") + (60 * 5)), "23", "55", "", "", "", "");
		if ($this->getNoOfResults() == 1) {
			$this->assertEquals("123", $this->getFirstResult());
		}
		/**
		 *  TC#6
		 *
		 */
		$this->searchForRoomByAll("123", "", date("d") - 1, $this->getCurrentMonth(), date("Y"), "00", "00", date("H"), date("i"), "", "", "", "");
		// TC#7 ==> Buchen in Vergangenheit funktioniert
		// TC#8 ==> evtl. nur mit erweiterer Suche möglich
		// TC#9 ==> TC nicht mehr gültig, da Buchen nun möglich ist
		//self::$webDriver->quit();
	}

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
	 * Search for room by all possible informations.
	 * @param type $roomName	Room name
	 * @param type $seats		Amount of seats
	 * @param type $day			Day of booking
	 * @param type $month		Month of booking
	 * @param type $year		Year of booking
	 * @param type $h_from		Hour (from)
	 * @param type $m_from		Minutes (from)
	 * @param type $h_to		Hour (to)
	 * @param type $m_to		Minutes (to)
	 * @param type $beamer		Amount of beamer
	 * @param type $sound		Amount of sound systems
	 * @param type $proj		Amount of projectors
	 * @param type $white		Amount of whiteboards
	 */
	private function searchForRoomByAll($roomName, $seats, $day, $month, $year, $h_from, $m_from, $h_to, $m_to, $beamer, $sound, $proj, $white) {
		self::$webDriver->findElement(WebDriverBy::linkText('Suche'))->click();
		self::$webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		self::$webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);
		self::$webDriver->findElement(WebDriverBy::id('date[date]_d'))->sendKeys($day);
		self::$webDriver->findElement(WebDriverBy::id('date[date]_m'))->sendKeys($month);
		self::$webDriver->findElement(WebDriverBy::id('date[date]_y'))->sendKeys($year);
		self::$webDriver->findElement(WebDriverBy::id('time_from[time]_h'))->sendKeys($h_from);
		self::$webDriver->findElement(WebDriverBy::id('time_from[time]_m'))->sendKeys($m_from);
		self::$webDriver->findElement(WebDriverBy::id('time_to[time]_h'))->sendKeys($h_to);
		self::$webDriver->findElement(WebDriverBy::id('time_to[time]_m'))->sendKeys($m_to);
		self::$webDriver->findElement(WebDriverBy::id('attribute_Beamer_amount'))->sendKeys($beamer);
		self::$webDriver->findElement(WebDriverBy::id('attribute_Soundanlage_amount'))->sendKeys($sound);
		self::$webDriver->findElement(WebDriverBy::id('attribute_Tageslichprojektor_amount'))->sendKeys($proj);
		self::$webDriver->findElement(WebDriverBy::id('attribute_Whiteboard_amount'))->sendKeys($white);
		self::$webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
	}

	private function getCurrentMonth() {
		$monate = array(1 => "Januar",
			2 => "Februar",
			3 => "M&auml;rz",
			4 => "April",
			5 => "Mai",
			6 => "Juni",
			7 => "Juli",
			8 => "August",
			9 => "September",
			10 => "Oktober",
			11 => "November",
			12 => "Dezember");
		$monat = date("n");
		return $monate[$monat];
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

	private function getCurrentDay() {
		return date("d");
	}

	private function getCurrentYear() {
		return date("y");
	}

	private function getNoOfResults() {
		try {
			$result = self::$webDriver->findElement(WebDriverBy::cssSelector('span.ilTableFootLight'))->getText();
			return substr($result, strripos($result, " ") + 1, -1);
		} catch (WebDriverException $exception) {
			return 0;
		}
	}

	private function getFirstResult() {
		return self::$webDriver->findElement(WebDriverBy::cssSelector('td.std'))->getText();
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