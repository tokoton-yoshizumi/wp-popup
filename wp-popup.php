<?php

/**
 * Plugin Name: WP Popup
 * Description: 時間またはスクロールでポップアップを表示するプラグインです。
 * Version: 1.4
 * Author: TAKUMA YOSHIZUMI
 * Author URI: https://yoshizumi.tech
 */

if (!defined('ABSPATH')) {
    exit; // 直接アクセスを禁止
}

// =============================================================================
// 1. 管理画面の設定ページ
// =============================================================================

// メニューを追加
add_action('admin_menu', 'wpp_add_admin_menu');
function wpp_add_admin_menu()
{
    add_menu_page('WP Popup 設定', 'WP Popup', 'manage_options', 'wp_popup', 'wpp_options_page_html', 'dashicons-slides', 30);
}

// 設定ページのHTML
function wpp_options_page_html()
{
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wpp_settings_group');
            do_settings_sections('wp_popup');
            submit_button('設定を保存');
            ?>
        </form>
        <hr>
        <p style="text-align: right; color: #666;">
            WP Popup ver 1.4 | Developed by <a href="https://yoshizumi.tech" target="_blank">YOSHIZUMI</a>
        </p>
    </div>
<?php
}

// 設定項目の初期化
add_action('admin_init', 'wpp_settings_init');
function wpp_settings_init()
{
    register_setting('wpp_settings_group', 'wpp_settings');

    // --- ポップアップのデフォルト動作設定 ---
    add_settings_section('wpp_default_section', 'ポップアップのデフォルト動作', null, 'wp_popup');
    add_settings_field('wpp_default_display', 'サイト全体の表示設定', 'wpp_field_default_display_cb', 'wp_popup', 'wpp_default_section');

    // --- 動作設定 ---
    add_settings_section('wpp_settings_section', 'ポップアップの動作設定', null, 'wp_popup');
    add_settings_field('wpp_trigger_type', '表示トリガー', 'wpp_field_trigger_type_cb', 'wp_popup', 'wpp_settings_section');
    add_settings_field('wpp_device_display', '表示デバイス', 'wpp_field_device_display_cb', 'wp_popup', 'wpp_settings_section');
    add_settings_field('wpp_timer_seconds', 'タイマーの秒数（秒）', 'wpp_field_timer_seconds_cb', 'wp_popup', 'wpp_settings_section', ['class' => 'wpp-timer-seconds-row']);
    add_settings_field('wpp_scroll_pixels', 'スクロール量（px）', 'wpp_field_scroll_pixels_cb', 'wp_popup', 'wpp_settings_section', ['class' => 'wpp-scroll-pixels-row']);

    // --- デザイン設定 ---
    add_settings_section('wpp_design_section', 'デザイン設定', null, 'wp_popup');
    add_settings_field('wpp_show_overlay', 'オーバーレイ', 'wpp_field_show_overlay_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_popup_position', '表示位置', 'wpp_field_popup_position_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_popup_width', '横幅（px）', 'wpp_field_popup_width_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_bg_color', 'ポップアップ背景色', 'wpp_field_bg_color_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_bg_opacity', '背景の不透明度', 'wpp_field_bg_opacity_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_bg_image', 'ポップアップ背景画像', 'wpp_field_bg_image_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_text_color', 'ポップアップ文字色', 'wpp_field_text_color_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_btn_bg_color', 'ボタン背景色', 'wpp_field_btn_bg_color_cb', 'wp_popup', 'wpp_design_section');
    add_settings_field('wpp_btn_text_color', 'ボタン文字色', 'wpp_field_btn_text_color_cb', 'wp_popup', 'wpp_design_section');

    // --- コンテンツ設定 ---
    add_settings_section('wpp_content_section', 'コンテンツ設定', null, 'wp_popup');
    add_settings_field('wpp_show_text', 'テキストの表示', 'wpp_field_show_text_cb', 'wp_popup', 'wpp_content_section');
    add_settings_field('wpp_popup_text', '表示テキスト', 'wpp_field_popup_text_cb', 'wp_popup', 'wpp_content_section');
    add_settings_field('wpp_image_url', '画像', 'wpp_field_image_uploader_cb', 'wp_popup', 'wpp_content_section');
    add_settings_field('wpp_button_text', 'ボタンテキスト', 'wpp_field_button_text_cb', 'wp_popup', 'wpp_content_section');
    add_settings_field('wpp_button_url', 'ボタンのリンク先URL', 'wpp_field_button_url_cb', 'wp_popup', 'wpp_content_section');
    add_settings_field('wpp_button_target', 'リンクの開き方', 'wpp_field_button_target_cb', 'wp_popup', 'wpp_content_section');
}

