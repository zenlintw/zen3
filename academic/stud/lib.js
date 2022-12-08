    /*
    * $Id: lib.js,v 1.1 2010/02/24 02:38:44 saly Exp $
    **/
	function picReSize() {
		var node = document.getElementById("MyPic");
		var obj = document.getElementById("PicRoom");

		if ((node == null) || (obj == null)) return false;
		chkPic(node);
		obj.appendChild(document.createElement("br"));
		if ((typeof(node.fileSize) != "undefined") && (parseInt(node.fileSize) > 51200)) {
			alert(MSG_TooLarge);
		}
	}

	function chkPic(node) {
		var orgW = 0, orgH = 0;
		var demagnify = 0;

		orgW = parseInt(node.width);
		orgH = parseInt(node.height);
		if ((orgW > 150) || (orgH > 200)) {
			demagnify = (((orgW / 150) > (orgH / 200)) ? parseInt(orgW / 150) : parseInt(orgH / 200)) + 1;
			node.width  = parseInt(orgW / demagnify);
			node.height = parseInt(orgH / demagnify);
		}
		node.onload = function() {};
	}

	function rebMenu(lang) {
		if (typeof(parent.rebuildMenu) == "function") {
			parent.rebuildMenu(lang);
		}
	}