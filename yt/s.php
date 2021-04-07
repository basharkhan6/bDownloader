<?php
  include 'Browser.php';
  include 'Parser.php';
  include 'SignatureDecoder.php';
  include 'YouTubeDownloader.php';

  use YouTube\YouTubeDownloader as Downloader;

  $videos = $nextPage = $previousPage = null;

  if(isset($_GET["q"]) && !empty($_GET["q"])) {
    $q = $_GET["q"];
    $cc = "bd";   //country code
    $api_key = "AIzaSyBpxMBZiXE4ZRSIG3YZ5HFresRiTA_gs_Q";
    $resPerPage = 5;
    $order = "relevance";   //relevance, viewCount, date, rating, title, videoCount
    $type = "video";    //any, video, movie, show, playlist, channel
    $thumbnail_quality = "default";   //default, medium, high, standard, maxres

    $yt = new Downloader();
    if(isset($_GET["pageToken"])) {
      $pageToken = $_GET["pageToken"];
      $data = $yt->searchVideo(str_replace(" ", "+", $q), $cc, $api_key, $resPerPage, $type, $order, $pageToken);
    } else {
      // $data = $yt->searchVideo(str_replace(" ", "+", $q), $cc, $api_key);
      $data = $yt->searchVideo(str_replace(" ", "+", $q), $cc, $api_key, $resPerPage, $type, $order);
    }
    // var_dump($data);
    $value = json_decode(json_encode($data), true);
    $items = $value["items"];

    if($items != null) {
      if(isset($value["nextPageToken"])) {
        $nextPage = $value["nextPageToken"];
      }
      if(isset($value["prevPageToken"])) {
        $previousPage = $value["prevPageToken"];
      }
      $videos = array();
      foreach ($items as $item) {
        if(empty($item["id"]["videoId"])) {
          continue;
        }
        $videoId = $item["id"]["videoId"];

        $url = "https://www.youtube.com/watch?v=".$videoId;
        $title = $item['snippet']['title'];
        // $description = $item['snippet']['description'];
        $thumbnail = $item['snippet']['thumbnails'][$thumbnail_quality]['url'];

        $videos[] = array(
                    'url' => $url,
                    'title' => $title,
                    'thumbnail' => $thumbnail);
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>YouTube Browser</title>
</head>
<body>
  <h1>YouTube Browser</h1>
  <h3>Please Enter Text</h3>
  <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
    <input type="text" name="q" placeholder="Jhoom ft. Minar">
    <input type="submit" name="submit" value="Search">
  </form>
  <a href="index.php">Download by Link</a><br>
  <a href="../fb">Download Facebook Video</a>
  <br>
  <br>
  <?php
    if($videos != null) {
      // var_dump($videos);
      foreach ($videos as $video) {
        echo "<a href='index.php?player=true&url=".$video["url"]."'><img src='". $video["thumbnail"]. "'><br>".$video["title"]. "</a><br>";
        echo "<a href='index.php?url=".$video["url"]."'>Download</a><br><br>";
      }
      echo "<br><br>";
      if($previousPage != null) {
        echo " <a href='".$_SERVER["PHP_SELF"]."?q=".$q."&pageToken=".$previousPage."'>Previous Page</a>";
      }
      if($nextPage != null) {
        echo " <a href='".$_SERVER["PHP_SELF"]."?q=".$q."&pageToken=".$nextPage."'>Next Page</a>";
      }
    }
  ?>
</body>
</html>
