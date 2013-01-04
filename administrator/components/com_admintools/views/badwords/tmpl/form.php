<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 */

defined('_JEXEC') or die();

?>
<div class="akeeba-bootstrap">
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option') ?>" />
	<input type="hidden" name="view" value="<?php echo JRequest::getCmd('view') ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />

	<div class="control-group">
		<label for="ip" class="control-label"><?php echo JText::_('ATOOLS_LBL_BADWORDS_WORD'); ?></label>
		<div class="controls">
			<input type="text" name="word" id="word" value="<?php echo $this->item->word ?>">
		</div>
	</div>
	
</form>
	</div>