<?php

include_once "Services/Form/classes/class.ilFormPropertyGUI.php";

/**
 * Class ilRoomSharingBookInputGUI
 *
 * @author Michael Dazjuk
 * @version $Id$
 */
class ilRoomSharingBookInputGUI extends ilFormPropertyGUI {

    protected $value;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    function __construct($a_title = "", $a_postvar = "") {
        parent::__construct($a_title, $a_postvar);
    }

    /**
     * Set Value.
     *
     * @param	string	$a_value	Value
     */
    function setValue($a_value) {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     *
     * @return	string	Value
     */
    function getValue() {
        return $this->value;
    }

    /**
     * Render item
     */
    protected function render($a_mode = "") {
        global $lng;
        $tpl = new ilTemplate("tpl.room_book_participant_input.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing");


        $def = array(null => null);


        foreach ($def as $slot) {
            $tpl->setVariable("TXT_PARTICIPANT", $lng->txt(""));
            $tpl->setVariable("IMG_MULTI_ADD", ilUtil::getImagePath('edit_add.png'));
            $tpl->setVariable("IMG_MULTI_REMOVE", ilUtil::getImagePath('edit_remove.png'));
            $tpl->setVariable("TXT_MULTI_ADD", $lng->txt("add"));
            $tpl->setVariable("TXT_MULTI_REMOVE", $lng->txt("remove"));


            // manage hidden buttons
            if ($row > 0) {
                $tpl->setVariable("ADD_CLASS", "ilNoDisplay");
            } else {
                $tpl->setVariable("RMV_CLASS", "ilNoDisplay");
            }

            $tpl->parseCurrentBlock();

            $row++;
        }

        return $tpl->get();
    }

    /**
     * Insert property html
     *
     * @return	int	Size
     */
    function insert(&$a_tpl) {
        global $tpl;

        $tpl->addJavascript("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/js/room_book_participant_input.js");

        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

}

?>
