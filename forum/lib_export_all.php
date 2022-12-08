<?php
	/**
	 * �ץX�峹�禡�w
	 *
	 * �إߤ���G2004/05/05
	 * @author  KuoYang Tsao
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	// ���� ID
	define('ExportVersion', '3.0');
	// �ץX��ƺ���
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
	 *	���oOwner �Ҧb�ؿ�( �Ǯ�, �Z��, �ҵ{, �p�� )
	 * @param int $owner_id : �Q�תO owner_id , �Y�����h�� $sysSession->board_ownerid
	 * @return string: ���|
	 */
	function getOwnerDir($owner_id='') {
		global $sysSession, $sysConn;
		if(empty($owner_id)) {
			if(empty($sysSession->board_ownerid))
				return -1;
			$owner_id = $sysSession->board_ownerid;
		}

		switch(strlen($owner_id)) {
			case 5:// �ǮհQ�ת�
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . $owner_id;
				break;

			case 7:// �Z��
			case 15:// �Z�Ťp��
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id. DIRECTORY_SEPARATOR . 'class'. DIRECTORY_SEPARATOR . $sysSession->class_id;
				break;

			case 8:// �ҵ{
			case 16:// �ҵ{�p��
				return sysDocumentRoot . DIRECTORY_SEPARATOR . 'base'. DIRECTORY_SEPARATOR . $sysSession->school_id . DIRECTORY_SEPARATOR . 'course'. DIRECTORY_SEPARATOR . $sysSession->course_id;
				break;
			default:
				return '';
		}
	}


	/**
	 * �N��r�s��
	 * @param string $filepath : �����ɮ׸��|���W(�����ɮרt��)
	 * @param string $content  : ���s�񤺮e
	 **/
	function saveFile($filepath , $content) {
		$fp = fopen($filepath,'w');
		fwrite($fp, $content);
		fclose($fp);
	}

	/*****************************
	 * ��g�峹���O(�]�t��ذ�)
	 *****************************/
	class bbsPost {
		var $m_getPost = false;
		var $filename  = '';
		var $m_post = Array(
				'open_time' =>'0000-00-00',
				'close_time'=>'0000-00-00',
				'lang_name' =>''
				);
		var $m_type = 'board';	// 'board':�@��� , 'quint':��ذ�
		var $IsNews = FALSE;	// �O�_���㦳�ҥήɶ���줧�Q�תO�峹

		/**
		 * �غc�l
		 * @param $RS : �I�s�̥H dbGetStSr �Ҩ��o����ư}�C
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
			if($this->m_getPost)	{ // �n�W�z�{�ǥ�����
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
	 * �[�J�@��ϩκ�ذϾ㪩�M��
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

			if($type=='quint' && $RS->fields['type']=='D') {	// ��ذϥؿ��u�ץX�b list.xml ��
				$n_node->set_attribute("type", 'D');

				$d_node = $dom->create_element('subject');	// �ؿ��W��
				$subject = $dom->create_text_node($RS->fields['subject']);
				$subject = $d_node->append_child($subject);
				$n_node->append_child($d_node);

				$d_node = $dom->create_element('path');	// ���|
				$path = $dom->create_text_node($RS->fields['path']);
				$path = $d_node->append_child($path);
				$n_node->append_child($d_node);

			} else {
				$n_node->set_attribute('type', 'F');
				$n_node->set_attribute('data', substr($type,0,1).$RS->fields['node'].'.xml');
				// �x�s��g�峹
				// �`�N���ɩ|�ݳB�z
				//
				$node_obj = new bbsPost($RS->fields, $type);
				if($type=='board' && $sysSession->news_board) $node_obj->getNewsFields();
				$node_obj->saveXML( $temp_path . DIRECTORY_SEPARATOR . $type, NULL );
			}
			$RS->MoveNext();
		}
	}

	/*
		�N���W�@�B�z , ���W�[�W (20041216_copy) �o�˪���r
		@param string (serialized array) $bname : �g�L serialize �B�z�L�����W�y�t�}�C
		@return string(seralized array ): �[�W������O���ǦC�ƪ��W
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
		���ͲM�� XML
		@param int $board_id :����
		@return DOM object: �M��XML ��dom object
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
