<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
<meta http-equiv="Content-Language" content="zh-tw" >
<!-- <link rel="stylesheet" type="text/css" href="/theme/default/learn/wm.css" > -->
<?php
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
?>
<style type="text/css">
<!--
a:link {  font-family: "Verdana", "serif"; font-size: 10pt; color: blue; text-decoration: none}
a:hover {  color: #004080; text-decoration: underline; background-color:yellow}
a:visited {  text-decoration: none ; color: green}
body {  font-family: "Verdana", "serif"; font-size: 10pt}
<?php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
?>
body { margin: 0 !important; padding: 0 !important; }
a { display: inline-block; width: 100%; color: #FFF; }
a:link {  font-size: 10pt; color: #FFF; text-decoration: none; }
a:visited { text-decoration: none ; color: #FFF; }
a:hover { color: #000; text-decoration: underline; background-color: #FFF; }
table { color: #FFF; }
table.active a, table.active a:link, table.active a:visited, table.active a:hover { color: #000; }
table:hover, table:hover a { color: #000; background-color: #FFF; }
td.caption { width: 100%; }
#LiveClockIE { display: none; }
<?php
    }
?>
-->
</style>
<title>Course Content</title>
</head>
<body class="cssTbTd">

<form id="fetchResourceForm" name="fetchResourceForm" target="s_main" method="POST" action="/learn/scorm/SCORM_fetchResource.php" style="display: none">
<input type="hidden" name="href" value="">
<input type="hidden" name="isOpenWindow" value="">
<input type="hidden" name="parameter" value="">
<input type="hidden" name="prev_href" value="">
<input type="hidden" name="prev_node_id" value="">
<input type="hidden" name="prev_node_title" value="">
<input type="hidden" name="course_id"       value="<?=sysNewEncode($sysSession->course_id)?>">
<input type="hidden" name="begin_time" value="">
</form>

<!--<script language="javascript" src="/learn/scorm/toc/function.js"></script>-->
<script language="javascript" src="/learn/scorm/toc/ftiens4_pure.js"></script>
<script language="javascript" src="/learn/scorm/toc/ftiens4.js"></script>
<script language="javascript">

	var xmlGetTime = XmlHttp.create();

	function fetchServerTime()
	{
		xmlGetTime.open('GET', '/learn/path/getServerTime.php', false);
		xmlGetTime.send(null);
		if (xmlGetTime.responseText.search(/server_time="([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})"/)){
			document.getElementById('fetchResourceForm').begin_time.value = RegExp.$1;
		}
		else
			alert('get server time failure.');
	}
	fetchServerTime();

	var xmlhttp_get="/learn/scorm/get.php";
	var xmlhttp_set="/learn/scorm/set.php";
	var manifest_file ="/learn/path/SCORM_loadCA.php";


	var course_ID="<?=$sysSession->course_id?>";
	var student_id="<?=$sysSession->username?>";

// ============Yunghsiao.2004.12.02======================================================================================
	var XMLDI = XmlDocument.create();
	XMLDI.async = false;
	XMLDI.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var rootElement = XMLDI.documentElement;
	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	var DataModelElement = XMLDI.createElement("DataModel");
	DataModelElement.appendChild(XMLDI.createTextNode('set.initial.data'));
	rootElement.appendChild(DataModelElement);

	var ServerSide = xmlhttp_get;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml");
	XMLHTTPObj.send(XMLDI.xml);

	XMLDI.loadXML(XMLHTTPObj.responseText);
	var InitialXML = XMLHTTPObj.responseText;

/*  ===============Record ChildNodes所代表的Tag============================================================================
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text; //<lesson_status>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(1).text; //<duration>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(2).text; //<exit_value>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(3).text; //<session_time>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(4).text; //<score_raw>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(5).text; //<score_normalize>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(6).text; //<last_time>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(7).text; //<isSuspended>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(8).text; //<success_status>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(9).text; //<complete_status>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(10).text; //<attempt_count>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(11).text; //<isDisabled>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(12).text; //<isHiddenFromChoice>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(13).text; //<attempt_absolut_duration>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(14).text; //<attempt_experienced_duration>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(15).text; //<activity_absolut_duration>
	XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(16).text; //<activity_experienced_duration>
	===============cmi_objectives ChildNodes所代表Tag======================================================================
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text; //<n>
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(1).text; //<id>
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(2).text; //<score_raw>
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(3).text; //<score_scaled>
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(4).text; //<suscess_status>
	XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']").childNodes.item(5).text; //<complete_status>
	=======================================================================================================================  */

	var xmldoc = XmlDocument.create();
	xmldoc.async = false;

	var SeqEngPath = "/learn/scorm/sequencing/NavEventTransfer.php";

	// ++++++++++++++++++++++++++++++++++++++++++
	/*
	1.先check看看有沒有submanifest...如果有...要調整manifest
	2.先將submanifest之resource整理至主要manifest之resource之中
	*/
	// ++++++++++++++++++++++++++++++++++++++++++
	var preloadXMLDoc = XmlDocument.create();
	preloadXMLDoc.async = false;
	if (typeof preloadXMLDoc.load === "undefined") {
		var xmlHttp = XmlHttp.create();
		xmlHttp.open('GET', manifest_file, preloadXMLDoc.async);
		xmlHttp.send(null);
		if(!preloadXMLDoc.loadXML(xmlHttp.responseText)) {
			alert('Loading XML Failure.');
		}
	} else {
		preloadXMLDoc.resolveExternals = false;
		if(!preloadXMLDoc.load(manifest_file)) {
			alert('Loading XML Failure.');
		}
	}

	var preloadRootElement = preloadXMLDoc.documentElement;
	var manifest_length = Number(preloadXMLDoc.selectNodes("//manifest").length);
	var main_resources_index = 0;
	var submanifest_index = 1;
	var submanifest_resource_index = 0;
	var firstResourcesElement;
	var submanifest_identifier = "";
	var submanifest_ref_length = 0;
	var submanifest_orgs_default = "";
	var submanifestOrgElement;
	var submanifestRefElement;
	var preload_i=0;


	if(manifest_length>1){
		// 要如何verify那一個才是submanifest呢?--> 如果rootElement之identifier等於manifest之identifier
		// 先找到第一個resource
		for(main_resources_index=0;main_resources_index<preloadRootElement.childNodes.length;main_resources_index++){
			if(preloadRootElement.childNodes.item(main_resources_index).nodeName=="resources"){
				firstResourcesElement = preloadRootElement.childNodes.item(main_resources_index);
				break;
			}
		}
		// 將submanifest的resource,剪下後加入rootElement之中
		// 找出reference到submanifest的item
		// 方法一
		// item-->identifierref-->resource identifier --> submanifest --> 需要很多時間
		// 不可以直接用findIdref..因為toc還沒有load進來
		// 方法二(簡化)
		// submanifest identifier -->  item identifierref


		var tempsubmanifestElement;
		for(submanifest_index=1;submanifest_index<manifest_length;submanifest_index++){
			tempsubmanifestElement = preloadRootElement.removeChild(preloadXMLDoc.selectNodes("//manifest").item(submanifest_index));
			submanifest_identifier = tempsubmanifestElement.attributes.getNamedItem("identifier").text;


			// merge submanifest resource into main manifest
			if(tempsubmanifestElement.selectNodes("//resource").length>0){
				for(submanifest_resource_index=0;submanifest_resource_index<tempsubmanifestElement.selectNodes("//resource").length;submanifest_resource_index++){
					// alert(tempsubmanifestElement.selectNodes("//resource").item(submanifest_resource_index).xml);
					firstResourcesElement.appendChild(tempsubmanifestElement.selectNodes("//resource").item(submanifest_resource_index));
				}
			}

			// submanifest identifier -->  item identifierref
			submanifestRefElement = preloadRootElement.selectSingleNode("//item[@identifierref='"+ submanifest_identifier +"']");
			submanifest_ref_length = preloadRootElement.selectNodes("//item[@identifierref='"+ submanifest_identifier +"']").length;
			if(submanifest_ref_length>0){
				// submanifest identifier --> organizations default --> organization identifier -> combine(將所有的子元素剪下) --> 將identifierref除去
				submanifest_orgs_default = tempsubmanifestElement.selectSingleNode("//organizations").attributes.getNamedItem("default").text;

				submanifestOrgElement = tempsubmanifestElement.selectSingleNode("//organization[@identifier='"+ submanifest_orgs_default +"']");

				for(preload_i=0;preload_i<submanifestOrgElement.childNodes.length;preload_i++){
					submanifestRefElement.appendChild(submanifestOrgElement.childNodes.item(preload_i).cloneNode(true));
				}

				// 將identifierref除去
				submanifestRefElement.attributes.removeNamedItem("identifierref").text;

			}


		}

		xmldoc.loadXML(preloadRootElement.xml);
	}else{
		xmldoc.loadXML(preloadXMLDoc.xml);
	}
	xmldoc.setProperty('SelectionNamespaces', 'xmlns:imsss="http://www.imsglobal.org/xsd/imsss"');
	var SCORM_VER = GetSCORMVetsion();

	// Copyrightc 2002. III Productions. All rights reserved.
	// **Start Encode**

	// Global variables
	var index = 0;
	var tocList = new Array();
	var OrganizationID;

	// 紀錄因為rollUp而改變Status的CA
	var rollUpIndex = 0;
	var rollUpList = new Array();

	// -----draw_status
	var draw_status = false;

	// --------timer-----------
	function Timer(itemIndex,sec,TimerID,startTime,endTime){
		this.itemIndex = itemIndex;
		this.sec = sec;
		this.TimerID = TimerID;
		this.startTime = startTime;
		this.endTime = endTime;
		this.startTimer = startTimer;
		this.suspendTimer = suspendTimer;
		this.endTimer = endTimer;
		this.count = count;

	}
	var SCOTimerList = new Array;
	var SCOTimerListIndex = 0;


	// -------adl nav request-----------------------
	function ADLNavRequest(p, c){
		this.p = p;
		this.c = c;
	}
	var ADLNavRequestObj = new ADLNavRequest("false","false");

	// --------Global State Model-------------------

	// Suspended Activity用分號隔開
	function GlobalState(CurrentActivity,SuspendedActivity,GlobalToSystem){
		this.CurrentActivity = CurrentActivity;
		this.SuspendedActivity = SuspendedActivity;
		this.GlobalToSystem = GlobalToSystem;
	}
	var GlobalStateObj = new GlobalState("","","true");


	// --------Random-------------------------------

	var RandomResultArray = new Array();
	var RandomResultArrayIndex = 0;
	var RandomResultIDArray = new Array();
	var RandomResultIDArrayIndex = 0;
	var ReorderParentIDArray = new Array();
	var ReorderParentIDArrayIndex = 0;
	var ReorderChildIDArray = new Array();
	var ReorderChildIDArrayIndex = 0;


	var randomResultXML = "";


	function InitRandomizeControlObj(){
		this.Randomize = Randomize;
		this.CheckRandomizeChildren = CheckRandomizeChildren;

	}

	var randomizeObj = new InitRandomizeControlObj();



	// --------historyRoute---------------------------
	var historyRouteList = new Array();
	var historyRouteIndex = 0;
	function history(itemIndex){
		this.itemIndex = itemIndex;

	}
	// ----------------sharedObjective---------------
	var sharedObjectiveList = new Array();
	var sharedObjectiveIndex = 0;
	function sharedObjective(objectiveID,objectiveProgressStatus,objectiveSatisfiedStatus,objectiveMeasureStatus,objectiveNormalizedMeasure){
		this.objectiveID = objectiveID;
		this.objectiveProgressStatus = objectiveProgressStatus; // False
		this.objectiveSatisfiedStatus = objectiveSatisfiedStatus; // False
		this.objectiveMeasureStatus = objectiveMeasureStatus; // False
		this.objectiveNormalizedMeasure = objectiveNormalizedMeasure; // 0.0
	}


	// --------concurrentActivity---------------------------
	var concurrentActivityList = new Array();
	var concurrentActivityIndex = 0;
	function concurrentActivity(itemIndex,activityName,activityRef){
		this.itemIndex = itemIndex;
		this.activityName = activityName;
		this.activityRef=activityRef;
	}

	var lang = "<?=$sysSession->lang?>";
	function getTitle(str) {
		if (str) {
			var a = str.split('\t');
			switch(lang){
				case 'GB2312'		: return a[1] ? a[1] : a[0];
				case 'en'			: return a[2] ? a[2] : a[0];
				case 'EUC-JP'		: return a[3] ? a[3] : a[0];
				case 'user_define'	: return a[4] ? a[4] : a[0];
				default: return a[0];
			}
		}
		else
			return 'No Title';
	}
	// -----------------------------------tocList-----------------------------------------------------

	// TOC object constructor
	function toc(id,title,idref,parentID,parameter,isvisible,disable,folderIsvisible,itemStatus,itemType,persistState,exit_value, target){
		 this.InitialStatus = InitialStatus;
		 this.id = id;
		 this.title = title;
		 this.idref = idref;
		 this.parentID = parentID;
		 this.parameter = parameter;
		 this.isvisible = isvisible;
		 this.disable = disable;
		 this.folderIsvisible = folderIsvisible;
		 this.itemStatus = itemStatus;
		 this.itemType = itemType;
		 this.persistState = persistState;
		 this.exit_value = exit_value;
		 this.target = target;
	}

	var tocDisplayList = new Array();
	var isvisibleCount = 0;
	function tocDisplay(id,isvisibleIndex,strDisplayParentID,strSelfLevelID,strParentLevelID,isShow, isChoice, buffer, DisplayItemType){
		 this.id = id;
		 this.isvisibleIndex = isvisibleIndex;
		 this.DisplayparentID = strDisplayParentID;
		 this.selflevelID = strSelfLevelID;
		 this.parentlevelID = strParentLevelID;
		 this.isShow = isShow;
		 this.isChoice = isChoice;
		 this.buffer = buffer;
		 this.DisplayItemType = DisplayItemType;
	}

	var resourceList = new Array();
	var resourceIndex = 0;
	function resource(id,itemID,href,persistState){
		this.id = id;
		this.itemID = itemID;
		this.href = href;
		this.persistState = persistState;

	}



	var isHiddenFromChoiceList = new Array();
	function isHiddenFromChoice(value){
		this.value = value;
	}

	var actionStatusList = new Array();
	function actionStatus(id,isDisabled,isSkip,isHiddenFromChoice,isStopForwardTraversal){
		 this.id = id;
		 this.isDisabled = isDisabled;
		 this.isSkip = isSkip;
		 this.isHiddenFromChoice = isHiddenFromChoice;
		 this.isStopForwardTraversal = isStopForwardTraversal;
	}



	// ----------precondition.condition="always"-------

	var alwaysPrecondtionsList = new Array();
	var alwaysPrecondtionsCount = 0;
	function alwaysPrecondtions(ruleIndex,itemIndex,action){
		this.ruleIndex = ruleIndex;
		this.itemIndex = itemIndex;
		this.action = action;
	}

	var miniTimeOutList = new Array();
	function miniTimeOut(limit,time,setTimeoutID,minTimeLimitType){
		 this.limit = limit;
		 this.time = time;
		 this.setTimeoutID = setTimeoutID;
		 this.minTimeLimitType = minTimeLimitType;
	}

	// --------API status--------


	var API_finished = false;

	function APIstatus(API_finished){
		this.API_finished = API_finished;
	}
	var APIstatusObj = new APIstatus(API_finished);


	// -----------------------------------------Sequencing 1.0 ------------------------------------------------

	// ------------controlMode-------------------------------
	var controlModeList = new Array();
	var controlModeListIndex = 0;
	function controlMode(existflag, choice,choiceExit,flow,forwardOnly,useCurrentAttemptObjectiveInfo,useCurrentAttemptProgressInfo){
		this.existflag = existflag;
		this.choice = choice;
		this.choiceExit = choiceExit;
		this.flow = flow;
		this.forwardOnly = forwardOnly;
		this.useCurrentAttemptObjectiveInfo = useCurrentAttemptObjectiveInfo;
		this.useCurrentAttemptProgressInfo = useCurrentAttemptProgressInfo;
	}

	// ------------sequencingRules---------------------------
	// preConditionRule,exitConditionRule,postConditionRule都有可能有多個
	// 因為一個ConditionRule可能會有多個condition所以用flag來區別,同時每個值用*號隔開
	var preConditionRuleList = new Array();
	var preConditionRuleListIndex = 0;
	function preConditionRule(multiflag,itemIndex,referencedObjective,measureThreshold,operator,condition,action,conditionCombination){
		this.multiflag=multiflag;
		this.itemIndex=itemIndex;
		this.referencedObjective=referencedObjective;
		this.measureThreshold=measureThreshold;
		this.operator=operator;
		this.condition=condition;
		this.action=action;
		this.conditionCombination=conditionCombination;
	}

	var exitConditionRuleList = new Array();
	var exitConditionRuleListIndex = 0;
	function exitConditionRule(multiflag,itemIndex,referencedObjective,measureThreshold,operator,condition,action,conditionCombination){
		this.multiflag=multiflag;
		this.itemIndex=itemIndex;
		this.referencedObjective=referencedObjective;
		this.measureThreshold=measureThreshold;
		this.operator=operator;
		this.condition=condition;
		this.action=action;
		this.conditionCombination=conditionCombination;
	}


	var postConditionRuleList = new Array();
	var postConditionRuleListIndex = 0;
	function postConditionRule(multiflag,itemIndex,referencedObjective,measureThreshold,operator,condition,action,conditionCombination){
		this.multiflag=multiflag;
		this.itemIndex=itemIndex;
		this.referencedObjective=referencedObjective;
		this.measureThreshold=measureThreshold;
		this.operator=operator;
		this.condition=condition;
		this.action=action;
		this.conditionCombination=conditionCombination;
	}


	// ------------limitConditions---------------------------
	var limitConditionsList = new Array();
	var limitConditionsListIndex = 0;
	function limitConditions(existflag,attemptLimit,attemptAbsoluteDurationLimit,attemptExperiencedDurationLimit,activityAbsoluteDurationLimit,activityExperiencedDurationLimit,beginTimeLimit,endTimeLimit){
		this.existflag = existflag;
		this.attemptLimit = attemptLimit;
		this.attemptAbsoluteDurationLimit = attemptAbsoluteDurationLimit;
		this.attemptExperiencedDurationLimit = attemptExperiencedDurationLimit;
		this.activityAbsoluteDurationLimit = activityAbsoluteDurationLimit;
		this.activityExperiencedDurationLimit = activityExperiencedDurationLimit;
		this.beginTimeLimit = beginTimeLimit;
		this.endTimeLimit = endTimeLimit;

	}


	// ------------auxiliaryResources------------------------
	// 可能同時會有多個auxiliaryResource
	var auxiliaryResourceList = new Array();
	var auxiliaryResourceListIndex = 0;
	function auxiliaryResource(itemID,auxiliaryResourceID,purpose,href){
		this.itemID = itemID;
		this.auxiliaryResourceID = auxiliaryResourceID;
		this.purpose = purpose;
		this.href = href;
	}


	// ------------rollupRules(rollup Control)-------------------------------
	var rollupRulesList = new Array();
	var rollupRulesListIndex = 0;

	function rollupRules(existflag,rollupObjectiveSatisfied,rollupProgressCompletion,objectiveMeasureWeight){
		this.existflag = existflag;
		this.rollupObjectiveSatisfied=rollupObjectiveSatisfied;
		this.rollupProgressCompletion=rollupProgressCompletion;
		this.objectiveMeasureWeight=objectiveMeasureWeight;
	}

	var rollupRuleList = new Array();
	var rollupRuleListIndex = 0;

	// 可能會有多個condition所以有multiflag,同時每個值用*號隔開
	function rollupRule(multiflag,itemIndex,operator,condition,action,childActivitySet,minimumCount,minimumPercent,conditionCombination){
		this.multiflag=multiflag;
		this.itemIndex=itemIndex;
		this.operator=operator;
		this.condition=condition;
		this.action=action;
		this.childActivitySet=childActivitySet;
		this.minimumCount=minimumCount;
		this.minimumPercent=minimumPercent;
		this.conditionCombination=conditionCombination;
	}

	var rollupRule_flagList = new Array();
	function rollupRule_flag(isObjective,isActivity,isRollupControl){
		this.isObjective=isObjective;
		this.isActivity=isActivity;
		this.isRollupControl=isRollupControl;
	}


	function rollupConsideration(measureSatisficationIfActive,requiredForSatisfied,requiredForNotSatisfied,requiredForCompleted,requiredForIncomplete){
		this.measureSatisficationIfActive = MeasureSatisficationIfActive;
		this.requiredForSatisfied = requiredForSatisfied;
		this.requiredForNotSatisfied = requiredForNotSatisfied;
		this.requiredForCompleted = requiredForCompleted;
		this.requiredForIncomplete = requiredForIncomplete;
	}


	// ------------objectives--------------------------------
		// 原本每個primary objective 對應單一 p_mapInfo
		// 應改成一個objective 可對應多個 p_mapInfo...

		var primaryObjectiveList = new Array();
		var primaryObjectiveListIndex = 0;
		function primaryObjective(existflag,objectiveID,satisfiedByMeasure,minNormalizedMeasure,p_mapInfoList,isAutoCreate){
			this.existflag = existflag;
			this.objectiveID = objectiveID;
			this.satisfiedByMeasure = satisfiedByMeasure;
			this.minNormalizedMeasure = minNormalizedMeasure;
			this.p_mapInfoList = p_mapInfoList;
			this.isAutoCreate = isAutoCreate;
		}


			function p_mapInfo(targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure){
				this.targetObjectiveID = targetObjectiveID;
				this.readSatisfiedStatus = readSatisfiedStatus;
				this.readNormalizedMeasure = readNormalizedMeasure;
				this.writeSatisfiedStatus = writeSatisfiedStatus;
				this.writeNormalizedMeasure = writeNormalizedMeasure;
			}


		var objectiveList = new Array();
		var objectiveListIndex = 0;
		function objective(itemIndex,objectiveID,satisfiedByMeasure,minNormalizedMeasure,mapInfoList){
			this.itemIndex = itemIndex;
			this.objectiveID = objectiveID;
			this.satisfiedByMeasure = satisfiedByMeasure;
			this.minNormalizedMeasure = minNormalizedMeasure;
			this.mapInfoList = mapInfoList;
		}
		// =============================================================================================
		function mapInfo(targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure){
			this.targetObjectiveID = targetObjectiveID;
			this.readSatisfiedStatus = readSatisfiedStatus;
			this.readNormalizedMeasure = readNormalizedMeasure;
			this.writeSatisfiedStatus = writeSatisfiedStatus;
			this.writeNormalizedMeasure = writeNormalizedMeasure;
		}

		var objectiveProgressInfoList = new Array();// 紀錄PassFailStatus
		var objectiveProgressListIndex = 0;

		function objectiveProgressInfo(objectiveProgressStatus,objectiveSatisfiedStatus,objectiveMeasureStatus,objectiveNormalizedMeasure){
			this.objectiveProgressStatus = objectiveProgressStatus; // False
			this.objectiveSatisfiedStatus = objectiveSatisfiedStatus; // False
			this.objectiveMeasureStatus=objectiveMeasureStatus; // False
			this.objectiveNormalizedMeasure=objectiveNormalizedMeasure; // 0.0
		}

	// ------------randomizationControls---------------------
	var randomizationControlsList = new Array();
	var randomizationControlsListIndex = 0;
	function randomizationControls(existflag,randomizationTiming,selectCount,reorderChildren,selectionTiming){
		this.existflag = existflag;
		this.randomizationTiming = randomizationTiming;
		this.selectCount = selectCount;
		this.reorderChildren = reorderChildren;
		this.selectionTiming = selectionTiming;
	}

	// ------------delivery Control---------------------------
	var deliveryControlsList = new Array();
	var deliveryControlsListIndex = 0;
	function deliveryControls(existflag,tracked,completionSetByContent,objectiveSetByContent){
		this.existflag = existflag;
		this.tracked = tracked;
		this.completionSetByContent = completionSetByContent;
		this.objectiveSetByContent = objectiveSetByContent;
	}


	// ------------presentation navigation Interface---------------------------

	var navigationInterfaceList = new Array();
	var navigationInterfaceListIndex = 0;
	function navigationInterface(hidePreviousButton, hideContinueButton, hideExitButton, hideAbandonButton){
		this.hidePreviousButton = hidePreviousButton;
		this.hideContinueButton = hideContinueButton;
		this.hideExitButton = hideExitButton;
		this.hideAbandonButton = hideAbandonButton;

	}

	// ------------constainedChoiceConsiderations---------------------------

	var constrainedChoiceConsiderationsList = new Array();
	var constrainedChoiceConsiderationsListIndex = 0;
	function constrainedChoiceConsiderations(existflag,preventActivation, constrainChoice){
		this.existflag = existflag;
		this.preventActivation = preventActivation;
		this.constrainChoice = constrainChoice;
	}

	// ------------rollupConsiderations---------------------------

	var rollupConsiderationsList = new Array();
	var rollupConsiderationsListIndex = 0;
	function rollupConsiderations(existflag,requiredForSatisfied, requiredForNotSatisfied, requiredForCompleted, requiredForIncomplete, measureSatisfactionIfActive){
		this.existflag = existflag;
		this.requiredForSatisfied = requiredForSatisfied;
		this.requiredForNotSatisfied = requiredForNotSatisfied;
		this.requiredForCompleted = requiredForCompleted;
		this.requiredForIncomplete = requiredForIncomplete;
		this.measureSatisfactionIfActive = measureSatisfactionIfActive;
	}






	// ---------------------------------------------Status model-----------------------------------------------------

	var trackingInfoList = new Array();// 紀錄PassFailStatus
	var trackingInfoListIndex = 0;
	function trackingInfo(objectiveProgressStatus,objectiveSatisfiedStatus,objectiveMeasureStatus,objectiveNormalizedMeasure){
		this.objectiveProgressStatus = objectiveProgressStatus; // False
		this.objectiveSatisfiedStatus = objectiveSatisfiedStatus; // False
		this.objectiveMeasureStatus=objectiveMeasureStatus; // False
		this.objectiveNormalizedMeasure=objectiveNormalizedMeasure; // 0.0
	}

	var activityStatusList = new Array();// 紀錄ActivityStatus
	var activityStatusListIndex = 0;
	function activityStatus(activityProgressStatus,activityAbsoluteDuration,activityExperiencedDuration,activityAttemptCount,activityAttemptProgressStatus,activityAttemptCompletionAmount,activityAttemptCompletionStatus,activityAttemptAbsoluteDuration,activityAttemptExperiencedDuration,activityisActive,activityisSuspended,availableChildren,lastTime){
		this.activityProgressStatus=activityProgressStatus; // False
		this.activityAbsoluteDuration=activityAbsoluteDuration; // 0.0
		this.activityExperiencedDuration=activityExperiencedDuration; // 0.0
		this.activityAttemptCount=activityAttemptCount; // 0
		this.activityAttemptProgressStatus=activityAttemptProgressStatus; // False
		this.activityAttemptCompletionAmount=activityAttemptCompletionAmount; // 0.0
		this.activityAttemptCompletionStatus=activityAttemptCompletionStatus; // False
		this.activityAttemptAbsoluteDuration=activityAttemptAbsoluteDuration; // 0.0
		this.activityAttemptExperiencedDuration=activityAttemptExperiencedDuration; // 0.0
		this.activityisActive=activityisActive; // False
		this.activityisSuspended=activityisSuspended; // False
		this.availableChildren=availableChildren; // All childern
		this.lastTime=lastTime; // 0.0
	}

	var inlineRulesList = new Array();
	function inlineRules(controlMode,sequencingRules,limitConditions,auxiliaryResources,rollupRules,primaryObjective,objective,randomizationControls,deliveryControls,navigationInterface,precondition,postcondition,exitcondition,constrainedChoiceConsiderations,rollupConsiderations){
		this.controlMode=controlMode;
		this.sequencingRules=sequencingRules;
		this.limitConditions=limitConditions;
		this.auxiliaryResources=auxiliaryResources;
		this.rollupRules=rollupRules;
		this.primaryObjective=primaryObjective;
		this.objective=objective;
		this.randomizationControls=randomizationControls;
		this.deliveryControls=deliveryControls;
		this.navigationInterface=navigationInterface;
		this.precondition=precondition;
		this.postcondition=postcondition;
		this.exitcondition=exitcondition;
		this.constrainedChoiceConsiderations=constrainedChoiceConsiderations;
		this.rollupConsiderations=rollupConsiderations;
	}


	var thisAttemptList = new Array();
	function thisAttempt(startTime,totalTime){
		this.startTime = startTime;
		this.totalTime = totalTime;
	}


	// ---------------------------------------------Set By Content Check---------------------------------------------

	var setByContentCheckList = new Array();// 紀錄PassFailStatus
	function setByContentCheck(objectiveSetByContentFlag,completionSetByCobtentFlag){
		this.objectiveSetByContentFlag=objectiveSetByContentFlag; // False
		this.completionSetByCobtentFlag=completionSetByCobtentFlag; // false
	}


	// ----------satisfy or completion status----------------------------------
	// "success status" has high priroty over "compeletion status"
	// 讀取重新載入時item的初始狀態, 供樹狀圖示顯示

	var selfItemType;
	var selfItemStatus="";
	var ItemTypeArray = new Array();
	var ItemStatusArray = new Array();



	var adlcpList = new Array()
	function adlcp(completionThreshold,dataFromLMS,timeLimitAction){
		this.completionThreshold=completionThreshold;
		this.dataFromLMS=dataFromLMS;
		this.timeLimitAction=timeLimitAction;
	}


	// ---------Parsing imsmanifest.xml -------------------------------------

	var OrganizationCheck;

	if(xmldoc.selectNodes("//organization").length > 0){
		// 取出第一個organization做為課程結構
		var organizationElement = GetOrganization();
		OrganizationCheck = "TRUE";
	}
	else{
		OrganizationCheck = "FALSE";
	}

	// ----------organization rule check----------------------------------------
	// ----------因為organization之下也有可能有sequencing rule------
	// alert("pre parseRule");
	parseRule("Org",OrganizationID);
	var isDisabled = new Array();

	actionStatusList[index]=new actionStatus(index,"false","false","false","false");


	var orgTitle = getTitle(organizationElement.selectSingleNode("title").text);
	tocList[index] = new toc(OrganizationID,orgTitle,"","","","true","false","true","","folder","false");

	tocList[index].InitialStatus(tocList[index].id);
	tocList[index].disable=isDisabled[index];
	actionStatusList[index].isDisabled=isDisabled[index];
	tocDisplayList[index] = new tocDisplay(OrganizationID,isvisibleCount,"","","","",false, "","folder");

	var limit = false;
	miniTimeOutList[index] = new miniTimeOut(limit,-1,"","");

	index++;

	isvisibleCount++;

	// item_length應該是以指定的organization,其下有幾個item數為原則
	// 而不應該是以整個manifest中有多少個item為主
	var item_length = organizationElement.selectNodes("//item").length;
	var itemIDArray = new Array(item_length + 1); // 因為多一個organization element
	var levelIDArray = new Array(item_length + 1);

	// ---------Build Tree -------------------------------------
	if(OrganizationCheck == "TRUE"){

		if(item_length > 0){
			initialArray();
			levelIDArray[item_length] = "level_0";
			itemIDArray[item_length] = organizationElement.attributes.getNamedItem("identifier").text;

			// ArrayIndex = ArrayIndex + 1;

			var tempElement1;
			var hasChildNodes;
			// var templength;
			var parentlevelID;
			var parentitemID;
			var selfitemID;
			var selflevelID;
			var selftitle;
			var selfparameter;
			var itemIsvisible;
			var DisplayParentID="";
			var resourceID;
			var resourcehref = "";
			var resourcepersistState = "false";
			var target;


			USETEXTLINKS = 1;
			level_0 = gFld("Course Content", "");

			tocDisplayList[index] = new tocDisplay(selfitemID,isvisibleCount,DisplayParentID,selflevelID,parentlevelID,"",false, "","");

			var IsvisibleFolderIDStamp="";

			// 從這裡產生數狀結構
			for(i=0;i<item_length;i++){
				actionStatusList[index]=new actionStatus(index,"false","false","false","false");
				// -----is have children-----------------
				tempElement1 = organizationElement.selectNodes("//item").item(i).cloneNode("TRUE");
				hasChildNodes = "FALSE";
				selfitemID = tempElement1.attributes.getNamedItem("identifier").text;


				parentitemID = organizationElement.selectNodes("//item").item(i).parentNode.attributes.getNamedItem("identifier").text;
				parentlevelID = findlevelID(parentitemID);
				target = organizationElement.selectNodes("//item").item(i).attributes.getNamedItem('target');
				target = target == null ? '' : target.text;

				if(parentitemID==IsvisibleFolderIDStamp){

					parentlevelID=parentlevelID.substr(0,parentlevelID.length-1);

					// find the index of parent
					var itempParentIndex=tocIDfindIndex(parentitemID);
					// find parentitemID of parent
					DisplayParentID=tocList[itempParentIndex].parentID;

				}else{
					DisplayParentID=parentitemID;
				}

				// ---------------
				selflevelID = generatelevelID(parentitemID,parentlevelID,selfitemID);
				selftitle = getTitle(tempElement1.selectSingleNode("title").text);



				if(findParameters(selfitemID)=="TRUE"){
					selfparameter = tempElement1.attributes.getNamedItem("parameters").text;
					selfparameter = TextFilter(selfparameter);
				}else{
					selfparameter = "";
				}

				itemIsvisible=findIsvisible(selfitemID);

				// ----------------------------------------------------------------------
				var tempString1;
				var tempString2;

				resourceID = getResourceID(selfitemID);
				resourcehref = getIdref(selfitemID);
				resourcepersistState = getItemPersistState(selfitemID);
				resourceList[resourceIndex] = new resource(resourceID, selfitemID, resourcehref, resourcepersistState);
				resourceIndex++;


				// ----------rules-----------------------------------------------
				parseRule("Item",selfitemID);
				//--------------------------------------------------------------

				// ----------satisfy or completion status---------------------------
				// "success status" has high priroty over "compeletion status" 讀取重新載入時item的初始狀態, 供樹狀圖示顯示

				var selfItemStatus="";
				// ---------------------------------------------------------------------------------

				// check是不是folder

				// Herry 2004.11.17
				if(checkLeaf(selfitemID)=="FALSE"){
					selfItemType="folder";
					// Boolean ,TRUE:parent folder顯示為資料夾圖示;FALSE:parent folder顯示為檔案圖示(POSTTEST例外, 不顯示)
					var FolderShowStamp=CheckFolderShow(selfitemID);
					// check有沒有resource
					if(findIdref(selfitemID) == "TRUE"){
						if(CheckRandomizeChildren(selfitemID,parentitemID)=="show"){
							// 如果parent有RandomizeChildren,就判斷有沒有在RandomResultArray
							tocList[index] = new toc(selfitemID,selftitle,resourcehref,parentitemID,selfparameter,itemIsvisible,"false","true","","",resourcepersistState);
							tocList[index].InitialStatus(tocList[index].id);
							tocList[index].disable=isDisabled[index];
							tocList[index].target = target;
							actionStatusList[index].isDisabled=isDisabled[index];

							Randomize(index);
							tocDisplayList[index] = new tocDisplay(selfitemID,isvisibleCount,DisplayParentID,selflevelID,parentlevelID,"",false,"","");

							// ----------controlMode-----------------------------------------
							checkControlMode(selfitemID);

							if(FolderShowStamp=="FALSE"){
								tocList[index].folderIsvisible=false;
							}
							if(itemIsvisible!="true"){
								tocList[index].folderIsvisible=false;
							}

							if(itemIsvisible=="true"){
								if(controlModeList[index].choice=="true"){
									if(FolderShowStamp=="TRUE"){
										eval(selflevelID + " = insFld(" + parentlevelID + ", gFld('"+ selftitle +"', '" + SeqEngPath + "?scoID="+ tocList[index].id + "&idx="+ index + "&navEvent=choice" + "'))");
										tocDisplayList[index].isChoice = true;
										tocDisplayList[index].DisplayItemType = "folder";
										isvisibleCount++;
									}
									else{
										eval(selflevelID + " = insFld(" + parentlevelID + ", gLnk(0,"+ selftitle +"', '" + SeqEngPath + "?scoID="+ tocList[index].id + "&idx="+ index + "&navEvent=choice" + "'))");
										tocDisplayList[index].isChoice = true;
										tocDisplayList[index].DisplayItemType = "leaf";
										isvisibleCount++;
									}

								}else{
									if(FolderShowStamp=="TRUE"){
										eval(selflevelID + " = insFld(" + parentlevelID + ", gFld('"+ selftitle +"', ''))");
										tocDisplayList[index].DisplayItemType = "folder";
										isvisibleCount++;
									}
									else{
										eval(selflevelID + " = insFld(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', ''))");
										tocDisplayList[index].DisplayItemType = "leaf";
										isvisibleCount++;
									}
								}
							}
							else{
								IsvisibleFolderIDStamp=selfitemID;
								tocDisplayList[index].isvisibleIndex--;
							}
							tocList[index].itemStatus = selfItemStatus;
							tocList[index].itemType = selfItemType;
							index++;
						}
					}else{
						if(CheckRandomizeChildren(selfitemID,parentitemID)=="show"){
							if(CheckRandomizeChildren(selfitemID,parentitemID)=="show"){
								tocList[index] = new toc(selfitemID,selftitle,resourcehref,parentitemID,selfparameter,itemIsvisible,"false","true","","",resourcepersistState);
								tocList[index].InitialStatus(tocList[index].id);
								tocList[index].disable=isDisabled[index];
								tocList[index].target = target;
								actionStatusList[index].isDisabled=isDisabled[index];

								Randomize(index);
								tocDisplayList[index] = new tocDisplay(selfitemID,isvisibleCount,DisplayParentID,selflevelID,parentlevelID,"",false,"","");

								checkControlMode(selfitemID);
								if(FolderShowStamp=="FALSE"){
									tocList[index].folderIsvisible=false;
								}
								if(itemIsvisible!="true"){
									tocList[index].folderIsvisible=false;
								}

								if(itemIsvisible=="true"){
									/* 檢查parent資料夾下方的leaf是否顯示,如果全不顯示, 資料夾圖示改為leaf圖示
									   都先給link讓engine去處理到底要delivery那一個  */
									if(controlModeList[index].choice=="true"){
										if(FolderShowStamp=="TRUE"){
											eval(selflevelID + " = insFld(" + parentlevelID + ", gFld('"+ selftitle +"', '" + SeqEngPath + "?scoID="+ tocList[index].id + "&idx="+ index + "&navEvent=choice" + "'))");

											tocDisplayList[index].isChoice = true;
											tocDisplayList[index].DisplayItemType = "folder";
											isvisibleCount++;
										}
										else{
											eval(selflevelID + " = insFld(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', '" + SeqEngPath + "?scoID="+ tocList[index].id + "&idx="+ index + "&navEvent=choice" + "'))");
											tocDisplayList[index].isChoice = true;
											tocDisplayList[index].DisplayItemType = "leaf";
											isvisibleCount++;
										}
									}else if(controlModeList[index].flow=="true"){
										if(FolderShowStamp=="TRUE"){
											eval(selflevelID + " = insFld(" + parentlevelID + ", gFld('"+ selftitle +"', ''))");
											tocDisplayList[index].DisplayItemType = "folder";
											isvisibleCount++;
										}
										else{
											eval(selflevelID + " = insFld(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', ''))");
											tocDisplayList[index].DisplayItemType = "leaf";
											isvisibleCount++;
										}
									}
								}
								else{
									IsvisibleFolderIDStamp=selfitemID;
									tocDisplayList[index].isvisibleIndex--;
								}
								tocList[index].itemStatus = selfItemStatus;
								tocList[index].itemType = selfItemType;
								index++;
							}
						}

					}

				}else{
					// 不是folder,是leaf
					// check有沒有resource
					selfItemType="leaf";
					if(findIdref(selfitemID) == "TRUE"){
								if(CheckRandomizeChildren(selfitemID,parentitemID)=="show"){
									tocList[index] = new toc(selfitemID,selftitle,resourcehref,parentitemID,selfparameter,itemIsvisible,"false","true","","",resourcepersistState);
									tocList[index].InitialStatus(tocList[index].id);
									tocList[index].disable=isDisabled[index];
									tocList[index].target = target;
									actionStatusList[index].isDisabled=isDisabled[index];

									Randomize(index);
									tocDisplayList[index] = new tocDisplay(selfitemID,isvisibleCount,DisplayParentID,selflevelID,parentlevelID,"",false,"","");

									// check isvisible---------

									// ----------controlMode-----------------------------------------
									checkControlMode(selfitemID);


									if(itemIsvisible=="true"){
										if(controlModeList[index].choice=="true"){
											tempString2 = eval("insDoc(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', '" + SeqEngPath + "?scoID="+ tocList[index].id + "&idx="+ index + "&navEvent=choice" + "'))");
											tocDisplayList[index].isChoice = true;
										}else{
											tempString2 = eval("insDoc(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', ''))");
										}

										eval(tempString2);
										// ------------------------------------------
										tocDisplayList[index].DisplayItemType = "leaf";

										isvisibleCount++;
									}
									else{

										tocDisplayList[index].isvisibleIndex--;
									}
									tocList[index].itemStatus = selfItemStatus;
									tocList[index].itemType = selfItemType;
									// ------------------------------------------
									index++;

								}

					}else{
						// 是leaf但沒有resource
						// 只要show出link就可以了

						tocList[index] = new toc(selfitemID,selftitle,resourcehref,parentitemID,selfparameter,itemIsvisible,"false","true","","",resourcepersistState);
						tocList[index].InitialStatus(tocList[index].id);
						tocList[index].disable=isDisabled[index];
						tocList[index].target = target;
						actionStatusList[index].isDisabled=isDisabled[index];

						Randomize(index);
						tocDisplayList[index] = new tocDisplay(selfitemID,isvisibleCount,DisplayParentID,selflevelID,parentlevelID,"",false,"","");

						checkControlMode(selfitemID);

						if(itemIsvisible=="true"){
							tempString2 = eval("insDoc(" + parentlevelID + ", gLnk(0,'"+ selftitle +"', ''))");
							eval(tempString2);

							tocDisplayList[index].DisplayItemType = "leaf";
							isvisibleCount++;
							// -------------------------------------
						}
						else{
							tocDisplayList[index].isvisibleIndex--;
						}
						tocList[index].itemStatus = selfItemStatus;
						tocList[index].itemType = selfItemType;
						// ------------------------------------------

						index++;


					}

				}

			}

			initializeDocument();
			initialIsShow();

			// changeStatus();
			parent.parent.s_main.location.href = "/learn/scorm/blank.htm";

		}
		else{
			alert('<?=$MSG["cfm_msg10"][$sysSession->lang]?> ');
		}
	}
	else{
		alert('imsmanifest error...no organization element!!!');
	}

	// ------------initial CA Status ------------------------------------

    function InitialStatus(SCO_ID) {

        // -----如果有來過這個SCO就取出以前的資料,如果沒有來過就初始化這些status model---------
        var flag = _isFirstEnterSco(SCO_ID);
        // -------------------------------initial Timer------------------------------------------
        SCOTimerList[index] = new Timer(index, 0, 0, 0, 0);
        SCOTimerListIndex++;
        SCOTimerList[index].sec = 0;

        // Yunghsiao 2005.08.10
        // --------------------------------tracking Info-------------------------------------------
        var objectiveProgressStatus = false;
        var objectiveSatisfiedStatus = false;
        var objectiveMeasureStatus = false;
        var objectiveNormalizedMeasure = 0.0;


        // ----create sharedObjective Object------------
        // -------primaryObject-------------------------
        // every item has a primary object so...always true
        if (primaryObjectiveList[index].existflag) {
            if (primaryObjectiveList[index].p_mapInfoList.length > 0) {
                for (var p = 0; p < primaryObjectiveList[index].p_mapInfoList.length; p++) {
                    if (primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID != "") {
                        var test_flag = "false";

                        for (var i = 0; i < sharedObjectiveList.length; i++) {
                            // alert("1323 toc In search");
                            if (sharedObjectiveList[i].objectiveID == primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID) {
                                // alert("1325 toc match objectiveID="+ sharedObjectiveList[i].objectiveID);
                                test_flag = "true";
                                break;
                            }
                        }
                        if (test_flag == "false") {
                            // alert("sharedObjectiveIndex="+sharedObjectiveIndex);
                            sharedObjectiveList[sharedObjectiveIndex] = new sharedObjective(primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID, false, false, false, 0.0)
                            sharedObjectiveIndex++;
                        }
                    }
                }
            }
        }
        // --------objective---------------------------

        if (flag == "") {
            // 第一次進入,設定初值
            objectiveProgressStatus = false;
            objectiveSatisfiedStatus = false;

            objectiveMeasureStatus = false;
            objectiveNormalizedMeasure = 0.0;
        } else {
            if (index == 0) {
                var exit_value = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(2).text;
                tocList[0].exit_value = exit_value;
            }

            objectiveProgressStatus = false;
            objectiveSatisfiedStatus = false;
            objectiveMeasureStatus = false;
            objectiveNormalizedMeasure = 0.0;

            // 對應objectiveProgressStatus
            if (XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text == "passed" || XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(8).text == "passed") {
                objectiveProgressStatus = true;
                objectiveSatisfiedStatus = true;
                selfItemStatus = "passed";
            } else if (XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text == "failed" || XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(8).text == "failed") {
                objectiveProgressStatus = true;
                objectiveSatisfiedStatus = false;
                selfItemStatus = "failed";
            }
            // 對應objectiveMeasureStatus
            if (XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(5).text != "") {
                objectiveMeasureStatus = true;
                objectiveNormalizedMeasure = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(5).text;
            }

            // ----update sharedObjective Object------------
            if (primaryObjectiveList[index].existflag) {
                if (primaryObjectiveList[index].p_mapInfoList.length > 0) {
                    for (var p = 0; p < primaryObjectiveList[index].p_mapInfoList.length; p++) {
                        if (primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID != "") {
                            for (var i = 0; i < sharedObjectiveList.length; i++) {
                                if (sharedObjectiveList[i].objectiveID == primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID) {
                                    if (primaryObjectiveList[index].p_mapInfoList[p].writeSatisfiedStatus.toString() == "true") {
                                        sharedObjectiveList[i].objectiveProgressStatus = objectiveProgressStatus;
                                        sharedObjectiveList[i].objectiveSatisfiedStatus = objectiveSatisfiedStatus;
                                    }
                                    if (primaryObjectiveList[index].p_mapInfoList[p].writeNormalizedMeasure.toString() == "true") {
                                        sharedObjectiveList[i].objectiveMeasureStatus = objectiveMeasureStatus;
                                        sharedObjectiveList[i].objectiveNormalizedMeasure = objectiveNormalizedMeasure;
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }
        trackingInfoList[index] = new trackingInfo(objectiveProgressStatus, objectiveSatisfiedStatus, objectiveMeasureStatus, objectiveNormalizedMeasure);

        trackingInfoListIndex++;

        // --------------------------------activityStatus------------------------------------------
        var activityProgressStatus = "";
        var activityAbsoluteDuration = "";
        var activityExperiencedDuration = "";
        var activityAttemptCount = "";
        var activityAttemptProgressStatus = "";
        var activityAttemptCompletionAmount = "";
        var activityAttemptCompletionStatus = "";
        var activityAttemptAbsoluteDuration = "";
        var activityAttemptExperiencedDuration = "";
        var activityisActive = "";
        var activityisSuspended = "false";
        var availableChildren = "";
        var lastTime = "";
        var isHiddenFromChoiceValue = "false";

        if (flag == "") {

            activityProgressStatus = false;
            activityAbsoluteDuration = 0.0;
            activityExperiencedDuration = 0.0;
            activityAttemptCount = 0;
            activityAttemptProgressStatus = false;
            activityAttemptCompletionAmount = 0.0;
            activityAttemptCompletionStatus = false;
            activityAttemptAbsoluteDuration = 0.0;
            activityAttemptExperiencedDuration = 0.0;
            activityisActive = false;
            activityisSuspended = false;
            availableChildren = "All children";
            lastTime = 0.0;

            isDisabled[index] = "false";
            actionStatusList[index].isDisabled = isDisabled[index];


            isHiddenFromChoiceValue = "false";

        } else {

            activityProgressStatus = false;

            // 就是0.75版的Timespan
            // activityAbsoluteDuration = InitialActivityAbsoluteDuration(SCO_ID);
            // 就是0.75版的duration
            // activityExperiencedDuration = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(1).text;

            activityAbsoluteDuration = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(15).text;
            activityExperiencedDuration = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(16).text;

            activityAttemptCount = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(10).text;

            activityAttemptProgressStatus = false;
            activityAttemptCompletionAmount = 0.0;

            // 對應lesson_status
            if (XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text == "completed" || XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(9).text == "completed") {
                activityProgressStatus = true;
                activityAttemptCompletionStatus = true;
                if (selfItemStatus == '') {
                    selfItemStatus = "completed";
                }
            } else if (XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(0).text == "incomplete" || XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(9).text == "incomplete") {
                activityProgressStatus = true;
                activityAttemptCompletionStatus = false;
                if (selfItemStatus == '') {
                    selfItemStatus = "incomplete";
                }
            } else {
                activityProgressStatus = false;
                activityAttemptCompletionStatus = false;
            }


            // 若前一個Record為suspend則activityAttemptAbsoluteDuration的格式為 secs | lastRecord
            activityAttemptAbsoluteDuration = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(13).text;
            activityAttemptExperiencedDuration = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(14).text;
            lastTime = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(6).text;
            activityisActive = false;
            activityisSuspended = CheckSuspend(SCO_ID);
            availableChildren = "All children";

            // ---------------disabled and hiddenFromChoice

            isDisabled[index] = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(11).text;
            if (isDisabled[index] != "true") {
                isDisabled[index] == "false"
                actionStatusList[index].isDisabled = isDisabled[index];
            }

            isHiddenFromChoiceValue = XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(12).text;
            if (isHiddenFromChoiceValue != "true") {
                isHiddenFromChoiceValue = "false";
            }

        }
        activityStatusList[index] = new activityStatus(activityProgressStatus, activityAbsoluteDuration, activityExperiencedDuration, activityAttemptCount, activityAttemptProgressStatus, activityAttemptCompletionAmount, activityAttemptCompletionStatus, activityAttemptAbsoluteDuration, activityAttemptExperiencedDuration, activityisActive, activityisSuspended, availableChildren, lastTime);
        thisAttemptList[index] = new thisAttempt(0, 0);
        activityStatusListIndex++;

        var limit = false;
        miniTimeOutList[index] = new miniTimeOut(limit, -1, "", "");


        var objectiveSetByContentFlag = false;
        var completionSetByCobtentFlag = false;
        setByContentCheckList[index] = new setByContentCheck(objectiveSetByContentFlag, completionSetByCobtentFlag);

        isHiddenFromChoiceList[index] = new isHiddenFromChoice(isHiddenFromChoiceValue);
        actionStatusList[index].isHiddenFromChoice = isHiddenFromChoiceList[index];


    }

    function initialIsShow(){

	for(var j=0;j<alwaysPrecondtionsList.length;j++){

		var alwaysIndex=alwaysPrecondtionsList[j].itemIndex;
		if(alwaysPrecondtionsList[j].action=="disabled"){
			tocList[alwaysIndex].disable="true";
			actionStatusList[alwaysIndex].isDisabled="true";
		}
		else if(alwaysPrecondtionsList[j].action=="hiddenFromChoice"){
			isHiddenFromChoiceList[alwaysIndex].value="true";
			actionStatusList[alwaysIndex].isHiddenFromChoice="true";
		}

	}


	for (var i=0;i<tocList.length;i++){
		tocDisplayList[i].isShow=itemIsShow(i);
	}
}

function itemIsShow(tempIndex){
	var itemShow="true";
	if(isHiddenFromChoiceList[tempIndex].value.toString()=="true" || tocList[tempIndex].isvisible.toString()=="false"){
		itemShow="false";
		return itemShow;
	}
	return itemShow;
}

function changeStatus() {
    var i, j;
    // alert("準備進入課程,請點選左側之課程架構或下方之導覽列");
    // 顯示上次離開之狀態
    for (i = 1; i < tocList.length; i++) {
        parent.parent.tocstatus.statusObj.changetocStatus(i, tocList[i].itemStatus, tocList[i].itemType);
    }

    for (j = 1; j < tocList.length; j++) {
        if (tocList[j].disable == "true") {
            // 回傳已做完的disable index
            j = parent.parent.tocstatus.statusObj.DisplayDisabled(j);
        }
    }

    for (j = 1; j < tocList.length; j++) {
        if (isHiddenFromChoiceList[j].value == "true") {
            // 回傳已做完的hiddenfromchoice index
            parent.parent.tocstatus.statusObj.ChangeTreeImage(j);
            j = parent.parent.tocstatus.statusObj.DisplayHiddenfromchoice(j);
        }
    }

}

function TextFilter(pText){
	var xmlText=pText;
	var strText="";
	strText=xmlText.replace("&amp;","&");
	return strText;
}

function GetOrganization(){
	// 先找organizations的default屬性
	// 如果有default的屬性就就依其定義之organization之為課程架構
	// 如果沒有default的屬性或是定義有誤時就以第一個organization為主
	var organizationsElement = xmldoc.selectSingleNode("//organizations");
	var defaultAttrfound = true;
	var defaultAttrValue = "";
	var i=0;
	var organizationElement;

	if(organizationsElement.attributes.length>0){
		for(i=0;i<organizationsElement.attributes.length;i++){
			if(organizationsElement.attributes.item(i).nodeName == "default"){
				defaultAttrfound = true;
				defaultAttrValue = organizationsElement.attributes.item(i).text;


				break;
			}
		}

		if(defaultAttrfound){
			OrganizationID = defaultAttrValue;
			organizationElement = xmldoc.selectSingleNode("//organization[@identifier='"+ defaultAttrValue +"']").cloneNode("TRUE");


		}else{
			organizationElement = xmldoc.selectSingleNode("//organization").cloneNode("TRUE");
		}

	}else{
		organizationElement = xmldoc.selectSingleNode("//organization").cloneNode("TRUE");
	}

	return organizationElement;


}

// Check if leaf is exist , exist:retuen "TRUE" , else return "FALSE"
// use to replace idref=""
function checkLeaf(itemID){

	var tempitemElement;
	tempitemElement = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']").cloneNode("TRUE");
	var tempItemType = "";
	// socool 2004.12.13
	if(tempitemElement.selectNodes("//item").length >= 1){
		// tempItemType = "folder";
		return "FALSE";
	}else{
		// tempItemType = "leaf";
		return "TRUE";
	}
}


function findIdref(itemID){

	var tempitemElement;
	tempitemElement = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']");
	var FOUND;
	FOUND = "FALSE";
	for(j=0;j<tempitemElement.attributes.length;j++){

		if(tempitemElement.attributes.item(j).nodeName == "identifierref" ){
			FOUND = "TRUE";
			return FOUND;
		}
	}

	return FOUND;

}



function findParameters(itemID){

	var tempitemElement;
	tempitemElement = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']");
	var FOUND;
	FOUND = "FALSE";
	for(var j=0;j<tempitemElement.attributes.length;j++){

		if(tempitemElement.attributes.item(j).nodeName == "parameters" ){
			FOUND = "TRUE";
		}
	}

	return FOUND;

}



function findResourcePersistState(resourceID){

	var tempResourceElement;
	var FOUND = "FALSE";
	if ((tempResourceElement = xmldoc.selectSingleNode("//resource[@identifier='"+ resourceID +"']")) != null)
	{
	for(var j=0;j<tempResourceElement.attributes.length;j++){

		if(tempResourceElement.attributes.item(j).nodeName == "adlcp:persistState" ){
			FOUND = "TRUE";
		}
	}
	}
	return FOUND;

}






function getResourcePersistState(resourceID){

	var tempResourcePersistStateValue = "false";
	if(findResourcePersistState(resourceID)=="TRUE"){

		tempResourcePersistStateValue = xmldoc.selectSingleNode("//resource[@identifier='"+ resourceID +"']").attributes.getNamedItem("adlcp:persistState").text;
	}
	return tempResourcePersistStateValue;

}

function getItemPersistState(itemID){
	var tempItemPersistStateValue = "false"; // folder default value

	if(findIdref(itemID)=="TRUE"){
		var tempResourceID  = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']").attributes.getNamedItem("identifierref").text;
		if(tempResourceID!=""){
			tempItemPersistStateValue = getResourcePersistState(tempResourceID);

		}
	}

	return tempItemPersistStateValue;

}


/******************************************************************************
**ADD by VEGA 2003/11/5
**
** Function CheckFolderShow()
** Inputs:  FolderItemID:資料夾項目id
**
** Return:  true-顯示為資料夾圖示; false-顯示為檔案圖示
** Description:判斷該資料夾是否須顯示於樹狀結構中;呼叫findIsvisible()檢查
**
*******************************************************************************/
function CheckFolderShow(FolderItemID){
	var tempitemElement= xmldoc.selectSingleNode("//item[@identifier='"+ FolderItemID +"']");
	var SHOW = "TRUE";
	var itemChildCount = 0;// 所有LEAF的數量
	var itemShowCount =0;// visible的LEAF數量
	var tempIsnisibleMark;

	for(var j=0;j<tempitemElement.childNodes.length;j++){

		if(tempitemElement.childNodes.item(j).nodeName == "item"){
			itemChildCount = itemChildCount + 1;
			tempIsnisibleMark=findIsvisible(tempitemElement.childNodes.item(j).attributes.getNamedItem("identifier").text);
			if(tempIsnisibleMark== "false"){
				itemShowCount = itemShowCount + 1;
			}

		}

	}

	if(itemShowCount == itemChildCount){
		SHOW = "FALSE";
	}else{
		SHOW = "TRUE";
	}
	return SHOW;
}


function findIsvisible(itemID){

	var tempitemNode;
	tempitemNode = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']");

	for(var i=0;i<tempitemNode.attributes.length;i++){

		if(tempitemNode.attributes.item(i).nodeName == "isvisible" ){
			return tempitemNode.attributes.getNamedItem("isvisible").text;
		}

	}
	return "true";

}

/*  找出identifierref所連結到的resource再找出其href屬性  */
function getIdref(itemID){
	if(findIdref(itemID)=="TRUE"){
		var tempitemElement;
		tempitemElement = xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']");
		var identifierref = tempitemElement.attributes.getNamedItem("identifierref").text;
		var tempResourceItem = xmldoc.selectSingleNode("//resource[@identifier='"+ identifierref +"']");
		var RShref, rtmp;
		if ((rtmp = xmldoc.selectSingleNode("//resource[@identifier='"+ identifierref +"']")) == null) return "";
		RShref = rtmp.attributes.getNamedItem("href").text;
		// 需要先check有沒有xml:base之屬性,如果有必須要加在href之前
		var Strxmlbase = "";
		var xmlbaseFound = ' ';
		var dummyIndex = 0;
		for(dummyIndex = 0; dummyIndex < tempResourceItem.attributes.length;dummyIndex++){
			if(tempResourceItem.attributes.item(dummyIndex).nodeName=="xml:base"){
				xmlbaseFound = tempResourceItem.attributes.getNamedItem("xml:base").text;
				break;
			}
		}

		RShref = xmlbaseFound + '@' + RShref;


		return RShref;
	}else{
		return "";
	}

}


function getResourceID(itemID){
	if(findIdref(itemID)=="TRUE"){
		return xmldoc.selectSingleNode("//item[@identifier='"+ itemID +"']").attributes.getNamedItem("identifierref").text;
	}else{
		return "";
	}


}

function initialArray(){
    var templength = xmldoc.selectNodes("//item").length;
    var tempNodeList = xmldoc.selectNodes("//item");
	for(var j=0;j<templength;j++){

		itemIDArray[j] = tempNodeList.item(j).attributes.getNamedItem("identifier").text
		levelIDArray[j] = "-1";
	}
}




function findlevelID(itemID){
	var FOUND = "FALSE";
	for(j=0;j<itemIDArray.length;j++){
		if(itemID == itemIDArray[j]){
			if(levelIDArray[j] != ""){
				FOUND = "TRUE"
				return levelIDArray[j];
			}
		}
	}
	if(FOUND == "FALSE"){
		return "null";
	}

}



function AddlevelID(itemID, levelID){
	for(j=0;j<itemIDArray.length;j++){
		if(itemID == itemIDArray[j]){
			levelIDArray[j]=levelID;
			return 0;
		}
	}
}


function GetItemID(itemID){

	// + 1 是因為多一個organization element
	for(j=0;j<xmldoc.selectNodes("//item").length + 1;j++){
		if(itemID == itemIDArray[j]){
				return j;
		}
	}
	return "null";
}





function generatelevelID(parentitemID,parentlevelID,selfitemID){

	if(parentlevelID != "level_0"){
		var tempParentElement;
		tempParentElement = xmldoc.selectSingleNode("//item[@identifier='"+ parentitemID +"']");
		var childlength;
		childlength = tempParentElement.childNodes.length;
		var newlevelID;

		for(k=0;k<childlength;k++){
			if(tempParentElement.childNodes.item(k).nodeName == "item"){
				if(selfitemID == tempParentElement.childNodes.item(k).attributes.getNamedItem("identifier").text){
					newlevelID = parentlevelID + k
					AddlevelID(selfitemID,newlevelID);
					return newlevelID;
				}
			}
		}
	}else{

		childlength = organizationElement.childNodes.length;

			for(k=0;k<childlength;k++){
				if(organizationElement.childNodes.item(k).nodeName == "item"){
					if(selfitemID == organizationElement.childNodes.item(k).attributes.getNamedItem("identifier").text){
						newlevelID = parentlevelID + k
						AddlevelID(selfitemID,newlevelID);
						return newlevelID;

					}
				}

			}
		var tempParentElement;
		tempParentElement = xmldoc.selectSingleNode("//item[@identifier='"+ parentitemID +"']");
		childlength = tempParentElement.childNodes.length;

		for(k=0;k<childlength;k++){
			if(tempParentElement.childNodes.item(k).nodeName == "item"){
				if(selfitemID == tempParentElement.childNodes.item(k).attributes.getNamedItem("identifier").text){
					newlevelID = parentlevelID + k
					AddlevelID(selfitemID,newlevelID);
					return newlevelID;
				}
			}
		}

	}

}
// ----------------------------------------------------Sequencing Part---------------------------------------------------------
function parseRule(OrgorItem,ID){

		inlineRulesList[index] = new inlineRules("false","false","false","false","false","false","false","false","false","false","false","false","false","false","false");


		var tempItem ;
		if(OrgorItem=="Org"){
			tempItem = xmldoc.selectSingleNode("//organization[@identifier='" + ID + "']");
			GlobalState.GlobalToSystem = "true";
			for(var j=0;j<tempItem.attributes.length;j++){
				if(tempItem.attributes.item(j).nodeName=="adlseq:objectivesGlobalToSystem"){
					var globaltosystem = tempItem.attributes.item(j).text;
					GlobalState.GlobalToSystem = globaltosystem;
				}
			}
		}else{
			tempItem = xmldoc.selectSingleNode("//item[@identifier='" + ID + "']");
		}


		// --------------------------controlMode---------------------------------------------------

		parsecontrolMode(tempItem,ID);
		// --------------------------sequencingRules-----------------------------------------------

		parsesequencingRules(tempItem,ID);

		// --------------------------limitConditions-----------------------------------------------

		parselimitConditions(tempItem,ID);

		// --------------------------auxiliaryResources--------------------------------------------

		parseauxiliaryResources(tempItem,ID);

		// --------------------------rollupRules---------------------------------------------------

		parserollupRules(tempItem,ID);

		// --------------------------objectives----------------------------------------------------
		parseprimaryObjective(tempItem,ID);
		parseobjective(tempItem,ID);
		// --------------------------randomizationControls-----------------------------------------

		parserandomizationControls(tempItem,ID);

		// --------------------------deliveryControls----------------------------------------------

		parsedeliveryControls(tempItem,ID);

		// --------------------------navigationInterface-------------------------------------------

		parsenavigationInterface(tempItem,ID);

		// --------------------------constrainedChoiceConsiderations------------------------------------------

		parseconstrainedChoiceConsiderations(tempItem,ID);


		// --------------------------rollupConsiderations------------------------------------------

		parserollupConsiderations(tempItem,ID);

		// -------------------------//Heroin 2004.05.11  completionThreshold----------
	 	parseadlcp(tempItem,ID);



	 	if(GlobalState.GlobalToSystem=="true"){
	 		_updateSharedObjective(tempItem,ID);
	 	}else if(GlobalState.GlobalToSystem=="false"){
	 		_updateSharedObjective2(tempItem,ID);
	 	}
}



function _updateSharedObjective(tempItem,ID){
	for(var i=0; i<sharedObjectiveList.length; i++){
		GetGlobalObjectiveInfo(ID,i,sharedObjectiveList[i].objectiveID);
	}
}


function GetGlobalObjectiveInfo(ID,globalObjIndex,globalObjID){
	if(XMLDI.selectNodes("//global_objectives[@User_ID='"+ student_id +"' and @id='"+ globalObjID +"' and @global_to_system='true']").length>0){
		var tempObjElement = XMLDI.selectSingleNode("//global_objectives[@User_ID='"+ student_id +"' and @id='"+ globalObjID +"' and @global_to_system='true']").cloneNode("TRUE");
		sharedObjectiveList[globalObjIndex].objectiveID = globalObjID;
		sharedObjectiveList[globalObjIndex].objectiveProgressStatus = tempObjElement.selectSingleNode("//ProgressStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveSatisfiedStatus = tempObjElement.selectSingleNode("//SatisfiedStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveMeasureStatus = tempObjElement.selectSingleNode("//MeasureStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveNormalizedMeasure = tempObjElement.selectSingleNode("//NormalizedMeasure").text;
	}
}


function _updateSharedObjective2(tempItem,ID){
	// 從get要資料
	for(var i=0; i<sharedObjectiveList.length; i++){
		GetGlobalObjectiveInfo2(ID,i,sharedObjectiveList[i].objectiveID);
	}
}


function GetGlobalObjectiveInfo2(ID,globalObjIndex,globalObjID){
	if(XMLDI.selectNodes("//global_objectives[@Course_ID='"+ course_ID +"' and @User_ID='"+ student_id +"' and @id='"+ globalObjID +"']").length>0){
		var tempObjElement = XMLDI.selectSingleNode("//global_objectives[@Course_ID='"+ course_ID +"' and @User_ID='"+ student_id +"' and @id='"+ globalObjID +"']").cloneNode("TRUE");;

		sharedObjectiveList[globalObjIndex].objectiveID = globalObjID;
		sharedObjectiveList[globalObjIndex].objectiveProgressStatus = tempObjElement.selectSingleNode("//ProgressStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveSatisfiedStatus = tempObjElement.selectSingleNode("//SatisfiedStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveMeasureStatus = tempObjElement.selectSingleNode("//MeasureStatus").text;
		sharedObjectiveList[globalObjIndex].objectiveNormalizedMeasure = tempObjElement.selectSingleNode("//NormalizedMeasure").text;
	}
}




function parseconstrainedChoiceConsiderations(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var constrainedChoiceConsiderationsfound = false;

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){

			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}



			// inLine Rules
			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){

				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "adlseq:constrainedChoiceConsiderations" ){
					GetconstrainedChoiceConsiderationsData(ID,tempItem.childNodes.item(i).childNodes.item(j));
					constrainedChoiceConsiderationsfound = true;
					inlineRulesList[index].constrainedChoiceConsiderations="true";
					break;
				}
			}

			if(checkIDref){
				if(inlineRulesList[index].rollupConsiderations!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="adlseq:constrainedChoiceConsiderations"){
							GetconstrainedChoiceConsiderationsData(ID,tempSeqElement.childNodes.item(j));
							constrainedChoiceConsiderationsfound = true;
							break;
						}
					}
				}
			}


		}
	}
	if(!constrainedChoiceConsiderationsfound){

		var constrainedChoiceConsiderationsObj = new constrainedChoiceConsiderations(false, "false", "false");
		// alert("preventActivation = " + constrainedChoiceConsiderationsObj.preventActivation + " / constrainChoice = " + constrainedChoiceConsiderationsObj.constrainChoice );
		constrainedChoiceConsiderationsList[Number(index)] = constrainedChoiceConsiderationsObj;
	}

}


function GetconstrainedChoiceConsiderationsData(ID,tempElement){
	var i=0;
	var j=0;

	var preventActivation = "false";
	var constrainChoice = "false";

	if(tempElement.nodeName=="adlseq:constrainedChoiceConsiderations"){
		for(j=0;j<tempElement.attributes.length;j++){

			if(tempElement.attributes.item(j).nodeName=="preventActivation"){
				preventActivation = tempElement.attributes.item(j).text;
			}

			if(tempElement.attributes.item(j).nodeName=="constrainChoice"){
				constrainChoice = tempElement.attributes.item(j).text;
			}
		}

		var constrainedChoiceConsiderationsObj = new constrainedChoiceConsiderations(true, preventActivation, constrainChoice);
		// alert("preventActivation = " + constrainedChoiceConsiderationsObj.preventActivation + " / constrainChoice = " + constrainedChoiceConsiderationsObj.constrainChoice );
		constrainedChoiceConsiderationsList[Number(index)] = constrainedChoiceConsiderationsObj;
	}


}




function parserollupConsiderations(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var rollupConsiderationsfound = false;

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){

			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}



			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){

				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "adlseq:rollupConsiderations" ){
					GetrollupConsiderationsData(ID,tempItem.childNodes.item(i).childNodes.item(j));
					rollupConsiderationsfound = true;
					inlineRulesList[index].rollupConsiderations="true";
					break;
				}
			}

			if(checkIDref){
				if(inlineRulesList[index].rollupConsiderations!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="adlseq:rollupConsiderations"){
							GetrollupConsiderationsData(ID,tempSeqElement.childNodes.item(j));
							rollupConsiderationsfound = true;
							break;
						}
					}
				}
			}
		}
	}
	if(!rollupConsiderationsfound){

		var rollupConsiderationsObj = new rollupConsiderations(true, "always","always","always","always","true");
		// alert("requiredForSatisfied = " + rollupConsiderationsObj.requiredForSatisfied + " / requiredForNotSatisfied = " + rollupConsiderationsObj.requiredForNotSatisfied + " / requiredForCompleted = " + rollupConsiderationsObj.requiredForCompleted + " / requiredForIncomplete = " + rollupConsiderationsObj.requiredForIncomplete + " / measureSatisfactionIfActive = " + rollupConsiderationsObj.measureSatisfactionIfActive);
		rollupConsiderationsList[Number(index)] = rollupConsiderationsObj;

	}

}

function GetrollupConsiderationsData(ID,tempElement){
	var i=0;
	var j=0;

	var requiredForSatisfied = "always";
	var requiredForNotSatisfied = "always";
	var requiredForCompleted = "always";
	var requiredForIncomplete = "always";
	var measureSatisfactionIfActive = true;



	if(tempElement.nodeName=="adlseq:rollupConsiderations"){
		for(j=0;j<tempElement.attributes.length;j++){

			if(tempElement.attributes.item(j).nodeName=="requiredForSatisfied"){
				requiredForSatisfied = tempElement.attributes.item(j).text;
			}

			if(tempElement.attributes.item(j).nodeName=="requiredForNotSatisfied"){
				requiredForNotSatisfied = tempElement.attributes.item(j).text;
			}

			if(tempElement.attributes.item(j).nodeName=="requiredForCompleted"){
				requiredForCompleted = tempElement.attributes.item(j).text;
			}

			if(tempElement.attributes.item(j).nodeName=="requiredForIncomplete"){
				requiredForIncomplete = tempElement.attributes.item(j).text;
			}

			if(tempElement.attributes.item(j).nodeName=="measureSatisfactionIfActive"){
				measureSatisfactionIfActive = tempElement.attributes.item(j).text;
			}

		}

		var rollupConsiderationsObj = new rollupConsiderations(true, requiredForSatisfied,requiredForNotSatisfied,requiredForCompleted,requiredForIncomplete,measureSatisfactionIfActive);
		// alert("index="+index+"  requiredForSatisfied = " + rollupConsiderationsObj.requiredForSatisfied + " / requiredForNotSatisfied = " + rollupConsiderationsObj.requiredForNotSatisfied + " / requiredForCompleted = " + rollupConsiderationsObj.requiredForCompleted + " / requiredForIncomplete = " + rollupConsiderationsObj.requiredForIncomplete + " / measureSatisfactionIfActive = " + rollupConsiderationsObj.measureSatisfactionIfActive);
		rollupConsiderationsList[Number(index)] = rollupConsiderationsObj;
	}



}

function parseauxiliaryResources(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}

			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:auxiliaryResources" ){
					GetauxiliaryResourcesData(ID,tempItem.childNodes.item(i).childNodes.item(j));
					inlineRulesList[index].auxiliaryResources="true";
					break;
				}
			}


			if(checkIDref){
				if(inlineRulesList[index].auxiliaryResources!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:auxiliaryResources"){
							GetauxiliaryResourcesData(ID,tempSeqElement.childNodes.item(j));
							break;
						}
					}
				}
			}
		}
	}



}
function GetauxiliaryResourcesData(ID,tempElement){
	var i=0;
	var j=0;
	var auxiliaryResourceID = "";
	var purpose = "";
	var auxiliaryResourceObj = "";
	for(i=0;i<tempElement.childNodes.length;i++){
		if(tempElement.childNodes.item(i).nodeName=="imsss:auxiliaryResource"){
			for(j=0;j<tempElement.childNodes.item(i).attributes.length;j++){

				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="auxiliaryResourceID"){
					auxiliaryResourceID = tempElement.childNodes.item(i).attributes.item(j).text;
				}

				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="purpose"){
					purpose = tempElement.childNodes.item(i).attributes.item(j).text;
				}
			}

			auxiliaryResourceObj = new auxiliaryResource(ID,auxiliaryResourceID,purpose,getIdref(ID));
			auxiliaryResourceList[auxiliaryResourceListIndex] = auxiliaryResourceObj;
			auxiliaryResourceListIndex++;

		}
	}
}

