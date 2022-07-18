<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;
/*$address[] = $this->user->getValue('sity');
$address[] = $this->user->getValue('area');
$address[] = $this->user->getValue('street');
$address[] = $this->user->getValue('house_number');*/
//var_dump($address);
$work = array();
$vyberite_spetsialnos = (array)$this->user->getField('vyberite_spetsialnos');
foreach($vyberite_spetsialnos as $svc_name);
$about = $svc_name;
$fields=$this->params->def('list_fields', 'col4_fields', array());
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
$work_from = json_decode(($work_from[0]=='[') ? $work_from : '['.$work_from.']');
if(count($work_from)==1)
	$work_from = array_fill(0, 7, $work_from[0]);
elseif(count($work_from)==count($work_day)){
	$work_from = array_replace(array_fill(0, 8, 0), array_combine($work_day, $work_from));
	array_shift($work_from);
}

$work_to = $this->user->getValue('work_to') ? $this->user->getValue('work_to') : '[0]';
$work_to = json_decode(($work_to[0]=='[') ? $work_to : '['.$work_to.']');
if(count($work_to)==1)
	$work_to = array_fill(0, 7, $work_to[0]);
elseif(count($work_to)==count($work_day)){
	$work_to = array_replace(array_fill(0, 8, 0), array_combine($work_day, $work_to));
	array_shift($work_to);
}
$work = array();
		
foreach(range(1, 7) as $week_day){
	if(in_array($week_day, $work_day))
		foreach(range(floor($work_from[$week_day-1]), floor($work_to[$week_day-1])) as $hour)
			if($hour>=$work_from[$week_day-1] && $hour<$work_to[$week_day-1]){ 	 
				$nday = $week_day-date("w")+(($week_day<(int)date("w")) ? 7 : 0);
				
				if($week_day==(int)date("w") && $hour <= (float)date("G")+1)
					$nday += 7;

				$date = JFactory::getDate($hour.":00")->modify("+".$nday." day");
				$work[$nday][] = $date->format("<b>H:i</b> ~ d F", true);
		  }
	
}
ksort($work);
	
$stars = rand(7, 10);

 $prices = $this->user->getValue('prices');
if($prices){
	$prices = preg_replace('/(\w+):/i', '"\1":', $prices); 
	$prices = (array)json_decode($prices);
}
/*$stock_prices = $this->user->getValue('stock_prices');
if($stock_prices){
	$stock_prices = preg_replace('/(\w+):/i', '"\1":', $stock_prices); 
	$stock_prices = (array)json_decode($stock_prices);
} */
JError::setErrorHandling(E_ERROR, "ignore");
$add_class = 'opened';			
?>

