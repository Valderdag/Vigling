<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

include(JPATH_SITE.'/templates/ryba/html/com_users/registration/default.php');

$config = JComponentHelper::getParams('com_jsn');
$layout_width=$config->get('layout_width','full');
if( $layout_width == 'full' ) $max_width = 'none';
else $max_width = $config->get('layout_maxwidth','500');
//$document = JFactory::getDocument();
//$document->addScript('https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js');

$db = JFactory::getDbo();
$db->setQuery("SELECT id FROM joomla_categories WHERE published=1 AND path LIKE 'zatochka-remont/%' AND level='2'");
$ids2 = $db->loadColumn();
//var_dump( $ids2);
?>
<style>
div.registration{max-width:<?php if ( empty($max_width) ) echo 'none'; else  echo $max_width.'px'; ?>;margin:auto;}
div.registration.formfullwidth{max-width:none;}
#profile_tabs{font-weight:bold;}
#member-registration.form-horizontal .control-group.privacy + .control-group .controls > input,
#member-registration.form-horizontal .control-group.privacy + .control-group .controls > .input-prepend input,
#member-registration.form-horizontal .control-group.privacy + .control-group .controls > fieldset.radio,
#member-registration.form-horizontal .control-group.privacy + .control-group .controls > fieldset.checkboxes,
#member-registration.form-horizontal .control-group.privacy + .control-group .controls > textarea{padding-right:45px !important;}
#member-registration .controls > input,#member-registration .controls > textarea,#member-registration .controls > .input-prepend,#member-registration .controls > .input-prepend input{width:100%;box-sizing:border-box;height:auto;}
#member-registration .controls > input[type="file"]{width:auto;}
#member-registration .control-label label{font-weight:bold;}

/*#jform_vyberite_spetsialnos label>input:not(:checked)+.flex_wrap{height: 0;overflow: hidden;}*/

