<script type="text/javascript" language="JavaScript" src="/lib/xmlextras.js"></script>
<script language="javascript" src="/learn/scorm/script/API/checkISO.js"></script>
<script language="javascript" for="window" event="onload">
// SCORM 2004 API function
// 2004.03.05 Vega 整理
// 2004.07.29 Vega 整理data model
/*
1. (1.2舊有改名) : 與1.2共用資料庫欄位
cmi.core.completion_status -> cmi.completion_status
cmi.core.credit -> cmi.credit
cmi.core.entry -> cmi.entry
cmi.core.exit -> cmi.exit
cmi.core.student_id -> cmi.learner_id
cmi.core.student_name -> cmi.learner_name
cmi.student_preference seriese -> cmi.learner_preference seriese
	- cmi.student_preference.audio -> cmi.learner_preference.audio_level
	- cmi.student_preference.language -> cmi.learner_preference.language
	- cmi.student_preference.speed -> cmi.learner_preference.delivery_speed
	- cmi.student_preference.text -> cmi.learner_preference.audio_captioning
cmi.core.lesson_location -> cmi.location
cmi.student_data.max_time_allowed -> cmi.max_time_allowed
cmi.core.lesson_mode -> cmi.mode
cmi.core.score seriese -> cmi.score
	- cmi.core.score._children -> cmi.score._children
	- cmi.core.score.max -> cmi.score.max
	- cmi.core.score.min -> cmi.score.min
	- cmi.core.score.raw -> cmi.score.raw
	- cmi.core.score.normalized -> cmi.score.scaled
cmi.core.session_time -> cmi.session_time
cmi.core.success_status -> cmi.success_status
cmi.student_data.time_limit_action -> cmi.time_limit_action
cmi.core.total_time -> cmi.total_time


*/

// Session變數設定
var student_id="<?=$sysSession->username?>";
var student_last_name="<?=$lname?>";
var student_name="<?=$fname?>";
var course_ID="<?=$sysSession->course_id?>";
var xmlhttp_get="/learn/scorm/get.php";
var xmlhttp_set="/learn/scorm/set.php";

// Heroin 2004.02.04
var IsLMSInitialize;
var IsCommit;
var IsFinished;
IsLMSInitialize = "false";
IsCommit = "false";
IsFinished = "false";

// ---------------- LMS Mandatory:Yes -------------------
var cmi_version="1.0";

var cmi_core__children="student_id,student_name,lesson_location,credit,lesson_status,success_status,completion_status,total_time,lesson_mode,exit,session_time,suspend_data,launch_data,entry,score";
// ------SCORM 1.3 modify-----------------------------
var cmi_score__children="raw,min,scaled,max";
// ------SCORM 1.3 modify-----------------------------
var cmi_learner_id=student_id;
var cmi_learner_name=student_last_name + ", " + student_name;
var cmi_location="";
var cmi_credit="<?=$credit?>";
var cmi_core_lesson_status="";

// -------SCORM 1.3 ---------------------------
var cmi_success_status="";
var cmi_completion_status="";
var cmi_core_attempt_count="";

// Heroin-2004.02.10
var cmi_completion_threshold="";
var cmi_progress_measure="";

// Heroin-2003.12.05
var cmi_core_isDisabled = "";
var cmi_core_isHiddenFromChoice = "";

// Heroin-2003.12.12
var cmi_core_attempt_absolut_duration = "";
var cmi_core_attempt_experienced_duration = "";
var cmi_core_activity_absolut_duration = "";
var cmi_core_activity_experienced_duration = "";

// --------Objecitve set by content flag add by Heroin 2003.10.07------------
var objective_set_by_content_flag="false";

// -------SCORM 1.3 ---------------------------

var cmi_total_time="";
var cmi_exit="";
var cmi_session_time="";
var cmi_suspend_data="";
var cmi_launch_data="";
var cmi_entry="";
var cmi_score_raw="";
var cmi_score_scaled="";


//-----------SCORM 1.3 WD ------------Heroin 2004.01.13
var cmi_scaled_passing_score="";

//---------------- LMS Mandatory:No --------------------
var cmi_mode= cmi_credit == 'credit' ? "normal" : 'browse';
var cmi_score_max="";
var cmi_score_min="";

//-----------------cmi.student_data data model-------------

//modified by 1.3 no max_time_allowed;
var cmi_student_data__children="mastery_score,time_limit_action";
var cmi_sd_mastery_score="";
var cmi_max_time_allowed="";
var cmi_time_limit_action="";

//-----------------cmi.learner_preference data model-------------

var cmi_learner_preference__children="audio_level,delivery_speed,audio_captioning,language";
var cmi_learner_preference_audio_level;
var cmi_learner_preference_language;
var cmi_learner_preference_delivery_speed;
var cmi_learner_preference_audio_captioning;

//Henry 2004.05.27
var cmi_learner_preference = ""; //不應該有這個data model (Vega 2004.08.04 不過test suite有檢查這個data model)
//var cmi_learner_preference__children="audio_level,delivery_speed,audio_captioning,language";
//var cmi_learner_preference_audio_level;
//var cmi_learner_preference_language;
//var cmi_learner_preference_delivery_speed;
//var cmi_learner_preference_audio_captioning;


//-----------------cmi_comments data model-------------

var cmi_comments="";
var cmi_comments_from_lms="";

//-----------------nav data model--------------------------
//var nav_event="";
//var nav_control_mode_enabled_choice = false;
//var nav_control_mode_enabled_flow = false;

//-----------------adl nav request data model-------------------

var adl_nav_request = "_none_";
var adl_nav_request_valid_continue = "";
var adl_nav_request_valid_previous = "";
var adl_nav_request_valid_choice = "";

//-----------------error string------------------------
//Vega add 2004/1/29
var lastError = 0;
var ErrorStringArray = new Array(27);
ErrorStringArray[0] = new Array(0,"No error");
ErrorStringArray[1] = new Array(101,"General Exception");
ErrorStringArray[2] = new Array(102,"General Initialization Failure"); //General Initialization Failure
ErrorStringArray[3] = new Array(103,"Already Initialized"); //Already Initialized
ErrorStringArray[4] = new Array(104,"Content Instance Terminated");  //Terminated
ErrorStringArray[5] = new Array(111,"General Termination Failure");
ErrorStringArray[6] = new Array(112,"Termination Before Initialization");
ErrorStringArray[7] = new Array(113,"Termination After Termination");
ErrorStringArray[8] = new Array(122,"Retrieve Data Before Initialization");
ErrorStringArray[9] = new Array(123,"Retrieve Data After Termination");
ErrorStringArray[10] = new Array(132,"Store Data Before Initialization");
ErrorStringArray[11] = new Array(133,"Store Data After Termination");
ErrorStringArray[12] = new Array(142,"Commit Before Initialization");
ErrorStringArray[13] = new Array(143,"Commit After Termination");
ErrorStringArray[14] = new Array(201,"General Argument Error");
ErrorStringArray[15] = new Array(301,"Not Initialized");
ErrorStringArray[16] = new Array(351,"General set failure");
ErrorStringArray[17] = new Array(391,"General Commit Failure");
ErrorStringArray[18] = new Array(401,"Undefined Data Model Element");
ErrorStringArray[19] = new Array(402,"Unimplemented Data Model Element");
ErrorStringArray[20] = new Array(403,"Data Model Element Value Not Initialized");
ErrorStringArray[21] = new Array(404,"Data Model Element Is Read Only");
ErrorStringArray[22] = new Array(405,"Data Model Element Is Write Only");
ErrorStringArray[23] = new Array(406,"Data Model Element Type Mismatch");
ErrorStringArray[24] = new Array(407,"Data model element out of range");
ErrorStringArray[25] = new Array(408,"Data model dependency not established");
ErrorStringArray[26] = new Array(001,"Error Undefined");


//-----------------lesson_status parameter-------------

var LessonStatusArray = new Array(6);
LessonStatusArray[0] = "passed";
LessonStatusArray[1] = "completed";
LessonStatusArray[2] = "failed";
LessonStatusArray[3] = "incomplete";
LessonStatusArray[4] = "browsed";
LessonStatusArray[5] = "not attempted";

//-----------------success_status parameter-------------
var SuccessStatusArray = new Array(3);
SuccessStatusArray[0] = "unknown";
SuccessStatusArray[1] = "passed";
SuccessStatusArray[2] = "failed";


//-----------------completion_status parameter----------
var CompletionStatusArray = new Array(4);
CompletionStatusArray[0] = "unknown";
CompletionStatusArray[1] = "completed";
CompletionStatusArray[2] = "incomplete";
CompletionStatusArray[3] = "not attempted";



//-----------------core.exit parameter-------------
//modifyed by Heroin 2004.05.11
var CoreExitArray = new Array(5);
CoreExitArray[0] = "time-out";
CoreExitArray[1] = "suspend";
CoreExitArray[2] = "logout";
CoreExitArray[3] = "normal";
CoreExitArray[4] = "";

//-----------------cmi.interactions.n.type parameter-------------

//2004.01.16-Vega modify
var TypeArray = new Array(10);
TypeArray[0] = "true-false";
TypeArray[1] = "choice";
TypeArray[2] = "fill-in";
TypeArray[3] = "long-fill-in"; //new -SCORM 1.3 beta3
TypeArray[4] = "matching";
TypeArray[5] = "performance";
TypeArray[6] = "sequencing";
TypeArray[7] = "likert";
TypeArray[8] = "numeric";
TypeArray[9] = "other";//new -SCORM 1.3 beta3

//-----------------cmi.interactions.n.result parameter-------------

//2004.01.16-Vega modify
var ResultArray = new Array(5);
ResultArray[0] = "correct";
ResultArray[1] = "incorrect";
ResultArray[2] = "wrong";
ResultArray[3] = "unanticipated";
ResultArray[4] = "neutral";
//ResultArray[4] = real(10,7);

//-----------------cmi.interactions parameter-------------

//2004.01.16-Vega modify
//var cmi_interactions__children = "id,objectives,timestamp,type,weighting,learner_response,result,latency, description";
var cmi_interactions__children = "id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description";  //Heroin 2004.05.11
var n=-1; //cmi.interactions._count
var m=-1; //cmi.interactions.n.correct_responses._count
var c=-1; //cmi.interactions.n.objectives._count
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
  CmiInteractionsArray[n][1]<==>timestamp
  CmiInteractionsArray[n][2]<==>type
  CmiInteractionsArray[n][3]<==>weighting
  CmiInteractionsArray[n][4]<==>learner_response
  CmiInteractionsArray[n][5]<==>result
  CmiInteractionsArray[n][6]<==>latency
  CmiInteractionsArray[n][7]<==>description
  CmiInteractionsArray[n][8]<==>cmi.interactions.n.objectives._count (c)
  CmiInteractionsArray[n][9]<==>cmi.interactions.n.correct_responses._count (m)
*/

//-----------------cmi.objectives parameter-------------

//Vega 2004.9.24 modified --1.3.1 TS
var cmi_objectives__children = "completion_status,description,score,progress_measure,id,success_status";
//status->success_status,completion_status
//------SCORM 1.3 modify-----------------------------
var cmi_objectives_n_score__children = "raw,max,min,scaled";
//normalized->scaled
//------SCORM 1.3 modify-----------------------------
var o_index=-1;
var o_count = -1; //cmi.objectives._count
var CmiObjectivesArray = new Array();
var CmiObjectiveStorageSwitch = "true";

/*宣告一個動態陣列,一個新的objective就initial一個元素,
  此元素再對應一個陣列並initial初值,例如:
  當LMSSetValue("cmi.objectives.n.id","value")執行時,先檢查
  CmiObjectivesArray[n]是否initial如果沒有就initial如下:
  CmiObjectivesArray[n]=new Array("","","","","","unknown","unknown","null","0") //SCORM 1.3 modify
  如果有就直接更改對應元素值,對應欄位如下:
  CmiObjectivesArray[o][0]<==>id
  CmiObjectivesArray[o][1]<==>score.raw
  CmiObjectivesArray[o][2]<==>score.max
  CmiObjectivesArray[o][3]<==>score.min
  CmiObjectivesArray[o][4]<==>score.normalized
  CmiObjectivesArray[o][5]<==>success_status
  CmiObjectivesArray[o][6]<==>completion_status
  CmiObjectivesArray[o][7]<==>description
  //Vega 2004.9.30 TS 1.3.1 added
  CmiObjectivesArray[o][8]<==>progress_measure
*/

//------------------nav.event vocabulary-------------------------
var NavEventArray = new Array(3);
NavEventArray[0] = "previous";
NavEventArray[1] = "continue";
NavEventArray[2] = "exit";


//------------------adl.nav.request vocabulary---------------------

var ADLNavRequestArray = new Array(8);
ADLNavRequestArray[0] = "continue";
ADLNavRequestArray[1] = "previous";
ADLNavRequestArray[2] = "choice";
ADLNavRequestArray[3] = "exit";
ADLNavRequestArray[4] = "exitAll";
ADLNavRequestArray[5] = "abandon";
ADLNavRequestArray[6] = "abandonAll";
ADLNavRequestArray[7] = "_none_";


//---modified by Henry 2004-01-29

//-----------------cmi.comments_from_learner---------------
//Modified by Vega 2004.8.23
//var cmi_comments_from_learner_children = "timestamp,comment,location";
var cmi_comments_from_learner_children = "location,comment,timestamp";
var CmiCommentsFromLearnerArray = new Array();
var CommentsFromLearnerCount=-1; //cmi.objectives._count
var CommentsFromLearnerIndex = -1;

/*宣告一個動態陣列,一個新的objective就initial一個元素,
  此元素再對應一個陣列並initial初值,例如:
  當LMSSetValue("cmi.comment_from_learner.n.id","value")執行時,先檢查
  CmiCommentFromLearnerArray[n]是否initial如果沒有就initial如下:
  CmiCommentFromLearnerArray[n]=new Array("","","") //SCORM 1.3 modify
  如果有就直接更改對應元素值,對應欄位如下:
  CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][0]<==>comment --> cmi.comments_from_learner.n.comment
  CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][1]<==>location --> cmi.comments_from_learner.n.location
  CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][2]<==>date_time --> cmi.comments_from_learner.n.date_time
*/

//------------------cmi.comments_from_lms---------------------

//Vega 2004.9.23 modified
//var cmi_comments_from_lms_children = "comment,location,date_time";
var cmi_comments_from_lms_children = "timestamp,comment,location";
var CmiCommentsFromLmsArray = new Array();
var CommentsFromLmsIndex=-1; //cmi.objectives._count
/*宣告一個動態陣列,一個新的objective就initial一個元素,
  此元素再對應一個陣列並initial初值,例如:
  當LMSSetValue("cmi.comment_from_learner.n.id","value")執行時,先檢查
  CmiCommentFromLmsArray[n]是否initial如果沒有就initial如下:
  CmiCommentFromLmsArray[n]=new Array("","","") //SCORM 1.3 modify
  如果有就直接更改對應元素值,對應欄位如下:
  CmiCommentsFromLmsArray[CommentsFromLmsIndex][0]<==>comment --> cmi.comments_from_lms.n.comment
  CmiCommentsFromLmsArray[CommentsFromLmsIndex][1]<==>location --> cmi.comments_from_lms.n.location
  CmiCommentsFromLmsArray[CommentsFromLmsIndex][2]<==>date_time --> cmi.comments_from_lms.n.date_time
*/



//------------------DataModelCheckArray----------------
var DataModelArray = new Array(30);
DataModelArray[0] = "cmi.core._children";
DataModelArray[1] = "cmi.core._count";
DataModelArray[2] = "cmi.learner_id";
DataModelArray[3] = "cmi.learner_name";
DataModelArray[4] = "cmi.location";
DataModelArray[5] = "cmi.credit";
DataModelArray[6] = "cmi.core.lesson_status";
DataModelArray[7] = "cmi.entry";
DataModelArray[8] = "cmi.total_time";
DataModelArray[9] = "cmi.mode";
DataModelArray[10] = "cmi.exit";
DataModelArray[11] = "cmi.session_time";
DataModelArray[12] = "cmi.score.raw";
DataModelArray[13] = "cmi.score.min";
DataModelArray[14] = "cmi.score.max";
DataModelArray[15] = "cmi.suspend_data";
DataModelArray[16] = "cmi.launch_data";
DataModelArray[17] = "cmi.comments";
DataModelArray[18] = "cmi.comments_from_lms";
DataModelArray[19] = "cmi.student_data._children";
DataModelArray[20] = "cmi.student_data._count";
DataModelArray[21] = "cmi.student_data.mastery_score";
DataModelArray[22] = "cmi.max_time_allowed";
DataModelArray[23] = "cmi.time_limit_action";
DataModelArray[24] = "cmi.learner_preference._children";
DataModelArray[25] = "cmi.learner_preference._count";
DataModelArray[26] = "cmi.learner_preference.audio_level";
DataModelArray[27] = "cmi.learner_preference.language";
DataModelArray[28] = "cmi.learner_preference.delivery_speed";
DataModelArray[29] = "cmi.learner_preference.audio_captioning";


//Heroin 2004.05.07 -- DataModel 2004
/*DataModelArray[] = "cmi._version";
DataModelArray[] = "cmi.comments_from_learner._children";
DataModelArray[] = "cmi.comments_from_learner._count";
DataModelArray[] = "cmi.comments_from_learner.n.comment";
DataModelArray[] = "cmi.comments_from_learner.n.location";
DataModelArray[] = "cmi.comments_from_learner.n.date_time";
DataModelArray[] = "cmi.comments_from_lms._children";
DataModelArray[] = "cmi.comments_from_lms._count";
DataModelArray[] = "cmi.comments_from_lms.n.comment";
DataModelArray[] = "cmi.comments_from_lms.n.location";
DataModelArray[] = "cmi.comments_from_lms.n.date_time";
DataModelArray[] = "cmi.completion_status";
DataModelArray[] = "cmi.completion_threshold";
DataModelArray[] = "cmi.credit";
DataModelArray[] = "";
DataModelArray[] = "";
DataModelArray[] = "";
DataModelArray[] = "";
DataModelArray[] = "";


*/


//---------------- User Define --------------------

var course_ID=course_ID;
var SCO_ID="";
var tocIndex;
//-------------------------------------------------
function InitialObject(){
     	this.LMSInitialize=LMSInitialize
     	this.LMSGetValue = LMSGetValue
     	this.LMSSetValue = LMSSetValue
	 this.LMSFinish = LMSFinish
	 this.LMSCommit = LMSCommit
	// this.GetManifestData = GetManifestData
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
	 this.objective_set_by_content_flag = objective_set_by_content_flag  //add by Heroin 2003.10.07
	 this.Set_Objective_by_content = Set_Objective_by_content
	 this.Get_Objective_by_content = Get_Objective_by_content

	 //Heroin 2004.01.12 new API function name
	 this.Initialize= LMSInitialize
	 this.GetValue = LMSGetValue
	 this.SetValue = LMSSetValue
	 this.Terminate = LMSFinish
	 this.Commit = LMSCommit
	 this.GetLastError = LMSGetLastError
	 this.GetErrorString = LMSGetErrorString
	 this.GetDiagnostic = LMSGetDiagnostic
	 this.version = "1.0";
}


function checkFinished(){
	// alert("api GlobalStateObj.CurrentActivity="+GlobalStateObj.CurrentActivity+" SCO_ID="+SCO_ID);
	if(SCO_ID != "" && GlobalStateObj.CurrentActivity !=""){
		var currentSCO = tocList[GlobalStateObj.CurrentActivity].id;
		if(currentSCO != SCO_ID){
			IsFinished = "false";
		}
	}

}

function LMSInitialize(){
	checkFinished();
	IsCommit = "false";
	// alert("LMSInitialize  SCO_ID="+tocList[GlobalStateObj.CurrentActivity].id+ "  IsLMSInitialize = " + IsLMSInitialize + " / IsFinished = " + IsFinished );
	//alert("IsLMSInitialize = " + IsLMSInitialize + " / IsFinished = " + IsFinished );

	if(IsLMSInitialize !="true"){
			if((LMSInitialize.arguments.length==1)&&(LMSInitialize.arguments[0] == "" )){
		         //依course_ID,user_ID,sco_ID判斷是否為第一次呼叫該SCO
			cmi_core_lesson_status = "not attempted";

			SCO_ID = tocList[GlobalStateObj.CurrentActivity].id;
			tocIndex = GlobalStateObj.CurrentActivity;



			cmi_score_raw="";
			cmi_total_time="PT0H0M0S";

		        //-----SCORM 1.3 modify---------好像跟cmi.scaled.passing_score重複了..沒有cmi.student_data.mastery_score這個data model了--------------
		        if(primaryObjectiveList[tocIndex].existflag){
		        	cmi_sd_mastery_score = primaryObjectiveList[tocIndex].minNormalizedMeasure;

		        }else{
		        	cmi_sd_mastery_score = 0;
		        }

			if(limitConditionsList[tocIndex].existflag){
				cmi_max_time_allowed = limitConditionsList[tocIndex].attemptAbsoluteDurationLimit;
			}else{
				cmi_max_time_allowed = 0.0;
			}

		        //-----SCORM 1.3 modify-------------------------------------------------------

		        //cmi_time_limit_action=GetManifestData("cmi.time_limit_action");
		        cmi_time_limit_action = adlcpList[tocIndex].timeLimitAction;
		       //Heroin 2004.02.05
		        cmi_location = "null"; // window.center.location.href;

			cmi_exit = "";

			//-----SCORM 1.3 ----------------
			cmi_success_status = "unknown";
			//cmi_completion_status = "not attempted";
			cmi_completion_status = "unknown";
			cmi_score_scaled = "";
			cmi_core_attempt_count="";

			//Heroin 2004.02.10
			cmi_completion_threshold="";

			//Henry 2004.05.26
			cmi_progress_measure="null";

			//Heroin-2003.12.05
			cmi_core_isDisabled = "";
			cmi_core_isHiddenFromChoice = "";

			//Heroin-2003.12.12
			cmi_core_attempt_absolut_duration = "";
			cmi_core_attempt_experienced_duration = "";
			cmi_core_activity_absolut_duration = "";
			cmi_core_activity_experienced_duration = "";

			//---Modified by Henry 2003.11.20
			//nav_event = "";
			//nav_control_mode_enabled_choice = false;
			//nav_control_mode_enabled_flow = false;

			adl_nav_request = "_none_";
			var adl_nav_request_valid_continue = "false";
			var adl_nav_request_valid_previous = "false";
			var adl_nav_request_valid_choice = "false";



			//不能連續Query engine因為前一次的Query會被後一次Query中斷,
			//要怎麼讓兩次query分開呢


			//-----SCORM 1.3 ----------------
		        var flag = isFirstEnterSco("is.first.enter.sco");
		       /*
		         if(flag==""){ //Yes:設定所有變數初始值
				//-------SCORM 1.3 -------
				cmi_entry="ab_initio";
				//----------------------

		      		cmi_suspend_data="null";
		      		cmi_launch_data=GetManifestData("cmi.launch_data");

		      		//Henry 2004.05.27
		 		cmi_learner_preference_audio = "null";
		                cmi_learner_preference_language = "null";
		                cmi_learner_preference_delivery_speed = "null";
		                cmi_learner_preference_audio_captioning = "null";

		         }
			 else{ //NO:從資料庫中載入所有變數初始值(透過XMLHTTP)
		 	        setSCOInitialData("set.sco.initial.data");
			 }
			 */

			//將target objective裡的資訊載入cmi.objective之data model中
			//先找出所有的objective






			//Henry 204.07.09
			//Henry 2004.10.4
			//由primary objective來的objective
			//如果有primary objective就要先將primary objective轉換成cmi.objective
			//Vega 2004.10.4 added

			//=====Yunghsiao.2004.12.15======================
			//var sharedObjectiveIndex=-1;
			var sharedObjectiveIndex = new Array();
			//===============================================

			//test2004
			//alert("In PrimaryObj / primaryObjectiveList.length="+primaryObjectiveList.length);
			//for(var i=0; i<primaryObjectiveList.length;i++){

				//if this item has primary objective
				if(primaryObjectiveList[tocIndex].existflag){
					if(!primaryObjectiveList[tocIndex].isAutoCreate){
						o_index++;
			 			o_count++;
			 			CmiObjectivesArray[o_index]=new Array("InitialData","","","","","unknown","unknown","null","");
						CmiObjectivesArray[o_index][0] = primaryObjectiveList[tocIndex].objectiveID;
						//alert("In PrimaryObj / o_index="+o_index);
					//以下來自胡大嬸的邏輯
					//如果是這個item的objective
					//if(primaryObjectiveList[i].itemIndex.toString()==tocIndex.toString()){
						//由target objective 來的objective
						//Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52

						//========Yunghsiao.2004.12.15========沒變
						sharedObjectiveIndex=parent.parent.functions.enfunctions.findTargetObjectiveIndex(primaryObjectiveList[tocIndex].objectiveID);
						//====================================

						//sharedObjectiveIndex=parent.parent.functions.enfunctions.findReadTargetObjectiveIndex(tocIndex,"readStatus");
						//alert(sharedObjectiveIndex + " / " + objectiveList[i].objectiveID + " / " + i + " / " + objectiveList.length);

						//======Yunghsiao.2004.12.15==============================
						for(var i=0;i<sharedObjectiveIndex.length;i++){
							if(sharedObjectiveIndex[i]!=-1){
								CmiObjectiveStorageSwitch = "false";
			 					//alert("sharedObjectiveIndex"+sharedObjectiveIndex);
								if(sharedObjectiveList[sharedObjectiveIndex[i]].objectiveProgressStatus.toString()=="true"){
									if(sharedObjectiveList[sharedObjectiveIndex[i]].objectiveSatisfiedStatus.toString()=="true"){
										CmiObjectivesArray[o_index][5] = "passed";
									}else if(sharedObjectiveList[sharedObjectiveIndex[i]].objectiveSatisfiedStatus.toString()=="false"){
										CmiObjectivesArray[o_index][5] = "failed";
									}
								}
								//alert("measure status="+sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus+"  measure="+sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure+" o_index="+o_index);
								if(sharedObjectiveList[sharedObjectiveIndex[i]].objectiveMeasureStatus.toString()=="true"){
									CmiObjectivesArray[o_index][4] = sharedObjectiveList[sharedObjectiveIndex[i]].objectiveNormalizedMeasure;
								}
							}
						}
							/*if(sharedObjectiveIndex!=-1){
								CmiObjectiveStorageSwitch = "false";
			 					//alert("sharedObjectiveIndex"+sharedObjectiveIndex);
								if(sharedObjectiveList[sharedObjectiveIndex].objectiveProgressStatus.toString()=="true"){
									if(sharedObjectiveList[sharedObjectiveIndex].objectiveSatisfiedStatus.toString()=="true"){
										CmiObjectivesArray[o_index][5] = "passed";
									}else if(sharedObjectiveList[sharedObjectiveIndex].objectiveSatisfiedStatus.toString()=="false"){
										CmiObjectivesArray[o_index][5] = "failed";
									}
								}
								//alert("measure status="+sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus+"  measure="+sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure+" o_index="+o_index);
								if(sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus.toString()=="true"){
									CmiObjectivesArray[o_index][4] = sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure;
								}
							}*/
						//=======================================================

					}
				}//------

			//}

			//====Yunghsiao.2004.12.15===========
			//sharedObjectiveIndex=-1;
			//===================================

			//alert("In Obj / objectiveList.length="+objectiveList.length);
			//由objective來的objective


			for(var i=0; i<objectiveList.length;i++){
				//以下來自胡大嬸的邏輯
				//如果是這個item的objective
				if(objectiveList[i].itemIndex.toString()==tocIndex.toString()){
					//由target objective 來的objective
					o_index++;
		 			o_count++;
		 			CmiObjectivesArray[o_index]=new Array("InitialData","","","","","unknown","unknown","null","");
					CmiObjectivesArray[o_index][0] = objectiveList[i].objectiveID;
					//Vega 2004.10.20 add p_mapInfo->p_mapInfoList TS 1.3.1 Course-52
					//sharedObjectiveIndex=parent.parent.functions.enfunctions.findTargetObjectiveIndex(objectiveList[i].objectiveID);

					//=======Yunghsiao.2004.12.15=========沒變
					//alert("Do findTargetObjectiveIndex");
					sharedObjectiveIndex=parent.parent.functions.enfunctions.findTargetObjectiveIndex(objectiveList[i].objectiveID,"readStatus");
					//alert("Exit findTargetObjectiveIndex");
					//====================================
					//alert(sharedObjectiveIndex + " / " + objectiveList[i].objectiveID + " / " + i + " / " + objectiveList.length);

					//======Yunghsiao.2004.12.15==========
					for(var j=0;j<sharedObjectiveIndex.length;j++){
						if(sharedObjectiveIndex[j]!=-1){
							CmiObjectiveStorageSwitch = "false";
							if(sharedObjectiveList[sharedObjectiveIndex[j]].objectiveProgressStatus.toString()=="true"){
								if(sharedObjectiveList[sharedObjectiveIndex[j]].objectiveSatisfiedStatus.toString()=="true"){
									CmiObjectivesArray[o_index][5] = "passed";
								}else if(sharedObjectiveList[sharedObjectiveIndex[j]].objectiveSatisfiedStatus.toString()=="false"){
									CmiObjectivesArray[o_index][5] = "failed";
								}
							}
							//alert("measure status="+sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus+"  measure="+sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure+" o_index="+o_index);
							if(sharedObjectiveList[sharedObjectiveIndex[j]].objectiveMeasureStatus.toString()=="true"){
								CmiObjectivesArray[o_index][4] = sharedObjectiveList[sharedObjectiveIndex[j]].objectiveNormalizedMeasure;
							}
						}
					}
					//=====================================
				}
			}

			//---------------------------------------------

			/*var sharedObjectiveIndex=-1;
			for(var i=0;i<objectiveList.length;i++){

				//如果是這個item的objective
				if(objectiveList[i].itemIndex.toString()==tocIndex.toString()){
					//由target objective 來的objective
					sharedObjectiveIndex=parent.parent.functions.enfunctions.findTargetObjectiveIndex(objectiveList[i].objectiveID);
					//alert(sharedObjectiveIndex + " / " + objectiveList[i].objectiveID + " / " + i + " / " + objectiveList.length);
					if(sharedObjectiveIndex!=-1){
						CmiObjectiveStorageSwitch = "false";
		 				o_index++;
		 				o_count++;
		 				//Vega 2004.10.1
		 				CmiObjectivesArray[o_index]=new Array("InitialData","","","","","unknown","unknown","null","");
						CmiObjectivesArray[o_index][0] = objectiveList[i].objectiveID;
						//Heroin 2004.09.01
						//以下Henry 邏輯先mark起來..
						//alert(sharedObjectiveList[sharedObjectiveIndex].objectiveProgressStatus);
						if(sharedObjectiveList[sharedObjectiveIndex].objectiveProgressStatus.toString()=="true"){
							if(sharedObjectiveList[sharedObjectiveIndex].objectiveSatisfiedStatus.toString()=="true"){
								CmiObjectivesArray[o_index][5] = "passed";
							}else if(sharedObjectiveList[sharedObjectiveIndex].objectiveSatisfiedStatus.toString()=="false"){
								CmiObjectivesArray[o_index][5] = "failed";
							}
						}
						//alert("measure status="+sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus+"  measure="+sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure+" o_index="+o_index);
						if(sharedObjectiveList[sharedObjectiveIndex].objectiveMeasureStatus.toString()=="true"){
							CmiObjectivesArray[o_index][4] = sharedObjectiveList[sharedObjectiveIndex].objectiveNormalizedMeasure;
						}

					}

				}

	 		}*/

				/*
				  CmiObjectivesArray[o][0]<==>id
				  CmiObjectivesArray[o][1]<==>score.raw
				  CmiObjectivesArray[o][2]<==>score.max
				  CmiObjectivesArray[o][3]<==>score.min
				  CmiObjectivesArray[o][4]<==>score.normalized
				  CmiObjectivesArray[o][5]<==>success_status
				  CmiObjectivesArray[o][6]<==>completion_status
				  CmiObjectivesArray[o][7]<==>description
				  CmiObjectivesArray[o][8]<==>progress_measure
	 			*/

			//Henry 2004.06.01
			//-------SCORM 1.3 -------
			//socool 2004.12.21 改ab_initio為ab-initio
			cmi_entry="ab-initio";
			//----------------------

	      		cmi_suspend_data="null";

	      		//Henry 2004.05.27
	 		cmi_learner_preference_audio_level = "null";
	                cmi_learner_preference_language = "null";
	                cmi_learner_preference_delivery_speed = "null";
	                cmi_learner_preference_audio_captioning = "null";


			 cmi_launch_data = adlcpList[tocIndex].dataFromLMS;
			 if(flag==""){
		      		//cmi_launch_data=GetManifestData("cmi.launch_data");

			 }else{
		 	        setSCOInitialData("set.sco.initial.data");
			 }

			 //alert(" IsLMSInitialize  cmi_launch_data="+cmi_launch_data);

			 //+++++++++++++++++++++++++++++++++++++++
			 //alert("cmi_completion_status = " + cmi_completion_status);
			 if(cmi_completion_status == "unknown"){
			 	activityStatusList[Number(tocIndex)].activityAttemptProgressStatus = false;
			 	activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = false;

				//2004.07.30 Marked by Heroin
			 	//deliveryControlsList[Number(tocIndex)].completionSetByContent = false;
			 	setByContentCheckList[Number(tocIndex)].completionSetByCobtentFlag = false;

			 }
			//++++++++++++++++++++++++
			//alert(" 1 activityAttemptProgressStatus = " + activityStatusList[Number(tocIndex)].activityAttemptProgressStatus + " / attemptCompletionStatus = " + activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus);
			//activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = true;
			//alert(" 2 activityAttemptCount = " + activityStatusList[Number(tocIndex)].activityAttemptCount);


			 IsLMSInitialize = "true";
			 IsFinished = "false";
			 //alert("initialized = " + IsLMSInitialize);
			 //alert("initialized cmi_completion_status="+cmi_completion_status);
			 lastError = 0;
			 return "true";
		    }else if ((LMSInitialize.arguments.length==1)&&(LMSInitialize.arguments[0] != "" )){
		    	 lastError = 201;
		    	 return "false";
		    }

	}else if(IsFinished =="true"){
		lastError = 104; //content instance terminated
	    	return "false";
	}
	else{
	    //alert("103  Already Initialized ");
	    lastError = 103; //Already Initialized
	    return "false";
	}
}



