#!/bin/sh
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#	WM3 安裝 SQL 檔‧匯出 shell script                                                              #
#   	1. 匯出 WM_MASTER table                                                                     #
#		2. 匯出 WM_MASTER.all_account 之 root 與研發成員帳號                                        #
#		3. 匯出 WM_MASTER.manager 之 root 與 wiseguy 帳號                                           #
#		4. 安裝預設第一個學校 (10001)                                                               #
#		5. 匯出 WM_10001 table                                                                      #
#		6. 匯出 WM_user_account 之 root 與研發成員帳號                                              #
#		7. 匯出 WM_acl_bindfile 及 WM_acl_function						    						#
#		8. 將 WM instance 序號初始化                                                                #
#		9. 設定第一個學校 (10001) 的建議板                                                          #
#		10. 設定資料庫存取帳號                                                                      #
#													By Wiseguy Liang 2007/05/23                     #
#   $id$
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

MYSQLD="/home/apps/mysql/bin/mysqldump -u root --skip-opt --create-options --allow-keywords --compact -K -q -Q -c -e"
#DEV_MEMBER="'root','gild','wiseguy','list','zenlin'"
#ROOT_MEMBER="'root','wiseguy'"
DEV_MEMBER="'root'"
ROOT_MEMBER="'root'"
TARGET_FILE=WM3_`date '+%Y%m%d'`.sql

$MYSQLD -B -d WM_MASTER																		 		> $TARGET_FILE
$MYSQLD -t --where "username in ($DEV_MEMBER)" WM_MASTER WM_all_account | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'				>> $TARGET_FILE
$MYSQLD -t --where "username in ($ROOT_MEMBER) and school_id=10001" WM_MASTER WM_manager | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'					>> $TARGET_FILE
$MYSQLD -t --where "school_id=10001 and username in ($DEV_MEMBER)" WM_MASTER WM_sch4user | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g' -e "s/(10001,'\([a-zA-Z]*\)',[^)]*)\([,;]\)/(10001,'\1',0,NULL,'',NOW(),NULL,NULL,0,0)\2/g"	>> $TARGET_FILE
echo -e "INSERT INTO WM_school (school_id, school_host, school_name, feedback, language, theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota, quota_limit, quota_used, school_mail) VALUES \
(10001,'wm3.learn.com.tw','Wisdom Master Pro v5.0',NULL,'Big5','default','N','Y','Y','noncheck',1,204800,204800,70580,'webmaster@wm3.learn.com.tw');\n" >> $TARGET_FILE

$MYSQLD -B -d WM_10001																				>> $TARGET_FILE
$MYSQLD -t --where "username in ('root',$DEV_MEMBER)" WM_10001 WM_user_account | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'				>> $TARGET_FILE
$MYSQLD -t --where "1 order by function_id" WM_10001 WM_acl_bindfile | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'						>> $TARGET_FILE
$MYSQLD -t --where "1 order by function_id" WM_10001 WM_acl_function | sed -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'						>> $TARGET_FILE
$MYSQLD -t --where "permute < 4 order by permute asc" WM_10001 WM_review_syscont | sed -e 's/([0-9]*,/(NULL,/g' -e 's/ VALUES /&\n\t/' -e 's/),(/),\n\t(/g'						>> $TARGET_FILE

#echo 'INSERT INTO WM_content (content_id,caption,path,status) VALUES ("100000", "", "", "disable");'	>> $TARGET_FILE
#echo 'DELETE from WM_content WHERE content_id=100000;'										>> $TARGET_FILE
#echo 'INSERT INTO WM_class_main (class_id, caption) VALUES (1000000,"");'					>> $TARGET_FILE
#echo 'DELETE from WM_class_main WHERE class_id=1000000;'									>> $TARGET_FILE
#echo 'INSERT INTO WM_term_course (course_id, caption, path) VALUES (10000000, "", "");'		>> $TARGET_FILE
#echo 'DELETE from WM_term_course WHERE course_id=10000000;'									>> $TARGET_FILE
#echo 'INSERT INTO WM_qti_exam_test (exam_id, title) VALUES (100000000, "");'				>> $TARGET_FILE
#echo 'DELETE from WM_qti_exam_test WHERE exam_id=100000000;'								>> $TARGET_FILE
#echo 'INSERT INTO WM_qti_homework_test (exam_id, title) VALUES (100000000, "");'			>> $TARGET_FILE
#echo 'DELETE from WM_qti_homework_test WHERE exam_id=100000000;'							>> $TARGET_FILE
#echo 'INSERT INTO WM_qti_questionnaire_test (exam_id, title) VALUES (100000000, "");'		>> $TARGET_FILE
#echo 'DELETE from WM_qti_questionnaire_test WHERE exam_id=100000000;'						>> $TARGET_FILE
echo "INSERT INTO WM_bbs_boards (board_id,bname,owner_id) VALUES (1000000001, '"'a:5:{s:4:"Big5";s:15:"系統建議板";s:6:"GB2312";s:15:"系统建议板";s:2:"en";s:17:"System suggestion";s:6:"EUC-JP";s:0:"";s:11:"user_define";s:0:"";}'"'"', 10001);' >> $TARGET_FILE

echo >> $TARGET_FILE
echo "GRANT SELECT, INSERT, UPDATE, DELETE, INDEX, ALTER, CREATE on \`WM\\_%\`.*  TO wm3@localhost IDENTIFIED BY 'WmIiI';" >> $TARGET_FILE

sed '/^--/d' $TARGET_FILE > _$TARGET_FILE
mv _$TARGET_FILE $TARGET_FILE
