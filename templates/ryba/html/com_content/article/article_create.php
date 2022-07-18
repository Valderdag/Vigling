<?php


use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


define( '_JEXEC', 1 );
define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT']);
define( 'DS', DIRECTORY_SEPARATOR );
require_once (JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once (JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once (JPATH_ADMINISTRATOR .DS.'components'.DS.'com_content'.DS.'models'.DS.'article.php');

$app = JFactory::getApplication('site');

$img_arr['image_intro'] = '';
$img_arr['float_intro'] = '';
$img_arr['image_intro_alt'] = '';
$img_arr['image_intro_caption'] = '';
$img_arr['image_fulltext'] = '';
$img_arr['float_fulltext'] = '';
$img_arr['image_fulltext_alt'] = '';
$img_arr['image_fulltext_caption'] = '';
	 
$data['catid'] = $_POST['category'];;
$data['created_by'] = $_POST['user_id'];
$data['title'] = $_POST['name'];
$data['alias'] = translit($_POST['name']);
$data['introtext'] = '';
$data['fulltext'] = '';
$data['images'] = json_encode($img_arr);
$data['state'] = 0;
$data['urls'] = '{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}';
$data['attribs'] = '{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';
$data['metadata'] = '{"robots":"","author":"","rights":"","xreference":""}';
$data['language'] = '*';
$data['created'] = Date('Y-m-d H:i:s', time());
$data['metakey'] = '';
$data['access'] = 1;
$data['rules'] = array(
	'core.edit.delete' => array(),
	'core.edit.edit' => array(),
	'core.edit.state' => array(),
);


$article_model =  JModelLegacy::getInstance('Article','ContentModel');
$save = $article_model->save($data);

if ($save) {
	saveArticleFields($article_model->getItem()->id);
	echo '<div class="message"><h2>Услуга</h2><p>'. $_POST['name'] .' создана</p><p>После проверки модератором она будет доступна</p></div>';
}else{
	$err_msg = $article_model->getError();
	echo '<div class="message"><h2>Произошла ошибка</h2><p>' . $err_msg .'</p></div>';
};




function saveArticleFields($articleId){
	$db = JFactory::getDbo();
	$fields = [
		'68' => $_POST['name'],
		'69' => $_POST['specialisation'],
		'57' => $_POST['type'],
		'66' => $_POST['duration'],
		'67' => $_POST['pause'],
		'64' => $_POST['fix'],
		'70' => $_POST['cost'],
		'63' => $_POST['foto_service']
	];
	
	foreach ($_POST['metod'] as  $metod) {
		$tmp = explode("|", $metod);
		$query = 'INSERT INTO #__fields_values (field_id, item_id, value) VALUES ("'. $tmp[0] .'","'. $articleId .'","'. $tmp[1] .'")';
		$db->setQuery($query);
		$db->execute();
	};
	
	foreach ($fields as $key => $value)
	{
		$pos = strpos( $value, '|');
		if ($pos === false) {
			$var = $value;
		} else {
			$tmp = explode("|", $value);
			$var = $tmp[1];
		}
		$query = 'INSERT INTO #__fields_values (field_id, item_id, value) VALUES ("'. $key .'","'. $articleId .'","'. $var .'")';
		$db->setQuery($query);
		$db->execute();
	};

	
}




function translit($s) {
  $s = (string) $s;
  $s = strip_tags($s);
  $s = str_replace(array("\n", "\r"), " ", $s);
  $s = preg_replace("/\s+/", ' ', $s);
  $s = trim($s);
  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
  $s = preg_replace("/[^0-9a-z-_. ]/i", "", $s);
  $s = str_replace(" ", "-", $s);
  return $s;
}




?>
