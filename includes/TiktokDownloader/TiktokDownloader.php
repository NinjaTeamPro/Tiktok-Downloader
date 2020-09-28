<?php
namespace NjtTiktok\TiktokDownloader;

defined('ABSPATH') || exit;

use NjtTiktok\TiktokDownloader\TiktokAPI;

class TiktokDownloader
{
    protected static $instance = null;
    public $titokSetting;
    private $hook_suffix = array();

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
                'text_example' => '@TikTok, #trend, https://www.tiktok.com/@ninjateamwp/video/6836997625070259458',
            );
        }
        register_shutdown_function(array($this, 'saveOptions'));

        //creat shortcode
        if (empty($this->titokSetting['text_shortcode'])) {
            $this->titokSetting['text_shortcode'] = 'tiktok-download';
            update_option('njt_tk_settings', $this->titokSetting);
        }
        add_shortcode($this->titokSetting['text_shortcode'], array($this, 'njt_tk_create_shortcode'));
        add_action('admin_menu', array($this, 'njt_tk_tiktokDownloader'));

        //Register Enqueue
        add_action('wp_enqueue_scripts', array($this, 'homeRegisterEnqueue'));
        add_action('admin_enqueue_scripts', array($this, 'adminRegisterEnqueue'));

        add_action('wp_ajax_njt_tk_tiktok_search', array($this, 'ajaxTiktokSearch'));
        add_action('wp_ajax_nopriv_njt_tk_tiktok_search', array($this, 'ajaxTiktokSearch'));

        add_action('wp_ajax_njt_tk_view_popup', array($this, 'njt_tk_viewPopup'));        
        add_action('wp_ajax_nopriv_njt_tk_view_popup', array($this, 'njt_tk_viewPopup'));

        add_action('wp_ajax_njt_tk_search_videourl', array($this, 'njt_tk_searchVideoUrl'));
        add_action('wp_ajax_nopriv_njt_tk_search_videourl', array($this, 'njt_tk_searchVideoUrl'));

        add_action('wp_ajax_njt_tk_download_video', array($this, 'njt_tk_downloadVideo'));
        add_action('wp_ajax_nopriv_njt_tk_download_video', array($this, 'njt_tk_downloadVideo'));

        add_action('wp_ajax_njt_tk_video_stream', array($this, 'video_stream'));
        add_action('wp_ajax_nopriv_njt_tk_video_stream', array($this, 'video_stream'));
    }
    public function saveOptions()
    {
        update_option('njt_tk_settings', $this->titokSetting);

    }

    public function homeRegisterEnqueue()
    {

        wp_enqueue_style('fancybox.css', NJT_TK_PLUGIN_URL . 'assets/home/js/fancybox/jquery.fancybox.css');
        wp_enqueue_script('fancybox.js', NJT_TK_PLUGIN_URL . 'assets/home/js/fancybox/jquery.fancybox.js', array('jquery'));

        wp_register_style('njt-tiktok', NJT_TK_PLUGIN_URL . 'assets/home/css/home-tiktok-downloader.css');
        wp_enqueue_style('njt-tiktok');

        wp_register_script('njt-tiktok', NJT_TK_PLUGIN_URL . 'assets/home/js/home-tiktok-downloader.js', array('jquery'));
        wp_enqueue_script('njt-tiktok');

        wp_localize_script('njt-tiktok', 'wpData', array(
            'admin_ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("njt-tk-downloader"),
            'NJT_TK_PLUGIN_URL' => NJT_TK_PLUGIN_URL,
            'viewVideoUrlSearch' => NJT_TK_PLUGIN_URL . 'views/pages/home/html-tiktok-search-videourl.php',
        ));
    }

    public function adminRegisterEnqueue($suffix)
    {
        if (in_array($suffix, $this->hook_suffix)) {
            wp_register_style('njt-tiktok-admin', NJT_TK_PLUGIN_URL . 'assets/admin/css/admin-tiktok-downloader.css');
            wp_enqueue_style('njt-tiktok-admin');
        }
        wp_register_style('njt-tiktok-icon', NJT_TK_PLUGIN_URL . 'assets/admin/css/style-icon.css');
        wp_enqueue_style('njt-tiktok-icon');
    }

    public function njt_tk_tiktokDownloader()
    {
        $settings_suffix = add_menu_page(
            __('TikTok Downloader', NJT_TK_DOMAIN),
            __('Downloader', NJT_TK_DOMAIN),
            'manage_options',
            __('tiktok_video_downloader', NJT_TK_DOMAIN),
            array($this, 'njt_tk_adminTiktokDownloader'),
            NJT_TK_PLUGIN_URL . 'assets/admin/img/Icon_Tiktok.svg',
            9
        );
        $this->hook_suffix = array($settings_suffix);
    }

    public function njt_tk_adminTiktokDownloader()
    {
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/admin/html-admin-tiktok-downloader.php';
        include_once $viewPath;
    }

    public function njt_tk_create_shortcode()
    {
        ob_start();
        $viewPath = NJT_TK_PLUGIN_PATH . 'views/pages/home/html-tiktok-search.php';
        include_once $viewPath;
        return ob_get_clean();
    }

    public function ajaxTiktokSearch()
    {

        // if (!wp_verify_nonce($_POST['nonce'], 'njt-tk-downloader')) {
        //     wp_die();
        // }

        //check_ajax_referer('njt-tk-downloader', 'nonce', true);
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
        $pattern = '/(https:\/\/+[a-z0-9]+-web-newkey.tiktokcdn.com)\/[a-z0-9@]*/';
        $result = preg_match($pattern, $linkVideo);

        if ($result) {
            if(isset($_POST['njt-button-download-no-watermark'])) {
                $tiktokApi = TiktokApi::getInstance();
                $videoId =  $tiktokApi->njt_tk_GetKey($linkVideo);
                if (!$videoId) {
                    $this->downloadDefaultVideoOrMusic($linkVideo, 'video/mp4', 'video-tiktok.mp4');
                } else {
                    $this->downloadVideoWithoutWaterMark($videoId);
                }
            } elseif (isset($_POST['njt-button-download-watermark'])) {
                $this->downloadDefaultVideoOrMusic($linkVideo, 'video/mp4', 'video-tiktok.mp4');
            } else{
                $this->downloadDefaultVideoOrMusic($linkVideo, 'audio/mpeg', 'music-tiktok.mp3');
            }
            exit();
        } else {
            exit('Can not download video');
        }
    }

    public function downloadDefaultVideoOrMusic($linkUrl, $type, $name) {
        if (isset($linkUrl)) {
            ob_start();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: public');
            if (isset($_SERVER['HTTP_REQUEST_USER_AGENT']) && strpos($_SERVER['HTTP_REQUEST_USER_AGENT'], 'MSIE') !== false) {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            }
            header('Connection: Close');
            ob_clean();
            flush();
            readfile($linkUrl, "", stream_context_create([
                "ssl"  => [
                    "verify_peer"      => false,
                    "verify_peer_name" => false,
                ],
                "http" => [
                    "header" => [
                        "Referer: https://www.tiktok.com/foryou?lang=en",
                    ],
                ],
    
            ]));
            exit;
        } else {
            die("Invalid file name!");
        }
    }
    public function downloadVideoWithoutWaterMark($videoId, $file_name="tk", $ext = "mp4") {
       
        try {
            $link = 'https://api2-16-h2.musical.ly/aweme/v1/play/?video_id='.$videoId.'&vr_type=0&is_play_url=1&source=PackSourceEnum_PUBLISH&media_type=4';
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
            $file = urldecode($redirectURL);
            $fopen = fopen($file ,"rb");
            header('Content-Description: File Transfer');
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . basename('video-tiktok.mp4') . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            $fread = fpassthru($fopen);
            fclose($fopen);
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage());
        }
    }

    public function video_stream()
    {
        $this->stream(esc_url(urldecode($_GET['url'])));
    }

    public function stream($url)
    {
        $ch = curl_init();

        $headers   = [];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        $options = array(
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'okhttp',
            CURLOPT_REFERER        => 'https://www.tiktok.com/',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_BUFFERSIZE => 256 * 1024,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_HEADERFUNCTION => array($this, 'headerCallback'),
            CURLOPT_WRITEFUNCTION => array($this, 'bodyCallback')
        );

        curl_setopt_array($ch, $options);
        $ret = curl_exec($ch);
        curl_close($ch);

        return true;
    }

    public function headerCallback($ch, $data)
    {
        // this should be first line
        if (preg_match('/HTTP\/[\d.]+\s*(\d+)/', $data, $matches)) {
            $status_code = $matches[1];

            // if Forbidden or Not Found -> those are "valid" statuses too
            if (200 == $status_code || 206 == $status_code || 403 == $status_code || 404 == $status_code) {
                $this->headers_sent = true;
                $this->sendHeader(rtrim($data));
            }
        } else {

            // only headers we wish to forward back to the client
            $forward = ['content-type', 'content-length', 'accept-ranges', 'content-range'];

            $parts = explode(':', $data, 2);

            if ($this->headers_sent && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                $this->sendHeader(rtrim($data));
            }
        }

        return strlen($data);
    }

    public function bodyCallback($ch, $data)
    {
        if (true) {
            echo $data;
            flush();
        }

        return strlen($data);
    }

    protected function sendHeader($header)
    {

        header($header);
    }
}
