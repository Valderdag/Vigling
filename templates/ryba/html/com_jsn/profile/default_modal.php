<?php defined('_JEXEC') or die;?>
		<!-- Modal -->
        <div class="modal fade" id="zapis" role="dialog" aria-labelledby="zapisModalLabel"  aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
						<form id="order-form" method="POST" action="/">
                        <div class="screen screen1">
                            <div class="calc__body">
                                <h2>Выберите дату и время</h2>
                                <div class="calendar__master preload">
									<?php
                                    $db = JFactory::getDbo();
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
									 $date = JFactory::getDate();
                                   	foreach(range(1, 45) as $day){?>
                                    <div class="calendar__master-item">
										<span class="mas-date">
											<?php echo $date->format("d.m.Y");?>
											<b><?php echo mb_strtolower($date->format("D"))?>.</b>
										</span>
										<?php $wday = $date->format("w"); $wday = $wday ? $wday : 7;
										if(array_key_exists($wday, $this->data->work) && ($day>1 || max($this->data->work[$wday])  < $date->format("d.m.Y"))):?>
										<p class="btns-m">
											<?php
											$work_from = $this->user->getValue('work_from');
                                            $work_to = $this->user->getValue('work_to');
											
											$start = strtotime($work_from);
											$end = strtotime($work_to);
											
											$step = 15 * 60;
											$arr_work = array();
									    for($i = $start; $i <= $end; $i += $step){
										    $arr_work[] = date("H:i", $start);
											$start += $step;
								     	}
									    foreach($arr_work as $hour){
											$reserved = false;
											$val = $date->format("Y-m-d").' '.$hour;
										if(isset($this->reserved_time) && !empty($this->reserved_time_to)){
											$a1 = new ArrayIterator($this->reserved_time);
                                            $a2 = new ArrayIterator($this->reserved_time_to);
                                           
                                            $it = new MultipleIterator;
                                            $it->attachIterator($a1);
                                            $it->attachIterator($a2);

                                            foreach($it as $e) {
											$break_start = strtotime($e[0]);
											$break_end = strtotime($e[1]);
										    $arr_reserv = array();
									    	while($break_start <= $break_end){
												$arr_reserv = date("Y-m-d H:i", $break_start);
											    $break_start += $step;
											    if($val == $arr_reserv){
											    $reserved = true;
												}
										    }
										    }
								    	}
								        
                                       if(isset($this->stock_time) && !empty($this->stock_time_to)){
											$s1 = new ArrayIterator($this->stock_time);
                                            $s2 = new ArrayIterator($this->stock_time_to);
                                            
                                            $iter = new MultipleIterator;
                                            $iter->attachIterator($s1);
                                            $iter->attachIterator($s2);

                                            foreach($iter as $st) {
											$stock_start = strtotime($st[0]);
											$stock_end = strtotime($st[1]);
										    $arr_stock = array();
									    	while($stock_start <= $stock_end){
												$arr_stock = date("Y-m-d H:i", $stock_start);
											    $stock_start += $step;
											    if($val == $arr_stock){
											    $reserved = true;
											    }
										    }
										    }
								    	}	 											
												$inp_id = str_replace(array('.', ' ', ':'), '-', $val);
												if(!$reserved){
													echo '<input type="radio" id="'.$inp_id.'-00" name="time" value="'.$val.'">';
													echo '<label for="'.$inp_id.'-00" class="btn-select'.$reserved.'">'.$hour.'</label>';
												}
												else echo '<label class="btn-select reserved">'.$hour.'</label>';
						    		    }?>
                                        </p>
										<?php else:?>
                                        	<span class="line-no"></span>
                                        <?php endif;?>
                                    </div>
                                    <?php $date->modify('+1 DAY');
									}?>
                                </div>
                            </div>
                            <div class="calc__btn">
                                <div class="btn-next">Далее</div>
                                <button type="button" class="close__btn" data-dismiss="modal">Отмена</button>
                            </div>
                        </div>


                        <div class="screen screen2">
                            <div class="calc__body">
                                <h2>Запись</h2>
                                <div class="zapis__data">
                                    <span class="zapis__data-time">Дата: <b id="req_date"></b></span>
                                    <span class="zapis__data-time">Время: <b id="req_time"></b></span>
                                    <div class="zapis__data-price-wrap">
                                     
                                    </div>
                                    <b class="zapis__data-result"></b>
                                    <textarea name="note" class="zapis__data-textarea" placeholder="Комментарий (необязательно)"></textarea>
                                </div>
                                <div class="calc__btn">
                                    <div class="btn-next">Далее</div>
                                    <button class="close__btn" type="button" onclick="jQuery('.screen').hide();jQuery(this).closest('.screen').prev().show();" >Назад</button>
                                </div>
                            </div>

                        </div>
                        <div class="screen screen3">
                            <div class="calc__body">
                                <h2>Контактные данные</h2>
                                <div class="form__finish">
                                    <div class="form__finish-top control-group">
                                        <div class="form__finish-left controls">
                                            <input type="text" name="name" required value="<?php echo $this->current_user->get('name')?>" placeholder="Ваше имя" />
                                        </div>
                                        <div class="form__finish-right controls">
                                            <span>+7<img src="/templates/ryba/images/rus.png" /></span>
                                            <input type="text" name="telefon" required value="<?php echo $this->current_user->getValue('telefon')?>" placeholder="Телефон" />
                                        </div>
                                        <div class="clearFloat"></div>
                                    </div>
                                    <div class="days__btns">
                                        <span>Напомнить до:</span>
                                        <ul>
                                            <li>
												<input type="radio" id="remind_1" name="remind" value="1440">
                                                <label for="remind_1" class="btn_pluse"></label>
                                                1 день
                                            </li>
                                            <li>
												<input type="radio" id="remind_2" name="remind" value="720">
                                                <label for="remind_2" class="btn_pluse"></label>
                                                12 часов
                                            </li>
                                            <li>
												<input type="radio" id="remind_3" name="remind" value="360">
                                                <label for="remind_3" class="btn_pluse"></label>
                                                6 часов
                                            </li>
                                            <li>
												<input type="radio" id="remind_4" name="remind" value="60">
                                                <label for="remind_4" class="btn_pluse"></label>
                                                1 час
                                            </li>
                                            <li>
												<input type="radio" id="remind_5" name="remind" value="30">
                                                <label for="remind_5" class="btn_pluse"></label>
                                                30 минут
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="calc__btn">
                                    <div class="btn-next">Записаться</div>
                                    <button class="close__btn" type="button" onclick="jQuery('.screen').hide();jQuery(this).closest('.screen').prev().show();">Назад</button>
                                </div>
                            </div>
                        </div>
                        <div class="screen screen4">
							<div class="calc__body">
								<label class="succes-round">Ваша запись создана и передана в обработку</label>
								<div class="calc__btn">
									<div class="btn-fin" data-dismiss="modal">Закрыть</div>
                                </div>
							</div>
						</div>
							<input type="hidden" name="master_id" value="<?php echo (int)$this->data->id ?>" />
							<input id="zapis__data-s-id" type="hidden" name="svc_id" value="0" />
							<input id="zapis__data-t-id" type="hidden" name="tag_id" value="0" />
							<input id="zapis__data-s-nm" type="hidden" name="svc_name" value="" />
							<input id="zapis__data-price" type="hidden" name="price" value="" />
							<input id="time_sum" type="hidden" class="time_sum" name="time_sum" value="" />
							<?php echo JHtml::_('form.token'); ?>
						</form>
                    </div>
                </div>
            </div>
        </div>
