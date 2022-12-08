/**
 *
 *  Simple Sequencing 設定介面  by Wiseguy Liang 2003/08
 *
 */

/**
 * 收攏整個 tree
 */
function setExpandEvent(obj){
	var nodes = obj.getElementsByTagName('LI');
	var firstNode;
	for(var i=0; i<nodes.length; i++){
		firstNode = nodes[i].firstChild;
		if (firstNode.tagName == 'INPUT' && firstNode.type == 'checkbox'){
			firstNode.onclick=expanding;
		}
		/*
		else if(firstNode.tagName == 'SPAN' && firstNode.firstChild.tagName == 'INPUT' && firstNode.firstChild.type == 'checkbox'){
			firstNode.firstChild.onclick=expanding;
		}
		*/
	}
}

/**
 * 當點選節點前的 checkbox 時，展開此 cluster
 */
function expanding(){
	var nodes = this.parentNode.childNodes;
	for(var i=1; i<nodes.length; i++){
		if (nodes[i] != null && (nodes[i].tagName == 'INPUT' || nodes[i].tagName == 'SELECT'))
			nodes[i].disabled = !this.checked;
	}
	nodes = this.parentNode.getElementsByTagName('UL');
	if (nodes.length) nodes[0].style.display = this.checked ? '' : 'none';
}

/**
 * 增加一個弟節點
 */
function addSibling(obj){
	var grandpa = obj.parentNode.parentNode;
	var parent = grandpa.lastChild;
	while(parent.tagName != 'LI' && parent != null) parent = parent.previousSibling;
	if (parent == null) return;
	var org, nodes, idx = grandpa.childNodes.length + 1;

	if (isIE){
		org = parent.outerHTML;
		org = org.replace(/(conditionCombination._)[0-9]+/g, '$1' + idx );
		org = org.replace(/<script.+<.script>/g, '');

		// 改變背景顏色 -------------------------
		org = (parent.className.toString().indexOf('Odd') > 0 ) ? org.replace('Odd', 'Evn') : org.replace('Evn', 'Odd');

		parent.outerHTML += org;
		nodes = grandpa.childNodes;
		for(var i=0; i<nodes.length; i++){
			if (nodes[i].tagName == 'LI' &&
			    nodes[i].firstChild.tagName == 'INPUT' &&
			    nodes[i].firstChild.type == 'checkbox') nodes[i].firstChild.onclick=expanding;
		}
	}
	else{
		org = parent.cloneNode(true);
		org.className = (parent.className.toString().indexOf('Odd') > 0 ) ? org.className.replace('Odd', 'Evn') : org.className.replace('Evn', 'Odd');

		nodes = org.getElementsByTagName('INPUT');
		for(var i=0; i<nodes.length; i++) if(nodes[i].type=='radio') nodes[i].name = nodes[i].name.replace(/(conditionCombination._)[0-9]+/, '$1' + idx );
		nodes = org.getElementsByTagName('SCRIPT');
		for(var i=nodes.length-1; i>=0; i--) nodes[i].parentNode.removeChild(nodes[i]);

		org = grandpa.appendChild(org);
		org.firstChild.addEventListener('click', expanding, false);
	}

	return false;
}

/**
 * 刪除一個弟節點
 */
function rmSibling(obj){
	var parent = obj.parentNode;
	var grandpa = parent.parentNode;
	if (grandpa.childNodes.length > 1){
		grandpa.removeChild(parent);
		checkedLi(grandpa);
	}
	return false;
}

/**
 * 把 <LI> 背景色交錯化
 */
function checkedLi(obj){
	var nodes = obj.childNodes;
	var cName = '', idx = 1;
	for(var i=0; i<nodes.length; i++){
		if (nodes[i].nodeType == 1 && nodes[i].tagName == 'LI'){
			if (cName == ''){
				cName = new Array(nodes[i].className);
				cName[1] =  cName[0].search('Odd') > 0 ?
							cName[0].replace('Odd', 'Evn') :
							cName[0].replace('Evn', 'Odd') ;
			}
			else{
				nodes[i].className = cName[idx];
				idx = ++idx%2;
			}
		}
	}
}

/**
 * 切換 ruleResult 選項
 */
function switchRuleResult(obj, idx){
	var nodes = obj.lastChild.getElementsByTagName('select');
		for(var j=0; j<3; j++) nodes[j].style.display = (idx == j) ? '' : 'none';
}

