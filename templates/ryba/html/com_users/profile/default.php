<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

?>
<div class="profile<?php echo $this->pageclass_sfx; ?>">
	<div class="container header-bot">
		<h1>Профиль</h1>
		<div class="clearFloat"></div>
	<?php if (JFactory::getUser()->id == $this->data->id) : ?>
		<ul class="btn-toolbar prof">
			<li class="btn-group">
				<a id="prof" href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id=' . (int) $this->data->id); ?>">
					<span class="icon-user"></span>
					<?php echo JText::_('COM_USERS_EDIT_PROFILE'); ?>
				</a>
			</li>
		</ul>
	<?php endif; ?>	
    </div>
	
	<?php echo $this->loadTemplate('core'); ?>
	<?php echo $this->loadTemplate('params'); ?>
	<?php echo $this->loadTemplate('custom'); ?>
</div>
