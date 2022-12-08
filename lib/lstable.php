<?php
	/**
	 * 建立可以翻頁的表格介面函式
	 *
	 * @todo 1. 完成 panel 的功能
	 * @todo 2. 構想如何建立比較簡單的全選全消的方式
	 * @todo 3. 測試排序功能
	 *
	 * @since   2004/02/16
	 * @author  ShenTing Lin
	 * @version $Id: lstable.php,v 1.1 2009-06-25 09:26:49 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/lstable.php');

	function parseVar($str, $data) {
		preg_match_all('/\%([0-9a-zA-Z_]+)/', $str, $regs);
		for ($i = 0; $i < count($regs[1]); $i++) {
			$regs[0][$i] = '/\\' . $regs[0][$i] . '/';
			$regs[1][$i] = $data[$regs[1][$i]];
		}
		return $regs;
	}


	/**
	 * 建立一個 panel，尚未完成
	 **/
	class panel {
		var $datas;
		var $align;
		var $width;
		var $height;
		var $reverse;
		var $wrap;
		var $cssName;
		var $extra;

		function panel() {
			$this->datas   = array();
			$this->align   = 'left';
			$this->width   = '';
			$this->height  = '';
			$this->reverse = false;
			$this->wrap    = false;
			$this->cssName = '';
			$this->extra   = 'border="0" cellspacing="1" cellpadding="3"';
		}

		/**
		 * 加入一個單元，僅供內部呼叫用
		 * @param string $type : 類型
		 * @param string or array $data : 資料
		 * @param integer $idx : 要放置的位置
		 **/
		function add_unit($type, $data, $idx=-1) {
			$idx = (is_int($idx)) ? intval($idx) : -1;
			if ($idx >= count($this->datas)) $idx = -1;
			if ($idx < 0) {
				$this->datas[] = array($type, $data);
			} else {
				$ary = array();
				for ($i = 0; $i < count($this->datas); $i++) {
					if ($i == $idx) $ary[] = array($type, $data);
					$ary[] = $this->datas[$i];
				}
				$this->datas = $ary;
			}
		}

		/**
		 * 加入一個 panel
		 * @param object $value : panel 物件
		 **/
		function add_toolbar($value=null, $idx=-1) {
			if (is_object($value)) $this->add_unit('toolbar', $value, $idx);
		}

		/**
		 * 加入一個 panel
		 * @param object $value : panel 物件
		 **/
		function add_panel($value=null, $idx=-1) {
			if (is_object($value)) $this->add_unit('panel', $value, $idx);
		}

		/**
		 * 取得加入多少個資料
		 * @return integer : 資料長度
		 **/
		function get_count() {
			return count($this->datas);
		}

		/**
		 * 刪除其中一個元件
		 * @param integer $value : 物件的索引值
		 * @return
		 **/
		function delete($value=null) {
			if (is_null($value)) {
				$idx = count($this->datas) - 1;
			} else {
				$idx = intval($value);
			}
			array_splice($this->datas, $idx, 1);
		}

		/**
		 * 顯示 panel
		 * @return
		 **/
		function show() {
			showXHTML_table_B($this->extra);
				showXHTML_tr_B();
					$extra  = 'align="' . $this->align . '"';
					$extra .= (empty($this->width))   ? '' : ' width="' . $this->width   . '"';
					$extra .= (empty($this->height))  ? '' : ' width="' . $this->height  . '"';
					$extra .= (empty($this->cssName)) ? '' : ' class="' . $this->cssName . '"';
					$extra .= ($this->wrap) ? '' : ' nowrap="noWrap"';

					showXHTML_td_B($extra);
						$ary = ($this->reverse) ? array_reverse($this->datas) : $this->datas;
						foreach ($ary as $val) {
							switch ($val[0]) {
								case 'toolbar':
									$val[1]->show();
									break;
								case 'panel'  :
									showXHTML_td_E();
									showXHTML_td_B();
									$val[1]->show();
									showXHTML_td_E();
									showXHTML_td_B();
									break;
								default:
							}
						}
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		}
	}

	/**
	 * 建立一個表單元件的集合，內部不含有 table 的 tag
	 **/
	class toolbar {
		var $datas;
		var $reverse;
		var $lstary;
		var $appendID;
		var $appendStr;

		function toolbar() {
			$this->datas     = array();
			$this->reverse   = false;
			$this->lstary    = array();
			$this->appendID  = false;
			$this->appendStr = '';
		}

		/**
		 * 加入一個單元，僅供內部呼叫用
		 * @param string $type : 類型
		 * @param string or array $data : 資料
		 * @param integer $idx : 要放置的位置
		 **/
		function add_unit($type, $data, $idx=-1) {
			$idx = (is_int($idx)) ? intval($idx) : -1;
			if ($idx >= count($this->datas)) $idx = -1;
			if ($idx < 0) {
				$this->datas[] = array($type, $data);
			} else {
				$ary = array();
				for ($i = 0; $i < count($this->datas); $i++) {
					if ($i == $idx) $ary[] = array($type, $data);
					$ary[] = $this->datas[$i];
				}
				$this->datas = $ary;
			}
		}

		/**
		 * 新增一個表單元件
		 * @param
		 **/
		function add_input($type, $id='', $value='', $default='', $extra='', $separator='', $idx=-1) {
			$ary = array($type, $id, $value, $default, $extra, $separator);
			$this->add_unit('input', $ary, $idx);
		}

		/**
		 * 新增一段文字
		 * @param string $value : 要顯示的文字
		 **/
		function add_caption($value='', $idx=-1) {
			$this->add_unit('caption', $value, $idx);
		}

		/**
		 * 新增一個分隔線
		 * @param integer $value : 分隔線的高度
		 **/
		function add_separator($value=16, $idx=-1) {
			$value = '<span style="margin: 0px 0px 0px 4px; border-bottom: 0px none #696969; border-left: 1px solid #696969; border-right: 0px none #696969; border-top: 0px none #696969; font-size: ' . $value . 'px;">&nbsp;</span>';
			$this->add_unit('caption', $value, $idx);
		}

		/**
		 * 取得加入多少個資料
		 * @return integer : 資料長度
		 **/
		function get_count() {
			return count($this->datas);
		}

		/**
		 * 刪除其中一個元件
		 * @param integer $value : 物件的索引值
		 * @return
		 **/
		function delete($value=null) {
			if (is_null($value)) {
				$idx = count($this->datas) - 1;
			} else {
				$idx = intval($value);
			}
			array_splice($this->datas, $idx, 1);
		}

		/**
		 * 顯示 toolbar
		 * @return
		 **/
		function show($param=null) {
			global $sysSession, $MSG;
			$ary = ($this->reverse) ? array_reverse($this->datas) : $this->datas;
			foreach ($ary as $val) {
				switch ($val[0]) {
					case 'input':
						if (is_array($param)) {
							for ($i = 2; $i < 5; $i++) {
								$regs = parseVar($val[1][$i], $param);
								$val[1][$i] = preg_replace($regs[0], $regs[1], $val[1][$i]);
							}
						}
						if ($this->appendID) $val[1][4] .= ' id="' . $val[1][1] . $this->appendStr . '" ';
						call_user_func_array('showXHTML_input', $val[1]);
						break;
					case 'caption':
						echo $val[1];
						break;
					default:
				}
			}
		}
	}

	/**
	 * 建立一個可翻頁與排序的表格
	 **/
	class table {
		var $help;
		var $deftool;
		var $selbtn;
		var $toolbar;
		var $fields;
		var $sortby;
		var $sqls;
		var $appendDefID;

		var $display;
		var $extra;

		function table() {
			$this->help       = array();
			$this->deftool    = new toolbar();
			$this->selbtn     = array();
			$this->toolbar    = array();
			$this->fields     = array();
			$this->sortby     = array();
			$this->sqls       = array();
			$this->display    = array(
					'tool'        => false,    // true: mirror, false: nothing
					'select'      => false,    // 要不要顯示全選或全消的按鈕
					'page'        => true,
					'sort'        => false,
					'page_num'    => false,
					'page_limit'  => 10,
					'page_method' => 'step',   // step: 用跳頁的方式, range: 區間, 其他: 全列
					'line'        => 10,
					'page_no'     => 1,
					'index'       => 1,
					'sortby'      => '',
					'order'       => 'asc',
					'page_func'   => 'null',
					'sort_func'   => 'null',
					'no_data'     => '',
					'help_class'  => 'cssTrHelp',
			        'total_count' => 0   // 資料筆數
				);
			$this->extra       = '';
			$this->appendDefID = false;
		}

		/**
		 * 加入說明
		 * @param string $value : 說明文字
		 **/
		function add_help($value) {
			$this->help[] = $value;
		}

		/**
		 * 加入沒有資料時顯示的文字
		 * @param string $value : 要顯示的文字
		 **/
		function add_no_data_help($value) {
			$this->display['no_data'] = $value;
		}

		/**
		 * 加入工具列
		 * @param object $value : 工具列物件
		 * @param string $pos : 位置
		 *     top    : 上面
		 *     bottom : 下面
		 *     both   : 上下都要
		 * @return
		 **/
		function add_toolbar($value, $pos='top') {
			if (is_object($value)) $this->toolbar[] = array($pos, $value);
		}

		/**
		 * 設定預設的工具列
		 * @param object $value : panel 物件
		 **/
		function set_def_toolbar($value) {
			$this->deftool = $value;
		}

		/**
		 * 啟動全選或全消的按鈕
		 *     系統會自動在 $id 之後加上 1 跟 2
		 * @param
		 * @return
		 **/
		function set_select_btn($val=true, $id='', $value='', $extra='') {
			global $sysSession, $MSG;
			$this->display['select'] = $val;
			if ($val) {
				$this->selbtn = array($id, $value, $extra);
			}
		}
		/**
		 * 加入一個排序
		 * @param string $key  : 排序的索引
		 * @param string $asc  : 遞增的設定
		 * @param string $desc : 遞減的設定
		 * @return
		 **/
		function add_sort($key, $asc, $desc) {
			$this->sortby[$key] = array($asc, $desc);
		}

		/**
		 * 設定是否啟用排序，並且設定預設的排序條件
		 * @param boolean $value  : true : 啟用，false : 不啟用
		 * @param string  $sortby : 排序的欄位
		 * @param string  $order  : asc : 遞增排序，desc : 遞減排序
		 **/
		function set_sort($value=true, $sortby='', $order='asc', $func='') {
			$this->display['sort']      = $value;
			$this->display['sortby']    = $sortby;
			$this->display['order']     = $order;
			$this->display['sort_func'] = $func;
		}

		function get_sort() {
			return array($this->display['sortby'], $this->display['order']);
		}

		/**
		 * 設定 SQL 指令
		 **/
		function set_sqls($table, $fields, $where) {
			$this->sqls = array($table, $fields, $where);
		}

		/**
		 * 設定要不要顯示翻頁，並且每頁顯示幾筆
		 * @param boolean $value : true : 翻頁，false : 不翻頁
		 * @param integer $line  : 每頁幾筆
		 * @return
		 **/
		function set_page($value=true, $def=1, $line=10, $func='') {
			$this->display['page'] = $value;
			$line = intval($line);
			if ($line < 10) $line = 10;
			$this->display['line']      = $line;
			$this->display['page_no']   = $def;
			$this->display['page_func'] = $func;
		}

		function get_page() {
			return $this->display['page_no'];
		}

		function get_index() {
			$idx = ($this->display['page_no'] == 0) ? 0 : intval($this->display['page_no'] - 1) * $this->display['line'];
			$idx += $this->display['index'];
			return $idx;
		}
		/**
		 * 設定下排工具列的顯示順序
		 * @param boolean $value : true : mirror，false : nothing
		 **/
		function set_mirror_toolbar($value=true) {
			$this->display['tool'] = $value;
		}

		/**
		 * 資料顯示的定義陣列
		 *     $value = array(caption, title,  sortby, fields, user func, extra);
		 *
		 *     caption：string(標題) 或 array(按鈕陣列)
		 *     title：string，浮動式說明
		 *     sortby：string，這邊即是上面 $OB 的 key，也就是你想要排序哪個欄位
		 *     fields：string，%n，想要顯示哪個欄位的資料，這是依 SQL 指令取出的欄位順序
		 *     %n：從 0 開始，不限制只能有一個。
		 *         例如：%0(%1) -> fields1(fields2)
		 *     user func：string，對於取出來的資料想要自己怎麼處理
		 *     extra：string，這是該欄位的其他資料
		 *
		 * @param
		 * @return
		 **/
		function add_field($caption, $title='', $sortby='', $fields='', $func='', $extra='') {
			$this->fields[] = array($caption, $title, $sortby, $fields, $func, $extra);
		}

		/**
		 * 翻頁的 JavaScript
		 * @param integer $total  : 總頁數
		 * @param integer $pageNo : 目前所在頁數
		 **/
		function jsPage($total=1, $pageNo=1) {
			$total  = intval($total);
			$pageNo = intval($pageNo);
			$str  = ($this->display['sort']) ? '"?sortby=" + sb + "&order=" + od + "&' : '"?';
			$str .= 'page=" + pg';
			$func = (empty($this->display['page_func'])) ? '' : 'str += ' . $this->display['page_func'] . ';';
			$js   = <<< BOF

	var total_page = "{$total}";
	var pg = "{$pageNo}";

	/**
	 * 翻頁
	 * @param integer n : 動作別或頁數
	 **/
	function go_page(n){
		var str = location.pathname;
		var res = '';
		switch(n){
			case -1:	// 第一頁
				pg = 1;
				break;
			case -2:	// 前一頁
				pg = parseInt(pg) - 1;
				if (parseInt(pg) == 0) pg = 1;
				break;
			case -3:	// 後一頁
				pg = parseInt(pg) + 1;
				break;
			case -4:	// 最末頁
				pg = parseInt(total_page);
				break;
			default:	// 指定某頁
				pg = parseInt(n);
				break;
		}
		str += {$str};

		//  判斷是否有設定一頁顯示幾筆
		if (typeof(page_num) != 'undefined'){
			str += '&page_num='+page_num;
		}
		{$func}
		location.replace(str);
	}
BOF;
			return $js;
		}

		/**
		 * 每頁幾筆的 JavaScript
		 **/
		function jsPageNum() {
			$lines = $this->display['line'];
			$js   = <<< BOF
	if (typeof Page_Row == "undefined")
	{
		eval(
			'var page_num = {$lines};' +
			'function Page_Row(val)' +
			'{' +
			'    page_num = val;' +
			'    go_page(-1);' +
			'}'
		);
	}

BOF;
			return $js;
		}

		/**
		 * 排序的 JavaScript
		 * @param string $sortby : 那個欄位要排序
		 * @param string $order  : asc : 遞增，desc : 遞減
		 **/
		function jsSort($sortby, $order) {
			$str  = ($this->display['page']) ? '"?page=" + pg + "&' : '"?';
			$str .= 'sortby=" + sb + "&order=" + od';
			$func = (empty($this->display['sort_func'])) ? '' : 'str += ' . $this->display['sort_func'] . ';';
			$js = <<< BOF

	var sb = "{$sortby}";
	var od = "{$order}";

	function trim(val) {
		var re = /\s/g;
		val = val.replace(re, '');
		return val;
	}

	function sortBy(val) {
		var tmp = location.href.split("?");
		var str = tmp[0];
		var res = '';
		var re = /asc/ig;

		if (trim(sb) == val) {
			od = (re.test(od)) ? 'desc' : 'asc';
		}
		sb = val;
		str += {$str};

		//  判斷是否有設定一頁顯示幾筆
		if (typeof(page_num) != 'undefined'){
			str += '&page_num='+page_num;
		}
		{$func}
		location.replace(str);
	}
BOF;
			return $js;
		}

		function show() {
			global $sysConn, $sysSession, $_POST, $_GET, $MSG;

			$js   = '';
			$sqls = '';

			// 處理排序 (Begin)
			if (count($this->sortby) <= 0) $this->display['sort'] = false;
			if ($this->display['sort']) {
				// 取得排序的順序是遞增或遞減
				$asc   = $this->display['order'];
				$order = empty($asc) ? 'asc' : $asc;
				if (isset($_POST['order']))
				$order = trim($_POST['order']);
				else if (isset($_GET['order']))
					$order = trim($_GET['order']);
				if (empty($order)) $order = 'asc';
				$this->display['order']  = $order;

				// 取得排序的欄位
				$sortby = $this->display['sortby'];
				if (isset($_POST['sortby']))
					$sortby = trim($_POST['sortby']);
				else if (isset($_GET['sortby']))
					$sortby = trim($_GET['sortby']);

				if (array_key_exists($sortby, $this->sortby)) {
					$val = $this->sortby[$sortby];
				} else {
					list($key, $val) = each($this->sortby);
				}
				$sb = ($order == 'asc') ? $val[0] : $val[1];
				$this->display['sortby'] = $sortby;

				// JavaScript
				$js .= $this->jsSort($sortby, $order);

				// SQL 指令
				$sqls .= " order by {$sb} ";
			}
			// 處理排序 (End)

			// 處理翻頁 (Begin)
			if ($this->display['page']) {
				// 計算總共有幾筆資料
				if (strpos(strtolower($this->sqls[1]), 'distinct') !== false || strpos(strtolower($this->sqls[2]), 'union all') !== false)
				{
					//  欄位有distinct
					$TRS = dbGetStMr($this->sqls[0], $this->sqls[1], $this->sqls[2], ADODB_FETCH_NUM);
					$total_msg = $TRS->RecordCount();
				}
				else if (strpos(strtolower($this->sqls[2]), 'group by') === false)
				{   // 沒有 Group By
					list($total_msg) = dbGetStSr($this->sqls[0], 'count(*) AS cnt', $this->sqls[2], ADODB_FETCH_NUM);
				}
				else
				{   // 有 Group By
					$TRS = dbGetStMr($this->sqls[0], '1', $this->sqls[2], ADODB_FETCH_NUM);
					$total_msg = $TRS->RecordCount();
				}
				if ($total_msg <= 0) $total_msg = 0;
				// $total_msg = intval($total_msg);

				// 計算總共分幾頁
				$lines = intval($this->display['line']);
				if ($this->display['page_num'])
				{
					if (isset($_POST['page_num']))
					{
						$lines = intval($_POST['page_num']);
					}
					else if (isset($_GET['page_num']))
					{
						$lines = intval($_GET['page_num']);
					}
					$js .= $this->jsPageNum();
				}
				if ($lines < 10) $lines = 10;
				$total_page = ceil($total_msg / $lines);

				// 設定下拉換頁選單顯示第幾頁
				$page_no = $this->display['page_no'];
				if (isset($_POST['page']))
					$page_no = intval($_POST['page']);
				else if (isset($_GET['page']))
					$page_no = intval($_GET['page']);
				if (($page_no < 0) || ($page_no > $total_page)) $page_no = $total_page;
				$this->display['page_no'] = $page_no;
				/*
				if ($total_page == 1) {
					$total_page = 0;
					$page_no = 0;
				}
				*/

				// 產生下拉換頁選單
				$all_page[0] = $MSG['all_page'][$sysSession->lang];
				// 解決頁數下拉框陣列過大 (Begin)
				$page_limit = intval($this->display['page_limit']);
				$c1   = max($page_no - $page_limit, 1); // 目前頁數前 10 頁
				$c2   = min($page_no + $page_limit, $total_page); // 目前頁數後 10 頁
				$lb = $page_no - $page_limit;
				$ub = $page_no + $page_limit;

				if ($this->display['page_method'] == 'step')
				{
					if ($total_page > 21)
					{
					    $all_page[1] = 1; // 第一頁一定要有

						$x = $page_no - 10;
						// 從第一頁到目前頁的前 10 頁，之間的每隔 10 頁
						for ($i=10; $i<$x; $i+=10) $all_page[$i] = $i;

						$y = $page_no + 10; $z = min($total_page-1,$y);
						// 目前頁的前後 10 頁
						for ($i=max(2,$x); $i<=$z; $i++) $all_page[$i] = $i;

                        // 從目前頁的後 10 頁到最後一頁，之間的每隔 10 頁
					    for ($i=(int)ceil($y/10)*10; $i<$total_page; $i+=10) $all_page[$i] = $i;

					    $all_page[$total_page] = $total_page; // 最後一頁一定要有
					}
					else
					{
					    $all_page = range(0, $total_page);
						$all_page[0] = $MSG['all_page'][$sysSession->lang];
					}
				}
				else if ($this->display['page_method'] == 'range')
				{
					$b1   = min($c1, $page_limit + 2);
					$b2   = max($c2 + 1, $total_page - $page_limit);
					if ($c1 > 1)
						for ($i = 1; $i < $b1; $i++)
							$all_page[$i] = $i;

					if ($c1 > $b1)
						$all_page[$i] = ' ... ';

					for ($i = $c1; $i <= $c2; $i++)
						$all_page[$i] = $i;

					if (($b2 - 1) > $c2)
						$all_page[$b2 - 1] = ' ... ';

					if ($c2 < $total_page)
						for ($i = $b2; $i <= $total_page; $i++)
							$all_page[$i] = $i;
				}
				else
				{
					for ($i = 1; $i <= $total_page; $i++) $all_page[] = $i;
				}
				// 解決頁數下拉框陣列過大 (End)

				// JavaScript
				$js .= $this->jsPage($total_page, $page_no);

				// SQL 指令
				if (!empty($page_no)) {
					$limit = intval($page_no - 1) * $lines;
					$sqls .= " limit {$limit}, {$lines} ";
				}

				// 翻頁按鈕 (Begin)
				$this->deftool->add_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
				$this->deftool->add_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
				$this->deftool->add_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);
				$this->deftool->add_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''), '', 0);

				if ($this->display['page_num'])
				{
					$page_array = array(sysPostPerPage=> $MSG['default'][$sysSession->lang],20 => 20,50 => 50,100 => 100,200 => 200,400 => 400);
					$this->deftool->add_input('select', 'page_num', $page_array, $lines, 'class="cssInput" onchange="Page_Row(this.value);" ', '', 0);
					$this->deftool->add_caption($MSG['every_page'][$sysSession->lang], 0);
				}

				$this->deftool->add_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"', '', 0);
				$this->deftool->add_caption($MSG['page_no'][$sysSession->lang], 0);
				// 翻頁按鈕 (End)
			}
			// 處理翻頁 (End)


			// 開始輸出 (Begin)
			if (!empty($js)) showXHTML_script('inline', $js);
			$colspan = count($this->fields);
			showXHTML_table_B($this->extra);
				// 顯示說明 (Begin)
				foreach ($this->help as $val) {
					showXHTML_tr_B('class="' . $this->display['help_class'] . '"');
						showXHTML_td('colspan="' . $colspan . '"', $val);
					showXHTML_tr_E();
				}
				// 顯示說明 (End)

				// 顯示上排工具列 (Begin)
					// 處理全選全消的按鈕
				if ($this->display['select']) {
					if ($this->appendDefID)
					{
						$id = $this->selbtn[0];
						$this->deftool->add_input('button', $id, $this->selbtn[1], '', $this->selbtn[2], '', 0);
					}
					else
					{
						$id = $this->selbtn[0] . '1';
						$this->deftool->add_input('button', $id, $this->selbtn[1], '', 'id="' . $id . '"' . $this->selbtn[2], '', 0);
					}
				}

				if ($this->deftool->get_count() > 0) {
					$this->toolbar['default'] = array('both', $this->deftool);
					if ($this->appendDefID)
					{
						$this->toolbar['default'][1]->appendID  = true;
						$this->toolbar['default'][1]->appendStr = '1';
					}
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				$tIdx = 0;
				foreach ($this->toolbar as $val) {
					if ($val[0] == 'bottom') continue;
					// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="' . $colspan . '" id="tools1' . ++$tIdx . '"');
							$val[1]->extra = 'border="0" cellspacing="0" cellpadding="0" ' . $col;
							$val[1]->show();
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				// 顯示上排工具列 (End)

				// 顯示標題 (Begin)
				showXHTML_tr_B('class="cssTrHead"');
				foreach ($this->fields as $val) {
					if (is_object($val[0])) {
						$title = empty($val[1]) ? '' : ' title="' . strip_tags($val[1]) . '" ';
						showXHTML_td_B('align="center"' . $title);
							$val[0]->show();
						showXHTML_td_E();
					} else {
						$title = empty($val[1]) ? 'title="' . strip_tags($val[0]) . '"' : ' title="' . strip_tags($val[1]) . '" ';
						if (!$this->display['sort'] || empty($val[2])) {
							showXHTML_td('align="center"' . $title, $val[0]);
						} else {
							showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(\'' . $val[2] . '\');"' . $title);
								echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
								echo trim($val[0]);
								echo '</a>';
								$icon_up = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001up.gif">';
								$icon_dw = '&nbsp;<img border="0" align="asbmiddle" src="/theme/' . $sysSession->theme . '/' . $sysSession->env . '/dude07232001down.gif">';
								echo ($sortby == $val[2]) ? ($order == 'asc' ? $icon_up : $icon_dw) : '';
								// echo ($sortby == $val[2]) ? ($order == 'asc' ? '&and;' : '&or;') : '';
							showXHTML_td_E('');
						}
					}
				}
				showXHTML_tr_E();
				// 顯示標題 (End)

				// 顯示資料 (Begin)
    		    $this->display['index'] = 1;
    		    $col = 'class="cssTrOdd"';
    		    $RS = dbGetStMr($this->sqls[0], $this->sqls[1], $this->sqls[2] . $sqls, ADODB_FETCH_BOTH);
    		    if ($sysConn->ErrorNo() <= 0) {
    		        $cnt = $RS->RecordCount();
    		        if (intval($cnt) == 0) {
    		            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
    		            showXHTML_tr_B($col);
    		            showXHTML_td('align="center" colspan="' . $colspan . '"', $MSG['no_data'][$sysSession->lang]);
    		            showXHTML_tr_E();
    		        } else {
    		            while (!$RS->EOF) {
    		                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
    		                showXHTML_tr_B($col);
    		                foreach ($this->fields as $val) {
    		                    $regs = parseVar($val[5], $RS->fields);
    		                    showXHTML_td_B(preg_replace($regs[0], $regs[1], $val[5]));
    		                    if (is_object($val[3])) {
    		                        $val[3]->show($RS->fields);
    		                    } else {
    		                        $regs = parseVar($val[3], $RS->fields);
    		                        // 輸出資料 (Begin)
    		                        if (empty($val[4]) || !function_exists($val[4])) {
    		                            echo preg_replace($regs[0], $regs[1], $val[3]);
    		                        } else {
    		                            // 呼叫使用者自訂的函式
    		                            echo call_user_func_array($val[4], $regs[1]);
    		                        }
    		                        // 輸出資料 (End)
    		                    }
    		                    showXHTML_td_E('');
    		                }
    		                showXHTML_tr_E();
    		                $this->display['index']++;
    		                $RS->MoveNext();
    		            } // End while (!$RS->EOF)
    		        } // End if (intval($cnt) == 0)
    		    } else {
    		        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
    		        showXHTML_tr_B($col);
    		        showXHTML_td('align="center" colspan="' . $colspan . '"', $MSG['msg_sql_error'][$sysSession->lang]);
    		        showXHTML_tr_E();
    		    }
    		    // 顯示資料 (End)

				// 顯示下排工具列 (Begin)
					// 處理全選全消的按鈕
				if ($this->display['select'] && !$this->appendDefID) {
					$this->deftool->delete(0);
					$id = $this->selbtn[0] . '2';
					$this->deftool->add_input('button', $id, $this->selbtn[1], '', 'id="' . $id . '"' . $this->selbtn[2], '', 0);
				}

				if ($this->deftool->get_count() > 0) {
					$this->toolbar['default'] = array('both', $this->deftool);
					if ($this->appendDefID)
					{
						$this->toolbar['default'][1]->appendID  = true;
						$this->toolbar['default'][1]->appendStr = '2';
					}
				}


				$ary = ($this->display['tool']) ? array_reverse($this->toolbar) : $this->toolbar;
				$tIdx = ($this->display['tool']) ? 0 - intval($tIdx) : 0;
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				foreach ($ary as $val) {
					if ($val[0] == 'top') continue;
					// $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="' . $colspan . '" id="tools2' . abs(++$tIdx) . '"');
							$val[1]->extra = 'border="0" cellspacing="0" cellpadding="0" ' . $col;
							$val[1]->show();
						showXHTML_td_E();
					showXHTML_tr_E();
				}
				// 顯示下排工具列 (End)
			showXHTML_table_E();
			// 開始輸出 (End)
		}
		
		/**
		 * 取得資料列
		 */
		function getDatalistView() {
		    global $sysConn, $sysSession, $MSG;
		    $this->display['index'] = 1;
		    $sqls = '';
		    // 處理排序 (Begin)
		    if (count($this->sortby) <= 0) $this->display['sort'] = false;
		    if ($this->display['sort']) {
		        // 取得排序的順序是遞增或遞減
		        $asc   = $this->display['order'];
		        $order = empty($asc) ? 'asc' : $asc;
		        if (isset($_POST['order']))
		            $order = trim($_POST['order']);
		        else if (isset($_GET['order']))
		            $order = trim($_GET['order']);
		        if (empty($order)) $order = 'asc';
		        $this->display['order']  = $order;
		    
		        // 取得排序的欄位
		        $sortby = $this->display['sortby'];
		        if (isset($_POST['sortby']))
		            $sortby = trim($_POST['sortby']);
		        else if (isset($_GET['sortby']))
		            $sortby = trim($_GET['sortby']);
		    
		        if (array_key_exists($sortby, $this->sortby)) {
		            $val = $this->sortby[$sortby];
		        } else {
		            list($key, $val) = each($this->sortby);
		        }
		        $sb = ($order == 'asc') ? $val[0] : $val[1];
		        $this->display['sortby'] = $sortby;
		    
		        // SQL 指令
		        $sqls .= " order by {$sb} ";
		    }
		    // 處理排序 (End)
		    
		    // 處理翻頁 (Begin)
		    if ($this->display['page']) {
		        // 計算總共有幾筆資料
		        if (strpos(strtolower($this->sqls[1]), 'distinct') !== false)
		        {
		            //  欄位有distinct
		            $TRS = dbGetStMr($this->sqls[0], $this->sqls[1], $this->sqls[2], ADODB_FETCH_NUM);
		            $total_msg = $TRS->RecordCount();
		        }
		        else if (strpos(strtolower($this->sqls[2]), 'group by') === false)
		        {   // 沒有 Group By
		            list($total_msg) = dbGetStSr($this->sqls[0], 'count(*) AS cnt', $this->sqls[2], ADODB_FETCH_NUM);
		        }
		        else
		        {   // 有 Group By
		            $TRS = dbGetStMr($this->sqls[0], '1', $this->sqls[2], ADODB_FETCH_NUM);
		            $total_msg = $TRS->RecordCount();
		        }
		        if ($total_msg <= 0) $total_msg = 0;
		        
		        $this->display['total_count'] = $total_msg;
		    }

		    $lines = intval($this->display['line']);
		    if ($lines < 10) $lines = 10;
		    $total_page = ceil($total_msg / $lines);
		    
		    // 設定下拉換頁選單顯示第幾頁
		    $page_no = $this->display['page_no'];
		    if (isset($_POST['page']))
		        $page_no = intval($_POST['page']);
		    else if (isset($_GET['page']))
		        $page_no = intval($_GET['page']);
		    if (($page_no < 0) || ($page_no > $total_page)) $page_no = 1;
		    $this->display['page_no'] = $page_no;

		    // SQL 指令
		    if (!empty($page_no)) {
		        $limit = intval($page_no - 1) * $lines;
		        $sqls .= " limit {$limit}, {$lines} ";
		    }
		    
		    $RS = dbGetStMr($this->sqls[0], $this->sqls[1], $this->sqls[2] . $sqls, ADODB_FETCH_BOTH);
		    $datalist = array();
		    if ($sysConn->ErrorNo() <= 0) {
		        $cnt = $RS->RecordCount();
		        if (intval($cnt) == 0) {
		            return array();
		        } else {
		            while (!$RS->EOF) {
		                $rowData = array();
		                // $myTable->add_field($MSG['attachment'][$sysSession->lang], '', '', '%attachment      '      , 'showAttach'   , 'align="center" nowrap="noWrap"');
		                foreach ($this->fields as $val) {
		                    if (is_object($val[3])) {
		                        continue;
		                    }
	                        $regs = parseVar($val[3], $RS->fields);
	                        if (empty($val[4]) || !function_exists($val[4])) {
	                            $rowData[] = preg_replace($regs[0], $regs[1], $val[3]);
	                        } else {
	                            // 呼叫使用者自訂的函式
	                            $rowData[] = call_user_func_array($val[4], $regs[1]);
	                        }
		                }
		                $datalist[] = $rowData;
		                $RS->MoveNext();
		            } // End while (!$RS->EOF)
		        } // End if (intval($cnt) == 0)
		    } else {
		        return false;
		    }
		    
		    return $datalist;
		}
	}
?>
