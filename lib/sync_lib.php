<?php
	/**
	 * 取得校方資料庫表格資料，匯入 WM 表格的處理類別
	 *
	 * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      wiseguy liang <wiseguy@sun.net.tw>
	 * @copyright   2000-2008 SunNet Tech. INC.
	 * @version     CVS: $Id: sync_lib.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2009-02-10
	 *
	 * 備註：
	 */

define('WM_DOCUMENT_ROOT', dirname(dirname(__FILE__)));
include_once(WM_DOCUMENT_ROOT . '/config/sys_config.php');
include_once(WM_DOCUMENT_ROOT . '/config/login.config');
include_once(WM_DOCUMENT_ROOT . '/lib/adodb/adodb.inc.php');

define('OTHDB_SERVER_HOST', OTHDB_SERVER_ADDR . ':' . OTHDB_SERVER_PORT);

class TableSync
{
	var $_has_init = false;
	var $source = array('db_type'    => OTHDB_SERVER_TYPE,            // DB 種類 (adodb 支援的都能用)
						'db_host'    => OTHDB_SERVER_HOST,			  // DB IP
						'db_user'    => OTHDB_USERNAME,               // DB 存取帳號
						'db_passwd'  => OTHDB_PASSWORD,               // DB 存取密碼
						'db_name'    => OTHDB_DATABASE,               // DB 資料庫名
						'table_name' => '',                           // 資料表名
						'fields'     => array('username',             // 要匯入的欄位名
											  'password',             // ※注意，欄位名稱、順序不見得與WM一樣，
											  'gender',               //   須依照WM要 insert 的順序排列。
											  'email',
											  'first_name',
											  'last_name'
											 ),
						'field_quote_left'  => '',                    // 欄位左括號。
						'field_quote_right' => '',                    // 欄位右括號。
						'sqls'       => 'select %s from %s where %s', // 取資料的 sql 指令
						'pre_sqls'   => '',                           // 取資料前要先下的 sql 指令
						'post_sqls'  => '',                           // 取資料後要下的 sql 指令
						'where'      => '1=1'                         // 取資料的 sql 條件
						);
	var $target = array('db_type'    => sysDBtype,                    // DB 種類
						'db_host'    => sysDBhost,                    // DB IP
						'db_user'    => sysDBaccoount,                // DB 存取帳號
						'db_passwd'  => sysDBpassword,                // DB 存取密碼
						'db_name'    => sysDBname,                    // DB 資料庫名
						'table_name' => '',                           // 資料表名
						'fields'     => array('username'   => '',     // 要匯入的欄位名，及其轉換函式
											  'password'   => 'md5',
											  'gender'     => array('TableSync', '_mapGender'),
											  'email'      => '',
											  'first_name' => '',
											  'last_name'  => ''
											 ),
						'pre_sqls'   => '',                           // 寫資料前要先下的 sql 指令
						'post_sqls'  => ''                            // 寫資料後要下的 sql 指令
						);

	/**
	 * 啟始兩邊資料庫。若已啟始就 reuse
	 */
	function init($fetch_mode=ADODB_FETCH_NUM)
	{
		global $ADODB_FETCH_MODE, $ADODB_COUNTRECS;

		$ADODB_FETCH_MODE = $fetch_mode;
		$ADODB_COUNTRECS  = true;

		if (!$this->_has_init)
		{
			$GLOBALS['SOURCE_CONN'] = &ADONewConnection($this->source['db_type']);
			$GLOBALS['SOURCE_CONN']->Connect($this->source['db_host'],
											 $this->source['db_user'],
											 $this->source['db_passwd'],
											 $this->source['db_name']) or
			die('source connect result = ' . $GLOBALS['SOURCE_CONN']->ErrorNo() . ': ' . $GLOBALS['SOURCE_CONN']->ErrorMsg());

			$GLOBALS['TARGET_CONN'] = &ADONewConnection($this->target['db_type']);
			$GLOBALS['TARGET_CONN']->Connect($this->target['db_host'],
											 $this->target['db_user'],
											 $this->target['db_passwd'],
											 $this->target['db_name']) or
			die('target connect result = ' . $GLOBALS['TARGET_CONN']->ErrorNo() . ': ' . $GLOBALS['TARGET_CONN']->ErrorMsg());
		}
		$this->_has_init = true;
	}