// Modify Here
function parseprimaryObjective(tempItem,ID){

	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var primaryObjectivefound = false;

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}

			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:objectives" ){
					GetprimaryObjectiveData(tempItem.childNodes.item(i).childNodes.item(j),ID);
					primaryObjectivefound = true;
					inlineRulesList[index].primaryObjective="true";
					break;
				}
			}

			// SequencingCollections
			if(checkIDref){
				if(inlineRulesList[index].primaryObjective != "true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:objectives"){
							GetprimaryObjectiveData(tempSeqElement.childNodes.item(j),ID);
							primaryObjectivefound = true;
							break;
						}
					}
				}

			}

		}
	}
	if(!primaryObjectivefound){
		// every item should have a primary objective,
		// primaryObjectiveList[Number(index)] = new primaryObjective(false,"","","","",true);

		var targetObjectiveID = "";
		var readSatisfiedStatus = true;
		var readNormalizedMeasure = true;
		var writeSatisfiedStatus = false;
		var writeNormalizedMeasure = false;
		mapInfoObj = new p_mapInfo(targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure);


		var objectiveID ="Primary_Objective_" + ID ;
		var satisfiedByMeasure = false;
		var minNormalizedMeasure = "1";
		var mapInfoObj = "";
		primaryObjectiveList[Number(index)] = new primaryObjective(true,objectiveID,satisfiedByMeasure,minNormalizedMeasure,mapInfoObj,true);

	}



}

