<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingPdfExport.php');

/**
 * Class ilReportingCoursesPerUserPdfExport.
 * PDF Export Courses per User
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

class ilReportingCoursesPerUserPdfExport extends ilReportingPdfExport {

    public function __construct() {
        parent::__construct();
        $this->reportTitle = $this->pl->txt('courses_per_user');
        $this->templateFilename = 'courses_per_user.jrxml';
        $this->outputFilename = 'courses_per_user_' . date('Y-m-d');
    }

} 