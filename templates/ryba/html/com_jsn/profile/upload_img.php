<?php
define( 'DS', DIRECTORY_SEPARATOR );
if(isset($_POST['user'])){
	$html = '';
	$uploaddir = $_SERVER[DOCUMENT_ROOT] . DS .'images'. DS . 'gallery' . DS . $_POST['user']; 
	if(!is_dir($uploaddir)) {
		mkdir($uploaddir, 0777, true);
	};
	$files      = $_FILES; 
	$done_files = array();
	$error		= false;
	foreach($files as $key => $file){
		$mime = $file['type'];
		$size = $file['size'];
		$image = getimagesize($file['tmp_name']);
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if ((strpos($mime, 'image') === false) || ($size > 512000) && (($ext == 'jpg') || ($ext == 'png')) || ($image[1] > $image[0])){  
			//	пропустим если файл > 500Мб
			//	если не картинка
			//	если не PNG или не JPG
			//	если портрет
			$error = true;
			continue;
		};
		$file_name = translit($file['name']);
		$file_name = strtolower($file_name);
		$file_name = str_replace('jpeg', 'jpg', $file_name);
		if( move_uploaded_file( $file['tmp_name'], "$uploaddir/$file_name" ) ){
			$done_files[] = realpath( "$uploaddir/$file_name" );
		}	
	};
	
 
	$files = array();
	foreach(glob($uploaddir . '/*.{jpg,png,jpeg,JPG,PNG,JPEG}', GLOB_BRACE) as $file) {
		$html .='<div class="gal_img">';
		$html .='<a class="del_img" data-fancybox data-src="#del-images" href="javascript:;" data-url="/images/gallery/' . $_POST['user'] . '/' . basename($file) . '"><i class="fa fa-trash" aria-hidden="true"></i></a>';
		$html .='<a href="/images/gallery/' . $_POST['user'] . '/' . basename($file) . '" data-fancybox="gal">';
		$html .='<img src="/images/gallery/' . $_POST['user'] . '/' . basename($file) . '" alt="">';
		$html .='</a></div>';	
	
	
	}; 
	$data = array('html' => $html,'error' =>  $error);
	echo (json_encode($data));

	
};	

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





