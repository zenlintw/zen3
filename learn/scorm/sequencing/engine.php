<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');
	
	$localpath = sprintf('/base/%05d/course/%08d/content/', $sysSession->school_id, $sysSession->course_id);
?>
<html onunload="javascript:ClearSequencingEngineObj();">
	<head>
	<title>Untitled Document</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
		<meta http-equiv="Content-Language" content="zh-tw" >
		<script type="text/javascript" language="JavaScript" src="/lib/xmlextras.js"></script>
<script	language="javascript">

var SequencingEngineObj;
/*  ��X�ܼ�  */
var student_id="<?=$sysSession->username?>";
var course_ID="<?=$sysSession->course_id?>";
var xmlhttp_get="/learn/scorm/get.php";
var xmlhttp_set="/learn/scorm/set.php";
var material_path ="<?=$localpath?>";


/* Global Variables */
var sco_ID;
var index;
var index_; /* �����Ұʪ�SCO�H��K��Rollup */
var choice_index;	
var navEvent;


var seqRequest;
var exitRequest;
var result="";
var minTimeLimitType = "";
/* var currentActivity=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity; */

var queryPermitted = false;
var queryPermittedprevious = false;
var queryPermittedcontinue = false;

var SCODelivered = "false";

/* ----delivery Mode----------------- */
var deliveryMode="normal";
/* ---------------------------------- */

var FOControlIndex = "";

/* -----------history------------------------ */
var historyRoute = new Array();
historyRouteIndex = 0;

/* ----------setTimeOut--------- */
/* Heroin 2003.12.19 */
var timeoutIndex = new Array();
var timeoutIndexCount=0;
var setTimeoutID = new Array();

var suspendFlag=false;

/* Heroin 2004.11.08 */
var previousActiveIndex = 0;

function setBackNextTxt(strscoID){
    var x=parent.s_catalog.pathtree.preloadXMLDoc.selectSingleNode('//item[@identifier="' + strscoID + '"]');
    if( x != null ){
        var nextTxt = x.selectSingleNode('preceding::item[@identifierref][1]/title');
        var backTxt = x.selectSingleNode('following::item[@identifierref][1]/title');
        if( backTxt != null ){
            var txt = parent.s_catalog.pathtree.getTitle(backTxt.text);
            
            parent.s_catalog.document.getElementById('nextNodeBtn1').setAttribute('title',txt);
            parent.s_catalog.document.getElementById('nextNodeBtn2').setAttribute('title',txt);
        }else{
            parent.s_catalog.document.getElementById('nextNodeBtn1').setAttribute('title','');
            parent.s_catalog.document.getElementById('nextNodeBtn2').setAttribute('title','');
        }
        if( nextTxt != null ){
            var txt = parent.s_catalog.pathtree.getTitle(nextTxt.text);
            parent.s_catalog.document.getElementById('backNodeBtn1').setAttribute('title',txt);
            parent.s_catalog.document.getElementById('backNodeBtn2').setAttribute('title',txt);
        }else{
            parent.s_catalog.document.getElementById('backNodeBtn1').setAttribute('title','');
            parent.s_catalog.document.getElementById('backNodeBtn2').setAttribute('title','');
        }
    }
}

/* Step0:�NNavigation Event�ഫ��Sequencing Request */
/******************************************************************************
**
** Function NavigationRequestProcess()
** Inputs:  None
** Return:  parameter "seqRequest" for function	SequencingRequestProcess("Find",seqRequest)
**
** Description:�NNavigation Event�ഫ��Sequencing Request
**					Returns	the handle to function SequencingRequestProcess("Find",seqRequest), (and calls function	EndAttemptProcess())
**
*******************************************************************************/
function NavigationRequestProcess(StrscoID, Strindex, StrnavEvent){
    
	sco_ID  = StrscoID;
	index = Strindex;
	index_ = Strindex
	navEvent = StrnavEvent;
	choice_index = Strindex;
	if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity!=""){
		previousActiveIndex = parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
	}
	
	for(var	h=0;h<parent.s_catalog.pathtree.objectiveProgressInfoList.length;h++){
		var tempindex = parent.s_catalog.pathtree.objectiveList[h].itemIndex;
	}
	
	SCODelivered = "false";
	var tempNavEvent = navEvent;
	navEvent = navEvent.toLowerCase();
	
	
	
	
	
	if(navEvent=="start"){
		if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity==""){
			seqRequest="Start";
			SequencingRequestProcess("Find",seqRequest);
		}
		else{
			alert("Navigation Request is not valid!");
		}	
	}	
	
	else if(navEvent=="resume_all"){		
		/* Heroin-2003.12.11 */
		resumeProcess();
        setBackNextTxt(parent.s_catalog.pathtree.tocList[index].id);
	}
		
		
	else if(navEvent=="continue"){
        
		setBackNextTxt(parent.s_catalog.pathtree.tocList[parseInt(index)+1].id);
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
		
		if(parent.s_catalog.pathtree.controlModeList[n_parentIndex].flow=="true" || parent.s_catalog.pathtree.controlModeList[n_parentIndex].flow==""){

			if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){
				
				seqRequest="Continue";
				exitRequestProcess();
			}
			else{
				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				seqRequest="Continue";
				SequencingRequestProcess("Find",seqRequest);
			}	
		}else{
			alert("Invalid control mode!!! Navigation Request is not valid!");
		}	
	}
	else if(navEvent=="previous"){
        setBackNextTxt(parent.s_catalog.pathtree.tocList[parseInt(index)-1].id);
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
		
		if(Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)>0){
			if(String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].flow)=="true" && String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].forwardOnly)=="false"){
				if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){
					seqRequest="Previous";
					exitRequestProcess();
				}else{
					index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
					seqRequest="Previous";
					SequencingRequestProcess("Find",seqRequest);
				}	
			}else{
				alert("Navigation Request is not valid!");
			}	
		}else{
			alert("Navigation Request is not valid!");
		}	
	}
	
	else if(navEvent=="forward"){		
		/* request not valid	*/
	}
	
	else if(navEvent=="backward"){		
		/* request not valid */
	}
		
	/* modified by Heroin -2003.11.21 */
	else if(navEvent=="choice"){
        setBackNextTxt(StrscoID);
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);

		if(Number(index)==0 || String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].choice)=="true"){
			if(Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)>Number(index)){
				var commonAncestor=parent.s_catalog.pathtree.tocList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].parentID;
			}else{
				var commonAncestor=parent.s_catalog.pathtree.tocList[Number(index)].parentID;
			}
			
			
			var activityCount=(Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity) - Number(parent.functions.enfunctions.tocIDfindIndex(commonAncestor)));
			if(activityCount - 1 > 0){
				for(var	i=Number(parent.functions.enfunctions.tocIDfindIndex(commonAncestor))+1;i<Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity);i++){
					if(parent.s_catalog.pathtree.controlModeList[i].choiceExit=="false"){
						alert("Navigation Request is not valid!");
						return("");
					}
				}
			} 
			if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){

				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				seqRequest="Choice";
				exitRequestProcess();					
			}else{
				/* index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity; */
				seqRequest="Choice";	
				
				/* Heroin 2004.03.31 */
				/* �o�̭n�[�JChoiceSequencingRequestProcess */
				SequencingRequestProcess("Find",seqRequest);
			}	
		}else{
			alert("Navigation Request is not valid!");
		}
		
	}

	else if(navEvent=="exit"){
		exitRequest="Exit";
		exitRequestProcess();
		
	}
	
	/* Heroin-2003.11.10 */
	else if(navEvent=="exit_all"){
		exitRequest="ExitAll";
		exitRequestProcess();

	}
	
	else if(navEvent=="abandon"){
		/* 2004.3.24 Vega: 2004 */
		exitRequest="Abandon";
		exitRequestProcess();
	}	
	
	else if(navEvent=="abandon_all"){
		/* 2004.3.24 Vega: 2004 */
		exitRequest="Abandon_all";
		exitRequestProcess();
	}	
	
	else if(navEvent=="suspend_all"){
		suspendFlag=true;
		suspendProcess();
		EndAttemptProcess(index);/* ��s�ثeSCO�����A */
		saveAncestors(index);
		suspendFlag=false;
		parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity="";
		// parent.s_catalog.location.replace('about:blank');
		//	parent.window.close();
	}
	
	else if(navEvent=="permitted_continue"){
		/* ���Nstatus�M�� */
		parent.s_catalog.pathtree.queryPermittedObj.c = false;
		queryPermitted = true;
		queryPermittedcontinue = true;
		queryPermittedprevious = false;
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
		if(parent.s_catalog.pathtree.controlModeList[n_parentIndex].flow=="true"){
			index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
			seqRequest="Continue";
			SequencingRequestProcess("Find",seqRequest);
		}else{
			// no permmit
			parent.s_catalog.pathtree.queryPermittedObj.c = false;
		}
	}
	else if(navEvent=="permitted_previous"){
		/* ���Nstatus�M�� */
		parent.s_catalog.pathtree.queryPermittedObj.p = false;
		queryPermitted = true;
		queryPermittedprevious = true;
		queryPermittedcontinue = false;
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
		if(Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)>0){
			if(String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].flow)=="true" && String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].forwardOnly)=="false"){
				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				seqRequest="Previous";
				SequencingRequestProcess("Find",seqRequest);
			
			}else{
				/* no permit */
				parent.s_catalog.pathtree.queryPermittedObj.p = false;
			}
		}else{
			/* no permit */
			parent.s_catalog.pathtree.queryPermittedObj.p = false;
		}
	}

	else if(navEvent=="permitted_all"){
	}
	else if(String(navEvent).indexOf("choice")!=-1 && navEvent.indexOf("{target=")==0){
		
		
		/* SetValue("adl.nav.request", "{target=activity_3}choice")  */
		/* �h�Y�h�� */
		var tempStringA=navEvent;
		var tempB=String(navEvent).indexOf("}choice");
		tempStringA=String(tempNavEvent).substring(8,tempB);
		/* alert("nave="+navEvent+"   tempStringA="+tempStringA); */
	
	
		/* tempStringA  */
	
		for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
			if(parent.s_catalog.pathtree.tocList[i].id==tempStringA){
				/* alert("@@ "+parent.s_catalog.pathtree.tocList[i].id);			*/
				choice_index=i;
				index=i;
				/* seqRequest="Choice"; */
				/* SequencingRequestProcess("Find",seqRequest); */
				break;
			}
		
		}
		
		
		var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);

		if(Number(index)==0 || String(parent.s_catalog.pathtree.controlModeList[n_parentIndex].choice)=="true"){
			if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){

				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				seqRequest="Choice";
				exitRequestProcess();					
			}
			else{
				/* index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity; */
				seqRequest="Choice";	
				
				/* Heroin 2004.03.31 */
				/* �o�̭n�[�JChoiceSequencingRequestProcess */
				SequencingRequestProcess("Find",seqRequest);
			}	
		}
		else{
			alert("Navigation Request is not valid!");
		}
		
		
		
	}
}


/* Heroin 2004.04.04 */
function ChoiceActivityTraversalSubprocess(choiceIndex,traversalDirection){
	/* alert("ChoiceActivityTraversalSubprocess index="+choiceIndex+"   traversalDirection="+traversalDirection); */
	var reachable="false";
	
	/* if Forward check StopForwardTraversal	*/
	if(traversalDirection=="Forward"){
	
	
		var tempStopForward=checkStopForward(choiceIndex);
		if(tempStopForward.toString()=="false"){
			/* check	�O�_�C��index����isStopForwardTravesal!!!!! */
			reachable="false";
			return	reachable;
		}else{
			reachable="true";
			return	reachable;
		}
	}	
	
	/* if Backword check ForwardOnly */
	/* alert(choiceIndex+"	traversalDirection="+traversalDirection+"  forwardOnly="+parent.s_catalog.pathtree.controlModeList[choiceIndex].forwardOnly); */
	var n_parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
	if(traversalDirection=="Backward"){
		if(parent.s_catalog.pathtree.controlModeList[n_parentIndex].forwardOnly.toString()=="true"){
			reachable="false";
			return	reachable;
		}else{
			reachable="true";	
			return	reachable;
		}
	}else{
		reachable="true";	
		return	reachable;
	
	}
	
}

/* Heroin 2004.04.07 */
function checkStopForward(tempIndex){
			var preconditionResult="true";

			var j=0;
			var preConditionRules_collection=new Array();
			var preConditionRules_flag="false";
			for(var	k=0;k<parent.s_catalog.pathtree.preConditionRuleList.length;k++){ /* �����ثeSCO�]�t��preConditionRules */
				if(parent.s_catalog.pathtree.preConditionRuleList[k].itemIndex==tempIndex){
					preConditionRules_collection[j]=k;
					preConditionRules_flag="true";
					j++;
				}
			}
			
			
			/* alert(tempIndex+"  preConditionRules_flag="+preConditionRules_flag); */
			if(preConditionRules_flag=="true"){
				var action_collection=parent.check.checkrules.preConditionRules(tempIndex);	
				var a =	action_collection.length;
				
				if(a==0){
					/* precondition_result=false */
					return preconditionResult;
				}else{
					
					/* P_precondition action */
					var action_skip=false;
					var action_disabled=false;
					var action_hiddenformchoice=false;
					var action_stopforwardTraversal=false;
					
					for(var	h=0;h<action_collection.length;h++){
						if(action_collection[h]=="disabled"){
							action_disabled=true;			
						}
						else if(action_collection[h]=="hiddenFromChoice"){
							action_hiddenformchoice=true;
						}
						else if(action_collection[h]=="stopForwardTraversal"){
							action_stopforwardTraversal=true;
							/* Heroin 2004.04.04 */
							parent.s_catalog.pathtree.actionStatusList[h].isStopForwardTraversal="true";
						}
					}
					
					/* ���U�ӭn�����h    */
					/* alert("action_stopforwardTraversal="+action_stopforwardTraversal	); */
					if(action_stopforwardTraversal==true){
						/* traversalDirection="Forward"; */
						preconditionResult="false";
					}
					return preconditionResult;
				}
			}else{
				return preconditionResult;
			
			}
}

function exitRequestProcess(){   /* Termination Request Process */
	/* alert(index+" @@exitRequestProcess"); */
	if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity==""){
		alert("The Exit	Request	was not	valid!");
		return(""); 
	}
	if(!parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){
		alert("The Exit	Request	was not	valid; the activity has	already	exited!");
		return(""); 
	}
	
	/* 2004.3.24 Vega: 2004  */
	if(!exitRequest	|| exitRequest==""){		
		EndAttemptProcess(index); /* ��s�ثeSCO�����A,activityisactivity = false */
		SequencingExitActionRulesSubprocess(); /* �ˬd�O�_�nExit */

	}	
	if(exitRequest=="Exit"){			
		EndAttemptProcess(index); /* ��s�ثeSCO�����A,activityisactivity = false */
		/* overallRollupProcess();	 */
		SequencingExitActionRulesSubprocess(); /* �ˬd�O�_�nExit */
	}
	/* Heroin-2003.12.31 */
	/* �Τ@�NData �s�JDB */
	/* saveAncestors(index); */
	/* alert("parsePostConditionRules11 : " + index); */
	
	/* socool 2005.11.22 �Yindex�W�X�d��,�h�]���̫�@�� */
	if(index > parent.s_catalog.pathtree.tocList.length-1){
		index = parent.s_catalog.pathtree.tocList.length-1;
	}
	
	
	var sequencingrequest=parsePostConditionRules(index); /* parsePostConditionRule */
	
	/* Heroin 2004.08.27  */
	/* save shared objective info to DB... */
	/* Heroin 2004.09.03 */
	var globalToSystem = parent.s_catalog.pathtree.GlobalState.GlobalToSystem;
	saveGlobalObjectiveInfoToDB(index);
	
	
	//alert("***index="+index);
	
	/* socool 2005.08.19--------------- */
	/* run parent's postcondition */
	
	
	if(exitRequest=="ExitParent"){
		
		/* socool 2005.08.09 */
		/* alert("index is run ExitParent : " + index); */
		/* EndAttemptProcess(index); */
		var tempActivityArray =	findAncestors(index);
		for(var	i=tempActivityArray.length-2;i>0;i--){	
			/* alert("i = " + i + " and tempActivityArray[i] = " + tempActivityArray[i]);		 */
			EndAttemptProcess(tempActivityArray[i]);
		}
		
		/* socool 2005.08.09 */
		var parentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);		
		/* alert("index = " + index + " and parenetIndex = " +  parentIndex); */
		exitRequest = "";
		var sequencingrequest = false;
		sequencingrequest = parsePostConditionRules(parentIndex); /* parsePostConditionRule */
		/* index = tempActivityArray[i]; */
		/* alert("sequencingrequest = "+ sequencingrequest+ " ; seqRequest = " + seqRequest );		 */
		/* alert("seqRequest = " + seqRequest + "exitRequest = " + exitRequest);		 */
		if(seqRequest == "Previous"){
			parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = parentIndex;
			index = parentIndex;
		}else if(seqRequest == "Continue"){
			/* ���즹folder�~���U�@�ӳ̪�SCO */
			var tempIndex;
			for(var i=parseInt(index)+1;i<parent.s_catalog.pathtree.tocList.length;i++){
				/* �P�_�O�_�O�l�]... */
				if(Number(parentIndex) <= Number(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID))){
					tempIndex=i;
				}
				else{
					break;				
				}
			}
			parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = tempIndex;
			index = tempIndex;
			/* alert("i = " + tempIndex + ";seqRequest = " + seqRequest); */
		}
		
		/* parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = index; */
		/* seqRequest="Continue"; */

	}
	if(exitRequest=="ExitAll"){
		/* alert("ExitAll"); */
		/* EndAttemptProcess(index);  */
		TerminateDescendentAttemptsProcess(index,0);
		index =	0;
		EndAttemptProcess(index);
		parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = 0;
		if(seqRequest=="retryAll"){
			alert("reload this course!");
			saveAggregationInfoToDB(0);
			// parent.s_main.window.location.href="../scorm_api_adapter.php?APISource=2004API";
			parent.s_main.window.location.href = '/learn/scorm/index.php';
			/* parent.window.close(); */
			
			/* alert("���ҵ{�w����! �����Y�N����!! exit this course!!"); */
			
			/* seqRequest="Start"; */
			/* SequencingRequestProcess("Find",seqRequest); */
		}else{
			alert("The course is completed! Window will closed!! exit this course!!");
			parent.window.close();
		}
	}
	/* Abandon */
	if(exitRequest=="Abandon"){		
		parent.s_catalog.pathtree.activityStatusList[index].activityisActive=false;	
		alert("Abandon finished");

	}
	/* Abandon_all */
	if(exitRequest=="Abandon_all"){
		var tempActivityArray =	findAncestors(index);
		if(tempActivityArray.length>0){
			for(var	i=tempActivityArray.length-1;i>0;i--){			
				parent.s_catalog.pathtree.activityStatusList[tempActivityArray[i]].activityisActive=false;
			}
			index =	tempActivityArray[i];
			parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = index;
			exitRequest="Valid";
			SeqRequest="Continue";

		}else{
			alert("The termination request:"+exitRequest+" is not valid!");
			exitRequest="Not Valid";
			SeqRequest="";
		}
		alert("Abandon_all finished");
	}

	
	if(sequencingrequest==true){
		if(seqRequest=="Choice"){
			index=choice_index;
		}			
		/* socool 2005.11.22 �Y���̫�@��sco,�N���ΦA���U��F. */
		/* if((index < parent.s_catalog.pathtree.tocList.length-1) && (seqRequest=="Continue")){ */
		/* socool 2005.11.29 �ץ�if������P�_  */
		if(index <= parent.s_catalog.pathtree.tocList.length-1){
			SequencingRequestProcess("Find",seqRequest);
		}
		
	}

	
	
}


