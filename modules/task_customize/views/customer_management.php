<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="customer-profile-group-heading">Customer Management</h4>

<?php echo form_open(admin_url('task_customize/update_customer_management')); ?>

<div class="row">
    <input type="hidden" name="client_id" value="<?php echo $client->userid; ?>">
    <div class="col-md-12">

        <?php
        $CI = &get_instance();
        $CI->load->model('staff_model');
        $staff = $CI->staff_model->get();

        echo render_select(
            'cam_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'CAM Assigned',
            $client->cam_id,
            []
        );

        echo render_select(
            'optimizer_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'Optimizer Assigned',
            $client->optimizer_id,
            []
        );

        echo render_select(
            'organic_social_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'Organic Social Lead',
            $client->organic_social_id,
            []
        );

        echo render_select(
            'seo_lead_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'SEO Lead',
            $client->seo_lead_id,
            []
        );

        echo render_select(
            'sale_rep_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'Sales Rep',
            $client->sale_rep_id,
            []
        );

        echo render_select(
            'content_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'Content Lead',
            $client->content_id,
            []
        );

        echo render_select(
            'web_lead_id',
            $staff,
            ['staffid', ['firstname', 'lastname']],
            'Web Lead',
            $client->web_lead_id,
            []
        );
        ?>

    </div>

    <div class="col-md-12" style="margin-top: 15px;">
        <button type="submit" class="btn btn-primary pull-right">
            Save
        </button>    
    </div>
</div>

<?php echo form_close(); ?>