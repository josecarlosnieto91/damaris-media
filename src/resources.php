<?php
if (!defined('ABSPATH')) exit;

add_action('init', 'damaris_register_resource_cpt');
function damaris_register_resource_cpt() {
    register_post_type('damaris_resource', array(
        'labels' => array(
            'name' => 'Recursos',
            'singular_name' => 'Recurso',
            'add_new' => 'Anadir recurso',
            'menu_name' => 'Recursos',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=damaris_audio',
        'menu_icon' => 'dashicons-download',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite' => array('slug' => 'recurso'),
        'show_in_rest' => true,
    ));
    
    register_post_meta('damaris_resource', 'resource_file', array('show_in_rest' => true, 'single' => true, 'type' => 'string'));
    register_post_meta('damaris_resource', 'resource_type', array('show_in_rest' => true, 'single' => true, 'type' => 'string'));
}

add_action('add_meta_boxes', 'damaris_resource_metabox');
function damaris_resource_metabox() {
    add_meta_box('damaris_resource_details', 'Detalles del recurso', 'damaris_resource_metabox_cb', 'damaris_resource', 'normal', 'high');
}

function damaris_resource_metabox_cb($post) {
    wp_nonce_field('damaris_resource_save', 'damaris_resource_nonce');
    $file = get_post_meta($post->ID, 'resource_file', true);
    $type = get_post_meta($post->ID, 'resource_type', true);
    ?>
    <table style="width:100%;">
        <tr><td style="padding:8px 0;width:120px;"><label>Archivo (URL):</label></td>
            <td><input type="url" name="resource_file" value="<?php echo esc_url($file); ?>" style="width:100%;" placeholder="https://...pdf"></td></tr>
        <tr><td style="padding:8px 0;"><label>Tipo:</label></td>
            <td><select name="resource_type" style="width:200px;">
                <option value="guia" <?php selected($type, 'guia'); ?>>Guia PDF</option>
                <option value="ejercicio" <?php selected($type, 'ejercicio'); ?>>Ejercicio</option>
                <option value="diario" <?php selected($type, 'diario'); ?>>Diario</option>
                <option value="audio" <?php selected($type, 'audio'); ?>>Audio MP3</option>
            </select></td></tr>
    </table>
    <?php
}

add_action('save_post', 'damaris_resource_save');
function damaris_resource_save($pid) {
    if (!isset($_POST['damaris_resource_nonce']) || !wp_verify_nonce($_POST['damaris_resource_nonce'], 'damaris_resource_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (array('resource_file', 'resource_type') as $f) {
        if (isset($_POST[$f])) update_post_meta($pid, $f, sanitize_text_field($_POST[$f]));
    }
}

add_shortcode('damaris_resources', 'damaris_resources_shortcode');
function damaris_resources_shortcode() {
    $resources = get_posts(array('post_type' => 'damaris_resource', 'posts_per_page' => -1));
    if (empty($resources)) {
        return '<div style="text-align:center;padding:2rem;background:var(--bg-beige);border-radius:16px;">
            <p style="font-size:1.2rem;">🌱</p>
            <p style="color:var(--text-light);">Proximamente recursos descargables.</p>
        </div>';
    }
    
    $html = '<div class="grid grid--2" style="gap:1rem;">';
    foreach ($resources as $r) {
        $file = get_post_meta($r->ID, 'resource_file', true);
        $type = get_post_meta($r->ID, 'resource_type', true);
        $icons = array('guia' => '📖', 'ejercicio' => '🧘', 'diario' => '📓', 'audio' => '🎵');
        $icon = isset($icons[$type]) ? $icons[$type] : '📄';
        
        $html .= '<div style="background:var(--bg-cream);border-radius:16px;padding:1.5rem;border:1px solid rgba(168,181,160,0.1);">';
        $html .= '<div style="font-size:2rem;margin-bottom:0.5rem;">' . $icon . '</div>';
        $html .= '<h4 style="font-family:var(--font-heading);margin-bottom:0.35rem;">' . esc_html($r->post_title) . '</h4>';
        if ($r->post_excerpt) $html .= '<p style="font-size:0.85rem;color:var(--text-light);margin-bottom:1rem;">' . esc_html($r->post_excerpt) . '</p>';
        if ($file) $html .= '<a href="' . esc_url($file) . '" target="_blank" rel="noopener" class="btn btn--primary btn--sm" style="display:inline-flex;align-items:center;gap:0.35rem;">⬇ Descargar</a>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}