<?php
require_once('class.ilReportingPlugin.php');

/**
 * Class ilReportingPdfExport
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

abstract class ilReportingPdfExport {

    /** @var \ilReportingPlugin  */
    protected $pl;

    /** @var string  */
    protected $templatesPath = '';

    /** @var string  */
    protected $reportTitle = '';

    /** @var string  */
    protected $outputFilename = '';

    /** @var string  */
    protected $templateFilename = '';

    /** @var \ilObjUser  */
    protected $user;

    public function __construct() {
        global $ilUser;
        $this->pl = new ilReportingPlugin();
        $this->templatesPath = $this->pl->getConfigObject()->getValue('jasper_reports_templates_path');
        $this->user = $ilUser;
    }

    /**
     * Execute the report
     */
    public function execute() {
        $report = new JasperReport($this->templatesPath . $this->templateFilename, $this->outputFilename);
        $params = $this->getPdfReportParameters();
        $params['report_title'] = $this->reportTitle;
        $report->setParameters($params);
        $report->downloadFile(true);
    }

    /**
     * Return parameters that are passed to Jasper Report
     * @return array
     */
    protected function getPdfReportParameters() {
        $params = array(
//            'ids' => implode(',', $_SESSION[ilReportingGUI::SESSION_KEY_IDS]),
            'unique_ids' => "'" . implode("','", $_SESSION[ilReportingGUI::SESSION_KEY_UNIQUE_IDS]) . "'",
            'status0' => $this->pl->txt('status0'),
            'status1' => $this->pl->txt('status1'),
            'status2' => $this->pl->txt('status2'),
            'status3' => $this->pl->txt('status3'),
            'firstname' => $this->pl->txt('firstname'),
            'lastname' => $this->pl->txt('lastname'),
            'department' => $this->pl->txt('department'),
            'country' => $this->pl->txt('country'),
            'status_changed' => $this->pl->txt('status_changed'),
            'user_status' => $this->pl->txt('user_status'),
            'title' => $this->pl->txt('title'),
            'path' => $this->pl->txt('path'),
            'owner_report' => $this->pl->txt('owner_of_report'),
            'owner_name' => $this->user->getPresentationTitle(),
        );
        // Check if a header image is specified
        $img = $this->pl->getConfigObject()->getValue('header_image');
        if ($img && is_file($img)) {
            $params['header_image'] = $img;
        }
        return $params;
    }


} 