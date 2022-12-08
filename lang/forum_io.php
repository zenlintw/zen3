<?php
    /**
     * 語系檔
     *
     * PHP 4.4.2+, MySQL 4.0.17+, Apache 1.3.34+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-4-3
     */

	$MSG['no_import_data'] = array(
		'Big5'			=> '在包裝檔中, 找不到資料檔(%s)',
		'GB2312'		=> '在包装档中, 找不到资料档(%s)',
		'en'			=> 'No imported data(%s) in file.',
		'EUC-JP'		=> 'There is no import data(%s) in file.',
		'user_define'	=> 'There is no import data(%s) in file.'
	);

	$MSG['tmpdir_fail'] = array(
		'Big5'			=> '建立暫存目錄失敗!',
		'GB2312'		=> '建立暂存目录失败!',
		'en'			=> 'Failed to create a temporary directory.',
		'EUC-JP'		=> 'Create temperatory directory  failed..',
		'user_define'	=> 'Create temperatory directory  failed..'
	);

	$MSG['msg_imp_err_0'] = array(
		'Big5'			=> '成功!',
		'GB2312'		=> '成功!',
		'en'			=> 'Succeeded!',
		'EUC-JP'		=> 'OK.',
		'user_define'	=> 'OK.'
	);

	$MSG['msg_imp_err_1'] = array(
		'Big5'			=> '檔案不存在!',
		'GB2312'		=> '档案不存在!',
		'en'			=> 'File not found!',
		'EUC-JP'		=> 'File Not Exists!',
		'user_define'	=> 'File Not Exists!'
	);

	$MSG['msg_imp_err_2'] = array(
		'Big5'			=> 'XML 檔解析失敗!',
		'GB2312'		=> 'XML 档解析失败!',
		'en'			=> 'XML parsing error!',
		'EUC-JP'		=> 'xml parsing error!',
		'user_define'	=> 'xml parsing error!'
	);

	$MSG['msg_imp_err_3'] = array(
		'Big5'			=> 'XML 根節點不是 &lt;data&gt;!',
		'GB2312'		=> 'XML 根节点不是 &lt;data&gt;!',
		'en'			=> 'XML root node must be &lt;data&gt;',
		'EUC-JP'		=> 'xml root tagname must be &lt;data&gt;',
		'user_define'	=> 'xml root tagname must be &lt;data&gt;'
	);

	$MSG['msg_imp_err_4'] = array(
		'Big5'			=> '資料版本必須是 "%s"!',
		'GB2312'		=> '资料版本必须是 %s!',
		'en'			=> 'Data version must be %s',
		'EUC-JP'		=> 'data version must be "%s"',
		'user_define'	=> 'data version must be "%s"'
	);

	$MSG['msg_imp_err_5'] = array(
		'Big5'			=> '資料類型必須是 "%s"!',
		'GB2312'		=> '资料类型必须是 %s!',
		'en'			=> 'Data type must be %s',
		'EUC-JP'		=> 'data type must be "%s"',
		'user_define'	=> 'data type must be "%s"'
	);

	$MSG['msg_imp_err_6'] = array(
		'Big5'			=> 'xml 資料找不到子節點!',
		'GB2312'		=> 'xml 资料找不到子节点!',
		'en'			=> 'XML data must contain child nodes.',
		'EUC-JP'		=> 'data(xml) must contain children nodes.',
		'user_define'	=> 'data(xml) must contain children nodes.'
	);

	$MSG['msg_imp_err_7'] = array(
		'Big5'			=> '附檔格式錯誤!',
		'GB2312'		=> '附件档格式错误!',
		'en'			=> 'Invalid attachment format.',
		'EUC-JP'		=> 'wrong attachment format.',
		'user_define'	=> 'wrong attachment format.'
	);

	$MSG['msg_imp_err_8'] = array(
		'Big5'			=> '在包裝檔中找不到指定附檔!',
		'GB2312'		=> '在包装档中找不到指定附件档!',
		'en'			=> 'Unable to find attachments!.',
		'EUC-JP'		=> 'attached file(s) missing.',
		'user_define'	=> 'attached file(s) missing.'
	);

	$MSG['msg_imp_err_9'] = array(
		'Big5'			=> '附檔無法匯入(可能因為附檔遺失, 或是上載空間限制...等因素)',
		'GB2312'		=> '附件档无法导入(可能因为附件档遗失, 或是上载空间限制...等因素)',
		'en'			=> 'Unable to import attachments. Failure may be caused by losing files or exceeding uploading limit.',
		'EUC-JP'		=> 'attached file(s) can not be uploaded, ( maybe attaches lost , or quota limit ... etc. ).',
		'user_define'	=> 'attached file(s) can not be uploaded, ( maybe attaches lost , or quota limit ... etc. ).'
	);

	$MSG['msg_imp_err_10'] = array(
		'Big5'			=> '必須先呼叫 bbsPost->init() !',
		'GB2312'		=> '必须先呼叫 bbsPost->init() !',
		'en'			=> 'Need to call bbsPost->init() first.',
		'EUC-JP'		=> 'need to call bbsPost->init() first.',
		'user_define'	=> 'need to call bbsPost->init() first.'
	);

	$MSG['msg_imp_err_11'] = array(
		'Big5'			=> '儲存附檔失敗, 請由編修來手動附上附檔 !',
		'GB2312'		=> '保存附件档失败, 请由修改来手动附上附件档 !',
		'en'			=> 'Failed to save attached file(s). Please click Edit to upload your attachments.',
		'EUC-JP'		=> 'save attached file(s) failed, please upload the attached by edit.',
		'user_define'	=> 'save attached file(s) failed, please upload the attached by edit.'
	);

	$MSG['msg_imp_err_12'] = array(
		'Big5'			=> '儲存到資料庫失敗, 可能是 Server 忙碌中, 請稍後再試 !',
		'GB2312'		=> '保存到资料库失败, 可能是 Server 忙碌中, 请稍后再试 !',
		'en'			=> 'Failed to save data. Server may be busy. Please try again later.',
		'EUC-JP'		=> 'save to database failed, server may be too busy, please try later.',
		'user_define'	=> 'save to database failed, server may be too busy, please try later.'
	);

	$MSG['msg_imp_err_13'] = array(
		'Big5'			=> '不明的討論板類型, 必須是"一般討論板"或是"精華區".',
		'GB2312'		=> '不明的讨论板类型, 必须是一般讨论板或是精华区.',
		'en'			=> 'Unknown board type. It must be Discussion Forum or Essential.',
		'EUC-JP'		=> 'unknown board type, must be "forum" or "quint".',
		'user_define'	=> 'unknown board type, must be "forum" or "quint".'
	);

	$MSG['msg_imp_err_14'] = array(
		'Big5'			=> '儲存日期欄位失敗,請由編修進入修改.',
		'GB2312'		=> '保存日期栏位失败,请由修改进入修改.',
		'en'			=> 'Failed to save date. Please click Edit or Modify to edit.',
		'EUC-JP'		=> 'save news time failed, please modify it by edit.',
		'user_define'	=> 'save news time failed, please modify it by edit.'
	);

	$MSG['msg_imp_err_15'] = array(
		'Big5'			=> '允許使用檔案空間已滿.',
		'GB2312'		=> '允许使用档案空间已满.',
		'en'			=> 'Quota full!',
		'EUC-JP'		=> 'File(s) Quota full.',
		'user_define'	=> 'File(s) Quota full.'
	);

	$MSG['import_all'] = array(
		'Big5'			=> '整板匯入',
		'GB2312'		=> '整板汇入',
		'en'			=> 'Import All',
		'EUC-JP'		=> 'Import ALL ',
		'user_define'	=> 'Import ALL '
	);

	$MSG['board'] = array(
		'Big5'			=> '討論板',
		'GB2312'		=> '讨论板',
		'en'			=> 'Discussion Forum',
		'EUC-JP'		=> 'Board ',
		'user_define'	=> 'Board '
	);

	$MSG['quint'] = array(
		'Big5'			=> '精華區',
		'GB2312'		=> '精华区',
		'en'			=> 'Essential',
		'EUC-JP'		=> 'Collection ',
		'user_define'	=> 'Collection '
	);

	$MSG['post'] = array(
		'Big5'			=> '文章',
		'GB2312'		=> '文章',
		'en'			=> 'Post',
		'EUC-JP'		=> 'post ',
		'user_define'	=> 'post '
	);

	$MSG['msg_create_board'] = array(
		'Big5'			=> '建立討論板 "%s" 失敗!',
		'GB2312'		=> '建立讨论板 "%s" 失败!',
		'en'			=> 'Failed to create new board %s!',
		'EUC-JP'		=> 'Create new board "%s" failed!',
		'user_define'	=> 'Create new board "%s" failed!'
	);

	$MSG['msg_create_qfolder'] = array(
		'Big5'			=> '建立精華區目錄 "%s" 失敗!',
		'GB2312'		=> '建立精华区目录 %s 失败!',
		'en'			=> 'Failed to create new Essential directory %s!',
		'EUC-JP'		=> 'Create collection folder "%s" failed!',
		'user_define'	=> 'Create collection folder "%s" failed!'
	);

	$MSG['backto_list'] = array(
		'Big5'			=> '回列表',
		'GB2312'		=> '回列表',
		'en'			=> 'Back to List',
		'EUC-JP'		=> 'Back to List',
		'user_define'	=> 'Back to List'
	);

	$MSG['finished'] = array(
		'Big5'			=> '完成',
		'GB2312'		=> '完成',
		'en'			=> 'Finished',
		'EUC-JP'		=> 'finished ',
		'user_define'	=> 'finished '
	);

	$MSG['import_all_success'] = array(
		'Big5'			=> '整板匯入"%s"成功，您可以在教師辦公室的「討論板管理」做進階的設定。或在議題討論列表中找到這個討論板(如果此討論板是開放的)。',
		'GB2312'		=> '整板汇入"%s"成功\，您可以在教师办公室的“讨论板管理”做进阶的设定。或在议题讨论列表中找到这个讨论板(如果此讨论板是开放的)。',
		'en'			=> 'All successfully imported to %s. Go to instructor&#039;s office for advenced settings. This board can be found in Topic Dicussions.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['goto_import_board'] = array(
		'Big5'			=> '進入此討論板',
		'GB2312'		=> '进入此讨论板',
		'en'			=> 'Enter this board',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

?>
