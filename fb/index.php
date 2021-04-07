<?php
  include 'Browser.php';
  include 'FacebookDownloader.php';

  use Facebook\FacebookDownloader as Downloader;


  $video = false;
  if(isset($_GET["url"]) && !empty($_GET["url"])) {
    $url = $_GET["url"];
    // $url = "https://web.facebook.com/tseriesmusic/videos/1094469510903034/";
    $fb = new Downloader();
    $page_html = $fb->getPageHtml($url);

    $info = $fb->getVideoInfo($url, $page_html);
    // var_dump($info);
    $video = true;
  }
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Facebook Video Downloader</title>
</head>
<body>
  <h1>Facebook Video Downloader</h1>
  <h3>Please Enter Video URL</h3>
  <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
    <input type="text" name="url" placeholder="https://web.facebook.com/tseriesmusic/videos/1094469510903034/">
    <input type="submit" name="submit" value="Submit">
  </form>
  <a href="../yt">Download Youtube Video</a>
  <br>
  <br>

  <?php
    if($video) {
      echo "<img src='". $info['image'] ."'><br>". $info['title'] ;

      $links = $fb->getDownloadLinks($url, $page_html);
      // var_dump($links);
      foreach ($links as $link) {
        echo "<a href=../bPlayer?url=". urlencode($link['url']) ."><h2>". $link['quality'] ."</h2></a><br>";
        // echo "<a href='". $link['url'] ."'><h2>". $link['quality'] ."</h2></a><br>";
      }
    }
  ?>
</body>
</html>
