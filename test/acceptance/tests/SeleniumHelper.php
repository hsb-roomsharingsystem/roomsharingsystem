<?php

/**
 * This class holds different methods to help creating readable Selenium tests.
 *
 * created by: Thomas Wolscht
 */
class SeleniumHelper {

	private $webDriver;
	private $rssObjectName;

	public function SeleniumHelper($driver, $rss) {

		$this->webDriver = $driver;
		$this->rssObjectName = $rss;
	}

	/**
	 * Search for room by room name.
	 * @param type $roomName Room name
	 */
	public function searchForRoomByName($roomName) {
		$this->webDriver->findElement(WebDriverBy::linkText('Suche'))->click();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);
		$this->webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
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
	public function searchForRoomByAll($roomName, $seats, $day, $month, $year, $h_from, $m_from, $h_to, $m_to, $beamer, $sound, $proj, $white) {
		$this->webDriver->findElement(WebDriverBy::linkText('Suche'))->click();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);
		$this->webDriver->findElement(WebDriverBy::id('date[date]_d'))->sendKeys($day);
		$this->webDriver->findElement(WebDriverBy::id('date[date]_m'))->sendKeys($month);
		$this->webDriver->findElement(WebDriverBy::id('date[date]_y'))->sendKeys($year);
		$this->webDriver->findElement(WebDriverBy::id('time_from[time]_h'))->sendKeys($h_from);
		$this->webDriver->findElement(WebDriverBy::id('time_from[time]_m'))->sendKeys($m_from);
		$this->webDriver->findElement(WebDriverBy::id('time_to[time]_h'))->sendKeys($h_to);
		$this->webDriver->findElement(WebDriverBy::id('time_to[time]_m'))->sendKeys($m_to);
		$this->webDriver->findElement(WebDriverBy::id('attribute_Beamer_amount'))->sendKeys($beamer);
		$this->webDriver->findElement(WebDriverBy::id('attribute_Soundanlage_amount'))->sendKeys($sound);
		$this->webDriver->findElement(WebDriverBy::id('attribute_Tageslichprojektor_amount'))->sendKeys($proj);
		$this->webDriver->findElement(WebDriverBy::id('attribute_Whiteboard_amount'))->sendKeys($white);
		$this->webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
	}

	/**
	 * Get current Month
	 * @return current month
	 */
	public function getCurrentMonth() {
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

	/**
	 * Login to RoomSharing
	 * @param type $user
	 * @param type $pass
	 */
	public function login($user, $pass) {
		$this->webDriver->findElement(WebDriverBy::id('username'))->sendKeys($user);
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys($pass)->submit();
		$this->webDriver->findElement(WebDriverBy::id('mm_rep_tr'))->click();
	}

	/**
	 * Navigate to RoomSharing Pool
	 */
	public function toRSS() {
		$this->webDriver->findElement(WebDriverBy::linkText('Magazin - Einstiegsseite'))->click();
		$this->webDriver->findElement(WebDriverBy::xpath("(//a[contains(text(),'" . $this->rssObjectName . "')])[2]"))->click();
		//$this->assertContains(self::$rssObjectName, $this->webDriver->getTitle());
	}

	/**
	 * Get current day
	 * @return day
	 */
	public function getCurrentDay() {
		return date("d");
	}

	/**
	 * Get current year
	 * @return year
	 */
	public function getCurrentYear() {
		return date("y");
	}

	/**
	 * Get amount of search results.
	 * @return search results
	 */
	public function getNoOfResults() {
		try {
			$result = $this->webDriver->findElement(WebDriverBy::cssSelector('span.ilTableFootLight'))->getText();
			return substr($result, strripos($result, " ") + 1, -1);
		} catch (WebDriverException $exception) {
			return 0;
		}
	}

	/**
	 * Get first result of search.
	 * @return first result
	 */
	public function getFirstResult() {
		return $this->webDriver->findElement(WebDriverBy::cssSelector('td.std'))->getText();
	}

	/**
	 * Get error message.
	 * @return error message
	 */
	public function getErrMessage() {
		return $this->webDriver->findElement(WebDriverBy::cssSelector('div.ilFailureMessage'))->getText();
	}

	/**
	 * This method creates a booking.
	 */
	public function doABooking($subject, $f_d, $f_m, $f_y, $f_h, $f_m, $t_d, $t_m, $t_y, $t_h, $t_m, $acc) {
		$this->webDriver->findElement(WebDriverBy::id('subject'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('subject'))->sendKeys($subject);
		$this->webDriver->findElement(WebDriverBy::id('from[date]_d'))->sendKeys($f_d);
		$this->webDriver->findElement(WebDriverBy::id('from[date]_m'))->sendKeys($f_m);
		$this->webDriver->findElement(WebDriverBy::id('from[date]_y'))->sendKeys($f_y);
		$this->webDriver->findElement(WebDriverBy::id('from[time]_h'))->sendKeys($f_h);
		$this->webDriver->findElement(WebDriverBy::id('from[time]_m'))->sendKeys($f_m);
		$this->webDriver->findElement(WebDriverBy::id('to[date]_d'))->sendKeys($t_d);
		$this->webDriver->findElement(WebDriverBy::id('to[date]_m'))->sendKeys($t_m);
		$this->webDriver->findElement(WebDriverBy::id('to[date]_y'))->sendKeys($t_y);
		$this->webDriver->findElement(WebDriverBy::id('to[time]_h'))->sendKeys($t_h);
		$this->webDriver->findElement(WebDriverBy::id('to[time]_m'))->sendKeys($t_m);
		//if ($acc == true) {
		//	$this->webDriver->findElement(WebDriverBy::id('accept_room_rules'))->click();
		//}
		$this->webDriver->findElement(WebDriverBy::name('cmd[book]'))->click();
	}

	/**
	 * Delete a booking by subject.
	 * @param booking subject
	 */
	public function deleteBooking($subject) {
		$row = $this->webDriver->findElement(WebDriverBy::xpath("//tr[contains(text(), " . $subject . ")]/td[7]"));
		$row->findElement(WebDriverBy::linkText('Stornieren'))->click();
		$this->webDriver->findElement(WebDriverBy::name('cmd[cancelBooking]'))->click();
	}

	/**
	 * Get success message.
	 * @return success message
	 */
	public function getSuccMessage() {
		return $this->webDriver->findElement(WebDriverBy::cssSelector('div.ilSuccessMessage'))->getText();
	}

}
