<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingSearchTableGUI.php');

/**
 * TableGUI ilReportingCoursesPerUserLPSearchTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilReportingUsersPerCourseLPSearchTableGUI extends ilReportingSearchTableGUI {

	/**
	 * @param ilReportingGUI $a_parent_obj
	 * @param string               $a_parent_cmd
	 */
	function __construct(ilReportingGUI $a_parent_obj, $a_parent_cmd) {
		parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addMultiCommand('report', $this->pl->txt('report_selected_users_per_course'));
        $this->addCommandButton('report', $this->pl->txt('report_all_users_per_course'));
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns() {
        $cols['title'] = array( 'txt' => $this->pl->txt('title'), 'default' => true );
        $cols['path'] = array( 'txt' => $this->pl->txt('path'), 'default' => true );
        return $cols;
	}

    /**
     * Init filter for searching the users
     */
    public function initFilter() {
        $te = new ilTextInputGUI('Title', 'title');
        $this->addFilterItemWithValue($te);
        parent::initFilter();
    }
}

?>