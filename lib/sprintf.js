function sprintf()
{
/*
======================================================================
 sprintf()
======================================================================
 Purpose : format a string

 Author  : Antoine Hurkmans, January 2002
----------------------------------------------------------------------
 Parameters :
 you may figure this one out yourself (hint: www.php.net/sprintf)
----------------------------------------------------------------------
 Returns : a formatted string
----------------------------------------------------------------------
 Revision History :
 12-Feb-02 AH  - Added support for alternate padding char
 05-Feb-02 AH  - Fixed bug in display of decimal part for floats
 28-Jan-02 AH  - Initial Version
======================================================================
*/
	var iCount, iPadLength, aMatch, iMatchIndex = 1;
	var bAlignLeft, sPad, iWidth, iPrecision, sType;
	
	
	var aArgs = sprintf.arguments;
	if (aArgs.length < 2) return '';
	
	var sFormat = aArgs[0];
	var re = /%(-)?(0| |'.)?(\d+)?(\.\d*)?([bcdfosxX]{1})/; //'
	while (re.test(sFormat))
	{
		aMatch = re.exec(sFormat);
		bAlignLeft = (aMatch[1] == '-');
		sPad = (aMatch[2] == '' ? ' ' : aMatch[2]);
		if (sPad.substring(0, 1) == "'") sPad = sPad.substring(1);
		iWidth = (aMatch[3] > 0 ? parseInt(aMatch[3]) : 0);
		iPrecision = (aMatch[4].length > 1 ? parseInt(aMatch[4].substring(1)) : 6);
		sType = aMatch[5];
		mArgument = (aArgs[iMatchIndex] != null ? aArgs[iMatchIndex] : '');
		++iMatchIndex;
		if (mArgument.toString().length)
		{
			if ('fbcdoxX'.indexOf(sType) != -1 && isNaN(mArgument)) mArgument = 0;
			switch (sType)
			{
				case 'f':	// floats
					var iPower = Math.pow(10, iPrecision);
					mArgument = (Math.round(parseFloat(mArgument) * iPower) / iPower).toString();
					var aFloatParts = mArgument.split('.');
					if (iPrecision > 0)
					{
						if (aFloatParts.length == 1) aFloatParts[1] = '';
						// pad with zeroes to precision
						for (iCount = aFloatParts[1].length; iCount < iPrecision; iCount++)
							aFloatParts[1] += '0';
						mArgument = aFloatParts[0] + '.' + aFloatParts[1];
					}
					else mArgument = aFloatParts[0];
					
					iPadLength = aFloatParts[0].length;
					break;
				case 'b':	// binary
					mArgument = parseInt(mArgument).toString(2);
					iPadLength = mArgument.length;
					break;
				case 'c':	// character
					mArgument = String.fromCharCode(parseInt(mArgument));
					break;
				case 'd':	// decimal
					mArgument = mArgument.toString();
					iPadLength = mArgument.length;
					break;
				case 'o':	// octal
					mArgument = parseInt(mArgument).toString(8);
					iPadLength = mArgument.length;
					break;
				case 'x':	// hexadecimal (lowercase)
					mArgument = parseInt(mArgument).toString(16);
					iPadLength = mArgument.length;
					break;
				case 'X':	// hexadecimal (uppercase)
					mArgument = parseInt(mArgument).toString(16).toUpperCase();
					iPadLength = mArgument.length;
					break;
				default:	// strings
					mArgument = mArgument.toString();
					iPadLength = mArgument.length;
			}
			
			if ('fbdoxX'.indexOf(sType) != -1)
			{
				// pad with padding-char to width
				if (bAlignLeft)
					for (iCount = iPadLength; iCount < iWidth; iCount++)
						mArgument += sPad;
				else
					for (iCount = iPadLength; iCount < iWidth; iCount++)
						mArgument = sPad + mArgument;
			}
		}
		sFormat = sFormat.replace(re, mArgument);
	}
	return sFormat;
}