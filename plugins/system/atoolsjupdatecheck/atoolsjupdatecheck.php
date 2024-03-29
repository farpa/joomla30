<?php
/**
 * @package AdminTools
 * @subpackage UpdateCheck
 * @copyright Copyright (c)2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

// Uncomment the following line to enable debug mode
//define('ATJUPDATEDEBUG',1);

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.2.7', '>=')) return;

// Joomla! version check
if(version_compare(JVERSION, '2.5', 'lt') && version_compare(JVERSION, '1.6.0', 'ge')) {
	// Joomla! 1.6.x and 1.7.x: sorry fellas, no go.
	return;
}

jimport('joomla.application.plugin');

class plgSystemAtoolsjupdatecheck extends JPlugin
{
	public function onAfterRender()
	{
		// Make sure Admin Tools is installed
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_admintools')) {
			return;
		}
		
		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		
		// If another extension using the Joomla! update model is already loaded, or if
		// another update check plugin has already run, bail out.
		if(class_exists('AdmintoolsModelJupdate')) return;
		
		// Is Admin Tools installed?
		$jUpdateFile = JPATH_ADMINISTRATOR.'/components/com_admintools/models/jupdate.php';
		if(!JFile::exists($jUpdateFile)) return;
		
		// Is Admin Tools enabled?
		$query = $db->getQuery(true)
			->select($db->qn('enabled'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('com_admintools'))
			->where($db->qn('type').' = '.$db->q('component'));
		$db->setQuery($query);
		$enabled = $db->loadResult();
		if(!$enabled) return;
		
		// Is the One Click Action plugin enabled?
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onOneClickActionEnabled');
		if(empty($jResponse)) return;
		$status = false;
		foreach($jResponse as $response)
		{
			$status = $status || $response;
		}
		if(!$status) return;

		// Do we have to run (at most once per 3 hours)?
		jimport('joomla.html.parameter');
		jimport('joomla.application.component.helper');
		// FIX 2.1.13: Load the component parameters WITHOUT using JComponentHelper
		$db = JFactory::getDbo();
		$sql = $db->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('com_admintools'))
			->where($db->qn('type').' = '.$db->q('component'));
		$db->setQuery($sql);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params->loadString($rawparams, 'JSON');
			$last = $params->get('plg_atoolsjupdatecheck', 0);
		} else {
			$params->loadJSON($rawparams);
			$last = $params->getValue('plg_atoolsjupdatecheck', 0);
		}
		$now = time();
		if(!defined('ATJUPDATEDEBUG') && (abs($now-$last) < 10800)) {
			return;
		}
		
		// Use a 20% chance of running; this allows multiple concurrent page
		// requests to not cause double update emails being sent out.
		$random = rand(1, 5);
		if(!defined('ATJUPDATEDEBUG') && ($random != 3)) {
			return;
		}
		
		// Update last run status
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params->set('plg_atoolsjupdatecheck', $now);
		} else {
			$params->setValue('plg_atoolsjupdatecheck', $now);
		}
		$db = JFactory::getDBO();
		$data = $params->toString('JSON');
		$sql = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('params').' = '.$db->q($data))
				->where($db->qn('element').' = '.$db->q('com_admintools'))
				->where($db->qn('type').' = '.$db->q('component'));
		$db->setQuery($sql);
		
		// If a DB error occurs, return null
		$db->query();
		if($db->getErrorNum()) return;
		
		// Load the Live Update code
		include_once JPATH_ADMINISTRATOR.'/components/com_admintools/version.php';
		require_once $jUpdateFile;
		
		// If this is not the Professional release, bail out. So far I have only
		// received complaints about this feature from users of the Core release
		// who never bothered to read the documentation. FINE! If you are bitching
		// about it, you don't get this feature (unless you are a developer who can
		// come here and edit the code). Fair enough.
		if(!defined('ADMINTOOLS_PRO')) return;
		if(!ADMINTOOLS_PRO) return;
		
		// OK, cool. Let's run Joomla! Update fetch, OK?
		if(interface_exists('JModel')) {
			$juModel = JModelLegacy::getInstance('Jupdate','AdmintoolsModel');
		} else {
			$juModel = JModel::getInstance('Jupdate','AdmintoolsModel');
		}
		$updateInfo = $juModel->getUpdateInfo();
		
		if(!defined('ATJUPDATEDEBUG') && ($updateInfo->status !== true)) {
			return; // No updates available
		}
		
		// If we're here, we have updates. Let's create an OTP.
		$uri = JURI::base();
		$uri = rtrim($uri,'/');
		$uri .= (substr($uri,-13) != 'administrator') ? '/administrator/' : '/';
		$link = 'index.php?option=com_admintools&view=jupdate';
		
		$superAdmins = $this->_getSuperAdministrators();
		if(empty($superAdmins)) return;
		
		$this->_loadLanguage();
		$email_subject	= JText::_('PLG_ATOOLSJUPDATECHECK_EMAIL_SUBJECT');
		$email_body		= JText::_('PLG_ATOOLSJUPDATECHECK_EMAIL_BODY');
		
		$newVersion = '';
		if($updateInfo->current['version'] && ($updateInfo->current['version'] != $updateInfo->installed['version'])) {
			$newVersion = $updateInfo->current['version'];
		} elseif($updateInfo->lts['version'] && ($updateInfo->lts['version'] != $updateInfo->installed['version'])) {
			$newVersion = $updateInfo->lts['version'];
		} elseif($updateInfo->sts['version'] && ($updateInfo->sts['version'] != $updateInfo->installed['version'])) {
			$newVersion = $updateInfo->sts['version'];
		}
		
		if(empty($newVersion)) return;
		
		$jconfig = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$sitename = $jconfig->get('sitename');
		} else {
			$sitename = $jconfig->getValue('config.sitename');
		}
		$substitutions = array(
			'[NEWVERSION]'		=> $newVersion,
			'[CURVERSION]'		=> $updateInfo->installed['version'],
			'[SITENAME]'		=> $sitename
		);
		
		// If Admin Tools Professional is installed, fetch the administrator secret key as well
		$adminpw = '';
		$modelFile = JPATH_ROOT.'/administrator/components/com_admintools/models/storage.php';
		if(@file_exists($modelFile)) {
			include_once $modelFile;
			if(class_exists('AdmintoolsModelStorage')) {
				if(interface_exists('JModel')) {
					$model = JModelLegacy::getInstance('Storage','AdmintoolsModel');
				} else {
					$model = JModel::getInstance('Storage','AdmintoolsModel');
				}
				$adminpw = $model->getValue('adminpw','');
			}
		}
		
		foreach($superAdmins as $sa)
		{
			$otp = plgSystemOneclickaction::addAction($sa->id, $link);
			if(is_null($otp)) {
				// If the OTP is null, a database error occurred
				return;
			} elseif(empty($otp)) {
				// If the OTP is empty, an OTP for the same action was already
				// created and it hasn't expired.
				continue;
			}
			$emaillink = $uri.'index.php?oneclickaction='.$otp;
			if(!empty($adminpw)) {
				$emaillink .= '&'.urlencode($adminpw);
			}
			
			$substitutions['[LINK]'] = $emaillink."\n\n".JText::_('PLG_ATOOLSJUPDATECHECK_EMAIL_IMPORTANTNOTES');
			foreach($substitutions as $k => $v) {
				$email_subject = str_replace($k, $v, $email_subject);
				$email_body = str_replace($k, $v, $email_body);
			}
			
			$mailer = JFactory::getMailer();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$mailfrom = $jconfig->get('mailfrom');
				$fromname = $jconfig->get('fromname');
			} else {
				$mailfrom = $jconfig->getValue('config.mailfrom');
				$fromname = $jconfig->getValue('config.fromname');
			}
			$mailer->setSender(array( $mailfrom, $fromname ));
			$mailer->addRecipient($sa->email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);
			$mailer->Send();
		}
	}
	
	private function _getSuperAdministrators()
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select(array(
				$db->qn('u').'.'.$db->qn('id'),
				$db->qn('u').'.'.$db->qn('email')
			))->from($db->qn('#__user_usergroup_map').' AS '.$db->qn('g'))
			->join(
				'INNER',
				$db->qn('#__users').' AS '.$db->qn('u').' ON ('.
				$db->qn('g').'.'.$db->qn('user_id').' = '.$db->qn('u').'.'.$db->qn('id').')'
			)->where($db->qn('g').'.'.$db->qn('group_id').' = '.$db->q('8'))
			->where($db->qn('u').'.'.$db->qn('sendEmail').' = '.$db->q('1'))
		;
		$db->setQuery($sql);
		
		return $db->loadObjectList();
	}
	
	private function _loadLanguage()
	{
		$jlang = JFactory::getLanguage();
		$jlang->load('plg_system_atoolsjupdatecheck', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_system_atoolsjupdatecheck', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_system_atoolsjupdatecheck', JPATH_ADMINISTRATOR, null, true);
		// Do we have an override?
		$langOverride = $this->params->get('language_override','');
		if(!empty($langOverride)) {
			$jlang->load('plg_system_atoolsjupdatecheck', JPATH_ADMINISTRATOR, $langOverride, true);
		}
	}
}