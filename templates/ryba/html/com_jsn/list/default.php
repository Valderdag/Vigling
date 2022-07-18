<?php
/**
 * @copyright    Copyright (C) 2013 Jsn Project company. All rights reserved.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @package        Easy Profile
 * website        www.easy-profile.com
 * Technical Support : Forum -    http://www.easy-profile.com/support.html
 */

defined('_JEXEC') or die;

$dispatcher = JEventDispatcher::getInstance();

//var_dump($this->state);die(); 
echo(implode(' ', $dispatcher->trigger('renderBeforeList', array($this->items, $this->config))));

//$title = $this->params->get('page_heading');
//if(empty($title))
$cat_ids = JFactory::getApplication()->input->get('vyberite_spetsialnos', array(), 'ARRAY');
$flt_url = JRoute::_('index.php?Itemid=' . JFactory::getApplication()->input->get('Itemid', ''), false);

$title = 'Все специалисты';
$db = JFactory::getDbo();
if (!empty($cat_ids) && (int)$cat_ids[0]) {
    $db->setQuery("SELECT `title` FROM `#__categories` WHERE `id`=" . $db->quote($cat_ids[0]));
    $title = $db->loadResult();
    //$flt_url = $flt_url.'/'.$cat_ids[0];
}

$city = JFactory::getApplication()->input->getString('city');

$db->setQuery("SELECT DISTINCT `sity` FROM `#__jsn_users` WHERE `sity`<>'' ORDER BY `sity` ASC");
$cities = $db->loadObjectList();
if (class_exists('McsData') && !$city) {
    $city = MCSData::get('cityName');
    if (!in_array($city, $cities))
        array_unshift($cities, JHTML::_('select.option', $city, $city, 'sity', 'sity'));
}
if (!array_key_exists('', $cities))
    array_unshift($cities, JHTML::_('select.option', '', '', 'sity', 'sity'));

$db->setQuery("SELECT DISTINCT `area` FROM `#__jsn_users` ORDER BY `area` ASC");
$areas = $db->loadObjectList('area');
if (!array_key_exists('', $areas))
    array_unshift($areas, JHTML::_('select.option', '', '', 'area', 'area'));
$area = JFactory::getApplication()->input->getString('area');

$query = "SELECT title, id FROM #__categories WHERE published='1' AND level='2' AND path LIKE 'uslugi/%' ORDER BY `title` ASC";
if ($layout == 'table' || (strpos(JUri::current(), 'zatochka-remont')) !== false) {
    $query = "SELECT title, id FROM #__categories WHERE published='1' AND level='2' AND path LIKE 'zatochka-remont/%' ORDER BY `title` ASC";
}

$db->setQuery($query);
$categories = $db->loadAssocList('id');
array_unshift($categories, JHTML::_('select.option', '', '0', 'title', 'id'));

$services = array();
if (!empty($cat_ids) && (int)$cat_ids[0]) {
    JLoader::import('joomla.application.component.model');
    JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
    $model = JModelLegacy::getInstance('Articles', 'ContentModel');
    $model->getState();
    $model->setState('filter.published', 1);
    $model->setState('filter.category_id', (int)$cat_ids[0]);
    $services = $model->getItems();
}
array_unshift($services, JHTML::_('select.option', '', '', 'title', 'id'));
$service = JFactory::getApplication()->input->getInt('service');

$tags = array();
if ($service) {
    foreach ($services as $svc) {
        if ($svc->id == $service)
            if (property_exists($svc, 'tags') && property_exists($svc->tags, 'itemTags'))
                $tags = $svc->tags->itemTags;
    }
}
array_unshift($tags, JHTML::_('select.option', '', '', 'title', 'id'));
$tag = JFactory::getApplication()->input->getInt('tag');
//var_dump($this->state);die();
$date = JFactory::getApplication()->input->getString('date');
$name = JFactory::getApplication()->input->getString('name');

