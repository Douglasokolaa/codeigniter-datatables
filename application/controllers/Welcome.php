<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->helper('url');
		$this->load->view('home');
	}

	public function load_data()
	{
		$this->load->library('datatables_server_side', array(
			'table' => 'customer',
			'primary_key' => 'customer_id',
			'columns' => array('first_name', 'last_name', 'email'),
			'where' => array()
		));

		$this->datatables_server_side->process();
	}
}
