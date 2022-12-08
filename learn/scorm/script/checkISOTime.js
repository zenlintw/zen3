<script language="javascript">


//alert("ISOTime true --> 2004 = " + checkISOTime("2004"));
//alert("ISOTime false --> -1 = " + checkISOTime("-1"));
//alert("ISOTime true --> 2004-07-25 = " + checkISOTime("2004-07-25"));
//alert("ISOTime false --> 2004-07-32 = " + checkISOTime("2004-07-32"));
//alert("ISOTime true --> 2003-02-28 = " + checkISOTime("2003-02-28"));
//alert("ISOTime false --> 2003-02-29 = " + checkISOTime("2003-02-29"));
//alert("ISOTime true --> 2004-02-28 = " + checkISOTime("2004-02-28"));
//alert("ISOTime true --> 2004-02-29 = " + checkISOTime("2004-02-29"));
//alert("ISOTime false --> 2004-02-30 = " + checkISOTime("2004-02-30"));

//alert("ISOTime true --> 2000-02-28 = " + checkISOTime("2000-02-28"));
//alert("ISOTime true --> 2000-02-29 = " + checkISOTime("2000-02-29"));
//alert("ISOTime false --> 2000-02-30 = " + checkISOTime("2000-02-30"));

//alert("ISOTime true --> 2004-07-25T03:00:00 = " + checkISOTime("2004-07-25T03:00:00"));
//alert("ISOTime false --> 2004-07-25T03:00:60 = " + checkISOTime("2004-07-25T03:00:60"));
//alert("ISOTime true --> 2004-07-25T03:00:00Z = " + checkISOTime("2004-07-25T03:00:00Z"));
//alert("ISOTime true --> 2004-07-25T03:00:00+08:00 = " + checkISOTime("2004-07-25T03:00:00+08:00"));

//alert("ISOTime true --> 2004-07-25T03:00:00+11:59 = " + checkISOTime("2004-07-25T03:00:00+11:59"));
//alert("ISOTime false --> 2004-07-25T03:00:00+05:200 = " + checkISOTime("2004-07-25T03:00:00+05:200"));
//alert("ISOTime false --> 2004-07-25T03:00:00+05:-1 = " + checkISOTime("2004-07-25T03:00:00+05:-1"));
alert("ISOTime true --> 2004-07-25T03:00:00+05:1 = " + checkISOTime("2004-07-25T03:00:00+05:1"));


//alert("ISOTime false --> 2004-07-25T03:00:00+12:59 = " + checkISOTime("2004-07-25T03:00:00+12:59"));


//alert("ISOTime false --> 2004-02-30T03:00:00+08:00 = " + checkISOTime("2004-02-30T03:00:00+08:00"));


//alert("ISOTime false --> 2004-01-01?03:00:00+08:00 = " + checkISOTime("2004-01-01?03:00:00+08:00"));

//alert("ISOTime false --> 2004?01-01 = " + checkISOTime("2004?01-01"));
//alert("ISOTime false --> 2004?01?01 = " + checkISOTime("2004?01?01"));
//alert("ISOTime false --> 2004-01-01 03:00:00+08:00 = " + checkISOTime("2004-01-01 03:00:00+08:00"));
//alert("ISOTime false --> 2004-01-01 03:00:00T08:00 = " + checkISOTime("2004-01-01 03:00:00T08:00"));
//alert("ISOTime false --> T = " + checkISOTime("T"));
//alert("ISOTime false --> TZ+ = " + checkISOTime("TZ+"));
alert("ISOTime false --> TT = " + checkISOTime("TT"));


