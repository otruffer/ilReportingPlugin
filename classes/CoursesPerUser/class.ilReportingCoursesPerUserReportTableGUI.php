<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingReportTableGUI.php');
require_once('class.ilReportingCoursesPerUserExcelExport.php');
require_once('class.ilReportingCoursesPerUserPdfExport.php');

/**
 * Report table for the report CoursesPerUser
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 */

class ilReportingCoursesPerUserReportTableGUI extends ilReportingReportTableGUI {

    public function __construct($a_parent_obj, $a_parent_cmd) {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addCommandButton("showObjectsInCourse", $this->pl->txt("show_objects_in_course"));
    }

    public function exportDataCustom($format, $send = false) {
        switch ($format) {
            case ilReportingGUI::EXPORT_EXCEL_FORMATTED:
                $export = new ilReportingCoursesPerUserExcelExport('courses_per_user_' . date('Y-m-d'));
                $export->execute($this->getData());
                break;
            case ilReportingGUI::EXPORT_PDF:
                $export = new ilReportingCoursesPerUserPdfExport();
                $export->execute();
                break;
        }
    }
}