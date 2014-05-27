<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once('./Services/Form/classes/class.ilCheckboxInputGUI.php');
require_once('./Services/Form/classes/class.ilSelectInputGUI.php');
require_once('./Services/Form/classes/class.ilDateTimeInputGUI.php');


/**
 * TableGUI ilReportingSearchTableGUI
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
abstract class ilReportingSearchTableGUI extends ilTable2GUI {

    protected $filter_names = array();

    /** @var \ilReportingPlugin  */
    protected $pl;

    /** @var \ilCtrl  */
    protected $ctrl;

    /** @var \ilToolbarGUI  */
    protected $toolbar;

    protected $filter_cmd = 'applyFilterSearch';
    protected $reset_cmd = 'resetFilterSearch';

	/**
	 * @param ilReportingGUI $a_parent_obj
	 * @param string               $a_parent_cmd
	 */
	function __construct(ilReportingGUI $a_parent_obj, $a_parent_cmd) {
        global $ilCtrl, $ilToolbar;
        $this->pl = new ilReportingPlugin();
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
//        $this->setId((string) $a_parent_obj);
        $this->setId($this->ctrl->getCmdClass());
        $this->setPrefix('pre');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setRowTemplate('tpl.template_row.checkboxes.html', $this->pl->getDirectory());
        $this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
        $this->setShowRowsSelector(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->initFilter();
        $this->setDisableFilterHiding(true);
        // Setup columns
        $this->addColumn("", "", "1", true);
        $this->setSelectAllCheckbox("id[]");
        foreach ($this->getSelectableColumns() as $k => $v) {
			if ($this->isColumnSelected($k)) {
                $this->addColumn($v['txt'], $k, 'auto');
			}
		}
	}

    /**
	 * @param      $item
	 * @param bool $optional
	 */
	protected function addFilterItemWithValue($item, $optional = false) {
		/**
		 * @var $item ilSelectInputGUI
		 */
		$this->addFilterItem($item, $optional);
		$item->readFromSession();
		switch (get_class($item)) {
			case 'ilSelectInputGUI':
				$value = $item->getValue();
				break;
			case 'ilCheckboxInputGUI':
				$value = $item->getChecked();
				break;
            case 'ilDateTimeInputGUI':
                $value = $item->getDate();
                break;
			default:
				$value = $item->getValue();
				break;
		}
		$this->filter_names[$item->getPostVar()] = $value;
	}


    public function getFilterNames() {
        return $this->filter_names;
    }

	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$this->tpl->setVariable('ID', $a_set['id']);
		foreach ($this->getSelectableColumns() as $k => $v) {
			if ($this->isColumnSelected($k)) {
				if ($a_set[$k]) {
					$this->tpl->setCurrentBlock('td');
					$this->tpl->setVariable('VALUE', (is_array($a_set[$k]) ? implode(", ", $a_set[$k]) : $a_set[$k]));
					$this->tpl->parseCurrentBlock();
				} else {
					$this->tpl->setCurrentBlock('td');
					$this->tpl->setVariable('VALUE', '&nbsp;');
					$this->tpl->parseCurrentBlock();
				}
			}
		}
	}


}

?>