function LMSFinish(){
	//alert("LMSFinish  SCO_ID="+SCO_ID + " IsLMSInitialize="+IsLMSInitialize +" IsFinished="+IsFinished);
	//alert("Finishing = " + adl_nav_request);
	checkFinished();
	//termination before initialixation  112
	//termination after termination 113
   	if(IsLMSInitialize=="true"){
   		//if(IsFinished=="true"){
   		//	lastError = 113; //termination after termination 113
		//  	return "false";
   		//}else{
	   		if((LMSFinish.arguments.length==1)){
	   	     		if(LMSFinish.arguments[0]==""){


					if(IsCommit == "false"){
						var tempStrCommit = LMSCommit("");

					}

					//-----SCORM 1.3 ----------------------
					//當LMSFinish被trigger後,如果有nav.event --> adl.nav.request
					//就要將request傳送到sequencing engine
					//if(nav_event!=""){
					if(adl_nav_request!="_none_"){
			        		//sequencing.location.href="sequencing/engine.php?scoID="+SCO_ID+"&idx="+tocIndex+"&navEvent="+nav_event;
			        		//alert("processing adl nav request!  adl_nav_request="+adl_nav_request);


			        		//SetValue("adl.nav.request", "{target=activity_3}choice")
			        		//if(adl_nav_request.indexOf("choice")!=-1 && adl_nav_request.indexOf("{target=")==0){

			        		//	sequencing.location.href="sequencing/engine.php?scoID="+SCO_ID+"&idx="+tocIndex+"&navEvent="+adl_nav_request;
			        		//}


			        		//alert("finishing = " + adl_nav_request);
			        		parent.parent.sequencing.location.href="/learn/scorm/sequencing/NavEventTransfer.php?scoID="+SCO_ID+"&idx="+tocIndex+"&navEvent="+adl_nav_request;

					}

					//-----SCORM 1.3 ----------------------

			   		IsLMSInitialize = "false";
			   		IsFinished="true";
			   		//alert("IsFinished!!!!");
					lastError = 0;
					n=-1;
					m=-1;
					//o=-1;
					o_index = -1;
					o_count = -1;
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

   		//}


   	}else if(IsFinished=="true"){
   		lastError = 113; //termination after termination 113
		return "false";
   	}
   	else{
		lastError = 112; //301->112   	//termination before initialixation  112
   		return "false";
   	}
}




function LMSGetValue(DataModel){
	checkFinished();
	//alert("@@getvalue DataModel="+DataModel);
	if(IsLMSInitialize == "true"){
		if(DataModel==""){
			lastError = 301;
			return "";
		}
		//if(IsFinished == "true"){
		//	lastError = 123; // 123 Retrieve Data After Termination
   		//	return "";
		//}
		//else{
	   		//2004.01.16- Vega modify base on SCORM1.3 Beta3
			/*
			403 value not initialized
			406 data type mismatch
			351 index greater than count
			408 data type not config
			*/

	   		if(DataModel.indexOf("cmi.objectives")!=-1 && DataModel!="cmi.objectives._children" && DataModel!="cmi.objectives._count"){
				var tempArray=DataModel.split(".");
				//2004.06.08- Vega modify base on Test Suite 1.3 test log

				if (DataModel=="cmi.objectives")
				{
					lastError= 401;
					return "";
				}
				//
				if(tempArray[2]>o_count){
					lastError = 301;
					return "";
				/*}else if(tempArray[2]==o_count && CmiObjectivesArray[eval(tempArray[2])][0]==null){
					lastError = 301;
					return "";	*/
				}

		        if(DataModel.indexOf("score._children")!=-1 && tempArray.length==5){
					lastError = 0;
					return cmi_objectives_n_score__children;
				}


				if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
					if(CmiObjectivesArray[eval(tempArray[2])][0]=="InitialData"){
						lastError = 403;
						return "";
					}else{
						lastError = 0;
						//alert("api get id="+CmiObjectivesArray[eval(tempArray[2])][0]);
						return CmiObjectivesArray[eval(tempArray[2])][0];
					}
	        	}
				//-------------------------

	        		//------------SCORM 1.3 modify----------------------------------------
				if(DataModel.indexOf("score.raw")!=-1 && tempArray.length==5){
	            		if(CmiObjectivesArray[eval(tempArray[2])][1]==""){
							lastError = 403; //0->403
							return "";
			    		}
			    		else{
			        		lastError = 0;
			        		return CmiObjectivesArray[eval(tempArray[2])][1];
			    		}

				}

		        	if(DataModel.indexOf("score.max")!=-1 && tempArray.length==5){
		            	if(CmiObjectivesArray[eval(tempArray[2])][2]==""){
							lastError = 403; //0->403
							return "";
						}
					    else{
					        	lastError = 0
					        	return CmiObjectivesArray[eval(tempArray[2])][2];
				    	}

					}

			         if(DataModel.indexOf("score.min")!=-1 && tempArray.length==5){
		        	    if(CmiObjectivesArray[eval(tempArray[2])][3]==""){
						lastError = 403; //0->403
						return "";
				    }
				    else{
				       	lastError = 0;//0->403
			        	return CmiObjectivesArray[eval(tempArray[2])][3];
				    }

				}
	        	 	//------------SCORM 1.3 Modify----------------------------------
	         		//------------SCORM 1.3 ---------------------------------------
		         	//Heroin 2004.01.13
	        	 	if(DataModel.indexOf("score.scaled")!=-1 && tempArray.length==5){
					//(0=="") -> true 一定要加toString再比較....
					if(CmiObjectivesArray[eval(tempArray[2])][4].toString()==""){
							lastError = 403;//0->403
							return "";
			    		}else{
				        	lastError = 0;
				        	return CmiObjectivesArray[eval(tempArray[2])][4];
				    	}
				  }
	         		if(DataModel.indexOf("success_status")!=-1 && tempArray.length==4){
		            	//if(tempArray[2]>o_count){
						//	lastError = 0;
						//	return "unknown";
			    		//}
				    	//else{
					    	lastError = 0;
					    		//alert("api get status="+CmiObjectivesArray[eval(tempArray[2])][5]);
							return CmiObjectivesArray[eval(tempArray[2])][5];
			    		//}
	         		}
		         	if(DataModel.indexOf("completion_status")!=-1 && tempArray.length==4){
	        	    	//if(tempArray[2]>o_count){
						//	lastError = 0;
						//	return "unknown";
				    	//}
						//else{
							lastError = 0;
							return CmiObjectivesArray[eval(tempArray[2])][6];
						//}
	        	 	}
	        	 	//Vega 2004.9.30 added TS 1.3.1 new
	        	 	if(DataModel.indexOf("progress_measure")!=-1 && tempArray.length==4){
	        	 		if(CmiObjectivesArray[eval(tempArray[2])][8]==null || CmiObjectivesArray[eval(tempArray[2])][8]==""){
						lastError = 403;
						return "";
					}else{
						lastError = 0;
						return CmiObjectivesArray[eval(tempArray[2])][8];
					}
	        	 	}
				if(DataModel.indexOf("description")!=-1 && tempArray.length==4){
	        	    	//if(tempArray[2]>o_count){
				//			lastError = 403;
				//			return "";
					if(CmiObjectivesArray[eval(tempArray[2])][7]=="null"){
						lastError = 403;
						return "";
					}else{
						lastError = 0;
						return CmiObjectivesArray[eval(tempArray[2])][7];
					}
	        	 	}
	                	//------------SCORM 1.3 ---------------------------------------

		   	}





	   		else if(DataModel.indexOf("cmi.interactions")!=-1 && DataModel!="cmi.interactions._children" && DataModel!="cmi.interactions._count"){
		                var tempArray=DataModel.split(".");
						lastError = "";
				//Heroin 2004.05.11
				//alert("tempArray[2]="+tempArray[2]+"  CmiInteractionsArray.length="+CmiInteractionsArray.length);
				if(tempArray[2]>=CmiInteractionsArray.length ){
					lastError = 301;
					return "";
				}else if(tempArray.length>=3 && CmiInteractionsArray[eval(tempArray[2])][0]=="InitialData"){
					lastError = 301;
					return "";
				}
				if(tempArray[3]=="id" && tempArray.length==4){
					if(CmiInteractionsArray[eval(tempArray[2])][0]==""){
						lastError = 403;
						return "";
					}


					lastError = 0;
					return CmiInteractionsArray[eval(tempArray[2])][0];
	                	}

	                	if(tempArray[3]=="type" && tempArray.length==4){
	                		if(CmiInteractionsArray[eval(tempArray[2])][2]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
					return CmiInteractionsArray[eval(tempArray[2])][2];
	                	}

	                	//cmi.interactions.n.objectives.n.id
	                	//cmi.interactions.n.objectives._count
	                	if(tempArray[3]=="objectives"){
	                		//alert("@@get objectives "+DataModel);
	                		if(tempArray.length==5 && tempArray[4]=="_count"){
	                			lastError = 0;
						if(c==-1){
							return 0;
						}else{
							return ( Number(CmiInteractionsArray[eval(tempArray[2])][8])+1);
						}
	                		}
	                		else if(tempArray.length==6 && tempArray[5]=="id"){
	                			//alert("interaction object = " + tempArray[2] + " id number = " + tempArray[4]);
	                			//alert("LMS Maintain objective= " + CmiInteractionsObjectivesArray.length + " and id = " + CmiInteractionsObjectivesArray[tempArray[2]].length);
	                			//socool 2004.12.21 沒有objective也要記得return error,否則後面的比較會錯.

	                			/*
	                			if(CmiInteractionsObjectivesArray != null && CmiInteractionsObjectivesArray[tempArray[2]] != null){
	         					alert("interaction object = " + tempArray[2] + " id number = " + tempArray[4]);
	                				alert("LMS Maintain objective= " + CmiInteractionsObjectivesArray.length + " and id = " + CmiInteractionsObjectivesArray[tempArray[2]].length);

							// lastError = 301;
							// return "";
						}


	                			if(tempArray[2]>=CmiInteractionsObjectivesArray.length){
	                				// alert("@@ out of range");
							lastError = 301;
							return "";
						}

	                			if(tempArray[4]>=CmiInteractionsObjectivesArray[tempArray[2]].length){
	                				// alert("@@ out of range");
							lastError = 301;
							return "";
						}
						*/

						// socool 2004.12.22
	                			if(tempArray[2]>=CmiInteractionsArray.length){
	                				// alert("@@ out of range");
	                				// alert("1: n = " + tempArray[2] + " m = " + tempArray[4] +  " c = " + CmiInteractionsArray.length);
							lastError = 301;
							return "";
						}
						if(Number(tempArray[4]) > Number(CmiInteractionsArray[Number(tempArray[2])][8])){
							//alert("@@ out of range");
							//alert("2: n = " + tempArray[2] + " m = " + tempArray[4] +  " c = " + CmiInteractionsArray[Number(tempArray[2])][8]);
							lastError = 301;
							return "";
						}
						else if(CmiInteractionsObjectivesArray[eval(tempArray[2])][eval(tempArray[4])]==""){
							//alert("@@get obj id n="+tempArray[2]+" m="+tempArray[4]+" id="+CmiInteractionsObjectivesArray[tempArray[2]][tempArray[4]]);
							//alert("3: n = " + tempArray[2]  + " m = " + tempArray[4] + " c = " + CmiInteractionsArray[Number(tempArray[2])][8]);
							lastError = 403;
							return "";
						}

						lastError = 0;
						//alert("@@ get obj id n="+tempArray[2]+" m="+tempArray[4]+" id="+CmiInteractionsObjectivesArray[tempArray[2]][tempArray[4]]);
						return CmiInteractionsObjectivesArray[eval(tempArray[2])][eval(tempArray[4])];
	                		}

	                	}

	                	/* && DataModel.indexOf("id")!=-1 && tempArray.length==6){
	                		//Heroin 2004.05.12
	                		if(tempArray[2]>=CmiInteractionsObjectivesArray.length){
						lastError = 301;
						return "";
					}
							lastError = 0;
							//alert("tempArray[2]="+tempArray[2]+" CmiInteractionsObjectivesArray.length="+CmiInteractionsObjectivesArray.length+" DataModel="+DataModel);
							//alert("tempArray[4]="+tempArray[4]+" CmiInteractionsObjectivesArray[eval(tempArray[2])].lengyh="+CmiInteractionsObjectivesArray[eval(tempArray[2])].length);
							return CmiInteractionsObjectivesArray[eval(tempArray[2])][eval(tempArray[4])];
	                	}
	                	if(DataModel.indexOf("objectives._count")!=-1 && tempArray.length==5){
	                		//var objectivesArray=DataModel.split(".");
		               		lastError = 0;
							if(c==-1){
								return 0;
							}else{
								return ( Number(CmiInteractionsArray[eval(tempArray[2])][8])+1);
							}
	                	}
	                	*/
	                	if(DataModel.indexOf("timestamp")!=-1 && tempArray.length==4){
	                		if(CmiInteractionsArray[eval(tempArray[2])][1]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
							return CmiInteractionsArray[eval(tempArray[2])][1];
	                	}

	                	if(DataModel.indexOf("weighting")!=-1 && tempArray.length==4){
	                		if(CmiInteractionsArray[eval(tempArray[2])][3]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
							return CmiInteractionsArray[eval(tempArray[2])][3];
	                	}
	                	if(DataModel.indexOf("learner_response")!=-1 && tempArray.length==4){
	                		//socool 2004.12.23 testsuite 1.3.2 value=""時,仍要可以回
	                		/*
	                		if(CmiInteractionsArray[eval(tempArray[2])][4]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		*/
	                		//socool 2004.12.23 當cmi.interaction.n.type = ""時,return 403
					if(CmiInteractionsArray[eval(tempArray[2])][2]==""){
	                			lastError = 403;
	                			return "";
	                		}


	                		if( CmiInteractionsArray[eval(tempArray[2])][2]=="sequencing" && CmiInteractionsArray[eval(tempArray[2])][4]==""){

	                			lastError = 403;
	                			return "";

	                		}


	                		lastError = 0;
					return CmiInteractionsArray[eval(tempArray[2])][4];
	                	}
	                	if(DataModel.indexOf("result")!=-1 && tempArray.length==4){
	                		if(CmiInteractionsArray[eval(tempArray[2])][5]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
							return CmiInteractionsArray[eval(tempArray[2])][5];
	                	}
	                	if(DataModel.indexOf("latency")!=-1 && tempArray.length==4){
	                		if(CmiInteractionsArray[eval(tempArray[2])][6]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
					return CmiInteractionsArray[eval(tempArray[2])][6];
				}
				if(DataModel.indexOf("description")!=-1 && tempArray.length==4){

					if(CmiInteractionsArray[eval(tempArray[2])][7]=="InitialData"){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
					return CmiInteractionsArray[eval(tempArray[2])][7];
	                	}
	                	if(tempArray[3]=="correct_responses"){
	                		if(tempArray.length==5 && tempArray[4]=="_count"){
	                			lastError = 0;
						if(m==-1){
							return 0;
						}else{
							return ( Number(CmiInteractionsArray[eval(tempArray[2])][9])+1);
						}
	                		}
	                		else if(tempArray.length==6 && tempArray[5]=="pattern"){

	                			//socool 2004.12.23 impletement "out of range"
	                			if(tempArray[2]>=CmiInteractionsArray.length){
	                				//alert("@@ out of range");
	                				//alert("1: n = " + tempArray[2] + " m = " + tempArray[4] +  " c = " + CmiInteractionsArray.length);
							lastError = 301;
							return "";
						}
						if(Number(tempArray[4]) > Number(CmiInteractionsArray[Number(tempArray[2])][9])){
							//alert("@@ out of range");
							//alert("2: n = " + tempArray[2] + " m = " + tempArray[4] +  " c = " + CmiInteractionsArray[Number(tempArray[2])][8]);
							lastError = 301;
							return "";
						}
	                		// socool marked 2004.12.23
	                			if(tempArray[2]>=CmiInteractionsCorrect_ResponsesArray.length){
	                				//alert("@@ out of range");
							lastError = 301;
							return "";
						}

	                			if(CmiInteractionsArray[eval(tempArray[2])][2]=="true-false" && tempArray[4]>=1){
							lastError = 301;
							return "";
						}
						//Vega 2004.9.27 added
						if(CmiInteractionsArray[eval(tempArray[2])][2]=="numeric" && tempArray[4]>=1){
							lastError = 301;
							return "";
						}
						if(CmiInteractionsArray[eval(tempArray[2])][2]=="other" && tempArray[4]>=1){
							lastError = 301;
							return "";
						}


						//Vega 2004.9.27 marked
						/*if(CmiInteractionsArray[eval(tempArray[2])][2]=="long-fill-in"){
							lastError = 0;
							return CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])];
						}else if(tempArray[4]>=CmiInteractionsCorrect_ResponsesArray[tempArray[2]].length){
	                				//alert("@@ out of range");
							lastError = 301;
							return "";
						}*/
						if(tempArray[4]>=CmiInteractionsCorrect_ResponsesArray[tempArray[2]].length){
	                				//alert("@@ out of range");
							lastError = 301;
							return "";
						}
						//Vega 2004.9.24 Modified
						//else if(CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])]==""){
						else if(CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])]=="InitialData"){
							//alert("@@get obj id n="+tempArray[2]+" m="+tempArray[4]+" id="+CmiInteractionsCorrect_ResponsesArray[tempArray[2]][tempArray[4]]);
							lastError = 403;
							return "";
						}
						lastError = 0;
						//alert("@@ get obj id n="+tempArray[2]+" m="+tempArray[4]+" id="+CmiInteractionsObjectivesArray[tempArray[2]][tempArray[4]]);
						return CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])];
	                		}

	                	}

	                	/*if(DataModel.indexOf("correct_responses")!=-1 && DataModel.indexOf("pattern")!=-1 && tempArray.length==6){
	                		if(CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])]==""){
	                			lastError = 403;
	                			return "";
	                		}
	                		lastError = 0;
					return CmiInteractionsCorrect_ResponsesArray[eval(tempArray[2])][eval(tempArray[4])];
	                	}
	                	if(DataModel.indexOf("correct_responses._count")!=-1 && tempArray.length==5){
	                		//var correctResponsesArray=DataModel.split(".");
		               		lastError = 0;
		               		if(m==-1){
						return 0;
					}else{
						return ( Number(CmiInteractionsArray[eval(tempArray[2])][9])+1);
					}
				}*/
				//if(lastError ==""){
				//	CheckDataModel(DataModel);
				//	return "";
				//}

				lastError = 401;
				return "";
	   		}




	   		else if(DataModel.indexOf("adl.nav")!=-1){
	   			//SCORM 1.3 Working Draft 1 Edit by Henry 2003.11.20
	   			if(DataModel=="adl.nav.request"){
	   				lastError = 0;
	   				return adl_nav_request;
	   			}else if(DataModel=="adl.nav.request_valid.continue"){
	   				lastError = 0;
	   				//return queryPermittedObj.c;
	   				return ADLNavRequestObj.c;

			   	}else if(DataModel=="adl.nav.request_valid.previous"){
					lastError = 0;
					//return queryPermittedObj.p;
					return ADLNavRequestObj.p;

	   			}else if(DataModel.indexOf("adl.nav.request_valid.choice")!=-1){
					//var delimiter = "";
					//DataModelString --> adl.nav.request_valid.choice.{target=URI}
					//delimiter = DataModel.slice(DataModel.indexOf("=")+1 , DataModel.indexOf("}"));
					//先用url找identifier,再去找看這個item
					//還要check....forwardOnly,controlMode,preCondition,limitCondition,disable,hiddenfromChoice


					if(String(controlModeList[Number(tocIndex)].choice)=="true"){
						adl_nav_request_valid_choice = "true";
					}

					return 	adl_nav_request_valid_choice;

	   			}else{
	   				lastError = 401;
	   				return "";
	   			}



	   		}
	   		else if(DataModel.indexOf("cmi.comments_from_learner")!=-1){
	                	var tempArray=DataModel.split(".");
	                	var StrDataModelCheck = DataModel.slice(DataModel.indexOf("learner"));
	                	if(StrDataModelCheck.indexOf("_children")!=-1){
					lastError = 0;
					return cmi_comments_from_learner_children;
				}else if(StrDataModelCheck.indexOf("_count")!=-1){
	                		lastError = 0;

	                		return	CommentsFromLearnerCount+1;

	                	}else if(StrDataModelCheck.indexOf("comment")!=-1){
		                	if(CommentsFromLearnerCount>=tempArray[2]){

		                		if(CmiCommentsFromLearnerArray[eval(tempArray[2])][0]=="null"){
		                			lastError = 403;
		                			return "";

		                		}else{
		                			lastError = 0;
		                			return	CmiCommentsFromLearnerArray[eval(tempArray[2])][0];
		                		}

					}else{
						lastError = 301;
						return "";
					}
		                }else if(StrDataModelCheck.indexOf("location")!=-1){
		                	if(CommentsFromLearnerCount>=tempArray[2]){

		                		if(CmiCommentsFromLearnerArray[eval(tempArray[2])][1]=="null"){
		                			lastError = 403;
		                			return "";

		                		}else{
		                			lastError = 0;
		                			return	CmiCommentsFromLearnerArray[eval(tempArray[2])][1];
		                		}

					}else{
						lastError = 301;
						return "";
					}

		                //}else if(StrDataModelCheck.indexOf("date_time")!=-1){
		                //Modified by Vega 2004.8.27 DM
		                }else if(StrDataModelCheck.indexOf("timestamp")!=-1){
		                	if(CommentsFromLearnerCount>=tempArray[2]){

		                		if(CmiCommentsFromLearnerArray[eval(tempArray[2])][2]=="null"){
		                			lastError = 403;
		                			return "";

		                		}else{
		                			lastError = 0;
		                			return	CmiCommentsFromLearnerArray[eval(tempArray[2])][2];
		                		}

					}else{
						lastError = 301;
						return "";
					}
		                }else{
		                	lastError = 401;
					return "";
		                }
			}else if(DataModel.indexOf("cmi.comments_from_lms")!=-1){
		      		var tempArray=DataModel.split(".");
		                var StrDataModelCheck = DataModel.slice(DataModel.indexOf("lms"));

		                //如果data model裡面還沒有被set過,結果又去get....就會error code 403
		                //可是問題是如何將值設入該data model之中,
		                //因為所有data model都是read-only...
		                //在spec裡有說...平台必須要有一個介面該administrator或是author可以將comment寫入
		                //並說這個並不太conformance的範圍之內...
		                //我們的模組應不應該提供這個功能,還是讓廠商自己做呢??

		                if(StrDataModelCheck.indexOf("_children")!=-1){
					lastError = 0;
					return cmi_comments_from_lms_children;
				}else if(StrDataModelCheck.indexOf("_count")!=-1){
		                	lastError = 0;
		                	return	CommentsFromLmsIndex+1;  //Heroin 2004.05.11

		                }else if(StrDataModelCheck.indexOf("comment")!=-1){
		                	if(CommentsFromLmsIndex>=tempArray[2]){
			                	lastError = 0;
			                	return	CmiCommentsFromLmsArray[eval(tempArray[2])][0];
					}else{
						lastError = 301;
						return "";
					}
		                }else if(StrDataModelCheck.indexOf("location")!=-1){
		                	if(CommentsFromLmsIndex>=tempArray[2]){
		                		lastError = 0;
		                		return	CmiCommentsFromLmsArray[eval(tempArray[2])][1];
		                	}else{
						lastError = 301;
						return "";
					}
		                //Modified by Vega 2004.8.27 DM
				}else if(StrDataModelCheck.indexOf("timestamp")!=-1){
		                //}else if(StrDataModelCheck.indexOf("date_time")!=-1){
		                	if(CommentsFromLmsIndex>=tempArray[2]){
		                		lastError = 0;
		                		return	CmiCommentsFromLmsArray[eval(tempArray[2])][2];
		                	}else{
						lastError = 301;
						return "";
					}
		                }else{
		                	lastError = 401;
					return "";
		                }


			}else if(DataModel.indexOf("cmi._version")!=-1){
	   			lastError = 0;
				return cmi_version;
			}else{
	   			// change DM to SCORM 1.3 WD
			   	// Heroin 2004.01.12
			   	// Vega 2004.07.29 marked
				//DataModel=changeDM(DataModel);
			   	//alert(DataModel);
			   	switch (DataModel){

		   		//----3-----
		   		case "cmi.credit":
					lastError = 0;
					return cmi_credit;
					break;
				case "cmi.entry":
					lastError = 0;
					return cmi_entry;
					break;
		   		case "cmi.exit":
					lastError = 405;   //Heroin
					return "";
				      	break;

		   		//----5-----
				case "cmi.launch_data":
					//Heroin 403
					//alert("get cmi_launch_data="+cmi_launch_data);
					if(cmi_launch_data!=null){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_launch_data;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.learner_id":  //learner_id
					lastError = 0;
					return cmi_learner_id;
					break;

				case "cmi.learner_name":
					lastError = 0;
					return cmi_learner_name;
					break;



				//----6------
				case "cmi.learner_preference._children":
				      	lastError = 0;
				      	return cmi_learner_preference__children;
				      	break;

				//Vega 2004.9.24 modified ----mistake in ver1.3.0 spec
				case "cmi.learner_preference":
					//lastError = 0;
					//return cmi_learner_preference;
					lastError = 401;
					return "";
					break;

				case "cmi.learner_preference.audio_level":	//Heroin
					//Henry 2004.05.27...............應該要改成2004之data model
					//cmi.learner_preference.audio_level
					if(cmi_learner_preference_audio_level!="null"){
						lastError = 0;
					      	return cmi_learner_preference_audio_level;
					}else{
						//Vega 2004.9.24 ----1.3.1 TS default value "1"
						lastError = 0;
						return 1;
					}
				      	break;
				case "cmi.learner_preference.language":
					//Henry 2004.05.27
					//cmi.learner_preference.language
					if(cmi_learner_preference_language!="null"){
						lastError = 0;
					      	return cmi_learner_preference_language;
					}else{
						//Vega 2004.9.24 ----1.3.1 TS default value ""
						lastError = 0;
						return "";
					}
				      	break;

				case "cmi.learner_preference.delivery_speed":
					//Henry 2004.05.27
					//cmi.learner_preference.delivery_speed
					if(cmi_learner_preference_delivery_speed!="null"){
						lastError = 0;
					      	return cmi_learner_preference_delivery_speed;
					}else{
						//lastError = 403;雖然在set之前就get但error code還是不是403而是0
						lastError = 0;
						return 1; //initial value is null but default is 1
					}
				      	break;

				case "cmi.learner_preference.audio_captioning":
					//cmi.learner_preference.audio_captioning
					if(cmi_learner_preference_audio_captioning!="null"){
						lastError = 0;
					      	return cmi_learner_preference_audio_captioning;
					}else{
						//lastError = 403;雖然在set之前就get但error code還是不是403而是0
						lastError = 0;
						return 0; //initial value is null but default is 0
					}
				      	break;


				//----7-----
				case "cmi.location":
					//Heroin 403

					if(cmi_location!="null"){
						lastError = 0;
						return cmi_location;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.max_time_allowed":
					//Heroin 403
					//test2004
					//alert("LMSGetValue cmi.max_time_allowed="+cmi_max_time_allowed);
					if(cmi_max_time_allowed!=""){ //是"" 還是 "undefine"??
						lastError = 0;
					      	return cmi_max_time_allowed;
					      	break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.mode":
					lastError = 0;
					return cmi_mode;
					break;




				//----9-----

				//-----Heroin 2004.01.13 SCORM 1.3 WD----- cmi.scaled_passing_score
				//2004.12.24 Yunghsiao TS 1.3.2
				case "cmi.scaled_passing_score":
				//alert("satisfiedByMeasure = "+ primaryObjectiveList[Number(tocIndex)].satisfiedByMeasure);
					if((primaryObjectiveList[Number(tocIndex)].satisfiedByMeasure == false)||(primaryObjectiveList[Number(tocIndex)].satisfiedByMeasure == "false")){
						//alert("lastError = 403");
						lastError = 403;
						return "";
						break;
					}
					cmi_scaled_passing_score=primaryObjectiveList[Number(tocIndex)].minNormalizedMeasure;
					if(cmi_scaled_passing_score!=""){
						lastError = 0;
						//alert("bla" + primaryObjectiveList[Number(tocIndex)].minNormalizedMeasure);
						return primaryObjectiveList[Number(tocIndex)].minNormalizedMeasure;
						//return cmi_scaled_passing_score;
						break;

					}else{
						lastError = 403;
						return "";
						break;
					}

				case "cmi.score._children":
					lastError = 0;
					return cmi_score__children;
					break;
				case "cmi.score.scaled":
					//Henry 2004.05.25

					//Heroin 403
					if(cmi_score_scaled!=""){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_score_scaled;

					}else{
						lastError = 403;
						return "";
					}

					break;
				case "cmi.score.raw":
					//Heroin 403
					if(cmi_score_raw!=""){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_score_raw;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.score.max":
					//Heroin 403
					if(cmi_score_max!=""){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_score_max;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.score.min":
					//Heroin 403
					if(cmi_score_min!=""){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_score_min;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}






				//------10-----


				case "cmi.session_time":
				      	lastError = 405;   //Heroin
				      	return "";
				      	break;
				//2004.12.24
				case "cmi.success_status":
					lastError = 0;
					return cmi_success_status;
					break;
				case "cmi.completion_status":
					lastError = 0;
					//alert("getvalue cmi_completion_status="+cmi_completion_status);
					return cmi_completion_status;
					break;

				//Heroin 2004.02.10
				case "cmi.completion_threshold":
					lastError = 0;
					cmi_completion_threshold = adlcpList[tocIndex].completionThreshold;
					return cmi_completion_threshold;
					break;
				case "cmi.progress_measure":
					if(cmi_progress_measure=="null"){
						lastError = 403;
						return "";

					}else{
						lastError = 0;
						return cmi_progress_measure;
					}
					break;


				//----SCORM 1.2-----
				case "cmi.core.lesson_status":
					lastError = 0;
					return cmi_core_lesson_status;
					break;

				case "cmi.suspend_data":
					//Heroin 403
					//Henry 2004.05.31
					if(cmi_suspend_data!="null"){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_suspend_data;
						break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.time_limit_action":
					//Heroin 403
					//Heroin 2004.05.11
					cmi_time_limit_action = adlcpList[tocIndex].timeLimitAction;
					if(cmi_time_limit_action!=""){ //是"" 還是 "undefine"??
					      	lastError = 0;

					      	return cmi_time_limit_action;
					      	break;
					}else{
						lastError = 403;
						return "";
						break;
					}
				case "cmi.total_time":
					//Heroin 403
					//if(cmi_total_time!=""){ //是"" 還是 "undefine"??
						lastError = 0;
						return cmi_total_time;
						break;
					//}else{
					//	lastError = 403;
					//	return "";
					//	break;
					//}



				//---1.3 WD 無
				case "cmi.student_data._children":
				      	lastError = 0;
				      	return cmi_student_data__children;
				      	break;
				case "cmi.core._children":
					lastError = 0;
					return cmi_core__children;
					break;

				//----????-----
				case "cmi.student_data.mastery_score":
					lastError = 0;
					return cmi_sd_mastery_score;
					break;
				//----????------
				case "backdoor.core.exit":
					return cmi_exit;
					break;



				//----interactions-----
				case "cmi.interactions._children":
					lastError = 0;
					return cmi_interactions__children;
					break;
				case "cmi.interactions._count":
					lastError = 0;
					return (Number(n)+1);
					break;

				//----objectives-----
				//2004.06.10 -Vega
				case "cmi.objectives":
					lastError = 401;
					return "";
					break;

				case "cmi.objectives._children":
					lastError = 0;
					return cmi_objectives__children;
					break;
				case "cmi.objectives._count":
					lastError = 0;
					//test2004
					//alert("Get obj_count/objectiveList.length="+objectiveList.length);
					//alert("Get obj_count/o_count="+o_count);
					return (Number(o_count)+1);
					break;



				//-----SCORM 1.3--------------------
				default :
					CheckDataModel(DataModel);
					lastError = 401;
				      	if(lastError == 201){
				      		return "";
				      	}else{
				      		return "";
				      	}
				      	break;
	 			}
	   		}

   		//}


	}else if(IsFinished == "true"){
			lastError = 123; // 123 Retrieve Data After Termination
   			return "";
	}
	else{
		lastError = 122; //122 Retrieve Data Before Initialization
		return "";
	}

}


//Heroin 2004.03.22
function LMSSetValue(DataModel, Value){

	//Yunghsiao 2005.08.11
	if(DataModel==""){
		lastError= 351;
		return "false";
	}
	//YunghsiaoAlert
	//alert("setvalue DataModel="+DataModel+" Value="+Value);
	checkFinished();
	if(IsLMSInitialize=="true"){
	//if(IsFinished=="true"){
	//	lastError = 133; //133 store data before initialization
	//	return "false";
	//}
	//else{

   		//2004.01.28- Vega modify base on SCORM1.3 Beta3 -objectives
   		if(DataModel.indexOf("cmi.objectives")!=-1 && DataModel!="cmi.objectives._children" && DataModel!="cmi.objectives._count"){
			var tempArray=DataModel.split(".");
			//alert("DataModel="+DataModel);
			//2004.06.08- Vega modify base on Test Suite 1.3 test log
			CmiObjectiveStorageSwitch = "true";
			if(DataModel=="cmi.objectives"){
				lastError= 401;
				return "false";
			}
			/*if(DataModel.indexOf("id")==-1 && tempArray.length!=4){
				if(CmiObjectivesArray.length==0){
					lastError = ;
					return "false";
				}else if(CmiObjectivesArray[o_index][0]=="" || CmiObjectivesArray[o_index][0]==null || CmiObjectivesArray.length==0){
					lastError = 408;
					return "false";
				}
			}*/
			//----------------
			if((tempArray[2]-o_count)==1){
				//Vega 2004.10.1
				CmiObjectivesArray[eval(tempArray[2])]=new Array("InitialData","","","","","unknown","unknown","null","");
				o_index=Number(tempArray[2]);
				/*if(o_index > o_count){
					o_count = o_index;
				}*/
				//Add by Vega 2004/1/28
			}else{
				if((tempArray[2]-o_count)>1){
					lastError = 351; //General set failure
					return "false";
				}else if(tempArray[2] <= o_count){
					o_index = Number(tempArray[2]);
				}
			}

			//-------------------------------------------------------
			//2004.06.08- Vega modify base on Test Suite 1.3 test log

			/*check if
				1. "" or " "
				2. unique
				3. if type is long_identifier_type, check the type
					3-1. urn:$<NID>:<NSS>
					3-2. <NSS> can't have any " "
				4. if not ...what can I check??
			*/
			if(DataModel.indexOf("id")!=-1 && tempArray.length==4){
				//socool 2004.12.24 base on TS 1.3.2 log
				if(Value == ""){
					lastError = 406;
					return "false"

				}



				if(o_index > o_count){
							o_count = o_index;
				}
				if(Value.indexOf(" ")==-1 && Value!=" " && Value!=""){ //1
					for(var i=0;i<CmiObjectivesArray.length-1;i++){ //2
						if(Value==CmiObjectivesArray[i][0]){
							lastError = 351;
							return "false"
						}
					}
					if (Value.indexOf(":")!=-1){//3
						var tempValueArray;
						tempValueArray = Value.split(":");
						if(tempValueArray[0]=="urn" && tempValueArray.length==3){//3-1
							var strNSS=tempValueArray[2];
							if(strNSS.indexOf(" ")==-1){ //3-2
							//if(CmiObjectivesArray[o].length<0){

								CmiObjectivesArray[o_index][0]=Value;
								lastError = 0;
								/*if(o_index > o_count){
									o_count = o_index;
								}*/
								return "true";
							//}else{
							//	lastError = 408;
							//}
							}else{
								lastError = 406;
								//CmiObjectivesArray[o_index][0]="";
								return "false";
							}
						}else{
							lastError = 406;
							//CmiObjectivesArray[o_index][0]="";
							return "false";
						}
					}else{//4
						CmiObjectivesArray[o_index][0]=Value;
						lastError = 0;
						/*if(o_index > o_count){
							o_count = o_index;
						}*/
						return "true";
					}
				 }else{
					lastError = 406;
					//CmiObjectivesArray[o_index][0]="";
					return "false";

				 }
			}else{
				if(CmiObjectivesArray[o_index][0]=="" || CmiObjectivesArray[o_index][0]=="InitialData"){
					lastError = 408;
					//CmiObjectivesArray[o_index][0]="";
					return "false";
				}
			}


   	        	//----- SCORM 1.3 modify--------------------------
   	        if(DataModel.indexOf("score.raw")!=-1 && tempArray.length==5){
				if(!isNaN(Value)){
					CmiObjectivesArray[o_index][1]=Value;
					lastError = 0;
					return "true";
				}else{
   	             	lastError = 406;
   	                return "false";
   	            }
   	        }
			if(DataModel.indexOf("score.max")!=-1 && tempArray.length==5){
				if(!isNaN(Value)){
					CmiObjectivesArray[o_index][2]=Value;
					lastError = 0;
					return "true";
		         }else{
	   	               	lastError = 406;
	   	               	return "false";
	   	         }
	   	    }
			if(DataModel.indexOf("score.min")!=-1 && tempArray.length==5){
				if(!isNaN(Value)){
					//if(CmiObjectivesArray[o_index][0]!="" && CmiObjectivesArray[o_index][0]!=null){
					CmiObjectivesArray[o_index][3]=Value;
					lastError = 0;
					return "true";
					//}else{
					//	lastError = 408;
					//	return "false";
					//}
		        }
	   	        else{
	   	        	lastError = 406;
	   	        	return "false";
	   	      	}
			}
	   		//----- SCORM 1.3 modify--------------------------
	   	        //----- SCORM 1.3 --------------------------------
	   	    if((DataModel.indexOf("score.normalized")!=-1 || DataModel.indexOf("score.scaled")!=-1) && tempArray.length==5){
				if(!isNaN(Value) || Value==""){
						if((Number(Value))<= 1 && (Number(Value))>=-1){
							//if(CmiObjectivesArray[o_index][0]!="" && CmiObjectivesArray[o_index][0]!=null){
								CmiObjectivesArray[o_index][4]=Value;

								//Heroin 2004.03.22
								//alert("SCO_ID="+SCO_ID+"  objectiveID="+CmiObjectivesArray[o][0]+"  Value="+Value);

								UpdateObjectivesMeasure(SCO_ID,CmiObjectivesArray[o_index][0],Value);
								// 1.判斷是否 satisfiedByMeasure
								// primary  satisfiedByMeasure =true
								// target satisfiedByMeasure=true
								// satisfiedByMeasure
								// a.primay satisfiedByMeasure
								// b.target satisfiedByMeasure writeSatisfiedStatus

								for(var i=0;i<objectiveList.length;i++){
									if(objectiveList[i].objectiveID==CmiObjectivesArray[o_index][0]){
										if(objectiveList[i].satisfiedByMeasure=="true"){
											// 是則做下面
											//modified by Heroin 2004.03.10
											//Heroin 2004.08.19
											var calStatus=CalObjectiveSuccessStatus(SCO_ID,objectiveList[i].objectiveID,Value);
												if(calStatus=="passed"){
													UpdateObjectivesStatus(SCO_ID,true,CmiObjectivesArray[o_index][0]);
													CmiObjectivesArray[o_index][5]="passed";
												}else if(calStatus=="failed"){
													UpdateObjectivesStatus(SCO_ID,false,CmiObjectivesArray[o_index][0]);
													CmiObjectivesArray[o_index][5]="failed";
												}
												setByContentCheckList[tocIndex].objectiveSetByContentFlag=true;
												Set_Objective_by_content(true);
										}
									}
								}

								lastError = 0;
								return "true";
							//}else{
							//   lastError = 408;
							//   return "false";
							//}
						}else{
							lastError = 407;
							return "false";
						}
	   	            }else{
	   	             	lastError = 406;
	   	               	return "false";
	   	            }
	   	        }

	   	        if(DataModel.indexOf("success_status")!=-1 && tempArray.length==4){
				var SuccessStatusParameterFound = "false";
	                for(i=0;i<SuccessStatusArray.length;i++){
						if(Value == SuccessStatusArray[i]){
							SuccessStatusParameterFound = "true";
						}
	                }
	                if(SuccessStatusParameterFound == "true"){
					//if(CmiObjectivesArray[o_index][0]=="" || CmiObjectivesArray[o_index][0]==null){
					//	lastError = 408;
					//	return "false";
					//}else{
						CmiObjectivesArray[o_index][5] = Value;
			            lastError = 0;
		            	//Heroin 2004.08.19
		            	//Heroin 2004.09.01
		            	if(Value == "passed"){
		            		UpdateObjectivesStatus(SCO_ID,true,CmiObjectivesArray[o_index][0]);
		            	}else if(Value == "failed"){
		            		UpdateObjectivesStatus(SCO_ID,false,CmiObjectivesArray[o_index][0]);
		            	}


			        	return "true";
					//}
	                }else{
						lastError = 406;
						return "false";
	   				}
	   	        }

	   	        if(DataModel.indexOf("completion_status")!=-1 && tempArray.length==4){
				var CompletionStatusParameterFound = "false";
	                	for(i=0;i<CompletionStatusArray.length;i++){
					if(Value == CompletionStatusArray[i]){
						CompletionStatusParameterFound = "true";
					}
	                	}
	                	//alert("length=" + CompletionStatusArray.length + " / " + CompletionStatusParameterFound);
	                	if(CompletionStatusParameterFound == "true"){
					//if(CmiObjectivesArray[o_index][0]=="" || CmiObjectivesArray[o_index][0]==null){
					//	lastError = 408;
					//	return "false";
					//}else{
			              	CmiObjectivesArray[o_index][6] = Value;
			                lastError = 0;
			                return "true";
					//}
	           		}
	   	       	 	else{
	   	        		lastError = 406;
	   	        		return "false";
	   	        	}
			}
	    	        //----- SCORM 1.3 --------------------------------

			//set to objective Heroin-2003.11.11
			//alert("objective.Length="+objectiveList.length+"  ID="+CmiObjectivesArray[eval(tempArray[2])][0]+"  tocIndex="+tocIndex);
			for(var i=0;i<objectiveList.length;i++){
				//alert("index="+objectiveList[i].itemIndex+" ="+tocIndex+"  ID="+objectiveList[i].objectiveID+" ="+CmiObjectivesArray[eval(tempArray[2])][0]);
				if(objectiveList[i].itemIndex==tocIndex && objectiveList[i].objectiveID==CmiObjectivesArray[eval(tempArray[2])][0]){
					if(CmiObjectivesArray[eval(tempArray[2])][5]=="passed"){
						objectiveProgressInfoList[i].objectiveProgressStatus=true;
						objectiveProgressInfoList[i].objectiveSatisfiedStatus=true;
					}
					else if(CmiObjectivesArray[eval(tempArray[2])][5]=="failed"){
						objectiveProgressInfoList[i].objectiveProgressStatus=true;
						objectiveProgressInfoList[i].objectiveSatisfiedStatus = false;
					}
					else if(CmiObjectivesArray[eval(tempArray[2])][5]=="unknown"){
						objectiveProgressInfoList[i].objectiveProgressStatus=false;
					}
					//sharedObjective not finished
					UpdateSharedObjective(i);
					lastError = 0;
			                return "true";
					break;
				}
			}
			//Vega 2004.9.30 added TS 1.3.1 new
			if(DataModel.indexOf("progress_measure")!=-1 && tempArray.length==4){
					if(isNaN(Value)){
						lastError = 406;
						return "false";
					}else{
						if(Value>=0 && Value<=1){
							CmiObjectivesArray[eval(tempArray[2])][8]=Value;
							lastError = 0;
							return "true";
						}else{
							lastError = 407;
							return "false";
						}
					}
	   	        }
	   	        if(DataModel.indexOf("score._children")!=-1 && tempArray.length==5){
					//if(CmiObjectivesArray[o_index][0]!="" && CmiObjectivesArray[o_index][0]!=null){
						lastError = 404;
						return "false";
					//}else{
					//	lastError = 408;
					//	return "false";
					//}
	   	        }
	   	        if(DataModel.indexOf("description")!=-1 && tempArray.length==4){
					if(CmiObjectivesArray[eval(tempArray[2])][0] == "" || CmiObjectivesArray[eval(tempArray[2])][0]== "InitialData"){
						lastError = 408 ;
						return "false";
					}else{
						var tempValue=Value;
						if(tempValue.indexOf("{lang=")!=-1){
							var tempA=tempValue.indexOf("{lang=");
							var tempB=tempValue.indexOf("}");
							var lanCode=tempValue.substring(tempA+6,tempB);

							var checkeType=checkISOLang(lanCode);
							if(checkeType.toString()=="false"){
								lastError = 406;
								return"false"
							}
						}
						CmiObjectivesArray[eval(tempArray[2])][7]=Value;
						lastError = 0;
						return "true";

					}
	   	        }

			/*if(lastError == 0){
				return "true";
			}else{
				return "false";
			}*/

		}
		//cmi.objectives--END






		//2004.01.16- Vega modify base on SCORM1.3 Beta3 -cmi.interactions
	   	/*
		404-Data Model is read only
		406-Data type dismatch
		351-General set failure
		*/
	  	else if(DataModel.indexOf("cmi.interactions")!=-1){

			if(DataModel=="cmi.interactions._children"){
				lastError = 404; //402->404 read only
				return "false";
			}else if(DataModel=="cmi.interactions._count"){
				lastError = 404; //402->404 read only
				return "false";
			}

			var tempArray=DataModel.split(".");  //cmi.interactions.n.id
	          	if((tempArray[2]-n)==1){
	          		CmiInteractionsArray[eval(tempArray[2])]=new Array("InitialData","","","","","","","InitialData",-1,-1);
	                	n=new Number(tempArray[2]);
	               	 	c=CmiInteractionsArray[n][8]; //cmi.interactions.n.objectives._count = -1
	               	 	m=CmiInteractionsArray[n][9]; //cmi.interactions.n.correct_responses._count = -1
	           	}else if((tempArray[2]-n)>1){
				lastError = 351;
				return "false";
			}

			var p=Number(tempArray[2]);   //現在的index
			//cmi.interactions.xx
			//xx != _children && xx != _count
			//則Data Model Undefined

			if(tempArray.length<=3){
				lastError = 401; //Data Model undefined
				return "false";
			}

			//cmi.interactions.n.id
			if(tempArray[3]=="id" && tempArray.length==4){

				//Heroin 2004.05.12 data type暫時不檢查.....
				//testSuit1.3 上沒有限制type
				//CmiInteractionsArray[n][0]=Value;
				//lastError = 0;
				//return "true";

				//check urn

				if(Value.indexOf(":")!=-1){
					if(Value.indexOf(" ")==-1){
						var tempValueArray;
						tempValueArray = Value.split(":");
						if(tempValueArray.length==3){
							if(tempValueArray[0].toLowerCase()=="urn" && tempValueArray[2]!=""){
								CmiInteractionsArray[p][0]=Value;
								lastError = 0;
								return "true";
							}
						}
					}
					lastError=406;
					return "false";
				}else{
					if(Value!="" && Value.indexOf(" ")==-1){
						CmiInteractionsArray[p][0]=Value;
						lastError = 0;
						return "true";

					}
					lastError=406;
					return "false";
				}

		   	}


			//cmi.interactions.n.type
			if(tempArray[3]=="type" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]=="InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{
					var TypeParameterFound = "false";
					for(i=0;i<TypeArray.length;i++){
						if(Value == TypeArray[i]){
							TypeParameterFound = "true";
						}
					}
					if(TypeParameterFound == "true" && Value!="" && Value!=" "){
						CmiInteractionsArray[p][2]=Value;
						lastError = 0;
						return "true";
					}else{
						CmiInteractionsArray[p][2]="other";
						lastError = 406; //405->406
						return "false";
					}
				}
			}



			//cmi.interactions.n.objective.n.xxx
			if(tempArray[3]=="objectives"){

				if(tempArray.length==5){
					if(tempArray[4]=="_count"){  //cmi.interactions.n.objectives._count
						lastError = 404; // 404 read only
						return "false";
					}
				}

				if(tempArray.length==6 && tempArray[5]=="id"){ //cmi.interactions.n.objective.n.id

					if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]=="InitialData" || TypeArray == null || TypeArray == ""){
						lastError = 408 ;
						return "false";
					}
					//socool 2004.12.22 value = "" ,應該不能initial
					if(Value==""){
						//CmiInteractionsObjectivesArray[p][eval(tempArray[4])]="";
						lastError=406;
						return "false";
					}

					//var temp1Array=DataModel.split(".");
					//socool 2004.12.22 將string -> number
		                	if((Number(tempArray[4])-Number(c))==1 && (Number(tempArray[4])==0)) {

		                		CmiInteractionsObjectivesArray[p]=new Array();
		                   		c=tempArray[4];
		                   		CmiInteractionsArray[p][8]=c;
		                   		CmiInteractionsObjectivesArray[p][eval(tempArray[4])]="";
					}else if((tempArray[4]-c)<=1){
		                   		c=tempArray[4];
		                   		CmiInteractionsArray[p][8]=c;
					}else{
						lastError=351;
						return "false";
					}
					//alert("n = " +tempArray[2]+ "m= " + tempArray[4] + " after set CmiInteractionsArray[p][8]= " + CmiInteractionsArray[p][8]);//socool


					if(Value.indexOf(":")!=-1){
						if(Value.indexOf(" ")==-1){
							var tempValueArray;
							tempValueArray = Value.split(":");
							if(tempValueArray.length==3){
								if(tempValueArray[0].toLowerCase()=="urn" && tempValueArray[2]!=""){
									CmiInteractionsObjectivesArray[p][eval(tempArray[4])]=Value;
									lastError = 0;
									return "true";
								}
							}
						}

						lastError=406;
						return "false";
					}else{
						if(Value!="" && Value.indexOf(" ")==-1){
							//Yunghsiao 2005.08.09
							if(CmiInteractionsObjectivesArray[p][eval(tempArray[4])]!= null && (c>n)){
							lastError=351;
							return "false";
							//alert("value1 = " + CmiInteractionsObjectivesArray[p][eval(tempArray[4])]);
							}
							CmiInteractionsObjectivesArray[p][eval(tempArray[4])]=Value;
							//alert("value2 = " + CmiInteractionsObjectivesArray[p][eval(tempArray[4])]);
							lastError = 0;
							return "true";
						}
						lastError=406;
						return "false";
					}
				}
			}


	            	//cmi.interactions.n.timestamp
		        if(tempArray[3]=="timestamp" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{
					if(checkISOTime(Value).toString()=="true" && Value!="" && Value!=" "){
						CmiInteractionsArray[p][1]=Value;
						lastError = 0;
						return "true";
					}
					else{
						lastError = 406;
						return "false";
					}
				}
		   	}
	            	//cmi.interactions.0.correct_responses.0.pattern
			//cmi.interactions.n.correct_responses
	   	        if(tempArray[3]=="correct_responses"){
	   	        	//alert("correct_responses");

	   	        	if(tempArray.length==5){
	   	        		if(tempArray[4]=="_count"){
	   	        			lastError = 404; // read only
						return "false";
	   	        		}
	   	        	}

	   	        	//cmi.interactions.n.correct_responses.n.pattern
	   	        	if(tempArray.length==6 && tempArray[5]=="pattern"){
	   	        		//socool 2004.12.24
	   	        		var isUpdated = "false";
	   	        		m = CmiInteractionsArray[p][9];


	   	        		//if(tempArray[2].toString() == "8" ){
	   	        			//alert("array[2]=8 ; and array[4] = " + tempArray[4].toString() + " type =  " + CmiInteractionsArray[p][2]);
	   	        		//}



					if(CmiInteractionsArray[p][2] == "" || CmiInteractionsArray[p][0]=="InitialData" || CmiInteractionsArray[p][0] == "" || TypeArray == null || TypeArray == ""){
						lastError = 408 ;
						return "false";
					}


					if(CmiInteractionsArray[eval(tempArray[2])][2]=="true-false" && tempArray[4]>=1){
	                  			lastError = 351; //201->351
	                  			return "false";
	                	 	}else if((tempArray[4]-m)==1 && tempArray[4]==0){
						CmiInteractionsCorrect_ResponsesArray[p]=new Array("");
	                  			m=tempArray[4];
	                  			//socool 2001.12.22 mark
	                   			//CmiInteractionsArray[p][9]=m;
	                   		//Vega 2004.9.24 Add
	                   			CmiInteractionsCorrect_ResponsesArray[p][m]="InitialData";
					}else if((tempArray[4]-m)==1){ //in start
	                  			m=tempArray[4];
	                  			//socool 2001.12.22 mark
	                   			//CmiInteractionsArray[p][9]=m;
	                   			CmiInteractionsCorrect_ResponsesArray[p][m]="InitialData";
	                   		//-------------------------------------------------------
					}else if((tempArray[4]-m)<=1){
						m=tempArray[4];
						isUpdated = "true";
	        	           		//socool 2001.12.22 mark
	        	           		//CmiInteractionsArray[p][9]=m;
	                	 	}else{
	                  			lastError = 351; //201->351
	                  			return "false";
		                 	}



		                 	//if(tempArray[5]=="pattern"){
	                 			//-------correct_response_type---------
						if(CmiInteractionsArray[p][2]=="true-false"){
							if(Value=="true" || Value=="false"){
								CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
								lastError = 0;
								//2004.12.22 socool added
								if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
								}
								return "true";
							}
							else{
								lastError = 406;
								//CmiInteractionsArray[p][9]= CmiInteractionsArray[p][9] - 1;
								return "false";
							}
						}
						else if(CmiInteractionsArray[p][2]=="choice"){

							//alert("set correctResponse n="+n+"  m="+m+" type="+CmiInteractionsArray[p][2]+" Value="+Value);
							var sArray=Value.split("[,]");
							if(sArray.length!=0){
								var temp_flag="true";
								if(Value.indexOf("{")!=-1 || Value.indexOf("}")!=-1){

										temp_flag="false";
										lastError=406;
										return "false";
								}
								//Vega 2004.9.23 marked choice可以空白,代表沒有選項
								if(Value=="" ){

									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
									}
									return "true"
								}
								//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
								for(var i=0;i<m;i++){
									if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
										lastError = 351; //201->351
			                  					return "false";
									}
								}
								//----------------------------------------------------------
								for(var i=0;i<sArray.length;i++){

									if(sArray[i].length <= 500){
										temp_flag="true";
									}else{
										temp_flag="false";
										lastError=406;
										return "false";
									}
									if(sArray[i]=="" || sArray[i]==null ){
										temp_flag="false";
										lastError=406;
										return "false";
									}
								}


								//check unique
								for(var q1=0;q1<sArray.length;q1++){
									for(var q2=0;q2<sArray.length;q2++){
										if(q1!=q2 && sArray[q1]==sArray[q2]){
											lastError = 406;
											return "false";
										}
									}
								}

								if(temp_flag=="true"){
									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
									}
									return "true"
								}
								else{
									lastError = 406;
									return "false";
								}
							}
							//Vega 2004.9.23 marked
							/*}
							else{
								if(String(Value).length <= 500){
									lastError = 0;
									return "true";
								}else{
									lastError = 406;
									return "false";
								}
							}*/
							//alert("choice m="+m+ " / value="+ CmiInteractionsCorrect_ResponsesArray[p][m]);
						}else if(CmiInteractionsArray[p][2]=="fill-in"){

							//socool 2004.12.23 testsuite 1.3.2測試結果可以空白,代表沒有選項
								if(Value=="" ){

									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.23 socool added
									if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
									}
									return "true"
								}

							//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
							for(var i=0;i<m;i++){
								if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
									//alert("fill-in p="+p+"/m="+m+ " / value="+ CmiInteractionsCorrect_ResponsesArray[p][i]);
									lastError = 351; //201->351
		                  					return "false";
								}
							}
							//----------------------------------------------------------
							var fillinArray=checkFillin(Value);
							fillinArray[1]="true";
							if(fillinArray[1]=="false"){
								lastError = 406;
								return "false";
							}else{

								//var fillin=checkFillinType(sArray[i]);
								var sArray=Value.split("[,]");

								if(sArray.length!=0){
									var temp_flag="true";
									for(var i=0;i<sArray.length;i++){
										var fillin=checkFillin2(sArray[i],Value);
										if(fillin=="false"){
											lastError=406;
											return "false";
										}
										//alert("fillin result="+fillin);
										if(sArray[i].length <= 500){
											temp_flag="true";
										}else{
											temp_flag="false";
											lastError=406;
											return "false";
										}
										//Vega 2004.9.24 Modified
										//if(sArray[i]=="" || sArray[i]==null ){
										if(String(sArray[i])==null ){
											temp_flag="false";
											lastError=406;
											return "false";
										}
									}
									/*
									for(var q1=0;q1<sArray.length;q1++){
										for(var q2=0;q2<sArray.length;q2++){
											if(q1!=q2 && sArray[q1]==sArray[q2]){
												lastError = 406;
												return "false";
											}
										}
									}
									*/
									if(temp_flag=="true"){
										CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true"
									}
									else{
										lastError = 406;
										return "false";
									}
								}
								else{
									var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError = 406;
										return "false";
									}
									if(String(Value).length <= 500){
										lastError = 0;
										//2004.12.22 socool added
										CmiInteractionsArray[p][9]=m;
										return "true";
									}else{
										lastError = 406;
										return "false";
									}
								}

							}



						}
						else if(CmiInteractionsArray[p][2]=="long-fill-in"){

							/* socool 2004.12.23 testsuite 1.3.2測試結果可以空白,代表沒有選項  */
								if(Value=="" ){

									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.23 socool added
									if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
									}
									return "true"
								}

							//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
							for(var i=0;i<m;i++){
								if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
									lastError = 351; //201->351
		                  					return "false";
								}
							}
							//socool 2004.12.23 marked
							/*
							if(Value==""){
								lastError = 406;
								return "false";
							}
							*/
							//----------------------------------------------------------
							var fillinArray=checkFillin(Value);
							fillinArray[1]="true";
							if(fillinArray[1]=="false"){
								lastError = 406;
								return "false";
							}else{

								var sArray=Value.split("[,]");
								if(sArray.length!=0){
									var temp_flag="true";
									for(var i=0;i<sArray.length;i++){
										var fillin=checkFillin2(sArray[i],Value);
										if(fillin=="false"){
											lastError=406;
											return "false";
										}
										if(sArray[i].length <= 8000){
											temp_flag="true";
										}else{
											temp_flag="false";
											lastError=406;
											return "false";
										}
										//Vega 2004.9.27 unmarked
										if(sArray[i]==null ){
											temp_flag="false";
											lastError=406;
											return "false";
										}
									}
									/*for(var q1=0;q1<sArray.length;q1++){
										for(var q2=0;q2<sArray.length;q2++){
											if(q1!=q2 && sArray[q1]==sArray[q2]){
												lastError = 406;
												return "false";
											}
										}
									}*/
									if(temp_flag=="true"){
										CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true"
									}
									else{
										lastError = 406; //405->406
										return "false";
									}
								}
								else{
									var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError = 406;
										return "false";
									}
									//if(String(Value).length <= 8000){
									//Vega 2004.9.27 modified TS 1.3.1
									if(String(Value).length <= 8000 && String(Value).length>0){
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true";
									}else{
										lastError = 406; //405->406
										return "false";
									}
								}


							 }
						}
						else if(CmiInteractionsArray[p][2]=="numeric"){
							var tempValueArray=Value.split("[:]");
							if(m>=1){
								lastError = 351;
								return "false";

							}

							if((!isNaN(tempValueArray[0]) || tempValueArray[0]=="") && (!isNaN(tempValueArray[1]) || tempValueArray[1]=="")){
								if(Number(tempValueArray[1])<Number(tempValueArray[0])){
									lastError = 406; //405->406
									return "false";
								}else{
									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
									}
									return "true";
								}
							}
							else{
								lastError = 406; //405->406
								return "false";
							}
						}
						else if(CmiInteractionsArray[p][2]=="likert"){
							/*var tempValueArray;
							tempValueArray=Value.split(":")
							if(tempValueArray[0].toString()=="urn" && tempValueArray.length==3){
								CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
								lastError = 0;
								return "true";
							}
							else{
								lastError = 406; //405->406
								alert("406");
								return "false";
							}
							*/



							//check uri 特殊符號
							//要查查看哪些是不能用的
							//Heroin 2004.06.14
							if(Value.indexOf("{")!=-1 || Value.indexOf("}")!=-1 || Value.indexOf("[")!=-1 || Value.indexOf("]")!=-1){
								lastError = 406; //405->406
								return "false";
							}



							CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
							lastError = 0;
							//2004.12.22 socool added
							if(isUpdated == "false"){
								CmiInteractionsArray[p][9]=m;
							}
							return "true";
						}
						else if(CmiInteractionsArray[p][2]=="matching"){
							//socool 2004.12.23 1.3.2 TS should marked
							/*
							//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
							for(var i=0;i<m;i++){
								if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
									lastError = 351; //201->351
		                  					return "false";
								}
							}
							*/
							//----------------------------------
							var reg=/^[0-9a-z]\.[0-9a-z]$/;
							var sArray=Value.split("[,]");
							if(sArray.length!=0){
								var temp_flag="true";
								for(var i=0;i<sArray.length;i++){
									if(sArray[i].length<=1000 && sArray[i].length > 0){
										if(sArray[i]==""){
											temp_flag="false";
											break;
										}
										temp_flag=checkMatching(sArray[i]);
										if(temp_flag=="false"){
											break;
										}
									}else{
										temp_flag="false";
										break;
									}
								}
								if(temp_flag=="true"){
									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
										CmiInteractionsArray[p][9]=m;
									}
									return "true";
								}
								else{
									lastError = 406; //405->406
									return "false";
								}
							}
							else{
								temp_flag=checkMatching(Value);
								if(temp_flag=="false"){
									lastError=406;
									return "false";
								}else{
									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
										CmiInteractionsArray[p][9]=m;
									}
									return "true";
								}
							}
						}
						else if(CmiInteractionsArray[p][2]=="performance"){
							//socool 2004.12.23 1.3.2 TS should marked
							/*
							//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
							for(var i=0;i<m;i++){
								if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
									lastError = 351; //201->351
		                  					return "false";
								}
							}
							*/
							//-------------------------
								var tempValue=Value;
								if(Value.indexOf("{order_matters=")==0){

									var tempA=Value;
									var tempB=tempA.indexOf("{order_matters=");
									var tempC=tempA.substring(15,tempA.length);
									var tempD=tempC.indexOf("}");
									var order_matters=tempC.substring(0,tempD);
									if(order_matters.toString()!="true" && order_matters.toString()!="false"){
										lastError = 406;
										return "false";
									}else{
										tempValue=Value.substring(16+tempD,Value.length);

									}
								}

								var sArray=tempValue.split("[,]");
								if(sArray.length!=0){
									var temp_flag="true";
									for(var i=0;i<sArray.length;i++){
										if(String(sArray[i])==""){
											temp_flag="false";
											break;
										}
										temp_flag=checkPerformance(sArray[i]);
										if(temp_flag=="false"){
											break;
										}
									}
									if(temp_flag=="true"){
										CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true";
									}
									else{
										lastError = 406; //405->406
										return "false";
									}
								}
								else{
									var temp_flag=checkPerformance(tempValue);
									if(temp_flag=="false"){
										lastError = 406;
										return "false";
									}else{
										CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true";
									}
								}

						}
						else if(CmiInteractionsArray[p][2]=="sequencing"){


							//Vega 2004.9.24 -- 1.3.1 TS correct response set should be unique
							for(var i=0;i<m;i++){
								if(Value== CmiInteractionsCorrect_ResponsesArray[p][i]){
									lastError = 351; //201->351
		                  					return "false";
								}
							}
							//----------------------------------------------------------
							var reg=/^[0-9a-z]$/;
							var sArray=Value.split("[,]");
							if(sArray.length!=0){
								//socool 2004.12.23 TS1.3.2 value != "[,]" and value != " "
								/*
								//Vega 2004.9.27 added
								if(Value=="[,]"){
									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									CmiInteractionsArray[p][9]=m;
									return "true";
								*/
								if((Value=="[,]") || (Value==" ")){
									lastError = 406;
									return "false";

								}else{
									var temp_flag="true";
									for(var i=0;i<sArray.length;i++){
										//Vega 2004.9.24 modified
										//if(String(sArray[i])==""){
										if(sArray[i]=="" || sArray[i]==null){
										   temp_flag="false";
										   break;
										}

										if(String(sArray[i]).indexOf("{")!=-1 || String(sArray[i]).indexOf("}")!=-1 || String(sArray[i]).indexOf("[")!=-1 || String(sArray[i]).indexOf("]")!=-1){
											temp_flag="false";
											break;
										}

									}
									if(temp_flag=="true"){
										CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
										lastError = 0;
										//2004.12.22 socool added
										if(isUpdated == "false"){
											CmiInteractionsArray[p][9]=m;
										}
										return "true";
									}
									else{
										lastError = 406;
										return "false";
									}
								}

							}else{
								if(Value=="" || Value==null){
									lastError = 406;
									return "false";
								}else if(String(Value).indexOf("{")!=-1 || String(Value).indexOf("}")!=-1 || String(Value).indexOf("[")!=-1 || String(Value).indexOf("]")!=-1){
									lastError = 406;
									return "false";
								}else{

									CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
									lastError = 0;
									//2004.12.22 socool added
									if(isUpdated == "false"){
										CmiInteractionsArray[p][9]=m;
									}
									return "true";
								}
							}
						}
						else if(CmiInteractionsArray[p][2]=="other"){
							if((new String(Value)).length <= 8000 && Value!="" && Value!=" "){
								CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
								lastError = 0;
								//2004.12.22 socool added
								if(isUpdated == "false"){
									CmiInteractionsArray[p][9]=m;
								}
								return "true";
							}else{
								lastError = 406; //405->406
								return "false";
							}
						}
	        	         	//}
	   	        	}
			}

			//cmi.interactions.n.weighting
	         	if(tempArray[3]=="weighting" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{

					if(!isNaN(Value) && Value!="" && Value!=" "){
						CmiInteractionsArray[p][3]=Value;
						lastError = 0;
						return "true";
	   	            		}
	   	            		else{
	   	              	  		lastError = 406; //405->406
						return "false";
	   	            		}
				}
	  		}

			//cmi.interactions.n.learner_response  learner_response
	        	if(tempArray[3]=="learner_response" && tempArray.length==4){
				var temp1Array=DataModel.split(".");
		            	if(temp1Array[2]<=n){
		            		n=temp1Array[2];
	        	    	}else{
	               			lastError = 351; //201->351
	           			return "false";
				}

				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || CmiInteractionsArray[p][2] == "" ||TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{
	                	//learner_response_type
					if(CmiInteractionsArray[p][2]=="true-false"){
						if(Value=="true" || Value=="false"){
							CmiInteractionsArray[p][4]=Value;
							lastError = 0;
							return "true";
						}else{
							lastError = 406;
							return "false"
						}
					}
					else if(CmiInteractionsArray[p][2]=="choice"){
						var sArray=Value.split("[,]");
						if(Value.indexOf("{")!=-1 || Value.indexOf("}")!=-1){

								temp_flag="false";
								lastError=406;
								return "false";
						}
						if(sArray.length!=0){
							var temp_flag="true";
							for(var i=0;i<sArray.length;i++){

								if(sArray[i].length <= 500){
									temp_flag="true";
								}else{
									temp_flag="false";
									lastError=406;
									return "false";
								}
								if(sArray[i]=="" || sArray[i]==null ){
									temp_flag="false";
									lastError=406;
									return "false";
								}
							}

							//check unique
								for(var q1=0;q1<sArray.length;q1++){
									for(var q2=0;q2<sArray.length;q2++){
										if(q1!=q2 && sArray[q1]==sArray[q2]){
											lastError = 406;
											return "false";
										}
									}

								}


							if(temp_flag=="true"){
								CmiInteractionsArray[p][4]=Value;
								lastError = 0;
								return "true";
							}
							else{
								lastError = 406; //405->406
								return "false";
							}
						}else{

							if(String(Value).length <= 500){
									lastError = 0;
									return "true";
							}else{
									lastError = 406; //405->406
									return "false";
							}
						}
					}
					else if(CmiInteractionsArray[p][2]=="fill-in"){
						//Vega 2004.9.30

						/* socool 2004.12.23 testsuit 1.3.2 測試時value = "",仍要ture 才行. */
						if((Value=="[,]") || (Value =="")){
							CmiInteractionsArray[p][4]=Value;
							lastError = 0;
							return "true";
						}
						//--------
						var fillinArray=checkFillin(Value);
						fillinArray[1]="true";
						if(fillinArray[1]=="false"){
							lastError = 406;
							return "false";
						}else{

							var sArray=Value.split("[,]");
							if(sArray.length!=0){
							var temp_flag="true";
								for(var i=0;i<sArray.length;i++){
									var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError=406;
										return "false";
									}
									if(sArray[i].length <= 500){
										temp_flag="true";
									}else{
										temp_flag="false";
										lastError=406;
										return "false";
									}
									if(sArray[i]=="" || sArray[i]==null ){
										temp_flag="false";
										lastError=406;
										return "false";
									}
								}
								/*for(var q1=0;q1<sArray.length;q1++){
									for(var q2=0;q2<sArray.length;q2++){
										if(q1!=q2 && sArray[q1]==sArray[q2]){
											lastError = 406;
											return "false";
										}
									}
								}*/
								if(temp_flag=="true"){
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true"
								}
								else{
									lastError = 406;
									return "false";
								}
							}
							else{
								var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError = 406;
										return "false";
									}
								if(String(Value).length <= 500){
									lastError = 0;
									return "true";
								}else{
									lastError = 406;
									return "false";
								}
							}

						}

					}
					else if(CmiInteractionsArray[p][2]=="long-fill-in"){
						/* socool 2004.12.23 testsuit 1.3.2 測試時value = "",仍要ture 才行. */
						if((Value=="[,]") || (Value =="")){
							CmiInteractionsArray[p][4]=Value;
							lastError = 0;
							return "true";
						}
						//--------
						var fillinArray=checkFillin(Value);
						fillinArray[1]="true";
						if(fillinArray[1]=="false"){
							lastError = 406;
							return "false";
						}else{
							var sArray=Value.split("[,]");
							if(sArray.length!=0){
								var temp_flag="true";
								for(var i=0;i<sArray.length;i++){
									var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError=406;
										return "false";
									}
									if(sArray[i].length <= 8000){
										temp_flag="true";
									}else{
										temp_flag="false";
										lastError=406;
										return "false";
									}
									if(sArray[i]=="" || sArray[i]==null ){
										temp_flag="false";
										lastError=406;
										return "false";
									}
								}

								if(temp_flag=="true"){
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true"
								}
								else{
									lastError = 406; //405->406
									return "false";
								}
							}
							else{


								var fillin=checkFillin2(sArray[i],Value);
									if(fillin=="false"){
										lastError = 406;
										return "false";
									}
								if(String(Value).length <= 8000){
									lastError = 0;
									return "true";
								}else{
									lastError = 406; //405->406
									return "false";
								}
							}
						}

					}
					else if(CmiInteractionsArray[p][2]=="numeric"){
						if(!isNaN(Value)){
							CmiInteractionsArray[p][4]=Value;
							lastError = 0;
							return "true";
						}else{
							lastError = 406;
							return "false";
						}
					}
					else if(CmiInteractionsArray[p][2]=="likert"){
						/*var tempValueArray;
						tempValueArray=Value.split(":")
						if(tempValueArray[0].toString()=="urn" && tempValueArray.length==3){
							CmiInteractionsCorrect_ResponsesArray[p][m]=Value;
							lastError = 0;
							return "true";
						}else{
							lastError = 406; //405->406
							return "false";
						}*/
						//CmiInteractionsCorrect_ResponsesArray[p][m]=Value;




						//check uri 特殊符號
						//要查查看哪些是不能用的
						//Heroin 2004.06.14
						if(Value.indexOf("{")!=-1 || Value.indexOf("}")!=-1 || Value.indexOf("[")!=-1 || Value.indexOf("]")!=-1){
							lastError = 406; //405->406
							return "false";
						}


						CmiInteractionsArray[p][4]=Value;
						lastError = 0;
						return "true";
					}
					else if(CmiInteractionsArray[p][2]=="matching"){
						var reg=/^[0-9a-z]\.[0-9a-z]$/;
						var sArray=Value.split("[,]");
						if(sArray.length!=0){
							var temp_flag="true";
							for(var i=0;i<sArray.length;i++){
								if(sArray[i].length<=1000 && sArray[i].length > 0){
									if(sArray[i]==""){
										temp_flag="false";
										break;
									}
									temp_flag=checkMatching(sArray[i]);
									if(temp_flag=="false"){
										break;
									}
								}else{
									temp_flag="false";
									break;
								}
							}
							if(temp_flag=="true"){
								CmiInteractionsArray[p][4]=Value;
								lastError = 0;
								return "true";
							}
							else{
								lastError = 406; //405->406
								return "false";
							}
						}
						else{

							temp_flag=checkMatching(Value);
							if(temp_flag=="false"){
								lastError=406;
								return "false";
							}else{
								CmiInteractionsArray[p][4]=Value;
								lastError = 0;

								return "true";
							}
						}
					}
					else if(CmiInteractionsArray[p][2]=="performance"){

								var tempValue=Value;
								if(Value.indexOf("{order_matters=")==0){

									var tempA=Value;
									var tempB=tempA.indexOf("{order_matters=");
									var tempC=tempA.substring(15,tempA.length);
									var tempD=tempC.indexOf("}");
									var order_matters=tempC.substring(0,tempD);
									if(order_matters.toString()!="true" && order_matters.toString()!="false" ){
										lastError = 406;
										return "false";
									}else{
										tempValue=Value.substring(16+tempD,Value.length);
									}
								}

							var sArray=tempValue.split("[,]");
							if(sArray.length!=0){
								var temp_flag="true";
								for(var i=0;i<sArray.length;i++){
									if(String(sArray[i])==""){
										temp_flag="false";
										break;
									}
									temp_flag = checkPerformance(sArray[i]);
									if(temp_flag=="false"){
										break
									}
								}
								if(temp_flag=="true"){
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true";
								}else{
									lastError = 406; //405->406
									return "false";
								}
							}else{
								var temp_flag=checkPerformance(tempValue);
								if(temp_flag=="false"){
									lastError = 406;
									return "false";
								}else{
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true";
								}
							}

					}
					else if(CmiInteractionsArray[p][2]=="sequencing"){
						//socool 2004.12.24 base TS1.3.2 log
						if(Value=="[,]" || Value=="" ||Value==" "){

							lastError = 406;
							return "false";
						}else{
						//----------------------------
							var reg=/^[0-9a-z]$/;
							var sArray=Value.split("[,]");
							if(sArray.length!=0){
								var temp_flag="true";
								for(var i=0;i<sArray.length;i++){
									//Vega 2004.9.24 modified
									//if(String(sArray[i])==""){
									if(sArray[i]==null || sArray[i]==""){
									   temp_flag="false";
									   break;
									}
									if(String(sArray[i]).indexOf("{")!=-1 || String(sArray[i]).indexOf("}")!=-1 || String(sArray[i]).indexOf("[")!=-1 || String(sArray[i]).indexOf("]")!=-1){
										temp_flag="false";
										break;
									}
								}
								if(temp_flag=="true"){
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true";
								}else{
									lastError = 406;
									return "false";
								}
							}else{
								if(String(Value)==""){
									lastError = 406;
									return "false";
								}else if(String(Value).indexOf("{")!=-1 || String(Value).indexOf("}")!=-1 || String(Value).indexOf("[")!=-1 || String(Value).indexOf("]")!=-1){
									lastError = 406;
									return "false";
								}else{
									CmiInteractionsArray[p][4]=Value;
									lastError = 0;
									return "true";
								}
							}
						}
					}
					else if(CmiInteractionsArray[p][2]=="other"){
						if((new String(Value)).length <= 8000 && Value!="" && Value!=" "){
							CmiInteractionsArray[p][4]=Value;
							lastError = 0;
							return "true";
						}
						else{
							lastError = 406; //405->406
							return "false";
						}
					}
				}
			}

			//cmi.interactions.n.result
	   	    	if(tempArray[3]=="result" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{

					var ResultParameterFound = "false";
					//alert("set result Value="+Value+" isNaN="+isNaN(Value));
					for(i=0;i<ResultArray.length;i++){
						if(Value == ResultArray[i]){
							ResultParameterFound = "true";
						}else if(!isNaN(Value)){
							ResultParameterFound = "true";
						}
					}
					if(ResultParameterFound == "true" && Value!="" && Value!=" "){
						CmiInteractionsArray[p][5]=Value;
						lastError = 0;
						return "true";
					}else{
						lastError = 406;
						return "false";
					}
				}
	   	    	}

			//cmi.interactions.n.latency
	   	   	if(tempArray[3]=="latency" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{
					if(checkISOTimespan(Value)=="true"){
						CmiInteractionsArray[p][6]=Value;
						lastError = 0;
						return "true";
					}
					else{
						lastError = 406;
						return "false";
	   				}
				}
	   	   	}
			//cmi.interactions.n.description
	   	   	if(tempArray[3]=="description" && tempArray.length==4){
				if(CmiInteractionsArray[p][0] == "" || CmiInteractionsArray[p][0]== "InitialData" || TypeArray == null || TypeArray == ""){
					lastError = 408 ;
					return "false";
				}else{
					var tempValue=Value;
					if(tempValue.indexOf("{lang=")!=-1){
						var tempA=tempValue.indexOf("{lang=");
						var tempB=tempValue.indexOf("}");
						var lanCode=tempValue.substring(tempA+6,tempB);

						var checkeType=checkISOLang(lanCode);
						if(checkeType.toString()=="false"){
							lastError = 406;
							return"false"
						}
					}
					CmiInteractionsArray[p][7]=Value;
					lastError = 0;
					return "true";

				}
	   	    	}
			lastError = 401;
			return "false";
		}












	   	else if(DataModel.indexOf("adl.nav")!=-1){
	   	//SCORM 1.3 Working Draft 1 Edit by Henry
	   	if(DataModel=="adl.nav.request"){
	              var ADLNavRequestParameterFound = "false";
	              for(i=0;i<ADLNavRequestArray.length;i++){
				var tempValueString = Value.toLowerCase();
				var tempRequestString = ADLNavRequestArray[i].toLowerCase();
				if(tempValueString.indexOf(tempRequestString)!=-1){
					ADLNavRequestParameterFound = "true";
				}
	              }
		     // alert("adl nav request setting ...ADLNavRequestParameterFound = " + ADLNavRequestParameterFound + " / Value = " + Value );
		      if(ADLNavRequestParameterFound.toString()=="true"){
		      		//alert("!!! "+DataModel);

		      		if(Value.indexOf("choice")!=-1){
		      			//如果是choice,要檢查有沒有delimiter --> {target=URL}   SetValue("adl.nav.request", "{target=activity_3}choice")

		      			if( (Value.indexOf("{")!=-1) && (Value.indexOf("}")!=-1) && (Value.indexOf("=")>Value.indexOf("{")) ){
		      				//如果有delimiter,就將值傳入
		      				adl_nav_request = Value;
		      				lastError = 0;
		      				return "true";
		      			}else{
		      				//alert("!!");
		      				//如果沒有delimiter
		      				lasError = 406;
		      				return "false";
		      			}

		      		}else{

		      			//如果不是choice
		      			//alert("adl.nav.request is set by " + Value);
			      		adl_nav_request = Value;
			      		//alert("!!! adl_nav_request="+adl_nav_request );
			      		lastError = 0;
		      		}

		      }else{
		      		//alert("!!!!");
				lastError = 406;
				return "false";
		      }

		}else if(DataModel=="adl.nav.request_valid.continue"){
	   		lastError = 404;
	   		return "false";

	   	}else if(DataModel=="adl.nav.request_valid.previous"){
			lastError = 404;
			return "false";

	   	}else if(DataModel.indexOf("adl.nav.request_valid.choice")!=-1){
			lastError = "404";
			return "false";

	   	}



	   	}else if(DataModel.indexOf("cmi.comments_from_learner")!=-1){
	                var tempArray=DataModel.split(".");
	           //     alert("DataModel="+DataModel+" tempArray.length="+tempArray.length);
	           //     alert("tempArray[2]="+tempArray[2]);

	                if((tempArray[2]-CommentsFromLearnerCount)==1){
	                   CmiCommentsFromLearnerArray[eval(tempArray[2])]=new Array("","","");
	                   CommentsFromLearnerCount=Number(tempArray[2]);
	                   CommentsFromLearnerIndex=CommentsFromLearnerCount;
	                   CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][0] = "null";
	                   CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][1] = "null";
	                   CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][2] = "null";


	                }else{
				if((tempArray[2]-CommentsFromLearnerCount)>1){
					lastError = 351; //General set failure
					return "false";
				}else if(tempArray[2] <= CommentsFromLearnerCount){
					CommentsFromLearnerIndex = Number(tempArray[2]);
				}
	                }

	               var StrDataModelCheck = DataModel.slice(DataModel.indexOf("learner"));
	                //因為cmi.comments_from_learner.n.comment中有兩個comment,所以先把他


	               if(StrDataModelCheck.indexOf("comment")!=-1){


				//cmi.comment_from_learner.n.comment
				//SPM 4096 --> double --> 8192
				//error code 351 先不管
				//error code 406 如果超過 SPM 的兩倍

				//先check字串是否為unicode的格式..
				//如果不是就把他轉換為unicode的格式
				//目前先不將其轉換成unicode
				//if(!checkUnicode(Value)){
				//	Value = escape(Value);
				//}
				if(Value.length <= 8000 ){
					//alert("CommentsFromLearnerIndex="+CommentsFromLearnerIndex+" CmiCommentsFromLearnerArray.length="+CmiCommentsFromLearnerArray.length);
					//Heroin 2004.05.11 check data type
					//{lang=<aaa>}<abc>
					//checkISOLang
					var tempValue=Value;

					if(tempValue.indexOf("{lang=")!=-1){
						var tempA=tempValue.indexOf("{lang=");
						var tempB=tempValue.indexOf("}");
						var lanCode=tempValue.substring(tempA+6,tempB);
						//Modified by Vega 2004.8.27
						var commnetString=tempValue.substring(tempB+1,tempValue.length);
						//檢查comment是否為空值
						//socool 2004.12.21
						/*
						if(commnetString==""){
							//alert("mark1");//socool
							lastError = 406;
							return"false";
						}
						*/

						//alert("tempValue="+tempValue+" lanCode="+lanCode); //socool
						var checkeTime=checkISOLang(lanCode);
						//alert("checkeTime="+checkeTime);
						if(checkeTime.toString()=="false"){
							//alert("mark2");//socool
							lastError = 406;
							return"false"
						}
					}

					CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][0]=Value;
					lastError = 0;
					return "true";
				}else{
					//alert("mark3");//socool
					lastError = 406;
					return "false";
				}
	   	        }else if(StrDataModelCheck.indexOf("location")!=-1){
				//cmi.comment_from_learner.n.location
				//SPM 255 --> double --> 512
				//目前先不將其轉換成unicode
				//if(!checkUnicode(Value)){
				//	Value = escape(Value);
				//}


				//if(Value.length <= 500){

					CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][1]=Value;
					lastError = 0;
					return "true";
				//}else{
				//	lastError = 406;
				//	return "false";
				//}
			//Modified by Vega 2004.8.27 DM
	   		}else if(StrDataModelCheck.indexOf("timestamp")!=-1){
	   		//}else if(StrDataModelCheck.indexOf("date_time")!=-1){
				//cmi.comment_from_learner.n.date_time
				//SPM 255 --> double --> 512


				if(checkISOTime(Value)){

					CmiCommentsFromLearnerArray[CommentsFromLearnerIndex][2]=Value;
					lastError = 0;
					return "true";
				}else{
					lastError = 406;
					return "false";
				}

	   	        }else if(StrDataModelCheck.indexOf("_children")!=-1){
				//Set --> cmi.comment_from_learner._children --> Error
				lastError = 404;
				return "false";
			}else if(StrDataModelCheck.indexOf("_count")!=-1){
				//Set --> cmi.comment_from_learner._count --> Error
				lastError = 404;
				return "false";
			}else{
				lastError = 401;
				return "false";
			}



	   	}else if(DataModel.indexOf("cmi.comments_from_lms")!=-1){
	                /*
	                var tempArray=DataModel.split(".");
	                if((tempArray[2]-CommentsFromLmsIndex)==1){
	                   	CmiCommentsFromLmsrArray[eval(tempArray[2])]=new Array("","","");
	                   	CommentsFromLmsIndex=Number(tempArray[2]);
	                }
	                alert(DataModel);
	                */
	                var StrDataModelCheck = DataModel.slice(DataModel.indexOf("lms"));
	                //因為cmi.comments_from_lms.n.comment中有兩個comment,所以先把他

	                //如果都是read-only要怎麼把值設進去呢???

	                if(StrDataModelCheck.indexOf("comment")!=-1){
				//cmi.comment_from_lms.n.comment --> read-only
				lastError = 404;
				return "false";

	   	        }else if(StrDataModelCheck.indexOf("location")!=-1){
				//cmi.comment_from_lms.n.location --> read-only
				lastError = 404;
				return "false";
	   		//Modified by Vega 2004.8.27 DM
	   		}else if(StrDataModelCheck.indexOf("timestamp")!=-1){
	   		//}else if(StrDataModelCheck.indexOf("date_time")!=-1){
				//cmi.comment_from_learner.n.date_time --> read-only
				lastError = 404;
				return "false";

	   	        }else if(StrDataModelCheck.indexOf("_children")!=-1){
				//Set --> cmi.comment_from_learner._children --> Error
				lastError = 404;
				return "false";
			}else if(StrDataModelCheck.indexOf("_count")!=-1){
				//Set --> cmi.comment_from_learner._count --> Error
				lastError = 404;
				return "false";
			}else{
				lastError = 401;
				return "false";
			}


	   	}else if(DataModel.indexOf("cmi._version")!=-1){
	   		lastError = 404;
			return "false";
		}else{  // cmi.core

			// change DM to SCORM 1.3 WD
		   	// Heroin 2004.01.12
		   	// Vega 2004.07.29 marked
		   	//DataModel=changeDM(DataModel);
			// 404: write-only
			// 406: wrong type
			// 407: out of range
			//alert("DataModel="+DataModel);
			switch (DataModel){

				//----3-----

				case "cmi.credit":
				      lastError = 404;
				      return "false";
				      break;
				case "cmi.entry":
				      lastError = 404;
				      return "false";
				      break;
				case "cmi.exit":

		                      var CoreExitParameterFound = "false";
		                      for(i=0;i<CoreExitArray.length;i++){
						if(Value == CoreExitArray[i]){
							CoreExitParameterFound = "true";
						}
		                      }
		                      if(CoreExitParameterFound=="true"){
			                      cmi_exit = Value;
			                      //adl_nav_request = "_none_";
			                      //Henry 2004.06.01
			                      if(Value=="time-out"){
			                      		//adl_nav_request = "exit_all";
			                      }else if(Value=="suspend"){
			                      		activityStatusList[tocIndex].ActivityisSuspended = true;
			                      }else if(Value=="logout"){
			                      		//adl_nav_request = "suspend_all";
			                      }

			                      lastError = 0;
			                      return "true";
			              }else{
			              	      lastError=406;
			              	      return "false";
			              }
		                     break;

				//----5-----
				case "cmi.launch_data":
				      lastError = 404;
				      return "false";
				      break;
				case "cmi.learner_id":
				      lastError = 404;
				      return "false";
				      break;
				case "cmi.learner_name":
				      lastError = 404;
				      return "false";
				      break;


				//----6-----
				case "cmi.learner_preference._children":
				      lastError = 404;
				      return "false";
				      break;
				//Henry 2004.05.28
				//-------cmi.learner_pre這個真是非常奇怪的data model在spec裡沒有看過這個東東
				//Vega 2004.9.24 Modified  ---- it might be a mistake in ver1.3.0,and in ver1.3.1 has modified to return error code "401"
				case "cmi.learner_preference":
					//cmi_learner_preference = Value;
					//lastError = 0;
					lastError = 401;
					break;
				case "cmi.learner_preference.audio_level":
				      //cmi.learner_preference.audio_level
				      //Heroin
				      if( !isNaN(Number(Value)) && Value!=" " && Value!=""){ //if value is not a real number

					      if( Value>=0){//not greater than......error code 407...應該是>就好了....不應該是>=
			                      		cmi_learner_preference_audio_level = Value;
							lastError = 0;
							return "true";
					      }else{
			                    		//cmi_learner_preference_audio_level = Value;
					      		lastError = 407;
					      		return "false";
					      }
				      }else{
				      	      lastError = 406;
				      	      return "false";
				      }
				      break;
				case "cmi.learner_preference.language":
					//cmi.learner_preference.language
			              	//Vega 2004.9.24 Added ---empty string is legal value 1.3.1
			              	if(Value==""){
			              		cmi_learner_preference_language = Value;
			              		lastError = 0;
			              		return "true";
			              	}
			              	if(checkISOLang(Value)=="true"){
			              		cmi_learner_preference_language = Value;
			              		lastError = 0;
			              		return "true";
			              	}else{
			              		//cmi_learner_preference_language = Value;
						lastError = 406;
						return "false";
			              	}
			             	break;
				case "cmi.learner_preference.delivery_speed":
					// == cmi.learner_preference.delivery_speed
					if( !isNaN(Number(Value)) && Value!=" " && Value!=""){ //if value is not a real number
						if(Value>=0){//not greater than......error code 407...應該是>就好了....不應該是>=
			                      		cmi_learner_preference_delivery_speed = Value;
							lastError = 0;
							return "true";
					       	}else{
			                  		//cmi_learner_preference_delivery_speed = Value;
					      		lastError = 407;
					      		return "false";
					      	}
					}else{
						 lastError = 406;
						 return "false";
					}
				      	break;
				case "cmi.learner_preference.audio_captioning":
				      //if(((Value<=1) && (Value>=-1)) && Value != " " && Value != ""){
				      // == cmi.learner_preference.audio_captioning
				      //restricted vocabulary -1 -- off,0 -- default ,1 -- on
				      if(((Number(Value)==1) || (Number(Value)==0) || (Number(Value)==-1))&&(Value!="")&&(Value!=" ")){
		                      		cmi_learner_preference_audio_captioning = Value;
						lastError = 0;
						return "true";
				      }else{
		                      		//cmi_learner_preference_audio_captioning = Value;
				      		lastError = 406;
				      		return "false";
				      }
				      break;


				//----7-----
				case "cmi.location":
					//Henry 200.05.28 SPM=1000
		                      	if(Value.length <= 8000){
		                      		cmi_location = Value;
		                      		lastError = 0;
		                      	}else{
		 				lastError = 405;
		 				return "false";
		                      	}
		                     	break;
				case "cmi.max_time_allowed":
				      lastError = 404;
				      return "false";
				      break;
				case "cmi.mode":
				      lastError = 404;
				      return "false";
				      break;


				//----9-----
				case "cmi.scaled_passing_score":
					lastError = 404;
					return "false";
					break;
				case "cmi.score._children":
				      lastError = 404;
				      return "false";
				      break;



				case "cmi.score.scaled":
					//alert("set cmi.score.scaled");
					//if value is not a real number
					//var tmpValue=Number(Value);

					//if( typeof(Number(Value))=="number" && Number(Value)!="NaN"){
					//Henry 2004.05.26
					if( !isNaN(Number(Value))){
			        if(((Number(Value)) <= 1 && (Number(Value)) >= -1) || Value==""){
							cmi_score_scaled = Value;
							lastError = 0;

							//modified by Heroin 2003.10.21
							UpdateObjectiveNormalizedMeasure(SCO_ID,Value);
							// 1.判斷是否 satisfiedByMeasure
							// primary  satisfiedByMeasure =true
							// target satisfiedByMeasure=true
							// satisfiedByMeasure
							// a.primay satisfiedByMeasure
							// b.target satisfiedByMeasure writeSatisfiedStatus

							for(var i=0;i<tocList.length;i++){
								if(tocList[i].id==SCO_ID){
						 			if(primaryObjectiveList[i].satisfiedByMeasure=="true"){
										var calStatus=CalSuccessStatus(SCO_ID,Value);
								      		if(calStatus=="passed"){
								      			UpdateObjectiveSuccessStatus(SCO_ID,true);
								      			cmi_success_status="passed";
								      			/* Heroin 2004.10.07 這裡  要判斷  cmi.completion_status 是否有設值 */
								      			if(cmi_completion_status == "" || cmi_completion_status == "unknown"){
								      				activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = true;
								      			}

								      		}else if(calStatus=="failed"){
								      			UpdateObjectiveSuccessStatus(SCO_ID,false);
								      			cmi_success_status="failed";
								      			/* Heroin 2004.10.07 這裡  要判斷  cmi.completion_status 是否有設值 */
								      			if(cmi_completion_status == "" || cmi_completion_status == "unknown"){
								      				activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = true;
								      			}


								      		}
								      		setByContentCheckList[tocIndex].objectiveSetByContentFlag=true;
								      		Set_Objective_by_content(true);
						 			}
								}
							}

							return "true";

						}
						else{
							lastError = 407;
							return "false";
						}
					}
					else{
						lastError = 406;
						return "false";
					}
					break;
				case "cmi.score.raw":
		                      if(!isNaN(Value)){

		                      		cmi_score_raw = Value;
						lastError = 0;
						return "true";
				      }else{
				      	lastError = 406;
				      	return "false";
				      }

		                     break;
				case "cmi.score.max":
		                      if(!isNaN(Value)){
		                      		cmi_score_max = Value;
						lastError = 0;
						return "true";
				      }else{
				      	lastError = 406;
				      	return "false";
				      }
					break;

				case "cmi.score.min":
		                      if(!isNaN(Value)){
		                      		cmi_score_min = Value;
						lastError = 0;
						return "true";
				      }else{
				      	lastError = 406;
				      	return "false";
				      }
					break;





				//----10-----
				case "cmi.session_time":
				      if(checkISOTimespan(Value)=="true"){
		                               cmi_session_time = Value;
					       lastError = 0;
					       return "true";
				      }
				      else{
				               lastError=406;
				               return "false";
				      }
		                     break;
				//-----------SCORM 1.2 cmi.core.lesson_status------
				case "cmi.core.lesson_status":

			                      var LessonStatusParameterFound = "false";
			                      for(i=0;i<LessonStatusArray.length;i++){
							if(Value == LessonStatusArray[i]){
								LessonStatusParameterFound = "true";
							}
			                      }
			                      if(LessonStatusParameterFound == "true" && Value!="not attempted"){
				                      cmi_core_lesson_status = Value;
				                      lastError = 0;
				                      if(cmi_core_lesson_status=="completed"){
				                            if(cmi_sd_mastery_score!=""){
				                                 if(cmi_score_raw >= cmi_sd_mastery_score){
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
				                            cmi_mode="review";
				                            lastError = 0;
				                      }
				                      return "true";
				              }else{
				              		lastError=405;
				              		return "false";
				              }

			                     break;
			    //2004.12.24 最後
					case "cmi.success_status":
			      var SuccessStatusParameterFound = "false";
			      for(i=0;i<SuccessStatusArray.length;i++){
							if(Value == SuccessStatusArray[i]){
								SuccessStatusParameterFound = "true";
							}
			      }
			      //alert("SuccessStatusParameterFound = "+SuccessStatusParameterFound);
					  if(SuccessStatusParameterFound=="true"){
					    cmi_success_status = Value;
					    //alert("cmi_success_status = "+cmi_success_status);
					    lastError = 0;
					    //modified by socool 2004.12.17

					    var SCO_INDEX = parent.parent.functions.enfunctions.tocIDfindIndex(SCO_ID);

					    //alert("SCO_INDEX = "+SCO_INDEX);
					    //alert(primaryObjectiveList[SCO_INDEX].objectiveID.toString());
					    //alert(primaryObjectiveList[SCO_INDEX].satisfiedByMeasure.toString());
					    if(primaryObjectiveList[SCO_INDEX].satisfiedByMeasure.toString()=="false"){
					      //modified by Heroin 2003.10.07
					      //modified by Heroin 2004.08.12
					      if(cmi_success_status=="passed"){
					      	//activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = true;
					      	UpdateObjectiveSuccessStatus(SCO_ID,true);
									//Heroin 2004.10.07
					      	//alert("1 cmi_completion_status="+cmi_completion_status);
					      	if(cmi_completion_status == "" || cmi_completion_status == "unknown"){
					      			UpdateActivityCompletionStatus(SCO_ID,true);
					      	}
					      }else if(cmi_success_status=="failed"){
					      	//activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = false;
					      	//alert("activityAttemptCompletionStatus = " + activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus);
					      	UpdateObjectiveSuccessStatus(SCO_ID,false);
					      	//Heroin 2004.10.07
					      	//alert("2 cmi_completion_status="+cmi_completion_status);
					      	if(cmi_completion_status == "" || cmi_completion_status == "unknown"){
					      		UpdateActivityCompletionStatus(SCO_ID,true);
					      	}
					      }
								//setbyContent 開關
					      //Set_Objective_by_content(true);
					      setByContentCheckList[tocIndex].objectiveSetByContentFlag=true;
					      //setByContentCheckList[tocIndex].completionSetByCobtentFlag=true;
					      //Yunghsiao 2004.12.27 TS 1.3.2
					    }else if((primaryObjectiveList[SCO_INDEX].satisfiedByMeasure.toString()=="true")&&(DataModel.indexOf("score.scaled")=="-1")){
					    	cmi_success_status = "unknown";
					    }
					    return "true";
					  }else{
							lastError = 406;
							return "false";
					  }
						break;


						case "cmi.completion_status":
						//alert("set cmi.completion_status="+Value);
						// modified by Heroin 2004.06.02
						if(cmi_completion_threshold=="" || cmi_progress_measure==""){
							      var CompletionStatusParameterFound = "false";
					                      for(i=0;i<CompletionStatusArray.length;i++){
									if(Value == CompletionStatusArray[i]){
										CompletionStatusParameterFound = "true";
									}
					                      }
								//alert("length=" + CompletionStatusArray.length + " / " + CompletionStatusParameterFound);
							      if(CompletionStatusParameterFound=="true"){
							      		cmi_completion_status = Value;

							      		/* modified by Aachen 2003-06-23 這個方法還要討論 */
							      		if(cmi_completion_status=="completed"){
							      			UpdateActivityCompletionStatus(SCO_ID,true);
							      			//alert(" ccc activityAttemptCompletionStatus = " + activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus);
							      		}else if(cmi_completion_status=="incomplete"){
							      			UpdateActivityCompletionStatus(SCO_ID,false);
							      			//alert(" ccc activityAttemptCompletionStatus = " + activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus);
							      		}
							      		setByContentCheckList[tocIndex].completionSetByCobtentFlag=true;
							      		//alert("setvalue cmi_completion_status="+cmi_completion_status);
							      		lastError = 0;
							      		return "true";
							      }else{
									lastError = 406;
									return "false";
							      }

						}

						/*

					      if(cmi_completion_threshold=="null"){
					      		if(cmi_completion_status=="null"){
					      			cmi_completion_status = "unknown";
					      		}else{

					                      var CompletionStatusParameterFound = "false";
					                      for(i=0;i<CompletionStatusArray.length;i++){
									if(Value == CompletionStatusArray[i]){
										CompletionStatusParameterFound = "true";
									}
					                      }

							      if(CompletionStatusParameterFound=="true"){
							      		cmi_completion_status = Value;

							      		//modified by Aachen 2003-06-23 這個方法還要討論
							      		if(cmi_completion_status=="completed"){
							      			UpdateActivityCompletionStatus(SCO_ID,true);
							      		}else if(cmi_completion_status=="incomplete"){
							      			UpdateActivityCompletionStatus(SCO_ID,false);
							      		}
							      		setByContentCheckList[tocIndex].completionSetByCobtentFlag=true;
							      		//alert("setvalue cmi_completion_status="+cmi_completion_status);
							      		lastError = 0;
							      		return "true";
							      }else{
									lastError = 406;
									return "false";
							      }

					      		}
					      }else{
					      		//set by progress measure
					      }
					      */
						break;



					//Heroin 2004.02.10

					case "cmi.completion_threshold":

						lastError = 404; //read only
						return "false";
						break;

  	                case "cmi.progress_measure":
				              // alert("set cmi.progress_measure="+Value+"   cmi_completion_threshold="+cmi_completion_threshold+"  cmi_completion_status="+cmi_completion_status);
				        			  for(i=0;i<tocList.length;i++){
											  	var tempSCO_index = i;
													//alert("tempSCO_index = "+tempSCO_index);
													//alert("parent.tocList[i].id = "+parent.tocList[i].id+" SCO_ID = "+SCO_ID);
													if(tocList[i].id==SCO_ID)
													{
														cmi_completion_threshold = adlcpList[tempSCO_index].completionThreshold//2005.08.12
								    				//alert("cmi_completion_threshold = "+cmi_completion_threshold);
								  				}
							  }

				               //if( typeof(Number(Value))=="number" && Number(Value)!="NaN"){
				               //Henry 2004.05.26
				               if( !isNaN(Number(Value))){
				                      	if(((Number(Value)) <= 1 && (Number(Value)) >= 0) || Value==""){
								cmi_progress_measure = Value;
								//Henry 2004.06.01
								//Heroin 2004.06.02
								//No value set by the sco
								if(cmi_completion_status==""){
									if(cmi_completion_threshold==""){
										cmi_completion_status = "unknown";
									}else if(cmi_progress_measure >= cmi_completion_threshold){
										cmi_completion_status = "completed";
										UpdateActivityCompletionStatus(SCO_ID,true);
									}else{
										cmi_completion_status = "incomplete";
										UpdateActivityCompletionStatus(SCO_ID,false);
									}

								//one of the defined vocabularies
								}else if(cmi_completion_status=="unknown" || cmi_completion_status=="not attempted" || cmi_completion_status=="incomplete" || cmi_completion_status=="completed"){
									if(cmi_completion_threshold==""){

									}else if(cmi_progress_measure >= cmi_completion_threshold){
										cmi_completion_status = "completed";
										UpdateActivityCompletionStatus(SCO_ID,true);
									}else{
										cmi_completion_status = "incomplete";
										UpdateActivityCompletionStatus(SCO_ID,false);
									}

								}
								//alert("set measure cmi_completion_status="+cmi_completion_status);
								lastError = 0;
								return "true";
							}
							else{
								lastError = 407;
								return "false";
							}
						}
						else{
							lastError = 406;
							return "false";
						}
			                     break;


					case "cmi.suspend_data":
			                    //  if( Value.length <= 4096){
			                      		cmi_suspend_data = Value;
							lastError = 0;
							return "true";
					    //  }else{
					   //   		lastError = 406;
					   //   		return "false";
					   //   }
			                   //  break;
					case "cmi.time_limit_action":
					      lastError = 404;
					      return "false";
					      break;
					case "cmi.total_time":
					      lastError = 404;
					      return "false";
					      break;

					//--------interactions------------

		        	//2004.01.16- Vega modify base on SCORM1.3 Beta3

					case "_children":
					      lastError = 404; //402->404
					      return "false";
					      break;
					case "cmi.interactions._count":
					      lastError = 404; //402->404
					      return "false";
					      break;

					case "cmi.objectives._children":
					      lastError = 404;
					      return "false";
					      break;
					case "cmi.objectives._count":
					      lastError = 404;
					      return "false";
					      break;
					case "cmi.student_data._children":
					      lastError = 402;
					      return "false";
					      break;


					default :

					      //alert("else DataModel="+DataModel);
					      CheckDataModel(DataModel);
					      lastError=401;
					      if(lastError==201){
					      		return "false";
					      }else if(lastError==401){
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

		//}

	}else if(IsFinished=="true"){
		lastError = 133; //133 store data before initialization
		return "false";
	}
	else{
		lastError = 132;  // Store data before initialization
		return "false";
	}

}

function LMSCommit(emptystring){
	checkFinished();
	if(IsLMSInitialize=="true"){
		//if(IsFinished=="true"){
		//	lastError = 143;  // Cimmit after termination
		//	return "false";
		//}
		//else{
			if((LMSCommit.arguments.length==1)&&(LMSCommit.arguments[0] == "")){

	        	//get cmi.session_time and compute cmi.total_time
			    cmi_total_time=computeTotalTime();

				var xmldoc = XmlDocument.create();
				xmldoc.async = false;
				//xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
				var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
				xmldoc.appendChild(xmldoc.createElement("root"));
				xmldoc.insertBefore(xmlpi,xmldoc.childNodes(0));
				var rootElement = xmldoc.documentElement;

				//course_ID,user_ID,sco_ID
				rootElement.setAttribute("course_ID",course_ID);
				rootElement.setAttribute("user_ID",cmi_learner_id);
				rootElement.setAttribute("sco_ID",SCO_ID);
				//rootElement.setAttribute("sco_ID",SCOIDTranslator(SCO_ID));

				//modified by SCORM 1.3
				rootElement.setAttribute("Scorm_Type","sco");
				rootElement.setAttribute("Message_Type","LMSCommit");

				var cmi_location_Element = xmldoc.createElement("cmi_core_lesson_location");
			    	if(cmi_location=="null"){
			    		cmi_location = "";
			    	}
			    	cmi_location_Element.text = cmi_location;
			    	rootElement.appendChild(cmi_location_Element);

				var cmi_credit_Element = xmldoc.createElement("cmi_core_credit");
				    cmi_credit_Element.text = cmi_credit;
				rootElement.appendChild(cmi_credit_Element);

				var cmi_core_lesson_status_Element = xmldoc.createElement("cmi_core_lesson_status");
				    cmi_core_lesson_status_Element.text = cmi_core_lesson_status;
				rootElement.appendChild(cmi_core_lesson_status_Element);

				var cmi_total_time_Element = xmldoc.createElement("duration");
				    cmi_total_time_Element.text = cmi_total_time;
				rootElement.appendChild(cmi_total_time_Element);

				var cmi_mode_Element = xmldoc.createElement("cmi_core_lesson_mode");
				    cmi_mode_Element.text = cmi_mode;
				rootElement.appendChild(cmi_mode_Element);

				var cmi_exit_Element = xmldoc.createElement("cmi_core_exit_value");
				    cmi_exit_Element.text = cmi_exit;
				rootElement.appendChild(cmi_exit_Element);

				var cmi_session_time_Element = xmldoc.createElement("cmi_core_session_time");
				    cmi_session_time_Element.text = cmi_session_time;
				rootElement.appendChild(cmi_session_time_Element);

				var cmi_suspend_data_Element = xmldoc.createElement("cmi_suspend_data");
				if(cmi_suspend_data=="null"){
			    		cmi_suspend_data = "";
			    	}
				cmi_suspend_data_Element.text = cmi_suspend_data;
				rootElement.appendChild(cmi_suspend_data_Element);

				//Heroin 2004.05.11
				cmi_launch_data = adlcpList[tocIndex].dataFromLMS;

				var cmi_launch_data_Element = xmldoc.createElement("cmi_launch_data");
				    cmi_launch_data_Element.text = cmi_launch_data;
				rootElement.appendChild(cmi_launch_data_Element);

				var cmi_entry_Element = xmldoc.createElement("cmi_core_entry");
				    cmi_entry_Element.text = cmi_entry;
				rootElement.appendChild(cmi_entry_Element);

				var cmi_score_raw_Element = xmldoc.createElement("cmi_core_score_raw");
				    cmi_score_raw_Element.text = cmi_score_raw;
				rootElement.appendChild(cmi_score_raw_Element);

				//------SCORM 1.3 -------------------------------------------------------------

				var cmi_score_scaled_Element = xmldoc.createElement("cmi_core_score_normalized");
				    cmi_score_scaled_Element.text = cmi_score_scaled;
				rootElement.appendChild(cmi_score_scaled_Element);


				//------SCORM 1.3 --------------------------------------------------------------

				var cmi_score_max_Element = xmldoc.createElement("cmi_core_score_max");
				    cmi_score_max_Element.text = cmi_score_max;
				rootElement.appendChild(cmi_score_max_Element);

				var cmi_score_min_Element = xmldoc.createElement("cmi_core_score_min");
				    cmi_score_min_Element.text = cmi_score_min;
				rootElement.appendChild(cmi_score_min_Element);

				//---SCORM 1.3-------------Add 2003/10/07 vega
				var cmi_success_status_Element = xmldoc.createElement("cmi_core_success_status");
				cmi_success_status_Element.text = cmi_success_status;
				rootElement.appendChild(cmi_success_status_Element );

				var cmi_completion_status_Element  = xmldoc.createElement("cmi_core_completion_status");
				cmi_completion_status_Element.text = cmi_completion_status;
				rootElement.appendChild(cmi_completion_status_Element);
				//---------------------------------------------------------------

				//alert("commit cmi_completion_status="+cmi_completion_status);


				//Heroin 2004.02.10
				//Heroin 2004.05.11
				cmi_core_completion_threshold = adlcpList[tocIndex].completionThreshold;
				var cmi_core_completion_threshold_Element  = xmldoc.createElement("cmi_core_completion_threshold");
				cmi_core_completion_threshold_Element.text = cmi_completion_threshold;
				rootElement.appendChild(cmi_core_completion_threshold_Element);



				var cmi_core_progress_measure_Element  = xmldoc.createElement("cmi_core_progress_measure");
				if(cmi_progress_measure=="null"){
			    		cmi_progress_measure = "";
			    	}
				cmi_core_progress_measure_Element.text = cmi_progress_measure;
			    	rootElement.appendChild(cmi_core_progress_measure_Element);

				//alert(cmi_completion_threshold+ "  "+cmi_progress_measure);


				//Heroin-2003.11.25
				var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");
				    cmi_core_attempt_count_Element.text = cmi_core_attempt_count;
				rootElement.appendChild(cmi_core_attempt_count_Element);

				//Heroin-2003.12.05 isDisabled and isHiddenFromChoice
				var cmi_core_isDisabled_Element = xmldoc.createElement("cmi_core_isDisabled");
				    cmi_core_isDisabled_Element.text = cmi_core_isDisabled;
				rootElement.appendChild(cmi_core_isDisabled_Element);

				var cmi_core_isHiddenFromChoice_Element = xmldoc.createElement("cmi_core_isHiddenFromChoice");
				    cmi_core_isHiddenFromChoice_Element.text = cmi_core_isHiddenFromChoice;
				rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);


				//Heroin-2003.12.12 limitcondition duration
				var cmi_core_attempt_absolut_duration_Element = xmldoc.createElement("cmi_core_attempt_absolut_duration");
				    cmi_core_attempt_absolut_duration_Element.text = cmi_core_attempt_absolut_duration;
				rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);

				var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");
				    cmi_core_attempt_experienced_duration_Element.text = cmi_core_attempt_experienced_duration;
				rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);

				var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");
				    cmi_core_activity_absolut_duration_Element.text = cmi_core_activity_absolut_duration;
				rootElement.appendChild(cmi_core_activity_absolut_duration_Element);

				var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");
				    cmi_core_activity_experienced_duration_Element.text = cmi_core_activity_experienced_duration;
				rootElement.appendChild(cmi_core_activity_experienced_duration_Element);



				var cmi_learner_preference_audio_level_Element = xmldoc.createElement("cmi_student_preference_audio");
				if(cmi_learner_preference_audio_level=="null"){
			    		cmi_learner_preference_audio_level = "";
			    	}
			    	cmi_learner_preference_audio_level_Element.text = cmi_learner_preference_audio_level;
			    	rootElement.appendChild(cmi_learner_preference_audio_level_Element);

				var cmi_learner_preference_language_Element = xmldoc.createElement("cmi_student_preference_language");
				if(cmi_learner_preference_language=="null"){
			    		cmi_learner_preference_language = "";
			    	}
			    	cmi_learner_preference_language_Element.text = cmi_learner_preference_language;
			    	rootElement.appendChild(cmi_learner_preference_language_Element);

				var cmi_learner_preference_delivery_speed_Element = xmldoc.createElement("cmi_student_preference_speed");
				if(cmi_learner_preference_delivery_speed=="null"){
			    		cmi_learner_preference_delivery_speed = "";
			    	}
				cmi_learner_preference_delivery_speed_Element.text = cmi_learner_preference_delivery_speed;
			    	rootElement.appendChild(cmi_learner_preference_delivery_speed_Element);

				var cmi_learner_preference_audio_captioning_Element = xmldoc.createElement("cmi_student_preference_text");
				if(cmi_learner_preference_audio_captioning=="null"){
			    		cmi_learner_preference_audio_captioning = "";
			    	}
				cmi_learner_preference_audio_captioning_Element.text = cmi_learner_preference_audio_captioning;
			    	rootElement.appendChild(cmi_learner_preference_audio_captioning_Element);


				if(((Number(o_count)+1)>0)&&(CmiObjectiveStorageSwitch.toString()=="true")){
				//Heroin 2004.09.01
				//if(((Number(o_count)+1)>0)){
					//CmiObjectivesArray
					//o-->CmiObjectiveArray:count
					//CmiObjectivesArray[o_count][0]<==>id
					//CmiObjectivesArray[o_count][1]<==>score.raw
					//CmiObjectivesArray[o_count][2]<==>score.max
					//CmiObjectivesArray[o_count][3]<==>score.min
					//CmiObjectivesArray[o_count][4]<==>score.normalized
					//CmiObjectivesArray[o_count][5]<==>success_status
					//CmiObjectivesArray[o_count][6]<==>completion_status
					//CmiObjectivesArray[o_count][7]<==>description
					//Vega 2004.9.30 TS 1.3.1 new
					//CmiObjectivesArray[o_count][8]<==>progress_measure
					var cmi_objectives_Element_Array = new Array(o_count);


					var u=0;
					for(u=0; u<Number(o_count)+1 ; u++){

						//----------------------------------------------------
						cmi_objectives_Element_Array[u] = xmldoc.createElement("cmi_objectives");
						//----------------------------------------------------
						cmi_objectives_Element_Array[u].setAttribute("n",u);
						cmi_objectives_Element_Array[u].setAttribute("id",CmiObjectivesArray[u][0]);
						cmi_objectives_Element_Array[u].setAttribute("score_raw",CmiObjectivesArray[u][1]);
						cmi_objectives_Element_Array[u].setAttribute("score_max",CmiObjectivesArray[u][2]);
						cmi_objectives_Element_Array[u].setAttribute("score_min",CmiObjectivesArray[u][3]);
						cmi_objectives_Element_Array[u].setAttribute("score_scaled",CmiObjectivesArray[u][4]);
						cmi_objectives_Element_Array[u].setAttribute("success_status",CmiObjectivesArray[u][5]);
						cmi_objectives_Element_Array[u].setAttribute("completion_status",CmiObjectivesArray[u][6]);
						//2004.6.15- Vega Add
						cmi_objectives_Element_Array[u].setAttribute("description",CmiObjectivesArray[u][7]);
						//-------------------
						//Vega 2004.9.30 added TS 1.3.1 new
						cmi_objectives_Element_Array[u].setAttribute("progress_measure",CmiObjectivesArray[u][8]);
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
				*/
				//interactions -- 2004/2/9 Vega
				//alert("n="+n);
				if(Number(n)+1>0){
					//alert("@@");
					var cmi_interactions_Element_Array = new Array(n);
					var v=0;
					for(v=0; v < Number(n)+1 ; v++){
						//alert("v="+v+"   "+CmiInteractionsArray[v][0]+"   "+CmiInteractionsArray[v][1]+"   "+CmiInteractionsArray[v][2]+"   "+CmiInteractionsArray[v][3]+"   "+CmiInteractionsArray[v][4]+"   "+CmiInteractionsArray[v][5]+"   "+CmiInteractionsArray[v][6]+"   "+CmiInteractionsArray[v][7]+"   "+CmiInteractionsArray[v][8]+"   "+CmiInteractionsArray[v][9]);
						cmi_interactions_Element_Array[v] = xmldoc.createElement("cmi_interactions");
						cmi_interactions_Element_Array[v].setAttribute("n",v);
						cmi_interactions_Element_Array[v].setAttribute("id",CmiInteractionsArray[v][0]);
						cmi_interactions_Element_Array[v].setAttribute("timestamp",CmiInteractionsArray[v][1]);
						cmi_interactions_Element_Array[v].setAttribute("type",CmiInteractionsArray[v][2]);
						cmi_interactions_Element_Array[v].setAttribute("weighting",CmiInteractionsArray[v][3]);
						cmi_interactions_Element_Array[v].setAttribute("learner_response",CmiInteractionsArray[v][4]);
						cmi_interactions_Element_Array[v].setAttribute("result",CmiInteractionsArray[v][5]);
						cmi_interactions_Element_Array[v].setAttribute("latency",CmiInteractionsArray[v][6]);
						cmi_interactions_Element_Array[v].setAttribute("description",CmiInteractionsArray[v][7]);
						cmi_interactions_Element_Array[v].setAttribute("objectives._count",CmiInteractionsArray[v][8]);
						cmi_interactions_Element_Array[v].setAttribute("correct_responses._count",CmiInteractionsArray[v][9]);

						rootElement.appendChild(cmi_interactions_Element_Array[v]);


						//interactions.n.objectives.id
						//Heroin 2004.05.11
						if(Number(CmiInteractionsArray[v][8])+1 > 0){
							var oIndex=0;
							var c = Number(CmiInteractionsArray[v][8]);
							var cmi_interactions_objects_Element_Array = new Array(c)

							for(oIndex=0; oIndex < Number(CmiInteractionsArray[v][8])+1 ; oIndex++){
								cmi_interactions_objects_Element_Array[oIndex] = xmldoc.createElement("cmi_interactions_objectives");
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("n",v);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("id",CmiInteractionsArray[v][0]);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("c",oIndex);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("objectives_id",CmiInteractionsObjectivesArray[v][oIndex]);

								//alert("oIndex="+oIndex+"  interactions_objectives_id="+CmiInteractionsObjectivesArray[v][oIndex]);
								rootElement.appendChild(cmi_interactions_objects_Element_Array[oIndex]);
							}
						}
						//interactions.n.correct_responses.pattern
						if(Number(CmiInteractionsArray[v][9])+1 > 0){
							var pIndex=0;
							var m= Number(CmiInteractionsArray[v][9]);
							var cmi_interactions_correctPattern_Element_Array = new Array(m)
							for(pIndex=0; pIndex < Number(CmiInteractionsArray[v][9])+1 ; pIndex++){
								cmi_interactions_correctPattern_Element_Array[pIndex]= xmldoc.createElement("cmi_interactions_correct_responses");
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("n",v);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("id",CmiInteractionsArray[v][0]);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("m",pIndex);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("pattern",CmiInteractionsCorrect_ResponsesArray[v][pIndex]);
								rootElement.appendChild(cmi_interactions_correctPattern_Element_Array[pIndex]);
							}
						}

					}
					/*
						//interactions.n.objectives.id
						//Heroin 2004.05.11
						if(Number(CmiInteractionsArray[v][8])+1 > 0){
							var oIndex=0;
							var c = Number(CmiInteractionsArray[v][8]);
							var cmi_interactions_objects_Element_Array = new Array(c)

							for(oIndex=0; oIndex < Number(CmiInteractionsArray[v][8])+1 ; oIndex++){
								cmi_interactions_objects_Element_Array[oIndex] = xmldoc.createElement("cmi_interactions_objectives");
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("n",v);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("id",CmiInteractionsArray[v][0]);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("c",oIndex);
								cmi_interactions_objects_Element_Array[oIndex].setAttribute("objectives_id",CmiInteractionsObjectivesArray[v][oIndex]);

								//alert("objectives_id="+CmiInteractionsObjectivesArray[v][oIndex]);
								rootElement.appendChild(cmi_interactions_objects_Element_Array[oIndex]);
							}
						}
						//interactions.n.correct_responses.pattern
						if(Number(CmiInteractionsArray[v][9])+1 > 0){
							var pIndex=0;
							var m= Number(CmiInteractionsArray[v][9]);
							var cmi_interactions_correctPattern_Element_Array = new Array(m)
							for(pIndex=0; pIndex < Number(CmiInteractionsArray[v][9])+1 ; pIndex++){
								cmi_interactions_correctPattern_Element_Array[pIndex]= xmldoc.createElement("cmi_interactions_correct_responses");
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("n",v);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("id",CmiInteractionsArray[v][0]);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("m",pIndex);
								cmi_interactions_correctPattern_Element_Array[pIndex].setAttribute("pattern",CmiInteractionsCorrect_ResponsesArray[v][pIndex]);
								rootElement.appendChild(cmi_interactions_correctPattern_Element_Array[pIndex]);
							}
						}
						*/
				}

				//if(Number(CommentsFromLearnerCount)+1>0){
				if(Number(CommentsFromLearnerCount+1)>0){

					var temp_cmi_comments_from_learner_Element;
					var t=0;
					for(t=0; t < Number(CommentsFromLearnerCount)+1 ; t++){
						temp_cmi_comments_from_learner_Element = xmldoc.createElement("cmi_comments_from_learner");
						temp_cmi_comments_from_learner_Element.setAttribute("n",t);
						temp_cmi_comments_from_learner_Element.setAttribute("comment",CmiCommentsFromLearnerArray[t][0]);
						temp_cmi_comments_from_learner_Element.setAttribute("location",CmiCommentsFromLearnerArray[t][1]);
						//Vega 2004.9.23 modified
						temp_cmi_comments_from_learner_Element.setAttribute("timestamp",CmiCommentsFromLearnerArray[t][2]);

						rootElement.appendChild(temp_cmi_comments_from_learner_Element);

					}

				}

				//---XMLHTTP---


				var ServerSide = xmlhttp_set;
				var XMLHTTPObj = XmlHttp.create();
				XMLHTTPObj.Open("POST",ServerSide,false);
				XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
				XMLHTTPObj.Send(xmldoc.xml);

				//2004.08.09...Henry
				//persistState
				persistStateXMLDoc = xmldoc;
				PersistStateLMSCommit();

				//vega
				//xmldoc.loadXML(XMLHTTPObj.responseText);

				//---XMLHTTP---
				lastError = 0;
				IsCommit = "true";
				return "true";

			}else{
			    	lastError = 201;
			    	return "false";
			}



	}else if(IsFinished=="true"){
		lastError = 143;  // Cimmit after termination
		return "false";
	}
	else{
		lastError = 142;  //commit before initialization
		return "false";
	}
}

/*
function GetManifestData(DataModel){
	checkFinished();
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;

	var str=manifest_file;
	xmldoc.load(str);

        var lesson_location=window.center.location.href;

	//get SCO_ID AND SCO_Title
	var total_resource_element;
	total_resource_element = xmldoc.selectNodes("//resource").length;


	for(i=0;i<total_resource_element;i++){
		var tempResourceElement = xmldoc.selectNodes("//resource").item(i).cloneNode("True");
		for(j=0;j<tempResourceElement.childNodes.length;j++){
			if(tempResourceElement.childNodes.item(j).nodeName == "file"){
				var temp_sco_location = tempResourceElement.childNodes.item(j).attributes.getNamedItem("href").text;
				if(lesson_location.indexOf(temp_sco_location) != -1 ){
					var Resource_ID = tempResourceElement.attributes.getNamedItem("identifier").text;
					var Current_Item = xmldoc.selectSingleNode("//item[@identifierref='"+ Resource_ID +"']")
					var CurrentSCO_ID = Current_Item.attributes.getNamedItem("identifier").text;
					switch (DataModel){
						case "SCO_ID":
							return CurrentSCO_ID;
							break;
						case "cmi.launch_data":
							if(Current_Item.selectSingleNode("adlcp:dataFromLMS") != null){
								//var a=Current_Item.selectSingleNode("adlcp:dataFromLMS").text;

								//a = adlcpList[tocIndex].completionThreshold;
								//alert("02="+cmi_launch_data);
								return Current_Item.selectSingleNode("adlcp:dataFromLMS").text;

							}else{
								return "";
							}

							break;
						case "cmi.student_data.mastery_score":
							if(Current_Item.selectSingleNode("adlcp:masteryscore") != null){
								return Current_Item.selectSingleNode("adlcp:masteryscore").text;
							}else{
								return "";
							}
							break;
						case "cmi.max_time_allowed":
							if(Current_Item.selectSingleNode("adlcp:maxtimeallowed") != null){
								return Current_Item.selectSingleNode("adlcp:maxtimeallowed").text;
							}else{
								return "";
							}

							break;

						case "cmi.time_limit_action":
							if(Current_Item.selectSingleNode("adlcp:timeLimitAction") != null){
								return Current_Item.selectSingleNode("adlcp:timeLimitAction").text;
							}else{
								return "";
							}
							break;

					}

				}
			}
		}
	}

}
*/


function isFirstEnterSco(DataModel){
	checkFinished();
        //---XMLHTTP---

	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	//xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes(0));
	//xmldoc.replaceChild(xmlpi,xmldoc.childNodes(0));
	var rootElement = xmldoc.documentElement;

	// alert(" course_ID= "+course_ID+" cmi_learner_id= "+cmi_learner_id+" SCO_ID= "+SCO_ID+" DataModel= "+DataModel);
	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",cmi_learner_id);
	rootElement.setAttribute("sco_ID",SCO_ID);
	//rootElement.setAttribute("sco_ID",SCOIDTranslator(SCO_ID));


	var DataModelElement = xmldoc.createElement("DataModel");
		DataModelElement.text = DataModel;
	rootElement.appendChild(DataModelElement);

	var ServerSide = xmlhttp_get;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.Open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.Send(xmldoc.xml);

	xmldoc.loadXML(XMLHTTPObj.responseText);



	var flag;
	flag = xmldoc.selectSingleNode("/root").text;


	// alert("isFirstEnterSCO = "+ flag + " / " + SCOIDTranslator(SCO_ID) + " / " + SCO_ID);

	return flag;

	//---XMLHTTP---

}

function setSCOInitialData(DataModel){
        //---XMLHTTP---

	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	//xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes(0));
	var rootElement = xmldoc.documentElement;

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",cmi_learner_id);
	rootElement.setAttribute("sco_ID",SCO_ID);
	//rootElement.setAttribute("sco_ID",SCOIDTranslator(SCO_ID));

	var DataModelElement = xmldoc.createElement("DataModel");
		DataModelElement.text = DataModel;
	rootElement.appendChild(DataModelElement);

	var ServerSide = xmlhttp_get;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.Open("POST",ServerSide,false);
	/* XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");  */
	XMLHTTPObj.Send(xmldoc.xml);

	// alert(xmldoc.xml);
	// alert(XMLHTTPObj.responseText);

	xmldoc.loadXML(XMLHTTPObj.responseText);
	// alert(xmldoc.xml);
	//alert(XMLHTTPObj.responseText);
       // alert(GetManifestData("SCO_ID"));
    	//由抓回來的XML Stream設定初始值

	//--------------cmi_core---------------------------------------------------------

	//Henry 2004.06.01
	//在SCORM 2004中規定當cmi exit = time-out時...必須要提供一組全新之data model...
	//cmi.entry = ab-initio
	cmi_exit = xmldoc.selectSingleNode("/root/exit_value").text;

	//suspend 及 logout要把舊的data 載入
	//time-out, normal, ""就不載入

	var loadflag = "false";
	if(tocList[tocIndex].persistState.toString()=="true"){
		loadflag = "true";
	}else{
		if((cmi_exit=="suspend")||(cmi_exit=="logout")){
			loadflag = "true";
		}
	}

	//alert("persistState = " + tocList[tocIndex].persistState.toString() + " / cmi_exit = " + cmi_exit + " / loadflag = " + loadflag);

	if(loadflag.toString()=="true"){

		cmi_location = xmldoc.selectSingleNode("/root/lesson_location").text;
		cmi_credit = xmldoc.selectSingleNode("/root/credit").text;
		cmi_core_lesson_status = xmldoc.selectSingleNode("/root/lesson_status").text;

		if((cmi_exit=="suspend")||(cmi_exit=="logout")){
			cmi_total_time = xmldoc.selectSingleNode("/root/duration").text;
		}

		cmi_mode = xmldoc.selectSingleNode("/root/lesson_mode").text;
		cmi_session_time = xmldoc.selectSingleNode("/root/session_time").text;
		cmi_suspend_data = xmldoc.selectSingleNode("/root/suspend_data").text;
		cmi_launch_data = xmldoc.selectSingleNode("/root/launch_data").text;
		//alert("@@init cmi_launch_data="+cmi_launch_data);
		cmi_entry = xmldoc.selectSingleNode("/root/entry").text;

		if((cmi_exit=="suspend")||(cmi_exit=="logout")){
			cmi_entry = "resume";
		}else{
			cmi_entry = "";
		}

		//reset cmi.exit
		cmi_exit = "";
		cmi_score_raw = xmldoc.selectSingleNode("/root/score_raw").text;

		//------SCORM 1.3 ----------------------
		cmi_score_scaled = xmldoc.selectSingleNode("/root/score_normalized").text;
		//alert(SCO_ID+"    cmi_score_scaled="+cmi_score_scaled);
		//------SCORM 1.3 ----------------------

		cmi_score_max = xmldoc.selectSingleNode("/root/score_max").text;
		cmi_score_min = xmldoc.selectSingleNode("/root/score_min").text;


		//-------SCORM 1.3 ------------ADD 2003/10/07 vega

		cmi_success_status = xmldoc.selectSingleNode("/root/success_status").text;
		cmi_completion_status = xmldoc.selectSingleNode("/root/completion_status").text;
		cmi_core_attempt_count = xmldoc.selectSingleNode("/root/attempt_count").text;

		//alert("set init data cmi_completion_status="+cmi_completion_status);
		//Heroin 2004.02.10
		cmi_completion_threshold = xmldoc.selectSingleNode("/root/completion_threshold").text;
		cmi_progress_measure = xmldoc.selectSingleNode("/root/progress_measure").text;


		//----------------------------added by Heroin -2003.12.05
		cmi_core_isDisabled = xmldoc.selectSingleNode("/root/isDisabled").text;
		cmi_core_isHiddenFromChoice = xmldoc.selectSingleNode("/root/isHiddenFromChoice").text;


		//----------------------------added by Heroin -2003.12.12 ---limitcondition duration
		cmi_core_attempt_absolut_duration = xmldoc.selectSingleNode("/root/attempt_absolut_duration").text;
		cmi_core_attempt_experienced_duration = xmldoc.selectSingleNode("/root/attempt_experienced_duration").text;
		cmi_core_activity_absolut_duration = xmldoc.selectSingleNode("/root/activity_absolut_duration").text;
		cmi_core_activity_experienced_duration = xmldoc.selectSingleNode("/root/activity_experienced_duration").text;



		//---------------cmi_learner_preference----------------------------------

		if(xmldoc.selectNodes("//audio").length>0){
			cmi_learner_preference_audio_level= xmldoc.selectSingleNode("/root/audio").text;
		}

		if(xmldoc.selectNodes("//language").length>0){
			cmi_learner_preference_language= xmldoc.selectSingleNode("/root/language").text;
		}

		if(xmldoc.selectNodes("//speed").length>0){
			cmi_learner_preference_delivery_speed= xmldoc.selectSingleNode("/root/speed").text;
		}

		if(xmldoc.selectNodes("//text").length>0){
			cmi_learner_preference_audio_captioning= xmldoc.selectSingleNode("/root/text").text;
		}



		//---------------cmi_objectives----------------------------------
		var objective_length = xmldoc.selectNodes("//cmi_objectives").length;
		var tempobjectiveElement;
	 	if(objective_length > 0){
			for(t=0;t<objective_length;t++){
				tempobjectiveElement = xmldoc.selectNodes("//cmi_objectives").item(t)
				CmiObjectivesArray[t]=new Array("","","","","","unknown","unknown","null","0");
				CmiObjectivesArray[t][0]=tempobjectiveElement.attributes.getNamedItem("id").text
				CmiObjectivesArray[t][1] = tempobjectiveElement.attributes.getNamedItem("score_raw").text
				CmiObjectivesArray[t][2] = tempobjectiveElement.attributes.getNamedItem("score_max").text
				CmiObjectivesArray[t][3] = tempobjectiveElement.attributes.getNamedItem("score_min").text
				CmiObjectivesArray[t][4] = tempobjectiveElement.attributes.getNamedItem("score_scaled").text
				//-----------------SCORM 1.3 ------------------------------------
				CmiObjectivesArray[t][5] = tempobjectiveElement.attributes.getNamedItem("success_status").text
				CmiObjectivesArray[t][6] = tempobjectiveElement.attributes.getNamedItem("completion_status").text
				//2004.6.15- Vega Add
				CmiObjectivesArray[t][7] = tempobjectiveElement.attributes.getNamedItem("description").text
				//Vega 2004.9.30 added TS 1.3.1 new
				CmiObjectivesArray[t][8] = tempobjectiveElement.attributes.getNamedItem("progress_measure").text
				//-----------------SCORM 1.3 ------------------------------------
			}
		}

		//++++++++++++++++++++++++++++++++++++++++++++++
		o_count = (objective_length - 1);
		o_index = o_count;

		//------------cmi_interactions---------------------------------Vega 2004/2/9--------

		var interactions_length = xmldoc.selectNodes("//cmi_interactions").length;
		var tempInteractionsElement;
	 	if(interactions_length > 0){
			for(var t=0;t<interactions_length;t++){
				tempInteractionsElement = xmldoc.selectNodes("//cmi_interactions").item(t)
				CmiInteractionsArray[t][0] = tempInteractionsElement.attributes.getNamedItem("id").text
				CmiInteractionsArray[t][1] = tempInteractionsElement.attributes.getNamedItem("timestamp").text
				CmiInteractionsArray[t][2] = tempInteractionsElement.attributes.getNamedItem("type").text
				CmiInteractionsArray[t][3] = tempInteractionsElement.attributes.getNamedItem("weighting").text
				CmiInteractionsArray[t][4] = tempInteractionsElement.attributes.getNamedItem("learner_response").text
				CmiInteractionsArray[t][5] = tempInteractionsElement.attributes.getNamedItem("result").text
				CmiInteractionsArray[t][6] = tempInteractionsElement.attributes.getNamedItem("latency").text
				CmiInteractionsArray[t][7] = tempInteractionsElement.attributes.getNamedItem("description").text
				CmiInteractionsArray[t][8] = tempInteractionsElement.attributes.getNamedItem("objectives._count").text
				//cmi_interactions_objectives
				if(CmiInteractionsArray[t][8]+1>0){
					var tempInteractionsObjectivesElement;
					tempInteractionsObjectivesElement = tempInteractionsElement.selectNodes("//cmi_interactions_objectives")
					for(var i=0;i<CmiInteractionsArray[t][8];i++){
						CmiInteractionsObjectivesArray[t][i]= tempInteractionsObjectivesElement.attributes.getNamedItem("objectives_id").text
					}
				}

				CmiInteractionsArray[t][9] =tempInteractionsElement.attributes.getNamedItem("correct_responses._count").text
				//cmi_interactions_correct_responses
				if(CmiInteractionsArray[t][9]+1>0){
					var tempInteractionsCorrectResponseElement;
					tempInteractionsCorrectResponseElement = tempInteractionsElement.selectNodes("//cmi_interactions_correct_responses")
					for(var i=0;i<CmiInteractionsArray[t][9];i++){
						CmiInteractionsCorrect_ResponsesArray[t][i]= tempInteractionsCorrectResponseElement.attributes.getNamedItem("pattern").text
					}
				}

			}
		}

		//-----------------------------------------------------------------------

		//---modified by Henry 2004-01-29
		//---------------cmi_comments_from_learner----------------------------------

		var cmi_comments_from_learner_length = xmldoc.selectNodes("//cmi_comments_from_learner").length;
		var tempCommentsFromLearnerElement;
	 	if(cmi_comments_from_learner_length > 0){
			for(t=0;t<cmi_comments_from_learner_length;t++){
				tempCommentsFromLearnerElement = xmldoc.selectNodes("//cmi_comments_from_learner").item(t)
				CmiCommentsFromLearnerArray[t] = new Array("","","");
				CmiCommentsFromLearnerArray[t][0] = tempCommentsFromLearnerElement.attributes.getNamedItem("comment").text
				CmiCommentsFromLearnerArray[t][1] = tempCommentsFromLearnerElement.attributes.getNamedItem("location").text
				//Vega 2004.9.23 modified
				CmiCommentsFromLearnerArray[t][2] = tempCommentsFromLearnerElement.attributes.getNamedItem("timestamp").text
				//-----------------SCORM 1.3 ------------------------------------
			}
		}

		CommentsFromLearnerCount = (cmi_comments_from_learner_length - 1);

		//---------------cmi_comments_from_lms----------------------------------

		var cmi_comments_from_lms_length = xmldoc.selectNodes("//cmi_comments_from_lms").length;
		var tempCommentsFromLmsElement;
	 	if(cmi_comments_from_lms_length > 0){
			for(t=0;t<cmi_comments_from_lms_length;t++){
				tempCommentsFromLmsElement = xmldoc.selectNodes("//cmi_comments_from_lms").item(t)
				CmiCommentsFromLmsArray[t] = new Array("","","");
				CmiCommentsFromLmsArray[t][0] = tempCommentsFromLmsElement.attributes.getNamedItem("comment").text
				CmiCommentsFromLmsArray[t][1] = tempCommentsFromLmsElement.attributes.getNamedItem("location").text
				//Vega 2004.9.23 modified
				CmiCommentsFromLmsArray[t][2] = tempCommentsFromLmsElement.attributes.getNamedItem("timestamp").text
				//-----------------SCORM 1.3 ------------------------------------
			}
		}

		CommentsFromLmsIndex = (cmi_comments_from_lms_length - 1);
		//2004.08.10...Henry
		trackingInfoList[Number(tocIndex)].objectiveMeasureStatus="true";
		trackingInfoList[Number(tocIndex)].objectiveNormalizedMeasure = Number(xmldoc.selectSingleNode("/root/score_normalized").text);


		if(xmldoc.selectSingleNode("/root/success_status").text=="passed"){
			trackingInfoList[Number(tocIndex)].objectiveProgressStatus = true;
			trackingInfoList[Number(tocIndex)].objectiveSatisfiedStatus = true;
		}else if(xmldoc.selectSingleNode("/root/success_status").text=="failed"){
			trackingInfoList[Number(tocIndex)].objectiveProgressStatus = true;
			trackingInfoList[Number(tocIndex)].objectiveSatisfiedStatus = false;
		}

		if(xmldoc.selectSingleNode("/root/completion_status").text=="completed"){
			activityStatusList[Number(tocIndex)].activityProgressStatus =true;
			activityStatusList[Number(tocIndex)].activityAttemptProgressStatus =true;
			activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = true;
		}else if(xmldoc.selectSingleNode("/root/completion_status").text=="incomplete"){
			activityStatusList[Number(tocIndex)].activityProgressStatus =true;
			activityStatusList[Number(tocIndex)].activityAttemptProgressStatus =true;
			activityStatusList[Number(tocIndex)].activityAttemptCompletionStatus = false;
		}
	//---XMLHTTP---
	}
}
/*
function computeTotalTime(){
	var sessionTimeRecArray = cmi_session_time.split(":");
	var totalTimeRecArray = cmi_total_time.split(":");
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
*/




function computeTotalTime(){

	// alert("Session_time = " + cmi_session_time + " / cmi_total_time = " + cmi_total_time);
	//時間格式改成P1Y1M1DT1H1M1S

	//先check是不是ISO的時間格式,再將轉換成ISO格式轉換成hhhh:mm:ss


	//alert("computeTotalTime called!!");
	var tempSessionTime = "";
	var tempTotalTime = "";

	var convertSessionTimeOK = false;
	var convertTotalTimeOK = false;


	if(cmi_session_time!=""){
		if(checkISOTimespan(cmi_session_time)=="true"){
			tempSessionTime = convertISOtime(cmi_session_time);
			convertSessionTimeOK = true;
		}
	}
	//如果totalTime是空白的...ok...因為是initial value

	if(cmi_total_time!=""){
		if(checkISOTimespan(cmi_total_time)=="true"){
			tempTotalTime = convertISOtime(cmi_total_time);
			convertTotalTimeOK = true;
		}
	}else{
		convertTotalTimeOK = true;
	}



	//alert("Session_time = " + cmi_session_time + " / " + convertSessionTimeOK  + " / " +  tempSessionTime  + " / total_time = " + cmi_total_time + " / " + convertTotalTimeOK + " / "+ tempTotalTime);






	//alert("cmi_session_time = " + cmi_session_time + " / tempSessionTime = " + tempSessionTime + " / cmi_total_time = " + cmi_total_time + " / tempTotalTime = " + tempTotalTime);
	//alert("cmi_session_time = " + cmi_session_time + " / cmi_total_time = " + cmi_total_time );

	var sessionTimeRecArray;
	var totalTimeRecArray;
        var strHour=0;
        var strMin=0;
        var strSec=0.00;
        var strTotalSec=0.00;

	//alert("0...strHour = " + strHour + " / strMin = " + strMin + " / strSec = " + strSec);

	if(convertTotalTimeOK && tempTotalTime!=""){
		totalTimeRecArray = tempTotalTime.split(":");
		strHour = Number(totalTimeRecArray[0]);
		strMin = Number(totalTimeRecArray[1]);
		strSec = Number(totalTimeRecArray[2]);

	}

	//alert("1...strHour = " + strHour + " / strMin = " + strMin + " / strSec = " + strSec);


	if(convertSessionTimeOK){
		sessionTimeRecArray = tempSessionTime.split(":");
	        strHour = strHour + Number(sessionTimeRecArray[0]);
	        strMin = strMin + Number(sessionTimeRecArray[1]);
	        strSec = strSec + Number(sessionTimeRecArray[2]);

}

	//alert("2...strHour = " + strHour + " / strMin = " + strMin + " / strSec = " + strSec);


        strTotalSec = (strHour * 3600) + (strMin * 60) + strSec;



        //alert("strTotalSec = " + strTotalSec);



        tempTotalTime = convertTotalSeconds(strTotalSec);

        totalTimeRecArray = tempTotalTime.split(":");
        //socool 04.12.21 為0的屬性不show
        var tempISOTotalTime = "PT";
        //var tempISOTotalTime = "PT" + totalTimeRecArray[0] + "H" + totalTimeRecArray[1] + "M" + totalTimeRecArray[2] + "S"

        var tempH = Number(totalTimeRecArray[0]);
        var tempM = Number(totalTimeRecArray[1]);
        var tempS = Number(totalTimeRecArray[2]);
        //var tempM = totalTimeRecArray[1].toString().replace("","0");
        //var tempS = totalTimeRecArray[2].toString().replace("","0");


        if(tempH != 0){
        	tempISOTotalTime = tempISOTotalTime + tempH.toString() + "H";
        }
        if(tempM != 0){
        	tempISOTotalTime = tempISOTotalTime + tempM.toString() + "M";
        }

        if(tempS != 0){
        	tempISOTotalTime = tempISOTotalTime + tempS.toString() + "S";
        }

        //socool 24.12.24 total_time may not be = HMS , ex:=P29D ,此時.就不用轉,直接= session_time
        if(tempISOTotalTime.indexOf("NaN") != "-1"){
        	tempISOTotalTime = cmi_session_time;
        }

        // alert(tempISOTotalTime + '' + cmi_session_time);

        return tempISOTotalTime;



}


function convertISOtime(ISOtime){
	//alert("ISOtime="+ISOtime);
	//將ISO的時間格式轉換成系統記錄的時間格式
	//P1Y2M3DT4H5M6S -> 一年,兩個月,三天,4個小時,5分鐘,6秒
	//P和T是separator沒有作用
	//轉換成hhhh:mm:ss
	//m跟s不用轉換....ymd都要換成小時

	//大小寫轉換
	ISOtime = ISOtime.toUpperCase();

	//先check是不是iso時間

	if(ISOtime.indexOf(":")==-1){

		/* 1.先找T  */
		if(ISOtime.indexOf("T")!=-1){


			var temp_Array = ISOtime.split("T");

			//會有兩個array	 P..Y..M..D   ..H..M..S包含沒有YMD的類型...因為temp_Array[0] = P

			var tempYear = 0 ;
			var tempMonth = 0 ;
			var tempDay =0 ;
			var tempHour =0;
			var tempMinute = 0;
			var tempSecond = 0;


			if(temp_Array[0].indexOf("Y")!=-1){
				tempYear = Number(temp_Array[0].slice(0, temp_Array[0].indexOf("Y") ));
			}

			if(temp_Array[0].indexOf("M")!=-1){
				if(temp_Array[0].indexOf("Y")!=-1){
					/* 有年也有月  */
					tempMonth = Number(temp_Array[0].slice( temp_Array[0].indexOf("Y")+1 , temp_Array[0].indexOf("M") ));
				}else{
					/* 只有月  */
					tempMonth = Number(temp_Array[0].slice( 0, temp_Array[0].indexOf("M") ));
				}
			}

			if(temp_Array[0].indexOf("D")!=-1){
				if(temp_Array[0].indexOf("M")!=-1){
					/* 有月有天  */
					tempDay = Number(temp_Array[0].slice( temp_Array[0].indexOf("M")+1, temp_Array[0].indexOf("D") ));
				}else if(temp_Array[0].indexOf("Y")!=-1){
					/* 有年有天  */
					tempDay = Number(temp_Array[0].slice( temp_Array[0].indexOf("Y")+1, temp_Array[0].indexOf("D") ));
				}else{
					/* 只有天  */
					tempDay = Number(temp_Array[0].slice( 0, temp_Array[0].indexOf("D") ));
				}
			}

			//-----------------------------------------------------------------------

			if(temp_Array[1].indexOf("H")!=-1){
				tempHour = Number(temp_Array[1].slice( 0, temp_Array[1].indexOf("H") ));
			}

			if(temp_Array[1].indexOf("M")!=-1){
				if(temp_Array[1].indexOf("H")!=-1){
					/* 有小時也有分  */
					tempMinute = Number(temp_Array[1].slice( temp_Array[1].indexOf("H")+1 , temp_Array[1].indexOf("M") ));
				}else{
					/* 只有分  */
					tempMinute = temp_Array[1].slice( 0, temp_Array[1].indexOf("M") );
				}
			}
			if(temp_Array[1].indexOf("S")!=-1){
				if(temp_Array[1].indexOf("M")!=-1){
					/* 有分有秒  */
					tempSecond = Number(temp_Array[1].slice( temp_Array[1].indexOf("M")+1, temp_Array[1].indexOf("S") ));
				}else if(temp_Array[1].indexOf("H")!=-1){
					/* 有小時有秒  */
					tempSecond = Number(temp_Array[1].slice( temp_Array[1].indexOf("H")+1, temp_Array[1].indexOf("S") ));
				}else{
					/* 只有秒  */
					tempSecond = Number(temp_Array[1].slice( 0, temp_Array[1].indexOf("S") ));
				}
			}




			//-------------------------------------------------------------------------------------

			var rtnVal;
			rtnVal = ((365*tempYear + 30*tempMonth + tempDay)*24 + tempHour) + ":" + tempMinute + ":" + tempSecond;
			//rtnVal = tempYear  + "/" + tempMonth + "/" + tempDay + "/" + tempHour + "/" + tempMinute + "/" + tempSecond;
			return rtnVal;
		}else{

			//只有 P..Y..M..D
			var tempTime = ISOtime;
			var tempYear = 0 ;
			var tempMonth = 0 ;
			var tempDay =0 ;

			if(tempTime.indexOf("Y")!=-1){
				tempYear = Number(tempTime.slice(0, temp_Array[0].indexOf("Y") ));
			}

			if(tempTime.indexOf("M")!=-1){
				if(tempTime.indexOf("Y")!=-1){
					/* 有年也有月  */
					tempMonth = Number(tempTime.slice( tempTime.indexOf("Y")+1 , tempTime.indexOf("M") ));
				}else{
					/* 只有月  */
					tempMonth = Number(tempTime.slice( 0, tempTime.indexOf("M") ));
				}
			}

			if(tempTime.indexOf("D")!=-1){
				if(tempTime.indexOf("M")!=-1){
					/* 有月有天  */
					tempDay = Number(tempTime.slice( tempTime.indexOf("M")+1,tempTime.indexOf("D") ));
				}else if(tempTime.indexOf("Y")!=-1){
					/* 有年有天  */
					tempDay = Number(tempTime.slice( tempTime.indexOf("Y")+1, tempTime.indexOf("D") ));
				}else{
					/* 只有天  */
					tempDay = Number(tempTime.slice( 0, tempTime.indexOf("D") ));
				}
			}
			var rtnVal;
			rtnVal = ((365*tempYear + 30*tempMonth + tempDay)*24 + tempHour) + ":00:00";
			//rtnVal = tempYear  + "/" + tempMonth + "/" + tempDay + "/" + tempHour + "/" + tempMinute + "/" + tempSecond;
			return rtnVal;

		}

	}else{
		//並非ISOtime所以本function不能轉換
		return ISOtime;

	}


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
			lastError = 403; //Vega 2004/2/24
		}else{
			//check是不是有保留字_version,_count,_children
			if(Model.indexOf("_version")!=-1 || Model.indexOf("_children") != -1){
				lastError = 202;

			}else if(Model.indexOf("_count")!=-1){
				lastError = "203";
			}else{
				if(Model.indexOf("cmi")==-1){
					lastError = 401;
				}else{
					lastError = 201;
				}
			}


		}

	}
	//alert("CheckDataModel lastError="+lastError);
}

