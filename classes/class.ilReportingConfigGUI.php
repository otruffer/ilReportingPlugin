<?php
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('class.ilReportingConfig.php');

/**
 * Reporting Configuration
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilReportingConfigGUI extends ilPluginConfigGUI {

	/** @var \ilReportingConfig  */
    protected $object;

    /** @var array  */
    protected $fields = array();

	/** @var string  */
    protected $table_name = '';


	function __construct() {
		global $ilCtrl, $tpl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 * @var $ilTabs ilTabsGUI
		 */
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->pl = new ilReportingPlugin();
		$this->object = new ilReportingConfig($this->pl->getConfigTableName());
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @return ilReportingConfig
	 */
	public function getObject() {
		return $this->object;
	}


	/**
	 * Handles all commmands, default is 'configure'
	 */
	public function performCommand($cmd) {
		switch ($cmd) {
			case 'configure':
			case 'save':
			case 'svn':
				$this->$cmd();
				break;
		}
	}

	/**
	 * Configure screen
	 */
	public function configure() {
		$this->initConfigurationForm();
		$this->setFormValues();
		$this->tpl->setContent($this->form->getHTML());
	}

    /**
     * Save config
     */
    public function save() {
        global $tpl, $ilCtrl;
        $this->initConfigurationForm();
        if ($this->form->checkInput()) {
            foreach ($this->getFields() as $key => $item) {
                $this->object->setValue($key, $this->form->getInput($key));
                if (is_array($item['subelements'])) {
                    foreach ($item['subelements'] as $subkey => $subitem) {
                        $this->object->setValue($key . '_' . $subkey, $this->form->getInput($key . '_' . $subkey));
                    }
                }
            }
            ilUtil::sendSuccess($this->pl->txt('conf_saved'), true);
            $ilCtrl->redirect($this, 'configure');
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    /**
     * Set form values
     */
    protected function setFormValues() {
        foreach ($this->getFields() as $key => $item) {
            $values[$key] = $this->object->getValue($key);
            if (is_array($item['subelements'])) {
                foreach ($item['subelements'] as $subkey => $subitem) {
                    $values[$key . '_' . $subkey] = $this->object->getValue($key . '_' . $subkey);
                }
            }
        }
        $this->form->setValuesByArray($values);
    }

    /**
	 * @return ilPropertyFormGUI
	 */
	protected function initConfigurationForm() {
		global $lng, $ilCtrl;
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		foreach ($this->getFields() as $key => $item) {
			$field = new $item['type']($this->pl->txt($key), $key);
			if ($item['info']) {
				$field->setInfo($this->pl->txt($key . '_info'));
			}
			if (is_array($item['subelements'])) {
				foreach ($item['subelements'] as $subkey => $subitem) {
					$subfield = new $subitem['type']($this->pl->txt($key . '_' . $subkey), $key . '_' . $subkey);
					if ($subitem['info']) {
						$subfield->setInfo($this->pl->txt($key . '_info'));
					}
					$field->addSubItem($subfield);
				}
			}
			$this->form->addItem($field);
		}
		$this->form->addCommandButton('save', $lng->txt('save'));
		$this->form->setTitle($this->pl->txt('configuration'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		return $this->form;
	}

    /**
     * Return the configuration fields
     * @return array
     */
    protected function getFields() {
        $this->fields = array(
            'restrict_user_access' => array(
                'type' => 'ilCheckboxInputGUI',
                'info' => true,
            ),
            'jasper_reports_templates_path' => array(
                'type' => 'ilTextInputGUI',
                'info' => true,
            ),
            'header_image' => array(
                'type' => 'ilTextInputGUI',
				'info' => true,
            ),
        );
        return $this->fields;
    }

}
?>
