<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 */

defined('_JEXEC') or die();

if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}

?>
<div class="akeeba-bootstrap">
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form-horizontal">
	<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option') ?>" />
	<input type="hidden" name="view" value="<?php echo JRequest::getCmd('view') ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />

	<div class="control-group">
		<label class="control-label" for="foption" title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION'); ?></label>
		<div class="controls">
			<input class="input-xlarge" type="text" name="foption" id="foption" value="<?php echo $this->item->option ?>">
		</div>
	</div>
	<div style="clear:left"></div>
	<div class="control-group">
		<label class="control-label" for="fview" title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW'); ?></label>
		<div class="controls">
			<input class="input-xlarge" type="text" name="fview" id="fview" value="<?php echo $this->item->view ?>">
		</div>
	</div>
	<div style="clear:left"></div>
	<div class="control-group">
		<label class="control-label" for="fquery" title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY'); ?></label>
		<div class="controls">
			<input class="input-xlarge" type="text" name="fquery" id="fquery" value="<?php echo $this->item->query ?>">
		</div>
	</div>
</form>
</div>