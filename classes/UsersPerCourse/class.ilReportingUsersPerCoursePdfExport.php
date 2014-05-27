<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingPdfExport.php');

/**
 * Class ilReportingUsersPerCoursePdfExport
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

class ilReportingUsersPerCoursePdfExport extends ilReportingPdfExport {

    public function __construct() {
        parent::__construct();
        $this->reportTitle = $this->pl->txt('users_per_course');
        $this->templateFilename = 'users_per_course.jrxml';
        $this->outputFilename = 'users_per_course' . date('Y-m-d');
    }

} 