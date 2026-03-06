<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="customer-profile-group-heading"><?php echo _l('tasks'); ?></h4>
<?php if (isset($client)) {
    ?>
    <a href="#" data-toggle="modal" data-target="#customer_task_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-rel-tasks">
                                <?php echo _l('bulk_actions'); ?>
                            </a>
    <?php
        init_relation_tasks_table_for_client_task(['data-new-rel-id' => $client->userid, 'data-new-rel-type' => 'customer']);
    }?>

<?php
    $CI = &get_instance();
    $CI->load->model('staff_model');
    $staff_members = $CI->staff_model->get();
?>
<div class="modal fade bulk_actions" id="customer_task_bulk_action" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="checkbox checkbox-danger">
                    <input type="checkbox" name="mass_delete" id="mass_delete">
                    <label for="mass_delete">Mass Delete</label>
                </div>
                <div class="row">


                    <div class="col-md-12">

                        <?php
                            echo render_select('assign', $staff_members, ['staffid', ['firstname', 'lastname']], 'Assign', '', ['multiple' => true]);
                        ?>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary"
                    onclick="customer_task_bulk_action(this);"><?php echo _l('confirm'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php
    hooks()->add_action('app_admin_footer', 'task_customize_hook_app_admin_footer_for_castomertask_bulk_action');
    function task_customize_hook_app_admin_footer_for_castomertask_bulk_action()
{?>
<script>

        function customer_task_bulk_action(event) {
            var r = confirm(app.lang.confirm_action_prompt);
            if (r == false) {
                return false;
            } else {
                var mass_delete = $('#mass_delete').prop('checked');
                var ids = [];
                var data = {};
                if (mass_delete == true) {
                    data.mass_delete = true;
                }
                var rows = $('.table-rel-tasks').find('tbody tr');
                $.each(rows, function() {
                    var checkbox = $($(this).find('td').eq(0)).find('input');
                    if (checkbox.prop('checked') == true) {
                        ids.push(checkbox.val());
                    }
                });
                data.ids = ids;
                
                data.assign = $('select[name="assign"]').val();

                // console.log('data', data);
                
                $.post(admin_url + 'task_customize/customer_tasks_bulk_action', data).done(function(res) {
                    var data = JSON.parse(res);
                    if (data.status == 1) {
                        $('#customer_task_bulk_action').modal('hide');
                        $('select[name="assign"]').val('').selectpicker('refresh');
                        alert_float('success', data.msg);
                        $('.table-rel-tasks').DataTable().ajax.reload(null, false);

                    }
                });

            }
        }

        // When modal fully closes
        $('#customer_task_bulk_action').on('hidden.bs.modal', function() {
            $('select[name="assign"]').val('').selectpicker('refresh');

        });
</script>

<?php }
?>