// =============================================================================
// 2. 設定項目のコールバック関数
// =============================================================================

function wpp_field_default_display_cb()
{
    $options = get_option('wpp_settings');
    $default = $options['default_display'] ?? 'show';
?>
    <select name="wpp_settings[default_display]">
        <option value="show" <?php selected($default, 'show'); ?>>サイト全体で表示する</option>
        <option value="hide" <?php selected($default, 'hide'); ?>>サイト全体で表示しない</option>
    </select>
    <p class="description">
        サイト全体の基本設定です。各固定ページの編集画面で、ページごとにこの設定を上書きできます。
    </p>
<?php
}

function wpp_field_show_overlay_cb()
{
    $options = get_option('wpp_settings');
    $checked = isset($options['show_overlay']) ? $options['show_overlay'] : 1;
    echo '<input type="hidden" name="wpp_settings[show_overlay]" value="0"><input type="checkbox" name="wpp_settings[show_overlay]" value="1" ' . checked($checked, 1, false) . '> オーバーレイを表示する<p class="description">画面全体を半透明の黒いフィルターで覆い、ポップアップを際立たせます。</p>';
}
function wpp_field_popup_position_cb()
{
    $options = get_option('wpp_settings');
    $position = $options['popup_position'] ?? 'center';
    echo '<select name="wpp_settings[popup_position]"><option value="center" ' . selected($position, 'center', false) . '>中央</option><option value="bottom-right" ' . selected($position, 'bottom-right', false) . '>右下</option><option value="bottom-left" ' . selected($position, 'bottom-left', false) . '>左下</option></select>';
}
function wpp_field_popup_width_cb()
{
    $options = get_option('wpp_settings');
    $width = isset($options['popup_width']) ? $options['popup_width'] : '400';
    echo '<input type="number" name="wpp_settings[popup_width]" value="' . esc_attr($width) . '" min="100"><p class="description">ポップアップの横幅をピクセル単位で指定します。（例: 500）</p>';
}
function wpp_field_bg_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['bg_color']) ? $options['bg_color'] : '#FFFFFF';
    echo '<input type="text" name="wpp_settings[bg_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}
function wpp_field_bg_opacity_cb()
{
    $options = get_option('wpp_settings');
    $opacity = $options['bg_opacity'] ?? '1';
    echo '<div style="display: flex; align-items: center;"><input type="range" name="wpp_settings[bg_opacity]" class="wpp-opacity-range" value="' . esc_attr($opacity) . '" min="0" max="1" step="0.01" style="flex: 1; margin-right: 10px; max-width: 300px;"><input type="number" class="wpp-opacity-number" value="' . esc_attr($opacity) . '" min="0" max="1" step="0.01" style="width: 60px;"></div><p class="description">0で完全に透明、1で完全に不透明になります。</p>';
}
function wpp_field_text_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['text_color']) ? $options['text_color'] : '#333333';
    echo '<input type="text" name="wpp_settings[text_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}
function wpp_field_btn_bg_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['btn_bg_color']) ? $options['btn_bg_color'] : '#ffcc00';
    echo '<input type="text" name="wpp_settings[btn_bg_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}
function wpp_field_btn_text_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['btn_text_color']) ? $options['btn_text_color'] : '#000000';
    echo '<input type="text" name="wpp_settings[btn_text_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}
