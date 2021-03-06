<?php
/**
 * Logfiles Object Module
 *
 * @author Javier Pastor (VSC55)
 * @license GPLv3
 */

namespace FreePBX\modules;

use \FreePBX\modules\Logfiles\Tail;
use \FreePBX\modules\Logfiles\logfiles_conf;

class Logfiles implements \BMO 
{
	const DEFAULT_SETTING = array(
		'dateformat' 	 => '%F %T',
		'rotatestrategy' => 'rotate',
		'queue_log' 	 => 'yes',
		'appendhostname' => 'no'
	);

	//emptry == on
	const DEFAULT_LOG_FILES = array(
		'name'		=> '',
		'debug'		=> '',
		'dtmf'		=> 'off',
		'error'		=> '',
		'fax'		=> 'off',
		'notice'	=> '',
		'verbose'	=> '',
		'warning'	=> '',
		'security'	=> 'off'
	);

	const FILES_ALLOW_ONLY_CLEAN = array(
		'backup.log', 
		'fail2ban', 
		'freepbx.log',
		'freepbx_security.log',
		'full',
		'queue_log',
		'ucp_err.log',
		'ucp_out.log',
		'xmpp_err.log',
		'xmpp_out.log'
	);

	public function __construct($freepbx = null) 
	{
		if ($freepbx == null) 
		{
			throw new \Exception("Not given a FreePBX Object");
		}

		$this->FreePBX 		 = $freepbx;
		$this->db 			 = $freepbx->Database;
		$this->config 		 = $freepbx->Config;
		$this->notifications = $freepbx->Notifications;

		$this->path = array();
		$this->path['etc_asterisk'] = $this->config->get('ASTETCDIR');
		$this->path['dir_www'] 		= $this->config->get('AMPWEBROOT');
		$this->path['dir_logs'] 	= $this->config->get('ASTLOGDIR');
		$this->path['logger.conf'] 	= $this->path['etc_asterisk'].'/logger.conf';

		
		// links:
		// 		/etc/asterisk/logger.conf -> /var/www/html/admin/modules/logfiles/etc/logger.conf
		
		// [directories]
		// astetcdir 	=> /etc/asterisk
		// astmoddir 	=> /usr/lib/asterisk/modules
		// astvarlibdir => /var/lib/asterisk
		// astagidir 	=> /var/lib/asterisk/agi-bin
		// astspooldir 	=> /var/spool/asterisk
		// astrundir 	=> /var/run/asterisk
		// astlogdir 	=> /var/log/asterisk
	}

	public function install() 
	{
		//set some defualts
		$first_install = $this->db->getOne('SELECT COUNT(*) FROM `logfile_settings`');
		
		if (!$first_install) 
		{
			//zero count (aka false) is a new install
			$this->setLogFiles('full');
			$this->setLogFiles('console');
		}

		$this->remove_file_link($this->path['logger.conf']);
	}

	public function uninstall() 
	{
		$this->remove_file_link($this->path['logger.conf']);
	}

	private function remove_file_link($file) 
	{
		if( file_exists($file) && is_link($file) )
		{
			unlink($file);
		}
	}

	// public function chownFreepbx()
	// {
	// 	$files = array();
	// 	return $files;
	// }

	public function backup() {}
	public function restore($backup) {}

	public function doConfigPageInit($page) {}
	public function getRightNav($request) {}
	public function getActionBar($request) {}

	public function showPage($page)
	{
		switch ($page) 
		{
			case "settings":
				$data_return = load_view(__DIR__.'/views/page.settings.php', array("lf" => $this) );
				break;

			case "logs":
				$data_return = load_view(__DIR__.'/views/page.logs.php', array("lf" => $this));
				break;

			default:
				$data_return = "";
		}
		return $data_return;
	}

	public function ajaxRequest($req, &$setting)
	{
		// ** Allow remote consultation with Postman **
		// ********************************************
		// $setting['authenticate'] = false;
		// $setting['allowremote'] = true;
		// ********************************************
		return true;
	}