	/**
	 * 一次設定所有屬性
	 *
	 * @param   string  $which          來源或目的
	 * @param   array   $properties     來源屬性陣列
	 */
	function _setDbProperty($which, $properties)
	{
		$this->$which = $properties;
	}

	/**
	 * 一次設定所有來源屬性
	 *
	 * @param   array   $properties     來源屬性陣列
	 */
	function setSourceProperty($properties)
	{
		$this->_setDbProperty('source', $properties);
	}

	/**
	 * 一次設定所有目的屬性
	 *
	 * @param   array   $properties     目的屬性陣列
	 */
	function setTargetProperty($properties)
	{
		$this->_setDbProperty('target', $properties);
	}

	/**
	 * 設定某個來源屬性
	 *
	 * @param   string  $key            屬性名
	 * @param   string  $value          屬性值
	 */
	function setSourceArgument($key, $value)
	{
		$this->source[$key] = $value;
	}

	/**
	 * 設定某個目的屬性
	 *
	 * @param   string  $key            屬性名
	 * @param   string  $value          屬性值
	 */
	function setTargetArgument($key, $value)
	{
		$this->target[$key] = $value;
	}

	/**
	 * 轉換性別欄位
	 */
	function _mapGender($g, $charset='BIG-5')
	{
		if ($g === true) return 'M';
	    $g = trim($g);
		if (preg_match('/^-?\d*(\.\d+)?$/', $g)) return floatval($g) ? 'M' : 'F';
	    switch (mb_strtolower($g, $charset))
	    {
	        case 'male':
	        case 'boy':
	        case 'man':
	        case '男':
	            return 'M';
			default:
			    return 'F';
		}
	}

	/**
	 * 欄位轉換
	 *
	 * @param   array   $templates      轉換指定 (key=要insert的欄位；value=要轉換的函數)
	 * @param   array   $values         欄位值
	 * @return  array                   傳會處理完畢的陣列
	 */
	function _mapFields($templates, $values)
	{
		if (empty($templates) || !is_array($templates)) return $values;

		$ret = array();
		foreach ($templates as $k => $v)
			if (is_callable($v))
			    $ret[$k] = call_user_func($v, array_shift($values));
			else
				$ret[$k] = array_shift($values);

		return $ret;
	}

	/**
	 * 把欄位串起來
	 */
	function _concatFields()
	{
		// 把欄位串起來，如果有左右括號，則用左右括號框起來
		$fields = $this->source['field_quote_left'] .
				  implode($this->source['field_quote_right'] . ',' . $this->source['field_quote_left'],
				  		  $this->source['fields']) .
				  $this->source['field_quote_right'];
		// 如果欄位是 * 或者有函數，則去掉括號
		if ($this->source['field_quote_left'])
		{
		    $ql      = preg_quote($this->source['field_quote_left']);
		    $qr      = preg_quote($this->source['field_quote_right']);
		    $match   = array('/' . $ql . '([^' . $ql . ']*[)*])' . $qr . '/',
							 '/' . $ql . '([^' . $qr . ']+\.)/');
			$replace = array('\1',
							 '\1' . $this->source['field_quote_left']);
            $fields  = preg_replace($match, $replace, $fields);
		}

		return $fields;
	}

