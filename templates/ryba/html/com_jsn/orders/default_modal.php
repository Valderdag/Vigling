<?php  
defined('_JEXEC') or die;
?>
<!-- Modal  THIS ORDER-->
<div class="modal fade" id="rpl" role="dialog" name="master" aria-labelledby="exampleModalLabel"   aria-hidden="false">
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
							<h2>Изменить время записи на услугу</h2>
							<div class="calendar__master preload"></div>
						</div>
						<div class="calc__btn">
							<div class="btn-next">Далее</div>
							<button type="button" class="close__btn" data-dismiss="modal">Отмена</button>
						</div>
					</div>


					<div class="screen screen2">
						<div class="calc__body">
							<h2>Новая запись</h2>
							<div class="zapis__data">
								<span class="zapis__data-time">Дата: <b id="req_date"></b></span>
								<span class="zapis__data-time">Время: <b id="req_time"></b></span>
								<div class="zapis__data-price-wrap"></div>
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
							<h2>Изменение записи</h2>
							<div class="form__finish">
							</div>
							<div class="calc__btn">
								<div class="btn-next">Изменить запись</div>
								<button class="close__btn" type="button" onclick="jQuery('.screen').hide();jQuery(this).closest('.screen').prev().show();">Назад</button>
							</div>
						</div>
					</div>
					<div class="screen screen4">
						<div class="calc__body">
							<label class="succes-round">Запись изменена</label>
							<div class="calc__btn">
								<div class="btn-fin" data-dismiss="modal">Закрыть</div>
							</div>
						</div>
					</div>
					<input type="hidden"  id="masid" class="masid" name="master_id" value="" />
					<input type="hidden"  id="userid" class="userid" name="user_id" value="" />
					<input id="zapis__data-s-id" type="hidden"  class="svcid" name="svc_id" value="" />
					<input id="zapis__data-t-id" type="hidden"  class="tagid" name="tag_id" value="" />
					<input id="zapis__data-s-nm" type="hidden"  class="svcname" name="svc_name" value=""/>
					<input id="time_sum" type="hidden" class="time_sum" name="time_sum" value="" />
				</form>
			</div>
		</div>
	</div>
</div>
<!-- Modal DELETE-->
<div class="modal fade" id="del" role="dialog" aria-labelledby="exampleModalLabel" tabindex="-1"  aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<h2>Отмена записи</h2>
				<form id="del-form" method="POST" action="/">
					<div class="del__btn">
						<div class="btn-del">Отменить запись</script></div>
					</div>

				</div>
				<input type="hidden"  class="masid" name="master_id" value="0" />
				<input id="del__data-time" type="hidden"  class="svctime" name="time" value="0" />
				<input id="del__data-t-id" type="hidden"  class="tagid" name="tag_id" value="0" />
				<input id="del__data-s-nm" type="hidden"  class="svcname" name="svc_name" value="" />
				<?php echo JHtml::_('form.token');?>
			</form>
		</div>
	</div>
</div>




