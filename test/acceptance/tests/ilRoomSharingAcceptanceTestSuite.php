<?php

/**
 * Acceptance selenium test suite for ilRoomSharing-Plugin.
 * Make sure you started selenium-server-standalone-2.43.1.jar and have firefox browser on the host.
 *
 * @author tmatern
 *
 * @property WebDriver $driver
 */
class ilRoomSharingAcceptanceTestSuite extends PHPUnit_Framework_TestSuite
{
	private $driver;

	/**
	 * Sets up the web driver for selenium tests and makes it globaly accessable.
	 */
	protected function setUp()
	{
		/* @var $webDriver WebDriver */
		global $webDriver;

		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::firefox();
		$webDriver = RemoteWebDriver::create($host, $capabilities, 5000);

		$this->driver = $webDriver;
	}

	/**
	 * Builds an suite with tests.
	 *
	 * @return \self
	 */
	public static function suite()
	{
		$suite = new self("acceptanceTestSuite");

		// To execute all tests use, as shown, addTestSuite-Method.
		include_once 'ilRoomSharingAcceptanceSearchTest.php';
		$suite->addTestSuite('ilRoomSharingAcceptanceSearchTest');

		return $suite;
	}

	/**
	 * Closes web browser.
	 */
	protected function tearDown()
	{
		$this->driver->quit();
	}

}

?>