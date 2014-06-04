<?php

/**
 * Class ilRoomSharingAdvancedSearchGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 */
class ilRoomSharingAdvancedSearchGUI
{
    protected $ref_id;
    protected $pool_id;

    /**
     * Constructor for the class ilRoomSharingAdvancedSearchGUI
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
        $cmd = $ilCtrl->getCmd("showAdvancedSearch");

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
     * Show an advanced search form.
     */
    public function showAdvancedSearchObject()
    {
        global $tpl, $ilCtrl;
        $this->lng->loadLanguageModule("dateplaner");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
        include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
        $tpl->addJavaScript('./Services/Form/js/date_duration.js');
		$asearch_form = new ilPropertyFormGUI();
        
        // Date Duration
        $date_duration = new ilDateDurationInputGUI($this->lng->txt('cal_fullday'), 'date_durtaion');
        $date_duration->setStartText($this->lng->txt('cal_start'));
		$date_duration->setEndText($this->lng->txt('cal_end'));
        $date_duration->enableToggleFullTime($this->lng->txt('cal_fullday_title'), false);
        $date_duration->setShowDate(true);
        $date_duration->setShowTime(true);
        $date_duration->setRequired(true);
        $asearch_form->addItem($date_duration);
        
        // Recurrence
        include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
        include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
        $rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
        $asearch_form->addItem($rec);
        
        $asearch_form->setTitle($this->lng->txt("rep_robj_xrs_advanced_search"));
		$asearch_form->addCommandButton("showAdvancedSearch", $this->lng->txt("rep_robj_xrs_search"));
        $asearch_form->setFormAction($ilCtrl->getFormAction($this));
        $tpl->setContent($asearch_form->getHTML());
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
