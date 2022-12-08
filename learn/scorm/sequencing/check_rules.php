<html>
<body>
<script language="javascript">
var checkrules;

function ruleConditions(){
	this.preConditionRules = preConditionRules	// check precondition rules  modified by Heroin 2004.09.09
	this.postConditionRules = postConditionRules	// check post condition rules	
	this.exitActionRules = exitActionRules		// check exit condition rules
	this.preConditionDisabledAndHiddendfromchoice = preConditionDisabledAndHiddendfromchoice	// check disabled and Hiddenfromchoice modified by Heroin 2004.09.18
}

//最新更新 Socool 2004.12.01
function preConditionRules(itemIndex){

	var actions = parseConditions("pre",itemIndex,0);
	return actions;

}

function postConditionRules(itemIndex){

	var actions = parseConditions("post",itemIndex,0);
	return actions;

}

function preConditionDisabledAndHiddendfromchoice(itemIndex,ruleIndex){
	var actions = parseConditions("preDH",itemIndex,ruleIndex);
	return actions;

}
		
function parseConditions(para,itemIndex,ruleIndex){	

	
	//sharable variables for different parameter ;socool 2004.12.02
	
	var j=0;
	var conditionRules_collection=new Array();
	var conditionRules_flag="false";
	
	switch(para){
		case "pre":
			for(var i=0;i<parent.s_catalog.pathtree.preConditionRuleList.length;i++){//紀錄目前SCO包含的preConditionRules
				if(parent.s_catalog.pathtree.preConditionRuleList[i].itemIndex==itemIndex){
					conditionRules_collection[j]=i;
					conditionRules_flag="true";
					j++;
				}
			}
			break;
		case "post":
			for(var i=0;i<parent.s_catalog.pathtree.postConditionRuleList.length;i++){//紀錄目前SCO包含的postConditionRules
				if(parent.s_catalog.pathtree.postConditionRuleList[i].itemIndex==itemIndex){
					conditionRules_collection[j]=i;
					conditionRules_flag="true";
					j++;
				}
			}	
			break;
		case "exit":
			break;
		default:
		//for preConditionDisableHiddenFromChoice
		conditionRules_collection[0]=ruleIndex;
		conditionRules_flag="true";
		
	}
	


	if(conditionRules_flag=="true"){
	
	 //alert("start parseCondition= "+para + "for Item :" +itemIndex);
	
		var a=0;
		var action_collection=new Array();
		//判斷每一個rule
		for(var i=0;i<conditionRules_collection.length;i++){
				
			var k=conditionRules_collection[i];
			var conditionRule_result="false";
			var preDH_result = false;
			
			var c,o,cc,oo,multiflag,combine;
			switch(para){
				case "post":
					c=parent.s_catalog.pathtree.postConditionRuleList[k].condition;
					o=parent.s_catalog.pathtree.postConditionRuleList[k].operator;
					multiflag = parent.s_catalog.pathtree.postConditionRuleList[k].multiflag;
					combine = parent.s_catalog.pathtree.postConditionRuleList[k].conditionCombination;
					break;
				case "exit":
					break;
				default:
				//for preCondition and preConditoinDisableHiddenFromChoice
					c=parent.s_catalog.pathtree.preConditionRuleList[k].condition;
					o=parent.s_catalog.pathtree.preConditionRuleList[k].operator;
					multiflag = parent.s_catalog.pathtree.preConditionRuleList[k].multiflag;
					combine = parent.s_catalog.pathtree.preConditionRuleList[k].conditionCombination;
			}
			
			
			if(multiflag){ //有conditionCombination
				cc=c.split("*");  //conditions
				oo=o.split("*");  //condtion operator		
			}else{
				cc=new Array(1);
				cc[0]=c;
				oo=new Array(1);
				oo[0]=o;
			}	
				
			
						
					for(var ii=0;ii<cc.length;ii++){
						var temp_result=false;
						//Modified by Heroin 2004-03-09
						//Modified by Socool 2004-12-02
						//alert("condition=" + cc[ii] + "  operator=" + oo[ii]);
						
						//*****start setting shared variables for different parameter *****
						var conditionRuleList_k_referencedObjective;
						switch(para){
							case "post":
								conditionRuleList_k_referencedObjective = parent.s_catalog.pathtree.postConditionRuleList[k].referencedObjective;
								conditionRuleList_k_measureThreshold = parent.s_catalog.pathtree.postConditionRuleList[k].measureThreshold;
								conditionRuleList_k_action = parent.s_catalog.pathtree.postConditionRuleList[k].action;
								break;
							case "exit":
								break;
							default:
							//for preCondition and preConditoinDisableHiddenFromChoice
								conditionRuleList_k_referencedObjective = parent.s_catalog.pathtree.preConditionRuleList[k].referencedObjective;
								conditionRuleList_k_measureThreshold = parent.s_catalog.pathtree.preConditionRuleList[k].measureThreshold;
								conditionRuleList_k_action = parent.s_catalog.pathtree.preConditionRuleList[k].action;
						}											
						//*****end setting shared variables for different parameter ****
						
						
						if(cc[ii]=="satisfied"){		
							var referencedObjectiveIndex;
							var referencedPrimary = "true";
							if (conditionRuleList_k_referencedObjective==""){
								referencedObjectiveIndex=itemIndex;
							}else{	
							
								//找primary以及objectives
								referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(conditionRuleList_k_referencedObjective);
								if(referencedObjectiveIndex==-1){
									var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
									referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(conditionRuleList_k_referencedObjective,itemIndex);
									referencedPrimary = "false";
								}
							}
							
							//是否為primary
							if(referencedPrimary == "false"){
								//Heroin 2004.09.09
								//objective
								if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){									
									if(oo[ii]=="noOp" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
										temp_result=true;
										
									}else if(oo[ii]=="not" && !parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
										temp_result=true;
										
									}
								}else{
									//==========Yunghsiao.2004.12.15===========
									if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 										for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){
											if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){							
												//if satisfiedByMeasure=true -> 讀target的分數回來判斷
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].satisfiedByMeasure.toString()=="true"){
													var sharedObjectiveMeasure=parent.functions.enfunctions.findObjectivesTargetMeasure(referencedObjectiveIndex);
													if(oo[ii]=="noOp" && sharedObjectiveMeasure >= parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].minNormalizedMeasure){
														temp_result = true;
													}else if(oo[ii]=="not" && sharedObjectiveMeasure < parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].minNormalizedMeasure){
														temp_result = true;
													}
												}else{
													var shardObjectiveStatus=parent.functions.enfunctions.findObjectivesTargetStatus(referencedObjectiveIndex);
													if(oo[ii]=="noOp" && shardObjectiveStatus.toString()=="true"){
														temp_result=true;	
													}else if(oo[ii]=="not" && shardObjectiveStatus.toString()=="false"){
														temp_result=true;
													}
												}
											}
										}
									}
									//========================================							
								}	
							}else{
								//primary
								if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){
									if(oo[ii]=="noOp" && parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
										temp_result=true;
										
									}else if(oo[ii]=="not" && !parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
										temp_result=true;
										
									}
								}else{																		
									//Vega 2004.10.26 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 																					
									if(parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus")!=-1){
										if(parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].satisfiedByMeasure.toString()=="true"){
											var sharedObjectiveMeasure=parent.functions.enfunctions.findTargetMeasure(referencedObjectiveIndex);
											if(oo[ii]=="noOp" && sharedObjectiveMeasure >= parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].minNormalizedMeasure){
												temp_result = true;
											}else if(oo[ii]=="not" && sharedObjectiveMeasure < parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].minNormalizedMeasure){
												temp_result = true;
											}
										
										}else{
											var shardObjectiveStatus=parent.functions.enfunctions.findTargetStatus(referencedObjectiveIndex);
											if(oo[ii]=="noOp" && shardObjectiveStatus.toString()=="true"){
												temp_result=true;
												
											}else if(oo[ii]=="not" && shardObjectiveStatus.toString()=="false"){
												temp_result=true;
												
											}
										}

									}
									
								}
							}
						}
						if(cc[ii]=="completed"){
							if(oo[ii]=="noOp"){
								if(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCompletionStatus){
									temp_result=true;
									
								}
							}else if(oo[ii]=="not"){
								if(!parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCompletionStatus){
									temp_result=true;
									
								}
							}
						}
						
						if(cc[ii]=="attempted"){
							if(oo[ii]=="noOp"){
								if(Number(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCount)>0){
									temp_result=true;
									
								}
								
							}else if(oo[ii]=="not"){
								if(Number(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCount)==0){
									temp_result=true;
									
								}
							}
						}
						if(cc[ii]=="always"){
							if(oo[ii]=="noOp"){
								temp_result=true;
								
							}else if(oo[ii]=="not"){
								//temp_result=true;
								
							}
						}
						
						if(cc[ii]=="objectiveMeasureGreaterThan"){
							var referencedPrimary = "true";
							if (conditionRuleList_k_referencedObjective==""){
								referencedObjectiveIndex=itemIndex;
							}else{	
							
								//找primary以及objectives
								referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(conditionRuleList_k_referencedObjective);
								if(referencedObjectiveIndex==-1){
									var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
									referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(conditionRuleList_k_referencedObjective,itemIndex);
									referencedPrimary = "false";
								}
							}
							if(referencedPrimary == "false"){
								//找objecitve
								
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
											temp_result=true;
											
										}
									}else{
										//========Yunghsiao.2004.12.15==========
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
															if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
																temp_result=true;
															}
														}
													}
												}
											}
										}
										//=======================================
									}
								}else if(oo[ii]=="not"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){
											temp_result=true;		
										}
									}else{
										//============Yunghsiao.2004.12.15=========
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
															if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){
																temp_result=true;
															}
														}
													}
												}
											}
										}
										//=========================================
									}
								}
							}else{
								//primary
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
											temp_result=true;
											
										}
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
													temp_result=true;
												}
											}
										}																			
									}
									
								}else if(oo[ii]=="not"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){
											temp_result=true;
											
										}
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){
													temp_result=true;
												}
											}
										}											
									}
								}	
							}
						}
						if(cc[ii]=="objectiveMeasureLessThan"){
							
							var referencedPrimary = "true";
							if (conditionRuleList_k_referencedObjective==""){
								referencedObjectiveIndex=itemIndex;
							}else{	
							
								//找primary以及objectives
								referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(conditionRuleList_k_referencedObjective);
								if(referencedObjectiveIndex==-1){
									var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
									referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(conditionRuleList_k_referencedObjective,itemIndex);
									referencedPrimary = "false";
								}
							}
							if(referencedPrimary == "false"){
								//找objecitve
								
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
											temp_result=true;
											
										}
									}else{
										//=====Yunghsiao.2004.12.15============================================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
															if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
																temp_result=true;
															}
														}
													}
												}
											}
										}
										//=====================================================================
									}
									
								}else if(oo[ii]=="not"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){
											temp_result=true;
											
										}
									}else{
										//=============Yunghsiao.2004.12.15=====================================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
															if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){
																temp_result=true;
															}
														}
													}
												}
											}
										}
										//=======================================================================
									}
								}
							}else{
								//primary
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
											temp_result=true;
											
										}
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)<=Number(conditionRuleList_k_measureThreshold)){//2005.08.12
													temp_result=true;
													
												}
											}											
										}										
									}
									
								}else if(oo[ii]=="not"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){
											temp_result=true;
											
										}
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)>=Number(conditionRuleList_k_measureThreshold)){
													temp_result=true;
													
												}
											}											
										}
									}
								}	
							}
						}
						if(cc[ii]=="objectiveMeasureKnown"){
						
							//Modified by Heroin 2004-03-09
							var referencedObjectiveIndex;
							var referencedPrimary = "true";
							if (conditionRuleList_k_referencedObjective==""){
								referencedObjectiveIndex=itemIndex;
							}else{	
							
								//找primary以及objectives
								referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(conditionRuleList_k_referencedObjective);
								if(referencedObjectiveIndex==-1){
									var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
									referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(conditionRuleList_k_referencedObjective,itemIndex);
									referencedPrimary = "false";
								}
							}
							
							
							//是否為primary
							if(referencedPrimary == "false"){
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										temp_result=true;
										
									}else{
										//==========Yunghsiao.2004.12.15========================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
															temp_result=true;
														}
													}
												}
											}
										}
										//=======================================================
									}
									
								}else if(oo[ii]=="not"){
									if(!parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){							
										temp_result=true;
										
									}else{
										//=============Yunghsiao.2004.12.15=======================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(!parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){
															temp_result=true;
														}
													}
												}
											}													
										}	
										//=========================================================					
									}
								}
								
							}else{
							
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
										temp_result=true;
										
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												temp_result=true;
												
											}											
										}	
									}
									
								}else if(oo[ii]=="not"){
									if(!parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){							
										temp_result=true;
										
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
												temp_result=true;
												
											}											
										}					
									}
								}
								
							}
						}
						if(cc[ii]=="objectiveStatusKnown"){
						
							var referencedObjectiveIndex;
							var referencedPrimary = "true";
							if (conditionRuleList_k_referencedObjective==""){
								referencedObjectiveIndex=itemIndex;
							}else{	
							
								//找primary以及objectives
								referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(conditionRuleList_k_referencedObjective);
								if(referencedObjectiveIndex==-1){
									var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
									referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(conditionRuleList_k_referencedObjective,itemIndex);
									referencedPrimary = "false";
								}
							}
							
							
							//是否為primary
							if(referencedPrimary == "false"){
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){
										temp_result=true;
										
									}else{
										//=========Yunghsiao.2004.12.15==========================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){															
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){
														if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveProgressStatus){							
															temp_result=true;
														}
													}
												}
											}
										}
										//========================================================
									}
								}else if(oo[ii]=="not"){
									if(!parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){							
										temp_result=true;
										
									}else{
										//========Yunghsiao.2004.12.15=============================
										if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 											for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){
													var SCO_ID = parent.s_catalog.pathtree.tocList[itemIndex].id;
													var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
													var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
													for(var i=0;i<shardObjectiveID.length;i++){										
														if(!parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveProgressStatus){
															temp_result=true;
														}
													}
												}
											}													
										}
										//=========================================================
									}
								}
								
								
								
							}else{
							
								if(oo[ii]=="noOp"){
									if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){
										temp_result=true;
										
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveProgressStatus){							
												temp_result=true;
												
											}											
										}
									}
									
								}else if(oo[ii]=="not"){
									if(!parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){							
										temp_result=true;
										
									}else{
										//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
										var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
										if(shardObjectiveID!=-1){
											if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveProgressStatus){							
												temp_result=true;
												
											}											
										}												
									}
								}
							}
						
						}
						
						if(cc[ii]=="activityProgressKnown"){

							if(oo[ii]=="noOp"){
								if(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityProgressStatus){
									temp_result=true;
									
								}	
							}else if(oo[ii]=="not"){
								if(!parent.s_catalog.pathtree.activityStatusList[itemIndex].activityProgressStatus){
									temp_result=true;
									
								}
							}
						
						}
						if(cc[ii]=="attemptLimitExceeded"){
						
							if(oo[ii]=="noOp"){
								if(Number(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCount)>=Number(parent.s_catalog.pathtree.limitConditionsList[itemIndex].attemptLimit) && parent.s_catalog.pathtree.activityStatusList[itemIndex].activityisActive.toString()=="false"){
									temp_result=true;
									
								}
							}else if(oo[ii]=="not"){
								if(Number(parent.s_catalog.pathtree.activityStatusList[itemIndex].activityAttemptCount)<Number(parent.s_catalog.pathtree.limitConditionsList[itemIndex].attemptLimit) && parent.s_catalog.pathtree.activityStatusList[itemIndex].activityisActive.toString()=="false"){
									temp_result=true;
									
								}
							}
						}

						// SCORM 2004 無 Heroin 2004.03.29
						if(cc[ii]=="outsideAvaliableTimeRange"){
						
							var checkBegin = true;
							var checkEnd = true;
							
							
							//Heroin 2003.11.27 修改時間轉換
							//判斷有沒有beginTimeLimit(availableTimeBegin)
							if(parent.s_catalog.pathtree.limitConditionsList[itemIndex].beginTimeLimit!="October,15 1582 00:00:00.0"){
								//check時間的格式 assume 2002/10/30 08:00:00.0
								//格式轉換 October,15 1582 00:00:00.0 -> 2002/10/30 00:00:00.0
								var OriginalBTL = parent.functions.enfunctions.convertTime(parent.s_catalog.pathtree.limitConditionsList[itemIndex].beginTimeLimit);
								var NewBTL = OriginalBTL;
								var CurrentSystemDate = "<?=date('Y/m/d')?>";
								
								//如果沒有日期  08:00:00 --> today + 08:00:00
								if(OriginalBTL.indexOf("/")==-1){
									NewBTL = CurrentSystemDate + " " + OriginalBTL; 
								}
								
								//如果沒有時間  2002/10/30 --> 2002/10/30 00:00:00.0
								if(OriginalBTL.indexOf(":")==-1){
									NewBTL = OriginalBTL + " " + "00:00:00";
								}
								//取得目前系統的時間
								var CurrentSystemTime = "<?=date('Y/m/d H:i:s')?>";
								
								//check目前時間有沒有大於beginTimelimit
								var tempDate1 = new Date(NewBTL);
								var tempDate2 = new Date(CurrentSystemTime);
								
								if(Number(Date.parse(tempDate2))>Number(Date.parse(tempDate1))){
								
								}else{
										checkBegin=false;
										alert(parent.s_catalog.pathtree.tocList[itemIndex].title+"無法進入,因為您已經尚未到開始時間!")
									
								}
							}
							
							//Heroin 2003.11.27 修改時間轉換
							//判斷有沒有endTimeLimit(availableTimeEnd)
							if(parent.s_catalog.pathtree.limitConditionsList[itemIndex].endTimeLimit!="October,15 1582 00:00:00.0"){
								//check時間的格式 assume 2002/10/30 08:00:00.0
								//格式轉換 October,15 1582 00:00:00.0 -> 2002/10/30 00:00:00.0
								var OriginalETL = parent.functions.enfunctions.convertTime(parent.s_catalog.pathtree.limitConditionsList[itemIndex].endTimeLimit);
								var NewETL = OriginalETL;
								var CurrentSystemDate = "<?=date ('Y/m/d')?>";
								
								//如果沒有日期  08:00:00 --> today + 08:00:00
								if(OriginalETL.indexOf("/")==-1){
									NewETL = CurrentSystemDate + " " + OriginalETL; 
								}
								
								//如果沒有時間  2002/10/30 --> 2002/10/30 23:59:59
								if(OriginalETL.indexOf(":")==-1){
									NewETL = OriginalETL + " " + "23:59:59";
								}
							
								//check目前時間有沒有小於availableTimeEnd
								//取得目前系統的時間
								var CurrentSystemTime = "<?=date ('Y/m/d H:i:s')?>";
								//check目前時間有沒有大於endTimeLimit
								var tempDate1 = new Date(NewETL);
								var tempDate2 = new Date(CurrentSystemTime);
								
								if(Number(Date.parse(tempDate2))<Number(Date.parse(tempDate1))){
								}else{
										checkEnd = false;
										alert(parent.s_catalog.pathtree.tocList[itemIndex].title+"無法進入,因為您已經超過結束時間!")
								}				
							}
							
							if(oo[ii]=="noOp"){
								if(checkBegin == false || checkEnd == false){
									temp_result=true;
													
								}								
							}else if(oo[ii]=="not"){
								if(checkBegin == true && checkEnd == true){
									temp_result=true;
													
								}
							}						
							
						}
					   // alert("over run section: "+ii + "\n combine =" + combine + "\n operator="+ oo[ii]+ "\n condition=" + cc[ii]);	
					    if(combine=="any" && temp_result==true){
						//alert("combine = any and one result = true, so break");
						break;
					    }else if(combine=="all" && temp_result==false){
					    	//alert("combine = all and one result = false, so break");
						break;
					    }
											
					}//end for cc.length
					
				if(temp_result==true){//收集condition的結果
					conditionRule_result="true";
					preDH_result = true;
					action_collection[a]=conditionRuleList_k_action;
					a++;					
				}
						
		} //end for conditionRules_collection.length	
		
	}//end for if flag
	//alert("conditionRule_result="+conditionRule_result);
	if(para == "preDH"){
		//alert("return boolean : " + preDH_result);
		return preDH_result;
		
	}else{
		//alert("action :" + action_collection);
		return action_collection;
	}
}


