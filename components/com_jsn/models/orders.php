<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


class JsnModelOrders extends JModelList
{
	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
			$config['filter_fields'] = array(
				'name', 'a.name', 'created_on', 'o.created_on'
				
			);
		parent::__construct($config);
	}
	
	protected function populateState($ordering = 'name', $direction = 'ASC')
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// List state information
		$value = $app->input->get('limit', $params->get('display_num',$app->getCfg('list_limit', 0)), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$orderCol = $app->input->get('filter_order', 'a.name'); //var_dump($this->filter_fields);
		$this->filter_fields=array('a.name');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.name';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('layout', $app->input->get('layout'));
	}
	
	protected function getListQuery()
	{
		$user=JsnHelper::getUser();
		$app = JFactory::getApplication();
		$params = $app->getParams();
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		
		if(!(int)$user->id)
			return false;
		$query->select('o.*, u.*, c.title AS service_name, t.title AS tag_name')->from('#__jsn_orders AS o');
		
		if((bool)$user->get('is_master'))
			$query->join('left','#__jsn_users as u ON u.id=o.master_id')->where("o.master_id=".$db->quote($user->id));
		else $query->join('left','#__jsn_users as u ON u.id=o.user_id')->where("o.user_id=".$db->quote($user->id));
		
		$query->join('left','#__content AS c ON c.id=o.svc_id');
		$query->join('left','#__tags AS t ON t.id=o.tag_id');
		$query->order('`time` ASC');
		//$this->getState('list.select','a.id'))->group('a.id')
		//$query->select($this->getState('list.select','a.id'))->group('a.id')->from('#__users AS a')->join('left','#__jsn_users as b ON a.id=b.id')->join('left','#__user_usergroup_map as c ON a.id=c.user_id')->where('a.block=0');//->order($db->escape('a.name') . ' ASC');
		
		//$query->where("b.is_master='1'");
//var_dump($query->__toString());
		return $query;
	}
}