function EndAttemptProcess(item_index){	
	/* alert("EndAttemptProcess  "+item_index+"   active="+parent.s_catalog.pathtree.activityStatusList[item_index].activityisActive); */
	
	/* �P�_�O�_�ݭnendAttempt */
	if(parent.s_catalog.pathtree.activityStatusList[item_index].activityisActive==true || suspendFlag==true){
		/* alert(item_index+" EndAttemptProcess"); */
		/* Vega 2004.11.17 itemType */
		if(parent.s_catalog.pathtree.tocList[Number(item_index)].itemType=="folder"){
			
			parent.s_catalog.pathtree.activityStatusList[item_index].activityisActive=false;
			
			var needDesabled=CheckDisplayDisabled(item_index);
			if(needDesabled){
				parent.s_catalog.pathtree.tocList[item_index].disable="true";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[item_index].isDisabled="true";
				var disabledIndex=parent.tocstatus.statusObj.DisplayDisabled(item_index);
			}
			
			var needHiddenformchoice=CheckDisplayHiddenfromchoice(item_index);
			if(needHiddenformchoice){
				/* Heroin-2003.12.30 */
				parent.s_catalog.pathtree.tocDisplayList[item_index].isShow="false";
				parent.s_catalog.pathtree.isHiddenFromChoiceList[item_index].value="true";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[item_index].isHiddenFromChoice="true";
				var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(item_index);
			}
			
			
			
			
			var needSkip=CheckDisplaySkip(item_index);
			if(needSkip){
				parent.s_catalog.pathtree.actionStatusList[item_index].isSkip="true";
			}else{
				parent.s_catalog.pathtree.actionStatusList[item_index].isSkip="false";
			}
			
			/* �s�JDB Heroin-2003.12.09 */
			/* set agregation rollupInfo to DB */
			saveAggregationInfoToDB(item_index);
		}
		else{
			/* alert(index+"	 tracked="+parent.s_catalog.pathtree.deliveryControlsList[index].tracked); */
			if(parent.s_catalog.pathtree.deliveryControlsList[index].tracked.toString()=="true"){
				if(parent.s_catalog.pathtree.deliveryControlsList[index].completionSetByContent.toString()=="true" || parent.s_catalog.pathtree.setByContentCheckList[index].completionSetByCobtentFlag.toString()=="true"){	
					/* �N�ثeSCO finish ,�ñN������sco��status��s��TOC��state model��   */
				
					/* Heroin-2003.11.18 */
					/* if parent is invisible then  */
					var parentIndex=index;
					if(parentIndex!=0){
						for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
							if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[index].parentID){
								parentIndex=i;
								break;
							}
						}
					}
					var checkItem="completion_status";
					var item_status=checkCurrentStatus(checkItem);
					/* alert(index+ " item_status="+item_status+" visilbe="+parent.s_catalog.pathtree.tocList[index].isvisible); */
					if(parent.s_catalog.pathtree.tocList[index].isvisible=="true"){
						parent.tocstatus.statusObj.changetocStatus(index,item_status,"leaf");
					}
				}
				else{
					if(parent.s_catalog.pathtree.setByContentCheckList[index].completionSetByCobtentFlag.toString()=="false" && parent.s_catalog.pathtree.deliveryControlsList[index].completionSetByContent.toString()=="false"){
						/* alert("cobtent="+parent.s_catalog.pathtree.setByContentCheckList[index].completionSetByCobtentFlag.toString()+"  setbycontent="+parent.s_catalog.pathtree.deliveryControlsList[index].completionSetByContent.toString()); */
						if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptProgressStatus==false){
							/* Heroin 2003.11.11
							   ���B�Ncompletion���w�]�ȳ]��"TRUE" */
							parent.s_catalog.pathtree.activityStatusList[index].activityAttemptProgressStatus=true;
							parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCompletionStatus=true;
						}
					}
					var item_status="completed";
					
					if(parent.s_catalog.pathtree.tocList[index].isvisible=="true"){
						parent.tocstatus.statusObj.changetocStatus(index,item_status,"leaf");
					}
					
				}
				
				if(parent.s_catalog.pathtree.deliveryControlsList[index].objectiveSetByContent	|| parent.s_catalog.pathtree.setByContentCheckList[index].objectiveSetByContentFlag==true){
					UpdateSharedObjective();
					
					/* Heroin-2003.11.14 */
					/* if parent is invisible then  */
					var parentIndex=index;
					if(parentIndex!=0){
						for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
							if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[index].parentID){
								parentIndex=i;
								break;
							}
						}
					}
					
					
					var checkItem="success_status";
					var item_status=checkCurrentStatus(checkItem);
					/* alert(index+ " item_status="+item_status+" visilbe="+parent.s_catalog.pathtree.tocList[index].isvisible); */
					if(parent.s_catalog.pathtree.tocList[index].isvisible=="true"){
						parent.tocstatus.statusObj.changetocStatus(index,item_status,"leaf");
					}
					
				}
				/* 2003.10.06 -Heroin objective set by content =	false 
				   Defalt Rule
				   ���B�NobjectiveProgressStatus���w�]�ȳ]��"FALSE" */
				else{
					/* if SetByContent=false	then set default value */
					/* Heroin 2003.10.13 */
					if(parent.s_catalog.pathtree.setByContentCheckList[index].objectiveSetByContentFlag==false){
						if(parent.s_catalog.pathtree.primaryObjectiveList[index].existflag){
							/* alert("objectiveSetByContentFlag==false"); */
							/* Modified by Henry 2004.06.18 */
							parent.s_catalog.pathtree.trackingInfoList[index].objectiveProgressStatus=true;						
							parent.s_catalog.pathtree.trackingInfoList[index].objectiveSatisfiedStatus=true;
						}
					}
					UpdateSharedObjective();
					var item_status="failed";
				}
			}
			
			/*  socool 2005.08.22 ---- */
			/* alert("index : " + index + "  's objectiveProgressStatus = " + parent.s_catalog.pathtree.trackingInfoList[index].objectiveProgressStatus); */
			if(parent.s_catalog.pathtree.deliveryControlsList[index].tracked.toString()=="false"){
				parent.s_catalog.pathtree.trackingInfoList[index].objectiveProgressStatus=false;
				/* alert("index 3's tracked = " + parent.s_catalog.pathtree.deliveryControlsList[index].tracked.toString()); */
			}
			
			// ----
			
			parent.s_catalog.pathtree.activityStatusList[index].activityisActive=false;
			/* alert("02 parent.s_catalog.pathtree.activityStatusList["+index+"].activityisActive=false"); */
			
			/*------------end or suspend Activity timer---------------------------------------  */
			if(parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended==true){
				parent.s_catalog.pathtree.SCOTimerList[index].suspendTimer(index);
			}else{
				parent.s_catalog.pathtree.SCOTimerList[index].endTimer(index);
			}
	

			/* �P�_�O���O�ݭnCheckDisplayDisabled Heroin-2003.11.28  */
			var needDesabled=false;
			needDesabled = CheckDisplayDisabled(index);
			if(needDesabled){
				parent.s_catalog.pathtree.tocList[index].disable="true";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[index].isDisabled="true";
				var disabledIndex=parent.tocstatus.statusObj.DisplayDisabled(index);
			}
			var needHiddenformchoice=CheckDisplayHiddenfromchoice(index);
			if(needHiddenformchoice){
				/* Heroin-2003.12.30 */
				parent.s_catalog.pathtree.tocDisplayList[index].isShow="false";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[index].isHiddenFromChoice="true";
				var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(index);
			}
				
			var needSkip=CheckDisplaySkip(index);
			/* alert(index+" /912 needSkip="+needSkip); */
			if(needSkip){
				parent.s_catalog.pathtree.actionStatusList[index].isSkip="true";
				/* alert(item_index+" isSkip"); */
			}else{
				parent.s_catalog.pathtree.actionStatusList[index].isSkip="false";
			}
			
	
			/* �p��Duratoin */
			var nowTime=new	Date().getTime();
			var thisAttemptTotalTime=(nowTime-parent.s_catalog.pathtree.thisAttemptList[index].startTime)/1000;
			
			parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration =	Number(parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration)+Number(thisAttemptTotalTime);
			parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration = Number(parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration)+Number(thisAttemptTotalTime);
			parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration = Number(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration)+Number(thisAttemptTotalTime);
			parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration	= Number(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration)+Number(thisAttemptTotalTime);
			parent.s_catalog.pathtree.thisAttemptList[index].startTime=new	Date().getTime();
			
			/* �p�⯪���̪�Duration */
			var tempParentIndex;
			var tempParentID=parent.s_catalog.pathtree.tocList[index].parentID;
			
			while(tempParentID!=""){
				tempParentIndex	=  parent.functions.enfunctions.tocIDfindIndex(tempParentID);
				
				parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAbsoluteDuration	= Number(parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAbsoluteDuration)+Number(thisAttemptTotalTime);
				parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityExperiencedDuration = Number(parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityExperiencedDuration)+Number(thisAttemptTotalTime);
				parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAttemptAbsoluteDuration = Number(parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAttemptAbsoluteDuration)+Number(thisAttemptTotalTime);
				parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAttemptExperiencedDuration = Number(parent.s_catalog.pathtree.activityStatusList[tempParentIndex].activityAttemptExperiencedDuration)+Number(thisAttemptTotalTime);
				parent.s_catalog.pathtree.thisAttemptList[tempParentIndex].startTime=new Date().getTime();
				
				
				/* �o�̭n�A��  ��Ҧ��sDB�����X�@�_�̫�s!!! 2003.12.17 */
				/* saveAggregationInfoToDB(tempParentIndex); */
					
				tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].parentID;
			}
	
			/* �NActivityStatus�^�Ǧ�server�� */
			CommitActivityStatus();
			
			/* Vega 2004.10.28 add index */
			overallRollupProcess(index);	
			saveAncestors(index);	 
					
		}
	}
		
	
}


/* Heroin 2004.08.09 */
function measureSatisficationIfActive(item_index){
			/* Heroin 2004.08.09
			   �p�GMeasureSatisfrcationIffActive = false
			   �n�b�������}����, deliver�U�@�Ӥ��e  �⪬�A�g�i�h */
			
			if(parent.s_catalog.pathtree.primaryObjectiveList[item_index].satisfiedByMeasure){
				objectiveRollupUsingMeasureProcess(parent.s_catalog.pathtree.tocList[item_index].id,item_index);
			}
			
			
			/* �P�_�O���O�ݭndisable��hiddenFromChoice
			   �p�G���A���ܫh�s�JDB -- (���k���n,���令���ܪ��A�A�sDB)
			   �æs�JDB Heroin-2003.11.28 */

			var needDesabled=CheckDisplayDisabled(item_index);
			if(needDesabled){
				parent.s_catalog.pathtree.tocList[item_index].disable="true";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[item_index].isDisabled="true";
				var disabledIndex=parent.tocstatus.statusObj.DisplayDisabled(item_index);
			}
			
			var needHiddenformchoice=CheckDisplayHiddenfromchoice(item_index);
			if(needHiddenformchoice){
				/* Heroin-2003.12.30 */
				parent.s_catalog.pathtree.tocDisplayList[item_index].isShow="false";
				parent.s_catalog.pathtree.isHiddenFromChoiceList[item_index].value="true";
				/* Heroin 2004.04.04 */
				parent.s_catalog.pathtree.actionStatusList[item_index].isHiddenFromChoice="true";
				var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(item_index);
			}
			
			var needSkip=CheckDisplaySkip(item_index);
			/* alert(item_index+" / 1005 needSkip="+needSkip); */
			if(needSkip){
				parent.s_catalog.pathtree.actionStatusList[item_index].isSkip="true";
				/* alert(item_index+" isSkip"); */
			}else{
				parent.s_catalog.pathtree.actionStatusList[item_index].isSkip="false";
			}
			
			/* �s�JDB Heroin-2003.12.09 */
			/* set agregation rollupInfo to DB */
			saveAggregationInfoToDB(item_index);
	
}

/*  �ˬditem��success��completion�����A
    Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52  */
function checkCurrentStatus(checkItem){
	var checkStatus="unknown";
	if(checkItem=="success_status"){
		if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveProgressStatus==true){
			/* alert("primay"); */
			if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveSatisfiedStatus==true){
				checkStatus="passed";
			}
			else{
				checkStatus="failed";
			}
			return checkStatus;
		}
		/* Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52  */
		else if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList.length>0){
			for(var p=0;p<primaryObjectiveList[index].p_mapInfoList.length;p++){
				if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID!=""){
				/* alert("shared"); */
					for(var	i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
						if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID){
							/* Vega add readSatisfiedStatus=true; */
							if(parent.s_catalog.pathtree.sharedObjectiveList[i].readSatisfiedStatus==true){
								if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveProgressStatus==true){
									if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveSatisfiedStatus==true){
										checkStatus="passed";
									}
									else{
										checkStatus="failed";
									}
								}								
							}							
						}
					}
				}
			}
			/* Vega:�Ӳz���|�u���@��read��objective */
			return checkStatus;
		}
		else{			
			checkStatus="unknown";
			return checkStatus;
		}
	}
	else if(checkItem=="completion_status"){
		if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptProgressStatus.toString()=="true"){
			if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCompletionStatus.toString()=="true"){
				checkStatus="completed";
				
			}
			else{
				checkStatus="incomplete";
			}
			return checkStatus;
		}
		else{			
			checkStatus="unknown";
			return checkStatus;
		}
	}
}


/* Heroin 2003.10.08 */
/* Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 */
function UpdateSharedObjective(){
	/* Vega: write�i�H�g�h�� */
	if(parent.s_catalog.pathtree.primaryObjectiveList[index].existflag){		
		if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList.length>0){
			for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList.length;p++){
				if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID!=""){
					/* Satisfied Statsus */
					if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].writeSatisfiedStatus=="true" || parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].writeSatisfiedStatus==true){
						for(var	i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
							if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID){
								parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveProgressStatus=true;
								parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveSatisfiedStatus=parent.s_catalog.pathtree.trackingInfoList[index].objectiveSatisfiedStatus;
								break;
							}
						}
					}					
					/* Normalized Measure */
					if(parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].writeNormalizedMeasure=="true" || parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].writeNormalizedMeasure==true){
						for(var	i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
							if(parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[index].p_mapInfoList[p].targetObjectiveID){
								parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveMeasureStatus=true;
								parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveNormalizedMeasure=parent.s_catalog.pathtree.trackingInfoList[index].objectiveNormalizedMeasure;
								break;
							}
						}
					}

				}
			}
		}
			
	}
	
}


/******************************************************************************
**
** Function useCurrentAttemptObjectiveInfo()
** Inputs:  folder_index
** Return:  boolean
**
** Description:�p�Gtoc.ControlModeList��
**	       useCurrentAttemptObjectiveInfo=false,�h�M�����e��objective������
**
** 2004.4.1 Vega: 2004
*******************************************************************************/
function useCurrentAttemptObjectiveInfo(folder_index){
	/* �M��parent��rollup(?)
	   parent.s_catalog.pathtree.trackingInfoList[folder_index].objectiveSatisfiedStatus = "";
	   parent.s_catalog.pathtree.trackingInfoList[folder_index].objectiveNormalizedMeasure = 0.0;
	   ��Xchildren
	   �M��children��objective status��l��
	   �^��true */


	var tempChildIndexArray	= findChildIndex(folder_index);
	var tempIndex="";

	if(tempChildIndexArray.length>0){ 
		for(var	i=0; i<tempChildIndexArray.length ; i++){
			tempIndex = Number(tempChildIndexArray[i].toString());
			/* recursive */
			useCurrentAttemptObjectiveInfo(Number(tempIndex));
			/* ------- */
		
			/* alert(tempIndex+"  /  "+parent.s_catalog.pathtree.controlModeList[Number(tempIndex)].useCurrentAttemptObjectiveInfo+" / "+parent.s_catalog.pathtree.setByContentCheckList[Number(tempIndex)].objectiveSetByContentFlag); */
		
			if(parent.s_catalog.pathtree.controlModeList[tempIndex].useCurrentAttemptObjectiveInfo=="false" || (parent.s_catalog.pathtree.controlModeList[tempIndex].useCurrentAttemptObjectiveInfo=="true" && parent.s_catalog.pathtree.setByContentCheckList[tempIndex].objectiveSetByContentFlag==false)){
				/* 1-1 Primary Objective:clear Primary Objective	status */
				parent.s_catalog.pathtree.trackingInfoList[tempIndex].objectiveProgressStatus = false;
				parent.s_catalog.pathtree.trackingInfoList[tempIndex].objectiveSatisfiedStatus	= false;
				parent.s_catalog.pathtree.trackingInfoList[tempIndex].objectiveMeasureStatus =	false;
				parent.s_catalog.pathtree.trackingInfoList[tempIndex].objectiveNormalizedMeasure = 0.0;
				/* �ʺA���ܹϥ� */
				/* Vega 2004.11.17 itemType */
				if(parent.s_catalog.pathtree.tocList[tempIndex].itemType=="folder"){				
					parent.tocstatus.statusObj.changetocStatus(tempIndex,"unknown","folder");
				}else{
					parent.tocstatus.statusObj.changetocStatus(tempIndex,"unknown","leaf");
				}
				/* 1-2 Primary Objective:clear Target Objective status of Primary Objective */
				/* Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52  */
				if(parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList.length>0){
					for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList.length;p++){
						if(parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList[p].targetObjectiveID!=""){
							var tempTargetObjIndex = parent.functions.enfunctions.returnTargetObjectiveIndex(parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList[p].targetObjectiveID);
							if(parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList[p].writeSatisfiedStatus=="true"){
								parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveProgressStatus = false;		
								parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveSatisfiedStatus = false;
							}
							if(parent.s_catalog.pathtree.primaryObjectiveList[tempIndex].p_mapInfoList[p].writeNormalizedMeasure=="true"){
								parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveMeasureStatus = false;		
								parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveNormalizedMeasure =	0.0;
							}
						}
					}
				}

			
			}
			
			
			if(parent.s_catalog.pathtree.controlModeList[Number(index)].useCurrentAttemptObjectiveInfo=="false" ){
				/* 2 Item Objective */
				var tempObjectiveIndexArray = parent.functions.enfunctions.findItemObjectiveIndex(tempIndex);
	
				if(tempObjectiveIndexArray.length > 0){
					var tempObjectiveIndex;
					for(var	i=0; i<tempObjectiveIndexArray.length; i++){
						/* 2-1 Item Objective: clear Item Objective status	 */
						tempObjectiveIndex = tempObjectiveIndexArray[i];
						parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveProgressStatus = false;
						parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveSatisfiedStatus = false;
						parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveMeasureStatus	= false;
						parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveNormalizedMeasure = 0.0;
						/* ---------------------------- */
						/* 2-2 Item Objective: clear Target Objective status of Item Objective */
						/* =======Yunghsiao.2004.12.13============================================== */
						if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList.length>0){
							for(var p=0;p<parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList.length;p++){
								if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].targetObjectiveID!=""){
									var tempTargetObjIndex = parent.functions.enfunctions.returnTargetObjectiveIndex(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].targetObjectiveID);
									if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].writeSatisfiedStatus=="true"){
										parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveProgressStatus = false;		
										parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveSatisfiedStatus = false;
									}
									if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].writeNormalizedMeasure=="true"){
										parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveMeasureStatus = false;		
										parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveNormalizedMeasure =	0.0;
									}
		      	/* ========================================================================= */
								}			
							}
						}
					}	
				}
			}
			RefreshActivityStatus(tempIndex);
		}
		return true;
	}else{
		return false;
	}
}


function clearItemObjectiveStatus(itemIndex){
	
			/* ------- */
		
	/* 1-1 Primary Objective:clear Primary Objective	status */
	parent.s_catalog.pathtree.trackingInfoList[itemIndex].objectiveProgressStatus = false;
	parent.s_catalog.pathtree.trackingInfoList[itemIndex].objectiveSatisfiedStatus	= false;
	parent.s_catalog.pathtree.trackingInfoList[itemIndex].objectiveMeasureStatus =	false;
	parent.s_catalog.pathtree.trackingInfoList[itemIndex].objectiveNormalizedMeasure = 0.0;
	/* �ʺA���ܹϥ� */
	/* Vega 2004.11.17 itemType */
	if(parent.s_catalog.pathtree.tocList[itemIndex].itemType=="folder"){	
		parent.tocstatus.statusObj.changetocStatus(itemIndex,"unknown","folder");
	}else{
		parent.tocstatus.statusObj.changetocStatus(itemIndex,"unknown","leaf");
	}
	/* 1-2 Primary Objective:clear Target Objective status of Primary Objective */
	/* Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52  */
	if(parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList.length>0){
		for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList.length;p++){
			if(parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList[p].targetObjectiveID!=""){
				var tempTargetObjIndex = parent.functions.enfunctions.returnTargetObjectiveIndex(parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList[p].targetObjectiveID);
				if(parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList[p].writeSatisfiedStatus=="true"){
					parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveProgressStatus = false;		
					parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveSatisfiedStatus = false;
				}
				if(parent.s_catalog.pathtree.primaryObjectiveList[itemIndex].p_mapInfoList[p].writeNormalizedMeasure=="true"){
					parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveMeasureStatus = false;		
					parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveNormalizedMeasure =	0.0;
				}
			}
		}
	}		
	/* 2 Item Objective */
	var tempObjectiveIndexArray = parent.functions.enfunctions.findItemObjectiveIndex(itemIndex);
		if(tempObjectiveIndexArray.length > 0){
		var tempObjectiveIndex;
		for(var	i=0; i<tempObjectiveIndexArray.length; i++){
			/* 2-1 Item Objective: clear Item Objective status */
				tempObjectiveIndex = tempObjectiveIndexArray[i]
			parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveProgressStatus = false;
			parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveSatisfiedStatus = false;
			parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveMeasureStatus	= false;
			parent.s_catalog.pathtree.objectiveProgressInfoList[tempObjectiveIndex].objectiveNormalizedMeasure = 0.0;
			/* ---------------------------- */
			/* 2-2 Item Objective: clear Target Objective status of Item Objective */
			/* =======Yunghsiao.2004.12.13================================================ */
			if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList.length>0){
				for(var p=0;p<parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList.length;p++){
					if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].targetObjectiveID!=""){
						var tempTargetObjIndex = parent.functions.enfunctions.returnTargetObjectiveIndex(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].targetObjectiveID);
						if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].writeSatisfiedStatus=="true"){
							parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveProgressStatus = false;		
							parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveSatisfiedStatus = false;
						}
						if(parent.s_catalog.pathtree.objectiveList[tempObjectiveIndex].mapInfoList[p].writeNormalizedMeasure=="true"){
							parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveMeasureStatus = false;		
							parent.s_catalog.pathtree.sharedObjectiveList[tempTargetObjIndex].objectiveNormalizedMeasure =	0.0;
						}
					}
				}
			}
			/* ========================================================================= */
		}			
	}
	RefreshActivityStatus(itemIndex);
		
}

/******************************************************************************
**
** Function useCurrentAttemptProgressInfo()
** Inputs:  folder_index
** Return:  boolean
**
** Description:�p�Gtoc.ControlModeList�� 
**	       useCurrentAttemptProgressInfo=false,�h�M�����e��Attempt������
**
** 2004.4.1 Vega: 2004
*******************************************************************************/
function useCurrentAttemptProgressInfo(folder_index){
/*  ��Xchildren
	�M��children��objective status��l��   
	�^��true  */
	var tempChildIndexArray	= findChildIndex(folder_index);
	var tempIndex="";
	if(tempChildIndexArray.length>0){ 
		for(var	i=0; i<tempChildIndexArray.length ; i++){
			tempIndex = tempChildIndexArray[i].toString();
			useCurrentAttemptProgressInfo(tempIndex);
			
			if(parent.s_catalog.pathtree.controlModeList[tempIndex].useCurrentAttemptProgressInfo=="false" || (parent.s_catalog.pathtree.controlModeList[tempIndex].useCurrentAttemptProgressInfo=="true" && parent.s_catalog.pathtree.setByContentCheckList[tempIndex].completionSetByCobtentFlag==false)){
			
				parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAttemptProgressStatus=false;
				parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAttemptCompletionAmount=0.0;
				parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAttemptCompletionStatus=false;
				parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAttemptAbsoluteDuration=0.0;
				parent.s_catalog.pathtree.activityStatusList[tempIndex].activityAttemptExperiencedDuration=0.0;
				RefreshActivityStatus(tempIndex);
				
				/* �ʺA���ܹϥ�
				   Vega 2004.11.17 itemType  */
				if(parent.s_catalog.pathtree.tocList[tempIndex].itemType=="folder"){	
					parent.tocstatus.statusObj.changetocStatus(tempIndex,"unknown","folder");
				}else{
					parent.tocstatus.statusObj.changetocStatus(tempIndex,"unknown","leaf");
				}
			}
			
			
			
		}
	return true;
	}else{
		return false;
	}
}



// Heroin 2004.10.06
function clearItemProgressStatus(itemIndex){ 

	// ------------
	parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptProgressStatus=false;
	parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCompletionAmount=0.0;
	parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCompletionStatus=false;
	parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptAbsoluteDuration=0.0;
	parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptExperiencedDuration=0.0;
	RefreshActivityStatus(itemIndex);
			
	// �ʺA���ܹϥ�
	// Vega 2004.11.17 itemType
	if(parent.s_catalog.pathtree.tocList[itemIndex].itemType=="folder"){	
		parent.tocstatus.statusObj.changetocStatus(itemIndex,"unknown","folder");
	}else{
		parent.tocstatus.statusObj.changetocStatus(itemIndex,"unknown","leaf");
	}
}

/******************************************************************************
**
** Function useCurrentAttemptObjectiveInfo()
** Inputs:  index of parent folder
** Return:  array of children's	index
**
** Description:Xchildrenindex
** 
** 2004.4.1 Vega: 2004
*******************************************************************************/
function findChildIndex(parent_index){
	var tempChildIndexArray=new Array();
	var j=0;
	var tempParentID = parent.s_catalog.pathtree.tocList[parent_index].id;

	for(var	i=0; i<parent.s_catalog.pathtree.tocList.length-1;i++){
		if(parent.s_catalog.pathtree.tocList[i].parentID==tempParentID){
			tempChildIndexArray[j] = i;
			j++;
		} 
	}
	if(j>0){
		return tempChildIndexArray;
	}else{
		return "";
	}
	
}

