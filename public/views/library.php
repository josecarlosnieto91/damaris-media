<?php

// Access check helper
function damaris_media_check_access($required_program = '') {
    if (!is_user_logged_in()) return false;
    if (empty($required_program)) return true;
    
    // RAÍZ program requires purchase
    if ($required_program === 'raiz') {
        $user_id = get_current_user_id();
        $purchased = get_user_meta($user_id, 'damaris_purchased_courses', true) ?: [];
        // Also check if user has RCP subscription
        if (function_exists('rcp_get_subscription_id')) {
            return true; // Has active subscription
        }
        return !empty($purchased);
    }
    
    return true; // Non-restricted programs
}
// Shortcode: [damaris_audio_library program="raiz"]

add_shortcode('damaris_audio_library', 'damaris_media_library');
function damaris_media_library($atts) {
    $atts = shortcode_atts(['program' => ''], $atts);
    
    // Check access
    $restricted = $atts['program'] === 'raiz';
    if ($restricted && !is_user_logged_in()) {
        return '<div style="text-align:center;padding:2rem;background:var(--bg-beige);border-radius:16px;">
            <p style="font-size:1.2rem;">🔒</p>
            <p>Inicia sesion para acceder a los audios del programa RAIZ.</p>
            <a href="/wp-login.php?redirect_to=' . urlencode(get_permalink()) . '" class="btn btn--primary">Iniciar sesion</a>
        </div>';
    }
    
    $args = ['post_type' => 'damaris_audio', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'ASC'];
    if ($atts['program']) {
        $args['meta_key'] = 'audio_program';
        $args['meta_value'] = sanitize_text_field($atts['program']);
    }
    
    $audios = get_posts($args);
    if (empty($audios)) return '<p style="text-align:center;color:var(--text-light);padding:2rem;">No hay audios disponibles en esta seccion.</p>';
    
    $html = '<div class="damaris-library">';
    $current_program = '';
    foreach ($audios as $a) {
        $program = get_post_meta($a->ID, 'audio_program', true);
        if ($program !== $current_program) {
            $current_program = $program;
            $label = ['meditacion' => '🧘 Meditaciones', 'raiz' => '🌿 Programa RAIZ', 'respiracion' => '🌬 Respiracion', 'otro' => '🎵 Otros'][$program] ?? '🎵 Audios';
            $html .= '<h3 class="damaris-library-section" style="font-family:var(--font-heading);margin:2rem 0 1rem;">' . $label . '</h3>';
        }
        $html .= '<div class="damaris-track" data-src="' . esc_url(get_post_meta($a->ID, 'audio_file', true)) . '">';
        $html .= '<div class="damaris-track-info">';
        $html .= '<strong>' . esc_html($a->post_title) . '</strong>';
        if ($a->post_excerpt) $html .= '<p style="font-size:0.85rem;color:var(--text-light);">' . esc_html($a->post_excerpt) . '</p>';
        $html .= '</div>';
        $html .= '<div class="damaris-track-meta">';
        $dur = get_post_meta($a->ID, 'audio_duration', true);
        if ($dur) $html .= '<span style="font-size:0.85rem;color:var(--text-muted);">' . esc_html($dur) . '</span>';
        $html .= '<button class="damaris-play-btn" aria-label="Reproducir">▶</button>';
        $html .= '</div></div>';
    }
    $html .= '</div>';
    $html .= '<div class="damaris-player-bar" style="display:none;">[reproductor]</div>';
    
    return $html;
}