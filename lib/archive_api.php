<?php
	/**
	 * ====== 解壓縮 API =====
	 * 2004.04.22 by Wiseguy Liang
	 * $id$
	 */

if (!function_exists('file_put_contents'))
{
	function file_put_contents($filename, $data, $flag=NULL)
	{
		$mode =  ($flag & FILE_APPEND) ? 'a' : 'w';
		if (($fp = fopen($filename, $mode)) == FALSE) return false;
		return (fwrite($fp, $data) and fclose($fp));
	}
}
if (!class_exists('Archive')) {
	class Archive{
		// MIS#15839-二個 error_log 訊息，取消非常見壓縮檔的支援 by Small 2010/3/31
		var $archivers = array( 'tar'      => NULL,
							    'gzip'     => NULL,
							    'bzip2'    => NULL,
							    'unzip'    => NULL,
							    'unrar'    => '7z'
								/*
							    'unarj'    => NULL,
							    'unace'    => NULL,
							    'lha'      => NULL
								*/
						 	  );
		var $arc_kinds = array( '.tar.gz'	=> array(FALSE, 'tar',   "%s zxf '%s' --overwrite --no-same-permissions --no-same-owner -C '%s'"),
								'.tgz'		=> array(FALSE, 'tar',   "%s zxf '%s' --overwrite --no-same-permissions --no-same-owner -C '%s'"),
							    '.tar.bz2'	=> array(FALSE, 'tar',   "%s jxf '%s' --overwrite --no-same-permissions --no-same-owner -C '%s'"),
							    '.tbz'		=> array(FALSE, 'tar',   "%s jxf '%s' --overwrite --no-same-permissions --no-same-owner -C '%s'"),
							    '.tar.Z'    => array(FALSE, 'tar',   "%s zxf '%s' --overwrite --no-same-permissions --no-same-owner -C '%s'"),
							    '.zip'      => array(FALSE, '7z', "%s -y x '%s' -o'%s'"),
							    '.rar'      => array(FALSE, '7z', "%s x '%s' -o'%s'")	//MIS#20188 by Small 2011/2/18
								/*
							    '.arj'      => array(FALSE, 'unarj', "cd %s && %s x q '%s'"),
							    '.ace'      => array(FALSE, 'unace', "cd %s && %s x -y '%s'"),
							    '.lzh'      => array(FALSE, 'lha',   "%s -xfw='%s' '%s'")
								*/
							  );
	
		/**
		 * 建構子
		 */
		function Archive(){
			foreach($this->archivers as $k => $v){
				//MIS#20188 by Small 2011/2/18
				$k = ($k=='unrar')? '7z' : $k;
				$program = exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which $k'");
				if (ereg("/$k$", $program)) $this->archivers[$k] = $program;
			}
			if ($this->archivers['tar'] && $this->archivers['gzip'])     { $this->arc_kinds['.tar.gz'][0]  = TRUE; $this->arc_kinds['.tgz'][0]  = TRUE; $this->arc_kinds['.tar.Z'][0] = TRUE; }
			if ($this->archivers['tar'] && $this->archivers['bzip2'])    { $this->arc_kinds['.tar.bz2'][0] = TRUE; $this->arc_kinds['.tbz'][0] = TRUE; }
			if ($this->archivers['unzip']) $this->arc_kinds['.zip'][0] = TRUE;
			if ($this->archivers['unrar']) $this->arc_kinds['.rar'][0] = TRUE;
			/*
			if ($this->archivers['unarj']) $this->arc_kinds['.arj'][0] = TRUE;
			if ($this->archivers['unace']) $this->arc_kinds['.ace'][0] = TRUE;
			if ($this->archivers['lha'])   $this->arc_kinds['.lzh'][0] = TRUE;
			*/
		}
	
		/**
		 * 解壓
		 * $arcfile 壓縮檔全路徑檔名
		 * $extract_path 要解壓的目的全路徑目錄 (必須存在)
		 * $type 壓縮類型(具句點附加檔名)，如果本參數省略則會由壓縮檔名取得。
		 */
		function extract_it($arcfile, $extract_path, $type=FALSE){
			if (is_file($arcfile) && is_readable($arcfile) && is_dir($extract_path) && is_writable($extract_path)){
				set_time_limit(0);
				$ext = ($type === FALSE) ? strrchr($arcfile, '.') : $type;
				if ($ext !== FALSE){
						$z = $this->arc_kinds[$ext][1];
						if ($this->arc_kinds[$ext][0]){
	
							if (in_array($ext, array('.arj', '.ace')))
								exec(sprintf($this->arc_kinds[$ext][2], $extract_path, $this->archivers[$this->arc_kinds[$ext][1]], $arcfile), $a, $ret);
							elseif ($ext == '.lzh')
								exec(sprintf($this->arc_kinds[$ext][2], $this->archivers[$this->arc_kinds[$ext][1]], $extract_path, $arcfile), $a, $ret);
							else {
                                                            // 改用 PclZip 解壓縮
                                                            define('PCLZIP_TEMPORARY_DIR', '/tmp/');
                                                            require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/PclZip/pclzip.lib.php');
                                                            $archive = new PclZip($arcfile);
//                                                            echo '<pre>';
//                                                            var_dump($arcfile, $extract_path);
//                                                            echo '</pre>';
////                                                            $file_list = $archive->extract(PCLZIP_OPT_PATH, $arcfile, PCLZIP_OPT_REMOVE_ALL_PATH);
////                                                            echo '<pre>';
////                                                            var_dump($file_list);
////                                                            echo '</pre>';
                                                            if (!file_exists($extract_path)) {
                                                                mkdir($extract_path, 755, true);
                                                            }
                                                            if ($archive->extract(PCLZIP_OPT_PATH, $extract_path, PCLZIP_OPT_ADD_TEMP_FILE_ON, PCLZIP_OPT_REPLACE_NEWER)) {
                                                            }        
//								exec(sprintf($this->arc_kinds[$ext][2], $this->archivers[$this->arc_kinds[$ext][1]], $arcfile, $extract_path), $a, $ret);
                                                        }
								
							if (PHP_OS != 'WINNT') exec("chmod -R ug+w '$extract_path'");
							return 0;
						}
						else
							return -3; // archivers kind not available
				}
				else
					return -2; // unknown extend filename
			}
			else
				return -1; // file or directory error
		}
	}
}

