<script type="text/javascript" language="JavaScript" src="/lib/xmlextras.js"></script>
<script language="javascript" for="window" event="onload">
/* SCORM 1.X API function */
/* 2004.03.05 Vega 整理 */


/* Session變數設定 */
var student_id="<?=$sysSession->username?>";
var student_last_name="<?=$lname?>";
var student_name="<?=$fname?>";
var course_ID="<?=$sysSession->course_id?>";
var xmlhttp_get="/learn/scorm/get.php";
var xmlhttp_set="/learn/scorm/set.php";

var IsLMSInitialize;
IsLMSInitialize = "false";
var IsCommit;
IsCommit = "false";

/* ---------------- LMS Mandatory:Yes ------------------- */

var cmi_core__children="student_id,student_name,lesson_location,credit,lesson_status,total_time,lesson_mode,exit,session_time,suspend_data,launch_data,entry,score";
var cmi_core_score__children="raw,max,min";
var cmi_core_student_id=student_id;
var cmi_core_student_name=student_last_name + ", " + student_name;
var cmi_core_lesson_location="";
var cmi_core_credit="<?=$credit?>";
var cmi_core_lesson_status="";
var cmi_core_total_time="";
var cmi_core_exit="";
var cmi_core_session_time="";
var cmi_suspend_data="";
var cmi_launch_data="";
var cmi_core_entry="";
var cmi_core_score_raw="";


/* ---------------- LMS Mandatory:No -------------------- */

var cmi_core_lesson_mode= cmi_core_credit == 'credit' ? "normal" : 'browse';
var cmi_student_data_mastery_score="";
var cmi_core_score_max="";
var cmi_core_score_min="";

/* -----------------cmi.student_data parameter------------- */

var cmi_student_data__children="mastery_score,max_time_allowed,time_limit_action";
var cmi_sd_max_time_allowed="";
var cmi_sd_time_limit_action="";

/* -----------------cmi.student_preference parameter------------- */

var cmi_student_preference__children="audio,language,speed,text";
var cmi_student_preference_audio;
var cmi_student_preference_language;
var cmi_student_preference_speed;
var cmi_student_preference_text;

/* -----------------cmi_comments parameter------------- */

var cmi_comments="";
var cmi_comments_from_lms="";

/* -----------------error string------------------------ */

var lastError = 0;
var ErrorStringArray = new Array(12);
ErrorStringArray[0] = new Array(0,"No error");
ErrorStringArray[1] = new Array(101,"General Exception");
ErrorStringArray[2] = new Array(201,"Invalid argument error");
ErrorStringArray[3] = new Array(202,"Element cannot have children");
ErrorStringArray[4] = new Array(203,"Element not an array - Cannot have count");
ErrorStringArray[5] = new Array(301,"Not Initialized");
ErrorStringArray[6] = new Array(401,"Not implemented error");
ErrorStringArray[7] = new Array(402,"Invalid set value, element is a keyword");
ErrorStringArray[8] = new Array(403,"Element is read only");
ErrorStringArray[9] = new Array(404,"Element is write only");
ErrorStringArray[10] = new Array(405,"Incorrect Data Type");

/* -----------------lesson_status parameter------------- */

var LessonStatusArray = new Array(6);
LessonStatusArray[0] = "passed"; 
LessonStatusArray[1] = "completed";
LessonStatusArray[2] = "failed";
LessonStatusArray[3] = "incomplete";
LessonStatusArray[4] = "browsed";
LessonStatusArray[5] = "not attempted";

/* -----------------core.exit parameter------------- */

var CoreExitArray = new Array(4);
CoreExitArray[0] = "time-out"; 
CoreExitArray[1] = "suspend";
CoreExitArray[2] = "logout";
CoreExitArray[3] = "";

/* -----------------cmi.interactions.n.type parameter------------- */

var TypeArray = new Array(8);
TypeArray[0] = "true-false"; 
TypeArray[1] = "choice";
TypeArray[2] = "fill-in";
TypeArray[3] = "matching";
TypeArray[4] = "performance";
TypeArray[5] = "sequencing";
TypeArray[6] = "likert";
TypeArray[7] = "numeric";

/* -----------------cmi.interactions.n.result parameter------------- */

var ResultArray = new Array(4);
ResultArray[0] = "correct"; 
ResultArray[1] = "wrong";
ResultArray[2] = "unanticipated";
ResultArray[3] = "neutral";

/* -----------------cmi.interactions parameter------------- */

var cmi_interactions__children = "id,objectives,time,type,correct_responses,weighting,student_response,result,latency";
var n=-1; /* cmi.interactions._count */
var m=-1; /* cmi.interactions.n.correct_responses._count   */
var c=-1; /* cmi.interactions.n.objectives._count */
var CmiInteractionsArray = new Array();
var CmiInteractionsObjectivesArray = new Array();
var CmiInteractionsCorrect_ResponsesArray = new Array();
/*宣告一個動態陣列,一個新的interaction就initial一個元素,
  此元素再對應一個陣列並initial初值,例如:
  當LMSSetValue("cmi.interactions.n.id","value")執行時,先檢查
  CmiInteractionsArray[n]是否initial如果沒有就initial如下:
  CmiInteractionsArray[n]=new Array("","","","","","","",-1,-1)
  如果有就直接更改對應元素值,對應欄位如下:
  CmiInteractionsArray[n][0]<==>id
  CmiInteractionsArray[n][1]<==>time
  CmiInteractionsArray[n][2]<==>type
  CmiInteractionsArray[n][3]<==>weighting
  CmiInteractionsArray[n][4]<==>student_response
  CmiInteractionsArray[n][5]<==>result
  CmiInteractionsArray[n][6]<==>latency
  CmiInteractionsArray[n][7]<==>cmi.interactions.n.objectives._count
  CmiInteractionsArray[n][8]<==>cmi.interactions.n.correct_responses._count  
*/

/* -----------------cmi.objectives parameter------------- */

var cmi_objectives__children = "id,score,status";
var cmi_objectives_n_score__children = "raw,max,min";
var o=-1; /* cmi.objectives._count */
var CmiObjectivesArray = new Array();
/*宣告一個動態陣列,一個新的objective就initial一個元素,
  此元素再對應一個陣列並initial初值,例如:
  當LMSSetValue("cmi.objectives.n.id","value")執行時,先檢查
  CmiObjectivesArray[n]是否initial如果沒有就initial如下:
  CmiObjectivesArray[n]=new Array("","","","","")
  如果有就直接更改對應元素值,對應欄位如下:
  CmiObjectivesArray[o][0]<==>id
  CmiObjectivesArray[o][1]<==>score.raw
  CmiObjectivesArray[o][2]<==>score.max
  CmiObjectivesArray[o][3]<==>score.min
  CmiObjectivesArray[o][4]<==>status
*/

/* ------------------DataModelCheckArray---------------- */
var DataModelArray = new Array(30);
DataModelArray[0] = "cmi.core._children";
DataModelArray[1] = "cmi.core._count";
DataModelArray[2] = "cmi.core.student_id";
DataModelArray[3] = "cmi.core.student_name";
DataModelArray[4] = "cmi.core.lesson_location";
DataModelArray[5] = "cmi.core.credit";
DataModelArray[6] = "cmi.core.lesson_status";
DataModelArray[7] = "cmi.core.entry";
DataModelArray[8] = "cmi.core.total_time";
DataModelArray[9] = "cmi.core.lesson_mode";
DataModelArray[10] = "cmi.core.exit";
DataModelArray[11] = "cmi.core.session_time";
DataModelArray[12] = "cmi.core.score.raw";
DataModelArray[13] = "cmi.core.score.min";
DataModelArray[14] = "cmi.core.score.max";
DataModelArray[15] = "cmi.suspend_data";
DataModelArray[16] = "cmi.launch_data";
DataModelArray[17] = "cmi.comments";
DataModelArray[18] = "cmi.comments_from_lms";
DataModelArray[19] = "cmi.student_data._children";
DataModelArray[20] = "cmi.student_data._count";
DataModelArray[21] = "cmi.student_data.mastery_score";
DataModelArray[22] = "cmi.student_data.max_time_allowed";
DataModelArray[23] = "cmi.student_data.time_limit_action";
DataModelArray[24] = "cmi.student_preference._children";
DataModelArray[25] = "cmi.studnet_preference._count";
DataModelArray[26] = "cmi.student_preference.audio";
DataModelArray[27] = "cmi.student_preference.language";
DataModelArray[28] = "cmi.student_preference.speed";
DataModelArray[29] = "cmi.student_preference.text";

