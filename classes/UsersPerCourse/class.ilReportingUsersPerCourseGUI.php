<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingGUI.php');
require_once('class.ilReportingUsersPerCourseSearchTableGUI.php');
require_once('class.ilReportingUsersPerCourseModel.php');
require_once('class.ilReportingUsersPerCourseReportTableGUI.php');

/**
 * GUI-Class ilReportingUsersPerCourseGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_IsCalledBy ilReportingUsersPerCourseGUI: ilRouterGUI
 */
class ilReportingUsersPerCourseGUI extends ilReportingGUI {

    function __construct() {
	    parent::__construct();
        $this->model = new ilReportingUsersPerCourseModel();
    }

	public function executeCommand() {
        parent::executeCommand();
    }

    /**
     * Redirect to UsersPerCourseLP report which shows objects in courses which are relevant for LP
     */
    public function showObjectsInCourse() {
        $this->ctrl->setParameterByClass("ilreportinguserspercourselpgui", "from", "ilreportinguserspercoursegui");
        $this->ctrl->redirectByClass("ilreportinguserspercourselpgui", "report");
    }

    /**
     * Display table for searching the courses
     */
    public function search() {
        $this->tpl->setTitle($this->pl->txt('report_users_per_course'));
        $this->table = new ilReportingUsersPerCourseSearchTableGUI($this, 'search');
        $this->table->setTitle($this->pl->txt('search_courses'));
        parent::search();
    }

    /**
     * Display report table
     */
    public function report() {
        parent::report();
        if (isset($_GET['rep_crs_ref_id'])) {
            $this->ctrl->saveParameter($this, 'rep_crs_ref_id');
        }
        $this->tpl->setTitle($this->pl->txt('report_users_per_course'));
        if ($this->table === null) {
            $this->table = new ilReportingUsersPerCourseReportTableGUI($this, 'report');
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
        $this->table = new ilReportingUsersPerCourseSearchTableGUI($this, $this->getStandardCmd());
        parent::applyFilterSearch();
    }

    public function resetFilterSearch() {
        $this->table = new ilReportingUsersPerCourseSearchTableGUI($this, $this->getStandardCmd());
        parent::resetFilterSearch();
    }

    public function applyFilterReport() {
        if (isset($_GET['rep_crs_ref_id'])) {
            $this->ctrl->saveParameter($this, 'rep_crs_ref_id');
        }
        $this->table = new ilReportingUsersPerCourseReportTableGUI($this, 'report');
        parent::applyFilterReport();
    }

    public function resetFilterReport() {
        if (isset($_GET['rep_crs_ref_id'])) {
            $this->ctrl->saveParameter($this, 'rep_crs_ref_id');
        }
        $this->table = new ilReportingUsersPerCourseReportTableGUI($this, 'report');
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