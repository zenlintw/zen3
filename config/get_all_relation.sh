#!/bin/sh
#
# 取得 WM3 所有程式所關聯的檔案 (不 include 任何檔的程式 不列出)
#
# @author		Wiseguy Liang <wiseguy@wiseguy.idv.tw>
# @version		CVS: $Id: get_all_relation.sh,v 1.1 2010-02-24 02:38:56 saly Exp $
# @since		2005-09-19
#
cd /home/wm3
for F in `find . -name '*.php' ! \( -path './base/*' -o -path './lib/adodb/*' -o -path './lib/FCKeditor/*'  -o -path './lib/fckeditor2/*'  -o -path './lib/fpdf/*'  -o -path './lib/jpgraph/*' -o -path './lib/htmlarea/*' -o -path './lib/treeview/*' -o -path './academic/dbcs*/*' -o -path './user/*' -o -path './seminar/*' \)`; do
	L=`egrep "(require|include)(_| *\().*\.[a-z]+'" $F | sed -e "s:^.*\$_SERVER\['DOCUMENT_ROOT'\][^']*'::" -e "s:^.*sysDocumentRoot[^']*'::" -e 's:^.*include\(_once\)*(::' -e 's:^.*require\(_once\)*(::' -e "s:'[^']*$::"| paste -s -d, -`
	if [ "$L" != "" ] ; then
		echo -e "$F\t\t\t$L"
	fi
done
