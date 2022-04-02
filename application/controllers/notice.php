<?php


class Notice extends CI_Controller
{
    
    private $_updatePurchasesUrl = 'https://fpkinvest.ru/purchases.xml';

    function __construct()
    {
        parent::__construct();

        $this->load->library('tank_auth');
        $this->load->model('tenders_data', 'tenders');
        $this->load->model('purchases');
    }


    function send()
    {
        if ('cli' ==  php_sapi_name()){
            $cron_frequency = 10 * 60;    //преодичность в кроне 10мин
            $day_before = 24 * 60 * 60;   //24ч до события
            $hour_before = 60 * 60;       //час до события
            //час до начала
            $tenders = array();
            $sql = "SELECT *
                    FROM `tenders` 
                    WHERE (UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) > 0 AND
                          ((UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) <= (" . $hour_before . ") AND
                          (UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) > (" . ($hour_before - $cron_frequency) . ") )";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $tenders = $query->result_array();
            }
//            echo 'hour_before';
//            print_r2($tenders);
            if (!empty($tenders)) {
                foreach ($tenders as $tender) {
                    $this->_send_email_by_tender($tender, 'hour_before_start');
                }
            }
            //день до начала
            $tenders = array();
            $sql = "SELECT *
                    FROM `tenders` 
                    WHERE (UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) > 0 AND
                          ((UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) <= (" . $day_before . ") AND
                          (UNIX_TIMESTAMP(begin_date) - UNIX_TIMESTAMP(NOW())) > (" . ($day_before - $cron_frequency) . ") )";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $tenders = $query->result_array();
            }
//            echo 'day_before';
//            print_r2($tenders);
            if (!empty($tenders)) {
                foreach ($tenders as $tender) {
                    $this->_send_email_by_tender($tender, 'day_before_start');
                }
            }
            //час до конца
            $tenders = array();
            $sql = "SELECT *
                    FROM `tenders` 
                    WHERE (UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) > 0 AND
                          ((UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) <= (" . $hour_before . ") AND
                          (UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) > (" . ($hour_before - $cron_frequency) . ") )";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $tenders = $query->result_array();
            }
//            echo 'hour_after';
//            print_r2($tenders);
            if (!empty($tenders)) {
                foreach ($tenders as $tender) {
                    $this->_send_email_by_tender($tender, 'hour_before_end');
                }
            }
            //день до конца
            $tenders = array();
            $sql = "SELECT *
                    FROM `tenders` 
                    WHERE (UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) > 0 AND
                          ((UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) <= (" . $day_before . ") AND
                          (UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(NOW())) > (" . ($day_before - $cron_frequency) . ") )";

            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $tenders = $query->result_array();
            }
