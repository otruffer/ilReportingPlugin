<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingExcelExport.php');

/**
 * Class ilReportingCoursesPerUserExcelExport
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */

class ilReportingCoursesPerUserExcelExport extends ilReportingExcelExport {

    public function __construct($filename = '') {
        parent::__construct($filename);
    }

    /**
     * Generate export
     * @param array $data
     */
    public function execute(array $data) {
        // Format data array to separate multiple Users by worksheet
        // array(user1 => array(course1, course2, course3), user2 => array(course1, course2...))
        $data = $this->formatData($data);
        ob_start();
        foreach ($data as $users) {
            $name = $users[0]['firstname'] . ' ' . $users[0]['lastname'];
            $worksheet = $this->workbook->addWorksheet();
            // Write in worksheet
            $row = 0;
            $this->write($worksheet, $row, 0, $name, $this->h1);
            $row++;
            $this->write($worksheet, ++$row, 0, $this->pl->txt('country'));
            $this->write($worksheet, $row, 1, $users[0]['country']);
            $this->write($worksheet, ++$row, 0, $this->pl->txt('department'));
            $this->write($worksheet, $row, 1, $users[0]['department']);
            $this->write($worksheet, ++$row, 0, $this->pl->txt('date_of_report'));
            $this->write($worksheet, $row, 1, date(self::DATE_FORMAT));
            $this->write($worksheet, ++$row, 0, $this->pl->txt('owner_of_report'));
            $this->write($worksheet, $row, 1, $this->user->getFirstname() . ' ' . $this->user->getLastname());
            $row+=2;
            // List courses: Title of columns
            $this->write($worksheet, $row, 0, $this->pl->txt('title'), $this->h3);
            $this->write($worksheet, $row, 1, $this->pl->txt('path'), $this->h3);
            $this->write($worksheet, $row, 2, $this->pl->txt('user_status'), $this->h3);
            $this->write($worksheet, $row, 3, $this->pl->txt('status_changed'), $this->h3);
            foreach ($users as $course) {
                $this->write($worksheet, ++$row, 0, $course['title']);
                $this->write($worksheet, $row, 1, $course['path']);
                $this->write($worksheet, $row, 2, $this->pl->txt('status'.$course['user_status']));
                $this->write($worksheet, $row, 3, date(self::DATE_FORMAT, strtotime($course['status_changed'])));
            }
            $this->autoSizeColumns($worksheet);
       }
        ob_end_clean();
        $this->workbook->close();
    }
}