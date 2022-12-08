<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
//echo '<pre>';
//var_dump(($_GET['data']));
//echo '</pre>';

$key = 'readlcmsvideolog';
$iv = 'KXyFiQCfgiKcyuVNCGoILQ==';
while (strlen($key) < 16) {
    $key = $key . "\0";
}

//echo '<pre>';
//var_dump(strlen($iv));
//var_dump(strlen(base64_encode(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND))));
//echo '</pre>';
if (strlen($iv) != strlen(base64_encode(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND)))) {
    exit();
}
$iv_base64_decode = base64_decode($iv);

//echo '<pre>';
//var_dump('k6/3mKuoATdH5DaaYtoSCLPerbV9xtrsVlcCkbwN2ixTAEDbXW1SPg1XdUK6/QWzfqNdAyqs+kyBOA+2ml8Wt23tcJpVLKt6xxQVbnVxCR0=');
//var_dump($_GET['data']);
//var_dump(str_replace('!%40%23%24', '+', $_GET['data']));
//var_dump(rawurldecode(str_replace('!%40%23%24', '+', $_GET['data'])));
//echo '</pre>';
$plaintext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(rawurldecode(str_replace('!%40%23%24', '+', $_GET['data']))), MCRYPT_MODE_CBC, $iv_base64_decode);

//echo '<pre>';
//var_dump($plaintext);
//echo '</pre>';

// 僅取{}之前的字串
preg_match('/{.*}/', $plaintext, $matches);
$plaintext = $matches[0];

// json_decode後為物件
$msg = json_decode($plaintext);

// 物件轉陣列
$msg = (array) $msg;

//echo '<pre>';
//var_dump($msg);
//echo '</pre>';

if (count($msg) !== 2) {
    die('parameter error.');
}
?>
<body style="margin:0 ;">
    <iframe width="80%" height="100%" src="<?php echo $msg['l'];?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    <iframe width="19%" height="100%" src="/learn/chat/index_phone.php?r=<?php echo $msg['r'];?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</body>