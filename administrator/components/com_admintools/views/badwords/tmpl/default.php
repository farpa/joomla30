<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 */

defined('_JEXEC') or die();

$model = $this->getModel();

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

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" id="option" value="com_admintools" />
	<input type="hidden" name="view" id="view" value="badwords" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->escape($this->lists->order) ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->escape($this->lists->order_Dir) ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />
<table class="table table-striped">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_BADWORDS_WORD', 'word', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
		</tr>
		<tr>
			<td></td>
			<td class="form-inline">
				<div class="form-inline">
					<input type="text" name="word" id="word"
						value="<?php echo $this->escape($this->getModel()->getState('word',''));?>" size="30"
						class="input-small" onchange="document.adminForm.submit();"
						placeholder="<?php echo JText::_('ATOOLS_LBL_BADWORDS_WORD') ?>"
						/>
					<button class="btn btn-mini" onclick="this.form.submit();">
						<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
					</button>
					<button class="btn btn-mini" onclick="document.adminForm.word.value='';this.form.submit();">
						<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
					</button>
				</div>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">
				<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php if($count = count($this->items)): ?>
		<?php
			$i = 0;

			foreach($this->items as $item):
		?>
		<tr>
			<td>
				<?php echo JHTML::_('grid.id', $i, $item->id, false); ?>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=badwords&task=edit&id=<?php echo $item->id ?>">
					<?php echo $this->escape($item->word) ?>
				</a>
			</td>
		</tr>
	<?php
			$i++;
			endforeach;
	?>
	<?php else : ?>
		<tr>
			<td colspan="2" align="center">
				<span class="label label-info">
					<?php echo JText::_('ATOOLS_ERR_BADWORDS_NOITEMS') ?>
				</span>
			</td>
		</tr>
	<?php endif ?>
	</tbody>
</table>

</form>

</div>