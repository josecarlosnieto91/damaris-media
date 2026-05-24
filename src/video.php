<?php
/**
 * Video library for Damaris Media
 */

if (!defined('ABSPATH')) exit;

// ─── CPT Video ───
add_action('init', 'damaris_register_video_cpt');
function damaris_register_video_cpt() {
    register_post_type('damaris_video', array(
        'labels' => array(
            'name' => 'Videos',
            'singular_name' => 'Video',
            'add_new' => 'Anadir video',
            'add_new_item' => 'Anadir nuevo video',
            'edit_item' => 'Editar video',
            'menu_name' => 'Videos',
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=damaris_audio',
        'menu_icon' => 'dashicons-format-video',
        'supports' => array('title', 'thumbnail', 'excerpt'),
        'rewrite' => array('slug' => 'video'),
        'show_in_rest' => true,
    ));
    
    register_post_meta('damaris_video', 'video_url', [
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
    ]);
    register_post_meta('damaris_video', 'video_platform', [
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
    ]);
    register_post_meta('damaris_video', 'video_duration', [
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
    ]);
    register_post_meta('damaris_video', 'video_program', [
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
    ]);
}

// ─── Metabox ───
add_action('add_meta_boxes', 'damaris_video_metabox');
function damaris_video_metabox() {
    add_meta_box('damaris_video_details', 'Detalles del video', 'damaris_video_metabox_cb', 'damaris_video', 'normal', 'high');
}
function damaris_video_metabox_cb($post) {
    wp_nonce_field('damaris_video_save', 'damaris_video_nonce');
    $url = get_post_meta($post->ID, 'video_url', true);
    $platform = get_post_meta($post->ID, 'video_platform', true);
    $duration = get_post_meta($post->ID, 'video_duration', true);
    $program = get_post_meta($post->ID, 'video_program', true);
    ?>
    <table style="width:100%;">
        <tr><td style="padding:8px 0;width:130px;"><label>URL del video:</label></td>
            <td><input type="url" name="video_url" value="<?php echo esc_url($url); ?>" style="width:100%;" placeholder="https://vimeo.com/... o https://youtube.com/watch?v=..."></td></tr>
        <tr><td style="padding:8px 0;"><label>Plataforma:</label></td>
            <td><select name="video_platform" style="width:200px;">
                <option value="vimeo" <?php selected($platform, 'vimeo'); ?>>Vimeo (privado)</option>
                <option value="youtube" <?php selected($platform, 'youtube'); ?>>YouTube (no listado)</option>
            </select></td></tr>
        <tr><td style="padding:8px 0;"><label>Duracion:</label></td>
            <td><input type="text" name="video_duration" value="<?php echo esc_attr($duration); ?>" style="width:200px;" placeholder="15:32"></td></tr>
        <tr><td style="padding:8px 0;"><label>Programa:</label></td>
            <td><select name="video_program" style="width:200px;">
                <option value="pilates" <?php selected($program, 'pilates'); ?>>Pilates MAT</option>
                <option value="raiz" <?php selected($program, 'raiz'); ?>>Programa RAIZ</option>
                <option value="meditacion" <?php selected($program, 'meditacion'); ?>>Meditacion</option>
                <option value="otro" <?php selected($program, 'otro'); ?>>Otro</option>
            </select></td></tr>
    </table>
    <?php if ($url): 
        $embed = damaris_get_video_embed($url);
        if ($embed): ?>
        <div style="margin-top:1rem;position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;background:#000;">
            <?php echo $embed; ?>
        </div>
    <?php endif; endif;
}
add_action('save_post', 'damaris_video_save');
function damaris_video_save($pid) {
    if (!isset($_POST['damaris_video_nonce']) || !wp_verify_nonce($_POST['damaris_video_nonce'], 'damaris_video_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['video_url', 'video_platform', 'video_duration', 'video_program'] as $f) {
        if (isset($_POST[$f])) update_post_meta($pid, $f, sanitize_text_field($_POST[$f]));
    }
}

// ─── Embed helper ───
function damaris_get_video_embed($url) {
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
        return '<iframe src="https://player.vimeo.com/video/' . $m[1] . '?title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay;fullscreen" allowfullscreen></iframe>';
    }
    if (preg_match('/youtube\.com\/watch\?v=([\w-]+)/', $url, $m) || preg_match('/youtu\.be\/([\w-]+)/', $url, $m)) {
        return '<iframe src="https://www.youtube.com/embed/' . $m[1] . '?rel=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay;fullscreen" allowfullscreen></iframe>';
    }
    return '';
}

// ─── Shortcode [damaris_video_library] ───
add_shortcode('damaris_video_library', 'damaris_video_library_shortcode');
function damaris_video_library_shortcode($atts) {
    $atts = shortcode_atts(['program' => ''], $atts);
    $args = ['post_type' => 'damaris_video', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC'];
    if ($atts['program']) { $args['meta_key'] = 'video_program'; $args['meta_value'] = sanitize_text_field($atts['program']); }
    
    $videos = get_posts($args);
    if (empty($videos)) return '<p style="text-align:center;color:var(--text-light);padding:2rem;">Proximamente videos exclusivos.</p>';
    
    $html = '<div class="grid grid--2" style="gap:1.5rem;">';
    foreach ($videos as $v) {
        $url = get_post_meta($v->ID, 'video_url', true);
        $duration = get_post_meta($v->ID, 'video_duration', true);
        $program = get_post_meta($v->ID, 'video_program', true);
        $program_icons = ['pilates' => '🧘', 'raiz' => '🌿', 'meditacion' => '🧘', 'otro' => '🎬'];
        $icon = $program_icons[$program] ?? '🎬';
        $thumb = get_the_post_thumbnail_url($v->ID, 'medium') ?: '';
        
        $html .= '<div style="background:white;border-radius:16px;overflow:hidden;box-shadow:var(--shadow-card);border:1px solid rgba(168,181,160,0.1);">';
        if ($url) {
            $html .= '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#000;">';
            $html .= damaris_get_video_embed($url);
            $html .= '</div>';
        }
        $html .= '<div style="padding:1.25rem;">';
        $html .= '<span style="font-size:0.85rem;color:var(--sage-dark);">' . $icon . ' ' . esc_html(ucfirst($program)) . '</span>';
        $html .= '<h3 style="font-family:var(--font-heading);font-size:1.15rem;margin:0.35rem 0;">' . esc_html($v->post_title) . '</h3>';
        if ($v->post_excerpt) $html .= '<p style="font-size:0.85rem;color:var(--text-light);">' . esc_html($v->post_excerpt) . '</p>';
        if ($duration) $html .= '<span style="font-size:0.8rem;color:var(--text-muted);">⏱ ' . esc_html($duration) . '</span>';
        $html .= '</div></div>';
    }
    $html .= '</div>';
    return $html;
}

// ─── Admin columns ───
add_filter('manage_damaris_video_posts_columns', 'damaris_video_columns');
function damaris_video_columns($cols) {
    $cols['video_duration'] = 'Duración';
    $cols['video_program'] = 'Programa';
    return $cols;
}
add_action('manage_damaris_video_posts_custom_column', 'damaris_video_column_data', 10, 2);
function damaris_video_column_data($col, $pid) {
    if ($col === 'video_duration') echo esc_html(get_post_meta($pid, 'video_duration', true) ?: '-');
    if ($col === 'video_program') echo esc_html(get_post_meta($pid, 'video_program', true) ?: '-');
}