function ruleAction(obj){
	var lastNode = obj.parentNode.parentNode.lastChild;
				// checkbox.span.li.ul
	if (lastNode.tagName == 'UL')
		lastNode.style.display = obj.checked ? '':'none';
	var nodes = obj.parentNode.parentNode.getElementsByTagName('input');

	for(var i=1; i<nodes.length; i++) nodes[i].disabled = !obj.checked;
	
	// 根據每個條件的點選狀態來決定action是否enable或disable
	// checkbox->span->li->ul->all input
	var inputs = obj.parentNode.parentNode.parentNode.getElementsByTagName('input');
	var step = 4;
	var select = false;
	for (var i = 0; i < inputs.length; i+= step) {
		if ( i > 23) step = 2;
		else if (i > 11) step = 6;		
		if (inputs[i].checked) {
			select = true;
			break;
		}
	}
	
	// checkbox->span->li->ul->td->next td->all select
	nodes = obj.parentNode.parentNode.parentNode.parentNode.nextSibling.getElementsByTagName('select');
	for(var i=0; i<nodes.length; i++) nodes[i].disabled = !select;
}

/**
 * 動態產生 <select> 之 <option> 選項
 */
function generateOptions(start, over, key){
	for(var i=start; i<=over; i++)
		document.writeln('<option value="' + i + '"' + (key == i ? ' selected>':'>') + i + '</option>');
}

/**
 * 切換本 SS 為實體設定或是參考設定
 */
function switchIDtype(mode){
	document.getElementById('ssPanel').firstChild.style.display = mode ? '' : 'none';
}

/**
 * 設定完畢 mode=true：確定  mode=false：取消
 */
