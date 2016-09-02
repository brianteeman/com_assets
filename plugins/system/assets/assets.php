<?php
/**
 * @copyright	Copyright (c) 2016 notwebdesign. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
require_once (JPATH_ADMINISTRATOR.'/components/com_assets/helpers/assets.php');

/**
 * System - Remove Scripts Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	notwebdesign.RemoveScripts
 */
class plgSystemAssets extends JPlugin {

	/**
	 * Constructor.
	 *
	 * @param 	$subject
	 * @param	array $config
	 */
	function __construct(&$subject, $config = array()) {

		// call parent constructor
		parent::__construct($subject, $config);
	}

	public function onBeforeCompileHead()
	{
		// Scan for assets if requested
		$do = JFactory::getApplication()->input->getCmd('do');
		if ($do == 'assetscan') {
			//AssetsHelpersAssets::scanForAssets();
		}

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			// Don't run in the admin
			return;
		}

		// Get the document object
		$doc = JFactory::getDocument();

		// Load the assets from database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('path, type, regex_pages');
		$query->from('#__assets_assets');
		$query->where('state = 1');
		$query->order('ordering');
		$db->setQuery($query);
		//echo str_replace('#__', JFactory::getConfig()->get('dbprefix'), $db->getQuery());
		$dbAssets = $db->loadObjectList();

		// Store and reset the doc scripts and styles
		$docScripts = $doc->_scripts;
		$doc->_scripts = array();
		$docStyles = $doc->_styleSheets;
		$doc->_styleSheets = array();

		// Loop the database assets and only add back in the ones published there (and in the desired order)
		foreach ($dbAssets as $dbAsset)
		{
			// Check if the database asset matches this page
			if (!empty($dbAsset->regex_pages))
			{
				// Get the current page
				$uri = JUri::getInstance();
				$thisPage = $uri->toString();
				$pattern = '|'.$dbAsset->regex_pages.'|i';

				//echo '<br />Looking for '.$pattern. 'in '.$thisPage;
				if (preg_match($pattern, $thisPage) === 0)
				{
					// Skip adding this asset as it does not match the pattern
					continue;
				}
			}

			// Handle scripts
			if ($dbAsset->type == 1 && array_key_exists($dbAsset->path, $docScripts))
			{
				$doc->_scripts[$dbAsset->path] = $docScripts[$dbAsset->path];
			}

			// Handle style sheets
			if ($dbAsset->type == 2 && array_key_exists($dbAsset->path, $docStyles))
			{
				$doc->_styleSheets[$dbAsset->path] = $docStyles[$dbAsset->path];
			}
		}
	}

	public function onBeforeRender() {
		// Scan for assets if requested
		$do = JFactory::getApplication()->input->getCmd('do');
		if ($do == 'assetscan') {
			AssetsHelpersAssets::scanForAssets();
		}

	}

}