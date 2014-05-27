<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingPdfExport.php');

/**
 * Class ilReportingUsersPerCourseLPPdfExport
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

class ilReportingUsersPerCourseLPPdfExport extends ilReportingPdfExport {

    public function __construct() {
        parent::__construct();
        $this->reportTitle = $this->pl->txt('users_per_course');
        $this->templateFilename = 'users_per_course_lp.jrxml';
        $this->outputFilename = 'users_per_course_lp_' . date('Y-m-d');
    }

} 