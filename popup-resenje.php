<?php
/**
 * Plugin Name: Top and Bottom Popup - Resenje
 * Description: Top and bottom popup HTML and CSS. Supports top and bottom positions with separate settings. Supports HTML + CSS content in popup.
 * Version: 1.0
 * Author: Stefan Zeljkic
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Add options to admin menu
add_action('admin_menu', function () {
    add_menu_page('Popup Resenje', 'Popup Resenje', 'manage_options', 'popup-resenje', 'popup_resenje_admin_page');
});

// Admin Page
function popup_resenje_admin_page()
{
    $positions = ['top', 'bottom'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('popup_resenje_save', 'popup_resenje_nonce')) {
        foreach ($positions as $pos) {
            update_option("popup_resenje_enable_{$pos}", isset($_POST["popup_enable_{$pos}"]) ? 1 : 0);
            update_option("popup_resenje_content_{$pos}", wp_kses_post($_POST["popup_content_{$pos}"]));
            update_option("popup_resenje_position_{$pos}", sanitize_text_field($_POST["popup_position_{$pos}"]));
            update_option("popup_resenje_interval_{$pos}", intval($_POST["popup_interval_{$pos}"]));
            update_option("popup_resenje_width_{$pos}", sanitize_text_field($_POST["popup_width_{$pos}"]));
            update_option("popup_resenje_height_{$pos}", sanitize_text_field($_POST["popup_height_{$pos}"]));
            update_option("popup_resenje_bg_{$pos}", sanitize_hex_color($_POST["popup_bg_{$pos}"]));
            update_option("popup_resenje_color_{$pos}", sanitize_hex_color($_POST["popup_color_{$pos}"]));
            update_option("popup_resenje_padding_{$pos}", sanitize_text_field($_POST["popup_padding_{$pos}"]));
            update_option("popup_resenje_radius_{$pos}", sanitize_text_field($_POST["popup_radius_{$pos}"]));
        }
        echo '<div class="updated"><p>Saved successfully.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Popup Settings</h1>
        <form method="post">
            <?php wp_nonce_field('popup_resenje_save', 'popup_resenje_nonce'); ?>
            <?php foreach (['top' => 'Top Popup', 'bottom' => 'Bottom Popup'] as $pos => $label): ?>
                <h2><?php echo esc_html($label); ?></h2>
                <p><label><input type="checkbox" name="popup_enable_<?php echo $pos; ?>" <?php checked(get_option("popup_resenje_enable_{$pos}"), 1); ?>> Enable popup</label></p>

                <p><label>Popup Content (HTML + CSS - example in documentation):</label><br>
                    <textarea name="popup_content_<?php echo $pos; ?>" rows="5" cols="60"><?php echo esc_textarea(get_option("popup_resenje_content_{$pos}", '')); ?></textarea></p>

                <p><label>Popup Position:</label>
                    <select name="popup_position_<?php echo $pos; ?>">
                        <option value="left" <?php selected(get_option("popup_resenje_position_{$pos}"), 'left'); ?>>Left</option>
                        <option value="center" <?php selected(get_option("popup_resenje_position_{$pos}"), 'center'); ?>>Center</option>
                        <option value="right" <?php selected(get_option("popup_resenje_position_{$pos}"), 'right'); ?>>Right</option>
                    </select>
                </p>

                <p><label>Re-display interval (in seconds):</label>
                    <input type="number" name="popup_interval_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_interval_{$pos}", 30)); ?>" min="0">
                </p>

                <p><label>Width (e.g., 300px or 80%):</label>
                    <input type="text" name="popup_width_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_width_{$pos}", '300px')); ?>">
                </p>

                <p><label>Height (e.g., auto or 200px):</label>
                    <input type="text" name="popup_height_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_height_{$pos}", 'auto')); ?>">
                </p>

                <p><label>Background Color:</label>
                    <input type="color" name="popup_bg_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_bg_{$pos}", '#ffffff')); ?>">
                </p>

                <p><label>X Button Color:</label>
                    <input type="color" name="popup_color_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_color_{$pos}", '#000000')); ?>">
                </p>

                <p><label>Padding (e.g., 20px):</label>
                    <input type="text" name="popup_padding_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_padding_{$pos}", '20px')); ?>">
                </p>

                <p><label>Border Radius (e.g., 8px):</label>
                    <input type="text" name="popup_radius_<?php echo $pos; ?>" value="<?php echo esc_attr(get_option("popup_resenje_radius_{$pos}", '8px')); ?>">
                </p>
                <hr>
            <?php endforeach; ?>
            <p><input type="submit" class="button button-primary" value="Save"></p>
            <hr>
            <h2>Support Development</h2>
            <p>If you find this plugin useful, please consider supporting future updates and development:</p>
            <p>
                <a href="https://www.patreon.com/user?u=76138587" class="button" target="_blank">Donate via Patreon</a>
                <a href="https://buymeacoffee.com/stefanzeljkic" class="button" target="_blank">Buy Me a Coffee</a>
                <a href="https://ko-fi.com/stefanzeljkic" class="button" target="_blank">Support on Ko-fi</a>
            </p>
        </form>
    </div>
    <?php
}

// Load styles and popup in footer
add_action('wp_footer', function () {
    if (is_admin()) return;

    foreach (['top', 'bottom'] as $pos) {
        if (!get_option("popup_resenje_enable_{$pos}")) continue;

        $content = get_option("popup_resenje_content_{$pos}", '');
        $position = get_option("popup_resenje_position_{$pos}", 'center');
        $interval = get_option("popup_resenje_interval_{$pos}", 30);
        $width = get_option("popup_resenje_width_{$pos}", '300px');
        $height = get_option("popup_resenje_height_{$pos}", 'auto');
        $bg = get_option("popup_resenje_bg_{$pos}", '#ffffff');
        $color = get_option("popup_resenje_color_{$pos}", '#000000');
        $padding = get_option("popup_resenje_padding_{$pos}", '20px');
        $radius = get_option("popup_resenje_radius_{$pos}", '8px');

        $horizontal_css = 'left: 20px;';
        if ($position === 'center') $horizontal_css = 'left: 50%; transform: translateX(-50%);';
        elseif ($position === 'right') $horizontal_css = 'right: 20px;';

        $vertical_css = $pos === 'top' ? 'top: 30px;' : 'bottom: 30px;';
        $popup_id = "popup-resenje-{$pos}";
        ?>

        <style>
            #<?php echo $popup_id; ?> {
                position: fixed;
                <?php echo $horizontal_css . $vertical_css; ?>
                z-index: 9999;
                background: <?php echo esc_attr($bg); ?>;
                color: <?php echo esc_attr($color); ?>;
                padding: <?php echo esc_attr($padding); ?>;
                border-radius: <?php echo esc_attr($radius); ?>;
                max-width: 100%;
                width: <?php echo esc_attr($width); ?>;
                height: <?php echo esc_attr($height); ?>;
                display: none;
            }

            #<?php echo $popup_id; ?> .close-btn {
                position: absolute;
                top: 5px;
                right: 10px;
                font-weight: bold;
                font-size: 18px;
                cursor: pointer;
            }

            @media (max-width: 768px) {
                #<?php echo $popup_id; ?> {
                    width: 90%;
                    left: 50% !important;
                    transform: translateX(-50%) !important;
                }
            }
        </style>

        <div id="<?php echo $popup_id; ?>">
            <div class="close-btn" onclick="document.getElementById('<?php echo $popup_id; ?>').style.display='none'; localStorage.setItem('<?php echo $popup_id; ?>Closed', Date.now());">&times;</div>
            <div><?php echo wp_kses_post($content); ?></div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let popup = document.getElementById('<?php echo $popup_id; ?>');
                let lastClosed = parseInt(localStorage.getItem('<?php echo $popup_id; ?>Closed') || 0);
                let now = Date.now();
                if ((now - lastClosed) / 1000 > <?php echo $interval; ?>) {
                    popup.style.display = 'block';
                }
            });
        </script>
        <?php
    }
});
