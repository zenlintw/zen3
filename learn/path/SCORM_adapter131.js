/**
 *	旭聯科技 - 企業大師 - SCORM 2004 (Version 1.3.1) R.T.E Adapter
 *
 *	Since 2002~2004 Copyright(C) SunNet
 *	Constructing by Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 *  $Id: SCORM_adapter131.js,v 1.1 2010/02/24 02:39:09 saly Exp $
 */

var _R  = 1;
var _W  = 2;
var _RW = 3;
var _NoError = 0;

/**
 * clean node values of a clone node tree
 */
function cleanNode(node)
{
	var nodes = node.selectNodes('./*//item');
	for(var i=nodes.length-1; i>0; i--)
		if (nodes[i].previousSibling != null && nodes[i].previousSibling.nodeName == 'item')
			nodes[i].parentNode.removeChild(nodes[i]);
	nodes = node.selectNodes('.//text()');
	for(var i=nodes.length-1; i>=0; i--)
		nodes[i].parentNode.removeChild(nodes[i]);
	nodes = node.selectNodes('.//@_count');
	for(var i=nodes.length-1; i>=0; i--)
		nodes[i].nodeValue = 0;
}

/**
 * calculate sum of two Timeintervals
 */
function cumulateTimeinterval(t1, t2)
{
	if (t1 == '' || t1.search(/^P/) !== 0)
		return (t2.search(/^P/) === 0) ? t2 : '';
	else
	{
		var x = '', lastChar = 0;
		var p = new Array(t1.split('T'), t2.split('T'));
		var s = new Array(new Array(0,0,'Y'), new Array(0,0,'M'), new Array(0,0,'D'),
						  new Array(0,24,'H'), new Array(0,60,'M'), new Array(0,60,'S') );
		var r = '';

		for(var i=0; i<2; i++)
			if (p[i][0].search(/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?$/i) === 0){
				for(var j=1; j<4; j++)
				{
					x = eval('RegExp.$' + j);
					if (x != '')
					{
						lastChar = x.length-1;
						switch(x.substr(lastChar))
						{
							case 'Y': s[0][0] += parseInt(x); break;
							case 'M': s[1][0] += parseInt(x); break;
							case 'D': s[2][0] += parseInt(x); break;
						}
					}
				}
			}

		for(var i=0; i<2; i++)
			if (p[i][1].search(/^([0-9]+H)?([0-9]+M)?([0-9]+(.[0-9]+)?S)?$/) === 0)
				for(var j=1; j<4; j++)
				{
					x = eval('RegExp.$' + j);
					if (x != '')
					{
						lastChar = x.length-1;
						switch(x.substr(lastChar))
						{
							case 'H': s[3][0] += parseInt(x); break;
							case 'M': s[4][0] += parseInt(x); break;
							case 'S': s[5][0] += parseFloat(x); break;
						}
					}
				}

		for(var i=5; i>=0; i--)
		{
			if (s[i][0] > 0)
				while (i && s[i][1] && s[i][0] >= s[i][1])
				{
					s[i][0] -= s[i][1]; s[i-1][0]++;
				}
			if (i==5)
			{
				s[5][0] = s[5][0].toString();
				if ((j = s[5][0].lastIndexOf('.')) != -1) s[5][0] = s[5][0].substring(0, j+3);
			}

			r = (i==3 ? 'T' : (i==0 ? 'P' : '')) + (s[i][0] ? (s[i][0] + s[i][2]) : '') + r;
		}

		return r;
	}
}

/**
 * trans millisecond to Timeinterval
 */
function millisec2timeinterval(t)
{
	var s = new Array(new Array(1000, 60, 60, 24),
					  new Array('S', '.', 'M', 'H'),
					  new Array(2, 2, 2, 10)
					 );
	var diff = t, result = '', x;

	for(var i = 0; i<4; i++)
	{
		if(x = diff % s[0][i])
			result = x.toString().substr(0,s[2][i]) + s[1][i] + result;
		else if (i==1 && result)
			result = '0.' + result;
		diff = Math.floor(diff / s[0][i]);
		if (diff < 1) break;
	}
	result = result.replace(/\.$/, 'S');
	return ('P' + (diff ? (diff + 'D') : '') + (result ? ('T' + result) : ''));
}