<div class="category__item <?=$addClass?>">
	<!-- Avatar Container -->
	<?php
	
	    $portfolio = $this->user->getField('portfolio_field');
		//var_dump($portfolio);
		if(!empty($portfolio)){
		foreach($portfolio as $img);
	?> 
		<div class="category__item-img" style="background-image: url('<?= reset($portfolio) ?>');">
		<?php }
		else {?>
		<div class="category__item-img" style="background-image: url('/images/service4.png');">	
		<?php } 
		if($this->config->get('avatar',1)) :
		if($this->config->get('status',1)) : ?>
			<div class="category__item-master" style="background-image: url('<?php echo $this->user->getValue('avatar_mini')?>'); background-size: cover;">
				<span class="online <?php echo $this->user->isOnline() ? '' : 'offline'; ?>"></span>
			</div>
		<?php endif; ?>				
		</div>
	<?php
		endif;
	?>
	<div class="category__item-content">
		<div class="category__item-content-left">
			<!-- Title Container -->
			<div class="category__content-info">
				<a class="category_cinfo-name" href="<?php echo $this->user->getLink($this->url_options); ?>">
					<?php echo $this->user->getField('formatname'); ?>
				</a>
				
				<span class="category_cinfo-spec"><?= $about; ?></span>
				    <ul class="category_cinfo-ratings" data-stars="<?= $stars?>" >
					<li><i class="fa fa-star" aria-hidden="true"></i></li>
					<li><i class="fa fa-star" aria-hidden="true"></i></li>
					<li><i class="fa fa-star" aria-hidden="true"></i></li>
					<li><i class="fa fa-star" aria-hidden="true"></i></li>
					<?php if($stars>=8): ?>
					<li><i  class="fa fa-star<?php ($stars>=9) ? '' : '-half'; ?>" aria-hidden="true"></i></li>
					<?php endif; ?>
				</ul>
				<?php if(!empty($address)): ?>
				<span class="category_cinfo-address"><i class="fa fa-map-marker" aria-hidden="true"></i><?= implode(', ',$address) ?></span>
				<?php endif; ?>	
				<?php if($this->user->getValue('home')):?>
				<span class="attr_left3">Форма работы: <b><?php echo $this->user->getField('home', false) ?></b></span>
				<?php endif;?>
                <?php
                $jinput = JFactory::getApplication()->input;
                $service = $jinput->get('service', '');
                $tag = $jinput->get('tag', '');
                if ($service) {
                    $prices = $this->user->getValue('prices');
                    $prices = preg_replace('/(\w+):/i', '"\1":', $prices);
                    $prices = (array)json_decode($prices);

                    if ($tag) {
                        $db = JFactory::getDbo();
                        $db->setQuery('SELECT id, title FROM #__tags WHERE id = ' . $tag);
                        $tags = $db->loadObjectList('id');

                        $tags = array_column($tags, 'title', 'id');
                    }

                    JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
                    $model = JModelLegacy::getInstance('Article', 'ContentModel');

                    JError::setErrorHandling(E_ERROR, 'ignore');

                    foreach($prices as $id => $prices2) {
                        if (!empty($prices2)) {
                            if ($id != $service) {
                                continue;
                            }

                            $article = $model->getItem($id);

                            if ($article instanceof JException) {
                                continue;
                            }

                            //echo '<h2>'.$article->category_title.'</h2>';

                            foreach($prices2 as $price) {
                                if ($tag) {
                                    $tag_id = (int)$price[2];

                                    if ($tag_id != $tag) {
                                        continue;
                                    }
                                }

                                $name = $article->title;

                                /*if (array_key_exists($tag_id, $tags)) {
                                    $name .= ' / '.$tags[$tag_id];
                                }*/

                                echo /*'<span>'.$name.'</span>&nbsp;&nbsp;<span>'.(int)$price[1].' мин</span>&nbsp;&nbsp;*/'<button style="border-radius: 5px; color: green; " >от '.$price[0].' руб.</button>';
                                //print_r($layout);
                            }
                        }
                    }

                    JError::setErrorHandling(E_ERROR, 'callback');
                }
                ?>
			</div>
			
			<div class="category__content-info-list">
				<?php// if(empty($prices))?>
			<!--	<ul>
					<?php /* foreach($prices as $id=>$prices2){
							$article = $this->art_model->getItem($id); //print_r($article);
							if($article instanceof JException)
								continue;
							
							foreach((array)$prices2 as $idx=>$price){
								$tag_id = (int)$price[2];
								$name = $article->title; //. ' '.print_r($price, 1);
								if(array_key_exists($tag_id, $this->all_tags))
									$name .= ' / '.$this->all_tags[$tag_id];
								echo '<li><span>'.$name.'</span><span> / '.$price[0].' руб.</span></li>';
								}
							} */
					?>
				</ul>
				<?php// endif;
				   // if(!empty($stock_prices)):?>
				<ul>
				    <button style="color:green; font-weight:bold; background-color:#fffff; border-radius: 5px">Акции</button>
					<?php /* foreach($stock_prices as $id=>$stock_prices2){
							$article = $this->art_model->getItem($id); //print_r($article);
							if($article instanceof JException)
								continue;
							
							foreach((array)$stock_prices2 as $idx=>$stock_price){
								$tag_id = (int)$stock_price[2];
								$name = $article->title; //. ' '.print_r($price, 1);
								if(array_key_exists($tag_id, $this->all_tags))
									$name .= ' / '.$this->all_tags[$tag_id];
								echo '<li><span>'.$name.'</span><span> / '.$stock_price[0].' руб.</span></li>';
								}
							} */
					?>
				</ul>-->
				<?php //endif;?>
			</div>
		</div>
	<div class="category__item-content-right">
		<!--	<span class="content__right-title">Ближайшее время</span>
			<ul class="content__right-times">
				<?php// if(!empty($work)): ?>
					<?php// foreach($work as $w)
						//echo '<li>'.implode('</li><li>', $w).'</li>'; ?>
			<?php// endif; ?>	
			</ul>
			<a class="right-all__time" href="#">Все время <i class="fa fa-angle-down" aria-hidden="true"></i></a>--><a class="btn__time-zapis" href="<?php echo $this->user->getLink($this->url_options); ?>">Записаться</a></div>
	</div>
	</div>
<?php JError::setErrorHandling(E_ERROR, 'callback'); ?>
