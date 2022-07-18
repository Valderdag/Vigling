<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


class JsnViewStocks extends JViewLegacy
{
	protected $state;
	protected $items;
	
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params		= $this->state->params;
		$this->config		= JComponentHelper::getParams('com_jsn');

		parent::display($tpl);
	}
}

?>
