<?php
	/**
	 * Ū�g ini �ɮ�
	 *
	 * @since   2004/07/28
	 * @author  ShenTing Lin
	 * @version $Id: lib_ini.php,v 1.1 2010/02/24 02:39:33 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/**
	 * �إߤ@�� ini ���]�w���
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
		 * �]�w�x�s�����| (�]�t�ɦW)
		 * @param string
		 * @return boolean : ���\�Υ���
		 **/
		function setStorePath($filename) {
			$val = trim($filename);
			if (empty($val)) return false;
			$this->filename = $val;
			return true;
		}

		/**
		 * ���o�x�s�����| (�]�t�ɦW)
		 * @return string : ���| (�]�t�ɦW)
		 **/
		function getStorePath() {
			return $this->filename;
		}

		/**
		 * �]�w�ηs�W���
		 * @param string $section : �Ϭq
		 * @param string $name    : �W��
		 * @param string or array $value   : �n�]�w����
		 *     �o���ܼƥi�H������ث��A����ơA�@���r��t�h���}�C
		 *     �r�� : �̷өҫ��w�� $name �ӷs�W�γ]�w��
		 *     �}�C : ���� $name�A�����N $value ���Ӫ���ƦX��
		 * @param boolean $replace: ���N��� section�A�u���b $value ���}�C�ɤ~���@��
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
		 * ���o��ӳ]�w����
		 * @param string $section : �Ϭq
		 * @param string $name    : �W��
		 * @return $data : �ҭn���o����
		 *     ���p�ҫ��w�� $name ���s�b���ܡA�^�� false
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
		 * ���J ini ���]�w��� (�r�ꫬ��)
		 * @param string  $str : �n�ѪR�����
		 **/
		function parseIniStr($str) {
			$section = NULL;
			$data = array();

			if ($temp = strtok($str, "\r\n")) {
				do {
					switch ($temp{0}) {
						case ';':
						case '#':
							// ����
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
