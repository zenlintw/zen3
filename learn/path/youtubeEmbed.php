<?php
    $videoId = $_GET['v'];
    if(empty($videoId))    die('params error');
?>
<!DOCTYPE html>
<html>
<head>
    <title>youtube video</title>
</head>
<body>
    <iframe width="640" height="320" src="https://www.youtube.com/embed/<?php echo $videoId;?>?autoplay=1" frameborder="0" allowfullscreen></iframe>
</body>
</html>
