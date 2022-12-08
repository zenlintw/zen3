<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
?>
<html>
<body>
<script language="javascript">
var disableAllFlag, statusObj;

function InitStatusControlObj(){
	this.changetocStatus = changetocStatus;
	this.changeCurrentBar = changeCurrentBar;
	this.unfold = unfold;
	this.disabletocStatus = disabletocStatus;
	this.hiddentocStatus = hiddentocStatus;
	this.DisplayDisabled = DisplayDisabled;
	this.DisplayHiddenfromchoice = DisplayHiddenfromchoice;
	this.ChangeTreeImage = ChangeTreeImage;
	this.setPreviousButtonDisplay = setPreviousButtonDisplay;
	this.setContinueButtonDisplay = setContinueButtonDisplay;
	this.setExitButtonDisplay = setExitButtonDisplay;
	this.tocMoveS = tocMoveS;
	this.disableChoice = disableChoice;
	this.enableChoice = enableChoice;
	this.disableAllChoice = disableAllChoice;
	this.enableAllChoice = enableAllChoice;
	this.constrainChoice = constrainChoice;
	this.preventActivation_disableTreeChoice = preventActivation_disableTreeChoice;
	this.preventActivation_enableClusterChoice = preventActivation_enableClusterChoice;
	this.choiceExit = choiceExit;
	this.checkChoiceControls = checkChoiceControls;
}

//2004.4.6 Vega: add itemStatus="unknown" or ""
//�ʺA����tree���A Heroin-2003.11.18
//currentIndex:TOC��index ,parentIndex:�W����folder, itemStatus:item���A ,changeCase:��Ƨ��θ`�I(leaf or folder)
function changetocStatus(currentIndex, itemStatus, changeCase) {
    //alert("currentIndex = " + currentIndex );
    var flag = Number(parent.s_catalog.pathtree.tocDisplayList[currentIndex].isvisibleIndex);
    if (parent.s_catalog.pathtree.tocList[currentIndex].isvisible == "true") {
        if (changeCase == "leaf") {
            if (itemStatus == "passed") {
                var p1 = parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
                if (p1.indexOf("passed.gif") != -1) {

                } else {
                    parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/passed.gif";
                }

            } else if (itemStatus == "failed") {
                parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/failed.gif";
            } else if (itemStatus == "completed") {
                parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/completed.gif";
            } else if (itemStatus == "incomplete") {
                parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/incomplete.gif";
            }
            //2004.4.5 Vega:add
            else {
                parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/ftv2doc.gif";
            }
        } else if (changeCase == "folder") { //is folder
            if (itemStatus == "passed") {
                var p1 = parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
                if (p1.indexOf("ftv2folderopen.gif") != -1 || p1.indexOf("ftv2folderclosed.gif") != -1 || p1.indexOf("completed01.gif") != -1 || p1.indexOf("passed01.gif") != -1 || p1.indexOf("completed01_open.gif") != -1 || p1.indexOf("passed01_open.gif") != -1) {
                    if (p1.indexOf("open") != -1) {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/passed01_open.gif";
                    }
                    else {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/passed01.gif";
                    }

                } else {
                    parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/passed.gif";
                }
            } else if (itemStatus == "completed") {

                var p1 = parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
                if (p1.indexOf("passed.gif") != -1 || p1.indexOf("passed01.gif") != -1 || p1.indexOf("passed01_open.gif") != -1) {

                } else if (p1.indexOf("ftv2doc.gif") != -1 || p1.indexOf("completed.gif") != -1) {
                    parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/completed.gif";
                } else {
                    if (p1.indexOf("open") != -1) {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/completed01_open.gif";
                    } else {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/completed01.gif";
                    }

                }
                //2004.4.5 Vega:add
            } else {
                var p1 = parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
                if (p1.indexOf("open") != -1) { //�u��folder�ϥܤ~��open
                    parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/ftv2folderopen.gif";
                } else {
                    //��ܬ�leaf��folder
                    if (p1.indexOf("ftv2doc.gif") != -1 || p1.indexOf("completed.gif") != -1 || p1.indexOf("passed.gif") != -1) {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/ftv2doc.gif";
                    } else {
                        parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/ftv2folderclosed.gif";
                    }
                }
            }
        }
    }
}

