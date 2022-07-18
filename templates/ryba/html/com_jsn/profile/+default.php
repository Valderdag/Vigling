<?php defined('_JEXEC') or die;
JLoader::register('JTplHelper', JPATH_SITE . '/templates/ryba/html/helper.php');
$this->document->setTitle($this->document->title.' - '.JsnHelper::getFormatName($this->data));
// Set Pathway
//JFactory::getApplication()->getPathway()->addItem(JsnHelper::getFormatName($this->data));
// Load Events Dispatcher
$dispatcher	= JEventDispatcher::getInstance();
$this->user=JsnHelper::getUser($this->data->id);
$this->current_user=JsnHelper::getUser();
$avatar=$this->form->getField('avatar');
$field = $this->user->getField('portfolio_field');
//var_dump();die();
$address = array();
$vyberite_spetsialnos = (array)$this->user->getField('vyberite_spetsialnos');
foreach($vyberite_spetsialnos as $svc_name);
if(!empty($svc_name)){
	$about = $svc_name;
}
$fields=$this->params->def('list_fields', array('about', 'sity', 'area', 'street', 'house_number', 'work_day', 'work_from', 'work_to'));
if(is_array($fields)){
	foreach($fields as $field){
		if(in_array($field, array('sity', 'area', 'street', 'house_number')) && $this->user->getValue($field))
			$address[] = $this->user->getValue($field);
		
		if($field=='about' && $this->user->getValue($field))
			$about = $this->user->getValue($field);
	}
}
$work_day = (array)$this->user->getValue('work_day');
$work_from = $this->user->getValue('work_from') ? $this->user->getValue('work_from') : '[0]';
$work_to = $this->user->getValue('work_to') ? $this->user->getValue('work_to') : '[0]';
$is_master = $this->user->getValue('is_master') ? 1 : 0;

$work_from = json_decode(($work_from[0]=='[') ? $work_from : '['.$work_from.']');
if(count($work_from)==1)
	$work_from = array_fill(0, 7, $work_from[0]);
elseif(count($work_from)==count($work_day)){
	$work_from = array_replace(array_fill(0, 8, 0), array_combine($work_day, $work_from));
	array_shift($work_from);
}

$work_to = json_decode(($work_to[0]=='[') ? $work_to : '['.$work_to.']');
if(count($work_to)==1)
	$work_to = array_fill(0, 7, $work_to[0]);
elseif(count($work_to)==count($work_day)){
	$work_to = array_replace(array_fill(0, 8, 0), array_combine($work_day, $work_to));
	array_shift($work_to);
}

foreach($work_day as $wd)
	foreach(range(ceil($work_from[$wd-1]), floor($work_to[$wd-1])) as $hour)
		$this->data->work[$wd][] = $hour;

	$stars = number_format(rand(8, 10)/2, 1, '.', '');

	function declOfNum($num, $sep = '<br/> ', $titles = array('фотография', 'фотографии', 'фотографий')) {
		$cases = array(2, 0, 1, 1, 1, 2);
		return $num.$sep.$titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
	}
	$portfolio = (array)$this->user->getField('portfolio_field');
//var_dump($portfolio);
	if(empty($portfolio) || !$portfolio[0])
	$portfolio[0] = $this->user->getValue('avatar');

if($this->current_user->id==$this->user->id){
	$this->task = JFactory::getApplication()->input->getCmd('task', 'view');
	if($this->task=='view'){
		echo $this->loadTemplate(((int)$this->data->get('is_master')) ? 'master' : 'client');
		return;
	}
}

