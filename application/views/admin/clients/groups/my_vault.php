<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="customer-profile-group-heading"><?php echo _l('vault'); ?></h4>
<button class="btn btn-primary mbot15" data-toggle="modal" data-target="#entryModal">
    <i class="fa-regular fa-plus tw-mr-1"></i> <?php echo _l('new_vault_entry'); ?>
</button>
<?php if (count($vault_entries) == 0) {?>
    <div class="alert alert-info text-center">
        <?php echo _l('no_vault_entries'); ?>
    </div>
<?php }?>
<?php foreach ($vault_entries as $entry) {?>
    <div
        class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-overflow-hidden tw-mb-3 last:tw-mb-0 panel-vault">
        <div class="tw-flex tw-justify-between tw-items-center tw-px-6 tw-py-3 tw-border-b tw-border-solid tw-border-neutral-200 tw-bg-neutral-50"
            id="<?php echo 'vaultEntryHeading-' . e($entry['id']); ?>">
            <h4 class="tw-font-semibold tw-my-0 tw-text-lg">
                <?php echo e($entry['server_address']); ?>
            </h4>
            <div class="tw-flex-inline tw-items-center tw-space-x-2">
                <?php if ($entry['creator'] == get_staff_user_id() || is_admin()) {?>
                    <a href="#" onclick="edit_vault_entry(<?php echo e($entry['id']); ?>); return false;" class="text-muted">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                    <a href="<?php echo admin_url('clients/vault_entry_delete/' . $entry['id']); ?>"
                        class="text-danger _delete">
                        <i class="fa fa-remove"></i>
                    </a>
                <?php }?>
            </div>
        </div>
        <div id="<?php echo 'vaultEntry-' . $entry['id']; ?>" class="tw-p-6">
            <div class="row">
                <div class="col-md-6 border-right">
                    <p class="tw-mb-1">
                        <b><?php echo _l('server_address'); ?>: </b>
                        <a href="<?php echo $entry['server_address'] ?>"
                            target="_blank">
                            <?php echo e($entry['server_address']); ?>
                        </a>
                    </p>
                    <p class="tw-mb-1">
                        <b>Roboform: </b><?php echo e($entry['roboform'] == 1 ? 'Yes' : 'No'); ?>
                    </p>
                    <p class="tw-mb-1">
                        <b><?php echo _l('port'); ?>:
                        </b><?php echo e(! empty($entry['port']) ? e($entry['port']) : _l('no_port_provided')); ?>
                    </p>
                    <p class="tw-mb-1">
                        <b><?php echo _l('vault_username'); ?>: </b>
                        <?php // echo e($entry['username']);
                            ?>
                        <span id="vault-username-<?php echo e($entry['id']); ?>">
                            <?php echo e($entry['username']); ?>
                        </span>
                        <a href="#"
                            onclick="copyToClipboard('vault-username-<?php echo e($entry['id']); ?>'); return false;"
                            class="tw-ml-2 text-muted"
                            data-toggle="tooltip"
                            data-title="Copy username">
                            <i class="fa-regular fa-copy"></i>
                        </a>
                    </p>
                    <!-- <p class="tw-mb-1">
                        <b><?php echo _l('vault_password'); ?>: </b><span class="vault-password-fake">
                            <?php echo str_repeat('&bull;', 10); ?> </span><span class="vault-password-encrypted"></span> <a
                            href="#" class="vault-view-password mleft10" data-toggle="tooltip"
                            data-title="<?php echo _l('view_password'); ?>"
                            onclick="vault_re_enter_password(<?php echo e($entry['id']); ?>,this); return false;"><i
                                class="fa fa-lock" aria-hidden="true"></i></a>
                    </p> -->
                    <p class="tw-mb-1">
                        <b><?php echo _l('vault_password'); ?>: </b>

                        <!-- Fake password (dots) -->
                        <span class="vault-password-fake">
                            <?php echo str_repeat('&bull;', 10); ?>
                        </span>

                        <!-- Real password (injected after unlock) -->
                        <span class="vault-password-encrypted hide"></span>

                        <!-- Unlock button -->
                        <a href="#"
                            class="vault-view-password mleft10"
                            data-toggle="tooltip"
                            data-title="<?php echo _l('view_password'); ?>"
                            onclick="vault_re_enter_password(<?php echo e($entry['id']); ?>, this); return false;">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </a>

                        <!-- Copy password button -->
                        <a href="#"
                            class="tw-ml-2 text-muted"
                            data-toggle="tooltip"
                            data-title="Copy password"
                            onclick="copyVaultPassword(<?php echo e($entry['id']); ?>); return false;">
                            <i class="fa-regular fa-copy"></i>
                        </a>
                    </p>

                    <p class="tw-mb-1">
                        <b>Contract :</b>

                        <?php
                            if (isset($entry['contract']) && $entry['contract'] != 0) {

                                    $CI = &get_instance();
                                    $CI->load->model('contracts_model');
                                    $contracts = $CI->contracts_model->get($entry['contract']);
                                    if (isset($contracts->subject)) {
                                        $contract_subject = $contracts->subject;
                                        echo '<a href="' . admin_url('contracts/contract/' . $entry['contract']) . '" target="_blank">' . $contract_subject . '</a>';
                                    }
                                } else {
                                    echo '-';
                                }
                            ?>
                    </p>

                    <p class="tw-mb-1">
                        <b>Vault Category :</b>

                        <?php
                            $vault_category = [
                                    ['id' => 1, 'name' => 'Domain Registrar'],
                                    ['id' => 2, 'name' => 'DNS'],
                                    ['id' => 3, 'name' => 'Hosting'],
                                    ['id' => 4, 'name' => 'Website Login'],
                                    ['id' => 5, 'name' => 'GA4/GSC'],
                                    ['id' => 6, 'name' => 'Google Business Profile'],
                                    ['id' => 7, 'name' => 'Google Ads'],
                                    ['id' => 8, 'name' => 'Meta'],
                                    ['id' => 9, 'name' => 'Other'],
                                ];

                                $db_vault_category       = $entry['vault_category']; // example: 1,2,3,
                                $db_vault_category_array = array_filter(explode(',', $db_vault_category));

                                $selected_names = [];

                                foreach ($vault_category as $cat) {
                                    if (in_array($cat['id'], $db_vault_category_array)) {
                                        $selected_names[] = $cat['name'];
                                    }
                                }

                                echo ! empty($selected_names) ? implode(', ', $selected_names) : '-';
                            ?>
                    </p>

                    <p class="tw-mb-1">
                        <b>Vault Details :</b>

                        <?php

                                // map category id => label + db field name
                                $vault_fields_map = [
                                    1 => ['label' => 'Domain Registrar', 'field' => 'domain_registrar'],
                                    2 => ['label' => 'DNS', 'field' => 'dns'],
                                    3 => ['label' => 'Hosting', 'field' => 'hosting'],
                                    4 => ['label' => 'Website Login', 'field' => 'website_login'],
                                    5 => ['label' => 'GA4 / GSC', 'field' => 'ga4_gsc'],
                                    6 => ['label' => 'Google Business Profile', 'field' => 'google_business_profile'],
                                    7 => ['label' => 'Google Ads', 'field' => 'google_ads'],
                                    8 => ['label' => 'Meta', 'field' => 'meta'],
                                    9 => ['label' => 'Other', 'field' => 'other'],
                                ];

                                $db_vault_category_array = array_filter(explode(',', $entry['vault_category']));

                                foreach ($db_vault_category_array as $cat_id) {

                                    if (isset($vault_fields_map[$cat_id])) {

                                        $label = $vault_fields_map[$cat_id]['label'];
                                        $field = $vault_fields_map[$cat_id]['field'];

                                        if (! empty($entry[$field])) {
                                            echo '<br><b>' . $label . ' :</b> ' . html_escape($entry[$field]);
                                        }
                                    }
                                }

                            ?>
                    </p>




                </div>
                <div class="col-md-6 text-center">
                    <?php if (! empty($entry['description'])) {?>
                        <p>
                            <b><?php echo _l('vault_description'); ?>: </b><br /><?php echo process_text_content_for_display($entry['description']); ?>
                        </p>
                        <hr />
                    <?php }?>
                    <p class="text-muted"><?php echo e(_l('vault_entry_created_from', $entry['creator_name'])); ?> -
                        <span class="text-has-action" data-toggle="tooltip"
                            data-title="<?php echo e(_dt($entry['date_created'])); ?>">
                            <?php echo e(time_ago($entry['date_created'])); ?>
                        </span>
                    </p>
                    <p>
                        <?php if (! empty($entry['last_updated_from'])) {?>
                    <p class="text-muted no-mbot">
                        <?php echo _l('vault_entry_last_update', $entry['last_updated_from']); ?> -
                        <span class="text-has-action" data-toggle="tooltip"
                            data-title="<?php echo e(_dt($entry['last_updated'])); ?>">
                            <?php echo e(time_ago($entry['last_updated'])); ?>
                    </p>
                    </span>
                    <p>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
<?php }?>

<!-- get contract data related to customer  -->
 <?php
     $CI = &get_instance();
     $CI->load->model('contracts_model');
     $contracts = $CI->contracts_model->get('', ['client' => $client->userid]);
 ?>

<!-- get contract data related to customer  -->

<div class="modal fade" id="entryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('clients/vault_entry_create/' . $client->userid), ['data-create-url' => admin_url('clients/vault_entry_create/' . $client->userid), 'data-update-url' => admin_url('clients/vault_entry_update')]); ?>
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

                <?php
                    // $multipleSelectList = [
                    //     ['id' => 1, 'name' => 'Domain Registrar'],
                    //     ['id' => 2, 'name' => 'DNS'],
                    //     ['id' => 3, 'name' => 'Hosting'],
                    //     ['id' => 4, 'name' => 'Website Login'],
                    //     ['id' => 5, 'name' => 'GA4/GSC'],
                    //     ['id' => 6, 'name' => 'Google Business Profile'],
                    //     ['id' => 7, 'name' => 'Google Ads'],
                    //     ['id' => 8, 'name' => 'Meta'],
                    //     ['id' => 9, 'name' => 'Other'],
                    // ];
                    // echo render_select('vault_category_multi', $multipleSelectList, ['id', 'name'], 'Vault Category', [], ['multiple' => true]);
                ?>

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
<!-- /.modal -->
<?php $this->load->view('admin/clients/vault_confirm_password'); ?>
<?php hooks()->add_action('app_admin_footer', 'vault_form_validate');
    function vault_form_validate()
{?>
    <script>
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
                $('#contract').val('').change();
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

        // function edit_vault_entry(id) {
        //     $.get(admin_url + 'clients/get_vault_entry/' + id, function(response) {
        //         var $form = $entryModal.find('form');
        //         $form.attr('action', $form.data('update-url') + '/' + id);
        //         $form.find('#server_address').val(response.server_address);
        //         $form.find('#port').val(response.port);
        //         $form.find('#username').val(response.username);
        //         $form.find('#description').val(response.description);
        //         $form.find('#password').rules('remove');
        //         $form.find('#password').parents().find('.req').addClass('hide');
        //         $form.find('input[value="' + response.visibility + '"]').prop('checked', true);
        //         $form.find('#share_in_projects').prop('checked', (response.share_in_projects == 1 ? true : false));
        //         $('#vault_password_change_notice').removeClass('hide');
        //         if (response.roboform == 1) {
        //             $('#roboform').prop('checked', true);
        //             $('input[name="roboform"]').val(1);
        //         } else {
        //             $('#roboform').prop('checked', false);
        //             $('input[name="roboform"]').val(0);
        //         }
        //         $entryModal.modal('show');
        //     }, 'json');
        // }

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

                $('#contract').selectpicker('val', response.contract);

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

        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            const text = el.innerText.trim(); // ✅ TRIM extra spaces

            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard');
            });
        }

        function copyVaultPassword(entryId) {
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
        }


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
<?php }?>