function tocIDfindIndex(tempitemID){
	var i;
	var foundindex = -1;
	for(i=0;i<tocList.length;i++){
		if(tocList[i].id==tempitemID){
			foundindex = i;
		}
	}
	return foundindex;
}


function GetSCO_ID(){

	return SCO_ID;

}



function SetSCO_ID(tempSCO_ID){

	SCO_ID  = tempSCO_ID;

}


function Set_Objective_by_content(value){
	objective_set_by_content_flag = value;
}

function Get_Objective_by_content(){
	return objective_set_by_content_flag;
}

//add by Heroin 2003.10.07  Update Primary Objective and Target Objective Status
//modifyed Heroin 2004.02.03
function UpdateObjectiveSuccessStatus(tempSCO_ID, Status){
	//alert("UpdateObjectiveSuccessStatus tempSCO_ID="+tempSCO_ID+"  Status="+Status);
 	var i=0;
	for(i=0;i<tocList.length;i++){
		//socool 2004.12.16

		if(tocList[i].id==tempSCO_ID ){
		//if(primaryObjectiveList[i].satisfiedByMeasure=="false"){
			//alert("test = " + primaryObjectiveList[i].satisfiedByMeasure.toString());
			trackingInfoList[i].objectiveProgressStatus=true;
			trackingInfoList[i].objectiveSatisfiedStatus = Status;
 			//sharedObjective not finished
			//Vega 2004.10.20 add p_mapInfo->p_mapInfoList TS 1.3.1 Course-52
			var shardObjective=parent.parent.functions.enfunctions.findWriteTargetObjectiveIndex(primaryObjectiveList[i].objectiveID,"writeStatus");
			if(shardObjective.length>0){
				var sIndex="";
				for (var s=0;s<shardObjective.length ;s++ )
				{
					sIndex = shardObjective[s]
					sharedObjectiveList[sIndex].objectiveSatisfiedStatus=Status;
	 				sharedObjectiveList[sIndex].objectiveProgressStatus=true;
				}
			}
 			/*if(primaryObjectiveList[i].p_mapInfo.targetObjectiveID!="" && primaryObjectiveList[i].p_mapInfo.writeSatisfiedStatus){
 				var shardObjective=parent.parent.functions.enfunctions.findTargetObjectiveIndex(primaryObjectiveList[i].objectiveID);
 				sharedObjectiveList[shardObjective].objectiveSatisfiedStatus=Status;
 				sharedObjectiveList[shardObjective].objectiveProgressStatus=true;
 			}*/

		}
	}
}

