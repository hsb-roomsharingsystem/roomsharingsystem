<?php

/**
 * Class ilRoomSharingQuickSearchGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 */
class ilRoomSharingQuickSearchGUI
{
    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor for the class ilRoomSharingQuickSearchGUI
     * @param object $a_parent_obj
     */
    public function __construct(ilRoomSharingSearchGUI $a_parent_obj)
    {
        global $ilCtrl, $lng, $tpl;

        $this->parent_obj = $a_parent_obj;
        $this->ref_id = $a_parent_obj->ref_id;
        $this->pool_id = $a_parent_obj->getPoolId();
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    /**
     * Execute the command given.
     */
    public function executeCommand()
    {
        global $ilCtrl;

        // the default command, if none is set
        $cmd = $ilCtrl->getCmd("showQuickSearch");

        switch ($next_class)
        {
            default:
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Show a quick search form.
     */
    public function showQuickSearchObject()
    {
        global $tpl, $ilCtrl, $lng;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
		$qsearch_form = new ilPropertyFormGUI();
        
        // Date
        $date_comb = new ilCombinationInputGUI($this->lng->txt("date"), "date");
        $date = new ilDateTimeInputGUI("", "date");
        $date_comb->addCombinationItem("date", $date, $lng->txt("rep_robj_xrs_on"));
        $date_comb->setRequired(true);
        $qsearch_form->addItem($date_comb);
        
        // Time Range
        $time_comb = new ilCombinationInputGUI($this->lng->txt("rep_robj_xrs_time_range"), "time");
        $time_from = new ilDateTimeInputGUI("", "time_from");		
        $time_from->setShowTime(true);
        $time_from->setShowDate(false);
        $time_comb->addCombinationItem("time_from", $time_from, $lng->txt("rep_robj_xrs_between"));
        $time_to = new ilDateTimeInputGUI("", "date_to");
        $time_to->setShowTime(true);
        $time_to->setShowDate(false);
        $time_comb->addCombinationItem("time_to", $time_to, $lng->txt("and"));
        $time_comb->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
        $time_comb->setRequired(true);
        $qsearch_form->addItem($time_comb);
        
        $qsearch_form->setTitle($lng->txt("rep_robj_xrs_quick_search"));
		$qsearch_form->addCommandButton("showQuickSearch", $lng->txt("rep_robj_xrs_search"));
        $qsearch_form->setFormAction($ilCtrl->getFormAction($this));
        $tpl->setContent($qsearch_form->getHTML());
    }

    /**
     * Returns the Roomsharing Pool ID.
     */
    public function getPoolId()
    {
        return $this->pool_id;
    }

    /**
     * Sets the Roomsharing Pool ID.
     */
    public function setPoolId($a_pool_id)
    {
        $this->pool_id = $a_pool_id;
    }
}

?>
