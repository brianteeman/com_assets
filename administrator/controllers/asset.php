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

jimport('joomla.application.component.controllerform');

/**
 * Asset controller class.
 *
 * @since  1.6
 */
class AssetsControllerAsset extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'assets';
		parent::__construct();
	}

	public function publish_plugin()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set('enabled = 1');
		$query->where('type="plugin" AND element="assets" AND folder="system"');
		$db->setQuery($query);
		//echo str_replace('#__', JFactory::getConfig()->get('dbprefix'), $db->getQuery());

		if ($db->execute())
		{
			$this->setRedirect('index.php?option=com_assets&view=assets');
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ASSETS_PLUGIN_PUBLISHED'), 'success');
		}

	}


}