//�ʺA���Tree���ثeItem����m Heroin-2003.11.18
function changeCurrentBar(item_index){
    var isvisibleIndex = parent.s_catalog.pathtree.tocDisplayList[Number(item_index)].isvisibleIndex;

    for (var t = 0; t < parent.s_catalog.pathtree.indexOfEntries.length; t++) {
        parent.s_catalog.pathtree.indexOfEntries[t].navObj.className = '';
        parent.s_catalog.pathtree.indexOfEntries[t].navObj.style.color = "";
        parent.s_catalog.pathtree.indexOfEntries[t].navObj.style.backgroundColor = "";
    }
<?php
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
?>
    parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].navObj.className = 'active';
    parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].navObj.style.color = "#000";
    parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].navObj.style.backgroundColor = "#FFF";
<?php
    } else {
?>
    parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].navObj.style.color = "black";
    parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].navObj.style.backgroundColor = "yellow";
<?php
    }
?>
}

//�ʺA�i�}Folder Heroin-2003.11.18
function unfold(item_index, expand){
				
	var isvisibleIndex=parent.s_catalog.pathtree.tocDisplayList[item_index].isvisibleIndex;
	if(parent.s_catalog.pathtree.tocList[item_index].folderIsvisible=="true" && parent.s_catalog.pathtree.tocDisplayList[item_index].DisplayItemType=="folder"){
		//var state="";
		//alert("157 title = "+ parent.s_catalog.pathtree.tocList[item_index].title + " itemType =  " + parent.s_catalog.pathtree.tocList[item_index].itemType +"isvisibleIndex="+isvisibleIndex);

		//parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].setState(!state);
		if(typeof(expand) == 'undefined') expand = true;
		parent.s_catalog.pathtree.indexOfEntries[isvisibleIndex].setState(expand);
	}
}

function disabletocStatus(item_index){
	var temp_index = Number(item_index);
	var flag=Number(parent.s_catalog.pathtree.tocDisplayList[temp_index].isvisibleIndex);
	var t1=eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"'].innerHTML");
	
	//���P�_�O���Ofolder ---> check���S��javascript:clickOnNode
	var tempitemType = "";
	
	if(t1.indexOf("javascript:clickOnNode")==-1){
		tempitemType = "leaf";
	}else{
		tempitemType = "folder";
	}
	
	//alert(tempitemType);
	
	var position1 = 0;
	var position2 = 0;
	var position3 = 0;
	
	var t2="";
	if(tempitemType=="leaf"){
		var t2 = eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML");
		//alert(t2);
		if(t2.indexOf("<A href=")>0){
			position1 = t2.indexOf("href=");
			position2= t2.indexOf("target=s_main");
			position3 = t2.indexOf("</A>");
			t2 = t2.slice(0,position1-3) + t2.slice(position2+14,position3) + t2.slice(position3+4);
			
		}
		parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/disabled.gif";
		//���ܦr����
		t2 = t2.replace("<FONT size=2>","<FONT size=2 color=gray>");
	 	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML=t2");
		
		
	}else{
		
		var t2 = eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML");
		if(t2.indexOf("href=")>0){
			position1 = t2.indexOf("onclick=");
			position2 = t2.indexOf("target=s_main");
			position3 = t2.indexOf("</A>");
			t2 = t2.slice(0,position1-3) + t2.slice(position2+14,position3) + t2.slice(position3+4);
			
		
		}
		
		var p1=parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
				
		if(p1.indexOf("ftv2doc.gif")!=-1 || p1.indexOf("passed.gif")!=-1 || p1.indexOf("completed.gif")!=-1 || p1.indexOf("failed.gif")!=-1 || p1.indexOf("disabled.gif")!=-1){
					
			parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/disabled.gif";
		}
		else{
			if(p1.indexOf("open")!=-1){
				parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/disabled01_open.gif";
			}
			else{
				parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src = "/learn/scorm/toc/disabled01.gif";
			}
			
		}
		t2 = t2.replace("<FONT size=2>","<FONT size=2 color=gray>");
	 	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML=t2");
		
		
	}

}

