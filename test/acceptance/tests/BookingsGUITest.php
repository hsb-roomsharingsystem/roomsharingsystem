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
	 * GUI tests for 'User Story 11 - Buchung als Student'
	 * @test
	 *
	 * TODO:  This is still under construcion..
	 */
	/*	 * public function testUserStory11(){
	  self::$webDriver->get($this->url);
	  $this->login($this->login_user, $this->login_pass);		// login
	  $this->toRSS();
	  // TC#1
	  //self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
	  //self::$webDriver->findElement(WebDriverBy::linkText('Buchung hinzufügen'))->click();
	  // TC#2
	  /**$this->searchForRoomByAll("", "10", date("d"), $this->getCurrentMonth(), date("Y"), "15", "00", "16", "30", "", "", "", "");
	  if($this->getNoOfResults() >= 1){
	  self::$webDriver->findElement(WebDriverBy::linkText('Buchen'))->click();
	  $this->doABooking("Lerngruppe Mathe2", "", "", "", date("d"), $this->getCurrentMonth(), date("Y"), "15", "00", date("d"), $this->getCurrentMonth(), date("Y"), "16", "30", true);
	  }
	  //TODO klick auf "Buchen"

	  // TC#3 -> noch nicht möglich, da das Hinzufügen von Teilnehmern noch nicht möglich ist
	  // TC#4 -> noch nicht möglich, da Höchstdauer einer Buchung noch nicht implementiert
	  // TC#5 -> noch nicht möglch, da Mehrfachbuchungen aktuell funktionieren
	  // TC#6 -> noch nicht möglich, da ein eingeladener Teilnehmer sich aktuell noch nicht austragen kann
	  // TC#7 -> noch nicht möglich, da Einladungen noch nicht implementiert sind
	  // TC#8 -> noch nicht möglich, da Einladungen noch nicht implementiert sind
	  $this->searchForRoomByName("116");
	  if($this->getNoOfResults() >= 1){
	  self::$webDriver->findElement(WebDriverBy::linkText('Buchen'))->click();
	  $this->doABooking("Lerngruppe Mathe2", "", "", "", date("d"), $this->getCurrentMonth(), date("Y"), date("H"), date("i"), date("d"), $this->getCurrentMonth(), date("Y"), date("i"), date("d") + (60*5), true);
	  }
	  //self::$webDriver->quit();
	  }
	 */

	/**
	 * Create an invalid booking.
	 *
	 */
	public function invalidBooking() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Test Buchung", "", "", "", "12", "12", "2013", "10", "00", "12", "12", "2013", "11", "30", "true");
		$this->assertContains("Vergangenheit", $this->getErrMessage());
	}

	/**
	 * Create a valid booking and delete after success.
	 * @test
	 */
	public function bookAndDelete() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", $this->getSuccMessage());
		$this->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", $this->getSuccMessage());
	}

	/**
	 * Create a valid booking and delete after success.
	 */
	public function bookAndDelete2() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "30", "true");
		$this->assertEquals("Buchung hinzugefügt", $this->getSuccMessage());
		$this->deleteBooking("Testbuchung");
		$this->assertContains("Buchung wurde gelöscht", $this->getSuccMessage());
	}

	/**
	 * Create an invalid booking.
	 * @test
	 */
	public function invalidBooking2() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "10", "00", "true");
		$this->assertContains("ist später oder gleich", $this->getErrMessage());
	}

	/**
	 * Create an invalid booking.
	 * @test
	 */
	public function invalidBooking3() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "11", "00", "true");
		$this->assertContains("unvollständig", $this->getErrMessage());
	}

	/**
	 * Create an invalid booking.
	 * @test
	 */
	public function invalidBooking4() {
		$this->searchForRoomByName("117");
		self::$webDriver->findElement(WebDriverBy::linktext('Buchen'))->click();
		$this->doABooking("Testbuchung", "", "", "", "12", "12", "2017", "10", "00", "12", "12", "2017", "09", "00", "true");
		$this->assertContains("später oder gleich", $this->getErrMessage());
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

	/**
	 * This method creates a booking.
	 * Click on "Buchung hinz
	 *
	 * @param type $subject		Subject of bookin
	 * @param type $mod
	 * @param type $cour
	 * @param type $sem
	 * @param type $f_d
	 * @param type $f_m
	 * @param type $f_y
	 * @param type $f_h
	 * @param type $f_m
	 * @param type $t_d
	 * @param type $t_m
	 * @param type $t_y
	 * @param type $t_h
	 * @param type $t_m
	 * @param type $acc
	 */
	private function doABooking($subject, $mod, $cour, $sem, $f_d, $f_m, $f_y, $f_h, $f_m, $t_d, $t_m, $t_y, $t_h, $t_m, $acc) {
		self::$webDriver->findElement(WebDriverBy::id('subject'))->clear();
		self::$webDriver->findElement(WebDriverBy::id('subject'))->sendKeys($subject);
		self::$webDriver->findElement(WebDriverBy::id('1'))->sendKeys($mod);
		self::$webDriver->findElement(WebDriverBy::id('2'))->sendKeys($cour);
		self::$webDriver->findElement(WebDriverBy::id('3'))->sendKeys($sem);
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