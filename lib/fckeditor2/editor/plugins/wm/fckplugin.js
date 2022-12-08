/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2008 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * This is the sample plugin definition file.
 */

// Register the related commands.
FCKCommands.RegisterCommand( 'WM_Blog', new FCKDialogCommand( FCKLang['DlgWMBlogTitle'], FCKLang['DlgWMBlogTitle']    , FCKConfig.PluginsPath + 'wm/blog.html', 540, 300 ) ) ;
FCKCommands.RegisterCommand( 'WM_Wiki', new FCKDialogCommand( FCKLang['DlgWMWikiTitle'], FCKLang['DlgWMWikiTitle']    , FCKConfig.PluginsPath + 'wm/wiki.html', 540, 300 ) ) ;

// Create the "Blog" toolbar button.
var oFindItem		= new FCKToolbarButton( 'WM_Blog', FCKLang['DlgWMBlogTitle'] ) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'wm/blog.gif' ;

FCKToolbarItems.RegisterItem( 'WM_Blog', oFindItem ) ;			// 'WM_Blog' is the name used in the Toolbar config.

// Create the "Wiki" toolbar button.
var oFindItem		= new FCKToolbarButton( 'WM_Wiki', FCKLang['DlgWMWikiTitle'] ) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'wm/wiki.gif' ;

FCKToolbarItems.RegisterItem( 'WM_Wiki', oFindItem ) ;			// 'WM_Wiki' is the name used in the Toolbar config.
