<?
	/**
	 * �פJ�峹�禡�w
	 *
	 * �إߤ���G2004/05/06
	 * @author  KuoYang Tsao
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lang/forum_io.php');
	require_once('System.php');

	// ���� ID
	define('ImportVersion', '3.0');
	// �פJ��ƺ���
	define('ImportType', 'post');
	$lang = Array(
			0=>'UTF-8',
			1=>'Big5',
			2=>'en',
			3=>'GB2312',
			4=>'EUC'
			);

	// ���~�T��������
	// s_ok(0) ���\
	// e_file (-1)~ e_miss_attach (-8) �� initial() �����~�T��
	//
	$import_err = Array(
			's_ok'		    => 0,
			'e_file'	    =>-1,
			'e_xml_parse'	=>-2,
			'e_wrong_root'	=>-3,
			'e_wrong_ver'	=>-4,
			'e_wrong_type'	=>-5,
			'e_no_child'	=>-6,
			'e_attach'	    =>-7,
			'e_miss_attach'	=>-8,
			'e_not_init'	=>-10,
			'e_save_attach'	=>-11,
			'e_db'		    =>-12,
			'e_unknown_type'=>-13,
			'e_save_news'   =>-14
			);
	$import_errmsg = Array(
			 0	=>$MSG['msg_imp_err_0'][$sysSession->lang],
			-1	=>$MSG['msg_imp_err_1'][$sysSession->lang],
			-2	=>$MSG['msg_imp_err_2'][$sysSession->lang],
			-3	=>$MSG['msg_imp_err_3'][$sysSession->lang],
			-4	=>str_replace('%s' ,ImportVersion, $MSG['msg_imp_err_4'][$sysSession->lang]),
			-5	=>str_replace('%s' ,ImportType, $MSG['msg_imp_err_5'][$sysSession->lang]),
			-6	=>$MSG['msg_imp_err_6'][$sysSession->lang],
			-7	=>$MSG['msg_imp_err_7'][$sysSession->lang],
			-8	=>$MSG['msg_imp_err_8'][$sysSession->lang],
			-10	=>$MSG['msg_imp_err_10'][$sysSession->lang],
			-11	=>$MSG['msg_imp_err_11'][$sysSession->lang],
			-12	=>$MSG['msg_imp_err_12'][$sysSession->lang],
			-13	=>$MSG['msg_imp_err_13'][$sysSession->lang],
			-14	=>$MSG['msg_imp_err_14'][$sysSession->lang],
			);

	class bbsPost {
		var $m_inited = false;
		var $filename  = '';
		var $m_post = Array(
				'board_id'  =>0,
				'node_id'   =>0,
				'site'      =>0,
				'open_time' =>'0000-00-00',
				'close_time'=>'0000-00-00',
				'board_name'=>'',
				'poster'    =>'',
				'realname'  =>'',
				'email'     =>'',
				'homepage'  =>'',
				'subject'   =>'',
				'content'   =>'',
				'attach'    =>'',
				'lang'      =>'',
				'lang_name' =>''
				);
		var $m_saved     = false;
		var $last_errmsg = '';
		var $tmp_dir     = '';
		var $save_path   = '';

		/**
		 * �]�w���( ���������̥��Q�I�s�B )
		 * �Щ�~�����T�{ $xml_file �s�b
		 * �Ǧ^��: ���\ : 0  ���� : �t�� (�Ԩ� $import_err �w�q)
		 */
		function initial($xml_file){
			global $import_err,$sysSession;

			$this->m_inited = false;

			// echo "<!-- $xml_file : " . is_file($xml_file) . " -->\r\n";

			if (!$post_xml = domxml_open_file( $xml_file )) return $import_err['e_xml_parse'];
			$root = $post_xml->document_element();
			if ($root->tagname != 'data') return $import_err['e_wrong_root'];

			// Check Version and Type
			if ($root->get_attribute('version') != ImportVersion ) return $import_err['e_wrong_ver'];
			if ($root->get_attribute('type') != ImportType ) return $import_err['e_wrong_type'];
			if (count($root->child_nodes())==0)	return $import_err['e_no_child'];
			foreach ($root->child_nodes() as $child) {
				$this->m_post[$child->tagname] = $child->get_content();
			}

			// ���秨��
			$attach = trim($this->m_post['attach']);
			if ($attach != '') {
				$attaches = explode(chr(9), $attach);
				if(count($attaches)%2 == 1)	// ���ɮ榡���~( �ݦ���e�{ )
					return $import_err['e_attach'];

				// �ˬd�ɮ׬O�_�s�b
				$this->tmp_dir = dirname($xml_file);	// ���o��Ƨ���m
				for ($i=0; $i<count($attaches); $i+=2){
					if ( !is_file($this->tmp_dir . '/' . $attaches[$i+1]) )
						return $import_err['e_miss_attach'];
				}
			}

			// �[�W�i�K�� IP
			// $this->m_post['content'] .= "\n<br />\n<br />--\n<br />Posting from $sysSession->ip\n<br />\n<br />====================\n<br />";

			$this->m_post['attach'] = $attach;
			$this->m_inited = true;
			return 0;
		}

		/********************
		 * �x�s���פJ
		 * 1.��Ʈw
		 * 2.�����ɮ�
		 ********************/
		function save($type='board') {	// $type:'board'(�@���)  'quint'(��ذ�)
			global $sysConn, $sysSession, $sysSiteNo;
			if (!$this->m_inited)
				return $import_err['e_not_init'];
			if ($type!='board' && $type!='quint')
				return $import_err['e_unknown_type'];

			$this->m_saved = false;

			$table_name = $type=='board'?'WM_bbs_posts':'WM_bbs_collecting';

			// ���o�ثe�O���̤j�� node
			list($mnode) = dbGetStSr($table_name, 'MAX(node)', "board_id={$sysSession->board_id} and length(node) = 9", ADODB_FETCH_NUM);
			// ���ͥ��g�� node
			$nnode = empty($mnode)?'000000001':sprintf("%09d", $mnode+1);
			$this->m_post['node_id'] = $nnode;

			if ($this->m_post['attach']) {	// �����ɤ~�ݳB�z�o�@�q
				$base_path = get_attach_file_path($type, $sysSession->board_ownerid);

				$this->save_path = $base_path . DIRECTORY_SEPARATOR . $nnode;
				// $this->m_post['node_id'] = $nnode;

				if (!is_dir($this->save_path)) @System::mkDir("-p {$this->save_path}");
				if (!is_dir($this->save_path)) return $import_err['e_save_attach'];

				$cmd = "cp {$this->tmp_dir}/* {$this->save_path} ";
				exec($cmd); //. "<br />";
				if(!is_file($this->save_path."/post.xml")) return $import_err['e_save_attach'];

			}

			// MIS#18184 ���^ - �Q�ת��פJ�\����D by Small 2010-09-23
			/*
			$username = mysql_escape_string($sysSession->username);
			$realname = mysql_escape_string($sysSession->realname);
			$email    = mysql_escape_string($sysSession->email);
			$homepage = mysql_escape_string($sysSession->homepage);
			*/
			$username = mysql_escape_string($this->m_post['poster']);
			$realname = mysql_escape_string($this->m_post['realname']);
			$email    = mysql_escape_string($this->m_post['email']);
			$homepage = mysql_escape_string($this->m_post['homepage']);
			$subject  = mysql_escape_string($this->m_post['subject']);
			$content  = mysql_escape_string($this->m_post['content']);

			// �[�J��Ʈw
			if ($type=='board') {
				$fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,attach,lang';
				$values = "$sysSession->board_id, '$nnode',$sysSiteNo".
					      ", NOW(), '$username', '$realname ', ".
					      "'$email', '$homepage ', '$subject ', '$content ',".
					      ($this->m_post['attach']?"'{$this->m_post['attach']}'":"NULL") . "," . $this->m_post['lang'];
			} else {
				$fields = 'board_id,node,site,path,pt,poster,picker,realname,email,homepage,subject,content,attach,lang';
				$path   = ($sysSession->q_path?$sysSession->q_path:'/');
				$values = "$sysSession->board_id, '$nnode',$sysSiteNo,'$path'".
					      ", NOW(), '$username', '$username', '$realname ', ".
					      "'$email', '$homepage ', '$subject ', '$content ',".
					      ($this->m_post['attach']?"'{$this->m_post['attach']}'":"NULL") . "," . $this->m_post['lang'];
			}

			dbNew($table_name, $fields,	$values);

			if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0){
                if (file_exists($this->save_path)) {
                    exec("rm -rf {$this->save_path}");  // �M����Ƨ�
                }
				return $import_err['e_db'];
			}
			$this->m_saved = true;
			return 0;
		}

		function saveNews() {
			global $sysConn, $sysSession;
			if(isset($this->m_saved)) {	// �n���g�L�W�z�x�s�{��
				$RS = dbGetStSr('WM_news_subject','news_id',"board_id={$sysSession->board_id}", ADODB_FETCH_ASSOC);
				if(!$RS)	return $import_err['e_save_news'];

				if(dbNew('WM_news_posts','news_id,board_id,node,open_time,close_time',
					"{$RS['news_id']},{$sysSession->board_id},'{$this->m_post['node_id']}','{$this->m_post['open_time']}','{$this->m_post['close_time']}'"))
					return 0;
				else
					return $import_err['e_save_news'];
			} else
				return $import_err['e_save_news'];
		}
	}

	/* �����Y tar.gz �ɦܫ��w���|
	 * input :	$tar_path		= ���w�����ؿ�( ����������|, ���ݤ��]�t "/" )
	 *		$tarfile	= tar.gz �ɦW(������|)
	 * return: ���\: true, ����: false
	 */
	 function untargz($src_file, $tar_path) {
	 	if (is_dir($tar_path)&&file_exists($src_file)) {
	 		exec("tar -zxf {$src_file} -C {$tar_path}");
			exec("chmod -Rf 755 $tar_path");
	 		return true;
	 	}
	 	return false;
	 }

	/* ���o tar �ɤ��e( �ҧt�ɮ� )
	 * input :
	 *		$tar_file	= tar.gz �ɧ����ɦW( �t���| )
	 *		$file_arr	= zip �ɩҧt�ɮ׸�T
	 * return: ���\: true, ����: false
	 */
	 function viewtar($tar_file, &$file_arr) {
	 	if (is_file($tar_file)) {

	 		ob_start();
	 		system("tar -tzf {$tar_file}");
	 		$str = ob_get_contents();
	 		ob_end_clean();

	 		$file_arr = explode("\n", $str);
	 		return true;
	 	}

	 	return false;
	 }

	function IsPostXmlExisted($tarfile) {
		$file_a = Array();
		if (!viewtar($tarfile, $file_a)) return false;
		if (in_array('post.xml', $file_a))
			return true;
		return false;
	}
?>
