//**************************************************************** 
// You are free to copy the "Folder-Tree" script as long as you  
// keep this copyright notice: 
// Script found in: http://www.geocities.com/Paris/LeftBank/2178/ 
// Author: Marcelino Alves Martins (martins@hks.com) December '97. 
//**************************************************************** 

//Log of changes: 
//       17 Feb 98 - Fix initialization flashing problem with Netscape
//       
//       27 Jan 98 - Root folder starts open; support for USETEXTLINKS; 
//                   make the ftien4 a js file 
//       


// Definition of class Folder 
// ***************************************************************** 

function stripTags(str) {
    return str.replace(/<[^>]+>/g, '').replace(/<(\w+)( [^>]*)?>([^<]*)<\/\\1>/ig, '$3');
}

function Folder(folderDescription, hreference) //constructor 
{
    //constant data
    this.desc = folderDescription;
    this.hreference = hreference;
    this.id = -1;
    this.navObj = 0;
    this.iconImg = 0;
    this.nodeImg = 0;
    this.isLastNode = 0;

    //dynamic data
    this.isOpen = true;
    this.iconSrc = "/learn/scorm/toc/ftv2folderopen.gif";
    this.children = new Array;
    this.nChildren = 0;

    //methods
    this.initialize = initializeFolder;
    this.setState = setStateFolder;
    this.addChild = addChild;
    this.createIndex = createEntryIndex;
    this.hide = hideFolder;
    this.display = display;
    this.renderOb = drawFolder;
    this.totalHeight = totalHeight;
    this.subEntries = folderSubEntries;
    this.outputLink = outputFolderLink;
}

function setStateFolder(isOpen) {
    var subEntries;
    var totalHeight;
    var fIt = 0;
    var i = 0;

    if (isOpen == this.isOpen)
        return;

    if (browserVersion == 2) {
        totalHeight = 0;
        for (i = 0; i < this.nChildren; i++)
            totalHeight = totalHeight + this.children[i].navObj.clip.height;
        subEntries = this.subEntries();
        if (this.isOpen)
            totalHeight = 0 - totalHeight;
        for (fIt = this.id + subEntries + 1; fIt < nEntries; fIt++)
            indexOfEntries[fIt].navObj.moveBy(0, totalHeight);
    }
    this.isOpen = isOpen;
    propagateChangesInState(this);
}


