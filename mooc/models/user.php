<?php
/**
 * 提供與使用者相關的函數
 *
 * 建立日期：2014/2/25
 * @author cch
 *
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/login/login.inc');
require_once(sysDocumentRoot . '/lib/username.php');

class user
{
    /**
     * @name 驗證使用者電子信箱是否在3天內驗證，並依管理者審核否連動啟用帳號
     * @author cch
     *
     * @param string $idx:驗證碼
     *
     * @return string $rtn:成功、失敗
    */
    function isEmailExists($idx = '')
    {
        if ($idx === null || $idx === '') {
            $rtn = '0';// 驗證碼空值，無效
        } else {
            // 取驗證碼時間與使用者帳號
            $cols = '`reg_time`, `username`, `verify_code`, `verify_flag`, `email`';
            $tb = 'CO_user_verify';
            $where  = '`verify_code` = \'' . $idx . '\' and `type` = \'email\' ';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() === 1) {
                while (!$rs->EOF) {
                    $username = $rs->fields['username'];
                    $verifyFlag = $rs->fields['verify_flag'];
                    $email = $rs->fields['email'];

                    if ($verifyFlag === 'Y') {
                        $rtn = '5';// 已經驗證過了
                    } else {
                        $start = strtotime($rs->fields['reg_time']);
                        $end = strtotime(date('Y-m-d H:i:s'));
                        $timeDiff = $end - $start;

                        if (floor($timeDiff) <= (60 * 60 * 24 * 3)) {
                            if ($this->setEmailExists($idx) === '1') {

                                // 判斷 wm_all_account有沒有資料
                                $account = $this->getSimpleProfileByUsername($username);
                                if (count($account) === 0) {
                                    // 取暫存資料
                                    $data = $this->getTmpProfileByUsername($username);
                                    addUser($username, $data, 'N');
                                    // 刪除註冊暫存資料
                                    $aryUser[] = array('username' => $username, 'email' => $email);
                                    $this->delExpiredTmpUsers($aryUser);
                                }

                                // 判斷是否需要管理者審核，如果是自由註冊，則連動將帳號啟用
                                // 取自由註冊設定值Y自由N不開放C開放但是需要管理者審核
                                $regStatus = getSchoolRegStatus();
                                if ($regStatus === 'Y') {
                                    $this->setUserEnable($username);
                                    $rtn = '1';// 3天內驗證有效，且帳號啟用
                                } else {
                                    $rtn = '2';// 3天內驗證有效，但帳號不直接啟用
                                }
                            } else {
                                $rtn = '4';// 更新失敗
                            }
                        } else {
                            $rtn = '3';// 超過3天內驗證，無效
                        }
                    }

                    $rs->MoveNext();
                }
            } else {
                $rtn = '0';// 驗證碼不存在，無效
            }
        }
        return array('code' => $rtn, 'username' => $username);
    }

    /**
     * @name 電子信箱驗證碼驗證通過註記Y
     * @author cch
     *
     * @param string $idx:驗證碼
     *
     * @return string $rtn:成功、失敗
    */
    function setEmailExists($idx = '')
    {
        if ($idx === null || $idx === '') {
            return '0';
        } else {
            $r = dbSet(
                '`CO_user_verify`',
                sprintf(
                    "verify_flag = '%s'",
                    'Y'
                ),
                "`verify_code` = '" . $idx . "' and `type` = 'email' "
            );

            return '1';
        }
    }

    /**
     * @name 更新密碼驗證碼驗證通過註記Y
     * @author cch
     *
     * @param string $idx:驗證碼
     * @param string $username:帳號
     *
     * @return string $rtn:成功、失敗
    */
    function setForgetCodeExists($idx = '', $username = '')
    {
        if ($idx === null || $idx === '' || $username === null || $username === '') {
            return '0';
        } else {
            $cols = '`reg_time`, `username`';
            $tb = 'CO_user_verify';
            $where  = '`verify_code` = \'' . $idx . '\' and `type` = \'forget\' and `username` = \'' . $username . '\' ';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() >= 1) {
                $r = dbSet(
                    '`CO_user_verify`',
                    sprintf(
                        "verify_flag = '%s'",
                        'Y'
                    ),
                    "`verify_code` = '" . $idx . "' and `type` = 'forget' and `username` = '" . $username . "' "
                );

                return '1';
            } else {
                return '0';
            }
        }
    }

    /**
     * @name 啟用帳號
     * @author cch
     *
     * @param string $username:使用者編號
     *
    */
    function setUserEnable($username)
    {
    	global $sysConn;
        dbSet(
            '`WM_all_account`',
            sprintf(
                "`enable` = '%s'",
                'Y'
            ),
            "`username` = '" . $username . "'"
        );

        dbSet(
            '`WM_user_account`',
            sprintf(
                "`enable` = '%s'",
                'Y'
            ),
            "`username` = '" . $username . "'"
        );

        // 切換到 Master資料庫
        $sysConn->Execute('use ' . sysDBname);
        dbSet(
            '`CO_mooc_account`',
            sprintf(
                "`enable` = '%s'",
                'Y'
            ),
            "`username` = '" . $username . "'"
        );
    }

    /**
     * @name 取中文姓名與電子信箱
     * @author cch
     *
     * @param string $username:使用者編號
     *
     * @return array
     *
    */
    function getSimpleProfileByUsername($username)
    {
        // 取姓名、電子信箱
        $cols = 'username, `first_name`, `last_name`, `email`';
        $tb = 'WM_all_account';
        $where  = '`username` = \'' . $username . '\'';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if ($rs && $rs->RecordCount() >= 1) {
            while (!$rs->EOF) {
                $realname = checkRealname($rs->fields['first_name'], $rs->fields['last_name']);
                $email = $rs->fields['email'];

                $rs->MoveNext();
            }
            return array('username' => $username, 'realname' => $realname, 'email' => $email);
        } else {
            return array();
        }
    }

    /**
     * @name 取中文姓名與電子信箱
     * @author cch
     *
     * @param string $email:電子信箱
     *
     * @return array
     *
    */
    function getProfileByEmail($email)
    {
        // 取姓名、電子信箱
        $cols = 'username, `first_name`, `last_name`, `email`, `homepage`';
        $tb = 'WM_all_account';
        $where  = '`email` = \'' . $email . '\'';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if ($rs && $rs->RecordCount() >= 1) {
            while (!$rs->EOF) {
                $username = $rs->fields['username'];
                $realname = checkRealname($rs->fields['first_name'], $rs->fields['last_name']);
                $email = $rs->fields['email'];
                $homepage = $rs->fields['homepage'];

                $rs->MoveNext();
            }
            return array('username' => $username, 'realname' => $realname, 'email' => $email, 'homepage' => $homepage);
        } else {
            return array();
        }
    }

    /**
     * @name 取個人資料
     * @author cch
     *
     * @param string $username:帳號
     *
     * @return array
     *
    */
    function getProfileByUsername($username)
    {
        $data = array();

        // 取姓名、電子信箱
        $cols = '`username`, `password`, enable, first_name, `last_name`, `gender`, `birthday`,
            `personal_id`, `email`, `homepage`, `home_tel`, `home_fax`, `home_address`, `office_tel`,
            `office_fax`, `office_address`, `cell_phone`, `company`, `department`, `title`, `language`,
            `theme`, `msg_reserved`, `hid`';
        $tb = 'WM_all_account';
        $where  = '`username` = \'' . $username . '\'';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if ($rs && $rs->RecordCount() === 1) {
            while (!$rs->EOF) {
                $username = $rs->fields['username'];
                $data = array(
                    'username' => $rs->fields['username'],
                    'password' => $rs->fields['password'],
                    'enable' => $rs->fields['enable'],
                    'first_name' => $rs->fields['first_name'],
                    'last_name' => $rs->fields['last_name'],
                    'gender' => $rs->fields['gender'],
                    'birthday' => $rs->fields['birthday'],
                    'personal_id' => $rs->fields['personal_id'],
                    'email' => $rs->fields['email'],
                    'homepage' => $rs->fields['homepage'],
                    'home_tel' => $rs->fields['home_tel'],
                    'home_fax' => $rs->fields['home_fax'],
                    'home_address' => $rs->fields['home_address'],
                    'office_tel' => $rs->fields['office_tel'],
                    'office_fax' => $rs->fields['office_fax'],
                    'office_address' => $rs->fields['office_address'],
                    'cell_phone' => $rs->fields['cell_phone'],
                    'company' => $rs->fields['company'],
                    'department' => $rs->fields['department'],
                    'title' => $rs->fields['title'],
                    'language' => $rs->fields['language'],
                    'theme' => $rs->fields['theme'],
                    'msg_reserved' => $rs->fields['msg_reserved'],
                    'hid' => $rs->fields['hid'],
                    'realname' => checkRealname($rs->fields['first_name'], $rs->fields['last_name'])
                    );

                $rs->MoveNext();
            }
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * @name 取個人資料
     * @author cch
     *
     * @param string $username:帳號
     *
     * @return array
     *
    */
    function getTmpProfileByUsername($username)
    {
        $data = array();

        // 取姓名、電子信箱
        $cols = '`username`, `password`, enable, first_name, `last_name`, `gender`, `birthday`,
            `personal_id`, `email`, `homepage`, `home_tel`, `home_fax`, `home_address`, `office_tel`,
            `office_fax`, `office_address`, `cell_phone`, `company`, `department`, `title`, `language`,
            `theme`, `msg_reserved`, `hid`';
        $tb = 'CO_mooc_account';
        $where  = '`username` = \'' . $username . '\'';

        $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

        if ($rs && $rs->RecordCount() === 1) {
            while (!$rs->EOF) {
                $username = $rs->fields['username'];
                $data = array(
                    'username' => $rs->fields['username'],
                    'password' => $rs->fields['password'],
                    'enable' => $rs->fields['enable'],
                    'first_name' => $rs->fields['first_name'],
                    'last_name' => $rs->fields['last_name'],
                    'gender' => $rs->fields['gender'],
                    'birthday' => $rs->fields['birthday'],
                    'personal_id' => $rs->fields['personal_id'],
                    'email' => $rs->fields['email'],
                    'homepage' => $rs->fields['homepage'],
                    'home_tel' => $rs->fields['home_tel'],
                    'home_fax' => $rs->fields['home_fax'],
                    'home_address' => $rs->fields['home_address'],
                    'office_tel' => $rs->fields['office_tel'],
                    'office_fax' => $rs->fields['office_fax'],
                    'office_address' => $rs->fields['office_address'],
                    'cell_phone' => $rs->fields['cell_phone'],
                    'company' => $rs->fields['company'],
                    'department' => $rs->fields['department'],
                    'title' => $rs->fields['title'],
                    'language' => $rs->fields['language'],
                    'theme' => $rs->fields['theme'],
                    'msg_reserved' => $rs->fields['msg_reserved'],
                    'hid' => $rs->fields['hid'],
                    'realname' => checkRealname($rs->fields['first_name'], $rs->fields['last_name'])
                    );

                $rs->MoveNext();
            }
            return $data;
        } else {
            return $data;
        }
    }

    /**
     * @name 驗證使用者更新密碼是否在3天內更新
     * @author cch
     *
     * @param string $idx:驗證碼
     *
     * @return string $rtn:成功、失敗
    */
    function isForgetCodeExists($idx = '')
    {
        if ($idx === null || $idx === '') {
            $rtn = '0';// 驗證碼空值，無效
        } else {
            // 取驗證碼時間與使用者帳號
            $cols = '`reg_time`, `username`, `verify_flag`';
            $tb = 'CO_user_verify';
            $where  = '`verify_code` = \'' . $idx .'\' and `type` = \'forget\' ';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $verifyFlag = $rs->fields['verify_flag'];

                    $start = strtotime($rs->fields['reg_time']);
                    $end = strtotime(date('Y-m-d H:i:s'));
                    $timeDiff = $end - $start;

                    if ($verifyFlag === 'Y') {
                        $rtn = '4';// 已經更新密碼過了
                    } else {
                        if (floor($timeDiff) <= (60 * 60 * 24 * 3)) {
                            $rtn = '1';// 3天內驗證有效，且帳號啟用
                        } else {
                            $rtn = '3';// 超過3天內驗證，無效
                        }
                    }

                    $rs->MoveNext();
                }
            } else {
                $rtn = '0';// 驗證碼不存在，無效
            }
        }
        return $rtn;
    }

    /**
     * @name 更新密碼
     * @author cch
     *
     * @param string $username:使用者編號
     * @param string $password:使用者密碼
     *
    */
    function setUserPassword($username = '', $password = '')
    {
        if ($username === null || $username === '' || $password === null || $password === '') {
            $rtn = '0';// 空值，無效
        } else {
            dbSet(
                '`WM_all_account`',
                sprintf(
                    "`password` = '%s'",
                    $password
                ),
                "`username` = '" . $username . "'"
            );

            dbSet(
                '`WM_user_account`',
                sprintf(
                    "`password` = '%s'",
                    $password
                ),
                "`username` = '" . $username . "'"
            );

            $rtn = '1';
        }

        return $rtn;
    }

    /**
     * @name 驗證使用者電子信箱驗證沒
     * @author cch
     *
     * @param string $email:電子信箱
     *
     * @return string $rtn:Y有N沒有X沒資料
    */
    function isEmailValidCodePass($email = '')
    {
        if (!(isset($email)) || $email === '') {
            $rtn = 'X';
        } else {
            // 取驗證碼時間與使用者帳號
            $cols = '`verify_flag`';
            $tb = 'CO_user_verify';
            $where  = '`email` = \'' . $email . '\' and `type` = \'email\' ';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $rtn = $rs->fields['verify_flag'];
                    $rs->MoveNext();
                }
            } else {
                $rtn = 'X';
            }
        }
        return $rtn;
    }

	/**
	 * addMoocUser()
	 *     新增一位使用者
	 * @param string $username : 要新增的帳號
	 * @param array  $data     : 這個使用者其它的資料
	 * @return
	 *     -1 : 新增成功，但需管理者審核
	 *     0  : 新增成功
	 *     1  : 保留的帳號
	 *     2  : 帳號使用中
	 *     3  : 帳號格式不符合
	 *     4  : 不可以註冊 (學校管理者設定該校不可自行註冊)
	 *          這一項還需要修改，可能會參考到權限控管的部分
	 **/
	function addMoocUser($username, $data, $lang = '') {
		global $_SERVER, $sysSession;
		$username = trim($username);

		// 1. WM_MASTER -> CO_mooc_account
        $RS = dbGetStSr('WM_school', 'language', "school_id={$sysSession->school_id} AND school_host='{$_SERVER[HTTP_HOST]}'", ADODB_FETCH_ASSOC);

        // 這邊需要依照是不是管理者或是使用者自行註冊
        if (empty($lang)) $lang = empty($RS['language']) ? sysDefaultLang : $RS['language'];

		// 帳號啟用時間與終止時間的預設值為NULL
		$time_ary['begin_time']  = 'NULL';
		$time_ary['expire_time'] = 'NULL';

		$values_ary['username']       = "'{$username}'";
		$values_ary['password']       = "''";
		$values_ary['enable']         = "'N'";
		$values_ary['first_name']     = "''";
		$values_ary['last_name']      = "''";
		$values_ary['gender']         = "'F'";
		$values_ary['birthday']       = 'NULL';
		$values_ary['personal_id']    = "''";
		$values_ary['email']          = "''";
		$values_ary['homepage']       = "''";
		$values_ary['home_tel']       = "''";
		$values_ary['home_fax']       = "''";
		$values_ary['home_address']   = "''";
		$values_ary['office_tel']     = "''";
		$values_ary['office_fax']     = "''";
		$values_ary['office_address'] = "''";
		$values_ary['cell_phone']     = "''";
		$values_ary['company']        = "''";
		$values_ary['department']     = "''";
		$values_ary['title']          = "''";
		$values_ary['language']       = "'{$lang}'";
		$values_ary['theme']          = "'default'";
		$values_ary['msg_reserved']   = 0;		// 是否要備份到訊息中心
		$values_ary['hid']            = 262076;

		$fields = array_keys($values_ary);

		$ary = array_slice($fields, 1, -2); // 去掉 username, msg_reserved, hid 這三個欄位
		$sql_fields = '`' . implode('`,`', $fields) . '`';

		// 帳號啟用時間與終止時間的key
		$aryTotime = array('begin_time', 'expire_time');

		if (is_array($data)) {
			while (list($key, $val) = each($data))
			{
				if (in_array($key, $ary))
				{
					if ($key == 'password')
					{
						$values_ary['password'] = "'" . $val . "'";
					}
					elseif ($key == 'gender')
					{
						if (strlen($val) > 0)
						{
							$values_ary[$key] = (($val == 'F') || ($val == 'M')) ? "'{$val}'" : "'F'";
						}
						else
						{
							$values_ary[$key] = "'F'";
						}
					}
					else
					{
						$values_ary[$key] = "'" . $val . "'";
					}
				}
				elseif (in_array($key, $aryTotime))
				{
					$time_ary[$key] = $val;
				}
			}
		}

		$values = implode(', ', $values_ary);

        $r = dbNew('CO_mooc_account', $sql_fields, $values);
	}

    /**
     * @name 取使用者第一次註冊時間
     * @author cch
     *
     * @param string $username:帳號
     *
     * @return string $rtn:時間、X沒資料
    */
    function getRegtimeByUsername($username = '')
    {
        if (!(isset($username)) || $username === '') {
            $rtn = 'X';
        } else {
            // 取驗證碼時間與使用者帳號
            $cols = '`reg_time`';
            $tb = 'CO_user_verify';
            $where  = '`username` = \'' . $username . '\' and `type` = \'email\' ';

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $rtn = $rs->fields['reg_time'];
                    $rs->MoveNext();
                }
            } else {
                $rtn = 'X';
            }
        }
        return $rtn;
    }

    /**
     * @name 取超過指定天數沒有電子信箱驗證的
     * @author cch
     *
     * @param int $days:設定超過幾天沒有驗證電子信箱則認定刪除
     *
     * @return array $data:array(array('username' => $username, 'email' => $email))
    */
    function getExpiredUsers($days = 3)
    {
        $data = array();

        // 取各分校有哪些人逾時驗證
        $schools = dbGetCol('WM_school', 'school_id', '1 group by school_id order by school_id');
        foreach($schools as $db) {
            $cols = 'username, email, reg_time, NOW() , TIMESTAMPDIFF(MINUTE , reg_time, NOW())';
            $tb = sysDBprefix . $db . '.CO_user_verify';
            $where  = sprintf(
                'verify_flag = \'N\' AND TIMESTAMPDIFF(MINUTE , reg_time, NOW() ) >= %d and `type` = \'email\' ',
                60 *24 * $days
            );

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);

            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $username = $rs->fields['username'];
                    $email = $rs->fields['email'];
                    $data[] = array('username' => $username, 'email' => $email);

                    $rs->MoveNext();
                }
            }
        }
        return $data;
    }

    /**
     * @name 刪除指定的暫存使用者
     * @author cch
     *
     * @param array $users: 暫存的使用者編號與電子信箱
     *
     * @return array $data: 被刪除的使用者編號
    */
    function delExpiredTmpUsers($users = array())
    {
        $data = array();

        if (count($users) >= 1) {
            foreach ($users as $v) {
                if ($v['username'] !== '' &&  $v['email'] !== '') {
                    // 刪除指定的MASTER MOOC USER 資料
                    dbDel(sysDBname . '.CO_mooc_account', sprintf('username = "%s" and email = "%s"', $v['username'], $v['email']));

                    // 刪除各分校符合條件的人
                    $schools = dbGetCol('WM_school', 'school_id', '1 group by school_id order by school_id');
                    foreach($schools as $db) {
                        dbDel(sysDBprefix . $db . '.CO_user_verify', sprintf('username = "%s" and email = "%s"', $v['username'], $v['email']));
                    }
                    $data[] = $v['username'];
                }
            }
            $data = implode(',', $data);
        }
        return $data;
    }

    /**
     * @name 重設使用者註冊時間
     * @author spring
     *
     * @param string $username:帳號
     *
     * @return string $rtn: $currtime:成功、0:失敗
    */
    function resetRegtimeByUsername($username = '')
    {
        if (!(isset($username)) || $username === '') {
            $rtn = '0';
        } else {
            // 修改註冊時間為NOW()
            $currtime = date('Y-m-d H:i:s');
            dbSet(
                '`CO_user_verify`',
                sprintf(
                    "`reg_time` = '%s'",
                    $currtime
                ),
                '`username` = \'' . $username . '\' and `type` = \'email\' '
            );
            $rtn = $currtime;
        }
        return $rtn;
    }

    /**
     * @name 重設使用者電子信箱
     * @author spring
     *
     * @param string $username:帳號
     *
     * @param string $email:電子信箱
     *
     * @return string $rtn:1:成功、0:失敗
    */
    function setEmailByUsername($username = '', $email = '')
    {
        if (!(isset($username)) || $username === '' || !(isset($email)) || $email === '') {
            $rtn = '0';
        } else {
            // 修改註冊信箱
            dbSet(
                '`CO_user_verify`',
                sprintf(
                    "`email` = '%s'",
                    $email
                ),
                '`username` = \'' . $username . '\' and `type` = \'email\' '
            );
            dbSet(
                '`CO_mooc_account`',
                sprintf(
                    "`email` = '%s'",
                    $email
                ),
                '`username` = \'' . $username . '\''
            );
            $rtn = '1';
        }
        return $rtn;
    }
    
    /**
     * @name 刪除使用者大頭照
     * @author cch
     *
     * @param string $username: 使用者
     *
     * @return array $data: code:-1失敗 0無異動 1成功, msg: 訊息
    */
    function delUserPic($username = null)
    {
        $data = array();
        
        if ($username === null) {
            global $sysSession;
            $username = $sysSession->username;
        }
        $cnt = dbGetOne('WM_user_picture', 'username', sprintf('username = "%s"', $username));
        $RS = dbDel('`WM_user_picture`', sprintf('username = "%s"', $username));
        if ($RS) {
            if ($cnt === false) {
                $data['code'] = 0;
                $data['msg'] = 'nothing';
            } else {
                $data['code'] = 1;
                $data['msg'] = 'success';
            }
        } else {
            $data['code'] = -1;
            $data['msg'] = 'fail';
        }
        
        return $data;
    }
}

