<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

require_once(JPATH_COMPONENT . '/../com_users/controllers/user.php');

/**
 * View class for a list of users.
 *
 * @since  1.6
 */
class JsnControllerUser extends UsersControllerUser
{
	public function import()
	{
		$app = JFactory::getApplication();
		$file = $this->input->files->get('file');
		if($file['error'] || $file['size']==0){
			$this->setMessage(JText::_('Выберите файл'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=users' . $this->getRedirectToListAppend(), false));
			return;
		}
		if($file['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
			$this->setMessage(JText::_('Файл должен быть в формате Excel (xlsx)'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=users' . $this->getRedirectToListAppend(), false));
			return;
		}
		
		JLoader::register('WTExcelFile', JPATH_ADMINISTRATOR.'/components/com_jsn/helpers/excel.php');
		
		$config	=& JFactory::getConfig();
		$filename = $config->get('tmp_path').'/'.basename($file['name']);
		
		if(!JFile::upload($file['tmp_name'], $filename)){
			$this->setMessage(JText::_('Не удалось загрузить файл'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=users' . $this->getRedirectToListAppend(), false));
			return;
		}
		
		
		$e = new WTExcelFile($filename, 0);
		$sheets = $e->xlsx_getWorkbook();
		$ids = array_keys($sheets);
		$table = $e->xlsx_getSheetData(array_shift($ids));
		
		$model = $this->getModel();
		foreach($table as $k=>$r){
			if($k==1) continue;
			if(!array_key_exists('A', $r) || !array_key_exists('B', $r) || trim($r['B'])=='')
				continue;
			if(trim($r['A'])=='Заточка / Ремонт' || trim($r['A'])=='Мастер')
				if(!$model->importUser($r)){ 
					$this->setMessage($model->getError(), 'error');
					break;
				}
		}
//var_dump($model);die();
//die();
		if(!$model->getError())
			if(!$model->updateServices())
				$this->setMessage($model->getError(), 'error');
		
		if(!$model->getError())
			$this->setMessage(JText::_('Загрузка завершена'));
			
		$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=users' . $this->getRedirectToListAppend(), false));
		return;
	}
}
