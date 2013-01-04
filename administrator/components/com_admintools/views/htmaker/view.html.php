<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
jimport('joomla.application.component.view');

class AdmintoolsViewHtmaker extends FOFViewHtml
{
	public function display($tpl = null) {
		parent::display($tpl);
	}
	
	protected function onBrowse($tpl = null)
	{
		$task = FOFInput::getCmd('task','browse',$this->input);
		
		switch($task) {
			case 'preview':
				$model = $this->getModel();
				$htaccess = $model->makeHtaccess();

				$this->assign('htaccess', $htaccess);

				$this->setLayout('plain');

				break;
			
			default:
				$model = $this->getModel();
				$config = $model->loadConfiguration();

				$this->assign('htconfig', $config);
				break;
		}
	}
}