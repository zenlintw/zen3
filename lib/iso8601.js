/**
 * 將 iso8601 日期時間字串轉換為毫秒數
 */
function iso8601toMilliseconds(iso8601s){
	var capitals = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	var a = iso8601s.split('T');
	var dates = a[0].split(/[-\/]/);
	var times = a[1].split(',');
	if (dates[0] == '') dates[0] = 1970;
	if (dates[1] == '') dates[1] = 1;
	if (dates[2] == '') dates[2] = 1;

	var jsDateTimeStr = capitals[parseInt(dates[1])-1] + ' ' + dates[2] + ', ' + dates[0] + ' ' + times[0];
	return Date.parse(jsDateTimeStr) + parseInt(times[1]);
}

/**
 * 將毫秒轉換為 iso8601 字串
 */
function millisecondsToIso8601(ms){
	var dt = new Date(parseInt(ms));
	return dt.getYear() + '-' +
		   (dt.getMonth() + 1) + '-' +
   		   dt.getDate() + 'T' +
   		   dt.getHours() + ':' +
   		   dt.getMinutes() + ':' +
   		   dt.getSeconds() + ',' +
   		   dt.getMilliseconds();
}

/**
 * 將兩個 iso8601 日期時間相減，傳回相差毫秒數絕對值
 */
function iso8601Subtract(dt1, dt2){
	return Math.abs(iso8601toMilliseconds(dt1) - iso8601toMilliseconds(dt2));
}

/**
 * 將兩個 iso8601 日期時間相加，傳回總合毫秒數
 */
function iso8601Addition(dt1, dt2){
	return iso8601toMilliseconds(dt1) + iso8601toMilliseconds(dt2);
}

var arrMonth =  // names of all the months
      new Array( "January", "February", "March", "April", "May",
                 "June", "July", "August", "September", "October",
                 "November", "December" ) ;

var arrWeekday = // names of all the days of the week
      new Array( "Sunday", "Monday", "Tuesday", "Wednesday",
                 "Thursday", "Friday", "Saturday" ) ;


// return today's date in ISO8601 format
function GetTodayISO8601()
{
  var now = new Date() ;

  var strNowISO8601 = "" + now.getFullYear() + "-" ;
  strNowISO8601 += ( now.getMonth()+1 < 10 ?
      "0" + (now.getMonth()+1) : now.getMonth()+1 ) ;
  strNowISO8601 += "-" + ( now.getDate() < 10 ?
      "0" + now.getDate() : now.getDate() ) ;

  return strNowISO8601 ;
}

// return "Month Day" given a date string in ISO8601 format
function MonthDay( strISO8601 )
{
  var strMonth = strISO8601.substr( 5, 2 ) ;
  while ( strMonth.substr( 0, 1 ) == "0" )
    strMonth = strMonth.substr( 1, strMonth.length ) ;
  var intMonth = parseInt( strMonth ) ;

  var strDay = strISO8601.substr( 8, 2 ) ;
  while ( strDay.substr( 0, 1 ) == "0" )  // parseInt does not like leading zeros
    strDay = strDay.substr( 1, strDay.length ) ;
  var intDay = parseInt( strDay ) ;

  return arrMonth[intMonth-1] + " " + intDay ;
}

// return "Month Day, Year" given a date string in ISO8601 format
function MonthDayYear( strISO8601 )
{
  return MonthDay( strISO8601 ) + ", " + strISO8601.substr( 0, 4 ) ;
}

// return a Date object given a date string in ISO8601 format
function DateISO8601( strISO8601 )
{
  var strYear = strISO8601.substr( 0, 4 ) ;
  while ( strYear.substr( 0, 1 ) == "0" )
    strYear = strYear.substr( 1, strYear.length ) ;
  var intYear = parseInt( strYear ) ;

  var strMonth = strISO8601.substr( 5, 2 ) ;
  while ( strMonth.substr( 0, 1 ) == "0" )  // parseInt does not like leading zeroes!
    strMonth = strMonth.substr( 1, strMonth.length ) ;
  var intMonth = parseInt( strMonth ) - 1;
 
  var strDay = strISO8601.substr( 8, 2 ) ; 
  while ( strDay.substr( 0, 1 ) == "0" )  // parseInt does not like leading zeroes!
    strDay = strDay.substr( 1, strDay.length ) ;
  var intDay = parseInt( strDay ) ;

  return new Date( intYear, intMonth, intDay ) ;
}


// return "Weekday, Month Day, Year" given a date string in ISO8601 format
function WeekdayMonthDayYear( strISO8601 )
{
  var strDate = DateISO8601( strISO8601 ).toString() ;
  var strAbbr = strDate.substr( 0, 3 ) ;
  var k ;
  for ( k = 0 ; k < arrWeekday.length ; k++ )
    if ( arrWeekday[k].indexOf( strAbbr ) == 0 )
      break ;
  if ( k == arrWeekday.length ) k = 0 ;
  return arrWeekday[k] + ", " + MonthDayYear( strISO8601 ) ;
}
