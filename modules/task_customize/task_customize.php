<?php

use PhpOffice\PhpSpreadsheet\Writer\Xls\Xf;

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: System Customize
Description: Module provides facility change in Task bulk action, create contract from project .
Author: Sunny Patel
Version: 1.0.0
Requires at least: 2.3.*
Author URI: https://palladiumhub.com/
*/

define('TASK_CUSTOMIZE_MODULE_NAME', 'task_customize');
define('WORK_PLANNED', 45);
define('ASSIGN_STATUS', 8);
define('INTERNAL_REVIEW_STATUS', 3);

//project custom field id Services Included
define('PROJECT_SERVICES_INCLUDED', 27);

//project custom field id Priority 1
define('PROJECT_PRIORITY', 49);

//project custom field id priority 2
define('PROJECT_PRIORITY_2', 50);

// Status Note
define('PROJECT_STATUS_NOTE', 46);
define('VERSION_TASK_CUSTOMIZE', 1094);

define('PROJECT_COLUMN_PRIORITY', 11);
define('PROJECT_COLUMN_PRIORITY_2', 12);
define('PROJECT_LAUNCH_ETA', 67);
define('STOPLIGHT_REPORT', 92);
define('CAM_MEETING_DATE', 93);
define('STAFF_ROLES', 96);
define('TASK_TYPE' , 10);

hooks()->add_action('tasks_related_table_columns', 'tasks_related_table_columns');
hooks()->add_action('tasks_related_table_sql_columns', 'tasks_related_table_sql_columns');

hooks()->add_action('tasks_related_table_row_data', 'tasks_related_table_row_data', 10, 2);

hooks()->add_action('admin_init', 'task_customize_init_menu_items');
hooks()->add_action('task_status_changed', 'task_customize_task_status_changed', 10, 1);

hooks()->add_action('tasks_table_sql_columns', 'task_customize_tasks_table_sql_columns', 10, 1);
hooks()->add_action('after_add_task', 'task_customize_after_add_task', 10, 1);
hooks()->add_action('before_log_project_activity', 'task_customize_before_log_project_activity', 10, 1);

hooks()->add_action('tasks_table_columns', 'task_customize_tasks_table_columns', 10, 1);
// $row = hooks()->apply_filters('tasks_table_row_data', $row, $aRow);

hooks()->add_filter('tasks_table_row_data', 'task_customize_tasks_table_row_data', 10, 2);

// Task log hooks
hooks()->add_action('task_assignee_added_controller', 'task_customize_log_assignee_added');
hooks()->add_action('task_assignee_removed_controller', 'task_customize_log_assignee_removed');
hooks()->add_action('task_follower_added_controller', 'task_customize_log_follower_added');
hooks()->add_action('task_follower_removed_controller', 'task_customize_log_follower_removed');
hooks()->add_action('task_status_changed_controller', 'task_customize_log_status_changed');
hooks()->add_action('task_priority_changed_controller', 'task_customize_log_priority_changed');
hooks()->add_action('task_timer_tracking_controller', 'task_customize_log_timer_tracking');
hooks()->add_action('task_time_logged_controller', 'task_customize_log_time_logged');
hooks()->add_action('task_time_updated_controller', 'task_customize_log_time_updated');
hooks()->add_action('task_date_changed_controller', 'task_customize_log_date_changed');
hooks()->add_action('task_custom_field_changed_controller', 'task_customize_log_custom_field_changed');

function task_customize_insert_log($task_id, $description)
{
    if ($task_id != '') {
        $CI = &get_instance();
        $CI->db->insert('tbltask_log', [
            'task_id'     => $task_id,
            'staff_id'    => get_staff_user_id(),
            'date'        => date('Y-m-d H:i:s'),
            'description' => $description,
        ]);    
    }
    
}

function task_customize_log_assignee_added($data)
{
    $staff_name = get_staff_full_name($data['staff_id']);
    task_customize_insert_log($data['task_id'], 'Assigned new user: ' . $staff_name);
}

function task_customize_log_assignee_removed($data)
{
    $staff_name = get_staff_full_name($data['staff_id']);
    task_customize_insert_log($data['task_id'], 'Removed assigned user: ' . $staff_name);
}

function task_customize_log_follower_added($data)
{
    $staff_name = get_staff_full_name($data['staff_id']);
    task_customize_insert_log($data['task_id'], 'Added follower: ' . $staff_name);
}

function task_customize_log_follower_removed($data)
{
    $staff_name = get_staff_full_name($data['staff_id']);
    task_customize_insert_log($data['task_id'], 'Removed follower: ' . $staff_name);
}

function task_customize_log_status_changed($data)
{
    $old_status_name = format_task_status($data['old_status'], true, true);
    $new_status_name = format_task_status($data['new_status'], true, true);
    task_customize_insert_log($data['task_id'], 'Change status from ' . $old_status_name . ' to ' . $new_status_name);
}

function task_customize_log_priority_changed($data)
{
    $CI            = &get_instance();
    $priority_name = task_priority($data['priority']);
    if ($data['priority'] == 4) {
        $updated_data['is_poked'] = 1;
        $CI->db->where('id', $data['task_id']);
        $CI->db->update(db_prefix() . 'tasks', $updated_data);
    }
    task_customize_insert_log($data['task_id'], 'Change priority to ' . $priority_name);
}

function task_customize_log_timer_tracking($data)
{
    task_customize_insert_log($data['task_id'], 'Update task time');
}

function task_customize_log_time_logged($data)
{
    task_customize_insert_log($data['task_id'], 'Add task time');
}

function task_customize_log_time_updated($data)
{
    task_customize_insert_log($data['task_id'], 'Update task time');
}

