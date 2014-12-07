<?php

/**
 * This class holds different methods to help creating readable Selenium tests.
 *
 * @author Thomas Wolscht
 * @author Dan Sörgel
 */
class ilRoomSharingAcceptanceSeleniumHelper
{
	private $webDriver;
	private $rssObjectName;

	public function __construct($driver, $rss)
	{
		$this->webDriver = $driver;
		$this->rssObjectName = $rss;
	}

	/**
	 * Search for room by room name.
	 * @param string $roomName Room name
	 */
	public function searchForRoomByName($roomName)
	{
		$this->webDriver->findElement(WebDriverBy::linkText('Suche'))->click();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);
		$this->webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
	}

	public function createPrivilegClass($className, $classComment = "", $roleAssign = "",
		$priority = "", $copyFrom = "Kein")
	{
		//Navigation
		$this->webDriver->findElement(WebDriverBy::linkText('Privilegien'))->click();
		$this->webDriver->findElement(WebDriverBy::linkText(' Neue Klasse anlegen '))->click();
		//Data
		$this->webDriver->findElement(WebDriverBy::id('name'))->sendKeys($className);
		$this->webDriver->findElement(WebDriverBy::id('description'))->sendKeys($classComment);
		if (!empty($roleAssign))
		{
			$this->webDriver->findElement(WebDriverBy::id('role_assignment'))->sendKeys($roleAssign);
		}
		if (!empty($priority))
		{
			$this->webDriver->findElement(WebDriverBy::id('priority'))->sendKeys($priority);
		}
		try
		{
			$this->webDriver->findElement(WebDriverBy::xpath("//label[text()='" . $copyFrom . "']"))->click();
		}
		catch (Exception $unused)
		{
			//The CopyFrom does not appear if there is no class yet
		}

		//Submit
		$this->webDriver->findElement(WebDriverBy::name('cmd[addClass]'))->click();
	}

	public function deletePrivilegClass($classNameWithRole)
	{
		//Navigation
		$this->webDriver->findElement(WebDriverBy::linkText('Privilegien'))->click();
		$this->webDriver->findElement(WebDriverBy::linkText($classNameWithRole))->click();
		$this->webDriver->findElement(WebDriverBy::linkText(' Klasse löschen '))->click();
		$this->webDriver->findElement(WebDriverBy::name('cmd[deleteClass]'))->click();
	}

	/**
	 * Search for room by all possible informations.
	 * @param string $roomName			Room name
	 * @param int $seats				Amount of seats
	 * @param int $day					Day of booking
	 * @param int $month				Month of booking
	 * @param int $year		    		Year of booking
	 * @param int $h_from				Hour (from)
	 * @param int $m_from				Minutes (from)
	 * @param int $h_to					Hour (to)
	 * @param int $m_to					Minutes (to)
	 * @param array $room_attributes	roomattributes as [name of attribute] => [amount]
	 */
	public function searchForRoomByAll($roomName, $seats, $day, $month, $year, $h_from, $m_from, $h_to,
		$m_to, array $room_attributes)
	{
		$this->webDriver->findElement(WebDriverBy::linkText('Suche'))->click();

		$this->webDriver->findElement(WebDriverBy::id('room_name'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('room_name'))->sendKeys($roomName);

		$this->webDriver->findElement(WebDriverBy::id('room_seats'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('room_seats'))->sendKeys($seats);

		$this->webDriver->findElement(WebDriverBy::id('date[date]_d'))->sendKeys($day);
		$this->webDriver->findElement(WebDriverBy::id('date[date]_m'))->sendKeys($month);
		$this->webDriver->findElement(WebDriverBy::id('date[date]_y'))->sendKeys($year);
		$this->webDriver->findElement(WebDriverBy::id('time_from[time]_h'))->sendKeys($h_from);
		$this->webDriver->findElement(WebDriverBy::id('time_from[time]_m'))->sendKeys($m_from);
		$this->webDriver->findElement(WebDriverBy::id('time_to[time]_h'))->sendKeys($h_to);
		$this->webDriver->findElement(WebDriverBy::id('time_to[time]_m'))->sendKeys($m_to);

		foreach ($room_attributes as $name => $amount)
		{
			$this->webDriver->findElement(WebDriverBy::id('attribute_' . $name . '_amount'))->clear();
			$this->webDriver->findElement(WebDriverBy::id('attribute_' . $name . '_amount'))->sendKeys($amount);
		}
		$this->webDriver->findElement(WebDriverBy::name('cmd[applySearch]'))->click();
	}

	public function getPrivilegeClassIDByName($name)
	{
		$link_taget = $this->webDriver->findElement(WebDriverBy::linkText($name))->getAttribute('href');
		$link_taget_A_vars = explode("&", $link_taget);
		foreach ($link_taget_A_vars as $var)
		{
			if (substr($var, 0, 9) === "class_id=")
			{
				$keyAndValue = explode("=", $var);
				return $keyAndValue[1];
			}
		}
	}

	public function changeAndCheckPrivilegeChange($priv_name, $class_id)
	{
		$el = $this->webDriver->findElement(WebDriverBy::name('priv[' . $class_id . '][' . $priv_name . ']'));
		$checked = $el->getAttribute('checked');
		$el->click();
		$this->webDriver->findElement(WebDriverBy::name('cmd[savePrivilegeSettings]'))->click();
		$el_saved = $this->webDriver->findElement(WebDriverBy::name('priv[' . $class_id . '][' . $priv_name . ']'));
		$checked_saved = $el_saved->getAttribute('checked');
		return (empty($checked) && !empty($checked_saved)) || (!empty($checked) && empty($checked_saved));
	}

	/**
	 * Get current Month
	 * @return string current month
	 */
	public function getCurrentMonth()
	{
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
	 * @param string $user User
	 * @param string $pass Password
	 */
	public function login($user, $pass)
	{
		$this->webDriver->findElement(WebDriverBy::id('username'))->sendKeys($user);
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys($pass)->submit();
		$this->webDriver->findElement(WebDriverBy::id('mm_rep_tr'))->click();
	}

	/**
	 * Navigate to RoomSharing Pool
	 */
	public function toRSS()
	{
		$this->webDriver->findElement(WebDriverBy::linkText('Magazin - Einstiegsseite'))->click();
		$this->webDriver->findElement(WebDriverBy::xpath("(//a[contains(text(),'" . $this->rssObjectName . "')])[2]"))->click();
		//$this->assertContains(self::$rssObjectName, $this->webDriver->getTitle());
	}

	/**
	 * Get current day
	 * @return string day
	 */
	public function getCurrentDay()
	{
		return date("d");
	}

	/**
	 * Get current year
	 * @return string year
	 */
	public function getCurrentYear()
	{
		return date("y");
	}

	/**
	 * Get amount of search results.
	 * @return string search results
	 */
	public function getNoOfResults()
	{
		try
		{
			$result = $this->webDriver->findElement(WebDriverBy::cssSelector('span.ilTableFootLight'))->getText();
			return substr($result, strripos($result, " ") + 1, -1);
		}
		catch (WebDriverException $exception)
		{
			return 0;
		}
	}

	/**
	 * Get first result of search.
	 * @return first result
	 */
	public function getFirstResult()
	{
		return $this->webDriver->findElement(WebDriverBy::cssSelector('td.std'))->getText();
	}

	/**
	 * Get error message.
	 * @return error message
	 */
	public function getErrMessage()
	{
		return $this->webDriver->findElement(WebDriverBy::cssSelector('div.ilFailureMessage'))->getText();
	}

	/**
	 * This method creates a booking.
	 * @param string $subject	Subject
	 * @param type $f_day		From day
	 * @param type $f_month		From Month
	 * @param type $f_year		From Year
	 * @param type $f_hour		From Hour
	 * @param type $f_minute	From Minute
	 * @param type $t_day		To Day
	 * @param type $t_month		To Month
	 * @param type $t_year		To Year
	 * @param type $t_hour		To Hour
	 * @param type $t_minute	To Minute
	 * @param bool $acc			Tick "Accept room using agreement" (Agreement must be there)
	 * @param string $comment	Comment
	 * @param bool $public		Tick "Booking is public"
	 * @param array $participants List of Participants (Must be User Names)
	 */
	public function doABooking($subject, $f_day, $f_month, $f_year, $f_hour, $f_minute, $t_day,
		$t_month, $t_year, $t_hour, $t_minute, $acc, $comment = "", $public = false,
		array $participants = array())
	{
		$this->webDriver->findElement(WebDriverBy::id('subject'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('subject'))->sendKeys($subject);

		$this->webDriver->findElement(WebDriverBy::id('comment'))->clear();
		$this->webDriver->findElement(WebDriverBy::id('comment'))->sendKeys($comment);

		$this->webDriver->findElement(WebDriverBy::id('from[date]_d'))->sendKeys($f_day);
		$this->webDriver->findElement(WebDriverBy::id('from[date]_m'))->sendKeys($f_month);
		$this->webDriver->findElement(WebDriverBy::id('from[date]_y'))->sendKeys($f_year);
		$this->webDriver->findElement(WebDriverBy::id('from[time]_h'))->sendKeys($f_hour);
		$this->webDriver->findElement(WebDriverBy::id('from[time]_m'))->sendKeys($f_minute);

		$this->webDriver->findElement(WebDriverBy::id('to[date]_d'))->sendKeys($t_day);
		$this->webDriver->findElement(WebDriverBy::id('to[date]_m'))->sendKeys($t_month);
		$this->webDriver->findElement(WebDriverBy::id('to[date]_y'))->sendKeys($t_year);
		$this->webDriver->findElement(WebDriverBy::id('to[time]_h'))->sendKeys($t_hour);
		$this->webDriver->findElement(WebDriverBy::id('to[time]_m'))->sendKeys($t_minute);
		if ($acc == true)
		{
			$this->webDriver->findElement(WebDriverBy::id('accept_room_rules'))->click();
		}
		if ($public == true)
		{
			$this->webDriver->findElement(WebDriverBy::id('book_public'))->click();
		}
		foreach ($participants as $num => $participant)
		{
			$this->webDriver->findElement(WebDriverBy::id('ilMultiAdd~participants~0'))->click();
			$this->webDriver->findElement(WebDriverBy::id('participants~' . $num))->sendKeys($participant);
		}
		$this->webDriver->findElement(WebDriverBy::name('cmd[book]'))->click();
	}

	/**
	 * Delete a booking by subject.
	 * @param booking subject
	 */
	public function deleteBooking($subject)
	{
		$row = $this->webDriver->findElement(WebDriverBy::xpath("//tr[contains(text(), " . $subject . ")]/td[7]"));
		$row->findElement(WebDriverBy::linkText('Stornieren'))->click();
		$this->webDriver->findElement(WebDriverBy::name('cmd[cancelBooking]'))->click();
	}

	/**
	 * Get success message.
	 * @return success message
	 */
	public function getSuccMessage()
	{
		return $this->webDriver->findElement(WebDriverBy::cssSelector('div.ilSuccessMessage'))->getText();
	}

}
