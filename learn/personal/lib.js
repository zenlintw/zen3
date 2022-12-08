	function doFunc(val) {
		switch (parseInt(val)) {
			case 1 : window.location.replace("info.php"); break;
			case 2 : window.location.replace("tagline.php"); break;
			case 3 : window.location.replace("mycourse_manage.php"); break;
			case 4 : var obj = getTarget();
				     if (obj) obj.chgMenuItem('SYS_06_01_001');			
		}
	}

	function getTarget() {
		var obj = null;
		switch (this.name) {			
			case "s_main"   : obj = parent.s_sysbar; break;
			case "c_main"   : obj = parent.c_sysbar; break;
			case "main"     : obj = parent.sysbar;   break;			
		}
		return obj;
	}
	
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
        
        /* 刪除大頭照 */
        $(function() {
            $('.dropbtn').click(function(){
                $.ajax({
                    'type': 'POST',
                    'url': '/mooc/controllers/user_ajax.php',
                    'data': {'action': 'delUserPic'},
                    'dataType': 'json',
                    'success': function(data) {
                        if (data.code === 1) {
                            alert(MSG_DELETE_SUCCESS);
                            var osrc = $('#MyPic').attr('src');
                            var posrc = $(window.parent.frames[1].document).find('.user-inner img').attr('src');
                            var timestamp = new Date().getTime();
                            if (osrc.indexOf('&timestamp=') === -1) {
                                $('#MyPic').attr('src', osrc + '&timestamp=' + timestamp);
                                $('#showPic').attr('src', osrc + '&timestamp=' + timestamp);
                                $(window.parent.frames[1].document).find('.user-inner img').attr('src', posrc + '&timestamp=' + timestamp);
                            } else {
                                $('#MyPic').attr('src', osrc.substr(0, osrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                                $('#showPic').attr('src', osrc.substr(0, osrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                                $(window.parent.frames[1].document).find('.user-inner img').attr('src', posrc.substr(0, posrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                            }
                        } else if (data.code === 0) {
                            alert(MSG_DELETE_NOTHING);
                        } else {
                            alert(MSG_DELETE_FAIL);
                        }
                    },
                    'error': function() {
                        if (window.console) {console.log('error');}
                    }
                });
            });
        });
        
