jQuery(function ($) {

    // Mostrar / ocultar el bloque al hacer clic en el botón de Elementor
    $('#inscribirme-upana').on('click', function () {
        $('#upana-ins-contenido').slideToggle();
    });

    // Navegación del steper
    $('.upana-ins-form').on('click', '.upana-ins-btn.next', function () {
        var paso = $(this).data('next');
        $('.upana-ins-paso').hide();
        $('.upana-ins-paso[data-paso="' + paso + '"]').show();
    });

    $('.upana-ins-form').on('click', '.upana-ins-btn.back', function () {
        var paso = $(this).data('prev');
        $('.upana-ins-paso').hide();
        $('.upana-ins-paso[data-paso="' + paso + '"]').show();
    });

    // Envío final: Crear inscripción
    // Envío final: Crear inscripción
    $('#upana-form-inscripcion').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $msg = $('#upana-ins-mensaje');
        var $lista = $('#upana-ins-lista-body');

        $msg.removeClass('ok error')
            .text('Guardando inscripción...')
            .show();

        // Armamos FormData para poder mandar la imagen
        var formData = new FormData(this);
        formData.append('action', 'upana_crear_inscripcion');
        formData.append('seguridad', UpanaInscripciones.nonce);

        $.ajax({
            url: UpanaInscripciones.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {

                if (!resp || typeof resp.success === 'undefined') {
                    $msg.addClass('error').text('Ocurrió un error inesperado.');
                    return;
                }

                if (resp.success) {
                    $msg.addClass('ok').text(resp.data.mensaje || 'Inscripción creada.');
                    if (resp.data.lista_html) {
                        $lista.html(resp.data.lista_html);
                    }

                    // Limpiamos y regresamos al paso 1
                    $form[0].reset();
                    $('.upana-ins-paso').hide();
                    $('.upana-ins-paso[data-paso="1"]').show();
                } else {
                    $msg.addClass('error').text(
                        resp.data && resp.data.mensaje
                            ? resp.data.mensaje
                            : 'No se pudo crear la inscripción.'
                    );
                }
            },
            error: function () {
                $msg.addClass('error').text('No se pudo conectar con el servidor.');
            }
        });
    });

});
