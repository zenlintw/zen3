/**
 * 輸入檢查函式
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang
 * @copyright   2000-2005 SunNet Tech. INC.
 * @version     CVS: $Id: input_check.js,v 1.1 2010/02/24 02:39:33 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-3-15
 */

var RE_INTEGER	= '^\\d+$';
var RE_FLOAT	= '^\\d+(\\.\\d+)?$';
var RE_DATE		= '^\\d{4}(-\\d{2}){2}$';
var RE_TIME		= '^\\d{2,}(:[0-5]\\d){2}(\\.\\d+)?$';
var RE_DATETIME	= '^\\d{4}(-\\d{2}){2}([ T]\\d{2,}(:[0-5]\\d){2}(\\.\\d+)?)?$';
var RE_EMAIL	= '^\\w+(-\\w+)*(\\.\\w+(-\\w+)*)?@\\w+(-\\w+)*(\\.\\w+(-\\w+)*)+$';
var RE_URL		= '^[A-Za-z]+:(//)?\\w+(.\\w+)+(/[^\\s]*)?$';
var RE_ID		= '^[A-Za-z][0-9A-Za-z]*([_-][0-9A-Za-z]+)*$';
var RE_NOPUNCT  = '^[^\\x00-\\x1F\\x21-\\x2F\\x3A-\\x40\\x5B-\\x60\\x7B-\\x80]*$';
var RE_FNAME    = '^[^\\x00-\\x1F\\x5C\\x2F\\x3A\\x2A\\x3F\\x22\\x3C\\x3E]*$';
var RE_ANY		= '^[^\\s]+$';

/**
 * 把 regexp 加上長度限制
 *
 * @param	string	re		RegExp 字串
 * @param	integer	minlen	最短
 * @param	integer	maxlen	最長
 * @param	string			加上長度限制的 RegExp 字串
 */
function withLengthLimit(re, minlen, maxlen)
{
	return '^(' + re.substring(1, re.lastIndexOf('$')) + '){' + minlen + ',' + maxlen + '}$';
}

/**
 * 檢查 input 資料
 *
 * @param	htmldom_element	obj		HTML INPUT 節點												(必要)
 * @param	string			re		檢查格式													(必要)
 * @param	integer			minlen	最短長度	(省略則不檢查、如果資料不填也可以，則最短長度應設 0)
 * @param	integer			maxlen	最長長度	(省略則不檢查)
 * @param	float			Lbound	最小值		(省略或re不是數值 則不檢查)
 * @param	float			Ubound	最大值		(省略或re不是數值 則不檢查)
 * @return	bool					格式正確(true)或錯誤(false)
 */
function checkInputValue(obj, re, minlen, maxlen, Lbound, Ubound)
{
	var xre;

	if (obj == null || typeof(obj) == 'undefined' ||
		re  == null || typeof(re)  == 'undefined' )
		return false;

	if (typeof(maxlen) != 'undefined' && maxlen != null)
	{
		maxlen = Math.abs(parseInt(maxlen, 10));
		minlen = (typeof(minlen) != 'undefined' && minlen != null) ? Math.abs(parseInt(minlen, 10)) : 0;

		xre = new RegExp(withLengthLimit(re, minlen, maxlen));
	}
	else if (typeof(minlen) != 'undefined' && minlen != null)
	{
		minlen = Math.abs(parseInt(minlen, 10));

		xre = new RegExp(withLengthLimit(re, minlen, ''));
	}
	else
		xre = new RegExp(re);

	if (!xre.test(obj.value)) return false;

	if ((re == RE_INTEGER || re == RE_FLOAT) &&
		((typeof(Lbound) != 'undefined' && Lbound != null && parseFloat(obj.value) < parseFloat(Lbound)) ||
		 (typeof(Ubound) != 'undefined' && Ubound != null && parseFloat(obj.value) > parseFloat(Ubound)) ))
		return false;

	return true;
}

/**
 * 在 輸入框後方標示輸入值是正確或不正確
 *
 * @param	htmldom_element	obj		HTML INPUT 節點												(必要)
 * @param	bool			mode	正確(true) 或不正確 (false)
 */
function markCheckResult(obj, mode)
{
	if (obj.nextSibling != null && obj.nextSibling.tagName != null)
	{
		if (obj.nextSibling.tagName.toLowerCase() != 'span')
			newSpan = obj.parentNode.insertBefore(document.createElement('span'), obj.nextSibling);
		else
			newSpan = obj.nextSibling;
	}
	else
		newSpan = obj.parentNode.appendChild(document.createElement('span'));

	newSpan.innerHTML = mode ? '<span style="font-family: Wingdings; color: green;">J</span>' :
							   '<span style="font-family: Wingdings; color: red;">L</span>';
}
