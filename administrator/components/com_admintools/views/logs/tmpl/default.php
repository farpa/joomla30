<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 */

defined('_JEXEC') or die();

$model = $this->getModel();
JHTML::_('behavior.calendar');
jimport('joomla.utilities.date');

if(interface_exists('JModel')) {
	$cparams = JModelLegacy::getInstance('Storage','AdmintoolsModel');
} else {
	$cparams = JModel::getInstance('Storage','AdmintoolsModel');
}
$iplink = $cparams->getValue('iplookupscheme','http').'://'.$cparams->getValue('iplookup','ip-lookup.net/index.php?ip={ip}');

$this->loadHelper('select');
?>
<div class="akeeba-bootstrap">
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" id="option" value="com_admintools" />
	<input type="hidden" name="view" id="view" value="logs" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getToken();?>" value="1" />
<table class="table table-striped">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_LOGDATE', 'logdate', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_IP', 'ip', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_REASON', 'reason', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_URL', 'url', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
		</tr>
		<tr>
			<td></td>
			<td width="280" class="form-inline">
				<?php echo JHTML::_('calendar', $this->getModel()->getState('datefrom',''), 'datefrom', 'datefrom', '%Y-%m-%d', array('onchange'=>'document.adminForm.submit();', 'class' => 'input-small')); ?>
				&ndash;
				<?php echo JHTML::_('calendar', $this->getModel()->getState('dateto',''), 'dateto', 'dateto', '%Y-%m-%d', array('onchange'=>'document.adminForm.submit();', 'class' => 'input-small')); ?>
			</td>
			<td class="form-inline">
				<input type="text" name="ip" id="ip"
					value="<?php echo $this->escape($this->getModel()->getState('ip',''));?>" size="30"
					class="input-small" onchange="document.adminForm.submit();"
					placeholder="<?php echo JText::_('ATOOLS_LBL_LOG_IP') ?>"
					/>
				<button class="btn btn-mini" onclick="this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<button class="btn btn-mini" onclick="document.adminForm.ip.value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
			<td>
				<?php echo AdmintoolsHelperSelect::reasons($this->getModel()->getState('reason',''), 'reason', array('onchange'=>'document.adminForm.submit();', 'class' => 'input-medium')) ?>
			</td>
			<td class="form-inline">
				<input type="text" name="url" id="url"
					value="<?php echo $this->escape($this->getModel()->getState('url',''));?>" size="30"
					class="input-medium" onchange="document.adminForm.submit();"
					placeholder="<?php echo JText::_('ATOOLS_LBL_LOG_URL') ?>" />
				<button class="btn btn-mini" onclick="this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<button class="btn btn-mini" onclick="document.adminForm.url.value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="5">
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
				<?php $date = new JDate($item->logdate); echo $date->format('Y-m-d H:i:s', true); ?>
			</td>
			<td>
				<a href="<?php echo str_replace('{ip}', $item->ip, $iplink) ?>" target="_blank">
					<img align="middle" border="0" width="16" height="16" src="<?php echo rtrim(JURI::base(),'/') ?>/../media/com_admintools/images/iplookup_16.png" />
				</a>&nbsp;
				<?php echo $this->escape($item->ip) ?><br/>&emsp;<i>
				<?php if($item->block): ?>
				<a href="index.php?option=com_admintools&view=log&task=unban&id=<?php echo (int)($item->id) ?>&<?php echo JFactory::getSession()->getToken();?>=1"><?php echo JText::_('ATOOLS_LBL_LOG_UNBAN') ?></a>
				<?php else: ?>
				<a href="index.php?option=com_admintools&view=log&task=ban&id=<?php echo (int)($item->id) ?>&<?php echo JFactory::getSession()->getToken();?>=1"><?php echo JText::_('ATOOLS_LBL_LOG_BAN') ?></a>
				<?php endif; ?>
				</i>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_REASON_'.strtoupper($item->reason)) ?>
				<?php if($item->extradata): ?>
				<?php
					if(stristr($item->extradata, '|') === false) $item->extradata .= '|';
					list($moreinfo, $techurl) = explode('|', $item->extradata);
					echo JHTML::_('tooltip', strip_tags($this->escape($moreinfo)), '', 'tooltip.png', '', $techurl );
				?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $this->escape($item->url) ?>
			</td>
		</tr>
	<?php
			$i++;
			endforeach;
	?>
	<?php else : ?>
		<tr>
			<td colspan="5" align="center"><?php echo JText::_('ATOOLS_ERR_LOG_NOITEMS') ?></td>
		</tr>
	<?php endif ?>
	</tbody>
</table>

</form>
</div>