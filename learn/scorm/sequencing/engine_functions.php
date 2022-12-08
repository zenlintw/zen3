<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');
?>
<html>

<body>
<script language="javascript">
var enfunctions;

function enginefunctions(){
	
	this.findFORule = findFORule  
	this.tocIDfindIndex = tocIDfindIndex												/* ��ID��Index  */
	this.findTargetObjectiveIndex = findTargetObjectiveIndex  					/* ��target Objective  */
	this.returnTargetObjectiveIndex = returnTargetObjectiveIndex 				/* ��target objective id ��X��target objective index  */
	this.findItemObjectiveIndex = findItemObjectiveIndex  						/* ��Item Objective  */
	/* ---------------- */
	this.findPrimaryObjectiveID = findPrimaryObjectiveID  						/* ��primary Objective  */
	this.findObjectiveID = findObjectiveID  											/* ��objective ID  */
	this.ComputeActivityAbsoluteDuration =ComputeActivityAbsoluteDuration 	/* �S�Ψ�  */
	this.ComputeAttemptDuration =ComputeAttemptDuration  							/* �S�Ψ�  */
	this.ComputeActivityDuration = ComputeActivityDuration  						/* �S�Ψ�  */
	this.findTargetStatus = findTargetStatus											/* ��target Objective Status  */
	this.findTargetMeasure = findTargetMeasure  										/* ��target objective Measure  */
	this.findTargetMeasureStatus = findTargetMeasureStatus
	this.convertISOtime = convertISOtime												/* �ɶ��榡�ഫ  */
	this.convertTime = convertTime														/* �ɶ��榡�ഫ  */
	this.computeTotalTime = computeTotalTime  										/* �S�Ψ�  */
	this.countAbsoluteDuration = countAbsoluteDuration								/* �p�� duration  */
	this.convertTotalSeconds = convertTotalSeconds									/* �p���`���  */
	/* ----objective ---- Heroin 2004.09.03 */
	this.findObjectivesTargetIndex = findObjectivesTargetIndex 					/* ��objectives �� target  */
	this.findObjectivesTargetStatus = findObjectivesTargetStatus
	this.findObjectivesTargetMeasure = findObjectivesTargetMeasure
	this.findObjectivesTargetMeasureStatus  = findObjectivesTargetMeasureStatus	
	/* Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			*/
	this.findTargetObj = findTargetObj ; 												/* �C��objective�itarget�h��, ��X�ѦҸ�status���A��mapInfo��array index  */
	this.findReadTargetObjectiveIndex=findReadTargetObjectiveIndex;
	this.findWriteTargetObjectiveIndex=findWriteTargetObjectiveIndex;
}
/* parent.functions.enfunctions.		 */


function findFORule(tempIndex){
	var CurrentIndex = Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempIndex].id));
	var flag = false;
	if(CurrentIndex>0){
		var tempParentIndex;
		tempParentIndex = Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempIndex].parentID));
		/* ���i��borganization�U�N���F�X��item,�ҥH�|�䤣��parentIndex */
		if(!isNaN(tempParentIndex)){
			do{
				if(parent.s_catalog.pathtree.controlModeList[tempParentIndex].forwardOnly=="true"){
					flag = true;
					FOControlIndex = tempParentIndex;
				}
				tempParentIndex = tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempParentIndex].parentID);
			}while(tempParentIndex > 0);
		}
		return flag;
	}else{
		return flag;
	}
}


