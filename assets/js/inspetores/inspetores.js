function inicio() {
    //Nova senha
    $('#btnNovaSenha').click(function () {
        var senhaContent = $(this).siblings('.position-relative.d-none');
        senhaContent.find('input[type=password]').attr('required', 'required');
        senhaContent.removeClass('d-none');
        $(this).remove();
    });

    updateImgChangeFile('fleImgAssinatura', 'imgAssinatura');
}