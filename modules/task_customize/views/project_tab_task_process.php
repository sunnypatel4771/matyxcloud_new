

 <div class="panel_s">
    <input type="hidden" id="project_id_tab_task_process" value="<?php echo $project->id; ?>">

            <div id='project_task_process_tab_div'></div>
 </div>

 <?php
     hooks()->add_action('app_admin_footer', 'task_customize_hook_app_admin_footer_for_task_process_tab');
     function task_customize_hook_app_admin_footer_for_task_process_tab()
 {?>
     <script>

         $(document).ready(function () {
    var project_id = $('#project_id_tab_task_process').val();

    $.ajax({
        url: admin_url + 'task_customize/project_tab_task_process',
        type: 'POST',
        data: { project_id: project_id },
        dataType: 'json',
        success: function (response) {
            $('#project_task_process_tab_div').html(response.content);
            // ✅ remove toggle link (Show Task Process)
            $('#project_task_process_tab_div').find('#a_project_diagram_toggle').remove();

            // ✅ force open content
            $('#project_task_process_tab_div')
                .find('#div_project_diagram_toggle')
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

