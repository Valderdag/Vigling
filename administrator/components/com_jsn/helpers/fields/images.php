<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


global $_FIELDTYPES;
$_FIELDTYPES['images']='COM_JSN_FIELDTYPE_IMAGE';

class JsnImagesFieldHelper
{
	public static function create($alias)
	{
		$db = JFactory::getDbo();
		$query = "ALTER TABLE #__jsn_users ADD ".$db->quoteName($alias)." VARCHAR(255)";
		$db->setQuery($query);
		$db->query();
	}
	
	public static function delete($alias)
	{
		$db = JFactory::getDbo();
		$query = "ALTER TABLE #__jsn_users DROP COLUMN ".$db->quoteName($alias);
		$db->setQuery($query);
		$db->query();
	}
	
	public static function getXml($item)
	{
		require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
		$hideTitle= ($item->params->get('hidetitle',0) && JFactory::getApplication()->input->get('view','profile')=='profile' && JFactory::getApplication()->input->get('option','')=='com_jsn') || ($item->params->get('hidetitleedit',0) && (JFactory::getApplication()->input->get('layout','')=='edit' || JFactory::getApplication()->input->get('view','')=='registration'));
		if(JFactory::getApplication()->input->get('view','profile')=='profile' && JFactory::getApplication()->input->get('option','')=='com_jsn' && $item->params->get('titleprofile','')!='') $item->title=$item->params->get('titleprofile','');
		$defaultvalue=($item->params->get('images_defaultvalue','')!='' ? 'default="'.JsnHelper::xmlentities($item->params->get('images_defaultvalue','')).'"' : '');//(isset($item->params['images_defaultvalue']) && $item->params['images_defaultvalue']!='' ? 'default="'.$item->params['images_defaultvalue'].'"' : '');
		
		if($item->params->get('field_readonly','')==1 && JFactory::getApplication()->isSite()) $readonly='readonly="true"';
		elseif($item->params->get('field_readonly','')==2 && JFactory::getApplication()->input->get('view')!='registration' && JFactory::getApplication()->isSite()) $readonly='readonly="true"';
		else $readonly='';
//var_dump($item->alias);die();
		$xml='
			<field
				name="'.$item->alias.'"
				type="imagefull"
				id="'.$item->alias.'"
				imageclass="'.$item->alias.' '.$item->params->get('images_class','').'"
				class="'.$item->params->get('field_cssclass','').'"
				description="'.JsnHelper::xmlentities(($item->description)).'"
				accept="image/*"
				label="'.($hideTitle ? JsnHelper::xmlentities('<span class="no-title">'.JText::_($item->title).'</span>') : JsnHelper::xmlentities($item->title)).'"
				alt="'.$item->params->get('images_alt','').'"
				'.$defaultvalue.'
				'.$readonly.'
				width="'.$item->params->get('images_width','500').'"
				height="'.$item->params->get('images_height','500').'"
				width_thumb="'.$item->params->get('images_thumbwidth','100').'"
				height_thumb="'.$item->params->get('images_thumbheight','100').'"
				cropwebcam="'.$item->params->get('images_cropwebcam','0').'"
				required="'.($item->required && JFactory::getApplication()->input->get('jform',null,'array')==null ? ($item->required==2 ? 'admin' : 'frontend' ) : 'false' ).'"
				requiredfile="'.($item->required ? ($item->required==2 ? 'admin' : 'frontend' ) : 'false' ).'"
				validate="images"
			/>
		';
		return $xml;
	}
	
	public static function loadData($field, $user, &$data)
	{
		$alias=$field->alias;
		$path=$field->params->get('images_path','');
		if(!isset($user->$alias))
			return;

		$data->$alias=json_decode($user->$alias);
		if(is_array($data->$alias) && !empty($data->$alias)){			
			foreach($data->$alias as &$fn)
				$fn = $path.$fn;
		}
		else $data->$alias=array(); //'images/portfolio/default.png');
	}
	