//最新更新 Socool 2004.12.03
function exitActionRules(itemIndex){ 
	//alert("itemIndex="+itemIndex+" SequencingExitActionRulesSubprocess");

	//在spec中是由root向下找,第一個合乎exit條件即可
	//可是反過來比較好寫,找到的最後一個即可
	//建立一個由current item到root的path,再把這個path reverse....因為要從頭開始找有沒有符合exit rule
	//checkExitRule();//在以上的path check有沒有符合exit條件的item
	//check -- Rule所在的Activity Status
	//將cueck current改成check ancestor Heroin 2003.10.13
	var i=0;
	var tempParentID = "";
	var tempParentIndex = 0;
	var tempCurrentIndex = itemIndex;
	var ExitIndex = itemIndex;
	var tempOriginalIndex = itemIndex;
	
	
	var tempPathArray = new Array();
	var PathArray = new Array();
	
	tempParentID = parent.s_catalog.pathtree.tocList[itemIndex].parentID;
	while(tempParentID!=""){
		tempParentIndex =  parent.functions.enfunctions.tocIDfindIndex(tempParentID);
		tempPathArray[i] = tempParentIndex;
		tempParentID = parent.s_catalog.pathtree.tocList[tempParentIndex].parentID;
		i++;
	}

	//reverse the Path --> root 2 leaf
	for(i=0;i<tempPathArray.length;i++){
		PathArray[i] = tempPathArray[tempPathArray.length-i-1];
	}
	
	
	tempCurrentIndex = 0;
	var exitConditionRule_result="false";
	
	
	for(i=0;i<PathArray.length;i++){
		
		if(exitConditionRule_result=="false"){
			tempCurrentIndex = PathArray[i];
			//alert("tempCurrentIndex="+tempCurrentIndex);
			//if completed then exit....  ??????
			
			var j=0;
			var k=0;
			var exitConditionRules_collection=new Array(); 
			var exitConditionRules_flag="false";
			
			
			for(j=0;j<parent.s_catalog.pathtree.exitConditionRuleList.length;j++){
				if(parent.s_catalog.pathtree.exitConditionRuleList[j].itemIndex==tempCurrentIndex){
					exitConditionRules_collection[k]=j;
					exitConditionRules_flag="true";
					k++;
				}
			}
			
			if(exitConditionRules_flag=="true"){
			
			//alert("start exitAction for item : " + tempCurrentIndex); //socool
				
				var a=0;
				var action_collection=new Array();
				for(var j=0;j<exitConditionRules_collection.length;j++){ 
					
					var k=exitConditionRules_collection[j]; 
					var cc,oo;
					var c=parent.s_catalog.pathtree.exitConditionRuleList[k].condition;
					var o=parent.s_catalog.pathtree.exitConditionRuleList[k].operator;
					var combine = parent.s_catalog.pathtree.exitConditionRuleList[k].conditionCombination;
					var temp_result=false;
					if(parent.s_catalog.pathtree.exitConditionRuleList[k].multiflag){ //有conditionCombination
						cc=c.split("*");  //conditions
						oo=o.split("*");  //condtion operator		
					}else{
						cc=new Array(1);
						cc[0]=c;
						oo=new Array(1);
						oo[0]=o;
					}
						
							for(var ii=0;ii<cc.length;ii++){
							
								//alert("section : " + ii + "\n combine = " + combine + "\n operator = " + oo[ii] + "\n condition = " + cc[ii]); //socool
								if(cc[ii]=="satisfied"){		
									var referencedObjectiveIndex;
									var referencedPrimary = "true";
									if (parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective==""){
										referencedObjectiveIndex=tempCurrentIndex;
									}else{	
									
										//找primary以及objectives
										referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective);
										if(referencedObjectiveIndex==-1){
											var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
											referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective,tempCurrentIndex);
											referencedPrimary = "false";
										}
									}
									
									//是否為primary
									if(referencedPrimary == "false"){
										//Heroin 2004.09.09
										//objective
										if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){									
											if(oo[ii]=="noOp" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
												temp_result=true;
												
											}else if(oo[ii]=="not" && !parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
												temp_result=true;
												
											}
										}else{
											//========Yunghsiao.2004.12.15====================
											if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 												for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
													if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){							
													//if satisfiedByMeasure=true -> 讀target的分數回來判斷
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].satisfiedByMeasure.toString()=="true"){
															var sharedObjectiveMeasure=parent.functions.enfunctions.findObjectivesTargetMeasure(referencedObjectiveIndex);
															if(oo[ii]=="noOp" && sharedObjectiveMeasure >= parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].minNormalizedMeasure){
																temp_result = true;
															}else if(oo[ii]=="not" && sharedObjectiveMeasure < parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].minNormalizedMeasure){
																temp_result = true;
															}
														}else{
															var shardObjectiveStatus=parent.functions.enfunctions.findObjectivesTargetStatus(referencedObjectiveIndex);
															if(oo[ii]=="noOp" && shardObjectiveStatus.toString()=="true"){
																temp_result=true;
															}else if(oo[ii]=="not" && shardObjectiveStatus.toString()=="false"){
																temp_result=true;
															}
														}
													}
												}
											}
											//==================================================						
										}
										
										
									}else{
										//primary
										if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){
											if(oo[ii]=="noOp" && parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
												temp_result=true;
												
											}else if(oo[ii]=="not" && !parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveSatisfiedStatus){
												temp_result=true;
												
											}
										}else{
											//Vega 2004.10.26 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 																					
											if(parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus")!=-1){										
												//Heroin 2004.08.17
												//if satisfiedByMeasure=true -> 讀target的分數回來判斷
												
												if(parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].satisfiedByMeasure.toString()=="true"){
													var sharedObjectiveMeasure=parent.functions.enfunctions.findTargetMeasure(referencedObjectiveIndex);
													if(oo[ii]=="noOp" && sharedObjectiveMeasure >= parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].minNormalizedMeasure){
														temp_result = true;
													}else if(oo[ii]=="not" && sharedObjectiveMeasure < parent.s_catalog.pathtree.primaryObjectiveList[referencedObjectiveIndex].minNormalizedMeasure){
														temp_result = true;
													}
												
												}else{
													var shardObjectiveStatus=parent.functions.enfunctions.findTargetStatus(referencedObjectiveIndex);
													if(oo[ii]=="noOp" && shardObjectiveStatus.toString()=="true"){
														temp_result=true;
														
													}else if(oo[ii]=="not" && shardObjectiveStatus.toString()=="false"){
														temp_result=true;
														
													}
												}
											}
										}
									}
								}
								if(cc[ii]=="completed"){
									if(oo[ii]=="noOp"){
										if(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCompletionStatus){
											temp_result=true;
											
										}
									}
									else if(oo[ii]=="not"){
										if(!parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCompletionStatus){
											temp_result=true;
											
										}
									}
								}
								if(cc[ii]=="attempted"){
									if(oo[ii]=="noOp"){
										if(Number(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCount)>0){
											temp_result=true;
											
										}
									}
									else if(oo[ii]=="not"){
										if(Number(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCount)==0){
											temp_result=true;
											
										}
									}
								}	
								if(cc[ii]=="attemptLimitExceeded"){
									if(oo[ii]=="noOp"){
										if(Number(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCount)>=Number(parent.s_catalog.pathtree.limitConditionsList[tempCurrentIndex].attemptLimit)){
											temp_result=true;
											
										}
									}
									else if(oo[ii]=="not"){
										if(Number(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityAttemptCount)<Number(parent.s_catalog.pathtree.limitConditionsList[tempCurrentIndex].attemptLimit)){
											temp_result=true;
											
										}
									}
								}
								
								if(cc[ii]=="always"){
									if(oo[ii]=="noOp"){
										temp_result=true;
										
									}else if(oo[ii]=="not"){
										//temp_result=true;
										//break;
									}
								}
								
								if(cc[ii]=="objectiveMeasureGreaterThan"){
									var referencedPrimary = "true";
									if (parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective==""){
										referencedObjectiveIndex=tempCurrentIndex;
									}else{	
									
										//找primary以及objectives
										referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective);
										if(referencedObjectiveIndex==-1){
											var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
											referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective,tempCurrentIndex);
											referencedPrimary = "false";
										}
									}
									if(referencedPrimary == "false"){
										//找objecitve
										
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//=======Yunghsiao.2004.12.15===============
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;													
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){													
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
																	if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)>Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
																		temp_result=true;
																	}
																}
															}
														}
													}
												}
												//===========================================
											}
											
										}else if(oo[ii]=="not"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//=========Yunghsiao.2004.12.15===============
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
														if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
																	if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)<=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
																		temp_result=true;
																	}
																}
															}
														}
													}
												}
												//============================================
											}
										}
									}else{
										//primary
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)>Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
															temp_result=true;
															
														}
													}											
												}
											}
											
										}else if(oo[ii]=="not"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)<=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
															temp_result=true;
															
														}
													}											
												}
											}
										}	
									}
								}
								if(cc[ii]=="objectiveMeasureLessThan"){
									
									var referencedPrimary = "true";
									if (parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective==""){
										referencedObjectiveIndex=tempCurrentIndex;
									}else{	
									
										//找primary以及objectives
										referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective);
										if(referencedObjectiveIndex==-1){
											var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
											referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective,tempCurrentIndex);
											referencedPrimary = "false";
										}
									}
									if(referencedPrimary == "false"){
										//找objecitve
										
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//========Yunghsiao.2004.12.15==============
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
																	if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)<Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
																		temp_result=true;
																	}
																}
															}
														}
													}
												}
												//==========================================
											}
											
										}else if(oo[ii]=="not"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//==========Yunghsiao.2004.12.15=============
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
														if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].mapInfoList[p].readNormalizedMeasure){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
																	if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveNormalizedMeasure)>=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
																		temp_result=true;
																	}
																}
															}
														}
													}
												}
												//===========================================
											}
										}
									}else{
										//primary
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)<Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)<Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
															temp_result=true;
															
														}
													}											
												}
											}
											
										}else if(oo[ii]=="not"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												if(Number(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveNormalizedMeasure)>=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
													temp_result=true;
													
												}
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														if(Number(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveNormalizedMeasure)>=Number(parent.s_catalog.pathtree.exitConditionRuleList[k].measureThreshold)){
															temp_result=true;
															
														}
													}											
												}
											}
										}	
									}
								}
								if(cc[ii]=="objectiveMeasureKnown"){
								
									//Modified by Heroin 2004-03-09
									var referencedObjectiveIndex;
									var referencedPrimary = "true";
									if (parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective==""){
										referencedObjectiveIndex=tempCurrentIndex;
									}else{	
									
										//找primary以及objectives
										referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective);
										if(referencedObjectiveIndex==-1){
											var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
											referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective,tempCurrentIndex);
											referencedPrimary = "false";
										}
									}
									
									
									//是否為primary
									if(referencedPrimary == "false"){
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												temp_result=true;
												
											}else{
												//==========Yunghsiao.2004.12.15=======================
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){	
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){							
																	temp_result=true;
																}
															}
														}
													}
												}
												//=====================================================
											}
											
										}else if(oo[ii]=="not"){
											if(!parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveMeasureStatus){							
												temp_result=true;
												
											}else{
												//============Yunghsiao.2004.12.15=====================
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(!parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveMeasureStatus){
																	temp_result=true;
																}
															}	
														}
													}													
												}						
												//======================================================
											}
										}
										
									}else{
									
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){
												temp_result=true;
												
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														temp_result=true;
														
													}											
												}
											}
											
										}else if(oo[ii]=="not"){
											if(!parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveMeasureStatus){							
												temp_result=true;
												
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readNormalized");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														temp_result=true;
														
													}											
												}
											}
										}
										
									}
								}
								if(cc[ii]=="objectiveStatusKnown"){
								
									var referencedObjectiveIndex;
									var referencedPrimary = "true";
									if (parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective==""){
										referencedObjectiveIndex=tempCurrentIndex;
									}else{	
									
										//找primary以及objectives
										referencedObjectiveIndex=parent.functions.enfunctions.findPrimaryObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective);
										if(referencedObjectiveIndex==-1){
											var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
											referencedObjectiveIndex=parent.functions.enfunctions.findObjectiveID(parent.s_catalog.pathtree.exitConditionRuleList[k].referencedObjective,tempCurrentIndex);
											referencedPrimary = "false";
										}
									}
									
									
									//是否為primary
									if(referencedPrimary == "false"){
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){
												temp_result=true;
												
											}else{
												//===========Yunghsiao.2004.12.15=================
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){															
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveProgressStatus){							
																	temp_result=true;
																}
															}
														}
													}
												}
												//================================================
											}				
										}else if(oo[ii]=="not"){
											if(!parent.s_catalog.pathtree.objectiveProgressInfoList[referencedObjectiveIndex].objectiveProgressStatus){							
												temp_result=true;
											}else{
												//============Yunghsiao.2004.12.15================
												if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length>0){
 													for(var p=0;p<parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList.length;p++){
														if(parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].targetObjectiveID!="" && parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].mapInfoList[p].readSatisfiedStatus){
															var SCO_ID = parent.s_catalog.pathtree.tocList[tempCurrentIndex].id;
															var referencedObjectiveID = parent.s_catalog.pathtree.objectiveList[referencedObjectiveIndex].objectiveID;
															var shardObjectiveID=parent.functions.enfunctions.findObjectivesTargetIndex(referencedObjectiveID,SCO_ID);
															for(var i=0;i<shardObjectiveID.length;i++){
																if(!parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID[i]].objectiveProgressStatus){
																	temp_result=true;
																}
															}
														}
													}													
												}
												//=================================================						
											}
										}		
									}else{
										if(oo[ii]=="noOp"){
											if(parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){
												temp_result=true;
												
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														temp_result=true;
														
													}											
												}
											}
											
										}else if(oo[ii]=="not"){
											if(!parent.s_catalog.pathtree.trackingInfoList[referencedObjectiveIndex].objectiveProgressStatus){							
												temp_result=true;
												
											}else{
												//Vega 2004.10.20 modified p_mapInfo->p_mapInfoList TS 1.3.1 Course-52 												
												var shardObjectiveID = parent.functions.enfunctions.findReadTargetObjectiveIndex(referencedObjectiveIndex,"readStatus");
												if(shardObjectiveID!=-1){
													if(parent.s_catalog.pathtree.sharedObjectiveList[shardObjectiveID].objectiveMeasureStatus){							
														temp_result=true;
														
													}											
												}
											}
										}
									}
								
								}
								
								if(cc[ii]=="activityProgressKnown"){
		
									if(oo[ii]=="noOp"){
										if(parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityProgressStatus){
											temp_result=true;
											
										}	
									}else if(oo[ii]=="not"){
										if(!parent.s_catalog.pathtree.activityStatusList[tempCurrentIndex].activityProgressStatus){
											temp_result=true;
											
										}
									}
								
								}
								
								if(combine=="any" && temp_result==true){
									//alert("combine = any and one result = true, so break");
									break;
					    			}else if(combine=="all" && temp_result==false){
					    				//alert("combine = all and one result = false, so break");
									break;
					    			}
								
													
							}//end for cc.length
						
						if(temp_result==true){
							exitConditionRule_result="true";
							action_collection[a]=parent.s_catalog.pathtree.exitConditionRuleList[k].action;
							a++;
						}
													
				}//end for exitConditionRules_collection.length	
						
			}//end for flag = true		
		
		}//if exitConditionRule_result
		
		if(exitConditionRule_result=="true"){
			//alert("break!");
			break;
		}

	}//for 
	//alert("exit condition ="+exitConditionRule_result+"  tempCurrentIndex="+tempCurrentIndex);
	var returnList=new Array();
	returnList[0]=exitConditionRule_result;
	returnList[1]=tempCurrentIndex;
	
	return returnList;
		
}

function init_check_rules() {
	checkrules = new ruleConditions();	
}
</script>

</body>
</html>
