<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class AdmintoolsControllerTwofactor extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);
		
		$this->modelName = 'twofactor';
	}
	
	public function execute($task) {
		if(!in_array($task, array('resetkey','resetpanic','validate'))) $task = 'browse';
		parent::execute($task);
	}
	
	public function resetkey()
	{
		$this->_csrfProtection();
		
		$model = $this->getThisModel();
		$model->generateTwoFactorAuthenticationSecret();
		
		$this->setRedirect('index.php?option=com_admintools&view=twofactor', JText::_('COM_ADMINTOOLS_TWOFACTOR_MSG_RESETKEY'));
	}
	
	public function resetpanic()
	{
		$this->_csrfProtection();
		
		$model = $this->getThisModel();
		$model->resetPanic();
		
		$this->setRedirect('index.php?option=com_admintools&view=twofactor', JText::_('COM_ADMINTOOLS_TWOFACTOR_MSG_RESETPANIC'));
	}
	
	public function validate()
	{
		$this->_csrfProtection();
		
		$code = FOFInput::getVar('securitycode', '', $this->input);
		
		$model = $this->getThisModel();
		$result = $model->validateAndEnable($code);
		
		if($result) {
			$this->setRedirect('index.php?option=com_admintools&view=waf', JText::_('COM_ADMINTOOLS_TWOFACTOR_MSG_ENABLED'));
		} else {
			$this->setRedirect('index.php?option=com_admintools&view=twofactor', JText::_('COM_ADMINTOOLS_TWOFACTOR_MSG_DISABLED'), 'error');
		}
	}
	
	protected function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			$model = FOFModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
			$config = $model->getConfig();
			$secret = $config['twofactorauth_secret'];
			if(empty($secret)) {
				$m = $this->getThisModel();
				$m->generateTwoFactorAuthenticationSecret();

				$this->setRedirect('index.php?option=com_admintools&view=twofactor');
			}
		}
		return $result;
	}
}