/**
 * translate object level to xpath
 */
function object2xpath(param)
{
	var pieces = param.split('.');
	var xpath = '/sco';
	for(var i=0; i<pieces.length; i++)
	{
		if (pieces[i].search(/^[0-9]+$/) === 0)
		{
			xpath += '/item[' + pieces[i] + ']';
		}
		else if (pieces[i].search(/^_/) === 0)
		{
			xpath += '/@' + pieces[i];
		}
		else
		{
			xpath += '/' + pieces[i];
		}
	}
	return xpath;
}

/**
 * check datatype whether the format and range are correct or incorrect.
 */
function CheckDatatype(type, value)
{
	if (type.search(/^<characterstring(\(([0-9]+)\))?>$/) === 0)
	{
		var max_length = (RegExp.$2 != '') ? parseInt(RegExp.$2) : (typeof(API)=='undefined' ? 4000 : 4096);
		return (value.length >= 0 && value.length <= max_length && (value == '' || value.search(/^[^\x00-\x07\x0E-\x1F]+$/) === 0));
	}
	else if (type.search(/^<localized_string_type(\(([0-9]+)\))?>$/) === 0)
	{
		var max_length = (RegExp.$2 != '') ? parseInt(RegExp.$2) : (typeof(API)=='undefined' ? 4000 : 4096);
		return (value.length >= 0 && value.length <= max_length && (value == '' || value.search(/^(\{lang=[\w.-]+\})?[^\x00-\x07\x0E-\x1F]+$/) === 0));
	}
	else if(type == '<language_type>')
	{
		return value.search(/^((?:[a-z][a-z][a-z]?(?:-[a-z][a-z][a-z])?)|sgn(?:-[a-z][a-z]))(?:-([a-z][a-z][a-z][a-z]))?(?:-([a-z][a-z]))?$/i) === 0;
	}
	else if(type.search(/^<long_identifier_type(\(([0-9]+)\))?>$/) === 0)
	{
		var max_length = (RegExp.$2 != '') ? parseInt(RegExp.$2) : (typeof(API)=='undefined' ? 4000 : 4096);
		return (value.length > 0 && value.length <= max_length && value.search(/^[\w]+$/) === 0);
	}
	else if(type.search(/^<short_identifier_type(\(([0-9]+)\))?>$/) === 0)
	{
		var max_length = (RegExp.$2 != '') ? parseInt(RegExp.$2) : 250;
		return (value.length > 0 && value.length <= max_length && value.search(/^[\w]+$/) === 0);
	}
	else if(type.search(/^<integer>(,range\((-?[0-9]+)\.\.(-?[0-9]+|\*)\))?$/) === 0)
	{
		return (RegExp.$2 != '') ? (parseInt(RegExp.$2) <= parseInt(value) && (RegExp.$3 == '*' || parseInt(value) <= parseInt(RegExp.$3))) : (value.search(/^-?[0-9]+$/) === 0);
	}
	else if(type.search(/^<state\((.+)\)>$/) === 0)
	{
		eval('var rr = /^(' + RegExp.$1.replace(/,/g, '|') + ')$/;');
		return (value.search(rr) === 0);
	}
	else if(type.search(/^<real\(10,7\)(,range\((-?[0-9.]+)\.\.(-?[0-9.]+|\*)\))?>$/) === 0)
	{
		return (RegExp.$2 != '') ? (parseFloat(RegExp.$2) <= parseFloat(value) && (RegExp.$3 == '*' || parseFloat(value) <= parseFloat(RegExp.$3))) : (value.search(/^-?[0-9]+(\.[0-9]+)?$/) === 0);
	}
	else if(type.search(/^<time\(second,10,0\)>$/) === 0)
	{
		return (value.search(/^[0-9]{4}(-[0-9]{2}(-[0-9]{2}(T[0-9]{2}(:[0-9]{2}(:[0-9]{2}(.[0-9]{1,2}(UTC|\+[0-9]{2}:[0-9]{2}|-[0-9]{2}:[0-9]{2})?)?)?)?)?)?)?$/) === 0);
	}
	else if(type.search(/^<timeinterval\(second,10,2\)>$/) === 0)
	{
		return (value.search(/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?(T([0-9]+H)?([0-9]+M)?([0-9]+(\.[0-9]{1,2})?S)?)?$/) === 0);
	}
	else
		return false;
}

