<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class login extends CI_Controller{

	public function memberLogin()
	{
		$data = json_decode(file_get_contents('php://input'),true);
		$email = $data['email'];
		$pass = $data['pass'];
		$this->db->from('member');
		$this->db->where('member_email',$email);
		$this->db->where('member_pass',$pass);
		$query = $this->db->get();
		$count = $query->num_rows();
		if($count > 0)
		{
			foreach ($query->result() as $row)
			{			    
				echo json_encode(array('statusLogin'=>true,'memberCode'=>$row->member_code,'email'=>$row->member_email));
			}
		}
		else 
		{
			echo json_encode(array('statusLogin'=>false));
		}
	}
}