<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('security');
		$this->load->library('tank_auth');
		$this->lang->load('tank_auth');
	}

	function index()
	{
		if ($message = $this->session->flashdata('message')) {
			$this->template->view('auth/general_message', array('message' => $message));
		} else {
			redirect('/auth/login/');
		}
	}

	/**
	 * Login user on the site
	 *
	 * @return void
	 */
	function login()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');

		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			//redirect('/auth/send_again/');
			$this->_show_message($this->lang->line('auth_message_no_activation'));

		} else {
			$data['login_by_username'] = ($this->config->item('login_by_username', 'tank_auth') AND
					$this->config->item('use_username', 'tank_auth'));
			$data['login_by_email'] = $this->config->item('login_by_email', 'tank_auth');

			$this->form_validation->set_rules('login', 'Логин', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Пароль', 'trim|required|xss_clean');
			$this->form_validation->set_rules('remember', 'Запомнить меня', 'integer');

			// Get login for counting attempts to login
			if ($this->config->item('login_count_attempts', 'tank_auth') AND
					($login = $this->input->post('login'))) {
				$login = $this->security->xss_clean($login);
			} else {
				$login = '';
			}

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if ($this->tank_auth->login(
						$this->form_validation->set_value('login'),
						$this->form_validation->set_value('password'),
						$this->form_validation->set_value('remember'),
						$data['login_by_username'],
						$data['login_by_email'])) {								// success
					redirect('');

				} else {
					$errors = $this->tank_auth->get_error_message();
					if (isset($errors['banned'])) {								// banned user
						$this->_show_message($this->lang->line('auth_message_banned'));

					} elseif (isset($errors['not_activated'])) {				// not activated user
						//redirect('/auth/send_again/');
						$this->_show_message($this->lang->line('auth_message_no_activation'));

					} else {													// fail
						$errors_str = "";
						foreach ($errors as $k => $v)
							$errors_str .= $this->lang->line($v);

						$this->_show_message("Произошла ошибка во время авторизации: " . $errors_str);
					}
				}
			}

			return NULL;
		}
	}

	/**
	 * Logout user
	 *
	 * @return void
	 */
	function logout()
	{
		$this->tank_auth->logout();

		redirect('/');
		//$this->_show_message($this->lang->line('auth_message_logged_out'));
	}

