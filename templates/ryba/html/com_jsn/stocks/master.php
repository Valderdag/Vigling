<?php defined('_JEXEC') or die;
JLoader::register('JTplHelper', JPATH_SITE . '/templates/ryba/html/helper.php');
//$this->document->setTitle($this->document->title.' - '.JsnHelper::getFormatName($this->data));
$dispatcher	= JEventDispatcher::getInstance();
$this->user=JsnHelper::getUser();
$this->current_user=JsnHelper::getUser();
//$user=JsnHelper::getUser();
$hidden_rows = $visible_rows = 0;
$db = JFactory::getDbo();
?> 
<!-- Main Container -->
<div class="jsn-p">
	<?php if(!$this->items || !count($this->items)):?>
		<h3>Нет записей на акционные услуги</h3>
	<?php else:?>
		<h2>Записи на акционные услуги</h2>
		<?php foreach($this->items as $i=>$stocks){
			$add_style = (JFactory::getDate($stocks->time)<JFactory::getDate())?' text-muted hidden':'';
			if(!$add_style)
				$visible_rows +=1;
			else $hidden_rows += 1;
            $user_m=JsnHelper::getUser($stocks->user_id);
            $break_start = strtotime($stocks->time);
            $break_end = strtotime($stocks->time_to);
            $duration =	$break_start - $break_end;
	        $time_sum = abs($duration);

            $query = 'SELECT b.title FROM #__content a INNER JOIN #__categories b ON b.id = a.catid WHERE a.id = '.$stocks->svc_id;
            $db->setQuery($query);
            $cat_title = $db->loadResult();
			?>
			<span id="row"class="row<?= $add_style ?>" style="padding: 6px 0;" id="<?= $stocks->time ?>" >
			    <div class="col-sm-5 col-md-2 p-0"><?php echo JHtml::_('date', $stocks->time, 'Дата: '.'d-m-y'.'/'.'H:i'.' - '.date("H:i", strtotime($stocks->time_to)));?></div></br>
				<div class="col-sm-7 col-md-6"><span style="font-weight:bold"> <?php echo $stocks->stocks_servis;?></span></div>
				<div class="col-sm-6 col-md-7"><span style="font-weight:bold"><?php echo $stocks->about_stock;?></span></div>
				<div class="col-sm-6 col-md-7"><strong style="font-weight:700">Комментарий клиента: </strong><?php echo $stocks->note;?></div>
				<div class="stock_old_price"><span style="text-decoration:line-through"><?php echo number_format($stocks->old_price, 0, '.', ' ').'p.';?></span></div>
				<div class="col-xs-6 col-sm-5 col-md-2"><?php echo number_format($stocks->stock_price, 0, '.', ' ').'p.';?></div>
				<div class="col-xs-6 col-sm-7 col-md-2"><a href="<?php echo $user_m->getLink();?>"><?php echo $stocks->user_name;?></a></div>
				<div class="display: inline;">
				<?php if(JFactory::getDate($stocks->time)>JFactory::getDate()): ?>
			    <button type="button" class="btnrpl" data-toggle="modal" data-target="#rplStock" data-svc-name="<?= $stocks->service_name ?>" data-stockid="<?= $stocks->stock_id ?>" data-timesum="<?= $time_sum ?>" data-user="<?= $stocks->user_id ?>" data-master="<?= $stocks->master_id ?>" data-svc-id="<?= $stocks->svc_id ?>" data-tag-id="<?= $stocks->tag_id ?>" style="margin: 3rem auto;">
						Изменить
				</button>
		        <button type="button" class="btndel" data-toggle="modal" data-target="#delStock" data-svc-name="<?= $stocks->service_name ?>" data-master="<?= $stocks->master_id ?>" data-time="<?= $stocks->time ?>" data-tag-id="<?= $stocks->tag_id ?>" style="margin: 3rem auto;">
				Отменить 
				</button><?php endif; ?>
		        </div>
			</div>
		<?php }?>
		<?php if($hidden_rows && $visible_rows):?>
		<div class="row m-5">
			<a class="btnrpl" style="margin: 3rem auto;" href="#" onclick="jQuery(this).parent().siblings().toggleClass('hidden')">Архивные показать/скрыть</a>
		</div>
		<?php endif;?>
		<?php if($hidden_rows && !$visible_rows):?>
				<style>.jsn-p .row.hidden{display: flex;}</style>
		<?php endif;?>
	<?php endif;?>
	</div>
	<?php 
	$work_day = (array)$this->user->getValue('work_day');
    $work_from = $this->user->getValue('work_from') ? $this->user->getValue('work_from') : '[0]';
    $work_to = $this->user->getValue('work_to') ? $this->user->getValue('work_to') : '[0]';

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
	?>
   	<script>
		function addZero(el) {
			if (el < 10) {
				el = '0' + el;
			}

			return el;
		}

		var slider_opts = {
			infinite: false,
			slidesToShow: 5,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 5,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 820,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}
			]
		}

		$(document).ready(function($) {
			$('.btnrpl').click(function(e){
				var svc_id  = $(this).attr('data-svc-id');
				$('.svcid').val(svc_id);
				var tag_id  = $(this).attr('data-tag-id');
				$('.tagid').val(tag_id);
				var svc_name  = $(this).attr('data-svc-name');
				$('.svcname').val(svc_name);
				var time_sum = $(this).attr('data-timesum');
				$('.time_sum').val(time_sum);
				var master_id  = parseInt($(this).attr('data-master'));
				$('.masid').val(master_id);
				var user_id  = parseInt($(this).attr('data-user'));
				$('.userid').val(user_id);
                var stock_id  = $(this).attr('data-stockid');
                $('.stock_id').val(stock_id);

                var days  = 45; //количество дней вперед
                var mas_wrapper = $('.calendar__master');
                mas_wrapper.slick('unslick');
                var url = '?option=com_ajax&template=ryba&format=json';
                $.ajax({
                	url: url,
                	data: {
                		'master': master_id
                	},
                	type: 'post',
                	dataType: 'json',
                	success: function(data){
                		mas_wrapper.html('');
                		var date = new Date();
                		var hour = date.getHours();
                		var minute = date.getMinutes();

                		var weekdays = ['вс.', 'пн.', 'вт.', 'ср.', 'чт.', 'пт.', 'сб.'];

                		for (var i = 1; i <= days; i++) {
                			var day = date.getDate();
                			var wday = date.getDay();
                			var month = date.getMonth() + 1;
                			var year = date.getFullYear();

                			day = addZero(day);
                			month = addZero(month);

                			var date_f1 = day + '.' + month + '.' + year;
                			var date_f2 = year + '-' + month + '-' + day;

                			var item = $('<div/>').attr('class', 'calendar__master-item');
                			var weekday = weekdays[wday];
                			item.append('<div class="mas-date">' + date_f1 + '<br><b>' + weekday + '</b></div>');

                			var wday_rus = wday;
                			if (wday == 0) {
                				wday_rus = 7;
                			}

                			if ($.inArray(wday_rus.toString(), data.work_day) !== -1) {
                				var item_p = $('<p/>').attr('class', 'btns-m');

                				for (var h = 0; h <= 23; h++) {
                					var h_0 = addZero(h);
                					var m_0 = '00';

                					for (var m = 0; m <= 3; m++) {
                						var m_int = m * 15;
                						m_0 = addZero(m_int);

                						if (i == 1 && h <= hour) {
                							continue;
                						}

                						var cur_date = date_f2 + ' ' + h_0 + ':' + m_0 + ':00';
                						if ((new Date(cur_date) < new Date(date_f2 + ' ' + data.start_work.replace('.', ':') + ':00')) || (new Date(cur_date) > new Date(date_f2 + ' ' + data.end_work.replace('.', ':') + ':00'))) {
                							continue;
                						}

                						var cls = '';
                						$.each(data.order_date, function(k, order){
                							if ((new Date(cur_date) >= new Date(order + ' ' + data.start_order[k])) && (new Date(cur_date) <= new Date(order + ' ' + data.end_order[k]))) {
                								cls = ' reserved';
                							}
                						});

                						$.each(data.stock_date, function(k, stock){
                							if ((new Date(cur_date) >= new Date(stock + ' ' + data.start_stock[k])) && (new Date(cur_date) <= new Date(stock + ' ' + data.end_stock[k]))) {
                								cls = ' reserved';
                							}
                						});

                						var item_id = date_f2 + '-' + h_0 + '-' + m_0 + '-00';
                						item_p.append('<input type="radio" id="' + item_id + '" name="time" value="' + date_f2 + ' ' + h_0 + ':' + m_0 + '">');
                						item_p.append('<label for="' + item_id + '" class="btn-select' + cls + '">' + h_0 + ':' + m_0 + '</label>');
                					}
                				}

                				item.append(item_p);
                			} else {
                				item.append('<span class="line-no"></span>');
                			}

                			date.setDate(date.getDate() + 1);
                			mas_wrapper.append(item);
                		}

                		mas_wrapper.slick(slider_opts);
                	}
                });
            });
});
$(document).ready(function($) {
	$('#rplStock').insertAfter('footer');
	$('#rplStock').on('shown.bs.modal', function (e) {
		$('#rplStock .screen').hide();
		$('#rplStock .screen1').show();
		$('.calendar__master input:checked').removeAttr('checked');
		$('.calendar__master.preload').removeClass("preload");
	});
	$('#rplStock .btn-next').click(function(el){
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
				$.ajax({type: "POST", url: '?option=com_jsn&format=json&task=updateStock',
					data: scr.closest('form').serialize(), dataType: 'json',
					success: function(data){
						if(data.success){
							$('#rplStock').modal('hide');
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
$(document).ready(function($) {
	$('#delStock').insertAfter('footer');
	$('.btndel').click(function(){
		var master  = $(this).attr('data-master');
		$('.masid').val(master);
		var time  = $(this).attr('data-time');
		$('.svctime').val(time);
		var tag_id  = $(this).attr('data-tag-id');
		$('.tagid').val(tag_id);
		var svc_name  = $(this).attr('data-svc-name');
		$('.svcname').val(svc_name);
        var stock_id  = $(this).attr('data-stockid');
        $('.stock_id').val(stock_id);

		$('.btn-del').click(function(){
			$.ajax({type: "POST", url: '?option=com_jsn&format=json&task=delStock',
				data: $('#delStock-form').serialize(), dataType: 'json',
				success: function(data){
					if(data.success){
						$('#delStock').modal('hide');
						document.location.reload();
					}

				}
			});
		});
	});
});
</script>
<?php echo $this->loadTemplate('modal');?>