	public static function storeData($field, $data, &$storeData)
	{
		$jsn_config = JComponentHelper::getParams('com_jsn');
		if($field->alias == 'avatar' && $jsn_config->get('avatar',1) == 2) // Gravatar
		{
			return;
		}

		//if($field->params->get('field_readonly','')==1 && JFactory::getApplication()->isSite()) return;
		//if($field->params->get('field_readonly','')==2 && JFactory::getApplication()->input->get('task')=='profile.save' && JFactory::getApplication()->isSite()) return;
		$upload_path=$field->params->get('images_path','images/profiler/');

		// Set Upload Dir
		$upload_dir=JPATH_SITE.'/'.$upload_path;
		if(!file_exists($upload_dir)) 
		{ 
			mkdir($upload_dir); 
		}

		// Get Alias
		$alias=$field->alias;
		if(isset($data[$alias])) $storeData[$alias]=$data[$alias];

		// Delete Image
		$jform=JFactory::getApplication()->input->post->get('jform', array(), 'ARRAY');
		//var_dump(($jform)); die();
		if(isset($jform['upload_'.$alias]))
			$names=$jform['upload_'.$alias];
		else return;
		
		$jformfiles=JFactory::getApplication()->input->files->get('jform',null,'raw');
		if(isset($jformfiles['upload_'.$alias]))
			$images=$jformfiles['upload_'.$alias];
		else return;
		
		
		//if(isset($storeData[$alias]) && is_array($storeData[$alias]) && !empty($storeData[$alias]))
		//	$old_names = $storeData[$alias];
		//$storeData[$alias] = array();
		
		/*{
			// Delete old file
			foreach ($storeData[$alias] as $file) //glob($upload_dir.$alias.$data['id'].'*') as $deletefile)
			{
				//unlink($deletefile);
			}
			
			//$storeData[$alias]='';
			return;
		}*/

		/* ------ METHOD INPUT ------ */


		if(file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/lib/class.upload.php'))
			require_once(JPATH_ADMINISTRATOR.'/components/com_k2/lib/class.upload.php');
		else require_once(JPATH_ADMINISTRATOR.'/components/com_jsn/assets/class.upload.php');
		$save_names = array();		
		jimport('joomla.filesystem.file');
		if(count($images)){
			foreach ($images as $k=>$image) { 
				if(!$image['name']){
					if($names[$k]!=''){
						$save_names[]=basename($names[$k]);
					}
					continue;
				}
				
				switch ($image['type']) {
					case 'image/gif':
					    $extension = '.gif';
					    break;
					case 'image/jpeg':
					    $extension = '.jpg';
					    break;
					case 'image/png':        
					    $extension = '.png';
					    break;
					default:
					    $extension = '.jpg';
					    break;
				}

				// Copy uploaded images
				$md5=md5(time().rand());
				$filename=$alias.$data['id'].'_'.$md5.$extension;
				$filename_mini=$alias.$data['id'].'mini_'.$md5.$extension;
				
				JFile::upload($image['tmp_name'], $upload_dir.$filename);
				JFile::copy($upload_dir.$filename, $upload_dir.$filename_mini);
				//JFile::upload($image['tmp_name'], $upload_dir.$filename_mini);
			    
			    $storeData[$alias][]=$filename;
			    $save_names[]=$filename;
			    // Resize Thumbs
			    $handle = new upload($upload_dir.$filename_mini);
			    $handle->file_new_name_body=$filename_mini;
			    //if($field->params->get('images_thumbwidth',100)>0) $handle->image_x = $field->params->get('images_thumbwidth',100);
			    //if($field->params->get('images_thumbheight',100)>0) $handle->image_y = $field->params->get('images_thumbheight',100);
			    $handle->image_resize = true;
			    $handle->image_ratio_crop = true;
			    $handle->process($upload_dir);
			    $handle->clean();
			}
		}
		//var_dump($save_names);die();
		if(is_array($save_names) && !empty($save_names))
			$storeData[$alias] = json_encode($save_names);
		else $storeData[$alias]='{}';

		/* Temp folder path to clean */
		$path = JPATH_SITE.'/images/_tmp/';
		
		$session = JFactory::getSession();
		/* Clean Tmp folder from not saved images */
		if($session->get('_tmp_rand_'.$alias,'')!='')
		{
			
			$rand=$session->get('_tmp_rand_'.$alias,'')!='';

			$filename_prefix=substr(md5($_SERVER['REMOTE_ADDR'].$alias.$rand),0,10);
			
			if (file_exists($path))
			{
				foreach (glob($path.$filename_prefix.'*') as $deletefile)
				{
					unlink($deletefile);
				}
			}
		}
	}
	
	public static function getSearchQuery($field, &$query)
	{
		$db=JFactory::getDbo();
		$query->where('b.'.$db->quoteName($field->alias).' LIKE '.$db->quote('_%'));
	}
	
	public static function image($field,$user)
	{
		$value=$field->__get('value');
		if (empty($value) && $field->name!='jform[avatar]' && $field->name!='avatar')
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			return $field->getImage($user);
		}
		
	}

	public static function deleteUser($field,$user){
		$upload_path=$field->params->get('images_path','images/profiler/');

		// Set Upload Dir
		$upload_dir=JPATH_SITE.'/'.$upload_path;

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');
		
		if($userId > 0) foreach (glob($upload_dir.$field->alias.$userId.'*') as $deletefile)
		{
				unlink($deletefile);
		}
	}
	
