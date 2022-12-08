/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.uiColor = '#EFEFDE';
	config.extraPlugins = 'eqneditor';
	config.toolbar_WM = [
		['FontSize','Bold','Italic','Underline','Strike','-','Subscript','Superscript','-'] ,
		['JustifyLeft','JustifyCenter','JustifyRight'],
		['Rule','Link','Unlink','-','Image','Table'],
		['Outdent','Indent','-','OrderedList','UnorderedList','-'],
		['/','NumberedList','BulletedList','-','TextColor','BGColor','-','Source','EqnEditor'],
		['Blockquote','Font','Maximize']
	];
	
	config.toolbar_EXAM = [
 		['FontSize','Bold','Italic','Underline','Strike','-','Subscript','Superscript','-'] ,
 		['JustifyLeft','JustifyCenter','JustifyRight'],
 		['Rule','Link','Unlink','-','Image','Table'],
 		['Outdent','Indent','-','OrderedList','UnorderedList','-'],
 		'/',
 		['NumberedList','BulletedList','-','TextColor','BGColor','-','Source','EqnEditor'],
 		['Blockquote','Font']
 	];
	
    config.toolbar_PHONE = [
        ['FontSize','Bold','Italic','Underline']
    ];
	
	config.pasteFromWordRemoveFontStyles = false; 
	config.pasteFromWordRemoveStyles = false; 
	config.toolbarCanCollapse = true;
	config.width = 500;
	config.toolbar = 'WM';
	config.allowedContent = true;
};

