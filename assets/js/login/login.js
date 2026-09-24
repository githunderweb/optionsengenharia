//Exibir ocultar senha
$('.btnTogglePassword').click(function () {
    $(this).find('svg').toggleClass('fa-eye-slash');
    $(this).find('svg').toggleClass('fa-eye');
    $(this).parent().find('input').attr('type', function (index, attr) {
        return attr == 'password' ? 'text' : 'password';
    });
});