<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingGUI.php');
require_once('class.ilReportingCoursesPerUserSearchTableGUI.php');
require_once('class.ilReportingCoursesPerUserModel.php');
require_once('class.ilReportingCoursesPerUserReportTableGUI.php');

/**
 * GUI-Class ilReportingCoursesPerUserGUI
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 *
 * @ilCtrl_IsCalledBy ilReportingCoursesPerUserGUI: ilRouterGUI
 */
class ilReportingCoursesPerUserGUI extends ilReportingGUI {

    function __construct() {
		parent::__construct();
        $this->model = new ilReportingCoursesPerUserModel();
	}

	public function executeCommand() {
        parent::executeCommand();
    }


    /**
     * Redirect to CoursesPerUserLP report which shows objects in courses which are relevant for LP
     */
    public function showObjectsInCourse() {
        $this->ctrl->setParameterByClass("ilreportingcoursesperuserlpgui", "from", "ilreportingcoursesperusergui");
        $this->ctrl->redirectByClass("ilreportingcoursesperuserlpgui", "report");
    }

    /**
     * Display table for searching the users
     */
    public function search() {
        $this->tpl->setTitle($this->pl->txt('report_courses_per_user'));
        $this->table = new ilReportingCoursesPerUserSearchTableGUI($this, 'search');
        $this->table->setTitle($this->pl->txt('search_users'));
        parent::search();
    }

    /**
     * Display report table
     */
    public function report() {
        parent::report();
        $this->tpl->setTitle($this->pl->txt('report_courses_per_user'));
        if ($this->table === null) {
            $this->table = new ilReportingCoursesPerUserReportTableGUI($this, 'report');
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
        $this->table = new ilReportingCoursesPerUserSearchTableGUI($this, $this->getStandardCmd());
        parent::applyFilterSearch();
    }

    public function resetFilterSearch() {
        $this->table = new ilReportingCoursesPerUserSearchTableGUI($this, $this->getStandardCmd());
        parent::resetFilterSearch();
    }

    public function applyFilterReport() {
        $this->table = new ilReportingCoursesPerUserReportTableGUI($this, 'report');
        parent::applyFilterReport();
    }

    public function resetFilterReport() {
        $this->table = new ilReportingCoursesPerUserReportTableGUI($this, 'report');
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