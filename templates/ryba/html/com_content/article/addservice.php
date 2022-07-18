<?php
define( 'DS', DIRECTORY_SEPARATOR );
JHtml::_('formbehavior.chosen', 'select');
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$userID = $user->id;
$empty = false;
$uploaddir =  $_SERVER[DOCUMENT_ROOT] . DS .'images'. DS . 'gallery' . DS . $userID;
require_once ($_SERVER[DOCUMENT_ROOT] . DS .'function.php');
 
if ($userID) {
	$query = $db->getQuery(true);
	$query->select($db->quoteName('value'));
	$query->from($db->quoteName('#__fields_values'));
	$query->where($db->quoteName('field_id')  . " = " . $db->quote('29'), 'AND');
	$query->where($db->quoteName('item_id')  . " = " . $db->quote($userID));
	$db->setQuery($query);
	$spec = $db->loadColumn();

	$query = $db->getQuery(true);
	$query->select($db->quoteName(array('sity', 'area', 'street', 'doorway', 'about', 'house_number')));
	$query->from($db->quoteName('#__jsn_users'));
	$query->where($db->quoteName('id')  . " = " . $db->quote($userID));
	$db->setQuery($query);
	$info = $db->loadRow();

	foreach ($info as $key => $value) {
		if (empty($value)) { //проверяем если пустой
			$empty = true;
			break;
		};
	};

	$query = $db->getQuery(true);
	$query->select($db->quoteName(array('id','title')));
	$query->from($db->quoteName('#__categories'));
	$query->where($db->quoteName('id')  . " IN (" .implode(',',$spec).")", "AND");
	$query->where($db->quoteName('published')  . " = " . $db->quote('1'));
	$db->setQuery($query);
	$category = $db->loadRowList();
};


if (!empty($_GET['s_id'])) {
	$task = 'edit';
	$query = $db->getQuery(true);
	$query = 'SELECT co.title,ca.parent_id as cat,co.catid as subcat FROM #__categories as ca,#__content as co WHERE co.id = 68 AND co.catid = ca.id';
	$db->setQuery($query);
	$info = $db->loadAssoc();
	$query = $db->getQuery(true);
	$query = 'SELECT title FROM #__categories WHERE #__categories.id =' . $info[cat];
	$db->setQuery($query);
	$name = $db->loadResult();
	
	
	print_r($name);
	
	
	
}






?>




