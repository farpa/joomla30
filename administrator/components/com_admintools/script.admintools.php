<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Com_AdmintoolsInstallerScript
{
	/** @var string The component's name */
	protected $_akeeba_extension = 'com_admintools';
	
	/** @var array The list of extra modules and plugins to install */
	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
			'admin' => array(
				'atjupgrade' => array('cpanel', 1)
			),
			'site' => array(
			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
			'system' => array(
				'admintools'			=> 1,
				'oneclickaction'		=> 0,
				'atoolsupdatecheck'		=> 0,
				'atoolsjupdatecheck'	=> 0
			),
			'quickicon' => array(
				'atoolsjupdatecheck'	=> 0
			),
			
		)
	);
	
	/** @var array Obsolete files and folders to remove from the Core release only */
	private $akeebaRemoveFilesCore = array(
		'files'	=> array(
		),
		'folders' => array(
		)
	);

	/** @var array Obsolete files and folders to remove from the Core and Pro releases */
	private $akeebaRemoveFilesPro = array(
		'files'	=> array(
			'administrator/components/com_admintools/controllers/default.php',
			'administrator/components/com_admintools/controllers/ipautoban.php',
			'administrator/components/com_admintools/models/base.php',
			'administrator/components/com_admintools/models/ipautoban.php',
			'administrator/components/com_admintools/models/ipbl.php',
			'administrator/components/com_admintools/models/ipwl.php',
			'administrator/components/com_admintools/models/log.php',
			'administrator/components/com_admintools/tables/badwords.php',
			'administrator/components/com_admintools/tables/base.php',
			'administrator/components/com_admintools/tables/customperms.php',
			'administrator/components/com_admintools/tables/redirs.php',
			'administrator/components/com_admintools/tables/wafexceptions.php',
			'administrator/components/com_admintools/views/badwords/view.html.php',
			'administrator/components/com_admintools/views/base.view.html.php',

			'administrator/components/com_jadmintools/fof/LICENSE.txt',
			'administrator/components/com_jadmintools/fof/controller.php',
			'administrator/components/com_jadmintools/fof/dispatcher.php',
			'administrator/components/com_jadmintools/fof/index.html',
			'administrator/components/com_jadmintools/fof/inflector.php',
			'administrator/components/com_jadmintools/fof/input.php',
			'administrator/components/com_jadmintools/fof/model.php',
			'administrator/components/com_jadmintools/fof/query.abstract.php',
			'administrator/components/com_jadmintools/fof/query.element.php',
			'administrator/components/com_jadmintools/fof/query.mysql.php',
			'administrator/components/com_jadmintools/fof/query.mysqli.php',
			'administrator/components/com_jadmintools/fof/query.sqlazure.php',
			'administrator/components/com_jadmintools/fof/query.sqlsrv.php',
			'administrator/components/com_jadmintools/fof/table.php',
			'administrator/components/com_jadmintools/fof/template.utils.php',
			'administrator/components/com_jadmintools/fof/toolbar.php',
			'administrator/components/com_jadmintools/fof/view.csv.php',
			'administrator/components/com_jadmintools/fof/view.html.php',
			'administrator/components/com_jadmintools/fof/view.json.php',
			'administrator/components/com_jadmintools/fof/view.php',
		),
		'folders' => array(
			'administrator/components/com_admintools/views/ipautoban',
			'administrator/components/com_admintools/views/ipbl',
			'administrator/components/com_admintools/views/ipwl',
			'administrator/components/com_admintools/views/log',
		)
	);
	
	private $akeebaCliScripts = array(
		'admintools-filescanner.php'
	);

	
	/**
	 * Joomla! pre-flight event
	 * 
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	public function preflight($type, $parent)
	{
		// Bugfix for "Can not build admin menus"
		if(in_array($type, array('install','discover_install'))) {
			$this->_bugfixDBFunctionReturnedNoError();
		} else {
			$this->_bugfixCantBuildAdminMenus();
		}
		
		// Only allow to install on Joomla! 2.5.0 or later
		return version_compare(JVERSION, '2.5.0', 'ge');
	}
	
	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent 
	 */
	function postflight( $type, $parent )
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);
		
		// Remove obsolete files and folders
		$isAkeebaPro = is_dir($parent->getParent()->getPath('source').'/plugins/system/atoolsupdatecheck');
		if($isAkeebaPro) {
			$akeebaRemoveFiles = $this->akeebaRemoveFilesPro;
		} else {
			$akeebaRemoveFiles = array(
				'files'		=> array_merge($this->akeebaRemoveFilesPro['files'], $this->akeebaRemoveFilesCore['files']),
				'folders'	=> array_merge($this->akeebaRemoveFilesPro['folders'], $this->akeebaRemoveFilesCore['folders']),
			);
		}
		$this->_removeObsoleteFilesAndFolders($akeebaRemoveFiles);
		$this->_copyCliFiles($parent);
		
		// Install FOF
		$fofStatus = $this->_installFOF($parent);
		
		// Install Akeeba Straper
		$straperStatus = $this->_installStraper($parent);
		
		// Show the post-installation page
		$this->_renderPostInstallation($status, $fofStatus, $straperStatus, $parent);
	}
	
	/**
	 * Runs on uninstallation
	 * 
	 * @param JInstaller $parent 
	 */
	function uninstall($parent)
	{
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);
		
		// Show the post-uninstallation page
		$this->_renderPostUninstallation($status, $parent);
	}
	
	/**
	 * Copies the CLI scripts into Joomla!'s cli directory
	 * 
	 * @param JInstaller $parent 
	 */
	private function _copyCliFiles($parent)
	{
		$src = $parent->getParent()->getPath('source');
		
		jimport("joomla.filesystem.file");
		jimport("joomla.filesystem.folder");
		
		if(empty($this->akeebaCliScripts)) {
			return;
		}
		
		foreach($this->akeebaCliScripts as $script) {
			if(JFile::exists(JPATH_ROOT.'/cli/'.$script)) {
				JFile::delete(JPATH_ROOT.'/cli/'.$script);
			}
			if(JFile::exists($src.'/cli/'.$script)) {
				JFile::move($src.'/cli/'.$script, JPATH_ROOT.'/cli/'.$script);
			}
		}
	}
	
	/**
	 * Renders the post-installation message 
	 */
	private function _renderPostInstallation($status, $fofStatus, $straperStatus, $parent)
	{
?>

<?php $rows = 1;?>
<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
	You can download translation files <a href="http://cdn.akeebabackup.com/language/admintools/index.html">directly from our CDN page</a>.
</div>
<img src="<?php echo rtrim(JURI::base(),'/') ?>/../media/com_admintools/images/admintools-48.png" width="48" height="48" alt="Admin Tools" align="right" />

<h2>Admin Tools Installation Status</h2>

<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2">Extension</th>
			<th width="30%">Status</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2">Admin Tools component</td>
			<td><strong style="color: green">Installed</strong></td>
		</tr>
		<tr class="row1">
			<td class="key" colspan="2">
				<strong>Framework on Framework (FOF) <?php echo $fofStatus['version']?></strong> [<?php echo $fofStatus['date'] ?>]
			</td>
			<td><strong>
				<span style="color: <?php echo $fofStatus['required'] ? ($fofStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
					<?php echo $fofStatus['required'] ? ($fofStatus['installed'] ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
				</span>	
			</strong></td>
		</tr>
		<tr class="row0">
			<td class="key" colspan="2">
				<strong>Akeeba Strapper <?php echo $straperStatus['version']?></strong> [<?php echo $straperStatus['date'] ?>]
			</td>
			<td><strong>
				<span style="color: <?php echo $straperStatus['required'] ? ($straperStatus['installed']?'green':'red') : '#660' ?>; font-weight: bold;">
					<?php echo $straperStatus['required'] ? ($straperStatus['installed'] ?'Installed':'Not Installed') : 'Already up-to-date'; ?>
				</span>	
			</strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th>Module</th>
			<th>Client</th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo ($rows++ % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?'Installed':'Not installed'; ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th>Plugin</th>
			<th>Group</th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo ($rows++ % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?'Installed':'Not installed'; ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<?php
	}
	
	private function _renderPostUninstallation($status, $parent)
	{
?>
<?php $rows = 0;?>
<h2>Admin Tools Uninstallation Status</h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
			<th width="30%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2">Admin Tools Component</td>
			<td><strong style="color: green"><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo JText::_('Module'); ?></th>
			<th><?php echo JText::_('Client'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo JText::_('Plugin'); ?></th>
			<th><?php echo JText::_('Group'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<?php
	}
	
	
	
	/**
	 * Joomla! 1.6+ bugfix for "DB function returned no error"
	 */
	private function _bugfixDBFunctionReturnedNoError()
	{
		$db = JFactory::getDbo();
			
		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids = $db->loadColumn();
		} else {
			$ids = $db->loadResultArray();
		}
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__assets')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids = $db->loadColumn();
		} else {
			$ids = $db->loadResultArray();
		}
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
				->where($db->qn('extension_id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_akeeba_extension));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids = $db->loadColumn();
		} else {
			$ids = $db->loadResultArray();
		}
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}
	
	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 */
	private function _bugfixCantBuildAdminMenus()
	{
		$db = JFactory::getDbo();
		
		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->qn('element').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids = $db->loadColumn();
		} else {
			$ids = $db->loadResultArray();
		}
		if(count($ids) > 1) {
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->qn('extension_id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}
		
		// @todo
		
		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->qn('name').' = '.$db->q($this->_akeeba_extension));
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(count($ids) > 1) {
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->qn('id').' = '.$db->q($id));
				$db->setQuery($query);
				$db->query();
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_akeeba_extension));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids1 = $db->loadColumn();
		} else {
			$ids1 = $db->loadResultArray();
		}
		if(empty($ids1)) $ids1 = array();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->qn('type').' = '.$db->q('component'))
			->where($db->qn('menutype').' = '.$db->q('main'))
			->where($db->qn('link').' LIKE '.$db->q('index.php?option='.$this->_akeeba_extension.'&%'));
		$db->setQuery($query);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$ids2 = $db->loadColumn();
		} else {
			$ids2 = $db->loadResultArray();
		}
		if(empty($ids2)) $ids2 = array();
		$ids = array_merge($ids1, $ids2);
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 * 
	 * @param JInstaller $parent 
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');
		
		$db = JFactory::getDbo();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();
		
		// Modules installation
		if(count($this->installation_queue['modules'])) {
			foreach($this->installation_queue['modules'] as $folder => $modules) {
				if(count($modules)) foreach($modules as $module => $modulePreferences) {
					// Install the module
					if(empty($folder)) $folder = 'site';
					$path = "$src/modules/$folder/$module";
					if(!is_dir($path)) {
						$path = "$src/modules/$folder/mod_$module";
					}
					if(!is_dir($path)) {
						$path = "$src/modules/$module";
					}
					if(!is_dir($path)) {
						$path = "$src/modules/mod_$module";
					}
					if(!is_dir($path)) continue;
					// Was the module already installed?
					$sql = $db->getQuery(true)
						->select('COUNT(*)')
						->from('#__modules')
						->where($db->qn('module').' = '.$db->q('mod_'.$module));
					$db->setQuery($sql);
					$count = $db->loadResult();
					$installer = new JInstaller;
					$result = $installer->install($path);
					$status->modules[] = array(
						'name'=>'mod_'.$module,
						'client'=>$folder,
						'result'=>$result
					);
					// Modify where it's published and its published state
					if(!$count) {
						// A. Position and state
						list($modulePosition, $modulePublished) = $modulePreferences;
						if($modulePosition == 'cpanel') {
							$modulePosition = 'icon';
						}
						$sql = $db->getQuery(true)
							->update($db->qn('#__modules'))
							->set($db->qn('position').' = '.$db->q($modulePosition))
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						if($modulePublished) {
							$sql->set($db->qn('published').' = '.$db->q('1'));
						}
						$db->setQuery($sql);
						$db->query();
						
						// B. Change the ordering of back-end modules to 1 + max ordering
						if($folder == 'admin') {
							$query = $db->getQuery(true);
							$query->select('MAX('.$db->qn('ordering').')')
								->from($db->qn('#__modules'))
								->where($db->qn('position').'='.$db->q($modulePosition));
							$db->setQuery($query);
							$position = $db->loadResult();
							$position++;

							$query = $db->getQuery(true);
							$query->update($db->qn('#__modules'))
								->set($db->qn('ordering').' = '.$db->q($position))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							$db->setQuery($query);
							$db->query();
						}
						
						// C. Link to all pages
						$query = $db->getQuery(true);
						$query->select('id')->from($db->qn('#__modules'))
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($query);
						$moduleid = $db->loadResult();

						$query = $db->getQuery(true);
						$query->select('*')->from($db->qn('#__modules_menu'))
							->where($db->qn('moduleid').' = '.$db->q($moduleid));
						$db->setQuery($query);
						$assignments = $db->loadObjectList();
						$isAssigned = !empty($assignments);
						if(!$isAssigned) {
							$o = (object)array(
								'moduleid'	=> $moduleid,
								'menuid'	=> 0
							);
							$db->insertObject('#__modules_menu', $o);
						}
					}
				}
			}
		}

		// Plugins installation
		if(count($this->installation_queue['plugins'])) {
			foreach($this->installation_queue['plugins'] as $folder => $plugins) {
				if(count($plugins)) foreach($plugins as $plugin => $published) {
					$path = "$src/plugins/$folder/$plugin";
					if(!is_dir($path)) {
						$path = "$src/plugins/$folder/plg_$plugin";
					}
					if(!is_dir($path)) {
						$path = "$src/plugins/$plugin";
					}
					if(!is_dir($path)) {
						$path = "$src/plugins/plg_$plugin";
					}
					if(!is_dir($path)) continue;

					// Was the plugin already installed?
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);
					
					$status->plugins[] = array('name'=>'plg_'.$plugin,'group'=>$folder, 'result'=>$result);

					if($published && !$count) {
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled').' = '.$db->q('1'))
							->where($db->qn('element').' = '.$db->q($plugin))
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}
		
		return $status;
	}
	
	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 * 
	 * @param JInstaller $parent 
	 * @return JObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');
		
		$db = & JFactory::getDBO();
		
		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();
		
		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if(count($this->installation_queue['modules'])) {
			foreach($this->installation_queue['modules'] as $folder => $modules) {
				if(count($modules)) foreach($modules as $module => $modulePreferences) {
					// Find the module ID
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q('mod_'.$module))
						->where($db->qn('type').' = '.$db->q('module'));
					$db->setQuery($sql);
					$id = $db->loadResult();
					// Uninstall the module
					if($id) {
						$installer = new JInstaller;
						$result = $installer->uninstall('module',$id,1);
						$status->modules[] = array(
							'name'=>'mod_'.$module,
							'client'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		// Plugins uninstallation
		if(count($this->installation_queue['plugins'])) {
			foreach($this->installation_queue['plugins'] as $folder => $plugins) {
				if(count($plugins)) foreach($plugins as $plugin => $published) {
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('type').' = '.$db->q('plugin'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($sql);

					$id = $db->loadResult();
					if($id)
					{
						$installer = new JInstaller;
						$result = $installer->uninstall('plugin',$id,1);
						$status->plugins[] = array(
							'name'=>'plg_'.$plugin,
							'group'=>$folder,
							'result'=>$result
						);
					}			
				}
			}
		}
		
		return $status;
	}
	
	/**
	 * Removes obsolete files and folders
	 * 
	 * @param array $akeebaRemoveFiles 
	 */
	private function _removeObsoleteFilesAndFolders($akeebaRemoveFiles)
	{
		// Remove files
		jimport('joomla.filesystem.file');
		if(!empty($akeebaRemoveFiles['files'])) foreach($akeebaRemoveFiles['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		jimport('joomla.filesystem.file');
		if(!empty($akeebaRemoveFiles['folders'])) foreach($akeebaRemoveFiles['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!JFolder::exists($f)) continue;
			JFolder::delete($f);
		}
	}
	
	private function _installFOF($parent)
	{
		$src = $parent->getParent()->getPath('source');
		
		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');
		$source = $src.'/fof';
		if(!defined('JPATH_LIBRARIES')) {
			$target = JPATH_ROOT.'/libraries/fof';
		} else {
			$target = JPATH_LIBRARIES.'/fof';
		}
		$haveToInstallFOF = false;
		if(!JFolder::exists($target)) {
			$haveToInstallFOF = true;
		} else {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
		}

		$installedFOF = false;
		if($haveToInstallFOF) {
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedFOF = $installer->install($source);
		} else {
			$versionSource = 'installed';
		}
		
		if(!isset($fofVersion)) {
			$fofVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$fofVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$fofVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$fofVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}
		
		if(!($fofVersion[$versionSource]['date'] instanceof JDate)) {
			$fofVersion[$versionSource]['date'] = new JDate();
		}
		
		return array(
			'required'	=> $haveToInstallFOF,
			'installed'	=> $installedFOF,
			'version'	=> $fofVersion[$versionSource]['version'],
			'date'		=> $fofVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}
	
	private function _installStraper($parent)
	{
		$src = $parent->getParent()->getPath('source');
		
		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');
		$source = $src.'/strapper';
		$target = JPATH_ROOT.'/media/akeeba_strapper';

		$haveToInstallStraper = false;
		if(!JFolder::exists($target)) {
			$haveToInstallStraper = true;
		} else {
			$straperVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;
		if($haveToInstallStraper) {
			$versionSource = 'package';
			$installer = new JInstaller;
			$installedStraper = $installer->install($source);
		} else {
			$versionSource = 'installed';
		}
		
		if(!isset($straperVersion)) {
			$straperVersion = array();
			if(JFile::exists($target.'/version.txt')) {
				$rawData = JFile::read($target.'/version.txt');
				$info = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version'	=> trim($info[0]),
					'date'		=> new JDate(trim($info[1]))
				);
			} else {
				$straperVersion['installed'] = array(
					'version'	=> '0.0',
					'date'		=> new JDate('2011-01-01')
				);
			}
			$rawData = JFile::read($source.'/version.txt');
			$info = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version'	=> trim($info[0]),
				'date'		=> new JDate(trim($info[1]))
			);
			$versionSource = 'installed';
		}
		
		if(!($straperVersion[$versionSource]['date'] instanceof JDate)) {
			$straperVersion[$versionSource]['date'] = new JDate();
		}
		
		return array(
			'required'	=> $haveToInstallStraper,
			'installed'	=> $installedStraper,
			'version'	=> $straperVersion[$versionSource]['version'],
			'date'		=> $straperVersion[$versionSource]['date']->format('Y-m-d'),
		);
	}
}