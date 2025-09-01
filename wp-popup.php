<?php

/**
 * Plugin Name: WP Popup
 * Description: 時間またはスクロールでポップアップを表示するプラグインです。
 * Version: 1.2
 * Author: TAKUMA YOSHIZUMI
 * Author URI: https://yoshizumi.tech
 */

if (!defined('ABSPATH')) {
    exit; // 直接アクセスを禁止
}

// 1. 管理画面に設定メニューを追加
function wpp_add_admin_menu()
{
    add_menu_page(
        'WP Popup 設定',      // ページのタイトル
        'WP Popup',         // メニュー名
        'manage_options',     // 権限
        'wp_popup',           // メニュースラッグ
        'wpp_options_page_html', // 表示内容を生成する関数
        'dashicons-slides',   // アイコン (Dashiconsから選択)
        30                    // 表示位置
    );
}
add_action('admin_menu', 'wpp_add_admin_menu');

// 2. 設定ページのHTMLを出力
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
            WP Popup ver 1.0.0 | Developed by <a href="dev.yoshizumi.tech" target="_blank">YOSHIZUMI</a>
        </p>
    </div>
<?php
}

// 3. プラグイン設定の初期化
function wpp_settings_init()
{
    register_setting('wpp_settings_group', 'wpp_settings');
    add_settings_section('wpp_settings_section', 'ポップアップ設定', null, 'wp_popup');

    // --- 表示設定 ---
    add_settings_section('wpp_settings_section', '表示設定', null, 'wp_popup'); // ←セクション名も変更
    add_settings_field(
        'wpp_trigger_type',
        '表示トリガー',
        'wpp_field_trigger_type_cb',
        'wp_popup',
        'wpp_settings_section',
        // ★ 追加: 'wpp-trigger-type-row' クラスを付与
        ['class' => 'wpp-trigger-type-row']
    );

    add_settings_field('wpp_device_display', '表示デバイス', 'wpp_field_device_display_cb', 'wp_popup', 'wpp_settings_section');

    add_settings_field(
        'wpp_timer_seconds',
        'タイマーの秒数（秒）',
        'wpp_field_timer_seconds_cb',
        'wp_popup',
        'wpp_settings_section',
        // ★ 追加: 'wpp-timer-seconds-row' クラスを付与
        ['class' => 'wpp-timer-seconds-row']
    );
    add_settings_field(
        'wpp_scroll_pixels',
        'スクロール量（px）',
        'wpp_field_scroll_pixels_cb',
        'wp_popup',
        'wpp_settings_section',
        // ★ 追加: 'wpp-scroll-pixels-row' クラスを付与
        ['class' => 'wpp-scroll-pixels-row']
    );

    // ★ 追加: デザイン設定のセクションを追加（見た目の整理のため）
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
add_action('admin_init', 'wpp_settings_init');

// ★ 追加: オーバーレイ表示のコールバック関数
function wpp_field_show_overlay_cb()
{
    $options = get_option('wpp_settings');
    $checked = isset($options['show_overlay']) ? $options['show_overlay'] : 1;
?>
    <input type="hidden" name="wpp_settings[show_overlay]" value="0">
    <input type="checkbox" name="wpp_settings[show_overlay]" value="1" <?php checked($checked, 1); ?>> オーバーレイを表示する
    <p class="description">画面全体を半透明の黒いフィルターで覆い、ポップアップを際立たせます。</p>
<?php
}

// ★ 追加: 表示位置のコールバック関数
function wpp_field_popup_position_cb()
{
    $options = get_option('wpp_settings');
    $position = $options['popup_position'] ?? 'center';
?>
    <select name="wpp_settings[popup_position]">
        <option value="center" <?php selected($position, 'center'); ?>>中央</option>
        <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>>右下</option>
        <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>>左下</option>
    </select>
<?php
}

function wpp_field_popup_width_cb()
{
    $options = get_option('wpp_settings');
    // デフォルト値を400に設定
    $width = isset($options['popup_width']) ? $options['popup_width'] : '400';
?>
    <input type="number" name="wpp_settings[popup_width]" value="<?php echo esc_attr($width); ?>" min="100">
    <p class="description">ポップアップの横幅をピクセル単位で指定します。（例: 500）</p>
<?php
}

// ★ 変更点: 管理画面用のスクリプトを読み込む関数を追加
function wpp_admin_enqueue_scripts($hook)
{
    // プラグイン設定画面でのみスクリプトを読み込む
    if ('toplevel_page_wp_popup' != $hook) {
        return;
    }
    // WordPressのメディアアップローダーに必要なスクリプトを読み込む
    wp_enqueue_media();
    // 自作のJSファイルを読み込む
    wp_enqueue_script('wpp-media-uploader', plugin_dir_url(__FILE__) . 'js/media-uploader.js', ['jquery'], '1.0', true);

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wpp-color-picker-init', plugin_dir_url(__FILE__) . 'js/color-picker-init.js', ['wp-color-picker'], false, true);

    wp_enqueue_script('wpp-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', ['jquery'], '1.0', true);
}
add_action('admin_enqueue_scripts', 'wpp_admin_enqueue_scripts');

function wpp_field_show_text_cb()
{
    $options = get_option('wpp_settings');
    $checked = isset($options['show_text']) ? $options['show_text'] : 1;
?>
    <input type="hidden" name="wpp_settings[show_text]" value="0">
    <input type="checkbox" name="wpp_settings[show_text]" value="1" <?php checked($checked, 1); ?>> 表示する
<?php
}

// 4. 設定項目のコールバック関数
function wpp_field_trigger_type_cb()
{
    $options = get_option('wpp_settings');

?>
    <select id="wpp_trigger_type" name="wpp_settings[trigger_type]">
        <option value="timer" <?php selected($options['trigger_type'] ?? 'timer', 'timer'); ?>>タイマー</option>
        <option value="scroll" <?php selected($options['trigger_type'] ?? '', 'scroll'); ?>>スクロール</option>
    </select>
<?php
}

// ★ 追加: 表示デバイス選択のコールバック関数
function wpp_field_device_display_cb()
{
    $options = get_option('wpp_settings');
    $device_display = $options['device_display'] ?? 'all';
?>
    <select name="wpp_settings[device_display]">
        <option value="all" <?php selected($device_display, 'all'); ?>>すべてのデバイスで表示</option>
        <option value="pc" <?php selected($device_display, 'pc'); ?>>PCのみ表示</option>
        <option value="mobile" <?php selected($device_display, 'mobile'); ?>>スマホのみ表示</option>
    </select>
<?php
}

function wpp_field_timer_seconds_cb()
{
    $options = get_option('wpp_settings');
    // デフォルト値を3に設定
    $seconds = isset($options['timer_seconds']) ? $options['timer_seconds'] : '3';
?>
    <input type="number" name="wpp_settings[timer_seconds]" value="<?php echo esc_attr($seconds); ?>" min="0" step="0.1">
    <p class="description">ポップアップが表示されるまでの秒数を指定します。0.5のように小数点も使えます。</p>
<?php
}

function wpp_field_scroll_pixels_cb()
{
    $options = get_option('wpp_settings');
    // デフォルト値を200に設定
    $pixels = isset($options['scroll_pixels']) ? $options['scroll_pixels'] : '200';
?>
    <input type="number" name="wpp_settings[scroll_pixels]" value="<?php echo esc_attr($pixels); ?>" min="0">
    <p class="description">ポップアップが表示されるまでのスクロール量をピクセル単位で指定します。</p>
<?php
}

function wpp_field_bg_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['bg_color']) ? $options['bg_color'] : '#FFFFFF';
    echo '<input type="text" name="wpp_settings[bg_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}

