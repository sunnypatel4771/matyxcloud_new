<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create',  'staff')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('staff/member'); ?>" class="btn btn-primary" style="margin-bottom: 10px;">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_staff'); ?>
                        </a>

                        <?php
                        $CI = &get_instance();
                        $CI->load->model('roles_model');
                        $CI->load->model('departments_model');
                        $roles = $CI->roles_model->get();
                        $departments = $CI->departments_model->get();
                        $cus_roles = get_custom_fields('staff');
                        if (!empty($cus_roles) && isset($cus_roles[0]['options']) && !empty($cus_roles[0]['options'])) {
                            $cus_roles = explode(',', $cus_roles[0]['options']);
                        }
                        ?>


                    <?php } ?>

                    <div class="panel_s">
                        <div class="panel-body panel-table-full">

                            <!-- filter dropdowns  -->
                            <div style="width: 15%; display: inline-block; margin-bottom: 15px;">
                                <select name="role_filter" id="role_filter" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('role'); ?>">
                                    <option value="">Select Role</option>
                                    <?php
                                    foreach ($roles as $role) {
                                        echo '<option value="' . $role['roleid'] . '">' . $role['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div style="width: 15%; display: inline-block; margin-left: 10px; margin-bottom: 15px;">
                                <select name="status_filter" id="status_filter" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('status'); ?>">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">In Active</option>
                                </select>
                            </div>

                            <div style="width: 15%; display: inline-block; margin-left: 10px; margin-bottom: 15px;">
                                <select name="cus_roles_filter" id="cus_roles_filter" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('custom_roles'); ?>">
                                    <option value="">Select Role(s)</option>
                                    <?php foreach ($cus_roles as $role) { ?>
                                        <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                                    <?php } ?>
                                </select>
                            </div>


                            <div style="width: 15%; display: inline-block; margin-left: 10px; margin-bottom: 15px;">
                                <select name="department_filter" id="department_filter" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('department'); ?>">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $role) { ?>
                                        <option value="<?php echo $role['departmentid']; ?>"><?php echo $role['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <a href="#" data-toggle="modal" data-target="#staff_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-staff">
                                <?php echo _l('bulk_actions'); ?>
                            </a>

                            <div class="modal fade bulk_actions" id="staff_bulk_action" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                        </div>
                                        <div class="modal-body">




                                            <div class="row">
                                                <!-- for selecting role -->
                                                <?php
                                                // $staff_roles = [
                                                //     ['1' => 'Standard Worker'],
                                                //     ['1' => 'CAM Director'],
                                                //     ['1' => 'Paid Ads Director'],
                                                //     ['1' => 'SEO Director'],
                                                //     ['1' => 'Content Director'],
                                                //     ['1' => 'Content Manager'],
                                                //     ['1' => 'Web Director'],
                                                //     ['1' => 'Web Development Manager'],
                                                //     ['1' => 'Web Support Manager'],

                                                // ];


                                                $CI = &get_instance();
                                                $CI->load->model('custom_fields_model');
                                                $staff_roles = $CI->custom_fields_model->get(STAFF_ROLES);
                                                $staff_roles_option = [];
                                                if (isset($staff_roles->options) && $staff_roles->options != '') {
                                                    $staff_roles_option = explode(',', $staff_roles->options);
                                                }

                                                ?>
                                                <!-- <div class="col-md-12" style="width: 100%;">
                                                    <select name="role" id="role" class="selectpicker" style="width: 100%;">
                                                        <option value=""></option>
                                                        <?php
                                                        foreach ($staff_roles_option as $key => $value) { ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                                                        <?php }
                                                        ?>
                                                    </select>
                                                </div> -->

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role"><?php echo _l('role'); ?>(s)</label>

                                                        <select name="role"
                                                            id="role"
                                                            class="selectpicker"
                                                            data-width="100%"
                                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" multiple>

                                                            <option value=""></option>

                                                            <?php foreach ($staff_roles_option as $value) { ?>
                                                                <option value="<?php echo $value; ?>">
                                                                    <?php echo $value; ?>
                                                                </option>
                                                            <?php } ?>

                                                        </select>
                                                    </div>
                                                </div>


                                                <!-- for selecting department -->
                                                <div class="col-md-12">
                                                    <?php echo render_select('department', $departments, ['departmentid', 'name'], 'department', '', ['multiple' => 'multiple']); ?>
                                                </div>

                                                <!-- for selcting active/inactive -->
                                                <div class="col-md-12">
                                                    <?php
                                                    $status = [
                                                        ['id' => 'active', 'name' => _l('active')],
                                                        ['id' => 'inactive', 'name' => _l('inactive')],
                                                    ];
                                                    echo render_select('status', $status, ['id', 'name'], 'Status');
                                                    ?>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal"><?php echo _l('close'); ?></button>
                                            <button type="button" class="btn btn-primary"
                                                onclick="staff_bulk_action(this);"><?php echo _l('confirm'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <?php
                            $table_data = [
                                '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="staff"><label></label></div>',
                                _l('staff_dt_name'),
                                _l('staff_dt_email'),
                                _l('role'),
                                _l('department'),
                                _l('staff_dt_last_Login'),
                                _l('staff_dt_active'),
                            ];
                            $custom_fields = get_custom_fields('staff', ['show_on_table' => 1]);
                            foreach ($custom_fields as $field) {
                                array_push($table_data, [
                                    'name'     => $field['name'],
                                    'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                ]);
                            }
                            render_datatable($table_data, 'staff');
                            ?>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete_staff" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <?php echo form_open(admin_url('staff/delete', ['delete_staff_form'])); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="delete_id">
                        <?php echo form_hidden('id'); ?>
                    </div>
                    <p><?php echo _l('delete_staff_info'); ?></p>
                    <?php
                    echo render_select('transfer_data_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'staff_member', get_staff_user_id(), [], [], '', '', false);
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
                </div>
            </div><!-- /.modal-content -->
            <?php echo form_close(); ?>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php init_tail(); ?>
    <script>
        $(document).on("change", "#role_filter", function() {
            var val = $(this).val();
            var staff_table = $(".table-staff");
            dt_custom_view(val, staff_table);
        });

        $(document).on("change", "#status_filter", function() {
            var val = $(this).val();
            var staff_table = $(".table-staff");
            dt_custom_view(val, staff_table);
        });

        // cus_roles_filter
        $(document).on("change", "#cus_roles_filter", function() {
            var val = $(this).val();
            var staff_table = $(".table-staff");
            dt_custom_view(val, staff_table);
        });

        // department_filter
        $(document).on("change", "#department_filter", function() {
            var val = $(this).val();
            var staff_table = $(".table-staff");
            dt_custom_view(val, staff_table);
        });

        var staffServerParams = {
            role_filter: "[name='role_filter']",
            status_filter: "[name='status_filter']",
            cus_roles_filter: "[name='cus_roles_filter']",
            department_filter: "[name='department_filter']",
        };

        // $(function() {
        initDataTable('.table-staff', window.location.href, [0], [0], staffServerParams, ['1', 'asc']);
        // });

        function delete_staff_member(id) {
            $('#delete_staff').modal('show');
            $('#transfer_data_to').find('option').prop('disabled', false);
            $('#transfer_data_to').find('option[value="' + id + '"]').prop('disabled', true);
            $('#delete_staff .delete_id input').val(id);
            $('#transfer_data_to').selectpicker('refresh');
        }

        // $('body').on('click', '#mass_select_all', function(e) {
        //     e.stopPropagation(); // 🔥 THIS LINE FIXES IT
        // });

        $("body").on('change', '#mass_select_all', function() {
            var to, rows, checked;
            to = $(this).data('to-table');

            rows = $('.table-' + to).find('tbody tr');
            checked = $(this).prop('checked');
            $.each(rows, function() {
                var input = $($($(this).find('td').eq(0)).find('input'));
                if (!input.is(':disabled')) {
                    input.prop('checked', checked);
                }
            });
        });

        function staff_bulk_action(event) {
            var r = confirm(app.lang.confirm_action_prompt);
            if (r == false) {
                return false;
            } else {
                var ids = [];
                var data = {};
                var rows = $('.table-staff').find('tbody tr');
                $.each(rows, function() {
                    var checkbox = $($(this).find('td').eq(0)).find('input');
                    if (checkbox.prop('checked') == true) {
                        ids.push(checkbox.val());
                    }
                });
                data.ids = ids;
                data.role = $('select[name="role"]').val();
                data.department = $('select[name="department"]').val();
                data.status = $('select[name="status"]').val();

                $.post(admin_url + 'task_customize/staff_bulk_action', data).done(function(res) {
                    var data = JSON.parse(res);
                    if (data.status == 1) {
                        $('#staff_bulk_action').modal('hide');
                        $('select[name="role"]').val('').selectpicker('refresh');
                        $('select[name="department"]').val('').selectpicker('refresh');
                        $('select[name="status"]').val('').selectpicker('refresh');
                        alert_float('success', data.msg);
                        $('.table-staff').DataTable().ajax.reload(null, false);

                    }
                });

            }
        }

        // When modal fully closes
        $('#staff_bulk_action').on('hidden.bs.modal', function() {
            $('select[name="role"]').val('').selectpicker('refresh');
            $('select[name="department"]').val('').selectpicker('refresh');
            $('select[name="status"]').val('').selectpicker('refresh');

        });
    </script>
    </body>

    </html>