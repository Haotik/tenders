<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->library('tank_auth');
		$this->load->model('tenders_data', 'tenders');

		if (!$this->tank_auth->is_logged_in() || $this->tank_auth->get_group_id() == 1)
			redirect('');
	}

	/*** Главная страница ***/
	function index()
	{
		$data['page_title'] = 'Глобальные настройки';
		$data['message'] = $this->session->flashdata('notice');

		$settings = $this->tenders->get_settings();
		foreach ($settings as $key => $value) {
			$data['setting'][$value['name']] = $value['value'];
		}

		$this->template->view('settings', $data);
	}

	/*** Сохранение настроек ***/
	function save_settings()
	{
		$this->tenders->set_settings('autorefresh', $this->input->post('autorefresh'));
		$this->tenders->set_settings('monthsarchive', $this->input->post('monthsarchive'));
		$this->tenders->set_settings('email-newuser', $this->input->post('email-newuser'));
		$this->tenders->set_settings('email-forgotpassword', $this->input->post('email-forgotpassword'));
		$this->tenders->set_settings('email-welcomeuser', $this->input->post('email-welcomeuser'));
		$this->tenders->set_settings('email-banneduser', $this->input->post('email-banneduser'));
		$this->tenders->set_settings('email-newtender', $this->input->post('email-newtender'));
		$this->tenders->set_settings('email-updatetender', $this->input->post('email-updatetender'));

		$this->session->set_flashdata('notice', '<span style="color: green; font-weight: bold;">Настройки сохранены успешно</span>');

		redirect('/settings/');
	}
}

/* End of file settings.php */
/* Location: ./application/controllers/settings.php */