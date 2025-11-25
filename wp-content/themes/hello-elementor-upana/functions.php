<?php
/**
 * Cargar estilos del tema padre y del hijo
 */
function upana_child_enqueue_styles() {
    // Padre
    wp_enqueue_style(
        'hello-elementor-style',
        get_template_directory_uri() . '/style.css'
    );

    // Hijo
    wp_enqueue_style(
        'hello-elementor-upana-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('hello-elementor-style'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );
}
add_action('wp_enqueue_scripts', 'upana_child_enqueue_styles');