function GetprimaryObjectiveData(tempElement,ID){
	var i=0;
	var j=0;
	var k=0;

	var primaryObjectiveObj;

	var objectiveID = "";
	var satisfiedByMeasure = false;
	// socool 2004.12.17
	var minNormalizedMeasure = 1;

	var mapInfoObj = "";
	var mapInfoObjList = new Array();
	var mapInfoCount = -1;
	// ----------------------------------

	var targetObjectiveID = "";
	var readSatisfiedStatus = true;
	var writeSatisfiedStatus = false;
	var readNormalizedMeasure = true;
	var writeNormalizedMeasure = false;
	var primaryObjectiveFound = false;
	var isAutoCreate = false;

	for(i=0;i<tempElement.childNodes.length;i++){

		if(tempElement.childNodes.item(i).nodeName == "imsss:primaryObjective"){
			// attribute
			for(j=0;j<tempElement.childNodes.item(i).attributes.length;j++){
				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="objectiveID"){
					objectiveID = tempElement.childNodes.item(i).attributes.item(j).text;
					primaryObjectiveFound = true;
				}
				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="satisfiedByMeasure"){
					satisfiedByMeasure = tempElement.childNodes.item(i).attributes.item(j).text;
				}
			}
			if(!primaryObjectiveFound){
				objectiveID = "Primary_Objective_" + ID;
				isAutoCreate = true;
			}

			// mapInfo
			for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
				if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:minNormalizedMeasure"){
					minNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).text;
				}
				if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:mapInfo"){
					mapInfoCount ++;
					// alert("mapInfoCount="+mapInfoCount);
					// -----------------------
					for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="targetObjectiveID"){
							targetObjectiveID = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="readSatisfiedStatus"){
							readSatisfiedStatus = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="readNormalizedMeasure"){
							readNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="writeSatisfiedStatus"){
							writeSatisfiedStatus = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="writeNormalizedMeasure"){
							writeNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
					}
				}
				if((mapInfoCount+1)>mapInfoObjList.length){
					mapInfoObj = new p_mapInfo(targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure);
					mapInfoObjList[mapInfoCount]= mapInfoObj;
				}
				// --------------------------------------------------------
			}
		}
	}

	primaryObjectiveObj = new primaryObjective(true,objectiveID,satisfiedByMeasure,minNormalizedMeasure,mapInfoObjList,isAutoCreate);
	primaryObjectiveList[Number(index)] = primaryObjectiveObj;


}

