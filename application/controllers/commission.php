<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Commission extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->library('tank_auth');
		$this->load->model('commission_data', 'commission');

		if (!$this->tank_auth->is_logged_in() || $this->tank_auth->get_group_id() == 1)
			redirect('');
	}

	/*** Состав комиссии ***/
	function index()
	{
		$data['page_title'] = 'Состав комиссии';

		if ($this->tank_auth->get_group_id() == 2)
			$user_id = $this->tank_auth->get_user_id();
		else
			$user_id = 0;

		$data['commission_list'] = $this->commission->get_tenders_commission($user_id);

		$this->template->view('commission/list', $data);
	}

	/*** Добавление личности ***/
	function add_user()
	{
		$data['page_title'] = 'Добавление личности';
		$data['no_user'] = FALSE;

		$this->template->view('commission/add_form', $data);
	}

	/*** Редактирование личности ***/
	function edit_user($person_id = 0)
	{
		$data['page_title'] = 'Редактирование личности';
		$data['no_user'] = FALSE;

		if ($person_id < 0)
			$data['no_user'] = TRUE;
		else
		{
			if ($this->tank_auth->get_group_id() == 2)
				$user_id = $this->tank_auth->get_user_id();
			else
				$user_id = 0;

			$person = $this->commission->get_person_by_id((int)$person_id, (int)$user_id);
			if ( $person )
			{
				$data['person'] = $person;
			}
			else
				$data['no_user'] = TRUE;
		}

		$data['person_id'] = (int)$person_id;

		$this->template->view('commission/add_form', $data);
	}

	/**
	 * Сохранение персоны в базе
	 *
	 * @return void
	 */
	function save()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('rank', 'Ранг', 'trim|required|xss_clean');
		$this->form_validation->set_rules('fio', 'ФИО', 'trim|required|xss_clean');
		$this->form_validation->set_rules('post', 'Должность', 'trim|required|xss_clean');

		$data['errors'] = array();
		$is_edit = FALSE;

		if ($this->form_validation->run()) {								// validation ok

			// Проверка ID персоны
			if ( !$this->commission->get_person_by_id((int)$this->input->post('person_id')) )
			{
				$is_edit = FALSE;
			}
			else
			{
				$person_id = (int)$this->input->post('person_id');
				$is_edit = TRUE;
			}

			$user_id = $this->tank_auth->get_user_id();
			$created = date("Y-m-d H:i:s", time());

			if ($is_edit == TRUE)
				$data['id'] = $person_id;

			$data = array(
				'admin_id'					=> (int)$user_id,
				'created'					=> $created,
				'rank'						=> $this->input->post('rank'),
				'fio'						=> $this->input->post('fio'),
				'post'						=> $this->input->post('post')
			);

			if ($is_edit == FALSE)
				$bool_data_person = $this->commission->create_person($data);
			else
				$bool_data_person = $this->commission->update_person($data, $person_id);

			if ($bool_data_person)
			{
				if ($is_edit == FALSE)
					echo "success|Личность успешно добавлена";
				else
					echo "success|Личность успешно сохранена";
			}
			else
				echo "error|Данные о личности не сохранены из-за возникших ошибок";
		}

		return TRUE;
	}

	/*** Удаление личности ***/
	function delete($person_id = 0)
	{
		if ($person_id > 0)
		{
			if ($this->commission->delete_person($person_id) == TRUE)
				echo "success|Личность успешно удалена";
			else
				echo "error|Ошибка удаления личности";
		}
		else
			echo "error|Ошибка удаления личности";
	}

}

/* End of file commission.php */
/* Location: ./application/controllers/commission.php */