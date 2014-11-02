<#1>
<?php
// This script creates the necessary tables for the "Roomsharing System" plugin
// Version 0.2
// author: T.Wolscht, T. Matern, T. Röhrig
// ##########################
// 'rep_robj_xrs_rattr'
// ##########################
$table_name = 'rep_robj_xrs_rattr';
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'name' => array(
		'type' => 'text',
		'length' => 45,
		'notnull' => true
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('pool_id'), 'i1');

// ##########################
// 'rep_robj_xrs_bookings'
// ##########################
$table_name = "rep_robj_xrs_bookings";
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'date_from' => array(
		'type' => 'timestamp',
		'notnull' => true
	),
	'date_to' => array(
		'type' => 'timestamp',
		'notnull' => true
	),
	'seq_id' => array(
		'type' => 'integer',
		'length' => 4,
		'default' => null
	),
	'room_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'subject' => array(
		'type' => 'text',
		'length' => 255
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('seq_id'), 'i1');
$ilDB->addIndex($table_name, array('room_id'), 'i2');
$ilDB->addIndex($table_name, array('pool_id'), 'i3');
$ilDB->addIndex($table_name, array('user_id'), 'i4');

// ##########################
// 'rep_robj_xrs_book_seqe'
// ##########################
$table_name = "rep_robj_xrs_book_seqe";
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'type' => array(
		'type' => 'text',
		'length' => 45
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('pool_id'), 'i1');

// ##########################
// 'rep_robj_xrs_book_user'
// ##########################
$table_name = "rep_robj_xrs_book_user";
$fields = array(
	'booking_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("booking_id", "user_id"));

// ##########################
// 'rep_robj_xrs_buildings'
// ##########################
$table_name = "rep_robj_xrs_buildings";
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'name' => array(
		'type' => 'text',
		'length' => 45
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('pool_id'), 'i1');

// ##########################
// 'rep_robj_xrs_pools'
// ##########################
$table_name = "rep_robj_xrs_pools";
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'short_description' => array(
		'type' => 'text',
		'length' => 1000
	),
	'pool_online' => array(
		'type' => 'integer',
		'length' => 1,
		'default' => 0
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);

// ##########################
// 'rep_robj_xrs_rooms'
// ##########################
$table_name = "rep_robj_xrs_rooms";
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'name' => array(
		'type' => 'text',
		'length' => 45,
		'notnull' => true
	),
	'type' => array(
		'type' => 'text',
		'length' => 45
	),
	'min_alloc' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 1
	),
	'max_alloc' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'file_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'building_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('max_alloc'), 'i1');
$ilDB->addIndex($table_name, array('pool_id'), 'i2');

// ##########################
// 'rep_robj_xrs_room_attr'
// ##########################
$table_name = "rep_robj_xrs_room_attr";
$fields = array(
	'room_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'att_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'count' => array(
		'type' => 'integer',
		'length' => 4
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("room_id", "att_id"));
?>

<#2>
<?php
// Add tables for variable attributes for bookings
// Author: R. Heimsoth
// ##########################
// 'rep_robj_xrs_battr'
// ##########################
$table_name = 'rep_robj_xrs_battr';
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'name' => array(
		'type' => 'text',
		'length' => 45,
		'notnull' => true
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("id"));
// add sequence
$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('pool_id'), 'i1');

// ##########################
// 'rep_robj_xrs_book_attr'
// ##########################
$table_name = "rep_robj_xrs_book_attr";
$fields = array(
	'booking_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'attr_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'value' => array(
		'type' => 'text',
		'length' => 250
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("booking_id"));
?>

<#3>
<?php
// Add tables for floor plans
// Author: T. Matern, T. Röhrig, T. Wolscht
// ##########################
// 'rep_robj_xrs_fplans'
// ##########################
$table_name = 'rep_robj_xrs_fplans';
$fields = array(
	'file_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'pool_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	)
);
//delete Table, if exists
if ($ilDB->tableExists($table_name))
{
	$ilDB->dropTable($table_name);
};
$ilDB->createTable($table_name, $fields);
// add primary key
$ilDB->addPrimaryKey($table_name, array("file_id"));
// add sequence
//$ilDB->createSequence($table_name);
// add index
$ilDB->addIndex($table_name, array('file_id'), 'i1');
?>

<#4>
<?php
// Mit diesem Skript wird ein Grunddatenstand für das "Roomsharing System" eingefügt.
// Version 0.1
// author: B.Hitzelberger
// Delete Anweisung um alle Tabellen zu leeren
$ilDB->manipulate("DELETE FROM rep_robj_xrs_pools");
$ilDB->manipulate("DELETE FROM rep_robj_xrs_buildings");
$ilDB->manipulate("DELETE FROM rep_robj_xrs_rattr");
$ilDB->manipulate("DELETE FROM rep_robj_xrs_rooms");
$ilDB->manipulate("DELETE FROM rep_robj_xrs_room_attr");

