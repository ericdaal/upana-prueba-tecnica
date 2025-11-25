<?php
/**
 * Plugin Name: UPANA API Integration
 * Description: Conecta el sitio con las APIs de apisandbox.upana.edu.gt (estudiantes, etc).
 * Version: 1.0.0
 * Author: Erick Alvarado
 */

if (!defined('ABSPATH')) {
    exit;
}

class UPANA_API_Integration
{
    const BASE_URL      = 'https://apisandbox.upana.edu.gt';
    const TOKEN_OPTION  = 'upana_api_token';
    const TOKEN_EXPIRES = 'upana_api_token_expires';

    public function __construct() {
        // Shortcodes
        add_shortcode('upana_estudiantes',      [$this, 'shortcode_estudiantes']);
        add_shortcode('upana_inscripciones', [$this, 'shortcode_inscripciones']);

        // Cargar CSS/JS solo en el front
        add_action('wp_enqueue_scripts',        [$this, 'enqueue_assets']);

        // Ajax para guardar estudiante nuevo
        add_action('wp_ajax_upana_crear_inscripcion',        [$this, 'ajax_crear_inscripcion']);
        add_action('wp_ajax_nopriv_upana_crear_inscripcion', [$this, 'ajax_crear_inscripcion']);
    }

    /**
     * Encola CSS/JS únicamente si el contenido usa alguno de los shortcodes.
     */
    public function enqueue_assets() {
        if (is_admin()) {
            return;
        }

        global $post;

        if (!isset($post) || !is_a($post, 'WP_Post')) {
            return;
        }

        $content          = $post->post_content ?? '';
        $usa_estudiantes  = has_shortcode($content, 'upana_estudiantes');
        $usa_inscripciones = has_shortcode($content, 'upana_inscripciones');

        // Si la página no usa ninguno de los 2 shortcodes, no cargo nada
        if (!$usa_estudiantes && !$usa_inscripciones) {
            return;
        }

        //
        // ⭐ Testimonios Slider
        //
        if ($usa_estudiantes) {
            wp_enqueue_style(
                'slick',
                'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
                [],
                '1.8.1'
            );
            wp_enqueue_style(
                'slick-theme',
                'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css',
                ['slick'],
                '1.8.1'
            );
            wp_enqueue_style(
                'upana-testimonios',
                plugins_url('assets/upana-testimonios.css', __FILE__),
                ['slick'],
                '1.0.0'
            );
            wp_enqueue_script(
                'slick',
                'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
                ['jquery'],
                '1.8.1',
                true
            );
            wp_enqueue_script(
                'upana-testimonios',
                plugins_url('assets/upana-testimonios.js', __FILE__),
                ['jquery', 'slick'],
                '1.0.0',
                true
            );
        }

        //
        // ⭐ Inscripciones (lista + botón Inscribirme + formulario steper)
        //
        if ($usa_inscripciones) {

            // CSS del flujo steper
            wp_enqueue_style(
                'upana-inscripciones',
                plugins_url('assets/upana-inscripciones.css', __FILE__),
                [],
                '1.0.0'
            );

            // JS del flujo
            wp_enqueue_script(
                'upana-inscripciones',
                plugins_url('assets/upana-inscripciones.js', __FILE__),
                ['jquery'],
                '1.0.0',
                true
            );

            wp_localize_script('upana-inscripciones', 'UpanaInscripciones', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('upana_inscripciones_nonce'),
            ]);
        }
    }

    /**
     * Pide el token a la API y lo guarda un ratito para no estar pegándole a cada rato.
     */
    public function get_token()
    {
        $tokenGuardado = get_option(self::TOKEN_OPTION);
        $expira        = (int) get_option(self::TOKEN_EXPIRES);

        // si el token sigue vivo, lo usamos
        if (!empty($tokenGuardado) && $expira > time() + 60) {
            return $tokenGuardado;
        }

        $url  = self::BASE_URL . '/api/token/';
        $body = [
            'username' => 'devtest',
            'password' => 'Upana+172025',
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($data['access'])) {
            return new WP_Error(
                'upana_token_error',
                'No se pudo obtener el token de la API.'
            );
        }

        $token = $data['access'];

        // no tengo el tiempo exacto de expiración, le dejo 10 minutos
        update_option(self::TOKEN_OPTION, $token, false);
        update_option(self::TOKEN_EXPIRES, time() + 10 * MINUTE_IN_SECONDS, false);

        return $token;
    }

    /**
     * Wrapper sencillo para llamar la API con Bearer.
     *
     * @param string $method GET|POST|PUT|PATCH|DELETE
     * @param string $path   Ruta, por ejemplo: '/api/estudiantes/'
     * @param array  $args   ['query' => [], 'body' => []]
     */
    public function api_request($method, $path, $args = [])
    {
        $token = $this->get_token();

        if (is_wp_error($token)) {
            return $token;
        }

        $url = self::BASE_URL . $path;

        // parámetros en query string
        if (!empty($args['query']) && is_array($args['query'])) {
            $url = add_query_arg($args['query'], $url);
        }

        $request_args = [
            'method'  => strtoupper($method),
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
            'timeout' => 15,
        ];

        // body para POST / PUT / PATCH
        if (!empty($args['body'])) {
            $request_args['headers']['Content-Type'] = 'application/json';
            $request_args['body'] = wp_json_encode($args['body']);
        }

        $response = wp_remote_request($url, $request_args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            return new WP_Error(
                'upana_api_error',
                'Error en la API (' . $code . ').',
                ['body' => $data]
            );
        }

        return $data;
    }

    /**
     * [upana_estudiantes cantidad="8"]
     * Render de los testimonios como slider.
     */
    public function shortcode_estudiantes($atts)
    {
        $atts = shortcode_atts([
            'cantidad' => 8,
        ], $atts, 'upana_estudiantes');

        $cantidad = (int) $atts['cantidad'];
        if ($cantidad <= 0) {
            $cantidad = 8;
        }

        $data = $this->api_request('GET', '/api/estudiantes/');

        if (is_wp_error($data)) {
            $msg = esc_html($data->get_error_message());
            return '<div class="upana-estudiantes-error">No se pudieron cargar los testimonios. ' . $msg . '</div>';
        }

        if (!is_array($data) || empty($data)) {
            return '<div class="upana-estudiantes-empty">No hay testimonios disponibles.</div>';
        }

        $estudiantes = array_slice($data, 0, $cantidad);

        $iconos_facultad = [
            'educ'      => '📘',
            'comunica'  => '🎦',
            'arquitect' => '🏛️',
            'econ'      => '💼',
            'salud'     => '⚕️',
            'ingenier'  => '🛠️',
            'odont'     => '🦷',
            'derecho'   => '⚖️',
        ];

        ob_start();
        ?>
        <div class="upana-testimonios-slider">
            <?php foreach ($estudiantes as $est):
                $nombre   = trim(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''));
                $texto    = $est['biografia'] ?? '';
                $facultad = $est['facultad'] ?? ($est['programa'] ?? '');
                $icono    = '🎓';

                $facu_lower = strtolower($facultad);
                foreach ($iconos_facultad as $clave => $emoji) {
                    if ($facu_lower !== '' && strpos($facu_lower, $clave) !== false) {
                        $icono = $emoji;
                        break;
                    }
                }
                ?>
                <div class="upana-testimonio-slide">
                    <article class="upana-testimonio-card">
                        <div class="upana-testimonio-head">
                            <div class="upana-testimonio-icon">
                                <span><?php echo esc_html($icono); ?></span>
                            </div>
                            <div class="upana-testimonio-title">
                                <h3><?php echo esc_html($nombre); ?></h3>
                                <?php if (!empty($facultad)): ?>
                                    <p class="upana-testimonio-facultad">
                                        <?php echo esc_html($facultad); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="upana-testimonio-facultad">Estudiante UPANA</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($texto)): ?>
                            <p class="upana-testimonio-text">
                                <?php echo esc_html($texto); ?>
                            </p>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    public function shortcode_inscripciones($atts) {
        // Traigo catálogos para los selects (punto 3 del flujo)
        $niveles   = $this->api_request('GET', '/api/niveles/');
        $programas = $this->api_request('GET', '/api/programas/');
        $sedes     = $this->api_request('GET', '/api/sedes/');
        $inscritos = $this->api_request('GET', '/api/estudiantes/');

        if (is_wp_error($niveles) || is_wp_error($programas) || is_wp_error($sedes)) {
            return '<div class="upana-ins-error">No se pudo cargar la información inicial para inscripciones.</div>';
        }

        $niveles   = is_array($niveles)   ? $niveles   : [];
        $programas = is_array($programas) ? $programas : [];
        $sedes     = is_array($sedes)     ? $sedes     : [];
        $inscritos = is_array($inscritos) ? $inscritos : [];

        ob_start();
        ?>
        <div class="upana-ins-wrap">

        <div id="upana-ins-contenido" class="upana-ins-contenido" style="display:none;">

                <div class="upana-ins-layout">
                    <!-- Lista de inscritos (izquierda) -->
                    <div class="upana-ins-lista">
                        <h3>Lista de inscritos</h3>
                        <div id="upana-ins-lista-body">
                            <?php if (empty($inscritos)): ?>
                                <p>No hay inscritos todavía.</p>
                            <?php else: ?>
                                <ul class="upana-ins-list">
                                    <?php foreach ($inscritos as $e): ?>
                                        <li>
                                            <strong><?php echo esc_html(($e['nombres'] ?? '') . ' ' . ($e['apellidos'] ?? '')); ?></strong><br>
                                            <span><?php echo esc_html($e['codigo_programa'] ?? ''); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Formulario steper (derecha) -->
                    <div class="upana-ins-form">
                        <h3>Formulario de nuevos estudiantes</h3>

                        <form id="upana-form-inscripcion" enctype="multipart/form-data">
                            <!-- Paso 1: filtros (nivel, carrera, sede) -->
                            <div class="upana-ins-paso" data-paso="1">
                                <h4>Paso 1. Carrera de interés</h4>

                                <div class="upana-ins-campo">
                                    <label for="ins_nivel">Nivel académico</label>
                                    <select id="ins_nivel" name="codigo_nivel_academico" required>
                                        <option value="">Selecciona un nivel</option>
                                        <?php foreach ($niveles as $n): ?>
                                            <option value="<?php echo esc_attr($n['codigo_nivel_academico'] ?? ''); ?>">
                                                <?php echo esc_html($n['descripcion_nivel_academico'] ?? ''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_programa">Carrera</label>
                                    <select id="ins_programa" name="codigo_programa" required>
                                        <option value="">Selecciona una carrera</option>
                                        <?php foreach ($programas as $p): ?>
                                            <option value="<?php echo esc_attr($p['codigo_programa'] ?? ''); ?>">
                                                <?php echo esc_html($p['descripcion_programa'] ?? ''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_sede">Sede</label>
                                    <select id="ins_sede" name="codigo_sede" required>
                                        <option value="">Selecciona una sede</option>
                                        <?php foreach ($sedes as $s): ?>
                                            <option value="<?php echo esc_attr($s['codigo_sede'] ?? ''); ?>">
                                                <?php echo esc_html($s['descripcion_sede'] ?? ''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="upana-ins-acciones derecha">
                                    <button type="button" class="upana-ins-btn next" data-next="2">
                                        Siguiente
                                    </button>
                                </div>
                            </div>

                            <!-- Paso 2: datos generales -->
                            <div class="upana-ins-paso" data-paso="2" style="display:none;">
                                <h4>Paso 2. Datos personales</h4>

                                <div class="upana-ins-campo">
                                    <label for="ins_nombres">Nombres</label>
                                    <input type="text" id="ins_nombres" name="nombres" required>
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_apellidos">Apellidos</label>
                                    <input type="text" id="ins_apellidos" name="apellidos" required>
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_fecha_nacimiento">Fecha de nacimiento</label>
                                    <input type="date" id="ins_fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_telefono">Teléfono</label>
                                    <input type="text" id="ins_telefono" name="telefono">
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_pagina">Página web</label>
                                    <input type="url" id="ins_pagina" name="pagina_web" placeholder="https://">
                                </div>

                                <div class="upana-ins-campo">
                                    <label for="ins_bio">Biografía</label>
                                    <textarea id="ins_bio" name="biografia" rows="3"></textarea>
                                </div>
                                <div class="upana-ins-campo">
                                    <label for="ins_foto">Foto de perfil</label>
                                    <input type="file" id="ins_foto" name="foto_perfil" accept="image/*">
                                    <small class="upana-ins-help">Opcional. Formato de imagen (JPG, PNG, etc.).</small>
                                </div>
                                <div class="upana-ins-acciones espacio">
                                    <button type="button" class="upana-ins-btn back" data-prev="1">
                                        Regresar
                                    </button>
                                    <button type="submit" class="upana-ins-btn guardar">
                                        Crear inscripción
                                    </button>
                                </div>
                            </div>

                            <div id="upana-ins-mensaje" class="upana-ins-mensaje" style="display:none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_crear_inscripcion() {
        check_ajax_referer('upana_inscripciones_nonce', 'seguridad');

        // Datos básicos
        $data = [
            'nombres'          => sanitize_text_field($_POST['nombres'] ?? ''),
            'apellidos'        => sanitize_text_field($_POST['apellidos'] ?? ''),
            'fecha_nacimiento' => sanitize_text_field($_POST['fecha_nacimiento'] ?? ''),
            'telefono'         => sanitize_text_field($_POST['telefono'] ?? ''),
            'pagina_web'       => esc_url_raw($_POST['pagina_web'] ?? ''),
            'biografia'        => sanitize_textarea_field($_POST['biografia'] ?? ''),
            // foto_perfil la agregamos abajo solo si viene archivo
        ];

        if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['fecha_nacimiento'])) {
            wp_send_json_error(['mensaje' => 'Hace falta información obligatoria.']);
        }

        // Token para la API
        $token = $this->get_token();
        if (is_wp_error($token)) {
            wp_send_json_error(['mensaje' => 'No se pudo obtener el token de la API.']);
        }

        // Si viene una foto, la preparamos como archivo
        if (!empty($_FILES['foto_perfil']) && isset($_FILES['foto_perfil']['tmp_name']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $tmp_name  = $_FILES['foto_perfil']['tmp_name'];
            $file_name = $_FILES['foto_perfil']['name'];
            $file_type = $_FILES['foto_perfil']['type'] ?: 'image/jpeg';

            if (function_exists('curl_file_create')) {
                $data['foto_perfil'] = curl_file_create($tmp_name, $file_type, $file_name);
            } else {
                // fallback viejo, por si acaso
                $data['foto_perfil'] = '@' . $tmp_name . ";filename={$file_name};type={$file_type}";
            }
        }

        // Armamos el POST directo (aquí no usamos api_request porque ese está pensado para JSON)
        $url = self::BASE_URL . '/api/estudiantes/';

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                // NO ponemos Content-Type, WP arma multipart/form-data al ver array + archivo
            ],
            'body'    => $data,
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['mensaje' => 'No se pudo crear la inscripción (error de conexión).']);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code < 200 || $code >= 300) {
            wp_send_json_error([
                'mensaje' => 'No se pudo crear la inscripción. Error en la API (' . $code . '). Detalle: ' . wp_json_encode($json),
            ]);
        }

        // Refrescamos lista
        $lista = $this->api_request('GET', '/api/estudiantes/');
        $lista = is_array($lista) ? $lista : [];

        ob_start(); ?>
        <ul class="upana-ins-list">
            <?php foreach ($lista as $e): ?>
                <li>
                    <strong><?php echo esc_html(($e['nombres'] ?? '') . ' ' . ($e['apellidos'] ?? '')); ?></strong><br>
                    <span><?php echo esc_html($e['codigo_programa'] ?? ''); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $html_lista = ob_get_clean();

        wp_send_json_success([
            'mensaje'    => 'Inscripción creada correctamente.',
            'lista_html' => $html_lista,
        ]);
    }

}

new UPANA_API_Integration();
