jQuery(function ($) {

    // Cambio entre pasos (Siguiente / Regresar)
    $('#upana-form-estudiante').on('click', '.upana-btn[data-next]', function () {
        var paso = $(this).data('next');
        $('.upana-form-paso').hide();
        $('.upana-form-paso[data-paso="' + paso + '"]').show();
    });

    $('#upana-form-estudiante').on('click', '.upana-btn[data-prev]', function () {
        var paso = $(this).data('prev');
        $('.upana-form-paso').hide();
        $('.upana-form-paso[data-paso="' + paso + '"]').show();
    });

    // Envío del formulario por AJAX
    $('#upana-form-estudiante').on('submit', function (e) {
        e.preventDefault();

        var $form    = $(this);
        var $mensaje = $('#upana-form-mensaje');

        $mensaje
            .removeClass('ok error')
            .text('Guardando información...')
            .show();

        var datos = $form.serializeArray();
        datos.push({ name: 'action', value: 'upana_guardar_estudiante' });
        datos.push({ name: 'seguridad', value: UpanaFormEstudiante.nonce });

        $.post(UpanaFormEstudiante.ajaxUrl, datos, function (resp) {
            if (!resp || typeof resp.success === 'undefined') {
                $mensaje
                    .addClass('error')
                    .text('Ocurrió un error inesperado.');
                return;
            }

            if (resp.success) {
                $mensaje
                    .addClass('ok')
                    .text(resp.data.mensaje || 'Guardado correctamente.');

                // si quieres limpiar todo después
                $form[0].reset();
                $('.upana-form-paso').hide();
                $('.upana-form-paso[data-paso="1"]').show();
            } else {
                $mensaje
                    .addClass('error')
                    .text(resp.data && resp.data.mensaje ? resp.data.mensaje : 'No se pudo guardar el registro.');
            }
        }).fail(function () {
            $mensaje
                .addClass('error')
                .text('No se pudo conectar con el servidor.');
        });
    });

});
