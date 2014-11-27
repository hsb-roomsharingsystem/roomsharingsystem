<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilRoomSharingBookingsTableGUI
 *
 * @author Alexander Keller <a.k3ll3r@gmail.com>
 * @version $Id$
 *
 */
class ilRoomSharingBookingsTableGUI extends ilTable2GUI
{
	protected $bookings;
	protected $pool_id;

	const EXPORT_PDF = 3;

	/**
	 * Constructor
	 *
	 * @param unknown $a_parent_obj
	 * @param unknown $a_parent_cmd
	 * @param unknown $a_ref_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng;

		$this->parent_obj = $a_parent_obj;
		$this->lng = $lng;

		$this->ctrl = $ilCtrl;
		$this->ref_id = $a_ref_id;
		$this->setId("roomobj");

		include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/' .
			'RoomSharing/classes/appointments/bookings/class.ilRoomSharingBookings.php';
		$this->bookings = new ilRoomSharingBookings($a_parent_obj->getPoolId());
		$this->bookings->setPoolId($a_parent_obj->getPoolId());
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("rep_robj_xrs_bookings"));
		$this->setLimit(10); // data sets per page
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		// add columns and column headings
		$this->_addColumns();

		// checkboxes labeled with "bookings" get affected by the "Select All"-Checkbox
		$this->setSelectAllCheckbox('bookings');
		$this->setRowTemplate("tpl.room_appointment_row.html",
			"Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/");
		// command for cancelling bookings

		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL, self::EXPORT_PDF));
		$this->addMultiCommand('showBookings', $this->lng->txt('rep_robj_xrs_booking_cancel'));

		$this->getItems();
	}

	/**
	 * Gets all the items that need to be populated into the table.
	 */
	public function getItems()
	{
		$data = $this->bookings->getList();

		$this->setMaxCount(count($data));
		$this->setData($data);
	}

