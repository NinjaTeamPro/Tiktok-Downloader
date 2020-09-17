<?php
namespace NjtTiktok\TiktokDownloader;

defined('ABSPATH') || exit;

class TiktokAPI
{
    protected static $instance = null;
    private $apiUrl;

    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->apiUrl = 'https://www.tiktok.com/node';
    }
    //Search User
    public function getUserId($username)
    {
        if (!$username) {
            return;
        }
        $userUrl = $this->apiUrl . '/share/user/' . $username;
        $resUser = $this->njt_tk_jsonDecode($userUrl);
        $userId = $resUser['body']['userData']['userId'];

        return $userId;
    }

    public function getDataVideoUser($username)
    {
        if (!$username) {
            return;
        }
        $userId = $this->getUserId($username);
        if (!$userId) {
            return;
        }
        $urlVideoUser = add_query_arg(array(
            'id' => $userId,
            'minCursor' => 0,
            'maxCursor' => 0,
            'count' => 78,
            'type' => 1,
        ), "{$this->apiUrl}/video/feed");

        $resListVideoUser = $this->njt_tk_jsonDecode($urlVideoUser);

        return $resListVideoUser['body'];
    }

    //Search Hagtag
    public function getHashtagId($hashtag)
    {
        $subHashTag = $hashtag;
        if (!$hashtag) {
            return;
        }
        if (substr($hashtag, 0, 1) == '#') {
            $subHashTag = substr($hashtag, 1);
        }

        $urlHashtag = "{$this->apiUrl}/share/tag/{$subHashTag}";

        $resHashtag = $this->njt_tk_jsonDecode($urlHashtag);
        $hashtagId = $resHashtag['body']['challengeData']['challengeId'];

        return $hashtagId;
    }

    public function getDataVideoHashtag($hashtag)
    {
        if (!$hashtag) {
            return;
        }
        $hashTagId = $this->getHashtagId($hashtag);
        if (!$hashTagId) {
            return;
        }

        $urlVideoHashtag = add_query_arg(array(
            'id' => $hashTagId,
            'minCursor' => 0,
            'maxCursor' => 0,
            'count' => 78,
            'type' => 3,
        ), "{$this->apiUrl}/video/feed");

        $resListVideoHashtag = $this->njt_tk_jsonDecode($urlVideoHashtag);

        return $resListVideoHashtag['body'];
    }

    public function constructDataVideo($valueSearch, $type = 3)
    {
        if (!$valueSearch) {
            return;
        }
        if ($type == 1) {
            $dataVideo = $this->getDataVideoUser($valueSearch);
        } else {
            $dataVideo = $this->getDataVideoHashtag($valueSearch);
        }
        $itemListData = $dataVideo['itemListData'];

        $itemVideo = array();

        foreach ($itemListData as $item) {
            $itemVideo[] = array(
                'pluginName' => NJT_TK_PLUGIN_DIR,
                //itemInfos
                'videoId' => $item['itemInfos']['id'] ? $item['itemInfos']['id'] : '',
                'createTime' => $this->formatCreatedTime($item['itemInfos']['createTime'] ? $item['itemInfos']['createTime']  : ''),
                'covers' => $item['itemInfos']['covers'][0] ? $item['itemInfos']['covers'][0] :'',
                'videoUrl' => $item['itemInfos']['video']['urls'][0] ? $item['itemInfos']['video']['urls'][0] : '',
                'videoDes' => $item['itemInfos']['text'] ? $item['itemInfos']['text'] : '',
                'videoLike' => $this->njt_tk_formatNumber($item['itemInfos']['diggCount'] ? $item['itemInfos']['diggCount'] : ''),
                'videoShare' => $this->njt_tk_formatNumber($item['itemInfos']['shareCount'] ? $item['itemInfos']['shareCount'] : ''),
                'videoComment' => $this->njt_tk_formatNumber($item['itemInfos']['commentCount'] ? $item['itemInfos']['commentCount']: ''),
                'playCount' => $this->njt_tk_formatNumber($item['itemInfos']['playCount'] ? $item['itemInfos']['playCount'] : ''),
                //authorInfos
                'userId' => $item['authorInfos']['userId'] ? $item['authorInfos']['userId'] : '',
                'username' => $item['authorInfos']['uniqueId'] ? $item['authorInfos']['uniqueId'] : '',
                'nickName' => $item['authorInfos']['nickName'] ? $item['authorInfos']['nickName'] : '',
                'avatar' => $item['authorInfos']['covers'][0] ? $item['authorInfos']['covers'][0] : '',
                //musicInfos
                'musicId' => $item['musicInfos']['musicId'] ? $item['musicInfos']['musicId'] : '',
                'musicName' => $item['musicInfos']['musicName'] ? $item['musicInfos']['musicName'] : '',
                'authorName' => $item['musicInfos']['authorName'] ? $item['musicInfos']['authorName'] : '' ,
            );
        }
        return $itemVideo;
    }

    //Search Video Url
    public function searhVideoUrl($link)
    {
        if (!$link) {
            return;
        }

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
        $makeArrLink = explode('/', $redirectURL);
        $sliceArrLink = array_slice($makeArrLink, -3);
        $userName = $sliceArrLink[0];
        $videoId = $sliceArrLink[2];

        if (!$videoId) {
            return;
        }

        $videoUrl = "{$this->apiUrl}/share/video/{$userName}/{$videoId}";

        $resVideoUrl = $this->njt_tk_jsonDecode($videoUrl);

        return $resVideoUrl['body'];
    }

    public function constructDataSearhVideoUrl($link)
    {
        if (!$link) {
            return;
        }
        $dataSearchVideo = $this->searhVideoUrl($link);
        if (!$dataSearchVideo) {
            return;
        }
        $itemVideo = array(
            'pluginName' => NJT_TK_PLUGIN_DIR,
            //video data
            'videoId' => $dataSearchVideo['videoData']['itemInfos']['id'] ? $dataSearchVideo['videoData']['itemInfos']['id'] : '',
            'createTime' => $this->formatCreatedTime($dataSearchVideo['videoData']['itemInfos']['createTime'] ? $dataSearchVideo['videoData']['itemInfos']['createTime'] : ''),
            'covers' => $dataSearchVideo['videoData']['itemInfos']['covers'][0] ? $dataSearchVideo['videoData']['itemInfos']['covers'][0] : '',
            'videoUrl' => $dataSearchVideo['videoData']['itemInfos']['video']['urls'][0] ? $dataSearchVideo['videoData']['itemInfos']['video']['urls'][0] : '',
            'videoDes' => $dataSearchVideo['videoData']['itemInfos']['text'] ? $dataSearchVideo['videoData']['itemInfos']['text'] : '',
            'videoLike' => $this->njt_tk_formatNumber($dataSearchVideo['videoData']['itemInfos']['diggCount'] ? $dataSearchVideo['videoData']['itemInfos']['diggCount'] : ''),
            'videoShare' => $this->njt_tk_formatNumber($dataSearchVideo['videoData']['itemInfos']['shareCount'] ? $dataSearchVideo['videoData']['itemInfos']['shareCount'] :''),
            'videoComment' => $this->njt_tk_formatNumber($dataSearchVideo['videoData']['itemInfos']['commentCount'] ? $dataSearchVideo['videoData']['itemInfos']['commentCount'] : ''),
            'playCount' => $this->njt_tk_formatNumber($dataSearchVideo['videoData']['itemInfos']['playCount'] ? $dataSearchVideo['videoData']['itemInfos']['playCount'] : ''),
            //authorInfos
            'userId' => $dataSearchVideo['videoData']['authorInfos']['userId'] ? $dataSearchVideo['videoData']['authorInfos']['userId'] :'',
            'username' => $dataSearchVideo['videoData']['authorInfos']['uniqueId'] ? $dataSearchVideo['videoData']['authorInfos']['uniqueId'] :'',
            'nickName' => $dataSearchVideo['videoData']['authorInfos']['nickName'] ? $dataSearchVideo['videoData']['authorInfos']['nickName'] : '',
            'avatar' => $dataSearchVideo['videoData']['authorInfos']['covers'][0] ? $dataSearchVideo['videoData']['authorInfos']['covers'][0] : '',
            //musicInfos
            'musicId' => $dataSearchVideo['videoData']['musicInfos']['musicId'] ? $dataSearchVideo['videoData']['musicInfos']['musicId'] : '',
            'musicName' => $dataSearchVideo['videoData']['musicInfos']['musicName'] ? $dataSearchVideo['videoData']['musicInfos']['musicName'] : '',
            'authorName' => $dataSearchVideo['videoData']['musicInfos']['authorName'] ? $dataSearchVideo['videoData']['musicInfos']['authorName'] : '',
        );

        return $itemVideo;
    }
    public function njt_tk_GetKey($playable)
    {
        if (!$playable) {
            return;
        }
        $ch = curl_init();
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Range: bytes=0-200000',
        ];

        $options = array(
            CURLOPT_URL => $playable,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'okhttp',
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
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $tmp = explode("vid:", $data);
        if (count($tmp) > 1) {
            $key = substr($tmp[1], 0, 32);
        } else {
            $key = "";
        }
        return $key;
    }

    public function njt_tk_jsonDecode($url = null, $args = array())
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
            CURLOPT_ENCODING       => "utf-8",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_MAXREDIRS      => 10,
        );
        curl_setopt_array( $ch, $options );
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
       	
        return json_decode($data, true);
    }

    public function njt_tk_formatNumber($n)
    {
        if (!$n) {
            return 0;
        }
        if ($n < 1e3) {
            return $n;
        }

        if ($n >= 1e3 && $n < 1e6) {
            return number_format(($n / 1e3), 1, '.', "") . "K";
        }

        if ($n >= 1e6 && $n < 1e9) {
            return number_format(($n / 1e6), 1, '.', "") . "M";
        }

        if ($n >= 1e9 && $n < 1e12) {
            return number_format(($n / 1e9), 1, '.', "") . "B";
        }

        if (n >= 1e12) {
            return number_format(($n / 1e12), 1, '.', "") . "T";
        }

    }
    public function formatCreatedTime($timestamp)
    {
        $SECOND = 1;
        $MINUTE = 60;
        $HOUR = 60 * 60;
        $DAY = 60 * 60 * 24;
        $MONTH = 60 * 60 * 24 * 30;
        $YEAR = 60 * 60 * 24 * 30 * 12;

        $today = getdate();
        $elapsed = (($today[0]) - $timestamp);

        if ($elapsed <= $MINUTE) {
            return round($elapsed / $SECOND) . ' Second ago';
        }

        if ($elapsed <= $HOUR) {
            return round($elapsed / $MINUTE) . ' Minute ago';
        }

        if ($elapsed <= $DAY) {
            return round($elapsed / $HOUR) . ' Hour ago';
        }

        if ($elapsed <= $MONTH) {
            return round($elapsed / $DAY) . ' Day ago';
        }

        if ($elapsed <= $YEAR) {
            return round($elapsed / $MONTH) . ' Month ago';
        }

        return round($elapsed / $YEAR) . ' Year ago';
    }
}
