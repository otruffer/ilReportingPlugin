<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingGUI.php');
require_once('class.ilReportingUsersPerTestSearchTableGUI.php');
require_once('class.ilReportingUsersPerTestModel.php');
require_once('class.ilReportingUsersPerTestReportTableGUI.php');

/**
 * GUI-Class ilReportingUsersPerTestGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_IsCalledBy ilReportingUsersPerTestGUI: ilRouterGUI
 */
class ilReportingUsersPerTestGUI extends ilReportingGUI {

    function __construct() {
	    parent::__construct();
        $this->model = new ilReportingUsersPerTestModel();
    }

	public function executeCommand() {
        parent::executeCommand();
    }

    /**
     * Display table for searching the tests
     */
    public function search() {
        $this->tpl->setTitle($this->pl->txt('report_users_per_test'));
        $this->table = new ilReportingUsersPerTestSearchTableGUI($this, 'search');
        $this->table->setTitle($this->pl->txt('search_tests'));
        parent::search();
    }

    /**
     * Display report table
     */
    public function report() {
        parent::report();
        $this->tpl->setTitle($this->pl->txt('report_users_per_test'));
        if ($this->table === null) {
            $this->table = new ilReportingUsersPerTestReportTableGUI($this, 'report');
        }
        $data = $this->model->getReportData($_SESSION[self::SESSION_KEY_IDS], $this->table->getFilterNames());
        $this->table->setData($data);
        if ($this->ctrl->getCmd() != 'applyFilterReport' && $this->ctrl->getCmd() != 'resetFilterReport') {
            $onlyUnique = isset($_GET['pre_xpt']);
            $this->storeIdsInSession($data, $onlyUnique);
        }
        $this->tpl->setContent($this->table->getHTML());
    }

    public function applyFilterSearch() {
        $this->table = new ilReportingUsersPerTestSearchTableGUI($this, $this->getStandardCmd());
        parent::applyFilterSearch();
    }

    public function resetFilterSearch() {
        $this->table = new ilReportingUsersPerTestSearchTableGUI($this, $this->getStandardCmd());
        parent::resetFilterSearch();
    }

    public function applyFilterReport() {
        $this->table = new ilReportingUsersPerTestReportTableGUI($this, 'report');
        parent::applyFilterReport();
    }

    public function resetFilterReport() {
        $this->table = new ilReportingUsersPerTestReportTableGUI($this, 'report');
        parent::resetFilterReport();
    }

    public function getAvailableExports() {
        $exports = array(
            self::EXPORT_EXCEL_FORMATTED => 'export_custom_excel',
        );
        if ($this->isActiveJasperReports()) $exports[self::EXPORT_PDF] = 'export_pdf';
        return $exports;
    }

}

?>