<?php
/*
Template Name: Handler
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Order_ID']) && isset($_POST['Status']) && isset($_POST['Signature'])) {
	
	$Order_ID = $_POST['Order_ID'];
	$Status = $_POST['Status'];
	$Signature = $_POST['Signature'];
	
	$text = date('d.m.Y - H:i') . " Вызов скрипта:\n";
	$text .= "1) Получен POST запрос Uniteller об оплате заказа " . $Order_ID . "\n";
	
	// здесь нужна проверка на корректный пароль
	// нужен основной запрос Курл в адрес Юнителлер по статусу
	// дальше работаем только, если статус в системе "пэйд"
	
	global $wpdb;
	$order_post_ID = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_order_number' AND meta_value = %s", $Order_ID));
	
	// запрос в БД надо переделать под другую таблицу и Джойн
	
	$text .= "2) В БД найден номер поста: " . $order_post_ID . "\n";
	
	if ($order_post_ID) {
		
		$order = wc_get_order( $order_post_ID );
		
		if ($order) {
			$text .= "3) Создался Объект ордер с номером: " . $order->get_order_number() . "\n";
			$text .= "4) Статус ордера до - " . $order->get_status() . "\n";
				
			if( ! $order->has_status( 'processing' )) {
				// надо все возможные статусы здесь прописать
				
				$order->update_status( 'processing' );
				
				// нужно отправку письма по эл почте сделать об оплате 
				
				$format = "5) Статус ордера после - заказ с номером %d, статус оплаты - %s, статус заказа - %s\n";
				$text .= sprintf($format, $order->get_order_number(), $Status, $order->get_status());
			}
		}
	}
}
else {
	$text = date('d.m.Y - H:i') . "Нерабочий вызов скрипта..\n";
}

$filename = 'callback.txt';
file_put_contents($filename, $text, FILE_APPEND);

// нужно еще класс ошибок прописать, проставить реперные сообщения о возможных ошибках

?>