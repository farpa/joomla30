<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsModelIpautobans extends FOFModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->select(array('*'))
			->from($db->quoteName('#__admintools_ipautoban'));
		
		$fltIP			= $this->getState('ip', null, 'string');
		if($fltIP) {
			$fltIP = '%'.$fltIP.'%';
			$query->where($db->quoteName('ip').' LIKE '.$db->quote($fltIP));
		}

		$fltReason		= $this->getState('reason', null, 'cmd');
		if($fltReason) {
			$fltReason = '%'.$fltReason.'%';
			$query->where($db->quoteName('reason').' LIKE '.$db->quote($fltReason));
		}
		
		if(!$overrideLimits) {
			$order = $this->getState('filter_order',null,'cmd');
			if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'ip';
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order.' '.$dir);
		}

		return $query;
	}
	
	/**
	 * Sets the list of IDs from the request data
	 */
	public function setIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = FOFInput::getVar('cid', null, $this->input, 'array');
		$id = FOFInput::getCmd('id', 0, $this->input);
		$kid = FOFInput::getCmd($this->getTable($this->table)->getKeyName(), 0, $this->input);

		if(is_array($cid) && !empty($cid))
		{
			$this->setIds($cid);
		}
		else
		{
			if(empty($id)) {
				$this->setId($kid);
			} else {
				$this->setId($id);
			}
		}
		
		return $this;
	}

	/**
	 * Sets the ID and resets internal data
	 * @param int $id The ID to use
	 * 
	 * @return FOFModel
	 */
	public function setId($id=0)
	{
		$this->reset();
		$this->id = $id;
		$this->id_list = array($this->id);
		return $this;
	}

	/**
	 * Sets a list of IDs for batch operations from an array and resets the model
	 * 
	 * @return FOFModel
	 */
	public function setIds($idlist)
	{
		$this->reset();
		$this->id_list = array();
		$this->id = 0;
		if(is_array($idlist) && !empty($idlist)) {
			foreach($idlist as $value)
			{
				$this->id_list[] = $value;
			}
			$this->id = $this->id_list[0];
		}
		return $this;
	}
}