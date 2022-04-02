<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Template library
 *
 * Library of additional functionality CodeIgniter
 *
 * @package    BackOffice CMS
 * @subpackage Templates
 * @category   Libraries
 * @author     Pavel Istomin <pavel@istom.in>
 * @copyright  Copyright (c) 2008-2009, MixxStudio
 * @link       http://www.mixxstudio.ru/
 * @version    1.0.0
 */

class Template extends CI_Controller
{
	function __construct()
	{
		$this->ci =& get_instance();

		log_message('debug', "Template Class Initialized");
	}

	function view($template = '', $data = array())
	{
		// Настройки
		$data['config'] =& get_config();

		$this->ci->load->library('tank_auth');

		if ($this->ci->tank_auth->is_logged_in())
		{
			$data['user_id']	= $this->ci->tank_auth->get_user_id();
			$data['name']		= $this->ci->tank_auth->get_name();
			$data['group_id']	= $this->ci->tank_auth->get_group_id();
			$data['group_title']= $this->ci->tank_auth->get_group_title();

			if (empty($data['name']) || empty($data['group_id']) || empty($data['group_title']))
			{
				$this->ci->tank_auth->logout();

				redirect('/');
			}
		}
		else
		{
			$data['user_id'] = $data['name'] = $data['group_id'] = $data['group_title'] = "";
		}

		// Загрузка шапки, контента и подвала
		$header_cache = $this->ci->load->view('header', $data, true);
		$tmpl_cache = $this->ci->load->view($template, $data, true);
		$footer_cache = $this->ci->load->view('footer', $data, true);
		echo $header_cache.$tmpl_cache.$footer_cache;
	}
}


/* End of file Template.php */
/* Location: /application/libraries/Template.php */