function parseobjective(tempItem,ID){

	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}
				for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
					if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:objectives" ){
						GetobjectiveData(tempItem.childNodes.item(i).childNodes.item(j),ID);
						inlineRulesList[index].objective="true";
						break;
					}
				}
			if(checkIDref){
				if(inlineRulesList[index].objective!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:objectives"){
							GetobjectiveData(tempSeqElement.childNodes.item(j),ID);
							break;
						}
					}
				}
			}
		}
	}
}

function GetobjectiveData(tempElement,SCO_ID){
	var i=0;
	var j=0;
	var k=0;

	// ======
	var mapInfoCount = -1;
	var mapInfoObjList = new Array();
	// ======
	var objectiveObj = "";

	var objectiveID = "";
	var satisfiedByMeasure = false;
	var minNormalizedMeasure = 1;

	var mapInfoObj = "";

	var targetObjectiveID = "";
	var readSatisfiedStatus = true;
	var writeSatisfiedStatus = false;
	var readNormalizedMeasure = true;
	var writeNormalizedMeasure = false;

	var objectiveProgressStatus = false;
	var objectiveSatisfiedStatus = false;

	var objectiveMeasureStatus = false;
	var objectiveNormalizedMeasure = 0.0;
	// test2004
	// alert("In GetobjectiveData "+ index+"  childnodes.length="+tempElement.childNodes.length);

	for(var i=0;i<tempElement.childNodes.length;i++){
		if(tempElement.childNodes.item(i).nodeName == "imsss:objective"){
			/* ======取得Item的ObjectiveID以及satisfiedByMeasure值  */
			for(j=0;j<tempElement.childNodes.item(i).attributes.length;j++){
				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="objectiveID"){
					objectiveID = tempElement.childNodes.item(i).attributes.item(j).text;
				}
				if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="satisfiedByMeasure"){
					satisfiedByMeasure = tempElement.childNodes.item(i).attributes.item(j).text;
				}
			}

			// mapInfo
			// ======取的mapInfo屬性值(有改到)===
			for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
				if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:minNormalizedMeasure"){
					minNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).text;
				}
				if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:mapInfo"){
					mapInfoCount++;
					for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="targetObjectiveID"){
							targetObjectiveID = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="readSatisfiedStatus"){
							readSatisfiedStatus = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="readNormalizedMeasure"){
							readNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="writeSatisfiedStatus"){
							writeSatisfiedStatus = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="writeNormalizedMeasure"){
							writeNormalizedMeasure = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						}
					}
				}
			  // -------SharedObject-------------------------
			  // ===判斷targetObjective是否存在於shareObjective中===
				if(targetObjectiveID!=""){
					var test_flag="false";
					for(var q=0;q<sharedObjectiveList.length;q++){
						if(sharedObjectiveList[q].objectiveID==targetObjectiveID){
							test_flag="true";
							break;
						}
					}
					if(test_flag=="false"){
						// alert("sharedObjectiveIndex="+sharedObjectiveIndex);
						sharedObjectiveList[sharedObjectiveIndex]=new sharedObjective(targetObjectiveID,false,false,false,0.0)
						sharedObjectiveIndex ++;
					}
				}

 				// 對應objectiveProgressStatus
				var tempNL;
				tempNL=XMLDI.selectNodes("//cmi_objectives[@SCO_ID='" + SCO_ID + "']");

				for(var h=0;h<tempNL.length;h++){
					if(tempNL.item(h).childNodes.item(1).text==objectiveID){ // id=i
						if(tempNL.item(h).childNodes.item(5).text=="passed"){
							objectiveProgressStatus = true;
							objectiveSatisfiedStatus = true;
						}else if(tempNL.item(h).childNodes.item(5).text=="failed"){
							objectiveProgressStatus = true;
							objectiveSatisfiedStatus = false;
						}
						if(tempNL.item(h).childNodes.item(4).text!=""){
							objectiveMeasureStatus = true;
							objectiveNormalizedMeasure = tempNL.item(h).childNodes.item(4).text;
						}
						if(targetObjectiveID!=""){
							for(var l=0;l<sharedObjectiveList.length;l++){
								if(sharedObjectiveList[l].objectiveID==targetObjectiveID){
									if(writeSatisfiedStatus=="true"){
										sharedObjectiveList[l].objectiveProgressStatus=objectiveProgressStatus;
										sharedObjectiveList[l].objectiveSatisfiedStatus=objectiveSatisfiedStatus;
									}
									if(writeNormalizedMeasure=="true"){
										sharedObjectiveList[l].objectiveMeasureStatus=objectiveMeasureStatus;
										sharedObjectiveList[l].objectiveNormalizedMeasure=objectiveNormalizedMeasure;
									}
									break;
								}
							}
						}
						break;
					}
				}
				// ==============
				if((mapInfoCount+1)>mapInfoObjList.length){
					mapInfoObj = new mapInfo(targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure);
					mapInfoObjList[mapInfoCount]= mapInfoObj;
				}
				// ==============
			}
		}

		// 2004.09.03
		if(objectiveID!=""){
			objectiveList[objectiveListIndex] = new objective(index,objectiveID,satisfiedByMeasure,minNormalizedMeasure,mapInfoObjList);
			objectiveProgressInfoList[objectiveListIndex]=new objectiveProgressInfo(objectiveProgressStatus,objectiveSatisfiedStatus,objectiveMeasureStatus,objectiveNormalizedMeasure);
			objectiveListIndex++;
		}
	}
}




