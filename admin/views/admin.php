<?php
// Admin meta boxes for audio
add_action('add_meta_boxes', 'damaris_media_metabox');
function damaris_media_metabox() {
    add_meta_box('damaris_audio_details', 'Detalles del audio', 'damaris_media_metabox_cb', 'damaris_audio', 'normal', 'high');
}
function damaris_media_metabox_cb($post) {
    wp_nonce_field('damaris_audio_save', 'damaris_audio_nonce');
    $file = get_post_meta($post->ID, 'audio_file', true);
    $duration = get_post_meta($post->ID, 'audio_duration', true);
    $program = get_post_meta($post->ID, 'audio_program', true);
    ?>
    <table style="width:100%;">
        <tr><td style="padding:8px 0;width:120px;"><label>Archivo MP3:</label></td>
            <td><input type="url" name="audio_file" value="<?php echo esc_url($file); ?>" style="width:100%;" placeholder="https://...mp3"></td></tr>
        <tr><td style="padding:8px 0;"><label>Duracion:</label></td>
            <td><input type="text" name="audio_duration" value="<?php echo esc_attr($duration); ?>" style="width:200px;" placeholder="15:32"></td></tr>
        <tr><td style="padding:8px 0;"><label>Programa:</label></td>
            <td><select name="audio_program" style="width:200px;">
                <option value="meditacion" <?php selected($program, 'meditacion'); ?>>Meditacion</option>
                <option value="raiz" <?php selected($program, 'raiz'); ?>>Programa RAIZ</option>
                <option value="respiracion" <?php selected($program, 'respiracion'); ?>>Respiracion</option>
                <option value="otro" <?php selected($program, 'otro'); ?>>Otro</option>
            </select></td></tr>
    </table>
    <?php if ($file): ?>
    <p style="margin-top:1rem;"><strong>Vista previa:</strong></p>
    <audio controls style="width:100%;max-width:400px;"><source src="<?php echo esc_url($file); ?>" type="audio/mpeg"></audio>
    <?php endif;
}
add_action('save_post', 'damaris_media_save');
function damaris_media_save($pid) {
    if (!isset($_POST['damaris_audio_nonce']) || !wp_verify_nonce($_POST['damaris_audio_nonce'], 'damaris_audio_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['audio_file', 'audio_duration', 'audio_program'] as $f) {
        if (isset($_POST[$f])) update_post_meta($pid, $f, sanitize_text_field($_POST[$f]));
    }
}