function tocIDfindIndex(tempitemID){
	var i;
	var flag=0;
	for(i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(parent.s_catalog.pathtree.tocList[i].id==tempitemID){
			flag = i;
			break;
		}
	}
	return flag;
}
/* =====Yunghsiao.2004.12.14================================================== */
function findTargetObjectiveIndex(objectiveID){	
	var referencedObjectiveID;
	var TargetObjectiveIndex = new Array();
	var TargetObjectiveCount = 0;

	for(var i=0;i<parent.s_catalog.pathtree.objectiveList.length;i++){
		if(parent.s_catalog.pathtree.objectiveList[i].objectiveID==objectiveID){
			if(parent.s_catalog.pathtree.objectiveList[i].mapInfoList.length>0){
				for(var p=0;p<parent.s_catalog.pathtree.objectiveList[i].mapInfoList.length;p++){
					if(parent.s_catalog.pathtree.objectiveList[i].mapInfoList[p].targetObjectiveID!=""){
						TargetObjectiveIndex[TargetObjectiveCount] = -1;
						for(var j=0;j<parent.s_catalog.pathtree.sharedObjectiveList.length;j++){
							if(parent.s_catalog.pathtree.sharedObjectiveList[j].objectiveID==parent.s_catalog.pathtree.objectiveList[i].mapInfoList[p].targetObjectiveID){
								TargetObjectiveIndex[TargetObjectiveCount] = j;
								TargetObjectiveCount++;
							}
						}
					}
				}
				return TargetObjectiveIndex;
				break;
			}
		}
	}
	return -1;
}

/* =====Yunghsiao.2004.12.13================================================= */
function findObjectivesTargetIndex(objectiveID,SCO_ID){

	var referencedObjectiveID;
	var tempSCO_index = "";
	var TargetObjectiveIndex = new Array();
	var TargetObjectiveCount = 0;
	
	for(var i=0;i<parent.s_catalog.pathtree.objectiveList.length;i++){
		
		tempSCO_index = parent.s_catalog.pathtree.objectiveList[i].itemIndex;
		
		if((parent.s_catalog.pathtree.objectiveList[i].objectiveID == objectiveID) && (parent.s_catalog.pathtree.tocList[tempSCO_index].id == SCO_ID)){
			if(parent.s_catalog.pathtree.objectiveList[i].mapInfoList.length>0){
				for(var p=0;p<parent.s_catalog.pathtree.objectiveList[i].mapInfoList.length;p++){
					if(parent.s_catalog.pathtree.objectiveList[i].mapInfoList[p].targetObjectiveID!=""){
						TargetObjectiveIndex[TargetObjectiveCount] = -1;
						for(var j=0;j<parent.s_catalog.pathtree.sharedObjectiveList.length;j++){
							if(parent.s_catalog.pathtree.sharedObjectiveList[j].objectiveID==parent.s_catalog.pathtree.objectiveList[i].mapInfoList[p].targetObjectiveID){
								TargetObjectiveIndex[TargetObjectiveCount]=j;
								TargetObjectiveCount++;
							}
						}
					}
				}
				return TargetObjectiveIndex;
				break;
			}
		}
	}
	return -1;
}

/* 2004.4.2 Vega: Add */
function findItemObjectiveIndex(itemIndex){
	var tempItemObjIndexArray = new Array();
	var j=0 ;
	for(var i=0;i<parent.s_catalog.pathtree.objectiveList.length;i++){
		if(parent.s_catalog.pathtree.objectiveList[i].itemIndex==itemIndex){
			tempItemObjIndexArray[j] = i;
			j++;
		}
	}	
	if(j>0){
		return tempItemObjIndexArray;
	}else{
		return "";
	}

}

/* 2004.4.2 Vega: Add */
function returnTargetObjectiveIndex(targetObjectiveID){
	for(var i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
		if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==targetObjectiveID){
			return i;
			break;
		}
	}

}

/*  added by Heroin 2003-10-20 */

function findPrimaryObjectiveID(objectiveID){
	for(var i=0;i<parent.s_catalog.pathtree.primaryObjectiveList.length;i++){		
		if(parent.s_catalog.pathtree.primaryObjectiveList[i].objectiveID==objectiveID){
			return i;
			break;
		}
	}
	return -1;
}