function task_customize_log_date_changed($data)
{
    $field     = $data['field'];
    $old_value = $data['old_value'];
    $new_value = $data['new_value'];

    $field_labels = [
        'startdate' => 'Start Date',
        'duedate'   => 'Due Date',
    ];

    $field_label = isset($field_labels[$field]) ? $field_labels[$field] : $field;

    $old_date_formatted = $old_value ? _d($old_value) : 'Not Set';
    $new_date_formatted = $new_value ? _d($new_value) : 'Not Set';

    task_customize_insert_log(
        $data['task_id'],
        'Change ' . $field_label . ' from ' . $old_date_formatted . ' to ' . $new_date_formatted
    );
}

function task_customize_log_custom_field_changed($data)
{
    $field_name = isset($data['field_name']) ? $data['field_name'] : 'Custom Field';
    $old_value  = $data['old_value'];
    $new_value  = $data['new_value'];

    $old_date_formatted = $old_value ? _d($old_value) : 'Not Set';
    $new_date_formatted = $new_value ? _d($new_value) : 'Not Set';

    task_customize_insert_log(
        $data['task_id'],
        'Change ' . $field_name . ' from ' . $old_date_formatted . ' to ' . $new_date_formatted
    );
}

register_activation_hook(TASK_CUSTOMIZE_MODULE_NAME, 'task_customize_module_activation_hook');
register_deactivation_hook(TASK_CUSTOMIZE_MODULE_NAME, 'task_customize_module_deactivation_hook');
//register languge
register_language_files(TASK_CUSTOMIZE_MODULE_NAME, [TASK_CUSTOMIZE_MODULE_NAME]);

$CI = &get_instance();
$CI->load->helper(TASK_CUSTOMIZE_MODULE_NAME . '/task_customize');

// hooks()->add_action('after_cron_run', 'task_customize_after_cron_run');

// function task_customize_after_cron_run()
// {
//     $CI = &get_instance();
//     $CI->load->model('tasks_model');

//     // Get all tasks that are in "Awaiting Feedback" status
//     $tasks = $CI->db->select('id')
//         ->from(db_prefix() . 'tasks')
//         ->where('status', Tasks_model::STATUS_AWAITING_FEEDBACK) // status = 2
//         ->where('is_email_sent', 0)
//         // ->where('id', 2304)
//         ->get()
//         ->result_array();

//     // Loop through each task
//     foreach ($tasks as $task) {
//         $task_id = $task['id'];

//         // Get "Work Planned" custom field value for this task
//         $custom_field = $CI->db->select('value')
//             ->from(db_prefix() . 'customfieldsvalues')
//             ->where('relid', $task_id)
//             ->where('fieldto', 'tasks')
//             ->where('fieldid', 45) // <-- Change this to your actual custom field ID for "Work Planned"
//             ->get()
//             ->row();

//         if (!$custom_field || empty($custom_field->value)) {
//             continue;
//         }

//         $work_planned_date = strtotime($custom_field->value);
//         $current_time = time();

//         if ($current_time - $work_planned_date >= 24 * 60 * 60) {

//             $CI->db->insert(db_prefix() . 'task_comments', [
//                 'taskid' => $task_id,
//                 'staffid' => 1,
//                 'content' => '<p>24 Hours Reminder:</p>
//                 <p>Status is Customer Review and Work Planned Date needs to be updated for when you will follow-up with the customer.</p>',
//                 'dateadded' => date('Y-m-d H:i:s'),
//             ]);

//              $CI->db->update(db_prefix() . 'tasks', ['is_email_sent' => 1] , ['id' => $task_id]);

//             $assigned_users = $CI->db->select('staffid')
//                 ->from(db_prefix() . 'task_assigned')
//                 ->where('taskid', $task_id)
//                 ->get()
//                 ->result_array();

//             $assigned_user_ids = array_column($assigned_users, 'staffid');

//             if (!empty($assigned_user_ids)) {
//                 $CI->load->library('email');

//                 foreach ($assigned_user_ids as $staff_id) {
//                     // Get staff email
//                     $staff = $CI->db->select('email')
//                         ->from(db_prefix() . 'staff')
//                         ->where('staffid', $staff_id)
//                         ->get()
//                         ->row();

//                     $task_link = admin_url('tasks/view/' . $task_id);
//                     $message = "24 Hours Reminder\n";
//                     $message .= "Status is Customer Review and Work Planned Date needs to be updated for when you will follow-up with the customer.\n\n";
//                     $message .= "Here is the task link: {$task_link}";

//                     $CI->email->clear(true);
//                     $CI->email->from(get_option('smtp_email'), get_option('companyname'));
//                     $CI->email->to($staff->email);
//                     $CI->email->subject('Reminder');
//                     $CI->email->message($message); // converts \n to <br> for HTML emails

//                     // Send email
//                     $CI->email->send();
//                 }
//             }
//         }
//     }
// }

