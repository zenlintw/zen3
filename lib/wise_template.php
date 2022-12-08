<?php
	/**
	 * WM ���t template engine
	 *
	 * @since   2004/03/03
	 * @author  Wiseguy Liang
	 * @version $Id: wise_template.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 */

	/**
	 * �p�G�S�� file_put_contents() �N�ۤv����
	 */
	if(!function_exists('file_put_contents')){
		function file_put_contents($path, $content){
			if ($fp = fopen($path, 'w')){
				$len = fwrite($fp, $content);
				fclose($fp);
				return $len;
			}
			else
				return FALSE;
		}
	}

	/**
	 * �����쥻�� preg_match
	 */
	function wm_match($key, &$context){
		$keys = explode('+', $key, 2);
		if (($s = strpos($context, $keys[0])) !== FALSE){
			$ss = $s + strlen($keys[0]);
			if (($e = strpos($context, $keys[1], $ss)) !== FALSE){
				$match = substr($context, $ss, $e-$ss);
				return array($keys[0] . $match . $keys[1], $match);
			}
		}
		return FALSE;
	}

	/**
	 * �N���B�⦡�����
	 */
	function proc_element(&$origin, $key, $expression){
		static $s1 = array('%s', '"');
		static $s2 = array(123, '"');
		$s2[0] = addcslashes($origin, '\'');
		if (isset($expression[$key]) && strpos($expression[$key], '%s') !== FALSE) {
			eval('$origin=' . str_replace($s1, $s2, $expression[$key]) . ';');
		}
	}

	/**
	 * �}�l�w�q����
	 */
	class Wise_Template{

		var $template_content;	// �˪�����
		var $template_file;     // �˪������|�ɦW
		var $template_base;     // �˪����ɦ�m
		var $replaces;          // ���N�r��}�C (���ޭȨ��N��������)
		var $caching;			// �O�_ cache
		var $cache_path;		// cache �Ȧs�ؿ�
		var $cache_name;

		/** �غc�l
		 * $resource = �˪������|�ɦW
		 */
		function Wise_Template($source){
			$this->template_file = $source;
			$this->cache_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'caching_files' . DIRECTORY_SEPARATOR;
			$this->replaces = array();
			$this->caching = true;
			$this->cache_name = md5($_SERVER['REQUEST_METHOD'] . $_SERVER['REQUEST_URI']) . '.htm';

			if (file_exists($this->template_file)){
				$this->template_content = file_get_contents($this->template_file);
				$this->template_base = $this->getURI($source);
			}
			else{
				$this->template_content = $this->template_file;
				$this->template_file = FALSE;
				$this->template_base = FALSE;
			}
		}

		function getURI($sour){
			 list($protocal) = explode('/',  strtolower($_SERVER['SERVER_PROTOCOL']), 2);

			if (eregi('^[a-z]+://', $sour))
				return dirname($sour);
			elseif(ereg('^/', $sour)){
				if (ereg('^' . $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']), $sour))
					return $protocal . '://' .
						   $_SERVER['HTTP_HOST'] .
						   ($_SERVER['SERVER_PORT'] == '80' ? '' : ":{$_SERVER['SERVER_PORT']}") .
						   substr($sour, strlen($_SERVER['DOCUMENT_ROOT']));
				else
					return FALSE;
			}
			else
				return $protocal . '://' .
					   $_SERVER['HTTP_HOST'] .
					   ($_SERVER['SERVER_PORT'] == '80' ? '' : ":{$_SERVER['SERVER_PORT']}") .
					   dirname($_SERVER['PHP_SELF']) . '/' . dirname($sour) . '/';
		}

		/** �[�@�Ө��N�r��
		 *  $key = ��l�r��
		 *  $value = �����N�r��
		 */
		function add_replacement($key, $value, $block=FALSE){
			if ($block) {
				$pattern = wm_match($key, $this->template_content);
				if ($pattern) $this->replaces[$pattern[0]] = $value;
			} else {
				$this->replaces[$key] = $value;
			}
		}

		/** �[�@�� mysql RecordSet
		 *  $key = ��l�r��
		 *  $rs = mysql Recordset
		 *  $replace = �O�_�n���۩w�����N�A�n�h�ǤJ�۩w���}�C
		 */
		function add_recordset($key, $rs, $replace=NULL, $operation=NULL){
			$content = '';
			if (($match = wm_match($key, $this->template_content)) !== FALSE){
				if(is_array($replace)){												// �p�G���[ $replace ���p���N��
					if (is_array($rs))													// �O�_�O�G���}�C
						foreach($rs as $fields){
							if (is_array($operation)) array_walk($fields, 'proc_element', $operation);
							$content .= str_replace($replace, $fields, $match[1]);
						}
					elseif (is_resource($rs) || is_object($rs))							// �Ϊ̬O RecordSet
						while($fields = $rs->FetchRow()){
							if (is_array($operation)) array_walk($fields, 'proc_element', $operation);
							$content .= str_replace($replace, $fields, $match[1]);
						}
					else
						$content = $rs;
				}
				else{																// �S���h�`�ǥN��
					if (is_array($rs))													// �O�_�O�G���}�C
						foreach($rs as $fields)
							$content .= vsprintf($match[1], $fields);
					elseif (is_resource($rs) || is_object($rs))							// �Ϊ̬O RecordSet
						while($fields = $rs->FetchRow())
							$content .= vsprintf($match[1], $fields);
					else
						$content = $rs;
				}
				$this->replaces[$match[0]] = $content;								// �[�J�N���}�C
			}
		}

		/**
		 * �]�w caching �ҰʻP�_
		 */
		function set_caching($value){
			$this->caching = (bool)$value;
		}

		/**
		 * ���o caching �]�w��
		 */
		function get_caching(){
			return $this->caching;
		}

		/**
		 * �P�_�����O�_�w cached
		 */
		function is_cached(){
			return file_exists($this->cache_path . $this->cache_name);
		}

		/** �L�X�˪����G
		 *  $redirect_base = �O�_�n����
		 */
		function print_result($redirect_base = TRUE, $from_cache=NULL){
			if (is_null($from_cache)) $from_cache = $this->caching;
			echo $this->get_result($redirect_base, $from_cache);
		}

		/** �Ǧ^�˪����G�r��
		 *  $redirect_base = �O�_�n����
		 */
		function get_result($redirect_base = TRUE, $from_cache=NULL){
			if (is_null($from_cache)) $from_cache = $this->caching;
			if (!empty($this->replaces)){
				// krsort($this->replaces);
				$content = (($redirect_base && $this->template_base) ? "<base href=\"{$this->template_base}\">\n" : '') .
						   str_replace(array_keys($this->replaces), array_values($this->replaces), $this->template_content);
							// strtr($this->template_content, $this->replaces);
				if ($this->caching && is_dir($this->cache_path) && is_writable($this->cache_path)) file_put_contents($this->cache_path . $this->cache_name, $content);
			}
			return ($from_cache && file_exists($this->cache_path . $this->cache_name)) ?
					file_get_contents($this->cache_path . $this->cache_name) :
					$content;
		}
	}
?>