/******************************************************************************
**
** Function RefreshActivityStatus(activeIndex)
** Inputs:  None
** Return:  None
**
** Description:	Inherit	from CommitActivityStatus()
** 
** 2004.4.5 Vega: 2004
*******************************************************************************/
function RefreshActivityStatus(activeIndex){
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	// xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes[0]);
	var rootElement	= xmldoc.documentElement;

	/* course_ID,user_ID,sco_ID
	   �n��check�Osco��sca,�p�G�Osco�N���ݭn�Nsession_time�^��
	   �p�G�Osca�N���ݭn��session_time, isSuspended
	   �p��P�_�Osco�άOsca??
	   �p�GtocList[activeIndex].id current id�p�G����api adapter�̪�id
	   �N��ܳo��sco��initialize,�ҥH�N�Osco   */

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",parent.s_catalog.pathtree.tocList[activeIndex].id);
	rootElement.setAttribute("Message_Type","ActivityStatus");


	if(parent.API.LMSGetValue("cmi.core.student_id")!=""){
		
		if(parent.API.GetSCO_ID() != parent.s_catalog.pathtree.tocList[activeIndex].id){
			
			rootElement.setAttribute("Scorm_Type","sca");
			parent.s_catalog.pathtree.SCOTimerList[activeIndex].endTime=new Date().getTime();
			
			// Heroin-2003.11.28
			var tmp=parent.s_catalog.pathtree.SCOTimerList[activeIndex].endTime-parent.s_catalog.pathtree.SCOTimerList[activeIndex].startTime;		
			var elapsedSeconds = ( tmp / 1000 );
					
			var cmi_core_session_time_Element = xmldoc.createElement("cmi_core_session_time");
            if( cmi_core_session_time_Element.textContent == undefined ){
                cmi_core_session_time_Element.text = parent.functions.enfunctions.convertTotalSeconds(elapsedSeconds);
            }else
				cmi_core_session_time_Element.textContent = parent.functions.enfunctions.convertTotalSeconds(elapsedSeconds);
			rootElement.appendChild(cmi_core_session_time_Element);
			
			
			var cmi_core_total_time	= cmi_core_session_time_Element.text;	
			var cmi_core_total_time_Element	= xmldoc.createElement("duration");
            if( cmi_core_total_time_Element.textContent == undefined )
			  cmi_core_total_time_Element.text = cmi_core_total_time;
            else
                cmi_core_total_time_Element.textContent = cmi_core_total_time;
			rootElement.appendChild(cmi_core_total_time_Element);
			
			// added	by Heroin 2003.10.27
			var isSuspended_Element	= xmldoc.createElement("isSuspended");
            if( isSuspended_Element.textContent == undefined )
                isSuspended_Element.text = "" +	parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
            else
				isSuspended_Element.textContent = "" +	parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
			rootElement.appendChild(isSuspended_Element);
			
			// added	by Heroin 2003.11.02
			var cmi_core_score_normalized_Element =	xmldoc.createElement("cmi_core_score_normalized");		
			if(parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveMeasureStatus){
                if( cmi_core_score_normalized_Element.textContent == undefined )
                    cmi_core_score_normalized_Element.text = parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveNormalizedMeasure;
                else
					cmi_core_score_normalized_Element.textContent = parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveNormalizedMeasure;
			}
			else{
                if( cmi_core_score_normalized_Element.textContent == undefined )
                    cmi_core_score_normalized_Element.text="";
                else
                    cmi_core_score_normalized_Element.textContent="";
			}			
			rootElement.appendChild(cmi_core_score_normalized_Element);

			var cmi_core_success_status_Element = xmldoc.createElement("cmi_core_success_status");		    
				if(parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveProgressStatus==true){
					if(parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveSatisfiedStatus==true){
                        if( cmi_core_success_status_Element.textContent == undefined )
                            cmi_core_success_status_Element.text = "passed";	
                        else
                            cmi_core_success_status_Element.textContent = "passed";		    
					}
					else if(parent.s_catalog.pathtree.trackingInfoList[activeIndex].objectiveSatisfiedStatus==false){
                        if( cmi_core_success_status_Element.textContent == undefined )
                            cmi_core_success_status_Element.text = "failed";
                        else
                            cmi_core_success_status_Element.textContent = "failed";
					}
				}
				else{
                    if( cmi_core_success_status_Element.textContent == undefined )
                        cmi_core_success_status_Element.text = "unknown";
                    else
                        cmi_core_success_status_Element.textContent = "unknown";
				}
			rootElement.appendChild(cmi_core_success_status_Element);
			var cmi_core_completion_status_Element = xmldoc.createElement("cmi_core_completion_status");
				if(parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptProgressStatus==true){
					if(parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCompletionStatus==true){
                        if( cmi_core_completion_status_Element.textContent == undefined )
                            cmi_core_completion_status_Element.text	= "completed";	
                        else
                            cmi_core_completion_status_Element.textContent	= "completed";		    
					}
					else if(parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCompletionStatus==false){
                        if( cmi_core_completion_status_Element.textContent == undefined )
                            cmi_core_completion_status_Element.text	= "incomplete";
                        else
                            cmi_core_completion_status_Element.textContent	= "incomplete";
					}
				 }else{
                    if( cmi_core_completion_status_Element.textContent == undefined )
                        cmi_core_completion_status_Element.text	= "unknown";
                    else
                        cmi_core_completion_status_Element.textContent	= "unknown"; 
				 }
			rootElement.appendChild(cmi_core_completion_status_Element);
			
			var cmi_score_raw_Element = xmldoc.createElement("cmi_score_raw");
                if( cmi_score_raw_Element.textContent == undefined )
                    cmi_score_raw_Element.text = ""	+ parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
                else
                    cmi_score_raw_Element.textContent = ""	+ parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
			rootElement.appendChild(cmi_score_raw_Element);

			
			var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");
            if( cmi_core_attempt_count_Element.textContent == undefined )
                cmi_core_attempt_count_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCount;
            else
                cmi_core_attempt_count_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCount;
			rootElement.appendChild(cmi_core_attempt_count_Element);
			
			
			// Heroin-2003.12.08
			var cmi_core_isDisabled_Element	= xmldoc.createElement("cmi_core_isDisabled");
            if( cmi_core_isDisabled_Element.textContent == undefined )   
                cmi_core_isDisabled_Element.text = parent.s_catalog.pathtree.tocList[activeIndex].disable;
            else
                cmi_core_isDisabled_Element.textContent = parent.s_catalog.pathtree.tocList[activeIndex].disable;
			rootElement.appendChild(cmi_core_isDisabled_Element);
			
			var cmi_core_isHiddenFromChoice_Element	= xmldoc.createElement("cmi_core_isHiddenFromChoice");		
            if( cmi_core_isHiddenFromChoice_Element.textContent == undefined )
                cmi_core_isHiddenFromChoice_Element.text = parent.s_catalog.pathtree.isHiddenFromChoiceList[activeIndex].value;
            else
                cmi_core_isHiddenFromChoice_Element.textContent = parent.s_catalog.pathtree.isHiddenFromChoiceList[activeIndex].value;
			rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
			
			
			// Heroin-2003.12.15 LimitCondtion Duration		
			var cmi_core_attempt_absolut_duration_Element =	xmldoc.createElement("cmi_core_attempt_absolut_duration");	
            if( cmi_core_attempt_absolut_duration_Element.textContent == undefined )
                cmi_core_attempt_absolut_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptAbsoluteDuration;
            else
                cmi_core_attempt_absolut_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptAbsoluteDuration;
			rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
			
			var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");	
            if( cmi_core_attempt_experienced_duration_Element.textContent == undefined )
                cmi_core_attempt_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptExperiencedDuration;
            else
                cmi_core_attempt_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptExperiencedDuration;
			rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
			
			var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");	
            if( cmi_core_activity_absolut_duration_Element.textContent == undefined )
                cmi_core_activity_absolut_duration_Element.text	= parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAbsoluteDuration;
            else
                cmi_core_activity_absolut_duration_Element.textContent	= parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAbsoluteDuration;
			rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
			
			var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");	
            if( cmi_core_activity_experienced_duration_Element.textContent == undefined )
                cmi_core_activity_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityExperiencedDuration;
            else
                cmi_core_activity_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityExperiencedDuration;
			rootElement.appendChild(cmi_core_activity_experienced_duration_Element);
			
		}else{
			
			rootElement.setAttribute("Scorm_Type","sco");

			var isSuspended_Element	= xmldoc.createElement("isSuspended");
            if( isSuspended_Element.textContent == undefined )
                isSuspended_Element.text = "" +	 parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
            else
				isSuspended_Element.textContent = "" +	 parent.s_catalog.pathtree.activityStatusList[activeIndex].activityisSuspended;
			rootElement.appendChild(isSuspended_Element);	
			var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");		    
			if( cmi_core_attempt_count_Element.textContent == undefined )
                cmi_core_attempt_count_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCount;
            else
                cmi_core_attempt_count_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptCount;
			rootElement.appendChild(cmi_core_attempt_count_Element);
			
			// Heroin-2003.12.08
			var cmi_core_isDisabled_Element	= xmldoc.createElement("cmi_core_isDisabled");	
            if( cmi_core_isDisabled_Element.textContent == undefined )
                cmi_core_isDisabled_Element.text = parent.s_catalog.pathtree.tocList[activeIndex].disable;
            else
                cmi_core_isDisabled_Element.textContent = parent.s_catalog.pathtree.tocList[activeIndex].disable;
			rootElement.appendChild(cmi_core_isDisabled_Element);
			
			
			var cmi_core_isHiddenFromChoice_Element	= xmldoc.createElement("cmi_core_isHiddenFromChoice");	
            if( cmi_core_isHiddenFromChoice_Element.textContent == undefined )
                cmi_core_isHiddenFromChoice_Element.text = parent.s_catalog.pathtree.isHiddenFromChoiceList[activeIndex].value;
            else
                cmi_core_isHiddenFromChoice_Element.textContent = parent.s_catalog.pathtree.isHiddenFromChoiceList[activeIndex].value;
			rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
			
			
			// Heroin-2003.12.15 LimitCondtion Duration		
			var cmi_core_attempt_absolut_duration_Element =	xmldoc.createElement("cmi_core_attempt_absolut_duration");		
            if( cmi_core_attempt_absolut_duration_Element.textContent == undefined )
                cmi_core_attempt_absolut_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptAbsoluteDuration;
            else
                cmi_core_attempt_absolut_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptAbsoluteDuration;
			rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
			
			var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");		
            if( cmi_core_attempt_experienced_duration_Element.textContent == undefined )
                cmi_core_attempt_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptExperiencedDuration;
            else
                cmi_core_attempt_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAttemptExperiencedDuration;
			rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
			
			var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");
            if( cmi_core_activity_absolut_duration_Element.textContent == undefined )
                cmi_core_activity_absolut_duration_Element.text	= parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAbsoluteDuration;
            else
                cmi_core_activity_absolut_duration_Element.textContent	= parent.s_catalog.pathtree.activityStatusList[activeIndex].activityAbsoluteDuration;
			rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
			
			var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");		
            if( cmi_core_activity_experienced_duration_Element.textContent == undefined )
                cmi_core_activity_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityExperiencedDuration;
            else
                cmi_core_activity_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[activeIndex].activityExperiencedDuration;
			rootElement.appendChild(cmi_core_activity_experienced_duration_Element);
			
			
		}
	}
	else return;
	
	var ServerSide = xmlhttp_set;	
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.send(xmldoc.xml);
	

}


/* Step2:�w��ثeSCO�ҥ]�t��preConditionRule�i��parsing */
function parsePreConditionRules(){
	// alert(index+"  parsePreConditionRules");
	var preConditionRuleFlag="false";
	var end="false";
	
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==index){
			var preConditionRuleFlag="true";
		}
	}
	if(preConditionRuleFlag=="true"){ // preConditionRule
	// socool 2004.12.13 ���a��,�n�A��SRTE�D��
		preConditionRuleFlow();
	}
	else{ // �S��preConditionRule
	
		if(parent.s_catalog.pathtree.controlModeList[index].existflag || index==0){
			// �P�_Content Agreegation��Rule
			
			if((parent.s_catalog.pathtree.controlModeList[index].flow=="false") ||	(parent.s_catalog.pathtree.controlModeList[index].flow=="")){
				alert("Wait for	another	navigation request!");
				parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity=index;
				// �ʺA���Tree���ثeItem����m
				parent.tocstatus.statusObj.changeCurrentBar(index);
				
				return("");
			}
			else{
				SequencingRequestProcess("Find",seqRequest); 
			}
		}
		else{
			SequencingRequestProcess("Find",seqRequest); 
		}
	}
}



function preConditionRuleFlow(){
	// alert(index+"  in precondition");
	var j=0;
	var preConditionRules_collection=new Array();
	var preConditionRules_flag="false";
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){// �����ثeSCO�]�t��preConditionRules
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==index){
			preConditionRules_collection[j]=i;
			preConditionRules_flag="true";
			j++;
		}
	}
	
	// Yunghsiao 2005.08.10
	if(parent.s_catalog.pathtree.deliveryControlsList[index].tracked.toString()=="false"){
		preConditionRules_flag = "false";
	}

	if(preConditionRules_flag=="false"){
		// �S������rules,����find
		SequencingRequestProcess("Find",seqRequest);
	}
	else{
		// HHEERROOIINN	 Heroin-2003.12.31 �Ncheck condition rules function����engine
		var action_collection=new Array();
		action_collection=parent.check.checkrules.preConditionRules(index);	
		var a =	action_collection.length;
		if(a==0){ // precondition_result=false
			// Vega 2004.11.17 itemType
			if(parent.s_catalog.pathtree.tocList[index].itemType=="folder"){	
				// �ʺA�i�}Folder
				parent.tocstatus.statusObj.unfold(index);
				
				// �ʺA���Concurrent Activity-----------------------------------------
				for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
					if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemID==parent.s_catalog.pathtree.tocList[index].id){
						if(parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose=="reference"){
							eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML='<a href=" + material_path + parent.s_catalog.pathtree.auxiliaryResourceList[i].href	+ " target=_blank><img src=images/"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+".gif border=0>"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"</a>'");
						}
						break;
					}
				}
					
				SequencingRequestProcess("Find",seqRequest);
			}
			else{
				DeliveryRequesetProcess();
				SCODelivered = "true";
			}	
		}
		else{
			// Heroin-2003.12.04 
			// �ק�action�����ǤΤ��e
			var action_skip=false;
			var action_disabled=false;
			var action_hiddenformchoice=false;
			var action_stopforwardTraversal=false;
			// Heroin 2004.12.02
			for(var	i=0;i<action_collection.length;i++){
				// alert("precondition action_collection[i]="+action_collection[i]);
				if(action_collection[i]=="skip"){
					action_skip = true;
					parent.s_catalog.pathtree.actionStatusList[index].isSkip = "true";
					// alert(index+"=isSkip!!!!!!!!!!!");
				}
				else if(action_collection[i]=="disabled"){
					action_disabled = true;		
					parent.s_catalog.pathtree.tocList[index].disable = "true";
					var disabledIndex=parent.tocstatus.statusObj.DisplayDisabled(index);	
				}
				else if(action_collection[i]=="hiddenFromChoice"){
					action_hiddenformchoice = true;
					parent.s_catalog.pathtree.tocDisplayList[index].isShow="false";
					var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(index);
				}
				else if(action_collection[i]=="stopForwardTraversal"){
					action_stopforwardTraversal = true;
					// Heroin 2004.04.04
					parent.s_catalog.pathtree.actionStatusList[i].isStopForwardTraversal = "true";
				}
			}
			
			if (action_skip != true)
				parent.s_catalog.pathtree.actionStatusList[index].isSkip="false";
			
			
			/* ���U�ӭn�����h     */
			if(action_skip==true ||	action_disabled==true){
				var count_skip_children;
				// Vega 2004.11.17 itemType
				if(parent.s_catalog.pathtree.tocList[index].itemType=="folder"){
					var s_index=index;
					count_skip_children=countChildrenSkip();
					if(seqRequest=="Previous"){
						index=s_index;
						SequencingRequestProcess("Find",seqRequest);
					}
					else if(seqRequest=="Continue"){
						index=s_index;
						if(action_skip==true){
							index=index+count_skip_children;
						}else if(action_disabled==true){
							index=disabledIndex;
						}
						SequencingRequestProcess("Find",seqRequest);
					}	
				}
				else{
					SequencingRequestProcess("Find",seqRequest);
				}
			}
			
			else if(action_hiddenformchoice==true){
				
				var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(index);
				// Vega 2004.11.17 itemType
				if(parent.s_catalog.pathtree.tocList[index].itemType=="folder"){
					// �ʺA�i�}Folder
					parent.tocstatus.statusObj.unfold(index);
					
					// �ʺA���Concurrent Activity-----------------------------------------
					for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
						if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemID==parent.s_catalog.pathtree.tocList[index].id){
							if(parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose=="reference"){
								eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML='<a href=" + material_path + parent.s_catalog.pathtree.auxiliaryResourceList[i].href	+ " target=_blank><img src=images/"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+".gif border=0>"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"</a>'");
							}
							break;
						}
					}
						
					SequencingRequestProcess("Find",seqRequest);
					}
				else{
					DeliveryRequesetProcess();
					SCODelivered = "true";
				}	
			}
			
			if(action_stopforwardTraversal==true){
			
				// Vega 2004.11.17 itemType
				if(parent.s_catalog.pathtree.tocList[index].itemType=="folder"){
					// �ʺA�i�}Folder
					parent.tocstatus.statusObj.unfold(index);
					
					// �ʺA���Concurrent Activity-----------------------------------------
					for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
						if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemID==parent.s_catalog.pathtree.tocList[index].id){
							if(parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose=="reference"){
								eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML='<a href=" + material_path + parent.s_catalog.pathtree.auxiliaryResourceList[i].href	+ " target=_blank><img src=images/"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+".gif border=0>"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"</a>'");
							}
							break;
						}
					}
						
					SequencingRequestProcess("Find",seqRequest);
				}
				else{
					DeliveryRequesetProcess();
					SCODelivered = "true";
				}	
			
			}
		
		}
	}	
}
// Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
function findTargetStatus(referencedObjectiveIndex){
	var shardObjectiveStatus;

	for(var	k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		if(parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length>0){
			for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;p++){				
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[p].targetObjectiveID){
					if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus){
						shardObjectiveStatus = true;
						break;
					}else{
						shardObjectiveStatus = false;
						break;										
					}	
				}

			}
		}

	}
	return shardObjectiveStatus;
}

// Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
// Heroin 2004.04.09
function findTargetStatusKnown(referencedObjectiveIndex){
	var shardObjectiveStatus;
	for(var	k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		
		if(parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length>0){
			for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;p++){				
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[p].targetObjectiveID){
					if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveProgressStatus.toString()=="true"){
						shardObjectiveStatus = true;
					}
					else{
						shardObjectiveStatus = false;						
					}	
				}
			}
		}

	}
	return shardObjectiveStatus;
}

// Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
// Heroin 2004.04.09
function findTargetMeasureKnown(referencedObjectiveIndex){
	var shardObjectiveStatus;
	for(var	k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
		
		if(primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length>0){
			for(var p=0;p<primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList.length;p++){				
				if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].p_mapInfoList[p].targetObjectiveID){
					if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus.toString()=="true"){
						shardObjectiveStatus = true;
					}
					else{
						shardObjectiveStatus = false;						
					}
				}
			}
		}
	}
	return shardObjectiveStatus;
}