function ssSetupComplete(mode){
	var obj = document.getElementById('ssSetupPanel');
	var objForm = document.getElementById('ssSetupForm');
    var directionValue = '';
	
	if (mode){
		var item_id = objForm.item_id.value;
		var isGlobal = objForm.isGlobal.value;
		
		if (isGlobal == 'true') {
			if (sequencingCollection == null) {
				newNode = isIE ? xmlDoc.createNode(1, 'imsss:sequencingCollection', 'http://www.imsglobal.org/xsd/imsss') :
								 xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:sequencingCollection');
				sequencingCollection = xmlDoc.selectSingleNode('./manifest').appendChild(newNode);
			}
			var sequencing_id = objForm.sequencing_id.value;
			// 檢查Sequencing ID格式
			if (sequencing_id.search(/^[A-Za-z][\w\.\-]*$/) != 0) {
				alert('ID Format Error!');
				return;
			}
			// 檢查有沒有重複的Global Sequencing
			if (item_id != sequencing_id) {
				if (sequencingCollection.selectSingleNode('//imsss:sequencing[@ID="'+sequencing_id+'"]')) {
					alert('ID Exist!');
					return;
				}
			}
			var itemNode = sequencingCollection;
    		var ssNode = sequencingCollection.selectSingleNode('//imsss:sequencing[@ID="'+item_id+'"]');
    	}
    	else {
			var itemNode = xmlDoc.selectSingleNode("//item[@identifier='" + item_id + "'] | //organization[@identifier='"+item_id+"']");
			var ssNode = xmlDoc.selectSingleNode("//item[@identifier='" + item_id + "']/imsss:sequencing | //organization[@identifier='"+item_id+"']/imsss:sequencing");
		}
		
		if (ssNode === null){   // there is no SS setup. create a new
			var newNode = isIE ? xmlDoc.createNode(1, 'imsss:sequencing', 'http://www.imsglobal.org/xsd/imsss') :
								 xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:sequencing');
			ssNode = itemNode.appendChild(newNode);
		}
		else{                   // It had SS property, Remove all, and add new.
			ssNode.removeAttribute('IDRef');
            var nodes = ssNode.childNodes;
			for(var i=nodes.length-1; i>=0; i--) ssNode.removeChild(nodes[i]);
		}
	
		// Start of parse html form Setup
	    // If create a new SS
			if (isGlobal == 'true')
				ssNode.setAttribute('ID', sequencing_id);
		
			if(objForm.ss_id.checked) {
				if (objForm.sequencing_idref.value != '')
					ssNode.setAttribute('IDRef', objForm.sequencing_idref.value);
				else {
					alert('No set IDRef!!');
					return;
				}
			}

			// ========== controlMode ==========
			if (objForm.controlMode.checked){
				newNode = isIE ? xmlDoc.createNode(1, 'imsss:controlMode', 'http://www.imsglobal.org/xsd/imsss') :
								 xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:controlMode');
				newNode = ssNode.appendChild(newNode);

				if (!objForm.choice.checked) newNode.setAttribute('choice', 'false');
				if (!objForm.choiceExit.checked) newNode.setAttribute('choiceExit', 'false');
				if (objForm.flow.checked) newNode.setAttribute('flow', 'true');
				if (objForm.forwardOnly.checked) newNode.setAttribute('forwardOnly', 'true');
				if (!objForm.useCurrentAttemptObjectiveInfo.checked) newNode.setAttribute('useCurrentAttemptObjectiveInfo', 'false');
				if (!objForm.useCurrentAttemptProgressInfo.checked) newNode.setAttribute('useCurrentAttemptProgressInfo', 'false');				
            }
            
            // ========== constrainedChoiceConsiderations ==========
            if (objForm.constrainedChoiceConsiderations.checked) {
            	newNode = isIE ? xmlDoc.createNode(1, 'adlseq:constrainedChoiceConsiderations', 'http://www.adlnet.org/xsd/adlseq_v1p3') :
								 xmlDoc.createElementNS('http://www.adlnet.org/xsd/adlseq_v1p3', 'adlseq:constrainedChoiceConsiderations');
				newNode = ssNode.appendChild(newNode);
				if (objForm.constrainChoice.checked) newNode.setAttribute('constrainChoice', 'true');
				if (objForm.preventActivation.checked) newNode.setAttribute('preventActivation', 'true');
            }
						
			// ========== sequencingRules ==========
			if(objForm.sequencingRules.checked){
               newNode = isIE ? xmlDoc.createNode(1, 'imsss:sequencingRules', 'http://www.imsglobal.org/xsd/imsss') :
                              xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:sequencingRules');
                newNode = ssNode.appendChild(newNode);
				uls = objForm.sequencingRules.parentNode.lastChild.childNodes;    // 取得「路徑規則」下的所有子節點 -------------------------
				for(var i=0; i<uls.length; i++){
					if(uls[i].tagName != 'LI') continue;                // 如果不是 <LI> 則不理會 -------------------------
					inputs  = uls[i].getElementsByTagName('input');     // 取得所有 <input> -------------------------
					selects = uls[i].getElementsByTagName('select');    // 取得所有 <select> -------------------------
					if (!inputs[0].checked) continue;                   // 如果沒有選取此 rule 則不處理 -------------------------
					newCond = isIE ? xmlDoc.createNode(1, 'imsss:' + selects[0].value, 'http://www.imsglobal.org/xsd/imsss') :
                                  xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:' + selects[0].value);
					newCond = newNode.appendChild(newCond);
					first = true; step = 4;
					for(var j=3; j<40; j+=step){                           // 12 個條件有沒有 enable -------------------------
						if (j > 26) step = 2;                               // 第 6 個規則後，就沒有子設定 -------------------------
					    else if (j > 14) step = 6;
						if (inputs[j].checked){
							if (first){                                     // 第一次有條件，先建一個 <ruleConditions> -------------------------
								newRcond = isIE ? xmlDoc.createNode(1, 'imsss:ruleConditions', 'http://www.imsglobal.org/xsd/imsss') :
                                               xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:ruleConditions');
								newRcond = newCond.appendChild(newRcond);
								newRcond.setAttribute('conditionCombination', inputs[2].checked ? 'all' : 'any');
								first = false;
							}
							newRule = isIE ? xmlDoc.createNode(1, 'imsss:ruleCondition', 'http://www.imsglobal.org/xsd/imsss') :
                                            xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:ruleCondition');
							newRule = newRcond.appendChild(newRule);
							newRule.setAttribute('condition', inputs[j].value);
							if (inputs[j+1].checked) newRule.setAttribute('operator', 'not');
							if (j < 27){                                    // 第 6 個規則前，有下列兩個子設定 -------------------------
								if (inputs[j+2].checked) newRule.setAttribute('referenceObjective', inputs[j+3].value);
								if (j > 14 && inputs[j+4].checked) newRule.setAttribute('measureThreshold', inputs[j+5].value);
							}
						}
					}
					newRcond = isIE ? xmlDoc.createNode(1, 'imsss:ruleAction', 'http://www.imsglobal.org/xsd/imsss') :
                                   xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:ruleAction');
					newRcond = newCond.appendChild(newRcond);
					newRcond.setAttribute('action', selects[selects[0].selectedIndex + 1].value);
				}
            } 

			// ========== limitConditions ==========
			if(objForm.limitConditions.checked){
               newNode = isIE ? xmlDoc.createNode(1, 'imsss:limitConditions', 'http://www.imsglobal.org/xsd/imsss') :
                              xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:limitConditions');
				newNode = ssNode.appendChild(newNode);
				if (objForm.attemptLimit.checked){
					newNode.setAttribute('attemptLimit', objForm.attemptLimitValue.value);
				}
				if (objForm.attemptAbsoluteDurationLimit.checked){
					newNode.setAttribute('attemptAbsoluteDurationLimit', sprintf('%02d:%02d:%02dd',
										  objForm.attemptAbsoluteDurationLimit_hour.value,
										  objForm.attemptAbsoluteDurationLimit_minute.value,
										  objForm.attemptAbsoluteDurationLimit_second.value));
				}
				if (objForm.attemptExperiencedDurationLimit.checked){
					newNode.setAttribute('attemptExperiencedDurationLimit', sprintf('%02d:%02d:%02dd',
										  objForm.attemptExperiencedDurationLimit_hour.value,
										  objForm.attemptExperiencedDurationLimit_minute.value,
										  objForm.attemptExperiencedDurationLimit_second.value));
				}
				if (objForm.activityAbsoluteDurationLimit.checked){
					newNode.setAttribute('activityAbsoluteDurationLimit', sprintf('%02d:%02d:%02dd',
										  objForm.activityAbsoluteDurationLimit_hour.value,
										  objForm.activityAbsoluteDurationLimit_minute.value,
										  objForm.activityAbsoluteDurationLimit_second.value));
				}
				if (objForm.activityExperiencedDurationLimit.checked){
					newNode.setAttribute('activityExperiencedDurationLimit', sprintf('%02d:%02d:%02dd',
										  objForm.activityExperiencedDurationLimit_hour.value,
										  objForm.activityExperiencedDurationLimit_minute.value,
										  objForm.activityExperiencedDurationLimit_second.value));
				}
				if (objForm.beginTimeLimit.checked){
					newNode.setAttribute('beginTimeLimit', sprintf('%04d:%02d:%02dT%02d:%02d:%02dd',
										  objForm.beginTimeLimit_year.value,
										  objForm.beginTimeLimit_month.value,
										  objForm.beginTimeLimit_day.value,
										  objForm.beginTimeLimit_hour.value,
										  objForm.beginTimeLimit_minute.value,
										  objForm.beginTimeLimit_second.value));
				}
				if (objForm.endTimeLimit.checked){
					newNode.setAttribute('endTimeLimit', sprintf('%04d:%02d:%02dT%02d:%02d:%02dd',
										  objForm.endTimeLimit_year.value,
										  objForm.endTimeLimit_month.value,
										  objForm.endTimeLimit_day.value,
										  objForm.endTimeLimit_hour.value,
										  objForm.endTimeLimit_minute.value,
										  objForm.endTimeLimit_second.value));
				}

			}

			// ========== auxiliaryResources ==========
			if(objForm.auxiliaryResources.checked){
                newNode = isIE ? xmlDoc.createNode(1, 'imsss:auxiliaryResources', 'http://www.imsglobal.org/xsd/imsss') :
                               xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:auxiliaryResources');
				newNode = ssNode.appendChild(newNode);
				nodes = objForm.auxiliaryResources.parentNode.lastChild.childNodes;
				for(var i=0; i<nodes.length; i++){
					if(nodes[i].tagName != 'LI') continue;
					childs = nodes[i].getElementsByTagName('input');
					if (childs[0].checked){
						newChild = isIE ? xmlDoc.createNode(1, 'imsss:auxiliaryResource', 'http://www.imsglobal.org/xsd/imsss') :
                                        xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:auxiliaryResource');
						newChild = newNode.appendChild(newChild);
						newChild.setAttribute('auxiliaryResourceID', childs[1].value);
						newChild.setAttribute('purpose', childs[2].value);
					}
				}
			}

			// ========== rollupRules ==========
			if(objForm.rollupRules.checked){
				newNode = isIE ? xmlDoc.createNode(1, 'imsss:rollupRules', 'http://www.imsglobal.org/xsd/imsss') :
                              xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:rollupRules');
				newNode = ssNode.appendChild(newNode);
				lis = objForm.rollupRules.parentNode.lastChild.childNodes;
				for(var i=0; i<lis.length; i++){
					if (lis[i].tagName != 'LI') continue;
					inputs = lis[i].getElementsByTagName('input');
					switch(inputs[0].name){
						case 'rollupObjectiveSatisfied':
						case 'rollupProgressCompletion':
							if (!inputs[0].checked) newNode.setAttribute(inputs[0].name, 'false'); break;
						case 'ObjectiveMeasureWeight':
							if (inputs[0].checked) newNode.setAttribute('ObjectiveMeasureWeight', inputs[1].value); break;
						case 'rollupRule':
							if (!inputs[0].checked) continue;
							selects = lis[i].getElementsByTagName('select');
							newCond = isIE ? xmlDoc.createNode(1, 'imsss:rollupRule', 'http://www.imsglobal.org/xsd/imsss') :
                                           xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:rollupRule');
							newCond = newNode.appendChild(newCond);
							newCond.setAttribute('childActivitySet', selects[0].value);
							newCond.setAttribute('minimunCount',   inputs[1].value);
							newCond.setAttribute('minimunPercent', inputs[2].value);
							first = true;
							for(var j=5; j<22; j+=2){
								if (!inputs[j].checked) continue;
								if (first){
									newRule = isIE ? xmlDoc.createNode(1, 'imsss:rollupConditions', 'http://www.imsglobal.org/xsd/imsss') :
                                                     xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:rollupConditions');
									newRule = newCond.appendChild(newRule);
									newRule.setAttribute('conditionCombination', inputs[3].checked ? 'all' : 'any');
									first = false;
								}
								newRcond = isIE ? xmlDoc.createNode(1, 'imsss:rollupCondition', 'http://www.imsglobal.org/xsd/imsss') :
                                                  xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:rollupCondition');
								newRcond = newRule.appendChild(newRcond);
								newRcond.setAttribute('condition', inputs[j].value);
								if(inputs[j+1].checked) newRcond.setAttribute('operator', 'not');
							}
							newRcond = isIE ? xmlDoc.createNode(1, 'imsss:rollupAction', 'http://www.imsglobal.org/xsd/imsss') :
                                             xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:rollupAction');
							newRcond = newCond.appendChild(newRcond);
							newRcond.setAttribute('action', selects[1].value);
							break;
					}
				}
			}			
			
			// ========== rollupConsiderations ==========
			if (objForm.rollupConsiderations.checked) {
				newNode = isIE ? xmlDoc.createNode(1, 'adlseq:rollupConsiderations', 'http://www.adlnet.org/xsd/adlseq_v1p3') :
								 xmlDoc.createElementNS('http://www.adlnet.org/xsd/adlseq_v1p3', 'adlseq:rollupConsiderations');
				newNode = ssNode.appendChild(newNode);
				if (!objForm.measureSatisfactionIfActive.checked) newNode.setAttribute('measureSatisfactionIfActive', 'false');
				if (objForm.requiredForSatisfied.checked) newNode.setAttribute('requiredForSatisfied', objForm.requiredForSatisfiedvalue.value);
				if (objForm.requiredForNotSatisfied.checked) newNode.setAttribute('requiredForNotSatisfied', objForm.requiredForNotSatisfiedvalue.value);
				if (objForm.requiredForCompleted.checked) newNode.setAttribute('requiredForCompleted', objForm.requiredForCompletedvalue.value);
				if (objForm.requiredForIncomplete.checked) newNode.setAttribute('requiredForIncomplete', objForm.requiredForIncompletevalue.value);
			}
						
			// ========== Objectives ==========
			if(objForm.Objectives.checked){
				newNode = isIE ? xmlDoc.createNode(1, 'imsss:Objectives', 'http://www.imsglobal.org/xsd/imsss') :
                                xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:Objectives');
				newNode = ssNode.appendChild(newNode);
				nodes = objForm.Objectives.parentNode.lastChild.childNodes;
				first = true;
				for(var i=0; i<nodes.length; i++){
					if (nodes[i].tagName != 'LI') continue;
					inputs = nodes[i].getElementsByTagName('input');
					if (i && !inputs[0].checked) continue;
					if (first){
						tname = 'imsss:primaryObjective'; first = false;
					}
					else{
						tname = 'imsss:objective';
					}
					newChild = isIE ? xmlDoc.createNode(1, tname, 'http://www.imsglobal.org/xsd/imsss') :
                                    xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', tname);
					newChild = newNode.appendChild(newChild);
					if (inputs[1].value)   newChild.setAttribute('objectiveID', inputs[1].value);
					if (inputs[2].checked) newChild.setAttribute('satisfiedByMeasure', 'true');
					childs = nodes[i].lastChild.childNodes;
					mnm = true;
					for(var j=0; j<childs.length; j++){
						if (childs[j].tagName != 'LI') continue;
						inputs = childs[j].getElementsByTagName('input');
						if (!inputs[0].checked) continue;
						switch(inputs[0].name){
							case 'minNormalizedMeasure':
								if (mnm){
									if (inputs[0].checked){
										newChild2 = isIE ? xmlDoc.createNode(1, 'minNormalizedMeasure', 'http://www.imsglobal.org/xsd/imsss') :
                                                           xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'minNormalizedMeasure');
										newChild2 = newChild.appendChild(newChild2);
										newText = xmlDoc.createTextNode(inputs[1].value);
										newChild2.appendChild(newText);
									}
									mnm = false;
								}
								break;
							case 'mapInfo':
								if (inputs[0].checked){
									newChild2 = isIE ? xmlDoc.createNode(1, 'mapInfo', 'http://www.imsglobal.org/xsd/imsss') :
                                                    xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'mapInfo');
									newChild2 = newChild.appendChild(newChild2);
									newChild2.setAttribute('targetObjectiveID', inputs[1].value);
									if (!inputs[2].checked) newChild2.setAttribute('readSatisfiedStatus', 'false');
									if (!inputs[3].checked) newChild2.setAttribute('readNormalizedMeasure', 'false');
									if (inputs[4].checked)  newChild2.setAttribute('writeSatisfiedStatus', 'true');
									if (inputs[5].checked)  newChild2.setAttribute('writeNormalizedMeasure', 'true');
								}
								break;
						} // End of switch (inputs[0].name)
					} //End of for(childs.length)
			    } // End of for (nodes.length)
			} // End of if (objectives checked)

			// ========== randomizationControls ==========
			if(objForm.randomizationControls.checked){
                newNode = isIE ? xmlDoc.createNode(1, 'imsss:randomizationControls', 'http://www.imsglobal.org/xsd/imsss') :
                                xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:randomizationControls');
				newNode = ssNode.appendChild(newNode);
				
				if (objForm.randomizationTiming.checked) newNode.setAttribute('randomizationTiming', objForm.RandomizationTimingValue.value);
				if (objForm.reorderChildren.checked) newNode.setAttribute('reorderChildren', 'true');
				if (objForm.selectCount.checked) {
					newNode.setAttribute('selectCount', objForm.selectCountValue.value);
					newNode.setAttribute('selectionTiming', objForm.selectionTimingValue.value);
				}							
			}
		
			// ========== deliveryControls ==========
			if(objForm.deliveryControls.checked){
              newNode = isIE ? xmlDoc.createNode(1, 'imsss:deliveryControls', 'http://www.imsglobal.org/xsd/imsss') :
                                 xmlDoc.createElementNS('http://www.imsglobal.org/xsd/imsss', 'imsss:deliveryControls');
				newNode = ssNode.appendChild(newNode);
				if (!objForm.tracked.checked) newNode.setAttribute('tracked', 'false');
				if (objForm.completionSetByContent.checked) newNode.setAttribute('completionSetByContent', 'true');
				if (objForm.objectiveSetByContent.checked) newNode.setAttribute('objectiveSetByContent', 'true');
			} 
					
		
		// Over of parse html form Setup
	}

	// hide SS Setup Dialog
	layerAction('ssSetupPanel', false);
	notSave = true;
}

function childActivitySetChange(obj){
	switch(obj.value){
		case 'atLeastCount':
			obj.nextSibling.style.display = '';
			obj.nextSibling.nextSibling.style.display = 'none';
			break;
		case 'atLeastPercent':
			obj.nextSibling.style.display = 'none';
			obj.nextSibling.nextSibling.style.display = '';
			break;
		default:
			obj.nextSibling.style.display = 'none';
			obj.nextSibling.nextSibling.style.display = 'none';
			break;
	}
}