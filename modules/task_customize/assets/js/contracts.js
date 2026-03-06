// $(function () {
//     $("select[name='custom_fields[contracts][28]']").on("change", function () {
//         let value = $(this).val();
//         $.post(admin_url + '/contracts', {
//             value: value,
//         }).done(function(response) {
//             response = JSON.parse(response);
//             console.log(response);
//             if (response.success) {
//                 $('.table-contracts').DataTable().ajax.reload(null, false);
//             }
//         });
//     });
// });
