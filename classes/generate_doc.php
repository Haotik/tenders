<?php
// Выявляем все ошибки
error_reporting(E_ALL | E_NOTICE);
if (empty($_POST) || empty($_POST['tender_id']) || empty($_POST['user_id'])) {
    echo "error|Документ не сгенерирован. Не хватает данных.";
    exit();
}

$tender_id = (int)$_POST['tender_id'];
$user_id = (int)$_POST['user_id'];
$months = array(
    "01" => 'января',
    "02" => 'февраля',
    '03' => 'марта',
    '04' => 'апреля',
    '05' => 'мая',
    '06' => 'июня',
    '07' => 'июля',
    '08' => 'августа',
    '09' => 'сентября',
    '10' => 'октября',
    '11' => 'ноября',
    '12' => 'декабря'
);

require_once("./mysql.class.php");
$db = new MySQL();
if ($db->Error()) $db->Kill();

$user = $db->QuerySingleRowArray("SELECT `user_id`, `group_id` FROM `user_profiles` WHERE `user_id` = " . $user_id, MYSQL_ASSOC);

$tender = $db->QuerySingleRowArray("SELECT * FROM `tenders` WHERE `id` = " . $tender_id . ($user['group_id'] == 3 ? "" : " AND `user_id` = " . $user_id) . " ", MYSQL_ASSOC);
//  AND (`status` = 2 OR (`status` = 3 AND `in_history` = 1) )

if (!empty($tender)) {
    include 'PHPDocx.php';

    $w = new WordDocument("protocol_" . (int)$tender['id'] . ".docx");

    $total_start_sum = $db->QuerySingleRowArray("SELECT SUM(`start_sum`) as `sum`, `step_lot` FROM `tenders_lotes` WHERE `tender_id` = " . $tender_id, MYSQL_ASSOC);
    $users = $db->QueryArray("SELECT `t2`.`name`, `t1`.`total_sum`, `t1`.`leader`,`t1`.`comment` FROM `tenders_results` `t1`, `user_profiles` `t2` WHERE `t1`.`tender_id` = " . $tender_id . " AND `t1`.`user_id` = `t2`.`user_id`", MYSQL_ASSOC);
//    echo "<pre>";
//    print_r($users);die;

    $array_users = $leader = array();
    if (!empty($users)) {
        foreach ($users as $key => $value) {
            $array_users[$value['name']] = $value['total_sum'];
            if ($value['leader'] == 1)
                $leader = array('name' => $value['name'], 'total_sum' => $value['total_sum'],'comment' => $value['comment']);
        }
    }
    $customer = $db->QuerySingleRowArray("SELECT * FROM `user_profiles` WHERE `user_id` = {$tender['user_id']}", MYSQL_ASSOC);
    $tender_end_date = DateTime::createFromFormat('Y-m-d H:i:s', $tender['end_date']);

    $tender_lot = $db->QuerySingleRowArray("SELECT * FROM `tenders_lotes` WHERE `tender_id` = {$tender['id']}", MYSQL_ASSOC);

    $tender_begin_date = DateTime::createFromFormat('Y-m-d H:i:s', $tender['begin_date']);

    $participants = $db->QueryArray(
        "SELECT * FROM `tender_users` AS tu LEFT JOIN user_profiles u ON u.id = tu.user_id WHERE tu.`tender_id` = {$tender['id']}",
        MYSQL_ASSOC);

    $array_content = array(
        'TENDERID' => (int)$tender['id'],
        'DATE' => $tender_end_date->format('«d»') . " " . $months[$tender_end_date->format('m')] . " " . $tender_end_date->format('Y'),
        'TENDER_TITLE' => $tender['title'],
        'TENDER_DATE' => date("d.m.Y", strtotime($tender['begin_date'])),
        'TENDER_COST' => $total_start_sum['sum'],
        'TENDER_STEP' => $total_start_sum['step_lot'],
        'TENDER_VICTORY' => isset($leader['name']) ? $leader['name'] : '',
        'TENDER_VICTORY_COST' => number_format(isset($leader['total_sum']) ? $leader['total_sum'] : 0, 0, '', ' '),
        'CUSTOMER_NAME' => $customer['name'],
        "START_SUM" => str_replace('.', ' руб. ', $tender_lot['start_sum']) . ' коп.',
        "STEP_LOT" => str_replace('.', ' руб. ', $tender_lot['step_lot']) . ' коп.',
        "TENDER_INTERVAL" => "с " . $tender_begin_date->format('H:i d.m.Y') . " до " . $tender_end_date->format('H:i d.m.Y'),
        "PARTICIPANTS" => implode("", array_map(function ($item) {
            return "<w:p w:rsidR=\"00AA6E71\" w:rsidRDefault=\"00AA6E71\" w:rsidP=\"003F24C7\"><w:r><w:t>- " . $item['name'] . "</w:t></w:r></w:p>";
        }, $participants)),
        "TENDER_DESCRIPTION" => "<w:t>" . str_replace("<br />", "</w:t><w:br/><w:t>", nl2br($tender['description'])) . "</w:t>",
    );
        $array_content["VICTORY_REASON"] = "Не указано";
    
    if ($leader['comment'] != ''){
        $array_content["VICTORY_REASON"] = $leader["comment"];
    }

    $commission = array();
    $com = "";
    if (!empty($_POST['commission'])) {
        foreach ($_POST['commission'] as $key => $value) {
            $com .= $value['value'] . ",";
        }
        $com = substr($com, 0, -1);
        if (!empty($com)) {
            $comdb = $db->QueryArray("SELECT * FROM `tenders_commission` WHERE `id` IN (" . $com . ")", MYSQL_ASSOC);
            foreach ($comdb as $k => $v) {
                $commission[(int)$v['rank']][] = array('fio' => $v['fio'], 'post' => $v['post']);
            }
        }
    }

    $w->create($array_content, $array_users, $commission);

    // Перенос сгенерированного файла в другую папку
    if (copy("protocol_" . (int)$tender['id'] . ".docx", $_SERVER['DOCUMENT_ROOT'] . "/data/protocol/protocol_" . (int)$tender['id'] . ".docx")) {
        unlink("protocol_" . (int)$tender['id'] . ".docx");
    }

    echo "success|Документ успешно сгенерирован";
} else
    echo "error|Документ не сгенерирован. Не хватает данных.";