/* Step3:��XCandidate SCO  */
/******************************************************************************
**
** Function SequencingRequestProcess(instruction,parameter)
** Inputs:  instruction:
				parameter: Sequencing Request
** Return:  None
**
** Description:
** 
**
*******************************************************************************/
function SequencingRequestProcess(instruction,parameter){
	
	
	if(parameter=="Continue"){
		// Vega 2004.11.17 itemType
		if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){	
			// Modified by Heroin 2004.06.25
			parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity=index;			
			if(parent.s_catalog.pathtree.controlModeList[Number(index)].useCurrentAttemptObjectiveInfo=="false"){
				useCurrentAttemptObjectiveInfo(Number(index));		
			}else if(index!=""){
				useCurrentAttemptObjectiveInfo(Number(index));
			}
			if(parent.s_catalog.pathtree.controlModeList[Number(index)].useCurrentAttemptProgressInfo =="false"){
				useCurrentAttemptProgressInfo(Number(index));
			}else if(index!=""){
			
				useCurrentAttemptProgressInfo(Number(index));
			}
			
			
			
			if(index>=0 && (index.toString()!="")){
				clearItemProgressStatus(index);
				clearItemObjectiveStatus(index);
			}
		}
			
	}else if(parameter=="Previous"){
		
		var heroinParentID = parent.s_catalog.pathtree.tocList[index].parentID;
		var heroinSelfIndex = index;
		var heroinPreviousIndex = -1;
		for(var dummy_index=heroinSelfIndex-1;dummy_index>0;dummy_index--){
			if(parent.s_catalog.pathtree.tocList[dummy_index].parentID==heroinParentID){
				heroinPreviousIndex = dummy_index;
				break;
			}
		
		}
		if(heroinPreviousIndex!= -1){
			// Vega 2004.11.17 itemType
			if(parent.s_catalog.pathtree.tocList[Number(heroinPreviousIndex)].itemType=="folder"){	
				// socool 2004.12.15 �n�Ҽ{parent��status,��render ICON
				var isSkip = parent.s_catalog.pathtree.actionStatusList[Number(heroinPreviousIndex)].isSkip;	
				var isDisable = parent.s_catalog.pathtree.tocList[Number(heroinPreviousIndex)].disable;		
				if((parent.s_catalog.pathtree.controlModeList[Number(heroinPreviousIndex)].useCurrentAttemptObjectiveInfo=="true")&& (isSkip != "true") &&(isDisable != "true")){
					useCurrentAttemptObjectiveInfo(Number(heroinPreviousIndex));		
				}
				// socool 2004.12.15�u�MuseCurrentAttemptObjectiveInfo=="true",�쥻�O�{��false�ɤ~�M
							
				
				if((parent.s_catalog.pathtree.controlModeList[Number(heroinPreviousIndex)].useCurrentAttemptProgressInfo =="true")&& (isSkip != "true") &&(isDisable != "true")){
					useCurrentAttemptProgressInfo(Number(heroinPreviousIndex));
				}
				// socool 2004.12.15�u�MuseCurrentAttemptProgressInfo=="true",�쥻�O�{��false�ɤ~�M
			}
			
		}
		
	}
	
	// -------------
	if(SCODelivered	== "false"){	
		if(parameter == "Start"){
			
			// �M���Ҧ���State Model
			// Heroin 2004.07.30
			
			 useCurrentAttemptProgressInfo(0);
			 useCurrentAttemptObjectiveInfo(0);
			
			
		/*  �A��data model�����x�s�����mapping��state model
			mapping�O���O�u��toc�i�H��???
			�n�qXMLDI���Ƨ�X��
			�bengine�i���i�H���XMLDI??? --> DBXML
			��toc����InitialStatus function����state model		
			�Astart --> launch�Ĥ@��sco */
			preRollup(index);

			// Vega 2004.11.17 itemType
			if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){// �ثeItem�O�ؿ��~��parse�U�@��Item
				index=0;
				seqRequest="Continue";
				SequencingRequestProcess("Find",seqRequest);
			}
			else{
				DeliveryRequesetProcess();// �N�ثeSCO��X
				SCODelivered = "true";
			}
		}
		else if(parameter == "Continue"){
			index++;
			if(index < parent.s_catalog.pathtree.tocList.length){
				preRollup(index);
			}

			if(index<parent.s_catalog.pathtree.tocList.length){
				// �ˬd preConditionRule
				var preConditionRule_flag="false";
				for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
					if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==index){
						preConditionRule_flag="true";
					}
				}
				
				if(preConditionRule_flag=="false"){
					// Heroin-2003.11.17 added isvisibleIndex
					// Vega 2004.11.17 itemType
					if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){
						// �ʺA�i�}Folder
						parent.tocstatus.statusObj.unfold(index);
						SequencingRequestProcess("Find",seqRequest);
					}else{
						var tempPPPIndex = parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);						
						DeliveryRequesetProcess();// �NCandidate	SCO Deliver
						SCODelivered = "true";
					}
				}else{
					parsePreConditionRules();
					
				}
			}
			else{
				if(!queryPermitted){
					alert("No SCO is available!");
				}	
			}	
		}
		else if(parameter=="Previous"){	
		
		/*  Heroin 2004.07.20
			if forward only == true
			�p�G�e�@�ӥS�̦��p�� , �hluanch�Ĥ@�Ӥp��
			�p�G�S��forward only=true
			parent.s_catalog.pathtree.controlModeList.forwardOnly
			
			1.������
			2.���� forwardOnly=true
			3.��������p�� ....   */
			
			var previousCandidate ;
			if(index <= 0){
				previousCandidate = 0;
			}else{
				previousCandidate = index-1;
			}
			
			if(parent.s_catalog.pathtree.controlModeList[previousCandidate].forwardOnly=="true"){
				previousCandidate = recursiveFindForwardOnlyCandidate(previousCandidate);
				
				// Heroin 2004.07.22
				// Forward Only check - skip & disable
				
				// �o�̭ncheck ���tree �O�_�� disable �H�� skip ...
				// �u�n���@�ӥi�Hluanch�N�i�H....
				var tempCandidate = previousCandidate;
				var nowIndex = index;
				var findCandidate = "false";
				
				// Heroin 2004.11.08 disabled 
				var itemAncestors=findAncestors(previousCandidate);
				var parentDisabled=false;
				// from current to common ancestor
				for(var	i=0;i<itemAncestors.length-1;i++){
					if(parent.s_catalog.pathtree.actionStatusList[itemAncestors[i]].isDisabled.toString()=="true" || parent.s_catalog.pathtree.actionStatusList[itemAncestors[i]].isSkip.toString()=="true"){
						parentDisabled=true;
						if(itemAncestors[i]>1){
							index = itemAncestors[i]-1;
						}
						break;
					}
					
				}	
				
				if(parentDisabled==true){
				
					seqRequest="Previous";  	
					SequencingRequestProcess("Find",seqRequest);
				
				}else{
				
					for(var i= tempCandidate ; i<nowIndex ; i++){
						/* check disabled �ӥB�n�Oleaf
						   check�u�n���@�Ӥp�ĥi�Hluanch (�S��disable �] �S��skip,�ӥB�Oleaf)	
						   Heroin 2004.10.22
						   �@����commn parent ���ncheck disabled �� skip  */
						
						if(parent.s_catalog.pathtree.actionStatusList[i].isDisabled.toString()!="true" && parent.s_catalog.pathtree.actionStatusList[i].isSkip.toString()!="true"){
							// check leaf
							if(parent.s_catalog.pathtree.tocList[i].idref!=""){							
								tempCandidate = i;
								previousCandidate = i;
								seqRequest="Continue";
								findCandidate = "true"
								break;
							}
						}			
					}
					
					
					if(findCandidate=="false"){
						index=previousCandidate;	
						seqRequest="Previous";  	
						SequencingRequestProcess("Find",seqRequest);
					}else{
						
						index=previousCandidate;
						DeliveryRequesetProcess();// �NCandidate	SCO Deliver
						SCODelivered = "true";
						
					}
					
				}
				
			}else{			
				index--;
			}
				// alert("&&index="+index);
				if(index >= 0){
					// 2004.10.28
					preRollup(index);
					// �ˬd preConditionRule
					var preConditionRule_flag="false";
					for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
						if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==index){
							preConditionRule_flag="true";
						}
					}
					
					// alert(index+"  @@preConditionRule_flag="+preConditionRule_flag);
					if(preConditionRule_flag=="false"){
						if(parent.s_catalog.pathtree.tocList[index].idref==""){ // �ثeItem�O�ؿ��~��parse�W�@��Item
							SequencingRequestProcess("Find",seqRequest);
						}
						else{
							DeliveryRequesetProcess();// �NCandidate	SCO Deliver
							SCODelivered = "true";
						}
					}
					else{
						parsePreConditionRules();
					
					}
				}	
				else{
					if(!queryPermitted){
						// alert(index);
						alert("No SCO is available!");
					}	
				}
						
		}
		else if(parameter=="Retry"){
			preRollup(index);

			if(index<parent.s_catalog.pathtree.tocList.length){
				// �ˬd preConditionRule
				var preConditionRule_flag="false";
				for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
					if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==index){
						preConditionRule_flag="true";
					}
				}
				if(preConditionRule_flag=="false"){
					// Heroin-2003.11.17 added isvisibleIndex
					// Vega 2004.11.17 itemType
					if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){ // �ثeItem�O�ؿ��~��parse�U�@��Item
						// �ʺA�i�}Folder
						parent.tocstatus.statusObj.unfold(index);
						seqRequest="Continue";
						SequencingRequestProcess("Find",seqRequest);
					}
					else{
						
						DeliveryRequesetProcess();// �NCandidate	SCO Deliver
						SCODelivered = "true";
					}
				}
				else{
					parsePreConditionRules();
					
				}
			}
			else{
				if(!queryPermitted){
					alert("No SCO is available!");
				}	
			}	
			
		}
		else if(parameter=="Choice"){
			preRollup(index);

			ChoiceSequencingRequestProcess();
			
		}
	}	
}
// 2004.3.31 Vega: 2004
function retrySequencingRequestProcess(){	
	
	if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive==true || parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisSuspended==true){
		return false; 
	}
	
	// Vega 2004.11.17 itemType
	if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){ // �ثeItem�O�ؿ�
		seqRequest="Continue";
		SequencingRequestProcess("Find",seqRequest);
		return true;
	}else{
		// �ALoad�ثeSCO�@��
		DeliveryRequesetProcess();
		SCODelivered = "true";
		return true; 
	}	

}

// Heroin 2004.07.21
function recursiveFindForwardOnlyCandidate(tempIndex){
	// find parentIndex
	var parentIndex;
	var cadnidateIndex = tempIndex;
	for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[tempIndex].parentID){
			parentIndex=i;
			break;
		}
	}
	
	if(parent.s_catalog.pathtree.controlModeList[tempIndex].forwardOnly.toString=="true"){
		candidateIndex = recursiveFindForwardOnlyCandidate(parentIndex);
	}else{
		candidateIndex = parentIndex;	
	}
	return candidateIndex;
}


/*********************RollUP********************************************************/

// Step4:�P�_�O�_�n�i��Rollup Process
// overall Rollup Process - Added by Heroin 2003.10.23
// Vega 2004.10.28 rollup �� index
function overallRollupProcess(current_index){ 
	if(deliveryMode=="normal"){	
		
		// Heroin 2004.04.08
		while(current_index!=0){  // for	each activity in the activity path!
			var parent_itemIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[current_index].parentID);			
			var parent_ID=parent.s_catalog.pathtree.tocList[current_index].parentID;			
			var rollupRulesListcollection= findRollupRules(parent_itemIndex);
			
			measureRollupProcess(current_index,parent_itemIndex,parent_ID);	
			objectiveRollupProcess(current_index,parent_itemIndex,parent_ID,rollupRulesListcollection);		
			activityProgressRollupProcess(current_index,parent_itemIndex,parent_ID,rollupRulesListcollection);
			current_index=parent_itemIndex;
		}
		
	}
}


// Heroin 2004.10.28
function preRollup(temp_index){
	/*  socool 2004.12.10
	�ѩ�preRollup�᪺�v�T�u�Osatisfied or completed,�ҥH�Q��preCondition��condition�ױ��@�ǰ�preRollup������
	(conditon�Y���Osatisfied or completed,��chiled�h���ΰ�preRollup)  */
	if(Number(temp_index)>0 ){
		
		var rollupFlag = true;
		
		var rollupFlag = false;
		for(var x=0;x<parent.s_catalog.pathtree.preConditionRuleList.length;x++){
			if(parent.s_catalog.pathtree.preConditionRuleList[x].itemIndex==temp_index){
					
				var condition = parent.s_catalog.pathtree.preConditionRuleList[x].condition;	
				if(condition.toLowerCase( ).search("satisfied")!= -1 || condition.toLowerCase( ).search("completed")!= -1){
	
					rollupFlag = true;
				}
			}
		}
		
		if(rollupFlag){
			if(parent.s_catalog.pathtree.tocList[Number(temp_index)].itemType=="folder"){
		
				for(var tempi=Number(temp_index)+1;tempi<parent.s_catalog.pathtree.tocList.length;tempi++){
				
					var parentIndex;
					if(tempi>0 && tempi!=""){
						parentIndex=Number(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempi].parentID));
						if(Number(parentIndex)>= Number(temp_index)){
								if(parent.s_catalog.pathtree.primaryObjectiveList[tempi].p_mapInfoList.length>0){
									for(var z=0;z<parent.s_catalog.pathtree.primaryObjectiveList[tempi].p_mapInfoList.length;z++){
										var readSatisfiedStatus = parent.s_catalog.pathtree.primaryObjectiveList[tempi].p_mapInfoList[z].readSatisfiedStatus;
										var readNormalizedMeasure = parent.s_catalog.pathtree.primaryObjectiveList[tempi].p_mapInfoList[z].readNormalizedMeasure;
										if((readSatisfiedStatus.toString()=="true")||(readNormalizedMeasure.toString()=="true")){
											// alert("item : " + tempi + "will prerollup");
											overallRollupProcess(tempi);
										}
		
									} 
									
								}	
							
						}else{
							break;
						}
					}
				}

			}else{ // not a folder
				if(parent.s_catalog.pathtree.primaryObjectiveList[temp_index].p_mapInfoList.length>0){
					for(var z=0;z<parent.s_catalog.pathtree.primaryObjectiveList[temp_index].p_mapInfoList.length;z++){
						var readSatisfiedStatus = parent.s_catalog.pathtree.primaryObjectiveList[temp_index].p_mapInfoList[z].readSatisfiedStatus;
						var readNormalizedMeasure = parent.s_catalog.pathtree.primaryObjectiveList[temp_index].p_mapInfoList[z].readNormalizedMeasure;
							if((readSatisfiedStatus.toString()=="true")||(readNormalizedMeasure.toString()=="true")){
								// alert("item : " + temp_index + "will prerollup");
								overallRollupProcess(temp_index);
							}
		
					} 
				
				}	
			}
		}
		
	}// end for temp_index > 0

}

// Heroin-2003.11.19 get	rollup rules list
function findRollupRules(parent_itemIndex){
	var rollupRules_collection=new Array();
	var j=0;
	for(var	i=0;i<parent.s_catalog.pathtree.rollupRuleList.length;i++){// �����ثeSCO��Parent�]�t��rollup Rules
		if(parent.s_catalog.pathtree.rollupRuleList[i].itemIndex==parent_itemIndex){
			rollupRules_collection[j]=i;
			rollupRules_flag="true";
			// alert(parent_itemIndex+" condition="+parent.s_catalog.pathtree.rollupRuleList[i].condition+"	action="+parent.s_catalog.pathtree.rollupRuleList[i].action);
			j++;
		}
	}
	return rollupRules_collection;
}


// Objective Measure Rollup - 2003.10.21	Heroin
function measureRollupProcess(current_Index,parent_itemIndex,parent_ID){
	var totalMeasure=0;
	var totalWieght=0;
	var objectiveNormalizedMeasure=0;
		
	for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){  
		if(parent.s_catalog.pathtree.tocList[i].parentID==parent_ID){ 
			if(parent.s_catalog.pathtree.deliveryControlsList[i].tracked.toString()=="true" && parent.s_catalog.pathtree.rollupRulesList[i].objectiveMeasureWeight>=0){
					if(parent.s_catalog.pathtree.rollupRulesList[i].rollupObjectiveSatisfied.toString()=="true"){
						if(parent.s_catalog.pathtree.trackingInfoList[i].objectiveMeasureStatus.toString()=="true"){
							totalMeasure=totalMeasure+Number(parent.s_catalog.pathtree.trackingInfoList[i].objectiveNormalizedMeasure)*Number(parent.s_catalog.pathtree.rollupRulesList[i].objectiveMeasureWeight);
							totalWieght=totalWieght+Number(parent.s_catalog.pathtree.rollupRulesList[i].objectiveMeasureWeight);
						}
						else{
							totalMeasure=totalMeasure;
							totalWieght=totalWieght+Number(parent.s_catalog.pathtree.rollupRulesList[i].objectiveMeasureWeight);	
						}
					}
				// }
			}
			else{
			
			}
		}
	}// end for	
	// alert("totalWieght="+totalWieght+" totalMeasure="+totalMeasure );
	// �p��Measure
	if(totalMeasure>0){
		objectiveNormalizedMeasure=totalMeasure/totalWieght;
	}
	else{
		objectiveNormalizedMeasure=0;
	}
	// alert("objectiveNormalizedMeasure="+objectiveNormalizedMeasure);
	
	// Rollup Measure
	if(objectiveNormalizedMeasure==0){
		parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveMeasureStatus=false;				
	}
	if(objectiveNormalizedMeasure>0){
		parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveMeasureStatus=true;
		parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveNormalizedMeasure=objectiveNormalizedMeasure;
	}
	
	
	// rollup target	objective!!
	// Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 
	if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].existflag){
		if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList.length>0){
			for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList.length;p++){
				
				if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].targetObjectiveID!="" &&	parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].writeNormalizedMeasure){
					for(var	k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
						if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].targetObjectiveID){
							parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveMeasureStatus=true;
							parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveNormalizedMeasure=objectiveNormalizedMeasure;
							break;
						}
					}
				}

			}
		}
	}
}

// Rollup Objective Satisfied Satus "By Measure"	-Heroin
function objectiveRollupUsingMeasureProcess(parent_ID,parent_itemIndex){
	// alert(parent_itemIndex+"  in objectiveRollupUsingMeasureProcess");
	if(parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveMeasureStatus){
		
		// Henry	2004.05.26
		var tempminNormalizedMeasure;
		if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].minNormalizedMeasure==""){
			tempminNormalizedMeasure = 1.0;
		}else{
			tempminNormalizedMeasure = parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].minNormalizedMeasure;
		}
		// Heroin 2004.08.04
		// alert(parent_itemIndex+"  measureSatisfactionIfActive="+parent.s_catalog.pathtree.rollupConsiderationsList[parent_itemIndex].measureSatisfactionIfActive+"    activityisActive="+parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityisActive);
		if((parent.s_catalog.pathtree.rollupConsiderationsList[parent_itemIndex].measureSatisfactionIfActive.toString()=="true" && parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityisActive.toString()=="true") || parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityisActive.toString()=="false"){
			if(parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveNormalizedMeasure>=tempminNormalizedMeasure){
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=true;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus=true;
				// Heroin-2003.11.18
				// �ʺA���CA status�Ϥ�-----
				var itemIndex=parent_itemIndex;
				var parentIdnex=itemIndex;
				for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
					if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[itemIndex].parentID){
						parentIndex=i;
						break;
					}
				}
				
				if(parent.s_catalog.pathtree.tocList[itemIndex].isvisible=="true"){
					parent.tocstatus.statusObj.changetocStatus(itemIndex,"passed","folder");
				}
				
			}
			else{
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=true;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus=false;
				
				if(parent.s_catalog.pathtree.tocList[parent_itemIndex].isvisible=="true"){
					parent.tocstatus.statusObj.changetocStatus(parent_itemIndex,"failed","folder");
				}
				
			}
		}
	}
	else{
		parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=false;	
	}
}



// Rollup Objective Satisfied Status - Heroin
function objectiveRollupProcess(current_Index,parent_itemIndex,parent_ID,rollupRulesListcollection){
	// alert("objectiveRollupProcess  "+ current_Index);
	// satisfied by measure process
	// alert(parent_itemIndex+"  satisfiedByMeasure="+parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].satisfiedByMeasure);
	if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].satisfiedByMeasure){
		// alert("measure");
		objectiveRollupUsingMeasureProcess(parent_ID,parent_itemIndex);
	}
			
	// satisfied by rule process
	else{
		// alert("status");
		// action = satisfied or	notsatisfied
		var actionType="satisfied";
		
		var rollup_satisfied=false;
		var rollup_notsatisfied=false;
		for(var	i=0;i<rollupRulesListcollection.length;i++){
			var j=rollupRulesListcollection[i];
			if(parent.s_catalog.pathtree.rollupRuleList[j].action=="satisfied"){
				rollup_satisfied=true;
				var rollup_satisfied_no=j;
			}
			else if(parent.s_catalog.pathtree.rollupRuleList[j].action=="notsatisfied"){
				rollup_notsatisfied=true;
				var rollup_notsatisfied_no=j;
			}
		}
		if(rollup_satisfied==true){
			var ruleCheckStatus=rollupRuleCheckSubprocess(current_Index,parent_itemIndex,parent_ID,actionType,rollup_satisfied_no);
			if(ruleCheckStatus=="true"){
				
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=true;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus=true;	
	
				// Heroin-2003.11.18
				// �ʺA���CA status�Ϥ�------------
				var itemIndex=parent_itemIndex;
				var parentIdnex=itemIndex;
				for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
					if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[itemIndex].parentID){
						parentIndex=i;
						break;
					}
				}
				
				if(parent.s_catalog.pathtree.tocList[itemIndex].isvisible=="true"){
					parent.tocstatus.statusObj.changetocStatus(itemIndex,"passed","folder");
				}	
			}
			else if(ruleCheckStatus=="false"){
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=true;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus=false;				
				// Heroin-2003.11.18
				// �ʺA���CA status�Ϥ�-----

				parent.tocstatus.statusObj.changetocStatus(parent_itemIndex,"failed","folder");
				
			}
			else if(ruleCheckStatus=="unknown"){
				// parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptProgressStatus=false;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=false;
			}
			
		}
		
		// not satidfied	added by Heroin	-2003.11.10 SCORM1.3 WD1
		if(rollup_notsatisfied==true){
			actionType="notsatisfied";
			ruleCheckStatus=rollupRuleCheckSubprocess(current_Index,parent_itemIndex,parent_ID,actionType,rollup_notsatisfied_no);
			
			if(ruleCheckStatus=="true"){
				
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus=true;
				parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus=false;	
					
			}
		
		}
	}
	// alert("status="+parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus);
	
	// rollup to target objective!!
	// Vega 2004.10.14 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			
	if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].existflag){
		if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList.length>0){
			for(var p=0;p<parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList.length;p++){

				if(parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].targetObjectiveID!="" &&	parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].writeSatisfiedStatus){
					if(parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveProgressStatus==true){
						for(var	k=0;k<parent.s_catalog.pathtree.sharedObjectiveList.length;k++){
							if(parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveID==parent.s_catalog.pathtree.primaryObjectiveList[parent_itemIndex].p_mapInfoList[p].targetObjectiveID){					
								parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveProgressStatus=true;
								parent.s_catalog.pathtree.sharedObjectiveList[k].objectiveSatisfiedStatus=parent.s_catalog.pathtree.trackingInfoList[parent_itemIndex].objectiveSatisfiedStatus;
								break;
							}		
						}
					}
				}

			}
		}
	}
}


// check	Rollup Condition - Heroin 
function rollupRuleCheckSubprocess(current_Index,parent_itemIndex,parent_ID,actionType,rollup_no){
	// alert(parent_itemIndex+" in rollupRuleCheckSubprocess	 "+actionType);
	var j=0;
	var rollupRules_collection=new Array();
	var rollupRules_flag="none";

	var ruleIndex=rollup_no;
	var childrenBag=new Array(); 
	var childrenCount=0;
	var childIndex=0;
	var child_status_unknown=false;
	var child_status_unknown_count=0;
				
	var k=0;
	// for each child of the	activity
	for(var	i=parent_itemIndex+1;i<parent.s_catalog.pathtree.tocList.length;i++){	
		
		if(parent.s_catalog.pathtree.tocList[i].parentID==parent.s_catalog.pathtree.tocList[parent_itemIndex].parentID){
			break;
		}
		
		if(parent.s_catalog.pathtree.tocList[i].parentID==parent_ID){ /* ���p�ĭ�  */ 
				var rollupConsiderationResult = "false";
				if(actionType=="satisfied"){
					rollupConsiderationResult = checkRollupConsiderations(i,parent.s_catalog.pathtree.rollupConsiderationsList[i].requiredForSatisfied);					
				}
				if(actionType=="nosatisfied"){
					rollupConsiderationResult = checkRollupConsiderations(i,parent.s_catalog.pathtree.rollupConsiderationsList[i].requiredForNotSatisfied);					
				}
				if(actionType=="completed"){
					rollupConsiderationResult = checkRollupConsiderations(i,parent.s_catalog.pathtree.rollupConsiderationsList[i].requiredForCompleted);					
				}
				if(actionType=="incomplete"){
					rollupConsiderationResult = checkRollupConsiderations(i,parent.s_catalog.pathtree.rollupConsiderationsList[i].requiredForIncomplete);					
				}
				if(actionType=="completed"){
					// alert("i="+i+"  actionType="+actionType+"   rollupConsiderationResult="+rollupConsiderationResult);
				}
				if(rollupConsiderationResult=="true"){
					if((((actionType=="satisfied" || actionType=="notsatisfied") &&	parent.s_catalog.pathtree.rollupRulesList[i].rollupObjectiveSatisfied.toString()=="true")) || (((actionType=="completed" || actionType=="incomplete") && parent.s_catalog.pathtree.rollupRulesList[i].rollupProgressCompletion.toString()=="true"))){
						childIndex=i;																
						var evaluateResult=false;						
						evaluateResult=evaluateRollupConditionSubprocess(childIndex,parent_itemIndex,parent_ID,ruleIndex);
						if(actionType=="completed"){
							// alert(i+"  actionType="+actionType+"  evaluateResult="+evaluateResult);
						}
						
						if(evaluateResult==true){
							// Add a	True in	children bag
							childrenBag[k]=true;
							childrenCount++;
						}
						else if(evaluateResult=="unknown"){
							childrenBag[k]="unknown";
							child_status_unknown=true;
							child_status_unknown_count++;
						}else{
							// Add a	False to children bag
							childrenBag[k]=false;
						}
									
						k++;
					}
					
				}
				
					
			// *}		
		}
	}// end	for each child

	var rollup_result="false";
	// alert("childrenCount="+childrenCount+"  k="+k);
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].childActivitySet=="all"){
		
		
		if(k>0){
			if(childrenCount==k){
				rollup_result="true";
			}
			else if(child_status_unknown==true){
				if(child_status_unknown_count==k){
					rollup_result="unknown";
				}
				else{
					rollup_result="false";						
				}
			}
		}
		else{
			rollup_result="unknown";
		}
		
	}
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].childActivitySet=="any"){
		if(childrenCount>0){
				rollup_result="true";
		}
	}
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].childActivitySet=="none"){
		// none: �p�G���A����unknown,�ä����none
		// Modified by Heroin 2004.06.25
		if(Number(childrenCount)==0 && child_status_unknown==false){
			// alert("none");
			rollup_result="true";
		}
	}
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].childActivitySet=="atLeastCount"){			
		if(childrenCount>=Number(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].minimumCount)){
			rollup_result="true";
		}
	}
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].childActivitySet=="atLeastPercent"){
		var percent=Number(childrenCount)/Number(k);
		if(Number(percent) >= Number(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].minimumPercent)){
			rollup_result="true";
		}
	}
			
	var action_result=rollup_result;
	if(child_status_unknown=="true"){
		action_result="unknown";
		return action_result;
	}
	else{
		action_result=rollup_result;
		
	}
	// alert("action_result="+action_result);
	return action_result;
	
}