	public function ajaxHandler()
	{
		// throw new Exception('Test Error!');
		$command = isset($_REQUEST['command']) ? trim($_REQUEST['command']) : '';
		switch ($command)
		{
			case "log_files":
				$list = $this->listFilesLog(false);
				$data = array_keys ($list);
				$data_return = array("status" => true, 'count' => count($list), 'data' => $data);
				break;

			case "log_file_read":
				$log_file    = isset($_REQUEST['log_file'])    ? trim($_REQUEST['log_file'])    : NULL;
				$log_lines   = isset($_REQUEST['log_lines'])   ? trim($_REQUEST['log_lines'])   : NULL;
				$log_filter  = isset($_REQUEST['log_filter'])  ? trim($_REQUEST['log_filter'])  : NULL;
				$log_resume  = isset($_REQUEST['log_resume'])  ? trim($_REQUEST['log_resume'])  : NULL;
				$log_session = isset($_REQUEST['log_session']) ? trim($_REQUEST['log_session']) : NULL;

				$log_lines = preg_replace("/[^0-9]/", "", $log_lines);
				if( ! is_numeric($log_lines) || $log_lines <= 0 )
				{
					$log_lines = 500;
				}
				$log_resume = ( strtolower($log_resume) == "true" ? false : true);
				$log_filter = ( empty($log_filter) ? false : $log_filter);

				if ( empty($log_file) )
				{
					$data_return = array("status" => false, "message" => _("Missing file name!"));
				}
				else
				{
					$log = $this->readFileLog($log_file, $log_lines, $log_filter, $log_resume, $log_session);
					if ($log['status'] == "OK")
					{
						$data_return = array(
							"status" => true,
							"file"  => $log_file,
							"info"  => $log['info'],
							"lines" => $log_lines,
							"data" => $log['lines']
							// "type_return" => $log['info']['type']
						);
					}
					else
					{
						$data_return = array("status" => false, "message" => $log['error'], "error" => $log['status'], "file"  => $log_file, "lines" => $log_lines );
					}
				}
				break;

			case "log_file_export":
				$log_file   = isset($_REQUEST['log_file']) ? trim($_REQUEST['log_file']) : NULL;
				if ( empty($log_file) || ! $this->exportFileLog($log_file) )
				{
					http_response_code(404);
					$this->showError404();
				}
				exit();
				break;

			case "log_file_destory":
				$log_file   = isset($_REQUEST['log_file']) ? trim($_REQUEST['log_file']) : NULL;
				if ( empty($log_file) )
				{
					$data_return = array("status" => false, "message" => _("Missing file name!"));
				}
				else
				{
					$destroy_log = $this->destroyFileLog($log_file);
					if ($destroy_log['status'] == "OK")
					{
						$data_return = array("status" => true, "message" => _("File removed successfully."), "file" => $log_file);
					}
					else if ($destroy_log['status'] == "OK_CLEAN")
					{
						$data_return = array("status" => true, "message" => _("File clean successfully."), "file" => $log_file);
					}
					else
					{
						$data_return = array("status" => false, "message" => $destroy_log['error'], "error" => $destroy_log['status'], "file"  => $log_file );
					}
				}
				break;

			case "settings_get":
				$setting = isset($_REQUEST['setting']) ? $_REQUEST['setting'] : NULL;

				if ( empty($setting) )
				{
					$data_return = array("status" => false, "message" => _("Missing data!"));
				}
				elseif (! self::isSettingAllowed($setting))
				{
					$data_return = array("status" => false, "message" => _("Setting not allowed!"), "setting" => $setting);
				}
				else
				{
					$value = $this->getSetting($setting);
					$data_return = array("status" => true, 'value' => $value);
				}
				break;

			case "settings_set":
				$setting = isset($_REQUEST['setting']) ? $_REQUEST['setting'] : NULL;
				$value 	 = isset($_REQUEST['val'])     ? $_REQUEST['val'] : NULL;

				if ( empty($setting) || empty($value) )
				{
					$data_return = array("status" => false, "message" => _("Missing data!"));
				}
				elseif (! self::isSettingAllowed($setting))
				{
					$data_return = array("status" => false, "message" => _("Setting not allowed!"), "setting" => $setting);
				}
				else
				{
					if ($this->setSetting($setting, $value))
					{
						$data_return = array("status" => true, "message" => _("Successful Update"));
					} 
					else
					{
						$data_return = array("status" => false, "message" => _("Update process failed!"));
					}
				}
				needreload();
				break;
			
			case "logfiles_get_all":
				$data_return = array("status" => true, "data" => $this->getLogfilesAll());
				break;
			
			case "logfiles_is_exist_file_name":
				$name = isset($_REQUEST['namefile']) ? $_REQUEST['namefile'] : NULL;
				if ( empty($name) )
				{
					$data_return = array("status" => false, "message" => _("Missing name!"));
				} 
				else
				{
					$data_return = array("status" => true, 'exist' => $this->isExistLogFiles($name));
				}
				break;

			case "logfiles_set":
				$name = isset($_REQUEST['namefile']) ? $_REQUEST['namefile'] : NULL;
				$data = isset($_REQUEST['data']) ? json_decode($_REQUEST['data']) : NULL;
				if ( empty($name))
				{
					$data_return = array("status" => false, "message" => _("Missing name!"));
				}
				else if ( empty($data) )
				{
					$data_return = array("status" => false, "message" => _("Missing data!"));
				}
				else
				{
					//convert stdClass to array
					$data = (array) $data;
					if ( $this->setLogFiles($name, $data) )
					{
						$data_return = array("status" => true, "message" => _("Save Successful"));
					}
					else
					{
						$data_return = array("status" => false, "message" => _("Save Failed!"));
					}
				}
				needreload();
				break;

			case "logfiles_destory":
				$name = isset($_REQUEST['namefile']) ? $_REQUEST['namefile'] : NULL;
				if ( empty($name) )
				{
					$data_return = array("status" => false, "message" => _("Missing name!"));
				} 
				else
				{
					if (!$this->isExistLogFiles($name))
					{
						$data_return = array("status" => false, "message" => _("The filename not exist!"));
					}
					else
					{
						if ( $this->destoryLogFiles($name) )
						{
							$data_return = array("status" => true, "message" => _("Remove Successful"));
						}
						else
						{
							$data_return = array("status" => false, "message" => _("Failure Removal Process!"));
						}
				 	}
				}
				break;

			defualt:
				$data_return = array("status" => false, "message" => _("Command not found!"), "command" => $command);
			
		}
		return $data_return;
	}