/* Heroin 2004.08.17 */
/*  �[�JitemIndex�P�_ 2004.09.02 */
function findObjectiveID(objectiveID,itemIndex){
	for(var i=0;i<parent.s_catalog.pathtree.objectiveList.length;i++){		
		if(parent.s_catalog.pathtree.objectiveList[i].objectiveID==objectiveID && parent.s_catalog.pathtree.objectiveList[i].itemIndex == itemIndex){
			return i;
			
			break;
		}
	}
}


function ComputeActivityAbsoluteDuration(tempIndex, CurrentTime){
	
	var tempDate = new Date(CurrentTime);
	
	if(parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAbsoluteDuration!=0.0){
	
		/* -----��CurrentTime��hactivityAbsoluteDuration�����(�Ĥ@���i�J���ɶ�,���O����),�Y��activityAbsoluteDuration */
		
		var tempActivityAbsoluteDuration = convertTotalSeconds(Number(Date.parse(tempDate))/1000-Number(parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAbsoluteDuration));
		
		return tempActivityAbsoluteDuration;
	}else{
		
		parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAbsoluteDuration = Number(Date.parse(tempDate))/1000;
		return "00:00:00"
	}	

}

function ComputeAttemptDuration(tempitemID){
	/*  -----�����SCO��session_time  */
	var temp_session_time = XMLDI.selectSingleNode("//Record[@SCO_ID='" + tempitemID + "']").childNodes.item(2).text;

	/*  -----��session_time���̫�@�Ӭ���  */
	var temp_session_time_Array = temp_session_time.split("~");
	var last_session_time = temp_session_time_Array[temp_session_time_Array.length-1];
	var temp_Array = last_session_time.split(";");
	var last_duration = temp_Array[0];
	
	return last_duration;

}


function ComputeActivityDuration(OriginalValue,NewValue){

	var temp_Array = OriginalValue.split(":");
	var temp_duration_second = Number(temp_Array[0])*3600 + Number(temp_Array[1])*60 + Number(temp_Array[2]);
		
	return convertTotalSeconds(Number(temp_duration_second) + Number(NewValue));


}


function convertTotalSeconds(ts)
{
   var sec = (ts % 60);

   ts -= sec;
   var tmp = (ts % 3600);  /* # of seconds in the total # of minutes */
   ts -= tmp;              /* # of seconds in the total # of hours */

   /*  convert seconds to conform to CMITimespan type (e.g. SS.00) */
   sec = Math.round(sec*100)/100;
   
   var strSec = new String(sec);
   var strWholeSec = strSec;
   var strFractionSec = "";

   if (strSec.indexOf(".") != -1)
   {
      strWholeSec =  strSec.substring(0, strSec.indexOf("."));
      strFractionSec = strSec.substring(strSec.indexOf(".")+1, strSec.length);
   }
   
   if (strWholeSec.length < 2)
   {
      strWholeSec = "0" + strWholeSec;
   }
   strSec = strWholeSec;
   
   if (strFractionSec.length)
   {
      strSec = strSec+ "." + strFractionSec;
   }


   if ((ts % 3600) != 0 )
      var hour = 0;
   else var hour = (ts / 3600);
   if ( (tmp % 60) != 0 )
      var min = 0;
   else var min = (tmp / 60);

   if ((new String(hour)).length < 2)
      hour = "000"+hour;
   if ((new String(hour)).length < 3)
      hour = "00"+hour;
   if ((new String(hour)).length < 4)
      hour = "0"+hour;            
   if ((new String(min)).length < 2)
      min = "0"+min;

   var rtnVal = hour+":"+min+":"+strSec;

   return rtnVal;
}