//Heroin 2004.03.10
function UpdateObjectiveNormalizedMeasure(tempSCO_ID, score){
 	var i=0;
	for(i=0;i<tocList.length;i++){
		if(tocList[i].id==tempSCO_ID){
			trackingInfoList[i].objectiveMeasureStatus="true";
			trackingInfoList[i].objectiveNormalizedMeasure = score;
 			//sharedObjective not finished
 			//這裡有點怪怪的...writeSatisfiedStatus應該和Measure有關而己
 			//NormalizedMeasure應用writeNormalizedMeasure有關
 			//i好像可以用成tocIndex
 			//modified by Heroin 2004.08.12
			//Vega 2004.10.19 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52
			var shardObjective=parent.parent.functions.enfunctions.findWriteTargetObjectiveIndex(primaryObjectiveList[i].objectiveID,"writeNormalized");
			if(shardObjective.length>0){
				var sIndex="";
				for (var s=0;s<shardObjective.length ;s++ )
				{
					sIndex = shardObjective[s]
					sharedObjectiveList[sIndex].objectiveMeasureStatus="true";
	 				sharedObjectiveList[sIndex].objectiveNormalizedMeasure=score;
				}
				saveGlobalObjectiveInfoToDB(i);
			}
 			/*if(primaryObjectiveList[i].p_mapInfo.targetObjectiveID!="" && primaryObjectiveList[i].p_mapInfo.writeNormalizedMeasure){
 				var shardObjective=parent.parent.functions.enfunctions.findTargetObjectiveIndex(primaryObjectiveList[i].objectiveID);
 				//alert("shardObjective="+shardObjective);
 				sharedObjectiveList[shardObjective].objectiveMeasureStatus="true";
 				sharedObjectiveList[shardObjective].objectiveNormalizedMeasure=score;

 				//Heroin 2004.09.07
 				saveGlobalObjectiveInfoToDB(i);

 			}*/
		}
	}

}

