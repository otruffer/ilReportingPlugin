<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('class.ilReportingGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Reporting/classes/class.ilReportingPlugin.php');

/**
 * User interface hook class for Reporting-Plugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilReportingUIHookGUI extends ilUIHookPluginGUI {

    /** @var  ilCtrl */
    protected $ctrl;

    /** @var  ilTabsGUI */
    protected $tabs;

    /** @var  ilReportingPlugin */
    protected $pl;

	/** @var  ilAccessHandler */
	protected $access;

	function __construct() {
		global $ilCtrl, $ilTabs, $tpl, $ilAccess;
		$this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->pl = new ilReportingPlugin();
		$this->access = $ilAccess;
        // Display error message in Administration if precondition is not valid
        if (!ilReportingPlugin::checkPreconditions() AND $_GET['ref_id'] == 9) {
            if(get_class($tpl) == 'ilTemplate') {
                ilUtil::sendFailure('Reporting plugin needs CtrlMainMenu (https://svn.ilias.de/svn/ilias/branches/sr/CtrlMainMenu) and ilRouterGUI (https://svn.ilias.de/svn/ilias/branches/sr/Router)');
            }
        }
    }

    /**
     * Add a new tab 'reports' for courses which shows the report table for all the members of a course
     */
    public function modifyGUI($a_comp, $a_part, $a_par = array()) {
        if ($a_part == 'tabs') {
            if ($this->ctrl->getContextObjType() == 'crs') {
                $crsID = $this->ctrl->getContextObjId();
                if ($crsID) {
					$arr_refId = ilObject2::_getAllReferences($crsID);
	                //check Permission "edit learning progress"
	                $refId = array_values($arr_refId);
                    if ($this->access->checkAccess("edit_learning_progress", "", $refId[0],"",$crsID)) {
		                $this->ctrl->setParameterByClass('ilReportingUsersPerCourseGUI', 'rep_crs_ref_id', $refId[0]);
	                    $uri = $this->ctrl->getLinkTargetByClass(array('ilRouterGUI', 'ilReportingUsersPerCourseGUI'), 'report');
	                    // Write the correct course ID into the session - this is used by the report table
	                    $_SESSION[ilReportingGUI::SESSION_KEY_IDS] = array($crsID);
	                    /** @var  $ilTabsGUI */
	                    $ilTabsGUI = $a_par['tabs'];
		                $ilTabsGUI->addTarget($this->pl->txt('reports'), $uri, "reports", array('ilRouterGUI', 'ilReportingUsersPerCourseGUI'), "", false,true);
	                }
                }
            }
        }
    }
}

?>