function checkISOTime(ts){
   
   var DayInMonthArray  = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
   
   
   //先Check有沒有'T',如果沒有T表示只有寫日期,如果有T代表有寫時間
   var T_position = ts.indexOf("T");
   var date_Array;
   var time_Array;
   var TZD_Array;

   if(T_position!=-1){
   	
   	var Str1 = ts.slice(0,T_position);
   	var Str2 = ts.slice(T_position+1);
   	//再尋找有沒有Time Zone Separator Z,+,-
   	var TZD_position = -1;
   	if(Str2.indexOf("Z")!=-1){
   		Str2 = Str2.replace("Z","");	
   		
   	}
   	
    	if(Str2.indexOf("+")!=-1){
   		TZD_position = Str2.indexOf("+");	
   	}
   	
    	if(Str2.indexOf("-")!=-1){
   		TZD_position = Str2.indexOf("-");	
   	}
   	
	if(TZD_position!=-1){
		var Str3 = Str2.slice(0,TZD_position);
		var Str4 = Str2.slice(TZD_position+1);
	}
	
	//找日期字串裡有沒有月跟日
	date_Array = Str1.split("-");
	
	
	//找時間字串
	if(TZD_position!=-1){
		time_Array = Str3.split(":");
	}else{
		time_Array = Str2.split(":");
	}
	
	//找TZD字串
	
	if(Str2.indexOf("+")!=-1 || Str2.indexOf("-")!=-1){
		
		TZD_Array = Str4.split(":");
		
	}	
  	
   	//判斷日期字串
   	
   	var check_date = "false";
   	var tempYear;
   	var tempMonth;
   	var tempDay;

	if(date_Array.length>0){
	   	if(date_Array.length == 1){
	   		//只有年
	   		//應該有4碼
	   		tempYear = Number(date_Array[0]);
	   		if(!isNaN(tempYear)){
		   		if(tempYear<9999 && tempYear>1){
		   			check_date = "true";
	   			}
	   		}
	   		
	   		
	   	}else if(date_Array.length == 2){
	   		//年及月
	   		tempYear = Number(date_Array[0]);
	   		tempMonth = Number(date_Array[1]);
	   		if(!isNaN(tempYear) && !isNaN(tempMonth)){
		   		if(tempYear<9999 && tempYear>1){
		   			if(tempMonth>0 && tempMonth<12){
		   				check_date = "true";
		   			}
		   		}
			}
	   		
	   	}else if(date_Array.length == 3){
	   		//年,月,日
	    		tempYear = Number(date_Array[0]);
	   		tempMonth = Number(date_Array[1]);
	   		tempDay = Number(date_Array[2]);
	   		if(!isNaN(tempYear) && !isNaN(tempMonth) && !isNaN(tempDay)){
		   		if(tempYear<9999 && tempYear>1){
		   			if(tempMonth>0 && tempMonth<12){
		   				if(tempDay>0){
		   					//如果是2月要check是不是閏年
		   					//如果不是2月就可以直接check該月的天數
		   					if(Number(tempMonth)==2 && (((Number(tempYear)%4==0) && (Number(tempYear)%100!=0)) || (Number(tempYear)%400==0))){
								if(tempDay<=DayInMonthArray[Number(tempMonth-1)]+1){
									check_date = "true";
		   						}
		   					}else if(tempDay<=DayInMonthArray[Number(tempMonth-1)]){
		   						check_date = "true";
		   					}
		   				}
		   			}
		   		}  		
			}
	   	}
	}else{
		check_date = "true";
	}
   	
   	//判斷時間字串
   	
   	var check_time = "false";
   	var tempHour;
   	var tempMinute;
   	var tempSecond;
   	
   	//alert("check_date = " + check_date);
	
	//alert(time_Array.length);
	if(time_Array.length>0){   	
	   	if(time_Array.length==1){
	   		tempHour = Number(time_Array[0]);
		   	if(!isNaN(tempHour)){
		   		if(tempHour>=0 && tempHour<24){
		   			check_time = "true";
		   		}
	   		}
	   		
	   	}else if(time_Array.length==2){
	   		tempHour = Number(time_Array[0]);
	   		tempMinute = Number(time_Array[1]);
		   	if(!isNaN(tempHour) && !isNaN(tempMinute)){
		   		if(tempHour>=0 && tempHour<24){
		   			if(tempMinute>=0 && tempMinute<60){
		   				check_time = "true";
		   			}
		   		}   		
	   		}
	   		
	   	}else if(time_Array.length==3){
	   		tempHour = Number(time_Array[0]);
	   		tempMinute = Number(time_Array[1]);
	   		tempSecond = Number(time_Array[2]);
		   	if(!isNaN(tempHour) && !isNaN(tempMinute) && !isNaN(tempSecond)){
		   		if(tempHour>0 && tempHour<24){
		   			if(tempMinute>=0 && tempMinute<60){
		   				if(tempSecond>=0 && tempSecond<60){
		   					check_time = "true";
		   				}
		   			}
		   		}
		   	}   	
		}   	
	}else{
		check_time  = "true";
	}

	//alert("check time = " + check_time);
	
	//判斷TZD
   	var check_TZD = "false";
   	var tempTZDHour;
   	var tempTZDMinute;
   	//alert(TZD_position);
  	if(TZD_position!=-1){
   		if(TZD_Array.length>0){
   			if(TZD_Array.length==1){
   				tempTZDHour = Number(TZD_Array[0]);
   				if(!isNaN(tempTZDHour)){
	   				if(tempTZDHour>0 && tempTZDHour<=12){
	   					check_TZD = "true";	
	   				}
	   			}
   			}else if(TZD_Array.length==2){
   				tempTZDHour = Number(TZD_Array[0]);
   				tempTZDMinute = Number(TZD_Array[1]);
	   			if(!isNaN(tempTZDHour) && !isNaN(tempTZDMinute)){
	   				if(tempTZDHour>=0 && tempTZDHour<=12){
	   					if(tempTZDMinute>=0 && (tempTZDMinute<60 && tempTZDHour<12 ) ){
	   						check_TZD = "true";
	   					}	
	   				}   				
   				}
   			}
   			
   		}
   	}else{
   		check_TZD = "true";
   	}
   		
	
	//alert("check_TZD = " + check_TZD);
	
	//如果日期,時間及TZD都正確就代表ok
	
	if(check_date=="true" && check_time=="true" && check_TZD=="true"){
		return true;
	}else{
		return false;
	}
	
	


   	
   	
   }else{
   	//只有日期字串
   	date_Array = ts.split("-");
 	if(date_Array.length>0){
	   	if(date_Array.length == 1){
	   		//只有年
	   		//應該有4碼
	   		tempYear = Number(date_Array[0]);
	   		if(!isNaN(tempYear)){
		   		if(tempYear<9999 && tempYear>1){
		   			check_date = "true";
		   		}
	   		}
	   		
	   		
	   	}else if(date_Array.length == 2){
	   		//年及月
	   		tempYear = Number(date_Array[0]);
	   		tempMonth = Number(date_Array[1]);
	   		if(!isNaN(tempYear) && !isNaN(tempMonth)){
		   		if(tempYear<9999 && tempYear>1){
		   			if(tempMonth>0 && tempMonth<12){
		   				check_date = "true";
		   			}
		   		}
			}	
	   		
	   	}else if(date_Array.length == 3){
	   		//年,月,日
	    		tempYear = Number(date_Array[0]);
	   		tempMonth = Number(date_Array[1]);
	   		tempDay = Number(date_Array[2]);
	   		if(!isNaN(tempYear) && !isNaN(tempMonth) && !isNaN(tempDay)){
		   		if(tempYear<9999 && tempYear>1){
		   			if(tempMonth>0 && tempMonth<12){
		   				if(tempDay>0){
		   					//如果是2月要check是不是閏年
		   					//如果不是2月就可以直接check該月的天數
		   					if(Number(tempMonth)==2 && (((Number(tempYear)%4==0) && (Number(tempYear)%100!=0)) || (Number(tempYear)%400==0))){
								if(tempDay<=DayInMonthArray[Number(tempMonth-1)]+1){
									check_date = "true";
		   						}
		   					}else if(tempDay<=DayInMonthArray[Number(tempMonth-1)]){
		   						check_date = "true";
		   					}
		   				}
		   			}
		   		}
		   	}  		
	   	}
	}
	
	if(check_date == "true"){
		return true;	
	}else{
		return false;
	}
  	
   	
   	
   	
   } 
   


}
</script>
