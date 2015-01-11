<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ilRoomSharingAcceptanceBookingFilterTest
 *
 * @author Albert Koch
 */
class ilRoomSharingAcceptanceBookingFilterTest
{
	private static $webDriver;
	private static $url = 'http://localhost/roomsharingsystem'; // URL to local RoomSharing
	private static $rssObjectName; // name of RoomSharing pool
	private static $login_user = 'root';
	private static $login_pass = 'homer';
	private static $helper;
	private static $new_user_gender = 'm';
	private static $new_user_first_name = 'karl';
	private static $new_user_last_name = 'auer'; //In German that's kind of funny
	private static $new_user_login = 'kauer';
	private static $new_user_pw = 'karl123';
	private static $new_user_email = 'karl@auer.de';
	private static $standard_roomname = 'Standard_Room';
	private static $differing_roomname = 'Differing_Room';
	private static $standard_subject = 'Standard Subjepct';
	private static $differing_subject = 'Differing Subject';
	private static $standard_comment = 'Standard Comment';
	private static $differing_comment = 'Differing Comment';

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
		//create a new user which will do a booking to test the "user"-Filter
		self::$helper->createNewUser(self::$new_user_login, self::$new_user_pw, self::$new_user_gender,
			self::$new_user_first_name, self::$new_user_last_name, self::$new_user_email);
		self::$helper->toRSS();
		$this->setUpPrivilegeClass(); //Grant the privileges necessary to book
		// Create Test Data
		self::$helper->createRoom(self::$standard_roomname, '1', '10');
		self::$helper->createRoom(self::$differing_roomname, '1', '10');
		self::$helper()->createBooking('01', 'Januar', date(Y) + 1, '12', '00', '13', '00',
			self::$standard_roomname, '', self::$standard_subject, 'true', self::$standard_comment);
		self::$helper()->createBooking('01', 'Januar', date(Y) + 1, '14', '00', '15', '00',
			self::$differing_roomname, '', self::$standard_subject, 'true', self::$standard_comment);
		self::$helper()->createBooking('01', 'Januar', date(Y) + 1, '16', '00', '17', '00',
			self::$standard_roomname, '', self::$differing_subject, 'true', self::$standard_comment);
		self::$helper()->createBooking('01', 'Januar', date(Y) + 1, '18', '00', '19', '00',
			self::$standard_roomname, '', self::$standard_subject, 'true', self::$differing_comment);
		self::$helper->logout();
		//new user will be asked to change his password at first login
		self::$helper->loginNewUserForFirstTime(self::$new_user_login, self::$new_user_pw,
			self::$new_user_pw);
		self::$helper->toRSS();
		self::$helper()->createBooking('01', 'Januar', date(Y) + 1, '20', '00', '21', '00',
			self::$standard_roomname, '', self::$standard_subject, 'true', self::$standard_comment);
	}

	private function setUpPrivilegeClass()
	{
		self::$helper->createPrivilegClass('Users', '', 'User');
		$class_id = getPrivilegeClassIDByName('Users');
		self::$helper->grantPrivilege('accessAppointments', $class_id);
		self::$helper->grantPrivilege('accessSearch', $class_id);
		self::$helper->grantPrivilege('addOwnBookings', $class_id);
		self::$helper->grantPrivilege('seeNonPublicBookingInformation', $class_id);
	}

	/**
	 * Tests the filter panel itself in booking filter
	 * @test
	 */
	public function testFilterPanel()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();

		#1: Hide Booking Filter
		self::$webDriver->findElement(WebDriverBy::linkText('Filter ausblenden'))->click();
		$this->assertEquals(false,
			self::$webDriver->findElement(WebDriverBy::name('cmd[applyFilter]'))->isDisplayed(),
			'#1 Hiding the Filter does not hide it');

		#2: Show Booking Filtere
		self::$webDriver->findElement(WebDriverBy::linkText('Filter anzeigen'))->click();
		$this->assertEquals(true,
			self::$webDriver->findElement(WebDriverBy::name('cmd[applyFilter]'))->isDisplayed(),
			'#2 Showing the Filter does not show it');

		#3 Apply and hide filter
		self::$webDriver->findElement(WebDriverBy::id('login'))->sendKeys(self::$login_user);
		self::$webDriver->findElement(WebDriverBy::name('cmd[applyRoomFilter]'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Filter ausblenden'))->click();
		self::$webDriver->findElement(WebDriverBy::linkText('Filter anzeigen'))->click();
		$this->assertEquals(self::$login_user,
			self::$webDriver->findElement(WebDriverBy::name('login'))->getAttribute('value'),
			'#3 Hiding and showing the RoomFilter resets it');

		#4 Reset filter
		self::$webDriver->findElement(WebDriverBy::name('cmd[resetFilter]'))->click();
		$this->assertEquals(true,
			empty(self::$webDriver->findElement(WebDriverBy::name('login'))->getAttribute('value')),
			'#4 Filter reseting does not work');
	}

	/*
	 * Tests the filter by username of booking person
	 * @test
	 */
	public function testUserName()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		#1: See the Bookings created by root user
		self::$helper->applyBookingFilter(self::$login_user);
		$this->assertEquals(4, self::$helper->getNoOfResults(),
			'#1 for Username in booking filter does not work');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className('ilInfoMessage'));
		}
		catch (Exception $ex)
		{
			$this->fail('#1 for username in booking filter does not work');
		}
		#2: See the bookings created by the user himself
		self::$helper->applyBookingFilter(self::$new_user_login);
		$this->assertEquals(4, self::$helper->getNoOfResults(),
			'#2 for sername in booking filter does not work');
		#3 Reset the filter
		self::$helper->applyBookingFilter('', '', '', '');
		$this->assertEquals(5, self::$helper->getNoOfResults(),
			'#3 for username - reset filter - does not work');
	}

	/*
	 * Tests the filter by the booked room
	 * @test
	 */
	public function testRoom()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		#1: See only the Booking of a differing room
		self::$helper->applyBookingFilter('', self::$differing_roomname);
		$this->assertEquals(1, self::$helper->getNoOfResults(),
			'#1 -1 for room in booking filter does not work');
		try
		{
			self::$webDriver->findElement(WebDriverBy::className('ilInfoMessage'));
		}
		catch (Exception $ex)
		{
			$this->fail('#1 for room in booking filter does not work');
		}
		#2: Reset the filter
		self::$helper->applyBookingFilter('', '', '', '');
		$this->assertEquals(5, self::$helper->getNoOfResults(),
			'#2 for room - reset filter - does not work');
	}

	/*
	 * Tests the filter by the subject of the booking
	 * @test
	 */
	public function testSubject()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		#1: See only the booking with a differing subject
		self::$helper->applyBookingFilter('', '', self::$differing_subject);
		$this->assertEquals(1, self::$helper->getNoOfResults(), '#1 for subject does not work');
		#2: Reset the filter
		self::$helper->applyBookingFilter('', '', '', '');
		$this->assertEquals(1, self::$helper->getNoOfResults(),
			'#2 for subject - reset filter - does not work');
	}

	/*
	 * Tests the filter by the comment given to the booking
	 * @test
	 */
	public function testComment()
	{
		self::$webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		#1: See only the booking with a differing comment
		self::$helper->applyBookingFilter('', '', '', self::$differing_comment);
		$this->assertEquals(1, self::$helper->getNoOfResults(), '#1 for comment does not work');
		#2: Reset the filter
		self::$helper->applyBookingFilter('', '', '', '');
		$this->assertEquals(1, self::$helper->getNoOfResults(),
			'#2 for comment - reset filter - does not work');
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
		self::$helper->logout();
	}

}