// Rollup Activity Completion Status - Heroin
function activityProgressRollupProcess(current_Index,parent_itemIndex,parent_ID,rollupRulesListcollection){

	var rollup_completed=false;
	var rollup_incomplete=false;
	for(var	i=0;i<rollupRulesListcollection.length;i++){
		var j=rollupRulesListcollection[i];
		if(parent.s_catalog.pathtree.rollupRuleList[j].action=="completed"){
			rollup_completed=true;
			var rollup_completed_no=j;
		}
		else if(parent.s_catalog.pathtree.rollupRuleList[j].action=="incomplete"){
			rollup_incomplete=true;
			var rollup_incomplete_no=j;
		}
	}

	// alert("rollup_completed="+rollup_completed);
	if(rollup_completed.toString()=="true"){			
		// objectiveRollupUsingRulesProcess(parent_itemIndex,current_Index,ruleIndex);	
		// action = completed or	incomplete
		var actionType="completed";
		var ruleCheckStatus=rollupRuleCheckSubprocess(current_Index,parent_itemIndex,parent_ID,actionType,rollup_completed_no);
		// alert("ruleCheckStatus="+ruleCheckStatus+" parent_itemIndex="+parent_itemIndex+" current_Index="+current_Index);
		if(ruleCheckStatus=="true"){
	
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptProgressStatus=true;
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptCompletionStatus=true;		
	
			// Heroin-2003.11.18		
			var itemIndex=parent_itemIndex;
			var parentIdnex=itemIndex;
			for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
				if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[itemIndex].parentID){
					parentIndex=i;
					break;
				}
			}
			
			if(parent.s_catalog.pathtree.tocList[itemIndex].isvisible=="true"){
				parent.tocstatus.statusObj.changetocStatus(itemIndex,"completed","folder");
			}
		}
		else if(ruleCheckStatus=="false"){
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptProgressStatus=true;
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptCompletionStatus=false;	
			// Heroin-2003.11.14
			// if parent is invisible then 
			var parentIndex=index;
			if(parentIndex!=0){
				for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
					if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[index].parentID){
						parentIndex=i;
						break;
					}
				}
			}
			
		}
		else if(ruleCheckStatus=="unknown"){
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptProgressStatus=false;
		}
	
	}
	// not satidfied	added by Heroin	-2003.11.10 SCORM1.3 WD1
	if(rollup_incomplete.toString()=="true"){
		actionType="incomplete";
		ruleCheckStatus=rollupRuleCheckSubprocess(current_Index,parent_itemIndex,parent_ID,actionType,rollup_incomplete_no);
		if(ruleCheckStatus=="true"){
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptProgressStatus=true;
			parent.s_catalog.pathtree.activityStatusList[parent_itemIndex].activityAttemptCompletionStatus=false;				
		}
	}

}


// Heroin 2004.04.09
// Evaluate children's Condition	Result - Heroin
function evaluateRollupConditionSubprocess(childIndex,parent_itemIndex,parent_ID,ruleIndex){	
	if(parent.s_catalog.pathtree.rollupRuleList[ruleIndex].multiflag){ // ��conditionCombination
		
		var conditionString=parent.s_catalog.pathtree.rollupRuleList[ruleIndex].condition;
		var cc=conditionString.split("*");
		var operatorString=parent.s_catalog.pathtree.rollupRuleList[ruleIndex].operator;
		var oo=operatorString.split("*");			
		var conditionSatisfiedCount=0;
		var status_unknown=0;
		

		// for each conditoin		
		for(var	i=0;i<cc.length;i++){	
			if(cc[i]=="satisfied"){
				if(oo[i]=="noOp"){
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==true){
						if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveSatisfiedStatus){
							conditionSatisfiedCount++;
						}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			
					}else if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatus=findTargetStatus(childIndex);							
						if(shardObjectiveStatus){
							conditionSatisfiedCount++;
						}
					}

					else{
						status_unknown++;
					}
				}
				else if(oo[i]=="not"){
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==true){
						if(!parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveSatisfiedStatus){
							conditionSatisfiedCount++;
						}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					}else if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatus=findTargetStatus(childIndex);	
						if(!shardObjectiveStatus){
							conditionSatisfiedCount++;
						}
					}

					else{
						status_unknown++;
					}
					
				}
			}
			if(cc[i]=="objectiveStatusKnown"){
				
				if(oo[i]=="noOp"){
					var statusKnown="false";
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==true){
						statusKnown="true";
						// conditionSatisfiedCount++;						
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetStatusKnown(childIndex);							
						if(shardObjectiveStatusKnown.toString()=="true"){				
							statusKnown="true";
						}				
					}					

					if(statusKnown=="true"){
						conditionSatisfiedCount++;
					}
				}
				else if(oo[i]=="not"){
					var statusKnown="true";
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==false){
						// conditionSatisfiedCount++;
						statusKnown="false";
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetStatusKnown(childIndex);	
						if(!shardObjectiveStatus){
							// conditionSatisfiedCount++;
							statusKnown="false";
						}
					}					
					if(statusKnown=="false"){
						conditionSatisfiedCount++;
					}
				}
			}
			if(cc[i]=="objectiveMeasureKnown"){
				if(oo[i]=="noOp"){
					var statusKnown="false";
					// alert(childIndex+"  measure Known="+parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveMeasureStatus);
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveMeasureStatus.toString()=="true"){
						statusKnown="true";				
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetMeasureKnown(childIndex);							
						if(shardObjectiveStatusKnown.toString()=="true"){
							statusKnown="true";
						}						
					}					
					if(statusKnown=="true"){
						conditionSatisfiedCount++;
					}
				}
				else if(oo[i]=="not"){
					var statusKnown="true";
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveMeasureStatus.toString()=="false"){					
						statusKnown="false";
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetMeasureKnown(childIndex);	
						if(!shardObjectiveStatus){
							statusKnown="false";
						}
					}					
					if(statusKnown=="false"){
						conditionSatisfiedCount++;
					}
				}
				
			}

			if(cc[i]=="completed"){
				// alert("cc[i]="+cc[i]+" oo[i]="+oo[i]);
				if(oo[i]=="noOp"){
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){		
						if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCompletionStatus==true){
							conditionSatisfiedCount++;
						}
					}
					else{
						status_unknown++;
					}
				}
				else if(oo[i]=="not"){
					// alert("progress="+parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus+" completion="+parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCompletionStatus);
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){						
						if(!parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCompletionStatus==true){
							conditionSatisfiedCount++;
						}
					}
					else{
						status_unknown++;
					}
				}
			}
			
			if(cc[i]=="activityProgressKnown"){
				if(oo[i]=="noOp"){
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
						conditionSatisfiedCount++;
					}
				}
				else if(oo[i]=="not"){
					if(!parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
						conditionSatisfiedCount++;
					}
				}
			}
			if(cc[i]=="attempted"){
				if(oo[i]=="noOp"){
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount>0){
						conditionSatisfiedCount++;
						// alert("attempted!!");
					}
				}
				else if(oo[i]=="not"){
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount==0){
						conditionSatisfiedCount++;
					}
				}
			}
			
			if(cc[i]=="attemptLimitExceeded"){
				if(oo[i]=="noOp"){
					if(Number(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount)>=Number(parent.s_catalog.pathtree.limitConditionsList[childIndex].attemptLimit)){
						conditionSatisfiedCount++;
					}
				}else if(oo[i]=="not"){
					if(Number(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount)<Number(parent.s_catalog.pathtree.limitConditionsList[childIndex].attemptLimit)){
						conditionSatisfiedCount++;
					}
				}
			}

			if(cc[i]=="alwas"){
				if(oo[i]=="noOp"){
					conditionSatisfiedCount++;
				}
			}
			if(cc[i]=="never"){
				if(oo[i]=="noOp"){
					conditionSatisfiedCount++;
				}
			}	
			// alert(parent_itemIndex+"   "+childIndex+ "  conditionSatisfiedCount="+conditionSatisfiedCount+" cc[i]="+cc[i]);	
			
		}// end	for

		// Heroin ���P�_unkown2003.11.06		
		var conditionEvaluatResult=false;
		// alert("parent.s_catalog.pathtree.rollupRuleList[ruleIndex].conditionCombination="+parent.s_catalog.pathtree.rollupRuleList[ruleIndex].conditionCombination);
		if (parent.s_catalog.pathtree.rollupRuleList[ruleIndex].conditionCombination=="all"){
			
			if(conditionSatisfiedCount==cc.length){
				conditionEvaluatResult=true;
			}else{
				conditionEvaluatResult=false;
			}
			// alert("conditionEvaluatResult="+conditionEvaluatResult);
			return conditionEvaluatResult;
	
		}
		
		if (parent.s_catalog.pathtree.rollupRuleList[ruleIndex].conditionCombination=="any"){
			if(conditionSatisfiedCount>0){
				conditionEvaluatResult=true;			
			}else{
				conditionEvaluatResult=false;
			}
			// alert("conditionSatisfiedCount="+conditionSatisfiedCount+"  conditionEvaluatResult="+conditionEvaluatResult);
			return conditionEvaluatResult;
		}
	}
	
	else{ // no conditionCombination!!
			
		var conditionString=parent.s_catalog.pathtree.rollupRuleList[ruleIndex].condition;
		var operatorString=parent.s_catalog.pathtree.rollupRuleList[ruleIndex].operator;
		var condition_result=false;
		var status_unknown=false;

		if(conditionString=="satisfied"){
			// alert(childIndex+" status="+parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus+"	"+parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveSatisfiedStatus);
			if(operatorString=="noOp"){
				if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus){
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveSatisfiedStatus){						
						condition_result=true;
					}
				// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			
				}else if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
					var shardObjectiveStatus=findTargetStatus(childIndex);							
					if(shardObjectiveStatus){
						condition_result=true;
					}
				}
				else{
					status_unknown=true;
				}		
			}else if(operatorString=="not"){
				if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus){
					if(!parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveSatisfiedStatus){						
						condition_result=true;
					}
				// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 			
				}else if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
					var shardObjectiveStatus=findTargetStatus(childIndex);							
					if(!shardObjectiveStatus){
						condition_result=true;
					}
				}				
				else{
					status_unknown=true;
				}
			}
		}
		
		if(conditionString=="objectiveStatusKnown"){
			
			if(operatorString=="noOp"){
				var statusKnown="false";
				if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==true){
					statusKnown="true";
					// conditionSatisfiedCount++;						
				}
				// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
				if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
					var shardObjectiveStatusKnown=findTargetStatusKnown(childIndex);							
					if(shardObjectiveStatusKnown.toString()=="true"){
						statusKnown="true";
					}
				}
				if(statusKnown=="true"){
					condition_result=true;
				}					
				
			}else if(operatorString=="not"){
					var statusKnown="true";
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveProgressStatus==false){
						// conditionSatisfiedCount++;
						statusKnown="false";
						
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetStatusKnown(childIndex);	
						if(!shardObjectiveStatus){						
							statusKnown="false";
						}
					}
					if(statusKnown=="false"){
						condition_result=true;
					}
			}
		}
		if(conditionString=="objectiveMeasureKnown"){
			if(operatorString=="noOp"){
				var statusKnown="false";
				if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveMeasureStatus==true){
					statusKnown="true";
					// conditionSatisfiedCount++;						
				}
				// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
				if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
					var shardObjectiveStatusKnown=findTargetMeasureKnown(childIndex);							
					if(shardObjectiveStatusKnown.toString()=="true"){
						statusKnown="true";
					}
				}
				if(statusKnown=="true"){
					condition_result=true;
				}					
				
			}else if(operatorString=="not"){
					var statusKnown="true";
					if(parent.s_catalog.pathtree.trackingInfoList[childIndex].objectiveMeasureStatus==false){
						// conditionSatisfiedCount++;
						statusKnown="false";
						
					}
					// Vega 2004.10.18 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 		
					if(parent.functions.enfunctions.findTargetObj(childIndex,"primary","readSatisfied")>-1){
						var shardObjectiveStatusKnown=findTargetMeasureKnown(childIndex);	
						if(!shardObjectiveStatus){
							statusKnown="false";
						}
					}
					if(statusKnown=="false"){
						condition_result=true;
					}
			}
		}

		if(conditionString=="completed"){
			if(operatorString=="noOp"){
				if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
					if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCompletionStatus==true){
						condition_result=true;
					}
				}else{
					status_unknown=true;
				}
			}else if(operatorString=="not"){
				if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
					if(!parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCompletionStatus==true){
						condition_result=true;
					}
				}else{
					status_unknown=true;
				}
			}
		}
		
		if(conditionString=="activityProgressKnown"){
			if(operatorString=="noOp"){
				if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
					condition_result=true;
				}	
			}else if(operatorString=="not"){
				if(!parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptProgressStatus==true){
					condition_result=true;
				}			
			}
		}
		if(conditionString=="attempted"){
			if(operatorString=="noOp"){
				if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount>0){
					condition_result=true;
				}
			}
			else if(operatorString=="not"){
				if(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount==0){
					condition_result=true;
				}
			}
		}
			
		if(conditionString=="attemptLimitExceeded"){
			// alert("attemptLimitExceeded	activityAttemptCount="+parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount+"	 attemptLimit="+parent.s_catalog.pathtree.limitConditionsList[childIndex].attemptLimit);
			if(operatorString=="noOp"){
				if(Number(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount)>=Number(parent.s_catalog.pathtree.limitConditionsList[childIndex].attemptLimit)){
					condition_result=true;
				}
			}
			else if(operatorString=="not"){
				if(Number(parent.s_catalog.pathtree.activityStatusList[childIndex].activityAttemptCount)<Number(parent.s_catalog.pathtree.limitConditionsList[childIndex].attemptLimit)){
					condition_result=true;
				}
			}
		}
		if(conditionString=="alwas"){
			if(operatorString=="noOp"){
				condition_result=true;
			}
		}
		if(conditionString=="never"){
			if(operatorString=="noOp"){
				condition_result=false;
			}else if(operatorString=="not"){
				condition_result=true;
			}
		}
		if(status_unknown==true){
			condition_result="unknown";
			return condition_result;
		}
		else{
			return condition_result;
		}
	} // end	no combination
}


// Heroin 2004.07.29
function checkRollupConsiderations(temp_index, checkItem){
	
	var checkResult = "false";
	// always
	if(checkItem=="always"){
		checkResult = "true";
	}

	// ifNotSuspended
	if(checkItem=="ifNotSuspended"){
		if(parent.s_catalog.pathtree.activityStatusList[temp_index].activityisSuspended.toString()=="false"){
			checkResult = "true";
		}
	}
	// ifAttempted
	if(checkItem=="ifAttempted"){
		if(parent.s_catalog.pathtree.activityStatusList[temp_index].activityAttemptCount > 0){
			checkResult = "true";
		}
	}
	// ifNotSkipped
		
	if(checkItem=="ifNotSkipped"){
		// alert(temp_index+"  isSkip="+parent.s_catalog.pathtree.actionStatusList[temp_index].isSkip.toString());
		
		var needSkip = CheckDisplaySkip(temp_index);
		// alert("temp_index="+temp_index+"  needSkip="+needSkip);
		if(needSkip){
			parent.s_catalog.pathtree.actionStatusList[temp_index].isSkip="true";
		}else{
			parent.s_catalog.pathtree.actionStatusList[temp_index].isSkip="false";
		}
		
		if(parent.s_catalog.pathtree.actionStatusList[temp_index].isSkip.toString()=="false" ){
			checkResult = "true";
		}
	}
	
	return checkResult;
}



// added by Heroin 2004.09.24
function checkPreConditionSkip(temp_index){
	// alert(temp_index+"  checkPreConditionSkip");
	var preConditionRuleFlag="false";
	var end="false";
	var ruleIndex;
	
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==temp_index && parent.s_catalog.pathtree.preConditionRuleList[i].action == "skip"){
			var preConditionSkipFlag="true";
			ruleIndex = i;
		}
	}
	if(preConditionSkipFlag=="true"){ // ��preConditionRule
	
		preConditionRuleFlow();	// �}�lparsePreConditionRule 
		parent.check.checkrules.preConditionDisabledAndHiddendfromchoice(temp_index,ruleIndex);
	}
	else{ // �S��preConditionRule
		
	}
}


// added	by Heroin 2003.12.31
function saveAncestors(itemIndex){
	var itemAncestors=findAncestors(itemIndex);
	
	// from current to common ancestor
	for(var	i=0;i<itemAncestors.length-1;i++){
		saveAggregationInfoToDB(itemAncestors[i]);
	}	
}

// added	by Heroin 2003.12.31
function clearSuspended(itemIndex){

	var itemAncestors=findAncestors(itemIndex);
	
	// from current to common ancestor
	for(var	i=0;i<itemAncestors.length;i++){
		parent.s_catalog.pathtree.activityStatusList[i].activityisSuspended=false;
	}	
	
}


// added	by Heroin 2003-10-31
// update rollup	Info to	DB
function saveAggregationInfoToDB(current_index){
	// alert("saveToDB  "+parent.s_catalog.pathtree.tocList[current_index].id);
	
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	// xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes[0]);
	var rootElement	= xmldoc.documentElement;

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",parent.s_catalog.pathtree.tocList[current_index].id);
	

	if(seqRequest=="retryAll"){
		
		rootElement.setAttribute("Message_Type","Start");
		rootElement.setAttribute("Scorm_Type","Start");
		var cmi_core_exit_value = xmldoc.createElement("cmi_exit_value");
        if( cmi_core_exit_value.textContent == undefined )
            cmi_core_exit_value.text = "start";
        else
		    cmi_core_exit_value.textContent = "start";
		rootElement.appendChild(cmi_core_exit_value);
		
		
		
	}else{
		rootElement.setAttribute("Message_Type","ActivityStatus");
	

	
		if(parent.API.GetSCO_ID() != parent.s_catalog.pathtree.tocList[current_index].id){
			rootElement.setAttribute("Scorm_Type","aggregation");
		
			var cmi_core_session_time_Element = xmldoc.createElement("cmi_core_session_time");
            if( cmi_core_session_time_Element.textContent == undefined )
                cmi_core_session_time_Element.text = parent.functions.enfunctions.convertTotalSeconds(parent.s_catalog.pathtree.SCOTimerList[current_index].sec);
            else
			    cmi_core_session_time_Element.textContent = parent.functions.enfunctions.convertTotalSeconds(parent.s_catalog.pathtree.SCOTimerList[current_index].sec);
			rootElement.appendChild(cmi_core_session_time_Element);
	
			// added	by Heroin 2003.11.02
			var cmi_core_score_normalized_Element =	xmldoc.createElement("cmi_core_score_normalized");		
			if(parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveMeasureStatus){
                if( cmi_core_score_normalized_Element.textContent == undefined )
                    cmi_core_score_normalized_Element.text = parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveNormalizedMeasure;
                else
                    cmi_core_score_normalized_Element.textContent = parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveNormalizedMeasure;
			}
			else{
                if( cmi_core_score_normalized_Element.textContent == undefined )
                    cmi_core_score_normalized_Element.text="";
                else
                    cmi_core_score_normalized_Element.textContent="";
			}			
			rootElement.appendChild(cmi_core_score_normalized_Element);
			
			// added	by Heroin 2003.10.27
			var isSuspended_Element	= xmldoc.createElement("isSuspended");
            if( isSuspended_Element.textContent == undefined )
                isSuspended_Element.text = "" + parent.s_catalog.pathtree.activityStatusList[current_index].activityisSuspended;
            else
			    isSuspended_Element.textContent = "" + parent.s_catalog.pathtree.activityStatusList[current_index].activityisSuspended;
			rootElement.appendChild(isSuspended_Element);
			
			var cmi_core_success_status_Element = xmldoc.createElement("cmi_core_success_status");		    
			    if(parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveProgressStatus==true){
				if(parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveSatisfiedStatus==true){
                    if( cmi_core_success_status_Element.textContent == undefined )
                        cmi_core_success_status_Element.text = "passed";	
                    else
                        cmi_core_success_status_Element.textContent = "passed";		    
				}
				else if(parent.s_catalog.pathtree.trackingInfoList[current_index].objectiveSatisfiedStatus==false){
                    if( cmi_core_success_status_Element.textContent == undefined )
                        cmi_core_success_status_Element.text = "failed";
                    else
                        cmi_core_success_status_Element.textContent = "failed";
				}
			    }
			    else{
                    if( cmi_core_success_status_Element.textContent == undefined )
                        cmi_core_success_status_Element.text = "unknown";
                    else
                        cmi_core_success_status_Element.textContent = "unknown";
			    }
			rootElement.appendChild(cmi_core_success_status_Element);
			
			var cmi_core_completion_status_Element = xmldoc.createElement("cmi_core_completion_status");
			    if(parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptProgressStatus==true){
				if(parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptCompletionStatus==true){
                    if( cmi_core_completion_status_Element.textContent == undefined )
                        cmi_core_completion_status_Element.text	= "completed";		 
                    else
                        cmi_core_completion_status_Element.textContent	= "completed";		    
				}
				else if(parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptCompletionStatus==false){
                    if( cmi_core_completion_status_Element.textContent == undefined )
                        cmi_core_completion_status_Element.text	= "incomplete";
                    else
                        cmi_core_completion_status_Element.textContent	= "incomplete";
				}
			     }
			     else{
                    if( cmi_core_completion_status_Element.textContent == undefined )
                        cmi_core_completion_status_Element.text	= "unknown"; 
                    else
                        cmi_core_completion_status_Element.textContent	= "unknown"; 
			     }
			rootElement.appendChild(cmi_core_completion_status_Element);
			
			
			
			var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");		    
            if( cmi_core_attempt_count_Element.textContent == undefined )
                cmi_core_attempt_count_Element.text = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptCount;		   
            else
                cmi_core_attempt_count_Element.textContent = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptCount;		   
			rootElement.appendChild(cmi_core_attempt_count_Element);
			
			// Heroin-2003.12.08
			var cmi_core_isDisabled_Element	= xmldoc.createElement("cmi_core_isDisabled");	
            if( cmi_core_isDisabled_Element.textContent == undefined )   
                cmi_core_isDisabled_Element.text = parent.s_catalog.pathtree.tocList[current_index].disable;
            else            
                cmi_core_isDisabled_Element.textContent = parent.s_catalog.pathtree.tocList[current_index].disable;
			rootElement.appendChild(cmi_core_isDisabled_Element);
			
			var cmi_core_isHiddenFromChoice_Element	= xmldoc.createElement("cmi_core_isHiddenFromChoice");		
            if( cmi_core_isHiddenFromChoice_Element.textContent == undefined )
                cmi_core_isHiddenFromChoice_Element.text = parent.s_catalog.pathtree.isHiddenFromChoiceList[current_index].value;
            else
                cmi_core_isHiddenFromChoice_Element.textContent = parent.s_catalog.pathtree.isHiddenFromChoiceList[current_index].value;
			rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
	
			
			// Heroin-2003.12.15 LimitCondtion Duration		
			var cmi_core_attempt_absolut_duration_Element =	xmldoc.createElement("cmi_core_attempt_absolut_duration");	
            if( cmi_core_attempt_absolut_duration_Element.textContent == undefined )
                cmi_core_attempt_absolut_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptAbsoluteDuration;
            else
                cmi_core_attempt_absolut_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptAbsoluteDuration;
			rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
			
			var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");	
            if( cmi_core_attempt_experienced_duration_Element.textContent == undefined )
                cmi_core_attempt_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptExperiencedDuration;
            else
                cmi_core_attempt_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[current_index].activityAttemptExperiencedDuration;
			rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
			
			var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");	

            if( cmi_core_activity_absolut_duration_Element.textContent == undefined )
                cmi_core_activity_absolut_duration_Element.text	= parent.s_catalog.pathtree.activityStatusList[current_index].activityAbsoluteDuration;
            else
                cmi_core_activity_absolut_duration_Element.textContent	= parent.s_catalog.pathtree.activityStatusList[current_index].activityAbsoluteDuration;
			rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
			
			var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");
            if( cmi_core_activity_experienced_duration_Element.textContent == undefined )   
                cmi_core_activity_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[current_index].activityExperiencedDuration;
            else            
                cmi_core_activity_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[current_index].activityExperiencedDuration;
			rootElement.appendChild(cmi_core_activity_experienced_duration_Element);
			
		}
	}
	
	var ServerSide = xmlhttp_set;	
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.send(xmldoc.xml);
}