//            echo 'day_after';
//            print_r2($tenders);
            if (!empty($tenders)) {
                foreach ($tenders as $tender) {
                    $this->_send_email_by_tender($tender, 'day_before_end');
                }
            }
        }
    }
    public function purchases()
    {
        if ('cli' ==  php_sapi_name()){

            $this->updatepurchases();
            $cron_frequency = 60 * 60;    //преодичность в кроне 60мин
            $day_before = 24 * 60 * 60;   //24ч до события
            //создана новая закупка
            $condition = array(
                'is_noticed' => 0,
            );
            $purchases = $this->purchases->findAllByAttributes($condition);
            if (!empty($purchases)) {
                foreach ($purchases as $purchase) {
                    $data = array(
                        'is_noticed' => '1',
                    );
                    $this->purchases->update($data, $purchase->id);
                    $this->_send_email_by_purchase($purchase, 'new_purchase');
                }
            }
            //день до начала
            $condition = array(
                '(UNIX_TIMESTAMP(date_start) - UNIX_TIMESTAMP(NOW())) <=' => $day_before,
                '(UNIX_TIMESTAMP(date_start) - UNIX_TIMESTAMP(NOW())) >' => $day_before - $cron_frequency,
            );
            $purchases = $this->purchases->findAllByAttributes($condition);
            if (!empty($purchases)) {
                foreach ($purchases as $purchase) {
                    $this->_send_email_by_purchase($purchase, 'day_before_start');
                }
            }
            //день до конца
            $condition = array(
                '(UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP(NOW())) <=' => $day_before,
                '(UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP(NOW())) >' => $day_before - $cron_frequency,
            );
            $purchases = $this->purchases->findAllByAttributes($condition);
            if (!empty($purchases)) {
                foreach ($purchases as $purchase) {
                    $this->_send_email_by_purchase($purchase, 'day_before_end');
                }
            }
        }
    }
    function _send_email_by_purchase($purchase, $type)
    {
        $notice_type = '';
        $sql_in = '';
        if($type == 'day_before_start'){
            $notice_type = 'up.notice_purchases_day_before_start';
        }elseif($type == 'day_before_end'){
            $notice_type = 'up.notice_purchases_day_before_end';
        }elseif($type == 'new_purchase'){
            $notice_type = 'up.notice_new_purchases';
        }
        if ($purchase->tags != '') {
            $sql_in = "OR ut.tag_id IN ({$purchase->tags})";
        }
        $get_users_query = "SELECT u.id, u.email, up.name AS user_name, 
                                   u.password, u.activated, up.*, up.notice_other_members, up.notice_new_auctions
                            FROM users u
                            INNER JOIN user_profiles up ON u.id = up.user_id 
                            LEFT JOIN users_tags ut ON u.id = ut.user_id
                            WHERE u.activated = 1 AND 
                                  up.notice_disable = 0 AND
                                  ".$notice_type." = 1 AND  
                                  (up.select_all_tags = 1 {$sql_in} )
                            GROUP BY u.id";
        $query = $this->db->query($get_users_query);
        $users = array();
        if ($query->num_rows() > 0) {
            $users = $query->result();
        }
        if(!empty($users)){
            foreach($users as $user){
                $this->_send_email_for_purchase_by_user($type, $user, $purchase);
            }
        }
    }
    function _send_email_for_purchase_by_user($type, $user, $purchase)
    {
        $subject = '';
        $when_message = '';
        $much_message = '';

        if($type == 'day_before_start'){
            $subject = 'Уведомление о начале принятия заявок на сайте fpkinvest.ru';
            $when_message = 'начала';
            $much_message = 'дня';
        }elseif($type == 'day_before_end'){
            $subject = 'Уведомление об окончании принятия заявок на сайте fpkinvest.ru';
            $when_message = 'конца';
            $much_message = 'дня';
        }elseif($type == 'new_purchase'){
            $subject = 'Уведомление о новой закупке на сайте fpkinvest.ru';
            $when_message = 'конца';
            $much_message = 'дня';
        }
        $event = "до " . $when_message . " принятия заявок для закупки &laquo;{$purchase->caption}&raquo; осталось менее {$much_message}";
        if($type == 'new_purchase'){
            $event = "добавлена новая закупка &laquo;{$purchase->caption}&raquo;.";
        }
        
            $message = "Уважаемый, " . $user->user_name . "!<br>
            Сообщаем Вам, что на сайте <a href='https://fpkinvest.ru/'>https://fpkinvest.ru/</a> $event<br> 
            Дата принятия заявок с " . $purchase->date_start . " по " . $purchase->date_end . ". <br>
            Для просмотра закупки пройдите по ссылке <a href='{$purchase->url}'>{$purchase->url}</a><br>

            Вы получили данное письмо, так как подписаны на данную рассылку. <br>
            Отписаться можно в личном кабинете 
            http://" . $this->config->item('engine_url') . "/auth/user_edit/" . $user->id;
//        print_r2($message);

            $this->send_message($user->email,$subject,$message);        
    }
    function _send_email_by_tender($tender, $type)
    {
        $notice_type = '';
        $tender_tags = array();
        $sql_in = '';
        $tags = $this->tenders->get_tenders_tags($tender['id']);
        if ($tags != null) {
            foreach ($tags as $one) {
                $tender_tags[] = $one['tag_id'];
            }
            $in_array = implode(',', $tender_tags);
            $sql_in = 'OR ut.tag_id IN ( '.$in_array.' )';
        }
//        print_r2($tender_tags);
        if($type == 'hour_before_start'){
            $notice_type = 'up.notice_hour_before_start';
        }elseif($type == 'day_before_start'){
            $notice_type = 'up.notice_day_before_start';
        }elseif($type == 'hour_before_end'){
            $notice_type = 'up.notice_hour_before_end';
        }elseif($type == 'day_before_end'){
            $notice_type = 'up.notice_day_before_end';
        }
		
		$users = $this->tenders->get_tenders_users($tender['id']);
        $users_filter = '';
        if (count($users)) {
            $users_ids = array_map(function ($item) {
                return $item['user_id'];
            }, $users);
            $users_filter = ' AND u.id IN ( '.implode(', ', $users_ids).' )';
        }
		
        $get_users_query = "SELECT u.id, u.email, up.name AS user_name, 
                                   u.password, u.activated, up.*, up.notice_other_members, up.notice_new_auctions
                            FROM users u
                            INNER JOIN user_profiles up ON u.id = up.user_id 
                            LEFT JOIN users_tags ut ON u.id = ut.user_id
                            WHERE u.activated = 1 AND 
                                  up.notice_disable = 0 AND
                                  ".$notice_type." = 1 AND  
                                  (up.select_all_tags = 1 {$sql_in} )
                                  ".$users_filter."
                            GROUP BY u.id";
        $query = $this->db->query($get_users_query);
        $users = array();
        if ($query->num_rows() > 0) {
            $users = $query->result_array();
        }
        if(!empty($users)){
            foreach($users as $user){
                $this->_send_email_by_user($type, $user, $tender);
            }
        }
    }
    function _send_email_by_user($type, $user, $tender)
    {
        $subject = '';
        $when_message = '';
        $much_message = '';

        if($type == 'hour_before_start'){
            $subject = 'Уведомление о начале аукциона';
            $when_message = 'начала';
            $much_message = 'часа';
        }elseif($type == 'day_before_start'){
            $subject = 'Уведомление о начале аукциона';
            $when_message = 'начала';
            $much_message = 'дня';
        }elseif($type == 'hour_before_end'){
            $subject = 'Уведомление об окончании аукциона';
            $when_message = 'конца';
            $much_message = 'часа';
        }elseif($type == 'day_before_end'){
            $subject = 'Уведомление об окончании аукциона';
            $when_message = 'конца';
            $much_message = 'дня';
        }

        
        $message = "Уважаемый, ".$user['user_name']."!<br>
            Сообщаем Вам, что на площадке электронных торгов  \"Invest eTenders\" до ".$when_message." аукциона ".$tender['title']." осталось менее ".$much_message."<br> 
            Дата проведения тендера с ".$tender['begin_date']." по ".$tender['end_date'].". <br>
            Для участия в торгах пройдите по ссылке http://" . $this->config->item('engine_url') . "/tenders/show/" . $tender['id']."<br>

            Вы получили данное письмо, так как подписаны на данную рассылку. <br>
            Отписаться можно в личном кабинете 
            http://" . $this->config->item('engine_url') . "/auth/user_edit/" . $user['id'];
            //print_r2($message);

        $this->send_message($user['email'],$subject,$message);
    }
    function get_tenders_tags($tender_id)
    {
        $sql = "SELECT * FROM `tenders_tags` WHERE `tender_id` = " . $tender_id;

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) return $query->result_array();
        return NULL;
    }
    public function send_message($email, $subject, $message)
    {
        $this->load->library('email');


        $config['protocol'] = 'sendmail'; 
        $config['mailtype'] = 'html';
        $config['validate'] = true;

        $this->email->initialize($config);



        $this->email->from($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
        $this->email->reply_to($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
        $this->email->to($email);
        $this->email->subject($this->config->item('engine_title') . ": " . $subject);
        $this->email->message($message);
        $this->email->send();
    }

    public function updatepurchases()
    {
        set_error_handler(function ($errno, $errmsg, $filename, $linenum, $vars) {
            if (!empty($errno)) {
                $f = fopen(dirname(__FILE__) . '/../logs/import_errors.log', 'a');
                $err = "error {$errno}: {$errmsg}";
                fwrite($f, $err);
                fclose($f);
                exit;
            }
        });
        $xml = new SimpleXMLElement($this->_updatePurchasesUrl, 0, true);
        if (!empty($xml->purchase)) {
            foreach ($xml->purchase as $purchase) {
                $model = $this->purchases->findByPk($purchase->id);
                if ($model) {
                    if ($model->purchase_hash !== md5($purchase->caption . $purchase->date_start . $purchase->date_end . $purchase->tags)) {
                        $data = array(
                            'purchase_id' => (int)$purchase->id,
                            'caption' => "{$purchase->caption}",
                            'url' => "{$purchase->url}",
                            'date_start' => "{$purchase->date_start}",
                            'date_end' => "{$purchase->date_end}",
                            'tags' => "{$purchase->tags}",
                            'purchase_hash' => md5($purchase->caption . $purchase->date_start . $purchase->date_end . $purchase->tags),
                        );
                        $this->purchases->update($data, $model->id);
                    }
                } else {
                    $data = array(
                        'purchase_id' => (int)$purchase->id,
                        'caption' => "{$purchase->caption}",
                        'url' => "{$purchase->url}",
                        'date_start' => "{$purchase->date_start}",
                        'date_end' => "{$purchase->date_end}",
                        'tags' => "{$purchase->tags}",
                        'purchase_hash' => md5($purchase->caption . $purchase->date_start . $purchase->date_end . $purchase->tags),
                    );
                    $this->purchases->create($data);
                }
            }
            echo "success\n";
        }
    }
}