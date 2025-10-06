<?php
/*
Plugin Name: CSLink Banner Plugin
Description: Adds top banner from API and sticky bottom banner
Version: 1.1
Author: Grok
*/

function cslink_top_banner_html() {
    $response = wp_remote_get('https://cslink.site/banners?category_id=7');
    $banner_html = '';

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['success']) && $data['success'] && isset($data['html'])) {
            $banner_html = $data['html'];
        }
    }

    ?>
    <div id="cslink-top-banner" class="cslink-banner-container"><?php echo $banner_html; ?></div>
    <?php
}
add_action('wp_head', 'cslink_top_banner_html');

function cslink_banner_css() {
    ?>
    <style>
        .cslink-banner-container {
            width: 100%;
            text-align: center;
            z-index: 999;
        }
        .cslink-bottom-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: transparent;
            z-index: 1000;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }
        .cslink-bottom-banner-content {
            position: relative;
            max-width: 1000px;
            width: 90%;
            margin: 0 auto;
        }
        .cslink-banner-img {
            width: 100%;
            max-width: 1000px;
            height: 90px;
            object-fit: cover;
            display: block;
        }
        .cslink-close-button {
            position: absolute;
            top: -10px;
            right: -10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .cslink-close-button:hover {
            background: rgba(0, 0, 0, 1);
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .cslink-bottom-banner-content { width: 95%; }
            .cslink-banner-img { height: 70px; }
        }
    </style>
    <?php
}
add_action('wp_head', 'cslink_banner_css');

function cslink_bottom_banner_html() {
    ?>
    <div id="cslink-bottom-banner" class="cslink-bottom-banner">
        <div class="cslink-bottom-banner-content">
            <a href="http://cslink.site/go/casimedya" target="_blank" rel="noopener">
                <img src="https://imagedelivery.net/tCKNxRxatPhv845e8mCGdw/edf0ce93-d2e9-410a-eba8-4ba61fe47200/public" alt="Banner" class="cslink-banner-img">
            </a>
            <button class="cslink-close-button" onclick="closeBottomBanner()">×</button>
        </div>
    </div>
    <script>
        function closeBottomBanner() {
            document.getElementById('cslink-bottom-banner').style.display = 'none';
            localStorage.setItem('cslink_bottom_banner_closed', 'true');
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('cslink_bottom_banner_closed') === 'true') {
                document.getElementById('cslink-bottom-banner').style.display = 'none';
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'cslink_bottom_banner_html');
?>