	public static function isSettingAllowed($setting)
	{
		$data_return = false;
		if($setting)
		{
			$data_return = array_key_exists(strtolower($setting) , self::DEFAULT_SETTING);
		}
		return $data_return;
	}

	public function getSetting($setting)
	{
		$default = self::DEFAULT_SETTING;
		$return_date = "";
		if (self::isSettingAllowed($setting))
		{
			$setting = strtolower($setting);
			$sql = sprintf("SELECT `value` FROM `logfile_settings` WHERE `key` = '%s'", $setting);
			$result = $this->db->getOne($sql);
			if ($result)
			{
				$return_date = $result;
			}
			else
			{
				if ( array_key_exists($setting, $default) )
				{
					$return_date = $default[$setting];
				}
			}
		}
		return $return_date;
	}

	public function setSetting($setting, $value)
	{
		$data_return = false;
		if (self::isSettingAllowed($setting))
		{
			$setting = strtolower($setting);	
			$ret = $this->db->prepare('REPLACE INTO `logfile_settings` (`key`, `value`) VALUES (?, ?)')->execute( [$setting, $value] );
			$data_return = ! db_e($ret);
		}
		return $data_return;
	}






	public function countLogFiles()
	{
		$count = $this->db->getOne('SELECT COUNT(*) FROM `logfile_logfiles`');
		return $count;
	}

	public function isExistLogFiles($name)
	{
		$count = $this->db->getOne("SELECT COUNT(*) FROM `logfile_logfiles` WHERE `name` = '".$name."'");
		return ($count == 1) ? true : false;
	}

	public function getLogfilesAll()
	{
		$ret = array();
		$sql = 'SELECT * FROM `logfile_logfiles`';
		foreach ($this->db->query($sql, DB_FETCHMODE_ASSOC) as $row) {
			$ret[] = (array)$row;
		}
		return $ret;
	}

