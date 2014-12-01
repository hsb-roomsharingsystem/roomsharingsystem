<?php

chdir("../../../../../../../../"); // necessary for the include paths that are used within the classes to be tested

/**
 * Class ilRoomsharingDatabaseTest
 *
 * @group unit
 */
class ilRoomsharingDatabaseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var ilRoomsharingDatabase
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new ilRoomsharingDatabase;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	/**
	 * @covers ilRoomsharingDatabase::setPoolId
	 * @todo   Implement testSetPoolId().
	 */
	public function testSetPoolId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAttributesForRooms
	 * @todo   Implement testGetAttributesForRooms().
	 */
	public function testGetAttributesForRooms()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getMaxSeatCount
	 * @todo   Implement testGetMaxSeatCount().
	 */
	public function testGetMaxSeatCount()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomIdsWithMatchingAttribute
	 * @todo   Implement testGetRoomIdsWithMatchingAttribute().
	 */
	public function testGetRoomIdsWithMatchingAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllRoomIds
	 * @todo   Implement testGetAllRoomIds().
	 */
	public function testGetAllRoomIds()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getMatchingRooms
	 * @todo   Implement testGetMatchingRooms().
	 */
	public function testGetMatchingRooms()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllAttributeNames
	 * @todo   Implement testGetAllAttributeNames().
	 */
	public function testGetAllAttributeNames()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getMaxCountForAttribute
	 * @todo   Implement testGetMaxCountForAttribute().
	 */
	public function testGetMaxCountForAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomName
	 * @todo   Implement testGetRoomName().
	 */
	public function testGetRoomName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomsBookedInDateTimeRange
	 * @todo   Implement testGetRoomsBookedInDateTimeRange().
	 */
	public function testGetRoomsBookedInDateTimeRange()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertBooking
	 * @todo   Implement testInsertBooking().
	 */
	public function testInsertBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertBookingParticipant
	 * @todo   Implement testInsertBookingParticipant().
	 */
	public function testInsertBookingParticipant()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertBookingAttributeAssign
	 * @todo   Implement testInsertBookingAttributeAssign().
	 */
	public function testInsertBookingAttributeAssign()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteCalendarEntryOfBooking
	 * @todo   Implement testDeleteCalendarEntryOfBooking().
	 */
	public function testDeleteCalendarEntryOfBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteCalendarEntriesOfBookings
	 * @todo   Implement testDeleteCalendarEntriesOfBookings().
	 */
	public function testDeleteCalendarEntriesOfBookings()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteBooking
	 * @todo   Implement testDeleteBooking().
	 */
	public function testDeleteBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteBookings
	 * @todo   Implement testDeleteBookings().
	 */
	public function testDeleteBookings()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllBookingIdsForSequence
	 * @todo   Implement testGetAllBookingIdsForSequence().
	 */
	public function testGetAllBookingIdsForSequence()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getSequenceAndUserForBooking
	 * @todo   Implement testGetSequenceAndUserForBooking().
	 */
	public function testGetSequenceAndUserForBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getBookingsForUser
	 * @todo   Implement testGetBookingsForUser().
	 */
	public function testGetBookingsForUser()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getParticipantsForBooking
	 * @todo   Implement testGetParticipantsForBooking().
	 */
	public function testGetParticipantsForBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getParticipantsForBookingShort
	 * @todo   Implement testGetParticipantsForBookingShort().
	 */
	public function testGetParticipantsForBookingShort()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAttributesForBooking
	 * @todo   Implement testGetAttributesForBooking().
	 */
	public function testGetAttributesForBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllBookingAttributes
	 * @todo   Implement testGetAllBookingAttributes().
	 */
	public function testGetAllBookingAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllFloorplans
	 * @todo   Implement testGetAllFloorplans().
	 */
	public function testGetAllFloorplans()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getFloorplan
	 * @todo   Implement testGetFloorplan().
	 */
	public function testGetFloorplan()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertFloorplan
	 * @todo   Implement testInsertFloorplan().
	 */
	public function testInsertFloorplan()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteFloorplan
	 * @todo   Implement testDeleteFloorplan().
	 */
	public function testDeleteFloorplan()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteFloorplanRoomAssociation
	 * @todo   Implement testDeleteFloorplanRoomAssociation().
	 */
	public function testDeleteFloorplanRoomAssociation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomsWithFloorplan
	 * @todo   Implement testGetRoomsWithFloorplan().
	 */
	public function testGetRoomsWithFloorplan()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteParticipation
	 * @todo   Implement testDeleteParticipation().
	 */
	public function testDeleteParticipation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteParticipations
	 * @todo   Implement testDeleteParticipations().
	 */
	public function testDeleteParticipations()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getParticipationsForUser
	 * @todo   Implement testGetParticipationsForUser().
	 */
	public function testGetParticipationsForUser()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getBooking
	 * @todo   Implement testGetBooking().
	 */
	public function testGetBooking()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getUserById
	 * @todo   Implement testGetUserById().
	 */
	public function testGetUserById()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getUserIdByUsername
	 * @todo   Implement testGetUserIdByUsername().
	 */
	public function testGetUserIdByUsername()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoom
	 * @todo   Implement testGetRoom().
	 */
	public function testGetRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomAttribute
	 * @todo   Implement testGetRoomAttribute().
	 */
	public function testGetRoomAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAttributesForRoom
	 * @todo   Implement testGetAttributesForRoom().
	 */
	public function testGetAttributesForRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getBookingsForRoom
	 * @todo   Implement testGetBookingsForRoom().
	 */
	public function testGetBookingsForRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getActualBookingsForRoom
	 * @todo   Implement testGetActualBookingsForRoom().
	 */
	public function testGetActualBookingsForRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteAttributesForRoom
	 * @todo   Implement testDeleteAttributesForRoom().
	 */
	public function testDeleteAttributesForRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertRoom
	 * @todo   Implement testInsertRoom().
	 */
	public function testInsertRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertAttributeForRoom
	 * @todo   Implement testInsertAttributeForRoom().
	 */
	public function testInsertAttributeForRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getRoomAgreementId
	 * @todo   Implement testGetRoomAgreementId().
	 */
	public function testGetRoomAgreementId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getCalendarId
	 * @todo   Implement testGetCalendarId().
	 */
	public function testGetCalendarId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::setCalendarId
	 * @todo   Implement testSetCalendarId().
	 */
	public function testSetCalendarId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::updateRoomProperties
	 * @todo   Implement testUpdateRoomProperties().
	 */
	public function testUpdateRoomProperties()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getClasses
	 * @todo   Implement testGetClasses().
	 */
	public function testGetClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getClassById
	 * @todo   Implement testGetClassById().
	 */
	public function testGetClassById()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getPrivilegesOfClass
	 * @todo   Implement testGetPrivilegesOfClass().
	 */
	public function testGetPrivilegesOfClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::setLockedClasses
	 * @todo   Implement testSetLockedClasses().
	 */
	public function testSetLockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAssignedClassesForUser
	 * @todo   Implement testGetAssignedClassesForUser().
	 */
	public function testGetAssignedClassesForUser()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getLockedClasses
	 * @todo   Implement testGetLockedClasses().
	 */
	public function testGetLockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getUnlockedClasses
	 * @todo   Implement testGetUnlockedClasses().
	 */
	public function testGetUnlockedClasses()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getPriorityOfClass
	 * @todo   Implement testGetPriorityOfClass().
	 */
	public function testGetPriorityOfClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::setPrivilegesForClass
	 * @todo   Implement testSetPrivilegesForClass().
	 */
	public function testSetPrivilegesForClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertClass
	 * @todo   Implement testInsertClass().
	 */
	public function testInsertClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::updateClass
	 * @todo   Implement testUpdateClass().
	 */
	public function testUpdateClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::assignUserToClass
	 * @todo   Implement testAssignUserToClass().
	 */
	public function testAssignUserToClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getUsersForClass
	 * @todo   Implement testGetUsersForClass().
	 */
	public function testGetUsersForClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deassignUserFromClass
	 * @todo   Implement testDeassignUserFromClass().
	 */
	public function testDeassignUserFromClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::clearUsersInClass
	 * @todo   Implement testClearUsersInClass().
	 */
	public function testClearUsersInClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteClassPrivileges
	 * @todo   Implement testDeleteClassPrivileges().
	 */
	public function testDeleteClassPrivileges()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteClass
	 * @todo   Implement testDeleteClass().
	 */
	public function testDeleteClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::isUserInClass
	 * @todo   Implement testIsUserInClass().
	 */
	public function testIsUserInClass()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getUserPriority
	 * @todo   Implement testGetUserPriority().
	 */
	public function testGetUserPriority()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::getAllRoomAttributes
	 * @todo   Implement testGetAllRoomAttributes().
	 */
	public function testGetAllRoomAttributes()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteRoomAttribute
	 * @todo   Implement testDeleteRoomAttribute().
	 */
	public function testDeleteRoomAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertRoomAttribute
	 * @todo   Implement testInsertRoomAttribute().
	 */
	public function testInsertRoomAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteAttributeRoomAssign
	 * @todo   Implement testDeleteAttributeRoomAssign().
	 */
	public function testDeleteAttributeRoomAssign()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteAttributeBookingAssign
	 * @todo   Implement testDeleteAttributeBookingAssign().
	 */
	public function testDeleteAttributeBookingAssign()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteBookingAttribute
	 * @todo   Implement testDeleteBookingAttribute().
	 */
	public function testDeleteBookingAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteBookingsUsesRoom
	 * @todo   Implement testDeleteBookingsUsesRoom().
	 */
	public function testDeleteBookingsUsesRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::deleteRoom
	 * @todo   Implement testDeleteRoom().
	 */
	public function testDeleteRoom()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::insertBookingAttribute
	 * @todo   Implement testInsertBookingAttribute().
	 */
	public function testInsertBookingAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::renameRoomAttribute
	 * @todo   Implement testRenameRoomAttribute().
	 */
	public function testRenameRoomAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers ilRoomsharingDatabase::renameBookingAttribute
	 * @todo   Implement testRenameBookingAttribute().
	 */
	public function testRenameBookingAttribute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
