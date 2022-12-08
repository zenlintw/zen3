<?php

/**
*  filepath : /academic/wm3update/lib.php
*  desc: library for wm3 online update.
*  author: jeff
*  date: 2005-06-22
*/

//將檔案md5
function md5_of_file($file) {
   if(!file_exists($file))
       return '';
   else {
       $filecontent = implode("", file($file));
       return md5($filecontent);
   }
}

/*
*	getDirctoryArchitecture : 取得目錄的檔案架構
*	@access public class
*	@return array : file info array
*/

function getDirctoryArchitecture($dir)
{
	$rtnArray = array();
	if (is_dir($dir)) {
	    if ($dh = opendir($dir))
	    {
	        while (($file = readdir($dh)) !== false) {
	           if (substr($file,0,1) == '.') continue;
	           if (is_dir($dir."/".$file))
	           {
	           		$rtnArray[] = array('D',"{$dir}/{$file}");
	           		$arr = getDirctoryArchitecture($dir."/".$file);
	           		if (count($arr)>0)
	           		{
	           			$rtnArray = array_merge($rtnArray, $arr);
	           		}
	           }else{
	           		$rtnArray[] = array('F',"{$dir}/{$file}");
	           }
	        }
	    closedir($dh);
	    }
	}else{
		$rtnArray[] = array('D',"{$dir}");
	}
	return $rtnArray;
}

class WM3Update
{
	/*
	*	getMatchTgzFileList : 取得 base/10001/school目錄中，符合規則的tgz files
	*	@access public class
	*	@return array : file info array
	*/
	function getMatchTgzFileList()
	{
		global $sysSession, $_SERVER;
		
		$dir = WM3Update::getTgzDirRealpath();
		if (!file_exists($dir)) die($dir ." is not found.");
		if (!is_dir($dir)) die($dir . " is not directory.");
    	if ($dh = opendir($dir)) {
    		$rtnArray = array();
        	while (($file = readdir($dh)) !== false)
        	{
        		if (preg_match('/^(Upgrade|Patch|Custom|FIX)_.*\.tgz$/',$file))
        		{
        			$rtnArray[] = array($file,filesize($dir.$file),filemtime($dir.$file));
        		}
        	}
    		closedir($dh);
    	}else{
    		die("fail to opendir : ". $dir);
		}
		return $rtnArray;
	}
	
	/*
	*	getTgzDirRealpath : 取得安裝檔所放置目錄的實體路徑
	*	@access public class
	*	@return string
	*/
	function getTgzDirRealpath()
	{
		global $_SERVER;
		return sysDocumentRoot.'/base/10001/door/';
	}

	/*
	*	isLockedFileExists : 線上更新lock是否
	*	@access public class
	*	@return bloor
	*/
	function isLockedFileExists()
	{
		global $_SERVER;
		return file_exists(sysDocumentRoot . '/base/wm3update/wm3update.lock');
	}

}

class WM3UpdateSession
{
	var $tgzfile;
	var $update_root_dir;
	var $untar_dir;
	var $backup_dir;
	var $lockfile;
	var $update_id;
	
	function WM3UpdateSession($id=null)
	{
		global $_SERVER;

		$this->update_root_dir = sysDocumentRoot . '/base/wm3update';

        //避免crontab的執行權限建立的目錄，造成apache的runner身份：elearn無法寫入檔案，所以
        //以$id為cronUpdate都不建立目錄
        if ($id != "cronUpdate") {
            if (!file_exists($this->update_root_dir)) mkdir($this->update_root_dir,0755);
            if (!file_exists($this->update_root_dir."/Progs_Backup"))
                mkdir($this->update_root_dir."/Progs_Backup",0755);

            // 新增更新指令目錄
            if (!file_exists($this->update_root_dir."/InstructionDir")){
                mkdir($this->update_root_dir."/InstructionDir",0755);
            }
        }

        $this->lockfile = $this->update_root_dir . "/wm3update.lock";
		if (($id != null)&&($id != "cronUpdate")) {
            $this->buildLockFile($id);
        }

        if ($id != "cronUpdate") {
            $this->update_id = $this->getSystemUpdateId();
            $this->untar_dir = $this->update_root_dir . "/" . $this->update_id;
            $this->backup_dir =  $this->update_root_dir . "/Progs_Backup/" . $this->update_id;
            if (!file_exists($this->untar_dir)) $this->buildUntarDirectory();
        }
	}
	
