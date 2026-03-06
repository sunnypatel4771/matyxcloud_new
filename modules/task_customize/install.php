<?php

defined('BASEPATH') or exit('No direct script access allowed');

$my_projects_path        = APPPATH . 'views/admin/tables/my_tasks.php';
$module_my_projects_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/task/my_tasks.php';
if (! file_exists($my_projects_path)) {
    copy($module_my_projects_path, $my_projects_path);
}

defined('BASEPATH') or exit('No direct script access allowed');

$my_projects_path        = APPPATH . 'views/admin/tasks/my_view_task_template.php';
$module_my_projects_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/task/my_view_task_template.php';
if (! file_exists($my_projects_path)) {
    copy($module_my_projects_path, $my_projects_path);
}

$my_projects_path        = APPPATH . 'views/admin/tables/my_projects.php';
$module_my_projects_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/my_projects.php';
if (! file_exists($my_projects_path)) {
    copy($module_my_projects_path, $my_projects_path);
}

//for projects
$my_manage_projects_path        = APPPATH . 'views/admin/projects/my_manage.php';
$module_my_manage_projects_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/projects/my_manage.php';
if (! file_exists($my_manage_projects_path)) {
    copy($module_my_manage_projects_path, $my_manage_projects_path);
}

//for projects view
$my_projects_view_path        = APPPATH . 'views/admin/projects/my_view.php';
$module_my_projects_view_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/projects/my_view.php';
if (! file_exists($my_projects_view_path)) {
    copy($module_my_projects_view_path, $my_projects_view_path);
}

//for project
$my_project_path        = APPPATH . 'views/admin/projects/my_project.php';
$module_my_project_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/projects/my_project.php';
if (! file_exists($my_project_path)) {
    copy($module_my_project_path, $my_project_path);
}

$my_project_groups_path        = APPPATH . 'views/admin/clients/groups/my_projects.php';
$module_my_project_groups_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/projects/my_projects.php';
if (! file_exists($my_project_groups_path)) {
    copy($module_my_project_groups_path, $my_project_groups_path);
}

//add colum in task table new colum name is_poked tinyint
if (! $CI->db->field_exists('is_poked', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `is_poked` TINYINT(1) NOT NULL DEFAULT 0;');
}

//for project overview
$my_project_overview_path        = APPPATH . 'views/admin/projects/my_project_overview.php';
$module_my_project_overview_path = module_dir_path(TASK_CUSTOMIZE_MODULE_NAME) . 'system_changes/projects/my_project_overview.php';
if (! file_exists($my_project_overview_path)) {
    copy($module_my_project_overview_path, $my_project_overview_path);
}

//add field in clients table
if (! $CI->db->field_exists('cam_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `cam_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('optimizer_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `optimizer_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('organic_social_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `organic_social_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('seo_lead_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `seo_lead_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('sale_rep_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `sale_rep_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('content_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `content_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('web_lead_id', db_prefix() . 'clients')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'clients` ADD `web_lead_id` INT(11) NULL DEFAULT;');
}
//tasks
if (! $CI->db->field_exists('cam_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `cam_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('optimizer_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `optimizer_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('organic_social_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `organic_social_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('seo_lead_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `seo_lead_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('sale_rep_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `sale_rep_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('content_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `content_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('web_lead_id', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `web_lead_id` INT(11) NULL DEFAULT;');
}
// projects
if (! $CI->db->field_exists('cam_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `cam_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('optimizer_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `optimizer_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('organic_social_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `organic_social_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('seo_lead_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `seo_lead_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('sale_rep_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `sale_rep_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('content_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `content_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('web_lead_id', db_prefix() . 'projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'projects` ADD `web_lead_id` INT(11) NULL DEFAULT;');
}

//contracts
if (! $CI->db->field_exists('cam_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `cam_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('optimizer_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `optimizer_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('organic_social_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `organic_social_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('seo_lead_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `seo_lead_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('sale_rep_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `sale_rep_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('content_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `content_id` INT(11) NULL DEFAULT;');
}
if (! $CI->db->field_exists('web_lead_id', db_prefix() . 'contracts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contracts` ADD `web_lead_id` INT(11) NULL DEFAULT;');
}

if (! $CI->db->table_exists(db_prefix() . 'project_timer')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'project_timer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `pause_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (! $CI->db->field_exists('roboform', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `roboform` INT(11) DEFAULT NULL;');
}

// add filed for conditinal field in task table

if (! $CI->db->field_exists('follow_sop', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `follow_sop` TINYINT(1) NULL DEFAULT NULL;');
}

if (! $CI->db->field_exists('link_to_contact_opp_in_ghl', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `link_to_contact_opp_in_ghl` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('date_contact_entered_in_ghl', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `date_contact_entered_in_ghl` date DEFAULT NULL;');
}

if (! $CI->db->field_exists('screenshot_or_loom_url', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `screenshot_or_loom_url` varchar(255) DEFAULT NULL;');
}

// for vault

if (! $CI->db->field_exists('vault_category', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `vault_category` int(11) DEFAULT NULL;');
}

if (! $CI->db->field_exists('domain_registrar', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `domain_registrar` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('dns', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `dns` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('hosting', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `hosting` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('website_login', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `website_login` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('ga4_gsc', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `ga4_gsc` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('google_business_profile', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `google_business_profile` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('google_ads', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `google_ads` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('meta', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `meta` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('other', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `other` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('contract', db_prefix() . 'vault')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'vault` ADD `contract` int(11) DEFAULT NULL;');
}


if (! $CI->db->field_exists('why_not', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `why_not` text DEFAULT NULL;');
}

if (! $CI->db->field_exists('did_add_automation_issue', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `did_add_automation_issue` TINYINT(1) NULL DEFAULT NULL;');
}

if (! $CI->db->field_exists('auto_issue_link_to_contact_opp_in_ghl', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `auto_issue_link_to_contact_opp_in_ghl` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('auto_issue_date_contact_entered_in_ghl', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `auto_issue_date_contact_entered_in_ghl` date DEFAULT NULL;');
}

if (! $CI->db->field_exists('auto_issue_screenshot_or_loom_url', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `auto_issue_screenshot_or_loom_url` varchar(255) DEFAULT NULL;');
}

if (! $CI->db->field_exists('auto_issue_why_not', db_prefix() . 'tasks')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'tasks` ADD `auto_issue_why_not` text DEFAULT NULL;');
}