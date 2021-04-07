<?php
  namespace Facebook;

  class FacebookDownloader {
    protected $client;
    protected $error;

    function __construct() {
      $this->client = new Browser();
    }

    public function getLastError() {
      return $this->error;
    }

    public function getPageHtml($url) {
      // $video_id = $this->extractVideoId($url);
      // return $this->client->get("https://www.youtube.com/watch?v={$video_id}");
      return $this->client->get($url);
    }


    public function getVideoInfo($url=false, $page_html=false) {
      $this->error = null;
      if($page_html == false) {
        $page_html = $this->getPageHtml($url);
      }
      $info = array('title' => $this->extractLink($page_html, 'title'),
                    'image' => $this->extractLink($page_html, 'image'));
      return $info;
    }


    public function extractLink($page_html, $obj='video', $quality=false) {
      if($obj == 'video') {
        if($quality == 'HD') {
          $startStr = 'hd_src:"';
        }
        else{
          $startStr = 'sd_src:"';
        }
      }
      elseif($obj == 'title') {
        $startStr = 'meta property="og:title" content="';
      }
      elseif($obj == 'image') {
        $startStr = 'meta property="og:image" content="';
      }


      $endStr = '"';
      $startPos = strpos($page_html, $startStr);
      $startPos += strlen($startStr);
      $endPos = strpos($page_html, $endStr, $startPos);
      $length = $endPos - $startPos;
      $url = substr($page_html, $startPos, $length);

      return html_entity_decode($url);
    }



    public function getDownloadLinks($url=false, $page_html=false) {
      $this->error = null;
      if($page_html == false) {
        $page_html = $this->getPageHtml($url);
      }

      $result = array();
      $i = 1;
      $hd = strpos($page_html, 'hd_src:"');
      if($hd != false) {    //HD Available
        $i = 2;
      }
      while($i != 0) {
        $quality = ($i==2) ? 'HD' : 'SD';
        $result[] = array('quality' => $quality,
                          'url' => $this->extractLink($page_html, 'video', $quality));
        $i--;
      }

      return $result;
    }

  }
?>
