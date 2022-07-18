<?php defined('_JEXEC') or die;

JLoader::register('JTplHelper', JPATH_SITE . '/templates/ryba/html/helper.php');
$this->user=JsnHelper::getUser($this->data->id);
$this->current_user=JsnHelper::getUser();

if($this->current_user->id==$this->user->id){
	echo $this->loadTemplate(((int)$this->data->get('is_master')) ? 'master' : 'client');
	return;
}
