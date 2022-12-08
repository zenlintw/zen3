<?php
    /**
     * 所見即所得編輯視窗
     *
     * @since   2004/03/19
     * @author  ShenTing Lin
     * @version $Id: editor.php,v 1.1 2009-06-25 09:26:48 edi Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');

    class wmEditor {
        var $Value;
        var $setType;
        var $setUploadFun;
        var $cType;
        var $editor;
        var $config;
        var $editor_name;

        function wmEditor($editor_name='editor')
        {
            $this->Value   = '';
            $this->setType = false;
            $this->setUploadFun = false;
            $this->cType   = array();
            $this->editor  = '';
            $this->config  = array();
            $this->editor_name = $editor_name;
        }

        /**
         * 檢查瀏覽器
         * @return array
         **/
        function chkBrowser()
        {
            global $_SERVER;

            $browser = array('Gecko', '1.3');
            $userAgent = trim($_SERVER['HTTP_USER_AGENT']);
            if (strpos($userAgent, 'MSIE') !== false) {
                eregi('MSIE (.+);', $userAgent, $regs);
                $browser = array('MSIE', $regs[1]);
            }
            return $browser;
        }

        function setValue($value)
        {
            $this->Value = $value;
        }

        function setContType($id, $value)
        {
            $this->setType = true;
            $this->cType = array($id, $value);
        }

        function addContType($id, $value)
        {
            $this->setContType($id, $value);
        }
        
        function adduploadfun()
        {
            $this->setUploadFun = true;
        }

        /**
         * 設定是否要啟用圖片瀏覽
         * @version
         * @param
         * @return void
         **/
        function setImageBrowser($enable, $url)
        {
            $this->setConfig('ImageBrowser', $enable);
            $this->setConfig('ImageBrowserURL', $url);
        }

        function setLinkBrowser($enable, $url)
        {
            $this->setConfig('LinkBrowser', $enable);
            $this->setConfig('LinkBrowserURL', $url);
        }

        function setConfig($key, $val)
        {
            $this->config[$key] = $val;
        }

        /**
         * 強制指定使用哪一個編輯器
         * @param string $value : fckeditor, htmlarea
         * @return
         **/
        function setEditor($value)
        {
            $this->editor = $value;
        }

        // function generate($id='', $width='585', $height='350') {
        function generate($id='', $width='450', $height='320')
        {
            global $sysSession, $_SERVER, $sysConn;

            $bw = $this->chkBrowser();
            $ver = intval($bw[1]);

            $editor = $this->editor;
            if (empty($this->editor)) {
                // $editor = (($bw[0] == 'MSIE') && ($ver < 5.5)) ? 'fckeditor' : 'fckeditor2';
                $editor = (($bw[0] == 'MSIE') && ($ver < 5.5)) ? 'fckeditor' : 'ckeditor';
            } else {
                if (($bw[0] != 'MSIE') && ($editor == 'fckeditor')) $editor = 'ckeditor';
            }

            if ($editor == 'fckeditor') {
                include_once(sysDocumentRoot . '/lib/FCKeditor/fckeditor.php');
                if ($width  == 'auto') $width  = '100%';
                if ($height == 'auto') $height = '100%';
                $oFCKeditor = new FCKeditor;
                $oFCKeditor->ToolbarSet = 'WM';
                $oFCKeditor->BasePath = '/lib/FCKeditor/';
                $oFCKeditor->Value = $this->Value;
                $oFCKeditor->CreateFCKeditor($id, $width, $height);
                showXHTML_script('inline', 'var editor = null;');
            } else if ($editor === 'fckeditor2') {
                include_once(sysDocumentRoot . '/lib/fckeditor2/fckeditor.php');

                if ($width  == 'auto') $width  = '100%';
                if ($height == 'auto') $height = '100%';
                $oFCKeditor = new FCKeditor($id);
                $oFCKeditor->ToolbarSet = 'WM';
                $oFCKeditor->BasePath   = '/lib/fckeditor2/';
                $oFCKeditor->Value      = $this->Value;
                $oFCKeditor->Width      = $width;
                $oFCKeditor->Height     = $height;
                $oFCKeditor->Config     = $this->config;
                $oFCKeditor->Create();
                $js = <<< BOF
    var {$this->editor_name} = null;
    function editorFunc() {
        if (typeof FCKeditorAPI === "undefined") {
            setTimeout(function () {
                var node = document.getElementById('{$this->cType[0]}');
                if (node !== null) {
                    node.value = 0;
                }
            }, 500);

            {$this->editor_name} = {
                'target': null,
                'Focus' : function () {
                    if (this.target === null) {
                        return;
                    }
                    this.target.focus();
                },
                'getHTML': function () {
                    if (this.target === null) {
                        return;
                    }
                    return this.target.value;
                },
                'SetHTML': function (val) {
                    if (this.target === null) {
                        return;
                    }
                    this.target.value = val;
                }
            };

            var nodes = document.getElementsByTagName('textarea');
            for (var i = 0, c = nodes.length; i < c; i++) {
                if (nodes[i].name === "{$id}") {
                    {$this->editor_name}.target = nodes[i];
                    break;
                }
            }
            return;
        }
        // 重新取得 editor
        {$this->editor_name} = FCKeditorAPI.GetInstance("{$id}");
        {$this->editor_name}.getHTML = function () {
            return {$this->editor_name}.GetHTML();
        };
        {$this->editor_name}.setHTML = function (val) {
            return {$this->editor_name}.SetHTML(val, true);
        };
    }

    function getEditorInstance(id) {
        return FCKeditorAPI.GetInstance(id);
    }
BOF;
                if ($bw[0] == 'MSIE') {
                    $js .= 'window.attachEvent("onload", editorFunc);';
                } else {
                    $js .= 'window.addEventListener("load", editorFunc, false);';
                }
                showXHTML_script('inline', $js);

            } else {
                $trans = array(
                    'Big5'        => 'zh',
                    'GB2312'      => 'zh-cn',
                    'en'          => 'en',
                    'EUC-JP'      => 'ja',
                    'user_define' => 'en'
                );
                $lang = $trans[$sysSession->lang];

                if (is_numeric($width))  $width  .= 'px';
                if (is_numeric($height)) $height .= 'px';

                if ($editor !== 'rtnFckeditor') {
                    echo '<div style="letter-spacing: 0px;">';

                    showXHTML_input(
                        'textarea',
                        $id,
                        str_replace('&', '&amp;', $this->Value),
                        '',
                        'id="' . $id . '" class="cssInput" style="width: ' . $width . '; height: ' . $height . ';"'
                    );
                    echo '</div>';
                }

                $js = <<< BOF
    var {$this->editor_name} = null;
    function editorFunc() {
        {$this->editor_name} = CKEDITOR.replace('{$id}', {
            'language': '{$lang}',
        'width':['{$width}']
        });
        {$this->editor_name}.getHTML = function () {
            return {$this->editor_name}.getData();
        };
        {$this->editor_name}.setHTML = function (val) {
            return {$this->editor_name}.setData(val, function () {}, true);
        };
    }
    
    function editorFuncWithUpload() {
        {$this->editor_name} = CKEDITOR.replace('{$id}', {
            'language': '{$lang}',
            'filebrowserImageBrowseUrl' : '/lib/kcfinder/browse.php?type=images',
            'filebrowserImageUploadUrl' : '/lib/kcfinder/upload.php?type=images'
        });
        {$this->editor_name}.getHTML = function () {
            return {$this->editor_name}.getData();
        };
        {$this->editor_name}.setHTML = function (val) {
            return {$this->editor_name}.setData(val, function () {}, true);
        };
    }

    function getEditorInstance(id) {
        var ed = CKEDITOR.instances[id];

        if (ed === undefined) {
            ed = {
                'getHTML': function () { return ''; },
                'setHTML': function () {},
                'GetHTML': function () { return ''; },
                'SetHTML': function () {}
            };
        } else {
            ed.getHTML = function () {
                return ed.getData();
            };
            ed.GetHTML = function () {
                return ed.getData();
            };
            ed.setHTML = function () {
                return ed.setData(val, function () {}, true);
            };
            ed.SetHTML = function () {
                return ed.setData(val, function () {}, true);
            };
        }
        return ed;
    }
BOF;
                if ($bw[0] == 'MSIE') {
                    if ($this->setUploadFun) {
                        $js .= 'window.attachEvent("onload", editorFuncWithUpload);';
                    } else {
                        $js .= 'window.attachEvent("onload", editorFunc);';
                    }
                } else {
                    $headers = apache_request_headers();
                    if (preg_match('/Trident\/(\d+)/', $headers['User-Agent'], $regs) && intval($regs[1])> 6) { // IE 11
                        if ($this->setUploadFun) {
                            $js .= 'window.attachEvent("onload", editorFuncWithUpload);';
                        } else {
                            $js .= 'window.addEventListener("load", editorFunc, false);';
                        }
                    } else {
                        if ($this->setUploadFun) {
                            $js .= 'window.addEventListener("load", editorFuncWithUpload, false);';
                        } else {
                            $js .= 'window.addEventListener("load", editorFunc, false);';
                        }
                    }
                }

                if ($editor === 'rtnFckeditor') {
                    $html = '<div style="letter-spacing: 0px;">';
                    $html .= '<textarea name="' . $id . '" id="' . $id . '" class="cssInput" style="width: ' . $width . '; height: ' . $height . ';">' . $this->Value . '</textarea>';
                    $html .= '</div>';

                    return $html;
                } else {
                    showXHTML_script('include', '/lib/ckeditor/ckeditor.js');
                    showXHTML_script('inline', $js);
                }
            }
            if ($this->setType) {
                showXHTML_input('hidden', $this->cType[0], $this->cType[1], '', '');
            }
        }

        function getEditorHtml($id='', $width='450', $height='320')
        {
            ob_start();
            $this->generate($id, $width, $height);
            $ret = ob_get_contents();
            ob_end_clean();
            return $ret;
        }
    }