<?php
	/**
	 * 儲存演講廳的設定
	 *
	 * @since   2012/08/17
	 * @author  ShenTing Lin
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	/**
	 * 取得節點內的文字
	 *
	 * @param object $ctx
	 * @param object $node
	 * @param $tagname 節點名稱
	 *
	 * @return string 節點內的文字
	 **/
	function getNodeContext(&$ctx, &$node, $tagname) {
		$res = '';
		$search = $ctx->xpath_eval($tagname . '/text()', $node);
		if (!is_null($search->nodeset) && (count($search->nodeset) > 0)) {
			$res = $search->nodeset[0]->node_value();
		}
		return $res;
	}

	/**
	 * 清除所有資料
	 **/
	function cleanAllData() {
		global $sysConn;

		$sysConn->Execute('TRUNCATE APP_experience_catalog;');
		$sysConn->Execute('TRUNCATE APP_experience_url;');
	}

	/**
	 * 取得現有的 catalog id
	 **/
	function getExistItemId() {
		global $sysConn;

		$data = $sysConn->GetAssoc('select catalog_id, permute FROM APP_experience_catalog');
		return $data;
	}

	/**
	 * 取得現有的 url id
	 **/
	function getExistUrlId($id) {
		global $sysConn;

		$data = $sysConn->GetAssoc("SELECT `idx`, `permute` FROM `APP_experience_url` WHERE `catalog_id` = {$id}");
		return $data;
	}

	/**
	 * 新增/修改 Catalog 的資料
	 *
	 * @param integer $permute 次序
	 * @param Object $node 節點資料
	 * @param Object $ctx xpath_new_context
	 * @param array $orgIds 現有的節點陣列
	 *
	 * @return string APP_experience_catalog 的流水號
	 **/
	function modifyCatalogData($permute, &$node, &$ctx, &$orgIds) {
		global $sysConn;

		$itemId = $node->get_attribute('id');
		$enable = ($node->get_attribute('enable') !== '0') ? '1' : '0';
		// 設定語系
		$title  = getCaption('');
		foreach ($title as $k => $v) {
			$lang = $ctx->xpath_eval('title/' . $k . '/text()', $node);
			if (!is_null($lang->nodeset) && (count($lang->nodeset) > 0)) {
				$title[$k] = $lang->nodeset[0]->node_value();
			}
		}
		// 封面
		$cover = getNodeContext($ctx, $node, 'cover');
		// 描述
		$description = getNodeContext($ctx, $node, 'description');
		// 開始日期
		$beginDate = '0000-00-00 00:00:00';
		// 結束日期
		$endDate = '9999-12-31 23:59:59';

		if (array_key_exists($itemId, $orgIds)) {
			// 更新
			$sysConn->Execute(
				'UPDATE APP_experience_catalog SET caption=?, description=?, cover=?, permute=?' .
					', enable=?, begin_date=?, end_date=?, update_time=NOW() where catalog_id=?',
				array(
					serialize($title), $description, $cover,
					$permute, $enable, $beginDate, $endDate, $itemId
				)
			);
		} else {
			// 新增
			$sysConn->Execute(
				'INSERT INTO APP_experience_catalog (caption, description, cover' .
					', permute, enable, begin_date, end_date, add_time) ' .
					' VALUES(?, ?, ?, ?, ?, ?, ?, NOW())',
				array(
					serialize($title), $description, $cover,
					$permute, $enable, $beginDate, $endDate
				)
			);
			$itemId = $sysConn->Insert_ID();
		}
		return $itemId;
	}

	/**
	 * 新增/修改 url 的資料
	 *
	 * @param array $urls 節點資料
	 * @param Object $ctx xpath_new_context
	 *
	 * @return boolean
	 **/
	function modifyUrlsData($itemId, &$urls, &$ctx) {
		global $sysConn;

		if (is_null($urls) || (count($urls) <= 0)) {
			return false;
		}

		$orgIds = getExistUrlId($itemId);
		$permute = 0;
		$newIds = array();
		foreach ($urls as $node) {
			$permute += 1;
			$idx    = $node->get_attribute('id');
			$enable = ($node->get_attribute('enable') !== '0') ? '1' : '0';
			// 設定語系
			$title  = getCaption('');
			foreach ($title as $k => $v) {
				$lang = $ctx->xpath_eval('title/' . $k . '/text()', $node);
				if (!is_null($lang->nodeset) && (count($lang->nodeset) > 0)) {
					$title[$k] = $lang->nodeset[0]->node_value();
				}
			}
			// 連結
			$link = getNodeContext($ctx, $node, 'link');
			// 開始日期
			$beginDate = '0000-00-00 00:00:00';
			// 結束日期
			$endDate = '9999-12-31 23:59:59';

			// 把這批儲存的idx記錄下來，作為稍後的刪除比對用
			$newIds[] = $idx;

			if (array_key_exists($idx, $orgIds)) {
				// 更新
				$sysConn->Execute(
					'UPDATE APP_experience_url SET catalog_id=?, caption=?, url=?, permute=?' .
						', enable=?, begin_date=?, end_date=?, update_time=NOW() where idx=?',
					array(
						$itemId, serialize($title), $link,
						$permute, $enable, $beginDate, $endDate, $idx
					)
				);
			} else {
				// 新增
				$sysConn->Execute(
					'INSERT INTO APP_experience_url (catalog_id, caption, url' .
						', permute, enable, begin_date, end_date, add_time) ' .
						' VALUES(?, ?, ?, ?, ?, ?, ?, NOW())',
					array(
						$itemId, serialize($title), $link,
						$permute, $enable, $beginDate, $endDate
					)
				);
			}
		}

		// 比對舊的idx是否有在新的idx裡面，若無則表示要刪除
		foreach ($orgIds as $key => $value) {
			if (!in_array($key, $newIds)) {
				$sysConn->Execute("DELETE FROM `APP_experience_url` WHERE `idx` = {$key} LIMIT 1");
			}
		}

		unset($orgIds);
		unset($newIds);

		return true;
	}

// ********** 主程式開始

	$xml = get_magic_quotes_gpc() ? stripslashes($_POST['xml']) : $_POST['xml'];
    $xml = rawurldecode($xml);
	$xmldoc = @domxml_open_mem($xml);

    // 要先執行這行，之後操作才會正確找到資料表
    $sysConn->Execute('USE '.sysDBprefix.$sysSession->school_id);

	if (is_null($xmldoc)) {
		die('fail');
	}

	$ctx = xpath_new_context($xmldoc);
	$ret = $ctx->xpath_eval('//item');

	if (is_null($ret->nodeset) || (count($ret->nodeset) <= 0)) {
		// 清除所有資料
		cleanAllData();
	} else {
		$permute = 0;
		$orgIds  = getExistItemId();

		// 更新資料
		foreach ($ret->nodeset as $node) {
			$permute += 1;
			$itemId = modifyCatalogData($permute, $node, $ctx, $orgIds);
			unset($orgIds[$itemId]);
			// 更新 URL
			$urls = $ctx->xpath_eval('urls/url', $node);
			modifyUrlsData($itemId, $urls->nodeset, $ctx);
		}
		// 清除多餘的資料
		if (is_array($orgIds)) {
			foreach ($orgIds as $k => $v) {
				dbDel('APP_experience_catalog', 'catalog_id=' . $k);
				dbDel('APP_experience_url', 'catalog_id=' . $k);
			}
		}
	}
	echo 'ok';