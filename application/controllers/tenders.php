<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tenders extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->library('tank_auth');
        $this->load->library('ExporterTenders');
        $this->load->model('tenders_data', 'tenders');
        $this->load->model('uservisitedtenders');


        if (!$this->tank_auth->is_logged_in())
            redirect('');
    }

    /*** Главная страница ***/
    function index($url = 'current')
    {
        $selected_tag = null;
        if (isset($_GET['tag']) && is_numeric($_GET['tag'])) {
            $selected_tag = $_GET['tag'];
        }
        $data['all_tags'] = $this->tenders->get_all_tender_tags();

        $is_bidder = $this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() == 6;

        $data['selected_tag'] = $selected_tag ? $selected_tag : ($is_bidder ? 27 : null);

        if (!$is_bidder) {
            $data['all_tags'] = array_filter($data['all_tags'], function($item) {
                return $item['id'] != 27;
            });
        }
        
        $date_from = null;
        if (!empty($_GET['from'])) {
            $date = DateTime::createFromFormat('d.m.Y', $_GET['from']);
            if ($date != false) {
                $date_from = $date->format('Y-m-d 00:00:00');
            }
        }
        $date_to = null;
        if (!empty($_GET['to'])) {
            $date = DateTime::createFromFormat('d.m.Y', $_GET['to']);
            if ($date != false) {
                $date_to = $date->format('Y-m-d 23:59:59');
            }
        }

        $additional_params = array(
            'date_from' => $date_from,
            'date_to' => $date_to,
        );


        switch ($url) {
            case 'archive':
                $data['page_title'] = 'Аукционы в архиве';

                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $data['tenders_list'] = $this->tenders->get_tenders_by_archive($user_id, $selected_tag, $additional_params);
                break;
            case 'previous':
                $data['page_title'] = 'Предстоящие аукционы';

                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $data['tenders_list'] = $this->tenders->get_tenders_by_status(0, $user_id, $selected_tag, $additional_params);
                $data['cnt_lotes'] = $this->tenders->get_cnt_lotes();
                break;
            case 'finished':
                $data['page_title'] = 'Завершённые аукционы';

                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $data['tenders_list'] = $this->tenders->get_tenders_by_status(2, $user_id, $selected_tag, $additional_params);
                $data['cnt_lotes'] = $this->tenders->get_cnt_lotes();
                $data['best_summa'] = $this->tenders->get_best_summa();
                $data['cnt_results'] = $this->tenders->get_cnt_results();
                break;
            case 'current':
            default:
                $data['page_title'] = 'Текущие аукционы';

                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $data['tenders_list'] = $this->tenders->get_tenders_by_status(1, $user_id, $selected_tag, $additional_params);
                $data['cnt_lotes'] = $this->tenders->get_cnt_lotes();
                break;
        }

        foreach ($data['tenders_list'] as $value) {
            $data["tenders_categories"][] = $this->tenders->get_tenders_tags($value["id"]);
        }

        $this->template->view('tenders/list', $data);
    }

    /*** Добавление аукциона ***/
    function add()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $data['page_title'] = 'Добавление аукциона';
            $data['no_tender'] = FALSE;

            /*$tender_id = $this->tenders->get_last_tender_id();
            $data['tender_id'] = (int)$tender_id['id'] + 1;*/
            $data['tender_id'] = mt_rand(100000, 200000);

            $data['tender_options'] = $data['tender_lotes'] = NULL;
            $data['all_tags'] = $this->tenders->get_all_tender_tags();
            $data['all_users'] = $this->tenders->get_all_users();
            $data['group_id'] = $this->tank_auth->get_group_id();
            $this->template->view('tenders/add_form', $data);
        } else
            redirect('');
    }

    /*** Редактирование аукциона ***/
    function edit($tender_id = 0)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $data['page_title'] = 'Редактирование аукциона';
            $data['no_tender'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);

                $begin_time = DateTime::createFromFormat('Y-m-d H:i:s', $tender['begin_date']);
                $now = new DateTime;
                $diff = $now->diff($begin_time);
                if ($diff->invert == 0) {
                    if ($tender) {
                        $data['tender_detail'] = $tender;
                        $data['tender_options'] = $this->tenders->get_tenders_options((int)$tender_id);
                        $data['tender_lotes'] = $this->tenders->get_tenders_lotes((int)$tender_id);
                    } else
                        $data['no_tender'] = TRUE;
                } else {
                    $data['no_tender'] = TRUE;
                }
            }

            $data['tender_id'] = (int)$tender_id;
            $data['all_tags'] = $this->tenders->get_all_tender_tags();
            $tender_tags = array();
            $tags = $this->tenders->get_tenders_tags($tender_id);
            if ($tags != null) {
                foreach ($tags as $one) {
                    $tender_tags[] = $one['tag_id'];
                }
            }
            $data['tender_tags'] = $tender_tags;

            $data['all_users'] = $this->tenders->get_all_users();
            $tender_users = array();
            $users = $this->tenders->get_tenders_users($tender_id);
            if ($users != null) {
                foreach ($users as $one) {
                    $tender_users[] = $one['user_id'];
                }
            }
            $data['tender_users'] = $tender_users;

            $this->template->view('tenders/add_form', $data);
        } else
            redirect('');
    }

    /*** Просмотр аукциона ***/
    function show($tender_id = 0)
    {
        $this->load->helper('file');
        $this->load->model('commission_data', 'commission');

        if ($this->tank_auth->get_group_id() == 2 || $this->tank_auth->get_group_id() == 3) {
            $data['visited_users'] = $this->uservisitedtenders->getVisitedUsers($tender_id);
        }

        $data['page_title'] = 'Аукцион не найден';
        $data['no_tender'] = $data['game_tender'] = $data['tender_author'] = FALSE;
        $data['start_tender'] = TRUE;

        if ($tender_id < 0)
            $data['no_tender'] = TRUE;
        else {
            if ($this->tank_auth->get_group_id() == 2)
                $user_id = $this->tank_auth->get_user_id();
            else
                $user_id = 0;

            $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);
            if ($tender) {
                $original_user_id = $this->tank_auth->get_user_id();
                if ($tender['user_id'] == $original_user_id)
                    $data['tender_author'] = TRUE;

                $data['page_title'] = 'Аукцион «' . $tender['title'] . '»';
                $data['tender_detail'] = $tender;
                $data['tender_options'] = $this->tenders->get_tenders_options((int)$tender_id);
                $data['tender_lotes'] = $this->tenders->get_tenders_lotes((int)$tender_id);
                $data['tenders_documents'] = $this->tenders->get_tenders_documents((int)$tender_id);

                $orig_user_id = $this->tank_auth->get_user_id();

                // Допусловия и ставки по участникам
                $data['tender_options_user'] = $this->tenders->get_tenders_options_by_user((int)$tender_id, (int)$orig_user_id);
                $data['tender_lotes_user'] = $this->tenders->get_tenders_lotes_by_user((int)$tender_id, (int)$orig_user_id);

                // Проверяем, закончился аукцион?
                if (strtotime($tender['begin_date']) > time()) {
                    $data['start_tender'] = FALSE;
                }
                // Проверяем, закончился аукцион?
                if (strtotime($tender['end_date']) < time()) {
                    $data['game_tender'] = TRUE;
                }

                // Период автообновления страницы тендера во время игры
                $autorefresh = $this->tenders->get_settings("autorefresh");
                $data['autorefresh'] = $autorefresh["value"];

                // Выдергиваем результаты по схеме Ebay
                if ($tender['type_auction'] == 2) {
                    // Самые минимальные ставки
                    $data['tender_results_lotes_ebay'] = $this->tenders->get_tender_results_lotes_best_min((int)$tender_id);
                    // Самые дорогие ставки
                    $data['tender_results_lotes_ebay_expensive'] = $this->tenders->get_tender_results_lotes_expensive((int)$tender_id);

                    if ((($this->tank_auth->get_group_id() == 2 && $data['tender_author'] == FALSE) || $this->tank_auth->get_group_id() == 1) && $data['game_tender'] == TRUE) {
                        $data['tender_results_lotes'] = $this->tenders->get_tender_results_lotes_best_min((int)$tender_id);
                    }
                }

                $data['tender_members'] = $this->tenders->get_tenders_results((int)$tender_id);
                $tender_commission = $this->commission->get_tenders_commission();
                $data['tender_commission'] = array(1 => array(), 2 => array(), 3 => array());
                if (!empty($tender_commission)) {
                    foreach ($tender_commission as $key => $value) {
                        $data['tender_commission'][(int)$value['rank']][] = $value;
                    }
                }

                if (($this->tank_auth->get_group_id() == 2 && $data['tender_author'] == TRUE) || $this->tank_auth->get_group_id() == 3) {
                    $data['users_list'] = $this->tank_auth->users_list();
                    $data['tender_results_options'] = $this->tenders->get_tenders_options_by_user((int)$tender_id);
                }

                // Самые лучшие цены
                if ($tender['type_auction_plus'] == 1) // если аукцион в плюс
                    $data['tender_results_lotes'] = $this->tenders->get_tender_results_lotes_best_max((int)$tender_id);
                else
                    $data['tender_results_lotes'] = $this->tenders->get_tender_results_lotes_best_min((int)$tender_id);
            } else
                $data['no_tender'] = TRUE;
        }

        $data['tender_id'] = (int)$tender_id;
        $data['completed_protocol_documents'] = $this->tenders->get_tenders_completed_protocol_documents((int)$tender_id);
        $data['success_upload'] = '';
        if (isset($_POST['sended'])) {
            $directory = $_SERVER['DOCUMENT_ROOT'] . "/upload/completed_protocol/" . $tender_id . "/";
            @mkdir($directory, 0777, true);
            if (copy($_FILES['protocol_file']['tmp_name'], $directory . $_FILES['protocol_file']['name'])) {
                $data['success_upload'] = '<span style=\'color:green\'>Файл загружен</span>';
                $this->tenders->set_tenders_documents($tender_id, $_FILES['protocol_file']['name'], $_FILES['protocol_file']['size']);
            } else {
                $data['success_upload'] = "<span style='color:red'>Ошибка загрузки файла</span>";
            }
        }

        $tender_tags = array();
        $tags = $this->tenders->get_tenders_tags_caption($tender_id);

        if ($tags != null) {
            foreach ($tags as $one) {
                $tender_tags[] = $one['caption'];
            }
        }
        $data['tender_tags'] = $tender_tags;

        $tender_users = array();
        $users = $this->tenders->get_tenders_users_caption($tender_id);
        $allowed_users = array();
        if ($users != null) {
            foreach ($users as $one) {
                $tender_users[] = $one['name'];
                $allowed_users[] = $one['user_id'];
            }
        }
        $data['tender_users'] = $tender_users;
        $data['allowed_users'] = $allowed_users;
        if ($this->tank_auth->get_group_id() == 1 &&
            (empty($allowed_users) || (!empty($allowed_users) && in_array($this->tank_auth->get_user_id(), $allowed_users)))) {
            $params = array(
                'user_id' => $this->tank_auth->get_user_id(),
                'tender_id' => $tender_id,
            );
            $visited = $this->uservisitedtenders->findAllByAttributes($params);
            if (!$visited) {
                $this->uservisitedtenders->create($params);
            }
        }
        $result_kp_files = [];
        $all_kp_files = scandir($_SERVER['DOCUMENT_ROOT'] . "/data/kp_tenders");
        unset($all_kp_files[0]); // .
        unset($all_kp_files[1]); // ..
        foreach ($all_kp_files as $kp_file){
            $file_data = explode('_',$kp_file);
            $result_kp_files[$file_data[1]]["user"] = $file_data[3];
            $result_kp_files[$file_data[1]]["file"] = $kp_file;
        }

        $data["kp_files"][0] = $result_kp_files[$tender_id];

        $this->template->view('tenders/view_auction', $data);
    }

    /*** Запуск аукциона ? Процесс работы скорее ***/
    function run()
    {
        // Валидация лотов для AJAX-запроса
        if (!empty($_GET['fieldId'])) {
            $lot_id = str_replace("tender_lot_", "", $_GET['fieldId']);
            $lot_id = (int)$lot_id;

            $value = $_GET['fieldValue'];

            $lot = $this->tenders->get_lotes_by_id($lot_id);

            if (!empty($lot)) {
                $tender = $this->tenders->get_tenders_by_id((int)$lot['tender_id']);
                $user_id = $this->tank_auth->get_user_id();

                if ($tender['type_auction_plus'] == 1) {
                    // Аукцион в плюс
                    $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_max((int)$lot['tender_id']);

                    $arrayToJs = array();
                    $max_price = 0;
                    if ($tender['type_auction'] == 2) {
                        if (!empty($tender_results_lotes[$lot_id]) && (float)$tender_results_lotes[$lot_id]['best_value'] <= (float)$lot['step_lot'])
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }
                    if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                        if (!empty($tender_results_lotes[$lot_id]))
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }

                    // Проверка ставок: 1 усл. - "Стандартная" и "Открытые торги", 2 усл. - "Ставка не меньше шага" и "Открытые торги", 3 усл. - "механизм «eBay»"
                    if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && $lot['start_sum'] <= floatval($value)) ||
                        ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price <= floatval($value))) ||
                        ($tender['type_auction'] == 2 && $max_price <= floatval($value))
                    ) {
                        // Если аукцион скандинавский, проверяем время до окончания тендера
                        if ($tender['type_auction_scandinavia'] == 1 && ceil((strtotime($tender['end_date']) - time()) / 60) <= $tender['tender_minute_end']) {
                            // Увеличиваем время окончания тендера
                            $data_tender['end_date'] = date("Y-m-d H:i:s", strtotime($tender['end_date']) + ($tender['scan_minute'] * 60));
                            $this->tenders->update_tender($data_tender, (int)$lot['tender_id']);
                        }

                        // Записываем лоты
                        $this->tenders->set_tenders_lotes(array($lot_id => floatval($value)), (int)$lot['tender_id'], (int)$user_id);

                        // Считаем результаты
                        $this->tenders->set_tenders_results((int)$lot['tender_id'], (int)$user_id);

                        // Определяем победителя
                        $this->tenders->set_tenders_leader((int)$lot['tender_id']);

                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = true;
                    } else {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = false;
                    }

                } elseif($tender['type_auction'] == 3){
                    $arrayToJs[0] = 'tender_lot_' . $lot_id;
                    $arrayToJs[1] = true;
                } else {
                    $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_min((int)$lot['tender_id']);

                    $arrayToJs = array();
                    $max_price = 0;
                    if ($tender['type_auction'] == 2) {
                        if (!empty($tender_results_lotes[$lot_id]) && (float)$tender_results_lotes[$lot_id]['best_value'] >= (float)$lot['step_lot'])
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] - (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] - (float)$lot['step_lot'];
                    }
                    if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                        if (!empty($tender_results_lotes[$lot_id]))
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }

                    // Проверка ставок: 1 усл. - "Стандартная" и "Открытые торги", 2 усл. - "Ставка не меньше шага" и "Открытые торги", 3 усл. - "механизм «eBay»"
                    if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && $lot['start_sum'] >= floatval($value)) ||
                        ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price >= floatval($value))) ||
                        ($tender['type_auction'] == 2 && $max_price >= floatval($value))
                    ) {
                        // Если аукцион скандинавский, проверяем время до окончания тендера
                        if ($tender['type_auction_scandinavia'] == 1 && ceil((strtotime($tender['end_date']) - time()) / 60) <= $tender['tender_minute_end']) {
                            // Увеличиваем время окончания тендера
                            $data_tender['end_date'] = date("Y-m-d H:i:s", strtotime($tender['end_date']) + ($tender['scan_minute'] * 60));
                            $this->tenders->update_tender($data_tender, (int)$lot['tender_id']);
                        }

                        // Записываем лоты
                        $this->tenders->set_tenders_lotes(array($lot_id => floatval($value)), (int)$lot['tender_id'], (int)$user_id);

                        // Считаем результаты
                        $this->tenders->set_tenders_results((int)$lot['tender_id'], (int)$user_id);

                        // Определяем победителя
                        $this->tenders->set_tenders_leader((int)$lot['tender_id']);

                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = true;
                    } else {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = false;
                    }
                }
            }

            echo $this->json_encode($arrayToJs);
        } else {
            // Запись ставок в базу
            $tender_id = (int)$this->input->post('tender_id');
            $data['no_tender'] = $data['game_tender'] = FALSE;


            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                $user_id = $this->tank_auth->get_user_id();
                $tender = $this->tenders->get_tenders_by_id($tender_id);

                if ($tender) {
                    // Проверяем, закончился аукцион?
                    if (strtotime($tender['end_date']) < time()) {
                        $data['game_tender'] = TRUE;
                    } else {

                        if ($tender['type_auction_plus'] == 1) {

                            // Аукцион в плюс
                            $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_max((int)$tender_id);

                            foreach ($this->input->post('tender_lot') as $key => $value) {
                                $lot = $this->tenders->get_lotes_by_id($key);
                                $max_price = 0;
                                if ($tender['type_auction'] == 2) {
                                    if (!empty($tender_results_lotes[$key]) && (float)$tender_results_lotes[$key]['best_value'] <= (float)$lot['step_lot'])
                                        $max_price = (float)$tender_results_lotes[$key]['best_value'] + (float)$lot['step_lot'];
                                    else
                                        $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                                }
                                if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$key]))
                                        $max_price = (float)$tender_results_lotes[$key]['best_value'] + (float)$lot['step_lot'];
                                    else
                                        $max_price = (float)$lot['start_sum'];
                                }
                                if ($tender['type_rate'] == 1 && $tender['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$key]))
                                        $max_price = (float)$tender_results_lotes[$key]['best_value'] + 1;
                                    else
                                        $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                                }
                                // Проверка ставок: 1 усл. - "Стандартная" и "Открытые торги",
                                // 2 усл. - "Ставка не меньше шага" и "Открытые торги",
                                // 3 усл. - "механизм «eBay»"
                                if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && $max_price <= floatval($value)) ||
                                    ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price <= floatval($value))) ||
                                    ($tender['type_auction'] == 2 && $max_price <= floatval($value))
                                ) {

                                    $checkBestValue = $this->tenders->get_tender_results_lotes_best_max($tender_id);

                                    if (isset($checkBestValue[$key]) &&
                                        $checkBestValue[$key]['best_value'] >= $value &&
                                        $checkBestValue != null) {
                                        $data['no_tender'] = TRUE;
                                        break;
                                    }

                                    // Если аукцион скандинавский, проверяем время до окончания тендера
                                    if ($tender['type_auction_scandinavia'] == 1 && ceil((strtotime($tender['end_date']) - time()) / 60) <= $tender['tender_minute_end']) {
                                        // Увеличиваем время окончания тендера
                                        $data_tender['end_date'] = date("Y-m-d H:i:s", strtotime($tender['end_date']) + ($tender['scan_minute'] * 60));
                                        $this->tenders->update_tender($data_tender, $tender_id);
                                    }


                                    // Записываем лоты
                                    $this->tenders->set_tenders_lotes(array($key => floatval($value)), (int)$tender_id, (int)$user_id);

                                    // Считаем результаты
                                    $this->tenders->set_tenders_results((int)$tender_id, (int)$user_id);

                                    // Определяем победителя
                                    $this->tenders->set_tenders_leader((int)$tender_id);

                                    // Отправка письма подписчикам
                                    $this->_send_email('updatetender', 'Изменения в аукционе', array('tender_id' => $tender_id));
                                } else {
                                    $data['no_tender'] = TRUE;
                                    break;
                                }
                            }

                        } else {

                            //$tender_results_lotes = $this->tenders->get_tender_results_lotes_best_min((int)$lot['tender_id']);
                            $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_min($tender_id);

                            foreach ($this->input->post('tender_lot') as $key => $value) {
                                if ($value < 0) {
                                    $value = abs($value);
                                }
                                $lot = $this->tenders->get_lotes_by_id($key);
                                $max_price = 0;
                                if ($tender['type_auction'] == 2) {
                                    if (!empty($tender_results_lotes[$key]) && (float)$tender_results_lotes[$key]['best_value'] <= (float)$lot['step_lot']) {
                                        $max_price = (float)$tender_results_lotes[$key]['best_value'] - (float)$lot['step_lot'];
                                    } else {
                                        $max_price = (float)$lot['start_sum'] /*- (float)$lot['step_lot']*/
                                        ;
                                    }
                                }

                                if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                                    if (!empty($tender_results_lotes[$key]))
                                        $max_price = (float)$tender_results_lotes[$key]['best_value'] + (float)$lot['step_lot'];
                                    else
                                        $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                                }
                                if ($tender['type_auction'] == 2) {
                                    if ($tender_results_lotes == null) {
                                        $max_price = (float)$lot['start_sum'];
                                    } else {
                                        $max_price = (float)$tender_results_lotes[$lot['id']]['best_value'] - (float)$lot['step_lot'];
                                    }
                                }
                                // Проверка ставок:
                                // 1 усл. - "Стандартная" и "Открытые торги",
                                // 2 усл. - "Ставка не меньше шага" и "Открытые торги",
                                // 3 усл. - "механизм «eBay»"


                                if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && $lot['start_sum'] > floatval($value) 
                                    || 
                                    (floatval($value) == $lot['start_sum']) && $tender_results_lotes == null) 
                                    ||
                                    ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price >= floatval($value))) 
                                    ||
                                    ($tender['type_auction'] == 2 && $max_price >= floatval($value))
                                ) {
                                    $checkBestValue = $this->tenders->get_tender_results_lotes_best_min($tender_id);
                                    if (isset($checkBestValue[$key]) &&
                                        $checkBestValue[$key]['best_value'] <= $value &&
                                        $checkBestValue != null) {
                                        $data['no_tender'] = TRUE;
                                        break;
                                    }
                                    // Если аукцион скандинавский, проверяем время до окончания тендера
                                    if ($tender['type_auction_scandinavia'] == 1 && ceil((strtotime($tender['end_date']) - time()) / 60) <= $tender['tender_minute_end']) {
                                        // Увеличиваем время окончания тендера
                                        $data_tender['end_date'] = date("Y-m-d H:i:s", strtotime($tender['end_date']) + ($tender['scan_minute'] * 60));
                                        $this->tenders->update_tender($data_tender, $tender_id);
                                    }


                                    // Записываем лоты
                                    $this->tenders->set_tenders_lotes(array($key => floatval($value)), (int)$tender_id, (int)$user_id);

                                    // Считаем результаты
                                    $this->tenders->set_tenders_results((int)$tender_id, (int)$user_id);

                                    // Определяем победителя
                                    $this->tenders->set_tenders_leader((int)$tender_id);

                                    // Отправка письма подписчикам
                                    $this->_send_email('updatetender', 'Изменения в аукционе', array('tender_id' => $tender_id));

                                } else {
                                    $data['no_tender'] = TRUE;
                                    break;
                                }
                            }

                        }

                    }
                } else
                    $data['no_tender'] = TRUE;
            }

            if ($tender['type_auction'] == 3) {
                    // Записываем лоты
                    $this->tenders->set_tenders_lotes(array($key => floatval($value)), (int)$tender_id, (int)$user_id);

                    // Считаем результаты
                    $this->tenders->set_tenders_results((int)$tender_id, (int)$user_id);

                    // Определяем победителя
                    $this->tenders->set_tenders_leader((int)$tender_id);

                    // Отправка письма подписчикам
                    $this->_send_email('updatetender', 'Изменения в аукционе', array('tender_id' => $tender_id));
                    $data['no_tender'] = FALSE;
                }

            if ($data['no_tender'] == TRUE || $data['game_tender'] == TRUE){
                echo "error|Ваши ставки не приняты".json_encode($data);
            }
            else
                echo "success|Ваши ставки приняты";
        }

        return TRUE;
    }


    /*** Проверка ставок по аукциону ***/
    function check()
    {
        // Валидация лотов для AJAX-запроса
        if (!empty($_GET['fieldId'])) {
            $lot_id = str_replace("tender_lot_", "", $_GET['fieldId']);
            $lot_id = (int)$lot_id;
            $value = $_GET['fieldValue'];
            if ($value < 0) {
                $value = abs($value);
            }

            $lot = $this->tenders->get_lotes_by_id($lot_id);

            if (!empty($lot)) {
                $tender = $this->tenders->get_tenders_by_id((int)$lot['tender_id']);
                $user_id = $this->tank_auth->get_user_id();

                if ($tender['type_auction_plus'] == 1) {
                    // Аукцион в плюс
                    $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_max((int)$lot['tender_id']);

                    $arrayToJs = array();
                    $max_price = 0;
                    if ($tender['type_auction'] == 2) {
                        if (!empty($tender_results_lotes[$lot_id])) {
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        } else {
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                        }
                    }

                    if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                        if (!empty($tender_results_lotes[$lot_id]))
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }
                    if ($tender['type_rate'] == 1 && $tender['type_auction'] == 1) {
                        if (!empty($tender_results_lotes[$lot_id]))
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + 1;
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }
                    // Проверка ставок: 1 усл. - "Стандартная" и "Открытые торги",
                    // 2 усл. - "Ставка не меньше шага" и "Открытые торги",
                    // 3 усл. - "механизм «eBay»"

                    if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && $max_price <= floatval($value)) ||
                        ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price <= floatval($value))) ||
                        ($tender['type_auction'] == 2 && $max_price <= floatval($value))
                    ) {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = true;
                    } else {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = false;
                    }

                } else {
                    $tender_results_lotes = $this->tenders->get_tender_results_lotes_best_min((int)$lot['tender_id']);
                    $arrayToJs = array();
                    $max_price = 0;
                    if ($tender['type_auction'] == 2) {
                        if (!empty($tender_results_lotes[$lot_id]) && (float)$tender_results_lotes[$lot_id]['best_value'] >= (float)$lot['step_lot'])
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] - (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] - (float)$lot['step_lot'];
                    }
                    if ($tender['type_rate'] == 2 && $tender['type_auction'] == 1) {
                        if (!empty($tender_results_lotes[$lot_id]))
                            $max_price = (float)$tender_results_lotes[$lot_id]['best_value'] + (float)$lot['step_lot'];
                        else
                            $max_price = (float)$lot['start_sum'] + (float)$lot['step_lot'];
                    }

                    // Проверка ставок: 1 усл. - "Стандартная" и "Открытые торги", 2 усл. - "Ставка не меньше шага" и "Открытые торги", 3 усл. - "механизм «eBay»"


                    if ($tender['type_auction'] == 2) {
                        if ($tender_results_lotes == null) {
                            $max_price = (float)$lot['start_sum'] - (float)$lot['step_lot'];
                        } else {
                            $max_price = (float)$tender_results_lotes[$lot['id']]['best_value'] + (float)$lot['step_lot'];
                        }
                    } else {
                        if ($tender_results_lotes == null) {
                            $best_value = (float)$lot['start_sum'] - (float)$lot['step_lot'];
                        } else {
                            $best_value = $tender_results_lotes[$lot['id']]['best_value'];
                        }
                    }
                    if (($tender['type_rate'] == 1 && $tender['type_auction'] == 1 && ($best_value > floatval($value) || (floatval($value) == $lot['start_sum']) && $tender_results_lotes == null)) ||
                        ($tender['type_rate'] == 2 && $tender['type_auction'] == 1 && ($max_price >= floatval($value))) ||
                        ($tender['type_auction'] == 2 && $max_price >= floatval($value))
                    ) {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = true;
                    } else {
                        $arrayToJs[0] = 'tender_lot_' . $lot_id;
                        $arrayToJs[1] = false;
                    }
                }
            }
            echo $this->json_encode($arrayToJs);
        } else {
            // Запись ставок в базу
            $tender_id = (int)$this->input->get('tender_id');
            $data['no_tender'] = $data['game_tender'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                $user_id = $this->tank_auth->get_user_id();
                $tender = $this->tenders->get_tenders_by_id($tender_id);
                if ($tender) {
                    // Проверяем, закончился аукцион?
                    if (strtotime($tender['end_date']) < time()) {
                        $data['game_tender'] = TRUE;
                    } else {
                        // Если аукцион скандинавский, проверяем время до окончания тендера
                        if ($tender['type_auction_scandinavia'] == 1 && ceil((strtotime($tender['end_date']) - time()) / 60) <= $tender['tender_minute_end']) {
                            // Увеличиваем время окончания тендера
                            $data_tender['end_date'] = date("Y-m-d H:i:s", strtotime($tender['end_date']) + ($tender['scan_minute'] * 60));
                            $this->tenders->update_tender($data_tender, $tender_id);
                        }

                        // Записываем лоты
                        $lot_data = [
                            "value"=>$this->input->get('tender_lot'),
                            "seller_name" => $this->input->get('product_name')
                        ];
                        $this->tenders->set_tenders_lotes($lot_data, (int)$tender_id, (int)$user_id);

                        // Считаем результаты
                        $this->tenders->set_tenders_results((int)$tender_id, (int)$user_id);

                        // Определяем победителя
                        $this->tenders->set_tenders_leader((int)$tender_id);

                        // Отправка письма подписчикам
                        $this->_send_email('updatetender', 'Изменения в аукционе', array('tender_id' => $tender_id));
                    }
                } else
                    $data['no_tender'] = TRUE;
            }

            if ($data['no_tender'] == TRUE || $data['game_tender'] == TRUE)
                echo "error|Ваши ставки не приняты";
            else
                echo "success|Ваши ставки приняты";
        }

        return TRUE;
    }

    /*** Запись допусловий к аукциону ***/
    function save_terms()
    {
        // Запись допусловий в базу
        $tender_id = (int)$this->input->post('term_tender_id');
        $data['no_tender'] = $data['game_tender'] = FALSE;

        if ($tender_id < 0)
            $data['no_tender'] = TRUE;
        else {
            $user_id = $this->tank_auth->get_user_id();
            $tender = $this->tenders->get_tenders_by_id($tender_id);
            if ($tender) {
                // Проверяем, закончился аукцион?
                if (strtotime($tender['end_date']) < time()) {
                    $data['game_tender'] = TRUE;
                } else {
                    // Записываем допусловия
                    $this->tenders->set_tenders_options($this->input->post('tender_option'), (int)$tender_id, (int)$user_id);
                }
            } else
                $data['no_tender'] = TRUE;
        }

        if ($data['no_tender'] == TRUE || $data['game_tender'] == TRUE)
            echo "error|Ваши условия не приняты";
        else
            echo "success|Ваши условия приняты";

        return TRUE;
    }

    function json_encode($data)
    {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = $this->json_encode($value);
                    $output_associative[] = $this->json_encode($key) . ':' . $this->json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else {
                    return '{' . implode(',', $output_associative) . '}';
                }
            default:
                return ''; // Not supported
        }
    }

    /*** Скачать документы ***/
    function down($what = "protocol", $tender_id = 0)
    {
        $this->load->helper('download');

        switch ($what) {
            default:
            case 'protocol':
                $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/data/protocol/protocol_" . $tender_id . ".docx");
                force_download("Протокол №" . $tender_id . " от " . date("d.m.Y") . ".docx", $data);
                break;
            case 'itogi':
                $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/data/itogi/itogi_" . $tender_id . ".xlsx");
                force_download("Итоги тендера №" . $tender_id . " от " . date("d.m.Y") . ".xlsx", $data);
                break;
        }

        return TRUE;
    }

    /*** Просмотр истории аукциона ***/
    function show_history($tender_id = 0)
    {

        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $data['page_title'] = 'Аукцион не найден';
            $data['no_tender'] = $data['game_tender'] = $data['tender_author'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);
                if ($tender) {
                    $original_user_id = $this->tank_auth->get_user_id();
                    if ($tender['user_id'] == $original_user_id)
                        $data['tender_author'] = TRUE;

                    $data['page_title'] = 'История ставок по аукциону «' . $tender['title'] . '»';

                    // Проверяем, закончился аукцион?
                    if (strtotime($tender['end_date']) < time()) {
                        $data['game_tender'] = TRUE;
                    }

                    $data['tender_results_lotes_history'] = $this->tenders->get_results_lotes_history((int)$tender_id);
                    $data['tender_lotes_users'] = $this->tenders->get_tenders_lotes_by_user((int)$tender_id);
                } else
                    $data['no_tender'] = TRUE;
            }

            $data['tender_id'] = (int)$tender_id;

            $this->template->view('tenders/view_auction_history', $data);
        } else
            redirect('');
    }

    /**
     * Сохранение аукциона в базе
     *
     * @return void
     */
    function save()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('title', 'Наименование', 'trim|required|xss_clean');
            $this->form_validation->set_rules('begin_date', 'Дата начала', 'trim|required|xss_clean');
            $this->form_validation->set_rules('end_date', 'Дата окончания', 'trim|required|xss_clean');
            $this->form_validation->set_rules('begin_time', 'Дата начала', 'trim|required|xss_clean');
            $this->form_validation->set_rules('end_time', 'Дата окончания', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description', 'Описание', 'trim|required|xss_clean');
            $this->form_validation->set_rules('type_rate', 'Опции ставки', 'trim|required|xss_clean');
            $this->form_validation->set_rules('type_auction', 'Опции торгов', 'trim|required|xss_clean');
            $this->form_validation->set_rules('tags', 'Теги', 'trim|xss_clean');
            $this->form_validation->set_rules('users', 'Пользователи', 'trim|xss_clean');


            $data['errors'] = array();
            $is_edit = FALSE;

            if ($this->form_validation->run()) {                                // validation ok

                // Проверка ID тендера
                /*				if ( !$this->tenders->get_tenders_by_id((int)$this->input->post('tender_id')) )
                                {
                                    $last_id = $this->tenders->get_last_tender_id();
                                    $tender_id = (int)$last_id['id'] + 1;
                                    $is_edit = FALSE;
                                }*/
                if ($this->input->post('is_add') == 'true') {
                    $last_id = $this->tenders->get_last_tender_id();
                    $tender_id = (int)$last_id['id'] + 1;
                    $url_tender = $this->input->post('tender_id');
                    $is_edit = FALSE;
                } else {
                    $tender_id = (int)$this->input->post('tender_id');
                    $is_edit = TRUE;
                }

                $user_id = $this->tank_auth->get_user_id();
                $begin_date_str = strtotime($this->input->post('begin_date') . " " . $this->input->post('begin_time'));
                $begin_date = date("Y-m-d H:i:s", $begin_date_str);
                $end_date_str = strtotime($this->input->post('end_date') . " " . $this->input->post('end_time'));
                $end_date = date("Y-m-d H:i:s", $end_date_str);
                $type_rate = $this->input->post('type_rate');
                $type_auction = $this->input->post('type_auction');
                $type_auction_scandinavia = $this->input->post('type_auction_scandinavia');
                $type_auction_plus = $this->input->post('type_auction_plus');
                $users_visible = $this->input->post('users_visible');

                $status = 0;
                if (time() > $begin_date_str && time() <= $end_date_str)
                    $status = 1;
                elseif (time() > $end_date_str)
                    $status = 2;

                $data_tender = array(
                    'user_id' => (int)$user_id,
                    'title' => $this->input->post('title'),
                    'begin_date' => $begin_date,
                    'end_date' => $end_date,
                    'description' => $this->input->post('description'),
                    'type_rate' => $type_rate,
                    'type_auction' => $type_auction,
                    'type_auction_scandinavia' => (!empty($type_auction_scandinavia) ? $type_auction_scandinavia : 0),
                    'type_auction_plus' => (!empty($type_auction_plus) ? $type_auction_plus : 0),
                    'tender_minute_end' => (!empty($type_auction_scandinavia) ? $this->input->post('tender_minute_end') : 0),
                    'scan_minute' => (!empty($type_auction_scandinavia) ? $this->input->post('scan_minute') : 0),
                    'users_visible' => (!empty($users_visible) ? $users_visible : 0),
                    'status' => (!empty($status) ? $status : 0),
                );

                $is_edit = FALSE;

                if ($is_edit == FALSE) {

                    $data_tender['id'] = $tender_id;

                    // Переименовываем папку с файлами к тендеру
                    if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload_tender/files/' . $url_tender))
                        rename($_SERVER['DOCUMENT_ROOT'] . '/upload_tender/files/' . $url_tender, $_SERVER['DOCUMENT_ROOT'] . '/upload_tender/files/' . $tender_id);

                    $bool_data_tender = $this->tenders->create_tender($data_tender);
                } else {
                    $bool_data_tender = $this->tenders->update_tender($data_tender, $tender_id);
                }

                if ($bool_data_tender) {

                    // Запись допусловий
                    $options = $this->input->post('options');

                    $arr_options = array();

                    if (!empty($options)) {
        //             //old logic
        //             for ($ao = 0; $ao <= sizeof($options) - 1; $ao++) {
        //                 if ($ao % 2 == 0 && !empty($options[$ao]))
        //                     $arr_options[$k]['name_field'] = $options[$ao];
        //                 if ($ao % 2 == 1 && !empty($options[$ao])) {
        //                     $arr_options[$k]['type_field'] = $options[$ao];
        //                     $k++;
        //                 }
        //             }
                        $k = 0;
                        for ($i = 0; $i <= count($options) - 1; $i = $i + 3) {
                            $arr_options[$k]['name_field'] = $options[$i];
                            $k++;
                        }
                        $k = 0;
                        for ($i = 1; $i <= count($options) - 1; $i = $i + 3) {
                            $arr_options[$k]['type_field'] = $options[$i];
                            $k++;
                        }

                        $this->tenders->add_options($arr_options, $tender_id, $is_edit);
                    }

                    // Дозапись допусловий
                    $new_term_name = $this->input->post('new_term_name');
                    $new_term_type = $this->input->post('new_term_type');
                    if (!empty($new_term_name) && !empty($new_term_type)) {
                        $this->tenders->add_options(array(0 => array("name_field" => $new_term_name, "type_field" => $new_term_type)), $tender_id);
                    }

                    // Запись лотов
                    $lots = $this->input->post('lots');
                    $arr_lots = array();
                    $l = 0;

                    if (!empty($lots)) {
        //             print_r2($lots);
                        for ($al = 0; $al <= sizeof($lots) - 1; $al++) {
                            if ($type_auction == 3) {
                                if ($al % 6 == 0)
                                    $arr_lots[$l]['name'] = $lots[$al];
                                if ($al % 6 == 1)
                                    $arr_lots[$l]['unit'] = $lots[$al];
                                if ($al % 6 == 2)
                                    $arr_lots[$l]['need'] = $lots[$al];
                                if ($al % 6 == 3)
                                    $arr_lots[$l]['start_sum'] = 0;
                                if ($al % 6 == 4) 
                                    $arr_lots[$l]['product_link'] = $lots[$al];
                                if ($al % 6 == 5) {
                                    $arr_lots[$l]['step_lot'] = 0;
                                    $l++;
                                }
                            } elseif ($type_rate == 2 || $type_auction == 2) {
                                if ($al % 6 == 0)
                                    $arr_lots[$l]['name'] = $lots[$al];
                                if ($al % 6 == 1)
                                    $arr_lots[$l]['unit'] = $lots[$al];
                                if ($al % 6 == 2)
                                    $arr_lots[$l]['need'] = $lots[$al];
                                if ($al % 6 == 3)
                                    $arr_lots[$l]['start_sum'] = $lots[$al];
                                if ($al % 6 == 4) 
                                    $arr_lots[$l]['product_link'] = '';
                                if ($al % 6 == 5) {
                                    $arr_lots[$l]['step_lot'] = $lots[$al];
                                    $l++;
                                }
                            } else {
                                if ($al % 6 == 0)
                                    $arr_lots[$l]['name'] = $lots[$al];
                                if ($al % 6 == 1)
                                    $arr_lots[$l]['unit'] = $lots[$al];
                                if ($al % 6 == 2)
                                    $arr_lots[$l]['need'] = $lots[$al];
                                if ($al % 6 == 3)
                                    $arr_lots[$l]['start_sum'] = $lots[$al];
                                if ($al % 6 == 4) 
                                    $arr_lots[$l]['product_link'] = '';
                                if ($al % 6 == 5) {
                                    $arr_lots[$l]['step_lot'] = 0;
                                    $l++;
                                }
                            }
                        }
        //             print_r2($arr_lots);
                        $this->tenders->add_lotes($arr_lots, $tender_id, $is_edit);
                    }

                    // Дозапись лотов
                    $new_lot_name = $this->input->post('new_lot_name');
                    $new_lot_unit = $this->input->post('new_lot_unit');
                    $new_lot_need = $this->input->post('new_lot_need');
                    $new_lot_start_sum = $this->input->post('new_lot_start_sum');
                    $new_lot_product_link = $this->input->post('new_lot_product_link');
                    //print_r2($_POST);
                    $new_lot_step_lot = $this->input->post('new_lot_step_lot');
                    if (empty($new_lot_step_lot)) $new_lot_step_lot = 0;
                    if (empty($new_lot_product_link)) $new_lot_product_link = '';
                    if (!empty($new_lot_name) || !empty($new_lot_unit) || !empty($new_lot_need) || !empty($new_lot_start_sum) || !empty($new_lot_step_lot)) {
                        $this->tenders->add_lotes(array(0 => array(
                            "name" => $new_lot_name, 
                            "unit" => $new_lot_unit, 
                            "need" => $new_lot_need, 
                            "start_sum" => $new_lot_start_sum, 
                            "step_lot" => $new_lot_step_lot,
                            "product_link" => $new_lot_product_link
                        )), $tender_id);
                    }

                    // Если аукцион перевели в "Стандартная ставка" и "Открытые торги", то обнулить все шаги лотов
                    if ($type_rate == 1 && $type_auction == 1) {
                        $this->tenders->clear_step_lotes($tender_id);
                    }

                    //добавление тегов
                    if ($is_edit == true) {
                        $this->db->where('tender_id', $tender_id);
                        $this->db->delete('tenders_tags');
                    }

                    $tags = array();
                    if (is_array($this->input->post('tags')))
                        $tags = $this->input->post('tags');
                    foreach ($tags as $tag) {
                        $tag_data = array(
                            'tender_id' => $tender_id,
                            'tag_id' => $tag,
                        );
                        $this->db->insert('tenders_tags', $tag_data);
                    }

                    //добавление пользователей к аукциону
                    if ($is_edit == true) {
                        $this->db->where('tender_id', $tender_id);
                        $this->db->delete('tender_users');
                    }

                    $users = array();
                    if (is_array($this->input->post('users')))
                        $users = $this->input->post('users');
                    foreach ($users as $user) {
                        $user_data = array(
                            'tender_id' => $tender_id,
                            'user_id' => $user,
                        );
                        $this->db->insert('tender_users', $user_data);
                    }


                    if ($is_edit == FALSE) {
                        // Отправка письма подписчикам

                        $this->_send_email('newtender', 'Новый аукцион', array('tender_id' => $tender_id));

                        echo "success|Тендер успешно создан";
                    } else
                        echo "success|Тендер успешно сохранен";

                } else
                    echo "error|Тендер не сохранен из-за возникших ошибок";

            }

        } else
            redirect('');

        return TRUE;
    }

    /*** Удаление аукциона ***/
    function delete($tender_id = 0)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() == 3) {
            if ($tender_id > 0) {
                if ($this->tenders->delete_tender($tender_id) == TRUE)
                    echo "success|Тендер успешно удален";
                else
                    echo "error|Ошибка удаления тендера";
            } else
                echo "error|Ошибка удаления тендера";
        } else
            redirect('');
    }

    /*** Перемещение в архив ***/
    function inarchive($tender_id = 0)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            if ($tender_id > 0) {
                if ($this->tenders->archive_tender($tender_id) == TRUE)
                    echo "success|Тендер успешно перемещен в архив";
                else
                    echo "error|Ошибка перемещения тендера в архив";
            } else
                echo "error|Ошибка перемещения тендера в архив";
        } else
            redirect('');
    }

    /*** Удаление лотов ***/
    function deleteLot($row_id = 0)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            if ($row_id > 0) {
                if ($this->tenders->delete_lote($row_id) == TRUE)
                    echo "success|Лот успешно удален";
                else
                    echo "error|Ошибка удаления лота";
            } else
                echo "error|Ошибка удаления лота";
        } else
            redirect('');
    }

    /*** Удаление допусловий ***/
    function deleteOption($row_id = 0)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            if ($row_id > 0) {
                if ($this->tenders->delete_option($row_id) == TRUE)
                    echo "success|Дополнительное условие успешно удалено";
                else
                    echo "error|Ошибка удаления дополнительного условия";
            } else
                echo "error|Ошибка удаления дополнительного условия";
        } else
            redirect('');
    }

    /*** Выбор победителя ***/
    function winner()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $tender_id = (int)$this->input->post('tender_id');
            $user_id = (int)$this->input->post('user_id');
            $comment = $this->input->post('comment');
            $data['no_user'] = FALSE;

            if ($user_id < 0 || $tender_id < 0)
                $data['no_user'] = TRUE;
            else {
                $tender = $this->tenders->get_tenders_by_id($tender_id);
                if ($tender) {
                    $this->tenders->set_tenders_leader_manual($tender_id, $user_id, $comment);
                } else
                    $data['no_user'] = TRUE;
            }

            if ($data['no_user'] == TRUE)
                echo "error|Произошла ошибка при выборе победителя";
            else
                echo "success|Победитель выбран";
        } else
            redirect('');

        return TRUE;
    }

    /*** Досрочное завершение торгов ***/
    function earlyend()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $tender_id = (int)$this->input->post('tender_id');
            $auto_end = (int)$this->input->post('auto_end');
            $reason = $this->input->post('reason');
            $data['no_tender'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);
                if ($tender) {
                    if (strtotime($tender['end_date']) < time() && $auto_end == 1)
                        $this->tenders->set_tenders_end($tender_id);
                    elseif ($auto_end == 0){
                        $data_tender['change_reason'] = $reason;
                        $this->tenders->update_tender($data_tender, $tender_id);
                        $this->tenders->set_tenders_end($tender_id, $user_id);
                    }
                } else
                    $data['no_tender'] = TRUE;
            }

            if ($data['no_tender'] == TRUE)
                echo "error|Произошла ошибка при завершении торгов";
            else
                echo "success|Торги успешно завершены";
        } else
            redirect('');

        return TRUE;
    }

    /*** Аннулирование торгов ***/
    function cancellation()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $tender_id = (int)$this->input->post('tender_id');
            $reason = $this->input->post('reason');
            $data['no_tender'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                if ($this->tank_auth->get_group_id() == 2)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;

                $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);
                if ($tender) {
                    $data_tender['change_reason'] = $reason;
                    $this->tenders->update_tender($data_tender, $tender_id);
                    $this->tenders->set_tenders_reset($tender_id);
                } else
                    $data['no_tender'] = TRUE;
            }

            if ($data['no_tender'] == TRUE)
                echo "error|Произошла ошибка при аннулировании торгов";
            else
                echo "success|Торги успешно аннулированы";
        } else
            redirect('');

        return TRUE;
    }

    function prolongation()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $tender_id = (int)$this->input->post('tender_id');
            $new_date = $this->input->post('new_date');
            $reason = $this->input->post('reason');
            $date = date("Y-m-d H:i:s", strtotime($new_date));
            $data['no_tender'] = FALSE;

            if ($tender_id < 0)
                $data['no_tender'] = TRUE;
            else {
                if ($this->tank_auth->get_group_id() == 2 OR $this->tank_auth->get_group_id() == 5)
                    $user_id = $this->tank_auth->get_user_id();
                else
                    $user_id = 0;
                $tender = $this->tenders->get_tenders_by_id((int)$tender_id, (int)$user_id);

                if ($tender) {
                   try{
                       $data_tender['end_date'] = $date;
                       $data_tender['change_reason'] = $reason;
                       $this->tenders->update_tender($data_tender, $tender_id);
                   } catch (Exception $e) {
                        var_dump($e);
                   }
                } else
                  $data['no_tender'] = TRUE;
            }

            if ($data['no_tender'] == TRUE)
                echo "error|Произошла ошибка при изменении даты торгов";
            else
                echo "success|Торги успешно продлены";
        } else
            redirect('');

        return TRUE;
    }

    /*** Отмена последней заявки ***/
    function resetlot($row)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            if (!empty($row)) {
                $lote = explode("_", $row);

                if ($this->tenders->reset_lot((int)$lote[0], (int)$lote[1], (int)$lote[2]) == TRUE)
                    echo "success|Ставка успешно отменена";
                else
                    echo "error|Ошибка отмены ставки";
            } else
                echo "error|Ошибка отмены ставки";
        } else
            redirect('');
    }

    /**
     * Отправка E-mail сообщений
     *
     * @param string
     * @param string
     * @param array
     * @return    void
     */
    function _send_email($type, $subject, $data = "")
    {

        $this->load->library('email');

        $config['protocol'] = 'sendmail';
        $config['mailtype'] = 'html';
        $config['validate'] = true;

        $this->email->initialize($config);

        $tender = array();
        if (!empty($data['tender_id'])) {
            $tender = $this->tenders->get_tenders_by_id((int)$data['tender_id']);
            $tender_users = $this->tenders->get_tenders_lotes_by_user((int)$data['tender_id']);
        }

        //выбираем адреса
        if ($type == "newtender") {
            $users_list = $this->users->users_list_newtender($tender['id']);
        } elseif ($type == "updatetender") {
            $users_list = $this->users->users_list_updatetender($tender['id']);
        } else {
            $users_list = $this->tank_auth->users_list();
        }

        if (!empty($users_list)) {
            foreach ($users_list as $k => $v) {

                if (
                    ($v['activated'] == 1 && $type == "newtender" && $v['notice_new_auctions'] == 1) ||
                    ($v['activated'] == 1 && $type == "updatetender" && $v['notice_other_members'] == 1 && !empty($tender_users[$v['id']])) ||
                    ($v['activated'] == 1 && $type == "welcomeuser" && $v['id'] == $data['user_id'])
                ) {
                    $repl_array = array("%user%" => $v['user_name'], "%email_user%" => $v['email'], "%pass_user%" => $v['password'], "%url_user%" => "http://" . $this->config->item('engine_url') . "/auth/user_edit/" . $v['id'], "%url_site%" => "http://" . $this->config->item('engine_url'), "%tender_name%" => $tender['title'], "%tender_date_start%" => date("d.m.Y H:i", strtotime($tender['begin_date'])), "%tender_date_end%" => date("d.m.Y H:i", strtotime($tender['end_date'])), "%url_tender%" => "http://" . $this->config->item('engine_url') . "/tenders/show/" . $tender['id']);

                    $text_message = $this->tenders->get_settings("email-" . $type);
                    $message = $text_message['value'];
                    unset($text_message);
                    if (!empty($message)) {
                        foreach ($repl_array as $key => $value) {
                            $message = str_replace($key, $value, $message);
                        }
                    }
                    $message = nl2br($message);

              		$this->email->from($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
                    //$this->email->from('robot@adyn.ru', $this->config->item('engine_title'));
                    $this->email->reply_to($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
                    $this->email->to($v['email']);
                    $this->email->subject($this->config->item('engine_title') . ": " . $subject);
                    $this->email->message($message);
                    $this->email->send();

                }
            }
        }

    }

    function tags()
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $data['page_title'] = 'Редактирование категорий';
            $data['group_id'] = $this->tank_auth->get_group_id();
            if (isset($_POST['sbmt'])) {
                if (trim($_POST['caption']) != '') {
                    $tag = array(
                        'caption' => htmlspecialchars(trim($_POST['caption'])),
                    );
                    $this->db->insert('tags', $tag);
                    redirect('/tenders/tags/');
                } else {
                    $data['post_error'] = 'Необходимо ввести название категории';
                }
            }
            $data['all_tags'] = $this->tenders->get_all_tender_tags('id');
            $this->template->view('tenders/tags', $data);
        } else
            redirect('');
    }

    function tag_delete($tag_id)
    {
        if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() == 3) {

            $this->db->where('id', $tag_id);
            $this->db->delete('tags');
            //удаляем из связаной таблицы все записи с ненужным тегом
            $this->db->where('tag_id', $tag_id);
            $this->db->delete('tenders_tags');
            redirect('/tenders/tags/');
        } else
            redirect('');
    }

    function export_lot_history($id)
    {
        if (!empty($id) && $this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1) {
            $id = (int)$id;
            $items = $this->tenders->get_tender_lot_history($id);
            if (count($items)) {
                include_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PHPExcel/PHPExcel.php";
                $xl = new PHPExcel();
                $sheet = $xl->getActiveSheet();
                foreach (range('A', 'E') as $colId) {
                    $sheet->getColumnDimension($colId)->setAutoSize(true);
                }
                $sheet->getCell('A1')->setValue('Наименование лота');
                $sheet->getCell('B1')->setValue('Время ставки');
                $sheet->getCell('C1')->setValue('Цена участника');
                $sheet->getCell('D1')->setValue('Участник');
                $sheet->getCell('E1')->setValue('Комментарий');
                $row = 2;

                foreach ($items as $item) {
                    $sheet->getCell('A' . $row)->setValue($item['lot_name']);
                    $sheet->getCell('B' . $row)->setValue($item['created']);
                    $sheet->getCell('C' . $row)->setValue($item['value']);
                    $sheet->getCell('D' . $row)->setValue($item['user_name']);
                    $sheet->getCell('E' . $row)->setValue($item['comment']);
                    if ($item['is_deleted'] == 1) {
                        $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->applyFromArray(array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => "f9dddd"
                            )
                        ));
                    }
                    $row++;
                }

        //     $writer = new PHPExcel_Writer_Excel5($xl);

                $writer = PHPExcel_IOFactory::createWriter($xl, 'Excel2007');
                ob_end_clean();
                $file_path = $_SERVER['DOCUMENT_ROOT'] . "/data/export_lot_history/{$id}/";
                $file_name = "export-history-lot-{$id}-" . date('Y-m-d_H_i_s') . ".xlsx";
                @mkdir($file_path, 0777, true);

                $writer->save($file_path . $file_name);

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Length: ' . filesize($file_path . $file_name));
                header('Content-Disposition: attachment; filename=' . basename($file_name));
                echo file_get_contents($file_path . $file_name);
            }
        } else {
            redirect('');
        }
    }

    function export_tenders()
    {
        $result['success'] = false;
        if (!empty($_POST['ids'])) {
            $exporter = new ExporterTenders();
            $ids = implode(",", $_POST['ids']);
            $exporter->tenders = $this->tenders->get_export_tenders($ids);
            $result['success'] = true;
            $result['link'] = $exporter->export();
        } else {
            $result['error'] = "Не выбраны аукционы";
        }
        echo $this->json_encode($result);
    }

    function kp_add(){
        if( isset( $_POST['my_file_upload'] ) ){  
            
            $uploaddir = $_SERVER['DOCUMENT_ROOT'] . "/data/kp_tenders"; // . - текущая папка где находится submit.php

            // cоздадим папку если её нет
            if( ! is_dir( $uploaddir ) ) mkdir( $uploaddir, 0777 );

            $files      = $_FILES; // полученные файлы
            $done_files = array();
            $user_id = $this->tank_auth->get_user_id();
            $auction_id = $_POST['auction'];
            // переместим файлы из временной директории в указанную
            foreach( $files as $file ){
                $file_name = "Auction_".$auction_id. "_seller_". $user_id . ".pdf";

                if( move_uploaded_file( $file['tmp_name'], "$uploaddir/$file_name" ) ){
                    $done_files[] = realpath( "$uploaddir/$file_name" );
                }
            }

            $data = $done_files ? array('files' => $done_files ) : array('error' => 'Ошибка загрузки файлов.');

            echo $this->json_encode($data);
        }
    }
}

/* End of file tenders.php */
/* Location: ./application/controllers/tenders.php */