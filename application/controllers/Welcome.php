<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->library('form_validation');
    }

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function testForm()
	{
		$this->load->view('test/head');
		$this->load->view('test/test-form');
		$this->load->view('test/footer');
	}

	public function testValidations()
	{
		$this->form_validation->set_rules('field1', 'Field1', 'required');
		$this->form_validation->set_rules('field2', 'Field2', 'alwaysFail[error on select <b>%s</b>, because i want]');
		$this->form_validation->set_rules('field3', 'Field3', 'required');
		$this->form_validation->set_rules('field4', 'Field4', 'alwaysFail[no idea why this is an error on <b>%s</b>]');
		$this->form_validation->set_rules('field5', 'Field5', 'required');
		if($this->form_validation->run()===TRUE)
		{
			echo json_encode( array("status"=>"correct"));
		}
		else
			echo json_encode(array("status"=>"error","errorList"=>$this->form_validation->error('*')));

	}
}
