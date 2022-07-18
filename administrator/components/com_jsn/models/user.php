<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

require_once(JPATH_COMPONENT . '/../com_users/models/user.php');

/**
 * Methods supporting a list of user records.
 *
 * @since  1.6
 */
class JsnModelUser extends UsersModelUser
{
	public $services = array();
	public $svc_types = array();
	public $svc_prices = array();
	
	public function importUser($data)
	{
		JLoader::register('JsnHelper', JPATH_SITE.'/components/com_jsn/helpers/helper.php');
		JLoader::register('JsnUser', JPATH_SITE.'/components/com_jsn/helpers/helper.php');
		
		if(!array_key_exists('D', $data) || !array_key_exists('E', $data)){
			$this->setError("Должны быть указаны улица и номер дома");
			return false;
		}
		
		$usr_type=0;
		if(trim($data['A'])=='Заточка / Ремонт')
			$usr_type=2;
		elseif(trim($data['A'])=='Мастер')
			$usr_type=1;
		
		$db = JFactory::getDbo();
		$db->setQuery("SELECT id FROM #__jsn_users WHERE UPPER(CONCAT_WS(' ', firstname, lastname))="
			.$db->quote(mb_strtoupper(trim($data['B'])))." AND `house_number`=".$db->quote(trim($data['E']))." LIMIT 1");
		if($uid = $db->loadResult()){
			$user = JsnHelper::getUser($uid);
			$user->setValue('rating', (float)trim(str_replace(',', '.', $data['H'])));
			if($usr_type && !array_key_exists($uid, $this->svc_types))
				if(!$this->updateUser($user)){
					$this->setError("Ошибка обновления данных");
					return false;
				}
		}
		else{
			$user=new JsnUser(0);
			$user->set('name', trim($data['B']));
			if(array_key_exists('C', $data))
				$user->setValue('sity', trim($data['C']));
			if(array_key_exists('D', $data))
				$user->setValue('street', trim($data['D']));
			if(array_key_exists('E', $data))
				$user->setValue('house_number', trim($data['E']));
			if(array_key_exists('F', $data))
				$user->setValue('doorway', trim($data['F']));
							
			$user->setValue('rating', (float)trim(str_replace(',', '.', $data['H'])));
			$user->setValue('is_master', $usr_type);
			
			if(!$this->storeUser($user)){
				$this->setError("Ошибка добавления пользователя - ".$user->get('name').'<br>'.$user->getError());
				return false;
			}
		}
		
		if($usr_type && array_key_exists('G', $data))
			$this->svc_types[$user->id][] = $data['G'];
			
		if($usr_type && array_key_exists('I', $data) && array_key_exists('M', $data))
			if(trim($data['I']) && trim(data['M']))
				$this->svc_prices[$user->id][] = array('spec'=>$data['I'], 'price'=>$data['M'],
					'usl'=>array_key_exists('J', $data) ? $data['J'] : '', 
					'tag'=>array_key_exists('K', $data) ? $data['K'] : '',
					'tm'=>array_key_exists('L', $data) ? $data['L'] : '');

		if($usr_type && array_key_exists('N', $data) && array_key_exists('R', $data))
			if(trim($data['N']) && trim(data['R']))
				$this->svc_prices[$user->id][] = array('spec'=>$data['N'], 'price'=>$data['R'],
					'usl'=>array_key_exists('O', $data) ? $data['O'] : '', 
					'tag'=>array_key_exists('P', $data) ? $data['P'] : '',
					'tm'=>array_key_exists('Q', $data) ? $data['Q'] : '');
								
		//$this->setError(print_r($this->svc_prices, 1));
		//$this->setError('hello '.$db->quote(mb_strtoupper(trim($data['B']))).' '.$uid);
		return true;
	}
	
	public function updateUser($user)
	{
		$db = JFactory::getDbo();
		$r = (float)$user->getValue('rating');
		if(!$r){
			$this->setError("Ошибка поля рейтинг");
			return false;
		}
		$db->setQuery("UPDATE #__jsn_users SET rating=".$db->quote($r)." WHERE id=".$db->quote($user->id));
		$db->execute();
		return true;
	}
	
	public function storeUser(&$user)
	{
		$names = explode(' ', $user->name);
		$user->setValue('firstname', $names[0]);
		if(count($names)>1)
			$user->setValue('lastname', $names[1]);
		
		$user->set('username', JFilterOutput::stringURLSafe($user->name).'-'.rand(1, 99));
		$user->setValue('email', $user->get('username').'@nm.ru');
		$user->set('groups', array(1));
		$user->set('block', 0);
		
		if($user->getValue('is_master')){
			$user->set('groups', array(3));
			$user->set('work_day', range(1,7));
			$user->set('work_from', json_encode(array_fill(0,7,'10')));
			$user->set('work_to', json_encode(array_fill(0,7,'20')));
		}
		var_dump($user);
		JPluginHelper::importPlugin('user');
		return $user->save();
	}
	
