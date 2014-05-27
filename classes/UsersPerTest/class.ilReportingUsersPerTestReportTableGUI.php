<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingReportTableGUI.php');
require_once('class.ilReportingUsersPerTestExcelExport.php');
require_once('class.ilReportingUsersPerTestPdfExport.php');

/**
 * Report table for the report UsersPerTest
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 */

class ilReportingUsersPerTestReportTableGUI extends ilReportingReportTableGUI {

    public function __construct($a_parent_obj, $a_parent_cmd) {
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function exportDataCustom($format, $send = false) {
        switch ($format) {
            case ilReportingGUI::EXPORT_EXCEL_FORMATTED:
                $export = new ilReportingUsersPerTestExcelExport('users_per_test_' . date('Y-m-d'));
                $export->execute($this->getData());
                break;
            case ilReportingGUI::EXPORT_PDF:
                $export = new ilReportingUsersPerTestPdfExport();
                $export->execute();
                break;
        }
    }
}