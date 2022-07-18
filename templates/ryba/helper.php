<?php defined('_JEXEC') or die;

class TplRybaHelper
{
	public static function getAjax(){
		$app = JFactory::getApplication()->input;
		$master_id = $app->getInt('master');
		$db = JFactory::getDbo();

		$fields = array('work_day', 'work_from', 'work_to');
		$query = $db->getQuery(true);
		$query
		->select($db->quoteName($fields))
		->from($db->quoteName('#__jsn_users'))
		->where($db->quoteName('id') . ' = ' . $master_id);
		$db->setQuery($query);
		$work = $db->loadObject();

		$fields = array('time', 'time_to');
		$query = $db->getQuery(true);
		$query
		->select($db->quoteName($fields))
		->from($db->quoteName('#__jsn_orders'))
		->where($db->quoteName('master_id') . ' = ' . $master_id);
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		$fields = array('time', 'time_to');
		$query = $db->getQuery(true);
		$query
		->select($db->quoteName($fields))
		->from($db->quoteName('#__jsn_stocks'))
		->where($db->quoteName('master_id') . ' = ' . $master_id);
		$db->setQuery($query);
		$stocks = $db->loadObjectList();

		$order_date = array();
		$start_order = array();
		$end_order = array();
		foreach ($orders as $order) {
			$arr = explode(' ', $order->time);
			$arr2 = explode(' ', $order->time_to);
			$order_date[] = $arr[0];
			$start_order[] = $arr[1];
			$end_order[] = $arr2[1];
		}

		$stock_date = array();
		$start_stock = array();
		$end_stock = array();
		foreach ($stocks as $stock) {
			$arr = explode(' ', $stock->time);
			$arr2 = explode(' ', $stock->time_to);
			$stock_date[] = $arr[0];
			$start_stock[] = $arr[1];
			$end_stock[] = $arr2[1];
		}

		$result = array(
			'start_work' => $work->work_from,
			'end_work' => $work->work_to,
			'work_day' => json_decode($work->work_day),
			'order_date' => $order_date,
			'start_order' => $start_order,
			'end_order' => $end_order,
			'stock_date' => $stock_date,
			'start_stock' => $start_stock,
			'end_stock' => $end_stock
		);

							//print_r($result);

		echo json_encode($result);
		die();
	}
}