<?php
namespace NjtTiktok\TiktokDownloader;

defined('ABSPATH') || exit;

use NjtTiktok\TiktokDownloader\TiktokAPI;

class TiktokDownloader
{
    protected static $instance = null;
    public $titokSetting;

    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        // Loading Options
        // Options
        $this->titokSetting = get_option('njt_tk_settings');
        if (empty($this->titokSetting)) {
            $this->titokSetting = array( // Setting up default values
                'text_shortcode' => 'tiktok-download',
                'text_heading' => 'TikTok Video Downloader',
                'text_description' => 'Search by Username, Hashtag or Video URL & download video with or without watermark',
                'text_example' => '@TikTok, #trend, https://www.tiktok.com/@zaheun/video/6811712703208361218',
            );
        }
        register_shutdown_function(array($this, 'saveOptions'));

        //Register Enqueue
        add_action('wp_enqueue_scripts', array($this, 'homeRegisterEnqueue'));
        add_action('admin_enqueue_scripts', array($this, 'adminRegisterEnqueue'));

        //creat shortcode
        if (empty($this->titokSetting['text_shortcode'])) {
            $this->titokSetting['text_shortcode'] = 'tiktok-download';
            update_option('njt_tk_settings', $this->titokSetting);
        }
        add_shortcode($this->titokSetting['text_shortcode'], array($this, 'create_shortcode'));
        add_action('admin_menu', array($this, 'njt_tk_tiktokDownloader'));
        add_action('wp_ajax_njt_tk_tiktok_search', array($this, 'ajaxTiktokSearch'));
        add_action('wp_ajax_njt_tk_view_popup', array($this, 'njt_tk_viewPopup'));
        add_action('wp_ajax_nopriv_njt_tk_view_popup', array($this, 'njt_tk_viewPopup'));
        add_action('wp_ajax_njt_tk_search_videourl', array($this, 'njt_tk_searchVideoUrl'));
        add_action('wp_ajax_nopriv_njt_tk_search_videourl', array($this, 'njt_tk_searchVideoUrl'));
        add_action('wp_ajax_njt_tk_download_video', array($this, 'njt_tk_downloadVideo'));
        add_action('wp_ajax_nopriv_njt_tk_download_video', array($this, 'njt_tk_downloadVideo'));
    }
    public function saveOptions()
    {
        update_option('njt_tk_settings', $this->titokSetting);

    }

    public function homeRegisterEnqueue()
    {

        wp_enqueue_style('fancybox.css', NJT_TK_PLUGIN_URL . '/assets/home/js/fancybox/jquery.fancybox.css');
        wp_enqueue_script('fancybox.js', NJT_TK_PLUGIN_URL . '/assets/home/js/fancybox/jquery.fancybox.js', array('jquery'));

        wp_register_style('njt-tiktok', NJT_TK_PLUGIN_URL . '/assets/home/css/home-tiktok-downloader.css');
        wp_enqueue_style('njt-tiktok');

        wp_register_script('njt-tiktok', NJT_TK_PLUGIN_URL . '/assets/home/js/home-tiktok-downloader.js', array('jquery'));
        wp_enqueue_script('njt-tiktok');

        wp_localize_script('njt-tiktok', 'wpData', array(
            'admin_ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("njt-tk-downloader"),
            'NJT_TK_PLUGIN_URL' => NJT_TK_PLUGIN_URL,
            'viewVideoUrlSearch' => NJT_TK_PLUGIN_URL . 'views/pages/home/html-tiktok-search-videourl.php',
        ));
    }

    public function adminRegisterEnqueue()
    {
        wp_register_style('njt-tiktok-admin', NJT_TK_PLUGIN_URL . '/assets/admin/css/admin-tiktok-downloader.css');
        wp_enqueue_style('njt-tiktok-admin');
    }

    public function njt_tk_tiktokDownloader()
    {
        add_menu_page(
            __('Tiktok Downloader', NJT_TK_DOMAIN),
            __('Tiktok Downloader', NJT_TK_DOMAIN),
            'manage_options',
            __('tiktok_video_downloader', NJT_TK_DOMAIN),
            array($this, 'njt_tk_adminTiktokDownloader'),
            '',
            9
        );
    }

    public function njt_tk_adminTiktokDownloader()
    {
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/admin/html-admin-tiktok-downloader.php';
        include_once $viewPath;
    }

    public function create_shortcode()
    {
        ob_start();
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/home/html-tiktok-search.php';
        include_once $viewPath;
        return ob_get_clean();
    }

    public function ajaxTiktokSearch()
    {

        if (!wp_verify_nonce($_POST['nonce'], 'njt-tk-downloader')) {
            wp_die();
        }

        check_ajax_referer('njt-tk-downloader', 'nonce', true);
        // type = 1-> user
        // type = 3 -> hashtag
        // type = 2 -> url

        $valueSearch = (isset($_POST['valueSearch'])) ? esc_attr($_POST['valueSearch']) : '';
        if (!empty($valueSearch)) {
            $tiktokApi = TiktokApi::getInstance();
            $searchType = null;
            if ($valueSearch && substr($valueSearch, 0, 1) == '@') {
                $dataVideo = $tiktokApi->constructDataVideo($valueSearch, 1);
                $searchType = 1;
            } else if ($valueSearch && substr($valueSearch, 0, 1) == '#') {
                $dataVideo = $tiktokApi->constructDataVideo($valueSearch, 3);
                $searchType = 3;
            } else {
                $dataVideo = $tiktokApi->constructDataSearhVideoUrl($valueSearch);
                $searchType = 2;
            }

            if ($dataVideo) {
                $resdataUser = array(
                    'searchType' => $searchType,
                    'dataVideo' => $dataVideo,
                );
                wp_send_json_success($resdataUser);
            }
        }
        wp_die();
    }
    public function njt_tk_viewPopup()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'njt-tk-downloader')) {
            wp_die();
        }
        $objData = json_decode(stripcslashes($_POST['datavideo']));
        $dataPopup = (array) $objData;
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/home/html-tiktok-video-popup.php';
        include_once $viewPath;
        exit();
    }
    public function njt_tk_searchVideoUrl()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'njt-tk-downloader')) {
            wp_die();
        }
        $dataPopup = $_POST['dataSearch'];
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/home/html-tiktok-video-popup.php';
        include_once $viewPath;
        exit();
    }
    public function njt_tk_downloadVideo()
    {
        if (!wp_verify_nonce($_POST['njt-tk-settings-security-token'], 'njt-tk-settings-security-token')) {
            wp_die();
        }
        $linkVideo = !empty($_POST['njt-tk-download-video']) ? $_POST['njt-tk-download-video'] : '';

        if(isset($_POST['njt-button-download-no-watermark'])) {
            $tiktokApi = TiktokApi::getInstance();
            $videoId =  $tiktokApi->njt_tk_GetKey($linkVideo);
            $this->downloadVideoWithoutWaterMark($videoId);
        } elseif (isset($_POST['njt-button-download-watermark'])) {
            $this->downloadDefaultVideoOrMusic($linkVideo, 'video/mp4', 'video-tiktok.mp4');
        } else{
            $this->downloadDefaultVideoOrMusic($linkVideo, 'audio/mpeg', 'music-tiktok.mp3');
        }
        exit();
    }

    public function downloadDefaultVideoOrMusic($linkUrl, $type, $name) {
        if (isset($linkUrl)) {
            $file = urldecode($linkUrl); // Decode URL-encoded string // Decode URL-encoded string
            header('Content-Description: File Transfer');
            header('Content-Type:'.$type);
            header('Content-Disposition: attachment; filename="' . basename($name) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($file);
        } else {
            die("Invalid file name!");
        }
    }
    public function downloadVideoWithoutWaterMark($videoId) {
        if (!$videoId) {
            die("Invalid file name!");
        }
        try {
            $link = 'https://api2.musical.ly/aweme/v1/playwm/?video_id=' . $videoId;
            $ch = curl_init();
            $headers = [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Accept-Encoding: gzip, deflate, br',
                'Accept-Language: en-US,en;q=0.9',
                'Range: bytes=0-200000',
            ];
    
            $options = array(
                CURLOPT_URL => $link,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
                CURLOPT_ENCODING => "utf-8",
                CURLOPT_AUTOREFERER => true,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_MAXREDIRS => 10,
            );
            curl_setopt_array($ch, $options);
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            curl_exec($ch);
            $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            header('Content-Description: File Transfer');
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . basename('video-tiktok.mp4') . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            readfile($redirectURL);
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage());
        }
    }
}
