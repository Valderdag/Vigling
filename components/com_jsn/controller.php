<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


class JsnController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$config = JComponentHelper::getParams('com_jsn');
		
		$vName=JFactory::getApplication()->input->get('view','profile');
		
		if(JFactory::getApplication()->input->get('format','')!='raw') echo('<div id="easyprofile" class="view_'.$vName.'">');
		
		switch($vName){
			case 'profile':
				$user=JFactory::getUser();
				$profileAcl=$config->get('profileACL',2);
				//var_dump($config);die();
				if(JFactory::getApplication()->input->get('id')==null && $user->guest)
					JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&task=&return='.base64_encode( JURI::getInstance()->toString()) ,false));
				else
				{
					if(JFactory::getUser(JFactory::getApplication()->input->get('id'))->block)
					{
						$lang = JFactory::getLanguage();
						$lang->load('com_users');
						JFactory::getApplication()->enqueueMessage(JText::_('COM_USERS_USER_BLOCKED'));
					}
					else
					switch($profileAcl){
						case 0: // Private
							if(JFactory::getApplication()->input->get('id')==$user->id || JFactory::getApplication()->input->get('id')==null || $user->authorise('core.edit', 'com_users'))
							{
								JsnHelper::getUserProfile(JFactory::getApplication()->input->get('id'));
							}
							else
							{
								JFactory::getApplication()->enqueueMessage(JText::_('COM_JSN_NOTVIEWPROFILE'));
							}
						break;
						case 1: // Only Registered
							if(!$user->guest)
							{
								JsnHelper::getUserProfile(JFactory::getApplication()->input->get('id'));
							}
							else
							{
								JFactory::getApplication()->enqueueMessage(JText::_('COM_JSN_NOTVIEWPROFILE').' - '.JText::_('COM_JSN_LOGINPLEASE'));
								JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&task=&return='.base64_encode( JURI::getInstance()->toString() ),false));
							}
						break;
						case 2: // Public
							JsnHelper::getUserProfile(JFactory::getApplication()->input->get('id'));
						break;
						case 3: // Custom
							$access=$user->getAuthorisedViewLevels();
							$profileAclCustom=$config->get('profileACLcustom','');
							if(JFactory::getApplication()->input->get('id')==$user->id || JFactory::getApplication()->input->get('id')==null || in_array($profileAclCustom,$access) || $user->authorise('core.edit', 'com_users'))
							{
								JsnHelper::getUserProfile(JFactory::getApplication()->input->get('id'));
							}
							else
							{
								JFactory::getApplication()->enqueueMessage(JText::_('COM_JSN_NOTVIEWPROFILE'));
							}
						break;
					}
				}
			break;
			case 'orders':
				$app=JFactory::getApplication();
				$user=JsnHelper::getUser();
				if(!$user->id){
					$app->enqueueMessage('Для доступа к записям необходимо войти на сайт.', 'warning');
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&task=&return='
						.base64_encode( JURI::getInstance()->toString()),false));
					return;
				}
					
				$model = $this->getModel($vName);
				$document	= JFactory::getDocument();
				$vFormat = $document->getType();
				$view=$this->getView($vName, $vFormat);
				$view->setModel($model, true);
				if((bool)$user->get('is_master'))
					$view->setLayout('master');
				else $view->setLayout($this->input->getCmd('layout', 'default'));

				$view->document = $document;
				$view->display();
			break;
			//STOCKS
			case 'stocks':
				$app=JFactory::getApplication();
				$user=JsnHelper::getUser();
				if(!$user->id){
					$app->enqueueMessage('Для доступа к записям необходимо войти на сайт.', 'warning');
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&task=&return='
						.base64_encode( JURI::getInstance()->toString()),false));
					return;
				}
					
				$model = $this->getModel($vName);
				$document	= JFactory::getDocument();
				$vFormat = $document->getType();
				$view=$this->getView($vName, $vFormat);
				$view->setModel($model, true);
				if((bool)$user->get('is_master'))
					$view->setLayout('master');
				else $view->setLayout($this->input->getCmd('layout', 'default'));

				$view->document = $document;
				$view->display();
			break;
			//END STOCKS
			case 'search':
			case 'list':
				$app = JFactory::getApplication();
				$menu = $app->getMenu();
				$item = $menu->getActive();
				if(isset($item->link) && (strpos($item->link,'option=com_jsn&view=list')>0 || strpos($item->link,'option=com_jsn&view=search'))>0)
				{
					if(JFactory::getApplication()->input->get('search',0) && !JSession::checkToken('get')){die('Not Valid Token');}
					$document	= JFactory::getDocument();
					$vName=($vName=='search' ? 'list' : $vName);
					$lName   = $this->input->getCmd('layout', (isset($item->query['layout']) ? $item->query['layout'] : 'default'));
					$vFormat = $document->getType();
					$model = $this->getModel($vName);
					
					$view=$this->getView($vName, $vFormat);
					$view->setModel($model, true);
					$view->setLayout($lName);

					// Push document object into the view.
					$view->document = $document;
					$view->display();
				}
				else echo('<h1>'.JText::_('JLIB_RULES_NOT_ALLOWED').'</h1>');
			break;
			case 'stocklist':
				$app = JFactory::getApplication();
				$menu = $app->getMenu();
				$item = $menu->getActive();
				if(isset($item->link) && (strpos($item->link,'option=com_jsn&view=stocklist')>0 || strpos($item->link,'option=com_jsn&view=search'))>0)
				{
					if(JFactory::getApplication()->input->get('search',0) && !JSession::checkToken('get')){die('Not Valid Token');}
					$document	= JFactory::getDocument();
					$vName=($vName=='search' ? 'stocklist' : $vName);
					$lName   = $this->input->getCmd('layout', (isset($item->query['layout']) ? $item->query['layout'] : 'default'));
					$vFormat = $document->getType();
					$model = $this->getModel($vName);
					
					$view=$this->getView($vName, $vFormat);
					$view->setModel($model, true);
					$view->setLayout($lName);

					// Push document object into the view.
					$view->document = $document;
					$view->display();
				}
				else echo('<h1>'.JText::_('JLIB_RULES_NOT_ALLOWED').'</h1>');
			break;
			case 'getField':
				$user=JsnHelper::getUser();
				if(!$user->guest && JFactory::getApplication()->input->get('alias','')!=''){
					echo $user->getField(JFactory::getApplication()->input->get('alias',''));
				}
			break;
			case 'setField':
				$user=JsnHelper::getUser();
				if(!$user->guest && JFactory::getApplication()->input->get('alias','')!=''){
					$user->setValue(JFactory::getApplication()->input->get('alias',''),JFactory::getApplication()->input->get('value',''));
					$user->save();
				}
			break;
			case 'opField':
				foreach (glob(JPATH_ADMINISTRATOR . '/components/com_jsn/helpers/fields/*.php') as $filename) {
					require_once $filename;
				}
				if(JFactory::getApplication()->input->get('type','')!=''){
					$class='Jsn'.ucfirst(JFactory::getApplication()->input->get('type','')).'FieldHelper';
					if(class_exists($class))
					{
						$class::operations();
					}
				}
			break;
			default:
				$dispatcher	= JEventDispatcher::getInstance();
				echo(implode(' ',$dispatcher->trigger('renderPlugin',array())));
			break;

		}
		
		if(JFactory::getApplication()->input->get('format','')!='raw') echo('</div>');
		
	}
	
	public function get_article()
	{
		$cat_id = JFactory::getApplication()->input->getInt('parent_cat_id');
		if(!$cat_id){
			echo new JResponseJson(array(1), 'error', true);
			jexit();
		}
		$name = JFactory::getApplication()->input->getString('name');
		$name = mb_strtolower(trim($name));
		$db = JFactory::getDBO();
		
		$query = "SELECT * FROM #__content WHERE catid=".$db->quote($cat_id)." AND LOWER(title)=".$db->quote($name);
		$db->setQuery($query); 
		$articles = $db->loadObjectList(); 

		if(!empty($articles)){
			echo new JResponseJson(array('item'=>$articles[0]));
			jexit();
		}
		
		$data = array('catid' => $cat_id,'title' => $name,'introtext' => '','fulltext' => '','state' => 1);
		
		JModelLegacy::addIncludePath(JPATH_SITE.'/administrator/components/com_content/models', 'ContentModel');
		$model =  JModelLegacy::getInstance('Article','ContentModel');
		if($model->save($data)){
			$query = "SELECT * FROM #__content WHERE catid=".$db->quote($cat_id)." AND LOWER(title)=".$db->quote($name);
			$db->setQuery($query); 
			$articles = $db->loadObjectList();
			if(!empty($articles))
				echo new JResponseJson(array('item'=>$articles[0]));
		}
		else echo new JResponseJson(array(2), 'error', true);
		jexit();		
	}
	
	public function get_articles()
	{
		$cat_id = JFactory::getApplication()->input->getInt('cat_id');
		if(!$cat_id){
			echo new JResponseJson(array(4), 'error', true);
			jexit();
		}
		JLoader::import('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
		$model = JModelLegacy::getInstance('Articles', 'ContentModel');
		$model->getState();
		$model->setState('filter.published', 1);
		$model->setState('filter.category_id', $cat_id);
		$model->setState('list.ordering', 'a.title');
		$model->setState('list.direction', 'ASC');
		$articles = $model->getItems();
						
		echo new JResponseJson($articles);
		jexit();
	}
	
	public function get_list_items()
	{
		$items = array();
		
		echo new JResponseJson($items);
		jexit();
	}
	
	public function get_services()
	{
		$sr = JFactory::getApplication()->input->getString('search');
		
		$db = JFactory::getDBO();
		$query = "SELECT title, id FROM #__categories WHERE published='1' AND level='2' AND path LIKE 'uslugi/%' AND LOWER(title) LIKE '".$sr."%'";
		if($layout == 'table'){
			$query = "SELECT title, id FROM #__categories WHERE published='1' AND level='2' AND path LIKE 'zatochka-remont/%' AND LOWER(title) LIKE '".$sr."%'";
		}
		$db->setQuery($query); 
		$categories = $db->loadAssocList('id'); 
		$categories = array_column($categories, 'title', 'id');
		
		$query = "SELECT CONCAT(cc.title, ' / ', c.title) as title, CONCAT(cc.id, '-',LPAD(c.id, 5, 0)) AS id" 
			." FROM #__content AS c LEFT JOIN #__categories AS cc ON c.catid=cc.id"
			." WHERE state='1' AND LOWER(c.title) LIKE '".$sr."%' AND cc.level='2' AND cc.path LIKE 'uslugi/%'";
		if($layout == 'table'){
			$query = "SELECT CONCAT(cc.title, ' / ', c.title) as title, CONCAT(cc.id, '-',LPAD(c.id, 5, 0)) AS id"
			." FROM #__content AS c LEFT JOIN #__categories AS cc ON c.catid=cc.id"
			." WHERE state='1' AND LOWER(c.title) LIKE '".$sr."%' AND cc.level='2' AND cc.path LIKE 'zatochka-remont/%'";
		}
		$db->setQuery($query); 
		$services = $db->loadAssocList('id');
		$services = array_column($services, 'title', 'id');
		if(!empty($categories))
			foreach($categories as $cid=>$cat)
				$services[$cid] = $cat;
			
		echo new JResponseJson($services);
		jexit();
	}
	
	public function bookmark()
	{
		$app = JFactory::getApplication();
		$master_id = $app->input->getInt('master_id');
		
		if(!$master_id || !(bool)JsnHelper::getUser($master_id)->get('is_master')){
			echo new JResponseJson(array('error_code'=>2), 'Ошибка пользователь не является мастером', true);
			jexit();
		}
		
		$user=JsnHelper::getUser();
		
		if(!$user->id){
			echo new JResponseJson(array('error_code'=>2), 'Ошибка необходимо зарегистрироваться на сайте', true);
			jexit();
		}
		
		if(!($user->favorites) || !is_array($user->favorites))
			$user->setValue('favorites', array());
		
		if(!$app->input->getBool('remove'))
			$user->setValue('favorites', array_merge($user->favorites, [(string)$master_id]));
		else $user->setValue('favorites', array_diff($user->favorites, [(string)$master_id]));

		$db = JFactory::getDbo();
		$db->setQuery("UPDATE #__jsn_users SET favorites=".$db->quote(json_encode(array_unique($user->favorites)))
			." WHERE id=".$db->quote($user->id));
		$db->execute();

		echo new JResponseJson(array($app->input->getBool('remove')));
		jexit();
	}
	
	public function get_key()
	{
		$mail = JFactory::getApplication()->input->getString('email1');
		$session=JFactory::getSession();
		if((time()-(int)$session->get('key_sended'))<120){
			echo new JResponseJson(array('error_code'=>3), 'Повторный запрос кода возможен через 2 минуты', true);
			jexit();
		}
		
		$key = $session->getFormToken(true);
		$code = str_pad(hexdec(substr($key, -3)), 5, '0', STR_PAD_LEFT);
		$key = substr($key, 0, -3);
		
		if(!$this->sendCodeEMail($mail, $code)){
			echo new JResponseJson(array('error_code'=>2), 'Ошибка отправки E-mail', true);
			jexit();
		}
		
		$session->set('key_sended', time());
		echo new JResponseJson(array('mail'=>$mail, 'key'=>$key, /*'code'=>$code,*/ 'snd'=>$session->get('key_sended')));
		jexit();
	}
	
	public function sendCodeEMail($mail, $code)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = 'Код подтверждения для регистрации на сайте '.$config->get('sitename').'.RU';
		
		$emailBody = '<h1 style="text-align:center">Код подтверждения</h1>
		<p style="font-size: 70px;font-family: &quot;Arial Black&quot;;letter-spacing:12px;text-align: center;position: relative;margin: 40px;">'.$code.'</p>
		<p style="text-align:center">Код действителен в течение 10 минут.</p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $ret = $mailer->Send();
         
		//file_put_contents(__FILE__.".log", print_r($mailer, 1));
		return $ret;
	}
	
	public function sendNotifyMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Новая запись на услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Новая запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p><p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $ret = $mailer->Send();

		return $ret;
	}
	
	public function sendUserMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Вы записались на услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Новая запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p><p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $ret = $mailer->Send();

		return $ret;
	}
	
	public function replNotifyMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Изменение записи на услугу '.$svc_name ;
		
		$emailBody = '<h1 style="text-align:center">Изменилась запись на услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="font-size:16px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=orders').'">все записи</a></p>';
		
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $repl = $mailer->Send();

		return $repl;
	}
	
	public function replUserMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject =  $config->get('sitename').'.RU Изменение записи на услугу '.$svc_name ;
		
		$emailBody = '<h1 style="text-align:center">Изменилась запись на услугу  '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="font-size:16px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=orders').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $repl = $mailer->Send();

		return $repl;
	}

