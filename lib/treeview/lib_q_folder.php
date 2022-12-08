<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	$tv_index = 0;

/***********
 * recursiveTreeJS 列出顯示 TreeView 的 JavaScript 程式碼
 * 參數: $parent : 上層資料夾路徑
 *	 $parent_id : 上層資料夾識別編號
 *
 ***********/
  Function recursiveTreeJS($board_id , $parent, $parent_id)
  {
  	global $sysConn, $tv_index;
	
	if($tv_index > 50)
		return;

	// 先列出 Folder
	$sql = "select node,subject from WM_bbs_collecting where board_id=$board_id and path='$parent' and type='D'";
	$rs  = $sysConn->Execute($sql);
	if( !$rs )
		return '';

	$gFldStr = "";
	$pName = "";

	if($parent_id == 0) {	// 原檔案為 parent = -1
		$pName = "root";
		$parent_path = '/';
	} else {
		$pName = "nd" . $parent_id;
		$parent_path = $parent .'/';
	}
	//echo "<!--($tv_index): parent = {$parent} , sql={$sql} -->\r\n";

	$tv_index++;
	$js = '';

	while( !$rs->EOF )
	{
		$cur_path = $parent_path . $rs->fields['subject'];
		
		//echo "\t<!-- parent_path={$parent_path}, cur path={$cur_path} -->\r\n";

		$gFldStr = "gFld('{$rs->fields['subject']}', 'choosefolder(\"{$cur_path}\")', true)";


		$js .= "nd{$tv_index} = insFld(" . $pName . "," . $gFldStr . ");\r\n";
		$js .="nd{$tv_index}.xID= '{$rs->fields['node']}';\r\n";

		$js .= recursiveTreeJS( $board_id, $cur_path, $tv_index );
		
		$rs->MoveNext();
	}
	
	return $js;
  }
?>