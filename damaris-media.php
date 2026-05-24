<?php
/**
 * Plugin Name: Damaris Media
 * Plugin URI: https://damarisholistica.es
 * Description: Gestion de audios de meditacion, videos y recursos descargables para Damaris Holistica.
 * Version: 1.0.0
 * Author: Jose Carlos Nieto Ramos
 * Author URI: https://damarisholistica.es
 * Text Domain: damaris-media
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

define('DAMARIS_MEDIA_VERSION', '1.0.0');
define('DAMARIS_MEDIA_FILE', __FILE__);
define('DAMARIS_MEDIA_PATH', plugin_dir_path(__FILE__));
define('DAMARIS_MEDIA_URL', plugin_dir_url(__FILE__));

// ─── Init ───
add_action('init', 'damaris_media_register_cpts');
add_action('plugins_loaded', 'damaris_media_init');
function damaris_media_init() {
    load_plugin_textdomain('damaris-media', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    if (is_admin()) {
        require_once DAMARIS_MEDIA_PATH . 'admin/views/admin.php';
    }
    
    require_once DAMARIS_MEDIA_PATH . 'public/views/player.php';
    require_once DAMARIS_MEDIA_PATH . 'public/views/library.php';
require_once DAMARIS_MEDIA_PATH . 'src/resources.php';
}

// ─── Register CPTs ───
function damaris_media_register_cpts() {
    // Audio CPT
    $labels = array(
        'name'          => 'Audios',
        'singular_name' => 'Audio',
        'add_new'       => 'Subir audio',
        'add_new_item'  => 'Subir nuevo audio',
        'edit_item'     => 'Editar audio',
        'view_item'     => 'Ver audio',
        'search_items'  => 'Buscar audios',
        'not_found'     => 'No se encontraron audios',
        'menu_name'     => 'Damaris Media',
    );
    register_post_type('damaris_audio', array(
        'labels'       => $labels,
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-format-audio',
        'menu_position'=> 26,
        'supports'     => array('title', 'thumbnail', 'excerpt'),
        'rewrite'      => array('slug' => 'audio'),
        'has_archive'  => true,
        'show_in_rest' => true,
    ));
    
    // Meta fields
    $metas = [
        'audio_file'     => ['type' => 'string', 'desc' => 'Archivo MP3 (URL)'],
        'audio_duration' => ['type' => 'string', 'desc' => 'Duracion (ej: 15:32)'],
        'audio_program'  => ['type' => 'string', 'desc' => 'Programa (raiz, meditacion, respiracion)'],
    ];
    foreach ($metas as $key => $m) {
        register_post_meta('damaris_audio', $key, [
            'show_in_rest' => true, 'single' => true, 'type' => $m['type'], 'default' => '',
        ]);
    }
    
    // Flush on first activation
    if (!get_option('damaris_media_flushed')) {
        flush_rewrite_rules();
        update_option('damaris_media_flushed', true);
    }
}

// ─── Admin columns ───
add_filter('manage_damaris_audio_posts_columns', 'damaris_media_columns');
function damaris_media_columns($cols) {
    $cols['audio_duration'] = 'Duracion';
    $cols['audio_program']  = 'Programa';
    return $cols;
}
add_action('manage_damaris_audio_posts_custom_column', 'damaris_media_column_data', 10, 2);
function damaris_media_column_data($col, $pid) {
    if ($col === 'audio_duration') echo esc_html(get_post_meta($pid, 'audio_duration', true) ?: '-');
    if ($col === 'audio_program') echo esc_html(get_post_meta($pid, 'audio_program', true) ?: '-');
}

// ─── Enqueue public assets ───
add_action('wp_enqueue_scripts', 'damaris_media_assets');
function damaris_media_assets() {
    wp_enqueue_style('damaris-media', DAMARIS_MEDIA_URL . 'public/css/media.css', [], DAMARIS_MEDIA_VERSION);
    wp_enqueue_script('damaris-media', DAMARIS_MEDIA_URL . 'public/js/media.js', ['jquery'], DAMARIS_MEDIA_VERSION, true);
    wp_localize_script('damaris-media', 'damarisMedia', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'restUrl' => rest_url('damaris-media/v1/'),
    ]);
}

// ─── Activation hook ───
register_activation_hook(__FILE__, function() {
    damaris_media_register_cpts();
    flush_rewrite_rules();
    update_option('damaris_media_flushed', true);
});