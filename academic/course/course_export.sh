#!/bin/sh

LANG=zh_TW.UTF-8
export LANG
MYSQLDUMP=$2
MYSQL=$1
SED=`which sed`
EXPR=`which expr`
PASTE=`which paste`

X1=`pwd`
X1=`dirname $X1`
PWD=`dirname $X1`
WMDB=$3
COURSE_ID=$4
OUTPUT_FILE="$PWD/base/"`echo $3|$SED 's/^.*_//'`"/course/$4/install.sql"
OUTPUT_CHAT="$PWD/base/"`echo $3|$SED 's/^.*_//'`"/course/$4/chat.lst"
OUTPUT_EX="$PWD/base/"`echo $3|$SED 's/^.*_//'`"/course/$4/qti_exam.lst"
OUTPUT_HW="$PWD/base/"`echo $3|$SED 's/^.*_//'`"/course/$4/qti_hw.lst"
OUTPUT_QS="$PWD/base/"`echo $3|$SED 's/^.*_//'`"/course/$4/qti_qs.lst"

###
### 匯出課程 instance
###
$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_term_course | \
	$SED -e '/^--/d' -e '/^$/d' -e "s/$COURSE_ID/NULL/" > $OUTPUT_FILE

echo -e "SET @new_course_id=LAST_INSERT_ID();\n" >> $OUTPUT_FILE

