<?php
	/**
	 * ���o�դ��Ʈw����ơA�פJ WM ��檺�B�z���O
	 *
	 * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
	 *
	 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
	 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
	 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
	 *
	 * @package     WM3
	 * @author      wiseguy liang <wiseguy@sun.net.tw>
	 * @copyright   2000-2008 SunNet Tech. INC.
	 * @version     CVS: $Id: sync_lib.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2009-02-10
	 *
	 * �Ƶ��G
	 */

define('WM_DOCUMENT_ROOT', dirname(dirname(__FILE__)));
include_once(WM_DOCUMENT_ROOT . '/config/sys_config.php');
include_once(WM_DOCUMENT_ROOT . '/config/login.config');
include_once(WM_DOCUMENT_ROOT . '/lib/adodb/adodb.inc.php');

define('OTHDB_SERVER_HOST', OTHDB_SERVER_ADDR . ':' . OTHDB_SERVER_PORT);

class TableSync
{
	var $_has_init = false;
	var $source = array('db_type'    => OTHDB_SERVER_TYPE,            // DB ���� (adodb �䴩�������)
						'db_host'    => OTHDB_SERVER_HOST,			  // DB IP
						'db_user'    => OTHDB_USERNAME,               // DB �s���b��
						'db_passwd'  => OTHDB_PASSWORD,               // DB �s���K�X
						'db_name'    => OTHDB_DATABASE,               // DB ��Ʈw�W
						'table_name' => '',                           // ��ƪ�W
						'fields'     => array('username',             // �n�פJ�����W
											  'password',             // ���`�N�A���W�١B���Ǥ����o�PWM�@�ˡA
											  'gender',               //   ���̷�WM�n insert �����ǱƦC�C
											  'email',
											  'first_name',
											  'last_name'
											 ),
						'field_quote_left'  => '',                    // ��쥪�A���C
						'field_quote_right' => '',                    // ���k�A���C
						'sqls'       => 'select %s from %s where %s', // ����ƪ� sql ���O
						'pre_sqls'   => '',                           // ����ƫe�n���U�� sql ���O
						'post_sqls'  => '',                           // ����ƫ�n�U�� sql ���O
						'where'      => '1=1'                         // ����ƪ� sql ����
						);
	var $target = array('db_type'    => sysDBtype,                    // DB ����
						'db_host'    => sysDBhost,                    // DB IP
						'db_user'    => sysDBaccoount,                // DB �s���b��
						'db_passwd'  => sysDBpassword,                // DB �s���K�X
						'db_name'    => sysDBname,                    // DB ��Ʈw�W
						'table_name' => '',                           // ��ƪ�W
						'fields'     => array('username'   => '',     // �n�פJ�����W�A�Ψ��ഫ�禡
											  'password'   => 'md5',
											  'gender'     => array('TableSync', '_mapGender'),
											  'email'      => '',
											  'first_name' => '',
											  'last_name'  => ''
											 ),
						'pre_sqls'   => '',                           // �g��ƫe�n���U�� sql ���O
						'post_sqls'  => ''                            // �g��ƫ�n�U�� sql ���O
						);

	/**
	 * �ҩl�����Ʈw�C�Y�w�ҩl�N reuse
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
	 * �@���]�w�Ҧ��ݩ�
	 *
	 * @param   string  $which          �ӷ��Υت�
	 * @param   array   $properties     �ӷ��ݩʰ}�C
	 */
	function _setDbProperty($which, $properties)
	{
		$this->$which = $properties;
	}

	/**
	 * �@���]�w�Ҧ��ӷ��ݩ�
	 *
	 * @param   array   $properties     �ӷ��ݩʰ}�C
	 */
	function setSourceProperty($properties)
	{
		$this->_setDbProperty('source', $properties);
	}

	/**
	 * �@���]�w�Ҧ��ت��ݩ�
	 *
	 * @param   array   $properties     �ت��ݩʰ}�C
	 */
	function setTargetProperty($properties)
	{
		$this->_setDbProperty('target', $properties);
	}

	/**
	 * �]�w�Y�Өӷ��ݩ�
	 *
	 * @param   string  $key            �ݩʦW
	 * @param   string  $value          �ݩʭ�
	 */
	function setSourceArgument($key, $value)
	{
		$this->source[$key] = $value;
	}

	/**
	 * �]�w�Y�ӥت��ݩ�
	 *
	 * @param   string  $key            �ݩʦW
	 * @param   string  $value          �ݩʭ�
	 */
	function setTargetArgument($key, $value)
	{
		$this->target[$key] = $value;
	}

	/**
	 * �ഫ�ʧO���
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
	        case '�k':
	            return 'M';
			default:
			    return 'F';
		}
	}

	/**
	 * ����ഫ
	 *
	 * @param   array   $templates      �ഫ���w (key=�ninsert�����Fvalue=�n�ഫ�����)
	 * @param   array   $values         ����
	 * @return  array                   �Ƿ|�B�z�������}�C
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
	 * ������_��
	 */
	function _concatFields()
	{
		// ������_�ӡA�p�G�����k�A���A�h�Υ��k�A���ذ_��
		$fields = $this->source['field_quote_left'] .
				  implode($this->source['field_quote_right'] . ',' . $this->source['field_quote_left'],
				  		  $this->source['fields']) .
				  $this->source['field_quote_right'];
		// �p�G���O * �Ϊ̦���ơA�h�h���A��
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
	 * �}�l�i��פJ��ưʧ@
	 *
	 * @param   string      $callback_function      �ഫ���A�n�^�Ǫ��i�׭�
	 * @return  array                               �Ǧ^�@�Ӱ}�C�A$array[0]=�������ơF$array[1]=���ѵ��ơF
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

           	    if (time()-$prev) // �W�L�@��~����
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
