<div class="panel_s">
    <?php 
    $clientid = isset($project->clientid) ? $project->clientid : '';
    ?>
    <input type="hidden" id="project_id_tab_vault" value="<?php echo $project->id; ?>">
    <?php 
    if ($clientid != '') { ?>
        <button class="btn btn-primary mbot15" data-toggle="modal" data-target="#entryModal" style="margin-top: 15px; margin-left: 15px;">
            <i class="fa-regular fa-plus tw-mr-1"></i> <?php echo _l('new_vault_entry'); ?>
        </button>    
        <div class="modal fade" id="entryModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <?php echo form_open(admin_url('clients/vault_entry_create/' . $clientid), ['data-create-url' => admin_url('clients/vault_entry_create/' . $clientid), 'data-update-url' => admin_url('clients/vault_entry_update')]); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo _l('vault_entry'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                        <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                        <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value=''
                            tabindex="-1" />
                        <?php echo render_input('server_address', 'server_address'); ?>
                        <input type="hidden" name="roboform" value="0">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" id="roboform" value="1">
                            <label for="roboform">Roboform</label>
                        </div>
                        <?php echo render_input('port', 'port', '', 'number'); ?>
                        <?php echo render_input('username', 'vault_username'); ?>
                        <?php echo render_input('password', 'vault_password', '', 'password'); ?>
                        <div id="vault_password_change_notice" class="help-block text-muted vault_password_change_notice hide">
                            <span class="text-muted tw-text-sm"><?php echo _l('password_change_fill_notice'); ?></span>
                        </div>
                        <?php echo render_textarea('description', 'vault_description'); ?>


                        <!-- new field for category with multiple select -->
                        <input type="hidden" name="vault_category" id="vault_category">

                        <div class="form-group" app-field-wrapper="vault_category_multi">
                        <label for="vault_category_multi" class="control-label">Vault Category</label>
                        <div class="dropdown bootstrap-select show-tick bs3" style="width: 100%;">
                            <select id="vault_category_multi" class="selectpicker" multiple="1" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true" tabindex="-98">
                                <option value=""></option>
                                <option value="1">Domain Registrar</option>
                                <option value="2">DNS</option>
                                <option value="3">Hosting</option>
                                <option value="4">Website Login</option>
                                <option value="5">GA4/GSC</option>
                                <option value="6">Google Business Profile</option>
                                <option value="7">Google Ads</option>
                                <option value="8">Meta</option>
                                <option value="9">Other</option>
                            </select>
                        </div>
                    </div>

                        <div id="vault_fields_wrapper">

                            <div class="form-group vault-field" data-id="1" style="display:none;">
                                <label>Domain Registrar</label>
                                <input type="text" name="domain_registrar" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="2" style="display:none;">
                                <label>DNS</label>
                                <input type="text" name="dns" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="3" style="display:none;">
                                <label>Hosting</label>
                                <input type="text" name="hosting" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="4" style="display:none;">
                                <label>Website Login</label>
                                <input type="text" name="website_login" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="5" style="display:none;">
                                <label>GA4 / GSC</label>
                                <input type="text" name="ga4_gsc" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="6" style="display:none;">
                                <label>Google Business Profile</label>
                                <input type="text" name="google_business_profile" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="7" style="display:none;">
                                <label>Google Ads</label>
                                <input type="text" name="google_ads" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="8" style="display:none;">
                                <label>Meta</label>
                                <input type="text" name="meta" class="form-control">
                            </div>

                            <div class="form-group vault-field" data-id="9" style="display:none;">
                                <label>Other</label>
                                <input type="text" name="other" class="form-control">
                            </div>

                        </div>


                        <!-- new field for category with multiple select -->

                         <?php 
                            $CI = &get_instance();
                            $CI->load->model('contracts_model');
                            $contracts = $CI->contracts_model->get('', ['client' => $clientid]);
                        ?>
                        <!-- select contract -->

                        <?php
                            echo render_select('contract', $contracts, ['id', 'subject'], 'contract');
                        ?>

                        <!-- select contract -->



                        <hr />
                        <div class="radio radio-info">
                            <input type="radio" name="visibility" value="1" id="only_creator_visible_all" checked>
                            <label for="only_creator_visible_all"><?php echo _l('vault_entry_visible_to_all'); ?></label>
                        </div>
                        <div class="radio radio-info">
                            <input type="radio" name="visibility" value="2" id="only_creator_visible_administrators">
                            <label
                                for="only_creator_visible_administrators"><?php echo _l('vault_entry_visible_administrators'); ?></label>
                        </div>
                        <div class="radio radio-info">
                            <input type="radio" name="visibility" value="3" id="only_creator_visible_me">
                            <label for="only_creator_visible_me"><?php echo _l('vault_entry_visible_creator'); ?></label>
                        </div>
                        <hr />
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" id="share_in_projects" name="share_in_projects" checked value="1">
                            <label for="share_in_projects"><?php echo _l('vault_entry_share_on_projects'); ?></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                    </div>
                </div>
                <!-- /.modal-content -->
                <?php echo form_close(); ?>
            </div>
            <!-- /.modal-dialog -->
        </div>
    <?php  }
    ?>
    

    <div id='project_vault_tab_div'></div>
</div>



