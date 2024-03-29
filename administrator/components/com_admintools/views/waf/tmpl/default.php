<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}

$lang = JFactory::getLanguage();
$option = JRequest::getCmd('option','com_admintools');

jimport('joomla.filesystem.file');
$pEnabled = JPluginHelper::getPlugin('system','admintools');
$pExists = JFile::exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'admintools'.DIRECTORY_SEPARATOR.'admintools.php');
$pExists |= JFile::exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'admintools.php');

?>
<div class="akeeba-bootstrap">
<?php if(!$pExists): ?>
<p class="alert alert-error">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINEXISTS'); ?>
</p>
<?php elseif(!$pEnabled): ?>
<p class="alert alert-error">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINACTIVE'); ?>
	<br/>
	<a href="index.php?option=com_plugins&client=site&filter_type=system&search=admin%20tools">
		<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINACTIVE_DOIT'); ?>
	</a>
</p>
<?php endif; ?>

<div class="alert alert-info">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<?php echo JText::_('ATOOLS_LBL_WAF_HTACCESSTIP'); ?>
</div>


<div id="cpanel">

	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=wafconfig">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/wafconfig-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_WAFCONFIG') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_WAFCONFIG') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
 		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=twofactor">
				<img
				src="<?php echo JURI::base(); ?>../media/com_admintools/images/twofactor-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_TWOFACTOR') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_TWOFACTOR') ?><br/>
				</span>
			</a>
		</div>
	</div>	
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
 		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=wafexceptions">
				<img
				src="<?php echo JURI::base(); ?>../media/com_admintools/images/wafexceptions-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS') ?><br/>
				</span>
			</a>
		</div>
	</div>	
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=ipwls">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/ipwl-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_IPWL') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_IPWL') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=ipbls">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/ipbl-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_IPBL') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_IPBL') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=badwords">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/badwords-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_BADWORDS') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_BADWORDS') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=geoblock">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/geoblock-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_GEOBLOCK') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_GEOBLOCK') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=logs">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/log-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_LOG') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_LOG') ?><br/>
				</span>
			</a>
		</div>
	</div>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=ipautobans">
				<img
				src="<?php echo rtrim(JURI::base(),'/'); ?>/../media/com_admintools/images/ipautoban-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_IPAUTOBAN') ?>" />
				<span>
					<?php echo JText::_('ADMINTOOLS_TITLE_IPAUTOBAN') ?><br/>
				</span>
			</a>
		</div>
	</div>

</div>

</div>