	function setTagfile($l_tgzfile)
	{
		$this->tgzfile = $l_tgzfile;
	}
	
	function getSystemUpdateId()
	{
		if (!file_exists($this->lockfile)) $this->buildLockFile('error');
		$fp = fopen($this->lockfile, "r");
		$id = fgets($fp,64);
		fclose($fp);
		return $id;
	}
	
	//建立lock file, 記錄update_id
	function buildLockFile($id)
	{
		$fp = fopen($this->lockfile, "w+");
		fputs($fp, $id);
		fclose($fp);
	}
	
	/*
	*	getSystemUpdateId : 取得線上update_id
	*	@access public class
	*	@return bloor
	*/
	function validSystemUpdateId($id)
	{
		return (strcmp($id,$this->update_id) == 0)?true:false;
	}


	//建立 Untar 目錄
	function buildUntarDirectory()
	{
		mkdir($this->untar_dir, 0755);
	}
	
	//進行Untar動作
	function doUntar()
	{
		exec('whereis tar',$tars);
		$ary_tar = explode(' ',$tars[0]);
		for($i=1;$i<count($ary_tar);$i++) {
			if ($ary_tar[$i]=='/bin/tar' || $ary_tar[$i]=='/usr/bin/tar' || $ary_tar[$i]=='/usr/local/bin/tar' || $ary_tar[$i]=='/usr/sbin/tar')
				$tar_type = $ary_tar[$i];
		}
		exec("{$tar_type} -zxf {$this->tgzfile} -C {$this->untar_dir}");	
	}
	
