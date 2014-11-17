<?php

include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/rooms/detail/calendar/class.ilRoomSharingCalendarEntry.php");

class ilRoomSharingCalendarSchedule extends ilCalendarSchedule
{
	protected $room_obj;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param ilDate seed date
	 * @param int type of schedule (TYPE_DAY,TYPE_WEEK or TYPE_MONTH)
	 * @param int user_id
	 *
	 */
	public function __construct(ilDate $seed, $a_type, $a_user_id = 0, ilRoomSharingRoom $room)
	{
		global $ilUser, $ilDB;

		$this->room_obj = $room;

		$this->db = $ilDB;

		$this->type = $a_type;
		$this->initPeriod($seed);

		if (!$a_user_id || $a_user_id == $ilUser->getId())
		{
			$this->user = $ilUser;
		}
		else
		{
			$this->user = new ilObjUser($a_user_id);
		}
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
		$this->weekstart = $this->user_settings->getWeekStart();
		$this->timezone = $this->user->getTimeZone();
	}

	/**
	 * calculate
	 *
	 * @access protected
	 */
	public function calculate()
	{
		$events = $this->getEvents();

		// we need category type for booking handling
		$ids = array();
		foreach ($events as $event)
		{
			$ids[] = $event->getEntryId();
		}

		$counter = 0;
		foreach ($events as $event)
		{
			$this->schedule[$counter]['event'] = $event;
			$this->schedule[$counter]['dstart'] = $event->getStart()->get(IL_CAL_UNIX);
			$this->schedule[$counter]['dend'] = $event->getEnd()->get(IL_CAL_UNIX);
			$this->schedule[$counter]['fullday'] = $event->isFullday();
			$this->schedule[$counter]['category_id'] = $cat_map[$event->getEntryId()];
			$this->schedule[$counter]['category_type'] = $cat_types[$cat_map[$event->getEntryId()]];

			if (!$event->isFullday())
			{
				switch ($this->type)
				{
					case self::TYPE_DAY:
					case self::TYPE_WEEK:
						// store date info (used for calculation of overlapping events)
						$tmp_date = new ilDateTime($this->schedule[$counter]['dstart'], IL_CAL_UNIX, $this->timezone);
						$this->schedule[$counter]['start_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE, '',
							$this->timezone);

						$tmp_date = new ilDateTime($this->schedule[$counter]['dend'], IL_CAL_UNIX, $this->timezone);
						$this->schedule[$counter]['end_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE, '', $this->timezone);
						break;

					default:
						break;
				}
			}
			$counter++;
			if ($this->areEventsLimited() && $counter >= $this->getEventsLimit())
			{
				break;
			}
		}
	}

	/**
	 * get new/changed events
	 *
	 * @param bool $a_include_subitem_calendars E.g include session calendars of courses.
	 * @return object $events[] Array of changed events
	 * @access protected
	 * @return
	 */
	public function getChangedEvents($a_include_subitem_calendars = false)
	{
		//geht noch nicht
	}

	/**
	 * Read events (will be moved to another class, since only active and/or visible calendars are shown)
	 *
	 * @access protected
	 */
	public function getEvents()
	{
		global $ilDB;

		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($this->user->getId())->getCategories($this->enabledSubitemCalendars());
		$cats = $this->filterCategories($cats);

		if (!count($cats))
		{
			return array();
		}

		// TODO: optimize
		$query = "SELECT b.id id" .
			" FROM rep_robj_xrs_bookings b";

		if ($this->type != self::TYPE_INBOX)
		{
			$query .= " WHERE ((date_from <= " . $this->db->quote($this->end->get(IL_CAL_DATETIME, '', 'UTC'),
					'timestamp') .
				" AND date_to >= " . $this->db->quote($this->start->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ")" .
				" OR (date_from <= " . $this->db->quote($this->end->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') .
				" ))";
		}
		else
		{
			$date = new ilDateTime(mktime(0, 0, 0), IL_CAL_UNIX);
			$query .= " WHERE date_from >= " . $this->db->quote($date->get(IL_CAL_DATETIME, '', 'UTC'),
					'timestamp');
		}

		$query .= " AND room_id = " . $this->db->quote($this->room_obj->getId(), 'integer') .
			" AND pool_id = " . $this->db->quote($this->room_obj->getPoolId(), 'integer') .
			" ORDER BY date_from";

		$res = $this->db->query($query);

		$events = array();
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$event = new ilRoomSharingCalendarEntry($row->id);
			if ($this->isValidEventByFilters($event))
			{
				$events[] = $event;
			}
		}
		return $events;
	}

}

?>