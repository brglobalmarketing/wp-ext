<?php
/*
Plugin Name: CSLink Banner Auto Loader
Description: cslink.site banner verisini sunucu tarafında çekip <body> açılır açılmaz en üste enjekte eder ve altta sticky banner ekler.
Version: 1.4
Author: Dev
*/

if (!defined('ABSPATH')) exit;

// ===== Ayarlar =====
define('CSLINK_ENDPOINT', 'https://cslink.site/banners?category_id=7');
define('CSLINK_CACHE_KEY', 'cslink_banner_html_cache');
define('CSLINK_CACHE_TTL', 300); // sn cinsinden (5 dk)
define('CSLINK_STICKY_BANNER_IMAGE', 'https://imagedelivery.net/tCKNxRxatPhv845e8mCGdw/edf0ce93-d2e9-410a-eba8-4ba61fe47200/public');
define('CSLINK_STICKY_BANNER_LINK', 'http://cslink.site/go/casimedya');

// ===== Uzak HTML'i çek + cache'le (mevcut top banner için) =====
function cslink_fetch_banner_html_fresh() {
    $response = wp_remote_get(CSLINK_ENDPOINT, [
        'timeout'   => 12,
        'sslverify' => false,
        'headers'   => [
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) return '';

    $body = wp_remote_retrieve_body($response);
    if (!$body) return '';

    $data = json_decode($body, true);
    if (isset($data['success']) && $data['success'] && !empty($data['html'])) {
        // Dış kabuğu da ekle (yüksek z-index)
        $html = '<div id="banner-container" style="position:relative;width:100%;margin:0 auto;">'
              . $data['html']
              . '</div>';
        return $html;
    }

    return '';
}

function cslink_get_banner_html() {
    // ?csdebug=1 ile cache bypass (yalnızca giriş yapmış kullanıcılar için)
    $debug_bypass = (is_user_logged_in() && isset($_GET['csdebug']) && $_GET['csdebug'] == '1');

    if (!$debug_bypass) {
        $cached = get_transient(CSLINK_CACHE_KEY);
        if ($cached !== false && is_string($cached)) {
            return $cached;
        }
    }

    $fresh = cslink_fetch_banner_html_fresh();
    if ($fresh) {
        set_transient(CSLINK_CACHE_KEY, $fresh, CSLINK_CACHE_TTL);
    }
    return $fresh;
}

// ===== Sticky Bottom Banner HTML =====
function cslink_get_sticky_banner_html() {
    $banner_html = '
    <div id="cslink-sticky-banner" style="position: fixed; bottom: 0; left: 0; right: 0; width: 100%; z-index: 9999; background: #fff; text-align: center; box-shadow: 0 -2px 5px rgba(0,0,0,0.1);">
        <div style="position: relative; max-width: 1000px; margin: 0 auto;">
            <a href="' . esc_url(CSLINK_STICKY_BANNER_LINK) . '" target="_blank" rel="noopener noreferrer">
                <img src="' . esc_url(CSLINK_STICKY_BANNER_IMAGE) . '" alt="Banner" style="width: 100%; max-width: 1000px; height: auto; display: block;">
            </a>
            <button id="cslink-close-banner" style="position: absolute; top: 5px; right: 5px; background: #fff; border: 1px solid #ccc; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; line-height: 1; text-align: center; padding: 0;">&times;</button>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var closeButton = document.getElementById("cslink-close-banner");
            var banner = document.getElementById("cslink-sticky-banner");
            if (closeButton && banner) {
                closeButton.addEventListener("click", function() {
                    banner.style.display = "none";
                    // Optional: Save close state in sessionStorage to persist for session
                    sessionStorage.setItem("cslink_sticky_banner_closed", "true");
                });
                // Check if banner was previously closed in this session
                if (sessionStorage.getItem("cslink_sticky_banner_closed") === "true") {
                    banner.style.display = "none";
                }
            }
        });
    </script>';

    return $banner_html;
}

// ===== Body açılır açılmaz enjekte (tema bağımsız) =====
// Top banner için mevcut hook
add_action('wp_body_open', function () {
    if (cslink_should_skip()) return;
    echo cslink_get_banner_html();
}, 0);

// Sticky bottom banner için wp_footer hook'u kullanıyoruz
add_action('wp_footer', function () {
    if (cslink_should_skip()) return;
    echo cslink_get_sticky_banner_html();
}, 9999);

// Bazı temalarda wp_body_open yok. Güvenli çözüm: output buffer ile <body> tagından hemen sonra enjekte et (top banner için)
add_action('template_redirect', function () {
    if (cslink_should_skip()) return;

    // Admin/REST/Ajax/Feed/CLI vb. durumlarda devre dışı
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed()) {
        return;
    }

    // Sadece HTML yanıtlar için
    ob_start('cslink_inject_after_body_open');
}, 0);

function cslink_inject_after_body_open($html) {
    // İlk <body ...>’dan hemen sonra top banner ekle
    $banner = cslink_get_banner_html();
    if (!$banner) return $html;

    // AMP sayfalarını bozmayalım
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
        return $html;
    }

    // <body> etiketini bul ve sadece ilk eşleşmeden sonra yerleştir
    $pattern = '/(<body\b[^>]*>)/i';
    if (preg_match($pattern, $html)) {
        $html = preg_replace($pattern, '$1' . $banner, $html, 1);
    }

    return $html;
}

// ===== Koşullar =====
function cslink_should_skip() {
    if (is_admin()) return true;
    if (is_feed()) return true;
    if (wp_doing_ajax()) return true;
    if (defined('REST_REQUEST') && REST_REQUEST) return true;
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) return true;

    return false;
}
?>