function TypePattern()
{
	this.true_false      = '<state(true,false)>';
	this.multiple_choice = '<short_identifier_type>([,]<short_identifier_type>)*';
	this.fill_in         = '({case_matters=<boolean>}{order_matters=<boolean>}<localized_string_type(250)>([,]<localized_string_type(250)>)*)+';
	this.long_fill_in    = '({case_matters=<boolean>}{order_matters=<boolean>}<localized_string_type(4000)>([,]<localized_string_type(4000)>)*)+';
	this.matching        = '<short_identifier_type>[.]<short_identifier_type>([,]<short_identifier_type>[.]<short_identifier_type>)*';
	this.performance     = '({order_matters=<boolean>}<step_name>[.]<step_answer>([,]<step_name>[.]<step_answer>)*)+';
	this.sequencing      = '(<short_identifier_type>([,]<short_identifier_type>)*)+';
	this.likert          = '<short_identifier_type>';
	this.numeric         = '<real(10,7)>[:]<real(10,7)>';
	this.other           = '<characterstring(4000)>';
}

function CmiStructure(){
    this._version = _R;
	this.comments_from_learner = new Object();
	this.comments_from_learner._children = _R;
	this.comments_from_learner._count = _R;
	this.comments_from_learner.n = new Object();
	this.comments_from_learner.n.comment = _RW;
	this.comments_from_learner.n.location = _RW;
	this.comments_from_learner.n.timestamp = _RW;
	this.comments_from_lms = new Object();
	this.comments_from_lms._children = _R;
	this.comments_from_lms._count = _R;
	this.comments_from_lms.n = new Object();
	this.comments_from_lms.n.comment = _RW;
	this.comments_from_lms.n.location = _RW;
	this.comments_from_lms.n.timestamp = _RW;
	this.completion_status = _RW;
	this.completion_threshold = _R;
	this.credit = _R;
	this.entry = _R;
	this.exit = _W;
	this.interactions = new Object();
	this.interactions._children = _R;
	this.interactions._count = _R;
	this.interactions.n = new Object();
	this.interactions.n.id = _RW;
	this.interactions.n.type = _RW;
	this.interactions.n.objectives = new Object();
	this.interactions.n.objectives._count = _R;
	this.interactions.n.objectives.n = new Object();
	this.interactions.n.objectives.n.id = _RW;
	this.interactions.n.timestamp = _RW;
	this.interactions.n.correct_responses = new Object();
	this.interactions.n.correct_responses._count = _R;
	this.interactions.n.correct_responses.n = new Object();
	this.interactions.n.correct_responses.n.pattern = _RW;
	this.interactions.n.weighting = _RW;
	this.interactions.n.learner_response = _RW;
	this.interactions.n.result = _RW;
	this.interactions.n.latency = _RW;
	this.interactions.n.description = _RW;
	this.launch_data = _R;
	this.learner_id = _R;
	this.learner_name = _R;
	this.learner_preference = new Object();
	this.learner_preference._children = _R;
	this.learner_preference.audio_level = _RW;
	this.learner_preference.language = _RW;
	this.learner_preference.delivery_speed = _RW;
	this.learner_preference.audio_captioning = _RW;
	this.location = _RW;
	this.max_time_allowed = _R;
	this.mode = _R;
	this.objectives = new Object();
	this.objectives._children = _R;
	this.objectives._count = _R;
	this.objectives.n = new Object();
	this.objectives.n.id = _RW;
	this.objectives.n.score = new Object();
	this.objectives.n.score._children = _R;
	this.objectives.n.score.scaled = _RW;
	this.objectives.n.score.raw = _RW;
	this.objectives.n.score.min = _RW;
	this.objectives.n.score.max = _RW;
	this.objectives.n.success_status = _RW;
	this.objectives.n.completion_status = _RW;
	this.objectives.n.progress_measure = _RW;
	this.objectives.n.description = _RW;
	this.progress_measure = _RW;
	this.scaled_passing_score = _R;
	this.score = new Object();
	this.score.scaled = _RW;
	this.score.raw = _RW;
	this.score.min = _RW;
	this.score.max = _RW;
	this.session_time = _W;
	this.success_status = _RW;
	this.suspend_data = _RW;
	this.time_limit_action = _R;
	this.total_time = _R;
}