function computeTotalTime(cmi_core_session_time,cmi_core_total_time){
	var sessionTimeRecArray = cmi_core_session_time.split(":");
	var totalTimeRecArray = cmi_core_total_time.split(":");
        var strHour=0;
        var strMin=0;
        var strSec=0.00;
        var strTotalSec=0.00;

        strHour = new Number(totalTimeRecArray[0]) + new Number(sessionTimeRecArray[0]);
        strMin = new Number(totalTimeRecArray[1]) + new Number(sessionTimeRecArray[1]);
        strSec = new Number(totalTimeRecArray[2]) + new Number(sessionTimeRecArray[2]);               

        strTotalSec = (strHour * 3600) + (strMin * 60) + strSec;
        
        return convertTotalSeconds(strTotalSec);
}


function convertISOtime(ISOtime){
	
	/* 
		�NISO���ɶ��榡�ഫ���t�ΰO�����ɶ��榡
	   	P1Y2M3DT4H5M6S -> �@�~,��Ӥ�,�T��,4�Ӥp��,5����,6��
	   	P�MT�Oseparator�S���@��
		�ഫ��hhhh:mm:ss
		m��s�����ഫ....ymd���n�����p�� 
	*/

	/*  �j�p�g�ഫ  */
	ISOtime = ISOtime.toUpperCase();

	/* ��check�O���Oiso�ɶ�	 */
		
	if(ISOtime.indexOf(":")==-1){
		
		/* 1.����T  */
		var temp_Array = ISOtime.split("T"); 
		
		/* �|�����array	 P..Y..M..D   ..H..M..S */
		
		var tempYear = 0 ;
		var tempMonth = 0 ;
		var tempDay =0 ;
		var tempHour =0;
		var tempMinute = 0;
		var tempSecond = 0;
		
		if(temp_Array[0].indexOf("Y")!=-1){
			tempYear = Number(temp_Array[0].slice(1, temp_Array[0].indexOf("Y") ));
		}
		
		if(temp_Array[0].indexOf("M")!=-1){
			if(temp_Array[0].indexOf("Y")!=-1){
				/*  ���~�]����  */
				tempMonth = Number(temp_Array[0].slice( temp_Array[0].indexOf("Y")+1 , temp_Array[0].indexOf("M") ));
			}else{
				/*  �u����  */
				tempMonth = Number(temp_Array[0].slice( 0, temp_Array[0].indexOf("M") ));
			}
		}
		
		if(temp_Array[0].indexOf("D")!=-1){
			if(temp_Array[0].indexOf("M")!=-1){
				/* ���릳��  */
				tempDay = Number(temp_Array[0].slice( temp_Array[0].indexOf("M")+1, temp_Array[0].indexOf("D") ));
			}else if(temp_Array[0].indexOf("Y")!=-1){
				/* ���~����  */
				tempDay = Number(temp_Array[0].slice( temp_Array[0].indexOf("Y")+1, temp_Array[0].indexOf("D") ));
			}else{
				/* �u����  */
				tempDay = Number(temp_Array[0].slice( 0, temp_Array[0].indexOf("D") ));
			}
		}
		
		//-----------------------------------------------------------------------
		
		if(temp_Array[1].indexOf("H")!=-1){
			tempHour = Number(temp_Array[1].slice( 0, temp_Array[1].indexOf("H") ));
		}	
		
		if(temp_Array[1].indexOf("M")!=-1){
			if(temp_Array[1].indexOf("H")!=-1){
				/* ���p�ɤ]����  */
				tempMinute = Number(temp_Array[1].slice( temp_Array[1].indexOf("H")+1 , temp_Array[1].indexOf("M") ));
			}else{
				/* �u����  */
				tempMinute = temp_Array[1].slice( 0, temp_Array[1].indexOf("M") );
			}
		}	
		if(temp_Array[1].indexOf("S")!=-1){
			if(temp_Array[1].indexOf("M")!=-1){
				/* ��������  */
				tempSecond = Number(temp_Array[1].slice( temp_Array[1].indexOf("M")+1, temp_Array[1].indexOf("S") ));
			}else if(temp_Array[1].indexOf("H")!=-1){
				/* ���p�ɦ���  */
				tempSecond = Number(temp_Array[1].slice( temp_Array[1].indexOf("H")+1, temp_Array[1].indexOf("S") ));
			}else{
				/* �u����  */
				tempSecond = Number(temp_Array[1].slice( 0, temp_Array[1].indexOf("S") ));
			}
		}
		
		/* ------------------------------------------------------------------------------------- */
		
		var rtnVal;
		rtnVal = ((365*tempYear + 30*tempMonth + tempDay)*24 + tempHour) + ":" + tempMinute + ":" + tempSecond;
		return rtnVal;
	
	}else{
		/* �ëDISOtime�ҥH��function�����ഫ  */
		return ISOtime;
	
	}
	

}