/**
 * 提供與使用者快照相關的函數
 *
 **/
class snapshot
{
    /**
     * @name 新增使用者重點筆記
     * @author spring
     *
     * @param string $username: 帳號
     *
     * @param string $cId: 課程編號
     *
     * @param string $cName: 課程名稱
     *
     * @param string $scoId: SCO ID
     *
     * @param string $title: 筆記標題
     *
     * @param string $url: 素材網址
     *
     * @param string $pointTime:截取時間
     *
     * @param string $imgUrl:   截圖網址
     *
     * @param string $aId:       素材 ID
     *
     * @return string $rtn:1:成功(回傳 insert id)、0:失敗
    */
    function addNoteByUsername($username = '', $cId='', $cName='', $scoId = '', $title = '', $url = '', $pointTime = '', $imgUrl = '', $aId = '')
    {
        global $sysConn;

        $rtn = array();
        
        if (!(isset($username)) || $username === '') {
            $rtn['code'] = '0';
        } else {
            if (!empty($imgUrl)){
                if (!filter_var($imgUrl, FILTER_VALIDATE_URL)){
                    return false;
                }
            }
            // 新增時間為NOW()
            $currtime = date('Y-m-d H:i:s');
            
            // 解析圖片名稱
            $getUrlAry = explode('/', $imgUrl);
            $getFilename = $getUrlAry[count($getUrlAry)-1];
            // 新增筆記
            dbNew(
                '`WM_user_note`',
                '`username`, `course_id`, `course_name`, `sco_id`, `title`, `url`, `point_time`, `image_name`, `asset_id`, `create_time`, `update_time`',
                sprintf(
                    "'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
                    $username, $cId, $cName, $scoId, $title, $url, $pointTime, $getFilename, $aId, $currtime, $currtime
                )
            );
            $note_id = $sysConn->Insert_ID();
            
            // 下載 LCMS 截圖回 MOOCs
            $pictureDir = sprintf(sysDocumentRoot . '/user/%1s/%1s/%s/note/',
                        substr($username, 0, 1), substr($username, 1, 1), $username);
            // php5 遞回建目錄
            /*
            $pictureDir = sprintf(sysDocumentRoot . '/user/%1s/%1s/%s/note/%s/',
                        substr($username, 0, 1), substr($username, 1, 1), $username, $note_id);
            if (!is_dir($pictureDir)) {
                mkdir($pictureDir, 0755,true);
            }
             * 
             */
            if (!is_dir($pictureDir)) {
                mkdir($pictureDir, 0755);
            }
            
            $pictureDir = sprintf($pictureDir . '%s/', $note_id);
            
            if (!is_dir($pictureDir)) {
                mkdir($pictureDir, 0755);
            }
            
            $pictureFile = $pictureDir . $getFilename;

            system('wget -q --no-check-certificate '.$imgUrl.' -O '.$pictureFile);
            
            $rtn = array(
                'code' => '1',
                'id' => $note_id
            );
        }
        return json_encode($rtn);
    }
    