/* ---------------- User Define -------------------- */

var course_ID=course_ID;
var SCO_ID="";

function InitialObject(){
         this.LMSInitialize=LMSInitialize
         this.LMSGetValue = LMSGetValue
         this.LMSSetValue = LMSSetValue
	 this.LMSFinish = LMSFinish
	 this.LMSCommit = LMSCommit
	 this.GetManifestData = GetManifestData
	 this.isFirstEnterSco = isFirstEnterSco
	 this.setSCOInitialData = setSCOInitialData
	 this.computeTotalTime = computeTotalTime
	 this.convertTotalSeconds = convertTotalSeconds
	 this.checkCMITimespan = checkCMITimespan
	 this.LMSGetLastError = LMSGetLastError
	 this.LMSGetErrorString = LMSGetErrorString
	 this.LMSGetDiagnostic = LMSGetDiagnostic
	 this.CheckDataModel = CheckDataModel
	 this.GetSCO_ID = GetSCO_ID
	 this.SetSCO_ID = SetSCO_ID
}

function LMSInitialize(){
IsCommit = "false";   
   
if(IsLMSInitialize !="true"){
   if((LMSInitialize.arguments.length==1)&&(LMSInitialize.arguments[0] == "" )){ 	        
         /* 依course_ID,user_ID,sco_ID判斷是否為第一次呼叫該SCO  */
	cmi_core_lesson_status = "not attempted"; 
	cmi_core_entry="ab-initio";
	cmi_core_total_time="0000:00:00.00";
    cmi_student_data_mastery_score=GetManifestData("cmi.student_data.mastery_score");
	cmi_sd_max_time_allowed=GetManifestData("cmi.student_data.max_time_allowed");
	cmi_sd_time_limit_action=GetManifestData("cmi.student_data.time_limit_action");
	cmi_core_lesson_location = "";
	SCO_ID = tocList[GlobalStateObj.CurrentActivity].id;
	var flag = isFirstEnterSco("is.first.enter.sco");
         if(flag==""){ /* Yes:設定所有變數初始值 */
                
      		cmi_suspend_data="";
      		cmi_launch_data=GetManifestData("cmi.launch_data");
		cmi_student_preference_audio = "0";
                cmi_student_preference_language = "";
                cmi_student_preference_speed = "0";
                cmi_student_preference_text = "0";
         }
	 else{ /* NO:從資料庫中載入所有變數初始值(透過XMLHTTP) */
 	        setSCOInitialData("set.sco.initial.data");
     	        
	 }
	 
	 
	 IsLMSInitialize = "true";
	 lastError = 0;
	 return "true";   
    }else if ((LMSInitialize.arguments.length==1)&&(LMSInitialize.arguments[0] != "" )){
    	 lastError = 201;
    	 return "false";
    }
}else{
    lastError = 101;
    return "false";
}
	 
}

function LMSFinish(){
   if(IsLMSInitialize=="true"){	
	if((LMSFinish.arguments.length==1)){
   	     if(LMSFinish.arguments[0]==""){	
		if(IsCommit == "false"){
			var tempStrCommit = LMSCommit("");
			if(tempStrCommit=="true"){
				/* alert("commit!!!"); */
				/* IsCommit = "false"; */
			}
		}
   		IsLMSInitialize = "false"; 	        
		lastError = 0;
		n=-1;
		m=-1;
		o=-1;
		c=-1;
		cmi_comments = "";
		return "true";
	     }else{
	     	lastError = 201;
	  	return "false";
	     }
	}else{
		return "false";
	}
   }else{
	lastError = 301;   	
   	return "false";
   }
}

function LMSGetValue(DataModel){
if(IsLMSInitialize == "true"){
   if(DataModel.indexOf("cmi.objectives")!=-1 && DataModel!="cmi.objectives._children" && DataModel!="cmi.objectives._count"){
                var tempArray=DataModel.split(".");
                
                if(DataModel.indexOf("score._children")!=-1 && tempArray.length==5){
			lastError = 0;
			return cmi_objectives_n_score__children;
		}	
                if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
                    if(tempArray[2]>o){
			lastError = 0;
			return "";
		    }
		    else{
		        lastError = 0;
		        return CmiObjectivesArray[eval(tempArray[2])][0];
		    }	
                }
		if(DataModel.indexOf("score.raw")!=-1 && tempArray.length==5){
                    if(tempArray[2]>o){
			lastError = 0;
			return "";
		    }
		    else{
		        lastError = 0;
		        return CmiObjectivesArray[eval(tempArray[2])][1];
		    }	
                } 
               	if(DataModel.indexOf("score.max")!=-1 && tempArray.length==5){
                    if(tempArray[2]>o){
			lastError = 0;
			return "";
		    }
		    else{
		        lastError = 0;
		        return CmiObjectivesArray[eval(tempArray[2])][2];
		    }	
                }  
                if(DataModel.indexOf("score.min")!=-1 && tempArray.length==5){
                    if(tempArray[2]>o){
			lastError = 0;
			return "";
		    }
		    else{
		        lastError = 0;
		        return CmiObjectivesArray[eval(tempArray[2])][3];
		    }	
                } 
                if(DataModel.indexOf("status")!=-1 && tempArray.length==4){
                    if(tempArray[2]>o){
			lastError = 0;
			return "";
		    }
		    else{
		        lastError = 0;
		        return CmiObjectivesArray[eval(tempArray[2])][4];
		    }	
                }                
                                                                                           
   }
   else if(DataModel.indexOf("cmi.interactions")!=-1 && DataModel!="cmi.interactions._children" && DataModel!="cmi.interactions._count"){
                var tempArray=DataModel.split(".");
                if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }
                if(DataModel.indexOf("time")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }  
                if(DataModel.indexOf("type")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }    
                if(DataModel.indexOf("weighting")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                } 
                if(DataModel.indexOf("student_response")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }    
                if(DataModel.indexOf("result")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }   
                if(DataModel.indexOf("latency")!=-1 && tempArray.length==4){
                	lastError = 404;
			return "";
                }
                if(DataModel.indexOf("objectives")!=-1 && DataModel.indexOf("id")!=-1 && tempArray.length==6){
                	lastError = 404;
			return "";
                }
                if(DataModel.indexOf("objectives._count")!=-1 && tempArray.length==5){
                	var objectivesArray=DataModel.split("."); 
	               	lastError = 0;
			if(c==-1){
				return 0;
			}
			else{
				return (new Number(CmiInteractionsArray[eval(objectivesArray[2])][7])+1);
			}	
                }   
                if(DataModel.indexOf("correct_responses")!=-1 && DataModel.indexOf("pattern")!=-1 && tempArray.length==6){
                	lastError = 404;
			return "";
                }
                if(DataModel.indexOf("correct_responses._count")!=-1 && tempArray.length==5){
                	var correctResponsesArray=DataModel.split("."); 
	               	lastError = 0;
	               	if(m==-1){
				return 0;
			}
			else{
				return (new Number(CmiInteractionsArray[eval(correctResponsesArray[2])][8])+1);
			}	
                }                 
                                                                                                                 
   }
   else{
	switch (DataModel){
		case "cmi.core.student_id":
			lastError = 0;
			return cmi_core_student_id;
			break;
		case "cmi.core.student_name":
			lastError = 0;
			return cmi_core_student_name;
			break;
		case "cmi.core.lesson_status":
			lastError = 0;
			return cmi_core_lesson_status;
			break;
		case "cmi.core.lesson_location":
			lastError = 0;
			return cmi_core_lesson_location;
			break;
		case "cmi.core.lesson_mode":
			lastError = 0;
			return cmi_core_lesson_mode;
			break;	
		case "cmi.core.credit":
			lastError = 0;
			return cmi_core_credit;
			break;			
		case "cmi.core.entry":
			lastError = 0;
			return cmi_core_entry;
			break;	
		case "cmi.core.score.raw":
			lastError = 0;
			return cmi_core_score_raw;
			break;	
		case "cmi.core.total_time":
			lastError = 0;
			return cmi_core_total_time;
			break;
		case "cmi.suspend_data":
			lastError = 0;
			return cmi_suspend_data;
			break;		
		case "cmi.launch_data":
			lastError = 0;
			return cmi_launch_data;
			break;																					
		case "cmi.core._children":
			lastError = 0;
			return cmi_core__children;
			break;																					
		case "cmi.core.score._children":
			lastError = 0;
			return cmi_core_score__children;
			break;																					
		case "cmi.core.score.max":
			lastError = 0;
			return cmi_core_score_max;
			break;
		case "cmi.core.score.min":
			lastError = 0;
			return cmi_core_score_min;
			break;	
		case "cmi.student_data.mastery_score":
			lastError = 0;
			return cmi_student_data_mastery_score;
			break;	
		case "cmi.interactions._children":
			lastError = 0;
			return cmi_interactions__children;
			break;	
		case "cmi.interactions._count":
			lastError = 0;
			return (new Number(n)+1);
			break;		
		case "cmi.objectives._children":
			lastError = 0;
			return cmi_objectives__children;
			break;	
		case "cmi.objectives._count":
			lastError = 0;
			return (new Number(o)+1);
			break;							
		
		case "cmi.core.exit":
			lastError = 404;
			return "";
		      	break;
		case "cmi.core.session_time":
		      	lastError = 404;
		      	return "";
		      	break;
		case "cmi.student_data._children":
		      	lastError = 0;
		      	return cmi_student_data__children;
		      	break;
		case "cmi.student_data.max_time_allowed":
		      	lastError = 0;
		      	return cmi_sd_max_time_allowed;
		      	break;
		case "cmi.student_data.time_limit_action":
		      	lastError = 0;
		      	return cmi_sd_time_limit_action;
		      	break;
		
		case "cmi.student_preference._children":
		      	lastError = 0;
		      	return cmi_student_preference__children;
		      	break;

		case "cmi.student_preference.audio":
		      	lastError = 0;
		      	return cmi_student_preference_audio;
		      	break;
		case "cmi.student_preference.language":
		      	lastError = 0;
		      	return cmi_student_preference_language;
		      	break;
		case "cmi.student_preference.speed":
		      	lastError = 0;
		      	return cmi_student_preference_speed;
		      	break;
		case "cmi.student_preference.text":
		      	lastError = 0;
		      	return cmi_student_preference_text;
		      	break;
		case "cmi.comments":
		      	lastError = 0;
		      	return cmi_comments;
		      	break;
		case "cmi.comments_from_lms":
		      	lastError = 0;
		      	return cmi_comments_from_lms;
		      	break;
		default :
		      CheckDataModel(DataModel);
		      return "";
		      break;
 	}
   }
}else{
	lastError = 301;
	return "";
}   
	
}

