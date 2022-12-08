<?php
/**
 * 列出使用者的課程列表
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category	xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-12-19
 */
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors','On');
include_once(dirname(__FILE__)."/action.class.php");
include_once(PATH_LIB .'lib_md5key.v2.php');
include_once(PATH_MODEL .'CourseModel.php');

class UserCourseList2Action extends baseAction
{
	var $username = null;
	function UserCourseListAction()
	{
		parent::baseAction();
		$this->username = $_GET['user'];
	}
	
	function getUserReadHours($user, $cid)
	{
		global $sysConn;
		$sqls = sprintf("SELECT ( UNIX_TIMESTAMP( over_time )  - UNIX_TIMESTAMP( begin_time )  ) AS cc
						FROM  `WM_record_reading` 
						WHERE course_id =%d AND username = '%s'", $cid, mysql_real_escape_string($user));
		$rs = $sysConn->Execute($sqls);
		$sum = 0;
		if ($rs)
		{
			while($row = $rs->FetchRow())
			{
				$sum += $row[0];
			}
		}
		return $sum;
	}
	
    /**
     * 取得使用者的課程列表
     * @return string
     */
    function getUserCourses()
    {
        global $sysConn, $sysRoles;
        
        $rs = &dbGetCourses('M.*,C.caption,C.teacher', $this->username, $sysRoles['auditor']|$sysRoles['student']|$sysRoles['teacher']|$sysRoles['assistant']);
        if ($rs) {
        	while($row = $rs->FetchRow()) {
        	    
        	    $cp = getCaption($row['caption']);

        		$data[] = array(
        			'course_id' => $row['course_id'],
        			'title' => $cp['Big5'],
        			'teacher' => $row['teacher'],
        			'img_url' => CourseModel::getCourseImage($row['course_id']),
        			'user_name' => $row['username'],
        			'update_datetime' => str_replace('-', '/', $row['last_login']),
        			'class_count' => $row['login_times'],
        			'read_hours' => $this->getUserReadHours($this->username, $row['course_id']),
        			'post_count' => $row['post_times'],
        			'discuss_count' => $row['dsc_times'],
        			'period' => str_replace('-','/',sprintf('%s%s',((!empty($row['co_st_begin']))?$row['co_st_begin'].' ~ ':''),$row['co_st_end']))
        		);
        	}
        }
        $header = '';
		$body = '';
        
        for($i=0, $size=count($data); $i<$size; $i++)
        {
        	$row = $data[$i];
        	if (empty($header))
        	{
        		$header = '<tr><th>'.implode('</th><th>',array_keys($row)).'</th></tr>';
        	}

			$body .= '<tr><td>'.implode('</td><td>',array_values($row)).'</td></tr>';
        }
		return '<table>'.$header.$body.'</table>';
    }

	function main()
	{
		global $sysConn;
        print_r($this->getUserCourses());
	}
}
?>
