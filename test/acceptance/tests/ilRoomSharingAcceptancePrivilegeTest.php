<?php

include_once './acceptance/php-webdriver/__init__.php';
include_once './acceptance/tests/ilRoomSharingAcceptanceSeleniumHelper.php';

/**
 * Description of ilRoomSharingAcceptanceBookingFilterTest
 * @group selenium-privileges
 * @property WebDriver $webDriver
 * @author Albert Koch
 */
class ilRoomSharingAcceptancePrivilegeTest extends PHPUnit_Framework_TestCase
{
private static $new_user_gender = 'm';
private static $new_user_first_name = 'karl';
private static $new_user_last_name = 'auer'; //In German that's kind of funny
private static $new_user_login = 'kauer';
private static $new_user_pw = 'karl123';
private static $new_user_initial_pw = 'karl321';
private static $new_user_email = 'karl@auer.de';
private static $classname = 'Users';

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
self::$helper->login(self::$login_user, self::$login_pass);  // login
self::$helper->toRSS();
self::createTestData();
self::$helper->logout();
}

public static function createTestData()
{

self::$webDriver->findElement(WebDriverBy::partialLinkText('Rechte'))->click();
self::$webDriver->findElement(WebDriverBy::id('select_4_nocreation'))->click();
self::$webDriver->findElement(WebDriverBy::name('cmd[savePermissions]'))->click();
self::$helper->createNewUser(self::$new_user_login, self::$new_user_initial_pw,
 self::$new_user_gender, self::$new_user_first_name, self::$new_user_last_name,
 self::$new_user_email);
}

public function setUp()
{
self::$helper->login(self::$login_user, self::$login_pass);  // login
self::$helper->toRSS();
}

/*
 * Tests the application of the Privileges considering the Appointments
 * @test
 */
public function appointmentsTest()
{
//Erstmal mit gar nix
//Dann mit Termine aufrufen, Attribute administrieren
//Dann mit Suche aufrufen und nicht öffentliche einsehen
//Dann mit Erstellen, bearbeiten, stornieren
//Dann mit niedrigere Klassenpriorität stornieren
//Dann mit Serienbuchung, Zeitlich unbegrenzt, Benachrichtigungen einstellen
}

/*
 * Tests the application of the privileges considering the rooms
 * @test
 */
public function roomsTest()
{
//erstmal nix
//dann buchungen einsehen, attribute administrieren
//dann erstellen
//dann bearbeiten, löschen
//
	}

/*
 * Tests the application of the privileges considering the floorplans
 * @test
 */
public function floorplanTest()
{
//erstmal nix
//dann aufrufen
//dann erstellen
//dann bearbeiten und löschen
}

/*
 * Tests the application of the other privileges
 * @test
 */
public function otherPrivilegesTest()
{
//erstmal nix
//dann einstellungen aufrufen, privilegien aufrufen
//dann klasse anlegen
//dann klasse bearbeiten, löschen
//dann privilegien bearbeiten, privilegien sperren
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
}
