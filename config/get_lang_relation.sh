#!/bin/sh
#
# 取得 WM3 所有程式所關聯的語系檔 (不 include 語系檔的程式 不列出)
#
# @author		Wiseguy Liang <wiseguy@wiseguy.idv.tw>
# @version		CVS: $Id: get_lang_relation.sh,v 1.1 2010-02-24 02:38:56 saly Exp $
# @since		2005-09-19
#
cd /home/wm3
for F in `find . -name '*.php' ! \( -path './base/*' -o -path './lib/adodb/*' -o -path './lib/FCKeditor/*'  -o -path './lib/fckeditor2/*'  -o -path './lib/fpdf/*'  -o -path './lib/jpgraph/*' -o -path './lib/htmlarea/*' -o -path './lib/treeview/*' -o -path './academic/dbcs*/*' -o -path './user/*' -o -path './seminar/*' \)`; do
	L=`grep 'require_once.*/lang/' $F`
	if [ "$L" != "" ] ; then
		SL=`echo $L | sed -e 's:^.*/lang/::' -e 's/...$//'`
		if [ "`echo "$SL" | fgrep "' . QTI_which . '"`" != "" ] ; then
			QTI=`echo $F | awk -F/ '{print $3;}'`
			SL=`echo "$SL" | sed "s/' \. QTI_which \. '/$QTI/"`
		fi
		echo -e "$F\t\t\t$SL"
	fi
done