##
## 匯出課程之課程介紹、課程安排、教師介紹
##
if [ `$EXPR $5 : ".*:course_intro:.*"` -ne 0 ]; then
	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_term_introduce | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/VALUES ($COURSE_ID,/VALUES (@new_course_id,/" >> $OUTPUT_FILE
	echo 'update WM_term_introduce set content=REPLACE(content,"/'$COURSE_ID'/",CONCAT("/",@new_course_id,"/")) where course_id=@new_course_id;' >> $OUTPUT_FILE
fi

##
## 匯出學習路徑
##
if [ `$EXPR $5 : ".*:course_path:.*"` -ne 0 ]; then
	$MYSQLDUMP -w "course_id=$COURSE_ID ORDER BY serial DESC LIMIT 1" $WMDB WM_term_path | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/VALUES ($COURSE_ID,[0-9]\{1,\},/VALUES (@new_course_id,1,/" >> $OUTPUT_FILE
fi

##
## 匯出 WM_acl_list, WM_acl_member
##
if [ `$EXPR $5 : ".*:permission_acl:.*"` -ne 0 ]; then
	echo "SET @acl_id_mapping='';" >> $OUTPUT_FILE

	$MYSQLDUMP -w "unit_id=$COURSE_ID" $WMDB WM_acl_list | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" \
		     -e 's/VALUES (\([0-9]*\),\(.*\)$/VALUES (NULL,\2\
SET @new_aid_\1=LAST_INSERT_ID();\
SET @acl_id_mapping=concat(@acl_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

	echo "SET @acl_id_mapping=SUBSTRING(@acl_id_mapping,2);" >> $OUTPUT_FILE

	AID=`echo "select DISTINCT acl_id from WM_acl_list where unit_id=$COURSE_ID order by acl_id" | $MYSQL $WMDB | $PASTE -s -d, -`
	$MYSQLDUMP -w 'acl_id in('$AID')' $WMDB WM_acl_member| \
		$SED -e '/^--/d' -e '/^$/d' -e 's/VALUES (\([0-9]*\),/VALUES (@new_aid_\1,/' >> $OUTPUT_FILE
fi

##
## 匯出 WM_bbs_board
##

if [ `$EXPR \( $5 : '.*:subject_board:.*' \) = 0` -eq 1 ]; then
	BID=`echo "select concat(discuss,',',bulletin) from WM_term_course where course_id=$COURSE_ID" | $MYSQL $WMDB`
else
	BID=`echo "select discuss from WM_term_course where course_id=$COURSE_ID union select bulletin from WM_term_course where course_id=$COURSE_ID union select board_id from WM_term_subject where course_id=$COURSE_ID" | $MYSQL $WMDB | $PASTE -s -d, -`
fi

echo "SET @board_id_mapping='';" >> $OUTPUT_FILE

$MYSQLDUMP -w 'board_id in ('$BID')' $WMDB WM_bbs_boards | \
	$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" \
	     -e 's/VALUES (\([0-9]*\),\(.*\)$/VALUES (NULL,\2\
SET @new_bid_\1=LAST_INSERT_ID();\
SET @board_id_mapping=concat(@board_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

echo "SET @board_id_mapping=SUBSTRING(@board_id_mapping,2);" >> $OUTPUT_FILE

if [ `$EXPR $5 : ".*:course_board:.*"` -ne 0 ]; then
	$MYSQLDUMP -w 'board_id in('$BID')' $WMDB WM_bbs_posts| \
		$SED -e '/^--/d' -e '/^$/d' -e 's/VALUES (\([0-9]*\),/VALUES (@new_bid_\1,/' >> $OUTPUT_FILE
	$MYSQLDUMP -w 'board_id in('$BID')' $WMDB WM_bbs_collecting| \
		$SED -e '/^--/d' -e '/^$/d' -e 's/VALUES (\([0-9]*\),/VALUES (@new_bid_\1,/' >> $OUTPUT_FILE
fi

##
## 匯出議題討論板
##
if [ `$EXPR $5 : ".*:subject_board:.*"` -ne 0 ]; then
	SID="course_id=$COURSE_ID"
else
	SID="course_id=$COURSE_ID and board_id in ("`echo "select concat(discuss,',',bulletin) from WM_term_course where course_id=$COURSE_ID" | $MYSQL $WMDB`")"
fi

echo "SET @subject_id_mapping='';" >> $OUTPUT_FILE

$MYSQLDUMP -w "$SID" $WMDB WM_term_subject | \
	$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" \
	     -e 's/@new_course_id,\([0-9]*\),\([0-9]*\),\(.*\)$/@new_course_id,NULL,@new_bid_\2,\3\
SET @new_sjid_\1=LAST_INSERT_ID();\
SET @subject_id_mapping=concat(@subject_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

##
## 匯出討論室
##
if [ `$EXPR $5 : ".*:chatroom:.*"` -ne 0 ]; then
	$MYSQLDUMP -w "owner='$COURSE_ID'" $WMDB WM_chat_setting | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,'$COURSE_ID\([0-9_]*\)',/,CONCAT(@new_course_id,'\1'),/" >> $OUTPUT_FILE

    echo 'select rid from WM_chat_setting where owner="'$COURSE_ID'"' | $MYSQL $WMDB > $OUTPUT_CHAT
fi

##
## 匯出作業
##
if [ `$EXPR $5 : ".*:homework:.*"` -ne 0 ]; then
	echo "SET @hw_id_mapping='';" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_homework_test | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" \
		     -e 's/VALUES (\([0-9]*\),\(.*\)$/VALUES (NULL,\2\
SET @hw_id_mapping=concat(@hw_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

	echo "SET @hw_id_mapping=SUBSTRING(@hw_id_mapping,2);" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_homework_item | \
		$SED -e '/^--/d' -e '/^$/d' \
		     -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE

	# 所有項目的 ident
	echo 'select ident from WM_qti_homework_item where course_id='$COURSE_ID | $MYSQL $WMDB > $OUTPUT_HW
fi

##
## 匯出測驗
##
if [ `$EXPR $5 : ".*:exam:.*"` -ne 0 ]; then
	echo "SET @ex_id_mapping='';" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_exam_test | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" \
		     -e 's/VALUES (\([0-9]*\),\(.*\)$/VALUES (NULL,\2\
SET @ex_id_mapping=concat(@ex_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

	echo "SET @ex_id_mapping=SUBSTRING(@ex_id_mapping,2);" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_exam_item | \
		$SED -e '/^--/d' -e '/^$/d' \
		     -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE

	# 所有項目的 ident
	echo 'select ident from WM_qti_exam_item where course_id='$COURSE_ID | $MYSQL $WMDB > $OUTPUT_EX
fi

##
## 匯出問卷
##
if [ `$EXPR $5 : ".*:questionnaire:.*"` -ne 0 ]; then
	echo "SET @qu_id_mapping='';" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_questionnaire_test | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" \
		     -e 's/VALUES (\([0-9]*\),\(.*\)$/VALUES (NULL,\2\
SET @qu_id_mapping=concat(@qu_id_mapping, ",", concat("\1=", LAST_INSERT_ID()));\
/'  >> $OUTPUT_FILE

	echo "SET @qu_id_mapping=SUBSTRING(@qu_id_mapping,2);" >> $OUTPUT_FILE

	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_questionnaire_item | \
		$SED -e '/^--/d' -e '/^$/d' \
		     -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE

	# 所有項目的 ident
	echo 'select ident from WM_qti_questionnaire_item where course_id='$COURSE_ID | $MYSQL $WMDB > $OUTPUT_QS
fi

##
## 匯出課程 Log
##
if [ `$EXPR $5 : ".*:course_log:.*"` -ne 0 ]; then
	for LOG_TABLE in classroom teacher others
	do
	$MYSQLDUMP -w "department_id=$COURSE_ID" $WMDB WM_log_$LOG_TABLE | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE
	done
fi

###
### 匯出帳號相關資料
###
if [ `$EXPR $5 : ".*:learner_.*"` -ne 0 ]; then
##
## 匯出選課記錄
##
	$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_term_major | \
		$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE

	ALL_STUDENTS='":'`echo "select username from WM_term_major where course_id=$COURSE_ID;" | $MYSQL $WMDB | xargs | $SED "s/ /:/g"`':"'
	echo $ALL_STUDENTS
##
## 匯出學員帳號
##
	if [ `$EXPR $5 : ".*:learner_account:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "LOCATE(concat(':',username,':'), $ALL_STUDENTS)" $WMDB WM_user_account | \
			$SED -e '/^--/d' -e '/^$/d' >> $OUTPUT_FILE
	fi

##
## 匯出學員分組 (小組討論板)
##
	if [ `$EXPR $5 : ".*:learner_group:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_student_separate | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" >> $OUTPUT_FILE
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_student_group | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" >> $OUTPUT_FILE
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_student_div | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" >> $OUTPUT_FILE
	fi
exit
##
## 匯出個人訊息
##
	if [ `$EXPR $5 : ".*:learner_message:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "LOCATE(concat(':',username,':'), $ALL_STUDENTS)" $WMDB WM_msg_folder | \
			$SED -e '/^--/d' -e '/^$/d' >> $OUTPUT_FILE
		$MYSQLDUMP -w "LOCATE(concat(':',receiver,':'), $ALL_STUDENTS)" $WMDB WM_msg_message | \
			$SED -e '/^--/d' -e '/^$/d' >> $OUTPUT_FILE
	fi

##
## 匯出學習路徑閱讀記錄
##
	if [ `$EXPR $5 : ".*:learner_study:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_record_reading | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" >> $OUTPUT_FILE
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_record_daily_course | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/($COURSE_ID,/(@new_course_id,/" >> $OUTPUT_FILE
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_record_daily_personal | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE
	fi

##
## 匯出繳交作業
##
	if [ `$EXPR $5 : ".*:learner_homework:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_homework_result | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/VALUES (\([0-9]*\),/VALUES (@new_hwid_\1,/" >> $OUTPUT_FILE
	fi

##
## 匯出繳交測驗
##
	if [ `$EXPR $5 : ".*:learner_exam:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_exam_result | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/VALUES (\([0-9]*\),/VALUES (@new_exid_\1,/" >> $OUTPUT_FILE
	fi

##
## 匯出繳交問卷
##
	if [ `$EXPR $5 : ".*:learner_questionnaire:.*"` -ne 0 ]; then
		$MYSQLDUMP -w "course_id=$COURSE_ID" $WMDB WM_qti_questionnaire_result | \
			$SED -e '/^--/d' -e '/^$/d' -e "s/VALUES (\([0-9]*\),/VALUES (@new_quid_\1,/" >> $OUTPUT_FILE
	fi

##
## 匯出所有 LOG
##
	if [ `$EXPR $5 : ".*:learner_logs:.*"` -ne 0 ]; then
		for LOG_TABLE in director manager
		do
			$MYSQLDUMP -w "department_id=$COURSE_ID" $WMDB WM_log_$LOG_TABLE | \
				$SED -e '/^--/d' -e '/^$/d' -e "s/,$COURSE_ID,/,@new_course_id,/" >> $OUTPUT_FILE
		done
	fi
fi

##
## 顯示所有新舊 ID 對照表
##
echo "select concat('course_id:', @new_course_id);" >> $OUTPUT_FILE
echo "select concat('acl_id_mapping:', @acl_id_mapping);" >> $OUTPUT_FILE
echo "select concat('board_id_mapping:', @board_id_mapping);" >> $OUTPUT_FILE
echo "select concat('hw_id_mapping:', @hw_id_mapping);" >> $OUTPUT_FILE
echo "select concat('ex_id_mapping:', @ex_id_mapping);" >> $OUTPUT_FILE
echo "select concat('qu_id_mapping:', @qu_id_mapping);" >> $OUTPUT_FILE
echo "select concat('subject_id_mapping:', @subject_id_mapping);" >> $OUTPUT_FILE
