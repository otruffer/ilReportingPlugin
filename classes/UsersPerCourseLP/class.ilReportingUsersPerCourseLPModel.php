<?php
require_once(dirname(dirname(__FILE__)) . '/class.ilReportingModel.php');
require_once(dirname(dirname(__FILE__)) . '/UsersPerCourse/class.ilReportingUsersPerCourseModel.php');


/**
 * Class ilReportingUsersPerCourserLPModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilReportingUsersPerCourseLPModel extends ilReportingModel {

    protected $modelUsersPerCourse;

    public function __construct() {
        parent::__construct();
        $this->modelUsersPerCourse = new ilReportingUsersPerCourseModel();
	    $this->pl = new ilReportingPlugin();
    }

    /**
     * Search users
     * @param array $filters
     * @return array
     */
    public function getSearchData(array $filters) {
        return $this->modelUsersPerCourse->getSearchData($filters);
    }

    public function getReportData(array $ids, array $filters) {
	    $sql  = "SELECT * FROM (
	             SELECT DISTINCT CONCAT_WS('_', rbac_ua.usr_id, rbac_ua.rol_id) AS unique_id, CONCAT_WS('_',obj.title,obj.obj_id) AS sort_user,
	             obj.obj_id AS id, obj.title, CONCAT_WS(' > ', gp.title, p.title) AS path, ref.ref_id, obj.obj_id,
	             usr.firstname, usr.lastname, usr.country, usr.department, ut.status_changed, ut.status AS user_status,
	             children.title AS object_title, children_ut.percentage AS object_percentage, children_ut.status AS object_status, children.type AS object_type,
	             children_ut.status_changed AS object_status_changed
	             FROM object_data as obj
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

				 LEFT JOIN tree AS children_tree ON (children_tree.parent = ref.ref_id)
                 LEFT JOIN object_reference AS children_reference ON (children_reference.ref_id = children_tree.child)
                 LEFT JOIN object_data AS children ON (children_reference.obj_id = children.obj_id)
                 LEFT JOIN ut_lp_marks AS children_ut ON (children_ut.obj_id = children.obj_id AND children_ut.usr_id = usr.usr_id)
                 LEFT JOIN ut_lp_collections AS children_ut_coll ON (children_ut_coll.obj_id = obj.obj_id AND children_ut_coll.item_id = children_reference.ref_id)

                 WHERE obj.type = " . $this->db->quote('crs', 'text') . " AND ref.deleted IS NULL
                 AND children.type != 'rolf'
                 AND children_ut_coll.active = 1 ";

        $sql .= $this->buildWhereString($ids, $filters);

        $sql .= "UNION

                SELECT DISTINCT CONCAT_WS('_', rbac_ua.usr_id, rbac_ua.rol_id) AS unique_id, CONCAT_WS('_',obj.title,obj.obj_id) AS sort_user,
                obj.obj_id AS id, obj.title, CONCAT_WS(' > ', gp.title, p.title) AS path, ref.ref_id, obj.obj_id,
                usr.firstname, usr.lastname, usr.country, usr.department, ut.status_changed, ut.status AS user_status,
                NULL AS object_title, NULL AS object_percentage, NULL AS object_status, NULL AS object_type, NULL as object_status_changed
                FROM object_data as obj
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

        $sql .= $this->buildWhereString($ids, $filters);
        $sql .= ") AS a ORDER BY sort_user, lastname, firstname, object_title";

        $return = array();
        $objects = array();
        $data = $this->buildRecords($sql);
        foreach ($data as $k => $v) {
            if (is_null($v['object_title'])) {
                if ($k != 0)
                    $return[count($return)-1]['_objects'] = $objects;
                $return[] = $v;
                $objects = array();
            } else {
                $objects[] = array_slice($v, -5);
            }
        }
        $return[count($return)-1]['_objects'] = $objects;
//        echo $sql; die();
//        echo "<pre>" . print_r($return, 1) . "</pre>";die();
        return $return;
    }


    /**
     * Build WHERE part of query with filters
     *
     * @param array $ids
     * @param array $filters
     * @return string SQL
     */
    private function buildWhereString(array $ids, array $filters) {
        $sql = '';
        if (count($ids)) {
            $sql .= "AND obj.obj_id IN (" . implode(',', $ids) . ") ";
        }
        if ($this->pl->getConfigObject()->getValue('restricted_user_access') == ilReportingConfig::RESTRICTED_BY_LOCAL_READABILITY) {
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
        return $sql;
    }
}