	public static function operations()
	{
		JFactory::getConfig()->set('gzip',false);
		$db=JFactory::getDbo();
		$query=$db->getQuery(true);
		$query->select('params')->from('#__jsn_fields')->where('alias='.$db->quote(JFactory::getApplication()->input->get('field')));
		$params = new JRegistry;
		$db->setQuery($query);
		$params->loadString($db->loadResult());
		$name=JFactory::getApplication()->input->get('field');

		$session = JFactory::getSession();
		

		require_once(JPATH_ADMINISTRATOR.'/components/com_jsn/assets/ImgPicker.php');

		if($session->get('_tmp_rand_'.$name,'')=='') $session->set('_tmp_rand_'.$name,md5(time().rand()));

		$rand=$session->get('_tmp_rand_'.$name,'');

		$filename=substr(md5($_SERVER['REMOTE_ADDR'].$name.$rand),0,10);

		$upload_tmp  = JPATH_SITE.'/images/_tmp/';
		if (!file_exists($upload_tmp)) {
		    mkdir($upload_tmp);
		}

		$options = array(

			// Upload directory path
			'upload_dir' => $upload_tmp,

			// Upload directory url:
			//'upload_url' => 'http://localhost/imgPicker/files/',
			'upload_url' => JURI::root(true) . '/images/_tmp/',

			// Accepted file types:
			'accept_file_types' => 'png|jpg|jpeg|gif',
			
			// Directory mode:
			'mkdir_mode' => 0777,
			
			// File size restrictions (in bytes):
			'max_file_size' => null,
		    'min_file_size' => 1,
		    
		    // Image resolution restrictions (in px):
		    'max_width'  => null,
		    'max_height' => null,
		    'min_width'  => 1,
		    'min_height' => 1,

		    // Image versions:
		    'versions' => array(
		    	// This will create 2 image versions: the original one and a 200x200 one
		    	'mini' => array(
		    		//'upload_dir' => '',
		    		//'upload_url' => '',
		    		// Create square image
		    		//'crop' => true,
		    		'max_width' => $params->get('images_thumbwidth',100), 
		    		'max_height' => $params->get('images_thumbheight',100)
		    	),
		    	'big' => array(
		    		//'upload_dir' => '',
		    		//'upload_url' => '',
		    		// Create square imag
		    		//'crop' => true,
		    		'max_width' => $params->get('images_width',500), 
		    		'max_height' => $params->get('images_height',500)
		    	),
		    ),

		    /**
			 * 	Load callback
			 *
			 *  @param 	ImgPicker 		$instance
			 *  @return string|array
			 */
		    'load' => function($instance) {
		    	//return 'avatar.jpg';
		    },

		    /**
			 * 	Delete callback
			 *
			 *  @param  string 		    $filename
			 *  @param 	ImgPicker 		$instance
			 *  @return boolean
			 */
		    'delete' => function($filename, $instance) {
		    	return true;
		    },
			
			/**
			 * 	Upload start callback
			 *
			 *  @param 	stdClass 		$image
			 *  @param 	ImgPicker 		$instance
			 *  @return void
			 */
			'upload_start' => function($image, $instance) {
				$session = JFactory::getSession();
				$rand=$session->get('_tmp_rand_'.JFactory::getApplication()->input->get('field'));
				$filename=substr(md5($_SERVER['REMOTE_ADDR'].JFactory::getApplication()->input->get('field').$rand),0,10);
				$image->name = $filename . '.' . $image->type;		
			},
			
			/**
			 * 	Upload complete callback
			 *
			 *  @param 	stdClass 		$image
			 *  @param 	ImgPicker 		$instance
			 *  @return void
			 */
			'upload_complete' => function($image, $instance) {
			},

			/**
			 * 	Crop start callback
			 *
			 *  @param 	stdClass 		$image
			 *  @param 	ImgPicker 		$instance
			 *  @return void
			 */
			'crop_start' => function($image, $instance) {
				$session = JFactory::getSession();
				$rand=$session->get('_tmp_rand_'.JFactory::getApplication()->input->get('field'));
				$filename=substr(md5($_SERVER['REMOTE_ADDR'].JFactory::getApplication()->input->get('field').$rand),0,10);
				$image->name = $filename . '.' . $image->type;
				$session->set('_tmp_img_'.JFactory::getApplication()->input->get('field'),$filename . '.' . $image->type);
			},

			/**
			 * 	Crop complete callback
			 *
			 *  @param 	stdClass 		$image
			 *  @param 	ImgPicker 		$instance
			 *  @return void
			 */
			'crop_complete' => function($image, $instance) {
				
			}
		);

		// Create new ImgPicker instance
		//new ImgPicker($options);

	}

}