/**
* SCORM API method : Initialize
 */
function WmScormApi_Initialize(parameter)
{
	globalState.PrevActivity = '';
	if (this.isInited){
		this.LastError = 103;	// already Initilized.
		return 'false';
	}

	if (parameter != ''){
		this.LastError = 201;	// Invalid argument errors
		return 'false';
	}

	this.sco = XmlDocument.create();

	this.sco.async = false;
	this.sco.resolveExternals = false;

    if( typeof this.sco.load == "undefined" ){
        var xmlHttp = XmlHttp.create();
        xmlHttp.open('GET', '/learn/path/SCORM_loadSCO.php?activity_id=' + globalState.CurrentActivity+'&SCORM_VERSION='+s_catalog.pathtree.SCORM_VERSION, this.sco.async);
        xmlHttp.send();
    }else{
        if (this.sco.load('/learn/path/SCORM_loadSCO.php?activity_id=' + globalState.CurrentActivity+'&SCORM_VERSION='+s_catalog.pathtree.SCORM_VERSION)){
            if (this.sco.documentElement.nodeName == 'errorlevel')
            {
                this.LastError = 102;   // Get SCO failure
                return 'false';
            }
            this.sessionBegin = new Date().getTime();
            this.isInited = true;
            this.terminated = false;
            this.LastError = 0;     // Initilize success
            return 'true';
        }
        else{
            this.LastError = 101;   // Get SCO failure
            return 'false';
        }
    }
}

/**
 * SCORM API method : Terminate
 */
function WmScormApi_Finish(parameter)
{
	if (!this.isInited){
		this.LastError = 112;	// Not initialized
		return 'false';
	}

	if (this.terminated)
	{
		this.LastError = 113;	// Termination After Termination
		return 'false';
	}

	if (parameter != ''){
		this.LastError = 201;	// Invalid argument error
		return 'false';
	}

	/*
	 * 設定 Finish 時的值
	 */
	var completion_threshold = this.GetValue('cmi.completion_threshold');
	var progress_measure	 = this.GetValue('cmi.progress_measure');
	if (completion_threshold != '' && progress_measure != '')
		this.SetValue('cmi.completion_status', (parseInt(completion_threshold) > parseInt(progress_measure)) ? 'incomplete' : 'complete');

	var scaled_passing_score = this.GetValue('cmi.scaled_passing_score');
	var scaled_score		 = this.GetValue('cmi.score.scaled');
	if (scaled_passing_score != '' && scaled_score != '')
		this.SetValue('cmi.success_status', (parseInt(scaled_passing_score) > parseInt(scaled_score)) ? 'failed' : 'passed');

	var total_time = this.sco.selectSingleNode('/sco/cmi/total_time');
	total_time.text = cumulateTimeinterval(total_time.text, this.sco.selectSingleNode('/sco/cmi/session_time').text);

	this.sco.selectSingleNode('/sco/cmi/entry').text = '';
	this.notCommit = true;

	this.Commit('');
	if (this.GetLastError() != 0)
	{
		this.LastError = 111;
		return 'false';
	}
	this.sco = null;
	this.isInited = false;
	this.terminated = true;
	this.LastError = 0;
	globalState.PrevActivity = '';	
	return 'true';
}

