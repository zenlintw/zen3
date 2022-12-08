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

$MSG['tab_step1'] = array(
    'Big5'			=> '第一步：選擇安裝檔案',
    'GB2312'		=> '第一步：选择安装档案',
    'en'			=> 'Step 1: Choose installation file.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['tab_step2'] = array(
    'Big5'			=> '第二步：進行安裝',
    'GB2312'		=> '第二步：进行安装',
    'en'			=> 'Step 2: Start installation.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['tab_step3'] = array(
    'Big5'			=> '第三步：檢查核對',
    'GB2312'		=> '第三步：检查核对',
    'en'			=> 'Step 3: Make validation.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['tab_step4'] = array(
    'Big5'			=> '第四步：寄發通知信',
    'GB2312'		=> '第四步：寄发通知信',
    'en'			=> 'Step 4: Send Notification mail.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['step1_desc'] = array(
        'Big5'			=> '注意：本功能主要是提供系統維護人員進行程式更新的所設計。在進行之前，請<font color="red">詳閱每步驟的說明</font>。<br>
                                        說明：<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. 系統更新安裝包是採用tarball的方式壓縮的，副檔案規定為.tgz。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. 為了確定在檔案傳輸過程中，檔案是正確無誤的，操作人員必須輸入MD5作為驗證。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. 若不知MD5碼，請操作人員向提供更新安裝包的研發人員索取。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4. 檔案必須先上傳至LMS平台，您可使用的上傳方式為以下三種方式之一：<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(1) 利用Web資料匣上傳。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2) 利用本畫面所提供的上傳機制。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(3) 或是利用ftp或sftp的方式。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5. 此檔案命名，除了副檔名需為.tgz外，其檔名必須以"FIX_"、"Patch_"、"Upgrade_"、"Custom_"為首，<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;且檔案放置於/base/10001/door目錄下。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6. 更新檔說明文件檔名需命名為「README」，內容可使用一般文字檔或html檔。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;7. 因應<font color="red">系統資安的唯讀設定</font>，將Wmpro的功能目錄都設定為唯讀。此線上更新<font color="red">改採用crontab背景排程更新</font>，而非之前的即時更新，但仍依序執行原四個更新步驟後，以避免造成更新lock。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;8. 排程更新程式，會在更新列表呈現後續的更新資訊，請更新人員要<font color="red">重新整理"線上更新列表"</font>以查看更新狀況，如指令的數字是否有變化。
                                        ',
        'GB2312'		=> '注意：本功能主要是提供系统维护人员进行程式更新的所设计。在进行之前，请<font color="red">详阅每步骤的说明</font>。<br>
                                        说明：<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. 系统更新安装包是采用tarball的方式压缩的，副档案规定为.tgz。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. 为了确定在档案传输过程中，档案是正确无误的，操作人员必须输入MD5作为验证。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. 若不知MD5码，请操作人员向提供更新安装包的研发人员索取。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4. 档案必须先上传至LMS平台，您可使用的上传方式为以下三种方式之一：<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(1) 利用Web资料匣上传。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2) 利用本画面所提供的上传机制。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(3) 或是利用ftp或sftp的方式。<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5. 此档案命名，除了副档名需为.tgz外，其档名必须以"FIX_"为首，且档案放置于/base/10001/door目录下。
                                        ',
        'en'			=> '',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step2_desc'] = array(
        'Big5'			=> '檔案列表為本次更新檔所解開的內容。目錄名稱以[D]開頭，而檔案名稱則以[F]開頭表示。',
        'GB2312'		=> '档案列表为本次更新档所解开的内容。目录名称以[D]开头，而档案名称则以[F]开头表示。',
        'en'			=> '',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step3_desc'] = array(
        'Big5'			=> '更新檔案程序如下所列：',
        'GB2312'		=> '更新档案程序如下所列：',
        'en'			=> 'File updated are as follow: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step1_th_radio'] = array(
        'Big5'			=> '請選擇',
        'GB2312'		=> '请选择',
        'en'			=> 'Please choose ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step1_th_filename'] = array(
        'Big5'			=> '檔名',
        'GB2312'		=> '档名',
        'en'			=> 'filename',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step1_th_filesize'] = array(
        'Big5'			=> '檔案大小',
        'GB2312'		=> '档案大小',
        'en'			=> 'file size',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['step1_th_filemtime'] = array(
        'Big5'			=> '檔案日期',
        'GB2312'		=> '档案日期',
        'en'			=> 'file date',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['lbl_upload_files'] = array(
        'Big5'			=> '上傳安裝檔案：',
        'GB2312'		=> '上传安装档案：',
        'en'			=> 'upload install files.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['btn_upload_files'] = array(
        'Big5'			=> '進行上傳',
        'GB2312'		=> '进行上传',
        'en'			=> 'file uploading',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['btn_do_step2'] = array(
        'Big5'			=> '進行解壓縮，至第二步進行安裝',
        'GB2312'		=> '进行解压缩，至第二步进行安装',
        'en'			=> 'Process decompression and go to step 2 to start installation.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['lbl_md5'] = array(
        'Big5'			=> '<font color="red">*</font>輸入MD5碼：&nbsp;',
        'GB2312'		=> '输入MD5码：&nbsp;',
        'en'			=> 'Input MD5 code: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['lbl_user'] = array(
        'Big5'			=> '<font color="red">*</font>輸入更新者名稱：&nbsp;',
        'GB2312'		=> '输入更新者名称：&nbsp;',
        'en'			=> 'Input Operator name: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);


$MSG['alert_msg_select_upload'] = array(
        'Big5'			=> '請先選擇「上傳檔案」！',
        'GB2312'		=> '请先选择“上传档案”！',
        'en'			=> 'Please choose file to upload first!',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['alert_msg_filename_error'] = array(
        'Big5'			=> '上傳檔案的檔名命名錯誤，請詳見本頁的說明！',
        'GB2312'		=> '上传档案的档名命名错误，请详见本页的说明！',
        'en'			=> 'The name of file uploaded is incorrect, please read direction of this page.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['alert_msg_select_one'] = array(
        'Big5'			=> '尚未選擇任何安裝檔，請點選！',
        'GB2312'		=> '尚未选择任何安装档，请点选！',
        'en'			=> 'You do not choose any installation file. Please choose one!',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['alert_msg_md5_notfilled'] = array(
        'Big5'			=> '請填寫MD5的字串，作為檔案驗證。',
        'GB2312'		=> '请填写MD5的字串，作为档案验证。',
        'en'			=> 'Please input MD5 encrypted string to verify this file. ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['alert_msg_user_notfilled'] = array(
        'Big5'			=> '請填寫更新者名稱，以作為記錄。',
        'GB2312'		=> '请填写更新者名称，以作为记录。',
        'en'			=> 'Please input user to trace. ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['btn_do_step3'] = array(
        'Big5'			=> '設定更新排程，並檢查步驟',
        'GB2312'		=> '进行安装，并检查步骤',
        'en'			=> 'Installation processing and check installation steps.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['btn_do_step4'] = array(
        'Big5'			=> '進行通知信',
        'GB2312'		=> '进行通知信',
        'en'			=> 'Notification email of installation',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['btn_do_final'] = array(
        'Big5'			=> '完成更新程序，回列表！',
        'GB2312'		=> '完成更新程序，回列表！',
        'en'			=> 'Live update is done, you can return to list.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_from'] = array(
        'Big5'			=> '寄件者：',
        'GB2312'		=> '寄件者：',
        'en'			=> 'From: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_to'] = array(
        'Big5'			=> '收件者：',
        'GB2312'		=> '收件者：',
        'en'			=> 'To: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_to_msg'] = array(
        'Big5'			=> '如果同時要送給多位收件者，請用半形的逗點 , 分號 ; 或空白將帳號分開',
        'GB2312'		=> '如果同时要送给多位收件者，请用半形的逗点 , 分号 ; 或空白将帐号分开',
        'en'			=> 'If want send to many user, use [,] [;] or [space] split username',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_subject'] = array(
        'Big5'			=> '主旨：',
        'GB2312'		=> '主旨：',
        'en'			=> 'Subject: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_subject_msg'] = array(
        'Big5'			=> '限填 200 個英文字或 100 個中文字',
        'GB2312'		=> '限填 200 个英文字或 100 个中文字',
        'en'			=> 'Max 200 letters',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['write_content'] = array(
        'Big5'			=> '內容：',
        'GB2312'		=> '内容：',
        'en'			=> 'Content: ',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['tab_list'] = array(
        'Big5'			=> '線上更新',
        'GB2312'		=> '线上更新',
        'en'			=> 'Live update',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th1'] = array(
        'Big5'			=> '日期',
        'GB2312'		=> '日期',
        'en'			=> 'Date',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th2'] = array(
        'Big5'			=> '狀況',
        'GB2312'		=> '状况',
        'en'			=> 'Status',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th3_1'] = array(
        'Big5'			=> '上傳檔名',
        'GB2312'		=> '上传档名',
        'en'			=> 'Upload file name',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th3_2'] = array(
        'Big5'			=> '版本次',
        'GB2312'		=> 'version',
        'en'			=> 'version',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th4'] = array(
        'Big5'			=> '更新者IP',
        'GB2312'		=> '更新者IP',
        'en'			=> 'Update user IP',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th4_1'] = array(
        'Big5'			=> '更新者',
        'GB2312'		=> '更新者',
        'en'			=> 'Update Operator',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th5'] = array(
        'Big5'			=> '是否回復',
        'GB2312'		=> '是否回复',
        'en'			=> 'Recover',
        'EUC-JP'		=> '',
        'user_define'	=> ''
);

$MSG['list_th6'] = array(
    'Big5'			=> '更新(或客製清單)',
    'GB2312'		=> '更新(或客制清单)',
    'en'			=> 'change list',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['list_th7'] = array(
    'Big5'			=> '程式列表',
    'GB2312'		=> '程式列表',
    'en'			=> 'files',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);


$MSG['btn_do_update'] = array(
    'Big5'			=> '進行線上更新',
    'GB2312'		=> '进行线上更新',
    'en'			=> 'Live update.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['btn_rollback'] = array(
    'Big5'			=> '回復更新前',
    'GB2312'		=> '回复更新前',
    'en'			=> 'If updated, please recover to the previous version.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['cofirm_rollback'] = array(
    'Big5'			=> '確定要進行程式回覆動作嗎？',
    'GB2312'		=> '确定要进行程式回覆动作吗？',
    'en'			=> 'Are you sure to take recover action?',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['tab_rollback'] = array(
    'Big5'			=> '回覆動作之預覽',
    'GB2312'		=> '回覆动作之预览',
    'en'			=> 'Preview recover action.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['step_rollback_desc'] = array(
    'Big5'			=> '預覽：回覆動作的程序是否正確，若無誤，請按表格下方的按鍵進行',
    'GB2312'		=> '预览：回覆动作的程序是否正确，若无误，请按表格下方的按键进行',
    'en'			=> 'Preview: If the recover procedure is correct , please press the button in the bottom of table.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['btn_go_rollback'] = array(
    'Big5'			=> '進行回復',
    'GB2312'		=> '进行回复',
    'en'			=> 'Process recovering.',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['error_md5'] = array(
    'Big5'			=> 'MD5驗證失敗！\n回上一個程序，重新動作。',
    'GB2312'		=> 'MD5验证失败！\n回上一个程序，重新动作。',
    'en'			=> 'Error MD5 Value',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['btn_more'] = array(
    'Big5'			=> '我看看',
    'GB2312'		=> 'detail',
    'en'			=> 'detail',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['sys_ver'] = array(
    'Big5'		=> '系統版本',
    'GB2312'		=> '系统版本',
    'en'		=> 'System version',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['xmlapi_ver'] = array(
    'Big5'		=> 'XML api 版本',
    'GB2312'		=> 'XML api 版本',
    'en'		=> 'XML api version',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['sys_info'] = array(
    'Big5'		=> '系統資訊',
    'GB2312'		=> '系统资讯',
    'en'		=> 'System information',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['th_item'] = array(
    'Big5'		=> '項目',
    'GB2312'		=> '项目',
    'en'		=> 'Item',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['th_info'] = array(
    'Big5'		=> '資訊',
    'GB2312'		=> '资讯',
    'en'		=> 'Information',
    'EUC-JP'		=> '',
    'user_define'	=> ''
);

$MSG['wait_cron_update'] = array(
    'Big5'      => '等待排程更新',
    'GB2312'        => '等待排程更新',
    'en'        => 'wait for schedule update',
    'EUC-JP'        => '',
    'user_define'   => ''
);

$MSG['untar_and_remove'] = array(
    'Big5'      => '所選擇的安裝檔在解壓縮之後，要同步進行刪除：',
    'GB2312'        => '所選擇的安裝檔在解壓縮之後，要同步進行刪除：',
    'en'        => 'remove this file after untar:',
    'EUC-JP'        => '',
    'user_define'   => ''
);

$MSG['instruction_number_info'] = array(
    'Big5'      => '目前正準備要安裝的更新指令檔的個數是：',
    'GB2312'        => '目前正準備要安裝的更新指令檔的個數是：',
    'en'        => 'wait to instruction number:',
    'EUC-JP'        => '',
    'user_define'   => ''
);