function wpp_field_show_text_cb()
{
    $options = get_option('wpp_settings');
    $checked = isset($options['show_text']) ? $options['show_text'] : 1;
    echo '<input type="hidden" name="wpp_settings[show_text]" value="0"><input type="checkbox" name="wpp_settings[show_text]" value="1" ' . checked($checked, 1, false) . '> 表示する';
}
function wpp_field_trigger_type_cb()
{
    $options = get_option('wpp_settings');
    $trigger = $options['trigger_type'] ?? 'timer';
    echo '<select id="wpp_trigger_type" name="wpp_settings[trigger_type]"><option value="timer" ' . selected($trigger, 'timer', false) . '>タイマー</option><option value="scroll" ' . selected($trigger, 'scroll', false) . '>スクロール</option></select>';
}
function wpp_field_device_display_cb()
{
    $options = get_option('wpp_settings');
    $device = $options['device_display'] ?? 'all';
    echo '<select name="wpp_settings[device_display]"><option value="all" ' . selected($device, 'all', false) . '>すべてのデバイスで表示</option><option value="pc" ' . selected($device, 'pc', false) . '>PCのみ表示</option><option value="mobile" ' . selected($device, 'mobile', false) . '>スマホのみ表示</option></select>';
}
function wpp_field_timer_seconds_cb()
{
    $options = get_option('wpp_settings');
    $seconds = $options['timer_seconds'] ?? '3';
    echo '<input type="number" name="wpp_settings[timer_seconds]" value="' . esc_attr($seconds) . '" min="0" step="0.1"><p class="description">ポップアップが表示されるまでの秒数を指定します。0.5のように小数点も使えます。</p>';
}
function wpp_field_scroll_pixels_cb()
{
    $options = get_option('wpp_settings');
    $pixels = $options['scroll_pixels'] ?? '200';
    echo '<input type="number" name="wpp_settings[scroll_pixels]" value="' . esc_attr($pixels) . '" min="0"><p class="description">ポップアップが表示されるまでのスクロール量をピクセル単位で指定します。</p>';
}
function wpp_field_popup_text_cb()
{
    $options = get_option('wpp_settings');
    $text = $options['popup_text'] ?? '';
    echo '<textarea name="wpp_settings[popup_text]" rows="4" cols="50">' . esc_textarea($text) . '</textarea>';
}
function wpp_field_bg_image_cb()
{
    $options = get_option('wpp_settings');
    $url = $options['bg_image'] ?? '';
    echo '<div class="wpp-media-uploader-field"><input type="hidden" name="wpp_settings[bg_image]" class="wpp-image-url-input" value="' . esc_url($url) . '"><button type="button" class="button wpp-upload-button">画像を選択</button> <button type="button" class="button wpp-remove-button">画像を削除</button><div class="wpp-image-preview">' . (!empty($url) ? '<img src="' . esc_url($url) . '" style="max-width:200px;height:auto;margin-top:10px;">' : '') . '</div></div><p class="description">背景画像を設定すると、背景色（不透明度含む）の上に表示されます。</p>';
}
function wpp_field_image_uploader_cb()
{
    $options = get_option('wpp_settings');
    $url = $options['image_url'] ?? '';
    echo '<div class="wpp-media-uploader-field"><input type="hidden" name="wpp_settings[image_url]" class="wpp-image-url-input" value="' . esc_url($url) . '"><button type="button" class="button wpp-upload-button">画像を選択</button> <button type="button" class="button wpp-remove-button">画像を削除</button><div class="wpp-image-preview">' . (!empty($url) ? '<img src="' . esc_url($url) . '" style="max-width:200px;height:auto;margin-top:10px;">' : '') . '</div></div>';
}
function wpp_field_button_text_cb()
{
    $options = get_option('wpp_settings');
    $text = $options['button_text'] ?? '今すぐ参加する';
    echo '<input type="text" name="wpp_settings[button_text]" value="' . esc_attr($text) . '" size="50">';
}
function wpp_field_button_url_cb()
{
    $options = get_option('wpp_settings');
    $url = $options['button_url'] ?? '#';
    echo '<input type="text" name="wpp_settings[button_url]" value="' . esc_url($url) . '" size="50" placeholder="https://example.com/your-page">';
}
function wpp_field_button_target_cb()
{
    $options = get_option('wpp_settings');
    $checked = isset($options['button_target']) ? $options['button_target'] : 0;
    echo '<input type="hidden" name="wpp_settings[button_target]" value="0"><input type="checkbox" name="wpp_settings[button_target]" value="1" ' . checked($checked, 1, false) . '> 新しいタブで開く';
}


