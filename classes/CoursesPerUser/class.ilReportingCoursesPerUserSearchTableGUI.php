<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingSearchTableGUI.php');

/**
 * TableGUI ilReportingCoursesPerUserTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilReportingCoursesPerUserSearchTableGUI extends ilReportingSearchTableGUI {

	/**
	 * @param ilReportingGUI $a_parent_obj
	 * @param string               $a_parent_cmd
	 */
	function __construct(ilReportingGUI $a_parent_obj, $a_parent_cmd) {
		parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addMultiCommand('report', $this->pl->txt('report_selected_courses_per_user'));
        $this->addCommandButton('report', $this->pl->txt('report_all_courses_per_user'));
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns() {
		$cols['lastname'] = array( 'txt' => $this->pl->txt('lastname'), 'default' => true );
		$cols['firstname'] = array( 'txt' => $this->pl->txt('firstname'), 'default' => true );
		$cols['email'] = array( 'txt' => $this->pl->txt('email'), 'default' => true );
        $cols['department'] = array( 'txt' => $this->pl->txt('department'), 'default' => true );
        $cols['country'] = array( 'txt' => $this->pl->txt('country'), 'default' => true );
		return $cols;
	}

    /**
     * Init filter for searching the users
     */
    public function initFilter() {
		$te = new ilTextInputGUI($this->pl->txt('lastname'), 'lastname');
		$this->addFilterItemWithValue($te);
		$te = new ilTextInputGUI($this->pl->txt('firstname'), 'firstname');
		$this->addFilterItemWithValue($te);
		$te = new ilTextInputGUI($this->pl->txt('email'), 'email');
		$this->addFilterItemWithValue($te);
        $te = new ilTextInputGUI($this->pl->txt('country'), 'country');
        $this->addFilterItemWithValue($te);
		$cb = new ilCheckboxInputGUI($this->pl->txt('incl_inactive_users'), 'include_inactive');
		$cb->setValue(1);
		$this->addFilterItemWithValue($cb);
	    parent::initFilter();
    }
}

?>