function LMSSetValue(DataModel, Value){
if(IsLMSInitialize=="true"){
   if(DataModel.indexOf("cmi.objectives")!=-1 && DataModel!="cmi.objectives._children" && DataModel!="cmi.objectives._count"){
                var tempArray=DataModel.split(".");
                if((tempArray[2]-o)==1){
                   CmiObjectivesArray[eval(tempArray[2])]=new Array("","","","","");
                   o=new Number(tempArray[2]);
                }   
                
                if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
			if(Value.indexOf(" ")==-1 && Value!=" " && Value.length <= 255 && Value!="O09" && Value!=""){
				CmiObjectivesArray[eval(tempArray[2])][0]=Value;
				lastError = 0;
       
   	                }else{
   	                        if(Value=="O09"){
   					lastError = 201;
   	                        }else{
   					lastError = 405;
   	                        }
   	                }
   	        }
   	        if(DataModel.indexOf("score.raw")!=-1 && tempArray.length==5){
			if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
				CmiObjectivesArray[eval(tempArray[2])][1]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }    
   	        if(DataModel.indexOf("score.max")!=-1 && tempArray.length==5){
			if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
				CmiObjectivesArray[eval(tempArray[2])][2]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }  
		if(DataModel.indexOf("score.min")!=-1 && tempArray.length==5){
			if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
				CmiObjectivesArray[eval(tempArray[2])][3]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        } 
   	        if(DataModel.indexOf("status")!=-1 && tempArray.length==4){
			var LessonStatusParameterFound = "false"; 
                        for(i=0;i<LessonStatusArray.length;i++){
				if(Value == LessonStatusArray[i]){
					LessonStatusParameterFound = "true";
				}                      
                        }
                        if(LessonStatusParameterFound == "true"){
	                      	CmiObjectivesArray[eval(tempArray[2])][4] = Value;
	                      	lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }         	        
   	        if(DataModel.indexOf("score._children")!=-1 && tempArray.length==5){
			lastError = 402;
		}	
   }
   else if(DataModel.indexOf("cmi.interactions")!=-1 && DataModel!="cmi.interactions._children" && DataModel!="cmi.interactions._count"){
                var tempArray=DataModel.split(".");
                if((tempArray[2]-n)==1){
                   CmiInteractionsArray[eval(tempArray[2])]=new Array("","","","","","","",-1,-1);
                   n=new Number(tempArray[2]);
                   m=CmiInteractionsArray[n][8];
                   c=CmiInteractionsArray[n][7];
                }  
                if(DataModel.indexOf("objectives._count")!=-1 && tempArray.length==5){
                	lastError = 402;
                }
                if(DataModel.indexOf("correct_responses._count")!=-1 && tempArray.length==5){
                	lastError = 402;
                }
                if(DataModel.indexOf("objectives")!=-1 && tempArray.length==6){
                        var temp1Array=DataModel.split(".");
                	if(temp1Array[4]!=c){
                	        CmiInteractionsObjectivesArray[n]=new Array("");
                   		c=temp1Array[4];
                   		CmiInteractionsArray[n][7]=c;
                   	}	
                        if(DataModel.indexOf("id")!=-1 && tempArray.length==6){
				if(Value.indexOf(" ")==-1 && Value!=" " && Value.length<=255 && Value!=""){
				     CmiInteractionsObjectivesArray[n][c]=Value;
				     lastError = 0;
   	                        }else{
   	                      	     lastError = 405;
   	                	}
   	                }
   	                
   	        }
   	        if(DataModel.indexOf("correct_responses")!=-1 && tempArray.length==6){
                        var temp1Array=DataModel.split(".");
                	if((temp1Array[4]-m)==1){
                	        CmiInteractionsCorrect_ResponsesArray[n]=new Array("");
                   		m=temp1Array[4];
                   		CmiInteractionsArray[n][8]=m;
                   	}else if ((temp1Array[4]-m)==0){
                   	}else{
                   		lastError = 201;
                   		return "false";
                   	}	
                        if(DataModel.indexOf("pattern")!=-1 && tempArray.length==6){
				if(CmiInteractionsArray[n][2]=="true-false"){
			 	    if(Value=="0" || Value=="1" || Value=="t" || Value=="f"){
					CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
					lastError = 0;
				    }
				    else{
				        lastError = 405;
				    }
	   	                }
	   	                else if(CmiInteractionsArray[n][2]=="choice"){
			 	    var reg=/^[0-9a-z]$/; 
			 	    var sArray=Value.split(",");
			 	    if(sArray.length!=0){
			 	        var temp_flag="true";
			 	        for(var i=0;i<sArray.length;i++){
					    if(sArray[i].match(reg)==null){
					       temp_flag="false";
					    }
					}
					if(temp_flag=="true"){    
						CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
						lastError = 0;
					}
					else{
						lastError = 405;
					}	
				    }
				    else{
				        if(Value.match(reg)!=null){
				            	CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
				            	lastError = 0;
				        }
				        else{
				        	lastError = 405;
				        }
				    }
	   	                }   
	   	                else if(CmiInteractionsArray[n][2]=="fill-in"){
			 	    if((new String(Value)).length <= 255 && Value!="" && Value!=" "){
					CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
					lastError = 0;
				    }
				    else{
				        lastError = 405;
				    }
	   	                }
	   	                else if(CmiInteractionsArray[n][2]=="numeric"){
			 	    if(!isNaN(Value) && Value!="" && Value!=" "){
					CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
					lastError = 0;
				    }
				    else{
				        lastError = 405;
				    }
	   	                }
				else if(CmiInteractionsArray[n][2]=="likert"){
			 	    var reg=/^[0-9a-z]$/; 
			 	    if(Value.match(reg)!=null || Value==""){
					CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
					lastError = 0;
				    }
				    else{
				        lastError = 405;
				    }
	   	                }   
	   	                else if(CmiInteractionsArray[n][2]=="matching"){
			 	    var reg=/^[0-9a-z]\.[0-9a-z]$/; 
			 	    var sArray=Value.split(",");
			 	    if(sArray.length!=0){
			 	        var temp_flag="true";
			 	        for(var i=0;i<sArray.length;i++){
					    if(sArray[i].match(reg)==null){
					       temp_flag="false";
					    }
					}
					if(temp_flag=="true"){    
						CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
						lastError = 0;
					}
					else{
						lastError = 405;
					}	
				    }
				    else{
				        if(Value.match(reg)!=null){
				            	CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
				            	lastError = 0;
				        }
				        else{
				        	lastError = 405;
				        }
				    }
	   	                }     
	   	                else if(CmiInteractionsArray[n][2]=="performance"){
			 	    
			 	    if(Value.length <= 255 && Value!=""){
					CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
					lastError = 0;
				    }
				    else{
				        lastError = 405;
				    }
	   	                }
				else if(CmiInteractionsArray[n][2]=="sequencing"){
			 	    var reg=/^[0-9a-z]$/; 
			 	    var sArray=Value.split(",");
			 	    if(sArray.length!=0){
			 	        var temp_flag="true";
			 	        for(var i=0;i<sArray.length;i++){
					    if(sArray[i].match(reg)==null){
					       temp_flag="false";
					    }
					}
					if(temp_flag=="true"){    
						CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
						lastError = 0;
					}
					else{
						lastError = 405;
					}	
				    }
				    else{
				        lastError = 405;
				    }
	   	                }  
	   	                else{
	   	                	CmiInteractionsCorrect_ResponsesArray[n][m]=Value;
	   	                	lastError = 0;
	   	                }
   	                
   	                }
   	                
   	        }
                if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
			if(Value.indexOf(" ")==-1 && Value!=" " && Value.length<=255 && Value!=""){
				CmiInteractionsArray[n][0]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }
                if(DataModel.indexOf("time")!=-1 && tempArray.length==4){
			if(checkCMITime(Value)=="true" && Value!="" && Value!=" "){
				CmiInteractionsArray[n][1]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }   
                if(DataModel.indexOf("type")!=-1 && tempArray.length==4){
			var TypeParameterFound = "false"; 
                        for(i=0;i<TypeArray.length;i++){
				if(Value == TypeArray[i]){
					TypeParameterFound = "true";
				}                      
                        }
                        if(TypeParameterFound == "true" && Value!="" && Value!=" "){
				CmiInteractionsArray[n][2]=Value;
				lastError = 0;
   	                }
   	                else{
   	                	CmiInteractionsArray[n][2]=Value;
   	                        lastError = 405;
   	                }
   	        }   	   
                if(DataModel.indexOf("weighting")!=-1 && tempArray.length==4){
			if(!isNaN(Value) && Value!="" && Value!=" "){
				CmiInteractionsArray[n][3]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }     
                if(DataModel.indexOf("student_response")!=-1 && tempArray.length==4){
			var temp1Array=DataModel.split(".");
                	if(temp1Array[2]<=n){
                		n=temp1Array[2];
                	}
                	else{
                   		lastError = 201;
                   		return "false";
                   	}
                   	
			if(CmiInteractionsArray[n][2]=="true-false"){
		 	    if(Value=="0" || Value=="1" || Value=="t" || Value=="f"){
				CmiInteractionsArray[n][4]=Value;
				lastError = 0;
			    }
			    else{
			        lastError = 405;
			    }
   	                }
   	                else if(CmiInteractionsArray[n][2]=="choice"){
		 	    var reg=/^[0-9a-z]$/;
		 	    if(String(Value).indexOf(",")!=-1){
			 	    var sArray=String(Value).split(",");
			 	    if(sArray.length!=0){
			 	        var temp_flag="true";
			 	        for(var i=0;i<sArray.length;i++){
					    if(sArray[i].match(reg)==null){
					       temp_flag="false";
					    }
					}
					if(temp_flag=="true"){    
						CmiInteractionsArray[n][4]=Value;
						lastError = 0;
					}
					else{
						lastError = 405;
					}	
				    }
			    }
			    else{
			        if(String(Value).match(reg)!=null){
			            	CmiInteractionsArray[n][4]=String(Value);
			            	lastError = 0;
			        }
			        else{
			        	lastError = 405;
			        }
			    }
   	                }   
   	                else if(CmiInteractionsArray[n][2]=="fill-in"){
		 	    if((new String(Value)).length <= 255 && Value!="" && Value!=" "){
				CmiInteractionsArray[n][4]=Value;
				lastError = 0;
			    }
			    else{
			        lastError = 405;
			    }
   	                }
   	                else if(CmiInteractionsArray[n][2]=="numeric"){
		 	    if(!isNaN(Value) && Value!="" && Value!=" "){
				CmiInteractionsArray[n][4]=Value;
				lastError = 0;
			    }
			    else{
			        lastError = 405;
			    }
   	                }
			else if(CmiInteractionsArray[n][2]=="likert"){
		 	    var reg=/^[0-9a-z]$/; 
		 	    if(Value.match(reg)!=null){
				CmiInteractionsArray[n][4]=Value;
				lastError = 0;
			    }
			    else{
			        lastError = 405;
			    }
   	                }   
   	                else if(CmiInteractionsArray[n][2]=="matching"){
		 	    var reg=/^[0-9a-z]\.[0-9a-z]$/; 
		 	    var sArray=Value.split(",");
		 	    if(sArray.length!=0){
		 	        var temp_flag="true";
		 	        for(var i=0;i<sArray.length;i++){
				    if(sArray[i].match(reg)==null){
				       temp_flag="false";
				    }
				}
				if(temp_flag=="true"){    
					CmiInteractionsArray[n][4]=Value;
					lastError = 0;
				}
				else{
					lastError = 405;
				}	
			    }
			    else{
			        if(Value.match(reg)!=null){
			            	CmiInteractionsArray[n][4]=Value;
			            	lastError = 0;
			        }
			        else{
			        	lastError = 405;
			        }
			    }
   	                }     
   	                else if(CmiInteractionsArray[n][2]=="performance"){
		 	    if(Value.length <= 255 && Value!=""){
				CmiInteractionsArray[n][4]=Value;
				lastError = 0;
			    }
			    else{
			        lastError = 405;
			    }
   	                }
			else if(CmiInteractionsArray[n][2]=="sequencing"){
		 	    var reg=/^[0-9a-z]$/; 
		 	    var sArray=Value.split(",");
		 	    if(sArray.length!=0){
		 	        var temp_flag="true";
		 	        for(var i=0;i<sArray.length;i++){
				    if(sArray[i].match(reg)==null){
				       temp_flag="false";
				    }
				}
				if(temp_flag=="true"){    
					CmiInteractionsArray[n][4]=Value;
					lastError = 0;
				}
				else{
					lastError = 405;
				}	
			    }
			    else{
			        lastError = 405;
			    }
   	                }  
   	                else{
   	                	CmiInteractionsArray[n][4]=Value;
   	                	lastError = 0;
   	                }    	                	                
   	                
   	        }    
   	        if(DataModel.indexOf("result")!=-1 && tempArray.length==4){

			var ResultParameterFound = "false"; 
                        for(i=0;i<ResultArray.length;i++){
				if(Value == ResultArray[i]){
					ResultParameterFound = "true";
				}                      
                        }
                        if((ResultParameterFound == "true" || (!isNaN(Value))) && Value!="" && Value!=" "){
				CmiInteractionsArray[n][5]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }   	
   	        if(DataModel.indexOf("latency")!=-1 && tempArray.length==4){
			if(checkCMITimespan(Value)=="true"){
				CmiInteractionsArray[n][6]=Value;
				lastError = 0;
   	                }
   	                else{
   	                        lastError = 405;
   	                }
   	        }     	      	         	                	        	        
   }
   else{
	switch (DataModel){
		case "cmi.core.lesson_location":
			if(Value.length <= 255){
                      		cmi_core_lesson_location = Value;
                      		
                      		lastError = 0;
                      	}else{
 				lastError = 405;
                      	}
                     	break;   
		case "cmi.core.lesson_status":
			var LessonStatusParameterFound = "false"; 
			for(i=0;i<LessonStatusArray.length;i++) {
				if(Value == LessonStatusArray[i]) {
					LessonStatusParameterFound = "true";
				}                      
			}
			
			if(LessonStatusParameterFound == "true" && Value!="not attempted") {
				cmi_core_lesson_status = Value;
				lastError = 0;
				if(cmi_core_lesson_status=="completed"){
					if(cmi_student_data_mastery_score!=""){
						if(cmi_core_score_raw >= cmi_student_data_mastery_score){
							cmi_core_lesson_status = "passed";
							lastError = 0;
						}
						else{
							cmi_core_lesson_status = "failed";
							lastError = 0;
						}     
					}   
				}
				if(cmi_core_lesson_status=="passed"){
					cmi_core_lesson_mode="review";
						lastError = 0;
				}
				return "true";
			}else{
				lastError=405;
			}
			
			break;   
		case "cmi.core.exit":
                      
                      var CoreExitParameterFound = "false";
                      for(i=0;i<CoreExitArray.length;i++){
				if(Value == CoreExitArray[i]){
					CoreExitParameterFound = "true";
				}                      
                      }
                      if(CoreExitParameterFound=="true"){
	                      cmi_core_exit = Value;
	                      lastError = 0;
	              }else{
	              	      lastError=405;
	              }        
                     break;     

		case "cmi.core.session_time":
		      if(checkCMITimespan(Value)=="true"){
                               cmi_core_session_time = Value;
			       lastError = 0;
		      }
		      else{
		               lastError=405;
		      }	       
                     break;    
		case "cmi.core.score.raw":
                      if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
                      		cmi_core_score_raw = Value;
				lastError = 0;
		      }else{
		      		lastError = 405;
		      }
                     break;     
		case "cmi.suspend_data":
                      if( Value.length <= 4096){ 
                      		cmi_suspend_data = Value;
				lastError = 0;
		      }else{
		      		lastError = 405;
		      }
                     break;                                                                                         
		case "cmi.core.score.max":
		      if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
                      		cmi_core_score_max = Value;
				lastError = 0;
		      }else{
		      		lastError = 405;
		      }
			break;
		case "cmi.core.score.min":
		      if(((new Number(Value)) <= 100 && (new Number(Value)) >= 0) || Value==""){
                      		cmi_core_score_min = Value;
				lastError = 0;
		      }else{
		      		lastError = 405;
		      }
			break;	
		case "cmi.core._children":
		      lastError = 402;
		      break;
		case "cmi.core.student_id":
		      lastError = 403;
		      break;
		case "cmi.core.student_name":
		      lastError = 403;
		      break;
		case "cmi.core.credit":
		      lastError = 403;
		      break;
		case "cmi.core.entry":
		      lastError = 403;
		      break;
		case "cmi.core.score._children":
		      lastError = 402;
		      break;
		case "cmi.core.total_time":
		      lastError = 403;
		      break;
		case "cmi.core.lesson_mode":
		      lastError = 403;
		      break;
		case "cmi.interactions._children":
		      lastError = 402;
		      break;		      
		case "cmi.interactions._count":
		      lastError = 402;
		      break;
		case "cmi.objectives._children":
		      lastError = 402;
		      break;		      
		case "cmi.objectives._count":
		      lastError = 402;
		      break;	
		case "cmi.student_data._children":
		      lastError = 402;
		      break;
		case "cmi.student_data.mastery_score":
		      lastError = 403;
		      break;
		case "cmi.student_data.max_time_allowed":
		      lastError = 403;
		      break;
		case "cmi.student_data.time_limit_action":
		      lastError = 403;
		      break;

		case "cmi.student_preference._children":
		      lastError = 402;
		      break;
		case "cmi.student_preference.audio":
		      if(((Value<=100) && (Value>=-1)) && Value != " " && Value != ""){
                      		cmi_student_preference_audio = Value;
				lastError = 0;
		      }else{
                    		/* cmi_student_preference_audio = Value; */
		      		lastError = 405;
		      }
		      break;
		case "cmi.student_preference.language":
	              	if(Value.length <= 255 && Value != " "){
	              		cmi_student_preference_language = Value;
	              		lastError = 0;
	              	}else{
	              		/* cmi_student_preference_language = Value; */
				lastError = 405;
	              	}
	             	break; 				
		case "cmi.student_preference.speed":
		      if(((Value<=100) && (Value>=-100)) && Value != " " && Value != ""){
                      		cmi_student_preference_speed = Value;
				lastError = 0;
		      }else{
                      		/* cmi_student_preference_speed = Value; */
		      		lastError = 405;
		      }
		      break;
		case "cmi.student_preference.text":
		      if(((Value<=1) && (Value>=-1)) && Value != " " && Value != ""){
                      		cmi_student_preference_text = Value;
				lastError = 0;
		      }else{
                      		/* cmi_student_preference_text = Value; */
		      		lastError = 405;
		      }
		      break;
		case "cmi.comments":
                      if( Value.length <= 4096){ 
                      		cmi_comments = cmi_comments + Value;
				lastError = 0;
		      }else{
		      		lastError = 405;
		      }
                     break;                                                                                         
		case "cmi.comments_from_lms":
		      lastError = 403;
		      break;
		      
		case "cmi.launch_data":
		      lastError = 403;
		      break;
		default :
		      CheckDataModel(DataModel);
		      if(lastError==201){
		      		return "false";
		      }else{
		      		return "";
		      }
		      break;
        }
   }     
	if(lastError != 0){ 
		return "false";
	}else{
		return "true";
	}
	
}else{
	lastError = 301;
	return "false";
}

}

function LMSCommit(emptystring){
	
	if(IsLMSInitialize=="true"){
		/* if(IsFinished=="true"){ */
		/* 	lastError = 143;  // Cimmit after termination */
		/* 	return "false"; */
		/* } */
		/* else{ */
			if((LMSCommit.arguments.length==1)&&(LMSCommit.arguments[0] == "")){	
	        
	        	/* get cmi.core.session_time and compute cmi.core.total_time */
			    cmi_core_total_time=computeTotalTime();	
				
				var xmldoc = XmlDocument.create();;
				xmldoc.async = false;
				xmldoc.loadXML("<"+"?xml version='1.0'?><root/>");
				var rootElement = xmldoc.documentElement;
			
				/* course_ID,user_ID,sco_ID */
				rootElement.setAttribute("course_ID",course_ID);
				rootElement.setAttribute("user_ID",cmi_core_student_id);
				rootElement.setAttribute("sco_ID",SCO_ID);
				
				/* modified by SCORM 1.3 */
				rootElement.setAttribute("Scorm_Type","sco");
				rootElement.setAttribute("Message_Type","LMSCommit");
			
				var cmi_core_lesson_location_Element = xmldoc.createElement("cmi_core_lesson_location");
				    cmi_core_lesson_location_Element.text = cmi_core_lesson_location;
				rootElement.appendChild(cmi_core_lesson_location_Element);
			
				var cmi_core_credit_Element = xmldoc.createElement("cmi_core_credit");
				    cmi_core_credit_Element.text = cmi_core_credit;
				rootElement.appendChild(cmi_core_credit_Element);
				
				var cmi_core_lesson_status_Element = xmldoc.createElement("cmi_core_lesson_status");
				    cmi_core_lesson_status_Element.text = cmi_core_lesson_status;
				rootElement.appendChild(cmi_core_lesson_status_Element);
			
				var cmi_core_total_time_Element = xmldoc.createElement("duration");
				    cmi_core_total_time_Element.text = cmi_core_total_time;
				rootElement.appendChild(cmi_core_total_time_Element);
			
				var cmi_core_lesson_mode_Element = xmldoc.createElement("cmi_core_lesson_mode");
				    cmi_core_lesson_mode_Element.text = cmi_core_lesson_mode;
				rootElement.appendChild(cmi_core_lesson_mode_Element);
			
				var cmi_core_exit_Element = xmldoc.createElement("cmi_core_exit_value");
				    cmi_core_exit_Element.text = cmi_core_exit;
				rootElement.appendChild(cmi_core_exit_Element);
			
				var cmi_core_session_time_Element = xmldoc.createElement("cmi_core_session_time");
				    cmi_core_session_time_Element.text = cmi_core_session_time;
				rootElement.appendChild(cmi_core_session_time_Element);
			
				var cmi_suspend_data_Element = xmldoc.createElement("cmi_suspend_data");
				    cmi_suspend_data_Element.text = cmi_suspend_data;
				rootElement.appendChild(cmi_suspend_data_Element);
			
				/* Heroin 2004.05.11 */
				/* cmi_launch_data = adlcpList[tocIndex].dataFromLMS; */
				
				var cmi_launch_data_Element = xmldoc.createElement("cmi_launch_data");
				    cmi_launch_data_Element.text = cmi_launch_data;
				rootElement.appendChild(cmi_launch_data_Element);
			
				var cmi_core_entry_Element = xmldoc.createElement("cmi_core_entry");
				    cmi_core_entry_Element.text = cmi_core_entry;
				rootElement.appendChild(cmi_core_entry_Element);
				
				var cmi_core_score_raw_Element = xmldoc.createElement("cmi_core_score_raw");
				    cmi_core_score_raw_Element.text = cmi_core_score_raw;
				rootElement.appendChild(cmi_core_score_raw_Element);
				
				/* ------SCORM 1.3 ------------------------------------------------------------- */
			
				var cmi_core_score_normalized_Element = xmldoc.createElement("cmi_core_score_normalized");
				    cmi_core_score_normalized_Element.text = "";
				rootElement.appendChild(cmi_core_score_normalized_Element);
				
				
				/* ------SCORM 1.3 -------------------------------------------------------------- */
			
				var cmi_core_score_max_Element = xmldoc.createElement("cmi_core_score_max");
				    cmi_core_score_max_Element.text = cmi_core_score_max;
				rootElement.appendChild(cmi_core_score_max_Element);
			
				var cmi_core_score_min_Element = xmldoc.createElement("cmi_core_score_min");
				    cmi_core_score_min_Element.text = cmi_core_score_min;
				rootElement.appendChild(cmi_core_score_min_Element);			
			
				/* ---SCORM 1.3-------------Add 2003/10/07 vega */
				var cmi_core_success_status_Element = xmldoc.createElement("cmi_core_success_status");
				cmi_core_success_status_Element.text = "";
				rootElement.appendChild(cmi_core_success_status_Element );	
				
				var cmi_core_completion_status_Element  = xmldoc.createElement("cmi_core_completion_status");
				cmi_core_completion_status_Element.text = "";
				rootElement.appendChild(cmi_core_completion_status_Element);	
				/* --------------------------------------------------------------- */
				
				/* Heroin 2004.02.10 */
				/* Heroin 2004.05.11 */
				var cmi_core_completion_threshold_Element  = xmldoc.createElement("cmi_core_completion_threshold");
				cmi_core_completion_threshold_Element.text = "";
				rootElement.appendChild(cmi_core_completion_threshold_Element);	
				
				
				
				var cmi_core_progress_measure_Element  = xmldoc.createElement("cmi_core_progress_measure");
				cmi_core_progress_measure_Element.text = "";
				rootElement.appendChild(cmi_core_progress_measure_Element);	
					
				/* alert(cmi_completion_threshold+ "  "+cmi_progress_measure); */
				
				
				/* Heroin-2003.11.25 */
				var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");
				    cmi_core_attempt_count_Element.text = "";
				rootElement.appendChild(cmi_core_attempt_count_Element);
				
				/* Heroin-2003.12.05 isDisabled and isHiddenFromChoice */
				var cmi_core_isDisabled_Element = xmldoc.createElement("cmi_core_isDisabled");
				    cmi_core_isDisabled_Element.text = "";
				rootElement.appendChild(cmi_core_isDisabled_Element);
				
				var cmi_core_isHiddenFromChoice_Element = xmldoc.createElement("cmi_core_isHiddenFromChoice");
				    cmi_core_isHiddenFromChoice_Element.text = "";
				rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
				
				
				/* Heroin-2003.12.12 limitcondition duration */
				var cmi_core_attempt_absolut_duration_Element = xmldoc.createElement("cmi_core_attempt_absolut_duration");
				    cmi_core_attempt_absolut_duration_Element.text = "";
				rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
			
				var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");
				    cmi_core_attempt_experienced_duration_Element.text = "";
				rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
			
				var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");
				    cmi_core_activity_absolut_duration_Element.text = "";
				rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
			
				var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");
				    cmi_core_activity_experienced_duration_Element.text = "";
				rootElement.appendChild(cmi_core_activity_experienced_duration_Element);
			
			
				
				var cmi_student_preference_audio_Element = xmldoc.createElement("cmi_student_preference_audio");
				    cmi_student_preference_audio_Element.text = cmi_student_preference_audio;
				rootElement.appendChild(cmi_student_preference_audio_Element);			
			
				var cmi_student_preference_language_Element = xmldoc.createElement("cmi_student_preference_language");
				    cmi_student_preference_language_Element.text = cmi_student_preference_language;
				rootElement.appendChild(cmi_student_preference_language_Element);			
			
				var cmi_student_preference_speed_Element = xmldoc.createElement("cmi_student_preference_speed");
				    cmi_student_preference_speed_Element.text = cmi_student_preference_speed;
				rootElement.appendChild(cmi_student_preference_speed_Element);			
			
				var cmi_student_preference_text_Element = xmldoc.createElement("cmi_student_preference_text");
				    cmi_student_preference_text_Element.text = cmi_student_preference_text;
				rootElement.appendChild(cmi_student_preference_text_Element);			
				
				if((new Number(o)+1)>0){
					/* CmiObjectivesArray
					   o-->CmiObjectiveArray:count
					   CmiObjectivesArray[o_count][0]<==>id
					   CmiObjectivesArray[o_count][1]<==>score.raw
					   CmiObjectivesArray[o_count][2]<==>score.max
					   CmiObjectivesArray[o_count][3]<==>score.min
					   CmiObjectivesArray[o_count][4]<==>score.normalized
					   CmiObjectivesArray[o_count][5]<==>success_status
					   CmiObjectivesArray[o_count][6]<==>completion_status */
					
					var cmi_objectives_Element_Array = new Array(o);
					
					
					var u=0;
					for(u=0; u<new Number(o)+1 ; u++){
						/* ---------------------------------------------------- */
						cmi_objectives_Element_Array[u] = xmldoc.createElement("cmi_objectives");
						/* ---------------------------------------------------- */
						cmi_objectives_Element_Array[u].setAttribute("n",u);
						cmi_objectives_Element_Array[u].setAttribute("id",CmiObjectivesArray[u][0]);
						cmi_objectives_Element_Array[u].setAttribute("score_raw",CmiObjectivesArray[u][1]);
						cmi_objectives_Element_Array[u].setAttribute("score_max",CmiObjectivesArray[u][2]);
						cmi_objectives_Element_Array[u].setAttribute("score_min",CmiObjectivesArray[u][3]);
						cmi_objectives_Element_Array[u].setAttribute("score_scaled","");
						if(CmiObjectivesArray[u][4]=="passed" || CmiObjectivesArray[u][4]=="failed"){
							cmi_objectives_Element_Array[u].setAttribute("success_status",CmiObjectivesArray[u][4]);
						}else{
							cmi_objectives_Element_Array[u].setAttribute("completion_status",CmiObjectivesArray[u][4]);
						}
						rootElement.appendChild(cmi_objectives_Element_Array[u]);
					}
				}


				/*
				//2004.01.16-Vega modify 
				var cmi_interactions__children = "id,objectives,timestamp,type,weighting,learner_response,result,latency, response";
				var n=-1; //cmi.interactions._count
				var m=-1; //cmi.interactions.n.correct_responses._count  
				var c=-1; //cmi.interactions.n.objectives._count
				var CmiInteractionsArray = new Array();
				var CmiInteractionsObjectivesArray = new Array();
				var CmiInteractionsCorrect_ResponsesArray = new Array();
			
				  CmiInteractionsArray[n][0]<==>id
				  CmiInteractionsArray[n][1]<==>timestamp
				  CmiInteractionsArray[n][2]<==>type
				  CmiInteractionsArray[n][3]<==>weighting
				  CmiInteractionsArray[n][4]<==>learner_response
				  CmiInteractionsArray[n][5]<==>result
				  CmiInteractionsArray[n][6]<==>latency
				  CmiInteractionsArray[n][7]<==>description
				  CmiInteractionsArray[n][8]<==>cmi.interactions.n.objectives._count (c)
				  CmiInteractionsArray[n][9]<==>cmi.interactions.n.correct_responses._count (m) 
				

				  CmiInteractionsArray[n][0]<==>id
				  CmiInteractionsArray[n][1]<==>time
				  CmiInteractionsArray[n][2]<==>type
				  CmiInteractionsArray[n][3]<==>weighting
				  CmiInteractionsArray[n][4]<==>student_response
				  CmiInteractionsArray[n][5]<==>result
				  CmiInteractionsArray[n][6]<==>latency
				  CmiInteractionsArray[n][7]<==>cmi.interactions.n.objectives._count
				  CmiInteractionsArray[n][8]<==>cmi.interactions.n.correct_responses._count  
				*/
				/* interactions -- 2004/2/9 Vega */
				if(Number(n)+1>0){
					var cmi_interactions_Element_Array = new Array(n);
					var v=0;
					
					for(v=0; v < Number(n)+1 ; v++){
						
						cmi_interactions_Element_Array[v] = xmldoc.createElement("cmi_interactions");
						cmi_interactions_Element_Array[v].setAttribute("n",v);
						cmi_interactions_Element_Array[v].setAttribute("id",CmiInteractionsArray[v][0]);
						cmi_interactions_Element_Array[v].setAttribute("timestamp",CmiInteractionsArray[v][1]);
						cmi_interactions_Element_Array[v].setAttribute("type",CmiInteractionsArray[v][2]);
						cmi_interactions_Element_Array[v].setAttribute("weighting",CmiInteractionsArray[v][3]);
						cmi_interactions_Element_Array[v].setAttribute("learner_response",CmiInteractionsArray[v][4]);
						cmi_interactions_Element_Array[v].setAttribute("result",CmiInteractionsArray[v][5]);
						cmi_interactions_Element_Array[v].setAttribute("latency",CmiInteractionsArray[v][6]);
						cmi_interactions_Element_Array[v].setAttribute("description","");
						cmi_interactions_Element_Array[v].setAttribute("objectives._count",CmiInteractionsArray[v][7]);
						cmi_interactions_Element_Array[v].setAttribute("correct_responses._count",CmiInteractionsArray[v][8]);
												
						rootElement.appendChild(cmi_interactions_Element_Array[v]);
						
						
						
						/* interactions.n.objectives.id */
						/* Heroin 2004.05.11 */
						if(Number(CmiInteractionsArray[v][7])+1 > 0){
							var oIndex=0;
							var c = Number(CmiInteractionsArray[v][7]); /* Henry 2004.06.03 */
							var cmi_interactions_objects_Element_Array = new Array(c)

							for(oIndex=0; oIndex < Number(CmiInteractionsArray[v][7])+1 ; oIndex++){
								cmi_interactions_objects_Element_Array[oIndex] = xmldoc.createElement("cmi_interactions_objectives");	
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("n",v);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("id",CmiInteractionsArray[v][0]);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("c",oIndex);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("objectives_id",CmiInteractionsObjectivesArray[v][oIndex]);
									
								/* alert("objectives_id="+CmiInteractionsObjectivesArray[v][oIndex]); */
								rootElement.appendChild(cmi_interactions_objects_Element_Array[oIndex]);
							}
						}
						
						
						//interactions.n.correct_responses.pattern
						
						
						if(Number(CmiInteractionsArray[v][8])+1 > 0){
							var pIndex=0;
							var m= Number(CmiInteractionsArray[v][8]); //Henry 2004.06.03
							var cmi_interactions_correctPattern_Element_Array = new Array(m)
							for(pIndex=0; pIndex < Number(CmiInteractionsArray[v][8])+1 ; pIndex++){
								cmi_interactions_correctPattern_Element_Array[pIndex]= xmldoc.createElement("cmi_interactions_correct_responses");	
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("n",v);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("id",CmiInteractionsArray[v][0]);	
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("m",pIndex);				
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("pattern",CmiInteractionsCorrect_ResponsesArray[v][pIndex]);
								rootElement.appendChild(cmi_interactions_correctPattern_Element_Array[pIndex]);
							}							
						}
						

					}
				}
			
				//if(Number(CommentsFromLearnerCount)+1>0){
				/*	
				if(Number(CommentsFromLearnerCount+1)>0){
					
					var temp_cmi_comments_from_learner_Element;
					var t=0;
					for(t=0; t < Number(CommentsFromLearnerCount)+1 ; t++){
						temp_cmi_comments_from_learner_Element = xmldoc.createElement("cmi_comments_from_learner");
						temp_cmi_comments_from_learner_Element.setAttribute("n",t);
						temp_cmi_comments_from_learner_Element.setAttribute("comment",CmiCommentsFromLearnerArray[t][0]);
						temp_cmi_comments_from_learner_Element.setAttribute("location",CmiCommentsFromLearnerArray[t][1]);
						temp_cmi_comments_from_learner_Element.setAttribute("date_time",CmiCommentsFromLearnerArray[t][2]);
						
						rootElement.appendChild(temp_cmi_comments_from_learner_Element);
						
					}
				
				}	
				*/
				//---XMLHTTP---
				 
					
				var ServerSide = xmlhttp_set;
				var XMLHTTPObj = XmlHttp.create();
				XMLHTTPObj.Open("POST",ServerSide,false);
				XMLHTTPObj.setRequestHeader("Content-Type","text/xml");
				XMLHTTPObj.Send(xmldoc.xml);
				//vega 
				xmldoc.loadXML(XMLHTTPObj.responseText);
				
				//---XMLHTTP---
				lastError = 0;
				IsCommit = "true";
				return "true";
			    
			}else{
			    	lastError = 201;
			    	return "false";
			}
		

    		

	}else{
		lastError = 301;  //commit before initialization
		return "false";
	}


}



function GetManifestData(DataModel){
	if (tocList[GlobalStateObj.CurrentActivity].id != '') {
		var Current_Item = xmldoc.selectSingleNode('//item[@identifier="'+tocList[GlobalStateObj.CurrentActivity].id+'"]');
		if (Current_Item != null) {
			switch (DataModel) {
				case 'SCO_ID' :
					return tocList[GlobalStateObj.CurrentActivity].id; 
					break;
				case 'cmi.launch_data' :
					if(Current_Item.selectSingleNode("adlcp:datafromlms") != null)
						return Current_Item.selectSingleNode("adlcp:datafromlms").text;
					else
						return "";
					
					break;
				case 'cmi.student_data.mastery_score' :
					if(Current_Item.selectSingleNode("adlcp:masteryscore") != null)
						return Current_Item.selectSingleNode("adlcp:masteryscore").text;
					else
						return "";
					
					break;
				case 'cmi.student_data.max_time_allowed': 
					if(Current_Item.selectSingleNode("adlcp:maxtimeallowed") != null)
						return Current_Item.selectSingleNode("adlcp:maxtimeallowed").text;
					else
						return "";

					break;
				case 'cmi.student_data.time_limit_action':
					if(Current_Item.selectSingleNode("adlcp:timelimitaction") != null)
						return Current_Item.selectSingleNode("adlcp:timelimitaction").text;
					else
						return "";
					
					break;
			}
		}
	}
	return '';

}

function isFirstEnterSco(DataModel){

        //---XMLHTTP---

	var xmldoc = XmlDocument.create();;
	xmldoc.async = false;
	xmldoc.loadXML("<"+"?xml version='1.0'?><root/>");
	var rootElement = xmldoc.documentElement;
	
	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",cmi_core_student_id);
	rootElement.setAttribute("sco_ID",SCO_ID);
	
	var DataModelElement = xmldoc.createElement("DataModel");
		DataModelElement.text = DataModel;
	rootElement.appendChild(DataModelElement);
	
	var ServerSide = xmlhttp_get;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.Open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml");
	XMLHTTPObj.Send(xmldoc.xml);
	
	xmldoc.loadXML(XMLHTTPObj.responseText);
	var flag;
	flag = xmldoc.selectSingleNode("/root").text;
	return flag;	
	
	//---XMLHTTP---
	
}
 
function setSCOInitialData(DataModel){

        //---XMLHTTP---

	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	xmldoc.loadXML("<"+"?xml version='1.0'?><root/>");
	var rootElement = xmldoc.documentElement;
	
	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",cmi_core_student_id);
	rootElement.setAttribute("sco_ID",SCO_ID);
	
	var DataModelElement = xmldoc.createElement("DataModel");
		DataModelElement.text = DataModel;
	rootElement.appendChild(DataModelElement);
	
	var ServerSide = xmlhttp_get;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.Open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml");
	XMLHTTPObj.Send(xmldoc.xml);
	
	xmldoc.loadXML(XMLHTTPObj.responseText);
        
        //由抓回來的XML Stream設定初始值 
	//--------------cmi_core---------------------------------------------------------
	cmi_core_lesson_location = xmldoc.selectSingleNode("/root/lesson_location").text;
	cmi_core_credit = xmldoc.selectSingleNode("/root/credit").text;	
	cmi_core_lesson_status = xmldoc.selectSingleNode("/root/lesson_status").text;
	cmi_core_total_time = xmldoc.selectSingleNode("/root/duration").text;
	cmi_core_lesson_mode = xmldoc.selectSingleNode("/root/lesson_mode").text;
	cmi_core_exit = xmldoc.selectSingleNode("/root/exit_value").text;
	cmi_core_session_time = xmldoc.selectSingleNode("/root/session_time").text;
	cmi_suspend_data = xmldoc.selectSingleNode("/root/suspend_data").text;
	cmi_launch_data = xmldoc.selectSingleNode("/root/launch_data").text;
	
	cmi_core_entry = xmldoc.selectSingleNode("/root/entry").text;
	if(cmi_core_exit=="suspend"){
		cmi_core_entry = "resume";
	}else if (cmi_core_exit != "resume"){
		cmi_core_entry = "";
	}
	
	cmi_core_score_raw = xmldoc.selectSingleNode("/root/score_raw").text;
	cmi_core_score_max = xmldoc.selectSingleNode("/root/score_max").text;
	cmi_core_score_min = xmldoc.selectSingleNode("/root/score_min").text;

	//---------------cmi_student_preference----------------------------------	
	if(xmldoc.selectNodes("//audio").length>0){
		cmi_student_preference_audio= xmldoc.selectSingleNode("/root/audio").text;	
	}	

	if(xmldoc.selectNodes("//language").length>0){
		cmi_student_preference_language= xmldoc.selectSingleNode("/root/language").text;
	}
	
	if(xmldoc.selectNodes("//speed").length>0){
		cmi_student_preference_speed= xmldoc.selectSingleNode("/root/speed").text;
	}
	
	if(xmldoc.selectNodes("//text").length>0){
		cmi_student_preference_text= xmldoc.selectSingleNode("/root/text").text;
	}	
	
	
	//---------------cmi_objectives----------------------------------	
	var objective_length = xmldoc.selectNodes("//cmi_objectives").length;
	var tempobjectiveElement;	
	
	if(objective_length > 0){
		for(t=0;t<objective_length;t++){
			tempobjectiveElement = xmldoc.selectNodes("//cmi_objectives").item(t)
			CmiObjectivesArray[t]=new Array("","","","","");
			CmiObjectivesArray[t][0]=tempobjectiveElement.attributes.getNamedItem("id").text
			CmiObjectivesArray[t][1] = tempobjectiveElement.attributes.getNamedItem("score_raw").text
			CmiObjectivesArray[t][2] = tempobjectiveElement.attributes.getNamedItem("score_max").text
			CmiObjectivesArray[t][3] = tempobjectiveElement.attributes.getNamedItem("score_min").text
			CmiObjectivesArray[t][4] = tempobjectiveElement.attributes.getNamedItem("status").text
		}
	}
	
	o = (objective_length - 1);
	
	
	//---XMLHTTP---
	
} 

function computeTotalTime(){
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

function convertTotalSeconds(ts)
{
   var sec = (ts % 60);

   ts -= sec;
   var tmp = (ts % 3600);  //# of seconds in the total # of minutes
   ts -= tmp;              //# of seconds in the total # of hours

   // convert seconds to conform to CMITimespan type (e.g. SS.00)
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

function checkCMITimespan(ts){
   var reg1_1=/^[0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]$/;
   var reg1_2=/^[0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9]$/;
   var reg1_3=/^[0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9][0-9]$/;

   var reg2_1=/^[0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]$/;
   var reg2_2=/^[0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9]$/;
   var reg2_3=/^[0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9][0-9]$/;   

   var reg3_1=/^[0-9][0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]$/;
   var reg3_2=/^[0-9][0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9]$/;
   var reg3_3=/^[0-9][0-9][0-9][0-9]\:[0-9][0-9]\:[0-5][0-9]\.[0-9][0-9]$/;  

   if(ts.match(reg1_1)!=null || ts.match(reg1_2)!=null || ts.match(reg1_3)!=null || ts.match(reg2_1)!=null || ts.match(reg2_2)!=null || ts.match(reg2_3)!=null || ts.match(reg3_1)!=null || ts.match(reg3_2)!=null || ts.match(reg3_3)!=null){
        return "true";
   }
   else{
        return "false";
   }
}

function checkCMITime(ts){
   var reg1=/^[0-5][0-9]\:[0-5][0-9]$/;
   var reg2=/^[0-5][0-9]\:[0-5][0-9]\.[0-9]$/;
   var reg3=/^[0-5][0-9]\:[0-5][0-9]\.[0-9][0-9]$/;

   if(ts==""){
      return "false";
   }   
   var tempArray=ts.split(":");
   if(tempArray.length != 3){
      return "false";
   }
   else{
      var str1 = ts.substring(0, ts.indexOf(":"));
      var str2 = ts.substring(ts.indexOf(":")+1, ts.length);  
      if(str1.length !=2 || isNaN(str1)){
           return "false";
      }
      else{
	   if(((new Number(str1))>=0 && (new Number(str1))<24) && (str2.match(reg1)!=null || str2.match(reg2)!=null || str2.match(reg3)!=null)){
	        return "true";
	   }
	   else{
	        return "false";
	   }
      }	   
   }
}

function LMSGetLastError(){
	if(lastError != 0){
		return lastError;
	}else{
		return 0;
	}
}

function LMSGetErrorString(errorcode){
        if(errorcode==""){
        	return "";
        }  
	for(i=0 ; i < ErrorStringArray.length ; i++) {
		if(ErrorStringArray[i][0] == errorcode){
			return ErrorStringArray[i][1];
		}
	}

}

function GetSCO_ID(){

	return SCO_ID;
	
}



function SetSCO_ID(tempSCO_ID){

	SCO_ID  = tempSCO_ID;
	
}


function LMSGetDiagnostic(errorcode){
	for(i=0 ; i < ErrorStringArray.length ; i++) {
		if(ErrorStringArray[i][0] == errorcode){
			return ErrorStringArray[i][1];
		}
	}
}

function CheckDataModel(Model){

	//先check是不是"固定"的那幾個
	var DataModelFound = "false";
	for(i=0;i<DataModelArray.length;i++){
		if(DataModelArray[i]==Model){
			DataModelFound = "true";
		}
	}
	
	if(DataModelFound=="true"){
		
		return "true";
	}else{
		//再check是不是interaction或是objectives
		if(Model.indexOf("interactions")!=-1 || Model.indexOf("objectives")!=-1){
	
		}else{
			//check是不是有保留字_version,_count,_children
			if(Model.indexOf("_version")!=-1 || Model.indexOf("_children") != -1){
				lastError = 202;

			}else if(Model.indexOf("_count")!=-1){
				lastError = "203";

			}else{
				lastError = 201;

			}
			
			
		}
		
	}
}

parent.parent.API=new InitialObject();
changeStatus();

</script>