// added	by Heroin 2004.08.27
// save global objective Info to DB
// �N"�Ҧ�" shared objective info �s�JDB ...
function saveGlobalObjectiveInfoToDB(current_index){
	// alert("saveGlobalObjectiveInfoToDB");
	
	
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	// xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi,xmldoc.childNodes[0]);
	var rootElement	= xmldoc.documentElement;

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",parent.s_catalog.pathtree.tocList[current_index].id);
	rootElement.setAttribute("Message_Type","globalObective");
	rootElement.setAttribute("Scorm_Type","globalObective");
	
	// Table: global_objectives


	for(var i=0;i<parent.s_catalog.pathtree.sharedObjectiveList.length;i++){
		var globalToSystem = parent.s_catalog.pathtree.GlobalState.GlobalToSystem;  // �o�̭n�A��...
		var objectiveID = parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveID;
		var objectiveProgressStatus = parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveProgressStatus;
		var objectiveSatisfiedStatus = parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveSatisfiedStatus;
		var objectiveMeasureStatus = parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveMeasureStatus;
		var objectiveNormalizedMeasure = parent.s_catalog.pathtree.sharedObjectiveList[i].objectiveNormalizedMeasure;
		
		
		// �O�o�n��toString()�AsetAttribute..���Mtrue/false�|�۰��ܦ�0.1.-1
		
		var tempblaElement = xmldoc.createElement("global_objective");
		tempblaElement.setAttribute("objectiveID",objectiveID.toString());
		tempblaElement.setAttribute("objectiveProgressStatus",objectiveProgressStatus.toString());
		tempblaElement.setAttribute("objectiveSatisfiedStatus",objectiveSatisfiedStatus.toString());
		tempblaElement.setAttribute("objectiveMeasureStatus",objectiveMeasureStatus.toString());
		tempblaElement.setAttribute("objectiveNormalizedMeasure",objectiveNormalizedMeasure.toString());
		tempblaElement.setAttribute("globalToSystem",globalToSystem.toString());
		
		
		
		
		rootElement.appendChild(tempblaElement);
		
	
	}

	
	
	var ServerSide = xmlhttp_set;	
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.send(xmldoc.xml);
	
	
}


function parsePostConditionRules(postIndex){
	// alert(postIndex+"  parsePostConditionRules");
	var j=0;
	var postConditionRules_collection=new Array();
	var postConditionRules_flag="false";
	var sequencingRequest=false;
	for(var	i=0;i<parent.s_catalog.pathtree.postConditionRuleList.length;i++){// �����ثeSCO�]�t��postConditionRules
		if(parent.s_catalog.pathtree.postConditionRuleList[i].itemIndex==postIndex){
			postConditionRules_collection[j]=i;
			postConditionRules_flag="true";
			j++;
		}
	}
	// socool 2005.08.19 ----
	// Yunghsiao 2005.08.10
	if(parent.s_catalog.pathtree.deliveryControlsList[index].tracked.toString()=="false"){
		postConditionRules_flag = "false";
	}
	// --------
	// alert("postConditionRules_flag="+postConditionRules_flag);
	if(postConditionRules_flag=="false"){
		sequencingRequest=true;
		return sequencingRequest;
		// SequencingRequestProcess("Find",seqRequest);
	}
	else{
		// /HHEERROOIINN	 Heroin-2003.12.31 �Ncheck condition rules function����engine
		var action_collection=new Array();
		action_collection=parent.check.checkrules.postConditionRules(postIndex);	
		var a =	action_collection.length;
		
		// alert("actionCollections.length="+action_collection.length);
				
		// -----------------------------------------------------------------------------------------
		if(a==0){
			// �ˬdparent��postConditionRule
			if(seqRequest=="Continue"){
				if(index==parent.s_catalog.pathtree.tocList.length-1){
					var tmp_index=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[postIndex].parentID);
					var tmp_check="false";
					for(var	i=0;i<parent.s_catalog.pathtree.postConditionRuleList.length;i++){// �����ثeSCO�]�t��postConditionRules
						if(parent.s_catalog.pathtree.postConditionRuleList[i].itemIndex==tmp_index){
							tmp_check="true";
						}
					}
					if(tmp_check=="true"){
						index=tmp_index;
						var sequencingrequest=parsePostConditionRules(index); // parsePostConditionRule
						if(sequencingrequest==true){
							if(seqRequest=="Choice"){
								index=choice_index;
							}			
							SequencingRequestProcess("Find",seqRequest);
						}
					}
					else{
						sequencingRequest=true;
						return sequencingRequest;
					}
				}
				else{
					
					sequencingRequest=true;
					return sequencingRequest;
				}	
			}
			else{
				sequencingRequest=true;
				return sequencingRequest;
			}	
		}
		else{
			for(var	i=0;i<action_collection.length;i++){
				if(action_collection[i]=="exitParent"){
					seqRequest="Continue";
					sequencingRequest=true;
					exitRequest="ExitParent";	
				}
				else if(action_collection[i]=="exitAll"){
					seqRequest="";
					sequencingRequest=false;
					exitRequest="ExitAll";
				}
				else if(action_collection[i]=="retry"){
					seqRequest="Retry";
					sequencingRequest=true;
					return sequencingRequest;
				}
				else if(action_collection[i]=="retryAll"){
					seqRequest="retryAll"
					exitRequest="ExitAll";
					
					sequencingRequest=true;
				}
				else if(action_collection[i]=="continue"){
					seqRequest="Continue";
					sequencingRequest=true;
					return sequencingRequest;
				}
				else if(action_collection[i]=="previous"){
					seqRequest="Previous";
					sequencingRequest=true;
					return sequencingRequest;
				}
			}
		}	
	}
	return sequencingRequest;
}





// Step5:�NCandidate SCO	Deliver
function DeliveryRequesetProcess(){
	// alert(index+" DeliveryRequesetProcess");
	var deliveryFlag = true;
	
	var time1=new Date();
	var t1=time1.getSeconds() +"''"+ time1.getMilliseconds();	
	
	// for each activity in the activity path 
	// check	the activity process -Heroin 2003.11.20	
	var current_index=index;
	
	var ancestorsArray=findAncestors(current_index);
	
	// for each activity in the activity path!
	for(var	i=0;i<ancestorsArray.length;i++){
		parent_itemIndex=ancestorsArray[i];
		deliveryFlag = CheckActivityProcess(parent_itemIndex);
		if(deliveryFlag==false){
			if(parent.s_catalog.pathtree.tocList[Number(parent_itemIndex)].itemType=="folder"){		
				if(seqRequest=="Previous"){
					current_index=parent_itemIndex;
				}else{
					var count_children=countChildren(parent_itemIndex);
					current_index=parent_itemIndex + count_children;
				}
				
			}
			
			
			if(deliveryFlag==false && parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended.toString()=="true"){
				clearSuspended(index);
				CommitActivityStatus();
				saveAncestors(index);
			}
			
			
			break;
		}
		// current_index=parent_itemIndex;
	}
	
	
	
	var time2=new Date();
	var t2=time2.getSeconds() +"''"+ time2.getMilliseconds();	
	
	ContentDeliveryEnvironmentProcess(deliveryFlag,current_index);
}


function ContentDeliveryEnvironmentProcess(deliveryFlag,current_index){
	if(parent.s_catalog.pathtree.activityStatusList[index].activityisActive==true){
			
		return("");
	}
	
	if(deliveryFlag){
		if(queryPermittedcontinue){
			parent.s_catalog.pathtree.queryPermittedObj.c = true;
		}else if(queryPermittedprevious){
			parent.s_catalog.pathtree.queryPermittedObj.p = true;
		}else{
			var j=0;
			var postCondition="false";
			postCondition=TerminateDescendentAttemptsProcess(previousActiveIndex,index);

			if(postCondition=="true"){
				if(seqRequest=="Previous"){
					SequencingRequestProcess("Find",seqRequest);
				}
				if(seqRequest=="Retry"){
					
					SequencingRequestProcess("Find",seqRequest);
					
				}
				
			}else{
		
			var idendifiedAncestors=findAncestors(index);
			
			var nowDate  = "<?=date('Y/m/d')?>";
			var nowTime00 =	"<?=date('H,i,s')?>";
			var nowTime02 =	nowDate.split("/");
			var nowTime03 =	nowTime00.split(",");
			var nowTime04 =	new Date(Number(nowTime02[0]),Number(nowTime02[1])-1,Number(nowTime02[2]),Number(nowTime03[0]),Number(nowTime03[1]),Number(nowTime03[2]));
			var nowTime = nowTime04.getTime();		
		
			
			
			// �Ҧ����������]��active
			// �æb���p��Duration
			for(var	i=0;i<idendifiedAncestors.length;i++){
				j=idendifiedAncestors[i];
				if(parent.s_catalog.pathtree.activityStatusList[j].activityisActive==false){
						if(parent.s_catalog.pathtree.activityStatusList[j].activityisSuspended.toString()=="true"){
							parent.s_catalog.pathtree.activityStatusList[j].activityisSuspended=false;
							
							// �֥[AbsoluteDuration-2003.12.15
							parent.s_catalog.pathtree.activityStatusList[j].activityAbsoluteDuration = parent.functions.enfunctions.countAbsoluteDuration(parent.s_catalog.pathtree.activityStatusList[j].activityAbsoluteDuration,parent.s_catalog.pathtree.activityStatusList[j].lastTime,nowTime);
							parent.s_catalog.pathtree.activityStatusList[j].activityAttemptAbsoluteDuration = parent.functions.enfunctions.countAbsoluteDuration(parent.s_catalog.pathtree.activityStatusList[j].activityAttemptAbsoluteDuration,parent.s_catalog.pathtree.activityStatusList[j].lastTime,nowTime);
							parent.s_catalog.pathtree.thisAttemptList[j].startTime	= new Date().getTime();	// 0.001 S
							
						}
						else{
							// ���s�p��attempt duration-2003.12.15
							parent.s_catalog.pathtree.activityStatusList[j].activityAttemptAbsoluteDuration = 0.0;
							parent.s_catalog.pathtree.activityStatusList[j].activityAttemptExperiencedDuration = 0.0;
							parent.s_catalog.pathtree.thisAttemptList[j].startTime	= new Date().getTime();	// 0.001 S
							parent.s_catalog.pathtree.activityStatusList[j].activityAttemptCount=Number(parent.s_catalog.pathtree.activityStatusList[j].activityAttemptCount)+1;
							if(parent.s_catalog.pathtree.activityStatusList[j].activityAttemptCount==1){
							}							
						}
					parent.s_catalog.pathtree.activityStatusList[j].activityisActive=true;
				}
			}
			objForm = parent.s_catalog.pathtree.document.getElementById('fetchResourceForm');			
			objForm.href.value = (parent.s_catalog.pathtree.tocList[index].idref == null) ? 'about:blank' : parent.s_catalog.pathtree.tocList[index].idref;			
			objForm.parameter.value = parent.s_catalog.pathtree.tocList[index].parameter;
			objForm.isOpenWindow.value = parent.s_catalog.pathtree.tocList[index].target == '_blank' ? 'true' : 'false';
			objForm.target = parent.s_catalog.pathtree.tocList[index].target == '_blank' ? '_blank' : 's_main';
			objForm.submit();
			objForm.prev_href.value = parent.s_catalog.pathtree.tocList[index].idref;
			objForm.prev_node_id.value = parent.s_catalog.pathtree.tocList[index].id;
			objForm.prev_node_title.value = parent.s_catalog.pathtree.tocList[index].title;			
			parent.s_catalog.pathtree.fetchServerTime();			
			parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity=index;	
			
			
			if(parent.s_catalog.pathtree.navigationInterfaceList[index].hidePreviousButton	== true){			
				parent.tocstatus.statusObj.setPreviousButtonDisplay("hide");
			}
			else{
				parent.tocstatus.statusObj.setPreviousButtonDisplay("show");
			}
			if(parent.s_catalog.pathtree.navigationInterfaceList[index].hideContinueButton	== true){
				parent.tocstatus.statusObj.setContinueButtonDisplay("hide");
			}
			else{
				parent.tocstatus.statusObj.setContinueButtonDisplay("show");
			}
			if(parent.s_catalog.pathtree.navigationInterfaceList[index].hideExitButton == true){
				parent.tocstatus.statusObj.setExitButtonDisplay("hide");
			}
			else{
				parent.tocstatus.statusObj.setExitButtonDisplay("show");
			}
			
			
			// Heroin-2003.11.14
			// if parent is invisible then 
			var parentIndex=index;
			if(parentIndex!=0){
				for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
					if(parent.s_catalog.pathtree.tocList[i].id ==parent.s_catalog.pathtree.tocList[index].parentID){
						parentIndex=i;
						break;
					}
				}
			}
			// �ʺA���Tree���ثeItem����m
			parent.tocstatus.statusObj.changeCurrentBar(index);
			
			
			// ------------put index	into history Route-------------------------------
			// ---don't move	this statement�|�v�T��U����timer-------------------------
			Addhistory(index);
			//------------------------------------------------------------------------
	
			//
			
			// Heroin-2003.12.18
			// �o�̩I�sTimeOut function �}�l�p��Ҧ�������time out
			setTimeOut_all(index);
			
			
			// ------------start Current Activity Timer-------------------------------------------------
			parent.s_catalog.pathtree.SCOTimerList[index].startTimer(index); 
			parent.s_catalog.pathtree.SCOTimerList[index].startTime=new Date().getTime();
			
			// --------------------�p��-----------------------------------------------
			
			}
		}
		// Heroin 2004.04.08
		parent.tocstatus.statusObj.enableAllChoice();
		
		// for all path
	
	
	
		var currnetAncestors;
		var identifiedAncestors;
		
		identifiedAncestors=findAncestors(index);		
		for(var	i = 0 ;i<identifiedAncestors.length;i++){
			parent.tocstatus.statusObj.checkChoiceControls(identifiedAncestors[i]);			
		}
		
		
	}else{
		
		if(!queryPermitted){
			
			if(seqRequest=="Choice"){
				// �p�G�Ochoice,�h����findnext		
				index++;
				SequencingRequestProcess("Find",seqRequest);
			}
			else{
				parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity=index;
				index=current_index;
				// �p�G�Olimit condition...���ӭn��continue with	message
				SequencingRequestProcess("Find",seqRequest);
			}
		}
	}	
}



// Heroin-2003.12.18
function setTimeOut_all(itemIndex){
	// for all ���� �]�t�ۤv
	if(itemIndex!=""){	
		var currnetAncestors=findAncestors(itemIndex);
		// from current to common ancestor
		// �qroot��current�]�wsetTimeout
		for(var	i=currnetAncestors.length-1;i>-1;i--){
			// (currnetAncestors[i]+"   "+parent.s_catalog.pathtree.miniTimeOutList[currnetAncestors[i]].limit);
			if(parent.s_catalog.pathtree.miniTimeOutList[currnetAncestors[i]].limit==true){
				timeoutIndex[timeoutIndexCount]=currnetAncestors[i];
				var leftTime=  Number(parent.s_catalog.pathtree.miniTimeOutList[currnetAncestors[i]].time)/1000;
				if(leftTime>=60){
					var leftMin = leftTime/60;
					alert(parent.s_catalog.pathtree.tocList[currnetAncestors[i]].title+"<?=$MSG['cfm_msg7'][$sysSession->lang]?>" + leftMin.toFixed(0) + "<?=$MSG['cfm_msg8'][$sysSession->lang]?>");
				}
				else{
					alert(parent.s_catalog.pathtree.tocList[currnetAncestors[i]].title+"<?=$MSG['cfm_msg7'][$sysSession->lang]?>" + leftTime.toFixed(0) + "<?=$MSG['cfm_msg9'][$sysSession->lang]?>");
				}
				setTimeoutID[timeoutIndexCount]=setTimeout("timeoutAction(timeoutIndex,'continueWithMessage')",parent.s_catalog.pathtree.miniTimeOutList[currnetAncestors[i]].time);
				timeoutIndexCount++;
			}
		}
	}
}

function timeoutAction(itemIndex){
	
	
	// ���index
	// timeLimitAction���|��
	
	var minLimit = -1; // time limit ���ɶ�
	var minIndex; // time limit�̤p�� item
	for(var	j=0;j<itemIndex.length;j++){
		if(parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].limit==true	&& Number(parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].time)!=-1){
			parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].limit==false;
			if(minLimit==-1	|| Number(minLimit)>Number(parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].time)){
					minIndex=itemIndex[j]; 
					minLimit=Number(parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].time); 
					parent.s_catalog.pathtree.miniTimeOutList[itemIndex[j]].time=-1;
			}			
		}
	}
	


	/*  Henry 2004.07.26
	    ��verify timeLimitAction������
	    exit, message -->  type 1
		exit, no message --> type 2
		continue,message -->  type 3
		continue, no message --> type 4
		��check �Oexit�٬Ocontinue  */
	
	
	var StrtimeLimitAction = parent.s_catalog.pathtree.adlcpList[minIndex].timeLimitAction;

	// �M��timeoutIndexCount
	timeoutIndexCount=0;
	timeoutIndex = new Array();
	for(var k=0;k<setTimeoutID.length;k++){
		clearTimeout(setTimeoutID[k]);
	}
	setTimeoutID = new Array();
	// �M���Ҧ�setTimeout
	// �M��timer
	parent.s_catalog.pathtree.SCOTimerList[index].endTimer(index);		

	if(StrtimeLimitAction.indexOf("exit")!=-1){
		// type 1 or type 2 exit navigation request... 
		seqRequest="";
		exitRequest="Exit";
		if(StrtimeLimitAction.indexOf("no message")!=-1){
			// type 2
			
			exitRequestProcess();
			parent.s_main.location.href="../blank.htm";
		}else{
			// type 1
			alert("<?=$MSG['cfm_msg14'][$sysSession->lang]?> ");
			exitRequestProcess();
			parent.s_main.location.href="../blank.htm";
		
		}
		exitRequest="";
	}else{
		exitRequest="";
		seqRequest="Continue";
		if(StrtimeLimitAction.indexOf("no message")!=-1){
			// type 4
			if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){
				
				
				exitRequestProcess();
		

			}
			else{
				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				// seqRequest="Continue";
				SequencingRequestProcess("Find",seqRequest);
				
			}	

		}else{
			// type 3
			alert("<?=$MSG['cfm_msg13'][$sysSession->lang]?> ");
			if(parent.s_catalog.pathtree.activityStatusList[Number(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity)].activityisActive){			
				exitRequestProcess();
			}
			else{
				index=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
				SequencingRequestProcess("Find",seqRequest);
			}
		}
		seqRequest="";
	}
	
	

}



// 1. current activity  2.identified activity
function TerminateDescendentAttemptsProcess(current_index,identifiedActivity){
	var postCondition="false";
	if(current_index!=""){	
			
		var currnetAncestors=findAncestors(current_index);
		var identifiedAncestors=findAncestors(identifiedActivity);
		var commonAncestor=findCommonAncestor(currnetAncestors,identifiedAncestors);
		
		// from current to common ancestor
		for(var	i=currnetAncestors.length-2;i>commonAncestor;i--){
			if(parent.s_catalog.pathtree.activityStatusList[Number(currnetAncestors[i])].activityisActive.toString()=="false"){
			
			
			}else{
				
				EndAttemptProcess(currnetAncestors[i]);
				// Heroin 2004.06.21
				// �o�̧P�_postcondition
				// �`��activity path��postcondition
				
				var postResult= parsePostConditionRules(currnetAncestors[i]);
				
				
				// Heroin 2004.08.09
				// �p�GMeasureSatisfrcationIffActive = false
				// �n�b�������}����, deliver�U�@�Ӥ��e  �⪬�A�g�i�h		
				if(parent.s_catalog.pathtree.primaryObjectiveList[currnetAncestors[i]].satisfiedByMeasure){
					measureSatisficationIfActive(currnetAncestors[i]);
				}
				
				if(postResult.toString()=="true"){
					if(seqRequest=="Previous"){
						postCondition="true";
						index=currnetAncestors[i];
						seqRequest="Previous";
					}else if(seqRequest=="Retry"){
						postCondition="true";
						index=currnetAncestors[i];
					}
					break;
				}
				
			
			
				
			}
		}		
	}
	return postCondition;	
}


// find	common ancestor
function findCommonAncestor(array1,array2){
	
	
	for(var	i=0;i<array1.length;i++){
		if(array1[i]==array2[i]){
		}
		else{
			var h=i-1;
			return h;
		}
	}
	return "";
}


// find	Ancestors path
// �]�t�ۤv
function findAncestors(item_index){
	var ancestors =	new Array();
	
	var tempPathArray = new	Array();
	var ancestorsPathArray = new Array();
	var i=0;
	tempPathArray[i] = item_index;
	i++;

	tempParentID = parent.s_catalog.pathtree.tocList[item_index].parentID;
	while(tempParentID!=""){
		tempParentIndex	=  parent.functions.enfunctions.tocIDfindIndex(tempParentID);
		tempPathArray[i] = tempParentIndex;
		tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].parentID;
		i++;
	}

	// reverse the Path --> root 2 leaf
	for(var	j=0;j<tempPathArray.length;j++){
		ancestorsPathArray[j] =	tempPathArray[tempPathArray.length-j-1];
	}
	return ancestorsPathArray;

}