    /**
     * @name 修改使用者重點筆記
     * @author spring
     *
     * @param string $noteId:筆記ID
     * 
     * @param string $username:帳號
     * 
     * @param string $title:筆記標題
     *
     * @param string $memo:筆記內容
     *
     * @return string $rtn:1:成功、0:失敗
    */
    function setNoteByNoteId($noteId = '', $username = '', $memo = '')
    {
        if (!(isset($username)) || $username === '') {
            $rtn = '0';
        } else {
            // 修改時間為NOW()
            $currtime = date('Y-m-d H:i:s');
            // 修改筆記內容
            dbSet(
                '`WM_user_note`',
                sprintf(
                    "`memo` = '%s', `update_time` = '%s'",
                    $memo, $currtime
                ),
                '`username` = \'' . $username . '\' and `note_id` = \''.$noteId.'\' '
            );
            $rtn = '1';
        }
        return $rtn;
    }
    
    /**
     * @name 取得使用者重點筆記
     * @author spring
     *
     * @param string $username:帳號
     * 
     * @param array  $filter: 篩選器 
     *
     * @param string $order: 排序
     *
     * @return string $rtn:1:成功、0:失敗
    */
    function getNoteByUsername($username = '', $filter = '', $order = '`create_time`')
    {
        if (!(isset($username)) || $username === '') {
            $rtn['code'] = '0';
        } else {
            $cols = '*';
            $tb = '`WM_user_note`';
            $where  = '`username` = \'' . $username . '\' ';
            $where .= ($filter['keyword'] != '') ? sprintf('AND (`title` LIKE "%%%s%%" OR `memo` LIKE "%%%s%%" OR `course_name` LIKE "%%%s%%") ', $filter['keyword'], $filter['keyword'], $filter['keyword']) : '';
            $where .= ($filter['course_id'] != 0) ? sprintf('AND `course_id` = %d ', $filter['course_id']) : '';

            $subWhere = sprintf('ORDER BY %s desc', $order);

            $rs = dbGetStMr($tb, $cols, $where.$subWhere, ADODB_FETCH_ASSOC);
            $dataIndex = array('note_id', 'username', 'course_id', 'course_name', 'sco_id', 'title', 'url', 'point_time', 'image_name', 'asset_id', 'memo', 'create_time', 'update_time', 'review_cnt');
            if ($rs && $rs->RecordCount() >= 1) {
                $data = array();
                while (!$rs->EOF) {
                    $dataAry = array();
                    foreach($dataIndex as $k => $v) {
                        $dataAry[$v] = $rs->fields[$v]; 
                    }
                    $createtime = strtotime($rs->fields['create_time']);
                    $dateYM = date("Y-m", $createtime);
                    $dateNum = 'a'.strtotime(date("Y-m-d h:i:s", $createtime));
                    $dataAry['shot_time'] = gmdate("H:i:s", $dataAry['point_time']);
                    $dataAry['create_time_noy'] = date("m-d H:i:s", $createtime);
                    $dataAry['image_name'] = rawurlencode($dataAry['image_name']);
                    $memo = $dataAry['memo'];
                    $dataAry['memo'] = htmlspecialchars($memo);
                    $dataAry['memo_view'] = nl2br(htmlspecialchars($memo));
                    $data[$dateYM][$dateNum] = $dataAry;
                    $allNote[] = $rs->fields['note_id'];
                    $rs->MoveNext();
                }

                // 取得討論版回覆數
                $noteReplys = $this->getReplyNumByNid($allNote);
                $replyData = ('1' === $noteReplys['code']) ? $noteReplys['data'] : null;
                $rtn['code'] = '1';
            }
            $rtn['data'] = $data;
            $rtn['reply'] = $replyData;
        }
        return $rtn;
    }
    
