<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;


JFormHelper::loadFieldClass('checkboxes');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldCheckboxlist extends JFormFieldCheckboxes
{
	public $type = 'Checkboxlist';

	public $isNested = null;
	
	public $table = null;

	protected $comParams = null;

	/**
	 * Constructor
	 *
	 * @since  3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Load com_jsn config
		$this->comParams = JComponentHelper::getParams('com_jsn');
	}

	
	protected function getInput()
	{
		if($this->element['optioninline']==1) $inline='inline';
		else $inline='';
		$html=array();
		$version=new JVersion();
		if($version->RELEASE<'3.5'){
			$from=array('<label','</label>','<ul>','</ul>','<li','</li>');
			$to=array('<span','</span>','','','<label class="checkbox '.$inline.'"','</label>');
		}
		else{
			$from=array('class="checkbox"');
			$to=array('class="checkbox '.$inline.'"');
		}
		$html[]=str_replace($from,$to,parent::getInput());
		if(isset($this->element['readonly']) && $this->element['readonly']=='true') $html[]='<input type="hidden" value="1" name="'.$this->name.'"/>' ;
		return implode('', $html);
	}
	public function getOptions()
	{
		if(isset($this->element['dbopttable']) && !empty($this->element['dbopttable']) && isset($this->element['dboptvalue']) && !empty($this->element['dboptvalue']) && isset($this->element['dbopttext']) && !empty($this->element['dbopttext'])) // set the alias of your field (width this conditions the select type work normally for all field except for this field)
		{	
			$db		= JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($this->element['dboptvalue'].' AS value, '.$this->element['dbopttext'].' AS text')->from($this->element['dbopttable']);

			/*if(isset($this->element['dboptfiltercolumn']) && !empty($this->element['dboptfiltercolumn']) && isset($this->element['dboptfiltervalue']) && !empty($this->element['dboptfiltervalue']))
			{
				if(empty($this->value))
				{
					return parent::getOptions();
				}
				elseif(is_array($this->value))
				{
					$value=$this->value;
					foreach($value as &$val)
						$val=$db->quote($val);
					$value=implode(',',$value);
					$query->where($this->element['dboptvalue'].' IN ('.$value.')');
				}
				else{
					$query->where($this->element['dboptvalue'].' = '.$db->quote($this->value));
				}

			}
			
			else*/if(isset($this->element['dboptwhere']) && !empty($this->element['dboptwhere']))
			{
				JPluginHelper::importPlugin('content');
				$where= JHtml::_('content.prepare', $this->element['dboptwhere'], 'customwhere', 'com_finder.indexer');
				if(empty($this->value))
				{
					$query->where($where);
				}
				elseif(is_array($this->value))
				{
					$value=$this->value;
					foreach($value as &$val)
						$val=$db->quote($val);
					$value=implode(',',$value);
					$query->where($where.' OR '.$this->element['dboptvalue'].' IN ('.$value.')');
				}
				else{
					$query->where($where.' OR '.$this->element['dboptvalue'].' = '.$db->quote($this->value));
				}
			}
			
			$query->order('text');
			
			$db->setQuery($query);

			try
			{
				$options = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				return false;
			}
			
			foreach($options as &$option)
			{
				$option->text=JText::_($option->text);
				$option->checked = null;
			}

			$options = array_merge(parent::getOptions(), $options); // Merge any additional options in the fields params (you can remove this).

			return $options;
		}
		else 
		{
			return parent::getOptions();
		}
	}

	protected function getLayoutData()
	{
		$hasValue = (isset($this->value) && !empty($this->value));
		if($hasValue && is_object($this->value)) $this->value = (array)$this->value;
		$data = parent::getLayoutData();
		return $data;
	}

}
