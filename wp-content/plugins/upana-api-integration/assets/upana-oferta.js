jQuery(function ($) {

    // Navegación entre pasos
    $('.upana-oferta-wrap').on('click', '.upana-oferta-btn.siguiente', function () {
        var pasoActual = $(this).closest('.upana-oferta-paso');
        var pasoNext   = $(this).data('next');

        pasoActual.hide();
        $('.upana-oferta-paso[data-paso="' + pasoNext + '"]').show();
    });

    $('.upana-oferta-wrap').on('click', '.upana-oferta-btn.anterior', function () {
        var pasoActual = $(this).closest('.upana-oferta-paso');
        var pasoPrev   = $(this).data('prev');

        pasoActual.hide();
        $('.upana-oferta-paso[data-paso="' + pasoPrev + '"]').show();
    });

    // Click en "Ver oferta"
    $('#upana-oferta-buscar').on('click', function () {
        var nivel    = $('#upana-nivel').val();
        var sede     = $('#upana-sede').val();
        var programa = $('#upana-programa').val();

        var $resultado = $('#upana-oferta-resultado');
        $resultado.html('<p class="upana-oferta-loading">Buscando programas...</p>');

        $.post(UpanaOfertaData.ajaxUrl, {
            action:    'upana_filtrar_oferta',
            seguridad: UpanaOfertaData.nonce,
            nivel:     nivel,
            sede:      sede,
            programa:  programa
        }, function (resp) {
            if (!resp || typeof resp.success === 'undefined') {
                $resultado.html('<p class="upana-oferta-error">Ocurrió un problema inesperado.</p>');
                return;
            }

            if (resp.success) {
                $resultado.html(resp.data.html);
            } else {
                $resultado.html('<p class="upana-oferta-error">' + (resp.data && resp.data.mensaje ? resp.data.mensaje : 'No se pudo obtener la información.') + '</p>');
            }
        }).fail(function () {
            $resultado.html('<p class="upana-oferta-error">Error de conexión al consultar la oferta.</p>');
        });
    });

});