// 背景の不透明度スライダー
function wpp_field_bg_opacity_cb()
{
    $options = get_option('wpp_settings');
    $opacity = $options['bg_opacity'] ?? '1';
?>
    <div style="display: flex; align-items: center;">
        <input type="range" name="wpp_settings[bg_opacity]" class="wpp-opacity-range" value="<?php echo esc_attr($opacity); ?>" min="0" max="1" step="0.01" style="flex: 1; margin-right: 10px; max-width: 300px;">
        <input type="number" class="wpp-opacity-number" value="<?php echo esc_attr($opacity); ?>" min="0" max="1" step="0.01" style="width: 60px;">
    </div>
    <p class="description">0で完全に透明、1で完全に不透明になります。</p>
<?php
}

function wpp_field_text_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['text_color']) ? $options['text_color'] : '#333333'; // デフォルト値
    echo '<input type="text" name="wpp_settings[text_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}



// ★ 追加: ボタン背景色のコールバック関数
function wpp_field_btn_bg_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['btn_bg_color']) ? $options['btn_bg_color'] : '#ffcc00'; // デフォルト値
    echo '<input type="text" name="wpp_settings[btn_bg_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}



// ★ 追加: ボタン文字色のコールバック関数
function wpp_field_btn_text_color_cb()
{
    $options = get_option('wpp_settings');
    $color = isset($options['btn_text_color']) ? $options['btn_text_color'] : '#000000'; // デフォルト値
    echo '<input type="text" name="wpp_settings[btn_text_color]" value="' . esc_attr($color) . '" class="wpp-color-picker">';
}

