<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/xmlapi/config.php');

	/*Custom by lubo (B) -- 取出時要反解2次 */
	if ( !empty($_COOKIE['forum_search']) ) {
		$_COOKIE['forum_search'] = urldecode( urldecode($_COOKIE['forum_search']) );
	}
	/*Custom by lubo (E) */
    /* Custom By TN 20120116(B)MIS#023770*/
    function GetAcaArray($school_id){
		return dbGetCol('WM_manager', 'username',"school_id='{$school_id}'", ADODB_FETCH_ASSOC);
    } 
    function GetAcaEmail($school_id){    
        chkSchoolId('WM_manager'); 
		return dbGetAssoc('WM_manager M,'.sysDBprefix.$school_id.'.WM_user_account A', 'A.username,A.email',"M.username=A.username AND M.school_id='{$school_id}'", ADODB_FETCH_ASSOC);
    } 
    function isNewBoard($board_id) {
		$count = dbGetOne('WM_news_subject', 'count(*) as total',"board_id={$board_id}", ADODB_FETCH_ASSOC);
        return ($count>0);
	} 
    function isCourseBoard($owner_id, $board_id) {
		if(strlen($owner_id)!=8) return false;	// 不是課程
		$count = dbGetOne('WM_term_subject S,WM_bbs_boards B', 'count(*) as total',"B.board_id = S.board_id AND S.course_id={$owner_id} AND S.board_id={$board_id}", ADODB_FETCH_ASSOC);
        return ($count>0);
	}  
    function getCourseTeacherLevel(){
        global $sysSession,$Sqls,$sysConn;
        chkSchoolId('WM_term_major');            
        $sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $Sqls['get_course_teacher_level']);
        $RS = $sysConn->GetAssoc($sqls);                    
        return $RS;         
    }              
    /* Custom By TN 20120116(E)MIS#023770*/

	/**
	 * 討論版 Cookie 存取函式
	 **/

	// 寫入 Cookie 搜尋條件
	/* SetForumCookie()
	 *
	 * $type : 'board' 一般討論區 'quint' 精華區
	 */
	function SetForumCookie($forum_search,$search_type,$search_keyword,$timeout=86400,$type='board') {
		global $_COOKIE;
		switch($type) {
			case 'quint':	$type_name = 'qsearch'; break;
			case 'board':
			default:	$type_name = 'search'; break;
		}
		//setcookie('forum_'.$type_name,   $forum_search,  time() +$timeout	, '/');
		/*Custom by lubo (B) -- 修正搜尋後會被forbidden */
		setcookie('forum_'.$type_name,   urlencode($forum_search),  time() +$timeout	, '/');
		/*Custom by lubo (E) */
		setcookie($type_name.'_type',    $search_type,   time() +$timeout	, '/');
		setcookie($type_name.'_keyword', $search_keyword,time() +$timeout	, '/');
	}

	// 清除 Cookie 所存搜尋條件
	function ClearForumCookie() {
		global $_COOKIE;
		setcookie('forum_search'   , '', time()-1,	'/');
		setcookie('search_type'    , '', time()-1,	'/');
		setcookie('search_keyword' , '', time()-1,	'/');
		setcookie('forum_qsearch'  , '', time()-1,	'/');
		setcookie('qsearch_type'   , '', time()-1,	'/');
		setcookie('qsearch_keyword', '', time()-1,	'/');
	}

	// 寫入 Cookie 每頁筆數
	function SetForumPageCookie($rows_page,$timeout=86400) {
		setcookie('rows_page', $rows_page,  time()+$timeout	, '/');
	}

	// 清除 Cookie 每頁筆數
	function ClearForumPageCookie() {
		setcookie('rows_page', '',  time()-1	, '/');
	}

	// 取得每頁筆數
	function GetForumPostPerPage() {

		global $_POST,$_COOKIE, $sysSession;
		$rows_page = -1;
		if(isset($_POST['rows_page'])) {	// 檢查是否要求變動 ( -1 為要求恢復預設值 )
			$rows_page = IntVal($_POST['rows_page']);

			if($rows_page==0) {	// 未作變動
				if(isset($_COOKIE['rows_page'])) {
					$rows_page = IntVal($_COOKIE['rows_page']);
				}
			}

		} else if(isset($_COOKIE['rows_page'])) {	// 無 POST, 但有 COOKIE
			$rows_page = IntVal($_COOKIE['rows_page']);
		}

		if($rows_page <= 0) {
			$rows_page = sysPostPerPage;
			ClearForumPageCookie();
		} else {
			SetForumPageCookie($rows_page);
		}

		return $rows_page;
	}

	/**************************************
		getSQLwhere() : 取得討論板(精華區)SQL 過濾語法
		參數 : &$is_search	: 用來回傳之參照參數, 供呼叫者識別目前是否為搜尋狀態
							  ( 注意:是以"字串"傳回, 非布林值 )
				$type		: 識別是一般討論板('board')或是精華區之旗標
		傳回值 : where 語法(字串)
	***************************************/
	function getSQLwhere(&$is_search, $type='board') {
		global $sysSession, $sysConn, $_POST, $_COOKIE;

		if($type == 'board') {
			$cookie_index = Array('forum_search','search_keyword','search_type');
		} else {	// quint
			$cookie_index = Array('forum_qsearch','qsearch_keyword','qsearch_type' );
		}

		$where = '';
		$is_search = 'false';

		if(isset($_POST['is_search'])) {
			$is_search = $_POST['is_search'];
			if($is_search=='true') {
				// $_POST['keyword'] = stripslashes($_POST['keyword']);
				$_POST['keyword'] = htmlspecialchars(stripslashes($_POST['keyword']), ENT_QUOTES);
				$keyword = mysql_escape_string($_POST['keyword']);
			  // 是搜尋，換掉 where
			  switch($_POST['search_type'])
			  {
				case 'subject':
					$where = 'and locate("'.$keyword.'", subject) > 0';
					//$where = 'and subject like "%' . $keyword . '%"';
					break;
				case 'poster':
					$search     = array('&quot;', '&lt;', '&gt;', '&amp;', '&#039;');
					$replace    = array('"', '<', '>', '&', "'");
					$orgKeyword = addslashes(str_replace($search, $replace, $_POST['keyword']));
					$where      = 'and (locate("'.$orgKeyword.'", poster) > 0 or locate("'.$orgKeyword.'", realname) > 0)';
					//$where = "and (poster like '%" . $keyword . "%' or locate('" .$keyword."',realname)>0)";
					break;
				case 'picker':
					$where = 'and locate("'.$keyword.'", picker) > 0';
					//$where = "and (picker like '%" . $keyword . "%')";
					break;
				case 'content':
					$where = 'and locate("'.$keyword.'", content) > 0';
					//$where = "and (content like '%" .$keyword."%')";
					break;
				default:
					$where = "";
					$is_search = 'false';
					break;
			  }
			}
		} elseif(isset($_COOKIE[ $cookie_index[0] ])) {	// 先前曾紀錄搜尋條件
			$is_search           = 'true';
			$where               = isset($_COOKIE[ $cookie_index[0] ])?stripslashes($_COOKIE[ $cookie_index[0] ]):'';
			$_POST['keyword']    = stripslashes($_COOKIE[ $cookie_index[1] ]);
			$_POST['search_type']= $_COOKIE[ $cookie_index[2] ];
		}
		// $_POST['keyword'] = str_replace('"','&quot;', $_POST['keyword']);
		return $where;
	}

	// getTotalPost() : 取得討論板跟精華區總張貼數
	// $where: 額外搜尋條件
	// $type : 'board' 一般區 'quint' 精華區
	function getTotalPost($where='', $type='board',$co_bid=0) {
		global $sysSession, $sysConn;
		$total_post = 0;
		
		if($co_bid==0){
			$co_board=$sysSession->board_id;
			$co_path=$sysSession->q_path;
		}else{
			$co_board=$co_bid;
			$co_path='/';
			
		}
		// 取得本板張貼數
		if($type=='board') {	// 一般區
		
			list($total_post) = dbGetStSr('WM_bbs_posts', 'count(*)', "board_id=$co_board $where", ADODB_FETCH_NUM);
			
		} else {				// 精華區
			if($where=='') {
				list($total_post) = dbGetStSr('WM_bbs_collecting', 'count(*)', "board_id=$co_board and path='$co_path'", ADODB_FETCH_NUM);
			} else { // 是搜尋, 不管路徑
				list($total_post) = dbGetStSr('WM_bbs_collecting', 'count(*)', "board_id=$co_board $where", ADODB_FETCH_NUM);
			}
		}
		return $total_post;
	}

	function isBoardManager($poster, $board_id)
	{
		static $caches;

	    if (!preg_match('/^[\w-]+$/', $poster) || !preg_match('/^\d{10}$/', $board_id))
			return false;

		if (isset($caches[$poster][$board_id]))
			return $caches[$poster][$board_id];

		list($manager, $board_owner) = dbGetRow('WM_bbs_boards', 'manager,owner_id', 'board_id=' . $board_id, ADODB_FETCH_NUM);
		if ($manager == $poster) return true;
		switch (strlen($board_owner))
		{
		    case 5: // school
		        return ($caches[$poster][$board_id] = (bool)dbGetOne('WM_manager', 'count(*)', "username='{$poster}' and school_id={$board_owner}"));

			case 7: // class
			    return ($caches[$poster][$board_id] = (bool)dbGetOne('WM_class_member', 'count(*)', "class_id={$board_owner} and username='{$poster}' and role&1088"));

			case 8: // course
			    return ($caches[$poster][$board_id] = (bool)dbGetOne('WM_term_major', 'count(*)', "username='{$poster}' and course_id={$board_owner} and role&704"));

			case 16: // course team
			    $course_id = substr($board_owner, 0, 8);
			    $group_id  = intval(substr($board_owner, 12));
			    $team_id   = intval(substr($board_owner, 8, 4));
			    return ($caches[$poster][$board_id] = (bool)dbGetOne('WM_term_major', 'count(*)', "username='{$poster}' and course_id={$course_id} and role&704") ||
			                                          (bool)dbGetOne('WM_student_group', 'count(*)', "course_id={$course_id} and group_id={$group_id} and team_id={$team_id} and captain='{$poster}' and board_id={$board_id}"));

			default:
			    return false;
		}
	}

	/******************************
		刪除一篇文章(一般區)
		參數:
				$board_id	: 板號
				$node		: 文章編號
				$site		: 站號
	 ******************************/
	function delete_post($board_id, $node, $site) {
			global $sysSession, $sysConn;

            list($poster, $subject) = dbGetStSr('WM_bbs_posts', 'poster, subject', "board_id='{$board_id}' and node='{$node}' and site='{$site}'", ADODB_FETCH_NUM);

            // 在研發尚未釋出解法前，先比照舊版本拿掉以下判斷 by Small 2011/11/16
            /*
            if ($poster != $sysSession->username && !isBoardManager($sysSession->username, $board_id))
            return false;
            */

            dbDel('WM_bbs_posts', "board_id='{$board_id}' and node='{$node}' and site='{$site}'");
            dbDel('WM_bbs_readed', "type='b' and board_id='{$board_id}' and node='{$node}'");
            dbDel('WM_bbs_ranking', "type='b' and board_id='{$board_id}' and node='{$node}' and site='{$site}'");
            dbDel('WM_bbs_push', "type='b' and board_id='{$board_id}' and node='{$node}' and site='{$site}'");

            // 如果刪除主題一併刪除回覆、留言
            if (strlen($node) === 9) {
                dbDel('WM_bbs_posts', "board_id='{$board_id}' and substr(node, 1,9)='{$node}' and site='{$site}'");
                dbDel('WM_bbs_readed', "type='b' and board_id='{$board_id}' and substr(node, 1,9)='{$node}'");
                dbDel('WM_bbs_ranking', "type='b' and board_id='{$board_id}' and substr(node, 1,9)='{$node}' and site='{$site}'");
                dbDel('WM_bbs_push', "type='b' and board_id='{$board_id}' and substr(node, 1,9)='{$node}' and site='{$site}'");
                dbDel('WM_bbs_whispers', "board_id='{$board_id}' and substr(node, 1,9)='{$node}' and site='{$site}'");
            }

            // 如果刪除回覆一併刪除留言
            if (strlen($node) === 18) {
                dbDel('WM_bbs_whispers', "board_id='{$board_id}' and node='{$node}' and site='{$site}'");
            }

            // 刪除所有檔案
            $attach_path = get_attach_file_path('board', $sysSession->board_ownerid, $board_id) . DIRECTORY_SEPARATOR . $node;
			if (is_dir($attach_path)) @System::rm("-rf $attach_path");
            
//            // 刪除所有檔案
//            $attach_files = glob($attach_path);
//            if (is_array($attach_files) && count($attach_files) >= 1) {
//                foreach ($attach_files as $v) {
//                    unlink($v);
//                }
//            }

            // 更新 Quota 資訊
            getCalQuota($sysSession->board_ownerid, $quota_used, $quota_limit);
            setQuota($sysSession->board_ownerid, $quota_used);

            if (isset($sysSession->course_id)) {
                dbSet('WM_term_major', 'post_times=post_times-1', "username='{$poster}' and course_id='{$sysSession->course_id}' and post_times>0");
                dbSet('WM_term_course', 'post_times=post_times-1', "course_id='{$sysSession->course_id}' and post_times>0");
            }
            dbDel('WM_news_posts', "board_id='{$board_id}' and node='{$node}'");
            // 是否為最新消息類型
            if ($sysSession->news_board) {
                include_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
                // 更新最新消息列表
                if (IsNewsBoard())
                    createNewsXML($sysSession->school_id, 'news');
            }
            wmSysLog(900200700, $board_id, $node, 0, 'auto', $_SERVER['PHP_SELF'], 'delete board post(site:' . $site . ', subject:' . $subject . ', poster:' . $poster . ')');
        }

	/******************************
		刪除一篇文章(精華區)
		參數:
				$board_id	: 板號
				$node		: 文章編號
				$site		: 站號
				$post_node	: 一般區原文章編號
	 ******************************/
	function delete_qost($board_id, $node, $site, $post_node) {

			global $sysSession, $sysConn;

			if (!isBoardManager($sysSession->username, $board_id))
				return false;

			dbDel('WM_bbs_collecting',   "board_id='{$board_id}' and node='{$node}' and site='{$site}'");
			dbDel('WM_bbs_readed',  "type='q' and board_id='{$board_id}' and node='{$node}'");
			dbDel('WM_bbs_ranking', "type='q' and board_id='{$board_id}' and node='{$node}' and site='{$site}'");
			dbDel('WM_bbs_push', "type='q' and board_id='{$board_id}' and node='{$node}' and site='{$site}'");
			// $attach_path = get_attach_file_path('quint', $sysSession->board_ownerid) . DIRECTORY_SEPARATOR . $node;
			// if (is_dir($attach_path)) @System::rm("-rf $attach_path");

			$attach_path = get_attach_file_path('quint', $sysSession->board_ownerid) . DIRECTORY_SEPARATOR . $node;
			if (is_dir($attach_path)) @System::rm("-rf $attach_path");

			// 更新 Quota 資訊
			getCalQuota($sysSession->board_ownerid, $quota_used, $quota_limit);
			setQuota($sysSession->board_ownerid, $quota_used);

			if(isset($sysSession->course_id) && strlen($post_no) > 0) {	// 非收錄文章, 所以應扣除刊登篇數
				dbSet('WM_term_major', 'post_times=post_times-1', "username='{$sysSession->username}' and course_id='{$sysSession->course_id}' and post_times>0");
				dbSet('WM_term_course', 'post_times=post_times-1', "course_id='{$sysSession->course_id}' and post_times>0");
			}

	}

	/*************************
		檢查精華區資料夾下是否有為空
	 *************************/
	function IsFolderEmpty($board_id, $path) {
		global $sysConn;
		list($total) = dbGetStSr('WM_bbs_collecting','count(*) as total',"board_id={$board_id} and path='{$path}'", ADODB_FETCH_NUM);
		return !$total;
	}

	/*************************
		刪除精華區資料夾 (注意:下層全部刪除)
	 *************************/
	function DelFolder($board_id, $path) {
		global $sysSession, $sysConn, $sysSiteNo;

		if (!isBoardManager($sysSession->username, $board_id))
			return false;

		// 列出此目錄下的布告與目錄
		$RS = dbGetStMr('WM_bbs_collecting',
						'node,subject,site,type,post_node',
						"board_id={$board_id} and site='{$sysSiteNo}' and path='{$path}'" ,
						ADODB_FETCH_ASSOC);
		// 有的話
		while(!$RS->EOF){
			$node = $RS->fields['node'];
			$site = $RS->fields['site'];
			if ($RS->fields['type'] == 'D'){	// 如果是目錄
				// 往下一層砍
				$the_path = ($path == '/'?'':$path) . '/'. $RS->fields['subject'];
				DelFolder($board_id, $the_path);
				dbDel('WM_bbs_collecting', "board_id={$board_id} and node='{$node}' and site='{$sysSiteNo}' and type='D'");
			}
			else{					// 或者是布告
				delete_qost($board_id, $node, $site, $RS->fields['post_node']);
			}
			$RS->MoveNext();
		}

		$the_folder = basename($path);
		$the_path   = dirname($path);
		if(strlen($the_path)!=0) {	// 刪除本資料夾
			dbDel('WM_bbs_collecting' , "board_id=$board_id and path='{$the_path}' and subject='{$the_folder}' and type='D'");
		}
	}

	/********************************
		建立精華區資料夾
	 ********************************/
	function MakeFolder($board_id, $path, $subject) {
		global $sysSession, $sysConn, $sysSiteNo;

		if (!isBoardManager($sysSession->username, $board_id))
			return false;

		// 檢查資料夾是否存在, 若不存在 , 則建立之
		$chkRS = dbGetStSr('WM_bbs_collecting', 'count(*) as total',
						   "board_id={$board_id} and path='{$path}' and subject='{$subject}'and type='D'",
						   ADODB_FETCH_ASSOC);
		if($chkRS && $chkRS['total']==0) {
			dbNew('WM_bbs_collecting',
			      'board_id,node,site,subject,picker,ctime,path,type',
			      "{$board_id},md5('{$subject}'),{$sysSiteNo},'{$subject}','{$sysSession->username}',now(),'{$path}','D'"
			     );
		}
	}

	/***********************************
		搬移精華區資料夾 (下層全部搬移)
		把 $source_path 下的 $source_folder 搬到 $target_path 下
	 ***********************************/
	function MoveFolder($board_id, $source_path, $source_folder, $target_path) {
		global $sysSession, $sysConn, $sysSiteNo;

		if (!isBoardManager($sysSession->username, $board_id))
			return false;

		static $i=0;
		$i++;
		$j = $i;
		$tab = str_repeat("\t",$j);

		// 列出此目錄下的布告與目錄
		$new_target = ($target_path=='/'?'':$target_path) . "/$source_folder";
		$new_source = ($source_path=='/'?'':$source_path) . "/$source_folder";

		dbSet('WM_bbs_collecting',"path='$target_path'", "board_id={$sysSession->board_id} and path='{$source_path}' and subject='{$source_folder}' and type='D'");

		$RS = dbGetStMr('WM_bbs_collecting',
						'node,path,subject,type',
						"board_id={$board_id} and site='{$sysSiteNo}' and path='{$new_source}'" ,
						ADODB_FETCH_ASSOC);

		// 先建立資料夾( 不用建立, 改變 path 值即可 )
		//MakeFolder($board_id, $target_path, $source_folder);

		while(!$RS->EOF){
			$node	 = $RS->fields['node'];
			$subject = $RS->fields['subject'];
			$type = $RS->fields['type'];

			if ($type == 'D'){	// 如果是目錄
				// 再搬下一層
				MoveFolder($board_id, $new_source, $subject, $new_target);
			}
			$RS->MoveNext();
		}
		// 變更路徑
		dbSet('WM_bbs_collecting', "path='{$new_target}'",  "board_id='{$board_id}' and site='{$sysSiteNo}' and path='{$new_source}'");

	}

	function getBoardTime($board_id) {
		global $sysConn;
		$RS = dbGetStSr('WM_bbs_boards','open_time,close_time,share_time',"board_id={$board_id}", ADODB_FETCH_ASSOC);
		if($RS) {
			return Array(
					'open_time'=> $sysConn->UnixTimeStamp($RS['open_time']),
					'close_time'=>$sysConn->UnixTimeStamp($RS['close_time']),
					'share_time'=>$sysConn->UnixTimeStamp($RS['share_time']) );
		} else
			return null;
	}

	/*
	 *	取得討論版之擁有者( 學校, 班級, 課程, 群組 )
	 *	以 global var ($Board_OwnerID, $Board_Owner)存取變數
	 */
	function getBoardOwner($board_id) {
		global $sysSession, $sysConn, $Board_Owner, $Board_OwnerID;

		// Get owner_id of Board
		if(empty($Board_Owner) || empty($Board_OwnerID)) {
			$rs  = dbGetStSr('WM_bbs_boards', 'owner_id', "board_id={$board_id}", ADODB_FETCH_ASSOC);
			if(!$rs)
				return false;
			$Board_OwnerID = $rs['owner_id'];
		}
		switch(strlen($Board_OwnerID)) {
			case 5:// 學校討論版
				$Board_Owner = $sysSession->school_name;
				break;

			case 7:// 班級
			case 15:// 群組
				$Board_Owner = $sysSession->class_name;
				break;

			case 8:// 課程
			case 16:// 群組
				$Board_Owner = $sysSession->course_name;
				break;
			default:	// 預設為課程名稱
				$Board_OwnerID = $sysSession->course_id;
				$Board_Owner = $sysSession->course_name;
				break;
		}
		return true;
	}

	/***************************************************
		Extras 設定值 ( 供儲存討論版特殊設定值 )
		2005/1/11
	***************************************************/
	// 討論版 extras 特殊設定預設值
	$board_extras =  Array(
					'rank'=>1	// 具有推薦星等功能
				);

	/*
	 * loadExtras2Cookie()
	 *    載入討論版特殊設定 : >>>>剛進入討論版時使用<<<<
	 *    @param int    $board_id : 討論板號
	 *    @param int    $timeout : cookie timeout
	 *    @return string : 所存 extras 字串
	 */
	function loadExtras2Cookie($board_id, $timeout=86400) {
		global $sysConn;
		list($extras) = dbGetStSr('WM_bbs_boards', 'extras', "board_id='{$board_id}'", ADODB_FETCH_NUM);

		setcookie('forum_extras',   $extras,  time() +$timeout	, '/');
		return $extras;
	}

	/*
	 * getExtras()
	 *    載入討論版特殊設定至 $board_extras 陣列: >>>>>>討論版中使用(除非 cookie 值不存在, 才從資料庫載入, 否則都只抓 cookie 值 ) <<<<<<
	 *   ( 注意:進入討論版前須先以 loadExtras2Cookie 寫入 cookie )
	 *    @param int    $key          : 要取值的 key ( 比如 : 'rank')
	 *    @return  int : 對應 key 的值( 比如 : 0 或 1)
	 */
	function getExtras($key) {
		global $board_extras, $_COOKIE;
		// $extras = $_COOKIE['forum_extras'];

		$extras_ar = explode(';',$_COOKIE['forum_extras']);
		foreach($extras_ar as $k=>$v) {
			$data = explode('=', $v);
			$board_extras[$data[0]] = $data[1];
		}
		return $board_extras[$key];
	}

	/**
	 * 課程討論板共用函式(WEB與APP)
	 *
	 * @param integer $cid: 課程代號
	 * @return array 討論板
	 */
	function getCourseBoard($cid, $excBids = array())
	{
		$cid = intval($cid);

		// 取課程公告版編號
		$rsForum = new forum();

		// 因應個人區的未讀文章，呈現課程公告於列表中
		$forumList = $rsForum->getCourseForumList($cid, $excBids, true);
		if (is_array($forumList)) {
			foreach ($forumList as $k => $v) {
				$forumList[$k]['canRead'] = ChkBoardReadRight($v['board_id'])? 'Y' : 'N';
			}
		}

		return $forumList;
	}

	/**
     * APP 判斷是否可以刪除討論板主題、回文、留言的權限
     *
     * @param {String} $user
     * @param {Integer} $boardID
     *
     * @return {Boolean} 是否具有刪除權限
	 **/
    function verifyDeleteRight ($user, $boardID) {
        global $sysSession;

        // 是本人就直接回傳true
        if ($sysSession->username === $user) {
            return true;
        }

        // 最後比對是否為管理者
        return isBoardManager($sysSession->username, $boardID);
    }