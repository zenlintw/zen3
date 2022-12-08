<?php
	/**
	 *	�� �̷s���� �� �`�����D �禡�w
	 *
	 * @since   2004/11/19
	 * @author  Yang
	 * @version $Id: lib_newsfaq.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/
	require_once(sysDocumentRoot . '/lib/file_api.php');

	/*
	 * IsNewsBoard()
	 *    �����O�_���̷s����(�α`�����D)
	 *	@param string $type : �̷s����=>'news' (�α`�����D=>'faq')
	 *	@return bool : �O true, �_ false
	 */
	function IsNewsBoard($type='news', $boardId = 0) {
		global $sysSession, $sysConn;

		$board = ($boardId === 0) ? $sysSession->board_id : intval($boardId);
		$RS = dbGetStSr('WM_news_subject', 'count(*) as total', "type='{$type}' and board_id={$board}", ADODB_FETCH_ASSOC);
		return ($RS && $RS['total']>0);
	}

	/**
	 * �N��r�s��
	 * @param string $filepath : �����ɮ׸��|���W(�����ɮרt��)
	 * @param string $content  : ���s�񤺮e
	 **/
	function saveFile($filepath , $content) {
		if(!mkdirs( dirname($filepath) )) return false;

		if( !($fp = fopen($filepath,'w')) ) return false;
		fwrite($fp, $content);
		fclose($fp);

		return true;
	}

	// ����@ table ����@ record
	// �Ǧ^�}�C
	// �� dbGetStSr ���P�b�󦹳B������ �Ǯ�DB ( �令�b�~������ )
	function _dbGetStSr($table, $fields, $where){
		global $sysConn;
		return $sysConn->GetRow("SELECT /*! SQL_SMALL_RESULT */ $fields FROM $table WHERE $where");
	}

	/**
	 * �b XML DOM node �U�إ߸`�I
	 * @param object $dom : DOM XML Object
	 * @param object $p_node : DOM XML node object
	 * @param string $name  : ��r�`�I�W��
	 * @param string $value : ��r�`�I���e
	 **/
	function createTextNode(&$dom, &$p_node, $name, $value) {
		$node = $dom->create_element($name);
		$text = $dom->create_text_node($value);
		$text = $node->append_child($text);
		$p_node->append_child($node);
	}
/*******************************************
 * createNewsXML
 * @param int $school_id : �Ǯսs��
 * @return bool : ���\ true, ���� false
 *******************************************/
	function createNewsXML($school_id, $type='news') {
		global $sysConn,$ADODB_FETCH_MODE;
		$run_time = date("Y-m-d H:m");

		$xml_file = sysDocumentRoot ."/base/{$school_id}/system/{$type}.xml";
		$news_xml = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>';
		$news_xml .= "<all{$type} date='{$run_time}'>";
		$news_xml .= "</all{$type}>";

		// ������ Database
        $sysConn->Execute('use ' . sysDBprefix . $school_id);
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$NEWS = $sysConn->Execute("SELECT news_id,board_id FROM WM_news_subject where type='{$type}'");

		if(!$NEWS) {
			saveFile($xml_file, $news_xml);
			return false;
		}

		while (!$NEWS->EOF) {
			$news_id = $NEWS->fields['news_id'];
			$news_board = $NEWS->fields['board_id'];
			$NEWS->MoveNext();
		}

		$dom = domxml_open_mem($news_xml);
		$root = $dom->document_element();
		$root->set_attribute('board'  , $news_board);
		$root->set_attribute('news_id', $news_id);

		$sql =  "SELECT node FROM WM_news_posts where news_id='{$news_id}' and ".
				"(open_time='0000-00-00' or open_time<=NOW()) and (close_time='0000-00-00' or close_time>NOW())";

		$NEWS_POSTS = $sysConn->Execute($sql);
		if(!$NEWS_POSTS->EOF) {
			$nodes = Array();
			while(!$NEWS_POSTS->EOF) {
				$nodes[] = $NEWS_POSTS->fields['node'];
				$NEWS_POSTS->MoveNext();
			}
			$sql1 = 'SELECT node,pt,poster,realname,subject,attach,content from WM_bbs_posts where '.
					"board_id={$news_board} and node in ('" . implode("','", $nodes) . "') order by pt desc limit 3";

			$POSTS = $sysConn->Execute($sql1);
			while(!$POSTS->EOF) {
				$node = $dom->create_element($type);
				$node->set_attribute('node',$POSTS->fields['node']);

				createTextNode($dom, $node,'time'    ,$POSTS->fields['pt']);
				createTextNode($dom, $node,'poster'  ,$POSTS->fields['poster']);
				createTextNode($dom, $node,'realname',$POSTS->fields['realname']);
				createTextNode($dom, $node,'caption' ,$POSTS->fields['subject']);
				createTextNode($dom, $node,'attach'  ,$POSTS->fields['attach']);
				createTextNode($dom, $node,'content' ,$POSTS->fields['content']);
				$root->append_child($node);

				$POSTS->MoveNext();
			}
			$news_xml = $dom->dump_mem(true);
		}

		return saveFile($xml_file, $news_xml);
	}

/*******************************************
 * createFAQXML
 * @param int $school_id : �Ǯսs��
 * @return bool : ���\ true, ���� false
 *******************************************/
	function createFAQXML($school_id, $type='faq') {
		global $sysConn;
		$run_time = date("Y-m-d H:m");

		$xml_file = sysDocumentRoot ."/base/{$school_id}/system/{$type}.xml";
		$news_xml = '<' . '?xml version="1.0" encoding="UTF-8" ?' .'>';
		$news_xml .= "<all{$type} date='{$run_time}'>";
		$news_xml .= "</all{$type}>";

		// ������ Database
		$sysConn->Execute('use ' . sysDBprefix . $school_id);
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$NEWS = $sysConn->Execute("SELECT news_id,board_id FROM WM_news_subject where type='{$type}'");
		if(!$NEWS) {
			saveFile($xml_file, $news_xml);
			return false;
		}

		$news_id    = $NEWS->fields['news_id'];
		$news_board = $NEWS->fields['board_id'];

		$dom = domxml_open_mem($news_xml);
		$root = $dom->document_element();
		$root->set_attribute('board'  , $news_board );
		$root->set_attribute('news_id', $news_id );

		$sql1 = 'SELECT node,hit,pt,poster,realname,subject from WM_bbs_collecting WHERE '.
				"board_id={$news_board} order by hit desc,pt desc limit 3";
		$POSTS = $sysConn->Execute($sql1);

		if ($POSTS) while(!$POSTS->EOF) {

			$node = $dom->create_element($type);
			$node->set_attribute('node',$POSTS->fields['node']);

			createTextNode($dom, $node,'hit'     ,$POSTS->fields['hit']);
			createTextNode($dom, $node,'time'    ,$POSTS->fields['pt']);
			createTextNode($dom, $node,'poster'  ,$POSTS->fields['poster']);
			createTextNode($dom, $node,'realname',$POSTS->fields['realname']);
			createTextNode($dom, $node,'caption' ,$POSTS->fields['subject']);
			$root->append_child($node);

			$POSTS->MoveNext();
		}
		$news_xml = $dom->dump_mem(true);
		return saveFile($xml_file, $news_xml);
	}
?>