function wpp_field_popup_text_cb()
{
    $options = get_option('wpp_settings');
    // ★ 変更点①: デフォルトテキストを削除（ロジックは出力時に集約するため）
    $text = $options['popup_text'] ?? '';

    echo '<textarea name="wpp_settings[popup_text]" rows="4" cols="50">' . esc_textarea($text) . '</textarea>';
}

// ★ 追加: 背景画像アップローダーのコールバック関数
function wpp_field_bg_image_cb()
{
    $options = get_option('wpp_settings');
    $url = $options['bg_image'] ?? '';
?>
    <div class="wpp-media-uploader-field">
        <input type="hidden" name="wpp_settings[bg_image]" class="wpp-image-url-input" value="<?php echo esc_url($url); ?>">
        <button type="button" class="button wpp-upload-button">画像を選択</button>
        <button type="button" class="button wpp-remove-button">画像を削除</button>
        <div class="wpp-image-preview">
            <?php if (!empty($url)) : ?>
                <img src="<?php echo esc_url($url); ?>" style="max-width:200px;height:auto;margin-top:10px;">
            <?php endif; ?>
        </div>
    </div>
    <p class="description">背景画像を設定すると、背景色（不透明度含む）の上に表示されます。</p>

<?php
}

// ★ 変更点: 画像URLの項目をメディアアップローダー対応に差し替え
function wpp_field_image_uploader_cb()
{
    $options = get_option('wpp_settings');
    $url = $options['image_url'] ?? '';
?>
    <div class="wpp-media-uploader-field">
        <input type="hidden" name="wpp_settings[image_url]" class="wpp-image-url-input" value="<?php echo esc_url($url); ?>">
        <button type="button" class="button wpp-upload-button">画像を選択</button>
        <button type="button" class="button wpp-remove-button">画像を削除</button>
        <div class="wpp-image-preview">
            <?php if (!empty($url)) : ?>
                <img src="<?php echo esc_url($url); ?>" style="max-width:200px;height:auto;margin-top:10px;">
            <?php endif; ?>
        </div>
    </div>
<?php
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
?>
    <input type="hidden" name="wpp_settings[button_target]" value="0">
    <input type="checkbox" name="wpp_settings[button_target]" value="1" <?php checked($checked, 1); ?>> 新しいタブで開く
<?php
}

// 5. フロントエンドにCSSとJSを読み込む
function wpp_enqueue_assets()
{
    wp_enqueue_style('wpp-style', plugin_dir_url(__FILE__) . 'css/popup-style.css', [], '1.1');

    $options = get_option('wpp_settings');
    $trigger_type = $options['trigger_type'] ?? 'timer';

    if ($trigger_type === 'scroll') {
        wp_enqueue_script('wpp-scroll-js', plugin_dir_url(__FILE__) . 'js/popup-scroll.js', ['jquery'], '1.1', true); // ★ 変更: jqueryを追加

        // ★ 追加: PHPからJSへ値を渡す
        $pixels = isset($options['scroll_pixels']) ? (int)$options['scroll_pixels'] : 200;
        wp_localize_script(
            'wpp-scroll-js',     // 値を渡す対象のJSファイル
            'wpp_scroll_vars',   // JavaScript側で使うオブジェクト名
            ['pixels' => $pixels] // 渡すデータ（ピクセル数）
        );
    } else {
        wp_enqueue_script('wpp-timer-js', plugin_dir_url(__FILE__) . 'js/popup-timer.js', ['jquery'], '1.1', true);

        $seconds = isset($options['timer_seconds']) ? (float)$options['timer_seconds'] : 3;
        wp_localize_script(
            'wpp-timer-js',
            'wpp_timer_vars',
            ['seconds' => $seconds]
        );
    }
}
add_action('wp_enqueue_scripts', 'wpp_enqueue_assets');


