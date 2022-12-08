<?php
	$sysIndent = 0;
/*
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
*/

	/*
	 *	�q�X XHTML ���Y����
	 *	input : $title = �������D
	 */
	function showXHTML_head_B($title){
		global $sysSession;
		$sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
		$ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
		if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';
		print <<< EOB
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="$ACCEPT_LANGUAGE" />
<title>$title </title>

EOB;
	}

	/*
	 *	�q�X </html> TAG
	 *	input : $extra = �q�X </html> ���e�B�~��T�C�Ҧp�G<base target=  �� <bgsound src= ...
	 */
	function showXHTML_head_E($extra){
		echo $extra, "\n</head>\n";
	}

	/*
	 *	�q�X script �q
	 *	input : $type = 'inline' :�ᱵ�� $source �Y�� script
	 *			'include':�ᱵ�� $source �� script ���|t
	 */
	function showXHTML_script($type, $source){
		switch($type){
			case 'inline':
				echo '<script type="text/javascript" language="JavaScript">',
				     "\n<!--\n$source \n//-->\n</script>\n";
				break;
			case 'include':
				echo '<script type="text/javascript" language="JavaScript" ',
				     "src=\"$source\"></script>\n";
				break;
			default:
				echo '<script type="text/javascript" language="JavaScript">',
				     "alert('Javascript �ޥο��~�I');</script>\n";
				break;
		}
	}

	/*
	 *	�q�X css �q
	 *	input : $type = (�P script)
	 */
	function showXHTML_css($type, $source){
		switch($type){
			case 'inline':
				echo '<style type="text/css">',
				     "\n<!--\n$source \n//-->\n</style>\n";
				break;
			case 'include':
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$source\" />\n";
				break;
			default:
				echo '<script type="text/javascript" language="JavaScript">',
				     "alert('css �ޥο��~�I');</script>\n";
				break;
		}
	}

	/*
	 *	�q�X <body>
	 *	input : $extra = <body> ���B�~�ݩ�
	 */
	function showXHTML_body_B($extra){
		echo "<body $extra >\n";
	}

	/*
	 *	�q�X </body></html>
	 *	input : $rxtra = �q�X TAG ���e�A�B�~ html
	 */
	function showXHTML_body_E($extra){
		echo "$extra \n<a href=\"#\" id=\"toGo\"></a>\n</body>\n</html>\n";
	}

	/*
	 *	�q�X <table>
	 *	input : $extra = <table> �B�~�ݩ�
	 */
	function showXHTML_table_B($extra){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent),"<table $extra >\n";
		$sysIndent++;
	}

	/*
	 *	�q�X </table>
	 *	input : $extra = �q�X TAG ���e�A�B�~ html
	 */
	function showXHTML_table_E($extra){
		global $sysIndent;
		$sysIndent--;
		echo str_repeat(chr(9), $sysIndent),$extra,"\n",
		     str_repeat(chr(9), $sysIndent),"</table>\n";
	}

	function showXHTML_tr_B($extra){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent),"<tr $extra >\n";
		$sysIndent++;
	}

	function showXHTML_tr_E($extra){
		global $sysIndent;
		$sysIndent--;
		echo str_repeat(chr(9), $sysIndent),$extra,"\n",
		     str_repeat(chr(9), $sysIndent),"</tr>\n";
	}

	function showXHTML_td_B($extra){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent),"<td $extra >";
	}

	function showXHTML_td_E($extra){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent),"$extra </td>\n";
	}

	function showXHTML_td($extra, $text){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent),"<td $extra >$text </td>\n";
	}

	function showXHTML_form_B($extra, $id){
		global $sysIndent;
		echo "\n",str_repeat(chr(9), $sysIndent),"<form id=\"$id\" name=\"$id\" accept-charset=\"UTF-8\" lang=\"ZH-TW\" $extra >\n";
		$sysIndent++;
	}

	function showXHTML_form_E($extra){
		global $sysIndent;
		$sysIndent--;
		echo "\n",str_repeat(chr(9), $sysIndent),"</form>\n";
	}

	function showXHTML_input($type, $id, $value, $default, $extra){
		global $sysIndent;
		echo str_repeat(chr(9), $sysIndent);
		switch($type){
			case 'text':
				$value = $value?(htmlspecialchars($value).' '):$value;
				echo "<input type=\"text\" name=\"$id\" value=\"$value\" $extra />\n";
				break;
			case 'checkbox':
				$value = $value?(htmlspecialchars($value).' '):'';
				echo "<input type=\"checkbox\" name=\"$id\" value=\"$value\" $extra ",$default?'checked="checked"':'',"/>\n";
				break;
			case 'password':
				echo "<input type=\"password\" name=\"$id\" $extra />\n";
				break;
			case 'hidden':
				$value = $value?(htmlspecialchars($value).' '):'';
				echo "<input type=\"hidden\" name=\"$id\" value=\"$value\" $extra />\n";
				break;
			case 'select':
				echo "<select name=\"$id\" $extra >\n";
				while(list($k,$v)=each($value)){
					echo str_repeat(chr(9), $sysIndent),
					     "<option value=\"$k\"",
					     ($k==$default?' selected="selected"':''),
					     ">$v </option>\n";
				}
				echo str_repeat(chr(9), $sysIndent),"</select>\n";
				break;
			case 'textarea';
				$value = $value?(htmlspecialchars($value).' '):'';
				echo str_repeat(chr(9), $sysIndent),"<textarea name=\"$id\" $extra >\n",
				     $value,"</textarea>\n";
				break;
			case 'radio':
				while(list($k,$v)=each($value)){
					echo "<input type=\"radio\" name=\"$id\" value=\"$k\"",
					     ($k==$default?' checked="checked"':'')," $extra />$v\n";
				}
				break;
			case 'button':
				$value = $value?(htmlspecialchars($value).' '):'';
				echo "<button $extra >$value</button>\n";
				break;
			case 'submit':
			case 'reset' :
				$value = $value?(htmlspecialchars($value).' '):'';
				echo "<input type=\"$type\" value=\"$value\" $extra />\n";
				break;
			case 'file':
				echo "<input type=\"file\" name=\"uploads[]\" $extra />\n";
				break;
		}
	}
?>