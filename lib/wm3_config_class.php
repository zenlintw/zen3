<?php
/**
 * �� �վ� wm3.conf �������D�� (�׼W�B�ק�Ǯե�)
 *
 * @since   2008-12-17
 * @author  Wiseguy Liang
 * @version $Id: wm3_config_class.php,v 1.1 2010/02/24 02:39:34 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/

class WM3config
{
	var	$others           = array(); // wm3.conf ���A���O�����D�����䥦�]�w
	var	$namevertualhosts = array(); // ��W�����D�� binding �W��
	var	$virtualhosts     = array(); // �S�] ServerName �������D��
	var	$wm_schools       = array(); // ���] ServerName �������D��
	var $load_config_file = '';
	var $save_config_file = '';
	var $is_loaded        = false;

	function WM3config()
	{
	    $this->load_config_file = sysDocumentRoot . '/config/wm3.conf';
	    $this->load();
	}
	
	/**
	 * ���o /config/wm3.conf �������D���]�w
	 */
	function load()
	{
	    if (!is_file($this->load_config_file))
	        return false;
		($wm3_conf = file($this->load_config_file)) and ($this->is_loaded = true);
		$this->virtualhosts = array();
		$flag = false;
	
		foreach ($wm3_conf as $line)
		{
			if ($flag)
			{
			    if (strcasecmp('</virtualhost>', trim($line)) === 0) $flag = false;
				$this->virtualhosts[$i][] = $line;
			}
			elseif (strpos(strtolower(ltrim($line)), '<virtualhost ') === 0)
			{
				$i = count($this->virtualhosts);
				$this->virtualhosts[$i][] = $line;
				$flag = true;
				continue;
			}
			elseif (strpos(strtolower(ltrim($line)), 'namevirtualhost ') === 0)
			{
			    $this->namevertualhosts[] = $line;
				continue;
			}
			else
				$this->others[] = $line;
		}
		$this->namevertualhosts = array_unique($this->namevertualhosts);

		foreach ($this->virtualhosts as $i => $virtualhost)
		{
			foreach ($virtualhost as $line)
			{
			    if (preg_match('/^\s*ServerName\s*(\S+)/i', $line, $regs))
			    {
			        $this->wm_schools[$regs[1]] = $this->virtualhosts[$i];
			        unset($this->virtualhosts[$i]);
			        break;
				}
			}
		}
		
		$c = count($this->others);
		for ($i = $c-1; $i>=0; $i--)
		    if (trim($this->others[$i]) == '')
		        unset($this->others[$i]);
			else
			    break;
	}

	/**
	 * �^�s /config/wm3.conf
	 */
	function save()
	{
	    if (empty($this->save_config_file))
	        $this->save_config_file = $this->load_config_file;
	        
	    if (!is_file($this->save_config_file))
	        return false;

	    if ($fp = fopen($this->save_config_file, 'w'))
	    {
			foreach ($this->others as $line)
			    fwrite($fp, $line);

			fwrite($fp, "\n");
			if (empty($this->wm_schools) && empty($this->virtualhosts))
			{
				foreach ($this->namevertualhosts as $line)
				    fwrite($fp, $line);
			}
			else
			{
				if (empty($this->namevertualhosts))
				    fwrite($fp, "NameVirtualHost *\n");
				else
					foreach ($this->namevertualhosts as $line)
					    fwrite($fp, $line);

				foreach ($this->virtualhosts as $hosts)
				{
				    fwrite($fp, "\n");
				    foreach ($hosts as $line)
				    	fwrite($fp, $line);
				}

				foreach ($this->wm_schools as $hosts)
				{
				    fwrite($fp, "\n");
				    foreach ($hosts as $line)
				    	fwrite($fp, $line);
				}
			}
			
			fclose($fp);
			return true;
		}
		else
		    return false;
	}

	/**
	 * �W�[�@�ӵ����D���]�w
	 *
	 * @param   string      	$hostname       �D�� domain name
	 * @param   array|string	$parameters     �]�w�ȡC�i�H�O��өΥH�}�C�Ǧh��
	 */
	function setHost($hostname, $parameters=false)
	{
	    if (is_array($parameters))
	    {
		    $this->wm_schools[$hostname] = array("<VirtualHost *>\n", "\tServerName {$hostname}\n");
		    foreach ($parameters as $parameter)
                $this->wm_schools[$hostname][] = "\t" . trim($parameter) . "\n";
            $this->wm_schools[$hostname][] = "</VirtualHost>\n";
	    }
	    elseif ($parameters)
	        $this->wm_schools[$hostname] = array("<VirtualHost *>\n", "\tServerName {$hostname}\n\t{$parameters}\n", "</VirtualHost>\n");
		else
		    $this->wm_schools[$hostname] = array("<VirtualHost *>\n", "\tServerName {$hostname}\n", "</VirtualHost>\n");
	}

	/**
	 * �R���@�ӵ����D��
	 *
	 * @param   string      	$hostname       �D�� domain name
	 * @return  bool                            true=���\�Ffalse=�䤣��
	 */
	function delHost($hostname)
	{
	    if (isset($this->wm_schools[$hostname]))
	    {
	        unset($this->wm_schools[$hostname]);
	        return true;
	    }
		else
		    foreach ($this->wm_schools as $host => $sets)
		        if (strcasecmp($hostname, $host))
			    {
			        unset($this->wm_schools[$host]);
			        return true;
				}
        return false;
	}

	/**
	 * ���o�ثe�w���������D��
	 */
	function getHosts()
	{
	    return array_keys($this->wm_schools);
	}

	/**
	 * ���o�ثe�B�@�����Ǯ� domain name �Ψ� school_id
	 */
	function getSchools()
	{
	    return dbGetAssoc('WM_school', 'school_host,school_id', 'school_host not like "[delete]%"');
	}

	/**
	 * ��s�Ǯժ������D���]�� wm3.conf
	 */
	function reGenerateVirtualHostConfig()
	{
		$news = array_keys($alls = $this->getSchools());
		if (count($news) > 1)
		{
		    list($server, $port) = explode(':', sysDBhost, 2);
		    if (empty($server)) $server = 'localhost';
		    if (!preg_match('/^\d+$/', $port)) $port = 3306;
		    $dsn = "WMAuthDSN {$server} {$port} " . sysDBprefix . '%s ' . sysDBaccoount . ' ' . sysDBpassword . ' /tmp/mysql.sock';
		    $olds = $this->getHosts();

			foreach (array_diff($olds, $news) as $host)
				$this->delHost($host);

			foreach (array_diff($news, $olds) as $host)
			    if ($alls[$host] == '10001')
			        $this->setHost($host);
			    else
					$this->setHost($host, sprintf($dsn, $alls[$host]));

			// $this->save_config_file = '/home/wm3/config/wm3a.conf';
			$this->save();
		}
	}

}

?>
