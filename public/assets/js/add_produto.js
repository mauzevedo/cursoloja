var App = function () {
    var calcula_frete = function () {
        $("#btn-calcula-frete").on('click', function () {
            var produto_id = $(this).attr('data-id');
            var cep = $("#cep").val();
            //alert('Produto ID: ' + produto_id + 'CEP: ' + cep);
            $.ajax({
                type: 'post',
                url: BASE_URL + 'ajax/index',
                dataType: 'json',
                data: {
                    cep: cep,
                    produto_id: produto_id,
                }
            }).then(function (response) {
                if(response.erro == 0) {
                    $('#retorno-frete').html(response.retorno_endereco);
                }
                console.log(response);
            });
        });
        return;
    };
    return {
        init: function () {
            calcula_frete();
        }
    }
}(); //Inicializa ao carregar
jQuery(document).ready(function () {
    $(window).keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
    App.init();
});