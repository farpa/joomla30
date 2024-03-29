<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Emergency Off-Line Mode
 */
class AdmintoolsControllerEom extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);
		
		$this->modelName = 'Eom';
	}
	
	public function execute($task) {
		if(!in_array($task, array('offline','online'))) $task = 'browse';
		parent::execute($task);
	}

	public function offline()
	{
		// CSRF prevention
		$this->_csrfProtection();
		
		$model = $this->getThisModel();

		$status = $model->putOffline();
		$url = 'index.php?option=com_admintools';
		if($status)
		{
			$this->setRedirect($url,JText::_('ATOOLS_LBL_EOM_APPLIED'));
		}
		else
		{
			$this->setRedirect($url,JText::_('ATOOLS_ERR_EOM_NOTAPPLIED'),'error');
		}
	}

	public function online()
	{
		// CSRF prevention
		$this->_csrfProtection();
		
		$model = $this->getThisModel();
		$status = $model->putOnline();
		$url = 'index.php?option=com_admintools';
		if($status)
		{
			$this->setRedirect($url,JText::_('ATOOLS_LBL_EOM_UNAPPLIED'));
		}
		else
		{
			$this->setRedirect($url,JText::_('ATOOLS_ERR_EOM_NOTUNAPPLIED'),'error');
		}
	}
}