function convertTime(OriginalTime){
	/* �榡�ഫ October,15 1582 00:00:00.0 -> 2002/10/30 00:00:00.0 */
			
	if(OriginalTime.indexOf(",")!=-1){
		
		var temp_Array = OriginalTime.split(" ");
		
		/* �|��3��array	 P..Y..M..D   ..H..M..S */
		
		var tempYear = 0 ;
		var tempMonth = 0 ;
		var tempDay =0 ;
		var tempTime =0;
		
				
		tempYear=temp_Array[1];	
		tempMonth =temp_Array[0].slice(0, temp_Array[0].indexOf(","));
		tempDay = temp_Array[0].slice(temp_Array[0].indexOf(",")+1, temp_Array[0].length );
		
		if(tempMonth.toUpperCase()=="JANUARY"){
			tempMonth=1;
		}
		else if(tempMonth.toUpperCase()=="FEBRUARY"){
			tempMonth=2;
		}
		else if(tempMonth.toUpperCase()=="MARCH"){
			tempMonth=3;
		}
		else if(tempMonth.toUpperCase()=="APRIL"){
			tempMonth=4;
		}
		else if(tempMonth.toUpperCase()=="MAY"){
			tempMonth=5;
		}
		else if(tempMonth.toUpperCase()=="JUNE"){
			tempMonth=6;
		}
		else if(tempMonth.toUpperCase()=="JULY"){
			tempMonth=7;
		}
		else if(tempMonth.toUpperCase()=="AUGUST"){
			tempMonth=8;
		}
		else if(tempMonth.toUpperCase()=="SEPTEMBER"){
			tempMonth=9;
		}
		else if(tempMonth.toUpperCase()=="OCTOBER"){
			tempMonth=10;
		}
		else if(tempMonth.toUpperCase()=="NOVEMBER"){
			tempMonth=11;
		}
		else if(tempMonth.toUpperCase()=="DECEMBER"){
			tempMonth=12;
		}
		
		/* Heroin 2004.03.24 */
		if(temp_Array.length<3){
			return OriginalTime;
		}
		tempTime = temp_Array[2].slice(0, temp_Array[2].indexOf("."));
		
		
		var rtnVal;
		rtnVal = tempYear  + "/" + tempMonth + "/" + tempDay + " " + tempTime;
		return rtnVal;
	
	}else{
		/* �ëDISOtime�ҥH��function�����ഫ */
		return OriginalTime;
	
	}
	

}

/* Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			*/
function findTargetStatus(referencedObjectiveIndex){
	var shardObjectiveStatus="unknown";
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var i=0;i<parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;i++){
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[i].targetObjectiveID){
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveProgressStatus.toString()=="true"){
					if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus.toString()=="true"){
						shardObjectiveStatus = true;
						return shardObjectiveStatus;
					
					}else if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus.toString()=="false"){
						shardObjectiveStatus = false;
						return shardObjectiveStatus;									
					}
					
				}else{
					shardObjectiveStatus = "unknown";
					return shardObjectiveStatus;				
				}	
			}			
		}
	}
}


/* Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			*/
/* Heroin 2004.08.17 */
function findTargetMeasure(referencedObjectiveIndex){
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var i =0;i<parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;i++){
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[i].targetObjectiveID){
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus){
					return parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveNormalizedMeasure;
					break;			
				}else{
					return 0;
					break;										
				}	
			}
		}
	}
	return 0;
}


/* Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			*/
/* Heroin 2004.08.17 */
function findTargetMeasureStatus(referencedObjectiveIndex){
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var i=0;i<parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;i++){
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[i].targetObjectiveID){				
				return parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus;
				break;			
			}
		}	
	}
	return "unknown";
}

/* =======Yunghsiao.2004.12.14================================================================== */
function findObjectivesTargetStatus(referencedObjectiveIndex){
	var shardObjectiveStatus="unknown";
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID){				
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveProgressStatus.toString()=="true"){
					if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus.toString()=="true"){
						shardObjectiveStatus = true;
						break;
					}else if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus.toString()=="false"){
						shardObjectiveStatus = false;
						break;										
					}			
				}else{
					shardObjectiveStatus = "unknown";
					break;										
				}	
			}
		}
	}
	return shardObjectiveStatus;
}
/* =====Yunghsiao.2004.12.14======================================================================== */
function findObjectivesTargetMeasure(referencedObjectiveIndex){
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID){
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus){
					return parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveNormalizedMeasure;
					break;			
				}else{
					return 0;
					break;										
				}	
			}
		}
	}
	return 0;
}

/* ====Yunghsiao.2004.12.14=============================================================================== */
function findObjectivesTargetMeasureStatus(referencedObjectiveIndex){
	for(var k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
			if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID){
				return parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus;
				break;			
			}
		}
	}
	return "unknown";
}

/* Heroin -2003.12.15 */
function countAbsoluteDuration01(tempDuration,LastTime,nowTime){
	
	
	/* �ഫLastTime�ɶ��榡2003/12/15 �W�� 9:30:20 */
	/* �ഫLastTime�ɶ��榡2003/12/15 17:30:20 */
	var tempLastTime=LastTime.split(" ");
	
	if(tempLastTime[0].indexOf("/")!=-1){
		var tempLastTime01=tempLastTime[0].split("/");
	}
	else{
		var tempLastTime01=tempLastTime[0].split(",");
	}
	
	
	if(tempLastTime[1]=="<?=$MSG['cfm_msg16'][$sysSession->lang];?>"){
		var tempLastTime02=tempLastTime[2].split(":");
		if(tempLastTime02[0].toString()!="12"){
			tempLastTime02[0]=Number(tempLastTime02[0])+12;
		}
	}
	else if(tempLastTime[1]=="<?=$MSG['cfm_msg15'][$sysSession->lang];?>"){
		var tempLastTime02=tempLastTime[2].split(":");
		if(tempLastTime02[0].toString()=="12"){
			tempLastTime02[0]=Number(tempLastTime02[0])-12;
		}
	}
	var tempLastTime04=new Date(Number(tempLastTime01[0]),Number(tempLastTime01[1])-1,Number(tempLastTime01[2]),Number(tempLastTime02[0]),Number(tempLastTime02[1]),Number(tempLastTime02[2]));
	var tempLastTime05=tempLastTime04.getTime();

	
	/* �p��ɶ� */
	var tempDuration= Number(tempDuration) + (Number(nowTime) - Number(tempLastTime05))/1000;
	return tempDuration;
	
}

