<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingModel.php');

/**
 * Class ilReportingUsersPerCourseModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilReportingUsersPerCourseModel extends ilReportingModel {

    public function __construct() {
        parent::__construct();
	    $this->pl = new ilReportingPlugin();
    }

    /**
     * Search courses
     * @param array $filters
     * @return array
     */
    public function getSearchData(array $filters) {
        $sql = "SELECT obj.obj_id AS id, obj.*, CONCAT_WS(' > ', gp.title, p.title) AS path FROM object_data AS obj
                INNER JOIN object_reference AS ref1 ON (ref1.obj_id = obj.obj_id)
                INNER JOIN tree AS t1 ON (ref1.ref_id = t1.child)
                INNER JOIN object_reference ref2 ON (ref2.ref_id = t1.parent)
                INNER JOIN object_data AS p ON (ref2.obj_id = p.obj_id)
                LEFT JOIN tree AS t2 ON (ref2.ref_id = t2.child)
                LEFT JOIN object_reference AS ref3 ON (ref3.ref_id = t2.parent)
                LEFT JOIN object_data AS gp ON (ref3.obj_id = gp.obj_id)
                WHERE obj.type = " . $this->db->quote('crs', 'text') . "
                AND obj.title LIKE " . $this->db->quote('%' . str_replace('*', '%', $filters['title']) . '%', 'text') ."
                AND ref1.deleted IS NULL
                ORDER BY obj.title";
        return $this->buildRecords($sql);
    }

    public function getReportData(array $ids, array $filters) {
        $sql  = "SELECT DISTINCT CONCAT_WS('_', rbac_ua.usr_id, rbac_ua.rol_id) AS unique_id, obj.obj_id AS id, obj.title, CONCAT_WS(' > ', gp.title, p.title) AS path,
                 usr.firstname, usr.lastname, usr.country, usr.department, ut.status_changed,
                 ut.status AS user_status FROM object_data as obj
				 INNER JOIN object_reference AS ref ON (ref.obj_id = obj.obj_id)
                 INNER JOIN object_data AS crs_member_role ON crs_member_role.title LIKE CONCAT('il_crs_member_', ref.ref_id)
				 INNER JOIN rbac_ua ON rbac_ua.rol_id = crs_member_role.obj_id
				 INNER JOIN usr_data AS usr ON (usr.usr_id = rbac_ua.usr_id)
                 INNER JOIN tree AS t1 ON (ref.ref_id = t1.child)
                 INNER JOIN object_reference ref2 ON (ref2.ref_id = t1.parent)
                 INNER JOIN object_data AS p ON (ref2.obj_id = p.obj_id)
                 LEFT JOIN tree AS t2 ON (ref2.ref_id = t2.child)
                 LEFT JOIN object_reference AS ref3 ON (ref3.ref_id = t2.parent)
                 LEFT JOIN object_data AS gp ON (ref3.obj_id = gp.obj_id)
                 LEFT JOIN ut_lp_marks AS ut ON (ut.obj_id = obj.obj_id AND ut.usr_id = usr.usr_id)
                 WHERE obj.type = " . $this->db->quote('crs', 'text') . " AND ref.deleted IS NULL ";
        if (count($ids)) {
            $sql .= "AND obj.obj_id IN (" . implode(',', $ids) . ") ";
        }
        $restrictAccess = $this->pl->getConfigObject()->getValue('restricted_user_access');
        // If coming from course, the user can see all the users from the course if he/she has 'edit_learning_progress' permission
        if ($restrictAccess == ilReportingConfig::RESTRICTED_BY_LOCAL_READABILITY && !isset($_GET['rep_crs_ref_id'])) {
            $refIds = $this->getRefIdsWhereUserCanAdministrateUsers();
            if (count($refIds)) {
                $sql .= ' AND usr.time_limit_owner IN (' . implode(',', $refIds) .')';
            } else {
                $sql .= 'AND usr.time_limit_owner IN (0)';
            }
        }elseif ($this->pl->getConfigObject()->getValue('restricted_user_access') == ilReportingConfig::RESTRICTED_BY_ORG_UNITS) {
	        //TODO: check if this is performant enough.
	        $users = $this->pl->modelCoursesPerUser->getRestrictedByOrgUnitsUsers();
	        $sql .= ' AND usr.usr_id IN('.implode(',', $users).')';
        }
        if (count($filters)) {
            if ($filters['status'] != '') {
                $sql .= ' AND ut.status = ' . $this->db->quote(($filters['status']-1), 'text');
            }
            if ($date = $filters['status_changed_from']) {
                $sql .= ' AND ut.status_changed >= ' . $this->db->quote($date, 'date');
            }
            if ($date = $filters['status_changed_to']) {
                /** @var $date ilDateTime */
                $date->increment(ilDateTime::DAY, 1);
                $sql .= ' AND ut.status_changed <= ' . $this->db->quote($date, 'date');
                $date->increment(ilDateTime::DAY, -1);
            }
        }
        $sql .= " ORDER BY obj.obj_id, usr.lastname, usr.firstname";
//        echo $sql;
        return $this->buildRecords($sql);
    }
}