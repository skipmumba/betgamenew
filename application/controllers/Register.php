<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class register extends CI_Controller{

  public function __construct()
  {
    parent::__construct();
    //Codeigniter : Write Less Do More
  }

  function check_reg()
  {
	$data = json_decode(file_get_contents('php://input'),true);
	$email = $data['email'];
	$pass = $data['pass'];
	if($this->isValidEmail($email))
	{
		$info = array('member_email'=>$email,'member_pass'=>$pass,'member_day'=>date('d:m:y'));
		$this->db->insert('member_regis',$info);
		if($this->db->affected_rows() > 0)
		{
		    echo 'succ';
		}
	}
	else
	{
		echo 'false';
	}

  }

function 	check_email()
	{
		$data = json_decode(file_get_contents('php://input'),true);
		$email = $data['email'];
		$this->db->where('member_email',$email);
	     $this->db->from("member_regis");
		$query=$this->db->count_all_results();
		if($query==0)
		{
			echo 'ok';
		}
		else
		{
			echo 'no';
		}
	}

  function isValidEmail($email)
  {
 		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
  }

}
