<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 */

/**
 * Generates email reports for scan results 
 */
class AEFinalizationEmail extends AEAbstractObject
{
	public function __construct() {
		// This empty function is required for direct instantiation of the
		// object, as this is forbidden in the base class' constructor
	}
	
	public function send_scan_email($parent) {
		$parent->relayStep('Sending email');
		$parent->relaySubstep('');
		
		// If no email is set, quit
		$registry = AEFactory::getConfiguration();
		$email = $registry->get('admintools.scanner.email', '');
		$email = trim($email);
		
		if(empty($email))
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "No email is set. Scan results will not sent by email." );
			return true;
		}
		
		// Get the ID of the scan
		$statistics = AEFactory::getStatistics();
		$latestBackupId = $statistics->getId();
		
		// Get scan statistics
		$items = FOFModel::getTmpInstance('Scans','AdmintoolsModel')
			->id($latestBackupId)
			->getItemList();
		$item = array_pop($items);
		
		// Populate table tada for new, modified and suspicious files
		$body_new = '';
		$body_modified = '';
		$body_suspicious = '';
		$totalFiles = FOFModel::getTmpInstance('Scanalerts','AdmintoolsModel')
			->scan_id($latestBackupId)
			->acknowledged(0)
			->getTotal();
		$segments = (int)($totalFiles / 100) + 1;
		for($i = 0; $i < $segments; $i++) {
			$limitstart = 100 * $i;
			
			$files = FOFModel::getTmpInstance('Scanalerts','AdmintoolsModel')
				->scan_id($latestBackupId)
				->acknowledged(0)
				->limit(100)
				->limitstart($limitstart)
				->getItemList();

			if(!empty($files)) foreach($files as $file) {
				$fileRow = "<tr><td>{$file->path}</td><td>{$file->threat_score}</td></tr>\n";
				if(empty($file->diff)) {
					// New file
					$body_new .= $fileRow;
				} elseif(substr($file->diff, 0, 19) == '###MODIFIED FILE###') {
					// Modified file
					$body_modified .= $fileRow;
				} else {
					// Suspicious file
					$body_suspicious .= $fileRow;
				}
			}
		}
		
		// Prepare the email body
		$body = '<html><head>'.JText::_('COM_ADMINTOOLS_SCANS_EMAIL_HEADING').'<title></title></head><body>';
		$body .= '<h1>'.JText::_('COM_ADMINTOOLS_SCANS_EMAIL_HEADING')."</h1><hr/>\n";
		$body .= '<h2>'.JText::_('COM_ADMINTOOLS_SCANS_EMAIL_OVERVIEW')."</h2>\n";
		$body .= "<p>\n";
		$body .= '<strong>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_TOTAL')."</strong>: ".$item->multipart."<br/>\n";
		$body .= '<strong>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_MODIFIED')."</strong>: ".$item->files_modified."<br/>\n";
		$body .= '<strong>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_ADDED')."</strong>: ".$item->files_new."<br/>\n";
		$body .= '<strong>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_SUSPICIOUS')."</strong>: ".$item->files_suspicious."<br/>\n";
		$body .= "</p>\n";

		$body .= '<hr/><h2>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_ADDED')."</h2>\n";
		$body .= "<table width=\"100%\">\n";
		$body .= "\t<thead>\n";
		$body .= "\t<tr>\n";
		$body .= "\t\t<th>".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH')."</th>\n";
		$body .= "\t\t<th width=\"50\">".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')."</th>\n";
		$body .= "\t</tr>\n";
		$body .= "\t</thead>\n";
		$body .= "\t<tbody>\n";
		$body .= $body_new;
		unset($body_new);
		$body .= "\t</tbody>\n";
		$body .= '</table>';

		$body .= '<hr/><h2>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_MODIFIED')."</h2>\n";
		$body .= "<table width=\"100%\">\n";
		$body .= "\t<thead>\n";
		$body .= "\t<tr>\n";
		$body .= "\t\t<th>".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH')."</th>\n";
		$body .= "\t\t<th width=\"50\">".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')."</th>\n";
		$body .= "\t</tr>\n";
		$body .= "\t</thead>\n";
		$body .= "\t<tbody>\n";
		$body .= $body_modified;
		unset($body_modified);
		$body .= "\t</tbody>\n";
		$body .= '</table>';

		$body .= '<hr/><h2>'.JText::_('COM_ADMINTOOLS_LBL_SCANS_SUSPICIOUS')."</h2>\n";
		$body .= "<table width=\"100%\">\n";
		$body .= "\t<thead>\n";
		$body .= "\t<tr>\n";
		$body .= "\t\t<th>".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH')."</th>\n";
		$body .= "\t\t<th width=\"50\">".JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')."</th>\n";
		$body .= "\t</tr>\n";
		$body .= "\t</thead>\n";
		$body .= "\t<tbody>\n";
		$body .= $body_suspicious;
		unset($body_suspicious);
		$body .= "\t</tbody>\n";
		$body .= '</table>';
		$body .= '</body></html>';

		// Prepare the email subject
		$sitename = AEUtilJconfig::getValue('sitname','Unknown Site');
		$subject = JText::sprintf('COM_ADMINTOOLS_SCANS_EMAIL_SUBJECT', $sitename);
		
		// Send the email
		$this->_send_email($email, $subject, $body);
		
		return true;
	}
	
	private function _send_email($email, $subject, $body)
	{
		$mailer = AEPlatform::getInstance()->getMailer();
		
		if(!is_object($mailer)) {
			AEUtilLogger::WriteLog(_AE_LOG_WARNING,"Could not send email to $to - Reason: Mailer object is not an object; please check your system settings");
			return false;
		}
		
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,"-- Creating email message");
		
		$recipient = array($email);
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->IsHTML(true);
		
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,"-- Sending message");

		$result = $mailer->Send();

		if($result instanceof JException)
		{
			AEUtilLogger::WriteLog(_AE_LOG_WARNING,"Could not email $to:");
			AEUtilLogger::WriteLog(_AE_LOG_WARNING,$result->getMessage());
			$ret = $result->getMessage();
			unset($result);
			unset($mailer);
			return $ret;
		}
		else
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG,"-- Email sent");
			return true;
		}
	}
}