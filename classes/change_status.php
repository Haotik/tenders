<?php

require_once("mysql.class.php");
$db = new MySQL();
if ($db->Error()) $db->Kill();

$tenders_setting = $db->QuerySingleRowArray("SELECT `value` FROM `settings` WHERE `name` = 'monthsarchive' ", MYSQL_ASSOC);

// Перемещение в архив
$tenders_inarchive = $db->QueryArray("SELECT `id` FROM `tenders` WHERE `status` = 2 AND `in_history` = 0 AND UNIX_TIMESTAMP(`end_date`) <= " . strtotime("-" . $tenders_setting['value'] . " months") , MYSQL_ASSOC);
if (!empty($tenders_inarchive))
{
	$str_tenders = "";
	foreach ($tenders_inarchive as $v) {
		$str_tenders .= $v['id'] . ",";
	}
	$str_tenders = substr($str_tenders, 0, -1);
	$db->Query("UPDATE `tenders` SET `status` = 3, `in_history` = 1 WHERE `id` IN (" . $str_tenders . ") ");
}

// Перемещение в текущие
$tenders_current = $db->QueryArray("SELECT `id` FROM `tenders` WHERE `begin_date` <= NOW() AND `end_date` >= NOW() AND `status` = 0 ", MYSQL_ASSOC);
if (!empty($tenders_current))
{
	$str_tenders = "";
	foreach ($tenders_current as $v) {
		$str_tenders .= $v['id'] . ",";
	}
	$str_tenders = substr($str_tenders, 0, -1);
	$db->Query("UPDATE `tenders` SET `status` = 1 WHERE `id` IN (" . $str_tenders . ") ");
}

// Перемещение в завершённые
$tenders_finished = $db->QueryArray("SELECT `id` FROM `tenders` WHERE `end_date` <= NOW() AND `status` IN (0, 1) ", MYSQL_ASSOC);
if (!empty($tenders_finished))
{
	$str_tenders = "";
	foreach ($tenders_finished as $v) {
		$str_tenders .= $v['id'] . ",";
	}
	$str_tenders = substr($str_tenders, 0, -1);
	$db->Query("UPDATE `tenders` SET `status` = 2 WHERE `id` IN (" . $str_tenders . ") ");
}