function task_customize_tasks_table_row_data($row, $aRow)
{
    $row['DT_RowClass'] = '';
    if ((! empty($aRow['duedate']) && $aRow['duedate'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' success';
    } elseif ((! empty($aRow['date_picker_cvalue_2']) && $aRow['date_picker_cvalue_2'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' warning';
    }

    $temp_row = $row;

    $working_days = '';
    if (! empty($aRow['startdate']) && ! empty($aRow['datefinished'])) {
        $start = new DateTime($aRow['startdate']);
        $end   = new DateTime($aRow['datefinished']);
        $end->modify('+1 day');

        $interval           = new DatePeriod($start, new DateInterval('P1D'), $end);
        $working_days_count = 0;

        foreach ($interval as $date) {
            if ($date->format('N') < 6) {
                $working_days_count++;
            }
        }

        $working_days = $working_days_count . ' day';
    }

    $before_dt_rowclass = $temp_row['DT_RowClass'];
    unset($temp_row['DT_RowClass']);

    $temp_values = array_values($temp_row);
    array_splice($temp_values, 7, 0, [$working_days]);
    $temp_values['DT_RowClass'] = $before_dt_rowclass;
    // $temp_values[] = "cdscd";
    // $temp_values[] = !empty($aRow['department_names']) 
    // ? e($aRow['department_names']) 
    // : '-';
    // $temp_values[] = !empty($aRow['staff_roles']) 
    // ? e($aRow['staff_roles']) 
    // : '-';
    return $temp_values;
}
function task_customize_module_activation_hook()
{

    $CI = &get_instance();
    require_once __DIR__ . '/install.php';

    // //============================================= my__bulk_actions.php
    $my_register_path        = APPPATH . 'views/admin/tasks/my_manage.php';
    $module_my_register_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_manage.php';
    if (! file_exists($my_register_path)) {
        copy($module_my_register_path, $my_register_path);
    }

    $my_project_path        = APPPATH . 'views/admin/projects/my_project_contracts.php';
    $module_my_project_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_project_contracts.php';
    if (! file_exists($my_project_path)) {
        copy($module_my_project_path, $my_project_path);
    }

    $my_contract_path        = APPPATH . 'views/admin/contracts/my_contract.php';
    $module_my_contract_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_contract.php';
    if (! file_exists($my_contract_path)) {
        copy($module_my_contract_path, $my_contract_path);
    }

    $my_task_relation_path        = APPPATH . 'views/admin/tables/my_tasks_relations.php';
    $module_my_task_relation_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_tasks_relations.php';
    if (! file_exists($my_task_relation_path)) {
        copy($module_my_task_relation_path, $my_task_relation_path);
    }

    $my_task_path        = APPPATH . 'views/admin/tasks/my_task.php';
    $module_my_task_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_task.php';
    if (! file_exists($my_task_path)) {
        copy($module_my_task_path, $my_task_path);
    }

    // this is my file for adding file in client bulk action
    $my_manage_path        = APPPATH . 'views/admin/clients/my_manage.php';
    $module_my_manage_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/clients/my_manage.php';
    if (! file_exists($my_manage_path)) {
        copy($module_my_manage_path, $my_manage_path);
    }

    // this is client table my fiel code for adding new filter
    $my_clients_path        = APPPATH . 'views/admin/tables/my_clients.php';
    $module_my_clients_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/clients/my_clients.php';
    if (! file_exists($my_clients_path)) {
        copy($module_my_clients_path, $my_clients_path);
    }

    // this my file is for adding new colum in vault table roboform and show that in list
    $my_vault_path        = APPPATH . 'views/admin/clients/groups/my_vault.php';
    $module_my_vault_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/clients/my_vault.php';
    if (! file_exists($my_vault_path)) {
        copy($module_my_vault_path, $my_vault_path);
    }

    // this my file is for adding filter and bulk action in staf side
    $my_staff_path        = APPPATH . 'views/admin/tables/my_staff.php';
    $module_my_staff_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/staff/my_staff.php';
    if (! file_exists($my_staff_path)) {
        copy($module_my_staff_path, $my_staff_path);
    }

    $my_manage_path        = APPPATH . 'views/admin/staff/my_manage.php';
    $module_my_manage_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/staff/my_manage.php';
    if (! file_exists($my_manage_path)) {
        copy($module_my_manage_path, $my_manage_path);
    }
    // this my file is for adding filter and bulk action in staf side

    // this my file is for adding bulk action in client page task list
    $my_manage_path        = APPPATH . 'views/admin/clients/groups/my_tasks.php';
    $module_my_manage_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/clients/my_tasks.php';
    if (! file_exists($my_manage_path)) {
        copy($module_my_manage_path, $my_manage_path);
    }

    // application\views\admin\staff\my_timesheets.php
    $my_manage_path        = APPPATH . 'views/admin/staff/my_timesheets.php';
    $module_my_manage_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/staff/my_timesheets.php';
    if (! file_exists($my_manage_path)) {
        copy($module_my_manage_path, $my_manage_path);
    }

    // application\views\admin\tables\my_staff_timesheets.php
    $my_manage_path        = APPPATH . 'views/admin/tables/my_staff_timesheets.php';
    $module_my_manage_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/staff/my_staff_timesheets.php';
    if (! file_exists($my_manage_path)) {
        copy($module_my_manage_path, $my_manage_path);
    }


    // application\views\admin\tables\my_staff_timesheets.php
    $my_filters_path        = APPPATH . 'views/admin/tasks/my_filters.php';
    $module_my_filters_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/tasks/my_filters.php';
    if (! file_exists($my_filters_path)) {
        copy($module_my_filters_path, $my_filters_path);
    }
}

function task_customize_module_deactivation_hook()
{
    require_once __DIR__ . '/uninstall.php';
}

function tasks_related_table_columns($table_data)
{
    $table_data[]     = 'Milestones';
    $table_data[]     = 'Comments';
    $startDateIndex   = null;
    $workPlannedIndex = null;

    // Loop through the array and find indices
    foreach ($table_data as $index => $item) {
        if (is_array($item) && isset($item['name'])) {
            if ($item['name'] === "Start Date") {
                $startDateIndex = $index;
            }
            if ($item['name'] === "Work Planned") {
                $workPlannedIndex = $index;
            }
        }
    }

    // If both are found, move "Work Planned" after "Start Date"
    if ($startDateIndex !== null && $workPlannedIndex !== null) {
        // Remove "Work Planned" from its original position
        $workPlanned = $table_data[$workPlannedIndex];
        unset($table_data[$workPlannedIndex]);

        if (isset($workPlanned['th_attrs']) && is_array($workPlanned['th_attrs'])) {
            $workPlanned['th_attrs']['class'] = 'duedate';
        }

        // Re-index array
        $table_data = array_values($table_data);

        // Insert "Work Planned" after "Start Date"
        array_splice($table_data, $startDateIndex + 1, 0, [$workPlanned]);
    }

    return $table_data;
}

function tasks_related_table_sql_columns($aColumns)
{
    $aColumns[] = 'milestone';

    $startdateIndex = array_search("startdate", $aColumns);
    $duedateIndex   = array_search("duedate", $aColumns);
    $moveIndex      = null;

    foreach ($aColumns as $index => $value) {
        if (strpos($value, "tasks_eta") !== false) {
            $moveIndex = $index;
            break;
        }
    }

    if ($moveIndex !== null && $startdateIndex !== false && $duedateIndex !== false) {
        $elementToMove = $aColumns[$moveIndex];
        unset($aColumns[$moveIndex]);
        $aColumns = array_values($aColumns);

        $startdateIndex = array_search("startdate", $aColumns);
        $duedateIndex   = array_search("duedate", $aColumns);

        array_splice($aColumns, $duedateIndex, 0, $elementToMove);
    }
    $aColumns[] = "2";

    return $aColumns;
}

function tasks_related_table_row_data($row, $aRow)
{
    // $milestone = get_milestone_data($aRow['milestone']);
    // $row[] = $milestone;
    // $row['DT_RowClass'] = 'has-row-options has-border-left';
    // if ((!empty($aRow['tasks_eta']) && $aRow['tasks_eta'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
    //     $row['DT_RowClass'] .= ' orange';
    // }
    // if ((! empty($aRow['duedate']) && $aRow['duedate'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
    //     $row['DT_RowClass'] .= ' danger';
    // }
    // if ((!empty($aRow['duedate']) && $aRow['duedate'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
    //     $row['DT_RowClass'] .= ' success';
    // }
    // if ((!empty($aRow['tasks_eta']) && $aRow['tasks_eta'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
    //     $row['DT_RowClass'] .= ' warning';
    // }
    $milestone          = get_milestone_data($aRow['milestone']);
    $row[]              = $milestone;
    $comments           = '<a href="#" class="task-comment" data-task-id="' . $aRow['id'] . '" data-toggle="modal" data-target="#task-comment-modal"><i class="fa fa-comment"></i>   ' . get_comments_count($aRow['id']) . '</a>';
    $row[]              = $comments;
    $row['DT_RowClass'] = 'has-row-options has-border-left';

    if ((! empty($aRow['tasks_eta']) && $aRow['tasks_eta'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' orange';
    } elseif ((! empty($aRow['tasks_eta']) && $aRow['tasks_eta'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' warning';
    } elseif ((! empty($aRow['duedate']) && $aRow['duedate'] < date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' danger';
    } elseif ((! empty($aRow['duedate']) && $aRow['duedate'] == date('Y-m-d')) && $aRow['status'] != Tasks_model::STATUS_COMPLETE) {
        $row['DT_RowClass'] .= ' success';
    }
    return $row;
}

function task_customize_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_tabs->add_project_tab('project_tasks', [
        'name'                      => _l('tasks'),
        'icon'                      => 'fa-regular fa-check-circle',
        'view'                      => TASK_CUSTOMIZE_MODULE_NAME . '/project_tasks',
        'position'                  => 10,
        'linked_to_customer_option' => ['view_tasks'],
    ]);

    // add three tab in project view page
    $CI->app_tabs->add_project_tab('task_process', [
        'name'     => 'Task Process',
        'icon'     => 'fa-regular fa-check-circle',
        'view'     => TASK_CUSTOMIZE_MODULE_NAME . '/project_tab_task_process',
        'position' => 11,
    ]);

    $CI->app_tabs->add_project_tab('task_resource', [
        'name'     => 'Resource',
        'icon'     => 'fa-regular fa-check-circle',
        'view'     => TASK_CUSTOMIZE_MODULE_NAME . '/project_tab_resource',
        'position' => 12,
    ]);

    $CI->app_tabs->add_project_tab('vault', [
        'name'     => 'Vault',
        'icon'     => 'fa-regular fa-check-circle',
        'view'     => TASK_CUSTOMIZE_MODULE_NAME . '/project_tab_vault',
        'position' => 13,
    ]);
    // add three tab in project view page

    //add menu name  reccuring tasks in main sidebar
    $CI->app_menu->add_sidebar_menu_item('recurring_tasks', [
        'name'     => "Repeating Tasks",
        'icon'     => 'fa fa-refresh',
        'position' => 15,
        'href'     => admin_url('task_customize/recurring_tasks'),
    ]);

    //add sidebar menu project custome filed 	Services Included Projects
    $CI->app_menu->add_sidebar_menu_item('project_custom_fields', [
        'name'     => "Services Included",
        'icon'     => 'fa-solid fa-chart-gantt',
        'position' => 16,
        'href'     => '#',
    ]);
    //chield menu project custom fields get fileds from database and make loop and add to menu
    $CI->db->select('options');
    $CI->db->from(db_prefix() . 'customfields');
    $CI->db->where('fieldto', 'projects');
    $CI->db->where('id', PROJECT_SERVICES_INCLUDED);
    $project_custom_fields = $CI->db->get()->result_array();

    //explode options by comma and make loop and add to menu
    $options = explode(',', $project_custom_fields[0]['options']);
    foreach ($options as $option) {
        $option = trim($option);
        //space remove and add _
        $value = '';
        if ($option != '') {
            $value = str_replace(' ', '_', $option);
        }

        $CI->app_menu->add_sidebar_children_item('project_custom_fields', [
            'slug' => 'project_custom_field_' . $value,
            'name' => $option,
            'icon' => 'fa-solid fa-chart-gantt',
            'href' => admin_url('task_customize/project_custom_fields?service=' . $value),
        ]);
    }

    //add new menu project_type
    $CI->app_menu->add_sidebar_menu_item('project_type', [
        'name'     => 'Project Type',
        'icon'     => 'fa-solid fa-chart-gantt',
        'position' => 17,
        'href'     => '#',
    ]);

    //projects
    $CI->app_menu->add_sidebar_children_item('project_type', [
        'slug' => 'project_type_projects',
        'name' => 'Projects',
        'href' => admin_url('projects'),
        'icon' => 'fa-solid fa-chart-gantt',
    ]);

    //add children menu project type
    $CI->app_menu->add_sidebar_children_item('project_type', [
        'slug' => 'project_type_website',
        'name' => 'Website',
        'icon' => 'fa-solid fa-globe',
        'href' => admin_url('task_customize/project_type?type=website'),
    ]);

    //landing page projects
    $CI->app_menu->add_sidebar_children_item('project_type', [
        'slug' => 'project_type_landing_page',
        'name' => 'Landing Page',
        'icon' => 'fa-solid fa-globe',
        'href' => admin_url('task_customize/project_type?type=landing_page'),
    ]);

    // add_customer_profile_tab add
    $CI->app_tabs->add_customer_profile_tab('status_check', [
        'name'     => 'Status Check',
        'icon'     => 'fa-solid fa-tasks',
        'view'     => 'task_customize/status_check',
        'position' => 5,
        'badge'    => [],
    ]);

    $CI->app_tabs->add_customer_profile_tab('customer_management', [
        'name'     => 'Customer Management',
        'icon'     => 'fa-solid fa-users',
        'view'     => 'task_customize/customer_management',
        'position' => 6,
        'badge'    => [],
    ]);

    //add sidebar menu for vault
    $CI->app_menu->add_sidebar_menu_item('vault', [
        'name'     => "Vault",
        'icon'     => 'fa fa-lock menu-icon',
        'position' => 50,
        'href'     => admin_url('task_customize/show_vault'),
    ]);
}

// i added dhaval

function task_customize_task_status_changed($data)
{
    $CI      = &get_instance();
    $status  = isset($data['status']) ? $data['status'] : '';
    $task_id = isset($data['task_id']) ? $data['task_id'] : '';
    if ($status != '' && $task_id != '') {

        if ($status == Tasks_model::STATUS_COMPLETE) {
            $CI->db->select('id,addedfrom,recurring_type,repeat_every,last_recurring_date,startdate,duedate,recurring ,is_recurring_from');
            $CI->db->where('id', $task_id);
            $recurring_tasks = $CI->db->get(db_prefix() . 'tasks')->result_array();
            if (! empty($recurring_tasks)) {
                foreach ($recurring_tasks as $task) {
                    if ((isset($task['is_recurring_from']) && $task['is_recurring_from'] != '') || (isset($task['recurring']) && $task['recurring'] == 1)) {
                        $last_recurring_date = $task['last_recurring_date'];
                        $type                = $task['recurring_type'];
                        $repeat_every        = $task['repeat_every'];
                        $task_date           = $task['startdate'];

                        if (isset($task['is_recurring_from']) && $task['is_recurring_from'] != null) {
                            $task_setail            = get_task_detail($task['is_recurring_from']);
                            $last_recurring_task_id = isset($task_setail[0]['id']) ? $task_setail[0]['id'] : '';
                            $last_recurring_date    = isset($task_setail[0]['last_recurring_date']) ? $task_setail[0]['last_recurring_date'] : '';
                            $type                   = isset($task_setail[0]['recurring_type']) ? $task_setail[0]['recurring_type'] : '';
                            $repeat_every           = isset($task_setail[0]['repeat_every']) ? $task_setail[0]['repeat_every'] : '';
                            $task_date              = isset($task_setail[0]['startdate']) ? $task_setail[0]['startdate'] : '';
                            if (isset($task_setail[0]['total_cycles']) && isset($task_setail[0]['cycles']) && $task_setail[0]['total_cycles'] == $task_setail[0]['cycles']) {
                                continue;
                            }
                        }
                        if ($task['recurring'] == 1) {
                            if (isset($task[0]['total_cycles']) && isset($task[0]['cycles']) && $task[0]['total_cycles'] == $task[0]['cycles']) {
                                continue;
                            }
                        }

                        $date = new DateTime(date('Y-m-d'));
                        // Check if is first recurring
                        if (! $last_recurring_date) {
                            $last_recurring_date = date('Y-m-d', strtotime($task_date));
                        } else {
                            $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
                        }

                        $re_create_at = date('Y-m-d', strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date)));

                        $task_id = $task['id'];
                        if (isset($last_recurring_task_id) && $last_recurring_task_id != '') {
                            $task_id = $last_recurring_task_id;
                        } else {
                            $task_id = $task['id'];
                        }
                        $copy_task_data['copy_task_followers']       = 'true';
                        $copy_task_data['copy_task_checklist_items'] = 'true';
                        $copy_task_data['copy_from']                 = $task_id;

                        $overwrite_params = [
                            'startdate'           => $re_create_at,
                            'status'              => ASSIGN_STATUS,
                            'recurring_type'      => null,
                            'repeat_every'        => 0,
                            'cycles'              => 0,
                            'recurring'           => 0,
                            'custom_recurring'    => 0,
                            'last_recurring_date' => null,
                            'is_recurring_from'   => $task_id,
                        ];

                        if (! empty($task['duedate'])) {
                            $dStart                      = new DateTime($task['startdate']);
                            $dEnd                        = new DateTime($task['duedate']);
                            $dDiff                       = $dStart->diff($dEnd);
                            $overwrite_params['duedate'] = date('Y-m-d', strtotime('+' . $dDiff->days . ' days', strtotime($re_create_at)));
                        }
                        $newTaskID = $CI->tasks_model->copy($copy_task_data, $overwrite_params);

                        if ($newTaskID) {
                            $task_id = $task['id'];
                            if (isset($last_recurring_task_id) && $last_recurring_task_id != '') {
                                $task_id = $last_recurring_task_id;
                            } else {
                                $task_id = $task['id'];
                            }
                            $CI->db->where('id', $task_id);
                            $CI->db->update(db_prefix() . 'tasks', [
                                'last_recurring_date' => $re_create_at,
                            ]);

                            $CI->db->where('id', $task_id);
                            $CI->db->set('total_cycles', 'total_cycles+1', false);
                            $CI->db->update(db_prefix() . 'tasks');

                            $CI->db->where('taskid', $task_id);
                            $assigned = $CI->db->get(db_prefix() . 'task_assigned')->result_array();
                            foreach ($assigned as $assignee) {
                                $assigneeId = $CI->tasks_model->add_task_assignees([
                                    'taskid'   => $newTaskID,
                                    'assignee' => $assignee['staffid'],
                                ], true);

                                if ($assigneeId) {
                                    $CI->db->where('id', $assigneeId);
                                    $CI->db->update(db_prefix() . 'task_assigned', ['assigned_from' => $task['addedfrom']]);
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($status == ASSIGN_STATUS || $status == INTERNAL_REVIEW_STATUS) {
            $CI->db->where('relid', $task_id);
            $CI->db->where('fieldid', WORK_PLANNED);
            $CI->db->where('fieldto', 'tasks');
            $CI->db->delete(db_prefix() . 'customfieldsvalues');
        }

        if ($status == Tasks_model::STATUS_COMPLETE) {
            $CI->db->where('task_id', $task_id);
            $CI->db->update(db_prefix() . 'task_timer', ['end_time' => date('Y-m-d H:i:s')]);
            $CI->db->insert(db_prefix() . 'task_timer_history', ['task_id' => $task_id, 'end_date' => date('Y-m-d H:i:s')]);
        }
    }
}

function task_customize_tasks_table_columns($table_data)
{
    foreach ($table_data as $key => $value) {
        if (is_array($value) && isset($value['name']) && $value['name'] === "Work Planned") {
            $work_planned = array_splice($table_data, $key, 1);

            if (isset($work_planned[0]['th_attrs']) && is_array($work_planned[0]['th_attrs'])) {
                $work_planned[0]['th_attrs']['class'] = 'duedate';
            }
            break;
        }
    }
    foreach ($table_data as $key => $value) {
        if ($value === "Start Date") {
            $start_date_index = $key;
            break;
        }
    }

    array_splice($table_data, $start_date_index + 1, 0, $work_planned);
    // Find "duedate" index
    $duedate_index = null;
    foreach ($table_data as $key => $value) {
        if ($value === "duedate" || (is_array($value) && isset($value['name']) && $value['name'] === "Due Date")) {
            $duedate_index = $key;
            break;
        }
    }

    // Insert "Working Days" after duedate
    if ($duedate_index !== null) {
        array_splice($table_data, $duedate_index + 1, 0, ['Working Days']);
    } else {
        $table_data[] = 'Working Days';
    }

    $table_data[] = _l('comments');
    $table_data[] = 'Department';
    $table_data[] = 'Role(s)';
    return $table_data;
}

function task_customize_tasks_table_sql_columns($aColumns)
{
    $startdateIndex = array_search("startdate", $aColumns);
    $duedateIndex   = array_search("duedate", $aColumns);
    $moveIndex      = null;

    foreach ($aColumns as $index => $value) {
        if (strpos($value, "tasks_eta") !== false) {
            $moveIndex = $index;
            break;
        }
    }

    if ($moveIndex !== null && $startdateIndex !== false && $duedateIndex !== false) {
        $elementToMove = $aColumns[$moveIndex];
        unset($aColumns[$moveIndex]);
        $aColumns = array_values($aColumns);

        $startdateIndex = array_search("startdate", $aColumns);
        $duedateIndex   = array_search("duedate", $aColumns);

        array_splice($aColumns, $duedateIndex, 0, $elementToMove);
    }
    $aColumns[] = "2";

    $duedateIndex = array_search("duedate", $aColumns);
    if ($duedateIndex !== false) {
        array_splice($aColumns, $duedateIndex + 1, 0, ['1']);
    }

    $aColumns[] = 'datefinished';
    // $aColumns[] = '(SELECT GROUP_CONCAT(DISTINCT d.name SEPARATOR ", ")
    // FROM ' . db_prefix() . 'task_assigned ta
    // JOIN ' . db_prefix() . 'staff_departments sd ON sd.staffid = ta.staffid
    // JOIN ' . db_prefix() . 'departments d ON d.departmentid = sd.departmentid
    // WHERE ta.taskid = ' . db_prefix() . 'tasks.id
    // ) as department_names';

    // $aColumns[] = '(SELECT GROUP_CONCAT(DISTINCT cf.value SEPARATOR ", ")
    // FROM ' . db_prefix() . 'task_assigned ta
    // JOIN ' . db_prefix() . 'customfieldsvalues cf 
    //     ON cf.relid = ta.staffid
    // WHERE ta.taskid = ' . db_prefix() . 'tasks.id
    // AND cf.fieldid = 96
    // AND cf.fieldto = "staff"
    // AND cf.value != ""
    // ) as staff_roles';

    return $aColumns;
}

hooks()->add_action('app_admin_footer', 'task_customize_hook_app_admin_footer');
function task_customize_hook_app_admin_footer()
{

    // Get the current request URI
    $viewuri = $_SERVER['REQUEST_URI'];

    // Check if the URI contains the desired path
    if (strpos($viewuri, 'group=project_tasks') !== false) {
        //load
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/project_tasks.js') . '?v=' . VERSION_TASK_CUSTOMIZE . '"></script>';
    }

    // Load task date activity handler on all admin/tasks pages
    if (strpos($viewuri, 'admin/tasks') !== false) {
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/task_date_activity.js') . '?v=' . VERSION_TASK_CUSTOMIZE . '"></script>';
    }

    //url is http://localhost/matyxcloud/admin/projects
    if (strpos($viewuri, 'admin/projects') !== false) {
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/project_change.js') . '?v=' . time() . '"></script>';
    }

    if (strpos($viewuri, 'admin/project_type') !== false) {
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/project_change.js') . '?v=' . VERSION_TASK_CUSTOMIZE . '"></script>';
    }

    //group=projects
    if (strpos($viewuri, 'group=projects') !== false) {
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/project_change.js') . '?v=' . VERSION_TASK_CUSTOMIZE . '"></script>';
    }

    if (strpos($viewuri, 'group=contracts') !== false) {
        echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/contracts.js') . '?v=' . VERSION_TASK_CUSTOMIZE . '"></script>';
    }


    echo "<script src='https://cdn.jotfor.ms/agent/embedjs/019c3d07502372629da2ef297ab4ec83516f/embed.js'></script>";

    echo '<script src="' . module_dir_url(TASK_CUSTOMIZE_MODULE_NAME, 'assets/js/mentions-filter.js') . '?v=1"></script>';
}

hooks()->add_action('before_add_task', 'task_customize_before_add_task');
function task_customize_before_add_task($data)
{

    //check assing exits in project or not
    $CI = &get_instance();
    if ($data['rel_type'] == 'project' && isset($data['assignees'])) {
        $project_id        = $data['rel_id'];
        $task_assignees    = $data['assignees'];
        $project_assignees = $CI->projects_model->get_project_members($project_id);
        $existingStaffIds  = array_column($project_assignees, 'staff_id');
        //array merge
        $assignees                       = array_merge($task_assignees, $existingStaffIds);
        $project_data['project_members'] = $assignees;
        //update assignees
        $CI->projects_model->add_edit_members($project_data, $project_id);
    }

    if (isset($data['payment_status']) && $data['payment_status'] !== '') {
        $data['is_paid'] = '';
        if ($data['payment_status'] == 'paid') {
            $data['is_paid'] = 1;
        } else if ($data['payment_status'] == 'unpaid') {
            $data['is_paid'] = 2;
        }
    }
    unset($data['payment_status']);

    // if($data['custome_customer_id'] != ''){
    //     $data['clientid'] = $data['custome_customer_id'];
    // }
    // unset($data['custome_customer_id']);

    if (isset($data['date_contact_entered_in_ghl'])) {
        $dateObj                             = DateTime::createFromFormat('m-d-Y', $data['date_contact_entered_in_ghl']);
        $data['date_contact_entered_in_ghl'] = $dateObj ? $dateObj->format('Y-m-d') : null;
    }

    return $data;
}

hooks()->add_action('before_update_task', 'task_customize_before_update_task');
function task_customize_before_update_task($data)
{
    //     if($data['custome_customer_id'] != ''){
    //         $data['clientid'] = $data['custome_customer_id'];
    //     }
    //     unset($data['custome_customer_id']);
    //     return $data;
    if (isset($data['payment_status']) && $data['payment_status'] !== '') {
        $data['is_paid'] = '';
        if ($data['payment_status'] == 'paid') {
            $data['is_paid'] = 1;
        } else if ($data['payment_status'] == 'unpaid') {
            $data['is_paid'] = 2;
        }
    }

    unset($data['payment_status']);
    if (isset($data['date_contact_entered_in_ghl'])) {
        $dateObj                             = DateTime::createFromFormat('m-d-Y', $data['date_contact_entered_in_ghl']);
        $data['date_contact_entered_in_ghl'] = $dateObj ? $dateObj->format('Y-m-d') : null;
    }
    return $data;
}

hooks()->add_action('task_assignee_added', 'task_customize_task_assignee_added');
function task_customize_task_assignee_added($data)
{
    $CI      = &get_instance();
    $task_id = $data['task_id'];
    $task    = $CI->tasks_model->get($task_id);

    if ($task) {
        $rel_type = $task->rel_type;
        $rel_id   = $task->rel_id;
        if ($rel_type == 'project') {
            $project_assignees = $CI->projects_model->get_project_members($rel_id);
            $existingStaffIds  = array_column($project_assignees, 'staff_id');
            $task_assignees    = [$data['staff_id']];
            $assignees         = array_merge($task_assignees, $existingStaffIds);

            $project_data['project_members'] = $assignees;
            $CI->projects_model->add_edit_members($project_data, $rel_id);
        }
    }
}

//after_customer_admins_tab
hooks()->add_action('after_customer_billing_and_shipping_tab', 'client_add_custome_staff');
function client_add_custome_staff($data)
{
    echo '   <li role="presentation">
                        <a href="#client_custome" aria-controls="client_custome" role="tab"
                            data-toggle="tab">
                             Customer Management
                        </a>
                    </li>';
}

hooks()->add_action('after_custom_profile_tab_content', 'client_add_custome_staff_content');
function client_add_custome_staff_content($client)
{
    $CI = &get_instance();
    echo $CI->load->view('task_customize/custome_content', $client);
}

//projects_table_columns
hooks()->add_action('projects_table_columns', 'task_customize_projects_table_columns');
function task_customize_projects_table_columns($columns)
{
    $columns[] = 'Day Count';
    return $columns;
}

//projects_table_sql_columns
hooks()->add_action('projects_table_sql_columns', 'task_customize_projects_table_sql_columns');
function task_customize_projects_table_sql_columns($columns)
{
    //join table project_timer
    $columns[] = '1';
    return $columns;
}

function get_active_days($project_id)
{
    $CI = &get_instance();
    $CI->db->where('project_id', $project_id);
    $timers = $CI->db->get(db_prefix() . 'project_timer')->result();

    $total_seconds = 0;
    foreach ($timers as $timer) {
        $start          = strtotime($timer->start_time);
        $end            = $timer->pause_time ? strtotime($timer->pause_time) : time();
        $total_seconds += ($end - $start);
    }

    return floor($total_seconds / 86400); // return days
}

function task_customize_after_add_task($data)
{
    $CI        = &get_instance();
    $save_data = [
        'task_id'    => $data,
        'start_time' => date('Y-m-d H:i:s'),
        'staff_id'   => get_staff_user_id(),
    ];

    $CI->db->insert(db_prefix() . 'task_timer', $save_data);

    if ($data > 0) {

        $CI->db->select('priority'); // only select priority column
        $CI->db->where('id', $data);

        $result = $CI->db->get(db_prefix() . 'tasks')->row();

        $priority = isset($result->priority) ? $result->priority : null;

        if ($priority == 4) {
            $updated_data['is_poked'] = 1;
            $CI->db->where('id', $data);
            $CI->db->update(db_prefix() . 'tasks', $updated_data);
        }
    }
}

function get_staff_id_by_name($first_name, $last_name)
{
    $CI = &get_instance();
    $CI->db->where('firstname', $first_name);
    $CI->db->where('lastname', $last_name);
    $staff = $CI->db->get(db_prefix() . 'staff')->row();
    return $staff->staffid;
}

function task_customize_before_log_project_activity($data)
{
    if ($data['description_key'] === 'project_activity_added_team_member' || $data['description_key'] === 'project_activity_removed_team_member') {
        // make seprate name and last name
        $additional_data = $data['additional_data'];
        if ($additional_data !== '') {

            $name                 = explode(' ', $additional_data);
            $first_name           = $name[0];
            $last_name            = $name[1];
            $get_staff_id_by_name = get_staff_id_by_name($first_name, $last_name);

            $CI = &get_instance();
            $CI->load->database();

            $project_id = $data['project_id'];
            $staff_id   = $get_staff_id_by_name; // now we get actual target staff_id
            $now        = date('Y-m-d H:i:s');

            if (empty($project_id) || empty($staff_id)) {
                return;
            }

            if ($data['description_key'] === 'project_activity_added_team_member') {
                // Avoid duplicate active rows
                $CI->db->where('project_id', $project_id);
                $CI->db->where('assigned_id', $staff_id);
                $CI->db->where('end_time IS NULL', null, false);
                $exists = $CI->db->count_all_results(db_prefix() . 'project_timer_history');

                if ($exists == 0) {
                    $insert_data = [
                        'project_id'   => $project_id,
                        'assigned_id'  => $staff_id,
                        'start_time'   => $now,
                        'created_date' => $now,
                    ];
                    $CI->db->insert(db_prefix() . 'project_timer_history', $insert_data);
                }
            }

            if ($data['description_key'] === 'project_activity_removed_team_member') {
                $CI->db->where('project_id', $project_id);
                $CI->db->where('assigned_id', $staff_id);
                $CI->db->where('end_time IS NULL');
                $CI->db->update(db_prefix() . 'project_timer_history', [
                    'end_time' => $now,
                ]);
            }
        }
    }

    return $data;
}

hooks()->add_action('customers_table_sql_columns', 'task_customize_customers_table_sql_columns');

function task_customize_customers_table_sql_columns($columns)
{
    $new_column = 'cam_id';
    array_splice($columns, 10, 0, [$new_column]);
    return $columns;
}

hooks()->add_action('customers_table_row_data', 'task_customize_customers_table_row_data', 10, 2);
function task_customize_customers_table_row_data($row, $aRow)
{
    $cam_name = '';

    if (! empty($aRow['cam_id']) && is_numeric($aRow['cam_id'])) {
        $cam_name = get_staff_full_name((int) $aRow['cam_id']);
    }

    // Insert at index 10
    $temp_row = $row;
    unset($temp_row['DT_RowClass']);

    $temp_values = array_values($temp_row);
    array_splice($temp_values, 10, 0, [$cam_name ?: '']);

    $temp_values['DT_RowClass'] = $row['DT_RowClass'];

    return $temp_values;
}

// customers_table_columns
hooks()->add_action('customers_table_columns', 'task_customize_customers_table_columns');
function task_customize_customers_table_columns($columns)
{
    $new_column = 'CAM';
    array_splice($columns, 10, 0, [$new_column]);
    return $columns;
}

hooks()->add_action(
    'before_do_bulk_action_for_customers',
    'task_customize_bulk_customer_update'
);

function task_customize_bulk_customer_update()
{
    $CI = &get_instance();

    if (empty($_POST) || isset($_POST['mass_delete'])) {
        return;
    }

    $data = $_POST;

    if (! isset($data['ids']) || ! is_array($data['ids'])) {
        return;
    }

    $update_data = [];

    $fields = [
        'cam_id',
        'optimizer_id',
        'organic_social_id',
        'seo_lead_id',
        'sale_rep_id',
        'content_id',
        'web_lead_id',
    ];

    foreach ($fields as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            $update_data[$field] = $data[$field];
        }
    }
    if (empty($update_data)) {
        return;
    }

    foreach ($data['ids'] as $customer_id) {
        $CI->db->where('userid', $customer_id);
        $CI->db->update(db_prefix() . 'clients', $update_data);
    }
}

hooks()->add_action('admin_navbar_start', 'task_customize_admin_navbar_start' , 5);
function task_customize_admin_navbar_start()
{
    echo '<li class="dropdown">
			<a href="' . admin_url('wiki/articles?filter_book_id=18') . '" 
                    target="_blank" 
                    class="ml-2" style="margin-left: 5px;">
                    <img src="' . base_url('assets/images/unicorn-help-icon-1.png') . '" 
                            width="25" 
                            height="25" 
                            alt="Help">
                    </a>';
	 echo '</li>';
}