// =============================================================================
// 3. 固定ページ編集画面のメタボックス
// =============================================================================

add_action('add_meta_boxes', 'wpp_add_meta_box');
function wpp_add_meta_box()
{
    add_meta_box('wpp_popup_settings', 'WP Popup 表示設定', 'wpp_meta_box_html', 'page', 'side', 'default');
}

function wpp_meta_box_html($post)
{
    $value = get_post_meta($post->ID, '_wpp_display_setting', true);
    wp_nonce_field('wpp_save_meta_box_data', 'wpp_meta_box_nonce');
    echo '<p>このページでポップアップを表示するかどうかを選択します。</p><select name="wpp_display_setting" style="width:100%;"><option value="" ' . selected($value, '', false) . '>サイト全体のデフォルト設定に従う</option><option value="show" ' . selected($value, 'show', false) . '>このページで表示する</option><option value="hide" ' . selected($value, 'hide', false) . '>このページで表示しない</option></select>';
}

add_action('save_post', 'wpp_save_meta_box_data');
function wpp_save_meta_box_data($post_id)
{
    if (!isset($_POST['wpp_meta_box_nonce']) || !wp_verify_nonce($_POST['wpp_meta_box_nonce'], 'wpp_save_meta_box_data')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) return;
    } else {
        return;
    }
    if (!isset($_POST['wpp_display_setting'])) return;

    update_post_meta($post_id, '_wpp_display_setting', sanitize_text_field($_POST['wpp_display_setting']));
}

// =============================================================================
// 4. フロントエンドへのCSS/JS読み込みとHTML出力
// =============================================================================

add_action('admin_enqueue_scripts', 'wpp_admin_enqueue_scripts');
function wpp_admin_enqueue_scripts($hook)
{
    if ('toplevel_page_wp_popup' != $hook) return;
    wp_enqueue_media();
    wp_enqueue_script('wpp-media-uploader', plugin_dir_url(__FILE__) . 'js/media-uploader.js', ['jquery'], '1.0', true);
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wpp-color-picker-init', plugin_dir_url(__FILE__) . 'js/color-picker-init.js', ['wp-color-picker'], false, true);
    wp_enqueue_script('wpp-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', ['jquery'], '1.0', true);
}


add_action('wp_enqueue_scripts', 'wpp_enqueue_assets');
function wpp_enqueue_assets()
{
    // 表示判定を行い、表示しない場合はJS/CSSを読み込まない
    if (!wpp_is_popup_visible()) return;

    wp_enqueue_style('wpp-style', plugin_dir_url(__FILE__) . 'css/popup-style.css', [], '1.2');

    $options = get_option('wpp_settings');
    $trigger_type = $options['trigger_type'] ?? 'timer';

    if ($trigger_type === 'scroll') {
        wp_enqueue_script('wpp-scroll-js', plugin_dir_url(__FILE__) . 'js/popup-scroll.js', [], '1.2', true);
        $pixels = $options['scroll_pixels'] ?? 200;
        wp_localize_script('wpp-scroll-js', 'wpp_scroll_vars', ['pixels' => (int)$pixels]);
    } else {
        wp_enqueue_script('wpp-timer-js', plugin_dir_url(__FILE__) . 'js/popup-timer.js', [], '1.2', true);
        $seconds = $options['timer_seconds'] ?? 3;
        wp_localize_script('wpp-timer-js', 'wpp_timer_vars', ['seconds' => (float)$seconds]);
    }
}