if (!class_exists('ArchiveBase')) {
	class ArchiveBase
	{
			var $program;	// zip 執行檔全路徑檔名
			var $tmpdir;    // 工作目錄
			var $filename;  // zip 壓縮檔名
	
			function ArchiveBase($filename='test.rar', $files='', $tmp='/tmp', $ext='.zip')
			{
				$this->tmpdir = substr($tmp, -1) == DIRECTORY_SEPARATOR ? $tmp : ($tmp . DIRECTORY_SEPARATOR);
				if (!is_dir($this->tmpdir) || !is_writable($this->tmpdir))
				{
					return -2;
				}
				$this->filename = empty($filename) ? basename(tempnam($this->tmpdir, 'wm_arc_') . $ext) : $filename;
				if ($files) $this->add_files($files);
	
				return true;
			}
			
			function add_string($context, $filename=NULL)
			{
				$filename = is_null($filename) ? tempnam($this->tmpdir, 'wm_arc_') : ($this->tmpdir . $filename);
				file_put_contents($filename, $context);
				if (chdir(dirname($filename)) && $this->add_files(basename($filename)))
				{
					@unlink($filename);
					return true;
				}
				else
					return false;
			}
	
			function readfile()
			{
				@readfile($this->tmpdir . $this->filename);
			}
	
			function rename($filename)
			{
				if (rename($this->tmpdir . $this->filename, $this->tmpdir . $filename))
				{
					$this->filename = $filename;
					return true;
				}
				else
					return false;
			}
	
			function delete()
			{
				if (is_file($this->tmpdir . $this->filename) && is_writable($this->tmpdir . $this->filename))
					@unlink($this->tmpdir . $this->filename);
			}
			
			function ready()
			{
			    return is_readable($this->tmpdir . $this->filename);
			}
	}
}

/**
 * 制作 .zip 壓縮檔
 */
if (!class_exists('ZipArchive_php4')) {
	class ZipArchive_php4 extends ArchiveBase
	{
	   		var $options;   // zip 執行參數
	
			function ZipArchive_php4($filename='test.zip', $files='', $onfly=false, $options='-D -r -q -7', $tmp='/tmp')
			{
				$this->program = trim(exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which zip'"));
				if (strpos($this->program, '/') !== 0)
				{
					return -1;
				}
	
				$this->options = $options;
				if ($onfly && $files)
				{
					if (is_dir($files) && $files != '.' && $files != '..') {
                        passthru("cd $files && {$this->program} {$options} - .");
					}else if (file_exists($files)) {
						passthru("{$this->program} {$options} - $files");
                    }
					return true;
				}
	
				return parent::ArchiveBase($filename, $files, $tmp, '.zip');
			}
	
			function _ZipArchive_php4()
			{
				$this->delete();
			}
	
			function add_files($filename)
			{
				if ($filename)
				{
				    if (is_array($filename)) $filename = implode(' ', $filename);
					exec("{$this->program} {$this->options} {$this->tmpdir}{$this->filename} $filename");
					return true;
				}
				else
					return false;
			}
			
			function add_dir($dir, $tail=1)
			{
			    if (is_dir($dir) && is_readable($dir))
			    {
			        $dirpath = dirname($dir);
			        $dirname = basename($dir);
			        for ($i=$tail; $i>1; $i--)
			        {
						$f = basename($dirpath);
			            $dirpath = dirname($dirpath);
			            $dirname = $f . DIRECTORY_SEPARATOR . $dirname;
					}
			        exec("cd $dirpath && {$this->program} {$this->options} {$this->tmpdir}{$this->filename} $dirname");
					return true;
				}
				else
					return false;
			}
	}
}

/**
 * 制作 .RAR 壓縮檔
 */
if (!class_exists('RarArchive')) {
	class RarArchive extends ArchiveBase
	{
	   		var $options;   // rar 執行參數
	
			function RarArchive($filename='test.rar', $files='', $options='-r -inul -y -m4 -ep1', $tmp='/tmp')
			{
				$this->program = trim(exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which rar'"));
				if (strpos($this->program, '/') !== 0)
				{
					return -1;
				}
	
				$this->options = $options;
				
				return parent::ArchiveBase($filename, $files, $tmp, '.rar');
			}
	
			function _RarArchive()
			{
				$this->delete();
			}
	
			function add_files($filename)
			{
				if ($filename)
				{
				    if (is_array($filename)) $filename = implode(' ', $filename);
					exec("{$this->program} a {$this->options} {$this->tmpdir}{$this->filename} $filename");
					return true;
				}
				else
					return false;
			}
	
			function add_dir($dir, $tail=1)
			{
			    if (is_dir($dir) && is_readable($dir))
			    {
			        $dirpath = dirname($dir);
			        $dirname = basename($dir);
			        for ($i=$tail; $i>1; $i--)
			        {
						$f = basename($dirpath);
			            $dirpath = dirname($dirpath);
			            $dirname = $f . DIRECTORY_SEPARATOR . $dirname;
					}
			        
			        exec("cd $dirpath && {$this->program} a {$this->options} {$this->tmpdir}{$this->filename} $dirname");
					return true;
				}
				else
					return false;
			}
	}
}

?>