public function delNotifyMail($mail, $name, $date, $svc_name, $phone)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Удалена запись на услугу '.$svc_name ;
		
		$emailBody = '<h1 style="text-align:center">Удалена запись на услугу '.$svc_name.' </h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=orders').'">все записи</a></p>';
		
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $del = $mailer->Send();

		return $del;
	}
	
	public function delUserMail($mail, $name, $date, $svc_name, $phone)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename'). '.RU Удалена запись на услугу '.$svc_name ;
		
		$emailBody = '<h1 style="text-align:center">Удалена запись на услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=orders').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $del = $mailer->Send();

		return $del;
	}
    ////STOCKS MAIL
	//SEND STOCKS BLOCK
	public function sendStockNotifyMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Новая запись на акционную услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Новая запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p><p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $stock = $mailer->Send();
		return $stock;
	}
	
	public function sendStockUserMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Вы записались на акционную услугу '.$svc_name;

		$emailBody = '<h1 style="text-align:center">Ваша запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p><p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $stock = $mailer->Send();

		return $stock;
	}
	//END SEND STOCKS BLOCK
	//DELETE STOCKS BLOCK
	public function delStockNotifyMail($mail, $name, $date, $svc_name)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Удалена запись на акционную услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Удалена запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $delStock = $mailer->Send();

		return $delStock;
	}
	
	public function delStockUserMail($mail, $name, $date, $svc_name)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Удалена запись на акционную услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Удалена запись на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Дата: '.$date.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=orders').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $delStock = $mailer->Send();

		return $delStock;
	}
	//END DELETE STOCKS BLOCK
    //REPLACE STOCKS BLOCK
	public function replStockNotifyMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Изменение записи на акционную услугу '.$svc_name;
		
		$emailBody = '<h1 style="text-align:center">Изменение записи на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Новая дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $replStock = $mailer->Send();

		return $replStock;
	}
	
	public function replStockUserMail($mail, $name, $date, $svc_name, $phone, $note)
	{
		$config = JFactory::getConfig();
		
		$emailSubject = $config->get('sitename').'.RU Изменение записи на акционную услугу '.$svc_name;

		$emailBody = '<h1 style="text-align:center">Изменение записи на акционную услугу '.$svc_name.'</h1>
		<p style="font-size:30px;font-family: &quot;Arial Black&quot;;letter-spacing:4px;text-align: center;position: relative;margin: 40px;">'.$name.'</p>
		<p style="text-align:center">Новая дата: '.$date.'</p>
		<p style="text-align:center">Комментарий клиента: '.$note.'</p>
		<p style="text-align:center">Телефон: '.$phone.'</p>
		<p style="text-align:center"><a href="'.JUri::base().'/'.JRoute::_('index.php?option=com_jsn&view=stocks').'">все записи</a></p>';
		
		$mailer=JFactory::getMailer();
		$mailer->isHTML(TRUE);
		$mailer->addRecipient(JStringPunycode::emailToPunycode($mail));
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setBody($emailBody);
		$mailer->setSubject($emailSubject);
        $replStock = $mailer->Send();

		return $replStock;
	}
	//END REPLACE STOCKS BLOCK
	////END STOCKS MAIL
	public function check_key()
	{
		$code = JFactory::getApplication()->input->getInt('code');
		$session=JFactory::getSession();
		if((time()-(int)$session->get('key_sended'))>600){
			//echo new JResponseJson(array('error_code'=>3), 'Код недействителен', true);
			//jexit();
		}
		$key = $session->getFormToken();
		if($code!=(int)hexdec(substr($key, -3))){
			echo new JResponseJson(array('error_code'=>1), 'Код недействителен', true);
			jexit();
		}
		echo new JResponseJson(array('key'=>$key, 'code'=>$code, 'code2'=>(int)hexdec(substr($key, -3)), 'snd'=>$session->get('key_sended')));
		jexit();
	}
	
	public function review()
	{		
		if(!JSession::checkToken()){
			echo new JResponseJson(array('error_code'=>1), 'Код недействителен', true);
			jexit();
		}
		
		$session=JFactory::getSession();
		if((time()-(int)$session->get('review_sended'))>1800){
			//echo new JResponseJson(array('error_code'=>2), 'Вы уже оставляли отзыв недавно', true);
			//jexit();
		}

		$uid = JFactory::getApplication()->input->getInt('id');
		$name = JFactory::getApplication()->input->getString('name');
		$msg = JFactory::getApplication()->input->getString('message');
		
		if(!$uid || !mb_strlen($name) || !mb_strlen($msg)){
			echo new JResponseJson(array('error_code'=>3), 'Не заполнены необходимые поля', true);
			jexit();
		}
		
		$rating = (float)JFactory::getApplication()->input->getString('rating');
		$user = JsnHelper::getUser($uid);
		$current_usr=JFactory::getUser();
		if(!$current_usr->get('id')){
			echo new JResponseJson(array('error_code'=>4), 'Вы должны быть авторизованы на сайте', true);
			jexit();
		}
		
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__easybook_gb WHERE id=".$db->quote($uid));
		if(!$db->loadResult() && $user->get('id')){
			$db->setQuery("INSERT INTO #__easybook_gb SET id=".$db->quote($uid).", title=".$db->quote($user->get('name')));
			$db->execute();
		}
				
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_easybookreloaded/tables', 'EasybookReloadedTable');
		$row = JTable::getInstance('entry', 'EasybookReloadedTable');
		$row->bind(array('gbname'=>$name, 'gbcomment'=>$msg, 'gbvote'=>$rating, 'gbid'=>$uid, 'gbtitle'=>$current_usr->get('username'),
			'gbip'=>$_SERVER['REMOTE_ADDR'], 'gbmail'=>$current_usr->get('email'), 'gbdate'=>JFactory::getDate()->toSql(),
			'gbtext'=>'#'.$current_usr->get('id').rand(10,99)));
		
		if(!$row->store()){
			echo new JResponseJson(array('error_code'=>5), 'Ошибка добавления коментария', true);
			jexit();
		}
		
		$session->set('review_sended', time());
		echo new JResponseJson(array('uid'=>$uid, 'username'=>$name, 'rating'=>$rating, 'msg'=>$msg));
		jexit();
	}
	
	public function save()
	{
		// Check for request forgeries.
		$this->checkToken();

		$app    = JFactory::getApplication();
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_users/models', 'ProfileModel');
		$model  = $this->getModel('Profile', 'UsersModel');
		$user   = JFactory::getUser();
		$userId = (int) $user->get('id');
		
		$requestData = $app->input->post->get('jform', array(), 'array');

		// Force the ID to this user.
		$requestData['id'] = $userId;
		
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}
		
		$objData = (object) $requestData;
		$app->triggerEvent(
			'onContentNormaliseRequestData',
			array('com_users.user', $objData, $form)
		);
		$requestData = (array) $objData;

		// Validate the posted data.
		$data = $model->validate($form, $requestData);

		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Unset the passwords.
			unset($requestData['password1'], $requestData['password2']);

			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $requestData);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=profile&layout=edit', false));

			return false;
		}

        $data['password2'] = $data['password1'];
		//var_dump($data);die();
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);
			$this->setMessage(JText::sprintf('Ошибка', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=profile&layout=edit', false));

			return false;
		}
		
		$this->setMessage(JText::_('Данные профиля сохранены'));
		$this->setRedirect(JRoute::_('index.php?option=com_jsn&view=profile', false));
		$app->setUserState('com_users.edit.profile.data', null);
	}
	
	public function save_order()
	{
		$msg = 'hello';
		$app = JFactory::getApplication();
		$user = JsnHelper::getUser();
		$name = $app->input->getString('name');
		$phone = $app->input->getString('telefon');
		$note = $app->input->getString('note');
		$master_id = $app->input->getInt('master_id');
		$tag_id = $app->input->getInt('tag_id');
		$svc_id = $app->input->getInt('svc_id');
		$remind = $app->input->getInt('remind');
		$price = $app->input->getString('price');
		$svc_name = $app->input->getString('svc_name');
		$time = $app->input->getString('time');
		$time = JFactory::getDate($time)->toSql();
		$time_sum = $app->input->getString('time_sum');
		$tm_for = strtotime($time);
		$tm_to = $tm_for + ($time_sum * 60);
		$time_to = date("Y-m-d H:i", $tm_to); 
		$db = JFactory::getDbo();
		$db->setQuery("SELECT COUNT(*) FROM #__jsn_orders WHERE master_id=".$db->quote($master_id)." AND time=".$db->quote($time)." AND time_to=".$db->quote($time_to)."");
		if($db->loadResult()){
			echo new JResponseJson(array('error_code'=>2), '<strong style:"color: red">Ошибка записи: в заданное время мастер занят</strong>', true);
			jexit();
		}

		$db->setQuery("INSERT INTO #__jsn_orders SET master_id=".$db->quote($master_id).", user_name=".$db->quote($name)
			.", telefon=".$db->quote($phone).", note=".$db->quote($note).", user_id=".$db->quote((int)$user->id)
			.", time=".$db->quote($time).", time_to=".$db->quote($time_to).", svc_id=".$db->quote($svc_id).", tag_id=".$db->quote($tag_id)
			.", svc_name=".$db->quote($svc_name).", price=".$db->quote($price).", remind=".$db->quote($remind));
		$db->execute();
		$oid = $db->insertid();
		
		$ms_user = JsnHelper::getUser($master_id);
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}
		
		if(!$this->sendNotifyMail($ms_user->email, $name, $time, $svc_name, $phone, $note)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id)
		  if(!$this->sendUserMail($user->email, $ms_user->name, $time, $svc_name, $ms_user->get('telefon'), $note)){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }

		echo new JResponseJson(array('oid'=>$oid), "Запись успешно добавлена");
		jexit();
	}
	// SAVE STOCKS
	public function save_stocks()
	{
		$msg = 'Новая запись на акционную услугу';
		$app = JFactory::getApplication();
		$user = JsnHelper::getUser();
		$name = $app->input->getString('name');
		$phone = $app->input->getString('telefon');
		$note = $app->input->getString('note');
		$master_id = $app->input->getInt('master_id');
		$tag_id = $app->input->getInt('tag_id');
		$svc_id = $app->input->getInt('svc_id');
		$remind = $app->input->getInt('remind');
		$stock_price = $app->input->getInt('s_price');
		$old_price = $app->input->getInt('o_price');
		$about_stock = $app->input->getString('a_stock');
		$svc_name = $app->input->getString('svc_name');
		$time = $app->input->getString('time');
		$time = JFactory::getDate($time)->toSql();
		$time_sum = $app->input->getString('time_sum');
		$tm_for = strtotime($time);
		$tm_to = $tm_for + ($time_sum * 60);
		$time_to = date("Y-m-d H:i", $tm_to); 
		$db = JFactory::getDbo();
		$db->setQuery("SELECT COUNT(*) FROM #__jsn_stocks WHERE master_id=".$db->quote($master_id)." AND time=".$db->quote($time)." AND time_to=".$db->quote($time_to)."");
		if($db->loadResult()){
			echo new JResponseJson(array('error_code'=>2), 'Ошибка записи: в заданное время мастер занят', true);
			jexit();
		}

		$db->setQuery("INSERT INTO #__jsn_stocks SET master_id=".$db->quote($master_id).", user_name=".$db->quote($name)
			.", telefon=".$db->quote($phone).", note=".$db->quote($note).", user_id=".$db->quote((int)$user->id)
			.", time=".$db->quote($time).",  time_to=".$db->quote($time_to).", svc_id=".$db->quote($svc_id).", tag_id=".$db->quote($tag_id)
			.", stocks_servis=".$db->quote($svc_name).", stock_price=".$db->quote($stock_price).", old_price=".$db->quote($old_price).", about_stock=".$db->quote($about_stock).", remind=".$db->quote($remind));
		$db->execute();
		$oid = $db->insertid();
		
		$ms_user = JsnHelper::getUser($master_id);
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}
		
		if(!$this->sendStockNotifyMail($ms_user->email, $name, $time, $svc_name, $phone, $note)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id)
		  if(!$this->sendStockUserMail($user->email, $ms_user->name, $time, $svc_name, $ms_user->get('telefon'), $note)){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }

		echo new JResponseJson(array('oid'=>$oid), "Новая запись на акционную услугу успешно добавлена!");
		jexit();
	}
	public function updateStock(){
		$msg = 'Изменена запись на акционную услугу';
		$app = JFactory::getApplication();
		$user_id = $app->input->getInt('user_id');
		$user = JsnHelper::getUser($user_id);
		$master_id = $app->input->getInt('master_id');
		$note = $app->input->getString('note');
		$tag_id = $app->input->getInt('tag_id');
		$svc_id = $app->input->getInt('svc_id');
		$svc_name = $app->input->getString('svc_name');
		$stock_id = $app->input->getInt('stock_id');
		$time = $app->input->getString('time');
		$time = JFactory::getDate($time)->toSql();
		$time_sum = $app->input->getString('time_sum');
		$tm_for = strtotime($time);
		$tm_to = $tm_for + $time_sum;
		$time_to = date("Y-m-d H:i", $tm_to);
		$phone = $app->input->getString('telefon');
		$name = $user->name;
		$db = JFactory::getDbo();
		/*$db->setQuery(
			"UPDATE #__jsn_stocks SET time=".$db->quote($time).", time_to=".$db->quote($time_to).", note=".$db->quote($note)."
			    WHERE master_id=".$db->quote($master_id)."
				AND user_id=".$db->quote($user_id)."
				AND stocks_servis=".$db->quote($svc_name)."
				AND tag_id=".$db->quote($tag_id)."LIMIT 1"
			);*/
		$db->setQuery(
            "UPDATE #__jsn_stocks SET time=".$db->quote($time).", time_to=".$db->quote($time_to).", note=".$db->quote($note)."
			    WHERE id=".$db->quote($stock_id)
        );
		$res = $db->execute();

		$oid = $db->insertid();
		$ms_user = JsnHelper::getUser($master_id);
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}
		
		if(!$this->replStockNotifyMail($ms_user->email, $name, $time, $svc_name, $phone, $note)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id)
		  if(!$this->replStockUserMail($user->email, $ms_user->name, $time, $svc_name, $ms_user->get('telefon'), $note)){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }
        if(($res != 0) && isset($oid)){
			 echo new JResponseJson(array('oid'=>$oid), "Акционная запись успешно изменена!");
		}else{
			 echo json_encode(array("message" => "Изменение акционной записи не удалось!"), JSON_UNESCAPED_UNICODE);
		}
		jexit();
	}
	public function delStock(){
		$msg = 'Запись на акционную услугу удалена';
		$app = JFactory::getApplication();
		$user_id = $app->input->getInt('user_id');
		$user = JsnHelper::getUser($user_id);
		$master_id = $app->input->getInt('master_id');
		$tag_id = $app->input->getInt('tag_id');
		$svc_name = $app->input->getString('svc_name');
		$time = $app->input->getString('time');
        $time = JFactory::getDate($time)->toSql();
		$phone = $app->input->getString('telefone');
		$name = $user->name;
		$db = JFactory::getDbo();
		$db->setQuery("DELETE FROM #__jsn_stocks WHERE time=".$db->quote($time). " AND tag_id=".$db->quote($tag_id)."AND master_id=".$db->quote($master_id)." LIMIT 1");
		$delStock = $db->execute();
		$oid = $db->insertid();
		$ms_user = JsnHelper::getUser($master_id);
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}	
		if(!$this->delStockNotifyMail($ms_user->email, $name, $time, $svc_name)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id)
		  if(!$this->delStockUserMail($user->email, $ms_user->name, $time, $svc_name)){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }

		if(!empty($delStock)){
			 echo new JResponseJson(array('oid'=>$oid), "Акционная запись удалена!");
		}else{
			 echo json_encode(array("message" => "Ошибка удаления акционной записи!"), JSON_UNESCAPED_UNICODE);
		}
		jexit();
	}
	// END SAVE STOCKS
	public function update(){
		$msg = 'Запись на услугу обновлена';
		$app = JFactory::getApplication();
		$user_id = $app->input->getInt('user_id');
		$user = JsnHelper::getUser($user_id);
		$master_id = $app->input->getInt('master_id');
		$note = $app->input->getString('note');
		$tag_id = $app->input->getInt('tag_id');
		$svc_id = $app->input->getInt('svc_id');
		$svc_name = $app->input->getString('svc_name');
		$time = $app->input->getString('time');
		$time = JFactory::getDate($time)->toSql();
		$time_sum = $app->input->getString('time_sum');
		$tm_for = strtotime($time);
		$tm_to = $tm_for + $time_sum;
		$time_to = date("Y-m-d H:i", $tm_to); 
		$phone = $app->input->getString('telefone');
		$name = $user->name;
		$db = JFactory::getDbo();
		$db->setQuery(
			"UPDATE #__jsn_orders SET time=".$db->quote($time).", time_to=".$db->quote($time_to).", note=".$db->quote($note)."
				WHERE master_id=".$db->quote($master_id)."
				AND user_id=".$db->quote($user->id)."
				AND svc_name=".$db->quote($svc_name)."
				AND tag_id=".$db->quote($tag_id)."LIMIT 1"
			);
		$res = $db->execute();
		$oid = $db->insertid();
		$ms_user = JsnHelper::getUser($master_id);
		
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}
		//print_r($master_id) or die;
		if(!$this->replNotifyMail($ms_user->email, $name, $time, $svc_name, $phone, $note)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id){
		  if(!$this->replUserMail($user->email, $ms_user->name, $time, $svc_name, $ms_user->get('telefon'), $note)){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }
        }
		if(!empty($res) && isset($oid)){
			 echo new JResponseJson(array('oid'=>$oid), "Запись успешно изменена!");
		}else{
			 echo json_encode(array("message" => "Изменение не удалось!"), JSON_UNESCAPED_UNICODE);
		}
		jexit();
		
	}
	public function del(){
		$msg = 'Запись на услугу удалена';
		$app = JFactory::getApplication();
		$user = JsnHelper::getUser();
		$master_id = $app->input->getInt('master_id');
		$tag_id = $app->input->getInt('tag_id');
		$svc_name = $app->input->getString('svc_name');
		$time = $app->input->getString('time');
        $time = JFactory::getDate($time)->toSql();
		$phone = $app->input->getString('telefone');
		$name = $user->name;
		$db = JFactory::getDbo();
		$db->setQuery("DELETE FROM #__jsn_orders WHERE time=".$db->quote($time). " AND tag_id=".$db->quote($tag_id)."AND master_id=".$db->quote($master_id)." LIMIT 1");
		$del = $db->execute();
		$oid = $db->insertid();
		$ms_user = JsnHelper::getUser($master_id);
		if(!$ms_user->id || empty($ms_user->email)){
			echo new JResponseJson(array('error_code'=>1), 'Ошибка - мастер не найден', true);
			jexit();
		}	
		if(!$this->delNotifyMail($ms_user->email, $name, $time, $svc_name, $phone)){
			echo new JResponseJson(array('error_code'=>3), 'Ошибка отправки e-mail', true);
			jexit();
		}
		
		if($user->id)
		  if(!$this->delUserMail($user->email, $ms_user->name, $time, $svc_name, $ms_user->get('telefon'))){
			echo new JResponseJson(array('error_code'=>4), 'Ошибка отправки e-mail', true);
			jexit();
		  }

		if(!empty($del)){
			 echo new JResponseJson(array('oid'=>$oid), "Запись Удалена!");
		}else{
			 echo json_encode(array("message" => "Ошибка удаления!"), JSON_UNESCAPED_UNICODE);
			 jexit();
		}
		
	}
}
