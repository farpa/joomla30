<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsModelScanalerts extends FOFModel
{
	public function buildQuery($overrideLimits = false) {
		$db = $this->getDbo();
		$query = parent::buildQuery($overrideLimits)
			->clear('select')
			->clear('order')
			->select(array(
				$db->quoteName('admintools_scanalert_id'),
				'IF('.$db->quoteName('diff').' != "",0,1) AS '.$db->quoteName('newfile'),
				'IF('.$db->quoteName('diff').' LIKE "###SUSPICIOUS FILE###%",1,0) AS '.$db->quoteName('suspicious'),
				'IF('.$db->quoteName('diff').' != "",'.
				'IF('.$db->quoteName('diff').' LIKE "###SUSPICIOUS FILE###%",'.
				$db->quote('0-suspicious').','.$db->quote('2-modified').')'
				.','.$db->quote('1-new').') AS '.$db->quoteName('filestatus'),
				$db->quoteName('path'),
				$db->quoteName('threat_score'),
				$db->quoteName('acknowledged'),
			));

		if(!$overrideLimits) {
			$order = $this->getState('filter_order',null,'cmd');
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			if(!in_array($order, array('path','threat_score','acknowledged','filestatus','newfile','suspcious'))) {
				$order = 'threat_score';
				$dir = 'DESC';
			}
			$query->order($db->quoteName($order).' '.$dir);
		}

		return $query;
	}
}