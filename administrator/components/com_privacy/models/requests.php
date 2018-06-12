<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Requests management model class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyModelRequests extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'email', 'a.email',
				'requested_at', 'a.requested_at',
				'request_type', 'a.request_type',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'status', 'a.status',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from($db->quoteName('#__privacy_requests', 'a'));

		// Join over the users for the username.
		$query->select($db->quoteName('ua.username', 'username'));
		$query->join('LEFT', $db->quoteName('#__users', 'ua') . ' ON ua.id = a.user_id');

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'));
		$query->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON uc.id = a.checked_out');

		// Filter by status
		$status = $this->getState('filter.status');

		if (is_numeric($status))
		{
			$query->where('a.status = ' . (int) $status);
		}

		// Filter by request type
		$requestType = $this->getState('filter.request_type', '');

		if ($requestType)
		{
			$query->where('a.request_type = ' . $db->quote($db->escape($requestType, true)));
		}

		// Filter by search in email
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . $db->quoteName('a.email') . ' LIKE ' . $search . ')');
			}
		}

		// Handle the list ordering.
		$ordering  = $this->getState('list.ordering');
		$direction = $this->getState('list.direction');

		if (!empty($ordering))
		{
			$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		}

		return $query;
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
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.status');
		$id .= ':' . $this->getState('filter.request_type');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		// Load the filter state.
		$this->setState(
			'filter.search',
			$this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search')
		);

		$this->setState(
			'filter.status',
			$this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'int')
		);

		$this->setState(
			'filter.request_type',
			$this->getUserStateFromRequest($this->context . '.filter.request_type', 'filter_request_type', '', 'string')
		);

		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_privacy'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to return older privacy requests.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOlder()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$items = array();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__privacy_requests'));
		$query->where($db->quoteName('status') . ' = 1 ');
		$query->where('DATE_SUB( NOW(), INTERVAL 14 DAY) > ' . $db->quoteName('requested_at'));
		$db->setQuery($query);
		
		$count = $db->loadRow();

		if (!$count['0'] > 0)
		{
			return array();
		}

		for ($i=0; $i < $count['0'] ; $i++) 
		{ 
			$items[] = $count['0'];
		}

		return $items;
	}
}