/**
 * SCORM API method : GetValue
 */
function WmScormApi_GetValue(parameter)
{
	if (!this.isInited){
		this.LastError = 122;	// Retrieve Data Before Initialization
		return '';
	}

	if (this.terminated)
	{
		this.LastError = 123;	// Retrieve Data After Termination
		return '';
	}

	try{
		var x = eval('this.' + parameter.replace(/\.[0-9]+\./g, '.n.'));
	}
	catch(e){
			var x = null;
	}
	if (x == null)
	{
		this.LastError = 401;	// Undefined Data Model Element
		return '';
	}

	if(!(x & _R))
	{
		this.LastError = 405;
		return '';
	}

	var node;
	if (parameter.search(/\.0\./) > -1)
	{
		var nodes = parameter.split(/\.0\./), x = '';
		for(var i=0; i<nodes.length-1; i++)
		{
			x += nodes[i];
			node = this.sco.selectSingleNode(object2xpath(x) + '/@_count');
			if (node == null || parseInt(node.text) == 0)
			{
				this.LastError = 401;
				return '';
			}
			x += '.0.';
		}
	}

	node = this.sco.selectSingleNode(object2xpath(parameter));
	if (node == null)
	{
		if (parameter.search(/(\.[0-9]+\.)|(_count|_children)$/g) > -1)
			this.LastError = 301;	// Data Model Element Does Not Have Children || Data Model Element Cannot Have Count || Data Model Collection Element Request Out Of Range
		else
			this.LastError = 405;	// Undefined Data Model Element
		return '';
	}
	else
	{
		this.LastError = 0;
		return (node.text == null || typeof(node.text) == 'undefined') ? '' : node.text;
	}
}

/**
 * SCORM API method : SetValue
 */
function WmScormApi_SetValue(parameter, value)
{
	if (!this.isInited){
		this.LastError = 132;	// Store Data Before Initialization
		return 'false';
	}

	if (this.terminated)
	{
		this.LastError = 133;	// Store Data After Termination
		return 'false';
	}

	// check parameter whether it's writable or not.
	try{
		var x = eval('this.' + parameter.replace(/\.[0-9]+\./g, '.n.'));
	}
	catch(e){
		var x = null;
	}
	if (x == null)
	{
		this.LastError = 401;
		return 'false';
	}

	if(!(x & _W))
	{
		this.LastError = 404;
		return 'false';
	}

	// check value whether the format is correct or incorrect.
	if (parameter.search(/\.(audio_level|delivery_speed)$/) > -1)
	{
		if (value.search(/^-?[0-9]+$/) !==0 || Math.abs(parseInt(value)) > 100)
		{
			this.LastError = 406;
			return 'false';
		}
	}
	else if(parameter.search(/\.language$/) > -1)
	{
		if (value.length > 255)
		{
			this.LastError = 406;
			return 'false';
		}
	}
	else if(!this.CheckDatatype(eval('this.d' + parameter.substr(1).replace(/\.[0-9]+\./g, '.n.')), value))
	{
		//if (!(parameter.search(/\.(pattern|location|suspend_data|comments)$/) > -1 && value == ''))
		//{
			this.LastError = 406;
			return 'false';
		//}
	}

	// check id whether it's unique or not
	if (typeof('API') == 'undefined' && parameter.search(/\.id$/) > -1)
	{
		var nodes = this.sco.selectNodes('//id');
		for(var i=0; i<nodes.length; i++)
		{
			if (nodes[i].text == value)
			{
				this.LastError = 351;	// Data Model Element Collection Set Out Of Order
				return 'false';
			}
		}
	}

	// create array element if assigning a new item
	var xpath = object2xpath(parameter);
	var i=0, j=0, c=0, cc=0, xp='', xn;
	while((i = xpath.indexOf('/item[', j)) > -1)
	{
		j = xpath.indexOf(']', i);
		c = parseInt(xpath.substring(i+6, j));
		xp = xpath.substring(0, i);
		xn = this.sco.selectSingleNode(xp);
		if (xn == null)
		{
			this.LastError = 401;
			return 'false';
		}
		cc = parseInt(xn.getAttribute('_count'));
		if (c == cc)
		{
			if (c > 0)
			{
				cleanNode(xn.appendChild(xn.lastChild.cloneNode(true)));
			}
			xn.setAttribute('_count', cc+1);
		}
		else if (c > cc)
		{
			this.LastError = 351;	// Data Model Element Collection Set Out Of Order
			return 'false';
		}
	}

	var node = this.sco.selectSingleNode(xpath);
	if (node == null)
	{
		this.LastError = 351;	// Undefined Data Model Element
		return 'false';
	}
	else
	{
		node.text = value;
		this.notCommit = true;
	}

	this.LastError = 0;
	return 'true';
}

