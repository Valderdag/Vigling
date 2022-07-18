<?php defined('_JEXEC') or die;

class JTplHelper
{
	static function renderField($field, $form)
	{
		switch($field->fieldname)
		{
			case 'avatar':
			echo str_replace('width:50px;', '', $form->getInput($field->fieldname));
			break;
			case 'portfolio_field':
			echo '<div class="control-group portfolio_field-group">';
			if($field->value){
				if(!is_array($field->value))
					$field->value=array($field->value);

				foreach($field->value as $img){
					echo '<div class="controls preview" style="background-image: url('.$img.');">
					<input type="file" name="jform[upload_portfolio_field][]" id="jform_upload_portfolio_field" accept="image/*" />
					<input type="hidden" name="jform[upload_portfolio_field][]" id="jform_portfolio_field" value="'.$img.'">
					<i></i></div>';
				}
			}
			echo '<div class="controls"><img src="/templates/ryba/images/3.png" alt="" class="img_portfolio_field">
			<input type="file" name="jform[upload_portfolio_field][]" id="jform_upload_portfolio_field" accept="image/*" readonly />
			<input type="hidden" name="jform[upload_portfolio_field][]" id="jform_portfolio_field_up" value="" readonly />
			</div>';
			echo '</div>';
			break;
			case 'prices':
			echo '<fieldset id="jform_vyberite_usl">';

			$spec_field = $form->getField('vyberite_spetsialnos');
			$usl_list = $spec_field->getOptions();
			$usl_list = array_column($usl_list, 'text', 'value');
			$selected = $spec_field->__get('value');
			$field->value = preg_replace('/(\w+):/i', '"\1":', $field->value);
			$prices = (array)json_decode($field->value);

			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
			$model = JModelLegacy::getInstance('Articles', 'ContentModel');

			if(empty($selected))
				echo 'Выберите специальность, чтобы  добавить услугу';
			else
				foreach($selected as $catid){
					if(array_key_exists($catid, $usl_list)){
						$model->getState();
						$model->setState('filter.published', 1);
						$model->setState('filter.category_id', $catid);
						$model->setState('list.ordering', 'a.title');
						$model->setState('list.direction', 'ASC');
						$articles = $model->getItems();
						$add_html = $usl_list[$catid].'<b></b><div class="flex_wrap">';
						foreach($articles as $art){
							if(!array_key_exists($art->id, $prices))
								continue;
							if(isset($art->tags) && isset($art->tags->itemTags))
								$tags = array_column($art->tags->itemTags, 'title', 'tag_id');
							else $tags = array();
							foreach((array)$prices[$art->id] as $price){
								$title = $art->title;
								$tid = 0;
								$service_id = $art->id;
								if(count($price) > 2 && $tid = $price[2])
									if(array_key_exists($tid, $tags)){
										$title .= ' /'.$tags[$tid];
										$service_id = $service_id.'-'.$tid;
									}

									$pause = explode('.', (string)$price[1]);
									$pause = (count($pause)==1) ? 0 : (int)$pause[1];
									$price[1] = floor($price[1]);
									$add_html .= '<div class="service__wrap"><p class="service__item"><a href="#" class="hdr">'.$title.'</a>';
									$add_html .= '<span class="time"><label>Время: </label><input name="time[]" value="'.$price[1].'" type="hidden">'.$price[1].' мин.</span>';
									$add_html .= '<span class="time2"><label>Перерыв: </label><input name="time2[]" value="'.$pause.'" type="hidden">'.$pause.' мин.</span>';
									$add_html .= '<span class="price"><label>Стоимость: </label><input type="hidden" name="price[]" value="'.$price[0].'">'.$price[0].' руб.</span>';
									$add_html .= '<i></i><input type="hidden" name="service_id[]" value="'.$service_id.'">';
									$add_html .= '</p></div>';
								}
							}
							echo '<label class="checkbox type_master_closed" data-id="'.$catid.'">';
							echo $add_html.'<div class="plus_key"></div></div></label>';
						}
					}
					echo '</fieldset>';

					echo '<input id="jform_prices" type="hidden" name="jform[prices]" value="'.str_replace('"', '', $field->value).'" />';
					break;
			////STOCK PRICES
					case 'stock_prices':
					echo '<fieldset id="jform_stocks_servis">';

					$spec_field = $form->getField('vyberite_spetsialnos');
					$usl_list = $spec_field->getOptions();
					$usl_list = array_column($usl_list, 'text', 'value');
					$selected = $spec_field->__get('value');
					//$field->value = preg_replace('/(\w+)/iu', '"\1"', str_replace('"', '', $field->value));
					$field->value = str_replace(array('["[', ']","[', ']"]'), array('[[', '],[', ']]'), str_replace(array(',', ']', '[', '{', ':'), array('","', '"]', '["', '{"', '":'), str_replace('"', '', $field->value)));

					$stock_prices = (array)json_decode($field->value);

					JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
					$model = JModelLegacy::getInstance('Articles', 'ContentModel');

					if(empty($selected))
						echo 'Выберите специальность, чтобы  добавить акционную услугу';
					else
						foreach($selected as $catid){
							if(array_key_exists($catid, $usl_list)){
								$model->getState();
								$model->setState('filter.published', 1);
								$model->setState('filter.category_id', $catid);
								$model->setState('list.ordering', 'a.title');
								$model->setState('list.direction', 'ASC');
                                $articles = $model->getItems();
								$add_html = $usl_list[$catid].'<b></b><div class="flex_wrap">';
								foreach($articles as $art){
									if(!array_key_exists($art->id, $stock_prices))
										continue;
									if(isset($art->tags) && isset($art->tags->itemTags))
										$tags = array_column($art->tags->itemTags, 'title', 'tag_id');
									else $tags = array();
									foreach((array)$stock_prices[$art->id] as $stock_price){
										$title = $art->title;
										$tid = 0;
										$service_id = $art->id;
										if(count($stock_price) > 2 && $tid = $stock_price[2])
											if(array_key_exists($tid, $tags)){
												$title .= ' /'.$tags[$tid];
												$service_id = $service_id.'-'.$tid;
											}
											$pause = explode('.', (string)$stock_price[1]);
											$pause = (count($pause)==1) ? 0 : (int)$pause[1];
											$stock_price[1] = floor($stock_price[1]);
											$add_html .= '<div class="service__wrap"><p class="service__item"><a href="#" class="hdr">'.$title.'</a>';
											$add_html .= '<span class="time"><label>Время: </label><input name="time[]" value="'.$stock_price[1].'" type="hidden">'.$stock_price[1].' мин.</span>';
											$add_html .= '<span class="time2"><label>Перерыв: </label><input name="time2[]" value="'.$pause.'" type="hidden">'.$pause.' мин.</span></br>';
											$add_html .= '<span class="stock_price"><label>Акционная стоимость: </label><input type="hidden" name="stock_price[]" value="'.$stock_price[0].'">'.$stock_price[0].' руб.</span></br>';
											$add_html .= '<span class="old_price"><label>Цена без скидки: </label><input type="hidden" name="old_price[]" value="'.$stock_price[3].'">'.$stock_price[3].' руб.</span></br>';
											$add_html .= '<span class="about_stock"><label>Условия акции: </label><input type="hidden" name="about_stock[]" value="'.$stock_price[4].'">'.$stock_price[4].'</span>';
											$add_html .= '<i></i><input type="hidden" name="service_id[]" value="'.$service_id.'">';
											$add_html .= '</p></div>';
										}
									}
									echo '<label class="checkbox type_master_closed" data-id="'.$catid.'">';
									echo $add_html.'<div class="stock_key"></div></div></label>';
								}
							}
							echo '</fieldset>';

							echo '<input id="jform_stock_prices" type="hidden" name="jform[stock_prices]" value="'.str_replace('"', '', $field->value).'" />';
							break;
			////END CTOCK PRICES
							case 'work_day':
							$work_from = $form->getValue('work_from') ? $form->getValue('work_from') : '[0]';
							$work_from = json_decode(($work_from[0]=='[') ? $work_from : '['.$work_from.']');
							if(count($work_from)==1)
								$work_from = array_fill(0, 7, $work_from[0]);
							elseif(count($work_from)==count($field->value)){
								$work_from = array_replace(array_fill(0, 8, 0), array_combine($field->value, $work_from));
								array_shift($work_from);
							}

							$work_to = $form->getValue('work_to') ? $form->getValue('work_to') : '[0]';
							$work_to = json_decode(($work_to[0]=='[') ? $work_to : '['.$work_to.']');
							if(count($work_to)==1)
								$work_to = array_fill(0, 7, $work_to[0]);
							elseif(count($work_to)==count($field->value)){
								$work_to = array_replace(array_fill(0, 8, 0), array_combine($field->value, $work_to));
								array_shift($work_to);
							}

				$cal = $hdr = $lbl = ''; //print_r($work_from);
				$days = $field->value ?	$field->value : array();
				$week = array('MON','TUE','WED','THU','FRI','SAT','SUN');
				foreach(range(0,6) as $weekday){
					$hdr .= '<li>'.JText::_($week[$weekday]).'<input type="checkbox" name="jform[work_day][]"'
					.' value="'.($weekday+1).'" '.(in_array($weekday+1, $days) ? 'checked' :'').' /></li>';
					$cal .= '<div class="calendar__table-item"><ul>';
					foreach(range(0, 23.75, 0.25) as $hour){
						$empty = 'class="empty" ';
						if(in_array($weekday+1, $days))
							if(array_key_exists($weekday, $work_from) || array_key_exists($weekday, $work_to))
								if($hour >= $work_from[$weekday] && $hour < $work_to[$weekday])
									$empty = '';

								$cal .= '<li><a '.$empty.'data-day="'.($weekday+1).'" data-time="'.$hour.'"'
								.' href="#" title="'.JText::_($week[$weekday]).' '.floor($hour).':'
								.str_pad((($hour - floor($hour))*60), 2, '0').'"></a>'.'</li>';
							}
							$cal .= '</ul></div>';
						}

						echo '<div class="calendar__system" data-min="0">';
						echo '<div class="calendar__table-head">';
						echo '<ul>'.$hdr.'</ul>';
						echo '</div>';

						echo '<div class="table__calendar-left"><ul>';
						foreach(range(0, 23) as $hour)
							echo '<li>'.$hour.'.00<ul><li>.15</li><li>.30</li><li>.45</li></ul></li>';
						echo '</ul></div>';

						echo '<div class="calendar__table">'.$cal.'</div>';
						echo '</div>';
						break;
						case 'favorites':
						echo '<ul class="fav-list">';
						$field = $form->getField('favorites');
						$selected = $field->__get('value');
						if(!$selected || empty($selected))
							echo '<li>Список пуст</li>';
						else
							foreach($selected as $master_id){
								$user=JsnHelper::getUser($master_id);
								if($user->id){
									echo '<li><a href="'.$user->getLink().'">'.$user->getField('avatar', false);
									echo $user->firstname.' '.$user->lastname;
									echo '<input type="hidden" name="jform[favorites][]" value="'.$user->id.'">';
									echo '</a><span class="del_key" onclick="jQuery(this).parent().remove()"></span></li>';
								}
							}
							echo '</ul>';
							break;
							default:
							echo $form->getInput($field->fieldname);
						}
					}