function parsesequencingRules(tempItem,ID){
	var i=0;
	var j=0;
	var seqRulesfound = false;
	var checkIDref = false;
	var tempIDref = "";

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}

			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:sequencingRules" ){
					inlineRulesList[index].sequencingRules="true";
					GetsequencingRulesData(tempItem.childNodes.item(i).childNodes.item(j));
					seqRulesfound = true;
					break;
				}
			}


			if(checkIDref){
				tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
				for(j=0;j<tempSeqElement.childNodes.length;j++){
					if(tempSeqElement.childNodes.item(j).nodeName=="imsss:sequencingRules"){
						GetsequencingRulesData(tempSeqElement.childNodes.item(j));
						seqRulesfound = true;
						break;
					}
				}
			}

		}
	}


}

var measureThresholdTemp = 0.0;

function GetsequencingRulesData(tempElement){
	var i=0;
	var j=0;
	var k=0;
	var t=0;
	var sequencingRulesObj = "";
	var preConditionRuleObj = "";
	var postConditionRuleObj = "";
	var exitConditionRuleObj = "";

	var referencedObjective = "";
	var measureThreshold = 0.0;
	var operator = "";
	var condition = "";
	var action = "";
	var conditionCombination = "all";
	var multiflag = false;
	var operatorfound = false;
	var tempoperator = "";

	var inLinePrecondition= false;
	var inLinePostcondition= false;
	var inLineExitcondition= false;


	for(i=0;i<tempElement.childNodes.length;i++){

		// initial
		preConditionRuleObj = "";
		postConditionRuleObj = "";
		exitConditionRuleObj = "";
		referencedObjective = "";
		measureThreshold = "";
		operator = "";
		condition = "";
		action = "";
		conditionCombination = "all";


		// preConditionRule
		if(inlineRulesList[index].precondition!="true"){
			if(tempElement.childNodes.item(i).nodeName=="imsss:preConditionRule"){
				var always_condition=false;
				for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
					// ruleCondition
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleConditions"){
						// attribute--conditionCombination
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="conditionCombination"){
								conditionCombination = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}

						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).childNodes.length;k++){
							if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).nodeName=="imsss:ruleCondition"){
								operatorfound = false;
								tempoperator = "";
								for(t=0;t<tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.length;t++){

									// referenceObjective
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="referencedObjective"){
										referencedObjective = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// measureThreshold
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="measureThreshold"){
										measureThreshold = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// operator
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="operator"){
										tempoperator = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
										operatorfound = true;
									}
									// condition
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="condition"){
										if(condition==""){
											condition = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
											if(condition=="always"){
												always_condition=true;

											}
										}else{
											condition = condition + "*" + tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
											multiflag = true;
										}
									}
								}

								if(!operatorfound){
									if(operator==""){
										operator = "noOp";
									}else{
										operator = operator + "*" + "noOp";
									}
								}else{
									if(operator==""){
										operator = tempoperator;
									}else{
										operator = operator + "*" + tempoperator;
									}
								}
							}
						}
					}

					// ruleAction
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleAction"){
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							// action
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="action"){
								action = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}
					}
				}

				// alert("<br> preConditionRule "+"  "+ multiflag+" index= "+index+"  referencedObjective="+referencedObjective+" measureThreshold= "+measureThreshold+"  "+operator+"  "+condition+"  "+action+"  "+conditionCombination);
				preConditionRuleObj = new preConditionRule(multiflag,index,referencedObjective,measureThreshold,operator,condition,action,conditionCombination);
				preConditionRuleList[preConditionRuleListIndex] = preConditionRuleObj;
				if(always_condition==true){
					always_condition=true;
					alwaysPrecondtionsList[alwaysPrecondtionsCount]=new alwaysPrecondtions(preConditionRuleListIndex,index,action);

					alwaysPrecondtionsCount++;
				}
				preConditionRuleListIndex++;

			inLinePrecondition= true;

			}
		}

		// postConditionRule
		if(inlineRulesList[index].postcondition!="true"){
		// if(inlineRulesList[index].sequencingRules!="true"){
			if(tempElement.childNodes.item(i).nodeName=="imsss:postConditionRule"){

				for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
					// ruleCondition
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleConditions"){
						// attribute--conditionCombination
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="conditionCombination"){
								conditionCombination = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}



						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).childNodes.length;k++){
							// alert("length="+tempElement.childNodes.item(i).childNodes.item(j).childNodes.length);
							if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).nodeName=="imsss:ruleCondition"){
								operatorfound = false;
								tempoperator = "";
								for(t=0;t<tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.length;t++){
									// referenceObjective
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="referencedObjective"){
										referencedObjective = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// measureThreshold
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="measureThreshold"){
										measureThreshold = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// operator
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="operator"){
										tempoperator = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
										operatorfound = true;
									}
									// condition
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="condition"){
										// alert("condition="+condition);
										if(condition==""){
											condition = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
										}else{
											condition = condition + "*" + tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
											multiflag = true;
										}
										// alert("!!condition="+condition);
									}
								}

								if(!operatorfound){
									if(operator==""){
										operator = "noOp";
									}else{
										operator = operator + "*" + "noOp";
									}
								}else{
									if(operator==""){
										operator = tempoperator;
									}else{
										operator = operator + "*" + tempoperator;
									}

								}


							}
						}
					}

					// ruleAction
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleAction"){
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							// action
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="action"){
								action = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}
					}
				}
				// build object
				postConditionRuleObj = new postConditionRule(multiflag,index,referencedObjective,measureThreshold,operator,condition,action,conditionCombination);
				postConditionRuleList[postConditionRuleListIndex] = postConditionRuleObj;
				postConditionRuleListIndex++;


				// alert("multiflag="+multiflag+"  ,index="+index+" ,referencedObjective="+referencedObjective+"  ,measureThreshold="+measureThreshold+"  ,operator="+operator+"  ,condition="+condition+"  ,action="+action+"  ,conditionCombination="+conditionCombination);
				inLinePostcondition= true;

			}


		}



		// exitConditionRule
		if(inlineRulesList[index].exitcondition!="true"){
		// if(inlineRulesList[index].sequencingRules!="true"){
			if(tempElement.childNodes.item(i).nodeName=="imsss:exitConditionRule"){

				for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
					// ruleCondition
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleConditions"){
						// attribute--conditionCombination
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="conditionCombination"){
								conditionCombination = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}

						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).childNodes.length;k++){
							if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).nodeName=="imsss:ruleCondition"){
								operatorfound = false;
								tempoperator = "";
								for(t=0;t<tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.length;t++){
									// referenceObjective
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="referencedObjective"){
										referencedObjective = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// measureThreshold
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="measureThreshold"){
										measureThreshold = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
									}
									// operator
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="operator"){
										tempoperator = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
										operatorfound = true;
									}
									// condition
									if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).nodeName=="condition"){
										if(condition==""){
											condition = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
										}else{
											condition = condition + "*" + tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(k).attributes.item(t).text;
											multiflag = true;
										}
									}
								}

								if(!operatorfound){
									if(operator==""){
										operator = "noOp";
									}else{
										operator = operator + "*" + "noOp";
									}
								}else{
									if(operator==""){
										operator = tempoperator;
									}else{
										operator = operator + "*" + tempoperator;
									}

								}

							}
						}
					}

					// ruleAction
					if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:ruleAction"){
						for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
							// action
							if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="action"){
								action = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
							}
						}
					}
				}

				// build object
				// document.write("<br>exitCondition = " + multiflag + "/" + index + "/" +referencedObjective + "/" + measureThreshold + "/" + operator + "/" + condition +  "/" + action + "/" + conditionCombination);
				exitConditionRuleObj = new exitConditionRule(multiflag,index,referencedObjective,measureThreshold,operator,condition,action,conditionCombination);
				exitConditionRuleList[exitConditionRuleListIndex] = exitConditionRuleObj;
				exitConditionRuleListIndex++;

				inLineExitcondition= true;

			}

		}


	}



	if (inLinePrecondition== true){
		inlineRulesList[index].precondition="true";
	}
	if (inLinePostcondition== true){
		inlineRulesList[index].postcondition="true";
	}
	if (inLineExitcondition== true){
		inlineRulesList[index].exitcondition="true";

	}

}

