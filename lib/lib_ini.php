<?php
	/**
	 * 讀寫 ini 檔案
	 *
	 * @since   2004/07/28
	 * @author  ShenTing Lin
	 * @version $Id: lib_ini.php,v 1.1 2010/02/24 02:39:33 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/**
	 * 建立一個 ini 的設定資料
	 **/
	class assoc_data {
		var $assoc_ary;
		var $filename;
		var $has_sections;

		function assoc_data() {
			$this->assoc_ary    = array();
			$this->filename     = '';
			$this->has_sections = false;
		}

		/**
		 * 設定儲存的路徑 (包含檔名)
		 * @param string
		 * @return boolean : 成功或失敗
		 **/
		function setStorePath($filename) {
			$val = trim($filename);
			if (empty($val)) return false;
			$this->filename = $val;
			return true;
		}

		/**
		 * 取得儲存的路徑 (包含檔名)
		 * @return string : 路徑 (包含檔名)
		 **/
		function getStorePath() {
			return $this->filename;
		}

		/**
		 * 設定或新增資料
		 * @param string $section : 區段
		 * @param string $name    : 名稱
		 * @param string or array $value   : 要設定的值
		 *     這個變數可以接受兩種型態的資料，一為字串另則為陣列
		 *     字串 : 依照所指定的 $name 來新增或設定值
		 *     陣列 : 忽略 $name，直接將 $value 跟原來的資料合併
		 * @param boolean $replace: 取代整個 section，只有在 $value 為陣列時才有作用
		 **/
		function setValues($section, $name, $value, $replace=FALSE) {
			$data = array();

			if ($this->has_sections) {
				if (!isset($this->assoc_ary[$section])) {
					$this->assoc_ary[$section] = array();
				}
				$data = $this->assoc_ary[$section];
			} else {
				$data = $this->assoc_ary;
			}

			if (is_array($value)) {
				$temp = ($replace) ? $value : $data + $value;
				$data = $temp;
			} else {
				$name = trim($name);
				if (empty($name))
					$data[] = $value;
				else
					$data[$name] = $value;
			}

			if ($this->has_sections) {
				$this->assoc_ary[$section] = $data;
			} else {
				$this->assoc_ary = $data;
			}
		}

		/**
		 * 取得原來設定的值
		 * @param string $section : 區段
		 * @param string $name    : 名稱
		 * @return $data : 所要取得的值
		 *     假如所指定的 $name 不存在的話，回傳 false
		 **/
		function getValues($section, $name) {
			$section = trim($section);
			$name = trim($name);
			$data = ($this->has_sections) ? $this->assoc_ary[$section][$name] : $this->assoc_ary[$name];
			$data = false;
			if ($this->has_sections) {
				if (empty($section)) return false;
				if (isset($this->assoc_ary[$section]) && isset($this->assoc_ary[$section][$name])) {
					$data = $this->assoc_ary[$section][$name];
				}
			} else {
				if (isset($this->assoc_ary[$name])) {
					$data = $this->assoc_ary[$name];
				}
			}
			return trim($data);
		}

		/**
		 * 載入 ini 的設定資料 (字串型式)
		 * @param string  $str : 要解析的資料
		 **/
		function parseIniStr($str) {
			$section = NULL;
			$data = array();

			if ($temp = strtok($str, "\r\n")) {
				do {
					switch ($temp{0}) {
						case ';':
						case '#':
							// 註解
							break;
						case '[':
							if (!$this->has_sections) break;
							$pos = strpos($temp,'[');
							$section = substr($temp, $pos + 1, strpos($temp, ']', $pos) - 1);
							$data[$section] = array();
						default:
							$pos = strpos($temp, '=');
							if ($pos === FALSE) break;
							$name  = trim(substr($temp, 0, $pos));
							$value = trim(substr($temp, $pos + 1), ' "');
							if ($this->has_sections) {
								$data[$section][$name] = $value;
							} else {
								$data[$name] = $value;
							}
					}
				} while ($temp = strtok("\r\n"));
			}
			$this->assoc_ary = $data;
		}

		function restore() {
			$filename = trim($this->filename);
			if (empty($filename)) return false;
			touch($filename);
			$this->assoc_ary = parse_ini_file($filename, $this->has_sections);
			return true;
		}

		function store() {
			$content = '';

			if ($this->has_sections) {
				foreach ($this->assoc_ary as $key=>$elem) {
					$content .= "[" . $key . "]\n";
					foreach ($elem as $key2=>$elem2) {
						$content .= $key2 . " = \"" . $elem2 . "\"\n";
					}
				}
			} else {
				foreach ($this->assoc_ary as $key=>$elem) {
					$content .= $key . " = \"" . $elem . "\"\n";
				}
			}

			touch($this->filename);
			if (!$handle = fopen($this->filename, 'w')) {
				return false;
			}
			if (!fwrite($handle, $content)) {
				return false;
			}
			fclose($handle);
			return true;
		}

		function erase() {
			$this->assoc_ary = array();
			$filename = trim($this->filename);
			if (empty($file)) return false;
			return @unlink($this->filename);
		}
	}
?>
