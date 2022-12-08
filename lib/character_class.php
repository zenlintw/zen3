<?php
/**
 * 課程教師與班級導師 身份之指定、移除 API 類別
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2005 SunNet Tech. INC.
 * @version     CVS: $Id: character_class.php,v 1.1 2010/02/24 02:39:33 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2005-10-27
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

/**
 * 提供給 WMteacher 及 WMdirector 繼承的父層類別
 */
class WMcharacter
{
    
    /**
     * 判斷是否已存在 $table
     *
     * @param	int		$cid		課程 ID 或 班級 ID
     * @param	string	$user		user帳號
     * @return	bool				true=存在；false=不存在
     */
    function _isExist($cid, $user, $table)
    {
        return (bool) aclCheckRole($user, $GLOBALS['sysRoles']['all'], $cid);
    }
    
    
    /**
     * 加一個帳號或一群帳號為某身份
     *
     * @param	string|array	$users	帳號
     * @param	int				$cid	課程 ID 或 班級 ID
     * @param	int				$role	身份
     * @param	string			$table	WM_term_major 或 WM_class_member
     */
    function _assign($users, $cid, $role, $table)
    {
        $isClass = false;
        switch ($table) {
            // Bug#1441-增加$GLOBALS['sysRoles']['student'] => 刪除助教身份時，是將其變換成學員身份 by Small 2006/11/7
            case 'WM_class_member':
                $mask    = $GLOBALS['sysRoles']['all'] ^ ($GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant'] | $GLOBALS['sysRoles']['student']);
                $isClass = true;
                break;
            case 'WM_term_major':
                $mask = $GLOBALS['sysRoles']['all'] ^ ($GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']);
                break;
            default:
                return false;
        }
        if (is_array($users)) {
            if ($isClass) {
                foreach ($users as $user) {
                	list($username) = dbGetStSr($table,'username',"class_id='{$cid}' and username='{$user}' and role=0", ADODB_FETCH_NUM);
                    if (WMcharacter::_isExist($cid, $user, $table) || !empty($username)) {
                        dbSet($table, "role=role&{$mask}|{$role}", "class_id={$cid} and username='{$user}' limit 1");
                    } else {
                        dbNew($table, 'class_id, username,role', "$cid,'$user',$role");
                    }
                } 
            }  else {
                foreach ($users as $user) {
                	list($username) = dbGetStSr($table,'username',"course_id='{$cid}' and username='{$user}' and role=0", ADODB_FETCH_NUM);
                    if (WMcharacter::_isExist($cid, $user, $table) || !empty($username)) {
                        dbSet($table, "role=role&{$mask}|{$role}", "username='{$user}' and course_id={$cid} limit 1");
                    } else {
                        dbNew($table, 'username,course_id,role,add_time', "'$user',$cid,$role,now()");
                    }
                }
            }
        } else {
        	// 判斷是否有被移除
        	if ($isClass) {
        		list($username) = dbGetStSr($table,'username',"class_id='{$cid}' and username='{$users}' and role=0", ADODB_FETCH_NUM);
        	} else {
        		list($username) = dbGetStSr($table,'username',"course_id='{$cid}' and username='{$users}' and role=0", ADODB_FETCH_NUM);
        	}
        	
            if (WMcharacter::_isExist($cid, $users, $table) || !empty($username)) {
                if ($isClass)
                    dbSet($table, "role=role&{$mask}|{$role}", "class_id={$cid} and username='{$users}' limit 1");
                else
                    dbSet($table, "role=role&{$mask}|{$role}", "username='{$users}' and course_id={$cid} limit 1");
            } else {
                if ($isClass)
                    dbNew($table, 'class_id, username,role', "$cid,'$users',$role");
                else
                    dbNew($table, 'username,course_id,role,add_time', "'$users',$cid,$role,now()");
            }
        }
    }
    
    /**
     * 移一個帳號或一群帳號為某身份
     *
     * @param	string|array	$users	帳號
     * @param	int				$cid	課程 ID 或 班級 ID
     * @param	int				$role	身份
     * @param	string			$table	WM_term_major 或 WM_class_member
     * @param	string			$table2	額外要清的 table (WM_term_teaacher 或 WM_class_director)
     */
    function _remove($users, $cid, $role, $table, $table2)
    {
        $mask = $GLOBALS['sysRoles']['all'] ^ $role;
        switch ($table) {
            case 'WM_class_member':
                $isClass = true;
                break;
            case 'WM_term_major':
                $isClass = false;
                break;
            default:
                return false;
        }
        
        if (is_array($users)) {
            $c = count($users);
            if ($isClass) {
                foreach ($users as $user) {
                    dbSet($table, 'role=role&' . $mask, "class_id={$cid} and username='{$user}' limit 1");
                }
                dbDel($table2, "class_id={$cid} and username in ('" . implode("','", $users) . "') limit " . $c);
            } else {
                foreach ($users as $user) {
                    dbSet($table, 'role=role&' . $mask, "username='{$user}' and course_id={$cid} limit 1");
                }
                dbDel($table2, "username in ('" . implode("','", $users) . "') and course_id={$cid} limit " . $c);
            }
            dbDel($table, 'role=0 limit ' . $c);
        } else {
            if ($isClass) {
                dbSet($table, 'role=role&' . $mask, "class_id={$cid} and username='{$users}' limit 1");
                dbDel($table2, "class_id={$cid} and username='{$users}' limit 1");
            } else {
                dbSet($table, 'role=role&' . $mask, "username='{$users}' and course_id={$cid} limit 1");
                dbDel($table2, "username='{$users}' and course_id={$cid} limit 1");
            }
            dbDel($table, 'role=0 limit 1');
        }
    }
    
    /**
     * 取得某課(班)的人員
     *
     * @param	int		$cid	課程 ID 或 班級 ID
     * @param	int		$role	身份
     * @param	string	$table	WM_term_major 或 WM_class_member
     * @return	array			array($username => $role)
     */
    function _listC2U($cid, $role, $table)
    {
        return dbGetAssoc($table, 'username,role', ($table == 'WM_term_major' ? 'course' : 'class') . '_id=' . $cid . ' and role&' . intval($role));
    }
    
    /**
     * 取得某人員有某權限的課(班)
     *
     * @param	string	$user	user 帳號
     * @param	int		$role	身份
     * @param	string	$table	WM_term_major 或 WM_class_member
     * @return	array			array($cid => $role)
     */
    function _listU2C($user, $role, $table)
    {
        return dbGetAssoc($table, ($table == 'WM_term_major' ? 'course' : 'class') . '_id,role&' . intval($role), 'username="' . $user . '" and role&' . intval($role));
    }
    
    /**
     * 取得某些課或某些班的班名或課名
     *
     * @param	array	$cids	班或課的 IP 之陣列
     * @param	string	$table	WM_class_main 或 WM_term_course
     * @return	array			array($cid => title)
     */
    function _getLocaleCaption($cids, $table)
    {
        if ($table == 'WM_class_main')
            $titles = dbGetAssoc('WM_class_main', 'class_id,caption', 'class_id in (' . implode(',', $cids) . ')');
        else
            $titles = dbGetAssoc('WM_term_course', 'course_id,caption', 'course_id in (' . implode(',', $cids) . ')');
        
        $locale_titles = array();
        foreach ($titles as $k => $v) {
            $x = getCaption($v);
            if (($locale_titles[$k] = trim($x[$GLOBALS['sysSession']->lang])) == '') // 取本語系的課名
                {
                $x                 = explode(chr(9), trim(implode(chr(9), $x))); // 如果本語系課名是空的，就取第一個有名字的
                $locale_titles[$k] = $x[0];
            }
        }
        
        return $locale_titles;
    }
}


/**
 * 課程之講師、教師、助教 指定、移除 API 類別
 */
class WMteacher extends WMcharacter
{
    
    /**
     * 指定{教師|講師|助教}
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']})
     * @param	int		$course_id	課程 ID (未指定即表示 $sysSession->course_id)
     */
    function assign($user, $role, $course_id = null)
    {
        if ($role != $GLOBALS['sysRoles']['teacher'] && $role != $GLOBALS['sysRoles']['instructor'] && $role != $GLOBALS['sysRoles']['assistant'])
            return false;
        
        if (is_null($course_id))
            $course_id = $GLOBALS['sysSession']->course_id;
        
        parent::_assign($user, $course_id, $role, 'WM_term_major');
    }
    
    
    /**
     * 移除{教師|講師|助教}
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']}) 不設表示三者皆移
     * @param	int		$course_id	課程 ID (未指定即表示 $sysSession->course_id)
     */
    function remove($user, $role = null, $course_id = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        if (is_null($course_id))
            $course_id = $GLOBALS['sysSession']->course_id;
        
        parent::_remove($user, $course_id, $role, 'WM_term_major', 'WM_term_teacher');
    }
    
    /**
     * 列出某人所具有身份的課程
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']}) 不設表示三者皆抓
     * @return	array				array($course_id => $role)
     */
    function listCourse($user, $role = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        return parent::_listU2C($user, $role, 'WM_term_major');
    }
    
    /**
     * 列出某課程具身份的使用者
     *
     * @param	int		$course_id	課程 ID
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']}) 不設表示三者皆抓
     * @return	array				array($username => $role)
     */
    function listMember($course_id = null, $role = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        if (is_null($course_id))
            $course_id = $GLOBALS['sysSession']->course_id;
        
        return parent::_listC2U($course_id, $role, 'WM_term_major');
    }
    
    /**
     * 取得指定課程的標題 (只有本地語系)
     *
     * @param	array	$course_ids		課程 ID 陣列
     * @return	array					array($course_id => $title)
     */
    function getLocaleCaption($course_ids)
    {
        return parent::_getLocaleCaption($course_ids, 'WM_term_course');
    }
}


/**
 * 班級之導師、助教 指定、移除 API 類別
 */
class WMdirector extends WMcharacter
{
    
    /**
     * 指定{導師|助教}
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['direct'] $GLOBALS['sysRoles']['assistant']})
     * @param	int		$class_id	班級 ID (未指定即表示 $sysSession->class_id)
     */
    function assign($user, $role, $class_id = null)
    {
        // Bug#1441-增加$GLOBALS['sysRoles']['student'] => 刪除助教身份時，是將其變換成學員身份 by Small 2006/11/7
        if ($role != $GLOBALS['sysRoles']['director'] && $role != $GLOBALS['sysRoles']['assistant'] && $role != $GLOBALS['sysRoles']['student'])
            return false;
        
        if (is_null($class_id))
            $class_id = $GLOBALS['sysSession']->class_id;
        
        parent::_assign($user, $class_id, $role, 'WM_class_member');
    }
    
    /**
     * 移除{導師|助教}
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['teacher'] | $GLOBALS['sysRoles']['instructor'] | $GLOBALS['sysRoles']['assistant']}) 不設表示三者皆移
     * @param	int		$class_id	班級 ID (未指定即表示 $sysSession->class_id)
     */
    function remove($user, $role = null, $class_id = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant'] | $GLOBALS['sysRoles']['student']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        if (is_null($class_id))
            $class_id = $GLOBALS['sysSession']->class_id;
        
        parent::_remove($user, $class_id, $role, 'WM_class_member', 'WM_class_director');
    }
    
    /**
     * 列出某人所具有身份的班級
     *
     * @param	string	$user		user帳號
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant']}) 不設表示兩者皆抓
     * @return	array				array($class_id => $role)
     */
    function listClass($user, $role = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        return parent::_listU2C($user, $role, 'WM_class_member');
    }
    
    /**
     * 列出某班級具身份的使用者
     *
     * @param	int		$class_id	班級 ID
     * @param	int		$role		身份代號 (只能是 {$GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant']}) 不設表示三者皆抓
     * @return	array				array($username => $role)
     */
    function listMember($class_id = null, $role = null)
    {
        $allPerm = ($GLOBALS['sysRoles']['director'] | $GLOBALS['sysRoles']['assistant']);
        
        if (is_null($role))
            $role = $allPerm;
        elseif ($role & $allPerm == 0)
            return false;
        
        if (is_null($class_id))
            $class_id = $GLOBALS['sysSession']->class_id;
        
        return parent::_listC2U($class_id, $role, 'WM_class_member');
    }
    
    /**
     * 取得指定班級的標題 (只有本地語系)
     *
     * @param	array	$class_ids		班級 ID 陣列
     * @return	array					array($class_id => $title)
     */
    function getLocaleCaption($class_ids)
    {
        return parent::_getLocaleCaption($class_ids, 'WM_class_main');
    }
    
}