/*  Heroin -2004.02.05 */
function countAbsoluteDuration(tempDuration,LastTime,nowTime){
	/* �ഫLastTime�ɶ��榡2003/12/15 �W�� 9:30:20 �w�אּ24hr�� */
	/* �ഫLastTime�ɶ��榡2003/12/15 17:30:20 */
	var tempLastTime=LastTime.split(" ");
	
	if(tempLastTime[0].indexOf("/")!=-1){
		var tempLastTime01=tempLastTime[0].split("/");
	}
	else{
		var tempLastTime01=tempLastTime[0].split(",");
	}
	
	var tempLastTime02=tempLastTime[1].split(":");
	
	var tempLastTime04=new Date(Number(tempLastTime01[0]),Number(tempLastTime01[1])-1,Number(tempLastTime01[2]),Number(tempLastTime02[0]),Number(tempLastTime02[1]),Number(tempLastTime02[2]));
	var tempLastTime05=tempLastTime04.getTime();

	
	/* �p��ɶ� */
	var tempDuration= Number(tempDuration) + (Number(nowTime) - Number(tempLastTime05))/1000;
	return tempDuration;
	
}

/******************************************************************************
**
** Function findTargetObj(item_index,ObjType,status)
** Inputs:  item_index,ObjType,status
** Return:  index of mapInfo array
**
** Description:�C��objective�itarget�h��, ��X�ѦҸ�status���A��mapInfo��array index
** 
** Vega 2004.10.15 add p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
*******************************************************************************/
function findTargetObj(item_index,ObjType,status){	
	if(ObjType=="primary"){		
		if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].existflag){
			if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].p_mapInfoList.length>0){
				for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[item_index].p_mapInfoList.length;p++){
					if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].p_mapInfoList[p].targetObjectiveID!=""){
						if(status=="readSatisfied"){	
							if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].p_mapInfoList[p].readSatisfiedStatus==true){
								return p;								
							}
						}else if(status=="readNormalized"){
							if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].p_mapInfoList[p].readNormalizedMeasure==true){
								return p;								
							}
						} 
					}						
				}
				return -1;
			}
		}
	}else if(parent.s_catalog.pathtree.ObjectiveList[item_index].mapInfoList.length>0){
		for(var p=0;p<parent.s_catalog.pathtree.ObjectiveList[item_index].mapInfoList.length;p++){
			if(parent.s_catalog.pathtree.ObjectiveList[item_index].mapInfoList[p].targetObjectiveID!=""){
				if(status=="readSatisfied"){	
					if(parent.s_catalog.pathtree.ObjectiveList[item_index].mapInfoList[p].readSatisfiedStatus==true){
						return p;								
					}
				}else if(status=="readNormalized"){
					if(parent.s_catalog.pathtree.ObjectiveList[item_index].mapInfoList[p].readNormalizedMeasure==true){
						return p;								
					}
				} 
			}						
		}
		return -1;
	}
}
/******************************************************************************
**
** Function findReadTargetObjectiveIndex(objectiveIndex,status)
** Inputs:  objectiveIndex,status
** Return:  index of mapInfo array
**
** Description:�C��objective�itarget�h��, ��X�ѦҸ�status���A��sharedObjectiveList��array index
** 
** Vega 2004.10.20 add p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
*******************************************************************************/
function findReadTargetObjectiveIndex(objectiveIndex,status){
	var referencedObjectiveID="";
	if(parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList!=null){
		if(status=="readStatus"){
			/*  Vega: read�u�঳�@��  */
			for(var j=0;j<parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList.length;j++){						
				if(parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].targetObjectiveID!=""){
					if(parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].readSatisfiedStatus){
						referencedObjectiveID=parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].targetObjectiveID;
						break;
					}
				}
			}
		}else if(status=="readNormalized"){
			/*  Vega: read�u�঳�@��  */
			for(var j=0;j<parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList.length;j++){	
				if(parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].targetObjectiveID!=""){
					if(parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].readNormalizedMeasure){
						referencedObjectiveID=parent.s_catalog.pathtree.primaryObjectiveList[objectiveIndex].p_mapInfoList[j].targetObjectiveID;
						break;
					}
				}
			}
		}
	}else if(parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList.length>0){
		if(status=="readStatus"){
			for(var p=0;p<parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList.length;p++){
				if(parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].targetObjectiveID!=""){
					if(parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].readSatisfiedStatus){
						referencedObjectiveID=parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].targetObjectiveID;
						break;
					}
				}
			}
		}else if(status=="readNormalized"){
			for(var p=0;p<parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList.length;p++){
				if(parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].targetObjectiveID!=""){
					if(parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].readNormalizedMeasure){
						referencedObjectiveID=parent.s_catalog.pathtree.ObjectiveList[objectiveIndex].mapInfoList[p].targetObjectiveID;
						break;
					}
				}
			}
		}
	}
	/* ����sharedObjectiveList��array index */
	if(referencedObjectiveID!=""){
		for(var i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
			if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==referencedObjectiveID){
				return i;				
			}	
		}			
	}else{
		return -1;
	}	
}