add_action('wp_footer', 'wpp_add_popup_html_to_footer');
function wpp_add_popup_html_to_footer()
{
    if (!wpp_is_popup_visible()) return;

    $options = get_option('wpp_settings');

    // --- HTML出力処理 (変更なし) ---
    $base_color = $options['bg_color'] ?? '#000000';
    $opacity = $options['bg_opacity'] ?? '0.85';
    $rgb_array = wpp_hex_to_rgb_array($base_color);
    $bg_color_rgba = sprintf('rgba(%s, %s, %s, %s)', $rgb_array[0], $rgb_array[1], $rgb_array[2], $opacity);

    $position = $options['popup_position'] ?? 'center';
    $position_style = '';
    switch ($position) {
        case 'bottom-right':
            $position_style = 'top: auto; left: auto; right: 20px; bottom: 20px; transform: none;';
            break;
        case 'bottom-left':
            $position_style = 'top: auto; right: auto; left: 20px; bottom: 20px; transform: none;';
            break;
        default:
            $position_style = 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            break;
    }

    $width = $options['popup_width'] ?? '400';
    $text_color = $options['text_color'] ?? '#ffffff';
    $bg_image_url = $options['bg_image'] ?? '';

    $popup_style = sprintf('%s width: %spx; background-color: %s; color: %s;', $position_style, esc_attr($width), $bg_color_rgba, esc_attr($text_color));
    if (!empty($bg_image_url)) {
        $popup_style .= sprintf("background-image: url('%s'); background-size: cover; background-position: center;", esc_url($bg_image_url));
    }

    $btn_bg_color = $options['btn_bg_color'] ?? '#ffcc00';
    $btn_text_color = $options['btn_text_color'] ?? '#000000';
    $button_style = sprintf('background-color: %s; color: %s;', esc_attr($btn_bg_color), esc_attr($btn_text_color));

    $show_text = $options['show_text'] ?? 1;
    $default_text = "ここに注目を集めるタイトルを入れます\nこれはポップアップの本文です。";
    $popup_text_raw = !empty($options['popup_text']) ? $options['popup_text'] : $default_text;
    $popup_text_formatted = nl2br(esc_textarea($popup_text_raw));
    $image_url = $options['image_url'] ?? '';
    $button_text = $options['button_text'] ?? '今すぐ参加する';
    $button_url = $options['button_url'] ?? '#';
    $button_target_blank = !empty($options['button_target']);

    $onclick_action = $button_target_blank ? "window.open('" . esc_url($button_url) . "', '_blank');" : "window.location.href='" . esc_url($button_url) . "';";
    $show_overlay = $options['show_overlay'] ?? 1;
    $container_class = $show_overlay ? 'wpp-container has-overlay' : 'wpp-container';
?>
    <div id="wpp-container" class="<?php echo $container_class; ?>" style="display: none;">
        <?php if ($show_overlay) : ?><div class="wpp-overlay"></div><?php endif; ?>
        <div class="wpp-popup" id="wpp-popup" style="<?php echo $popup_style; ?>">
            <div class="wpp-popup-content">
                <div class="wpp-close-icon" id="wpp-closePopup">×</div>
                <?php if ($show_text) : ?><p><?php echo $popup_text_formatted; ?></p><?php endif; ?>
                <?php if (!empty($image_url)): ?><img src="<?php echo esc_url($image_url); ?>" alt="" width="100%" style="margin: 20px 0;"><?php endif; ?>
                <button class="wpp-cta-button" style="<?php echo $button_style; ?>" onclick="<?php echo $onclick_action; ?>"><?php echo esc_html($button_text); ?></button>
            </div>
        </div>
    </div>
<?php
}

// =============================================================================
// 5. ヘルパー関数
// =============================================================================

function wpp_is_popup_visible()
{
    $options = get_option('wpp_settings');

    // デバイス判定
    $device_display = $options['device_display'] ?? 'all';
    $is_mobile = wp_is_mobile();
    if (($device_display === 'pc' && $is_mobile) || ($device_display === 'mobile' && !$is_mobile)) {
        return false;
    }

    // 表示ページ判定
    $default_display = $options['default_display'] ?? 'show';
    $show_popup = ($default_display === 'show');

    if (is_singular('page')) { // 固定ページの場合のみメタ情報を確認
        $page_specific_setting = get_post_meta(get_the_ID(), '_wpp_display_setting', true);
        if (!empty($page_specific_setting)) {
            $show_popup = ($page_specific_setting === 'show');
        }
    }

    return $show_popup;
}

function wpp_hex_to_rgb_array($hex)
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        list($r, $g, $b) = sscanf($hex, "%1s%1s%1s");
        return [hexdec("$r$r"), hexdec("$g$g"), hexdec("$b$b")];
    }
    return sscanf($hex, "%2s%2s%2s") ? [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))] : [0, 0, 0];
}