	public function updateServices()
	{
		if(!$this->updateSvcTypes())
			return false;
		
		$db = JFactory::getDbo();
		$query = "SELECT LOWER(title) as title, id FROM #__categories WHERE published='1' AND level='2'";
		$query .= " AND (path LIKE 'uslugi/%' OR path LIKE 'zatochka-remont/%') ORDER BY `title` ASC";
		$db->setQuery($query); 
		$categories = $db->loadAssocList('id');
		if($categories && !empty($categories))
			$categories = array_column($categories, 'title', 'id');
		
		JFactory::getApplication()->set('list_limit', 5000);
		JLoader::import('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_content/models', 'ContentModel');
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		$model->setState('filter.published', 1);
		$model->setState('load_tags', 1);
		$model->setState('list.start', 0);
		$model->setState('list.limit', 5000);
		$model->setState('filter.category_id', array_keys($categories));
		
		$articles = $model->getItems();
		$usl_names = array();
		foreach($articles as $art)
			$usl_names[$art->id]=$art->catid.'-'.mb_strtolower($art->title);

		$tags = $tag_names = array();
		if(!empty($this->svc_prices))
			foreach($this->svc_prices as $uid=>$prices){
				$spec_array = array();
				$prices_array = array();
				if(!empty($prices))
					foreach($prices as $k=>$price){
						$spec = trim($price['spec']);
						$spec_id = (int)array_search(mb_strtolower($spec), $categories);
						if($spec_id)
							$spec_array[] = (string)$spec_id;
						else{
							$this->setError("Не найдена специализация - ".$spec);
							return false;
						}
						
						$usl = trim($price['usl']);
						$usl_id = (int)array_search($spec_id.'-'.mb_strtolower($usl), $usl_names);
						if(!$usl_id){
							$data = array('catid' => $spec_id,'title' => $usl,'introtext' => '','fulltext' => '','state' => 1);
							JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/models');
							$model = JModelLegacy::getInstance('Article','ContentModel');
							if($model->save($data))
								$usl_id = $model->getState('article.id');
						}
						if($usl_id){
							$tag_id = 0;
							if($price['tag']){
								if(!array_key_exists($usl_id, $tags)){
									$tags[$usl_id] = new JHelperTags;
									$tags[$usl_id]->getItemTags('com_content.article', $usl_id);
									
									if(array_key_exists($usl_id, $tags) && !empty($tags[$usl_id]->itemTags))
										foreach($tags[$usl_id]->itemTags as $tt)
											$tag_names[$usl_id][$tt->tag_id] = mb_strtolower($tt->title);
								}
								
								$tag_id = (int)array_search(mb_strtolower($price['tag']), $tag_names[$usl_id]);
								if(!$tag_id){
									$content = JTable::getInstance("Content", 'JTable', array());
									$content->load($usl_id);
									$tag_id = $this->addTag($price['tag'], $usl_id);
									$tags[$usl_id]->typeAlias = 'com_content.article';
									$tags[$usl_id]->postStoreProcess($content, array($tag_id), false);
									$tag_names[$usl_id][$tag_id]=mb_strtolower($price['tag']);
								}
								var_dump($tag_names[$usl_id]);
							}
							 
							$prices_array[$usl_id][] = array((int)$price['price'], (int)$price['tm'], $tag_id);
						}
						
							
					}
				
				$db->setQuery("UPDATE #__jsn_users SET vyberite_spetsialnos=".$db->quote(json_encode(array_unique($spec_array)))." WHERE id=".$db->quote($uid));
				$db->execute();
				
				if(!empty($prices_array)){
					$val = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($prices_array));
					
					$db->setQuery("UPDATE #__jsn_users SET prices=".$db->quote($val)." WHERE id=".$db->quote($uid));
					$db->execute();
				}
			}

		//$this->setError(print_r($categories, 1));		
		return true;
	}
	
	public function updateSvcTypes()
	{
		if(empty($this->svc_types)){
			$this->setError("Список типов пуст");
			return false;
		}
		
		foreach($this->svc_types as $uid=>$type){
			$home = array();
			if(is_array($type))
				$type=implode('/', $type);
			
			if(strpos($type, 'сaлон')!==false || strpos($type, 'салон')!==false)
				$home[] = '1';
			if(strpos($type, 'на дому')!==false)
				$home[] = '2';
			if(strpos($type, 'выезд')!==false)
				$home[] = '3';
			$this->svc_types[$uid]=json_encode($home);
			
		}
		
		foreach($this->svc_types as $uid=>$values){
			$db = JFactory::getDbo();
			$db->setQuery("UPDATE #__jsn_users SET home=".$db->quote($values)." WHERE id=".$db->quote($uid));
			$db->execute();
		}
		return true;
	}
	
	function addTag($tagName, $item_id){
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
		$tag = JTable::getInstance('Tag', 'TagsTable');
		
		if(!$tag->load(array('title'=>$tagName))){
			$th = new JHelperTags;
			$ids = $th->createTagsFromField(array('#new#'.$tagName));

			if(is_array($ids) && (int)$ids[0])
				$tag->load((int)$ids[0]);
			else return FALSE;
		}
		
		if(!$tag->id){ 
			$this->setError($tag->getError());
			return FALSE;
		}
		
		return $tag->id;
	}
}