/**
 * SCORM API method : Commit
 */
function WmScormApi_Commit(parameter)
{
	if (!this.isInited){
		this.LastError = 142;	// Commit Before Initialization
		return 'false';
	}

	if (this.terminated)
	{
		this.LastError = 143;	// Commit After Termination
		return 'false';
	}

	if (parameter != ''){
		this.LastError = 201;	// Invalid argument error
		return 'false';
	}

	if (this.notCommit)
	{
		var xmlHttp = XmlHttp.create();
		xmlHttp.open('POST', '/learn/path/SCORM_saveSCO.php?activity_id=' + (globalState.PrevActivity == '' ? globalState.CurrentActivity : globalState.PrevActivity), false);
		xmlHttp.send(this.sco.xml);
		var ret = xmlHttp.responseText;
		if (ret != '<errorlevel>0</errorlevel>')
		{
			this.LastError = 391;
			return 'false';
		}
		this.notCommit = false;
	}

	this.LastError = 0;
	return 'true';
}

/**
 * SCORM API method : GetLastError
 */
function WmScormApi_GetLastError()
{
	return this.LastError;
}

/**
 * SCORM API method : GetErrorString
 */
function WmScormApi_GetErrorString(errornumber)
{
	switch(parseInt(errornumber,10)){
		case 0  : return 'No error';
		case 101: return 'General Exception';
		case 102: return 'General Initialization Failure';
		case 103: return 'Already Initialized';
		case 104: return 'Content Instance Terminated';
		case 111: return 'General Termination Failure';
		case 112: return 'Termination Before Initialization';
		case 113: return 'Termination After Termination';
		case 122: return 'Retrieve Data Before Initialization';
		case 123: return 'Retrieve Data After Termination';
		case 132: return 'Store Data Before Initialization';
		case 133: return 'Store Data After Termination';
		case 142: return 'Commit Before Initialization';
		case 143: return 'Commit After Termination';
		case 201: return 'General Argument Error';
		case 301: return 'General Get Failure';
		case 351: return 'General Set Failure';
		case 391: return 'General Commit Failure';
		case 401: return 'Undefined Data Model Element';
		case 402: return 'Unimplemented Data Model Element';
		case 403: return 'Data Model Element Value Not Initialized';
		case 404: return 'Data Model Element Is Read Only';
		case 405: return 'Data Model Element Is Write Only';
		case 406: return 'Data Model Element Type Mismatch';
		case 407: return 'Data Model Element Value Out Of Range';
		case 408: return 'Data Model Dependency Not Established';
		default:  return '';
	}
}

/**
 * SCORM API method : GetDiagnostic
 */
function WmScormApi_GetDiagnostic(errornumber)
{
	return 'WM: ' + this.GetErrorString(errornumber);
}

/**
 * SCORM API object declare
 */