function parserollupRules(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var tempSeqElement = "";
	var rollupRulesfound = false;

	rollupRule_flagList[index] = new rollupRule_flag(false,false,false);

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}
				for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
					if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:rollupRules" ){
						// alert("index="+index);
						GetrollupRulesData(tempItem.childNodes.item(i).childNodes.item(j));
						rollupRulesfound = true;
						inlineRulesList[index].rollupRules="true";
						break;
					}
				}


			if(checkIDref){
				if(inlineRulesList[index].rollupRules!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:rollupRules"){
							GetrollupRulesData(tempSeqElement.childNodes.item(j));
							rollupRulesfound = true;
							break;
						}
					}
				}
			}
		}
	}

		if(rollupRule_flagList[index].isRollupControl==true){
		}
		else{
			rollupRulesList[Number(index)] = new rollupRules(true,true,true,1.0);
		}

		if (rollupRule_flagList[index].isObjective==false){
			rollupRuleList[rollupRuleListIndex] = new rollupRule(false,index,"noOp","satisfied","satisfied","all","","","");
			rollupRuleListIndex++;
		}
			// if there is no rollup rules , the default rule is  "If all completed then completed" -- modified 2003/10/14
		if (rollupRule_flagList[index].isActivity==false){
			rollupRuleList[rollupRuleListIndex] = new rollupRule(false,index,"noOp","completed","completed","all","","","");
			rollupRuleListIndex++;
		}
}


