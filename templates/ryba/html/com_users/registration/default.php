<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
$this->form->setFieldAttribute('avatar', 'default', 'templates/ryba/images/avatar_upload.png');
$this->form->setFieldAttribute('portfolio_field', 'default', 'templates/ryba/images/3.png');

?>
<div class="registration<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="container header-bot">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		<div class="clearFloat"></div>
    </div>
	<?php endif; ?>
	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate form-horizontal well init" enctype="multipart/form-data">
		<?php // Iterate through the form fieldsets and display each one. disabled?>
		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
			<?php $fields = $this->form->getFieldset($fieldset->name); ?>
			<?php if ((count($fields)) && ($fieldset->name != "fields-0")) : ?>
					<fieldset id="<?php echo $fieldset->name?>">
						<?php // If the fieldset has a label set, display it as the legend. ?>
						<?php if (isset($fieldset->label)) : ?>
							<legend><?php echo JText::_($fieldset->label); ?></legend>
						<?php endif; ?>
						<?php echo $this->form->renderFieldset($fieldset->name); ?>
					</fieldset>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="dale validate" >
					<?php echo JText::_('JREGISTER'); ?>
				</button>
				<a class="dale" href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
				</a>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="registration.register" />
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
