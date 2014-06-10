<?php

/**
 * Description of class
 * @author Michael
 */
class ilRoomSharingBook {

    public function getBookingAddenda() {
        global $ilDB;
        $cols = array();
        $attributesSet = $ilDB->query('SELECT *' .
                ' FROM rep_robj_xrs_battr' .
                ' WHERE pool_id = ' . $ilDB->quote(1, 'integer'));
        while ($attributesRow = $ilDB->fetchAssoc($attributesSet)) {
            $cols[$attributesRow['name']] = array("txt" => $attributesRow['name']);
        }
        return $cols;
    }

}