//Heroin 2004.08.19
function UpdateObjectivesStatus(tempSCO_ID, Status, objectiveID){
	//alert("UpdateObjectiveSuccessStatus tempSCO_ID="+tempSCO_ID+"  Status="+Status+"   objectiveID="+objectiveID);
 	var i=0;
	for(i=0;i<objectiveList.length;i++){
		//alert("objectiveList[i].id="+objectiveList[i].id+"     tempSCO_ID="+tempSCO_ID+"   objectiveList[i].objectiveID="+objectiveList[i].objectiveID+"  objectiveID="+objectiveID);
		//Heroin 2004.09.01 check objectiveID & scoID
		var tempSCO_index = objectiveList[i].itemIndex;

		if(objectiveList[i].objectiveID==objectiveID && tocList[tempSCO_index].id == tempSCO_ID){
			//alert("//objectiveIndex="+i+"  objSCOID="+parent.tocList[tempSCO_index].id+"  tempSCO_ID="+tempSCO_ID +" Status="+Status);
			objectiveProgressInfoList[i].objectiveProgressStatus=true;
			objectiveProgressInfoList[i].objectiveSatisfiedStatus = Status;
 			//sharedObjective not finished

 			//======Yunghsiao.2004.12.15=========
 			if(objectiveList[i].mapInfoList.length>0){
 				for(var p=0;p<objectiveList[i].mapInfoList.length;p++){
 					if(objectiveList[i].mapInfoList[p].targetObjectiveID!="" && objectiveList[i].mapInfoList[p].writeSatisfiedStatus){
 						var shardObjective = parent.parent.functions.enfunctions.findObjectivesTargetIndex(objectiveList[i].objectiveID,tempSCO_ID);
 						for(var j=0;j<shardObjective.length;j++){
 							sharedObjectiveList[shardObjective[j]].objectiveSatisfiedStatus=Status;
 							sharedObjectiveList[shardObjective[j]].objectiveProgressStatus=true;
 							saveGlobalObjectiveInfoToDB(tempSCO_index);
 						}
 					}
 				}
 			}
 			/*
 			 	if(objectiveList[i].mapInfo.targetObjectiveID!="" && objectiveList[i].mapInfo.writeSatisfiedStatus){

 					//findObjectivesTargetIndex(objectiveID,SCO_ID)
 					//var tempSCO_index = objectiveList[i].itemIndex;

 					var shardObjective=parent.parent.functions.enfunctions.findObjectivesTargetIndex(objectiveList[i].objectiveID,tempSCO_ID);
 					//var shardObjective=parent.parent.functions.enfunctions.findTargetObjectiveIndex(objectiveList[i].objectiveID);
 					//alert("objective target="+shardObjective);
 					sharedObjectiveList[shardObjective].objectiveSatisfiedStatus=Status;
 					sharedObjectiveList[shardObjective].objectiveProgressStatus=true;

 					//Heroin 2004.09.07
 					saveGlobalObjectiveInfoToDB(tempSCO_index);
 				}
 				*/
 			//===================================
		}
	}
}

