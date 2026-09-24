function inicio() {
    var formularioEdicaoUsuario = $('#btnSalvar').length > 0;
    var alteracaoSenhaAtiva = !$('#alterar-senha .row').first().hasClass('d-none');

    if (formularioEdicaoUsuario && !alteracaoSenhaAtiva) {
        $('#txtSenha, #txtConfirmarSenha').val('').prop('required', false).prop('disabled', true);
        $('#btnSalvar').prop('disabled', false);
    }

    // Verificar se a senha e a confirmação são iguais. Na edição, deixar os
    // dois campos vazios significa manter a senha atual.
    function validarSenhasUsuario() {
        var senha = $('#txtSenha').val() || '';
        var confirmarSenha = $('#txtConfirmarSenha').val() || '';
        var senhaFoiInformada = senha !== '' || confirmarSenha !== '';
        var senhasDiferentes = senha !== confirmarSenha;

        $('form.was-validated').removeClass('was-validated');
        var deveBloquear = formularioEdicaoUsuario
            ? (alteracaoSenhaAtiva && senhaFoiInformada && senhasDiferentes)
            : (confirmarSenha !== '' && senhasDiferentes);
        $('#btnProximo, #btnSalvar').prop('disabled', deveBloquear);

        if (deveBloquear) {
            $('#txtConfirmarSenha').addClass('is-invalid');
            $('#ifConfirmarSenha').addClass('d-block');
        } else {
            $('#txtConfirmarSenha').removeClass('is-invalid');
            $('#ifConfirmarSenha').removeClass('d-block');
        }
    }

    $('#txtSenha, #txtConfirmarSenha').on('input keyup change', validarSenhasUsuario);
    validarSenhasUsuario();

    // Aparece campo de empresa quando função for cliente
    // $('#slcFuncao').change(function () {
    //     if ($(this).val() == 'Cliente') {
    //         $('.empresa-funcao').html(slcEmpresaAtual);
    //         choicesInit();
    //         $('.empresa-funcao .collapse').collapse('show');
    //     }
    //     else {
    //         $('.empresa-funcao .collapse').collapse('hide');
    //         setTimeout(function () {
    //             $('.empresa-funcao').html("");
    //         }, 200);
    //     }
    // });
    $('#slcFuncao').change(function () {
        if ($(this).val() == 'Cliente') {
            $('#slcEmpresa').attr('required', 'required');
            $('#slcTiposEquipamento').attr('required', 'required');

            $.getJSON(`./empresas/busca-empresas.php?`, function (dados) {
                if (!('erro' in dados) && dados.length > 0) {
                    var optionsEmpresas = '';

                    dados.forEach(function (empresa) {
                        optionsEmpresas += `<option value='${empresa.id}'>${empresa.nome}</option>`;
                    });

                    $('#slcEmpresa').html(optionsEmpresas);

                    $('.empresa-funcao').collapse('show');
                    $('.tipo-equipamento-funcao').collapse('show');
                }
            });
        }
        else {
            $('#slcEmpresa').html(`<option value='' selected disabled>Selecione...</option>`);
            $('#slcEmpresa').removeAttr('required');
            $('#slcTiposEquipamento').removeAttr('required');
            $('.empresa-funcao').collapse('hide');
            $('.tipo-equipamento-funcao').collapse('hide');
        }
    });

    // Criação

    // Botão voltar na aba de conclusão
    // $('#btnVoltarConcluir').click(function () {
    //     $('#tabConcluir').removeClass(['active', 'show']);
    //     $('#tabPersonalisacao').addClass(['active', 'show']);
    //     $('#btnVoltar').click();

    //     // $('.card-footer').removeClass('d-none');
    // });




    // Edição

    //Nova senha
    $('#btnNovaSenha').click(function () {
        alteracaoSenhaAtiva = true;
        $(this).parents('.row').remove();
        $('#alterar-senha .row').removeClass('d-none');
        $('#txtSenha, #txtConfirmarSenha').val('').prop('required', false).prop('disabled', false);
        validarSenhasUsuario();
    });


    // Ao selecionar imagem
    $('#flImagemPerfil').change(function () {
        $('input[name=removerImagem]').prop('checked', false);
    });


    // Remover imagem de perfil
    $('#btnRemoverImagem').click(function () {
        $('#imgPerfil').attr('src', './assets/img/team/avatar.png');
        $('#flImagemPerfil').val('');
        $('input[name=removerImagem]').prop('checked', true);
    });

    updateImgChangeFile('flImagemPerfil', 'imgPerfil');
    updateImgChangeFile('fleImgAssinatura', 'imgAssinatura');
}