.jsn_registration_controls{border-top:1px solid #ccc;padding:20px 0 0;margin-top:20px;clear:both;}
</style>
<script>
$(document).ready(function($){

	$('#jform_firstname,#jform_lastname').parent().parent().wrapAll("<div class='control-group name-group' />");
	$('#jform_email1,#jform_telefon').parent().parent().wrapAll("<div class='control-group mail-group' />");

	$('#jform_sity,#jform_area,#jform_street,#jform_house_number').parent().parent().wrapAll("<div class='address-group form-row m-0' />");
	var w = $('#jform_link,#jform_link_2,#jform_link_3').parent().parent().wrapAll("<div class='links-group form-row' />");
	w.find('.controls').click(function(el){ $(this).addClass('active') });
	$('#jform_upload_avatar').change(function(e){
		var src = URL.createObjectURL(e.target.files[0]);
		$(e.target).parent().find('img').on('load', function(e){ URL.revokeObjectURL(src) });
		$(e.target).parent().find('img').attr('src', src);
	});

	var empty_txt = "Выберите специальность, чтобы  добавить услугу";
	$('<fieldset id="jform_vyberite_usl" />').insertBefore('#jform_prices');
	$('#jform_vyberite_usl').html(empty_txt);
	$('#jform_vyberite_spetsialnos>label>input').change(function(e){
		if(!e.target.checked)
			$(this).parent().removeClass('active');
		else $(this).parent().addClass('active');
		
		if(!$('body #d-shosen').length){
			$('head').append('<link href="/templates/ryba/css/chosen.min.css" rel="stylesheet" />');
			$('body').append('<script id="d-shosen" src="/templates/ryba/js/chosen.jquery.min.js" />');
		}
		
		$('#jform_vyberite_usl').empty();
		if(!$('#jform_vyberite_spetsialnos>label.active').length)
			$('#jform_vyberite_usl').html(empty_txt);
		else $('#jform_vyberite_spetsialnos>label.active').each(function(idx, el){
			var item  = $('<label class="checkbox type_master_closed" />').html($(el).text());
			item.attr('data-id', parseInt($(el).find('input').val()));
			item.append('<b></b>');
			$('#jform_vyberite_usl').append(item);
		});
	});
	$('#jform_vyberite_usl').on('click', 'label', function(e){
		var el = $(e.target);

		if(el.is('b'))
			el=el.parent();
		if(el.is('label')){
			if(!el.hasClass('type_master_open')){
				el.addClass('type_master_open');
				if(!el.find('.plus_key').length)
					el.append('<div class="flex_wrap"><div class="plus_key"></div></div>');
			}
			else el.removeClass('type_master_open');
			return true;
		}
		e.preventDefault();
		event.stopImmediatePropagation();

		if(el.is('.plus_key')){
			var cat_id = parseInt($(el).closest('label').data('id'));
			$.getJSON('?option=com_jsn&format=json&task=get_articles&cat_id='+cat_id, function(data) {
				var ed = $('<div class="service__wrap"><p class="service__item"></p></div>');
				ed.find('p').append('<select name="service_id[]" data-placeholder="Выберите услугу..."><option value=""></option></select><br>');
				ed.find('p').append('<label>Время: </label><span class="time"><input type="text" name="time[]" value="30" /> мин.</span><br>');
				ed.find('p').append('<label>Перерыв: </label><span class="time2"><input type="text" name="time2[]" value="" /> мин.</span><br>');
				ed.find('p').append('<label>Стоимость: </label><span class="price"><input type="text" name="price[]" value=""/> руб.</span>');
				ed.insertBefore(el);
				$.each(data.data, function(idx, item){
					var list_item = $('<optgroup label="'+item.title+'"></optgroup>');
					if(item.tags===undefined || !item.tags.itemTags.length)
						list_item.append('<option value="'+item.id+'">'+item.title+'</option>');
					else if(item.tags.itemTags!==undefined)
							$.each(item.tags.itemTags, function(idx2, tag){
								list_item.append('<option value="'+item.id+'-'+tag.id+'">'+item.title+' / '+tag.title+'</option>');
							});
					ed.find('select').append(list_item);
				});
				ed.find('select').chosen({disable_search_threshold: 10, no_results_text: "Услуга не найдена:"});
			});
		}
		else if(el.is('i')){
			el.parent().parent().remove();
			updatePrices();
		}
		else if(el.is('button')){
			if(!parseInt(el.parent().find('.price input').val())){
				el.parent().find('.price input').addClass('error');
				return false;
			}
			var t = el.parent().find('.chosen-single>span').html();
			el.parent().prepend(t);
			el.parent().find('.chosen-container').remove();
			var time = 0;
			el.parent().find('input').each(function(idx,inp){
				$(inp).attr('type', 'hidden');
				$(inp).parent().prepend($(inp).val());
				$(inp).parent().css('font-weight','bold');
			});
			var s_id = el.parent().find('select').val();
			el.parent().find('select').remove();
			el.parent().append('<input type="hidden" name="service_id[]" value="'+s_id+'" />');
			el.remove();
			updatePrices();
		}
		return false;
	});
	$('#jform_vyberite_usl').on('change', 'select', function(e,el){
		var pb = $(this).parent();
		if(!pb.find('button').length)
			pb.append('<button>Добавить</button>');
	});
	//STOCKS
	var empty_txt = "Выберите специальность, чтобы  добавить акционную услугу";
	$('<fieldset id="jform_stocks_servis" />').insertBefore('#jform_stocks_price');
	$('#jform_stocks_servis').html(empty_txt);
	$('#jform_vyberite_spetsialnos>label>input').change(function(e){
		if(!e.target.checked)
			$(this).parent().removeClass('active');
		else $(this).parent().addClass('active');
		
		if(!$('body #d-shosen').length){
			$('head').append('<link href="/templates/ryba/css/chosen.min.css" rel="stylesheet" />');
			$('body').append('<script id="d-shosen" src="/templates/ryba/js/chosen.jquery.min.js" />');
		}
		
		$('#jform_stocks_servis').empty();
		if(!$('#jform_vyberite_spetsialnos>label.active').length)
			$('#jform_stocks_servis').html(empty_txt);
		else $('#jform_vyberite_spetsialnos>label.active').each(function(idx, el){
			var item  = $('<label class="checkbox type_master_closed" />').html($(el).text());
			item.attr('data-id', parseInt($(el).find('input').val()));
			item.append('<b></b>');
			$('#jform_stocks_servis').append(item);
		});
	});
	$('#jform_stocks_servis').on('click', 'label', function(e){
		var el = $(e.target);

		if(el.is('b'))
			el=el.parent();
		if(el.is('label')){
			if(!el.hasClass('type_master_open')){
				el.addClass('type_master_open');
				if(!el.find('.plus_key').length)
					el.append('<div class="flex_wrap"><div class="plus_key"></div></div>');
			}
			else el.removeClass('type_master_open');
			return true;
		}
		e.preventDefault();
		event.stopImmediatePropagation();

		if(el.is('.plus_key')){
			var cat_id = parseInt($(el).closest('label').data('id'));
			$.getJSON('?option=com_jsn&format=json&task=get_articles&cat_id='+cat_id, function(data) {
				var ed = $('<div class="service__wrap"><p class="service__item"></p></div>');
				ed.find('p').append('<select name="service_id[]" data-placeholder="Выберите услугу..."><option value=""></option></select>');
				ed.find('p').append('<span class="time"><label>Время: </label><input type="text" name="time[]" value="30" /> мин.</span>');
				ed.find('p').append('<span class="time2"><label>Перерыв: </label><input type="text" name="time2[]" value="15" /> мин.</span>');
				ed.find('p').append('<span class="s_price"><label> Акционная стоимость: </label><input type="text" name="s_price[]" value=""/> руб.</span><i></i>');
				ed.insertBefore(el);
				$.each(data.data, function(idx, item){
					var list_item = $('<optgroup label="'+item.title+'"></optgroup>');
					if(item.tags===undefined || !item.tags.itemTags.length)
						list_item.append('<option value="'+item.id+'">'+item.title+'</option>');
					else if(item.tags.itemTags!==undefined)
							$.each(item.tags.itemTags, function(idx2, tag){
								list_item.append('<option value="'+item.id+'-'+tag.id+'">'+item.title+' / '+tag.title+'</option>');
							});
					ed.find('select').append(list_item);
				});
				ed.find('select').chosen({disable_search_threshold: 10, no_results_text: "Услуга не найдена:"});
			});
		}
		else if(el.is('i')){
			el.parent().parent().remove();
			updatePrices();
		}
		else if(el.is('button')){
			if(!parseInt(el.parent().find('.s_price input').val())){
				el.parent().find('.s_price input').addClass('error');
				return false;
			}
			var t = el.parent().find('.chosen-single>span').html();
			el.parent().prepend(t);
			el.parent().find('.chosen-container').remove();
			var time = 0;
			el.parent().find('input').each(function(idx,inp){
				$(inp).attr('type', 'hidden');
				$(inp).parent().prepend($(inp).val());
				$(inp).parent().css('font-weight','bold');
			});
			var s_id = el.parent().find('select').val();
			el.parent().find('select').remove();
			el.parent().append('<input type="hidden" name="service_id[]" value="'+s_id+'" />');
			el.remove();
			updateStocks();
		}
		return false;
	});
	$('#jform_stocks_servis').on('change', 'select', function(e,el){
		var pb = $(this).parent();
		if(!pb.find('button').length)
			pb.append('<button>Добавить</button>');
	});
	//END STOCKS
	$('#jsn_portfolio .control-group img').attr('style', '');
	$('#jsn_portfolio .control-group input').attr('name', 'jform[upload_portfolio_field][]');
	$('#jsn_portfolio .control-group').on('change', 'input[type=file]', function(e){
		var pb = $(this).parent();
		if(!pb.hasClass('preview'))
			pb.parent().append('<div class="controls">'+pb.html()+'</div>');
		var src = URL.createObjectURL(e.target.files[0]);
		pb.find('img').on('load',function(e){ URL.revokeObjectURL(src) });
		pb.attr('style', 'background-image: url("'+src+'");');
		pb.addClass('preview');
		pb.append('<i></i>');
	});
	$('#jsn_portfolio .control-group').on('click', 'i', function(e){
		$(e.target).parent().remove();
	});
	
	$('#jsn_code_tab .control-group input').attr('placeholder', '00000');
	$('#jsn_code_tab>div:last-child').append('<button type="button" class="dale">Получить код</button>');
	$('#jsn_code_tab button').click(function(e){ 
		var val = parseInt($("#jform_is_master input:checked").val());
		if(val==0)
			$('#jform_username').val($('#jform_email1').val());
		if(!document.formvalidator.validate($('#jform_email1')[0]))
			$('#jsn_code_tab label').html('Введите корректный Е-mail');
		else
		  $.getJSON('?option=com_jsn&format=json&task=get_key&email1='+$('#jform_email1').val(), function(data) {
			if(data.success){
				$('#jsn_code_tab button').prop('disabled', true);
				$('#jsn_code_tab label').html('Код был успешно отправлен на Е-mail: ');
				$('#jsn_code_tab label').append('<red>'+$('#jform_email1').val()+'</red>');
			}
			else{
				$('#jsn_code_tab label').html('Ошибка отправки кода на Е-mail: ');
				$('#jsn_code_tab label').append('<red>'+$('#jform_email1').val()+'</red>');
			}
		  });
	});
	$('#jsn_code_tab>div:last-child input').keyup(function(e){
		$.getJSON('?option=com_jsn&format=json&task=check_key&code='+$(this).val(), function(data) {
			if(data.success){			
				$('.jsn_registration_controls button').prop('disabled', false);
				$('#jsn_code_tab input').prop('disabled', true);
				$('#jsn_code_tab button').css('opacity', 0);
				$('#jsn_code_tab label').html('Е-mail <red>'+$('#jform_email1').val()+'</red> подтверждён.');
				$('#member-registration input').last().attr('name', data.data.key);
			}
		});
	});
	
	if(window.location.hash)
		$('#member-registration.init').removeClass('init');
		
	$('#jform_is_master input').click(function(e){
		if($('#member-registration.init').length)
			$('#member-registration.init').removeClass('init');
		
		var remove_ids = <?php echo json_encode($ids2) ?>;
		var val = parseInt($("#jform_is_master input:checked").val());
		if(val==0){
			
			//$(".control-group.name-group").hide().addClass('hide');
			$(".control-group.name-group input").show();
			$(".control-group.sity-group").insertBefore($(".control-group.name-group"));
			$("#jsn_login>.control-group").insertBefore($('.control-group.mail-group')).wrapAll('<div class="control-group login-group"></div>');
			$("#jsn_login").hide().addClass('hide');
            $("#jsn_raspisanie>.control-group").hide().addClass('hide');
			tabs($);
			
			if($('a.current-location').length)
				$("#jform_sity").val($('a.current-location').text());
		}
		else if(val==1){
			remove_ids.forEach(function(v,idx){
				var el = $('input[name="jform[vyberite_spetsialnos][]"][value='+v+']');
				el.parent().hide();
				console.log(el);
			});
		}
		else if(val==2){
			$('input[name="jform[vyberite_spetsialnos][]"]').each(function(idx, el){console.log(el);
				if(!remove_ids.includes(el.value)){
					$(el).parent().hide();
				}
			});
		}
	});
	
	if( $('div.registration').parent().width() < 800 ) {
		$('div.registration').addClass('formfullwidth');
	}
	<?php if ( $layout == 'horizontal' ) : ?>
	if( $('div.registration').parent().width() < 500 ) {
		$('#member-registration').removeClass('form-horizontal');
	}
	<?php endif; ?>
	<?php if ( $layout == 'vertical' ) : ?>$('#member-registration').removeClass('form-horizontal');<?php endif; ?>
	$(window).resize(function(){
		if( $('div.registration').parent().width() < 800 ) {
			$('div.registration').addClass('formfullwidth');
		}
		else {
			$('div.registration').removeClass('formfullwidth');
		}
		<?php if ( $layout == 'horizontal' ) : ?>
		if( $('div.registration').parent().width() < 500 ) {
			$('#member-registration').removeClass('form-horizontal');
		}
		else {
			$('#member-registration').addClass('form-horizontal');
		}
		<?php endif; ?>
	});
	$('#member-registration a.btn:not([class*="btn-"])').addClass('btn-danger');
	$('#member-registration .control-group > .control-label > label').each(function(){
		$(this).closest('.control-group').addClass($(this).attr('id').replace('jform_','').replace('-lbl','-group'));
		$(this).append('<span style="display:none;"> ('+$(this).closest('fieldset').children('legend').text()+')</span>');
	});
	if($('#system-message-container').length) $('#system-message-container').prependTo("#member-registration");
	else $("#member-registration").prepend('<div id="system-message-container" />');
  
	function updatePrices()
	{
		var prices = {};
		var i = $('#jform_vyberite_usl>label .service__item');
		i.each(function(idx, el){
			var ii = $(el).find('input');
			if(ii.length==4){
				var tm = parseInt(ii.first().val());
				var tm = tm+'.'+parseInt(0+ii.eq(1).val());
				var tmp = ii.last().val().split('-');
				var uid = parseInt(tmp[0]);
				var tag_id = 0;
				if(tmp.length>1)
					tag_id = parseInt(tmp[1]);
				
				if(prices[uid]==undefined)
					prices[uid] = [];
				prices[uid].push([parseInt(ii.eq(2).val()), tm, tag_id]);
				
			}
		});
		console.log(JSON.stringify(prices));
		$('#jform_prices').val(JSON.stringify(prices).replace(/"([^"]+)":/g, '$1:'));
	}
	function updateStocks()
	{
		var s_price = {};
		var i = $('#jform_stocks_servis>label .service__item');
		i.each(function(idx, el){
			var ii = $(el).find('input');
			if(ii.length==4){
				var tm = parseInt(ii.first().val());
				var tm = tm+'.'+parseInt(0+ii.eq(1).val());
				var tmp = ii.last().val().split('-');
				var uid = parseInt(tmp[0]);
				var tag_id = 0;
				if(tmp.length>1)
					tag_id = parseInt(tmp[1]);
				
				if(s_price[uid]==undefined)
					s_price[uid] = [];
				s_price[uid].push([parseInt(ii.eq(2).val()), tm, tag_id]);
				
			}
		});
		console.log(JSON.stringify(s_price));
		$('#jform_stocks_price').val(JSON.stringify(s_price).replace(/"([^"]+)":/g, '$1:'));
	}
});
</script>