//objectiveProgressInfo
//Heroin 2004.03.22
function UpdateObjectivesMeasure(tempSCO_ID,objectiveID,score){
 	var i=0;
	for(i=0;i<objectiveList.length;i++){
		//Heroin 2004.09.01
		var tempSCO_index = objectiveList[i].itemIndex;

		//if(objectiveList[i].objectiveID==objectiveID && parent.tocList[tempSCO_index].id == tempSCO_ID){


		if(tocList[tempSCO_index].id==tempSCO_ID && objectiveList[i].objectiveID==objectiveID){
			//alert("000objSCOID="+parent.tocList[tempSCO_index].id+"  tempSCO_ID="+tempSCO_ID+" objectiveID="+objectiveID);
			objectiveProgressInfoList[i].objectiveMeasureStatus="true";
			objectiveProgressInfoList[i].objectiveNormalizedMeasure = score;
 			//sharedObjective not finished
 			//alert("target="+objectiveList[i].mapInfo.targetObjectiveID+" writeNormalizedMeasure="+objectiveList[i].mapInfo.writeNormalizedMeasure);

 			//======Yunghsiao.2004.12.15=============
 			if(objectiveList[i].mapInfoList.length>0){
 				for(var p=0;p<objectiveList[i].mapInfoList.length;p++){
 					if(objectiveList[i].mapInfoList[p].targetObjectiveID!="" && objectiveList[i].mapInfoList[p].writeNormalizedMeasure){
 						var tempSCO_index = objectiveList[i].itemIndex;
 						var shardObjective=parent.parent.functions.enfunctions.findObjectivesTargetIndex(objectiveList[i].objectiveID,tocList[tempSCO_index].id);
 						for(var j=0;j<shardObjective.length;j++){
 							sharedObjectiveList[shardObjective[j]].objectiveMeasureStatus="true";
 							sharedObjectiveList[shardObjective[j]].objectiveNormalizedMeasure=score;
 							saveGlobalObjectiveInfoToDB(tempSCO_index);
 						}
 					}
 				}
 			}
 			/*
 			 if(objectiveList[i].mapInfo.targetObjectiveID!="" && objectiveList[i].mapInfo.writeNormalizedMeasure){

 				var tempSCO_index = objectiveList[i].itemIndex;

				//if(objectiveList[i].objectiveID==objectiveID && tocList[tempSCO_index].id == tempSCO_ID){
 				var shardObjective=parent.parent.functions.enfunctions.findObjectivesTargetIndex(objectiveList[i].objectiveID,tocList[tempSCO_index].id);
 				//alert("shardObjective="+shardObjective);

 				//var shardObjective=parent.parent.functions.enfunctions.findTargetObjectiveIndex(objectiveList[i].objectiveID);
 				sharedObjectiveList[shardObjective].objectiveMeasureStatus="true";
 				sharedObjectiveList[shardObjective].objectiveNormalizedMeasure=score;



 				//Heroin 2004.09.07
 				saveGlobalObjectiveInfoToDB(tempSCO_index);

 			}
 			*/
 			//=======================================
		}
	}
}

