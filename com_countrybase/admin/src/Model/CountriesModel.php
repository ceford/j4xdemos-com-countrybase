<?php
/**
 * @package     Countrybase.Administrator
 * @subpackage  com_countrybase
 *
 * @copyright   (C) 2022 Clifford E Ford
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace J4xdemos\Component\Countrybase\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class CountriesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'iso_2', 'a.iso_2',
					'iso_3', 'a.iso_3',
					'country_code', 'a.country_code',
					'region_code', 'a.region_code',
					'state', 'a.state',
					'subregion_code', 'a.subregion_code',
					'phone_prefix', 'a.phone_prefix',
					'currency_code', 'a.currency_code',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.title'));
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		//$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of countries subject to the model state.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select',
						[
								$db->quoteName('a.id'),
								$db->quoteName('a.title'),
								$db->quoteName('a.iso_2'),
								$db->quoteName('a.iso_3'),
								$db->quoteName('a.country_code'),
								$db->quoteName('a.region_code'),
								$db->quoteName('a.subregion_code'),
								$db->quoteName('a.phone_prefix'),
								$db->quoteName('a.currency_code'),
								$db->quoteName('a.state'),
								$db->quoteName('b.title') . ' AS currency_title',
								$db->quoteName('b.dollar_exchange_rate'),
						]
						)
				)
				->from($db->quoteName('#__countrybase_countries', 'a'))
				->leftjoin($db->quoteName('#__countrybase_currencies', 'b') . 'ON a.currency_code = b.currency_code');

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.title LIKE ' . $search . ')');
		}

		// Filter by published state
		$published = (string) $this->getState('filter.published');

		if ($published !== '*')
		{
			if (is_numeric($published))
			{
				$state = (int) $published;
				$query->where($db->quoteName('a.state') . ' = :state')
				->bind(':state', $state, ParameterType::INTEGER);
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		return $query;
	}
}