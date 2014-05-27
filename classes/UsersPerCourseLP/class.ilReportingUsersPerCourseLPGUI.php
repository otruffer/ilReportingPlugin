<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingGUI.php');
require_once('class.ilReportingUsersPerCourseLPSearchTableGUI.php');
require_once('class.ilReportingUsersPerCourseLPModel.php');
require_once('class.ilReportingUsersPerCourseLPReportTableGUI.php');

/**
 * GUI-Class ilReportingCoursesPerUserLPGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_IsCalledBy ilReportingUsersPerCourseLPGUI: ilRouterGUI
 */
class ilReportingUsersPerCourseLPGUI extends ilReportingGUI {

    function __construct() {
		parent::__construct();
        $this->model = new ilReportingUsersPerCourseLPModel();
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/styles.css');
    }

	public function executeCommand() {
        parent::executeCommand();
    }

    /**
     * Display table for searching the users
     */
    public function search() {
        $this->tpl->setTitle($this->pl->txt('report_users_per_course'));
        $this->table = new ilReportingUsersPerCourseLPSearchTableGUI($this, 'search');
        $this->table->setTitle($this->pl->txt('search_courses'));
        parent::search();
    }

    /**
     * Display report table
     */
    public function report() {
        parent::report();
        $this->tpl->setTitle($this->pl->txt('report_users_per_course'));
        if ($this->table === null) {
            $this->table = new ilReportingUsersPerCourseLPReportTableGUI($this, 'report');
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
        $this->table = new ilReportingUsersPerCourseLPSearchTableGUI($this, $this->getStandardCmd());
        parent::applyFilterSearch();
    }

    public function resetFilterSearch() {
        $this->table = new ilReportingUsersPerCourseLPSearchTableGUI($this, $this->getStandardCmd());
        parent::resetFilterSearch();
    }

    public function applyFilterReport() {
        $this->table = new ilReportingUsersPerCourseLPReportTableGUI($this, 'report');
        parent::applyFilterReport();
    }

    public function resetFilterReport() {
        $this->table = new ilReportingUsersPerCourseLPReportTableGUI($this, 'report');
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