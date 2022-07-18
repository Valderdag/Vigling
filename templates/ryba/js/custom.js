jQuery(function($) {

if ($('.registration form').length > 0) {
    // при загрузке скрываем поле Логин и подтв. пароля
    $('#jform_username, #jform_email2, #jform_password2').parents('.control-group').css('display', 'none');

    // при вводе email вводим в логин то же самое
    $('#jform_email1').on('input', function() {
        $('#jform_username').val($(this).val());
        $('#jform_email2').val($(this).val());
    });
}

    // при вводе пароля вводим в подтв. то же самое
    $('#jform_password1').on('change', function() {
        $('#jform_password2').val($(this).val());
    });
});