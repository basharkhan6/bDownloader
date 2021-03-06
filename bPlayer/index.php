<?php
  $url = "https://www.w3schools.com/html/mov_bbb.mp4";
  if(isset($_GET["url"]) && !empty($_GET["url"])) {
    $url = urldecode($_GET["url"]);
  }
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>bPlayer</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg">
  <div class="bPlayer">
    <video class="video" autoplay loop>
      <source  src="<?php echo $url; ?>">
    </video>
    <input type="range" class="progressRng" min="1" max="100" value="0" step="0.1"></input>
  <span class="current">00:00</span>
  <span class="duration">01:59</span>
    <div class="ctrlArea">
      <button class="playBtn" id="playBtn">▶</button>
      <button data-skip="-10" class="fastBkrd"><<</button>
      <button data-skip="20" class="fastFwrd">>></button>
      <input type="range" class="volume" min="0" max="1" step="0.05" value="1">
      <input type="range" class="speed" min="0" max="2" step="0.1" value="1">
      <button class="fullScreen">⛶</button>
    </div>
  </div>

  <a href="../yt">YouTube</a>
  <a href="../fb">Facebook</a>
  <script src="script.js"></script>
</body>
</html>