					static function renderStatic($field, $form, $user)
					{
						switch($field->fieldname)
						{
			case 'portfolio_field': //print_r($field);
			echo '<div class="portfolio_field-group">';
			if($field->value){
				if(!is_array($field->value))
					$field->value=array($field->value);

				foreach($field->value as $img)
					echo '<div class="controls preview" style="background-image: url('.(($img[0]=='/') ? $img : '/'.$img).');"></div>';
			}
			echo '</div>';
			break;
			case 'prices':
			echo '<fieldset id="jform_vyberite_usl" class="readonly">';

			$spec_field = $form->getField('vyberite_spetsialnos');
			$usl_list = $spec_field->getOptions();
			$usl_list = array_column($usl_list, 'text', 'value');
			$selected = $spec_field->__get('value');
			$field->value = preg_replace('/(\w+):/i', '"\1":', $field->value);
			$prices = (array)json_decode($field->value);

			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
			$model = JModelLegacy::getInstance('Articles', 'ContentModel');

			if(empty($selected))
				echo 'Выберите специальность, чтобы  добавить услугу';
			else
				foreach($selected as $catid){
					if(array_key_exists($catid, $usl_list)){
						$model->getState();
						$model->setState('filter.published', 1);
						$model->setState('filter.category_id', $catid);
						$model->setState('list.ordering', 'a.title');
						$model->setState('list.direction', 'ASC');
						$articles = $model->getItems();
						$add_html = $usl_list[$catid].'<div class="flex_wrap">';
						foreach($articles as $art){
							if(!array_key_exists($art->id, $prices))
								continue;
							if(isset($art->tags) && isset($art->tags->itemTags))
								$tags = array_column($art->tags->itemTags, 'title', 'tag_id');
							else $tags = array();
							foreach((array)$prices[$art->id] as $price){
								$title = $art->title;
								$tid = 0;
								$service_id = $art->id;
								if(count($price) > 2 && $tid = $price[2])
									if(array_key_exists($tid, $tags)){
										$title .= ' /'.$tags[$tid];
										$service_id = $service_id.'-'.$tid;
									}

									$pause = explode('.', (string)$price[1]);
									$pause = (count($pause)==1) ? 0 : (int)$pause[1];
									$price[1] = floor($price[1]);
									$add_html .= '<div class="service__wrap"><p class="service__item" style="width: auto;min-width: 90%;">';
									$add_html .= '<span class="hdr">'.$title.'</span><span class="time"><label>Время:</label>'.$price[1].' мин.</span>';
									$add_html .= '<span class="time2"><label>Перерыв:</label>'.$pause.' мин.</span>';
									$add_html .= '<span class="price"><label>Стоимость:</label>'.$price[0].' руб.</span></p></div>';
								}
							}
							echo '<label class="checkbox type_master_open" data-id="'.$catid.'">';
							echo $add_html.'</div></label>';
						}
					}
					echo '</fieldset>';
					break;
		    ////STOCK PRICES
					case 'stock_prices':
					echo '<fieldset id="jform_stocks_servis" class="readonly">';

					$spec_field = $form->getField('vyberite_spetsialnos');
					$usl_list = $spec_field->getOptions();
					$usl_list = array_column($usl_list, 'text', 'value');
					$selected = $spec_field->__get('value');
					//$field->value = preg_replace('/(\w+):/i', '"\1":', $field->value);
                    $field->value = str_replace(array('["[', ']","[', ']"]'), array('[[', '],[', ']]'), str_replace(array(',', ']', '[', '{', ':'), array('","', '"]', '["', '{"', '":'), str_replace('"', '', $field->value)));
					$stock_prices = (array)json_decode($field->value);

					JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
					$model = JModelLegacy::getInstance('Articles', 'ContentModel');

					if(empty($selected))
						echo 'Выберите специальность, чтобы акционную добавить услугу';
					else
						foreach($selected as $catid){
							if(array_key_exists($catid, $usl_list)){
								$model->getState();
								$model->setState('filter.published', 1);
								$model->setState('filter.category_id', $catid);
								$model->setState('list.ordering', 'a.title');
								$model->setState('list.direction', 'ASC');
								$articles = $model->getItems();
								$add_html = $usl_list[$catid].'<div class="flex_wrap">';
								foreach($articles as $art){
									if(!array_key_exists($art->id, $stock_prices))
										continue;
									if(isset($art->tags) && isset($art->tags->itemTags))
										$tags = array_column($art->tags->itemTags, 'title', 'tag_id');
									else $tags = array();
									foreach((array)$stock_prices[$art->id] as $stock_price){
										$title = $art->title;
										$tid = 0;
										$service_id = $art->id;
										if(count($stock_price) > 2 && $tid = $stock_price[2])
											if(array_key_exists($tid, $tags)){
												$title .= ' /'.$tags[$tid];
												$service_id = $service_id.'-'.$tid;
											}
											$pause = explode('.', (string)$stock_price[1]);
											$pause = (count($pause)==1) ? 0 : (int)$pause[1];
											$stock_price[1] = floor($stock_price[1]);
											$add_html .= '<div class="service__wrap"><p class="service__item" style="width: auto;min-width: 90%;">';
											$add_html .= '<span class="hdr">'.$title.'</span><span class="time"><label>Время:</label>'.$stock_price[1].' мин.</span>';
											$add_html .= '<span class="time2"><label>Перерыв:</label>'.$pause.' мин.</span></br>';
											$add_html .= '<span class="stock_price"><label>Акционная стоимость: </label> '.$stock_price[0].' руб.</span></br>';
											$add_html .= '<span class="old_price"><label>Цена без скидки: </label> '.$stock_price[3].' руб.</span></br>';
											$add_html .= '<span class="about_stock"><label>Условия акции: </label> '.$stock_price[4].'</span></p></div>';
										}
									}
									echo '<label class="checkbox type_master_open" data-id="'.$catid.'">';
									echo $add_html.'</div></label>';
								}
							}
							echo '</fieldset>';
							break;
			////END STOCK PRICES
							case 'favorites':
							echo '<ul class="fav-list">';
							$field = $form->getField('favorites');
							$selected = $field->__get('value');
							if(!$selected || empty($selected))
								echo '<li>Список пуст</li>';
							else
								foreach($selected as $master_id){
									$user=JsnHelper::getUser($master_id);
									if($user->id){
										echo '<li><a href="'.$user->getLink().'">'.$user->getField('avatar', false);
										echo $user->firstname.' '.$user->lastname.'</a></li>';
									}
								}
								echo '</ul>';
								break;
								default:
								echo $user->getField($field, false);
							}
						}
					}
