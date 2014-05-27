<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingReportTableGUI.php');
require_once('class.ilReportingUsersPerCourseLPPdfExport.php');
require_once('class.ilReportingUsersPerCourseLPExcelExport.php');

/**
 * Report table for the report CoursesPerUser
 *
 * @author            Stefan Wanzenried <sw@studer-raimann.ch>
 * @version           $Id:
 */

class ilReportingUsersPerCourseLPReportTableGUI extends ilReportingReportTableGUI {


    /**
     * Additional columns showing learning progress of objects inside course
     * @var array
     */
    protected $additionalColumns = array(
        'object_title', 'object_percentage', 'object_status', 'object_type', 'object_status_changed'
    );


    public function __construct($a_parent_obj, $a_parent_cmd) {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setRowTemplate('tpl.template_row.colspan.html', $this->pl->getDirectory());
        $this->ignored_cols = array_merge($this->ignored_cols, array('sort_user', '_objects'));
    }


    public function exportDataCustom($format, $send = false) {
        switch ($format) {
            case ilReportingGUI::EXPORT_PDF:
                $export = new ilReportingUsersPerCourseLPPdfExport();
                $export->execute();
                break;
            case ilReportingGUI::EXPORT_EXCEL_FORMATTED:
                $export = new ilReportingUsersPerCourseLPExcelExport('users_per_course_' . date('Y-m-d'));
                $export->execute($this->getData());
                break;
        }
    }


    /**
     * @param array $a_set
     */
    public function fillRow($a_set) {
        parent::fillRow($a_set);
        if (count($a_set['_objects'])) {
            $this->tpl->setCurrentBlock('colspan');
            $this->tpl->setVariable('N_COLUMNS', count($this->columns));
            $this->tpl->setVariable('ROW_DETAILS', $this->renderObjectsTable($a_set['_objects']));
            $this->tpl->parseCurrentBlock();
        }
    }


    /**
     * @param object $a_csv
     * @param array  $a_set
     */
    protected function fillRowCSV($a_csv, $a_set) {
        parent::fillRowCSV($a_csv, $a_set);
        // Display each object in course as row
        if (count($a_set['_objects'])) {
            foreach ($a_set['_objects'] as $object) {
                foreach ($this->columns as $v) $a_csv->addColumn('');
                foreach ($this->additionalColumns as $column) {
                    $a_csv->addColumn($object[$column]);
                }
                $a_csv->addRow();
            }
        }
    }

    /**
     * @param object $a_worksheet
     * @param int    $a_row
     * @param array  $a_set
     */
    protected function fillRowExcel($a_worksheet, &$a_row, $a_set) {
        parent::fillRowExcel($a_worksheet, $a_row, $a_set);
        // Display each object in course as row
        if (count($a_set['_objects'])) {
            foreach ($a_set['_objects'] as $object) {
                $col = count($this->columns);
                $a_row++;
                foreach ($this->additionalColumns as $column) {
                    $a_worksheet->writeString($a_row, $col, $object[$column]);
                    $col++;
                }
            }
        }
    }


    /**
     * Render a table displaying the objects in course
     *
     * @param array $objects
     * @return string
     */
    protected function renderObjectsTable(array $objects) {
        $table = $this->pl->getTemplate('tpl.objects_table.html', true, true);
        foreach ($this->additionalColumns as $k => $column) {
            $table->setVariable(strtoupper("TH_{$column}"), $this->pl->txt($column));
        }
        foreach ($objects as $object) {
            $table->setCurrentBlock('rows');
            foreach ($this->additionalColumns as $column) {
                switch ($column) {
                    case 'object_type':
                        $value = ($object[$column] == "") ? "" : $this->lng->txt($object[$column]);
                        break;
                    case 'object_status':
                        $value = ($object[$column] == "") ? "" : $this->pl->txt("status" . $object[$column]);
                        break;
                    case 'object_percentage':
                        $value = (int)$object[$column] . "%";
                        break;
                    case 'object_status_changed':
                        $value = (!is_null($object[$column])) ? date($this->date_format, strtotime($object[$column])) : "";
                        break;
                    default:
                        $value = $object[$column];

                }
                $table->setVariable(strtoupper($column), $value);
            }
            $table->parseCurrentBlock();
        }
        return $table->get();
    }

    /**
     * CSV Version of Fill Header. Likely to
     * be overwritten by derived class.
     *
     * @param   object  $a_csv          current file
     */
    protected function fillHeaderCSV($a_csv) {
        $columns = array_merge($this->columns, $this->additionalColumns);
        foreach ($columns as $column) {
            $a_csv->addColumn($column);
        }
        $a_csv->addRow();
    }

    /**
     * Excel Version of Fill Row. Likely to
     * be overwritten by derived class.
     *
     * @param $worksheet
     * @param   int $a_row row counter
     * @internal param object $a_worksheet current sheet
     */
    protected function fillHeaderExcel($worksheet, &$a_row) {
        $col = 0;
        $columns = array_merge($this->columns, $this->additionalColumns);
        foreach ($columns as $column) {
            $worksheet->write($a_row, $col, $column);
            $col++;
        }
        $a_row++;
    }

    /**
     * Alter back link when coming from ilreportinguserspercoursegui
     */
    protected function initToolbar() {
        if (isset($_GET['from'])) {
            $class = $_GET['from'];
            $url = $this->ctrl->getLinkTargetByClass($class, 'report');
            $txt = $this->pl->txt('back');
            $this->toolbar->addButton('<b>&lt; ' .$txt. '</b>', $url);
        } else {
            parent::initToolbar();
        }
    }

}