	function addUserInfo($user)
	{
		$fp = fopen($this->untar_dir.'/.user','w');
		fputs($fp,$user);
		fclose($fp);
	}
	
	
	/*
	*	doBackup : 進行程式備份工作
	*	@access public class
	*	@return void
	*/
	function doBackup($arr)
	{
		global $_SERVER;
		if (count($arr) == 0) return;
		//確認備份目錄存在
		if (!file_exists($this->backup_dir))
		{
			mkdir($this->backup_dir, 0755);
		}
		
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			list($type, $filepath) = explode("_",$arr[$i],2);
			$src_path = str_replace($this->untar_dir,$_SERVER['DOCUMENT_ROOT'], $filepath);
			$backup_path = str_replace($this->untar_dir,$this->backup_dir, $filepath);
			if ($type == 'D')
			{
				if (!file_exists($backup_path))
					mkdir($backup_path,0755);
			}else{
				if (file_exists($src_path))	copy($src_path, $backup_path);
			}
		}
	}

    /**
     * 建立更新或回復的指令檔
     * @param  string $action 更新或回復，其值為 update 或 rollback
     * @return [type]         [description]
     */
    function createInstructionFile($action='update', $rawfname, $patchFiles=""){
        if (!in_array($action, array("update", "rollback"))) return false;
        if ($action == "update") {
            $insFilename = $this->update_root_dir."/InstructionDir/".uniqid("UPDATE");
            $insContent = sprintf("update\t%s\t%s\t%s\t%s\n", 
                $_SERVER['REMOTE_ADDR'], 
                date('Y-m-d H:i:s'), 
                $this->update_id,
                $rawfname
            );
            if (is_array($patchFiles) && count($patchFiles)){
                for($i=0, $size=count($patchFiles); $i<$size; $i++) {
                    $insContent .= sprintf("%s\n", $patchFiles[$i]);
                }
            }
        }else if ($action == "rollback") {
            $this->update_id = $rawfname;
            $insFilename = $this->update_root_dir."/InstructionDir/".uniqid("ROLLBACK");
            $insContent = sprintf("rollback\t%s\t%s\t%s\n", 
                $_SERVER['REMOTE_ADDR'], 
                date('Y-m-d H:i:s'), 
                $this->update_id
            );
        }
	
		//20191004多主機更新-驗證檔名是否為SERVER_IP(B)		
		if(EnableMulitServer){
				$Mulit_Server = explode(";", MulitServer);
				$SERVERARY=$Mulit_Server;
				foreach($SERVERARY as $key => $val){
					file_put_contents($insFilename."_{$val}", $insContent);
				
					if (!file_exists($insFilename."_{$val}")) {
						return false;
					}
				}
		}else{
				file_put_contents($insFilename."_{$val}", $insContent);
				
				if (!file_exists($insFilename."_{$val}")) {
					return false;
				}
		}

		//20191004多主機更新-驗證檔名是否為SERVER_IP(E)
        return true;
    }

    function doUpdateInstruction($id, $insFilename){
        $this->update_id = $id;
        $this->untar_dir = $this->update_root_dir . "/" . $this->update_id;
        $this->backup_dir =  $this->update_root_dir . "/Progs_Backup/" . $this->update_id;
        $lines = file($insFilename);

        if (is_array($lines) && count($lines)){
            $files = array();
            for($i=1, $size=count($lines); $i<$size; $i++) {
                $files[] = trim($lines[$i]);
            }
            $proc = $this->doUpdate($files);
            if (empty($proc)) {
                return false;
            }
            return true;
        }

        return false;
    }

    function doRollBackInstruction($id, $insFilename){
        $this->update_id = $id;
        $o_rollback = new WM3Rollback($id);
        $o_rollback->doRollback();
        // 將此更新patch設為Rollback
        $o_log = new WM3UpdateLog();
        $o_log->setRollBackStatus($id);
        return true;
    }

    function hasInstruction(){
        if (!file_exists($this->update_root_dir)) {
            return 0;
        }

        $dir = $this->update_root_dir."/InstructionDir";
        if (!file_exists($dir)) {
            return 0;
        }

        $insCount = 0;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (($file == ".") || ($file == "..")) continue;
                    $insFilename = $dir."/".$file;
                    $lines = file($insFilename);
                    if (is_array($lines) && count($lines)){
                        list($action, $addr, $insDate, $id, $rawfname) = explode("\t", trim($lines[0]));
                        if ($action == "update") {
                            $insCount++;
                        }else if ($action == "rollback"){
                            $insCount++;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $insCount;
    }

    // 進行線上更新的指令
    function doInstruction(){
        $dir = $this->update_root_dir."/InstructionDir";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (($file == ".") || ($file == "..")) continue;
                    $insFilename = $dir."/".$file;
					
					
					//20191004多主機更新-驗證檔名是否為SERVER_IP(B)
					if(EnableMulitServer){
						exec('/sbin/ifconfig | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | head -n 1',$rtn);
						$continue_bk = true;
						if(is_array($rtn)){
							foreach($rtn as $key => $val){
								if(strpos($file,"_{$val}") === false){
									$continue_bk = false;
									continue;
								}
							}
						}		
						if($continue_bk === false){
							continue;
						}
					}
					//20191004多主機更新-驗證檔名是否為SERVER_IP(E)
					
                    $lines = file($insFilename);
                    if (is_array($lines) && count($lines)){
                        list($action, $addr, $insDate, $id, $rawfname) = explode("\t", trim($lines[0]));
                        if ($action == "update") {
                            list($action, $addr, $insDate, $id, $rawfname) = explode("\t", trim($lines[0]));
                            if ($this->doUpdateInstruction($id, $insFilename)){
                                $log = new WM3UpdateLog();
                                $log->AppendLog($rawfname, $id, $addr);
                                echo "update success\n";
                            }else{
                                echo "update fail:\n";
                            }
                            unlink($insFilename);
                            $this->removeLockFile();
                        }else if ($action == "rollback"){
                            $this->doRollBackInstruction($id, $insFilename);
                            unlink($insFilename);
                            $this->removeLockFile();
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
	
	/*
	*	doUpdate : 進行更新程式工作
	*	@access public class
	*	@return void
	*/
	function doUpdate($arr)
	{
		if (count($arr) == 0) return;
		$rtns = '';
		$uid = fileowner(sysDocumentRoot);
		$gid = filegroup(sysDocumentRoot);
		for($i=0, $j=1,$size=count($arr); $i<$size; $i++)
		{
			list($type, $filepath) = explode("_",$arr[$i],2);
			$src_path = $filepath;
			if (basename($src_path) == 'README') continue;
			$target_path = str_replace($this->untar_dir,sysDocumentRoot, $filepath);
			if ($type == 'D')
			{
				if (!file_exists($target_path))
				{	
					mkdir($target_path,0755);
					chown($target_path,$uid);
					chgrp($target_path,$gid);
					
					$rtns .= ($j++)." mkdir({$target_path},0755)<br>\r\n";
				}
			}else{
				if (file_exists($src_path))
				{
					copy($src_path, $target_path);
					chown($target_path,$uid);
					chgrp($target_path,$gid);
					$rtns .= ($j++)." copy {$src_path} {$target_path}<br>\r\n";
				}
			}
		}
		return $rtns;
	}
	
	/*
	*	removeLockFile : 移除LockFile
	*	@access public class
	*	@return void
	*/
	function removeLockFile()
	{
		unlink($this->lockfile);
	}
}

class WM3UpdateLog
{
	var $update_root_dir;
	var $logDir;
	var $logFile;
	
	function WM3UpdateLog()
	{
		$this->init();
	}
	
	function init()
	{
		global $_SERVER;
		$this->update_root_dir = sysDocumentRoot . '/base/wm3update';
		if (!file_exists($this->update_root_dir)) mkdir($this->update_root_dir,0755);
		$this->logDir = $this->update_root_dir . "/LogDir";
		if (!file_exists($this->logDir)) mkdir($this->logDir,0755);
		$this->logFile = $this->logDir ."/WM3Fix.log";
		if (!file_exists($this->logFile)) touch($this->logFile);
	}
	
	function AppendLog($fname, $id, $ip)
	{
		$str = sprintf("%s\t%s\t%s\t%s\t%s\r\n", date("Y-m-d H:i:s"), "U", $id, $fname, $ip);
		$fp = fopen($this->logFile, "a");
		fputs($fp, $str);
		fclose($fp);
	}
	
	function getLogList()
	{
		$lines = file($this->logFile);
		if (count($lines) == 0) return array();
		
		$rtnArray = array();
		for($i=0,$size=count($lines); $i<$size; $i++)
		{
			$rtnArray[] = explode("\t",$lines[$i]);
		}
		return $rtnArray;
	}
	
	function setRollBackStatus($id)
	{
		$arr = $this->getLogList();
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			if ($arr[$i][2] == $id && $arr[$i][1] == 'U')
			{
				$arr[$i][1] = 'R';
				break;
			}
		}
		
		//重寫log
		$fp = fopen($this->logFile, "w");
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			$str = sprintf("%s\t%s\t%s\t%s\t%s", $arr[$i][0],$arr[$i][1],$arr[$i][2],$arr[$i][3],$arr[$i][4]);
			fputs($fp, $str);
		}
		fclose($fp);
	}
}


class WM3UpdateInfo
{
	var $update_root_dir;
	var $fix_id;
	var $Patch_dir;
	function WM3UpdateInfo($id)
	{
		$this->fix_id = $id;
		$this->init();
	}
	
	function init()
	{
		global $_SERVER;
		$this->update_root_dir = sysDocumentRoot . '/base/wm3update';
		if (!file_exists($this->update_root_dir)) die('Fatal Error: WM3Update directory is not existed.');
		
		$this->Patch_dir = $this->update_root_dir . "/" . $this->fix_id;
		if (!file_exists($this->Patch_dir)) die('Error: Original Patch Directory is not exists.');
	}
	
	function getUpdateUserInfo()
	{
		$filename = $this->Patch_dir . '/.user';
		if (!file_exists($filename)) return '';
		return file_get_contents($filename);
	}
	
	/**
	*	show content of README file
	*/
	function getReadmeContent()
	{
		$filename = $this->Patch_dir . '/README';
		if (!file_exists($filename)) return 'README file is not found.';
		return file_get_contents($filename);
	}
	
	function getFilelist()
	{
		$rtnArray = array();
		if (is_dir($this->Patch_dir)) {
			if ($dh = opendir($this->Patch_dir))
			{
				while (($file = readdir($dh)) !== false) {
				if (substr($file,0,1) == '.') continue;
				if (is_dir($this->Patch_dir."/".$file))
				{
						$rtnArray[] = '['."{$this->Patch_dir}/{$file}".']';
						$arr = getDirctoryArchitecture($this->Patch_dir."/".$file);
						if (($size=count($arr))>0)
						{
							for($i=0; $i<$size; $i++)
							{
								$rtnArray[] = $arr[$i][1];
							}
						}
				}else{
						$rtnArray[] = "{$this->Patch_dir}/{$file}";
				}
				}
			closedir($dh);
			}
		}else{
			$rtnArray[] = '['."{$this->Patch_dir}".']';
		}
		return implode("<br>", $rtnArray);
	}
}


class WM3Rollback
{
	var $update_root_dir;
	var $fix_id;
	var $Patch_dir;
	var $Backup_dir;
	var $Rollback_log;
	function WM3Rollback($id)
	{
		$this->fix_id = $id;
		$this->init();
	}
	
	function init()
	{
		global $_SERVER;
		$this->update_root_dir = sysDocumentRoot . '/base/wm3update';
		if (!file_exists($this->update_root_dir)) mkdir($this->update_root_dir,0755);
		
		$this->Patch_dir = $this->update_root_dir . "/" . $this->fix_id;
		if (!file_exists($this->Patch_dir)) die("Error: Original Patch Directory is not exists.");
		
		$this->Backup_dir = $this->update_root_dir . "/Progs_Backup/" . $this->fix_id;
		if (!file_exists($this->Backup_dir)) die("Error: Backup Directory is not exists.");
		
		$this->Rollback_log = $this->update_root_dir . "/LogDir/rb_".$this->fix_id.".log";
		if (!file_exists($this->Rollback_log)) touch($this->Rollback_log);
	}
	
	function doRollback()
	{
		global $_SERVER;
		
		//Patch file的移除
		$this->doPatchRemove();
		
		//備份檔的回復
		$this->doBackupRollback();
	}
	
	
	function doPatchRemove()
	{
		global $_SERVER;
		
		$arr = getDirctoryArchitecture($this->Patch_dir);
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			if ($arr[$i][0] == 'D') continue;
			$filename = str_replace($this->Patch_dir,$_SERVER['DOCUMENT_ROOT'],$arr[$i][1]);
			@unlink($filename);
			$this->appendlog("remove ".$filename);
		}
	}
	
	function doBackupRollback()
	{
		global $_SERVER;
		
		$arr = getDirctoryArchitecture($this->Backup_dir);
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			if ($arr[$i][0] == 'D') continue;
			$filename = str_replace($this->Backup_dir,$_SERVER['DOCUMENT_ROOT'],$arr[$i][1]);
			copy($arr[$i][1], $filename);
			$this->appendlog("copy ".$arr[$i][1]." to ". $filename);
		}
	}
	
	function appendlog($msg)
	{
		$fp = fopen($this->Rollback_log, "a+");
		fputs($fp, $msg."\r\n");
		fclose($fp);
	}
	
	
}

?>