<?php
    hooks()->add_action('app_admin_footer', 'task_customize_hook_app_admin_footer_for_vault_tab');
    function task_customize_hook_app_admin_footer_for_vault_tab()
{?>
     <script>

         $(document).ready(function () {
    var project_id = $('#project_id_tab_vault').val();

    $.ajax({
        url: admin_url + 'task_customize/project_tab_vault',
        type: 'POST',
        data: { project_id: project_id },
        dataType: 'json',
        success: function (response) {
            $('#project_vault_tab_div').html(response.content);
        },
        error: function (xhr, status, error) {
            // console.error('AJAX Error: ' + status + error);
        }
    });


    window.copyToClipboard = function(elementId) {
        const el = document.getElementById(elementId);
        const text = el.innerText.trim();

        navigator.clipboard.writeText(text).then(function() {
            alert('Copied to clipboard');
        });
    };

    window.copyVaultPassword = function(entryId) {
        $.get(admin_url + 'task_customize/copy_vault_password/' + entryId)
            .done(function(res) {
                res = JSON.parse(res);

                if (!res.password) {
                    alert_float('danger', 'Unable to copy password');
                    return;
                }

                navigator.clipboard.writeText(res.password).then(function() {
                    alert_float('success', 'Password copied');
                });
            })
            .fail(function() {
                alert_float('danger', 'Error copying password');
            });
    };



     });


     var $entryModal = $('#entryModal');
        $(function() {

            appValidateForm($entryModal.find('form'), {
                server_address: 'required',
                vault_category: 'required',
            });
            setTimeout(function() {
                $($entryModal.find('form')).trigger('reinitialize.areYouSure');
            }, 1000)
            $entryModal.on('hidden.bs.modal', function() {
                var $form = $entryModal.find('form');
                $form.attr('action', $form.data('create-url'));
                $form.find('input[type="text"]').val('');
                $form.find('input[type="radio"]:first').prop('checked', true);
                $form.find('textarea').val('');
                $('#vault_password_change_notice').addClass('hide');
                $form.find('#password').rules('add', {
                    required: true
                });
                $form.find('#password').parents().find('.req').removeClass('hide');
                $form.find('#share_in_projects').prop('checked', true);
                $('#vault_category_multi').val('').change();
                $('#vault_category').val('');
                toggleVaultFields([]);
            });
        });


        function syncVaultCategory()
        {
            var selected = $('#vault_category_multi').val();

            if(selected && selected.length){
                $('#vault_category').val(selected.join(','));
            }else{
                $('#vault_category').val('');
            }
        }

        function edit_vault_entry(id) {
            $.get(admin_url + 'clients/get_vault_entry/' + id, function(response) {

                var $form = $entryModal.find('form');

                $form.attr('action', $form.data('update-url') + '/' + id);
                $form.find('#server_address').val(response.server_address);
                $form.find('#port').val(response.port);
                $form.find('#username').val(response.username);
                $form.find('#description').val(response.description);

                $form.find('#password').rules('remove');
                $form.find('#password').parents().find('.req').addClass('hide');

                $form.find('input[value="' + response.visibility + '"]').prop('checked', true);
                $form.find('#share_in_projects').prop('checked', (response.share_in_projects == 1 ? true : false));

                $('#vault_password_change_notice').removeClass('hide');

                if (response.roboform == 1) {
                    $('#roboform').prop('checked', true);
                    $('input[name="roboform"]').val(1);
                } else {
                    $('#roboform').prop('checked', false);
                    $('input[name="roboform"]').val(0);
                }

                // ====================================================
                // ⭐ RESTORE VAULT CATEGORY MULTI SELECT
                // ====================================================
                if(response.vault_category){

                    var categories = response.vault_category.split(',');

                    // set bootstrap select value
                    $('#vault_category_multi').selectpicker('val', categories);

                    // sync hidden input
                    $('#vault_category').val(response.vault_category);

                    // show only selected fields
                    toggleVaultFields(categories);

                }else{

                    $('#vault_category_multi').selectpicker('deselectAll');
                    $('#vault_category').val('');
                    toggleVaultFields([]);

                }

                // ====================================================
                // ⭐ AUTO FILL DYNAMIC FIELDS (clean scalable way)
                // ====================================================
                $('#vault_fields_wrapper input').each(function(){

                    var name = $(this).attr('name');

                    if(response[name] !== undefined){
                        $(this).val(response[name]);
                    }

                });

                $entryModal.modal('show');

            }, 'json');
        }


        $(function() {
            $('#roboform').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="roboform"]').val(1);
                } else {
                    $('input[name="roboform"]').val(0);
                }
            });
        });

        // new field for category with multiple select
        function toggleVaultFields(values)
        {
            // hide all first
            $(".vault-field").hide();

            if(!values || values.length === 0){
                return;
            }

            // show selected fields only
            $.each(values, function(i,val){
                $('.vault-field[data-id="'+val+'"]').show();
            });
        }

        $(function(){
            var $vaultSelect = $('#vault_category_multi');
            $vaultSelect.on('changed.bs.select', function(){
                var selected = $(this).val();
                syncVaultCategory();
                toggleVaultFields(selected);
            });

            $vaultSelect.on('change', function(){
                var selected = $(this).val();
                toggleVaultFields(selected);

            });

            $('#entryModal').on('shown.bs.modal', function(){
                var selected = $vaultSelect.val();
                toggleVaultFields(selected);

            });

        });


        $(function () {

            var $modal = $('#entryModal');
            var $form  = $modal.find('form');

            // When modal is fully hidden
            $modal.on('hidden.bs.modal', function () {

                // 1. Reset native form fields
                if ($form.length) {
                    $form[0].reset();
                }

                // 2. Clear validation errors (Perfex uses jquery validation)
                if ($form.data('validator')) {
                    $form.validate().resetForm();
                }

                // 3. Remove error classes added by Perfex
                $form.find('.has-error').removeClass('has-error');
                $form.find('.text-danger').remove();

                // 4. IMPORTANT: reset Perfex dirty-form tracking
                $form.trigger('change');
                window.onbeforeunload = null;

            });

        });

</script>
<?php
}
?>