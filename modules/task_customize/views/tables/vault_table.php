<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'vault.id as id',
    db_prefix() . 'clients.company as client_name',
    db_prefix() . 'contracts.subject as contract_subject',
    db_prefix() . 'vault.roboform as roboform',
    db_prefix() . 'vault.vault_category as vault_category',
    db_prefix() . 'vault.server_address as server_address',
    db_prefix() . 'vault.username as username',
    db_prefix() . 'vault.password as password',
    db_prefix() . 'vault.description as description',
    db_prefix() . 'vault.port as port',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'vault';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'vault.customer_id',
    'LEFT JOIN ' . db_prefix() . 'contracts ON ' . db_prefix() . 'contracts.id = ' . db_prefix() . 'vault.contract',

];

$where         = [];
$client_filter = $this->ci->input->post('client_filter');
if ($client_filter && $client_filter != '' && $client_filter > 0) {
    $where[] = 'AND ' . db_prefix() . 'vault.customer_id = ' . $client_filter;
}

$contract_filter = $this->ci->input->post('contract_filter');
if ($contract_filter && $contract_filter != '' && $contract_filter > 0) {
    $where[] = 'AND ' . db_prefix() . 'vault.contract = ' . $contract_filter;
}

$roboform_filter = $this->ci->input->post('roboform_filter');
if ($roboform_filter && $roboform_filter != '') {
    if ($roboform_filter == 'yes') {
        $where[] = 'AND ' . db_prefix() . 'vault.roboform = 1';
    } else if ($roboform_filter == 'no') {
        $where[] = 'AND (' . db_prefix() . 'vault.roboform = 0 OR ' . db_prefix() . 'vault.roboform IS NULL)';
    }
}

$vault_category_filter = $this->ci->input->post('vault_category_filter');

if (! empty($vault_category_filter)) {

    $category_conditions = [];

    foreach ($vault_category_filter as $cat) {
        $category_conditions[] = 'FIND_IN_SET("' . $cat . '", ' . db_prefix() . 'vault.vault_category)';
    }

    if (! empty($category_conditions)) {
        $where[] = 'AND (' . implode(' OR ', $category_conditions) . ')';
    }
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'vault.customer_id', db_prefix() . 'vault.contract']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];

    $row[] = $aRow['id'];
    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['customer_id']) . '">' . $aRow['client_name'] . '</a>';
    $row[] = '<a href="' . admin_url('contracts/contract/' . $aRow['customer_id']) . '">' . $aRow['contract_subject'] . '</a>';

    $roboform_yes_html = '<a target="_blank" href="https://online.roboform.com">Yes</a>';
    $row[] = $aRow['roboform'] == 1 ? $roboform_yes_html : 'No';

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

    $db_vault_category       = $aRow['vault_category']; // example: 1,2,3,
    $db_vault_category_array = array_filter(explode(',', $db_vault_category));
    $selected_names          = [];

    foreach ($vault_category as $cat) {
        if (in_array($cat['id'], $db_vault_category_array)) {
            $selected_names[] = $cat['name'];
        }
    }

    $row[] = ! empty($selected_names) ? implode(', ', $selected_names) : '-';

    $row[] = '<a target="_blank" href="' . $aRow['server_address'] . '">' . $aRow['server_address'] . '</a>';
    $row[] = '<div class="tw-flex tw-items-center tw-gap-2">
    <span>' . htmlspecialchars($aRow['username']) . '</span>

    <a href="#" 
       onclick="copyToClipboard(\'' . htmlspecialchars($aRow['username'], ENT_QUOTES) . '\'); return false;"
       class="text-muted"
       data-toggle="tooltip"
       data-title="Copy username"
       style="cursor:pointer;">
        <i class="fa-regular fa-copy"></i>
    </a>
</div>';



    // $CI = &get_instance();
    // $password = $CI->encryption->decrypt($aRow['password']);
    // $row[] = $password;

    // $CI       = &get_instance();
    // $password = $CI->encryption->decrypt($aRow['password']);

    // $masked_password = '........'; // what user sees in table
    // $vault_class     = is_admin() ? 'vault_password_view' : '';

    // // $row[] = '<span class="'. $vault_class .'" data-id="' . $aRow['id'] . '" style="cursor:pointer;color:#03a9f4;"> ' . $masked_password . ' </span>';
    // if ($password != '') {
    //     $row[] = '<span class="vault_password_view" data-id="' . $aRow['id'] . '" style="cursor:pointer;color:#03a9f4;"> ' . $masked_password . ' </span>';
    // } else {
    //     $row[] = '';
    // }

    $CI       = &get_instance();
    $password = $CI->encryption->decrypt($aRow['password']);

    $masked_password = '........';

    if ($password != '') {

        $row[] = '<div class="tw-flex tw-items-center tw-gap-2">

            <span class="vault_password_view"
                data-id="' . $aRow['id'] . '"
                style="cursor:pointer;color:#03a9f4;">
                ' . $masked_password . '
            </span>

            <a href="#"
            onclick="copyToClipboard(\'' . htmlspecialchars($password, ENT_QUOTES) . '\'); return false;"
            class="text-muted"
            data-toggle="tooltip"
            data-title="Copy password"
            style="cursor:pointer;">
                <i class="fa-regular fa-copy"></i>
            </a>

        </div>';

    } else {
        $row[] = '';
    }


    $description = $aRow['description'];
    if (strlen($description) > 50) {
        $description = substr($description, 0, 50) . '...';
    }
    $row[] = $description;
    $row[] = $aRow['port'];

    $output['aaData'][] = $row;

}

return $output;