	/**
	 * 開始進行匯入資料動作
	 *
	 * @param   string      $callback_function      轉換中，要回傳的進度值
	 * @return  array                               傳回一個陣列，$array[0]=完成筆數；$array[1]=失敗筆數；
	 */
	function import($keyCols=array(), $callback_function=null)
	{
		if (!$this->_has_init) $this->init();

		if ($this->source['pre_sqls'])
			$GLOBALS['SOURCE_CONN']->Execute($this->source['pre_sqls']) or die('source pre sql result = ' . $GLOBALS['SOURCE_CONN']->ErrorNo() . ': ' . $GLOBALS['SOURCE_CONN']->ErrorMsg());
		if ($this->target['pre_sqls'])
			$GLOBALS['TARGET_CONN']->Execute($this->target['pre_sqls']) or die('target pre sql result = ' . $GLOBALS['TARGET_CONN']->ErrorNo() . ': ' . $GLOBALS['TARGET_CONN']->ErrorMsg());

		$source_rs = $GLOBALS['SOURCE_CONN']->Execute(sprintf($this->source['sqls'],
															  $this->_concatFields(),
															  $this->source['table_name'],
															  $this->source['where']));
		$amount = $failure = $success = 0;
		if ($source_rs)
		{
			$total = $source_rs->RecordCount();
		    echo '<div style="display: none">';
		    $prev = time();
			while ($fields = $source_rs->FetchRow())
			{
/*
				if ($GLOBALS['TARGET_CONN']->AutoExecute($this->target['table_name'],
														 $this->_mapFields($this->target['fields'], $fields),
														 'INSERT'))
*/
				if ($GLOBALS['TARGET_CONN']->Replace($this->target['table_name'],
													 $this->_mapFields($this->target['fields'], $fields),
													 $keyCols, true))
					$success++;
				else
				{
					$failure++;
					echo $GLOBALS['TARGET_CONN']->ErrorNo() . ': ' . $GLOBALS['TARGET_CONN']->ErrorMsg() . "\n";
				}
				$amount++;

           	    if (time()-$prev) // 超過一秒才執行
           	    {
					is_callable($callback_function) or
						call_user_func($callback_function, round($amount/$total,1));
                    $prev = time();
				}
			}
		    echo '</div>';
		}
		else
			die('source select result = ' . $GLOBALS['SOURCE_CONN']->ErrorNo() . ': ' . $GLOBALS['SOURCE_CONN']->ErrorMsg());

		if ($this->source['post_sqls'])
			$GLOBALS['SOURCE_CONN']->Execute($this->source['post_sqls']) or die('source post sql result = ' . $GLOBALS['SOURCE_CONN']->ErrorNo() . ': ' . $GLOBALS['SOURCE_CONN']->ErrorMsg());
		if ($this->target['post_sqls'])
			$GLOBALS['TARGET_CONN']->Execute($this->target['post_sqls']) or die('target post sql result = ' . $GLOBALS['TARGET_CONN']->ErrorNo() . ': ' . $GLOBALS['TARGET_CONN']->ErrorMsg());

		return array($success, $failure);
	}
}


class ProgreeBar
{
	var $id;
	var $prev;

	function ProgreeBar()
	{
	    $this->id   = substr(uniqid(rand()), 0, 5);
	    $this->prev = time();
	}

	function showBar($title='')
	{
		while (ob_end_clean());
		echo <<< EOB
		<table width="100%" height="100%" id="panel_{$this->id}">
			<tr>
				<td align="center"><B>{$title}</B>
					<table style="border: 1px solid #5176D2; width: 500px" cellspacing="0" cellpadding="0">
						<tr>
							<td>
								<table id="progree_{$this->id}" width="0" cellspacing="0" cellpadding="0">
									<tr>
										<td style="height: 1em; background-color: #C7D8FA"></td>
									</tr>
								</table>
			                </td>
						</tr>
					</table>
		        </td>
			</tr>
		</table>
		<script>var pId_{$this->id} = document.getElementById("progree_{$this->id}");</script>
EOB;
        flush();
	}

	function step($p)
	{
		if ($p > 1)
		{
			echo '<script>pId_', $this->id, '.width="', $p, '%";</script>';
			flush();
		}
	}

	function close()
	{
	    echo <<< EOB
<script>
	var node_{$this->id} = document.getElementById("panel_{$this->id}");
	node_{$this->id}.parentNode.removeChild(node_{$this->id});
	delete node_{$this->id};
</script>
EOB;
		flush();
	}
}

?>