function propagateChangesInState(folder) {
    var i = 0;
    //alert(folder.nodeImg.src);
    if (folder.isOpen) {
        if (folder.nodeImg)
            if (folder.isLastNode)
                folder.nodeImg.src = "/learn/scorm/toc/ftv2mlastnode.gif";
            else
                folder.nodeImg.src = "/learn/scorm/toc/ftv2mnode.gif";

        if (folder.iconImg.src.indexOf("/learn/scorm/toc/ftv2folderclosed.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/ftv2folderopen.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/passed01.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/passed01_open.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/completed01.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/completed01_open.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/disabled01.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/disabled01_open.gif";
        }

        for (i = 0; i < folder.nChildren; i++)
            folder.children[i].display();
    }
    else {
        if (folder.nodeImg)
            if (folder.isLastNode)
                folder.nodeImg.src = "/learn/scorm/toc/ftv2plastnode.gif";
            else
                folder.nodeImg.src = "/learn/scorm/toc/ftv2pnode.gif";

        if (folder.iconImg.src.indexOf("/learn/scorm/toc/ftv2folderopen.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/ftv2folderclosed.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/passed01_open.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/passed01.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/completed01_open.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/completed01.gif";
        } else if (folder.iconImg.src.indexOf("/learn/scorm/toc/disabled01_open.gif") != -1) {
            folder.iconImg.src = "/learn/scorm/toc/disabled01.gif";
        }
        for (i = 0; i < folder.nChildren; i++) {
            folder.children[i].hide();
        }
    }

    replaceChangesInState(folder);

}


function replaceChangesInState(folder) {
    var t1 = eval("document.all['s" + (folder.id) + "'].innerHTML");
}

function hideFolder() {
    if (browserVersion == 2) {
        if (this.navObj.visibility == "hidden")
            return;
        this.navObj.visibility = "hidden";
    } else {
        if (this.navObj.style.display == "none")
            return;
        this.navObj.style.display = "none";
    }

    this.setState(0)
}

function initializeFolder(level, lastNode, leftSide) {
    var j = 0;
    var i = 0;
    var numberOfFolders;
    var numberOfDocs;
    var nc;

    nc = this.nChildren;

    this.createIndex();

    var auxEv = "";

    if (browserVersion > 0)
        auxEv = "<a href='javascript:clickOnNode(" + this.id + ")'>";
    else
        auxEv = "<a>";

    if (level > 0)
        if (lastNode) //the last 'brother' in the children array
        {
            this.renderOb(leftSide + auxEv + "<img align=absmiddle name='nodeIcon" + this.id + "' src='/learn/scorm/toc/ftv2mlastnode.gif' width=16 height=22 border=0></a>");
            leftSide = leftSide + "<img align=absmiddle  src='/learn/scorm/toc/ftv2blank.gif' width=16 height=22>";
            this.isLastNode = 1;
        } else {
            this.renderOb(leftSide + auxEv + "<img  align=absmiddle name='nodeIcon" + this.id + "' src='/learn/scorm/toc/ftv2mnode.gif' width=16 height=22 border=0></a>");
            leftSide = leftSide + "<img  align=absmiddle src='/learn/scorm/toc/ftv2vertline.gif' width=16 height=22>";
            this.isLastNode = 0;
        }
    else
        this.renderOb("");


    if (nc > 0) {
        level = level + 1;
        for (i = 0; i < this.nChildren; i++) {
            if (i == this.nChildren - 1)
                this.children[i].initialize(level, 1, leftSide);
            else
                this.children[i].initialize(level, 0, leftSide);
        }
    }
}

function drawFolder(leftSide) {
    if (browserVersion == 2) {
        if (!doc.yPos) {
            doc.yPos = 8;
        }
        doc.write("<layer id='folder" + this.id + "' top=" + doc.yPos + " visibility=hiden>");
    }

    doc.write("<table ");
//    if (browserVersion == 1) {
        doc.write(" id='folder" + this.id + "' style='position:block;' ");
//    }
    doc.write(" border=0 cellspacing=0 cellpadding=0>");
    doc.write("<tr><td nowrap>");
    if (this.id == 0) {
        //doc.write(leftSide)
        doc.write("<div align=absmiddle id='_s" + this.id + "'>");
        doc.write(leftSide);
        doc.write("</div>");
        doc.write("</td>");
        doc.write("<td nowrap>");

        doc.write("<div id='s" + this.id + "'>");
        //this.outputLink()
        doc.write("<a href='javascript:clickOnNode(" + this.id + ")'>");
        doc.write("<img align=absmiddle name='folderIcon" + this.id + "' id='folderIcon" + this.id + "' ");
        doc.write("src='" + this.iconSrc + "' border=0></a>");
    } else {
        //alert(this.id);
        doc.write("<div align=absmiddle id='_s" + this.id + "'>");
        doc.write(leftSide);
        doc.write("</div>");
        doc.write("</td>");
        doc.write("<td nowrap>");

        //doc.write("<div id='s" + this.id + "'>" +leftSide)
        doc.write("<div id='s" + this.id + "'>");
        //this.outputLink()
        doc.write("<a href='javascript:clickOnNode(" + this.id + ")'>");
        doc.write("<img align='absmiddle' name='folderIcon" + this.id + "' id='folderIcon" + this.id + "' ");
        doc.write("src='" + this.iconSrc + "' border=0></a>");
        //doc.write("</div>")
    }
    doc.write("</div>");
    doc.write("</td><td class='caption' valign=middle nowrap>");
    //if (USETEXTLINKS)
    doc.write("<div id='s" + this.id + "_'>");
    if (this.link != "") {
        this.outputLink();
        doc.write("<font size='2'>" + this.desc + "</a>");
    } else {
        doc.write("<font size='2'>" + this.desc);
    }
    //if(this.id!=0){
    doc.write("</div>");
    //}
    doc.write("</td>");
    doc.write("</tr>");

    doc.write("</table>");

    if (browserVersion == 2) {
        doc.write("</layer>")
    }

    if (browserVersion == 1) {
        this.navObj = doc.all["folder" + this.id];
        this.iconImg = doc.all["folderIcon" + this.id];
        this.nodeImg = doc.all["nodeIcon" + this.id];
    } else if (browserVersion == 2) {
        this.navObj = doc.layers["folder" + this.id];
        this.iconImg = this.navObj.document.images["folderIcon" + this.id];
        this.nodeImg = this.navObj.document.images["nodeIcon" + this.id];
        doc.yPos = doc.yPos + this.navObj.clip.height
    } else {
        this.navObj = doc.getElementById("folder" + this.id);
        this.iconImg = doc.getElementById("folderIcon" + this.id);
        this.nodeImg = doc.getElementById("nodeIcon" + this.id);
    }
}

function outputFolderLink() {
    if (this.hreference) {
        doc.write("<font size='2'><a href='" + this.hreference + "' TARGET=\"s_main\" title='" + stripTags(this.desc) + "'");
        if (browserVersion > 0) {
            doc.write("onClick='javascript:clickOnFolder(" + this.id + ")'");
        }
        doc.write(">");
    }
    else {
        doc.write("<a>");
    }
//  doc.write("<font size='2'><a href='javascript:clickOnFolder("+this.id+")'>")   
}

function addChild(childNode) {
    this.children[this.nChildren] = childNode;
    this.nChildren++;
    return childNode;
}

function folderSubEntries() {
    var i = 0;
    var se = this.nChildren;

    for (i = 0; i < this.nChildren; i++) {
        if (this.childNodes[i].childNodes) {//is a folder
            se = se + this.childNodes[i].subEntries();
        }
    }

    return se;
}


// Definition of class Item (a document or link inside a Folder) 
// ************************************************************* 

function Item(itemDescription, itemLink) // Constructor 
{
    // constant data
    this.desc = itemDescription;
    this.link = itemLink;
    this.id = -1; //initialized in initalize()
    this.navObj = 0; //initialized in render()
    this.iconImg = 0; //initialized in render()
    this.iconSrc = "/learn/scorm/toc/ftv2doc.gif";

    // methods
    this.initialize = initializeItem;
    this.createIndex = createEntryIndex;
    this.hide = hideItem;
    this.display = display;
    this.renderOb = drawItem;
    this.totalHeight = totalHeight;
}

function hideItem() {
    if (browserVersion == 2) {
        if (this.navObj.visibility == "hiden")
            return;
        this.navObj.visibility = "hiden";
    } else {
        if (this.navObj.style.display == "none")
            return;
        this.navObj.style.display = "none";
    }
}

function initializeItem(level, lastNode, leftSide) {
    this.createIndex();

    if (level > 0)
        if (lastNode) //the last 'brother' in the children array
        {
            this.renderOb(leftSide + "<img  align=absmiddle src='/learn/scorm/toc/ftv2lastnode.gif' width=16 height=22>");
            //alert(leftSide);
            //leftSide = leftSide + "<img  align=absmiddle  src='/learn/scorm/toc/ftv2blank.gif' width=16 height=22>"
        }
        else {
            this.renderOb(leftSide + "<img  align=absmiddle src='/learn/scorm/toc/ftv2node.gif' width=16 height=22>");
            //alert(leftSide);
            //leftSide = leftSide + "<img  align=absmiddle src='/learn/scorm/toc/ftv2vertline.gif' width=16 height=22>"
        }
    else
        this.renderOb("");


}

function drawItem(leftSide) {
    if (browserVersion == 2) {
        doc.write("<layer id='item" + this.id + "' top=" + doc.yPos + " visibility=hiden>");
    }

    doc.write("<table ");
//    if (browserVersion == 1) {
        doc.write(" id='item" + this.id + "' style='position:block;' ");
//    }
    doc.write(" border=0 cellspacing=0 cellpadding=0>");


    //alert(this.id);

    doc.write("<tr  nowrap>");
    doc.write("<td nowrap  valign=top>");
    doc.write("<div align=absmiddle id='_s" + this.id + "'>");
    doc.write(leftSide);
    doc.write("</div>");
    doc.write("</td>");
    doc.write("<td nowrap  valign=top>");

    doc.write("<div align=absmiddle id='s" + this.id + "'>");

    //doc.write("<a href=" + this.link + ">")
    doc.write("<img  align=absmiddle id='itemIcon" + this.id + "' ");

    //初始狀態
    doc.write("src='" + this.iconSrc + "' border=0>");
    doc.write("</div>");
    doc.write("</td><td class='caption' valign=middle nowrap>");

    /*
     //動態顯示SCO狀態---------------------------------------

     if(parent.toc.trackingInfoList[this.id-1].objectiveProgressStatus){
     if(parent.toc.trackingInfoList[this.id-1].objectiveSatisfiedStatus){
     doc.write("src='/learn/scorm/toc/passed.gif' border=0>")
     }
     else{
     doc.write("src='/learn/scorm/toc/failed.gif' border=0>")
     }
     }
     else if(parent.toc.activityStatusList[this.id-1].activityProgressStatus){
     if(parent.toc.activityStatusList[this.id-1].activityAttemptCompletionStatus){
     doc.write("src='/learn/scorm/toc/completed.gif' border=0>")
     }
     else{
     doc.write("src='/learn/scorm/toc/incomplete.gif' border=0>")
     }
     }
     else{
     doc.write("src='"+this.iconSrc+"' border=0>")
     }
     */
    //------------------------------------------------------
    //doc.write("</a>")
    doc.write("<div align=absmiddle id='s" + this.id + "_'>");
    if (this.link != "") {
        doc.write("<font size='2'><a href=" + this.link + " title='" + stripTags(this.desc) + "'  >" + this.desc + "</a>");
    } else {
        doc.write("<font size='2'>" + this.desc);
    }


    doc.write("</font>");

    doc.write("</div>");


    doc.write("</td>");


    doc.write("</tr>");


    //doc.write("</td><td valign=middle nowrap>")
    //if(USETEXTLINKS)


    doc.write("</table>");

    if (browserVersion == 2)
        doc.write("</layer>");

    if (browserVersion == 1) {
        this.navObj = doc.all["item" + this.id];
        this.iconImg = doc.all["itemIcon" + this.id];
        //this.lalala = doc.all["itemlink"+this.id]
    } else if (browserVersion == 2) {
        this.navObj = doc.layers["item" + this.id];
        this.iconImg = this.navObj.document.images["itemIcon" + this.id];
        doc.yPos = doc.yPos + this.navObj.clip.height;
    } else {
        this.navObj = doc.getElementById("item" + this.id);
        this.iconImg = doc.getElementById("itemIcon" + this.id);
    }
}


// Methods common to both objects (pseudo-inheritance) 
// ******************************************************** 

function display() {
    if (browserVersion == 2) {
        this.navObj.visibility = "show";
    } else {
        this.navObj.style.display = "block";
    }
}

function createEntryIndex() {
    this.id = nEntries;
    indexOfEntries[nEntries] = this;
    nEntries++;
}

// total height of subEntries open 
function totalHeight() //used with browserVersion == 2 
{
    var h = this.navObj.clip.height;
    var i = 0;

    if (this.isOpen) {//is a folder and _is_ open
        for (i = 0; i < this.nChildren; i++) {
            h = h + this.children[i].totalHeight();
        }
    }

    return h;
}


// Events 
// ********************************************************* 

function clickOnFolder(folderId) {
    var clicked = indexOfEntries[folderId];

    if (!clicked.isOpen) {
        clickOnNode(folderId);
    }

    return;

    if (clicked.isSelected) {
        return;
    }
}

function clickOnNode(folderId) {
    var clickedFolder = 0;
    var state = 0;

    clickedFolder = indexOfEntries[folderId];
    state = clickedFolder.isOpen;

    clickedFolder.setState(!state); //open<->close
}

function initializeDocument() {
    if (doc.all)
        browserVersion = 1; //IE4
    else if (doc.layers)
        browserVersion = 2; //NS4
    else
        browserVersion = 3; //other

    level_0.initialize(0, 1, "");
    level_0.display();

    if (browserVersion > 0) {
        doc.write("<layer top=" + indexOfEntries[nEntries - 1].navObj.top + ">&nbsp;</layer>");

        // close the whole tree
        clickOnNode(0);
        // open the root folder
        clickOnNode(0);
    }

    return true;
}

// Auxiliary Functions for Folder-Treee backward compatibility 
// ********************************************************* 

function gFld(description, hreference) {
    folder = new Folder(description, hreference);
    return folder;
}

function gLnk(target, description, linkData) {
    fullLink = "";
    if (target == 0) {
        if (linkData == "") {
            fullLink = "";
        } else {
            fullLink = "'" + linkData + "' target=\"s_main\"";
        }
    }
    else {
        if (target == 1)
            fullLink = "'http://" + linkData + "' target=_blank";
        else
            fullLink = "'http://" + linkData + "' target=\"s_main\"";
    }

    linkItem = new Item(description, fullLink);
    //alert('in gLink');
    return linkItem;
}

function insFld(parentFolder, childFolder) {
    return parentFolder.addChild(childFolder);
}

function insDoc(parentFolder, childFolder) {
    return parentFolder.addChild(childFolder);
}

// Global variables 
// **************** 

var
    USETEXTLINKS = 0,
    indexOfEntries = new Array,
    nEntries = 0,
    doc = document,
    browserVersion = 0,
    selectedFolder = 0;
