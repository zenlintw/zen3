function newID()
{
	var today = new Date();
	return today.getTime();
}

function CWindow(w,h,y,x,title,html,bgc,bgi,css,cssf,scrollbar,sz)
{
	this._width=w;					//window width
	this._height=h;					//window height
	this._top=y;					//window top position (px)
	this._left=x;					//window left position (px)
	this._title=title;				//window title
	this._bgc=bgc;					//background color
	this._bgi=bgi;					//background Image
	this._css=css;					//css
	this._cssf=cssf;				//cssf
	this._scrollbar=scrollbar;		//ScrollBar
	this._resizable=sz;				//Resizable
	this._html=html||'';
	this._hwnd=null;
	this._id=newID();
}

CWindow.prototype.create=function(url,opt)
{
	var w;
	this.chkWnd();
	w=this._hwnd=window.open(url||'',this._id||'',opt||"toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars="+(this._scrollbar?'yes':'no')+",resizable="+(this._resizable?'yes':'no')+(Def(this._width)?",width="+this._width:'')+(Def(this._height)?",height="+this._height:'')+(Def(this._top)?",top="+this._top:'')+(Def(this._left)?",left="+this._left:''));
	if(Undef(w))	return 0;
	if(url)	return 1;
	this.refresh();
	return 1;
};

CWindow.prototype.mkHTML=function()
{
	return '<html><head><title>'+(this._title||'')+'</title>'+(this._cssf?'<link rel="stylesheet" type="text/css" href="'+this._cssf+'">':'')+'</head><body marginwidth=0 marginheight=0 topmargin=0 leftmargin=0'+(this._bgc?' bgcolor="'+this._bgc+'"':'')+(this._bgi?' background="'+this._bgi+'"':'')+(this._css?' class="'+this._css+'"':'')+'>'+(this._html||'')+'</body></html>';
};

CWindow.prototype.chkWnd=function()
{
	if(this._hwnd&&this._hwnd.closed)	this._hwnd=null;
	return this._hwnd==null;
};

CWindow.prototype.move=function(x,y)
{
	this._top=x;
	this._left=y;
	this.chkWnd();
	if(this._hwnd) this._hwnd.moveTo(x,y);
};

CWindow.prototype.resize=function(w,h)
{
	this._width=w;
	this._height=h;
	this.chkWnd();
	if(this._hwnd)this._hwnd.resizeTo(w,h);
};

CWindow.prototype.refresh=function()
{
	this.chkWnd();
	if(this._hwnd)
	{
		var d=this._hwnd.document;
		d.open();
		d.write(this.mkHTML());
		d.close();
	}
};

CWindow.prototype.HTML=function(html)
{
	if(Und(html)) return this._html;
	this._html=html;
	this.refresh();
};

CWindow.prototype.appHTML=function(html)
{
	this.HTML(this._html+html);
};

CWindow.prototype.bgColor=function(bgc)
{
	if(Und(bgc)) return this._bgc;
	this._bgc=bgc;
	this.chkWnd();
	if(this._hwnd)
	{
		if(ua.nn4||ua.oldOpera)
		{
			this.refresh();
		}else{
			this._hwnd.document.body.style.backgroundColor=bgc;
		}
	}
};

CWindow.prototype.bgImage=function(bgi)
{
	if(Und(bgi))
	return this._bgi;
	this._bgi=bgi;
	this.chkWnd();
	if(this._hwnd)
	{
		if(ua.nn4||ua.oldOpera)
		{
			this.refresh();
		}else{
			this._hwnd.document.body.style.backgroundImage='url('+bgi+')';
		}
	}
};

CWindow.prototype.CSS=function(css)
{
	if(Und(css)) return this._css;
	this._css=css;
	this.chkWnd();
	if(this._hwnd)
	{
		if(ua.nn4||ua.oldOpera)
		{
			this.refresh();
		}else{
			this._hwnd.document.body.style.className=css;
		}
	}
};

CWindow.prototype.CSSFile=function(cssf)
{
	if(Und(cssf)) return this._cssf;
	this._cssf=cssf;
	this.refresh();
};

CWindow.prototype.Title=function(s)
{
	if(Und(s)) return this._title;
	this._title=s;
	this.chkWnd();
	if(this._hwnd)
	{
		if(ua.nn4||ua.oldOpera)
		{
			this.refresh();
		}else{
			this._hwnd.document.title=s;
		}
	}
};

CWindow.prototype.loadURL=function(url)
{
	if(this._hwnd) this._hwnd.location.href=URL;
};

CWindow.prototype.opened=function()
{
	return Def(this._hwnd);
};