function LimitConditionsCheckProcess(itemInedx){
	var deliveryFlag = true;	
	// Heroin 2003.11.24
	
	// �Y�OSuspended�p��duration
	// 1.activity_abslute = activity_abslute	+ new()	- last_time
	// 2.activity_experienced = activity_experienced
	// 3.attempt_abslute = attempt_abslute +	now() -	last_time
	// 4.attempt_experienced	= attempt_experienced
	// Else
	// 1.activity_abslute = activity_abslute
	// 2.activity_experienced = activity_experienced
	// 3.attempt_abslute = 0
	// 4.attempt_experienced	= 0
	
	// Heroin-2003.12.15 LimitCondtion Duration	
	var tempActivityAbsoluteDuration = parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAbsoluteDuration;
	var tempActivityExperiencedDuration = parent.s_catalog.pathtree.activityStatusList[itemInedx].activityExperiencedDuration;
	var tempActivityAttemptAbsoluteDuration	= parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAttemptAbsoluteDuration;
	var tempActivityAttemptExperiencedDuration = parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAttemptExperiencedDuration;
	
	
	if(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityisSuspended.toString()=="true"){
		// �bDeliver�ɦA�~��֥[Duration!!
		
		// ���o�ثe�t�Ϊ��ɶ�
		var nowDate  = "<?=date('Y/m/d')?>";
		var nowTime00 =	"<?=date('H,i,s')?>";
		var nowTime02 =	nowDate.split("/");
		var nowTime03 =	nowTime00.split(",");
		var nowTime04 =	new Date(Number(nowTime02[0]),Number(nowTime02[1])-1,Number(nowTime02[2]),Number(nowTime03[0]),Number(nowTime03[1]),Number(nowTime03[2]));
		var nowTime = nowTime04.getTime();		
				
		tempActivityAbsoluteDuration = parent.functions.enfunctions.countAbsoluteDuration(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAbsoluteDuration,parent.s_catalog.pathtree.activityStatusList[itemInedx].lastTime,nowTime);
		tempActivityAttemptAbsoluteDuration = parent.functions.enfunctions.countAbsoluteDuration(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAttemptAbsoluteDuration,parent.s_catalog.pathtree.activityStatusList[itemInedx].lastTime,nowTime);
		
		
	}
	else{
		// New attempt ���s�p��--
		tempActivityAttemptAbsoluteDuration = 0; // ����
		tempActivityAttemptExperiencedDuration = 0;
		
	}
	
	
	// �P�_limitConditions
	if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].existflag){
		// �P�_���S���W�LActivityAttemptCount(maxAttempts)		
		// Heroin-2003.11.24 �W�[activityisActive�P�_	
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptLimit!=""){
			if(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityisActive==false){
				if(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityisSuspended.toString()=="true"){
					if(Number(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAttemptCount-1)>=Number(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptLimit)){
						deliveryFlag = false;
						if(!queryPermitted){	
							alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg6'][$sysSession->lang]?> ");
						}
					}
				}
				else if(Number(parent.s_catalog.pathtree.activityStatusList[itemInedx].activityAttemptCount)>=Number(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptLimit)){
					deliveryFlag = false;
					if(!queryPermitted){	
						alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg6'][$sysSession->lang]?> ");
					}
				}
			}
		}
		
		
		// �P�_���S���W�LattemptAbsoluteDurationLimit
		// �C���i�J���ɶ��{��
		// �n�[�J�p�ɥH��TimeOut������!!!!!!! Heroin
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptAbsoluteDurationLimit!=0.0){
			// Heroin 2003.12.17
			var tempAttemptAbsDurationLimit	= parent.functions.enfunctions.convertISOtime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptAbsoluteDurationLimit);
			var tempArray1 = tempAttemptAbsDurationLimit.split(":");
			var AttemptAbsDurationLimit = Number(tempArray1[0])*3600 + Number(tempArray1[1]*60) + Number(tempArray1[2]);
			if(Number(tempActivityAttemptAbsoluteDuration)>=AttemptAbsDurationLimit){
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg11'][$sysSession->lang]?> ");
				}
			}
			else{
				temptimeoutTime	= Number(AttemptAbsDurationLimit-tempActivityAttemptAbsoluteDuration)*1000;
				parent.s_catalog.pathtree.miniTimeOutList[itemInedx].limit=true;
			
				if((parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time ==-1)||(temptimeoutTime<parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time)){
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time = temptimeoutTime;
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].minTimeLimitType = "attemptAbsoluteDurationLimit";
				}
			}
		}
		
		
					
		// �P�_���S���W�LattemptExperiencedDurationLimit(maxAttemptDuration)
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptExperiencedDurationLimit!=0.0){
			
			
			var tempAttemptExpDurationLimit	= parent.functions.enfunctions.convertISOtime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].attemptExperiencedDurationLimit);
			var tempArray1 = tempAttemptExpDurationLimit.split(":");
			var AttemptExpDurationLimit = Number(tempArray1[0])*3600 + Number(tempArray1[1])*60 + Number(tempArray1[2]);
			if(Number(tempActivityAttemptExperiencedDuration)>=AttemptExpDurationLimit){
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg11'][$sysSession->lang]?> ");
				}
			}
			else{
				temptimeoutTime	= Number(AttemptExpDurationLimit-tempActivityAttemptExperiencedDuration)*1000;
				parent.s_catalog.pathtree.miniTimeOutList[itemInedx].limit=true;
				if((parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time ==-1)||(temptimeoutTime<parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time)){
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time = temptimeoutTime;
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].minTimeLimitType = "AttemptExpDurationLimit";
				}
				
			}
			
			
		}
		
		// �P�_���S���W�LactivityAbsoluteDurationLimit(maxActivityTimespan)
		/* total�i�J���ɶ��{��   */
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].activityAbsoluteDurationLimit!=0.0){
			
			
			var tempactivityAbsoluteDurationLimit =	parent.functions.enfunctions.convertISOtime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].activityAbsoluteDurationLimit);
			var tempArray1 = tempactivityAbsoluteDurationLimit.split(":");
			var activityAbsoluteDurationLimit = Number(tempArray1[0])*3600 + Number(tempArray1[1])*60 + Number(tempArray1[2]);
			
			if(Number(tempActivityAbsoluteDuration)>=activityAbsoluteDurationLimit ){
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg11'][$sysSession->lang]?> ");
				}
			}
			else{
				temptimeoutTime	= Number(activityAbsoluteDurationLimit-tempActivityAbsoluteDuration)*1000;
				parent.s_catalog.pathtree.miniTimeOutList[itemInedx].limit=true;
				if((parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time ==-1)||(temptimeoutTime<parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time)){
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time = temptimeoutTime;
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].minTimeLimitType = "attemptAbsoluteDurationLimit";
				}
				
			}
			
			
			
		}
		
		// �P�_���S���W�LactivityExperiencedDurationLimit(maxActivityDuration)
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].activityExperiencedDurationLimit!=0.0){
			
			var tempactivityExperiencedDurationLimit = parent.functions.enfunctions.convertISOtime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].activityExperiencedDurationLimit);
			var tempArray1 = tempactivityExperiencedDurationLimit.split(":");
			var activityExperiencedDurationLimit = Number(tempArray1[0])*3600 + Number(tempArray1[1]*60) + Number(tempArray1[2]);
			
			if(Number(tempActivityExperiencedDuration)>=activityExperiencedDurationLimit){
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg11'][$sysSession->lang]?> ");
				}
			}
			else{
				temptimeoutTime	= Number(activityExperiencedDurationLimit-tempActivityExperiencedDuration)*1000;
				
				parent.s_catalog.pathtree.miniTimeOutList[itemInedx].limit=true;
				if((parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time ==-1)||(temptimeoutTime<parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time)){
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].time = temptimeoutTime;
					parent.s_catalog.pathtree.miniTimeOutList[itemInedx].minTimeLimitType = "activityExperiencedDurationLimit";
				}
				
			}
			
			
			
		}
		// Heroin 2003.11.27 �ק�ɶ��ഫ
		// �P�_���S��beginTimeLimit(availableTimeBegin)
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].beginTimeLimit!="October,15 1582 00:00:00.0"){
			// check�ɶ����榡 assume 2002/10/30 08:00:00.0
			// �榡�ഫ October,15 1582 00:00:00.0 -> 2002/10/30 00:00:00.0
			var OriginalBTL	= parent.functions.enfunctions.convertTime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].beginTimeLimit);
			var NewBTL = OriginalBTL;
			var CurrentSystemDate =	"<?=date ('Y/m/d')?>";
			
			// �p�G�S�����	08:00:00 --> today + 08:00:00
			if(OriginalBTL.indexOf("/")==-1){
				NewBTL = CurrentSystemDate + " " + OriginalBTL;	
			}
			
			
			// �p�G�S���ɶ�	2002/10/30 --> 2002/10/30 00:00:00.0
			if(OriginalBTL.indexOf(":")==-1){
				NewBTL = OriginalBTL + " " + "00:00:00";
			}
			// ���o�ثe�t�Ϊ��ɶ�
			var CurrentSystemTime =	"<?=date ('Y/m/d H:i:s')?>";
			
			// check�ثe�ɶ����S���j��beginTimelimit
			var tempDate1 =	new Date(NewBTL);
			var tempDate2 =	new Date(CurrentSystemTime);
			
			if(Number(Date.parse(tempDate2))>Number(Date.parse(tempDate1))){
			
			}else{
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg4'][$sysSession->lang]?> ")
				}
			}
			
				
		}
		
		// Heroin 2003.11.27 �ק�ɶ��ഫ
		// �P�_���S��endTimeLimit(availableTimeEnd)
		if(parent.s_catalog.pathtree.limitConditionsList[itemInedx].endTimeLimit!="October,15 1582 00:00:00.0"){
			// check�ɶ����榡 assume 2002/10/30 08:00:00.0
			// �榡�ഫ October,15 1582 00:00:00.0 -> 2002/10/30 00:00:00.0
			var OriginalETL	= parent.functions.enfunctions.convertTime(parent.s_catalog.pathtree.limitConditionsList[itemInedx].endTimeLimit);
			var NewETL = OriginalETL;
			var CurrentSystemDate =	"<?=date ('Y/m/d')?>";
			
			// �p�G�S�����	08:00:00 --> today + 08:00:00
			if(OriginalETL.indexOf("/")==-1){
				NewETL = CurrentSystemDate + " " + OriginalETL;	
			}
			
			// �p�G�S���ɶ�	2002/10/30 --> 2002/10/30 23:59:59
			if(OriginalETL.indexOf(":")==-1){
				NewETL = OriginalETL + " " + "23:59:59";
			}
		
			// check�ثe�ɶ����S���p��availableTimeEnd
			// ���o�ثe�t�Ϊ��ɶ�
			var CurrentSystemTime =	"<?=date ('Y/m/d H:i:s')?>";
			// check�ثe�ɶ����S���j��endTimeLimit
			var tempDate1 =	new Date(NewETL);
			var tempDate2 =	new Date(CurrentSystemTime);
			
			if(Number(Date.parse(tempDate2))<Number(Date.parse(tempDate1))){
			}else{
				deliveryFlag = false;
				if(!queryPermitted){
					alert(parent.s_catalog.pathtree.tocList[itemInedx].title+"<?=$MSG['cfm_msg5'][$sysSession->lang]?>")
				}
			}				
		
		}

		// -----------------------------delivery	it or not-------------------------------------------
		
	}	
	var ActivityisLimited =	false;
	if(!deliveryFlag){
		ActivityisLimited = true;
	}
	
	return ActivityisLimited;
	
}




// -------------suspend process------------------------------------------------
function suspendProcess(){
	// 1.�p�G�Osco�N��sco��cmi.core.exit�]��suspend
	if(parent.API.GetSCO_ID	!= parent.s_catalog.pathtree.tocList[index].id){

		parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended = true;
	}else{

		parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended = true;
		parent.API.LMSSetValue("cmi.core.exit","suspend");
	}
	
	
	
	// 2.�N�Ҧ�ancestor��ActivityisSuspend�]��true
	suspendAncestorActivity(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity);

		
}

function suspendAncestorActivity(tempindex){
	
	parent.s_catalog.pathtree.activityStatusList[tempindex].activityisActive = false;
	parent.s_catalog.pathtree.activityStatusList[tempindex].activityisSuspended = true;
	if(tempindex!=index){
	}
	if(parent.s_catalog.pathtree.tocList[Number(tempindex)].parentID != ""){
		suspendAncestorActivity(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[Number(tempindex)].parentID));		
	}

}


// -------------suspend process------------------------------------------------

// -------------resume process------------------------------------------------
function resumeProcess(){
	index =	parent.functions.enfunctions.tocIDfindIndex(sco_ID);
	resumeChildActivity(index);
}

// added	by Heroin-2003.12.10
// ���^���̤U�h,��deliver��activity
function resumeChildActivity(tempindex){
	if(parent.s_catalog.pathtree.tocList[Number(tempindex)].itemType=="folder"){	
		parent.tocstatus.statusObj.unfold(tempindex);
	}
	
	if(parent.s_catalog.pathtree.tocList[Number(tempindex)].itemType=="leaf"){		
		index=tempindex;
		DeliveryRequesetProcess();
	
	}
	else{
		// find child 
		for(var	i=tempindex+1;i<parent.s_catalog.pathtree.tocList.length;i++){
			if(parent.s_catalog.pathtree.activityStatusList[i].activityisSuspended.toString()=="true"){
				// Vega 2004.11.17 itemType
				if(parent.s_catalog.pathtree.tocList[i].itemType=="folder"){
					resumeChildActivity(i);
					break;
				}
				else {
					// �o�̭n��confirm.......�n�ݬO�_�n�^��W�����}����m
					
					index=i;
					DeliveryRequesetProcess();
					break;
				}
			}
		}
	
	}
}


// -------------resume process------------------------------------------------
function showConcurrentActivity(){
	if(seqRequest=="Previous"){
		var tempParentIndex;
		tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
		var tempParentID;
		tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].id;
		do{
			for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
				if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemID==tempParentID){
					eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML='<a href=" + material_path + parent.s_catalog.pathtree.auxiliaryResourceList[i].href	+ " target=_blank><img src=images/"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+".gif border=0>"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"</a>'");
				}
			}	
			if(tempParentIndex!=0){
				tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempParentIndex].parentID);
				tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].id;
			}	
		}while(tempParentIndex > 0);
	}
}

function closeConcurrentActivity(){
	for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
		if(parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose!="glossary" && parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose!="reference" ){
			eval("parent.navigation.document.all['activity'].style.visibility='hidden'");
		}
	}	

	if(index_ != 0){
		var idx=parent.s_catalog.pathtree.historyRouteList.length-1;
		if(Number(idx)!=-1){
			var temp_index=eval("parent.s_catalog.pathtree.historyRouteList["+idx+"].itemIndex");
			if(parent.s_catalog.pathtree.tocList[index].parentID!=parent.s_catalog.pathtree.tocList[temp_index].parentID){
				var tempParentIndex;
				tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[temp_index].parentID);
				var tempParentID;
				tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].id;
				while(tempParentIndex != parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID) && tempParentIndex > 0){
					for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
						if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemID==tempParentID && tempParentIndex!=0){
							eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML=''");
						}
					}	
					tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempParentIndex].parentID);
					tempParentID = parent.s_catalog.pathtree.tocList[index].parentID;
				}
				tempParentIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[index].parentID);
				tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].id;
				do{
					for(var	i=0;i<parent.s_catalog.pathtree.auxiliaryResourceList.length;i++){
						if(parent.s_catalog.pathtree.auxiliaryResourceList[i].itemIndex==tempParentIndex){
							eval("parent.navigation.document.all['"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"'].innerHTML='<a href=" + material_path + parent.s_catalog.pathtree.auxiliaryResourceList[i].href	+ " target=_blank><img src=images/"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+".gif border=0>"+parent.s_catalog.pathtree.auxiliaryResourceList[i].purpose+"</a>'");
						}
					}	
					if(tempParentIndex!=0){
						tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempParentIndex].parentID);
						tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].id;
					}	
				}while(tempParentIndex > 0);
			}
		}
	}
}



// Heroin 2004.04.29
// Heroin 2004.05.18
function countChildrenSkip(){
	var count_children=0;
	for(var	i=parseInt(index)+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
		var parentIndex=Number(parent.tocstatus.tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
		if(Number(parentIndex)>= Number(index)){
			count_children++;
		}
		else{
			count_children++;
			index=i;
			break;
		}
	}	

	return count_children-1;
	
}

function countChildren(itemIndex){
	var count_children=0;
	for(var	i=itemIndex+1;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(parent.s_catalog.pathtree.tocList[i].parentID==parent.s_catalog.pathtree.tocList[itemIndex].id){
			// Vega 2004.11.17 itemType
			if(parent.s_catalog.pathtree.tocList[i].itemType=="folder"){
				count_children++;
				index=i;
				count_children+=countChildrenSkip(itemIndex);
				break;
			}
			else{
				count_children++;
			}	
		}
	}
	return count_children;
	
}



// rename Heroin	-02004.03.31 findChoice	-> ChoiceSequencingRequestProcess

function ChoiceSequencingRequestProcess(){

	var deliveryResult="false";
	var tempCurrentActivity=0;
	if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity==""){
		tempCurrentActivity=0;
	}else{
		tempCurrentActivity=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
	}
	
	// 1.�P�_traversal
	var traversalDirection="Forward";
	if(Number(index)>Number(tempCurrentActivity)){
		traversalDirection="Forward";
	}else{
		traversalDirection="Backward";
	}
	
	var IsAggregation = false;
	// �u�n���@��item�L��parentID����N�N��OAggregation
	
	// Heroin 2004.06.25 �ק�Aggregation�P�_��k
	for(var	i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(parent.s_catalog.pathtree.tocList[i].parentID==parent.s_catalog.pathtree.tocList[index].id){
			IsAggregation =	true;
			break;
		}
	}
		
		
                                                                                                            	var tempCurrentActivity=0;
	if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity==""){
		tempCurrentActivity=0;
	}else{
		tempCurrentActivity=parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity;
	}
	
	if(index == parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity){
		deliveryResult="true";
		
		// case	1 break	all cases
		// �I��ۤv ������
	}else if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity==""){
		// case 3 current undefine
		// current undefine �ˬd���path	
		// alert("case 3: Current Undefined");
		var traversalDirection="Forward";
 		//     
		var tempTraversalResult="true";
		var activityList=findAncestors(index);
		// all path
		for(var	i=0;i<activityList.length;i++){
			var traversalResult=ChoiceActivityTraversalSubprocess(activityList[i],traversalDirection);
			// alert("traversalResult="+traversalResult);
			if(traversalResult.toString()=="false"){
				// �����\Delivery
				tempTraversalResult="false";
				break;
			}else{
				tempTraversalResult="true";
			}
		}
		
		if(tempTraversalResult=="true"){
			deliveryResult="true";
		}
		
	}else if(parent.s_catalog.pathtree.tocList[index].parentID==parent.s_catalog.pathtree.tocList[tempCurrentActivity].parentID){
		// case	2 siblings
		// �I��S��  �ˬd�S�̴N�i
		// alert("case 2: siblings");
		// �P�_	Forward	or Backward
		var traversalDirection;
		if(Number(index)>Number(tempCurrentActivity)){
			traversalDirection="Forward";
		}else{
			traversalDirection="Backward";
		}
		var tempTraversalResult;
		// Current to target -->	current	and target --> target
		
		var traversalResult=ChoiceActivityTraversalSubprocess(index,traversalDirection);
		if(traversalResult.toString()=="false"){
			// �����\Delivery
			// break;
			tempTraversalResult="false";
		}else{
			tempTraversalResult="true";
			// DeliveryRequesetProcess();
			// SCODelivered = "true";
		}
		
		
		if(tempTraversalResult=="true"){
			deliveryResult="true";
		}
		
		
		
		
		

	}else {
		// if(IsAggregation){
			// �P�_�O�_��common ancestor
			var isCommonAncestor="false";
			var currentActivityList=findAncestors(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity);
			for(var	i=0;i<currentActivityList.length;i++){
				
				if(index==currentActivityList[i]){
					// �I�쯪��
					// ������
					// alert("case 4: Target	is the common ancestor");
					isCommonAncestor="true";
					
					var traversalDirection;
					if(Number(index)>Number(tempCurrentActivity)){
						traversalDirection="Forward";
					}else{
						traversalDirection="Backward";
					}

					var activityList=findAncestors(index);
					
					for(var	i=0;i<activityList.length;i++){
						
						var traversalResult=ChoiceActivityTraversalSubprocess(activityList[i],traversalDirection);
						if(traversalResult.toString()=="false"){
						
							// �����\Delivery
							deliveryResult="false";
							break;
						}else{
							deliveryResult="true";							
						}
					}
				}
			}
			
			if(isCommonAncestor=="false"){
			
				// alert("case 5: Other");
				// ��L
				
				// �P�_ common -> target
				var commonAncestor;
				var currnetAncestors;
				var identifiedAncestors;
				if(parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity!=""){
					currnetAncestors=findAncestors(tempCurrentActivity);
					identifiedAncestors=findAncestors(index);
					commonAncestor=findCommonAncestor(currnetAncestors,identifiedAncestors);
					
				}else{
					identifiedAncestors=findAncestors(index);
					commonAncestor=0;
				}
				
				
				var tempTraversalResult="true";
				for(var	i=commonAncestor+1;i<identifiedAncestors.length;i++){
					var traversalResult=ChoiceActivityTraversalSubprocess(identifiedAncestors[i],traversalDirection);
					if(traversalResult.toString()=="false"){
						// �����\Delivery
						tempTraversalResult="false";
						break;
					}else{
						tempTraversalResult="true";
					}
				}
				
				if(tempTraversalResult=="true"){
					deliveryResult="true";
					
				}
			}
	}
	
	
	
	if(deliveryResult=="true"){
		if(!IsAggregation){
			DeliveryRequesetProcess();
			SCODelivered = "true";
		}else{
			// Vega 2004.11.17 itemType
			if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){
				index++;
				SequencingRequestProcess("Find","Choice");
			}else{
				DeliveryRequesetProcess();
				SCODelivered = "true";
			}
		}
	}else{
	
		// alert("Traversal Return	False!!");
	
	}
}


function Addhistory(tempIndex){
	var temp_length=parent.s_catalog.pathtree.historyRouteList.length;
	parent.s_catalog.pathtree.historyRouteList[temp_length] = new parent.s_catalog.pathtree.history(tempIndex);	
}


function CheckGroup(tempIndex){
	var tempParentIndex = Number(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempIndex].parentID));
	var flag = false;
	do{
		if(tempParentIndex == FOControlIndex){
			flag=true;
		}
		tempParentIndex	= parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tempParentIndex].parentID);
	}while(tempParentIndex > 0);
	return flag;
}


function CheckGrouphistory(tempIndex){
	var i;
	var flag = false;
	for(i=0;i<parent.s_catalog.pathtree.historyRouteList.length;i++){
		// �p�G�P�@��Group�N����@�UIndex�j�p
		if(CheckGroup(parent.s_catalog.pathtree.historyRouteList[i].itemIndex)){
			/* �p�G�ثe��index�j��P�@��group��index�Nok,�Ϥ��N����   */
			if(Number(parent.s_catalog.pathtree.historyRouteList[i].itemIndex)>Number(tempIndex)){
				flag = true;
			}
		}			
	}
	return flag;
}



