<?php
require_once('./Services/Calendar/classes/class.ilDateTime.php');

/**
 * Class ilReportingModel: Each report must implement its own model which extends this class.
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
abstract class ilReportingModel {

    /** @var  ilDB */
    protected $db;

    /** @var \ilReportingPlugin  */
    protected $pl;

    /** @var  ilObjUser */
    protected $user;

    /** @var  ilAccess */
    protected $access;

    public function __construct() {
        global $ilDB, $ilUser, $ilAccess;
        $this->db = $ilDB;
        $this->pl = new ilReportingPlugin();
        $this->user = $ilUser;
        $this->access = $ilAccess;
    }

    abstract public function getSearchData(array $filters);

    abstract public function getReportData(array $ids, array $filters);

    /**
     * Return all the ref-ids (of Categories) where the current user can administrate users
     * @return array
     */
    protected function getRefIdsWhereUserCanAdministrateUsers() {
        $sql = 'SELECT DISTINCT time_limit_owner FROM usr_data';
        $set = $this->db->query($sql);
        $refIds = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $refIds[] = $rec['time_limit_owner'];
        }
        foreach ($refIds as $k => $refId) {
            if (!$this->access->checkAccess('cat_administrate_users', '', $refId)) {
                unset($refIds[$k]);
            }
        }
        return $refIds;
    }

    /**
     * Build records from SQL Query string
     * @param $sql SQL String
     * @return array
     */
    protected function buildRecords($sql) {
        $result = $this->db->query($sql);
        $return = array();
        while ($rec = $this->db->fetchAssoc($result)) {
            $return[] = $rec;
        }
        return $return;
    }
} 