<!-----------------------------------------------------STOCKS------------------------------------------------------------------>
        <div class="modal fade" id="stocks" role="dialog" aria-labelledby="stockModalLabel"  aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
						<form id="stock-form" method="POST" action="/">
                        <div class="screen screen1">
                            <div class="calc__body">
                                <h2>Выберите дату и время</h2>
                                <div class="calendar__master preload">
									<?php 
									$db = JFactory::getDbo();
                                    
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
									$date = JFactory::getDate();									
									foreach(range(1, 45) as $day){?>
                                    <div class="calendar__master-item">
										<span class="mas-date">
											<?php echo $date->format("d.m.Y");?>
											<b><?php echo mb_strtolower($date->format("D"))?>.</b>
										</span>
										<?php $wday = $date->format("w"); $wday = $wday ? $wday : 7; //var_dump(max($this->data->work[$wday]) > (float)date("G")+1);
										if(array_key_exists($wday, $this->data->work) && ($day>1 || max($this->data->work[$wday]) < $date->format("d.m.Y"))):?>
										<p class="btns-m" id="stock">
											<?php 
											$work_from = $this->user->getValue('work_from') ? $this->user->getValue('work_from') : '[0]';
                                            $work_to = $this->user->getValue('work_to') ? $this->user->getValue('work_to') : '[0]';
											
											$start = strtotime($work_from);
											$end = strtotime($work_to);
											
											$step = 15 * 60;
											$arr_work = array();
									    for($i = $start; $i <= $end; $i += $step){
										    $arr_work[] = date("H:i", $start);
											$start += $step;
								     	}
									    foreach($arr_work as $hour){
											$reserved = false;
											$val = $date->format("Y-m-d").' '.$hour;
								        if(isset($this->reserved_time) && !empty($this->reserved_time_to)){
											$a1 = new ArrayIterator($this->reserved_time);
                                            $a2 = new ArrayIterator($this->reserved_time_to);

                                            $it = new MultipleIterator;
                                            $it->attachIterator($a1);
                                            $it->attachIterator($a2);

                                            foreach($it as $e) {
											$break_start = strtotime($e[0]);
											$break_end = strtotime($e[1]);
										    $arr_reserv = array();
									    	while($break_start <= $break_end){
												$arr_reserv = date("Y-m-d H:i", $break_start);
											    $break_start += $step;
											    if($val == $arr_reserv){
											    $reserved = true;
											    }
										    }
										    }
								    	}	
                                        if(isset($this->stock_time) && !empty($this->stock_time_to)){
											$s1 = new ArrayIterator($this->stock_time);
                                            $s2 = new ArrayIterator($this->stock_time_to);
                                            
                                            $iter = new MultipleIterator;
                                            $iter->attachIterator($s1);
                                            $iter->attachIterator($s2);

                                            foreach($iter as $st) {
											$stock_start = strtotime($st[0]);
											$stock_end = strtotime($st[1]);
										    $arr_stock = array();
									    	while($stock_start <= $stock_end){
												$arr_stock = date("Y-m-d H:i", $stock_start);
											    $stock_start += $step;
											    if($val == $arr_stock){
											    $reserved = true;
											    }
										    }
										    }
								    	}		
                                        										
												$inp_id = str_replace(array('.', ' ', ':'), '-', $val);
												if(!$reserved){
													echo '<input type="radio" id="'.$inp_id.'-00" name="time" value="'.$val.'">';
													echo '<label for="'.$inp_id.'-00" class="btn-select'.$reserved.'">'.$hour.'</label>';
												}
												else echo '<label class="btn-select reserved">'.$hour.'</label>';
											}?>
                                        </p>
										<?php else:?>
                                        	<span class="line-no"></span>
                                        <?php endif;?>
                                    </div>
                                    <?php $date->modify('+1 DAY');
										}?>
                                </div>
                            </div>
                            <div class="calc__btn">
                                <div class="btn-next">Далее</div>
                                <button type="button" class="close__btn" data-dismiss="modal">Отмена</button>
                            </div>
                        </div>
                        <div class="screen screen2">
                            <div class="calc__body">
                                <h2>Запись на акцию</h2>
                                <div class="stock__data">
                                    <span class="stock__data-time">Дата: <b id="stk_date"></b></span>
                                    <span class="stock__data-time">Время: <b id="stk_time"></b></span>
                                    <div class="stock__data-stock_price-wrap"></div>
                                    <b class="stock__data-result" ></b>
                                    <textarea name="note" class="zapis__data-textarea" placeholder="Комментарий (необязательно)"></textarea>
                                </div>
                                <div class="calc__btn">
                                    <div class="btn-next">Далее</div>
                                    <button class="close__btn" type="button" onclick="jQuery('.screen').hide();jQuery(this).closest('.screen').prev().show();" >Назад</button>
                                </div>
                            </div>

                        </div>
                        <div class="screen screen3">
                            <div class="calc__body">
                                <h2>Контактные данные</h2>
                                <div class="form__finish">
                                    <div class="form__finish-top control-group">
                                        <div class="form__finish-left controls">
                                            <input type="text" name="name" required value="<?php echo $this->current_user->get('name')?>" placeholder="Ваше имя" />
                                        </div>
                                        <div class="form__finish-right controls">
                                            <span>+7<img src="/templates/ryba/images/rus.png" /></span>
                                            <input type="text" name="telefon" required value="<?php echo $this->current_user->getValue('telefon')?>" placeholder="Телефон" />
                                        </div>
                                        <div class="clearFloat"></div>
                                    </div>
                                </div>
                                <div class="calc__btn">
                                    <div class="btn-next">Записаться</div>
                                    <button class="close__btn" type="button" onclick="jQuery('.screen').hide();jQuery(this).closest('.screen').prev().show();">Назад</button>
                                </div>
                            </div>
                        </div>
                        <div class="screen screen4">
							<div class="calc__body">
								<label class="succes-round">Ваша запись создана и передана в обработку</label>
								<div class="calc__btn">
									<div class="btn-fin" data-dismiss="modal">Закрыть</div>
                                </div>
							</div>
						</div>
							<input type="hidden" name="master_id" value="<?php echo (int)$this->data->id ?>" />
							<input id="stock__data-s-id" type="hidden" name="svc_id" value="0" />
							<input id="stock__data-t-id" type="hidden" name="tag_id" value="0" />
							<input id="stock__data-s-nm" type="hidden" name="svc_name" value="" />
							<input id="stock__data-s_price" type="hidden" name="s_price" value="" />
							<input id="stock__data-o_price" type="hidden" name="o_price" value="" />
							<input id="stocks__data-a_stock" type="hidden" name="a_stock" value="" />
							<input id="time_sum" type="hidden" class="time_sum" name="time_sum" value="" />
							<?php echo JHtml::_('form.token'); ?>
						</form>
                    </div>
                </div>
            </div>
        </div>
       