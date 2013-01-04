<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsControllerScanalert extends FOFController
{
	protected function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			if(
				(FOFInput::getCmd('format','html',$this->input) == 'csv') ||
				(FOFInput::getCmd('layout','default',$this->input) == 'print')
			) {
				$this->getThisModel()
					->savestate(0);
				$this->getThisModel()
					->setState('limit',0);
				$this->getThisModel()
					->setState('limitstart',0);
				$this->getThisView()->assign('scan',
					FOFModel::getTmpInstance('Scans','AdmintoolsModel')
						->id(FOFInput::getInt('scan_id',0,$this->input))
						->getItem()
				);
			}
		}
		return $result;
	}
	
	public function onBeforeEdit()
	{
		$result = parent::onBeforeEdit();
		if($result) {
			// Get the componetn parameters
			$db = JFactory::getDbo();
			$sql = $db->getQuery(true)
				->select($db->qn('params'))
				->from($db->qn('#__extensions'))
				->where($db->qn('type').' = '.$db->q('component'))
				->where($db->qn('element').' = '.$db->q('com_admintools'));
			$db->setQuery($sql);
			$rawparams = $db->loadResult();
			$params = new JRegistry();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$params->loadString($rawparams, 'JSON');
			} else {
				$params->loadJSON($rawparams);
			}
			
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$this->getThisView()->assign('generateDiff', $params->get('scandiffs', false));
			} else {
				$this->getThisView()->assign('generateDiff', $params->getValue('scandiffs', false));
			}
			
			// Look for Geshi
			jimport('joomla.filesystem.file');
			$geshiPath = JPATH_PLUGINS.'/content/geshi/geshi/geshi.php';
			if(JFile::exists($geshiPath)) require_once $geshiPath;
		}
		return $result;
	}
	
	public function onAfterCancel()
	{
		$item = $this->getThisModel()->getItem();
		$this->redirect .= '&scan_id='.(int)$item->scan_id;
		return true;
	}
	
	public function onAfterSave()
	{
		$item = $this->getThisModel()->getItem();
		$this->redirect .= '&scan_id='.(int)$item->scan_id;
		return true;
	}
	
	public function add() {
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}
	
	public function delete() {
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}
	
	public function onBeforePublish() {
		$customURL = FOFInput::getString('returnurl','',$this->input);
		if(!$customURL) {
			$scan_id = FOFInput::getCmd('scan_id', 0, $this->input);
			$url = 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view).'&scan_id='.$scan_id;
			FOFInput::setVar('returnurl', base64_encode($url), $this->input);
		}
		
		return parent::onBeforePublish();
	}
	
	public function onBeforeUnpublish() {
		$customURL = FOFInput::getString('returnurl','',$this->input);
		if(!$customURL) {
			$scan_id = FOFInput::getCmd('scan_id', 0, $this->input);
			$url = 'index.php?option='.$this->component.'&view='.FOFInflector::pluralize($this->view).'&scan_id='.$scan_id;
			FOFInput::setVar('returnurl', base64_encode($url), $this->input);
		}
		
		return parent::onBeforeUnpublish();
	}
}