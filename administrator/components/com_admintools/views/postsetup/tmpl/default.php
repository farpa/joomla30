<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 1.3
 */

defined('_JEXEC') or die();

$disabled = ADMINTOOLS_PRO ? '' : 'disabled = "disabled"';

if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}
?>
<div id="atools-container" class="akeeba-bootstrap">
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="postsetup" />
	<input type="hidden" name="task" id="task" value="save" />
	<?php echo JHTML::_( 'form.token' ); ?>
	
	<p class="alert alert-info"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_WHATTHIS'); ?></p>

	<label for="autoupdate" class="postsetup-main">
		<input type="checkbox" id="autoupdate" name="autoupdate" <?php if($this->enableautoupdate): ?>checked="checked"<?php endif; ?> <?php echo $disabled?> />
		<?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_AUTOUPDATE')?>
	</label>
	
	<?php if(ADMINTOOLS_PRO): ?>
	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_DESC_autoupdate');?></div>
	<?php else: ?>
	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_NOTAVAILABLEINCORE');?></div>
	<?php endif; ?>
	<br/>
	
	<label for="autojupdate" class="postsetup-main">
		<input type="checkbox" id="autojupdate" name="autojupdate" <?php if($this->enableautojupdate): ?>checked="checked"<?php endif; ?> <?php echo $disabled?> />
		<?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE')?>
	</label>
	
	<?php if(ADMINTOOLS_PRO): ?>
	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_DESC_autojupdate');?></div>
	<?php else: ?>
	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_NOTAVAILABLEINCORE');?></div>
	<?php endif; ?>

	<div class="form-actions">
		<button class="btn btn-large btn-primary" onclick="this.form.submit(); return false;"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_APPLY');?></button>
	</div>
</form>
</div>