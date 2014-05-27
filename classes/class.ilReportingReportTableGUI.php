<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ilReportingGUI.php');
require_once('./Services/Object/classes/class.ilObject2.php');

/**
 * TableGUI ilReportingReportTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 */
abstract class ilReportingReportTableGUI extends ilTable2GUI {

	/** @var array  */
    protected $all_columns = array();

	/** @var array  */
    protected $ignored_cols = array();

    /** @var array  */
    protected $date_cols = array();

    /** @var string  */
    protected $date_format = 'd.M Y, H:i';

    /** @var \ilReportingPlugin  */
    protected $pl;

    /** @var \ilToolbarGUI  */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /** @var \ilCtrl  */
    protected $ctrl;

    protected $filter_cmd = 'applyFilterReport';
    protected $reset_cmd = 'resetFilterReport';
    protected $filter_names = array();

    /** @var array Columns to display in this table */
    protected $columns = array(
        'title', 'path', 'firstname', 'lastname', 'country', 'department', 'status_changed', 'user_status',
    );

    /**
	 * @param ilReportingGUI $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	function __construct(ilReportingGUI $a_parent_obj, $a_parent_cmd) {
		global $ilCtrl, $ilToolbar, $lng;
		$this->pl = new ilReportingPlugin();
		$this->toolbar = $ilToolbar;
        $this->lng = $lng;
		$this->ctrl = $ilCtrl;
        $this->setId('reporting_' . $this->ctrl->getCmdClass());
        $this->setPrefix('pre');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setIgnoredCols(array('id', 'unique_id', 'obj_id', 'ref_id'));
        $this->setDateCols(array('status_changed'));
        $this->setRowTemplate('tpl.template_row.html', $this->pl->getDirectory());
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setShowRowsSelector(true);
        $this->initColumns();
        $this->initToolbar();
        $this->parent_object = $a_parent_obj;
        $this->setExportFormats();
        $this->setDisableFilterHiding(true);
        $this->initFilter();
    }

    /**
     * @return bool
     */
    public function numericOrdering() {
        return true;
    }

