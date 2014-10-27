<?php

/**
 * This class represents the gui-testing for the RoomSharing System
 * 

 * @author twolscht
 *
 * @property WebDriver $driver
 */
class ilRoomSharingAcceptanceGUITest extends PHPUnit_Framework_TestCase {
    
    protected $webDriver;
	protected $url = 'http://localhost/roomsharingsystem';	// URL to local RoomSharing
	protected $rss = 'MeinRoomsharingPool';	// name of RoomSharing pool
	protected $login_user = 'root';
	protected $login_pass = 'homer';

	protected function setUp()
	{
		global $webDriver;
		$this->webDriver = $webDriver;
		$this->webDriver->manage()->timeouts()->implicitlyWait(3); // implicitly wait time => 3 sec.
		$this->webDriver->manage()->window()->maximize();		   // maxize browser window
	}
	    

	/**
	 * GUI tests for 'User Story 4 - Suche als Student - Variante 1'
	 * 
	 * @test
	 */
   public function testUserStory4()
    {
        $this->webDriver->get($this->url);	// go to RoomSharing System
		$this->login($this->login_user, $this->login_pass);		// login
		$this->toRSS();
		/**
		 *  TC#2
		 *	Search for room "123". Verify result.
		 */
		$this->searchForRoomByName("123");
		$this->assertEquals("1", $this->getNoOfResults());
		if($this->getNoOfResults() == 1){
			$this->assertEquals("123",$this->getFirstResult());
		}
		/**
		 *  TC#3
		 *	Search for room "032a". Verify result.
		 */
		$this->searchForRoomByName("032a");
		if($this->getNoOfResults() == 1){
			$this->assertEquals("032A",$this->getFirstResult());
		}
		/**
		 *  TC#4
		 *	Search for room "\';SELECT * FROM usr;--". Verify result.
		 *	Test SQL injection.
		 */
		$this->searchForRoomByName("\';SELECT * FROM usr;--");
		$this->assertEquals("0", $this->getNoOfResults());
		/**
		 *  TC#5
		 *	Search for room "123" with date (today).
		 */
		$this->searchForRoomByAll("123", "", date("d"), $this->getCurrentMonth(), date("Y"), date("H"), (date("i")+(60*5)), "23", "55", "", "", "", "");
		if($this->getNoOfResults() == 1){
			$this->assertEquals("123",$this->getFirstResult());
		}
		/**
		 *  TC#6
		 *	
		 */
		$this->searchForRoomByAll("123", "", date("d") - 1, $this->getCurrentMonth(), date("Y"), "00", "00", date("H"), date("i"), "", "", "", "");
		// TC#7 ==> Buchen in Vergangenheit funktioniert
		// TC#8 ==> evtl. nur mit erweiterer Suche möglich
		// TC#9 ==> TC nicht mehr gültig, da Buchen nun möglich ist
		$this->webDriver->quit();
   }  
   
		/**
		 * GUI tests for 'User Story 11 - Buchung als Student'
		 * @test
		 * 
		 * TODO:  This is still under construcion..
		 */
   /**public function testUserStory11(){
        $this->webDriver->get($this->url);
		$this->login($this->login_user, $this->login_pass);		// login
		$this->toRSS();
		// TC#1
		//$this->webDriver->findElement(WebDriverBy::linkText('Termine'))->click();
		//$this->webDriver->findElement(WebDriverBy::linkText('Buchung hinzufügen'))->click();
		// TC#2
		/**$this->searchForRoomByAll("", "10", date("d"), $this->getCurrentMonth(), date("Y"), "15", "00", "16", "30", "", "", "", "");
		if($this->getNoOfResults() >= 1){
			$this->webDriver->findElement(WebDriverBy::linkText('Buchen'))->click();
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
			$this->webDriver->findElement(WebDriverBy::linkText('Buchen'))->click();
			$this->doABooking("Lerngruppe Mathe2", "", "", "", date("d"), $this->getCurrentMonth(), date("Y"), date("H"), date("i"), date("d"), $this->getCurrentMonth(), date("Y"), date("i"), date("d") + (60*5), true);
		}
		//$this->webDriver->quit();
	}
	*/
	