	public function getLogFiles($name = NULL)
	{
		$query = $this->db->prepare( 'SELECT * FROM `logfile_logfiles`' . (($name) ? ' where `name` = ?' : '') );
		if ($name)
		{
			$query->execute(array($name));
		}
		else
		{
			$query->execute();
		}
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function setLogFiles($name, $data = array())
	{
		$has_security_option = version_compare($this->config->get("ASTVERSION"),'11.0','ge');
		$default = self::DEFAULT_LOG_FILES;
		$data_return = false;
		
		if ($name)
		{
			$name = strtolower($name);
			// Two arrays are generated, one with the columns and the other with the
			// values to avoid problems with the order of the columns in the table.
			$columns = "";
			$arr_val = array ();

			foreach ($default as $key => $val_default)
			{
				$columns .= sprintf("%s`%s`", (empty($columns) ? '' : ', '), $key);

				$new_val =  $val_default;
				if ($key == 'name')
				{
					$new_val = $name;
				}
				elseif ($key == "security" && ! $has_security_option) 
				{
					$new_val = 'off';
				}
				elseif ( array_key_exists($key, $data) )
				{
					$new_val = $data[$key];
				}
				array_push($arr_val, $new_val);
			}
			$sql_args = implode(',', array_fill(0, count($arr_val), '?'));
			$ret = $this->db->prepare('REPLACE INTO `logfile_logfiles` ('.$columns.') VALUES ('.$sql_args.')')
							->execute($arr_val);
			$data_return = ! db_e($ret);
		}
		return $data_return;
	}

	public function destoryLogFiles($name)
	{
		$data_return = false;
		if ($name)
		{
			if ( $this->isExistLogFiles($name) )
			{
				$sql = 'DELETE FROM `logfile_logfiles` WHERE `name` = :name';
				$sth = $this->db->prepare($sql);
				$sth->execute([
					":name" => $name
				]);
			}
			$data_return = ! $this->isExistLogFiles($name);
		}
		return $data_return;
	}






	public function showError404()
	{
		echo "<h1>404 Not Found</h1>";
		echo "The page that you have requested could not be found.";
	}



	public function listFilesLog()
	{
		$data_return = array();
		$path_logs = $this->path['dir_logs'];

		$dir = scandirr($path_logs, true);
		if( ! is_array($dir) )
		{
			$dir = array();
		}
		//only show files, relative to $amp_conf['ASTLOGDIR']
		foreach ($dir as $k => $v) 
		{
			if (is_file($v))
			{
				$filename = str_replace($path_logs . DIRECTORY_SEPARATOR, '', $v); //relative paths only
				$data_return[$filename] = $v;
			}
		}
		return $data_return;
	}

	public function isExistFileLog($name_file) 
	{
		return array_key_exists($name_file, $this->listFilesLog(true));
	}

	public function getFullPathFileLog($name_file) 
	{
		$data_return = "";
		if ( $this->isExistFileLog($name_file) )
		{
			$all_logs = $this->listFilesLog(true);
			$data_return = $all_logs[$name_file];
		}
		return $data_return;	
	}

	public function exportFileLog($name_file)
	{
		if ( $this->isExistFileLog($name_file) )
		{
			$path_file = $this->getFullPathFileLog($name_file);
			if ( is_readable($path_file) )
			{
				$file_name = basename($path_file);
				$file_size = filesize($path_file);
				header('Content-Type: text/plain');
				header("Content-Transfer-Encoding: Binary"); 
				header( sprintf ('Content-disposition: attachment; filename="%s"', $file_name) );
				header("Content-Length: ".$file_size);
				readfile($path_file);
				return true;
			}
		}
		return false;
	}

	public function destroyFileLog($name_file) 
	{
		$data_return = array(
			"status" => "INIT",
			"error"  => "",
		);

		if ( ! $this->isExistFileLog($name_file) )
		{
			$data_return['status'] = "ERROR_FILE_NOT_FOUND";
			$data_return['error'] = _('The file was not found!');
		} 
		else 
		{
			$log_file = $this->getFullPathFileLog($name_file);
			if ( ! file_exists($log_file) )
			{
				$data_return['status'] = "ERROR_FILE_NOT_EXIST";
				$data_return['error'] = _('The log file does not exist!');
			}
			else if ( ! is_file($log_file) )
			{
				$data_return['status'] = "ERROR_IS_NOT_FILE";
				$data_return['error'] = _('The specified name is not from a file!');
			}
			else if ( ! is_writable($log_file) )
			{
				$data_return['status'] = "ERROR_NO_WRITE_PERMISSION";
				$data_return['error'] = _('There is no permission to write this log file!');
			}
			else
			{
				$only_clean = self::FILES_ALLOW_ONLY_CLEAN;
				if ( in_array($name_file, $only_clean) ) 
				{
					$data_return['status'] = "CLEAN_FILE";
					file_put_contents($log_file, "");
					$data_return['status'] = "OK_CLEAN";
				}
				else
				{
					$data_return['status'] = "DELETE_FILE";
					unlink($log_file);
					$data_return['status'] = "OK";
				}
			}
		}
		return $data_return;
	}

	public function readFileLog($name_file, $lines = 500, $filter = false, $new_session = true, $tail_session = null)
	{
		$data_return = array(
			"status" => "INIT",
			"error"  => "",
			"lines"  => array(),
			"type" 	 => 'NONE'
		);

		$log_file = NULL;

		// Start Checking...
		if ( ! $this->isExistFileLog($name_file) )
		{
			$data_return['status'] = "ERROR_FILE_NOT_FOUND";
			$data_return['error'] = _('The file was not found!');
		}
		else
		{
			$log_file = $this->getFullPathFileLog($name_file);
			if ( ! file_exists($log_file) )
			{
				$data_return['status'] = "ERROR_FILE_NOT_EXIST";
				$data_return['error'] = _('The log file does not exist!');
			}
			else if ( ! is_file($log_file) )
			{
				$data_return['status'] = "ERROR_IS_NOT_FILE";
				$data_return['error'] = _('The specified name is not from a file!');
			}
			else if ( ! is_readable($log_file) )
			{
				$data_return['status'] = "ERROR_NO_READING_PERMISSION";
				$data_return['error'] = _('There is no permission to read this log file!');
			}
			else if ( ! is_numeric($lines) )
			{
				$data_return['status'] = "ERROR_LINES_NOT_IS_NUMBER";
				$data_return['error'] = _('The specified lines value is not a numeric value!');
			}
			else
			{
				if ( $filter )
				{
					$regex_check = @preg_match('/'.$filter.'/', null);
					if ( $regex_check !== 0 )
					{
						$data_return['status'] = "ERROR_FILTER_INVALID";
						$data_return['error'] = _('Invalid pattern to filter!');
					}
				}
			}

			if ($data_return['status'] == "INIT")
			{
				// No detected errors
				$data_return['status'] = "CHECK_FILE_OK";
			}
		}


		//Checking Ok, read file.
		if ($data_return['status'] == "CHECK_FILE_OK")
		{
			$data_return['status'] = "READ_FILE";

			$file_read 	= new Tail($log_file, $lines);
			if ( ! empty($tail_session) )
			{
				$file_read->set_id_tail( $tail_session );
			}
			$out_log 	= $file_read->out($new_session);

			$data_return['info'] = $file_read->get_info_out();

			if ( $filter )
			{
				$data_return['status'] = "APPLY_FILTER";
				$out_log = preg_grep('/'.$filter.'/', $out_log);
			}

			$data_return['status'] = "APPLY_HIGHLIGHT";
			$data_return['lines'] = $this->highlight($out_log);	

			$data_return['status'] = "OK";
		}

		return $data_return;
	}

	public function highlight($lines)
	{
		$data_return = array();
		
		@session_start();
		if ( ! isset($_SESSION['mod_logfiles_read_log_channels']) )
		{
			$_SESSION['mod_logfiles_read_log_channels'] = array();
		}
		// $channels = array();

		$span_html = '<span class="%s">%s</span>';
		foreach($lines as $line)
		{
			$line_html = htmlentities($line, ENT_COMPAT | ENT_HTML401, "UTF-8");
			switch (true)
			{
				case strpos($line, 'INFO'):
					$line = sprintf($span_html, "beige", $line_html);
					break;
				case strpos($line, 'WARNING'):
					$line = sprintf($span_html, "orange", $line_html);
					break;
				case strpos($line, 'DEBUG'):
					$line = sprintf($span_html, "green", $line_html);
					break;
				case strpos($line, 'UPDATE'):
				case strpos($line, 'NOTICE'):
					$line = sprintf($span_html, "cyan", $line_html);
					break;
				case strpos($line, 'FATAL'):
				case strpos($line, 'CRITICAL'):
				case strpos($line, 'ERROR'):
					$line = sprintf($span_html, "red", $line_html);
					break;
				case strpos($line, 'SECURITY'):
					$line = sprintf($span_html, "yellow", $line_html);
					break;
				default:
					$line_html = htmlentities($line, ENT_NOQUOTES, "UTF-8");
					$line = $this->highlight_asterisk($line_html, $_SESSION['mod_logfiles_read_log_channels']);
					// $line = $this->highlight_asterisk($line_html, $channels);
			}
			$data_return[] = $line;
		}
		return $data_return;
	}

	/*
	* Highlight asterisk applications
	*/
	public function highlight_asterisk($line, &$channels) 
	{
		$span_html = '<span class="%s">%s</span>';

		//for i in `asterisk -rx 'core show applications'|awk '{print $1}'|grep -v -|sed 's/://g'`; do echo -n $i'|'; done
		static $apps = 'AddQueueMember|ADSIProg|AELSub|AGI|Answer|Authenticate|BackGround|BackgroundDetect|Bridge|Busy|CallCompletionCancel|CallCompletionRequest|CELGenUserEvent|ChangeMonitor|ChanIsAvail|ChannelRedirect|ChanSpy|ClearHash|ConfBridge|Congestion|ContinueWhile|ControlPlayback|DAHDIAcceptR2Call|DAHDIBarge|DAHDIRAS|DAHDIScan|DAHDISendKeypadFacility|DateTime|DBdel|DBdeltree|DeadAGI|Dial|Dictate|Directory|DISA|DumpChan|EAGI|Echo|EndWhile|Exec|ExecIf|ExecIfTime|ExitWhile|ExtenSpy|ExternalIVR|Flash|Flite|ForkCDR|GetCPEID|Gosub|GosubIf|Goto|GotoIf|GotoIfTime|Hangup|IAX2Provision|ICES|ImportVar|Incomplete|Log|Macro|MacroExclusive|MacroExit|MacroIf|MailboxExists|MeetMe|MeetMeAdmin|MeetMeChannelAdmin|MeetMeCount|Milliwatt|MinivmAccMess|MinivmDelete|MinivmGreet|MinivmMWI|MinivmNotify|MinivmRecord|MixMonitor|Monitor|Morsecode|MP3Player|MSet|MusicOnHold|MYSQL|NBScat|NoCDR|NoOp|Originate|Page|Park|ParkAndAnnounce|ParkedCall|PauseMonitor|PauseQueueMember|Pickup|PickupChan|Playback|PlayTones|PrivacyManager|Proceeding|Progress|Queue|QueueLog|RaiseException|Read|ReadExten|ReadFile|ReceiveFAX|Record|RemoveQueueMember|ResetCDR|RetryDial|Return|Ringing|SayAlpha|SayCountPL|SayDigits|SayNumber|SayPhonetic|SayUnixTime|SendDTMF|SendFAX|SendImage|SendText|SendURL|Set|SetAMAFlags|SetCallerPres|SetMusicOnHold|SIPAddHeader|SIPDtmfMode|SIPRemoveHeader|SLAStation|SLATrunk|SMS|SoftHangup|SpeechActivateGrammar|SpeechBackground|SpeechCreate|SpeechDeactivateGrammar|SpeechDestroy|SpeechLoadGrammar|SpeechProcessingSound|SpeechStart|SpeechUnloadGrammar|StackPop|StartMusicOnHold|StopMixMonitor|StopMonitor|StopMusicOnHold|StopPlayTones|System|TestClient|TestServer|Transfer|TryExec|TrySystem|UnpauseMonitor|UnpauseQueueMember|UserEvent|Verbose|VMAuthenticate|VMSayName|VoiceMail|VoiceMailMain|Wait|WaitExten|WaitForNoise|WaitForRing|WaitForSilence|WaitMusicOnHold|WaitUntil|While|Zapateller';

		//Match Channel ID
		$colors = array("silver", "seagreen", "lime", "red", "orange", "green", "yellow", "magenta", "pink");

		if ( preg_match('/\[(\d*)\]/', $line, $matches) )
		{
			if( ! isset($channels[$matches[1]]) )
			{
				$channels[$matches[1]] = $colors[rand(0,count($colors)-1)];
			}
			$span = sprintf($span_html, $channels[$matches[1]], $matches[1]);
			$line = str_replace('['.$matches[1].']', '['.$span.']', $line);
		}

		//match any app
		$span = sprintf($span_html, "app", "$0");
		$line = preg_replace('/(?:' . $apps . ')(?=\()/', $span, $line, 1);

		//match arguments
		$span = sprintf($span_html, "appargs", "$0");
		$line = preg_replace('/(?<=\(\").*(?=\"\,)/', $span, $line, 1);
		$line = preg_replace('/(?<=\,( )\").*(?=\"\))/', $span, $line, 1);

		return $line;
	}

	function dialplanHooks_get_configOld($engine)
	{
		switch ($engine)
		{
			case 'asterisk':
				$logfiles_conf = logfiles_conf::create();
				$has_security_option = version_compare($this->config->get("ASTVERSION"),'11.0','ge');

				//set logfile data to be generated
				//dbug('here', (isset($logfiles_conf) && ($logfiles_conf instanceof logfiles_conf)), 1);
				if ( ! isset($logfiles_conf) || ! ($logfiles_conf instanceof logfiles_conf) )
				{
					dbug('NOT GENERATING LOGGER CONFIGS AS $logfiles_conf IS NOT SET!');
					return false;
				}

				foreach (self::DEFAULT_SETTING as $k => $v)
				{
					$value = $this->getSetting($k);
					switch ($k)
					{
						case 'appendhostname':
							if ( trim($value) === "yes" )
							{
								$this->notifications->add_warning("Asterisk Logfile", "Warning", _("appendhostname is set to: Yes."), _("Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to: no."), "config.php?display=logfiles_settings", true, true);
							}
						case 'dateformat':
						case 'queue_log':
						case 'rotatestrategy':
							if ($value)
							{
								$logfiles_conf->addLoggerGeneral($k, $value);
							}
							break;

						default:
							break;
					}
				}

				foreach ($this->getLogfilesAll() as $k => $v)
				{
					$name = $v['name'];
					unset($v['name']);
					$name_opt = array();

					foreach ($v as $opt => $set)
					{
						switch ($opt)
						{
							case 'verbose':
								if (is_numeric($set) || $set == '*')
								{
									$name_opt[] = 'verbose(' . $set . ')';
								}
								elseif ($set === 'on')
								{
									$name_opt[] = $opt;
								}
								break;

							default:
								if ($set === 'on')
								{
									if ($has_security_option || $opt != 'security')
									{
										$name_opt[] = $opt;
									}
								}
								break;
						}
					}

					if( ! empty($name) && ! empty($name_opt) )
					{
						$logfiles_conf->addLoggerLogfiles($name, implode(',', $name_opt));
					}
				}
				break;
		}
	}

	public function disabled__genConfig()
	{
		$general = "";
		foreach (self::DEFAULT_SETTING as $k => $v)
		{
			$value = $this->getSetting($k);
			switch ($k)
			{
				case 'appendhostname':
					if ( trim($value) === "yes" )
					{
						$this->notifications->add_warning("Asterisk Logfile", "Warning", _("appendhostname is set to: Yes."), _("Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to: no."), "config.php?display=logfiles_settings", true, true);
					}
				case 'dateformat':
				case 'queue_log':
				case 'rotatestrategy':
					if ($value)
					{
						$general .= $k . '=' . $value . "\n";
					}
					break;

				default:
					break;
			}
		}

		$logfiles = "";
		$has_security_option = version_compare($this->config->get("ASTVERSION"),'11.0','ge');
		foreach ($this->getLogfilesAll() as $k => $v)
		{
			$name = $v['name'];
			unset($v['name']);
			$name_opt = array();

			foreach ($v as $opt => $set)
			{
				switch ($opt)
				{
					case 'verbose':
						if (is_numeric($set) || $set == '*')
						{
							$name_opt[] = 'verbose(' . $set . ')';
						}
						elseif ($set === 'on')
						{
							$name_opt[] = $opt;
						}
						break;

					default:
						if ($set === 'on')
						{
							if ($has_security_option || $opt != 'security')
							{
								$name_opt[] = $opt;
							}
						}
						break;
				}
			}

			if( ! empty($name) && ! empty($name_opt) )
			{
				$logfiles .= $name . ' => ' . implode(',', $name_opt) . "\n";
			}
		}		

		$conf['logger_general_additional.conf']  = $general;
		$conf['logger_logfiles_additional.conf'] = $logfiles;

		return $conf;
	}

	public function disabled__writeConfig($conf)
	{
		$this->FreePBX->WriteConfig($conf);
	}

}
