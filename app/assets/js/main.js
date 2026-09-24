$(document).ready(function () {

    $('footer nav.bottom-bar a').click(function () {
        $('footer nav.bottom-bar a').removeClass('active');
        $(this).addClass('active');
    });

    select2Init();


    $('form.needs-validation').submit(function (event) {
        var form = $(this);
        form.find('button[type="submit"]').prop('disabled', true);
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.find('button[type="submit"]').prop('disabled', false);
            scrollarPara(form);
        }
        form.addClass('was-validated');
    });


    if (typeof inicio === 'function')
        inicio();

});

function select2Init() {
    var select2 = $('.selectpicker');
    select2.length && select2.each(function (index, value) {
        var $this = $(value);
        var options = $.extend({
            theme: 'bootstrap-5'
        }, $this.data('options'));
        $this.select2(options);

        // $this.on('select2:open', function (e) {
        //     // Quando a lista de opções é exibida, coloque o foco no campo de busca
        //     $('.select2-search__field').prop('focus', true).focus();
        //     console.log(e);
        // });
    });
};

function scrollarPara(elemento) {
    $(window).scrollTop(elemento.offset().top - $('header').height() - 10);
}

function alerta(type, text, element) {
    var iconsAlerta = {
        'success': 'check-circle',
        'danger': 'x-circle',
        'warning': 'exclamation-triangle'
    }

    if (element == undefined)
        element = $('main > section.principal');

    $(element).prepend(`<div class="alert alert-${type} alert-dismissible mb-5 fade show" role="alert"> <i class="bi bi-${iconsAlerta[type]} fs-5 lh-1 me-2"></i>${text}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>`);

    element[0].scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

function dataToDate(data, string = false) {
    var dateParts = data.split(' ');
    var date = dateParts[0];
    if (!string) {

        var dateComponents = date.split('/');
        var day = parseInt(dateComponents[0], 10);
        var month = parseInt(dateComponents[1], 10) - 1;
        var year = parseInt(dateComponents[2], 10);

        if (dateParts.length > 1) {
            var time = dateParts[1];

            var timeComponents = time.split(':');
            var hour = parseInt(timeComponents[0], 10);
            var minute = parseInt(timeComponents[1], 10);

            return new Date(year, month, day, hour, minute);
        }
        else
            return new Date(year, month, day);

    }
    else {
        var strDate = date.split('/').reverse().join('-');
        if (dateParts.length > 1)
            strDate += ` ${dateParts[1]}`

        return strDate;
    }
}

function dateToData(date) {
    var data = "";
    if (date != "") {
        if (date.length > 10) {
            data = " " + date.split(" ")[1].substr(0, 5);
            date = date.split(" ")[0];
        }
        data = date.substr(8) + "/" + date.substr(5, 2) + "/" + date.substr(0, 4) + data;
    }
    return data;
}


function isDateValid(dateString) {
    var date = new Date(dateString);
    return !isNaN(date);
}

function isDateInPast(dateString) {
    var date = new Date(dateString);
    var currentDate = new Date();
    return date < currentDate;
}

function isDateInFuture(dateString) {
    var date = new Date(dateString);
    var currentDate = new Date();
    return date > currentDate;
}

function minToHor(minutos) {
    var horas = Math.floor(minutos / 60); // Calcula as horas inteiras
    var minutosRestantes = Math.round(minutos % 60); // Calcula os minutos restantes

    return `${horas.toString().padStart(2, '0')}:${minutosRestantes.toString().padStart(2, '0')}`;
}