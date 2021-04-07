<?php
  include 'Browser.php';
  include 'Parser.php';
  include 'SignatureDecoder.php';
  include 'YouTubeDownloader.php';

  use YouTube\YouTubeDownloader as Downloader;


  $video = false;
  if(isset($_GET["url"]) && !empty($_GET["url"])) {
    $url = $_GET["url"];
    // $url = "https://www.youtube.com/watch?v=YSuHrTfcikU";
    // $url = "https://www.youtube.com/watch?v=HkjaFXNEcvE";
    $api_key = "AIzaSyBpxMBZiXE4ZRSIG3YZ5HFresRiTA_gs_Q";
    $thumbnail_quality = "default";   //default, medium, high, standard, maxres

    $yt = new Downloader();

    if(isset($_GET["player"]) && !empty($_GET["player"])) {
      $links = $yt->getDownloadLinks($url);
      $firstLink = $links[0]["url"];
      // echo $firstLink;
      header('Location: ../bPlayer?url='.urlencode($firstLink));
      exit();
    }

    $data = $yt->getVideoInfo($url, $api_key);
    $value = json_decode(json_encode($data), true);
    $title = $value['items'][0]['snippet']['title'];
    $description = $value['items'][0]['snippet']['description'];
    $thumbnail = $value['items'][0]['snippet']['thumbnails'][$thumbnail_quality]['url'];
    // var_dump($title);
    // var_dump($description);
    // var_dump($thumbnail);
    // var_dump($links);

    $video = true;

  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>YouTube Video Downloader</title>
</head>
<body>
  <h1>YouTube Video Downloader</h1>
  <h3>Please Enter Video URL</h3>
  <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="GET">
    <input type="text" name="url" placeholder="https://www.youtube.com/watch?v=HkjaFXNEcvE">
    <input type="submit" name="submit" value="Submit">
  </form>
  <a href="s.php">Search Videos</a><br>
  <a href="../fb">Download Facebook Video</a>
  <br>
  <br>
  <?php
    if($video) {
      echo "<img src='". $thumbnail. "'><br>";
      echo $title. "<br><br>";
      $links = $yt->getDownloadLinks($url);
      foreach ($links as $link) {
        echo "<a href=../bPlayer?url=".urlencode($link["url"]).">". $link["format"] ."</a><br>";
        // echo "<a href='".$link["url"]."'>". $link["format"] ."</a><br>";
      }
    }
  ?>
</body>
</html>
