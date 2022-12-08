<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

$pattern = array(
    '!<tr [^>]+>\s*<td [^>]+>!isU', // 每行前面的 <tr 去掉
    '! *</td>\s*<td [^>]+>!isU', // 欄位間隔換成逗號
    '! *</td>\s*</tr>!isU', // 每行最後的 </tr> 去掉
    '!<a[^>]*title=("[^"]*"|[^ >]+)( [^>]*)?>[^<]*</a>!isU', // 取出各分數標題
    '!<span\b[^>]*>([0-9.]+) %</SPAN><BR><SPAN\b[^>]*>[^<]*</SPAN> *!i', // 把各科比重取出
    '!^.+\n!U', // 去掉第一行的 <tbody>
    '![^\n]+\n[^\n]+\n[^\n]+$!sU', // 去掉最後三行 (高低標及圖表)
    '!^(.*),!smU', // 把第一欄(姓名)加雙引號
    '! "!', // 把重覆的雙引號去掉一個
    '!&nbsp;!'
);

$replace = array(
    '',
    ', ',
    '',
    '\1',
    '\1%',
    '',
    '',
    '"\1",',
    '"',
    ' '
);

header('Content-Disposition: attachment; filename="grades.csv"');
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Transfer-Encoding: binary');
header('Cache-Control:');
header("Pragma:");
header("Expires:");
if (stristr($_SERVER['HTTP_USER_AGENT'], 'ipad') OR stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') OR stristr($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
    header('Content-Type: application/octet-stream; name="grades.csv"');
} else {
    header('Content-Type: application/vnd.ms-excel');
}

if (stristr($_SERVER['HTTP_USER_AGENT'], 'ipad') OR stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') OR stristr($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
    echo strip_tags(preg_replace($pattern, $replace, stripslashes($_POST['table_html'])));
} else {
    // echo utf8_to_excel_unicode(strip_tags(preg_replace($pattern, $replace, stripslashes($_POST['table_html']))));
    echo utf8_to_excel_unicode(strip_tags(preg_replace($pattern, $replace, stripslashes(str_replace('return property','onclick="return property',$_POST['table_html'])))));
}