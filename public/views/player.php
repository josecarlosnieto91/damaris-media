<?php
// Shortcode: [damaris_player id="123" program="raiz"]

add_shortcode('damaris_player', 'damaris_media_player');
function damaris_media_player($atts) {
    $atts = shortcode_atts(['id' => 0, 'program' => ''], $atts);
    
    $args = ['post_type' => 'damaris_audio', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'ASC'];
    if ($atts['id']) $args['p'] = intval($atts['id']);
    if ($atts['program']) $args['meta_key'] = 'audio_program'; $args['meta_value'] = sanitize_text_field($atts['program']);
    
    $audios = get_posts($args);
    if (empty($audios)) return '<p style="color:var(--text-light);text-align:center;">No hay audios disponibles.</p>';
    
    $html = '<div class="damaris-playlist">';
    foreach ($audios as $a) {
        $file = get_post_meta($a->ID, 'audio_file', true);
        $duration = get_post_meta($a->ID, 'audio_duration', true);
        $program = get_post_meta($a->ID, 'audio_program', true);
        $program_label = ['meditacion' => '🧘', 'raiz' => '🌿', 'respiracion' => '🌬', 'otro' => '🎵'][$program] ?? '🎵';
        $thumb = get_the_post_thumbnail_url($a->ID, 'thumbnail') ?: '';
        
        $html .= '<div class="damaris-track" data-src="' . esc_url($file) . '">';
        $html .= '<div class="damaris-track-cover">';
        if ($thumb) $html .= '<img src="' . esc_url($thumb) . '" alt="" loading="lazy">';
        else $html .= '<div class="damaris-track-icon">' . $program_label . '</div>';
        $html .= '</div>';
        $html .= '<div class="damaris-track-info">';
        $html .= '<span class="damaris-track-program">' . $program_label . ' ' . esc_html(ucfirst($program)) . '</span>';
        $html .= '<strong class="damaris-track-title">' . esc_html($a->post_title) . '</strong>';
        if ($a->post_excerpt) $html .= '<p class="damaris-track-desc">' . esc_html($a->post_excerpt) . '</p>';
        $html .= '</div>';
        $html .= '<div class="damaris-track-meta">';
        if ($duration) $html .= '<span class="damaris-track-duration">' . esc_html($duration) . '</span>';
        $html .= '<button class="damaris-play-btn" aria-label="Reproducir">▶</button>';
        $html .= '</div></div>';
    }
    $html .= '</div>';
    
    // Player bar
    $html .= '<div class="damaris-player-bar" style="display:none;">';
    $html .= '<button class="damaris-player-prev" aria-label="Anterior">⏮</button>';
    $html .= '<button class="damaris-player-playpause" aria-label="Reproducir/Pausar">▶</button>';
    $html .= '<button class="damaris-player-next" aria-label="Siguiente">⏭</button>';
    $html .= '<span class="damaris-player-current">0:00</span>';
    $html .= '<div class="damaris-player-progress"><div class="damaris-player-progress-bar"></div></div>';
    $html .= '<span class="damaris-player-total">0:00</span>';
    $html .= '<span class="damaris-player-title"></span>';
    $html .= '<button class="damaris-player-close" aria-label="Cerrar">✕</button>';
    $html .= '</div>';
    
    return $html;
}