    /**
     * @param array $formats
     */
    public function setExportFormats() {
        parent::setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));
        foreach ($this->parent_object->getAvailableExports() as $k => $format) {
            $this->export_formats[$k] = $this->pl->getPrefix() . '_' . $format;
        }
    }

    /**
     * Add filters for status and status changed
     */
    public function initFilter() {
        $item = new ilSelectInputGUI($this->pl->txt('user_status'), 'status');
        $states = array('' => '');
        for ($i=1;$i<=4;$i++) {
            $k = $i-1;
            $states[$i] = $this->pl->txt("status$k");
        }
        $item->setOptions($states);
        $this->addFilterItemWithValue($item);
        $item = new ilDateTimeInputGUI($this->pl->txt('status_changed_from'), 'status_changed_from');
        $item->setMode(ilDateTimeInputGUI::MODE_INPUT);
        $this->addFilterItemWithValue($item);
        $item = new ilDateTimeInputGUI($this->pl->txt('status_changed_to'), 'status_changed_to');
        $item->setMode(ilDateTimeInputGUI::MODE_INPUT);
        $this->addFilterItemWithValue($item);
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set) {
        $this->tpl->setVariable('ID', $a_set['id']);
        foreach ($this->columns as $k) {
            if (isset($a_set[$k])) {
                if (! in_array($k, $this->getIgnoredCols())) {
                    if (in_array($k, $this->getDateCols())) {
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', date($this->getDateFormat(), strtotime($a_set[$k])));
                        $this->tpl->parseCurrentBlock();
                    } else {
                        $this->tpl->setCurrentBlock('td');
                        if ($k == 'user_status') {
                            $this->tpl->setVariable('VALUE', $this->pl->txt("status{$a_set[$k]}"));
                        } else {
                            $this->tpl->setVariable('VALUE', $a_set[$k]);
                        }
                        $this->tpl->parseCurrentBlock();
                    }
                }
            } else {
                $this->tpl->setCurrentBlock('td');
                $this->tpl->setVariable('VALUE', '&nbsp;');
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    /**
     * Method each subclass must implement to handle custom exports
     * @param $format Format constant from ilReportingGUI EXPORT_EXCEL_FORMATTED|EXPORT_PDF
     * @param bool $send
     */
    abstract public function exportDataCustom($format, $send = false);

	/**
     * Apply custom report downloads
	 * @param int  $format
	 * @param bool $send
	 */
	public function exportData($format, $send = false) {
        if (in_array($format, array_keys($this->parent_object->getAvailableExports()))) {
            $this->exportDataCustom($format, $send);
		} else {
			parent::exportData($format, $send);
		}
	}

    /**
     * Init toolbar containing a back link
     * Determine if the user has clicked on the report tab in a course ($_GET['rep_crs_ref_id'] is set)
     * or the back link should go to the parent's search form...
     */
    protected function initToolbar() {
        if (isset($_GET['rep_crs_ref_id'])) {
            $this->ctrl->setParameterByClass('ilObjCourseGUI', 'ref_id', $_GET['rep_crs_ref_id']);
            $url = $this->ctrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjCourseGUI'));
            $txt = $this->pl->txt('back_to_course');
        } else {
            $url = $this->ctrl->getLinkTarget($this->parent_obj);
            $txt = $this->pl->txt('back');
        }
        $this->toolbar->addButton('<b>&lt; ' .$txt. '</b>', $url);
    }


    /**
     * Setup columns based on data
     */
    protected function initColumns() {
        foreach ($this->columns as $column) {
            $this->addColumn($this->pl->txt($column), $column, 'auto');
        }
    }

	/**
	 * @param object $a_worksheet
	 * @param int    $a_row
	 * @param array  $a_set
	 */
	protected function fillRowExcel($a_worksheet, &$a_row, $a_set) {
		$col = 0;
		foreach ($a_set as $key => $value) {
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
            if ($key == 'user_status' || ($key == 'object_status' && $value)) {
                $value = $this->pl->txt("status{$value}");
            }
			if (! in_array($key, $this->getIgnoredCols())) {
				$a_worksheet->writeString($a_row, $col, strip_tags($value));
				$col ++;
			}
		}
	}


	/**
	 * @param object $a_csv
	 * @param array  $a_set
	 */
	protected function fillRowCSV($a_csv, $a_set) {
		foreach ($a_set as $key => $value) {
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
            if ($key == 'user_status' || ($key == 'object_status' && $value)) {
                $value = $this->pl->txt("status{$value}");
            }
			if (! in_array($key, $this->getIgnoredCols())) {
				$a_csv->addColumn(strip_tags($value));
			}
		}
		$a_csv->addRow();
	}

    /**
     * @param      $item
     * @param bool $optional
     */
    protected function addFilterItemWithValue($item, $optional = false) {
        $this->addFilterItem($item, $optional);
        $value = $this->getFilterItemValue($item);
        $this->filter_names[$item->getPostVar()] = $value;
    }

    /**
     * Return value of a filter depending on the InputGUI class
     * @param $item
     * @return bool|object|string
     */
    protected function getFilterItemValue($item) {
        $value = '';
        $item->readFromSession();
        switch (get_class($item)) {
            case 'ilSelectInputGUI':
                /** @var $item ilSelectInputGUI */
                $value = $item->getValue();
                break;
            case 'ilCheckboxInputGUI':
                /** @var $item ilCheckboxInputGUI */
                $value = $item->getChecked();
                break;
            case 'ilDateTimeInputGUI':
                /** @var $item ilDateTimeInputGUI */
                // Why is this necessary? Bug? ilDateTimeInputGUI::clearFromSession() has no effect...
                if ($this->ctrl->getCmd() == 'resetFilterReport') {
                    $item->setDate(null);
                }
                $date = $item->getDate();
                if ($date) {
                    $value = $date;
                }
                break;
            default:
                $value = $item->getValue();
                break;
        }
        return $value;
    }

    /******************************************************
     * Getters & Setters
     ******************************************************/

    /**
     * Get all the filter with the current value from session
     * @return array
     */
    public function getFilterNames() {
        foreach ($this->getFilterItems() as $item) {
            $this->filter_names[$item->getPostVar()] = $this->getFilterItemValue($item);
        }
        return $this->filter_names;
    }

	/**
	 * @param array $all_columns
	 */
	public function setAllColumns($all_columns) {
		$this->all_columns = $all_columns;
	}


	/**
	 * @return array
	 */
	public function getAllColumns() {
		return $this->all_columns;
	}


	/**
	 * @param array $ignored_cols
	 */
	public function setIgnoredCols($ignored_cols) {
		$this->ignored_cols = $ignored_cols;
	}


	/**
	 * @return array
	 */
	public function getIgnoredCols() {
		return $this->ignored_cols;
	}


	/**
	 * @param array $date_cols
	 */
	public function setDateCols($date_cols) {
		$this->date_cols = $date_cols;
	}


	/**
	 * @return array
	 */
	public function getDateCols() {
		return $this->date_cols;
	}


	/**
	 * @param string $date_format
	 */
	public function setDateFormat($date_format) {
		$this->date_format = $date_format;
	}


	/**
	 * @return string
	 */
	public function getDateFormat() {
		return $this->date_format;
	}

}

?>