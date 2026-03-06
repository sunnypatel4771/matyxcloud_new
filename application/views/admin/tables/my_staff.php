<?php

defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = staff_can('delete',  'staff');

$custom_fields = get_custom_fields('staff', [
    'show_on_table' => 1,
]);

$aColumns = [
    '1', // bulk actions
    'firstname',
    db_prefix() . 'staff.email',
    db_prefix() . 'roles.name',
    '(
        SELECT GROUP_CONCAT(d2.name SEPARATOR ", ")
        FROM ' . db_prefix() . 'staff_departments sd2
        JOIN ' . db_prefix() . 'departments d2 
        ON d2.departmentid = sd2.departmentid
        WHERE sd2.staffid = ' . db_prefix() . 'staff.staffid
    ) as departments',
    'last_login',
    'active',
];
$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';
$join         = ['LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role'];
// $join[] = 'LEFT JOIN ' . db_prefix() . 'staff_departments sd ON sd.staffid = ' . db_prefix() . 'staff.staffid';
// $join[] = 'LEFT JOIN ' . db_prefix() . 'departments d ON d.departmentid = sd.departmentid';
$i            = 0;
foreach ($custom_fields as $field) {
    $select_as = 'cvalue_' . $i;
    if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
        $select_as = 'date_picker_cvalue_' . $i;
    }
    array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $i . ' ON ' . db_prefix() . 'staff.staffid = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
    $i++;
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$where = hooks()->apply_filters('staff_table_sql_where', []);

$role_filter = $this->ci->input->post('role_filter');
if ($role_filter && $role_filter != '') {
    $where[] = 'AND ' . db_prefix() . 'roles.roleid = ' . $role_filter;
}

$status_filter = $this->ci->input->post('status_filter');
if ($status_filter != '') {
    $where[] = 'AND ' . db_prefix() . 'staff.active = ' . $status_filter;
}

$cus_roles_filter = $this->ci->input->post('cus_roles_filter');
// if ($cus_roles_filter != '') {
//     $where[] = 'AND ctable_0.value = "' . $cus_roles_filter . '"';
// }

if ($cus_roles_filter != '') {
    $this->ci->db->escape_like_str($cus_roles_filter);
    $where[] = 'AND ctable_0.value LIKE "%' . $cus_roles_filter . '%"';
}

$department_filter = $this->ci->input->post('department_filter');
if ($department_filter && $department_filter != '') {
    $where[] = 'AND EXISTS (
        SELECT 1 
        FROM ' . db_prefix() . 'staff_departments sd_filter
        WHERE sd_filter.staffid = ' . db_prefix() . 'staff.staffid
        AND sd_filter.departmentid = ' . (int)$department_filter . '
    )';
}

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where,
    [
        'profile_image',
        'lastname',
        db_prefix() . 'staff.staffid',
    ],
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }

        if ($aColumns[$i] === '1') {
            $_data = '<div class="checkbox">
                        <input type="checkbox" class="row-select" value="' . $aRow['staffid'] . '">
                        <label></label>
                      </div>';
        }
        if ($aColumns[$i] == 'last_login') {
            if ($_data != null) {
                $_data = '<span class="text-has-action is-date" data-toggle="tooltip" data-title="' . e(_dt($_data)) . '">' . time_ago($_data) . '</span>';
            } else {
                $_data = 'Never';
            }
        } elseif ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }

            $_data = '<div class="onoffswitch">
                <input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || staff_cant('edit', 'staff')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
        } elseif ($aColumns[$i] == 'firstname') {
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], [
                'staff-profile-image-small',
            ]) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . e($aRow['firstname'] . ' ' . $aRow['lastname']) . '</a>';

            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('view') . '</a>';

            if (($has_permission_delete && ($has_permission_delete && !is_admin($aRow['staffid']))) || is_admin()) {
                if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
                    $_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
                }
            }

            $_data .= '</div>';
        } elseif (strpos($aColumns[$i], 'departments') !== false) {
            $_data = $_data ? $_data : '-';
        } elseif ($aColumns[$i] == 'email') {
            $_data = '<a href="mailto:' . e($_data) . '">' . e($_data) . '</a>';
        } else {
            if (strpos($aColumns[$i], 'date_picker_') !== false) {
                $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
            }
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('staff_table_row', $row, $aRow);

    $output['aaData'][] = $row;
}
