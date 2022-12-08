<?php
	/**
	 * 讀取試聽課程資料
	 *
	 * @since   2012/08/09
	 * @author  ShenTing Lin
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

/*
	節點格式
	<item id="" enable="">
		<title>
			<Big5></Big5>
			<GB2312></GB2312>
			<en></en>
			<EUC-JP></EUC-JP>
			<user_define></user_define>
		</title>
		<description></description>
		<cover></cover>
		<begin_date></begin_date>
		<end_date></end_date>
		<urls>
			<url id="" enable="">
				<title>
					<Big5></Big5>
					<GB2312></GB2312>
					<en></en>
					<EUC-JP></EUC-JP>
					<user_define></user_define>
				</title>
				<link></link>
				<begin_date></begin_date>
				<end_date></end_date>
			</url>
		</urls>
	</item>
*/
	function buildCaption(&$doc, &$caption) {
		$title = $doc->create_element('title');
		$lang = getCaption($caption);
		foreach ($lang as $k => $v) {
			$l = $doc->create_element($k);
			$l->append_child($doc->create_text_node(htmlspecialchars_decode($v)));
			$title->append_child($l);
		}
		return $title;
	}

	function buildUrls(&$doc, &$id) {
		$rs = dbGetStMr(
			'APP_experience_url',
			'idx, caption, url, enable, begin_date, end_date',
			'catalog_id=' . $id . ' ORDER BY permute ASC',
			ADODB_FETCH_ASSOC
		);
		$urls = $doc->create_element('urls');
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$url  = $doc->create_element('url');
				$url->set_attribute('id'    , $row['idx']);
				$url->set_attribute('enable', $row['enable']    );

				// title
				$title = buildCaption($doc, $row['caption']);
				$url->append_child($title);

				// url
				$node = $doc->create_element('link');
				$node->append_child($doc->create_text_node($row['url']));
				$url->append_child($node);

				// begin_date
				$node = $doc->create_element('begin_date');
				$node->append_child($doc->create_text_node($row['begin_date']));
				$url->append_child($node);

				// end_date
				$node = $doc->create_element('end_date');
				$node->append_child($doc->create_text_node($row['end_date']));
				$url->append_child($node);

				$urls->append_child($url);
			}
		}
		return $urls;
	}

	function buildItem(&$doc, &$row) {
		$item  = $doc->create_element('item');
		$item->set_attribute('id'    , $row['catalog_id']);
		$item->set_attribute('enable', $row['enable']    );

		// title
		$title = buildCaption($doc, $row['caption']);
		$item->append_child($title);

		// description
		$node = $doc->create_element('description');
		$node->append_child($doc->create_text_node($row['description']));
		$item->append_child($node);

		// cover
		$node = $doc->create_element('cover');
		$node->append_child($doc->create_text_node($row['cover']));
		$item->append_child($node);

		// begin_date
		$node = $doc->create_element('begin_date');
		$node->append_child($doc->create_text_node($row['begin_date']));
		$item->append_child($node);

		// end_date
		$node = $doc->create_element('end_date');
		$node->append_child($doc->create_text_node($row['end_date']));
		$item->append_child($node);

		// urls
		$node = buildUrls($doc, $row['catalog_id']);
		$item->append_child($node);

		return $item;
	}

	$xml = '<?xml version="1.0" encoding="UTF-8"?><manifest></manifest>';
	$xmldoc = @domxml_open_mem($xml);
	if (!is_null($xmldoc)) {
		$ctx = xpath_new_context($xmldoc);
		$ret = $ctx->xpath_eval('//manifest');
		$org = $ret->nodeset[0];
		$doc = $org->owner_document();

		$rs = dbGetStMr(
			'APP_experience_catalog',
			'catalog_id, caption, description, cover, permute, enable, begin_date, end_date',
			'1=1 ORDER BY permute ASC',
			ADODB_FETCH_ASSOC
		);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$item = buildItem($doc, $row);
				$org->append_child($item);
			}
		}
	}

	header('Content-type: application/xml; charset=UTF-8');
	echo $xmldoc->dump_mem(true, 'UTF-8');