/******************************************************************************
**
** Function findWriteTargetObjectiveIndex(objectiveID,status)�MREAD���@��
** Inputs:  objectiveID,status
** Return:  index of mapInfo array
**
** Description:�C��objective�itarget�h��, ��X�ѦҸ�status���A��mapInfo��array index
** 
** Vega 2004.10.20 add p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
*******************************************************************************/
function findWriteTargetObjectiveIndex(objectiveID,status){
	var referencedObjectiveIDArray= new Array;	
	for(var i=0;i<parent.s_catalog.pathtree.primaryObjectiveList.length;i++){
		if(parent.s_catalog.pathtree.primaryObjectiveList[i].objectiveID==objectiveID){
			if(status=="writeStatus"){
				/*  Vega :write�i�g�h��  */
				var objectiveCount=0;
				for(var j=0;j<parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList.length;j++){				
					if(parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].targetObjectiveID!=""){
						if(parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].writeSatisfiedStatus){
							referencedObjectiveIDArray[objectiveCount]=parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].targetObjectiveID;
							objectiveCount++;
						}
					}
				}
				break;
			}else if(status=="writeNormalized"){
				/*  Vega :write�i�g�h��  */
				var objectiveCount=0;
				for(var j=0;j<parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList.length;j++){				
					if(parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].targetObjectiveID!=""){
						if(parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].writeNormalizedMeasure){
							referencedObjectiveIDArray[objectiveCount]=parent.s_catalog.pathtree.primaryObjectiveList[i].p_mapInfoList[j].targetObjectiveID;
							objectiveCount++;
						}
					}
				}
				break;
			}
		}
	}
	for(var i=0;i<parent.s_catalog.pathtree.objectiveList.length;i++){
		if(parent.s_catalog.pathtree.objectiveList[i].objectiveID==objectiveID){
			if(status=="writeStatus"){
				var objectiveCount=0;
				for(var j=0;j<parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList.length;j++){				
					if(parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].targetObjectiveID!=""){
						if(parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].writeSatisfiedStatus){
							referencedObjectiveIDArray[objectiveCount]=parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].targetObjectiveID;
							objectiveCount++;
						}
					}
				}
				break;
			}else if(status=="writeNormalized"){
				var objectiveCount=0;
				for(var j=0;j<parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList.length;j++){				
					if(parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].targetObjectiveID!=""){
						if(parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].writeNormalizedMeasure){
							referencedObjectiveIDArray[objectiveCount]=parent.s_catalog.pathtree.ObjectiveList[i].mapInfoList[j].targetObjectiveID;
							objectiveCount++;
						}
					}
				}
				break;
			}
		}
	}			
	/* ����sharedObjectiveList��array index */
	var referencedObjectiveIDIndexArray = new Array();
	if(referencedObjectiveIDArray.length>0){		
		for(var r=0;r<referencedObjectiveIDArray.length;r++){
			for(var i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
				if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==referencedObjectiveIDArray[r]){
					referencedObjectiveIDIndexArray[r]= i;
					break;
				}
			}
		}		
	}
	return referencedObjectiveIDIndexArray;		
}

function init_engine_functions() {
	enfunctions = new enginefunctions();	
}

</script>
</body>
</html>