<script>
jQuery(document).ready(function() {
	<?php if ($task != 'edit') {?>
		jQuery(".spec_1").chosen().on('change', function () {
			let param = jQuery(this).val();	
			if (param !="0") {
				goPreloader('start');	
				jQuery.ajax({ 
					url: '/function.php',
					data: {action: 'getCategory', param: param},
					type: 'post',
					success: function(output) {
						jQuery("#spec_2 .sel").html(output);
						jQuery("#spec_2").addClass('d-block');
						jQuery('.spec_2').chosen();
						goPreloader('stop');
					}
				});
			} else {
				jQuery("#spec_2 .sel").html('');
				jQuery("#spec_2").removeClass('d-block');
				jQuery("#spec_3 .sel").html('');
				jQuery("#spec_3").removeClass('d-block');
			}
		});	
		jQuery("#spec_2").on('change', jQuery(".spec_2").chosen(),  function () {
			let param = jQuery(".spec_2").val();	
			if (param !="0") { 
				goPreloader('start');
				jQuery.ajax({ 
					url: '/function.php',
					data: {action: 'getMetod', param: param},
					type: 'post',
					success: function(output) {
						jQuery("#spec_3 .sel").html(output);
						jQuery("#spec_3").addClass('d-block');
						jQuery('.spec_3').chosen();
						goPreloader('stop');
					}
				});
			} else {
				jQuery("#spec_3 .sel").html('');
				jQuery("#spec_3").removeClass('d-block');
			}
		});  
	<?php } else {?>
		Query('input[name="name"]').val(<?php echo $info['cat_name'];?>)
		jQuery.ajax({ 
			url: '/function.php',
			data: {action: 'getCategory', param: <?php echo info['cat'];?>},
			type: 'post',
			success: function(output) {
				jQuery("#spec_2 .sel").html(output);
				jQuery("#spec_2").addClass('d-block');
				jQuery('.spec_2').chosen();
			}
		});
	
	
	
	
	<?php } ?>

		
    jQuery('.time1').timepicker({
        'show2400': true,
		'minTime': '00:15',
		'maxTime': '04:00',
		'timeFormat': 'H:i',
		'step':'15'
    });
	jQuery('.time2').timepicker({
        'show2400': true,
		'minTime': '00:00',
		'maxTime': '01:00',
		'timeFormat': 'H:i',
		'step':'5'
    });
	
	
	jQuery(".s_img .thumbnail").on('click',  function () {
		jQuery(".s_img .thumbnail").removeClass("select");
		jQuery(this).addClass("select");
		jQuery("input[name=foto_service]").val(jQuery(this).find("img").attr('src'));
	});
	
	
	jQuery('#create_service').on('submit', function(e){
		let err = false;
		e.preventDefault();
		jQuery("#create_service select:not('.spec_3')").each(function (index, el){
			let n  = jQuery(el).val();
			if (n == 0) {
				err = true;
				jQuery(el).closest('.sel').find(".chzn-container").append('<i class="fa fa-exclamation-circle choseninfo" aria-hidden="true"></i>');
			} else {
				jQuery(el).closest('.sel').find(".choseninfo").remove();
			}
		});
		if (!err) {	
			let $data = jQuery(this).serialize();
			jQuery.ajax({ 
				url: '/templates/ryba/html/com_content/article/article_create.php',
				data: $data,
				type: 'POST',
				success: function(output) {
					jQuery.fancybox.open(output);
					jQuery(this).trigger("reset");
					sendmoderatormail('create',<?php echo $userID;?>);
				}
			});
		};	
	});		
});	
</script>
<?php if ($userID) {?>
	<?php if ((!empty($spec)) && (!$empty)) {?>
		
		<?php if (!empty($task)) {  // создание материала   ?>	
		<div class="container">
			<form action="" id="create_service">
				<div class="row">
					<div class="col-12" id="spec_0">
						<p>Название услуги</p>
						<input class="name validate" name="name" type="text" placeholder="Наименование" required>
					</div>
					<div class="col-12" id="spec_1">
						<p>Специализация</p>
						<div class='sel'>
							<select class="spec_1" name="specialisation" data-placeholder="Выбрать...">
								<option value="0"></option>
								<?php foreach ($category as $key => $item) { ?>
								<option value="<?php echo $item[0]; ?>"><?php echo $item[1]; ?></option>	
								<?php } ?>
							</select>
						</div>	
					</div>
					<div class="col-12 d-none" id="spec_2">
						<p>Катеория</p>
						<div class='sel'></div>
					</div>
					<div class="col-12 d-none" id="spec_3">
						<p>Метод</p>
						<div class='sel'></div>
					</div>
					<div class="col-12" id="spec_4">
						<p>Тип мастера</p>
						<div class='sel'><?php echo getTypeMaster(); ?></div>
					</div>
					<div class="col-12" id="spec_5">
						<p>Продолжительность</p>
						<input class="time1 validate" name="duration" placeholder="00:00" required>
					</div>
					<div class="col-12" id="spec_6">
						<p>Перерыв после записи</p>
						<input class="time2 validate" name="pause" placeholder="00:00" required>
					</div>
					<div class="col-12" id="spec_7">
						<p>Стоимость</p>
						<div class="row">
							<div class="col-6">
								<div class='sel'>
									<select name="fix" class="validate" data-placeholder="Выбрать тип цены...">
										<option value="0"></option>
										<option value="1">Фиксированная</option>
										<option value="2">Начальная</option>
									</select>
								</div>	
								<input class="cost validate" name="cost" type="number" min="0" step="100" placeholder="1 000" required>
							</div>
						</div>	
					</div>
					<div class="col-12" id="spec_8">
						<p>Фото услуги</p>
						<ul class="d-flex s_img">
						<?php
						$files = array();
						foreach(glob($uploaddir . '/*.{jpg,png,jpeg,JPG,PNG,JPEG}', GLOB_BRACE) as $n => $file) {
							if ($n == 0) {$t = ' select';$url = "/images/gallery/" . $userID . '/' . basename($file); } else {$t='';} ;
							$html .='<li class="thumbnail'. $t .'">';
							$html .='<img src="/images/gallery/'. $userID . '/' . basename($file) .'" alt=""  />';
							$html .='</li>';
						}; 
						echo $html;
						?>
						</ul>
						<input type="hidden" name="foto_service" value="<?php echo $url; ?>">
						<input type="hidden" name="user_id" value="<?php echo $userID; ?>">
					</div>
					<button type="submit" class="btn btn-xs btn-default">Добавить</button>
				</div>
			</form>
		</div>
		<?php } else {  // редактирование материала   ?>
		<div class="container">
			<form action="" id="create_service">
				<div class="row">
					<div class="col-12" id="spec_0">
						<p>Название услуги</p>
						<input class="name validate" value="<?php echo $name; ?>" name="name" type="text" placeholder="Наименование" required>
					</div>
					<div class="col-12" id="spec_1">
						<p>Специализация</p>
						<div class='sel'>
							<select class="spec_1" name="specialisation" data-placeholder="Выбрать...">
								<option value="0"></option>
								<?php foreach ($category as $key => $item) { ?>
								<option value="<?php echo $item[0]; ?>"><?php echo $item[1]; ?></option>	
								<?php } ?>
							</select>
						</div>	
					</div>
					<div class="col-12 d-none" id="spec_2">
						<p>Катеория</p>
						<div class='sel'></div>
					</div>
					<div class="col-12 d-none" id="spec_3">
						<p>Метод</p>
						<div class='sel'></div>
					</div>
					<div class="col-12" id="spec_4">
						<p>Тип мастера</p>
						<div class='sel'><?php echo getTypeMaster(); ?></div>
					</div>
					<div class="col-12" id="spec_5">
						<p>Продолжительность</p>
						<input class="time1 validate" name="duration" placeholder="00:00" required>
					</div>
					<div class="col-12" id="spec_6">
						<p>Перерыв после записи</p>
						<input class="time2 validate" name="pause" placeholder="00:00" required>
					</div>
					<div class="col-12" id="spec_7">
						<p>Стоимость</p>
						<div class="row">
							<div class="col-6">
								<div class='sel'>
									<select name="fix" class="validate" data-placeholder="Выбрать тип цены...">
										<option value="0"></option>
										<option value="1">Фиксированная</option>
										<option value="2">Начальная</option>
									</select>
								</div>	
								<input class="cost validate" name="cost" type="number" min="0" step="100" placeholder="1 000" required>
							</div>
						</div>	
					</div>
					<div class="col-12" id="spec_8">
						<p>Фото услуги</p>
						<ul class="d-flex s_img">
						<?php
						$files = array();
						foreach(glob($uploaddir . '/*.{jpg,png,jpeg,JPG,PNG,JPEG}', GLOB_BRACE) as $n => $file) {
							if ($n == 0) {$t = ' select';$url = "/images/gallery/" . $userID . '/' . basename($file); } else {$t='';} ;
							$html .='<li class="thumbnail'. $t .'">';
							$html .='<img src="/images/gallery/'. $userID . '/' . basename($file) .'" alt=""  />';
							$html .='</li>';
						}; 
						echo $html;
						?>
						</ul>
						<input type="hidden" name="foto_service" value="<?php echo $url; ?>">
						<input type="hidden" name="user_id" value="<?php echo $userID; ?>">
					</div>
					<button type="submit" class="btn btn-xs btn-default">Добавить</button>
				</div>
			</form>
		</div>
		<?php } ?>
	<?php } else { ?>
		<div class="container">
			<div class="row">
				<div class="col">
					<p>Ваш профиль заполнен не полностью. <a href="<?php echo JRoute::_('index.php?option=com_users&view=profile&layout=edit#profile-tab1',false);?>">Перейдите в кабинет и заполните Ваш профиль.</a></p>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } else { ?>	
	<div class="container">
		<div class="row">
			<div class="col">
				<p>Сессия истекла. Необходимо <a href="<?php echo JRoute::_('index.php?option=com_users&view=login',false);?>">авторизоваться</a></p>
			</div>
		</div>
	</div>
<?php } ?>	
	
	

	
	