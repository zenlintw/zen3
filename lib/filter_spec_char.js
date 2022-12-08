	/**
	 * 將字串的特殊字元移除
	 * @author  Amm Lee
	 * @version $Id: filter_spec_char.js,v 1.1 2010/02/24 02:39:33 saly Exp $:
	 * @copyright 2003 SUNNET
	 **/


	 /* Filter_Spec_char : 將字串的特殊字元移除
	  * 字元 : `~!@#$%^&*()+=|:<>?;',./"[]{}
	  * true   => 無特殊字元
	  * false  => 有含特殊字元
	  */
	 function Filter_Spec_char(str, type)
	 {
		if (typeof(type) == 'undefined') type = 'title';
	    switch (type)
	    {
	        case 'title':
	        case 'caption':
				return (str.search(/[\x01-\x1F\x22\x27\x3A\x3B\x5C\x7B\x7D]/) == -1);
            case 'username':
				return (str.search(/[^\w-]/) == -1);
            case 'realname':
				return (str.search(/[\x01-\x1F\x21-\x2C\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]/) == -1);
            case 'filename':
				return (str.search(/[\x01-\x1F\x22\x25\x2A\x2F\x3A\x3C\x3E\x3F\x5C\x7C]/) == -1);
            case 'float':
            case 'score':
				return (str.search(/[^0-9.+-]/) == -1);
            case 'int':
            case 'integer':
            case 'times':
				return (str.search(/[^0-9+-]/) == -1);
            case 'no_punct':
            case 'search':
				return (str.search(/[\x01-\x1F\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]/) == -1);
			default:
			    return true;
		}
	 }


	 /**
    * 將  普通文字 轉成 html 的 tag 顯示
    */
    function un_htmlspecialchars(str) {
		var re = /</ig;
		var val = str;
		val = val.replace(/&amp;/ig, "&");
		val = val.replace(/&lt;/ig, "<");
		val = val.replace(/&gt;/ig, ">");
		val = val.replace(/&#039;/ig, "'");
		val = val.replace(/&quot;/ig, "\"");
		return val;
	}

