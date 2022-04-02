<?php

/**
 * Responses controller
 *
 * Контроллер раздела сайта "Отзывы"
 *
 * @package    BackOffice CMS
 * @subpackage Pages
 * @category   Controllers
 * @author     Pavel Istomin <pavel@istom.in>
 * @copyright  Copyright (c) 2008-2009, MixxStudio
 * @link       http://www.mixxstudio.ru/
 * @version    1.0.0
 */
/*==========================================================================*\
|| ######################################################################## ||
|| # Передача третьим лицам, равно как и продажа исходных кодов данного   # ||
|| # программного обеспечения ЗАПРЕЩЕНО.                                  # ||
|| #---------------BackOffice CMS IS NOT FREE SOFTWARE--------------------# ||
|| ######################################################################## ||
\*==========================================================================*/

class Responses extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->library('tank_auth');
		$this->load->model('tenders_data', 'tenders');

		if (!$this->tank_auth->is_logged_in())
			redirect('');
	}

	/**
	 * [page] Основная страница
	 *
	 */
	function index($page = 0)
	{
		// Выборка страницы
		$data['page_title'] = 'Отзывы';

		// Выборка отзывов
		$items_per_page = 10;
		if (empty($page))
		{
			$page = intval( (int)$page * $items_per_page);
		}
		else
		{
			$page = $page/10;
		}
		$comments = $this->tenders->get_comments(0, $page, $items_per_page);
		$data['comments'] = $comments;
		$data['comments_count'] = $this->tenders->get_comments_count();

		// Постраничная навигация
		$this->load->library('pagination');
		$conf['base_url'] = base_url() . "/responses";
		$conf['uri_segment'] = sizeof($this->uri->segments);
		$conf['total_rows'] = (int)$data['comments_count'];
		$conf['per_page'] = (int)$items_per_page;
		$conf['full_tag_open'] = '<div class="paginate">';
		$conf['full_tag_close'] = '<div class="clear"></div></div>';
		$conf['first_link'] = 'Первая';
		$conf['prev_link'] = 'Назад';
		$conf['next_link'] = 'Вперед';
		$conf['last_link'] = 'Послед.';
		$conf['cur_tag_open'] = '<span>';
		$conf['cur_tag_close'] = '</span>';
		$conf['prev_tag_open'] = "<span class=\"back\">";
		$conf['next_tag_open'] = "<span class=\"next\">";
		$conf['next_tag_close'] = $conf['prev_tag_close'] = "</span>";
		$conf['first_tag_open'] = "<span class=\"back\">";
		$conf['last_tag_open'] = "<span class=\"next\">";
		$conf['first_tag_close'] = $conf['last_tag_close'] = "</span>";
		$this->pagination->initialize($conf);

		$data['paginate'] = $this->pagination->create_links();

		$this->template->view("responses", $data);
	}

	/**
	 * [process] Сохранение комментария
	 *
	 */
	function save()
	{
		if (!$this->tank_auth->is_logged_in()) {									// logged in
			redirect('');

		} else {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('responses', 'Комментарий', 'trim|required|xss_clean');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok

				$responses = $this->input->post('responses');
				$user_id = $this->tank_auth->get_user_id();

				// Сохраняем отзыв
				$data_comment = array('id' => NULL, 'user_id' => $user_id, 'date_publish' => time(), 'comment' => $responses, 'answer' => "");
				$comment_id = $this->tenders->create_comment($data_comment);

				if ( $comment_id )
					echo "success|Отзыв успешно опубликован";
				else
					echo "error|Ошибка при сохранении отзыва";
			}

		}

	}

	/**
	 * [page] Ответ на отзыв
	 *
	 */
	function answer($comment_id = 0)
	{
		if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1)
		{
			$comment = $this->tenders->get_comments((int)$comment_id);
			$data['comment'] = $comment;

			$this->template->view('responses_edit', $data);
		}
		else
			redirect('');
	}

	/**
	 * [process] Сохранение ответа
	 *
	 */
	function saveanswer($comment_id = 0)
	{
		if (!$this->tank_auth->is_logged_in()) {									// logged in
			redirect('');

		} else {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('answer', 'Ответ на комментарий', 'trim|required|xss_clean');

			$data['errors'] = array();

			if ($this->form_validation->run()) {								// validation ok

				$answer = $this->input->post('answer');

				// Сохраняем отзыв
				$data_comment = array('answer' => $answer);
				$comment_flag = $this->tenders->update_comment($data_comment, (int)$comment_id);

				if ( $comment_flag )
					echo "success|Ответ успешно опубликован";
				else
					echo "error|Ошибка при сохранении ответа";

			} else {
				$errors = $this->tank_auth->get_error_message();
				foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
			}

		}

	}

	/*** Удаление отзыва ***/
	function delete($comment_id = 0)
	{
		if ($this->tank_auth->is_logged_in() && $this->tank_auth->get_group_id() != 1)
		{
			if ($comment_id > 0)
			{
				if ($this->tenders->delete_comment($comment_id) == TRUE)
					echo "success|Отзыв успешно удален";
				else
					echo "error|Ошибка удаления отзыва";
			}
			else
				echo "error|Ошибка удаления отзыва";
		}
		else
			redirect('');
	}

}

/* End of file responses.php */
/* Location: ./system/application/controllers/responses.php */