// Raumbuchungspool Einfügen und die ID in einer Variable abspeichern
$resultPoolID = $ilDB->nextId("rep_robj_xrs_pools");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_pools (id, short_description) VALUES (%s, %s)",
	array("integer", "text"), array($resultPoolID, "Raumbuchungssystem vom ZIMT"));

// Gebäude Einfügen und die ID in einer Variable abspeichern
$resultBuildingID = $ilDB->nextId("rep_robj_xrs_buildings");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_buildings (id, name, pool_id) VALUES (%s, %s, %s)",
	array("integer", "text", "integer"), array($resultBuildingID, "ZIMT", $resultPoolID));

// Raumattribut Beamer Einfügen und die ID in einer Variable abspeichern
$resultBeamerID = $ilDB->nextId("rep_robj_xrs_rattr");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_rattr (id, name, pool_id) VALUES (%s, %s, %s)",
	array("integer", "text", "integer"), array($resultBeamerID, "Beamer", $resultPoolID));

// Raumattribut Tageslichprojektor Einfügen und die ID in einer Variable abspeichern
$resultTageslichtprojektorID = $ilDB->nextId("rep_robj_xrs_rattr");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_rattr (id, name, pool_id) VALUES (%s, %s, %s)",
	array("integer", "text", "integer"),
	array($resultTageslichtprojektorID, "Tageslichprojektor", $resultPoolID));

// Raumattribut Whiteboard Einfügen und die ID in einer Variable abspeichern
$resultWhiteboardID = $ilDB->nextId("rep_robj_xrs_rattr");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_rattr (id, name, pool_id) VALUES (%s, %s, %s)",
	array("integer", "text", "integer"), array($resultWhiteboardID, "Whiteboard", $resultPoolID));

// Raumattribut Soundanlage Einfügen und die ID in einer Variable abspeichern
$resultSoundanlageID = $ilDB->nextId("rep_robj_xrs_rattr");
$ilDB->manipulateF("INSERT INTO rep_robj_xrs_rattr (id, name, pool_id) VALUES (%s, %s, %s)",
	array("integer", "text", "integer"), array($resultSoundanlageID, "Soundanlage", $resultPoolID));

// Statement für das Einfügen von Räumen erstellen
$statementRoom = $ilDB->prepareManip("INSERT INTO rep_robj_xrs_rooms (id, name, type, min_alloc, max_alloc, file_id, building_id, pool_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?)",
	array("integer", "text", "text", "integer", "integer", "integer", "integer", "integer"));

// Daten erfassen für das Statement
$resultRoomID012 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom = array();
$dataRoom[] = array($resultRoomID012, "012", "Hoersaal", 1, 120, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID032A = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID032A, "032A", "Plenum", 1, 56, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID032B = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID032B, "032B", "Plenum", 1, 56, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID032C = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID032C, "032C", "Plenum", 1, 56, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID037 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID037, "037", "Projektraum", 1, 12, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID106 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID106, "106", "Bibliothek", 1, 12, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID116 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID116, "116", "Seminar", 1, 40, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID117 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID117, "117", "Seminar", 1, 40, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID119 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID119, "119", "Seminar", 1, 40, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID122 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID122, "122", "Seminar", 1, 40, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID123 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID123, "123", "Seminar", 1, 40, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID126 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID126, "126", "Projektraum", 1, 6, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID132 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID132, "132", "Projektraum", 1, 6, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID213 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID213, "213", "Diplomantenraum", 1, 6, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID235 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID235, "235", "Vorbereitung", 1, 6, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID236 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID236, "236", "Studtenraum", 1, 10, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID245 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID245, "245", "Vorbereitung", 1, 6, NULL, $resultBuildingID, $resultPoolID);
$resultRoomID305 = $ilDB->nextId("rep_robj_xrs_rooms");
$dataRoom[] = array($resultRoomID305, "305", "Seminar", 1, 20, NULL, $resultBuildingID, $resultPoolID);

// Statement mit den Daten ausführen
$ilDB->executeMultiple($statementRoom, $dataRoom);

// Allgemeines Statement für das Hinzufügen von Räumen erstellen
$statementRoomAttributes = $ilDB->prepareManip("INSERT INTO rep_robj_xrs_room_attr (room_id, att_id, count) VALUES(?, ?, ?)",
	array("integer", "integer", "integer"));

$dataRoomAttributes012 = array(
	array($resultRoomID012, $resultBeamerID, 1),
	array($resultRoomID012, $resultTageslichtprojektorID, 1),
	array($resultRoomID012, $resultWhiteboardID, 1),
	array($resultRoomID012, $resultSoundanlageID, 1));

$dataRoomAttributes032A = array(
	array($resultRoomID032A, $resultBeamerID, 1),
	array($resultRoomID032A, $resultTageslichtprojektorID, 1),
	array($resultRoomID032A, $resultWhiteboardID, 1));

$dataRoomAttributes032B = array(
	array($resultRoomID032B, $resultBeamerID, 1),
	array($resultRoomID032B, $resultTageslichtprojektorID, 1),
	array($resultRoomID032B, $resultWhiteboardID, 1));

