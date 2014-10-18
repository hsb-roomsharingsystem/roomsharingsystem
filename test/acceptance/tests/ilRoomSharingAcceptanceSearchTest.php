<?php

include_once './acceptance/php-webdriver/__init__.php';

/**
 * Example of an PHPUnit-Test with Selenium. Produces simple search test.
 *
 * @author tmatern
 *
 * @property WebDriver $driver
 */
class ilRoomSharingAcceptanceSearchTest extends PHPUnit_Framework_TestCase
{
	private $driver;

	protected function setUp()
	{
		/* @var $webDriver WebDriver */
		global $webDriver;
		$this->driver = $webDriver;
	}

	/**
	 * Test for simple search in google.
	 *
	 * @test
	 */
	public function testExample()
	{
		$this->driver->get('http://google.de/');

		// Find search field and search.
		$searchField = $this->driver->findElement(WebDriverBy::name('q'));
		$searchField->sendKeys("Hochschule Bremen")->submit();

		// Wait until the page with search results is loaded.
		$this->driver->wait(10, 500)->until(
			WebDriverExpectedCondition::presenceOfElementLocated(
				WebDriverBy::partialLinkText('Hochschule Bremen')
			)
		);

		// Find first search result containing search expression and click on it.
		$firstMatch = $this->driver->findElement(WebDriverBy::partialLinkText('Hochschule Bremen'));
		$firstMatch->click();

		// Check whether we are on the right page or not.
		$this->assertContains("Hochschule", $this->driver->getTitle());
	}

}