$db = JFactory::getDbo();
$orders = array();
if($this->user->id && $this->current_user->id){
	$db->setQuery("SELECT CONCAT(svc_id, '-',tag_id) FROM #__jsn_orders WHERE time>NOW() AND user_id=".$db->quote($this->current_user->id)." AND master_id=".$db->quote($this->user->id));
	$orders = $db->loadColumn();
}
$this->reserved_time = array();
if($this->user->id){
	$db->setQuery("SELECT CONCAT(DATE(time), ' ', TIME(time)) FROM #__jsn_orders WHERE time>NOW() AND master_id=".$db->quote($this->user->id));
	$this->reserved_time = $db->loadColumn();
}
$this->reserved_time_to = array();
if($this->user->id){
	$db->setQuery("SELECT CONCAT(DATE(time_to), ' ', TIME(time_to)) FROM #__jsn_orders WHERE time>NOW() AND master_id=".$db->quote($this->user->id));
	$this->reserved_time_to = $db->loadColumn();
}
//STOCKS
$db = JFactory::getDbo();
$stocks = array();
if($this->user->id && $this->current_user->id){
	$db->setQuery("SELECT CONCAT(svc_id, '-',tag_id) FROM #__jsn_stocks WHERE time>NOW() AND user_id=".$db->quote($this->current_user->id)." AND master_id=".$db->quote($this->user->id)); // 
	$stocks = $db->loadColumn();
}
$this->stock_time = array();
if($this->user->id){
	$db->setQuery("SELECT CONCAT(DATE(time), ' ', TIME(time)) FROM #__jsn_stocks WHERE time>NOW() AND master_id=".$db->quote($this->user->id));
	$this->stock_time = $db->loadColumn();
}
$this->stock_time_to = array();
if($this->user->id){
	$db->setQuery("SELECT CONCAT(DATE(time_to), ' ', TIME(time_to)) FROM #__jsn_stocks WHERE time>NOW() AND master_id=".$db->quote($this->user->id));
	$this->stock_time_to = $db->loadColumn();
}
//END STOCKS
$doc = JFactory::getDocument();
$doc->addScript('https://api-maps.yandex.ru/2.1/?lang=ru-RU&amp;apikey=705d45a1-9138-4d99-afd4-dc261c612036');
$city = $this->user->getValue('sity');

$favorite = '';
if($this->current_user->get('favorites') && is_array($this->current_user->get('favorites')))
	if(in_array((string)$this->user->id, $this->current_user->favorites))
		$favorite =' class="active"';?>
	<div class="masters__big-img-cont col-md-6">
		<div class="arrows_master-slider">
			<button type="button" class="my-slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
			<button type="button" class="my-slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
		</div>
		<div class="masters__big-img">
			<?php foreach($portfolio as $img): ?>
				<div style="background-image: url('<?php echo $img?>')" class="masters__big-img-item"></div>
			<?php endforeach;?>
		</div>
	</div>
	<div class="masters__big-info col-md-6">
		<div class="masters__big-info-head">
			<div class="masters__big-info-head-master" style="background-image: url('<?php echo $this->user->getValue('avatar_mini')?>'); background-size: cover;">
				<span class="masters__big-info-head-master-online<?php echo $this->user->isOnline() ? '' : ' gray2'; ?>"></span>
			</div>
			<h3 class="h3biginfo"><?php echo $this->user->getField('formatname'); ?></h3>
			<a id="bookmarkme"<?php echo $favorite?> href="#" data-id="<?php echo $this->data->id?>" title="Добавить в избранное"></a>
			<div class="clearFloat"></div>
		</div>
		<div class="masters__big-info-attr">
			<h3 class="h3biginfo1"><?php echo $this->user->getField('formatname'); ?></h3>
			<div class="masters__attr-left">
				<span class="attr_left1"><?php echo $about?></span>
				<span class="attr_left2"><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo $addr = implode(', ', $address)?></span>
				<?php if($this->user->getValue('home')):?>
					<span class="attr_left3">Форма работы: <b><?php echo $this->user->getField('home', false) ?></b></span>
				<?php endif;?>
			</div>
			<div class="masters__attr-right">
				<span class="attr-rating" ><?= $stars?></span>
				<div class="attr-div-rating"  >
					<ul class="category_cinfo-ratings" style="display:none">
						<li><i class="fa fa-star" aria-hidden="true"></i></li>
						<li><i class="fa fa-star" aria-hidden="true"></i></li>
						<li><i class="fa fa-star" aria-hidden="true"></i></li>
						<li><i class="fa fa-star" aria-hidden="true"></i></li>
						<li><i class="fa fa-star-half" aria-hidden="true"></i></li>
					</ul>
					<span style="display:none"><?php //echo declOfNum($reviews, ' ', array('отзыв','отзыва','отзывов'))?></span>
				</div>
				<div class="clearFloat"></div>
			</div>
			<div class="clearFloat"></div>
		</div>
        <?php if ($is_master) { ?>
		<div class="masters__gall-small">
			<span class="masters__gall-small-count"><i>Еще <?= declOfNum(count($portfolio)-1)?></i></span>
			<div class="masters__small-img">
				<?php foreach($portfolio as $img): ?>
					<div style="background-image: url('<?= $img?>')" class="masters__small-img-item"></div>
				<?php endforeach;?>
			</div>
			<div class="clearFloat"></div>
		</div>
        <?php } ?>
	</div>
	<div class="clearFloat"></div>
