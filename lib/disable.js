function rightButtonDisable(e)
{
	if (navigator.userAgent.indexOf('MSIE') !== -1 && (event.button == 2 || event.button == 3))
		return false;
	else if (navigator.userAgent.indexOf('MSIE') === -1 && (e.which == 3 || e.which == 2))
		return false;
	return true;
}

if (navigator.userAgent.search('MSIE') !== -1)
{
	document.attachEvent("onmousedown", rightButtonDisable);
	document.attachEvent("onmouseup", rightButtonDisable);
	window.attachEvent("onmousedown", rightButtonDisable);
	window.attachEvent("onmouseup", rightButtonDisable);
}
else
{
	document.addEventListener("mousedown", rightButtonDisable, false);
	document.addEventListener("mouseup", rightButtonDisable, false);
	window.addEventListener("mousedown", rightButtonDisable, false);
	window.addEventListener("mouseup", rightButtonDisable, false);
}

document.onbeforecopy =
document.onbeforecut  =
document.oncontextmenu =
document.oncopy =
document.oncut =
document.ondragstart =
document.onhelp =
function(evnt)
{
	return false;
};

document.onselectstart =
function(e)
{
	if (navigator.userAgent.indexOf('MSIE') !== -1)
	{
		if (event.srcElement.tagName.search(/^(input|textarea)$/i) !== -1) return true;
	}
	else
	{
		if ((typeof e.target !== "undefined") && (typeof e.target.tagName !== "undefined")) {
			if (e.target.tagName.search(/^(input|textarea)$/i) !== -1) return true;
		}
	}
	return false;
};

document.body.onkeydown = function(e){
	if (typeof(event) == 'undefined') event = e;
	if ((event.keyCode == 78  && event.ctrlKey)){
		alert ("No new window")
		event.cancelBubble = true;
		event.returnValue = false;
		event.keyCode = false;
		return false;
	}
};
