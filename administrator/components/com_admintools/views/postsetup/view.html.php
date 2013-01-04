<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.3.b1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewPostsetup extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$this->_setAutoupdateStatus();
		$this->_setJAutoupdateStatus();
	}
	
	private function _setAutoupdateStatus()
	{
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true)
			->select($db->qn('enabled'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('oneclickaction'))
			->where($db->qn('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledOCA = $db->loadResult();
		
		$query = $db->getQuery(true)
			->select($db->qn('enabled'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('atoolsupdatecheck'))
			->where($db->qn('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledAUC = $db->loadResult();
		
		if(!ADMINTOOLS_PRO) {
			$enabledAUC = false;
			$enabledOCA = false;
		}
		
		$this->assign('enableautoupdate', $enabledAUC && $enabledOCA);
	}
	
	private function _setJAutoupdateStatus()
	{
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true)
			->select($db->qn('enabled'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('oneclickaction'))
			->where($db->qn('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledOCA = $db->loadResult();
		
		$query = $db->getQuery(true)
			->select($db->qn('enabled'))
			->from($db->qn('#__extensions'))
			->where($db->qn('element').' = '.$db->q('atoolsjupdatecheck'))
			->where($db->qn('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledJUC = $db->loadResult();
		
		if(!ADMINTOOLS_PRO) {
			$enabledJUC = false;
			$enabledOCA = false;
		}
		
		$this->assign('enableautojupdate', $enabledJUC && $enabledOCA);
	}
}