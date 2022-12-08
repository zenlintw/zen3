<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/xmlapi/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/xmlapi/lib/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/xmlapi/lib/encryption.php');

$word = trim($_GET['word']);

$encryptWord = encryptImmediately($word);

echo 'encrypt is ' . $encryptWord . '<br>';

$decryptWord = decryptImmediately($encryptWord);

echo '<br>decrypt again is ' . $decryptWord;