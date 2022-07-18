<?php defined('_JEXEC') or die;
//error_reporting(E_ALL);
//define( 'DS', DIRECTORY_SEPARATOR );
// Add Custom Fields
//if(class_exists('FieldsHelper')) FieldsHelper::prepareForm('com_users.user', $this->form, $this->data);
// Set Title
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$this->document->setTitle($this->document->title.' - '.JsnHelper::getFormatName($this->data));
$this->document->addScript('/templates/ryba/js/chosen.jquery.min.js');
$this->document->addScript('/templates/ryba/js/profile.js');
$this->form->setFieldAttribute('portfolio_field', 'default', 'default.png');

// Set Pathway
JFactory::getApplication()->getPathway()->addItem(JsnHelper::getFormatName($this->data));
// Load Events Dispatcher
$dispatcher	= JEventDispatcher::getInstance();
$this->user=JsnHelper::getUser($this->data->id);
$avatar=$this->form->getField('avatar');
//var_dump($this->form->getField('portfolio_field')); //if(validateForm('form.jsn-p-fields') jQuery('form.jsn-p-fields').submit()

?>
<!-- Main Container -->
<div class="jsn-p">
	<?php echo(implode(' ',$dispatcher->trigger('renderBeforeProfile',array($this->data,$this->config)))); ?>
	<div class="jsn-p-opt">
		<?php if (JFactory::getApplication()->input->get('back')=='1') : ?>
				<?php if(JFactory::getUser()->id == $this->data->id) $other_id=''; else $other_id='&user_id='.$this->data->id; ?> 
				<a class="btn btn-xs btn-default" href="#" onclick="window.history.back();return false;">
						<i class="jsn-icon jsn-icon-share"></i> <?php echo JText::_('COM_JSN_BACK'); ?></a>
		<?php endif; ?>
		<?php if (JFactory::getUser()->id == $this->data->id) : ?> 
		<span class="btn btn-xs btn-default btn-save" onclick="saveProfile();"><i class="fa fa-save"></i> <?php echo JText::_('Сохранить'); ?></span>
		<?php endif; ?>
		<a href="index.php?option=com_jsn&view=profile" class="btn btn-xs btn-default" ><i class="fa fa-close"></i> Отмена</a>
		<?php 
			echo(implode(' ',$dispatcher->trigger('renderProfileButtons',array($this->data,$this->config))));
		?>
	</div>

	<!-- Top Container -->
	<div class="jsn-p-top <?php echo ($avatar ? 'jsn-p-top-a' : ''); ?>">

		<!-- Before Fields Container -->
		<div class="jsn-p-before-fields">
				<?php 
					$registerdate=$this->form->getField('registerdate');
					$lastvisitdate=$this->form->getField('lastvisitdate');
					if(0) : //$registerdate || $lastvisitdate ) : ?>
						<div class="jsn-p-dates">
							<?php if($registerdate) : ?>
							<div class="jsn-p-date-reg">
								<b><?php echo JText::_('COM_JSN_MEMBER_SINCE'); ?></b> <?php echo $this->user->getField('registerdate'); ?>
							</div>
							<?php endif; ?>
							<?php if($lastvisitdate) : ?>
							<div class="jsn-p-date-last">
								<b><?php echo JText::_('COM_JSN_LASTVISITDATE'); ?></b> <?php echo $this->user->getField('lastvisitdate'); ?>
							</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php 
				echo(implode(' ',$dispatcher->trigger('renderBeforeFields',array($this->data,$this->config))));
				?>
		</div>		
	</div>

	<!-- Fields Container -->
	<form action="<?php echo JRoute::_('index.php?option=com_jsn&task=save'); ?>" class="jsn-p-fields" name="profileForm" method="post" class="form-validate form-horizontal well" enctype="multipart/form-data">
	<!-- Avatar Container -->
		<?php
			if($avatar) :
		?> 
			<div class="jsn-p-avatar av-edit">
				<?php
					echo JTplHelper::renderField($this->form->getField('avatar'), $this->form); //$this->user->getField('avatar');
				?>
			</div>
		<?php
			endif;
		?>

		<!-- Title Container -->
		<div class="jsn-p-title">
			<h3>
				<?php echo $this->user->getField('formatname'); ?>
			</h3>

			<?php if($this->config->get('status',1)) : ?>	
				<?php echo $this->user->getField('status'); ?>
			<?php endif; ?>
		</div>
	<?php
		$tabs=$dispatcher->trigger('renderTabs',array($this->data,$this->config)); 
		$fields_output=implode(' ',$dispatcher->trigger('renderTabBeforeFields',array($this->data,$this->config)));
		$fields_output.=$this->loadTemplate('fields');
		$fields_output.=$this->loadTemplate('params');
		$fields_output.=implode(' ',$dispatcher->trigger('renderTabAfterFields',array($this->data,$this->config)));
		if($this->config->get('profile_fg_tabs',1)) echo($fields_output);
		else echo('<fieldset><legend>'.JText::_('COM_JSN_PROFILE_INFO').'</legend><div>'.$fields_output.'</div></fieldset>');
	
		$titles=array();
		$contents=array();
	
		foreach($tabs as $tab)
		{
			if (is_object($tab[0]))
		    {
		        foreach ($tab as $tabobject)
		        {
		            $contents[]='<fieldset><legend>'.$tabobject->title.'</legend>'.$tabobject->content.'</fieldset>';
		        }
		    }
		    else
				$contents[]='<fieldset><legend>'.$tab[0].'</legend>'.$tab[1].'</fieldset>';
		}
		echo(implode(' ',$contents));?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<div class="jsn-p-bottom">
		<div class="jsn-p-after-fields">
			<?php 
				echo(implode(' ',$dispatcher->trigger('renderAfterFields',array($this->data,$this->config))));
			?>
		</div>
	</div>
	<div style="display:none;" id="del-images">
		<div>
			<h3>Вы действительно желаете удалить</h3>
			<div class="del-img">
				<img src="" alt="">
			</div>
			<div class="del-but d-flex jsn-p">
				<input type="button" class="btn btn-xs btn-default goDel" value="Да" data-url="">
				<input type="button" class="btn btn-xs btn-default" value="Нет" onclick="jQuery.fancybox.close();">
			</div>
		</div>	
	</div>
</div>
<?php echo(implode(' ',$dispatcher->trigger('renderAfterProfile',array($this->data,$this->config))));
      echo $this->loadTemplate('modal');
 ?>