function GetrollupRulesData(tempElement){
	var i=0;
	var j=0;
	var k=0;
	var rollupObjectiveSatisfied = true;
	var rollupProgressCompletion = true;
	var objectiveMeasureWeight = 1.0;
	var childActivitySet = "";
	var minimumCount = 0;
	var minimumPercent = 0.0;
	var conditionCombination = "any";
	var condition = "";
	var action = "satisfied";
	var operator = "";
	var multiflag = false;
	var operatorfound = false;
	var tempoperator = "";
	/* 先取rollupRules屬性 */
	for(i=0;i<tempElement.attributes.length;i++){
		// alert("i="+i+" nodeName="+tempElement.attributes.item(i).nodeName);
		if(tempElement.attributes.item(i).nodeName=="rollupObjectiveSatisfied"){
			rollupObjectiveSatisfied = tempElement.attributes.item(i).text;
		}
		if(tempElement.attributes.item(i).nodeName=="rollupProgressCompletion"){
			rollupProgressCompletion = tempElement.attributes.item(i).text;
			// alert(index+"  toc rollupProgressCompletion="+rollupProgressCompletion);
		}
		if(tempElement.attributes.item(i).nodeName=="objectiveMeasureWeight"){
			// alert("objectiveMeasureWeight="+tempElement.attributes.item(i).text);
			objectiveMeasureWeight = tempElement.attributes.item(i).text;
		}
	}
	rollupRulesList[Number(index)] = new rollupRules(true,rollupObjectiveSatisfied,rollupProgressCompletion,objectiveMeasureWeight);
	rollupRule_flagList[index].isRollupControl=true;
	// alert(index+"  rollupProgressCompletion="+rollupRulesList[Number(index)].rollupProgressCompletion);

	// 取rollupRule資訊
	for(i=0;i<tempElement.childNodes.length;i++){
		childActivitySet = "";
		minimumCount = 0;
		minimumPercent =0.0;
		conditionCombination = "any";
		condition = "";
		action = "";
		operator = "";
		operatorfound = false;
		tempoperator = "";
		/* 取rollupRule屬性 */
		for(j=0;j<tempElement.childNodes.item(i).attributes.length;j++){
			if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="childActivitySet"){
				childActivitySet = tempElement.childNodes.item(i).attributes.item(j).text;
			}
			if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="minimumCount"){
				minimumCount = tempElement.childNodes.item(i).attributes.item(j).text;
			}
			if(tempElement.childNodes.item(i).attributes.item(j).nodeName=="minimumPercent"){
				minimumPercent = tempElement.childNodes.item(i).attributes.item(j).text;
			}

		}
		// 如果conditionCombnation有值時,表示一定有多個condition

		for(j=0;j<tempElement.childNodes.item(i).childNodes.length;j++){
			// alert("i="+i+"  j="+tempElement.childNodes.item(i).childNodes.length+" name="+tempElement.childNodes.item(i).childNodes.item(j).nodeName);
			if(tempElement.childNodes.item(i).childNodes.item(j).nodeName == "imsss:rollupConditions"){

				for(var h=0;h<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;h++){
					if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(h).nodeName=="conditionCombination"){
						conditionCombination = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(h).text;
					}
					// alert("conditionCombination="+conditionCombination);
				}


				operatorfound = false;
				for(var l=0;l<tempElement.childNodes.item(i).childNodes.item(j).childNodes.length;l++){
					// alert("l="+l+"  name="+tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).nodeName);
					for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.length;k++){
						// alert("length="+tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.length);
						if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).nodeName=="condition"){
							// alert("condition="+condition+"   text="+tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).text);
							if(condition==""){
								condition = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).text;
							}else{
								multiflag = true;
								condition = condition + "*" + tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).text;
							}
							// alert("!!!!condition="+condition);
						}
						if(tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).nodeName=="operator"){
							tempoperator = tempElement.childNodes.item(i).childNodes.item(j).childNodes.item(l).attributes.item(k).text;
							operatorfound = true;
						}
					}

					if(!operatorfound){
						if(operator==""){
							operator = "noOp";
						}else{
							operator = operator + "*" + "noOp";
						}
					}else{
						if(operator==""){
							operator = tempoperator;
						}else{
							operator = operator + "*" + tempoperator;
						}

					}
					// alert("operator="+operator);

				}




			}
			if(tempElement.childNodes.item(i).childNodes.item(j).nodeName=="imsss:rollupAction"){
				for(k=0;k<tempElement.childNodes.item(i).childNodes.item(j).attributes.length;k++){
					if(tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).nodeName=="action"){
						action = tempElement.childNodes.item(i).childNodes.item(j).attributes.item(k).text;
						action = action.toLowerCase();
						if (action=="satisfied" || action=="notsatisfied"){
							rollupRule_flagList[index].isObjective= true;
						}
						if (action=="completed" || action=="incompleted"){
							rollupRule_flagList[index].isActivity= true;
						}

					}
				}
			}

		}
		rollupRuleList[rollupRuleListIndex] = new rollupRule(multiflag,index,operator,condition,action,childActivitySet,minimumCount,minimumPercent,conditionCombination);
		rollupRuleListIndex++;

	}


}


function parseadlcp(tempItem,ID){
	var adlcpfound = false;
	var completionThreshold = "";
	var dataFromLMS = "";
	var timeLimitAction = "";

	for(var i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName.toLowerCase() == "adlcp:completionthreshold"){
			completionThreshold = tempItem.childNodes.item(i).text;

		}else if(tempItem.childNodes.item(i).nodeName.toLowerCase() == "adlcp:datafromlms"){
			dataFromLMS = tempItem.childNodes.item(i).text;

		}else if(tempItem.childNodes.item(i).nodeName.toLowerCase() == "adlcp:timelimitaction"){
			timeLimitAction = tempItem.childNodes.item(i).text;
		}

	}
		adlcpList[Number(index)] = new adlcp(completionThreshold,dataFromLMS,timeLimitAction);

}


function parsecontrolMode(tempItem,ID){
	var i=0;
	var j=0;
	var controlModefound = false;
	var tempControlModeObj = "";
	var checkIDref = false;
	var tempIDref = "";
	var tempSeqElement = "";

	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中


			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}

			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:controlMode" ){
					GetControlModeData(tempItem.childNodes.item(i).childNodes.item(j));
					controlModefound = true;
					inlineRulesList[index].controlMode="true";
					break;
				}
			}


			if(checkIDref){

				// sequencin collection
				if(inlineRulesList[index].controlMode!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:controlMode"){
							GetControlModeData(tempSeqElement.childNodes.item(j));
							controlModefound = true;
							break;
						}
					}
				}

			}
		}
	}
	if(!controlModefound){
		// socool 2005.09.07 ---------
		if(SCORM_VER < 1.3){
		   controlModeList[Number(index)] = new controlMode(false, "true", "true", "true", "false", "true", "true");
		}else{
		   controlModeList[Number(index)] = new controlMode(false, "true", "true", "true", "false", "true", "true");
		}

	}

}

function GetControlModeData(tempElement){
	var i;
	// socool 2004.12.13
	var choice = "true";
	var choiceExit = "true";
	var flow = "false";
	var forwardOnly = "false";
	var useCurrentAttemptObjectiveInfo = "true";
	var useCurrentAttemptProgressInfo = "true";
	for(i=0;i<tempElement.attributes.length;i++){
		if(tempElement.attributes.item(i).nodeName=="choice"){
			choice = tempElement.attributes.item(i).text.toString();
		}

		if(tempElement.attributes.item(i).nodeName=="choiceExit"){
			choiceExit = tempElement.attributes.item(i).text.toString();
		}

		if(tempElement.attributes.item(i).nodeName=="flow"){
			flow = tempElement.attributes.item(i).text.toString();
		}

		if(tempElement.attributes.item(i).nodeName=="forwardOnly"){
			forwardOnly = tempElement.attributes.item(i).text.toString();
		}

		if(tempElement.attributes.item(i).nodeName=="useCurrentAttemptObjectiveInfo"){
			useCurrentAttemptObjectiveInfo = tempElement.attributes.item(i).text.toString();
		}

		if(tempElement.attributes.item(i).nodeName=="useCurrentAttemptProgressInfo"){
			useCurrentAttemptProgressInfo = tempElement.attributes.item(i).text.toString();
		}
	}
	var tempControlModeObj = new controlMode(true,choice,choiceExit,flow,forwardOnly,useCurrentAttemptObjectiveInfo,useCurrentAttemptProgressInfo);
	controlModeList[Number(index)]= tempControlModeObj;

}

function parselimitConditions(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var limitConditionsfound = false;
	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}
			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
				if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:limitConditions" ){
					GetlimitConditionsData(tempItem.childNodes.item(i).childNodes.item(j));
					limitConditionsfound = true;
					inlineRulesList[index].limitConditions="true";
					break;
				}
			}

			if(checkIDref){
				if(inlineRulesList[index].limitConditions!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:limitConditions"){
							GetlimitConditionsData(tempSeqElement.childNodes.item(j));
							limitConditionsfound = true;
							break;
						}
					}
				}

			}
		}
	}
	if(!limitConditionsfound){
		limitConditionsList[Number(index)] = new limitConditions(false,"","","","","","","");
	}




}

function GetlimitConditionsData(tempElement){
	var i;
	var attemptLimit = 0;
	var attemptAbsoluteDurationLimit = 0.0;
	var attemptExperiencedDurationLimit = 0.0;
	var activityAbsoluteDurationLimit = 0.0;
	var activityExperiencedDurationLimit = 0.0;
	var beginTimeLimit = "October,15 1582 00:00:00.0";
	var endTimeLimit = "October,15 1582 00:00:00.0";

	for(i=0;i<tempElement.attributes.length;i++){

		if(tempElement.attributes.item(i).nodeName=="attemptLimit"){
			attemptLimit = tempElement.attributes.item(i).text;
		}
		// alert("index="+index+" attemptLimit="+attemptLimit);

		if(tempElement.attributes.item(i).nodeName=="attemptAbsoluteDurationLimit"){
			attemptAbsoluteDurationLimit = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="attemptExperiencedDurationLimit"){
			attemptExperiencedDurationLimit = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="activityAbsoluteDurationLimit"){
			activityAbsoluteDurationLimit = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="activityExperiencedDurationLimit"){
			activityExperiencedDurationLimit = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="beginTimeLimit"){
			beginTimeLimit = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="endTimeLimit"){
			endTimeLimit = tempElement.attributes.item(i).text;
		}
	}
	var templimitConditionsObj = new limitConditions(true,attemptLimit,attemptAbsoluteDurationLimit,attemptExperiencedDurationLimit,activityAbsoluteDurationLimit,activityExperiencedDurationLimit,beginTimeLimit,endTimeLimit);
	limitConditionsList[Number(index)]= templimitConditionsObj;
}


function parserandomizationControls(tempItem,ID){
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var tempRCObj = "";
	var randomizationControlsfound = false;
	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}

				for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
					if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:randomizationControls" ){
						GetrandomizationControlsData(tempItem.childNodes.item(i).childNodes.item(j));
						randomizationControlsfound = true;
						inlineRulesList[index].randomizationControls="true";
						break;
					}
				}



			if(checkIDref){
				if(inlineRulesList[index].randomizationControls!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:randomizationControls"){
							GetrandomizationControlsData(tempSeqElement.childNodes.item(j));
							randomizationControlsfound = true;
							break;
						}
					}
				}

			}
		}
	}
	if(!randomizationControlsfound){
		randomizationControlsList[Number(index)]= new randomizationControls(false,"","","","");
	}


}

function GetrandomizationControlsData(tempElement){
	var i;
	var randomizationTiming = "never";
	var selectCount = 0;
	var reorderChildren = false;
	var selectionTiming = "never";


	for(i=0;i<tempElement.attributes.length;i++){
		if(tempElement.attributes.item(i).nodeName=="randomizationTiming"){
			randomizationTiming = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="selectCount"){
			selectCount = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="reorderChildren"){
			reorderChildren = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="selectionTiming"){
			selectionTiming = tempElement.attributes.item(i).text;
		}

	}
	var tempRCObj = new randomizationControls(true,randomizationTiming,selectCount,reorderChildren,selectionTiming);
	randomizationControlsList[Number(index)]= tempRCObj;

}

function parsedeliveryControls(tempItem,ID){
	// alert("parsedeliveryControls   tempItem="+tempItem.childNodes.length);
	var i=0;
	var j=0;
	var checkIDref = false;
	var tempIDref = "";
	var deliveryControlsfound = false;
	for(i=0;i<tempItem.childNodes.length;i++){

		if(tempItem.childNodes.item(i).nodeName == "imsss:sequencing"){
			// 判斷有沒有idref,如果有idref表示實際上的sequencing rule是在sequencing collection中
			for(j=0;j<tempItem.childNodes.item(i).attributes.length;j++){
				if(tempItem.childNodes.item(i).attributes.item(j).nodeName.toLowerCase()=="idref"){
					checkIDref = true;
					tempIDref = tempItem.childNodes.item(i).attributes.item(j).text;
					break;
				}
			}
			for(j=0;j<tempItem.childNodes.item(i).childNodes.length;j++){
					// alert("noedname=+" + tempItem.childNodes.item(i).childNodes.item(j).nodeName );
					if(tempItem.childNodes.item(i).childNodes.item(j).nodeName == "imsss:deliveryControls" ){

						GetdeliveryControlsData(tempItem.childNodes.item(i).childNodes.item(j));
						deliveryControlsfound = true;
						inlineRulesList[index].deliveryControls="true";
						break;
					}
				}

			if(checkIDref){
				if(inlineRulesList[index].deliveryControls!="true"){
					tempSeqElement = xmldoc.selectSingleNode("//imsss:sequencingCollection/imsss:sequencing[@ID='"+ tempIDref +"']");
					for(j=0;j<tempSeqElement.childNodes.length;j++){
						if(tempSeqElement.childNodes.item(j).nodeName=="imsss:deliveryControls"){
							GetdeliveryControlsData(tempSeqElement.childNodes.item(j));
							deliveryControlsfound = true;
							break;
						}
					}


				}
			}

		}
	}
	if(!deliveryControlsfound){
		deliveryControlsList[Number(index)]= new deliveryControls(false,true,false,false);
	}

}

function GetdeliveryControlsData(tempElement){
	var i;
	var tracked = true;
	var completionSetByContent = false;
	var objectiveSetByContent = false;

	for(i=0;i<tempElement.attributes.length;i++){
		if(tempElement.attributes.item(i).nodeName=="tracked"){
			tracked = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="completionSetByContent"){
			completionSetByContent = tempElement.attributes.item(i).text;
		}

		if(tempElement.attributes.item(i).nodeName=="objectiveSetByContent"){
			objectiveSetByContent = tempElement.attributes.item(i).text;
		}


	}
	// alert(index+"  tracked="+tracked);
	var tempDCObj = new deliveryControls(true,tracked,completionSetByContent,objectiveSetByContent);
	deliveryControlsList[Number(index)]= tempDCObj;

}

// ======================================================================================================
function parsenavigationInterface(tempItem,ID){
	var i=0;
	var j=0;
	var presentationfound = false;
	var navigationInterfacefound = false;
	var tempPresentationElement;
	var tempNavigationInterfaceElement;
	for(i=0;i<tempItem.childNodes.length;i++){
		if(tempItem.childNodes.item(i).nodeName == "adlnav:presentation"){
			presentationfound = true;
			tempNavigationInterfaceElement = tempItem.childNodes.item(i).childNodes.item(0);
			// 假設adlnav:presentation裡,就只有一個adlnav:navigationInterface
			GetnavigationInterfaceData(tempNavigationInterfaceElement);
			break;
		}
	}

	if(!presentationfound){
		navigationInterfaceList[Number(index)]= new navigationInterface(false,false,false,false);
	}
}


function GetnavigationInterfaceData(tempElement){
	var i=0;
	var hidePreviousButton = false;
	var hideContinueButton = false;
	var hideExitButton = false;
	var hideAbandonButton = false;

	for(i=0;i<tempElement.childNodes.length;i++){
		if(tempElement.childNodes.item(i).nodeName == "adlnav:hideLMSUI"){
			if(tempElement.childNodes.item(i).text == "previous"){
				hidePreviousButton = true;
			}else if(tempElement.childNodes.item(i).text == "continue"){
				hideContinueButton = true;
			}else if(tempElement.childNodes.item(i).text == "exit"){
				hideExitButton = true;
			}else if(tempElement.childNodes.item(i).text == "abandon"){
				hideAbandonButton = true;
			}
		}

	}

	navigationInterfaceList[Number(index)]= new navigationInterface(hidePreviousButton,hideContinueButton,hideExitButton,hideAbandonButton);
	// alert("hidePreviousButton = " + hidePreviousButton + "/hideContinueButton = " + hideContinueButton + "/hideExitButton = " + hideExitButton + "/hideAbandonButton = " + hideAbandonButton);
}