   /**
    * GUI tests for floorplans.
    * 
    * These tests are not the ones from "User Story (Gebäudeplan)" because
    * the tests from user story use functionality which is not yet implemented.
	* @test	  
    */
   public function testGebaeudePlaeneExplorativ(){
		$this->webDriver->get($this->url);						// go to RoomSharing System
		$this->login($this->login_user, $this->login_pass);		// login
		$this->toRSS();											// navigate to RoomSharing Pool
		$this->webDriver->findElement(WebDriverBy::linkText('Gebäudeplan'))->click();
		/**
		 *  Check editing of an existing floorplan
		 */
		if($this->getNoOfResults() >= 1){
			$desc = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[3]"))->getText();
			$menu = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[4]"));
			$menu->findElement(WebDriverBy::cssSelector("#ilAdvSelListAnchorElement_263 > a > img"))->click();
			$menu->findElement(WebDriverBy::linkText('Bearbeiten'))->click();
			$desc1 = $this->webDriver->findElement(WebDriverBy::id("description"));
			$desc1->clear();
			$desc1->sendKeys($desc . "test");
			$this->webDriver->findElement(WebDriverBy::name("cmd[update]"))->click();
			$succ_mess = $this->webDriver->findElement(WebDriverBy::cssSelector("div.ilSuccessMessage"))->getText();
			$new_desc = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='il_center_col']/div[4]/table/tbody/tr[2]/td[3]"))->getText();
			// assert that editing was successful
			$this->assertEquals("Gebäudeplan erfolgreich aktualisiert",$succ_mess);
			// assert that new description is correct
			$this->assertEquals($desc . "test", $new_desc);
		}
		// check adding floorplan with insufficient informations
		$this->webDriver->findElement(WebDriverBy::linkText(" Gebäudeplan hinzufügen "))->click();
		$this->webDriver->findElement(WebDriverBy::id("title"))->sendKeys("Mein Titel");
		$this->webDriver->findElement(WebDriverBy::id("description"))->sendKeys("Meine Beschreibung");
		$this->webDriver->findElement(WebDriverBy::name("cmd[save]"))->click();
		$error_mess = $this->webDriver->findElement(WebDriverBy::cssSelector("div.ilFailureMessage"))->getText();
		// assert error message
		$this->assertEquals("Einige Angaben sind unvollständig oder ungültig. Bitte korrigieren Sie Ihre Eingabe.", $error_mess);
		$this->webDriver->quit();
   }
   
   //public function testUserStory5(){
		// TC#1 -> Benutzbarkeit nicht testbar
		// TC#2 -> Suchen nach Zeitbereich nicht implementiert
		// TC#3 -> Suchen nach Zeitbereich nicht implementiert
		// TC#4 -> Suchen nach Zeitbereich nicht implementiert
		// TC#5 -> Suchen nach Zeitbereich nicht implementiert
		// TC#6 -> Suchen nach Zeitbereich nicht implementiert
   //}
   
   /**
    * Search for room by room name.
    * @param type $roomName Room name
    */
   private function searchForRoomByName($roomName){
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
	private function searchForRoomByAll($roomName, $seats, $day, $month, $year, $h_from, $m_from, $h_to, $m_to, $beamer, $sound, $proj, $white){
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
	private function doABooking($subject, $mod, $cour, $sem, $f_d, $f_m, $f_y, $f_h, $f_m, $t_d, $t_m, $t_y, $t_h, $t_m, $acc){
		$this->webDriver->findElement(WebDriverBy::id('subject'))->sendKeys($subject);
		$this->webDriver->findElement(WebDriverBy::id('1'))->sendKeys($mod);
		$this->webDriver->findElement(WebDriverBy::id('2'))->sendKeys($cour);
		$this->webDriver->findElement(WebDriverBy::id('3'))->sendKeys($sem);
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
		if($acc == true){
			$this->webDriver->findElement(WebDriverBy::id('accept_room_rules'))->click();
		}
	}

	private function getCurrentMonth(){
		$monate = array(1=>"Januar",
                2=>"Februar",
                3=>"M&auml;rz",
                4=>"April",
                5=>"Mai",
                6=>"Juni",
                7=>"Juli",
                8=>"August",
                9=>"September",
                10=>"Oktober",
                11=>"November",
                12=>"Dezember");
		$monat = date("n");
		return $monate[$monat];
	}
	
	private function login($user, $pass){
		$this->webDriver->findElement(WebDriverBy::id('username'))->sendKeys($user);
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys($pass)->submit();
		$this->webDriver->findElement(WebDriverBy::id('mm_rep_tr'))->click();
	}
	
	private function toRSS(){
		$this->webDriver->findElement(WebDriverBy::linkText('Magazin - Einstiegsseite'))->click();
		$this->webDriver->findElement(WebDriverBy::xpath("(//a[contains(text(),'" . $this->rss ."')])[2]"))->click();
		$this->assertContains($this->rss, $this->webDriver->getTitle());
		
	}
	
	private function getCurrentDay(){
	return date("d");
	}
	
	private function getCurrentYear(){
	return date("y");
	}
	
	private function getNoOfResults() {
	try{
		$result = $this->webDriver->findElement(WebDriverBy::cssSelector('span.ilTableFootLight'))->getText();
		return substr($result, strripos($result, " ")+1, -1);	
	}
	catch (WebDriverException $exception) {
		return 0;
		}
	}
	
	private function getFirstResult() {
	return $this->webDriver->findElement(WebDriverBy::cssSelector('td.std'))->getText();
	}
	
}
?>