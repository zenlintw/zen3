<?php
	/**
	 * 匯出文章函式庫
	 *
	 * 建立日期：2004/05/05
	 * @author  KuoYang Tsao
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	// 版本 ID
	define('ExportVersion', '3.0');
	// 匯出資料種類
	define('ExportType', 'board');
	define('ExportPostType', 'post');
	$lang = Array(
			0=>'UTF-8',
			1=>'Big5',
			2=>'en',
			3=>'GB2312',
			4=>'EUC'
			);

	/*
	 *	取得Owner 所在目錄( 學校, 班級, 課程, 小組 )
	 * @param int $owner_id : 討論板 owner_id , 若不給則抓 $sysSession->board_ownerid
	 * @return string: 路徑
	 */
	function getOwnerDir($owner_id='') {
		global $sysSession, $sysConn;
		if(empty($owner_id)) {
			if(empty($sysSession->board_ownerid))
				return -1;
			$owner_id = $sysSession->board_ownerid;
		}

		switch(strlen($owner_id)) {
			case 5:// 學校討論版
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . $owner_id;
				break;

			case 7:// 班級
			case 15:// 班級小組
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id. DIRECTORY_SEPARATOR . 'class'. DIRECTORY_SEPARATOR . $sysSession->class_id;
				break;

			case 8:// 課程
			case 16:// 課程小組
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id . DIRECTORY_SEPARATOR . 'course'. DIRECTORY_SEPARATOR . $sysSession->course_id;
				break;
			default:
				return '';
		}
	}


	/**
	 * 將文字存檔
	 * @param string $filepath : 完整檔案路徑全名(實體檔案系統)
	 * @param string $content  : 欲存放內容
	 **/
	function saveFile($filepath , $content) {
		$fp = fopen($filepath,'w');
		fwrite($fp, $content);
		fclose($fp);
	}

	/*****************************
	 * 單篇文章類別(包含精華區)
	 *****************************/
	class bbsPost {
		var $m_getPost = false;
		var $filename  = '';
		var $m_post = Array(
				'open_time' =>'0000-00-00',
				'close_time'=>'0000-00-00',
				'lang_name' =>''
				);
		var $m_type = 'board';	// 'board':一般區 , 'quint':精華區
		var $IsNews = FALSE;	// 是否為具有啟用時間欄位之討論板文章

		/**
		 * 建構子
		 * @param $RS : 呼叫者以 dbGetStSr 所取得之資料陣列
		 */
		function bbsPost(&$RS,$board_type='board'){
			global $sysConn,$lang;
			$this->m_type = $board_type;

			if($RS) {
				foreach($RS as $k=>$v)
					$this->m_post[$k] = $v;
				$this->m_post['lang_name'] = $lang[$this->m_post['lang']];
				$this->m_getPost = true;
				if($this->m_post['node']) $this->filename = ($this->m_type=='board'?'b':'q').$this->m_post['node'].'.xml';
			} else {
				$this->m_getPost = false;
			}
		}

		function getNewsFields() {
			global $sysSession, $sysConn;
			if($this->m_getPost)	{ // 要上述程序先完成
				$RS = dbGetStSr('WM_news_posts','open_time,close_time',"board_id={$this->m_post['board_id']} and node='{$this->m_post['node_id']}'", ADODB_FETCH_ASSOC);
				if(!$RS) return false;
				$this->m_post['open_time'] = $RS['open_time'];
				$this->m_post['close_time']= $RS['close_time'];
				return true;
			}
		}

		function saveXML($path) {
			global $lang, $Y_DEBUG;
			if(!$this->m_getPost) return false;

			$post = $this->m_post;

			$str = '<?xml version="1.0"?><data version="'. ExportVersion . '" time="'.Date('Y-m-d h:i:s',time()).'" type="'.ExportPostType.'" filename="'.$this->filename . '"></data>';
			$dom = domxml_open_mem($str);
			$root = $dom->document_element();
			foreach($post as $k=>$v) {
				if(!is_int($k)) {
					$node = $dom->create_element($k);
					$text = $dom->create_text_node($v);
					$text = $node->append_child($text);
					$root->append_child($node);
				}
			}
			saveFile($path . DIRECTORY_SEPARATOR . $this->filename, @$dom->dump_mem(true));
			return true;
		}

	}


	/***********************
	 * 加入一般區或精華區整版清單
	 * gen_list_xml()
	 ***********************/
	function add_post_list_xml($temp_path, &$forum_node, $board_id, $type='board') {
		global $sysConn;
		if($type=='board')
			$RS = dbGetStMr('WM_bbs_posts','*',"board_id='{$board_id}'", ADODB_FETCH_ASSOC);
		else
			$RS = dbGetStMr('WM_bbs_collecting','*',"board_id='{$board_id}'", ADODB_FETCH_ASSOC);

		$dom = $forum_node->owner_document();
		$b_node = $dom->create_element($type);
		$forum_node->append_child($b_node);
		while(!$RS->EOF) {
			$n_node = $dom->create_element('node');
			$n_node->set_attribute('id', $RS->fields['node']);
			$b_node->append_child($n_node);

			if($type=='quint' && $RS->fields['type']=='D') {	// 精華區目錄只匯出在 list.xml 中
				$n_node->set_attribute("type", 'D');

				$d_node = $dom->create_element('subject');	// 目錄名稱
				$subject = $dom->create_text_node($RS->fields['subject']);
				$subject = $d_node->append_child($subject);
				$n_node->append_child($d_node);

				$d_node = $dom->create_element('path');	// 路徑
				$path = $dom->create_text_node($RS->fields['path']);
				$path = $d_node->append_child($path);
				$n_node->append_child($d_node);

			} else {
				$n_node->set_attribute('type', 'F');
				$n_node->set_attribute('data', substr($type,0,1).$RS->fields['node'].'.xml');
				// 儲存單篇文章
				// 注意夾檔尚需處理
				//
				$node_obj = new bbsPost($RS->fields, $type);
				if($type=='board' && $sysSession->news_board) $node_obj->getNewsFields();
				$node_obj->saveXML( $temp_path . DIRECTORY_SEPARATOR . $type, NULL );
			}
			$RS->MoveNext();
		}
	}

	/*
		將版名作處理 , 版名加上 (20041216_copy) 這樣的文字
		@param string (serialized array) $bname : 經過 serialize 處理過的版名語系陣列
		@return string(seralized array ): 加上日期註記的序列化版名
	 */
	function processBName($bname) {
			$langs = unserialize($bname);
			$langs1 = Array();
			if (is_array($langs)) {
				foreach($langs as $k=>$lang) {
					$langs1[$k] = $lang . '('.Date('Ymd').'_copy)' ;
				}
			}
			return serialize($langs1);
	}

	/*
		產生清單 XML
		@param int $board_id :版號
		@return DOM object: 清單XML 之dom object
	 */
	function gen_list_xml($board_id) {
		global $sysConn;

		$str = '<?xml version="1.0"?><data version="'. ExportVersion . '" time="'.Date('Y-m-d h:i:s',time()).'" type="'.ExportType.'" filename="list.xml"></data>';
		$dom = domxml_open_mem($str);
		$root = $dom->document_element();
		$root->set_attribute('id',$board_id);
		$RS = dbGetStSr('WM_bbs_boards','*',"board_id='{$board_id}'", ADODB_FETCH_ASSOC);
		foreach($RS as $k=>$v) {
			if(!is_int($k)) {
				$node = $dom->create_element($k);
				if($k=='bname')
					$text = $dom->create_text_node(processBName($v));
				else
					$text = $dom->create_text_node($v);
				$text = $node->append_child($text);
				$root->append_child($node);
			}
		}
		return $dom;
	}
?>
