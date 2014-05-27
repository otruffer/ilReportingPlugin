<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingPdfExport.php');

/**
 * Class ilReportingUsersPerTestPdfExport
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

class ilReportingUsersPerTestPdfExport extends ilReportingPdfExport {

    public function __construct() {
        parent::__construct();
        $this->reportTitle = $this->pl->txt('users_per_test');
        $this->templateFilename = 'users_per_test.jrxml';
        $this->outputFilename = 'users_per_test_' . date('Y-m-d');
    }

} 