function API_Adapter(){
	this.Initialize     = WmScormApi_Initialize;
	this.Terminate      = WmScormApi_Finish;
	this.GetValue       = WmScormApi_GetValue;
	this.SetValue       = WmScormApi_SetValue;
	this.Commit         = WmScormApi_Commit;
	this.GetLastError   = WmScormApi_GetLastError;
	this.GetErrorString = WmScormApi_GetErrorString;
	this.GetDiagnostic  = WmScormApi_GetDiagnostic;
	this.CheckDatatype	= CheckDatatype;
	this.LastError		= 0;
	this.notCommit		= false;
	this.isInited		= false;
	this.terminated		= false;
	this.sessionBegin	= 0;
	this.sco			= null;
	this.cmi = new CmiStructure();	// READ/WRITE attribute
	this.dmi = new CmiStructure();	// Data Type attribute
	this.dmi.comments_from_learner.n.comment			= '<localized_string_type>';
	this.dmi.comments_from_learner.n.location			= '<characterstring>';
	this.dmi.comments_from_learner.n.timestamp			= '<time(second,10,0)>';
	this.dmi.comments_from_lms.n.comment				= '<localized_string_type>';
	this.dmi.comments_from_lms.n.location				= '<characterstring>';
	this.dmi.comments_from_lms.n.timestamp				= '<time(second,10,0)>';
	this.dmi.completion_status							= '<state(completed,incomplete,not_attempted,unknown)>';
	this.dmi.exit										= '<state(timeout,suspend,logout,normal,)>';
	this.dmi.interactions.n.id							= '<long_identifier_type>';
	this.dmi.interactions.n.type						= '<state(true_false,multiple_choice,fill_in,long_fill_in,matching,performance,sequencing,likert,numeric,other)>';
	this.dmi.interactions.n.objectives.n.id				= '<long_identifier_type>';
	this.dmi.interactions.n.timestamp					= '<time(second,10,0)>';
	this.dmi.interactions.n.correct_responses.n.pattern = '<characterstring>'; // set it temporarily
	this.dmi.interactions.n.weighting					= '<real(10,7)>';
	this.dmi.interactions.n.learner_response			= '<characterstring>';
	this.dmi.interactions.n.result						= '<state(correct,incorrect,unanticipated,neutral,-?[0-9]+(\.[0-9]+)?)>';
	this.dmi.interactions.n.latency						= '<timeinterval(second,10,2)>';
	this.dmi.interactions.n.description					= '<localized_string_type(250)>';
	this.dmi.learner_preference.audio_level				= '<real(10,7),range(0..*)>';
	this.dmi.learner_preference.language				= '<language_type>';
	this.dmi.learner_preference.delivery_speed			= '<real(10,7),range(0..*)>';
	this.dmi.learner_preference.audio_captioning		= '<state(-1,0,1)>';
	this.dmi.location									= '<characterstring>';
	this.dmi.objectives.n.id							= '<long_identifier_type>';
	this.dmi.objectives.n.score.scaled					= '<real(10,7),range(-1..1)>';
	this.dmi.objectives.n.score.raw 					= '<real(10,7)>';
	this.dmi.objectives.n.score.min 					= '<real(10,7)>';
	this.dmi.objectives.n.score.max 					= '<real(10,7)>';
	this.dmi.objectives.n.success_status				= '<state(passed,failed,unknown)>';
	this.dmi.objectives.n.completion_status				= '<state(completed,incomplete,not_attempted,unknown)>';
	this.dmi.objectives.n.progress_measure				= '<real(10,7),range(0..1)>';
	this.dmi.objectives.n.description					= '<localized_string_type(250)>';
	this.dmi.progress_measure							= '<real(10,7),range(0..1)>';
	this.dmi.score.scaled								= '<real(10,7),range(-1..1)>';
	this.dmi.score.raw									= '<real(10,7)>';
	this.dmi.score.min									= '<real(10,7)>';
	this.dmi.score.max									= '<real(10,7)>';
	this.dmi.session_time								= '<timeinterval(second,10,2)>';
	this.dmi.success_status								= '<state(passed,failed,unknown)>';
	this.dmi.suspend_data								= '<characterstring>';
}

function _Global()
{
	this.CurrentActivity = '';
	this.SuspendedActivity = '';
	this.PrevActivity = '';
}

// var API_1484_11 = new API_Adapter;
var globalState = new _Global;
