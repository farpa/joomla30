<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id$
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Subdirectories exclusion filter. Excludes temporary, cache and backup output
 * directories' contents from being backed up.
 */
class AEFilterPlatformSkipdirs extends AEAbstractFilter
{
	public function __construct()
	{
		$this->object	= 'dir';
		$this->subtype	= 'children';
		$this->method	= 'direct';
		$this->filter_name = 'PlatformSkipdirs';

		// We take advantage of the filter class magic to inject our custom filters
		$configuration = AEFactory::getConfiguration();
		if(defined('AKEEBACLI'))
		{
			$tmpdir = AEUtilJconfig::getValue('tmp_path');
		}
		else
		{
			$jreg = JFactory::getConfig();
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$tmpdir = $jreg->get('tmp_path');
			} else {
				$tmpdir = $jreg->getValue('config.tmp_path');
			}
		}

		$this->filter_data['[SITEROOT]'] = array (
			// Output & temp directory of the component
			self::treatDirectory($configuration->get('akeeba.basic.output_directory')),
			// Joomla! temporary directory
			self::treatDirectory($tmpdir),
			// Joomla! front- and back-end cache, as reported by Joomla!
			self::treatDirectory(JPATH_CACHE),
			self::treatDirectory(JPATH_ADMINISTRATOR.'/cache'),
			self::treatDirectory(JPATH_ROOT.'/cache'),
			// This is not needed except on sites running SVN or beta releases
			self::treatDirectory(JPATH_ROOT.'/installation'),
			// Joomla! front- and back-end cache, as calculated by us (redundancy, for funky server setups)
			self::treatDirectory( AEPlatform::getInstance()->get_site_root().'/cache' ),
			self::treatDirectory( AEPlatform::getInstance()->get_site_root().'/administrator/cache'),
			'administrator/components/com_akeeba/backup',
			// MyBlog's cache
			self::treatDirectory( AEPlatform::getInstance()->get_site_root().'/components/libraries/cmslib/cache' ),
			// The logs directory
			'logs'
		);

		parent::__construct();
	}

	private static function treatDirectory($directory)
	{
		$site_root = AEUtilFilesystem::TrimTrailingSlash(AEUtilFilesystem::TranslateWinPath(JPATH_ROOT));

		$directory = AEUtilFilesystem::TrimTrailingSlash(AEUtilFilesystem::TranslateWinPath($directory));

		// Trim site root from beginning of directory
		if( substr($directory, 0, strlen($site_root)) == $site_root )
		{
			$directory = substr($directory, strlen($site_root));
			if( substr($directory,0,1) == '/' ) $directory = substr($directory,1);
		}

		return $directory;
	}
}