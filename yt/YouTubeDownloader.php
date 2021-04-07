<?php
  namespace YouTube;

  class YouTubeDownloader {
    protected $client;
    protected $error;

    function __construct() {
      $this->client = new Browser();
    }

    public function getLastError() {
      return $this->error;
    }

    // accepts either raw HTML or url
    // <script src="//s.ytimg.com/yts/jsbin/player-fr_FR-vflHVjlC5/base.js" name="player/base"></script>
    public function getPlayerUrl($video_html) {
      $player_url = null;
      // check what player version that video is using
      if(preg_match('@<script\s*src="([^"]+player[^"]+js)@', $video_html, $matches)) {
        $player_url = $matches[1];
        // relative protocol?
        if (strpos($player_url, '//') === 0) {
          $player_url = 'http://' . substr($player_url, 2);
        } elseif (strpos($player_url, '/') === 0) {
          // relative path?
          $player_url = 'http://www.youtube.com' . $player_url;
        }
      }
      return $player_url;
    }

    public function getPlayerCode($player_url) {
      $contents = $this->client->getCached($player_url);
      return $contents;
    }

    public function extractVideoId($str) {
      if(preg_match('/[a-z0-9_-]{11}/i', $str, $matches)) {
          return $matches[0];
      }
      return false;
    }

    private function selectFirst($links, $selector) {
      $result = array();
      $formats = preg_split('/\s*,\s*/', $selector);
      // has to be in this order
      foreach ($formats as $f) {
        foreach ($links as $l) {
          if (stripos($l['format'], $f) !== false || $f == 'any') {
            $result[] = $l;
          }
        }
      }
      return $result;
    }


    public function getVideoInfo($url, $api_key) {
      $video_id = $this->extractVideoId($url);
      return json_decode($this->client->get("https://www.googleapis.com/youtube/v3/videos?id=" . $video_id . "&key=" . $api_key . "&part=snippet"));
    }

    public function searchVideo($q, $cc, $api_key, $maxResults=5, $type="video", $order="relevance", $pageToken=null) {
      $cc = "&regionCode=".$cc;
      $cc = htmlentities($cc);
      if($pageToken != null) {
        return json_decode($this->client->get("https://www.googleapis.com/youtube/v3/search?part=snippet&q=".$q.$cc."&key=".$api_key."&maxResults=".$maxResults."&type=".$type."&order=".$order."&pageToken=".$pageToken));
      }
      return json_decode($this->client->get("https://www.googleapis.com/youtube/v3/search?part=snippet&q=".$q.$cc."&key=".$api_key."&maxResults=".$maxResults."&type=".$type."&order=".$order));
    }

    public function getPageHtml($url) {
      $video_id = $this->extractVideoId($url);
      return $this->client->get("https://www.youtube.com/watch?v={$video_id}");
    }

    public function getPlayerResponse($page_html) {
      if (preg_match('/player_response":"(.*?)","/', $page_html, $matches)) {
        $match = stripslashes($matches[1]);
        $ret = json_decode($match, true);
        return $ret;
      }
      return null;
    }


    // redirector.googlevideo.com
    //$url = preg_replace('@(\/\/)[^\.]+(\.googlevideo\.com)@', '$1redirector$2', $url);
    public function parsePlayerResponse($player_response, $js_code) {
      $parser = new Parser();
      try {
        $formats = $player_response['streamingData']['formats'];
        $adaptiveFormats = $player_response['streamingData']['adaptiveFormats'];
        if (!is_array($formats)) {
            $formats = array();
        }
        if (!is_array($adaptiveFormats)) {
            $adaptiveFormats = array();
        }
        $formats_combined = array_merge($formats, $adaptiveFormats);
        // final response
        $return = array();
        foreach ($formats_combined as $item) {
          // $cipher = isset($item['cipher']) ? $item['cipher'] : '';
          $cipher = isset($item['cipher']) ? $item['cipher'] : (isset($item['signatureCipher']) ? $item['signatureCipher'] : '');
          // return $cipher;

            $itag = $item['itag'];
            // some videos do not need to be decrypted!
            if(isset($item['url'])) {
                $return[] = array(
                    'url' => $item['url'],
                    'itag' => $itag,
                    'format' => $parser->parseItagInfo($itag)
                );
                continue;
            }
            // return $return;

            parse_str($cipher, $result);
            $url = $result['url'];
            // return $url;
            $sp = $result['sp']; // typically 'sig'
            // return $sp;
            $signature = $result['s'];
            // return $signature;

            $decoded_signature = (new SignatureDecoder())->decode($signature, $js_code);
            // return $decoded_signature;

            // redirector.googlevideo.com
            //$url = preg_replace('@(\/\/)[^\.]+(\.googlevideo\.com)@', '$1redirector$2', $url);
            $return[] = array(
                'url' => $url . '&' . $sp . '=' . $decoded_signature,
                'itag' => $itag,
                'format' => $parser->parseItagInfo($itag)
            );
          }
          return $return;
      } catch (\Exception $exception) {
          // do nothing
      } catch (\Throwable $throwable) {
          // do nothing
      }
      return null;
    }

    public function getDownloadLinks($video_id, $selector = false) {
      $this->error = null;
      $page_html = $this->getPageHtml($video_id);
      if(strpos($page_html, 'We have been receiving a large volume of requests') !== false || strpos($page_html, 'systems have detected unusual traffic') !== false) {
        $this->error = 'HTTP 429: Too many requests.';
        return array();
      }
      // return $page_html;

      // get JSON encoded parameters that appear on video pages
      $json = $this->getPlayerResponse($page_html);
      // return $json;
      // get player.js location that holds signature function
      $url = $this->getPlayerUrl($page_html);
      // return $url;

      $js = $this->getPlayerCode($url);
      // return $js;

      $result = $this->parsePlayerResponse($json, $js);
      return $result;
      // if error happens
      if(!is_array($result)) {
        return array();
      }
      // do we want all links or just select few?
      if($selector) {
        return $this->selectFirst($result, $selector);
      }
      return $result;
    }

  }
?>
