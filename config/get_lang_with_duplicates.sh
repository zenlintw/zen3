#!/bin/sh
#
# ���o�����Щw�q�y�����y�t�� (���t���Щw�q�y�����y�t�� ���C�X)
#
# @author		Wiseguy Liang <wiseguy@wiseguy.idv.tw>
# @version		CVS: $Id: get_lang_with_duplicates.sh,v 1.1 2010-02-24 02:38:56 saly Exp $
# @since		2005-09-19
#
cd /home/wm3/lang
for F in `find . -name '*.php'`; do
	DS=`fgrep '$MSG[' $F | sed -e 's/^[^$]*//' -e 's/ *= *array.*$//' | sort | uniq -d | paste -s -d, -`
	if [ "$DS" != "" ] ; then
		echo -e "$F\t\t\t$DS"
	fi
done
