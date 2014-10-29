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
	private $webDriver;

	protected function setUp()
	{
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::firefox();
		$this->webDriver = RemoteWebDriver::create($host, $capabilities, 5000);
	}

	/**
	 * Test for simple search in google.
	 *
	 * @test
	 */
	public function testExample()
	{
		$this->webDriver->get('http://google.de/');

		// Find search field and search.
		$searchField = $this->webDriver->findElement(WebDriverBy::name('q'));
		$searchField->sendKeys("Hochschule Bremen")->submit();

		// Wait until the page with search results is loaded.
		$this->webDriver->wait(10, 500)->until(
			WebDriverExpectedCondition::presenceOfElementLocated(
				WebDriverBy::partialLinkText('Hochschule Bremen')
			)
		);

		// Find first search result containing search expression and click on it.
		$firstMatch = $this->webDriver->findElement(WebDriverBy::partialLinkText('Hochschule Bremen'));
		$firstMatch->click();

		// Check whether we are on the right page or not.
		$this->assertContains("Hochschule", $this->webDriver->getTitle());
	}

	/**
	 * Closes web browser.
	 */
	protected function tearDown()
	{
		$this->webDriver->quit();
	}

}
