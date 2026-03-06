// const url = new URL(window.location.href);
// const segments = url.pathname.split('/').filter(Boolean);
// const projectId = segments[segments.length - 1];

// $(document).ready(function () {
//     var project_id = projectId;

//     $.ajax({
//         url: admin_url + 'task_customize/project_tab_task_resource',
//         type: 'POST',
//         data: { project_id: project_id },
//         dataType: 'json',
//         success: function (response) {
//             console.log('response', response);
//             $('#project_task_resource_tab_div').html(response.content);
//             // ✅ remove toggle link
//             $('#project_task_resource_tab_div')
//                 .find('#a_project_resource_toggle')
//                 .remove();

//             // ✅ force content open
//             $('#project_task_resource_tab_div')
//                 .find('#div_project_resource_toggle')
//                 .removeClass('hide')
//                 .show();
//         },
//         error: function (xhr, status, error) {
//             console.error('AJAX Error: ' + status + error);
//         }
//     });
// });

