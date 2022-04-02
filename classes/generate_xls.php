<?php
// Выявляем все ошибки
error_reporting( E_ALL | E_NOTICE );

if (empty($_POST) || empty($_POST['tender_id']) || empty($_POST['user_id']))
{
	echo "error|Документ не сгенерирован. Не хватает данных.";
	exit();
}

$tender_id = (int)$_POST['tender_id'];
$user_id = (int)$_POST['user_id'];

require_once("./mysql.class.php");
$db = new MySQL();
if ($db->Error()) $db->Kill();

$user = $db->QuerySingleRowArray("SELECT `user_id`, `group_id` FROM `user_profiles` WHERE `user_id` = " . $user_id, MYSQL_ASSOC);

$tender = $db->QuerySingleRowArray("SELECT * FROM `tenders` WHERE `id` = " . $tender_id . ($user['group_id'] == 3 ? "" : " AND `user_id` = " . $user_id) . " ", MYSQL_ASSOC);

if (!empty($tender))
{
	// Подключаем класс
	include 'PHPXlsx.php';

	$lotes = $db->QueryArray("SELECT * FROM `tenders_lotes` WHERE `tender_id` = " . $tender_id, MYSQL_ASSOC);
	$results_lotes = $db->QueryArray("SELECT `t1`.*, `t2`.`name` FROM `tenders_results_lotes` `t1`, `user_profiles` `t2` WHERE `t1`.`tender_id` = " . $tender_id . " AND `t1`.`user_id`=`t2`.`user_id`", MYSQL_ASSOC);

	$newresults_lotes = $orgs = array();
	if (!empty($results_lotes))
	{
		foreach ($results_lotes as $key => $value) {
			$newresults_lotes[$value['user_id']][$value['lote_id']] = $value['value'];
			$orgs[$value['user_id']] = $value['name'];
		}
	}

//var_dump($lotes, $newresults_lotes, $orgs); exit;

	// Создаем и пишем в файл. Деструктор закрывает
	$w = new ExcelDocument( "itogi_" . (int)$tender['id'] . ".xlsx" );

	$w->create($lotes, $orgs, $newresults_lotes);

	// Перенос сгенерированного файла в другую папку
	if (copy("itogi_" . (int)$tender['id'] . ".xlsx", $_SERVER['DOCUMENT_ROOT'] . "/data/itogi/itogi_" . (int)$tender['id'] . ".xlsx"))
	{
		unlink("itogi_" . (int)$tender['id'] . ".xlsx");
	}

	echo "success|Документ успешно сгенерирован";
}
else
	echo "error|Документ не сгенерирован. Не хватает данных.";