function CommitActivityStatus(){
	
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	var xmlpi = xmldoc.createProcessingInstruction("xml","version='1.0' encoding='big5'");
	xmldoc.appendChild(xmldoc.createElement("root"));
	xmldoc.insertBefore(xmlpi, xmldoc.childNodes[0]);
	var rootElement	= xmldoc.documentElement;

	/* 	course_ID,user_ID,sco_ID
		�n��check�Osco��sca,�p�G�Osco�N���ݭn�Nsession_time�^��
		�p�G�Osca�N���ݭn��session_time, isSuspended
		�p��P�_�Osco�άOsca??
		�p�GtocList[index].id	current	id�p�G����api adapter�̪�id
		�N��ܳo��sco��initialize,�ҥH�N�Osco  */

	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);
	rootElement.setAttribute("sco_ID",parent.s_catalog.pathtree.tocList[index].id);
	rootElement.setAttribute("Message_Type","ActivityStatus");
	
	if(parent.API.GetSCO_ID() != parent.s_catalog.pathtree.tocList[index].id){
		
		rootElement.setAttribute("Scorm_Type","sca");
		parent.s_catalog.pathtree.SCOTimerList[index].endTime=new Date().getTime();
		
		// Heroin-2003.11.28
		var tmp=parent.s_catalog.pathtree.SCOTimerList[index].endTime-parent.s_catalog.pathtree.SCOTimerList[index].startTime;		
		var elapsedSeconds = ( tmp / 1000 );
				
		var cmi_core_session_time_Element = xmldoc.createElement("cmi_core_session_time");
        if( cmi_core_session_time_Element.textContent == undefined )
            cmi_core_session_time_Element.text = parent.functions.enfunctions.convertTotalSeconds(elapsedSeconds);
        else
		    cmi_core_session_time_Element.textContent = parent.functions.enfunctions.convertTotalSeconds(elapsedSeconds);
		rootElement.appendChild(cmi_core_session_time_Element);
		
		
		// total_time�p�⦳���D
		var cmi_core_total_time	= cmi_core_session_time_Element.text;	
		var cmi_core_total_time_Element	= xmldoc.createElement("duration");
        if( cmi_core_total_time_Element.textContent == undefined )
            cmi_core_total_time_Element.text = cmi_core_total_time;
        else
		  cmi_core_total_time_Element.textContent = cmi_core_total_time;
		rootElement.appendChild(cmi_core_total_time_Element);
		
		// added	by Heroin 2003.10.27
		var isSuspended_Element	= xmldoc.createElement("isSuspended");
        if( isSuspended_Element.textContent == undefined )
            isSuspended_Element.text = "" + parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
        else
		    isSuspended_Element.textContent = "" + parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
		rootElement.appendChild(isSuspended_Element);

		// added	by Heroin 2003.11.02
		var cmi_core_score_normalized_Element =	xmldoc.createElement("cmi_core_score_normalized");		
		if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveMeasureStatus){
            if( cmi_core_score_normalized_Element.textContent == undefined )
                cmi_core_score_normalized_Element.text = parent.s_catalog.pathtree.trackingInfoList[index].objectiveNormalizedMeasure;
            else
                cmi_core_score_normalized_Element.textContent = parent.s_catalog.pathtree.trackingInfoList[index].objectiveNormalizedMeasure;
		}
		else{
            if( cmi_core_score_normalized_Element.textContent == undefined )
                cmi_core_score_normalized_Element.text="";
            else
                cmi_core_score_normalized_Element.textContent="";
		}			
		rootElement.appendChild(cmi_core_score_normalized_Element);

		var cmi_core_success_status_Element = xmldoc.createElement("cmi_core_success_status");		    
		    if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveProgressStatus==true){
			if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveSatisfiedStatus==true){
                if( cmi_core_success_status_Element.textContent == undefined )
                    cmi_core_success_status_Element.text = "passed";		 
                else
                    cmi_core_success_status_Element.textContent = "passed";		    
			}
			else if(parent.s_catalog.pathtree.trackingInfoList[index].objectiveSatisfiedStatus==false){
				if( cmi_core_success_status_Element.textContent == undefined )
                    cmi_core_success_status_Element.text = "failed";
                else
                    cmi_core_success_status_Element.textContent = "failed";
			}
		    }
		    else{
                if( cmi_core_success_status_Element.textContent == undefined )
                    cmi_core_success_status_Element.text = "unknown";
                else
                    cmi_core_success_status_Element.textContent = "unknown";
		    }
		rootElement.appendChild(cmi_core_success_status_Element);
		var cmi_core_completion_status_Element = xmldoc.createElement("cmi_core_completion_status");
		    if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptProgressStatus==true){
			if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCompletionStatus==true){
                if( cmi_core_completion_status_Element.textContent == undefined )
                    cmi_core_completion_status_Element.text	= "completed";
                else
                    cmi_core_completion_status_Element.textContent	= "completed";		    
			}
			else if(parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCompletionStatus==false){
                if( cmi_core_completion_status_Element.textContent == undefined )
                    cmi_core_completion_status_Element.text	= "incomplete";
                else
                    cmi_core_completion_status_Element.textContent	= "incomplete";
			}
		     }
		     else{
                if( cmi_core_completion_status_Element.textContent == undefined )
                    cmi_core_completion_status_Element.textContent	= "unknown"; 
                else
                    cmi_core_completion_status_Element.textContent	= "unknown"; 
		     }
		rootElement.appendChild(cmi_core_completion_status_Element);
		
		var cmi_score_raw_Element = xmldoc.createElement("cmi_score_raw");
            if( cmi_score_raw_Element.textContent == undefined )
                cmi_score_raw_Element.text = "" + parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
            else
                cmi_score_raw_Element.textContent = "" + parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
		rootElement.appendChild(cmi_score_raw_Element);

		
		var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");
        if( cmi_core_attempt_count_Element.textContent == undefined )	 
            cmi_core_attempt_count_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCount;
        else        
            cmi_core_attempt_count_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCount;
		rootElement.appendChild(cmi_core_attempt_count_Element);
		
		
		// Heroin-2003.12.08
		var cmi_core_isDisabled_Element	= xmldoc.createElement("cmi_core_isDisabled");	
        if( cmi_core_isDisabled_Element.textContent == undefined )        
            cmi_core_isDisabled_Element.text = parent.s_catalog.pathtree.tocList[index].disable;
        else
            cmi_core_isDisabled_Element.textContent = parent.s_catalog.pathtree.tocList[index].disable;
		rootElement.appendChild(cmi_core_isDisabled_Element);
		
		var cmi_core_isHiddenFromChoice_Element	= xmldoc.createElement("cmi_core_isHiddenFromChoice");		
        if( cmi_core_isHiddenFromChoice_Element.textContent == undefined )
            cmi_core_isHiddenFromChoice_Element.text = parent.s_catalog.pathtree.isHiddenFromChoiceList[index].value;
        else
            cmi_core_isHiddenFromChoice_Element.textContent = parent.s_catalog.pathtree.isHiddenFromChoiceList[index].value;
		rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
		
		
		// Heroin-2003.12.15 LimitCondtion Duration		
		var cmi_core_attempt_absolut_duration_Element =	xmldoc.createElement("cmi_core_attempt_absolut_duration");		
        if( cmi_core_attempt_absolut_duration_Element.textContent == undefined )
            cmi_core_attempt_absolut_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration;
        else
            cmi_core_attempt_absolut_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration;
		rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
		
		var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");	
        if( cmi_core_attempt_experienced_duration_Element.textContent == undefined )
            cmi_core_attempt_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration;
        else
            cmi_core_attempt_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration;
		rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
		
		var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");
        if( cmi_core_activity_absolut_duration_Element.textContent == undefined )	
            cmi_core_activity_absolut_duration_Element.text	= parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration;
        else        
            cmi_core_activity_absolut_duration_Element.textContent	= parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration;
		rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
		
		var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");		
        if( cmi_core_activity_experienced_duration_Element.textContent == undefined )
            cmi_core_activity_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration;
        else
            cmi_core_activity_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration;
		rootElement.appendChild(cmi_core_activity_experienced_duration_Element);

	}else{
		
		rootElement.setAttribute("Scorm_Type","sco");

		var isSuspended_Element	= xmldoc.createElement("isSuspended");
        if( isSuspended_Element.textContent == undefined )
             isSuspended_Element.text = "" +  parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
        else
		    isSuspended_Element.textContent = "" +  parent.s_catalog.pathtree.activityStatusList[index].activityisSuspended;
		rootElement.appendChild(isSuspended_Element);	
		var cmi_core_attempt_count_Element = xmldoc.createElement("cmi_core_attempt_count");
        if( cmi_core_attempt_count_Element.textContent == undefined )
            cmi_core_attempt_count_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCount;
        else
            cmi_core_attempt_count_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptCount;
		rootElement.appendChild(cmi_core_attempt_count_Element);
		
		// Heroin-2003.12.08
		var cmi_core_isDisabled_Element	= xmldoc.createElement("cmi_core_isDisabled");	
        if( cmi_core_isDisabled_Element.textContent == undefined )
            cmi_core_isDisabled_Element.text = parent.s_catalog.pathtree.tocList[index].disable;
        else
            cmi_core_isDisabled_Element.textContent = parent.s_catalog.pathtree.tocList[index].disable;
		rootElement.appendChild(cmi_core_isDisabled_Element);
		
		
		var cmi_core_isHiddenFromChoice_Element	= xmldoc.createElement("cmi_core_isHiddenFromChoice");		
        if( cmi_core_isHiddenFromChoice_Element.textContent == undefined )
            cmi_core_isHiddenFromChoice_Element.text = parent.s_catalog.pathtree.isHiddenFromChoiceList[index].value;
        else
            cmi_core_isHiddenFromChoice_Element.textContent = parent.s_catalog.pathtree.isHiddenFromChoiceList[index].value;
		rootElement.appendChild(cmi_core_isHiddenFromChoice_Element);
		
		
		// Heroin-2003.12.15 LimitCondtion Duration		
		var cmi_core_attempt_absolut_duration_Element =	xmldoc.createElement("cmi_core_attempt_absolut_duration");	
        if( cmi_core_attempt_absolut_duration_Element.textContent == undefined )
            cmi_core_attempt_absolut_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration;
        else
            cmi_core_attempt_absolut_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptAbsoluteDuration;
		rootElement.appendChild(cmi_core_attempt_absolut_duration_Element);
		
		var cmi_core_attempt_experienced_duration_Element = xmldoc.createElement("cmi_core_attempt_experienced_duration");	
        if( cmi_core_attempt_experienced_duration_Element.textContent == undefined )
            cmi_core_attempt_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration;
        else
            cmi_core_attempt_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityAttemptExperiencedDuration;
		rootElement.appendChild(cmi_core_attempt_experienced_duration_Element);
		
		var cmi_core_activity_absolut_duration_Element = xmldoc.createElement("cmi_core_activity_absolut_duration");		
        if( cmi_core_activity_absolut_duration_Element.textContent == undefined )
            cmi_core_activity_absolut_duration_Element.text	= parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration;
        else
            cmi_core_activity_absolut_duration_Element.textContent	= parent.s_catalog.pathtree.activityStatusList[index].activityAbsoluteDuration;
		rootElement.appendChild(cmi_core_activity_absolut_duration_Element);
		
		var cmi_core_activity_experienced_duration_Element = xmldoc.createElement("cmi_core_activity_experienced_duration");	
        if( cmi_core_activity_experienced_duration_Element.textContent == undefined ) 
            cmi_core_activity_experienced_duration_Element.text = parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration;
        else
            cmi_core_activity_experienced_duration_Element.textContent = parent.s_catalog.pathtree.activityStatusList[index].activityExperiencedDuration;
		rootElement.appendChild(cmi_core_activity_experienced_duration_Element);
		
		
	}
	
	var ServerSide = xmlhttp_set;	
	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",ServerSide,false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml; charset=big5");
	XMLHTTPObj.send(xmldoc.xml);

}



// ---------------------------------------Sequencing Exit Action	Rules Subprocess----------------------------------
function SequencingExitActionRulesSubprocess(){	
	/*	�bspec���O��root�V�U��,�Ĥ@�ӦX�Gexit����Y�i
		�i�O�ϹL�Ӥ���n�g,��쪺�̫�@�ӧY�i
		�إߤ@�ӥ�current item��root��path,�A��o��path reverse....�]���n�q�Y�}�l�䦳�S���ŦXexit rule
		checkExitRule();//�b�H�W��path check���S���ŦXexit����item
		check	-- Rule�Ҧb��Activity Status
		�Ncueck current�令check ancestor Heroin 2003.10.13 */
	var i=0;
	var tempParentID = "";
	var tempParentIndex = 0;
	var tempCurrentIndex = index;
	var ExitIndex =	index;
	var tempOriginalIndex =	index;
	
	
	var tempPathArray = new	Array();
	var PathArray =	new Array();
	
	tempParentID = parent.s_catalog.pathtree.tocList[index].parentID;
	
	
	// HHEERROOIINN	Heroin-2003.12.31 �Ncheck condition rules function����engine
	var returnList=new Array();
	returnList=parent.check.checkrules.exitActionRules(index);	
	var exitConditionRule_result = returnList[0];
	var tempCurrentIndex = returnList[1];
	
	
	// --------------------------------Exit-------------------------------
	if(exitConditionRule_result=="true"){
		// alert("in exitConditaionRule_Result=true!");
				
		var rtnEvent = "";
		var exitTarget = tempCurrentIndex;
		if(tempCurrentIndex==0){ // �]�N�OExit��organization
			rtnEvent = "Exit_All";
		}else{
			rtnEvent = "Exit_Parent";
		}
		
		// Heroin-2003.11.10
		if(tempCurrentIndex==0){  // exit this course!!
			index = tempCurrentIndex;
			var postResult="false";
			postResult= parsePostConditionRules(index);
			if(postResult.toString()=="true"){
				// alert("seqRequest="+seqRequest);
				if(seqRequest=="Previous"){
					SequencingRequestProcess("Find",seqRequest);
				}else if(seqRequest=="Retry"){
					
					// Heroin 2004.10.06 @@@@@@@
					useCurrentAttemptObjectiveInfo(index);
					useCurrentAttemptProgressInfo(index);
					clearItemProgressStatus(index);
					clearItemObjectiveStatus(index);
					
					
					SequencingRequestProcess("Find",seqRequest);
				}else if(seqRequest=="Continue"){
					// Vega 2004.11.17 itemType
					if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){
						/* ���Ҧ��p��,�����Ҧ��p��  */
						for(var i=parseInt(index)+1;i<parent.s_catalog.pathtree.tocList.length;i++){
							// �P�_�O�_�O�l�]...
							var parentIndex=Number(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
							if(Number(parentIndex)>= Number(index)){
								return_index=i;
							}
							else{
								break;				
							}
						}
						index=i-1;
						SequencingRequestProcess("Find",seqRequest);
					}else{
						SequencingRequestProcess("Find",seqRequest);
					}
				}
			}
		}else{
			/*	�NactivityActive�]��false
				form index+1 to tempCurrentIndex
				�NactivityActive=false  */
			var tmp_index=index;
			var tmp_index=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tmp_index].parentID);
			
			while(tmp_index	>= tempCurrentIndex){  // for each activity in the activity path!
				
				parent_itemIndex=parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[tmp_index].parentID);			
				parent.s_catalog.pathtree.activityStatusList[tmp_index].activityisActive=false;
				// �P�_�O���O�ݭnCheckDisplayDisabled Heroin-2003.11.28
				var needDesabled=CheckDisplayDisabled(tmp_index);
				if(needDesabled){
					parent.s_catalog.pathtree.tocList[tmp_index].disable =	"true";
					// Heroin 2004.04.04
					parent.s_catalog.pathtree.actionStatusList[tmp_index].isDisabled="true";
					var disabledIndex=parent.tocstatus.statusObj.DisplayDisabled(tmp_index);
				}
				
				var needHiddenformchoice=CheckDisplayHiddenfromchoice(tmp_index);
				if(needHiddenformchoice){
					// Heroin-2003.12.30
					parent.s_catalog.pathtree.tocDisplayList[tmp_index].isShow="false";
					// Heroin 2004.04.04
					parent.s_catalog.pathtree.actionStatusList[tmp_index].isHiddenFromChoice="true";
					var hiddenFormChoiceIndex=parent.tocstatus.statusObj.DisplayHiddenfromchoice(tmp_index);
				}
				var needSkip=CheckDisplaySkip(tmp_index);
				if(needSkip){
					parent.s_catalog.pathtree.actionStatusList[tmp_index].isSkip="true";
				}else{
					parent.s_catalog.pathtree.actionStatusList[tmp_index].isSkip="false";
				}
				// Vega 2004.11.17 itemType
				if(parent.s_catalog.pathtree.tocList[Number(tmp_index)].itemType=="folder"){
					saveAggregationInfoToDB(tmp_index);
				}
				tmp_index=parent_itemIndex;
			}
			// �Nindex���V�ŦXExit����activity
			index =	tempCurrentIndex;
			var currenttitle=parent.s_catalog.pathtree.tocList[index].title
			// postcondition
			var postResult="false";
			postResult= parsePostConditionRules(index);
			
			
			// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// Heroin 2004.06.21
			if(postResult.toString()=="true"){
				if(seqRequest=="Previous"){
					SequencingRequestProcess("Find",seqRequest);
				}else if(seqRequest=="Retry"){
					SequencingRequestProcess("Find",seqRequest);
				}else if(seqRequest=="Continue"){
					// Vega 2004.11.17 itemType
					if(parent.s_catalog.pathtree.tocList[Number(index)].itemType=="folder"){
						/* ���Ҧ��p��,�����Ҧ��p��  */
						for(var i=parseInt(index)+1;i<parent.s_catalog.pathtree.tocList.length;i++){
							// �P�_�O�_�O�l�]...
							var parentIndex=Number(parent.functions.enfunctions.tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
							if(Number(parentIndex)>= Number(index)){
								return_index=i;
							}
							else{
								break;				
							}
						}
						
						if(exitRequest=="ExitParent"){
								
						}else{
							index=i-1;
							SequencingRequestProcess("Find",seqRequest);
						}
						
					}else{
						SequencingRequestProcess("Find",seqRequest);
					}
				
				}
			}
			else{
				// Heroin 2004.02.13 �[�W����deliver�U�@��SCO
				parent.s_catalog.pathtree.GlobalStateObj.CurrentActivity = index;
				seqRequest="Continue";
				SequencingRequestProcess("Find",seqRequest);
			}
			
		}

	}
	
}

// �ˬd��item�O�_�Qdisable
function CheckActivityDisableProcess(itemInedx){

	var i=0;
	var ActivityisDisabled = false;
	
	// ��check��Activity�O�_�v�g�Qdisable
	if(parent.s_catalog.pathtree.tocList[itemInedx].disable=="true"){
		ActivityisDisabled = true;
	}
	
	return ActivityisDisabled;

}

// added	by Heroin 2003.11.11
// �ˬd��item�O�_�nskip
function CheckActivityisSkipProcess(itemInedx){

	var i=0;
	var ActivityisSkiped = false;
	
	// Heroin 2004.08.02
	// ��check��Activity�O�_�v�g�Qskip
	if(parent.s_catalog.pathtree.actionStatusList[itemInedx].isSkip=="true"){
		ActivityisSkiped = true;
	}

	return ActivityisSkiped;


}


/***************************Disabled and HiddenFormChoice ************************************/
// added	by Heroin -2003.11.28

function CheckDisplayDisabled(itemInedx){
	var conditionList=new Array();
	var conditionListCount=0;
	var checkResult	= false;
	
	action_collection=parent.check.checkrules.preConditionRules(index);	
	
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==itemInedx && parent.s_catalog.pathtree.preConditionRuleList[i].action=="disabled"){
			checkResult = parent.check.checkrules.preConditionDisabledAndHiddendfromchoice(itemInedx,i);
			
			return checkResult;
		}
	}
	return checkResult;
	
}


function CheckDisplayHiddenfromchoice(itemInedx){

	var conditionList=new Array();
	var conditionListCount=0;
	var checkResult	= false;
	
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==itemInedx && parent.s_catalog.pathtree.preConditionRuleList[i].action=="hiddenFromChoice"){
			checkResult = parent.check.checkrules.preConditionDisabledAndHiddendfromchoice(itemInedx,i);
			
			return checkResult;
			
		}
	}
	return checkResult;

}

// Heroin 2004.07.26
function CheckDisplaySkip(itemInedx){
	var conditionList=new Array();
	var conditionListCount=0;
	var checkResult	= false;
	
	for(var	i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){
		if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==itemInedx && parent.s_catalog.pathtree.preConditionRuleList[i].action=="skip"){
			checkResult = parent.check.checkrules.preConditionDisabledAndHiddendfromchoice(itemInedx,i);	
			return checkResult;
			
		}
	}
	return checkResult;

}


function CheckActivityProcess(current_index){
	// Heroin 2004.12.01
	
	var ActivityisReady = false;
	
	var ActivityisDisabled = false;
	ActivityisDisabled = CheckActivityDisableProcess(current_index);	
	// check	skip
	var ActivityisSkiped = false;	
	ActivityisSkiped = CheckActivityisSkipProcess(current_index);	
	var ActivityisLimited =	false;	
	ActivityisLimited = LimitConditionsCheckProcess(current_index);
	// ActivityisLimited=true;
	// alert(current_index+"   ActivityisSkiped="+ActivityisSkiped);
		
		var isSkip = false;
		if(ActivityisSkiped == true){
			isSkip = true;
			if(seqRequest=="Choice"){
				isSkip = false;
			}else{ // flow				
				
				var isFirstLeaf = true;
				var isLastLeaf = true;
				if(Number(index)>Number(previousActiveIndex)){
					// forward
					
					for(var i=current_index; i<index; i++ ){
						if(parent.s_catalog.pathtree.tocList[i].itemType!="folder"){
							isFirstLeaf = false;	
							isSkip = false;
							break;
						}
					}					
				}else{
				// backward
										
					if(Number(previousActiveIndex)==parseInt(index)+1 && parent.s_catalog.pathtree.tocList[previousActiveIndex].parentID == parent.s_catalog.pathtree.tocList[index].parentID){
						isSkip = false;
					}else{
						for(var i=previousActiveIndex-1; i>index; i-- ){
							// alert("parent.s_catalog.pathtree.tocList["+i+"].parentID="+parent.s_catalog.pathtree.tocList[i].parentID+"  parent.s_catalog.pathtree.tocList["+current_index+"].parentID="+parent.s_catalog.pathtree.tocList[current_index].parentID);

							if(parent.s_catalog.pathtree.tocList[i].parentID == parent.s_catalog.pathtree.tocList[current_index].parentID ){								
								isLastLeaf = true;	
								isSkip = true;
							}else if(parent.s_catalog.pathtree.tocList[i].itemType!="folder" ){
								isLastLeaf = false;	
								isSkip = false;
							}
						}
					}
				}
			}
		}
		
 	if(!ActivityisDisabled && !ActivityisLimited && !isSkip){
		var ActivityisReady = true;
	}
	// alert(current_index+"   ActivityisReady="+ActivityisReady);
	return ActivityisReady;

}




function ClearNavigationRequest(){
	scoID="";
	navEvent="";

}


function ClearSequencingEngineObj(){

	SequencingEngineObj = null;
}

function SequencingEngineInit(){
	this.NavigationRequestProcess = NavigationRequestProcess;
	this.ClearNavigationRequest = ClearNavigationRequest;
}


function init_engine() {
	sco_ID = index = index_ = choice_index = navEvent = seqRequest = exitRequest = null;
	result = minTimeLimitType = "";
	queryPermitted = queryPermittedprevious = queryPermittedcontinue = false;
	
	course_ID = parent.s_main.course_ID;

	SCODelivered = "false";
	deliveryMode="normal";
	FOControlIndex = "";
	
	historyRoute = new Array();
	historyRouteIndex = 0;

	timeoutIndex = new Array();
	timeoutIndexCount=0;
	setTimeoutID = new Array();

	suspendFlag=false;
	previousActiveIndex = 0;
	
	SequencingEngineObj = new SequencingEngineInit();	
}

</script>


</head>
</html>


