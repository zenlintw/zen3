if (!Object.keys) {
    Object.keys = function(obj) {
        var keys = [];

        for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
                keys.push(i);
            }
        }

        return keys;
    };
}
// 物件轉陣列
if (!Array.prototype.forEach)
{
    Array.prototype.forEach = function(fun /*, thisp*/)
    {
        var len = this.length;
        if (typeof fun != "function")
            throw new TypeError();

        var thisp = arguments[1];
        for (var i = 0; i < len; i++)
        {
            if (i in this)
                fun.call(thisp, this[i], i, this);
        }
    };
}

function listTypeLayout(type){
    if(type=="list"){
        $("#btnListTypeIcon,#listtype_list_container").show();
        $("#btnListTypeList,#listtype_icon_container").hide();
    }else{
        $("#btnListTypeIcon,#listtype_list_container").hide();
        $("#btnListTypeList,#listtype_icon_container").show();
    }
}

function json2array(json){
    var result = [];
    var keys = Object.keys(json);
    keys.forEach(function(key){
        result.push(json[key]);
    });
    return result;
}

function gotoCourse(csid)
{
    top.location.href = "/"+csid;
}