</div></div></div></div>
</section>
<section class="master__services">
	<div class="container">
		<span class="req__info">Выберите услуги, нажав на кнопку +</span>
		<div class="accordionWrapper">
			<?php $prices = $this->user->getValue('prices');
			$prices = preg_replace('/(\w+):/i', '"\1":', $prices);
			$prices = (array)json_decode($prices);
			$db = JFactory::getDbo();
			$db->setQuery("SELECT id, title FROM #__tags");
			$tags = $db->loadObjectList('id');
			$tags = array_column($tags, 'title', 'id');
			$db->setQuery("SELECT id, title FROM #__categories");
			$categories = $db->loadObjectList('title');
			$categories = array_column($categories, 'title');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
			$model = JModelLegacy::getInstance('Article', 'ContentModel');
			JError::setErrorHandling(E_ERROR, "ignore");
			
//$add_class = 'opened';
    		if(!empty($prices))
				foreach($prices as $id=>$prices2){
					if(!empty($prices2)){
						  $article = $model->getItem($id); //print_r($article);
						   if($article instanceof JException)
						  	continue; 
						  echo '<div class="accordionItem ">';
						  echo '<h2 class="accordionItemHeading">'.$article->category_title.' </h2>';
						  echo '<div class="accordionItemContent"><div class="priceList">';
						foreach($prices2 as $idx=>$price){
							$tag_id = (int)$price[2];
							$name = $article->title; //. ' '.print_r($price, 1);
							if(array_key_exists($tag_id, $tags))
								$name .= ' /'.$tags[$tag_id];
							if(in_array($id.'-'.(int)$tag_id, $orders))
								$addClass = 'btn__am-yellow fa fa-check';
							else $addClass = 'plus';
							echo '<div class="priceList__item" data-svc-id="'.$id.'" data-tag-id="'.(int)$tag_id.'">
							<div class="priceList__item-coll price__coll1">'.$name.'</div>
							<div class="priceList__item-coll price__coll2">от '.$price[0].' руб.</div>
							<div class="priceList__item-coll price__coll3">'.(int)$price[1].' мин</div>
							<button data-toggle="modal" id="btn_order" data-srv-time="'.$price[1].'" data-target="#zapis" class="btn_add-master '.$addClass.'"></button>
							<div class="clearFloat"></div>
							</div>';
						}
						echo '</div></div>';
						//var_dump($prices2);
						//$add_class='closed';
					}
				}
			
				JError::setErrorHandling(E_ERROR, 'callback');
				?>
			</div>
		</div>
		<!------------------------------------------------------STOCKS-------------------------------------------------------------->
		<?php
		$stock_prices = $this->user->getValue('stock_prices');
		if(!empty($stock_prices)): ?>
			<div class="container">
				<h4><span class="req__info">Выберите акционную услугу, нажав на кнопку +</span></h4>
				<div class="accordionWrapper">
					<?php
					$stock_prices = preg_replace('/(\w+):/i', '"\1":', $stock_prices);
					$stock_prices = (array)json_decode($stock_prices);
					$db = JFactory::getDbo();
					$db->setQuery("SELECT id, title FROM #__tags");
					$tags = $db->loadObjectList('id');
					$tags = array_column($tags, 'title', 'id');
					JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
					$model = JModelLegacy::getInstance('Article', 'ContentModel');
					JError::setErrorHandling(E_ERROR, "ignore");
					$add_class = 'opened';
					if(!empty($stock_prices))
						$counter=0;
					foreach($stock_prices as $id=>$stock_prices2){
						$counter++;
						if(!empty($stock_prices2)){
							$article = $model->getItem($id);
							if($article instanceof JException)
								continue;
							echo '<div style="background-color:#f7cc53; border-radius: 10px; padding: 5px; margin-bottom: 5px" class="accordionItem '.$add_class.'"><button style="color:green; font-weight:bold; background-color:#fffff; border-radius: 5px">Акция</button>';
							echo '<h2 class="accordionItemHeading'.$counter.'">'.$article->category_title.'</h2>';
							echo '<div class="accordionItemContent'.$counter.'"><div class="stockList">';
							foreach($stock_prices2 as $idx=>$stock_price){
								$tag_id = (int)$stock_price[2];
								$name = $article->title;
								$s_price = $stock_price[0];
								$o_price = $stock_price[3];
								$tm = (int)$stock_price[1];
								$a_stock = $stock_price[4];
								if(array_key_exists($tag_id, $tags))
									$name .= ' /'.$tags[$tag_id];
								if(in_array($id.'-'.(int)$tag_id, $stocks))
									$addClass = 'btn__am-green fa fa-check';
								else $addClass = 'plus-stock';
								echo '<div class="stockList__item" data-svc-id="'.$id.'" data-tag-id="'.(int)$tag_id.'">
								<div class="stockList__item-coll stock__coll1">'.$name.'</div>
								<div class="stockList__item-coll stock__coll2">Описание: '.$a_stock.'</div>
								<div class="stockList__item-coll stock__coll3">от: '.$o_price.' руб.</div>
								<div class="stockList__item-coll stock__coll4">'.$s_price.' руб.</div>
								<div class="stockList__item-coll stock__coll5">'.$tm.' мин</div>
								<button data-toggle="modal" data-srv-time="'.$stock_price[1].'"data-target="#stocks" class="btn_add-stock '.$addClass.'"></button>
								<div class="clearFloat"></div>
								</div>';
							}
							echo '</div></div></div>';
							$add_class='closed';
						}
					}
					JError::setErrorHandling(E_ERROR, 'callback'); 'stockList__item';
					?>
				</div>
			</div>
		<?php endif; ?>
		<!--END STOCKS-->
	</section>
	<section class="review__master">
		<div class="container">
			<?php if(!empty($modules = JModuleHelper::getModules('master_reviews')))
			echo JModuleHelper::renderModule($modules[0]);
			?>
		</div>
	</section>
    <?php if ($is_master) { ?>
	<section class="master__about">
		<div class="container">
			<div class="master__about-left">
				<h2>О мастере</h2>
				<p><span>Обо мне: "<?= $this->user->o_sebe ?>"</span></p></br>
				<div class="master__about-call">
					<img src="/templates/ryba/images/iphone1.png">
					<a href="tel:'<?=$this->user->telefon ?>'" target="_blank" rel="noopener noreferrer">Позвонить мастеру</a>
				</div>
				<?php
				$vk = $this->user->getValue('link');
				if(!empty($vk)):?>
					<div class="master__about-vk">
						<img style="width: 27px;height: 27px" src="/templates/ryba/images/vk.jpg"><a href="<?= $vk ?>" target="_blank" rel="noopener noreferrer">Вконтакте</a>
					</div>
					<?php 
				endif;
				$ok = $this->user->getValue('link_4');
				if(!empty($ok)):?>
					<div class="master__about-vk">
						<img style="width: 27px;height: 27px" src="/templates/ryba/images/vk.jpg"><a href="<?= $ok ?>" target="_blank" rel="noopener noreferrer">Вконтакте</a>
					</div>
					<?php 
				endif;
				$inst = $this->user->getValue('link_2');
				if(!empty($inst)):?>
					<div class="master__about-vk">
						<img style="width: 27px;height: 27px" src="/templates/ryba/images/i0.png"><a href="<?= $inst ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
					</div>
					<?php 
				endif;
				$fbk = $this->user->getValue('link_3');
				if(!empty($fbk)):?>
					<div class="master__about-vk">
						<img  style="width: 27px;height: 27px" src="/templates/ryba/images/faceb.png"><a href="<?= $fbk ?>" target="_blank" rel="noopener noreferrer" >Facebook</a>
					</div>
					<?php 
				endif;?>
				<div class="master__about-address">
					<img src="/templates/ryba/images/loclo.png">
					<?php echo $addr?>
				</div>

				<div class="master__about-time">
					<img src="/templates/ryba/images/timet.png">
					<ul class="category__content-info-list">
						<?php
						$week = array('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY');
						foreach($week as $num=>$wd):
							$start_work = bcdiv($work_from[$num], 1, 2);
							$end_work = bcdiv($work_to[$num], 1, 2);
							$start_work = strtr($start_work, '\.', '\:');
							$end_work = strtr($end_work, '\.', '\:');
							?>
							<li>
								<span><?php echo JText::_($wd)?></span>
								<?php if(in_array($num+1, $work_day)):?>
									<span>
										<?php echo $start_work.' - '.$end_work; ?>
									</span>
								<?php else:?>
									<span>Выходной</span>
								<?php endif;?>
							</li>
						<?php endforeach;?>
					</ul>
					<div class="clearFloat"></div>
				</div>
			</div>
			<div class="master__about-right">
				<div id="map" style="width:100%; height:380px"></div>
			</div>
			<div class="clearFloat"></div>
		</div>
		<div class="container">
			<div class="container bot-tex"></div>
		</section>v
		<script>
			$(document).ready(function($) {
				$('#bookmarkme').click(function(e){
					e.preventDefault();
					$.ajax({type: "POST", url: '?option=com_jsn&format=json&task=bookmark',
						data: {master_id: $(e.target).data('id'), remove: $(e.target).hasClass('active') ? 1 : 0}, dataType: 'json',
						success: function(data){
							if(data.success){
								$(e.target).toggleClass('active');
							}
							Joomla.renderMessages({"success":['Мастер добавлен в избранное!']});
						}
					});
				});


				$('.priceList').on('click', '.btn_add-master', function(e) {

					if(!$(e.target).hasClass('plus')){
						e.stopPropagation();
						return;
					}
					$('#zapis').insertAfter('head');

					var tm  = $(this).attr('data-srv-time');
					var res = tm.match(/\d+/g);
					var time = parseInt(res[0]);
					var ps = parseInt(res[1]);
					var time_sum = time + ps;
					$('.time_sum').val(time_sum);
				//alert('Длительность: '+time+ ' мин Пеpерыв: ' +ps+ ' мин');
				
				
				$('.zapis__data-price-wrap').empty();
				var total = parseInt($(e.target).prev().prev().html().replace(/от/g, ''));
				var name = $(e.target).prev().prev().prev().html();
				$('.zapis__data-price-wrap').append('<p class="zapis__data-price"><span>' +name+ '</span><span style="margin-left: 5px"> - длительность: ' +time+ ' мин. </span><b>~ ' +total+ ' руб.</b></p>');
				$('#order-form input[name="svc_id"]').val($(e.target).parent().data('svc-id'));
				$('#order-form input[name="tag_id"]').val($(e.target).parent().data('tag-id'));
				$('#order-form input[name="svc_name"]').val(name);
				$('#order-form input[name="price"]').val(total);
				$('.zapis__data-result').html('Итого:  '+total+' руб.');
				console.log(total);
			});
				$('#zapis').on('shown.bs.modal', function (e) {
					$('.calendar__master').slick("refresh");
					$('#zapis .screen').hide();
					$('#zapis .screen1').show();
					$('.calendar__master input:checked').removeAttr('checked');
					$('.calendar__master.preload').removeClass("preload");
				});

				$('#zapis .btn-next').click(function(el){
					var scr = jQuery(this).closest('.screen');
					if(scr.hasClass('screen1')){
						if(!scr.find('input:checked').length)
							$('.calendar__master').addClass('error');
						else{
							var date = new Date(scr.find('input:checked').first().val());
							$('#req_date').html(date.toLocaleString().slice(0, 10).split('/').reverse().join('.'));
							$('#req_time').html(date.getHours()+':'+(date.getMinutes()==0?'00':date.getMinutes()));
							$('.calendar__master').removeClass('error');
							scr.hide().next().show();
						}
					}else if(scr.hasClass('screen2'))
					scr.hide().next().show();
					else if(scr.hasClass('screen3'))
						if($('#order-form').find('input[name="name"]').val()=='')
							$('#order-form').find('input[name="name"]').addClass('invalid');
						else if($('#order-form').find('input[name="telefon"]').val()=='')
							$('#order-form').find('input[name="telefon"]').addClass('invalid');
						else {
							$.ajax({type: "POST", url: '?option=com_jsn&format=json&task=save_order',
								data: scr.closest('form').serialize(), dataType: 'json',
								success: function(data){
									if(data.success){
										scr.hide().next().show();
										var sid = parseInt($('#order-form #zapis__data-s-id').val());
										var tid = parseInt($('#order-form #zapis__data-t-id').val());
										var item = $('.priceList__item[data-svc-id="'+sid+'"]');
										if(item.length>1 && tid)
											item = item.filter('[data-tag-id="81"]');
										item.find('button').removeClass('plus').addClass('btn__am-yellow fa fa-check');
										document.location.reload();
									}
									else{
										if(!$('#order-form .error-msg').length)
											$('<div class="error-msg"></div>').insertAfter($('#order-form .days__btns'));
										$('#order-form .error-msg').html(data.message);
									}
								}
							});
						}
					});
			});
		//STOCKS
		$(document).ready(function($) {
			$('.stockList').on('click', '.btn_add-stock', function(e) {
				if(!$(e.target).hasClass('plus-stock')){
					e.stopPropagation();
					return;
				}
				var tm  = $(this).attr('data-srv-time');
				var res = tm.match(/\d+/g);
				var time = parseInt(res[0]);
				var ps = parseInt(res[1]);
				var time_sum = time + ps;
				$('.time_sum').val(time_sum); 
				//alert(time);
				$('#stocks').insertAfter('head');
				$('.stock__data-price-wrap').empty();
				var s_price = parseInt($(e.target).prev().prev().html().replace(/от/g, ''));		
				var name = $(e.target).prev().prev().prev().prev().prev().html();
				var o_price = $(e.target).prev().prev().prev().html();
				var a_stock = $(e.target).prev().prev().prev().prev().html();
				var total = 0;
				$('.stock__data-stock_price-wrap').append('<p class="stock__data-s_price"><span>'+name+'</span><span style="margin-left: 5px"> - длительность: ' +time+ ' мин. </span></br><b style="float:left">Стоимость: '+s_price+' руб.</b></p>');
				$('.stock__data-stock_price-wrap').append('<p class="stock__data-o_price"><span>'+o_price+'</span></p></br>');
				$('.stock__data-stock_price-wrap').append('<p class="stock__data-a_stock"><span><b>'+a_stock+'</span></b></p>');
				$('#stock-form input[name="svc_id"]').val($(e.target).parent().data('svc-id'));
				$('#stock-form input[name="tag_id"]').val($(e.target).parent().data('tag-id'));
				$('#stock-form input[name="svc_name"]').val(name);
				$('#stock-form input[name="s_price"]').val(s_price);
				$('#stock-form input[name="o_price"]').val(o_price);
				$('#stock-form input[name="a_stock"]').val(a_stock);
				$('.stock__data-s_price').each(function(){total+=s_price; return total;});
				$('.stock__data-result').html('Итого:  '+total+' руб.');
				console.log(total);
			});
			$('#stocks').on('show.bs.modal', function (e) {
				$('.calendar__master').slick('refresh');
				$('#stocks .screen').hide();
				$('#stocks .screen1').show();
				$('.calendar__master input:checked').removeAttr('checked');
				$('.calendar__master.preload').removeClass("preload");
			});
			
			$('#stocks .btn-next').click(function(el){
				var scr = $(this).closest('.screen');
				if(scr.hasClass('screen1')){
					if(!scr.find('input:checked').length)
						$('.calendar__master').addClass('error');
					else{
						var date = new Date(scr.find('input:checked').first().val());
						$('#stk_date').html(date.toLocaleString().slice(0, 10).split('/').reverse().join('.'));					
						$('#stk_time').html(date.getHours()+':'+(date.getMinutes()==0?'00':date.getMinutes()));
						
						$('.calendar__master').removeClass('error');
						scr.hide().next().show();
					}
				}else if(scr.hasClass('screen2'))
				scr.hide().next().show();
				else if(scr.hasClass('screen3'))
					if($('#stock-form').find('input[name="name"]').val()=='')
						$('#stock-form').find('input[name="name"]').addClass('invalid');
					else if($('#stock-form').find('input[name="telefon"]').val()=='')
						$('#stock-form').find('input[name="telefon"]').addClass('invalid');
					else {
						$.ajax({type: "POST", url: '?option=com_jsn&format=json&task=save_stocks',
							data: scr.closest('form').serialize(), dataType: 'json',
							success: function(data){
								if(data.success){
									scr.hide().next().show();
									var sid = parseInt($('#stock-form #stock__data-s-id').val());
									var tid = parseInt($('#stock-form #stock__data-t-id').val());
									var item = $('.stockList__item[data-svc-id="'+sid+'"]');
									var item = $('.stockList__item[data-svc-id="'+sid+'"]');
									if(item.length>0 && tid)
										item = item.filter('[data-tag-id="81"]');
									item.find('button').removeClass('plus-stock').addClass('btn__am-green fa fa-check');
									document.location.reload();
								}
								else{
									if(!$('#stock-form .error-msg').length)
										$('<div class="error-msg"></div>').insertAfter($('#stock-form .days__btns'));
									$('#stock-form .error-msg').html(data.message);
								}
							}
						}); 
					}
				});
		});

		//END STOCKS
		var map = {};
		ymaps.ready(function () {
			var city = '<?php echo $city?>';
			city = city ?  city : $('a.current-location').text();
			
			ymaps.geocode(city ? city : 'Москва', { results: 1 }).then(function (res) { console.log(city);
				var obj = res.geoObjects.get(0);
				var conf = {center: obj.geometry.getCoordinates(), zoom: 9, controls: []};
				map = new ymaps.Map('map', conf);
				map.defaults = conf;
				map.locations = {[city]:conf.center};
				map.lt = ymaps.templateLayoutFactory.createClass(
					'<div class=\"placemark_layout_container\"><div class=\"circle_layout\"></div></div>');
				var addr = '<?php echo $addr?>';
				if(addr)
					ymaps.geocode(addr, { results: 1 }).then(function (res) {
						var firstGeoObject = res.geoObjects.get(0), coords = firstGeoObject.geometry.getCoordinates();
						var m = new ymaps.Placemark(coords, {}, {iconLayout: map.lt, iconShape: 
							{type: 'Circle', coordinates: [0, 0],radius: 25}});
						map.geoObjects.add(m);
					});
			});
		});
	</script>
	<?php } echo $this->loadTemplate('modal');?>
