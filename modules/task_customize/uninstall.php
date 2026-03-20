<?php
defined('BASEPATH') or exit('No direct script access allowed');

$unlink_files = array(
    APPPATH . 'views/admin/tasks/my_manage.php',  //done 1
    APPPATH . 'views/admin/projects/my_project_contracts.php', //done 1
    APPPATH . 'views/admin/contracts/my_contract.php', //done 1
    APPPATH . 'views/admin/tables/my_tasks_relations.php', //done 1
    APPPATH . 'views/admin/tables/my_tasks.php', // done 2
    APPPATH . 'views/admin/tasks/my_view_task_template.php', // done 2
    APPPATH . 'views/admin/tasks/my_task.php',  // done 1
    APPPATH . 'views/admin/tables/my_projects.php', // done 2
    APPPATH . 'views/admin/projects/my_manage.php', // done 2
    APPPATH . 'views/admin/projects/my_view.php', // done 2
    APPPATH . 'views/admin/projects/my_project.php', // done 2
    APPPATH . 'views/admin/projects/my_project_overview.php', // done 2
    APPPATH . 'views/admin/clients/groups/my_projects.php', // done 2
    APPPATH . 'views/admin/clients/my_manage.php', //done 1
    APPPATH . 'views/admin/tables/my_clients.php', //done 1
    APPPATH . 'views/admin/clients/groups/my_vault.php',//done 1
    APPPATH . 'views/admin/tables/my_staff.php', //done 1
    APPPATH . 'views/admin/staff/my_manage.php', //done1
    APPPATH . 'views/admin/clients/groups/my_tasks.php', //done 1
    APPPATH . 'views/admin/staff/my_timesheets.php', //done 1
    APPPATH . 'views/admin/tables/my_staff_timesheets.php', //done 1
    APPPATH . 'views/admin/tasks/my_filters.php', //done 1

    // this is that files which is used by system but not in this module 
    APPPATH . 'views/admin/clients/groups/my_contracts.php', //done 1
    APPPATH . 'views/admin/clients/my_client_js.php', //done 1
    APPPATH . 'views/admin/contracts/my_manage.php', // done 1
    APPPATH . 'views/admin/contracts/my_table_html.php', // done 1
    APPPATH . 'views/admin/tables/my_contracts.php', // done 1
    // this is that files which is used by system but not in this module 
);

foreach ($unlink_files as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
