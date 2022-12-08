var dialog  = window.parent ;
var oEditor = dialog.InnerDialogLoaded() ;

var FCK         = oEditor.FCK ;
var FCKLang     = oEditor.FCKLang ;
var FCKConfig   = oEditor.FCKConfig ;
var FCKRegexLib = oEditor.FCKRegexLib ;
var FCKTools    = oEditor.FCKTools ;

// oLink: The actual selected link in the editor.
var oLink = dialog.Selection.GetSelection().MoveToAncestorNode( 'A' ) ;
if ( oLink )
	FCK.Selection.SelectNode( oLink ) ;

var WMESrc = "", WMETitle = "";

function setWMESrc(src) {
	var ary = eval(src);
	WMESrc   = ary[0];
	WMETitle = ary[1];
}

function Ok() {
	if (WMESrc == "") {
		alert(FCKLang.DlgWMESelect);
		return false ;
	}

	var sUri, sInnerHtml ;
	oEditor.FCKUndo.SaveUndoStep() ;

	sUri = WMESrc;

	// If no link is selected, create a new one (it may result in more than one link creation - #220).
	var aLinks = oLink ? [ oLink ] : oEditor.FCK.CreateLink( sUri, true ) ;

	var aHasSelection = ( aLinks.length > 0 ) ;
	if ( !aHasSelection )
	{
		sInnerHtml = WMETitle;
		aLinks = [ oEditor.FCK.InsertElement( 'a' ) ] ;
	}

	for ( var i = 0 ; i < aLinks.length ; i++ )
	{
		oLink = aLinks[i] ;

		if ( aHasSelection )
			sInnerHtml = oLink.innerHTML ;		// Save the innerHTML (IE changes it if it is like an URL).

		oLink.href = sUri ;
		SetAttribute( oLink, '_fcksavedurl', sUri ) ;

		var onclick;

		oLink.innerHTML = sInnerHtml ;		// Set (or restore) the innerHTML
		SetAttribute( oLink, 'target', '_blank' ) ;
	}

	// Select the (first) link.
	oEditor.FCKSelection.SelectNode( aLinks[0] );

	return true ;
}

window.onload = function () {
	// Show the "Ok" button.
	dialog.SetOkButton( true ) ;

	// First of all, translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage( document ) ;

	window.parent.SetAutoSize( true ) ;
};
