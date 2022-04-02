<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Общие настройки
|--------------------------------------------------------------------------
*/
$config['engine_url'] = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST'] : 'http://tender.fpkinvest.ru';   // URL сайта, для рассылки по крону установим сами
$config['engine_title'] = "Электронная торговая площадка";      // Название сайта
$config['engine_admin_email'] = "etp@fpkinvest.ru";       // E-mail адрес, куда будут приходить сообщения от движка
//$config['engine_admin_email'] = "anatoly@adyn.ru";

/* End of file configsite.php */
/* Location: ./application/config/configsite.php */