// 6. サイトのフッターにポップアップのHTMLを出力
function wpp_add_popup_html_to_footer()
{
    $options = get_option('wpp_settings');

    $device_display = $options['device_display'] ?? 'all';

    // wp_is_mobile() はスマホ・タブレットでtrueを返すWordPressの標準関数
    $is_mobile = wp_is_mobile();

    // 表示しない条件に当てはまれば、何も出力せずに処理を終了
    if (($device_display === 'pc' && $is_mobile) || ($device_display === 'mobile' && !$is_mobile)) {
        return;
    }

    $base_color = isset($options['bg_color']) ? $options['bg_color'] : '#000000';
    $opacity = isset($options['bg_opacity']) ? $options['bg_opacity'] : '0.85';
    // ヘルパー関数（後述）を使ってHEXをRGBに変換
    $rgb_array = wpp_hex_to_rgb_array($base_color);
    $bg_color_rgba = sprintf('rgba(%s, %s, %s, %s)', $rgb_array[0], $rgb_array[1], $rgb_array[2], $opacity);

    // ★ 追加: 表示位置のスタイルを生成
    $position = $options['popup_position'] ?? 'center';
    $position_style = '';
    switch ($position) {
        case 'bottom-right':
            $position_style = 'top: auto; left: auto; right: 20px; bottom: 20px; transform: none;';
            break;
        case 'bottom-left':
            $position_style = 'top: auto; right: auto; left: 20px; bottom: 20px; transform: none;';
            break;
        case 'center':
        default:
            $position_style = 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            break;
    }


    // ポップアップ本体のスタイル
    $width = isset($options['popup_width']) ? esc_attr($options['popup_width']) : '400';
    $text_color = isset($options['text_color']) ? esc_attr($options['text_color']) : '#ffffff';

    $bg_image_url = isset($options['bg_image']) ? esc_url($options['bg_image']) : '';

    $popup_style = sprintf(
        '%s width: %spx; background-color: %s; color: %s;',
        $position_style,
        $width,
        $bg_color_rgba,
        $text_color
    );

    // ★ 追加: 背景画像が設定されていればスタイルを追加
    if (!empty($bg_image_url)) {
        $popup_style .= sprintf(
            "background-image: url('%s'); background-size: cover; background-position: center;",
            $bg_image_url
        );
    }

    // ★ 追加: ボタンのスタイル
    $btn_bg_color = isset($options['btn_bg_color']) ? esc_attr($options['btn_bg_color']) : '#ffcc00';
    $btn_text_color = isset($options['btn_text_color']) ? esc_attr($options['btn_text_color']) : '#000000';
    $button_style = sprintf(
        'background-color: %s; color: %s;',
        $btn_bg_color,
        $btn_text_color
    );

    // 他の変数の定義...
    $show_text = isset($options['show_text']) ? $options['show_text'] : 1;
    $default_text = "ここに注目を集めるタイトルを入れます\nこれはポップアップの本文です。";
    $popup_text_raw = !empty($options['popup_text']) ? $options['popup_text'] : $default_text;
    $popup_text_formatted = nl2br(esc_textarea($popup_text_raw));
    $image_url = $options['image_url'] ?? '';
    $button_text = $options['button_text'] ?? '今すぐ参加する';
    $button_url = $options['button_url'] ?? '#';

    // ★ 追加: リンクを新しいタブで開くかの設定値を取得
    $button_target_blank = isset($options['button_target']) && $options['button_target'] == 1;

    // ★ 変更: 設定に応じてonclickの挙動を変更
    if ($button_target_blank) {
        $onclick_action = "window.open('" . esc_url($button_url) . "', '_blank');";
    } else {
        $onclick_action = "window.location.href='" . esc_url($button_url) . "';";
    }

    $show_overlay = isset($options['show_overlay']) ? $options['show_overlay'] : 1;
    $container_class = $show_overlay ? 'wpp-container has-overlay' : 'wpp-container';
?>
    <div id="wpp-container" class="<?php echo $container_class; ?>" style="display: none;">
        <?php // オーバーレイがONの場合だけ、背景用のdivを出力
        if ($show_overlay) : ?>
            <div class="wpp-overlay"></div>
        <?php endif; ?>

        <div class="wpp-popup" id="wpp-popup" style="<?php echo $popup_style; ?>">
            <div class="wpp-popup-content">
                <div class="wpp-close-icon" id="wpp-closePopup">×</div>

                <?php if ($show_text) : ?>
                    <p><?php echo $popup_text_formatted; ?></p>
                <?php endif; ?>

                <?php if (!empty($image_url)): ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="" width="100%" style="margin: 20px 0;">
                <?php endif; ?>

                <button class="wpp-cta-button" style="<?php echo $button_style; ?>" onclick="<?php echo $onclick_action; ?>"><?php echo esc_html($button_text); ?></button>
            </div>
        </div>
    </div>
<?php
}
add_action('wp_footer', 'wpp_add_popup_html_to_footer');

// ★ 追加: HEXカラーコードをRGB配列に変換するヘルパー関数
function wpp_hex_to_rgb_array($hex)
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return [$r, $g, $b];
}
