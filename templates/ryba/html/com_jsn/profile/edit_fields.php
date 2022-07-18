<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


JLoader::register('JHtmlUsers', JPATH_COMPONENT . '/helpers/html/users.php');
JHtml::register('users.spacer', array('JHtmlUsers', 'spacer'));

$this->user=JsnHelper::getUser($this->data->id);

$fieldsets = $this->form->getFieldsets();
if (isset($fieldsets['core']))   unset($fieldsets['core']);
if (isset($fieldsets['params'])) unset($fieldsets['params']);
if (isset($fieldsets['privacyconsent'])) unset($fieldsets['privacyconsent']);
if (isset($fieldsets['actionlogs'])) unset($fieldsets['actionlogs']);
if (isset($fieldsets['jsn_code_tab']))   unset($fieldsets['jsn_code_tab']);
if(isset($fieldsets['jsn_raspisanie']) && !(int)$this->data->get('is_master')) unset($fieldsets['jsn_raspisanie']);
if(isset($fieldsets['jsn_stocks']) && !(int)$this->data->get('is_master')) unset($fieldsets['jsn_stocks']);
if(isset($fieldsets['jsn_bookmarks']) && (int)$this->data->get('is_master'))
	unset($fieldsets['jsn_bookmarks']);	
if (JFactory::getUser()->id != $this->data->id && isset($fieldsets['profile'])) unset($fieldsets['profile']);
//var_dump($fieldsets);
/* Add field to exclude (these fields are in other template position) */
$this->excludeFromProfile[]='jform[avatar]';
$this->excludeFromProfile[]='jform[registerdate]';
$this->excludeFromProfile[]='jform[lastvisitdate]';



if($this->data->get('is_master')==1)
	$this->form->setFieldAttribute('vyberite_spetsialnos', 'dboptwhere', "published=1 AND path LIKE 'uslugi/%' AND level='2'");
elseif($this->data->get('is_master')==2)
	$this->form->setFieldAttribute('vyberite_spetsialnos', 'dboptwhere', "published=1 AND path LIKE 'zatochka-remont/%' AND level='2'");
	
//var_dump($this->form->getField('vyberite_spetsialnos'));
//published=1 AND (path LIKE 'uslugi/%' OR  path LIKE 'zatochka-remont/%') AND level='2

$tmp          = isset($this->data->jcfields) ? $this->data->jcfields : array();
$customFields = array();

foreach ($tmp as $customField)
{
	$customFields[$customField->name] = $customField;
}

foreach ($fieldsets as $group => $fieldset): // Iterate through the form fieldsets
//var_dump($this->form);die();
    $fields = $this->form->getFieldset($group);
	$this->form->setFieldAttribute('stocks_servis', 'type', 'input');
	$this->form->setFieldAttribute('stock_prices', 'type', 'input');
	$this->form->setFieldAttribute('raspisanie', 'type', 'hidden');
    $this->form->setFieldAttribute('work_day', 'type', 'checkboxlist');
	$this->form->setFieldAttribute('work_from', 'type', 'list');
	$this->form->setFieldAttribute('work_to', 'type', 'list');
	$this->form->setFieldAttribute('is_master', 'type', 'hidden');
	$this->form->setFieldAttribute('password2', 'type', 'hidden');

	$count=0;
	$empty=array();
	foreach ($fields as $field)
	{
		if(!$field->hidden && !in_array($field->name,$this->excludeFromProfile))
		{
			if($field->value=='' && $this->config->get('hideempty',0)) $empty[]=$field->name; 
			else $count+=1;
		}
	}
	if($count):
?>
<fieldset id="<?php echo $group;?>" class="fields-edit">
	<?php if (isset($fieldset->label)): // If the fieldset has a label set, display it as the legend.?>
	<legend><?php echo JText::_($fieldset->label); ?></legend>
	<?php endif;?>
	<dl class="dl-horizontal">
	<?php foreach ($fields as $field): //var_dump($field->required);
		if($field->type=='Hidden' || $field->type=='hidden'): 
			echo $this->form->getInput($field->fieldname);
		elseif(!in_array($field->name,$empty) && !in_array($field->name,$this->excludeFromProfile)) :
			if($field->title && $field->title[0]=='<')
				$field->title = str_replace('span', 'dt', $field->title);
			else $field->title = '<dt class="'.$field->fieldname.'Label'.($field->required ? ' required' : '').'">'.$field->title.'</dt>';
		?>
		<?php echo ($group=='jsn_default') ? str_replace('no-title', $field->fieldname.'Label'.($field->required ? ' required' : ''), $field->title) : $field->title; ?>
		<dd class="control-group <?php echo $field->fieldname; ?>Value"><?php 
			if(array_key_exists($field->fieldname, $customFields))
				echo $customFields[$field->fieldname]->value ?: JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
			else echo JTplHelper::renderField($field, $this->form);
		?></dd>
		<?php endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
	<?php endif;?>
<?php endforeach;?>
