<?php
include_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');
require_once('class.ilReportingConfig.php');

/**
 * Reporting Plugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilReportingPlugin extends ilUserInterfaceHookPlugin {

	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'Reporting';
	}

	/**
	 * @return ilReportingConfig
	 */
	public function getConfigObject() {
		return new ilReportingConfig($this->getConfigTableName());
	}

	/**
	 * @return string
	 */
	public function getConfigTableName() {
		return $this->getSlotId() . substr(strtolower($this->getPluginName()), 0, 20 - strlen($this->getSlotId())) . '_c';
	}

    /**
     * Only activate plugin if CtrlMainMenu and ilRouterGUI are installed and active
     *
     * @return bool|void
     * @throws ilPluginException
     */
    protected function beforeActivation() {
        if (!self::checkPreconditions()) {
            ilUtil::sendFailure("Cannot activate plugin: Make sure that you have installed the CtrlMainMenu plugin and RouterGUI. Please read the documentation: http://www.ilias.de/docu/goto_docu_wiki_1357_Reporting_Plugin.html", true);
            //throw new ilPluginException("Cannot activate plugin: Make sure that you have installed the CtrlMainMenu plugin and RouterGUI. Please read the documentation: http://www.ilias.de/docu/goto_docu_wiki_1357_Reporting_Plugin.html");
            return false;
        }
        return true;
    }

    /**
     * Create the MainMenu entries if not yet existing
     *
     */
    protected function afterActivation() {
        self::createReportMainMenuEntries();
    }

    public static function getRootDir() {
        return dirname(dirname(__FILE__)) . '/';
    }

    /**
     * Check if CtrlMainMenu Plugin and ilRouter are installed and active
     * @return bool
     */
    public static function checkPreconditions() {
        /**
         * @var $ilCtrl ilCtrl
         * @var $ilPluginAdmin ilPluginAdmin
         */
        global $ilCtrl, $ilPluginAdmin;
        $existCtrlMainMenu = $ilPluginAdmin->exists(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'CtrlMainMenu');
        $isActiveCtrlMainMenu = $ilPluginAdmin->isActive(IL_COMP_SERVICE, 'UIComponent', 'uihk', 'CtrlMainMenu');
        $existRouterGUI = (!is_null($ilCtrl->lookupClassPath('ilRouterGUI')));
        return ($existCtrlMainMenu && $isActiveCtrlMainMenu && $existRouterGUI);
    }

    /**
     * Create the main menu entries of the reporting plugin.
     * Abort if they were already created before...
     *
     */
    public static function createReportMainMenuEntries() {
        require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenu.php');
        require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');

        ctrlmmEntry::includeAllEntryTypes();

        $entries_cpu = ctrlmmEntry::getEntriesByCmdClass('ilReportingCoursesPerUserGUI');
        $entries_upc = ctrlmmEntry::getEntriesByCmdClass('ilReportingUsersPerCourseGUI');
        $entries_upt = ctrlmmEntry::getEntriesByCmdClass('ilReportingUsersPerTestGUI');
        $entries_cpu_lp = ctrlmmEntry::getEntriesByCmdClass('ilReportingCoursesPerUserLPGUI');
        $entries_upc_lp = ctrlmmEntry::getEntriesByCmdClass('ilReportingUsersPerCourseLPGUI');

        $main_reports_created = false;
        $additional_reports_created = false;

        if (count($entries_cpu) || count($entries_upc) || count($entries_upt))
            $main_reports_created = true;

        if (count($entries_cpu_lp) || count($entries_upc_lp))
            $additional_reports_created = true;

        global $ilUser;
        $langUser = $ilUser->getLanguage();

        if (!$main_reports_created) {
            $dropdown = new ctrlmmEntryDropdown();
            $trans = array("$langUser" => 'Reports');
            $trans = array_merge($trans, array('de' => 'Reports', 'en' => 'Reports'));
            $dropdown->setTranslations($trans);
            $dropdown->setUseImage(true);
            $dropdown->create();

            $trans = array("$langUser" => 'Courses per User');
            $trans = array_merge($trans, array('de' => 'Kurse pro Benutzer', 'en' => 'Courses per User'));
            self::createMainMenuEntry($trans, 'ilRouterGUI,ilReportingCoursesPerUserGUI', $dropdown->getId());

            $trans = array("$langUser" => 'Users per Course');
            $trans = array_merge($trans, array('de' => 'Benutzer pro Kurs', 'en' => 'Users per Course'));
            self::createMainMenuEntry($trans, 'ilRouterGUI,ilReportingUsersPerCourseGUI', $dropdown->getId());

            $trans = array("$langUser" => 'Users per Test');
            $trans = array_merge($trans, array('de' => 'Benutzer pro Test', 'en' => 'Users per Test'));
            self::createMainMenuEntry($trans, 'ilRouterGUI,ilReportingUsersPerTestGUI', $dropdown->getId());
        }

        if (!$additional_reports_created) {
            // Find dropdown ID
	        $entries_cpu = ctrlmmEntry::getEntriesByCmdClass('ilReportingCoursesPerUserGUI');
            /** @var ilCtrlMainMenuEntry $entry */
            $entry = $entries_cpu[0];
            $dropdown_id = $entry->getParent();

            $trans = array("langUser" => 'Courses per User, detailed');
            $trans = array_merge($trans, array('de' => 'Kurse pro Benuzter, detailliert', 'en' => 'Courses per User, detailed'));
            self::createMainMenuEntry($trans, 'ilRouterGUI,ilReportingCoursesPerUserLPGUI', $dropdown_id);

            $trans = array("langUser" => 'Users per Course, detailed');
            $trans = array_merge($trans, array('de' => 'Benutzer pro Kurs, detailliert', 'en' => 'Users per Course, detailed'));
            self::createMainMenuEntry($trans, 'ilRouterGUI,ilReportingUsersPerCourseLPGUI', $dropdown_id);
        }
    }

    /**
     * Create a Report Entry in MainMenu
     *
     * @param array $trans
     * @param $gui_class
     * @param $dropdown_id
     */
    private static function createMainMenuEntry(array $trans, $gui_class, $dropdown_id) {
        $entry = new ctrlmmEntryCtrl();
        $entry->setTranslations($trans);
        $entry->setGuiClass($gui_class);
        $entry->setCmd('search');
        $entry->setPermissionType(ctrlmmMenu::PERM_ROLE);
        $entry->setPermission('["2"]');
        $entry->setParent($dropdown_id);
        $entry->create();
    }

	/**
	 * @return int[] All users that the current user has access to concerning the permissions view_learning_progress[_rec]
	 */
	public function getRestrictedByOrgUnitsUsers(){
		require_once ('./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php');
		$orgunits = ilObjOrgUnitTree::_getInstance();
		$users = array();

		//get the users that are directly accessible
		$orgus = $orgunits->getOrgusWhereUserHasPermissionForOperation('view_learning_progress');
		foreach ($orgus as $orgu)
			$users = array_merge($users, $orgunits->getEmployees($orgu, false));

		//get users that are recursivly accessible.
		$orgus = $orgunits->getOrgusWhereUserHasPermissionForOperation('view_learning_progress_rec');
		foreach ($orgus as $orgu)
			$users = array_merge($users, $orgunits->getEmployees($orgu, true));

		return $users;
	}
}

?>