/**
	function pass()
	{
		$users = $this->db->query("SELECT `id`, `password` FROM `users` WHERE `id` != 1");
		foreach($users->result_array() as $v)
		{
			$this->tank_auth->new_pass($v['id'], $v['password']);
		}
	}
*/

	/**
	 * Register user on the site
	 *
	 * @return void
	 */
	function register_from_admin()
	{
		$use_username = $this->config->item('use_username', 'tank_auth');

		$admin_group_id = $this->tank_auth->get_group_id();
		if ($use_username) {
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length['.$this->config->item('username_min_length', 'tank_auth').']|max_length['.$this->config->item('username_max_length', 'tank_auth').']|alpha_dash');
		}
			$this->form_validation->set_rules('email', 'E-mail', 'trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('name', 'Наименование организации (ФИО)', 'trim|required|xss_clean');
			$this->form_validation->set_rules('legal_form', 'Организационно-правовая форма', 'trim|required|xss_clean');
			$this->form_validation->set_rules('region', 'Регион', 'trim|required|xss_clean');
			$this->form_validation->set_rules('city', 'Населенный пункт', 'trim|required|xss_clean');
			$this->form_validation->set_rules('director_name', 'ФИО руководителя предприятия', 'trim|required|xss_clean');
			$this->form_validation->set_rules('inn', 'ИНН', 'trim|required|xss_clean|numeric');
			$this->form_validation->set_rules('kpp', 'КПП', 'trim|required|xss_clean|numeric');
			$this->form_validation->set_rules('address', 'Адрес', 'trim|required|xss_clean');
			$this->form_validation->set_rules('fact_address', 'Фактический адрес поставщика', 'trim|required|xss_clean');
			$this->form_validation->set_rules('phone', 'Телефон', 'trim|required|xss_clean');
			$this->form_validation->set_rules('contacts', 'Контактные лица', 'trim|required|xss_clean');
			$this->form_validation->set_rules('requisites', 'Реквизиты для включения в контракт', 'trim|required|xss_clean');

			$this->form_validation->set_rules('register_password', 'Пароль', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
			$this->form_validation->set_rules('confirm_password', 'Еще раз Пароль', 'trim|required|xss_clean|matches[register_password]');

			$data['errors'] = array();

			$email_activation = $this->config->item('email_activation', 'tank_auth');

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->create_user(
						$use_username ? $this->form_validation->set_value('username') : '',
						$this->form_validation->set_value('email'),
						$this->form_validation->set_value('register_password'),
						$email_activation))) {									// success

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					$data_profile = array(
						'user_id'	=> $data['user_id'],
						'group_id'	=> $this->input->post('group'),
						'name'	=> $this->input->post('name'),
						'type_data'	=> $this->input->post('type_data'),
						'legal_form'	=> $this->input->post('legal_form'),
						'small_business'	=> $this->input->post('small_business'),
						'region'	=> $this->input->post('region'),
						'city'	=> $this->input->post('city'),
						'address'	=> $this->input->post('address'),
						'fact_address'	=> $this->input->post('fact_address'),
						'phone'	=> $this->input->post('phone'),
						'fax'	=> $this->input->post('fax'),
						'director_name'	=> $this->input->post('director_name'),
						'contacts'	=> $this->input->post('contacts'),
						'certifikates'	=> $this->input->post('certifikates'),
						'organization_date'	=> $this->input->post('organization_date'),
						'inn'	=> $this->input->post('inn'),
						'kpp'	=> $this->input->post('kpp'),
						'employes_count'	=> $this->input->post('employes_count'),
						'okved'	=> $this->input->post('okved'),
						'services'	=> $this->input->post('services'),
						'requisites'	=> $this->input->post('requisites'),
						'notice_other_members'	=> $this->input->post('notice_other_members'),
						'notice_new_auctions'	=> $this->input->post('notice_new_auctions'),
                        /*new notifications*/
                        'notice_disable'	=> $this->input->post('notice_disable'),
                        'notice_day_before_start'	=> $this->input->post('notice_day_before_start'),
                        'notice_hour_before_start'	=> $this->input->post('notice_hour_before_start'),
                        'notice_day_before_end'	=> $this->input->post('notice_day_before_end'),
                        'notice_hour_before_end'	=> $this->input->post('notice_hour_before_end'),
                        'select_all_tags'	=> $this->input->post('select_all_tags'),
                        'notice_new_purchases'	=> $this->input->post('notice_new_purchases'),
                        'notice_purchases_day_before_start'	=> $this->input->post('notice_purchases_day_before_start'),
                        'notice_purchases_day_before_end'	=> $this->input->post('notice_purchases_day_before_end'),
					);
					$bool_data_profile = $this->tank_auth->create_profile($data_profile);
					
					if($bool_data_profile){
                        $user_id = $this->db->insert_id();
                        $tags = array();
                        if (is_array($this->input->post('user_tags')))
                            $tags = $this->input->post('user_tags');
                        foreach ($tags as $tag) {
                            $tag_data = array(
                                'user_id' => $user_id,
                                'tag_id' => $tag,
                            );
                            $this->db->insert('users_tags', $tag_data);
                        }
                    }

					if ($email_activation) {									// send "activate" email
/*						$data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

						$this->_send_email('activate', $data['email'], $data);
*/
						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_1'));
					} else {
/*						if ($this->config->item('email_account_details', 'tank_auth')) {	// send "welcome" email

							$this->_send_email('welcome', $data['email'], $data);
						}
*/
						$this->_send_email('newuser', "Новый пользователь", array('user_id' => $data['user_id']), $this->config->item('engine_admin_email'));
						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_2'));
					}
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
            $data['all_tags'] = $this->users->get_all_tender_tags();
			$data['use_username'] = $use_username;
			$data['page_title'] = 'Регистрация';
			$data['user_profile'] = array();
			$data['current_groupe_id'] = $admin_group_id;
			$this->template->view('auth/register_form_admin', $data);
		
	}

	function register()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');

		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			//redirect('/auth/send_again/');
			$this->_show_message($this->lang->line('auth_message_no_activation'));

		} elseif (!$this->config->item('allow_registration', 'tank_auth')) {	// registration is off
			$this->_show_message($this->lang->line('auth_message_registration_disabled'));

		} else {
			$use_username = $this->config->item('use_username', 'tank_auth');
			if ($use_username) {
				$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length['.$this->config->item('username_min_length', 'tank_auth').']|max_length['.$this->config->item('username_max_length', 'tank_auth').']|alpha_dash');
			}
			$this->form_validation->set_rules('email', 'E-mail', 'trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('name', 'Наименование организации (ФИО)', 'trim|required|xss_clean');
			$this->form_validation->set_rules('legal_form', 'Организационно-правовая форма', 'trim|required|xss_clean');
			$this->form_validation->set_rules('region', 'Регион', 'trim|required|xss_clean');
			$this->form_validation->set_rules('city', 'Населенный пункт', 'trim|required|xss_clean');
			$this->form_validation->set_rules('director_name', 'ФИО руководителя предприятия', 'trim|required|xss_clean');
			$this->form_validation->set_rules('inn', 'ИНН', 'trim|required|xss_clean|numeric');
			$this->form_validation->set_rules('kpp', 'КПП', 'trim|required|xss_clean|numeric');
			$this->form_validation->set_rules('address', 'Адрес', 'trim|required|xss_clean');
			$this->form_validation->set_rules('fact_address', 'Фактический адрес поставщика', 'trim|required|xss_clean');
			$this->form_validation->set_rules('phone', 'Телефон', 'trim|required|xss_clean');
			$this->form_validation->set_rules('contacts', 'Контактные лица', 'trim|required|xss_clean');
			$this->form_validation->set_rules('requisites', 'Реквизиты для включения в контракт', 'trim|required|xss_clean');

			$this->form_validation->set_rules('register_password', 'Пароль', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
			$this->form_validation->set_rules('confirm_password', 'Еще раз Пароль', 'trim|required|xss_clean|matches[register_password]');

			$data['errors'] = array();

			$email_activation = $this->config->item('email_activation', 'tank_auth');

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->create_user(
						$use_username ? $this->form_validation->set_value('username') : '',
						$this->form_validation->set_value('email'),
						$this->form_validation->set_value('register_password'),
						$email_activation))) {									// success

					$data['site_name'] = $this->config->item('website_name', 'tank_auth');

					$data_profile = array(
						'user_id'	=> $data['user_id'],
						'group_id'	=> 1,
						'name'	=> $this->input->post('name'),
						'type_data'	=> $this->input->post('type_data'),
						'legal_form'	=> $this->input->post('legal_form'),
						'small_business'	=> $this->input->post('small_business'),
						'region'	=> $this->input->post('region'),
						'city'	=> $this->input->post('city'),
						'address'	=> $this->input->post('address'),
						'fact_address'	=> $this->input->post('fact_address'),
						'phone'	=> $this->input->post('phone'),
						'fax'	=> $this->input->post('fax'),
						'director_name'	=> $this->input->post('director_name'),
						'contacts'	=> $this->input->post('contacts'),
						'certifikates'	=> $this->input->post('certifikates'),
						'organization_date'	=> $this->input->post('organization_date'),
						'inn'	=> $this->input->post('inn'),
						'kpp'	=> $this->input->post('kpp'),
						'employes_count'	=> $this->input->post('employes_count'),
						'okved'	=> $this->input->post('okved'),
						'services'	=> $this->input->post('services'),
						'requisites'	=> $this->input->post('requisites'),
						'notice_other_members'	=> $this->input->post('notice_other_members'),
						'notice_new_auctions'	=> $this->input->post('notice_new_auctions'),
                        /*new notifications*/
                        'notice_disable'	=> $this->input->post('notice_disable'),
                        'notice_day_before_start'	=> $this->input->post('notice_day_before_start'),
                        'notice_hour_before_start'	=> $this->input->post('notice_hour_before_start'),
                        'notice_day_before_end'	=> $this->input->post('notice_day_before_end'),
                        'notice_hour_before_end'	=> $this->input->post('notice_hour_before_end'),
                        'select_all_tags'	=> $this->input->post('select_all_tags'),
                        'notice_new_purchases'	=> $this->input->post('notice_new_purchases'),
                        'notice_purchases_day_before_start'	=> $this->input->post('notice_purchases_day_before_start'),
                        'notice_purchases_day_before_end'	=> $this->input->post('notice_purchases_day_before_end'),
					);
					$bool_data_profile = $this->tank_auth->create_profile($data_profile);

					if($bool_data_profile){
                        $user_id = $this->db->insert_id();
                        $tags = array();
                        if (is_array($this->input->post('user_tags')))
                            $tags = $this->input->post('user_tags');
                        foreach ($tags as $tag) {
                            $tag_data = array(
                                'user_id' => $user_id,
                                'tag_id' => $tag,
                            );
                            $this->db->insert('users_tags', $tag_data);
                        }
                    }

					if ($email_activation) {									// send "activate" email
/*						$data['activation_period'] = $this->config->item('email_activation_expire', 'tank_auth') / 3600;

						$this->_send_email('activate', $data['email'], $data);
*/
						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_1'));
					} else {
/*						if ($this->config->item('email_account_details', 'tank_auth')) {	// send "welcome" email

							$this->_send_email('welcome', $data['email'], $data);
						}
*/
						$this->_send_email('newuser', "Новый пользователь", array('user_id' => $data['user_id']), $this->config->item('engine_admin_email'));
						unset($data['password']); // Clear password (just for any case)

						$this->_show_message($this->lang->line('auth_message_registration_completed_2'));
					}
				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
            $data['all_tags'] = $this->users->get_all_tender_tags();
			$data['use_username'] = $use_username;
			$data['page_title'] = 'Регистрация';
			$data['user_profile'] = array();
			$this->template->view('auth/register_form', $data);
		}
	}

	/**
	 * Generate reset code (to change password) and send it to user
	 *
	 * @return void
	 */
	function forgot_password()
	{
		if ($this->tank_auth->is_logged_in()) {									// logged in
			redirect('');

		} elseif ($this->tank_auth->is_logged_in(FALSE)) {						// logged in, not activated
			//redirect('/auth/send_again/');
			$this->_show_message($this->lang->line('auth_message_no_activation'));

		} else {
			$this->form_validation->set_rules('email', 'E-mail', 'trim|required|xss_clean');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok
				if (!is_null($data = $this->tank_auth->forgot_password(
						$this->form_validation->set_value('email')))) {

					// Send email with password activation link
					$this->_send_email('forgotpassword', "Восстановление пароля", array('user_id' => $data['user_id']));

					$this->_show_message($this->lang->line('auth_message_new_password_sent'));

				} else {
					$errors = $this->tank_auth->get_error_message();
					foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
				}
			}
			$data['page_title'] = 'Восстановление пароля';
			$this->template->view('auth/forgot_password_form', $data);
		}
	}

	/**
	 * Delete user from the site (only when user is logged in)
	 *
	 * @return void
	 */
	function unregister()
	{
		if (!$this->tank_auth->is_logged_in()) {								// not logged in or not activated
			redirect('');

		} else {
			$user_id = $this->uri->segment(3);

			if ($this->tank_auth->delete_user($user_id)) {		// success
				echo $this->lang->line('auth_message_unregistered');

			} else {														// fail
				echo $this->lang->line('auth_error_unregistered');

			}
		}
	}

	/**
	 * Show info message
	 *
	 * @param	string
	 * @return	void
	 */
	function _show_message($message)
	{
		$this->session->set_flashdata('message', $message);
		redirect('/auth/');
	}

	/**
	 * Send email message of given type (activate, forgot_password, etc.)
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	void
	 */
    function _send_email($type, $subject, $data = "", $admin_email = "")
    {
        $this->load->library('email');
        $this->load->model('tenders_data', 'tenders');
		
        $config['protocol'] = 'sendmail'; 
        $config['mailtype'] = 'html';
        $config['validate'] = true;
        $this->email->initialize($config);

        if (!empty($data['user_id']))
            $user_detail = $this->tank_auth->user($data['user_id']);

        $repl_array = array("%user%" => $user_detail['user_name'], "%email_user%" => $user_detail['email'], "%pass_user%" => $user_detail['password'], "%url_user%" => "http://" . $this->config->item('engine_url') . "/auth/user_edit/" . $user_detail['id'], "%url_site%" => "http://" . $this->config->item('engine_url'));

        $text_message = $this->tenders->get_settings("email-" . $type);
        $message = $text_message['value'];
        unset($text_message);
        if (!empty($message))
        {
            foreach ($repl_array as $key => $value) {
                $message = str_replace($key, $value, $message);
            }
        }
        $message = nl2br($message);

		$this->email->from($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
        $this->email->reply_to($this->config->item('engine_admin_email'), $this->config->item('engine_title'));
        $this->email->to( (!empty($admin_email) ? $admin_email : $user_detail['email']) );
        $this->email->subject($this->config->item('engine_title') . ": " . $subject);
        $this->email->message($message);
        $this->email->send();
        /*
                $this->email->from($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
                $this->email->reply_to($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
                $this->email->to($email);
                $this->email->subject(sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth')));
                $this->email->message($this->load->view('email/'.$type.'-html', $data, TRUE));
                $this->email->set_alt_message($this->load->view('email/'.$type.'-txt', $data, TRUE));
                $this->email->send();
        */
    }

	/**
	 * List users on the site
	 *
	 * @return void
	 */
	function users()
	{
		$group_id = $this->tank_auth->get_group_id();

		if (!$this->tank_auth->is_logged_in() || ((int)$group_id != 3 && (int)$group_id != 4) ) {		// logged in
			redirect('');

		} else {
			$data['users_list'] = $this->tank_auth->users_list();
			$data['page_title'] = 'Список пользователей';
			$data['group_id'] = $group_id;
			$this->template->view('users_list', $data);
		}
	}

	/**
	 * List users confirm on the site
	 *
	 * @return void
	 */
	function users_confirm()
	{
		$group_id = $this->tank_auth->get_group_id();

		if (!$this->tank_auth->is_logged_in() || ((int)$group_id != 3 && (int)$group_id != 4) ) {			// logged in
			redirect('');

		} else {
			$data['users_list'] = $this->tank_auth->users_list(TRUE);
			$data['page_title'] = 'Список ожидающих пользователей';
			$this->template->view('users_list', $data);
		}
	}

	/**
	 * List users in blacklist on the site
	 *
	 * @return void
	 */
	function users_blacklist()
	{
		$group_id = $this->tank_auth->get_group_id();

		if (!$this->tank_auth->is_logged_in() || ((int)$group_id != 3 && (int)$group_id != 4) ) {			// logged in
			redirect('');

		} else {
			$data['users_list'] = $this->tank_auth->users_list(FALSE, TRUE);
			$data['page_title'] = 'Черный список пользователей';
			$this->template->view('users_list', $data);
		}
	}

	/**
	 * List users on the site
	 *
	 * @return void
	 */
	function user_edit()
	{
		$user_id 		= (int)$this->uri->segment(3);
		$user_id_orig	= (int)$this->tank_auth->get_user_id();
		$group_id 		= (int)$this->tank_auth->get_group_id();

		if (empty($user_id)) $user_id = $user_id_orig;

		if (!$this->tank_auth->is_logged_in() || ( ($group_id != 3 && $user_id != $user_id_orig) && ($group_id != 4 && $user_id != $user_id_orig) ) ) {									// logged in
			redirect('');

		} else {

			$this->form_validation->set_rules('name', 'Наименование организации (ФИО)', 'trim|required|xss_clean');
			$this->form_validation->set_rules('legal_form', 'Организационно-правовая форма', 'trim|required|xss_clean');
			$this->form_validation->set_rules('region', 'Регион', 'trim|required|xss_clean');
			$this->form_validation->set_rules('city', 'Населенный пункт', 'trim|required|xss_clean');
			$this->form_validation->set_rules('director_name', 'ФИО руководителя предприятия', 'trim|required|xss_clean');
			$this->form_validation->set_rules('inn', 'ИНН', 'trim|required|xss_clean|numeric');
			$this->form_validation->set_rules('kpp', 'КПП', 'trim|required|xss_clean|numeric');

			$this->form_validation->set_rules('register_password', 'Пароль', 'trim|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
			$this->form_validation->set_rules('confirm_password', 'Еще раз Пароль', 'trim|xss_clean|matches[register_password]');

			$data['notice'] = "";
			$data['user_profile'] = $this->tank_auth->user($user_id);

			if ($this->form_validation->run()) {								// validation ok

				if (!empty($data['user_profile']) && ((int)$group_id == 3 || (int)$group_id == 4))
				{
					$data_user['activated'] = $this->input->post('activated');
					$data_user['banned'] = $this->input->post('banned');
					$data_user['ban_reason'] = $this->input->post('ban_reason');

					if ($this->input->post('banned') == 1)
						$this->_send_email('banneduser', "Ваша учетная запись заблокирована по причине: " . $this->input->post('ban_reason'), array('user_id' => $user_id));
					elseif (empty($data_user['banned']) && $data_user['activated'] == 1) {
						$this->_send_email('welcomeuser', "Ваша учетная запись активирована", array('user_id' => $user_id));
					}

				}

				$pass = $this->input->post('register_password');
				if (!empty($pass))
					$data_user['password'] = $pass;

				if ( !empty($data_user) )
					$this->tank_auth->update_user($user_id, $data_user);

				$data_profile = array(
					'name'	=> $this->input->post('name'),
					'type_data'	=> $this->input->post('type_data'),
					'legal_form'	=> $this->input->post('legal_form'),
					'small_business'	=> $this->input->post('small_business'),
					'region'	=> $this->input->post('region'),
					'city'	=> $this->input->post('city'),
					'address'	=> $this->input->post('address'),
					'fact_address'	=> $this->input->post('fact_address'),
					'phone'	=> $this->input->post('phone'),
					'add_email'	=> $this->input->post('add_email'),
					'fax'	=> $this->input->post('fax'),
					'director_name'	=> $this->input->post('director_name'),
					'contacts'	=> $this->input->post('contacts'),
					'certifikates'	=> $this->input->post('certifikates'),
					'organization_date'	=> $this->input->post('organization_date'),
					'inn'	=> $this->input->post('inn'),
					'kpp'	=> $this->input->post('kpp'),
					'employes_count'	=> $this->input->post('employes_count'),
					'okved'	=> $this->input->post('okved'),
					'services'	=> $this->input->post('services'),
					'requisites'	=> $this->input->post('requisites'),
					'notice_other_members'	=> $this->input->post('notice_other_members'),
					'notice_new_auctions'	=> $this->input->post('notice_new_auctions'),
                    /*new notifications*/
                    'notice_disable'	=> $this->input->post('notice_disable'),
                    'notice_day_before_start'	=> $this->input->post('notice_day_before_start'),
                    'notice_hour_before_start'	=> $this->input->post('notice_hour_before_start'),
                    'notice_day_before_end'	=> $this->input->post('notice_day_before_end'),
                    'notice_hour_before_end'	=> $this->input->post('notice_hour_before_end'),
                    'select_all_tags'	=> $this->input->post('select_all_tags'),
                    'notice_new_purchases'	=> $this->input->post('notice_new_purchases'),
                    'notice_purchases_day_before_start'	=> $this->input->post('notice_purchases_day_before_start'),
                    'notice_purchases_day_before_end'	=> $this->input->post('notice_purchases_day_before_end'),
				);

				if (!empty($data['user_profile']) && (int)$group_id == 3)
					$data_profile['group_id'] = $this->input->post('group');

				$bool_data_profile = $this->tank_auth->update_profile($user_id, $data_profile);

				if ($bool_data_profile == TRUE)
					$data['notice'] = "<span style=\"color: green; font-weight: bold;\">" . $this->lang->line('auth_message_save_completed') . "</span>";
				else
					$data['notice'] = "<span style=\"color: red; font-weight: bold;\">" . $this->lang->line('auth_message_save_notcompleted') . "</span>";

                //добавление тегов
                $this->db->where('user_id', $user_id);
                $this->db->delete('users_tags');
                $tags = array();
                if (is_array($this->input->post('user_tags')))
                    $tags = $this->input->post('user_tags');
                foreach ($tags as $tag) {
                    $tag_data = array(
                        'user_id' => $user_id,
                        'tag_id' => $tag,
                    );
                    $this->db->insert('users_tags', $tag_data);
                }

                redirect('/auth/user_edit/');
			}

            $data['all_tags'] = $this->users->get_all_tender_tags();
            $user_tags = array();
            $tags = $this->users->get_tags_by_user_id($user_id);
            if ($tags != null) {
                foreach ($tags as $one) {
                    $user_tags[] = $one['tag_id'];
                }
            }
            $data['user_tags'] = $user_tags;
			$data['page_title'] = 'Редактирование профиля';
			$this->template->view('auth/register_form', $data);
		}
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */