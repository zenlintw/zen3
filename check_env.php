<?php
header("HTTP/1.1 404 Not Found");  
header("Status: 404 Not Found");  
exit;
	$check_results = array();
	define('OK', '<span style="color: green;">OK</span>');
	define('UNDETECTABLE', '<span style="color: red; font-weight: bold">無法偵測</span>');
	function flunk($msg='沒找到')
	{
		return '<span style="color: red; font-weight: bold">' . $msg . '</span>';
	}
	function inadequate($msg)
	{
		return '<span style="color: orange;">' . $msg . '</span>';
	}


	// apache 版本
	if (function_exists('apache_get_version'))
	{
		preg_match('!Apache.([0-9.]+)!i', apache_get_version(), $regs);
		$apache_version = $regs[1];

		if( (version_compare('2.0.0', $apache_version) > 0 && version_compare('1.3.36', $apache_version) <= 0) /*||
            (version_compare('2.2.0', $apache_version) > 0 && version_compare('2.0.59', $apache_version) <= 0) ||
            (version_compare('2.3.0', $apache_version) > 0 && version_compare('2.2.4',  $apache_version) <= 0) */
		  )
			$check_results['apache_version'] = OK;
		else
			$check_results['apache_version'] = flunk($regs[1]);
	}
	else
		$check_results['apache_version'] = UNDETECTABLE;


	// apache 模組
	ob_start();
	phpinfo(INFO_MODULES);
	$text = ob_get_contents();
	ob_end_clean();
	if (preg_match('!^<tr>.*\bLoaded Modules\b.*</tr>$!mU', $text, $regs))
	{
		$apache_mods = split('[, ]', substr(strip_tags($regs[0]), 15, -1));
		$check_results['apache_mod_dav']		= in_array('mod_dav', $apache_mods)		 ? OK : flunk();
		$check_results['apache_mod_encoding']	= in_array('mod_encoding', $apache_mods) ? OK : flunk();
		$check_results['apache_mod_headers']	= in_array('mod_headers', $apache_mods)	 ? OK : flunk();
		$check_results['apache_mod_expires']	= in_array('mod_expires', $apache_mods)	 ? OK : flunk();
	}
	else
	{
		$check_results['apache_mod_dav']		= UNDETECTABLE;
		$check_results['apache_mod_encoding']	= UNDETECTABLE;
		$check_results['apache_mod_headers']	= UNDETECTABLE;
		$check_results['apache_mod_expires']	= UNDETECTABLE;
	}

	if (preg_match('!^<tr>.*\bServer Root\b.*</tr>$!mU', $text, $regs))
	{
        $server_root = trim(substr(strip_tags($regs[0]), 12, -1));
        if (is_dir($server_root . '/var') && is_writable($server_root . '/var'))
            $check_results['web_dav_db_dir'] = OK;
        else
            $check_results['web_dav_db_dir'] = '<span style="color: rad">目錄不存在或無法寫入</span>';
    }
    else
        $check_results['web_dav_db_dir']	= UNDETECTABLE;

	if (preg_match('!^<tr>.*\bUser/Group\b.*</tr>$!mU', $text, $regs) &&
        preg_match('!^.{11}(\w+)!', strip_tags($regs[0]), $regs1)
       )
        $apache_runner = $regs1[1];
    else
        $apache_runner = UNDETECTABLE;


	// libxml2
	if (preg_match('!\blibxml Version[^0-9]*([0-9.]+)!', $text, $regs))
		$check_results['php_mod_libxml2'] = (20614 <= intval($regs[1]) || version_compare('2.6.14', $regs[1]) <= 0) ? OK : flunk($regs[1]);
	else
		$check_results['php_mod_libxml2'] = UNDETECTABLE;

	// libxslt
	if (preg_match('!\blibxslt Version[^0-9]*([0-9.]+)!', $text, $regs))
		$check_results['php_mod_libxslt'] = version_compare('1.1.11', $regs[1]) <= 0 ? OK : flunk($regs[1]);
	else
		$check_results['php_mod_libxslt'] = UNDETECTABLE;

	// php 版本
	$check_results['php_version'] = (version_compare('5.0.0', PHP_VERSION) > 0 && version_compare('4.4.1', PHP_VERSION) <= 0) ? OK : flunk(PHP_VERSION);
	$php_version = PHP_VERSION;

	// iconv
	if (version_compare('1.9.2', ICONV_VERSION) <= 0)
		$check_results['php_mod_iconv'] = OK;
	elseif (version_compare('1.9', ICONV_VERSION) <= 0)
		$check_results['php_mod_iconv'] = inadequate(ICONV_VERSION);
	else
		$check_results['php_mod_iconv'] = flunk(ICONV_VERSION);

	// Zend 版本
	ob_start();
	phpinfo(INFO_GENERAL);
	$text = ob_get_contents();
	ob_end_clean();
	$zend_ver = 'unknown';
	if (preg_match('!\bZend( |&nbsp;)Optimizer( |&nbsp;)v([0-9.]+)!i', $text, $regs))
	{
		$check_results['php_mod_zend'] = version_compare('2.6.2', $regs[3]) <= 0 ? OK : flunk($regs[3]);
		$zend_ver = $regs[3];
	}
	else
		$check_results['php_mod_zend'] = UNDETECTABLE;

	// 其它 PHP modules
	$loaded_extensions = get_loaded_extensions();
	$check_results['php_mod_shmop']			= in_array('shmop', $loaded_extensions)		 ? OK : flunk();
	$check_results['php_mod_mime_magic']	= in_array('mime_magic', $loaded_extensions) ? OK : flunk();
	$check_results['php_mod_mcrypt']		= in_array('mcrypt', $loaded_extensions)	 ? OK : flunk();
	$check_results['php_mod_mbstring']		= in_array('mbstring', $loaded_extensions)	 ? OK : flunk();
	$check_results['php_mod_xml']			= in_array('xml', $loaded_extensions)		 ? OK : flunk();
	$check_results['php_mod_zlib']			= in_array('zlib', $loaded_extensions)		 ? OK : flunk();

	// MySQL
	if (in_array('mysql', $loaded_extensions) && ($ident = mysql_connect(':/tmp/mysql.sock','wm3','WmIiI')))
	{
		list($mysql_version) = explode('-', mysql_get_server_info($ident));
		mysql_close($ident);
		if (version_compare('4.1.0', $mysql_version) > 0 && version_compare('4.0.26', $mysql_version) <= 0)
			$check_results['php_mod_mysql'] = OK;
		elseif (version_compare('5.1.0', $mysql_version) > 0 && version_compare('5.0.0', $mysql_version) <= 0)
			$check_results['php_mod_mysql'] = OK;
		else
			$check_results['php_mod_mysql'] = flunk($mysql_version);
	}
	else
		$check_results['php_mod_mysql'] = flunk();

	// GD
	if (in_array('gd', $loaded_extensions))
	{
		$gd_info = gd_info();
		preg_match('!\b\d+(\.\d+){2}\b!', $gd_info['GD Version'], $regs);
		$gd_ver = $regs[0];
		$check_results['php_mod_gd']			= version_compare('2.0.28', $gd_ver) <= 0 ? OK : flunk($regs[0]);
		$check_results['php_mod_gd_freetype']	= $gd_info['FreeType Support']	 ? OK : flunk();
		$check_results['php_mod_gd_gif-r']		= $gd_info['GIF Read Support']	 ? OK : flunk();
		$check_results['php_mod_gd_gif-c']		= $gd_info['GIF Create Support'] ? OK : flunk();
		$check_results['php_mod_gd_jpg']		= $gd_info['JPG Support']		 ? OK : flunk();
		$check_results['php_mod_gd_png']		= $gd_info['PNG Support']		 ? OK : flunk();
	}
	else
		$check_results['php_mod_gd'] = flunk();


	// 檢查 WM pro 必要外部檔案
	$exs = array('chmod'	=> '</td><td style="color: red">not found</td></tr>',
				 'cp'		=> '</td><td style="color: red">not found</td></tr>',
				 'du'       => '</td><td style="color: red">not found</td></tr>',
				 'find'     => '</td><td style="color: red">not found</td></tr>',
				 'gzip'		=> '</td><td style="color: red">not found</td></tr>',
				 'head'     => '</td><td style="color: red">not found</td></tr>',
				 'mkdir'    => '</td><td style="color: red">not found</td></tr>',
				 'mv'       => '</td><td style="color: red">not found</td></tr>',
				 'mysql'    => '</td><td style="color: red">not found</td></tr>',
				 'mysqldump'=> '</td><td style="color: red">not found</td></tr>',
				 'paste'    => '</td><td style="color: red">not found</td></tr>',
				 'rm'       => '</td><td style="color: red">not found</td></tr>',
				 'sed'      => '</td><td style="color: red">not found</td></tr>',
				 'tar'      => '</td><td style="color: red">not found</td></tr>',
				 'unzip'    => '</td><td style="color: red">not found</td></tr>',
				 'xargs'    => '</td><td style="color: red">not found</td></tr>',
				 'zip'      => '</td><td style="color: red">not found</td></tr>');
	exec('PATH=/usr/local/bin:/usr/local/mysql/bin:/home/apps/mysql/bin:/bin:/usr/bin:/sbin:/usr/sbin which ' . implode(' ', array_keys($exs)), $es);
	foreach($es as $e)
	{
	    $i = basename($e);
	    if (isset($exs[$i]))
	    	$exs[$i] = '<td>' . $i . '</td><td colspan="2">' . $e . '</td><td style="color: green">OK</td></tr>';
	}
	foreach($exs as $k => $v)
		if (strpos($v, '</td>') === 0)
			$exs[$k] = '<td colspan="3">' . $k . $v;

	$result = '<tr><td width="70" rowspan="' . count($exs) . '" valign="top">外部執行檔</td>' . implode('<tr>', $exs);


	function getVer($var)
	{
	    return (strpos($var, '@version') !== false);
	}

	// PEAR
	$pear = array('/usr/local/lib/php/PEAR.php'				=> '<td style="color: red">not found</td>',
				  '/usr/local/lib/php/System.php'			=> '<td style="color: red">not found</td>',
				  '/usr/local/lib/php/Console/Getopt.php'	=> '<td style="color: red">not found</td>',
				  '/usr/local/lib/php/Archive/Tar.php'		=> '<td style="color: red">not found</td>');
	foreach($pear as $k => $v)
	{
		if (is_readable($k))
		{
			$v = array_filter(file($k), 'getVer');
			$t = preg_match('/\b(\d+\.\d+)\b/', reset($v), $regs) ? $regs[0] : 'unknown';
			$pear[$k] = '<td style="color: green">OK (' . $t . ')</td>';
		}
	}

echo <<< EOB
<html>

<head>
<meta http-equiv="content-type" content="text/html; charset=big5">
<title>WM system check</title>
</head>

<body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
<table border="1" cellpadding="3" cellspacing="0" align="center" style="border-collapse: collapse; border-color: black" bordercolor="black" style="font-size: 10pt">
	<tr><th colspan="5" style="font-size: 14pt">WM Pro v1.2~1.3 執行環境偵測程式</th></tr>
    <tr style="font-style: italic; font-weight: bold">
        <td width="70">&nbsp;</td>
        <td width="195" colspan="2">必要模組</td>
        <td width="130">確認版本或啟用</td>
        <td width="72">檢查結果</td>
    </tr>
    <tr>
        <td width="70" rowspan="6" valign="top">apache</td>
        <td width="195" colspan="2">{$apache_version}</td>
        <td width="130">2.0.0 &gt; 版本 &gt;= 1.3.36</td>
        <td width="72">{$check_results['apache_version']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mod_dav</td>
        <td width="130">on</td>
        <td width="72">{$check_results['apache_mod_dav']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mod_encoding</td>
        <td width="130">on</td>
        <td width="72">{$check_results['apache_mod_encoding']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mod_headers</td>
        <td width="130">on</td>
        <td width="72">{$check_results['apache_mod_headers']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mod_expires</td>
        <td width="130">on</td>
        <td width="72">{$check_results['apache_mod_expires']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">WebDAV LockDB 目錄<br>  ({$server_root}/var)</td>
        <td width="130">存在且 {$apache_runner} 可寫入</td>
        <td width="72">{$check_results['web_dav_db_dir']}</td>
    </tr>
    <tr>
        <td width="70" rowspan="17" valign="top">PHP</td>
        <td width="195" colspan="2">{$php_version}</td>
        <td width="130">5.0.0 &gt; 版本 &gt;= 4.4.1</td>
        <td width="72">{$check_results['php_version']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">libxml2</td>
        <td width="130">版本 &gt;= 2.6.14</td>
        <td width="72">{$check_results['php_mod_libxml2']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">libxslt</td>
        <td width="130">版本 &gt;= 1.1.11</td>
        <td width="72">{$check_results['php_mod_libxslt']}</td>
    </tr>
    <tr>
        <td width="33" rowspan="6" valign="top">gd</td>
        <td width="156">{$gd_ver}</td>
        <td width="130">版本 &gt;= 2.0.28</td>
        <td width="72">{$check_results['php_mod_gd']}</td>
    </tr>
    <tr>
        <td width="156">FreeType Support</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_gd_freetype']}</td>
    </tr>
    <tr>
        <td width="156">GIF Read Support</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_gd_gif-r']}</td>
    </tr>
    <tr>
        <td width="156">GIF Create Support</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_gd_gif-c']}</td>
    </tr>
    <tr>
        <td width="156">JPG Support</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_gd_jpg']}</td>
    </tr>
    <tr>
        <td width="156">PNG Support</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_gd_png']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">iconv</td>
        <td width="130">版本 &gt;= 1.9.2</td>
        <td width="72">{$check_results['php_mod_iconv']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mbstring</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_mbstring']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mcrypt</td>
        <td width="130">版本 &gt;= 2.5.7</td>
        <td width="72">{$check_results['php_mod_mcrypt']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">mime_magic</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_mime_magic']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">shmop</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_shmop']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">xml</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_xml']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">zlib</td>
        <td width="130">on</td>
        <td width="72">{$check_results['php_mod_zlib']}</td>
    </tr>
    <tr>
        <td width="195" colspan="2">Zend Optimizer ({$zend_ver})</td>
        <td width="130">
            <p>版本 &gt;= 2.6.2</p>
        </td>
        <td width="72">{$check_results['php_mod_zend']}</td>
    </tr>
    <tr>
        <td width="70">MySQL</td>
        <td width="195" colspan="2">{$mysql_version}</td>
        <td width="130">4.1.0 &gt; 版本 &gt;= 4.0.26 或<br>5.1.0 &gt; 版本 &gt;= 5.0.0</td>
        <td width="72">{$check_results['php_mod_mysql']}</td>
    </tr>
	$result
	<tr>
		<td width="70" rowspan="4" valign="top">PEAR</td>
		<td colspan="3">/usr/local/lib/php/PEAR.php</td>
		{$pear['/usr/local/lib/php/PEAR.php']}
	</tr>
	<tr>
		<td colspan="3">/usr/local/lib/php/System.php</td>
		{$pear['/usr/local/lib/php/System.php']}
	</tr>
	<tr>
		<td colspan="3">/usr/local/lib/php/Console/Getopt.php</td>
		{$pear['/usr/local/lib/php/Console/Getopt.php']}
	</tr>
	<tr>
		<td colspan="3">/usr/local/lib/php/Archive/Tar.php</td>
		{$pear['/usr/local/lib/php/Archive/Tar.php']}
	</tr>
</table>
</body>
</html>
EOB;

?>
