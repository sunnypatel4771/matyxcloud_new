

 <div class="panel_s">
<input type="hidden" id="project_id_tab_task_resource" value="<?php echo $project->id; ?>">
            <div id='project_task_resource_tab_div'></div>
 </div>

 <?php
     hooks()->add_action('app_admin_footer', 'task_customize_hook_app_admin_footer_for_task_resource_tab');
     function task_customize_hook_app_admin_footer_for_task_resource_tab()
 {?>
     <script>

         $(document).ready(function () {
    var project_id = $('#project_id_tab_task_resource').val();

    $.ajax({
        url: admin_url + 'task_customize/project_tab_task_resource',
        type: 'POST',
        data: { project_id: project_id },
        dataType: 'json',
        success: function (response) {
            $('#project_task_resource_tab_div').html(response.content);
            // ✅ remove toggle link
            $('#project_task_resource_tab_div')
                .find('#a_project_resource_toggle')
                .remove();

            // ✅ force content open
            $('#project_task_resource_tab_div')
                .find('#div_project_resource_toggle')
                .removeClass('hide')
                .show();
        },
        error: function (xhr, status, error) {
            // console.error('AJAX Error: ' + status + error);
        }
    });

     });
</script>
<?php
    }
?>
