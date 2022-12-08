<?php
	/*****************************************
	 * 供討論版批次或單一
	 * 複製檔案用
	 * ( 可用於收入筆記本, 收入(移入)精華區 )
	 *****************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/file_api.php');

	// 複製檔案並產生新檔名
	// ( eg: 自討論版複製實體檔案到筆記本資料夾中, 或是討論版複製到精華區 )
	// 參數: $from_path : 原路徑 ( 尾端不含 '/' )
	//	 $to_path   : 至路徑 ( ............ )
	//	 $old_attach: 原夾檔字串 ( '名稱'[TAB]'實體檔名'[TAB]'名稱'[TAB]'實體檔名'[TAB]... )
	//	 $attach    : 新夾檔字串 ( ... ) ( 參照回傳參數 )
	// 傳回值: 成功 true
	//	   失敗 false ( 會 Rollback 已複製之檔案 )
	function b_copyfiles( $from_path , $to_path , $old_attach, &$file_str)
	{
		$old_attach = trim($old_attach);
		$files  = explode("\t", $old_attach);		// 分解原夾檔字串 Chr(9)
		if(strlen($old_attach)==0 || count($files)<2) {// 無夾檔, 則傳回成功
			$file_str = '';
			return true;
		}

		$files_copied = array();	// 紀錄已複製之檔案
		for($i=0;$i<count($files);$i+=2) {
			$target_file = uniqid('WM') . strrchr($files[$i], '.');	// 新實體檔名
			$target = $to_path .DIRECTORY_SEPARATOR. $target_file;			// 新檔案實體完整路徑
			$source = $from_path .DIRECTORY_SEPARATOR. $files[$i+1];		// 舊檔案實體完整路徑

			if (@copy($source, $target)){				// 複製檔案
				//$files .= ($_FILES['uploads']['name'][$i] . "\t" . $target . "\t");
				$files_copied[] = $target;
				$files[$i+1] = $target_file;
			} else {	// rollback copied files
				foreach($files_copied as $k=>$v) {
					//delete($v);
					unlink($v);
				}
				return false;
			}
		}

		$file_str = implode("\t", $files);	// 重組檔案字串
		return true;
	}

?>