//Heroin 2004.03.22  判斷是否大於min,並設定success_status  cmi.scaled_passing_score
function CalObjectiveSuccessStatus(tempSCO_ID,objectiveID, score){
	//1.找到Objective Min Normalized
	//2.比較
	//3.回傳passed or failed
	var SCO_index="";
	for(var i=0;i<tocList.length;i++){
		if(tocList[i].id==tempSCO_ID){
			SCO_index=i;
			break;
		}
	}
	//alert("SCO_index="+SCO_index);
	for(var i=0;i<objectiveList.length;i++){
		//alert("tempSCO_ID="+objectiveList[i].itemIndex+"   "+tempSCO_ID+"  objectiveID="+objectiveList[i].objectiveID+"  "+objectiveID);
		if(objectiveList[i].itemIndex==SCO_index && objectiveList[i].objectiveID==objectiveID){
			var minNormalized=objectiveList[i].minNormalizedMeasure;

			if(score>=minNormalized){
				return("passed");
			}else if(score<minNormalized){
				return("failed");
			}

		}
	}

}
//Heroin-2004.03.22
function UpdateObjectiveStatus(tempSCO_ID, objectiveID, Status){
 	var i=0;
	for(i=0;i<objectiveList.length;i++){
		if(objectiveList[i].itemIndex==tempSCO_ID && objectiveList[i].objectiveID==objectiveID){
			objectiveProgressInfo[i].objectiveProgressStatus="true";
			objectiveProgressInfo[i].objectiveSatisfiedStatus = Status;
 			//sharedObjective not finished
			break;
		}
	}
}

