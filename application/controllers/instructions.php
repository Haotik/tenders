<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Instructions extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->library('tank_auth');
	}

	/*** Главная страница ***/
	function index()
	{
		$structure = $this->db->query("SELECT `content` FROM `structure` WHERE `id` = 1 LIMIT 1");
		$row_structure = $structure->row_array();

		$data['page_title'] = 'Инструкция';
		$data['page_content'] = $row_structure['content'];

		$this->template->view('instructions', $data);
	}

	/*** Редактирование ***/
	function edit()
	{
		if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1)
		{
			$structure = $this->db->query("SELECT `content` FROM `structure` WHERE `id` = 1 LIMIT 1");
			$row_structure = $structure->row_array();

			$data['page_title'] = 'Редактировать инструкцию';
			$data['page_content'] = $row_structure['content'];
			$data['message'] = $this->session->flashdata('notice');

			$this->template->view('instructions_edit', $data);
		}
		else
		{
			redirect('/instructions/');
		}
	}

	/*** Сохранение ***/
	function save()
	{
		if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1)
		{
			if ($this->db->query("UPDATE `structure` SET `content` = '" . $this->input->post('page_content', TRUE) . "' WHERE `id` = 1;"))
				$this->session->set_flashdata('notice', '<span style="color: green; font-weight: bold;">Инструкция сохранена успешно</span>');
			else
				$this->session->set_flashdata('notice', '<span style="color: red; font-weight: bold;">Произошел сбой при сохранении инструкции</span>');

			redirect('/instructions/edit/');
		}
		else
		{
			redirect('/instructions/');
		}
	}
}

/* End of file instructions.php */
/* Location: ./application/controllers/instructions.php */