<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Assets
 * @author     Søren Beck Jensen <soren@notwebdesign.com>
 * @copyright  2016 Søren Beck Jensen
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Assets helper.
 *
 * @since  1.6
 */
class AssetsHelpersAssets
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  string
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_ASSETS_TITLE_ASSETS'),
			'index.php?option=com_assets&view=assets',
			$vName == 'assets'
		);

	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 *
	 * @since    1.6
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_assets';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Show a message if the plugin is not published
	 * @return bool
	 * @throws Exception
	 */
	public static function ensurePluginPublished() {

		$published = JPluginHelper::isEnabled('system', 'assets');

		if (!$published)
		{
			$publishLink = JRoute::_('index.php?option=com_assets&task=asset.publish_plugin');
			$message = JText::sprintf('COM_ASSETS_PLUGIN_NOT_CORRECT', $publishLink);
			JFactory::getApplication()->enqueueMessage($message, 'error');
		}

		return false;
	}


	/**
	 * Get news assets and save them to the database
	 *
	 * @return array of new assets found
	 */
	public static function scanForAssets() {

		// Load assets already in the database
		$dbAssets = self::getModel('Assets')->getItems();

		// Convert to simple array to allow comparison
		$dbPaths = array();
		if (!empty($dbAssets))
		{
			foreach ($dbAssets as $dbAsset)
			{
				$dbPaths[] = $dbAsset->path;
			}
		}

		// Check to see if we have this script recorded already
		$scripts = self::getDocScripts();
		foreach ($scripts as $path)
		{
			if (!in_array($path, $dbPaths)) {
				self::addAsset($path, 1);
			}
		}

		// Check to see if we have this style recorded already
		$styles = self::getDocStyles();
		foreach ($styles as $path)
		{
			if (!in_array($path, $dbPaths)) {
				self::addAsset($path, 2);
			}
		}

	}


	/**
	 * Store a new asset into the database
	 * @param $path string
	 * @param $type (1 = Script, 2=Style Sheet)
	 */
	public static function addAsset($path, $type)
	{
		$data = array();
		$data['id'] = 0;
		$data['ordering'] = null;
		$data['state'] = 1;
		$data['path'] = $path;
		$data['type'] = $type;

		// Use model to store
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_assets/tables');
		$assetModel = self::getModel('Asset');
		$assetModel->save($data);
	}


	/**
	 * Get a list of the script files found in $doc->_scripts
	 * @return array
	 */
	public static function getDocScripts()
	{

		$doc = JFactory::getDocument();
		$scripts = $doc->_scripts;
		$files = array();
		if (!empty($scripts))
		{
			foreach ($scripts as $file => $script)
			{
				$files[] = $file;
			}
		}

		return $files;
	}

	/**
	 * Get a list of the css files found in $doc->_styles
	 * @return array
	 */
	public static function getDocStyles()
	{

		$doc = JFactory::getDocument();
		$styles = $doc->_styleSheets;
		$files = array();
		if (!empty($styles))
		{
			foreach ($styles as $file => $style)
			{
				$files[] = $file;
			}
		}

		return $files;
	}

	/**
	 * Get a list of the asset types
	 */
	public static function getAssetTypes()
	{
		$model = self::getModel('Types');
		return $model->getItems();
	}


	/**
	 * Get an instance of the named model
	 *
	 * @param   string $name The filename of the model
	 *
	 * @return JModel|null An instantiated object of the given model or null if the class does not exist.
	 */
	public static function getModel($name)
	{
		$classFilePath = JPATH_ADMINISTRATOR . '/components/com_assets/models/' . strtolower($name) . '.php';

		$model_class = 'AssetsModel' . ucwords($name);

		// Register the class if the file exists.
		if (file_exists($classFilePath))
		{
			JLoader::register($model_class, $classFilePath);

			return new $model_class;
		}

		return null;
	}

}


class AssetsHelper extends AssetsHelpersAssets
{

}