function hiddentocStatus(item_index){

	var temp_index = Number(item_index);
	//alert("hiddentocStatus = " + parent.s_catalog.pathtree.tocList[temp_index].id);
	var flag=Number(parent.s_catalog.pathtree.tocDisplayList[temp_index].isvisibleIndex);
	
	eval("parent.s_catalog.pathtree.document.all['_s"+(flag)+"'].style.display='none'");
	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"'].style.display='none'");
	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].style.display='none'");
}


//disabled tree Heroin-2003.12.08
//Heroin 2004.04.30
function DisplayDisabled(item_index){	
	
	var return_index=item_index;
	//1.�p�G�Oleafe ����disable
	if(parent.s_catalog.pathtree.tocList[item_index].idref!=""){
		//2003.12.08-Heroin
		parent.s_catalog.pathtree.tocList[item_index].disable = "true";
		enableChoice(item_index);
		disabletocStatus(item_index);
		return return_index;
	}
	
	
	//2.�p�G�Ofolder disable�ۤv�M�Ҧ���children
	else{
		var parent_Index=tocIDfindIndex(parent.s_catalog.pathtree.tocList[item_index].parentID);
		enableChoice(item_index);		
		disabletocStatus(item_index);
		
		for(var i=item_index+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
			//Heroin-2003.12.30
			//if(parent.s_catalog.pathtree.tocList[i].parentID!=parent.s_catalog.pathtree.tocList[item_index].parentID && parent.s_catalog.pathtree.tocList[i].parentID!=parent.s_catalog.pathtree.tocList[parent_Index].parentID){
			var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
			if(Number(parentIndex)>= Number(item_index)){
				parent.s_catalog.pathtree.tocList[i].disable = "true";
				if(parent.s_catalog.pathtree.tocList[i].isvisible=="true"){
					//Heroin 2004.04.30
					enableChoice(i);
					disabletocStatus(i);
				}
				return_index=i;
				//alert(parent.s_catalog.pathtree.tocList[i].id);
			}
			else{
				//�qi�~�򩹤U�ˬd				
				return return_index;
				break;				
			}
		}		
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

function displayIDfindIsvisibleIndex(tempitemID){
	var i;
	var flag=0;
	for(i=0;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
		if(parent.s_catalog.pathtree.tocDisplayList[i].id==tempitemID){
			flag = parent.s_catalog.pathtree.tocDisplayList[i].isvisibleIndex;
			break;
		}
	}
	return flag;
}


function displayIDfindIndex(tempitemID){
	var i;
	var flag=0;
	for(i=0;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
		if(parent.s_catalog.pathtree.tocDisplayList[i].id==tempitemID){
			flag = i;
			break;
		}
	}
	return flag;
}


function displayIsvisiblefindID(tempIsivisbleIndex){
	var i;
	var flag=0;
	for(i=0;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
		if(parent.s_catalog.pathtree.tocDisplayList[i].isvisibleIndex==tempIsivisbleIndex && parent.s_catalog.pathtree.tocDisplayList[i].isShow=="true"){
			flag = parent.s_catalog.pathtree.tocDisplayList[i].id;
			break;
		}
	}
	return flag;
}

function DisplayHiddenfromchoice(itemInedx){
	//alert("hidden itemInedx="+itemInedx+" parentId="+parent.s_catalog.pathtree.tocList[itemInedx].parentID);
	var return_index=itemInedx;
	//1.�p�G�Oleafe ����hidden
	if(parent.s_catalog.pathtree.tocList[itemInedx].idref!=""){
		parent.s_catalog.pathtree.isHiddenFromChoiceList[itemInedx].value = "true";
		hiddentocStatus(itemInedx);
		return return_index;
	}
	
		
	//2.�p�G�Ofolder hidden�ۤv�M�Ҧ���children
	
	else{	
		
		var parent_Index=tocIDfindIndex(parent.s_catalog.pathtree.tocList[itemInedx].parentID);
		parent.s_catalog.pathtree.isHiddenFromChoiceList[itemInedx].value = "true";
		hiddentocStatus(itemInedx);
		for(var i=itemInedx+1;i<parent.s_catalog.pathtree.tocList.length;i++){
			//�P�_�O�_�O�l�]...
			//if(parent.s_catalog.pathtree.tocList[i].parentID!=parent.s_catalog.pathtree.tocList[itemInedx].parentID && parent.s_catalog.pathtree.tocList[i].parentID!=parent.s_catalog.pathtree.tocList[parent_Index].parentID){
			//Heroin-2003.12.30
			var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
			if(Number(parentIndex)>= Number(itemInedx)){
				parent.s_catalog.pathtree.isHiddenFromChoiceList[i].value = "true";
				if(parent.s_catalog.pathtree.tocList[i].isvisible=="true"){
					hiddentocStatus(i);					
				}
				return_index=i;
			}
			else{
				//�qi�~�򩹤U�ˬd
				return return_index;
				break;				
			}
		}
	}
	
}

function ChangeTreeImage(flag){
	
	//flag�Ƕi�Ӫ��OtocList��Index�A�������N�o��Index�ഫ��DisplayList��Index
	//�P�ɥ����n�����ثe�ǤJ��Index
	
	//ThisIndex
	var ThisIndex = parent.s_catalog.pathtree.tocDisplayList[Number(flag)].isvisibleIndex; //displayIDfindIndex(parent.s_catalog.pathtree.tocList[Number(flag)].id); //Number(flag);
	var ThisID = parent.s_catalog.pathtree.tocDisplayList[Number(flag)].id;
	var ThisParentID = parent.s_catalog.pathtree.tocDisplayList[Number(flag)].DisplayparentID;
	var ThisParentIndex = displayIDfindIsvisibleIndex(ThisParentID);
	var i=0;
	
	//StartIndex
	
	var t1="";
	
	
	//����previous sibling
	var PSIndex=-1;
	var PSID = "";
	for(i=Number(flag)-1;i>0;i--){
	    //�p�G�ݱo��~�i�ˬd��parentID�O���O�ۦP
	       //alert(parent.s_catalog.pathtree.tocList[i].title + " /  isShow = " + parent.s_catalog.pathtree.tocDisplayList[i].isShow + " / ThisParentID = " + ThisParentID + " / DisplayparentID = " + parent.s_catalog.pathtree.tocDisplayList[i].DisplayparentID);

	    if(parent.s_catalog.pathtree.tocDisplayList[i].isShow=="true"){	

		if(parent.s_catalog.pathtree.tocDisplayList[i].DisplayparentID==ThisParentID){
			//alert("found Prevous Sibling");
			PSIndex = parent.s_catalog.pathtree.tocDisplayList[i].isvisibleIndex;
			PSID = 	parent.s_catalog.pathtree.tocDisplayList[i].id;
			break;
		}
	    }	
		
	}
	
	
	var NSIndex = -1;
	var NSID = "";
	//��next sibling
	
	for(i=Number(flag)+1;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
	    if(parent.s_catalog.pathtree.tocDisplayList[i].isShow == "true"){
		if(parent.s_catalog.pathtree.tocDisplayList[i].DisplayparentID==ThisParentID){
			NSIndex = parent.s_catalog.pathtree.tocDisplayList[i].isvisibleIndex;	
			NSID = parent.s_catalog.pathtree.tocDisplayList[i].id;
			break;
		}
	    }
	}	
	

	//alert("PS = " + PSIndex + " / This = " + ThisIndex + " / NS = " + NSIndex);


	if(PSIndex==-1 && NSIndex==-1){
	
	}else if(PSIndex==-1 && NSIndex !=-1){
	
	}else if(PSIndex !=-1 && NSIndex==-1){
		if((ThisIndex-PSIndex)>1){
			for(i=PSIndex;i<ThisIndex;i++){
				
				//i�OisvisibleIndex
				//alert("before get innerHTML");
				var tempStyle = eval("parent.s_catalog.pathtree.document.all['_s"+(i)+"'].style.display");
				//alert("execute~~~~");
				if(tempStyle!="none"){
					t1 = eval("parent.s_catalog.pathtree.document.all['_s"+(i)+"'].innerHTML");
					if(i==PSIndex){
						//alert("i=" + i);
						if(t1.indexOf("ftv2mnode.gif")!=-1){
							t1 = t1.replace("ftv2mnode.gif","ftv2mlastnode.gif");
						}
						if(t1.indexOf("ftv2pnode.gif")!=-1){
							t1 = t1.replace("ftv2pnode.gif","ftv2plastnode.gif");
						}
						if(t1.indexOf("ftv2node.gif")!=-1){
							t1 = t1.replace("ftv2node.gif","ftv2lastnode.gif");
						}
					}
					else{
						if(t1.indexOf("ftv2vertline.gif")!=-1){
							//�ncheck�n�������@�����u
							//var level = Number(checkLevel(PSIndex,i));
							var tempID = displayIsvisiblefindID(i);
							//alert(tempID + " / " +  parent.s_catalog.pathtree.tocList[tocIDfindIndex(tempID)].id);
							var level = Number(checkLevel(PSID,tempID));
							
							//alert("level = " + level);
							//level=1 �N��Oparent-child...�ҥH�n���˼ƲĤ@�����u
							//�P�zlevel=2 �Ogrand parent-child�ҭn���˼ƲĤG�����u
							//alert(replaceVline(t1,level));
							//alert("replaceVline");
							t1 = replaceVline(t1,level);
							//alert("t1=" + t1);
						
						}
					}
				}
				eval("parent.s_catalog.pathtree.document.all['_s"+(i)+"'].innerHTML=t1");
				//alert("eval done!");
			
			}
		}else{
			//��PSIndex����...��Ϭ�innerHTML���˼Ʋ�1��IMG
			t1 = eval("parent.s_catalog.pathtree.document.all['_s"+(PSIndex)+"'].innerHTML");
			if(t1.indexOf("ftv2node.gif")!=-1){
				t1 = t1.replace("ftv2node.gif","ftv2lastnode.gif");
				eval("parent.s_catalog.pathtree.document.all['_s"+(PSIndex)+"'].innerHTML=t1");
			}
		}
		
		
	}else if(PSIndex !=-1 && NSIndex!=-1){
		//middle node
		//alert("middle node = " + parent.s_catalog.pathtree.tocList[Number(tocIDfindIndex(ThisParentID))].title + " / " + parent.s_catalog.pathtree.tocList[Number(tocIDfindIndex(ThisID))].title);
		//����
	}
	
	//step �����ثe��m
	
	//step ���s�����覡
	
	//step ��v�T�d��
	
	

}


function setPreviousButtonDisplay(strStatus){
	parent.s_catalog.setButtonDisplay('Previous', strStatus);
	return;
	
	if(strStatus == "show"){
		eval("parent.control.document.all['PreviousButtonLayer'].style.visibility='hidden'");
		eval("parent.mid.document.all['PreviousButtonLayer'].style.visibility='hidden'");
	}else{
		eval("parent.control.document.all['PreviousButtonLayer'].style.visibility=''");
		eval("parent.mid.document.all['PreviousButtonLayer'].style.visibility=''");
	}

}

function setContinueButtonDisplay(strStatus){
	parent.s_catalog.setButtonDisplay('Continue', strStatus);
	return;

	if(strStatus == "show"){
		eval("parent.control.document.all['NextButtonLayer'].style.visibility='hidden'");
		eval("parent.mid.document.all['NextButtonLayer'].style.visibility='hidden'");
	}else{
		eval("parent.control.document.all['NextButtonLayer'].style.visibility=''");	
		eval("parent.mid.document.all['NextButtonLayer'].style.visibility=''");	
	}

}


function setExitButtonDisplay(strStatus){
return;
	if(strStatus == "show"){
		eval("parent.control.document.all['ExitButtonLayer'].style.visibility='hidden'");
	}else{
		eval("parent.control.document.all['ExitButtonLayer'].style.visibility=''");	
	}

}

function checkLevel(pID,cID){

	var parentID = pID;
	var childID = cID;
	
	var parentIndex =Number(displayIDfindIndex(pID));
	var childIndex = Number(displayIDfindIndex(cID));
	
	var level = 0;
	var i=0;
	var levelFound = false;
	while(!levelFound){
		level++;
		if(parentID==parent.s_catalog.pathtree.tocDisplayList[childIndex].DisplayparentID){
			levelFound = true;
		}else{
			childIndex = displayIDfindIndex(parent.s_catalog.pathtree.tocDisplayList[childIndex].DisplayparentID);
			//���ӭn��isShow = true��Index,���L�]���Ĥ@��item���Otrue
			
		}
	}
	
	return level;
	
}



function replaceVline(strHTML,level){
	var tempArray = new Array();
	var tempArrayIndex = 0;
	var tempPosition = 0;
	var previousPosition = -1;
	//alert(strHTML);
	//alert("lalala1");

	while(strHTML.indexOf("src",tempPosition)!=-1){
		
		tempArray[tempArrayIndex] = strHTML.indexOf("src",tempPosition);
		//alert(tempArray[tempArrayIndex]);
		tempPosition = Number(tempArray[tempArrayIndex]+30); //30�u�Oguess�̪����r��
		tempArrayIndex++;
			
	} 
	
	//alert("lalala2");
	
	
	
	var position1 = 0;
	var position2 = 0;
	position1 = tempArray[Number(tempArray.length-level-1)]+5; //�n��h�ۤv�@�h..
	position2 = Number(strHTML.indexOf(".gif",position1+1))+4;
	
	
	var str1 = "";
	var str2 = "";
	str1 = strHTML.slice(0,position1);
	str2 = strHTML.slice(position2);
	
	var strResult = str1 + "/learn/scorm/toc/ftv2blank.gif" + str2;
	
	return strResult;


}

//�]�wtoc���ʤ�V
var tocDisplay = "<?=$_GET['tocDisplay']?>";
var tocInitial = "<?=$_GET['tocInitial']?>"
var sType;

var sType = "";
var iTime=1;     // �w�]���ʶ��j�ɶ�
var iMove=1;      // �w�]���ʶ��j�I��
var gMove=4;      // �w�]���ʶ��j�[���I��
var mPoint=0;     // ���ʶ��j�I��(���ݳ]�w)
var timerID=0;    // �w�]setInterval��ƽs��(���ݳ]�w)

var cols1=200;    // ��frame�e��,�ݰt�X��]�w
var cols2=18;     // ��frame�e��,�ݰt�X��]�w
var cols3="*";    // �kframe�e��,�ݰt�X��]�w

var rows1=80;
var rows2="*";
var rows3=61;


//�]�w�}�X��V Vega  2004.3.1 modified

function tocMoveS(){
	 //�w�]�_�l���ئ�m(���k) , Right or Left

	if(tocDisplay="open"){ 
	  spStr=parent.middleFrame.cols.split(",",1);
	  colLen=parseInt(spStr);
      var tempSting = "";
	  if(Number(colLen)==0){
		sType = "Right";
      }else{
		sType = "Left";
	  }		

	  mPoint=iMove;
	  if (sType=="Right"){ 
		 sType="Left";
	  }else{
		 sType="Right";
      }
	  timerID=setInterval("goMove()",iTime);
	} 	
}


//���ʩԪ� ('blue'�����ئW�ٻݦۭq)

function goMove(){   
    spStr=parent.middleFrame.cols.split(",",1);
    colLen=parseInt(spStr);

    if (sType=="Right"){
       if (colLen > 0)
          parent.middleFrame.cols=(colLen-mPoint) + "," + cols2 + "," + cols3 ;
       else{
          parent.middleFrame.cols="0," + cols2 + "," + cols3 ; 	
		  clearInterval(timerID);
        }

    }else {
       if (colLen < cols1-mPoint)
          parent.middleFrame.cols=(colLen+mPoint) + "," + cols2 + "," + cols3 ;
       else{
          parent.middleFrame.cols=cols1 + "," + cols2 + "," + cols3 ;
			clearInterval(timerID);
        }

     }

	mPoint = mPoint + gMove;
}

function disableChoice(item_index){
	var temp_index = Number(item_index);
	var flag=Number(parent.s_catalog.pathtree.tocDisplayList[temp_index].isvisibleIndex);
	var t1=eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"'].innerHTML");
	
	//���P�_�O���Ofolder ---> check���S��javascript:clickOnNode
	var tempitemType = "";
	//alert("in disableChoice   "+ flag +"  isChoice="+parent.s_catalog.pathtree.tocDisplayList[flag].isChoice);
	
	if(parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice.toString()=="true" && parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice!="NotAvailable"){
		if(t1.indexOf("javascript:clickOnNode")==-1){
			tempitemType = "leaf";
		}else{
			tempitemType = "folder";
		}

		var position1 = 0;
		var position2 = 0;
		var position3 = 0;
		
		var t2="";
		if(tempitemType=="leaf"){
			var t2 = eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML");
			
			//�n�����ª�innerHTML��buffer�_�Ӥ���~��enable
			parent.s_catalog.pathtree.tocDisplayList[temp_index].buffer = t2;

			if(t2.indexOf("<A href=")>0){
				position1 = t2.indexOf("href=");
				position2= t2.indexOf("target=s_main");
				position3 = t2.indexOf("</A>");
				t2 = t2.slice(0,position1-3) + t2.slice(position2+14,position3) + t2.slice(position3+4);
				
			}

		 	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML=t2");
			parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice = "NotAvailable";
			
		}else{
			
			
			var t2 = eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML");
	
			//�n�����ª�innerHTML��buffer�_�Ӥ���~��enable
			parent.s_catalog.pathtree.tocDisplayList[temp_index].buffer = t2;
	
	
	
			if(t2.indexOf("href=")>0){
				position1 = t2.indexOf("onclick=");
				position2 = t2.indexOf("target=s_main");
				position3 = t2.indexOf("</A>");
				t2 = t2.slice(0,position1-3) + t2.slice(position2+14,position3) + t2.slice(position3+4);
				
			
			}
			
			var p1=parent.s_catalog.pathtree.indexOfEntries[Number(flag)].iconImg.src;
			
		 	
		 	eval("parent.s_catalog.pathtree.document.all['s"+(flag)+"_'].innerHTML=t2");
			parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice = "NotAvailable";
			
		}
	}
}

function enableChoice(item_index){
	var temp_index = Number(item_index);
	var isvisibleIndex = parent.s_catalog.pathtree.tocDisplayList[temp_index].isvisibleIndex;
	//alert("bla" + item_index +  " / " +  isvisibleIndex + " / " + parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice);
	
	if(parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice=="NotAvailable"){
		if(parent.s_catalog.pathtree.tocDisplayList[temp_index].buffer!=""){
			eval("parent.s_catalog.pathtree.document.all['s"+(isvisibleIndex)+"_'].innerHTML = parent.s_catalog.pathtree.tocDisplayList[temp_index].buffer");
			parent.s_catalog.pathtree.tocDisplayList[temp_index].isChoice = true;
			parent.s_catalog.pathtree.tocDisplayList[temp_index].buffer = "";
		}
	}

}

function disableAllChoice(){

	var i = 0;
	var checkNum = 0;
	
	disableAllFlag="true";
	//alert("disableAllChoice  disableAllFlag="+disableAllFlag);
	for(i=0;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
		
		disableChoice(i);			
	}
	

}

function enableAllChoice(){

	var i = 0;
	for(i=0;i<parent.s_catalog.pathtree.tocDisplayList.length;i++){
		enableChoice(i);
	}
	
}

function constrainChoice(item_index){
	disableAllChoice();	
	
	var itemID = parent.s_catalog.pathtree.tocList[Number(item_index)].id;
	var parentID = parent.s_catalog.pathtree.tocList[Number(item_index)].parentID;
	var i = 0 ;
	
	//��children��choice���}
	
	//var return_index=item_index;
	enableChoice(item_index);
	for(i=item_index+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
		var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
		if(Number(parentIndex)>= Number(item_index)){
			enableChoice(i);
				
		}else{
			//�qi�~�򩹤U�ˬd				
			//return return_index;
			break;				
		}
	}	


	//���}previous sibling tree ��choice
	var previousSiblingFound = false;
	var previousSiblingIndex = -1;

	for(i=Number(item_index)-1;i>0;i--){
		if(!previousSiblingFound){
			if(parent.s_catalog.pathtree.tocList[i].isvisible.toString()=="true" && parent.s_catalog.pathtree.isHiddenFromChoiceList[i].value.toString()!="true"){				
				if(parent.s_catalog.pathtree.tocList[i].parentID==parentID){
					previousSiblingFound = true;
					previousSiblingIndex = i;
					break;
				}	
			}
		}
	}

	if(previousSiblingFound){
		enableChoice(previousSiblingIndex);
		for(i=previousSiblingIndex+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
			var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
			//alert("parentIndex = " + parentIndex + " / previousSiblingIndex = " + previousSiblingIndex);
			if(Number(parentIndex)>= Number(previousSiblingIndex)){
				
				enableChoice(i);
					
			}else{
				//�qi�~�򩹤U�ˬd				
				//return return_index;
				break;				
			}
		}
	}

	//���}next sibling tree��choice
	var nextSiblingFound = false;
	var nextSiblingIndex = -1;

	for(i=Number(item_index)+1;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(!nextSiblingFound){
			if(parent.s_catalog.pathtree.tocList[i].isvisible.toString()=="true" && parent.s_catalog.pathtree.isHiddenFromChoiceList[i].value.toString()!="true"){				
				if(parent.s_catalog.pathtree.tocList[i].parentID==parentID){
					nextSiblingFound = true;
					nextSiblingIndex = i;
					break;
				}	
			}
		}
	}	

	if(nextSiblingFound){
		enableChoice(nextSiblingIndex);
		for(i=nextSiblingIndex+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
			var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
			if(Number(parentIndex)>= Number(nextSiblingIndex)){
				enableChoice(i);
					
			}else{
				//�qi�~�򩹤U�ˬd				
				//return return_index;
				break;				
			}
		}
	}
	
}

function preventActivation_disableTreeChoice(item_index){

	//��parent���]�wpreventActivation��children��disable choice
	//���ۤv�n���n��choice�����O?
	
	var i=0;
	for(i=item_index+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
		var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
		if(Number(parentIndex)>= Number(item_index)){
			disableChoice(i);
				
		}else{
			//�qi�~�򩹤U�ˬd				
			//return return_index;
			break;				
		}
	}


}


function preventActivation_enableClusterChoice(item_index){
	
	var parentID = parent.s_catalog.pathtree.tocList[Number(item_index)].id;	
	var i=0;
	for(i=0;i<parent.s_catalog.pathtree.tocList.length;i++){
		if(parent.s_catalog.pathtree.tocList[i].isvisible.toString()=="true" && parent.s_catalog.pathtree.isHiddenFromChoiceList[i].value.toString()!="true"){				
			if(parent.s_catalog.pathtree.tocList[i].parentID==parentID){
				enableChoice(i);
			
			}	
		}
	}


}

function choiceExit(item_index){
	
	//����Ҧ���choice����disable��A�N�ۤv��tree���enable
	//if(disableAllFlag=="false"){
		disableAllChoice();	
	//}
	var i=0;
	for(i=item_index+1;i<parent.s_catalog.pathtree.tocList.length;i++){		
		var parentIndex=Number(tocIDfindIndex(parent.s_catalog.pathtree.tocList[i].parentID));
		if(Number(parentIndex)>= Number(item_index)){
			enableChoice(i);
				
		}else{
			//�qi�~�򩹤U�ˬd				
			//return return_index;
			break;				
		}
	}

}

//Heroin 2004.04.08
function checkChoiceControls(item_index){
	var choiceExitResult="false";
	var preventActivationResult="false";
	var constrainChoiceResult="false";
	disableAllFlag="false";
	
	//1.choiceExit
	
	if(parent.s_catalog.pathtree.controlModeList[item_index].choiceExit.toString()=="false"){
		//alert(item_index+"  $��choiceExit$");
		//��choiceExit
		choiceExitResult="true";
		choiceExit(item_index);
	}
	
	if(parent.s_catalog.pathtree.constrainedChoiceConsiderationsList[item_index].existflag.toString()=="true"){
		//3.constrainChoice
		if(parent.s_catalog.pathtree.constrainedChoiceConsiderationsList[item_index].constrainChoice.toString()=="true"){
			constrainChoiceResult="true";
			if(choiceExitResult=="false"){
				constrainChoice(item_index);
			}
		}
		//2.preventActivation 
		if(parent.s_catalog.pathtree.constrainedChoiceConsiderationsList[item_index].preventActivation.toString()=="true"){
			preventActivationResult="true";
			preventActivation_disableTreeChoice(item_index);
			preventActivation_enableClusterChoice(item_index);
			
		}
		
	}
}


function init_status_control() {
	disableAllFlag="false";
	statusObj = new InitStatusControlObj();	
}

</script>

</body>
</html>