$dataRoomAttributes032C = array(
	array($resultRoomID032C, $resultBeamerID, 1),
	array($resultRoomID032C, $resultTageslichtprojektorID, 1),
	array($resultRoomID032C, $resultWhiteboardID, 1));

$dataRoomAttributes037 = array(
	array($resultRoomID037, $resultWhiteboardID, 1));

$dataRoomAttributes106 = array(
	array($resultRoomID106, $resultWhiteboardID, 1));

$dataRoomAttributes116 = array(
	array($resultRoomID116, $resultBeamerID, 1),
	array($resultRoomID116, $resultTageslichtprojektorID, 1),
	array($resultRoomID116, $resultWhiteboardID, 1));

$dataRoomAttributes117 = array(
	array($resultRoomID117, $resultBeamerID, 1),
	array($resultRoomID117, $resultTageslichtprojektorID, 1),
	array($resultRoomID117, $resultWhiteboardID, 1));

$dataRoomAttributes119 = array(
	array($resultRoomID119, $resultBeamerID, 1),
	array($resultRoomID119, $resultTageslichtprojektorID, 1),
	array($resultRoomID119, $resultWhiteboardID, 1));

$dataRoomAttributes122 = array(
	array($resultRoomID122, $resultBeamerID, 1),
	array($resultRoomID122, $resultTageslichtprojektorID, 1),
	array($resultRoomID122, $resultWhiteboardID, 1));

$dataRoomAttributes123 = array(
	array($resultRoomID123, $resultBeamerID, 1),
	array($resultRoomID123, $resultTageslichtprojektorID, 1),
	array($resultRoomID123, $resultWhiteboardID, 1));

$dataRoomAttributes213 = array(
	array($resultRoomID213, $resultWhiteboardID, 1));

$dataRoomAttributes235 = array(
	array($resultRoomID235, $resultWhiteboardID, 1));

$dataRoomAttributes245 = array(
	array($resultRoomID245, $resultWhiteboardID, 1));

$dataRoomAttributes305 = array(
	array($resultRoomID305, $resultWhiteboardID, 1));

// Statement mit den Daten für die einzelnen Räume ausführen
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes012);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes032A);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes032B);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes032C);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes037);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes106);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes116);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes117);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes119);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes122);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes123);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes213);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes235);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes245);
$ilDB->executeMultiple($statementRoomAttributes, $dataRoomAttributes305);
?>
<#5>
<?php
$ilDB->dropPrimaryKey("rep_robj_xrs_book_attr");
$ilDB->addPrimaryKey("rep_robj_xrs_book_attr", array('booking_id', 'attr_id'));
?>
<#6>
<?php
//Testattributes for Roomsharing Bookings
/* $resultNextId = $ilDB->nextId("rep_robj_xrs_battr");
  $ilDB->manipulate("INSERT INTO rep_robj_xrs_battr (id, name, pool_id) VALUES "
  . "(".$ilDB->quote($resultNextId, 'integer').", 'Modul', 1)");
  $resultNextId = $ilDB->nextId("rep_robj_xrs_battr");
  $ilDB->manipulate("INSERT INTO rep_robj_xrs_battr (id, name, pool_id) VALUES "
  . "(".$ilDB->quote($resultNextId, 'integer').", 'Kurs', 1)");
  $resultNextId = $ilDB->nextId("rep_robj_xrs_battr");
  $ilDB->manipulate("INSERT INTO rep_robj_xrs_battr (id, name, pool_id) VALUES "
  . "(".$ilDB->quote($resultNextId, 'integer').", 'Semester', 1)"); */
?>

<#7>
<?php
// Additional main settings: max booking time and room use aggreement.
/* @var $ilDB ilDB */
$table = 'rep_robj_xrs_pools';

$agreementColumn = 'rooms_agreement';
$agreementAttributes = array(
	'type' => 'integer',
	"length" => 4,
	"default" => "0",
	'notnull' => true);
$ilDB->addTableColumn($table, $agreementColumn, $agreementAttributes);

$bookTimeColumn = 'max_book_time';
$bookTimeAttributes = array(
	'type' => 'timestamp',
	"default" => "1970-01-01 03:00:00.000000",
	'notnull' => true);
$ilDB->addTableColumn($table, $bookTimeColumn, $bookTimeAttributes);
?>

<#8>
<?php
// Additional main setting: calendar-id to create one calender per poolId.
// Additional attribute in bookings: public to clarify if username is visible (used later).
/* @var $ilDB ilDB */
$tablePools = 'rep_robj_xrs_pools';

$calendarColumn = 'calendar_id';
$calendarAttributes = array(
	'type' => 'integer',
	"length" => 4,
	"default" => "0",
	'notnull' => true);
$ilDB->addTableColumn($tablePools, $calendarColumn, $calendarAttributes);

$tableBookings = 'rep_robj_xrs_bookings';
$bookPublicColumn = 'public_booking';
$bookPublicAttributes = array(
	'type' => 'boolean');
$ilDB->addTableColumn($tableBookings, $bookPublicColumn, $bookPublicAttributes);
?>