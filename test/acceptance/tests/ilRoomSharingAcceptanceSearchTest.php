<?php

include_once './acceptance/php-webdriver/__init__.php';

/**
 * Example of an PHPUnit-Test with Selenium. Produces simple search test.
 * Make sure you started selenium-server-standalone-2.43.1.jar and have firefox browser on the host.

 * @author Thomas Matern <tmatern@stud.hs-bremen.de>
 * @group acceptance
 * @property WebDriver $webDriver
 */
class ilRoomSharingAcceptanceSearchTest extends PHPUnit_Framework_TestCase
{
	private static $webDriver;

	public static function setUpBeforeClass()
	{
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::firefox();
		self::$webDriver = RemoteWebDriver::create($host, $capabilities, 5000);
	}

	/**
	 * Test for simple search in google.
	 *
	 * @test
	 */
	public function testExample()
	{
		self::$webDriver->get('http://google.de/');

		// Find search field and search.
		$searchField = self::$webDriver->findElement(WebDriverBy::name('q'));
		$searchField->sendKeys("Hochschule Bremen")->submit();

		// Wait until the page with search results is loaded.
		self::$webDriver->wait(10, 500)->until(
			WebDriverExpectedCondition::presenceOfElementLocated(
				WebDriverBy::partialLinkText('Hochschule Bremen')
			)
		);

		// Find first search result containing search expression and click on it.
		$firstMatch = self::$webDriver->findElement(WebDriverBy::partialLinkText('Hochschule Bremen'));
		$firstMatch->click();

		// Check whether we are on the right page or not.
		$this->assertContains("hs-bremen.de", self::$webDriver->getCurrentURL());
	}

	/**
	 * Test for simple navigation.
	 *
	 * @test
	 */
	public function testExampleAfterHomepage()
	{
		// Wait until the page is loaded.
		self::$webDriver->wait(10, 500)->until(
			WebDriverExpectedCondition::presenceOfElementLocated(
				WebDriverBy::id('navtop2')
			)
		);

		// Find search field and search.
		$navElement = self::$webDriver->findElement(WebDriverBy::id('navtop2'));

		$navElement->click();

		// Check whether we are on the right page or not.
		$this->assertContains("studium", self::$webDriver->getCurrentURL());
	}

	/**
	 * Closes web browser.
	 */
	public static function tearDownAfterClass()
	{
		self::$webDriver->quit();
	}

}