$svc_types = JsnHelper::getFieldOptions('home');
$home = JFactory::getApplication()->input->getString('home');
$doc = JFactory::getDocument();
$doc->addScript('https://api-maps.yandex.ru/2.1/?lang=ru-RU&amp;apikey=705d45a1-9138-4d99-afd4-dc261c612036');
$doc->addStyleSheet('/templates/ryba/css/chosen.min.css');
$doc->addStyleSheet('//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
$doc->addScript('/templates/ryba/js/chosen.jquery.min.js');
$doc->addScriptDeclaration("
	var regional = {closeText: 'Закрыть', prevText: '<Пред', nextText: 'След>', currentText: 'Сегодня', monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь', 'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'], 
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн', 'Июл','Авг','Сен','Окт','Ноя','Дек'], dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'], 
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'], weekHeader: 'Не', dateFormat: 'dd.mm.yy', firstDay: 1, isRTL: false, 
		showMonthAfterYear: false, yearSuffix: ''};

	jQuery(document).ready(function($){
		$('.category__masters-sidebar select').chosen({disable_search_threshold: 100, allow_single_deselect: true, no_results_text: 'Не найдено:'});
		
		$.datepicker.setDefaults(regional);
		$('.category__masters-sidebar input[name=\"date\"]').datepicker({dateFormat: 'dd.mm.yy', minDate: 'now', onSelect: function(dateText){ this.setAttribute('value', dateText) },  beforeShow: function(input, inst) { inst.dpDiv.css({ marginLeft: (input.offsetWidth - $(inst.dpDiv).outerWidth(false))+ 'px' }); }});

		$('ul.sort input').change(function(){
			$('form.filter input[name=\"filter_order\"]').val($(this).val());
			updateSvcList();
		});
		
		$('.category__masters').on('click', '.right-all__time', function(e){
			e.preventDefault();
			$(this).prev().toggleClass('open');
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});
		
		$('#vyberite_spetsialnos, #service').change(function(elem){ console.log($(this).val());
			if(this.value==0){
				$(this).parent().next().addClass('hidden');
				if(this.id!='service')
					$(this).parent().next().next().addClass('hidden');
				$(this).parent().parent().find('.clearable.hidden select').val(0);
			}
			else{
				$(this).parent().next().find('.chosen-container').css('width', $(this).css('width'));
				var sel = $(this).parent().next().find('select');
				$.getJSON('?option=com_jsn&format=json&task=get_articles', 
					{'cat_id': $('#vyberite_spetsialnos').val()}, function(data) {
						if(data.success){
							if(data.data.length !== undefined && data.data.length==0)
								console.log('hide');
							else {
								sel.empty();
								sel.append('<option value=\"0\">'+'</option>');
								$.each(data.data, function(idx, item){
									if(sel.is('#service'))
										sel.append('<option value=\"'+item.id+'\">'+item.title+'</option>');
									else if(item.tags.itemTags!=undefined && item.tags.itemTags.length && (item.id==$('#service').val())){			
										$.each(item.tags.itemTags, function(idx2, tag){
											sel.append('<option value=\"'+tag.id+'\">'+tag.title+'</option>');
										});
									}
								});
								if(sel.is('#tag')){ 
									var opts_list = sel.find('option');
									opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
									sel.html('').append(opts_list);
								}
								sel.val(0);
								sel.trigger('chosen:updated');
								if(sel.children().length>1)
									sel.parent().removeClass('hidden');
								else sel.parent().addClass('hidden');
							}
						}
				});
			}
		});
	});
	var map = {};
	ymaps.ready(function () {
		var city = '" . $city . "';
		city = city ?  city : $('a.current-location').text();
		
		ymaps.geocode(city ? city : 'Москва', { results: 1 }).then(function (res) { 
			var obj = res.geoObjects.get(0);
			var conf = {center: obj.geometry.getCoordinates(), zoom: 10, controls: []};
			map = new ymaps.Map('map', conf);
			map.defaults = conf;
			map.locations = {[city]:conf.center};
			map.lt = ymaps.templateLayoutFactory.createClass(
				'<div class=\"placemark_layout_container\"><div class=\"circle_layout\"></div></div>');
				map.blt = ymaps.templateLayoutFactory.createClass(
            '<div class=\"map_card\">' +
                '$[properties.content]' +
            '</div>');
				
	
			updateMap();
		});
	});
	
	function updateMap(){
		map.geoObjects.removeAll();
		var items = jQuery('.category__item');
		
		console.log(items);
			console.log('1');
		
		if(items.length)
			items.each(function(idx, el){
				var addr = jQuery(el).find('.category_cinfo-address').text();
				
				var content = jQuery(el).find('.category__content-info').html();			
	
				console.log(addr);
				ymaps.geocode(addr, { results: 1 }).then(function (res) {
					var firstGeoObject = res.geoObjects.get(0),
						coords = firstGeoObject.geometry.getCoordinates(),
						bounds = firstGeoObject.properties.get('boundedBy');
					console.log(coords);
					var m = new ymaps.Placemark(coords, {content: content}, {iconLayout: map.lt, balloonContentLayout:map.blt, iconShape: 
						{type: 'Circle', coordinates: [0, 0],radius: 25}});
					map.geoObjects.add(m);
				});
			});
		
		
		if(jQuery('#city')){
			ymaps.geocode(jQuery('#city').val(), { results: 1 }).then(function (res) {
				var obj = res.geoObjects.get(0);
				map.setCenter(obj.geometry.getCoordinates());
			});
		}
		else{ map.setCenter(map.defaults.center); map.setZoom(map.defaults.zoom);
		}
	}
	
	
	//var placemark_index = 0;
	//function setMapCenter(){
	//	placemark_index += 1;
	//	if(jQuery('.category__item').length == placemark_index) {console.log(map.geoObjects.getCenter());
			
	//		map.setBounds(map.geoObjects.getBounds(),{checkZoomRange:true, zoomMargin:9});
			//if (map.getZoom() > 7) map.setZoom(7); 
			//se
	//		placemark_index = 0;
	//	}
	//}
	
	function updateSvcList()
	{
		var f = $('form.filter');
		var url = f.attr('action');
		if(parseInt(f.find('#vyberite_spetsialnos').val()))
			url = url+'/'+f.find('#vyberite_spetsialnos').val();
			
		var fields = f.find('.clearable>input,.clearable>select:not(#vyberite_spetsialnos)');
		url += '?'+fields.serialize().replace(/(?<=&|^)[^=]+=(?:&|$)|(?:&|^)[^=]+=(?=&|$)/g, '');
		history.pushState(undefined, '', url);
		
		url += '&filter_order='+f.find('[name=\"filter_order\"]').val();
		url += '&filter_order_Dir='+f.find('[name=\"filter_order_Dir\"]').val();

		$.ajax({type: 'GET', url: url, dataType: 'html', 
            beforeSend: function () { $('.category.jsn_list').addClass('waiting') },
            success: function (data) {
                if (data.length > 0) { 
					var content = $('.category__item', data);
					$('.category__masters').html(content);
					var head = $('.category__head', data);
					var pagin = $('.pagination__wrap', data);
					$('.category__masters').append(pagin);
					$('.category__head h2').html(head.find('h2').text());
					$('.category__head h2').next().html(head.find('.cat_head-res').html());
					updateMap();
			    }
            }, 
            complete: function () { $('.category.jsn_list, .category.jsn_table ' ).removeClass('waiting') },
        });
		return false;
	}
	");
$db = JFactory::getDbo();
$db->setQuery("SELECT id, title FROM #__tags");
$tags = $db->loadObjectList('id');
$tags[1]->id = '';
$tags[1]->title = 'Нет';

$this->all_tags = array_column($tags, 'title', 'id');
  
$this->articles = array();
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
$this->art_model = JModelLegacy::getInstance('Article', 'ContentModel');
?>
    <div id="map" style="width:100%; height:380px"></div>
    <!--iframe src="https://yandex.ru/map-widget/v1/-/CCr5j1b" width="100%" height="400" frameborder="0" allowfullscreen="true"></iframe-->
    <div class="category jsn_list">
        <style>
            main > .container {
                padding: 0;
                max-width: 1170px;
            }

            .category__content-info-list li span {
                max-width: 280px
            }

            .category__masters-sidebar .clearable {
                width: 100%
            }

            .category__masters-sidebar select {
                width: 100%
            }
        </style>
        <div class="category__head">
            <h2><?php echo $title ?></h2>
            <?php if (0 && $this->params->def('search_enabled', 0) && !(JFactory::getApplication()->input->get('search', '0') && $this->params->def('search_hideform', 0)))
                echo $this->loadTemplate('search');
            echo(implode(' ', $dispatcher->trigger('renderBeforeResultList', array($this->items, $this->config))));
            if (/*$this->items && count($this->items)>0 && */
            !($this->params->def('search_enabled', 0) && !$this->params->def('search_showuser', 0) && !JFactory::getApplication()->input->get('search', 0)))
                if ($this->params->def('show_total', 1))
                    echo('<span class="cat_head-res">' . JText::_('COM_JSN_MEMBERS') . ' - <span>' . $this->pagination->total . '</span></span>');
            ?>
            <form action="<?php echo JRoute::_('index.php?Itemid=' . JFactory::getApplication()->input->get('Itemid', ''), false); ?>" class="form-horizontal" method="get">
                <ul class="sort">
                    <li>Сортировка:</li>
                    <li><label class="radioBox"> Рекомендуемое <input checked="checked" name="filter_order" type="radio" value="a.name"> <span class="checkmark"></span> </label></li>
                    <li><label class="radioBox"> Рейтинг <input name="filter_order" type="radio" value="rate"> <span class="checkmark"></span> </label></li>
                    <li><label class="radioBox"> Цена <input name="filter_order" type="radio" value="price"> <span class="checkmark"></span> </label></li>
                </ul>
                <input type="hidden" name="search" value="<?php echo JFactory::getApplication()->input->get('status', '') ?>"/>
                <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction') ?>"/>
                <?php echo JHtml::_('form.token'); ?>
            </form>
        </div>
        <div class="category__body">
            <div class="clearFloat">&nbsp;</div>
            <div class="category__masters">
                <?php if ($this->items && count($this->items) == 0) { ?>
                    <div class="alert alert-warning">
                        <?php echo(JText::_('COM_JSN_NORESULT')); ?>
                    </div>
                <?php } ?>
                <?php $this->url_options = array();
                $this->url_options['Itemid'] = $this->params->def('profile_menuid', '');
                //if($this->params->def('profile_back', 1)) $this->url_options['back'] = 1;

                $cols = $this->params->def('num_columns', 1);
                $this->span = 12 / $cols;
                $countUsers = 0;
                global $JSNLIST_DISPLAYED_ID;
                if (is_array($this->items)) foreach ($this->items as $item) {
                    $JSNLIST_DISPLAYED_ID = $item->id;
                    $this->user = JsnHelper::getUser($item->id);
                    //if(($countUsers%$cols)==0) echo('<div class="jsn-l-row">');
                    echo $this->loadTemplate('user');
                    //if(($countUsers%$cols)==($cols-1)) echo('</div>');
                    $countUsers += 1;
                }
                if (($countUsers % $cols) != 0) echo('</div>');
                $JSNLIST_DISPLAYED_ID = false;
                ?>

                <?php // Add pagination links ?>
                <?php if (!empty($this->items)) : ?>
                    <?php if (($this->params->def('show_pagination', 1) == 1 || $this->params->def('show_pagination', 1) == 3) && $this->pagination->pagesTotal > 1) : ?>
                        <div class="pagination__wrap" style="clear:both">
                            <?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php echo(implode(' ', $dispatcher->trigger('renderAfterResultList', array($this->items, $this->config)))); ?>
            </div>
            <div data-da=".pagination__wrap,922,1" class="category__masters-sidebar">
                <h2>Фильтр специалистов</h2>
                <form action="<?php echo $flt_url; ?>" class="form-horizontal filter" method="get">
                    <div class="masters-sidebar__body">
                        <span class="clearable"><?php echo JHTML::_('select.genericlist', $cities, 'city', array('data-placeholder' => "Город"), 'sity', 'sity', $city); ?></span>
                        <span class="clearable"><?php echo JHTML::_('select.genericlist', $areas, 'area', array('data-placeholder' => "Район"), 'area', 'area', $area); ?></span>
                        <!--span class="clearable"><input class="filed__master" name="name" type="text" value="<?php echo $name ?>" placeholder="Имя"><i class="clearable__clear">×</i> </span-->
                        <span class="clearable"><?php echo JHTML::_('select.genericlist', $categories, 'vyberite_spetsialnos[]', array('data-placeholder' => "Мастер"), 'id', 'title', empty($cat_ids) ? 0 : (int)$cat_ids[0]); ?></span>
                        <span class="clearable<?php echo (!empty($cat_ids) && (int)$cat_ids[0]) ? '' : ' hidden' ?>"><?php echo JHTML::_('select.genericlist', $services, 'service', array('data-placeholder' => "Услуга"), 'id', 'title', $service); ?></span>
                        <span class="clearable<?php echo (!empty($cat_ids) && (int)$cat_ids[0] && $service) ? '' : ' hidden' ?>"><?php echo JHTML::_('select.genericlist', $tags, 'tag', array('data-placeholder' => "Метод"), 'id', 'title', $tag); ?></span>
                        <span class="clearable"><?php echo JHTML::_('select.genericlist', $svc_types, 'home[]', array('data-placeholder' => "Вид услуги", 'multiple' => true), 'id', 'title', $home); ?></span>
                        <span class="clearable">
				<input class="filed__master" name="date" type="text" value="<?php echo $date ?>" placeholder="Дата"><i class="clearable__clear">×</i> </span>
                    </div>
                    <input class="submit__search" type="button" value="Поиск" onclick="return updateSvcList()">
                    <input type="hidden" name="status" value="<?php echo(JFactory::getApplication()->input->get('status', '') != '') ?>"/>
                    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering') ?>"/>
                    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction') ?>"/>
                </form>
            </div>
        </div>
        <script id="d-shosen" src="/templates/ryba/js/chosen.jquery.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
            function resizeBlock() {
                if (window.matchMedia("screen and (max-width: 620px)").matches) {
                    $('.category__head').appendTo($('.masters-sidebar__body'));
                } else if (window.matchMedia("screen and (min-width: 620px)").matches) {
                    $('.category__head').prependTo($('.jsn_list'));
                }
            }

            $(window).resize(function () {
                resizeBlock();
            });

            $(document).ready(function () {
                resizeBlock();
            });
        </script>
    </div>
    </div>
<?php 
echo(implode(' ', $dispatcher->trigger('renderAfterList', array($this->items, $this->config))));
?>