	/**
	 * Adds columns and column headings to the table.
	 */
	private function _addColumns()
	{
		$this->addColumn('', 'f', '1'); // checkboxes
		$this->addColumn('', 'f', 'l'); // icons
		$this->addColumn($this->lng->txt("rep_robj_xrs_date"), "date");
		$this->addColumn($this->lng->txt("rep_robj_xrs_room"), "room");
		$this->addColumn($this->lng->txt("rep_robj_xrs_subject"), "subject");
		$this->addColumn($this->lng->txt("rep_robj_xrs_participants"), "participants");

		// Add the selected optional columns to the table
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($c, $c);
		}
		$this->addColumn($this->lng->txt(''), 'optional');
	}

	/**
	 * Fills an entire table row with the given set.
	 *
	 * (non-PHPdoc)
	 * @see ilTable2GUI::fillRow()
	 * @param $a_set data set for that row
	 */
	public function fillRow($a_set)
	{
		// the "CHECKBOX_NAME" has to match with the label set in the
		// setSelectAllCheckbox()-function in order to be affected when the
		// "Select All" Checkbox is checked
		$this->tpl->setVariable('CHECKBOX_NAME', 'bookings');

		// ### Recurrence ###
		if ($a_set ['recurrence'])
		{
			// icon for the recurrence date
			$this->tpl->setVariable('IMG_RECURRENCE_PATH', ilUtil::getImagePath("cmd_move_s.png"));
		}
		$this->tpl->setVariable('IMG_RECURRENCE_TITLE',
			$this->lng->txt("rep_robj_xrs_room_date_recurrence"));

		// ### Appointment ###
		$this->tpl->setVariable('TXT_DATE', $a_set ['date']);
		// link for the date overview
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', $a_set['id']);
		// $this->tpl->setVariable('HREF_DATE', $this->ctrl->getLinkTargetByClass(
		// 'ilobjroomsharinggui', 'showBooking'));
		// $this->ctrl->setParameterByClass('ilobjroomsharinggui', 'booking_id', '');
		// ### Room ###
		$this->tpl->setVariable('TXT_ROOM', $a_set ['room']);
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', $a_set ['room_id']);
		$this->tpl->setVariable('HREF_ROOM',
			$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showRoom'));
		$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'room_id', '');

		$this->tpl->setVariable('TXT_SUBJECT', ($a_set ['subject'] === null ? '' : $a_set ['subject']));

		// ### Participants ###
		$participant_count = count($a_set ['participants']);
		for ($i = 0; $i < $participant_count; ++$i)
		{
			$this->tpl->setCurrentBlock("participants");
			$this->tpl->setVariable("TXT_USER", $a_set ['participants'] [$i]);

			// put together a link for the user profile view
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id',
				$a_set ['participants_ids'] [$i]);
			$this->tpl->setVariable('HREF_PROFILE',
				$this->ctrl->getLinkTargetByClass('ilobjroomsharinggui', 'showProfile'));
			// unset the parameter for safety purposes
			$this->ctrl->setParameterByClass('ilobjroomsharinggui', 'user_id', '');

			if ($i < $participant_count - 1)
			{
				$this->tpl->setVariable('TXT_SEPARATOR', ',');
			}
			$this->tpl->parseCurrentBlock();
		}

		// Populate the selected additional table cells
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->tpl->setCurrentBlock("additional");
			$this->tpl->setVariable("TXT_ADDITIONAL", $a_set [$c] === null ? "" : $a_set [$c]);
			$this->tpl->parseCurrentBlock();
		}

		// actions
		$this->tpl->setCurrentBlock("actions");
		$this->tpl->setVariable('LINK_ACTION',
			$this->ctrl->getLinkTarget($this->parent_obj, 'showBookings'));
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('edit'));
		$this->tpl->setVariable('LINK_ACTION_SEPARATOR', '<br>');
		$this->tpl->parseCurrentBlock();

		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_id', $a_set ['id']);
		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', $a_set ['subject']);
		$this->tpl->setVariable('LINK_ACTION',
			$this->ctrl->getLinkTargetByClass('ilroomsharingbookingsgui', 'confirmCancel'));
		$this->tpl->setVariable('LINK_ACTION_TXT', $this->lng->txt('rep_robj_xrs_booking_cancel'));
		$this->ctrl->setParameterByClass('ilroomsharingbookingssgui', 'booking_id', '');
		$this->ctrl->setParameterByClass('ilroomsharingbookingsgui', 'booking_subject', '');

		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Can be used to add additional columns to the bookings table.
	 *
	 * (non-PHPdoc)
	 * @see ilTable2GUI::getSelectableColumns()
	 * @return additional information for bookings
	 */
	public function getSelectableColumns()
	{
		return $this->bookings->getAdditionalBookingInfos();
	}

	public function setExportFormats(array $formats)
	{
		$this->export_formats = array();

		// #11339
		$valid = array(self::EXPORT_EXCEL => "tbl_export_excel",
			self::EXPORT_CSV => "tbl_export_csv", self::EXPORT_PDF => $this->lng->txt("rep_robj_xrs_export_pdf"));

		foreach ($formats as $format)
		{
			if (array_key_exists($format, $valid))
			{
				$this->export_formats[$format] = $valid[$format];
			}
		}
	}

	/**
	 * Fill footer row
	 */
	function fillFooter()
	{
		global $lng, $ilCtrl, $ilUser;

		$footer = false;

		// select all checkbox
		if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())) && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("select_all_checkbox");
			$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
			$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
			$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
			$this->tpl->setVariable("CHECKBOXNAME", "chb_select_all_" . $this->unique_id);
			$this->tpl->parseCurrentBlock();
		}

		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{
			$start = $this->offset + 1; // compute num info
			if (!$this->dataExists())
			{
				$start = 0;
			}
			$end = $this->offset + $this->limit;

			if ($end > $this->max_count or $this->limit == 0)
			{
				$end = $this->max_count;
			}

			if ($this->max_count > 0)
			{
				if ($this->lang_support)
				{
					$numinfo = "(" . $start . " - " . $end . " " . strtolower($this->lng->txt("of")) . " " . $this->max_count . ")";
				}
				else
				{
					$numinfo = "(" . $start . " - " . $end . " of " . $this->max_count . ")";
				}
			}
			if ($this->max_count > 0)
			{
				if ($this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("tbl_footer_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
			}
			$footer = true;
		}

		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit != 0 && $this->max_count
			> 0)
		{
			$layout = array(
				"link" => $this->footer_style,
				"prev" => $this->footer_previous,
				"next" => $this->footer_next,
			);
			//if (!$this->getDisplayAsBlock())
			//{
			$linkbar = $this->getLinkbar("1");
			$this->tpl->setCurrentBlock("tbl_footer_linkbar");
			$this->tpl->setVariable("LINKBAR", $linkbar);
			$this->tpl->parseCurrentBlock();
			$linkbar = true;
			//}
			$footer = true;
		}

		// column selector
		if (count($this->getSelectableColumns()) > 0)
		{
			$items = array();
			foreach ($this->getSelectableColumns() as $k => $c)
			{
				$items[$k] = array("txt" => $c["txt"],
					"selected" => $this->isColumnSelected($k));
			}
			include_once("./Services/UIComponent/CheckboxListOverlay/classes/class.ilCheckboxListOverlayGUI.php");
			$cb_over = new ilCheckboxListOverlayGUI("tbl_" . $this->getId());
			$cb_over->setLinkTitle($lng->txt("columns"));
			$cb_over->setItems($items);
			//$cb_over->setUrl("./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
			//		$this->getId()."&cmd=saveSelectedFields&user_id=".$ilUser->getId());
			$cb_over->setFormCmd($this->getParentCmd());
			$cb_over->setFieldVar("tblfs" . $this->getId());
			$cb_over->setHiddenVar("tblfsh" . $this->getId());
			$cb_over->setSelectionHeaderClass("ilTableMenuItem");
			$column_selector = $cb_over->getHTML();
			$footer = true;
		}

		if ($this->getShowTemplates() && is_object($ilUser))
		{
			// template handling
			if (isset($_REQUEST["tbltplcrt"]) && $_REQUEST["tbltplcrt"])
			{
				if ($this->saveTemplate($_REQUEST["tbltplcrt"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_created"));
				}
			}
			else if (isset($_REQUEST["tbltpldel"]) && $_REQUEST["tbltpldel"])
			{
				if ($this->deleteTemplate($_REQUEST["tbltpldel"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_deleted"));
				}
			}

			$create_id = "template_create_overlay_" . $this->getId();
			$delete_id = "template_delete_overlay_" . $this->getId();
			$list_id = "template_stg_" . $this->getId();

			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();
			$templates = $storage->getNames($this->getContext(), $ilUser->getId());

			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");

			// form to delete template
			if (sizeof($templates))
			{
				$overlay = new ilOverlayGUI($delete_id);
				$overlay->setTrigger($list_id . "_delete");
				$overlay->setAnchor("ilAdvSelListAnchorElement_" . $list_id);
				$overlay->setAutoHide(false);
				$overlay->add();

				$lng->loadLanguageModule("form");
				$this->tpl->setCurrentBlock("template_editor_delete_item");
				$this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", "");
				$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", "- " . $lng->txt("form_please_select") . " -");
				$this->tpl->parseCurrentBlock();
				foreach ($templates as $name)
				{
					$this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", $name);
					$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", $name);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("template_editor_delete");
				$this->tpl->setVariable("TEMPLATE_DELETE_ID", $delete_id);
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE", $lng->txt("tbl_template_delete"));
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE_SUBMIT", $lng->txt("delete"));
				$this->tpl->setVariable("TEMPLATE_DELETE_CMD", $this->parent_cmd);
				$this->tpl->parseCurrentBlock();
			}


			// form to save new template
			$overlay = new ilOverlayGUI($create_id);
			$overlay->setTrigger($list_id . "_create");
			$overlay->setAnchor("ilAdvSelListAnchorElement_" . $list_id);
			$overlay->setAutoHide(false);
			$overlay->add();

			$this->tpl->setCurrentBlock("template_editor");
			$this->tpl->setVariable("TEMPLATE_CREATE_ID", $create_id);
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE", $lng->txt("tbl_template_create"));
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE_SUBMIT", $lng->txt("save"));
			$this->tpl->setVariable("TEMPLATE_CREATE_CMD", $this->parent_cmd);
			$this->tpl->parseCurrentBlock();

			// load saved template
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($list_id);
			$alist->addItem($lng->txt("tbl_template_create"), "create", "#");
			if (sizeof($templates))
			{
				$alist->addItem($lng->txt("tbl_template_delete"), "delete", "#");
				foreach ($templates as $name)
				{
					$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_tpl", urlencode($name));
					$alist->addItem($name, $name, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
					$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_tpl", "");
				}
			}
			$alist->setListTitle($lng->txt("tbl_templates"));
			$this->tpl->setVariable("TEMPLATE_SELECTOR", "&nbsp;" . $alist->getHTML());
		}

		if ($footer)
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
			if ($this->getDisplayAsBlock())
			{
				$this->tpl->setVariable("BLK_CLASS", "Block");
			}
			$this->tpl->parseCurrentBlock();

			// top navigation, if number info or linkbar given
			if ($numinfo != "" || $linkbar != "" || $column_selector != "" ||
				count($this->filters) > 0 || count($this->optional_filters) > 0)
			{
				if (is_object($ilUser) && (count($this->filters) || count($this->optional_filters)))
				{
					$this->tpl->setCurrentBlock("filter_activation");
					$this->tpl->setVariable("TXT_ACTIVATE_FILTER", $lng->txt("show_filter"));
					$this->tpl->setVariable("FILA_ID", $this->getId());
					if ($this->getId() != "")
					{
						$this->tpl->setVariable("SAVE_URLA",
							"./ilias.php?baseClass=ilTablePropertiesStorage&table_id=" .
							$this->getId() . "&cmd=showFilter&user_id=" . $ilUser->getId());
					}
					$this->tpl->parseCurrentBlock();


					if (!$this->getDisableFilterHiding())
					{
						$this->tpl->setCurrentBlock("filter_deactivation");
						$this->tpl->setVariable("TXT_HIDE", $lng->txt("hide_filter"));
						if ($this->getId() != "")
						{
							$this->tpl->setVariable("SAVE_URL",
								"./ilias.php?baseClass=ilTablePropertiesStorage&table_id=" .
								$this->getId() . "&cmd=hideFilter&user_id=" . $ilUser->getId());
							$this->tpl->setVariable("FILD_ID", $this->getId());
						}
						$this->tpl->parseCurrentBlock();
					}
				}

				if ($numinfo != "" && $this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("top_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
				if ($linkbar != "" && !$this->getDisplayAsBlock())
				{
					$linkbar = $this->getLinkbar("2");
					$this->tpl->setCurrentBlock("top_linkbar");
					$this->tpl->setVariable("LINKBAR", $linkbar);
					$this->tpl->parseCurrentBlock();
				}

				// column selector
				$this->tpl->setVariable("COLUMN_SELECTOR", $column_selector);

				// row selector
				if ($this->getShowRowsSelector() && is_object($ilUser))
				{
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setId("sellst_rows_" . $this->getId());
					$hpp = ($ilUser->getPref("hits_per_page") != 9999) ? $ilUser->getPref("hits_per_page") : $lng->txt("unlimited");

					$options = array(0 => $lng->txt("default") . " (" . $hpp . ")", 5 => 5, 10 => 10, 15 => 15, 20 => 20,
						30 => 30, 40 => 40, 50 => 50,
						100 => 100, 200 => 200, 400 => 400, 800 => 800);
					foreach ($options as $k => $v)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_trows", $k);
						$alist->addItem($v, $k, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
						$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_trows", "");
					}
					$alist->setListTitle($this->getRowSelectorLabel() ? $this->getRowSelectorLabel() : $lng->txt("rows"));
					$this->tpl->setVariable("ROW_SELECTOR", $alist->getHTML());
				}

				// export
				if (sizeof($this->export_formats) && $this->dataExists())
				{
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setId("sellst_xpt");
					foreach ($this->export_formats as $format => $caption_lng_id)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_xpt", $format);
						$url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd);
						$ilCtrl->setParameter($this->parent_obj, $this->prefix . "_xpt", "");
						$caption = $lng->txt($caption_lng_id);
						//this part is necessary, because the labels for xls- and csv-Export are fetched from the ilias-lang-files, while the label for pdf-export is fetched from the lang-file of the plugin
						if (strpos($caption, '-') === 0 && strpos($caption, '-', strlen($caption) - 1) === strlen($caption)
							- 1)
						{
							$alist->addItem($caption_lng_id, $format, $url);
						}
						else
						{
							$alist->addItem($lng->txt($caption_lng_id), $format, $url);
						}
					}
					$alist->setListTitle($lng->txt("export"));
					$this->tpl->setVariable("EXPORT_SELECTOR", "&nbsp;" . $alist->getHTML());
				}

				$this->tpl->setCurrentBlock("top_navigation");
				$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
				if ($this->getDisplayAsBlock())
				{
					$this->tpl->setVariable("BLK_CLASS", "Block");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
	}

}