// ======================================================================================================


function CheckSuspend(SCO_ID){
	var templength = XMLDI.selectNodes("//Record[@SCO_ID='" + SCO_ID + "']").length;
	if(templength>0){
		return XMLDI.selectSingleNode("//Record[@SCO_ID='" + SCO_ID + "']").childNodes.item(7).text;
	}
}




function _isFirstEnterSco(SCO_ID){
    var Count = XMLDI.selectNodes("//Record[@SCO_ID='" + SCO_ID + "']").length;
    if (Count > 0)
        return SCO_ID;
    else
        return "";
}

/******************************************************************************
**
** Function checkControlMode()
** Inputs:  tempitemID:項目id
**
** Return:  true-顯示為資料夾圖示; false-顯示為檔案圖示
** Description:如果沒有,設預設值 (recusive). choice=true;*choiceExit=true;flow=false;*forwardOnly=false
**
*******************************************************************************/
function checkControlMode(tempitemID){
	var itemIndex = Number(tocIDfindIndex(tempitemID));
	// alert("  itemIndex="+itemIndex+"   index="+index);
	if(itemIndex != -1){
		if(controlModeList[itemIndex].existflag){

			if(controlModeList[itemIndex].choice=="false"){
				controlModeList[index].choice = "false";
			}else if(controlModeList[itemIndex].choice=="true"){
				controlModeList[index].choice = "true";
			}

			if(controlModeList[itemIndex].flow=="true"){
				controlModeList[index].flow = "true";
			}else if(controlModeList[itemIndex].flow=="false"){
				controlModeList[index].flow = "false";
			}
			if(controlModeList[itemIndex].forwardOnly=="true"){
				controlModeList[index].forwardOnly = "true";
			}else if(controlModeList[itemIndex].forwardOnly=="false"){
				controlModeList[index].forwardOnly = "false";
			}
		}else{
			if(itemIndex != 0){
				checkControlMode(tocList[itemIndex].parentID);
			}
		}
	}else{
		if((itemIndex != 0)&&(itemIndex!=-1)){
			checkControlMode(tocList[itemIndex].parentID);
		}
		if(itemIndex==-1){
			controlModeList[index].choice = "true";
			controlModeList[index].flow="false";
		}

	}

}



function tocIDfindIndex(tempitemID){
	var tempi=0;
	var foundindex = -1;
	for(tempi=0;tempi<tocList.length;tempi++){
		if(tocList[tempi].id==tempitemID){
			foundindex = tempi;
			return foundindex;
		}
	}
	return foundindex;
}



// ----------------------------------------------------------Randomize-----------------------------------------------------


function Randomize(randomIndex){
	tempitemID = tocList[Number(randomIndex)].id;

	var tempElement1 = xmldoc.selectSingleNode("//item[@identifier='"+ tempitemID +"']").cloneNode("TRUE");

	if(randomizationControlsList[randomIndex].existflag){

		var OriginalRandomResult  = GetOriginalRandomResult(tempitemID,randomIndex);
		// alert("OriginalRandomResult = " + OriginalRandomResult);
		// 先check之前有沒有random過
		if(OriginalRandomResult != "New"){
			/*  如果有suspend就直接回復上一次random的結果
				要check --> suspend or select timing = once
				如果是suspend就直接回復(child裡有susped)
				如果是select timing = once再check random timing
				如果random timing = OnEachNewAttempt則需要再重新random  */
			if(OriginalRandomResult=="Restore"){
				RestoreRandomResultIDArray(randomIndex,false);
			}else if(OriginalRandomResult=="RestoreWithCheckRandomTiming"){
				if(randomizationControlsList[randomIndex].reorderChildren=="true"){
					if(randomizationControlsList[randomIndex].randomizationTiming=="once"){
						// 因為不需要random所以可以直接restore database裡的結果
						RestoreRandomResultIDArray(randomIndex,false);
					}else if(randomizationControlsList[randomIndex].randomizationTiming=="never"){
						RestoreRandomResultIDArray(randomIndex,false);

					}else if(randomizationControlsList[randomIndex].randomizationTiming=="onEachNewAttempt"){
						// restore之前的結果但需要重新random
						// alert("RestoreWithRandom");
						ResetRandomResult();
						RestoreRandomResultIDArray(randomIndex,true);
						ChangeManifestRandomResult(tempitemID);
						SaveReorderArray(tempitemID);
						SaveRandomResultIDArray(tempElement1);


					}else{
						RestoreRandomResultIDArray(randomIndex,false);
					}
				}

			}

		}else{
			// 這裡之所以要用randomIndex而不是用SeqIndex是因為己經parserule了所以SeqIndex會比randomIndex多一
			var randomflag = false;
			var selectionflag = false;
			var restoreflag = false;
			var tempSelectCount = "";
			var j = 0;
			var attemptflag = false;

			if(randomizationControlsList[randomIndex].selectCount!=""){
				selectionflag = true;
				if(randomizationControlsList[randomIndex].selectionTiming=="never"){
					selectionflag = false;
				}else if(randomizationControlsList[randomIndex].selectionTiming=="once"){
					selectionflag = true;
					tempSelectCount = Number(randomizationControlsList[randomIndex].selectCount);
				}else if(randomizationControlsList[randomIndex].selectionTiming=="onEachNewAttempt"){
					selectionflag = true;
					tempSelectCount = Number(randomizationControlsList[randomIndex].selectCount);
				}else if(randomizationControlsList[randomIndex].selectionTiming==""){
					selectionflag = true;
					tempSelectCount = Number(randomizationControlsList[randomIndex].selectCount);
				}
			}

			// alert("tempSelectCount = " + tempSelectCount);

			if(randomizationControlsList[randomIndex].reorderChildren!=""){
				randomflag = true;
				if(randomizationControlsList[randomIndex].randomizationTiming=="never"){
					randomflag = false;
				}else if(randomizationControlsList[randomIndex].randomizationTiming=="once"){
					attemptflag = false;
					if(randomizationControlsList[randomIndex].reorderChildren=="true")
						randomflag = true;
					else
						randomflag = false;

					// 若不select但要random
					if(randomflag){
						if(!selectionflag){
							tempSelectCount = Number(tempElement1.selectNodes("//item").length);
						}
					}
				}else if(randomizationControlsList[randomIndex].randomizationTiming=="onEachNewAttempt"){
					if(randomizationControlsList[randomIndex].reorderChildren=="true")
						randomflag = true;
					else
						randomflag = false;

					// 若不select但要random
					if(randomflag){
						if(!selectionflag){
							tempSelectCount = Number(tempElement1.selectNodes("//item").length);
						}
					}
				}else if(randomizationControlsList[randomIndex].randomizationTiming==""){
					if(randomizationControlsList[randomIndex].reorderChildren=="true"){
						randomflag = true;
					}else{
						randomflag = false;
					}
					// 若不select但要random
					if(randomflag){
						if(!selectionflag){
							tempSelectCount = Number(tempElement1.selectNodes("//item").length);
						}
					}
				}

			}

			// alert("randomflag = " + tempitemID + " / " + randomflag);

			if(selectionflag || randomflag){
				ResetRandomResult();

				if((Number(tempSelectCount)<=Number(tempElement1.selectNodes("//item").length))&&(Number(tempSelectCount)>0)){

				}else{
					tempSelectCount = Number(tempElement1.selectNodes("//item").length);
				}

				// random

				// alert("selectionflag = " + selectionflag + " / randomflag = " + randomflag );

				GetRandomChild(tempSelectCount,0,tempElement1.selectNodes("//item[@identifierref!='']").length-1,randomflag);
				GetRandomResultIDArray(tempElement1);

				// reorder將資料存入reorder的array中
				SaveReorderArray(tempitemID);

				// 要把random的結果存回database中
				SaveRandomResultIDArray(tempElement1);
				ChangeManifestRandomResult(tempitemID);
			}
		}
	}
}

function SaveReorderArray(tempitemID){
 	var i=0;
 	for(i=0;i<RandomResultIDArray.length;i++){
		ReorderParentIDArray[i] = tempitemID;
		ReorderParentIDArrayIndex = ReorderParentIDArrayIndex + 1;
		ReorderChildIDArray[i] = RandomResultIDArray[i];
 		ReorderChildIDArrayIndex = ReorderChildIDArrayIndex + 1;
 	}
}

function ChangeManifestRandomResult(tempitemID){
	// reorder the child
	var x=0;
	var y=0;
	var tempElement1;
	var tempElement2;
	/* 要從後往前...這樣insertbefore的順序才會對  */
	tempElement1 = xmldoc.selectSingleNode("//item[@identifier='"+ tempitemID +"']");
	for(x=RandomResultIDArray.length-1;x>-1;x--){
		// alert("changing!");
		tempElement2 = tempElement1.removeChild(xmldoc.selectSingleNode("//item[@identifier='" + RandomResultIDArray[x] + "']"));
		// alert(tempElement2.xml);
		tempElement1.insertBefore(tempElement2,xmldoc.selectNodes("//item").item(i).childNodes.item(1));
	}

	// 將沒有random到的item remove

	// 將Random完的結果重新設回organizationElement
	organizationElement = GetOrganization();
}
// =============Yunghsiao.2004.12.07=====================================================================================
function RestoreRandomResultIDArray(randomIndex,reorder){
	var templength = XMLDI.selectNodes("//Random_Child_ID").length;
	var i=0;

	if(reorder){
		GetRandomChild(templength,0,templength-1,reorder);
		for(i=0;i<templength;i++){
			RandomResultIDArray[RandomResultIDArrayIndex] = XMLDI.selectNodes("//Random_Child_ID").item(RandomResultArray[i]).text;
			RandomResultIDArrayIndex = RandomResultIDArrayIndex + 1;
		}
	}else{
		for(i=0;i<templength;i++){
			RandomResultIDArray[RandomResultIDArrayIndex] = XMLDI.selectNodes("//Random_Child_ID").item(i).text;
			RandomResultIDArrayIndex = RandomResultIDArrayIndex + 1;
		}
	}
}
// ===========Yunghsiao.2004.12.06=======================================================================================
function GetOriginalRandomResult(tempItemID,randomIndex){
	/*  有兩個情況要將之前的random結果restore
		1.如果使用者suspend
		2.如果select timing 是 once可是已random過了  */

	if(XMLDI.selectNodes("//Random_Child_ID").length >0){
		if(XMLDI.selectSingleNode("//IsSuspended").text=="true"){
			return "Restore";
		}else if(randomizationControlsList[randomIndex].selectionTiming=="once"){
			return "RestoreWithCheckRandomTiming";
		}else{
			return "New";
		}
	}else{
		return "New";
	}
}
// ======================================================================================================================

function GetRandomChild(RandomSelectCount,lowerBound,upperBound,reorder){
	// --------random--------------------------------------------
	// 用round頭和尾比較不容易ran到
	var RandomResult;
	var i=0;
	var RandomSelectedfound = false;
	while(RandomResultArrayIndex<Number(RandomSelectCount)){
		RandomResult = Math.round((Number(upperBound)-Number(lowerBound))*Math.random()) + Number(lowerBound);
		if(RandomResultArrayIndex>0){
			for(i=0;i<RandomResultArrayIndex;i++){
				if(RandomResultArray[i]==RandomResult){
					RandomSelectedfound = true;
					break;
				}
			}
		}
		if(!RandomSelectedfound){
			RandomResultArray[RandomResultArrayIndex] = RandomResult;
			RandomResultArrayIndex = RandomResultArrayIndex + 1;
		}
		RandomSelectedfound = false;

	}

	if(!reorder){
		BubbleSort();
	}

}

function BubbleSort(){
	var i=0;
	var j=0;
	for(i=0;i<RandomResultArrayIndex;i++){
		for(j=i+1;j<RandomResultArrayIndex;j++){
			if(RandomResultArray[i]>RandomResultArray[j]){
				BubbleSwap(i,j);
			}
		}
	}

}
function BubbleSwap(index_i,index_j){
	var tempfield = "";
	tempfield = RandomResultArray[index_i];
	RandomResultArray[index_i] = RandomResultArray[index_j];
	RandomResultArray[index_j] = tempfield;

}

function GetRandomResultIDArray(tempAggrElement){
	var i;
	for(i=0;i<RandomResultArray.length;i++){
		RandomResultIDArray[i] = tempAggrElement.selectNodes("//item[@identifierref!='']").item(Number(RandomResultArray[i])).attributes.getNamedItem("identifier").text;
		RandomResultIDArrayIndex = RandomResultIDArrayIndex + 1;
	}
}



function CheckRandomizeChildren(tempitemID,tempParentID){
	var tempString = "show";
	var tempParentindex = tocIDfindIndex(tempParentID);
	var i=0;

	if(tempParentindex!=-1){

		if((randomizationControlsList[tempParentindex].existflag)&&(RandomResultIDArrayIndex>0)){
			tempString = "hide";
			for(i=0;i<RandomResultIDArray.length;i++){
				if(tempitemID==RandomResultIDArray[i]){
					tempString = "show";
					return tempString;
				}
			}
		}
	}
	return tempString;
}


function SaveRandomResultIDArray(tempAggrElement){
	var tempdoc = XmlDocument.create();
	tempdoc.async = false;
	tempdoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var rootElement = tempdoc.documentElement;

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",tempAggrElement.attributes.getNamedItem("identifier").text);
	rootElement.setAttribute("Scorm_Type","not");
	rootElement.setAttribute("Message_Type","randomResult");

	var randomElement = tempdoc.createElement("random");

	var resultElement;
	var i=0;

	for(i=0;i<RandomResultIDArray.length;i++){
		resultElement = tempdoc.createElement("result");
		resultElement.text = RandomResultIDArray[i];
		randomElement.appendChild(resultElement);
	}

	rootElement.appendChild(randomElement);

	var ServerSide = xmlhttp_set;
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml");
	XMLHTTPObj.send(tempdoc.xml);
}


function ResetRandomResult(){
	var i=0;
	for(i=0;i<RandomResultArray.length;i++){
		RandomResultArray[i] = "";
	}
	for(i=0;i<RandomResultIDArray.length;i++){
		RandomResultIDArray[i] = "";
	}
	RandomResultArrayIndex = 0;
	RandomResultIDArrayIndex = 0;
}


// ---------------------------------timer----------------------------------------------


function startTimer(itemIndex){
	var tempTimerID = setInterval("count("+ Number(itemIndex) +")",1000);
	SCOTimerList[Number(itemIndex)].TimerID = tempTimerID;
}

function count(itemIndex){
	SCOTimerList[Number(itemIndex)].sec++;
}

function suspendTimer(itemIndex){
	clearInterval(SCOTimerList[Number(itemIndex)].TimerID);
}

function endTimer(itemIndex){
	clearInterval(SCOTimerList[Number(itemIndex)].TimerID);
	SCOTimerList[Number(itemIndex)].sec=0;
}

// socool 2005.09.07 -------------------------------------------------------------
function GetSCORMVetsion(){
		var version_Found = false;

		for(var i=0;i<xmldoc.selectSingleNode("manifest").attributes.length;i++){
			if(xmldoc.selectSingleNode("manifest").attributes.item(i).nodeName=="version"){
				version_Found = true;
				break;
			}
		}
		if(version_Found){
			version = xmldoc.selectSingleNode("manifest").attributes.getNamedItem("version").text;
		}else{
			version = "2004";
		}
		return version;
}

// --------------------------------------------------------------------------------

</script>
<base target="s_main">


</body>
</html>
