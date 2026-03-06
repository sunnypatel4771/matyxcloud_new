<?php

defined('BASEPATH') or exit('No direct script access allowed');

return App_table::find('projects')
    ->outputUsing(function ($params) {
        extract($params);

        $hasPermissionEdit   = staff_can('edit',  'projects');
        $hasPermissionDelete = staff_can('delete',  'projects');
        $hasPermissionCreate = staff_can('create',  'projects');
        $project_statuses = $this->ci->projects_model->get_project_statuses();

        $aColumns = [
            db_prefix() . 'projects.id as id',
            'name',
            get_sql_select_client_company(),
            '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'projects.id and rel_type="project" ORDER by tag_order ASC) as tags',
            'start_date',
            'deadline',
            '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM ' . db_prefix() . 'project_members JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'project_members.staff_id WHERE project_id=' . db_prefix() . 'projects.id ORDER BY staff_id) as members',
            'status',
        ];


        $sIndexColumn = 'id';
        $sTable       = db_prefix() . 'projects';

        $join = [
            'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'projects.clientid',
        ];

        $where  = [];

        if ($filtersWhere = $this->getWhereFromRules()) {
            $where[] = $filtersWhere;
        }

        if ($clientid != '') {
            array_push($where, ' AND clientid=' . $this->ci->db->escape_str($clientid));
        }

        if (staff_cant('view', 'projects')) {
            array_push($where, ' AND ' . db_prefix() . 'projects.id IN (SELECT project_id FROM ' . db_prefix() . 'project_members WHERE staff_id=' . get_staff_user_id() . ')');
        }

        $custom_fields = get_table_custom_fields('projects');

        foreach ($custom_fields as $key => $field) {
            $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
            $customFieldsColumns[$key] = [
                'slug' => $field['slug'],
                'name' => $selectAs,
                'id' => $field['id'],
                'options' => $field['options']
            ];
            array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
            array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'projects.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
        }

        $custom_view_type = $this->ci->input->post('custom_view_type');
        $filters = $this->ci->input->post('filters');

        if ($custom_view_type && in_array($custom_view_type, ['website', 'landing_page'])) {

            $services_included_alias = '';
            $project_service_alias   = '';

            foreach ($custom_fields as $key => $field) {
                if ($field['slug'] === 'projects_services_included') {
                    $services_included_alias = 'ctable_' . $key;
                }
                if ($field['slug'] === 'projects_service') {
                    $project_service_alias = 'ctable_' . $key;
                }
            }

            if ($custom_view_type === 'website' && $services_included_alias) {
                $where[] = 'AND ' . $services_included_alias . '.value LIKE "%Website%"';
            }

            if ($custom_view_type === 'landing_page') {
                if ($services_included_alias) {
                    $where[] = 'AND ' . $services_included_alias . '.value LIKE "%Landing Pages%"';
                }
                if ($project_service_alias) {
                    $where[] = 'AND ' . $project_service_alias . '.value = "Landing Page"';
                }
            }

            $status_in     = [];
            $status_not_in = [];

            if (!empty($filters['rules'])) {
                foreach ($filters['rules'] as $rule) {
                    if ($rule['id'] === 'status') {
                        if ($rule['operator'] === 'in') {
                            $status_in = array_map('intval', (array) $rule['value']);
                        }
                        if ($rule['operator'] === 'not_in') {
                            $status_not_in = array_map('intval', (array) $rule['value']);
                        }
                    }
                }
            }

            if (!empty($status_in)) {
                $where[] = 'AND ' . db_prefix() . 'projects.status IN (' . implode(',', $status_in) . ')';
            }

            if (!empty($status_not_in)) {
                $status_not_in = array_diff($status_not_in, $status_in);
                if (!empty($status_not_in)) {
                    $where[] = 'AND ' . db_prefix() . 'projects.status NOT IN (' . implode(',', $status_not_in) . ')';
                }
            }

            unset($_POST['filters']);
        }

        $aColumns = hooks()->apply_filters('projects_table_sql_columns', $aColumns);

        // Fix for big queries. Some hosting have max_join_limit
        if (count($custom_fields) > 4) {
            @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
            'clientid',
            '(SELECT GROUP_CONCAT(staff_id SEPARATOR ",") FROM ' . db_prefix() . 'project_members WHERE project_id=' . db_prefix() . 'projects.id ORDER BY staff_id) as members_ids',
        ]);

        $output  = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];

            $link = admin_url('projects/view/' . $aRow['id']);

            $row[] = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

            $name = '<a href="' . $link . '">' . e($aRow['name']) . '</a>';

            $name .= '<div class="row-options">';

            $name .= '<a href="' . $link . '">' . _l('view') . '</a>';

            if ($hasPermissionCreate && !$clientid) {
                $name .= ' | <a href="#" data-name="' . e($aRow['name']) . '" onclick="copy_project(' . $aRow['id'] . ', this);return false;">' . _l('copy_project') . '</a>';
            }

            if ($hasPermissionEdit) {
                $name .= ' | <a href="' . admin_url('projects/project/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }

            if ($hasPermissionDelete) {
                $name .= ' | <a href="' . admin_url('projects/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $name .= '</div>';

            $row[] = $name;

            $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . e($aRow['company']) . '</a>';

            $row[] = render_tags($aRow['tags']);

            $row[] = e(_d($aRow['start_date']));

            $row[] = e(_d($aRow['deadline']));



            $membersOutput = '<div class="tw-flex -tw-space-x-1">';
            $exportMembers = '';
            if ($aRow['members'] != '') {
                $members       = explode(',', $aRow['members']);

                foreach ($members as $key => $member) {
                    if ($member != '') {
                        $members_ids = explode(',', $aRow['members_ids']);
                        $member_id   = $members_ids[$key];
                        $membersOutput .= '<a href="' . admin_url('profile/' . $member_id) . '">' .
                            staff_profile_image($member_id, [
                                'tw-inline-block tw-h-7 tw-w-7 tw-rounded-full tw-ring-2 tw-ring-white',
                            ], 'small', [
                                'data-toggle' => 'tooltip',
                                'data-title'  => $member,
                            ]) . '</a>';
                        // For exporting
                        $exportMembers .= $member . ', ';
                    }
                }
            }

            $membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';
            $membersOutput .= '</div>';
            $row[] = $membersOutput;
            $canChangeStatus = (staff_can('edit',  'projects'));
            $outputStatus    = '';
            $status = get_project_status_by_id($aRow['status']);
            if ($canChangeStatus) {
                $outputStatus .= '<div class="dropdown inline-block table-export-exclude">';
                $outputStatus .= '<a href="#" class="dropdown-toggle label tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';" projects-status-table="' . e($aRow['status']) . '" id="tableProjectsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $outputStatus .= e($status['name']);
                $outputStatus .= '<i class="chevron tw-shrink-0"></i>';
                $outputStatus .= '</a>';

                $outputStatus .= '<ul class="dropdown-menu" aria-labelledby="tableProjectsStatus-' . $aRow['id'] . '">';
                foreach ($project_statuses as $projectChangeStatus) {
                    if ($aRow['status'] != $projectChangeStatus['id']) {
                        $outputStatus .= '<li>                                                          
                  <a href="#" onclick="project_mark_as(' . $projectChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                        ' . e(_l('project_mark_as', $projectChangeStatus['name'])) . '
                  </a>
               </li>';
                    }
                }
                $outputStatus .= '</ul>';
                $outputStatus .= '</div>';
            } else {
                $outputStatus .= '<span class="label" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';" task-status-table="' . e($aRow['status']) . '">' . e($status['name']) . '</span>';
            }

            $row[] = $outputStatus;

            // $status = get_project_status_by_id($aRow['status']);
            // $row[]  = '<span class="label project-status-' . $aRow['status'] . '" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';">' . e($status['name']) . '</span>';

            // Custom fields add values
            foreach ($customFieldsColumns as $customFieldColumn) {


                if ($customFieldColumn['id'] == PROJECT_SERVICES_INCLUDED && staff_can('edit',  'projects')) {

                    $options = explode(',', $customFieldColumn['options']);
                    $select_options = [];
                    foreach ($options as $option) {
                        $option = trim($option);
                        $select_options[] = ['id' => $option, 'name' => $option];
                    }

                    if ($aRow[$customFieldColumn['name']] != '') {
                        $selected = array_map('trim', explode(',', $aRow[$customFieldColumn['name']]));
                    } else {
                        $selected = [];
                    }


                    $row[] = render_select('service_include', $select_options, ['id', 'name'], '', $selected, ['multiple' => true, 'data-width' => '100%', 'onchange' => 'project_change_custom_field_value_multiselect(' . $aRow['id'] . ',' . $customFieldColumn['id'] . ',$(this).val())'], ['style' => 'width:200px;'], 'custom-field-select') . '<span class="hide">'.e(implode(',', $selected)).'</span>';
                } else if ($customFieldColumn['id'] == PROJECT_PRIORITY) {

                    $outputCustomPriority = '<div class="dropdown inline-block table-export-exclude">';
                    $outputCustomPriority .= '<a href="#" class="dropdown-toggle tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle" id="tableCustomPriority-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $outputCustomPriority .= e($aRow[$customFieldColumn['name']] ?? 'Please Select');
                    $outputCustomPriority .= '<i class="chevron tw-shrink-0"></i>';
                    $outputCustomPriority .= '</a>';

                    $outputCustomPriority .= '<ul class="dropdown-menu" aria-labelledby="tableCustomPriority-' . $aRow['id'] . '">';
                    $options = explode(',', $customFieldColumn['options']);
                    foreach ($options as $option) {
                        $option = trim($option);
                        // '' space add - 
                        if (strpos($option, ' ') !== false) {
                            $value = str_replace(' ', '-', $option);
                        } else {
                            $value = $option;
                        }


                        $outputCustomPriority .= '<li>
                            <a href="#" onclick="project_change_custom_field_value(' . $aRow['id'] . ',\'' . $customFieldColumn['id'] . '\',\'' . $value . '\'); return false;">
                                ' . e($option) . '
                            </a>
                        </li>';
                    }
                    $outputCustomPriority .= '</ul>';
                    $outputCustomPriority .= '</div>';
                    $row[] = $outputCustomPriority . '<span class="hide">' . e($aRow[$customFieldColumn['name']]) . '</span>';
                } else if ($customFieldColumn['id'] == PROJECT_PRIORITY_2 && staff_can('edit',  'projects')) {
                    $outputCustomPriority = '<div class="dropdown inline-block table-export-exclude">';
                    $outputCustomPriority .= '<a href="#" class="dropdown-toggle tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle" id="tableCustomPriority-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $outputCustomPriority .= e($aRow[$customFieldColumn['name']] ?? 'Please Select');
                    $outputCustomPriority .= '<i class="chevron tw-shrink-0"></i>';
                    $outputCustomPriority .= '</a>';

                    $outputCustomPriority .= '<ul class="dropdown-menu" aria-labelledby="tableCustomPriority-' . $aRow['id'] . '">';
                    $options = explode(',', $customFieldColumn['options']);
                    foreach ($options as $option) {
                        $option = trim($option);
                        // '' space add - 
                        if (strpos($option, ' ') !== false) {
                            $value = str_replace(' ', '-', $option);
                        } else {
                            $value = $option;
                        }
                        $outputCustomPriority .= '<li>
                            <a href="#" onclick="project_change_custom_field_value(' . $aRow['id'] . ',\'' . $customFieldColumn['id'] . '\',\'' . $value . '\'); return false;">
                                ' . e($option) . '
                            </a>
                        </li>';
                    }
                    $outputCustomPriority .= '</ul>';
                    $outputCustomPriority .= '</div>';
                    $row[] = $outputCustomPriority . '<span class="hide">' . e($aRow[$customFieldColumn['name']]) . '</span>';
                } else if ($customFieldColumn['id'] == PROJECT_STATUS_NOTE && staff_can('edit',  'projects')) {
                    //make text area  when typing save in database
                    /* <textarea class="form-control status_notes" rows="3"  data-custom-field-id="' . $customFieldColumn['id'] . '" data-project-id="' . $aRow['id'] . '">' . $aRow[$customFieldColumn['name']] . '</textarea> */
                    //  $row[] = '<a href="javascript:void(0);" class="project_status_note" data-custom-field-id="' . $customFieldColumn['id'] . '" data-custom-field-value="' . $aRow[$customFieldColumn['name']] . '" data-project-id="' . $aRow['id'] . '" ><i class="fa fa-comment"></i></a>';
                    $row[] = '<a href="javascript:void(0);" 
                    class="tw-ml-2 tw-text-neutral-500 hover:tw-text-neutral-700 project_status_note" data-custom-field-id="' . $customFieldColumn['id'] . '" data-custom-field-value="' . $aRow[$customFieldColumn['name']] . '" data-project-id="' . $aRow['id'] . '">
                    <i class="fa fa-comment"></i>
                </a>'. '<span class="hide">' . e($aRow[$customFieldColumn['name']]) . '</span>';
                } else if ($customFieldColumn['id'] == PROJECT_LAUNCH_ETA && staff_can('edit',  'projects')) {
                    $row[] = '<input name="project_launch_eta" tabindex="-1"
                            value="' . $aRow[$customFieldColumn['name']] . '"
                            id="project_launch_eta"
                            class="form-control project_launch_eta datepicker pointer tw-text-neutral-800" data-project_id="' . $aRow['id'] . '"
                        data-field_id="" style="width: 100%;">' . '<span class="hide">'.e($aRow[$customFieldColumn['name']]).'</span>';
                } else if ($customFieldColumn['id'] == STOPLIGHT_REPORT && staff_can('edit',  'projects')) {
                    // for stoplight report render select picker jp

                    $canChangeStatus = staff_can('edit', 'projects');
                    $outputStatus   = '';

                    $options       = explode(',', $customFieldColumn['options']);
                    $option_value  = trim((string) ($aRow[$customFieldColumn['name']] ?? ''));
                    $options_new = [];

                    foreach ($options as $value) {
                        $value = trim($value);

                        if ($value === 'ROADBLOCKS') {
                            $color = '#c41010';
                        } elseif ($value === 'POTENTIAL RISKS') {
                            $color = '#fcf82d';
                        } else {
                            $color = '#1acf29';
                        }

                        $options_new[] = [
                            'name'  => $value,
                            'color' => $color,
                        ];
                    }

                    $status_selected_data = [
                        'name'  => 'Nothing Selected',
                        'color' => '#9ca3af', // neutral gray
                    ];
                    foreach ($options_new as $value) {
                        if ($value['name'] === $option_value) {
                            $status_selected_data = $value;
                            break;
                        }
                    }

                    $color = $status_selected_data['color'];
                    $name  = $status_selected_data['name'];
                    if ($canChangeStatus) {

                        $outputStatus .= '<div class="dropdown inline-block table-export-exclude">';

                        $outputStatus .= '<a href="#" 
                            class="dropdown-toggle label tw-flex tw-items-center tw-gap-1 tw-flex-nowrap hover:tw-opacity-80 tw-align-middle"
                            style="
                                color:' . $color . ';
                                border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';
                                background:' . adjust_hex_brightness($color, 0.04) . ';
                            "
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false">';

                        $outputStatus .= e($name);
                        $outputStatus .= '<i class="chevron tw-shrink-0"></i>';
                        $outputStatus .= '</a>';

                        $outputStatus .= '<ul class="dropdown-menu" aria-labelledby="tableProjectsStatus-' . $aRow['id'] . '">';

                        foreach ($options_new as $projectChangeStatus) {
                            if ($option_value != $projectChangeStatus['name']) {
                                $outputStatus .=
                                    '<li>
                                <a href="#"
                                class="change-custom-field"
                                data-project-id="' . (int) $aRow['id'] . '"
                                data-field-id="' . (int) $customFieldColumn['id'] . '"
                                data-value="' . e($projectChangeStatus['name']) . '">
                                    ' . e($projectChangeStatus['name']) . '
                                </a>
                            </li>';
                            }
                        }

                        $outputStatus .= '</ul>';
                        $outputStatus .= '</div>';
                    } else {

                        $outputStatus .= '
                            <span class="label"
                                style="
                                    color:' . $color . ';
                                    border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';
                                    background:' . adjust_hex_brightness($color, 0.04) . ';
                                ">
                                ' . e($name) . '
                            </span>';
                    }

                    $row[] = $outputStatus . '<span class="hide">'.e($option_value).'</span>';

                    // for stoplight report render select picker 

                } else if ($customFieldColumn['id'] == CAM_MEETING_DATE && staff_can('edit',  'projects')) {
                    $row[] = '<input name="cam_meeting_date" tabindex="-1"
                            value="' . $aRow[$customFieldColumn['name']] . '"
                            id="cam_meeting_date"
                            class="form-control cam_meeting_date datepicker pointer tw-text-neutral-800" data-project_id="' . $aRow['id'] . '"
                        data-field_id="" style="width: 100%;">'. '<span class="hide">' . e($aRow[$customFieldColumn['name']]) . '</span>';

                } else {
                    $row[] = (strpos($customFieldColumn['name'], 'date_picker_') !== false ? _d($aRow[$customFieldColumn['name']]) : $aRow[$customFieldColumn['name']]);
                }
            }

            $row[] = get_active_days($aRow['id']);

            $row['DT_RowClass'] = 'has-row-options';

            $row = hooks()->apply_filters('projects_table_row_data', $row, $aRow);

            $output['aaData'][] = $row;
        }
        return $output;
    })->setRules([
        App_table_filter::new('name', 'TextRule')->label(_l('project_name')),
        App_table_filter::new('start_date', 'DateRule')->label(_l('project_start_date')),
        App_table_filter::new('deadline', 'DateRule')->label(_l('project_deadline')),
        App_table_filter::new('billing_type', 'SelectRule')->label(_l('project_billing_type'))->options(function ($ci) {
            return [
                ['value' => 1, 'label' => _l('project_billing_type_fixed_cost')],
                ['value' => 2, 'label' => _l('project_billing_type_project_hours')],
                ['value' => 3, 'label' => _l('project_billing_type_project_task_hours_hourly_rate')],
            ];
        }),

        App_table_filter::new('status', 'MultiSelectRule')->label(_l('project_status'))->options(function ($ci) {
            return collect($ci->projects_model->get_project_statuses())->map(fn($data) => [
                'value' => $data['id'],
                'label' => $data['name'],
            ])->all();
        }),

        App_table_filter::new('members', 'MultiSelectRule')->label(_l('project_members'))
            ->isVisible(fn() => staff_can('view', 'projects'))
            ->options(function ($ci) {
                return collect($ci->projects_model->get_distinct_projects_members())->map(function ($staff) {
                    return [
                        'value' => $staff['staff_id'],
                        'label' => get_staff_full_name($staff['staff_id'])
                    ];
                })->all();
            })->raw(function ($value, $operator, $sqlOperator) {
                $dbPrefix = db_prefix();
                $sqlOperator = $sqlOperator['operator'];
                return "({$dbPrefix}projects.id IN (SELECT project_id FROM {$dbPrefix}project_members WHERE staff_id $sqlOperator ('" . implode("','", $value) . "')))";
            }),
            App_table_filter::new('cam_id', 'SelectRule')
                ->label(_l('cam_id'))
                ->options(function ($ci) {
                    $ci->load->model('staff_model');

                    return collect($ci->staff_model->get())->map(fn($staff) => [
                        'value' => $staff['staffid'],
                        'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                    ]);
                })
                ->raw(function ($value) {
                    return db_prefix() . 'projects.cam_id = ' . (int) $value;
                }),

        App_table_filter::new('optimizer_id', 'SelectRule')
            ->label(_l('optimizer_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.optimizer_id = ' . (int) $value;
            }),

        App_table_filter::new('organic_social_id', 'SelectRule')
            ->label(_l('organic_social_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.organic_social_id = ' . (int) $value;
            }),

        App_table_filter::new('seo_lead_id', 'SelectRule')
            ->label(_l('seo_lead_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.seo_lead_id = ' . (int) $value;
            }),

        App_table_filter::new('sale_rep_id', 'SelectRule')
            ->label(_l('sale_rep_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.sale_rep_id = ' . (int) $value;
            }),

        App_table_filter::new('content_id', 'SelectRule')
            ->label(_l('content_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.content_id = ' . (int) $value;
            }),

        App_table_filter::new('web_lead_id', 'SelectRule')
            ->label(_l('web_lead_id'))
            ->options(function ($ci) {
                $ci->load->model('staff_model');

                return collect($ci->staff_model->get())->map(fn($staff) => [
                    'value' => $staff['staffid'],
                    'label' => $staff['firstname'] . ' ' . $staff['lastname'],
                ]);
            })
            ->raw(function ($value) {
                return db_prefix() . 'projects.web_lead_id = ' . (int) $value;
            }),
    ]);
