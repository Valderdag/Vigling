<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

include(JPATH_COMPONENT . '/../com_users/views/users/view.html.php');

/**
 * View class for a list of users.
 *
 * @since  1.6
 */
class JsnViewUsers extends UsersViewUsers
{
	public function display($tpl = null)
	{
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		$canDo = $this->canDo;
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_USERS_VIEW_USERS_TITLE'), 'users user');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('user.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('user.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publish('users.activate', 'COM_USERS_TOOLBAR_ACTIVATE', true);
			JToolbarHelper::unpublish('users.block', 'COM_USERS_TOOLBAR_BLOCK', true);
			JToolbarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_USERS_TOOLBAR_UNBLOCK', true);
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'users.delete', 'JTOOLBAR_DELETE');
			JToolbarHelper::divider();
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_users')
			&& $user->authorise('core.edit', 'com_users')
			&& $user->authorise('core.edit.state', 'com_users'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		
			$title = JText::_('Импорт');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.modal');

			$dhtml = $layout->render(array('text' => $title, 'selector'=>'collapseModalImport', 'class'=>'btn btn-small btn-warning'));
			$bar->appendButton('Custom', $dhtml, 'batch1');	

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_users');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_USERS_USER_MANAGER');
	}
}
