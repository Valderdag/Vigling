<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

$field_link=array('name','formatname','firstname','lastname','avatar','avatar_mini','username');

?>
<tr class="profile<?php echo substr(md5($this->user->username),0,10) ?>">
<?php if($this->params->def('col1_enable', 0)) : 
	$fields=$this->params->def('col1_fields', array());
?>
<td>

	
		<?php if(is_array($fields)) foreach($fields as $field) : ?>
			
				<div class="<?php echo $field ?>">
					
				<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
					<?php  echo $this->user->getField($field,true); ?>
				<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
					
				</div>
			
		<?php endforeach; ?>


</td>
<?php endif; ?>

<?php if($this->params->def('col2_enable', 0)) : 
	$fields=$this->params->def('col2_fields', array());
?>
<td>

	<?php if(is_array($fields)) foreach($fields as $field) : ?>
		
			<div class="<?php echo $field ?>">
				
			<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
				<?php  echo $this->user->getField($field,true); ?>
			<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
				
			</div>
		
	<?php endforeach; ?>

</td>
<?php endif; ?>

<?php if($this->params->def('col3_enable', 0)) : 
	$fields=$this->params->def('col3_fields', array());
?>
<td>

	<?php if(is_array($fields)) foreach($fields as $field) : ?>
		
			<div class="<?php echo $field ?>">
				
			<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
				<?php  echo $this->user->getField($field,true); ?>
			<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
				
			</div>
		
	<?php endforeach; ?>

</td>
<?php endif; ?>

<?php if($this->params->def('col4_enable', 0)) : 
	$fields=$this->params->def('col4_fields', array());
?>
<td>

	<?php if(is_array($fields)) foreach($fields as $field) : ?>
		
			<div class="<?php echo $field ?>">
				
			<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
				<?php  echo $this->user->getField($field,true); ?>
			<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
				
			</div>
		
	<?php endforeach; ?>

</td>
<?php endif; ?>

<?php if($this->params->def('col5_enable', 0)) : 
	$fields=$this->params->def('col5_fields', array());
?>
<td>

	<?php if(is_array($fields)) foreach($fields as $field) : ?>
		
			<div class="<?php echo $field ?>">
				
			<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
				<?php  echo $this->user->getField($field,true); ?>
			<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
				
			</div>
		
	<?php endforeach; ?>

</td>
<?php endif; ?>

<?php if($this->params->def('col6_enable', 0)) : 
	$fields=$this->params->def('col6_fields', array());
?>
<td>

	<?php if(is_array($fields)) foreach($fields as $field) : ?>
		
			<div class="<?php echo $field ?>">
				
			<?php if(in_array($field,$field_link)) : ?><a href="<?php echo $this->user->getLink($this->url_options); ?>"><?php endif; ?>
				<?php  echo $this->user->getField($field,true); ?>
			<?php if(in_array($field,$field_link)) : ?></a><?php endif; ?>
				
			</div>
		
	<?php endforeach; ?>

</td>
<?php endif; ?>
</tr>
<?php
$jinput = JFactory::getApplication()->input;
$service = $jinput->get('service', '');
$tag = $jinput->get('tag', '');
if ($service && $tag) {
    $prices = $this->user->getValue('prices');
    $prices = preg_replace('/(\w+):/i', '"\1":', $prices);
    $prices = (array)json_decode($prices);

    $db = JFactory::getDbo();
    $db->setQuery('SELECT id, title FROM #__tags WHERE id = '.$tag);
    $tags = $db->loadObjectList('id');

    $tags = array_column($tags, 'title', 'id');
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

            echo '<h2>'.$article->category_title.'</h2>';

            foreach($prices2 as $price) {
                $tag_id = (int)$price[2];

                if ($tag_id != $tag) {
                    continue;
                }

                $name = $article->title;

                if (array_key_exists($tag_id, $tags)) {
                    $name .= ' / '.$tags[$tag_id];
                }

                echo '<span>'.$name.'</span>&nbsp;&nbsp;<span>'.(int)$price[1].' мин</span>&nbsp;&nbsp;<span>от '.$price[0].' руб.</span>';
            }
        }
    }

    JError::setErrorHandling(E_ERROR, 'callback');
}
?>