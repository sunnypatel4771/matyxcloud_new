<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel_s">
                    <div class="panel-body">

                    <div class="row">
                        <div class="col-sm-12">
                            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">Vault</h4>
                            <hr>
                        </div>

                        <div class="col-sm-12" style="margin-bottom: 15px;">
                            <div class="row">
                                <div class="col-sm-3">
                                    <!-- customer filter  -->
                                     <?php
                                         $CI = &get_instance();
                                         $CI->load->model('clients_model');
                                         $clients = $CI->clients_model->get();
                                         echo render_select('client_filter', $clients, ['userid', 'company'], 'Customer');
                                     ?>
                                    <!-- customer filter  -->
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                        $CI = &get_instance();
                                        $CI->load->model('contracts_model');
                                        $contracts = $CI->contracts_model->get();
                                        echo render_select('contract_filter', $contracts, ['id', 'subject'], 'Contract');
                                    ?>
                                </div>
                                <div class="col-sm-3">
                                    <?php
                                        $roboform_options = [
                                            ['id' => 'yes', 'name' => 'Yes'],
                                            ['id' => 'no', 'name' => 'No'],
                                        ];
                                        echo render_select('roboform_filter', $roboform_options, ['id', 'name'], 'Roboform');
                                    ?>

                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group" app-field-wrapper="vault_category_filter">
                                        <label for="vault_category_filter" class="control-label">Vault Category</label>
                                        <div class="dropdown bootstrap-select show-tick bs3" style="width: 100%;">
                                            <select id="vault_category_filter" name="vault_category_filter" class="selectpicker" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true" tabindex="-98" multiple>
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
                                </div>
                            </div>
                            <hr>
                        </div>

                    </div>

                    <?php
                        $table_data = [
                            _l('id'),
                            _l('client'),
                            _l('contract'),
                            'Roboform',
                            'Vault Category',
                            'Server Address',
                            'Username',
                            'Password',
                            'Short Description',
                            'Port',
                        ];
                        render_datatable($table_data, 'vault_table');
                    ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



    <div class="modal fade" id="vault_password_show_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content width-100">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        Password
                    </h4>
                </div>
                <div class="modal-body vault_password_show_div">
                </div>
            </div>
        </div>
    </div>





<?php init_tail(); ?>

<script>

    $(document).on("change", "#client_filter, #contract_filter, #roboform_filter, #vault_category_filter", function () {
        var val = $(this).val();
        var vault_table = $(".table-vault_table");
        dt_custom_view(val, vault_table);
    });


    var vault_tableServerParams = {
        client_filter: "[name='client_filter']",
        contract_filter: "[name='contract_filter']",
        roboform_filter: "[name='roboform_filter']",
        vault_category_filter: "[name='vault_category_filter']",
    };


    initDataTable('.table-vault_table', admin_url + 'task_customize/vault_table' , undefined , undefined , vault_tableServerParams);


    $(document).on("click" , ".vault_password_view" , function(){
        var vault_id = $(this).data('id');
        if (vault_id != '') {
            $.ajax({
                url: admin_url + 'task_customize/copy_vault_password/' + vault_id,
                type: "POST",
                dataType: 'json',
                success: function (response) {
                    if (response.password) {
                        $("#vault_password_show_modal .vault_password_show_div").html('<p>Password: '+ response.password +'</p>');
                    }
                    $("#vault_password_show_modal").modal("show");
                },
            })
        }
    })

    function copyToClipboard(username) {
        if (!username) return;

        const text = username.trim();

        navigator.clipboard.writeText(text)
            .then(() => {
                alert_float('success', 'Username copied');
            })
            .catch(() => {
                alert_float('danger', 'Copy failed');
            });
    }


    function copyToClipboard(text) {
        if (!text) return;

        navigator.clipboard.writeText(text.trim())
            .then(() => alert_float('success', 'Copied to clipboard'))
            .catch(() => alert_float('danger', 'Copy failed'));
    }


</script>