//Heroin-2004.03.22
function UpdateObjectiveMeasure(tempSCO_ID, objectiveID, score){
 	var i=0;
	for(i=0;i<objectiveList.length;i++){
		if(objectiveList[i].itemIndex==tempSCO_ID && objectiveList[i].objectiveID==objectiveID){
			objectiveProgressInfo[i].objectiveMeasureStatus="true";
			objectiveProgressInfo[i].objectiveNormalizedMeasure = score;
 			//sharedObjective not finished
		}
	}

}



//Heroin 2004.03.10  判斷是否大於min,並設定success_status  cmi.scaled_passing_score
function CalSuccessStatus(tempSCO_ID, score){
	cmi_scaled_passing_score=primaryObjectiveList[Number(tocIndex)].minNormalizedMeasure;
	if(score>=cmi_scaled_passing_score){
		return("passed");
	}else if(score<cmi_scaled_passing_score){
		return("failed");
	}


}


function UpdateActivityCompletionStatus(tempSCO_ID, Status){
	var i=0;
	for(i=0;i<tocList.length;i++){
		if(tocList[i].id==tempSCO_ID){

			activityStatusList[i].activityProgressStatus =true;
			activityStatusList[i].activityAttemptProgressStatus =true;
			activityStatusList[i].activityAttemptCompletionStatus = Status;
			//alert("id="+tocList[i].id+" activityAttemptCompletionStatus="+activityStatusList[i].activityAttemptCompletionStatus );
			//modified by Heroin -2003.11.07

		}
	}
}



//Heroin 2004.01.12
//Vega 2004.7.29 maked
/*function changeDM(DataModel){

	//alert("in changeDM "+DataModel);
	switch (DataModel){
		//---------3 ---------
		case "cmi.completion_status":
			DataModel = "cmi.core.completion_status";
			return DataModel;
			break;
		case "cmi.credit":
			DataModel = "cmi.core.credit";
			return DataModel;
			break;
		case "cmi.entry":
			DataModel = "cmi.core.entry";
			return DataModel;
			break;
		case "cmi.exit":
			DataModel = "cmi.core.exit";
			return DataModel;
			break;

		//----------5----------
		case "cmi.learner_id":
			DataModel =  "cmi.core.student_id";
			return DataModel;
			break;
		case "cmi.learner_name":
			DataModel = "cmi.core.student_name";
			return DataModel;
			break;
		//-------6 learner preference-----
		case "cmi.learner_preference._children":
		      	DataModel = "cmi.student_preference._children";
		      	return DataModel;
		      	break;

		case "cmi.learner_preference.audio":
		      	DataModel = "cmi.student_preference.audio";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.audio_level":
		      	DataModel = "cmi.student_preference.audio";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.language":
		      	DataModel = "cmi.student_preference.language";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.speed":
		      	DataModel = "cmi.student_preference.speed";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.delivery_speed":
		      	DataModel = "cmi.student_preference.speed";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.text":
		      	DataModel = "cmi.student_preference.text";
		      	return DataModel;
		      	break;
		case "cmi.learner_preference.audio_captioning":
		      	DataModel = "cmi.student_preference.text";
		      	return DataModel;
		      	break;
		//---------7-----------------
		case "cmi.location":
			DataModel = "cmi.core.lesson_location";
			return DataModel;
			break;
		case "cmi.max_time_allowed":
		      	DataModel = "cmi.student_data.max_time_allowed";
		      	return DataModel;
		      	break;
		case "cmi.mode":
			DataModel = "cmi.core.lesson_mode";
			return DataModel;
			break;

		//-------9 score----------
		//case "cmi.core._children":
		//	lastError = 0;
		//	return cmi_core__children;
		//	break;
		case "cmi.score._children":
			DataModel = "cmi.core.score._children";
			return DataModel;
			break;
		case "cmi.score.max":
			DataModel = "cmi.core.score.max";
			return DataModel;
			break;
		case "cmi.score.min":
			DataModel = "cmi.core.score.min";
			return DataModel;
			break;
		case "cmi.score.raw":
			DataModel = "cmi.core.score.raw";
			return DataModel;
			break;
		case "cmi.score.scaled":
			DataModel = "cmi.core.score.normalized";
			return DataModel;
			break;


		//------10----------
		case "cmi.session_time":
			DataModel = "cmi.core.session_time";
			return DataModel;
		      	break;
		case "cmi.success_status":
			DataModel = "cmi.core.success_status";
			return DataModel;
			break;
		case "cmi.suspend_data":
			DataModel = "cmi.suspend_data";
			return DataModel;
			break;
		case "cmi.time_limit_action":
			DataModel = "cmi.student_data.time_limit_action";
			return DataModel;
		      	break;
		case "cmi.total_time":
			DataModel = "cmi.core.total_time";
			return DataModel;
			break;
		default :
			return DataModel;
			break;

	}
}
*/

// Heroin 2003.11.13
function UpdateSharedObjective(objective_index){

	//======Yunghsiao.2004.12.15==========
	if(objectiveList[i].mapInfoList.length>0){
 		for(var p=0;p<objectiveList[i].mapInfoList.length;p++){
			if(objectiveList[objective_index].mapInfoList[p].targetObjectiveID!=""){
				if(objectiveList[objective_index].mapInfoList[p].writeSatisfiedStatus){
					for(var i=0;i<sharedObjectiveList.length;i++){
						if(sharedObjectiveList[i].objectiveID==objectiveList[objective_index].mapInfoList[p].targetObjectiveID){
							sharedObjectiveList[i].objectiveProgressStatus=true;
							sharedObjectiveList[i].objectiveSatisfiedStatus=objectiveProgressInfoList[objective_index].objectiveSatisfiedStatus;
							break;
						}
					}
				}
				if(objectiveList[objective_index].mapInfoList[p].writeNormalizedMeasure){
					for(var i=0;i<sharedObjectiveList.length;i++){
						if(sharedObjectiveList[i].objectiveID==objectiveList[objective_index].mapInfoList[p].targetObjectiveID){
							sharedObjectiveList[i].objectiveMeasureStatus=true;
							sharedObjectiveList[i].objectiveNormalizedMeasure=objectiveProgressInfoList[objective_index].objectiveNormalizedMeasure;
							break;
						}
					}
				}
			}
		}
	}
	/*
		if(objectiveList[objective_index].mapInfo.targetObjectiveID!=""){
		//Satisfied Statsu
		if(objectiveList[objective_index].mapInfo.writeSatisfiedStatus){
			for(var i=0;i<parent.sharedObjectiveList.length;i++){
				if(sharedObjectiveList[i].objectiveID==objectiveList[objective_index].mapInfo.targetObjectiveID){
					sharedObjectiveList[i].objectiveProgressStatus=true;
					sharedObjectiveList[i].objectiveSatisfiedStatus=objectiveProgressInfoList[objective_index].objectiveSatisfiedStatus;
					//alert("objectiveStatus="+objectiveProgressInfoList[objective_index].objectiveSatisfiedStatus);
					break;
				}
			}
		}

		//Normalized Measure
		if(objectiveList[objective_index].mapInfo.writeNormalizedMeasure){
			for(var i=0;i<parent.sharedObjectiveList.length;i++){
				if(sharedObjectiveList[i].objectiveID==parent.objectiveList[objective_index].mapInfo.targetObjectiveID){
					sharedObjectiveList[i].objectiveMeasureStatus=true;
					sharedObjectiveList[i].objectiveNormalizedMeasure=objectiveProgressInfoList[objective_index].objectiveNormalizedMeasure;
					break;
				}
			}
		}
	}
	*/
	//======================================


	//Heroin 2004.09.07
 	//saveGlobalObjectiveInfoToDB(tempSCO_index);

}

//Heroin -2004.02.12
//check 是否為整數
function checkInteger(ts){
	var checkInt=Number(ts);
	var modeInt = checkInt % 1;
	var checkResult;
	if(modeInt != 0){
		checkResult = false;
		return checkResult;

	}else{
		checkResult = true;
		return checkResult;

	}
}

//check 為最多小數以下兩位
function checkSeconds(ts){
	var checkSec=Number(ts)*100;
	var modeInt = checkSec % 1;
	var checkResult;
	if(modeInt != 0){
		checkResult = false;
		return checkResult;

	}else{
		checkResult = true;
		return checkResult;

	}

}


//Heroin -2004.01.15
function checkISOTimespan(ts){
	var TS=ts.toUpperCase();
	//type: P1Y1M1DT1H1M1S
	var checkResault="false";
	if(TS.charAt(0)!="P"){
		return checkResault;
	}else{
		var tempDate="";
		var tempTime="";
		checkResault="true";
		var tempT=TS.indexOf("T");
		if(tempT!=-1){
			var t1=tempT-1;
			tempDate=TS.substring(1,tempT);
			var t2=tempT+1;
			var t3=TS.length;
			tempTime=TS.substring(tempT+1,TS.length);
			//Heroin 2004.06.09
			if(tempTime==""){
				checkResault="false";
			}

		}
		else{
			var t4=TS.length;
			tempDate=TS.substring(1,TS.length);
		}
		//alert("tempDate="+tempDate);
		//alert("tempTime="+tempTime);

		//check Day
		if(tempDate!=""){
			if(tempDate.indexOf("Y")!=-1){
				for(var i=1; i<tempDate.length;i++){
					if(tempDate.charAt(i)=="Y"){
						var tempYindex=i;
						var tempY=tempDate.substr(0,tempYindex);
						tempDate=tempDate.substring(tempYindex+1,tempDate.length);
						if(typeof(Number(tempY))=="number" && Number(tempY)!="NaN" && tempY!="" && tempY!=" "){
							if(Number(tempY)>=0){
								if(checkInteger(tempY)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}

							}else{
								//alert("01");
								checkResault="false";
							}
						}else{
							//alert("02");
							checkResault="false";
						}
						break;
					}
				}
			}
			if(tempDate.indexOf("M")!=-1){
				for(var i=1; i<tempDate.length;i++){
					if(tempDate.charAt(i)=="M"){
						var tempMindex=i;
						var tempM=tempDate.substr(0,tempMindex);
						tempDate=tempDate.substring(tempMindex+1,tempDate.length);
						if(typeof(Number(tempM))=="number" && Number(tempM)!="NaN" && tempM!="" && tempM!=" "){
							if(Number(tempM)>=0){
								if(checkInteger(tempM)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}
							}else{
								//alert("03");
								checkResault="false";
							}
						}else{
							//alert("04");
							checkResault="false";
						}

						break;
					}
				}
			}
			if(tempDate.indexOf("D")!=-1){
				for(var i=1; i<tempDate.length;i++){
					if(tempDate.charAt(i)=="D"){
						var tempDindex=i;
						var tempD=tempDate.substr(0,tempDindex);
						tempDate=tempDate.substring(tempDindex+1,tempDate.length);
						if(typeof(Number(tempD))=="number" && Number(tempD)!="NaN" && tempD!="" && tempD!=" "){
							if(Number(tempD)>=0){
								if(checkInteger(tempD)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}
							}else{
								//alert("05");

								checkResault="false";
							}
						}else{
							checkResault="false";
						}
						break;
					}
				}
			}

			//alert("tempDate="+tempDate+" -should be null");
			if(tempDate!=""){
				checkResault="false";
			}


		}

		//check Time
		if(tempTime!=""){

			if(tempTime.indexOf("H")!=-1){
				for(var i=1; i<tempTime.length;i++){
					if(tempTime.charAt(i)=="H"){
						var tempHindex=i;
						var tempH=tempTime.substr(0,tempHindex);
						tempTime=tempTime.substring(tempHindex+1,tempTime.length);
						if(typeof(Number(tempH))=="number" && Number(tempH)!="NaN" && tempH!="" && tempH!=" "){
							if(Number(tempH)>=0){
								if(checkInteger(tempH)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}
							}else{

								//alert("07");

								checkResault="false";
							}
						}else{
							//alert("08");
							checkResault="false";
						}
						break;
					}
				}
			}
			if(tempTime.indexOf("M")!=-1){
				for(var i=1; i<tempTime.length;i++){
					if(tempTime.charAt(i)=="M"){
						var tempMindex=i;
						var tempM=tempTime.substr(0,tempMindex);
						tempTime=tempTime.substring(tempMindex+1,tempTime.length);
						//alert("tempM="+tempM);
						if(typeof(Number(tempM))=="number" && Number(tempM)!="NaN" && tempM!="" && tempM!=" "){
							if(Number(tempM)>=0){
								if(checkInteger(tempM)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}
							}else{
								//alert("09");
								checkResault="false";
							}
						}else{
							//alert("10");
							checkResault="false";
						}
						break;
					}
				}
			}
			if(tempTime.indexOf("S")!=-1){
				for(var i=1; i<tempTime.length;i++){
					if(tempTime.charAt(i)=="S"){
						var tempSindex=i;
						var tempS=tempTime.substr(0,tempSindex);
						tempTime=tempTime.substring(tempSindex+1,tempTime.length);
						if(typeof(Number(tempS))=="number" && Number(tempS)!="NaN" && tempS!="" && tempS!=" "){
							if(Number(tempS)>=0){
								if(checkSeconds(tempS)== true){
									//checkResault="true";
								}else{
									checkResault="false";
								}
							}else{
								//alert("11");
								checkResault="false";
							}
						}else{
							//alert("12");
							checkResault="false";
						}
						break;
					}
				}
			}

			//alert("tempTime="+tempTime+" -should be null");
			if(tempTime!=""){
				checkResault="false";
			}
		}
	return checkResault;
	}

}

//Heroin 2004.06.09
function checkFillin2(fillinString,fullString){
	//alert("fillinString="+fillinString);
	//{case_matters=}{order_matters=}{lang=}<XXX>
	if(fillinString.indexOf("{lang=")!=-1){
		var tempValue=fillinString;
		var tempA=tempValue.indexOf("{lang=");
		var tempB=tempValue.substring(tempA+6,tempValue.length);
		var tempC=tempB.indexOf("}");
		var lanCode=tempB.substring(0,tempC);

		var checkType=checkISOLang(lanCode);
		if(checkType.toString()=="false"){
			return "false";
		}

	}

	if(fillinString.indexOf("{case_matters=")!=-1){
		var tempValue=fillinString;
		var tempA=tempValue.indexOf("{case_matters=");
		var tempB=tempValue.substring(tempA+14,tempValue.length);
		var tempC=tempB.indexOf("}");
		var case_matters=tempB.substring(0,tempC);

		if(case_matters.toString()=="invalid"){
			if(fullString.indexOf("{case_matters=true}")==-1 && fullString.indexOf("{case_matters=false}{")==-1 ){
				return "false";
			}else{
				return "true";
			}
		}

		if(case_matters.toString()!="true" && case_matters.toString()!="false"){
			return "false";
		}

	}

	if(fillinString.indexOf("{order_matters=")!=-1){
		var tempValue=fillinString;
		var tempA=tempValue.indexOf("{order_matters=");
		var tempB=tempValue.substring(tempA+15,tempValue.length);
		var tempC=tempB.indexOf("}");
		var order_matters=tempB.substring(0,tempC);
		//alert("order_matters="+order_matters);
		if(order_matters.toString()!="true" && order_matters.toString()!="false"){
			return "false";
		}

	}

}

function checkFillin(fillinString){
	//最多有三個{xxx=yyy}
	//1.
	var fillinArray1 = fillinRecursive(fillinString);
	//alert("fillinArray1="+ fillinArray1[0]+"   "+fillinArray1[1]);
	if(fillinArray1[1]=="false"){
		return fillinArray1;
	}else{
		//2.
		var fillinArray2 = fillinRecursive(fillinArray1[0]);
		//alert("fillinArray2="+ fillinArray2[0]+"   "+fillinArray2[1]);
		if(fillinArray2[1]=="false"){
			return fillinArray2;
		}else{
			var fillinArray3 = fillinRecursive(fillinArray2[0]);
			//alert("fillinArray3="+ fillinArray3[0]+"   "+fillinArray3[1]);
			return fillinArray3;
		}
	}
}

//Heroin
function fillinRecursive(fillinTempString,fillinResult){


	var fillArray = new Array(2);
	fillArray[0] = fillinTempString;
	fillArray[1] = "true";

	if(fillinTempString.indexOf("{")==-1){
		//沒有{ 直接分[,]判斷
		return fillArray;

	}else if(fillinTempString.indexOf("{")==0){

		/* 判斷三個 */
		if(fillinTempString.indexOf("{lang=")==0){
			var tempValue=fillinTempString;
			var tempA=tempValue.indexOf("{lang=");
			var tempB=tempValue.substring(tempA+6,tempValue.length);
			var tempC=tempB.indexOf("}");
			var lanCode=tempB.substring(0,tempC);

			var checkType=checkISOLang(lanCode);
			if(checkType.toString()=="false"){
				fillArray[1] = "false";
				return fillArray;
			}else{
				/* 切字串  */
				fillArray[0]=fillinTempString.substring(7+tempC,fillinTempString.length);
				//alert("fillArray[0]="+fillArray[0]);
				return fillArray;
			}

		}else if(fillinTempString.indexOf("{order_matters=")==0){

			var tempA=fillinTempString;
			var tempB=tempA.indexOf("{order_matters=");
			var tempC=tempA.substring(15,tempA.length);
			var tempD=tempC.indexOf("}");
			var order_matters=tempC.substring(0,tempD);
			//alert("order_matters="+order_matters);
			if(order_matters.toString()!="true" && order_matters.toString()!="false"){
				fillArray[1] = "false";
				return fillArray;
			}else{
				fillArray[0]=fillinTempString.substring(16+tempD,fillinTempString.length);
				//alert("fillArray[0]="+fillArray[0]);
				return fillArray;
			}

		}else if(fillinTempString.indexOf("{case_matters=")==0){
			var tempValue=fillinTempString;
			var tempA=tempValue.indexOf("{case_matters=");
			var tempB=tempValue.substring(tempA+14,tempValue.length);
			var tempC=tempB.indexOf("}");
			var case_matters=tempB.substring(0,tempC);
			//alert("case_matters="+case_matters);
			if(case_matters.toString()!="true" && case_matters.toString()!="false" && case_matters.toString()!="invalid"){
				fillArray[1] = "false";
				return fillArray;
			}else{
				fillArray[0]=fillinTempString.substring(15+tempC,fillinTempString.length);
				//alert("fillArray[0]="+fillArray[0]);
				return fillArray;
			}

		}else{

			//有"{"但是不是上面三種case
			//fillArray[1] = "false";
			return fillArray;
		}
	}else{
		//有"{"但是不是在字串開始處
		//判斷是不是[,]{lang=xx}


		//fillArray[1]="false";
		return fillArray;
	}

}


//Heroin 2004.06.14
function checkMatching(tempValue){
	//格式 xxx[.]yyy

	var temp_result="true";
	var tempValueArray = tempValue.split("[.]");
	if(tempValueArray.length != 2){
		temp_result="false";
		return temp_result;
	}

	if(tempValueArray[0]=="" || tempValueArray[1]==""){
		temp_result="false";
		return temp_result;
	}
	if(String(tempValueArray[0]).indexOf("{")!=-1 || String(tempValueArray[0]).indexOf("}")!=-1 || String(tempValueArray[0]).indexOf("[")!=-1 || String(tempValueArray[0]).indexOf("]")!=-1){
		temp_result="false";
		return temp_result;
	}
	if(String(tempValueArray[1]).indexOf("{")!=-1 || String(tempValueArray[1]).indexOf("}")!=-1 || String(tempValueArray[1]).indexOf("[")!=-1 || String(tempValueArray[1]).indexOf("]")!=-1){
		temp_result="false";
		return temp_result;
	}
	return temp_result;
}


//Heroin 2004.06.14
function checkPerformance(tempValue){
	//格式 xxx[.]yyy


	var temp_result="true";
	var tempValueArray = tempValue.split("[.]");
	if(tempValueArray.length != 2){
		temp_result="false";
		return temp_result;
	}

	if(tempValueArray[0]=="" && tempValueArray[1]==""){
		temp_result="false";
		return temp_result;
	}
	if(String(tempValueArray[0]).indexOf("{")!=-1 || String(tempValueArray[0]).indexOf("}")!=-1 || String(tempValueArray[0]).indexOf("[")!=-1 || String(tempValueArray[0]).indexOf("]")!=-1){
		temp_result="false";
		return temp_result;
	}
	if(String(tempValueArray[1]).indexOf("{")!=-1 || String(tempValueArray[1]).indexOf("}")!=-1 || String(tempValueArray[1]).indexOf("[")!=-1 || String(tempValueArray[1]).indexOf("]")!=-1){
		temp_result="false";
		return temp_result;
	}
	return temp_result;
}

function SCOIDTranslator(tempitemID){

	//找resource
	var tempResourceID = "";
	var itemCount = 0;
	var tempIndex = -1;
	for(var i=0;i<resourceList.length;i++){
		if(resourceList[i].itemID==tempitemID){
			tempResourceID = resourceList[i].id;
			break;
		}
	}

	if(tempResourceID!=""){
		for(var j=0;j<resourceList.length;j++){
			if((resourceList[j].id==tempResourceID)){
				itemCount++;
				tempIndex = j;
			}
		}
	}

	//如果itemCount==1代表itemID與resourceID是一對一....儲存在資料庫之SCO_ID用itemID即可
	//如果itemCount>1代表itemID與resourceID是多對一....儲存在資料庫之SCO_ID將用resourceID
	//alert("itemCount = " + itemCount + " / "+ tempResourceID);

	if(itemCount==1){
		return tempitemID;
	}else if(itemCount>1){
		//當itemCount大於1時,代表多個item共用一個resource...,但是還要看有沒有persistState的限制....
		//如果有persistState的限制...就要多個item共寫一筆資料...如果沒有persistState的限制...就還是一個item寫一筆資料

		if(resourceList[tempIndex].persistState.toString()=="true"){
			return tempResourceID;
		}else{
			return tempitemID;
		}
	}



}



//added	by Heroin 2004.08.27
//save global objective Info to DB
//將"所有" shared objective info 存入DB ...
function saveGlobalObjectiveInfoToDB(current_index){
	//alert("saveGlobalObjectiveInfoToDB");

	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	//xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes(0));
	var rootElement	= xmldoc.documentElement;

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",tocList[current_index].id);
	rootElement.setAttribute("Message_Type","globalObective");
	rootElement.setAttribute("Scorm_Type","globalObective");

	//Table: global_objectives

	for(var i=1;i<sharedObjectiveList.length;i++){
		var globalToSystem = GlobalState.GlobalToSystem;  //這裡要再改...
		var objectiveID = sharedObjectiveList[i].objectiveID;
		var objectiveProgressStatus = sharedObjectiveList[i].objectiveProgressStatus.toString();
		var objectiveSatisfiedStatus = sharedObjectiveList[i].objectiveSatisfiedStatus.toString();
		var objectiveMeasureStatus = sharedObjectiveList[i].objectiveMeasureStatus.toString();
		var objectiveNormalizedMeasure = sharedObjectiveList[i].objectiveNormalizedMeasure.toString();
		//alert("存 objectiveID="+objectiveID+"  objectiveProgressStatus="+objectiveProgressStatus+"  objectiveSatisfiedStatus="+objectiveSatisfiedStatus+"  objectiveMeasureStatus="+objectiveMeasureStatus +" objectiveNormalizedMeasure="+objectiveNormalizedMeasure);

		var tempblaElement = xmldoc.createElement("global_objective");
		tempblaElement.setAttribute("objectiveID",objectiveID);
		tempblaElement.setAttribute("objectiveProgressStatus",objectiveProgressStatus);
		tempblaElement.setAttribute("objectiveSatisfiedStatus",objectiveSatisfiedStatus);
		tempblaElement.setAttribute("objectiveMeasureStatus",objectiveMeasureStatus);
		tempblaElement.setAttribute("objectiveNormalizedMeasure",objectiveNormalizedMeasure);
		tempblaElement.setAttribute("globalToSystem",globalToSystem);

		rootElement.appendChild(tempblaElement);


	}


	var ServerSide = xmlhttp_set;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.Open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.Send(xmldoc.xml);

}



//2004.08.09...Henry
//persistState
function PersistStateLMSCommit(){

	//一定要在LMSCommit之後才能呼叫

	var tempResourceID = "";
	var itemCount = 0;
	var tempIndex = -1;

	var ServerSide = xmlhttp_set;
	var XMLHTTPObj = XmlHttp.create();

	for(var i=0;i<resourceList.length;i++){
		if(resourceList[i].itemID==SCO_ID){
			tempResourceID = resourceList[i].id;
			break;
		}
	}

	if(tempResourceID!=""){
		for(var j=0;j<resourceList.length;j++){
			if((resourceList[j].id==tempResourceID)){
				//多個SCO reference到同一個resource ID,而且persistState == true
				//issue Commit XML Document to Server

				if(resourceList[j].persistState.toString()=="true"){


					persistStateXMLDoc.documentElement.setAttribute("sco_ID",resourceList[j].itemID);
					//alert(resourceList[j].itemID + "============" + persistStateXMLDoc.xml);

					XMLHTTPObj.Open("POST",ServerSide,false);
					XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
					XMLHTTPObj.Send(persistStateXMLDoc.xml);

				}

			}
		}
	}


}


parent.parent.API=new InitialObject();
// Heroin 2004.01.12
parent.parent.API_1484_11=new InitialObject();
if( navigator.userAgent.match("MSIE") ){
    setTimeout(function () {
        changeStatus();
    //	show_clock();
    }, 500);
}else{
    window.addEventListener('load',function(){
        setTimeout(function () {
            changeStatus();
        //	show_clock();
        }, 500);
    });
}
/**
bindEvent(this, 'load', function () {
   setTimeout(function () {
        changeStatus();
    //	show_clock();
    }, 500);
});

function bindEvent(el, eventName, eventHandler) {
    debugger;
  if (el.addEventListener){
    el.addEventListener(eventName, eventHandler, false); 
  } else if (el.attachEvent){
    el.attachEvent('on'+eventName, eventHandler);
  }
}
**/
</script>