    /**
     * @name 刪除指定的使用者筆記
     * @author spring
     *
     * @param string $username: 使用者
     * 
     * @param array  $notes: 被刪除的使用者Note編號
     *
     * @return array $data: code:0 失敗 1 成功, msg: 訊息
    */
    function delNoteById($username = '', $notes = array())
    {
        $data = array();
        $data['code'] = 0;
        if (count($notes) >= 1) {
            foreach ($notes as $k => $v) {
                // 刪除指定的 USER NOTE 資料
                dbDel( '`WM_user_note`', sprintf('username = "%s" and note_id = "%s"', $username, $v));
                $data['data'][$k] = $v;
                // 刪除使用者資料夾裡的筆記檔案
                $noteDir = sprintf(sysDocumentRoot . '/user/%1s/%1s/%s/note/%s/',
                        substr($username, 0, 1), substr($username, 1, 1), $username, $v);
                if (is_dir($noteDir) || file_exists($noteDir)) {
                    system("rm -rf ".$noteDir."", $delDirRtn);
                    // 判斷是否成功刪除資料夾
                    if ($delDirRtn !== 0) {
                        $data['msg'] = 'Could not delete!';
                    }
                }
            }
            $data['code'] = 1;
        }
        return $data;
    }
    
    /**
     * @name 取得指定的使用者筆記的課程
     * @author spring
     *
     * @param string $username: 使用者
     * 
     * @return array $rtn: code: 0(失敗), 1(成功); msg: 訊息
    */
    function getNoteCourseByUsername($username = '')
    {
        global $sysSession;
         if (!(isset($username)) || $username === '') {
            $rtn['code'] = '0';
        } else {
            $cols = 'distinct(n.`course_id`), c.`caption`';
            $tb = '`WM_user_note` n INNER JOIN `WM_term_course` c ON n.course_id = c.course_id';
            $where  = 'n.`username` = \'' . $username . '\' ';
            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $caption = getCaption($rs->fields['caption']);
                    $rtn['data'][$caption[$sysSession->lang]] =  $rs->fields['course_id'];
                    $rs->MoveNext();
                }
            }
            $rtn['code'] = '1';
        }
        return $rtn;
    }

    /**
     * @name 取得指定筆記的討論區回覆數
     * @author spring
     *
     * @param string $nid:筆記ID
     * 
     * @return array $rtn:code:0(失敗),1(成功);msg:訊息
    */
    function getReplyNumByNid($nid=array())
    {
        global $sysSession;
        if (is_array($nid) && count($nid) > 0) {
            $allNote = implode(',', $nid);
            $tb = '`WM_user_note_post` n 
                    INNER JOIN `WM_bbs_posts` b ON n.`board_id` = b.`board_id` 
                    AND  n.`node` = CONCAT(substr(b.`node`, 1, 9)) ';
            $cols = 'n.`note_id`,n.`node`, b.`board_id`, count(b.`node`) as cnt';
            $where = 'n.`note_id` in ('.$allNote.') GROUP BY n.`note_id`, b.`board_id`';
            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
            while (!$rs->EOF) {
                $replyData[$rs->fields['note_id']]['bid'] = $rs->fields['board_id'];
                $replyData[$rs->fields['note_id']]['nid'] = $rs->fields['node'];
                $replyData[$rs->fields['note_id']]['num'] = $rs->fields['cnt'];
                $rs->MoveNext();
            }

            $rtn['data'] = $replyData;
            $rtn['code'] = '1';
        } else {
            $rtn['code'] = '0';
        }
        return $rtn;
    }
}

