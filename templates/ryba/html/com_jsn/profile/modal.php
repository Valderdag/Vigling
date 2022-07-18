<?php defined('_JEXEC') or die;

?>
		<!-----------------------------------------------------STOCKS------------------------------------------------------------------>
        <div class="modal fade" id="stocks" role="dialog" aria-labelledby="exampleModalLabel"  aria-hidden="true">
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
                                <div class="calendar__stock preload">
									<?php 
									$date = JFactory::getDate();									
									foreach(range(1, 14) as $day){?>
                                    <div class="calendar__stock-item">
										<span class="mas-date">
											<?php echo $date->format("d.m.Y");?>
											<b><?php echo mb_strtolower($date->format("D"))?>.</b>
										</span>
										<?php $wday = $date->format("w"); $wday = $wday ? $wday : 7; 
										if(array_key_exists($wday, $this->data->work) && ($day>1 || max($this->data->work[$wday]) > (float)date("G")+1)):?>
										<p class="stks-m">
											<?php foreach(range(0, 23) as $hour){
												if(!in_array($hour, $this->data->work[$wday]))
													continue;
												if($day==1 && ($hour <= (float)date("G")+1))
													continue;
												
												$val = $date->format("Y-m-d").' '.$hour;
												$reserved = false;
												if($this->reserved_time && !empty($this->reserved_time))
													if(in_array($val,$this->reserved_time))
														$reserved = true;
												
												$inp_id = str_replace(array('.', ' ', ':'), '-', $val);
												if(!$reserved){
													echo '<input type="radio" id="'.$inp_id.'-00" name="time" value="'.$val.':00'.'">';
													echo '<label for="'.$inp_id.'-00" class="btnstk-select'.$reserved.'">'.$hour.':00</label>';
												}
												else echo '<label class="btnstk-select reserved">'.$hour.':00</label>';
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
                                <h2>Запись на акционную услугу</h2>
                                <div class="stock__data">
                                    <span class="stock__data-time">Дата: <b id="stock_date"></b></span>
                                    <span class="stock__data-time">Время: <b id="stock_time"></b></span>
                                    <div class="stock__data-stock_price-wrap">
                                      <span>Стоимоть</span>
                                      </div>
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
							<?php echo JHtml::_('form.token'); ?>
						</form>
                    </div>
                </div>
            </div>
        </div>
