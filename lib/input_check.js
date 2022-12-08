/**
 * ��J�ˬd�禡
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
 * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
 * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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
 * �� regexp �[�W���׭���
 *
 * @param	string	re		RegExp �r��
 * @param	integer	minlen	�̵u
 * @param	integer	maxlen	�̪�
 * @param	string			�[�W���׭�� RegExp �r��
 */
function withLengthLimit(re, minlen, maxlen)
{
	return '^(' + re.substring(1, re.lastIndexOf('$')) + '){' + minlen + ',' + maxlen + '}$';
}

/**
 * �ˬd input ���
 *
 * @param	htmldom_element	obj		HTML INPUT �`�I												(���n)
 * @param	string			re		�ˬd�榡													(���n)
 * @param	integer			minlen	�̵u����	(�ٲ��h���ˬd�B�p�G��Ƥ���]�i�H�A�h�̵u�������] 0)
 * @param	integer			maxlen	�̪�����	(�ٲ��h���ˬd)
 * @param	float			Lbound	�̤p��		(�ٲ���re���O�ƭ� �h���ˬd)
 * @param	float			Ubound	�̤j��		(�ٲ���re���O�ƭ� �h���ˬd)
 * @return	bool					�榡���T(true)�ο��~(false)
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
 * �b ��J�ث��Хܿ�J�ȬO���T�Τ����T
 *
 * @param	htmldom_element	obj		HTML INPUT �`�I												(���n)
 * @param	bool			mode	���T(true) �Τ����T (false)
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
