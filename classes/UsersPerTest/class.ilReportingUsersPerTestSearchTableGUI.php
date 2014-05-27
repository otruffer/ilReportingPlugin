<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingSearchTableGUI.php');

/**
 * TableGUI ilReportingUsersPerTestTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilReportingUsersPerTestSearchTableGUI extends ilReportingSearchTableGUI {

    /**
	 * @param ilReportingGUI $a_parent_obj
	 * @param string               $a_parent_cmd
	 */
	function __construct(ilReportingGUI $a_parent_obj, $a_parent_cmd) {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addCommandButton('report', $this->pl->txt('report_all_users_per_test'));
        $this->addMultiCommand('report', $this->pl->txt('report_selected_users_per_test'));
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
     * Init the filter for searching tests
     */
    public function initFilter() {
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$this->addFilterItemWithValue($te);
	    parent::initFilter();
    }

}

?>