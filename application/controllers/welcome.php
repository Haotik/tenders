<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->template->view('welcome');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */