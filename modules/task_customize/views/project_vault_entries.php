<?php
    if (! empty($vault_entries)) {
    foreach ($vault_entries as $entry) {?>
<div
        class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-overflow-hidden tw-mb-3 last:tw-mb-0 panel-vault">
        <div class="tw-flex tw-justify-between tw-items-center tw-px-6 tw-py-3 tw-border-b tw-border-solid tw-border-neutral-200 tw-bg-neutral-50"
            id="<?php echo 'vaultEntryHeading-' . e($entry['id']); ?>">
            <h4 class="tw-font-semibold tw-my-0 tw-text-lg">
                <?php echo e($entry['server_address']); ?>
            </h4>
            <!-- <div class="tw-flex-inline tw-items-center tw-space-x-2">
                <?php if ($entry['creator'] == get_staff_user_id() || is_admin()) {?>
                    <a href="#" onclick="edit_vault_entry(<?php echo e($entry['id']); ?>); return false;" class="text-muted">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                    <a href="<?php echo admin_url('clients/vault_entry_delete/' . $entry['id']); ?>"
                        class="text-danger _delete">
                        <i class="fa fa-remove"></i>
                    </a>
                <?php }?>
            </div> -->
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

                        $db_vault_category = $entry['vault_category']; // example: 1,2,3,
                        $db_vault_category_array = array_filter(explode(',', $db_vault_category));

                        $selected_names = [];

                        foreach ($vault_category as $cat) {
                            if (in_array($cat['id'], $db_vault_category_array)) {
                                $selected_names[] = $cat['name'];
                            }
                        }

                        echo !empty($selected_names) ? implode(', ', $selected_names) : '-';
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

                                if (!empty($entry[$field])) {
                                    echo '<br><b>'.$label.' :</b> ' . html_escape($entry[$field]);
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
<?php }
} else {
    echo '<div class="alert alert-info text-center">
        Vault entries not found for this customer.    </div>';
}
?>
