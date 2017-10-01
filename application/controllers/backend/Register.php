<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class register extends CI_Controller{
	public function __construct()
	{
	   	parent::__construct();
	    //Codeigniter : Write Less Do More
	}

	public function sameUser($username)
	{
		$this->db->from('member');
		$this->db->where('member_email',$username);
		$query = $this->db->get();
		$count = $query->num_rows();
		if($count > 0)
		{
			echo json_encode(array('state'=>true)); // 1 have
		}
		else 
		{
			echo json_encode(array('state'=>false)); // 0 no
		}
	}

	public function sameUserSserver($username)
	{
		$this->db->from('member');
		$this->db->where('member_email',$username);
		$query = $this->db->get();
		$count = $query->num_rows();
		if($count > 0)
		{
			return $count;
		}
		else 
		{
			return $count;
		}
	}

	public function regisTer()
	{
		$data = json_decode(file_get_contents('php://input'),true);
		$email = $data['email'];
		$pass = $data['pass'];
		$checkUSer = $this->sameUserSserver($email);
		$patternEmail ="/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
		$patternPass ="/^[a-zA-Z0-9_-]{8,30}$/";
		if($checkUSer < 1)
		{
			if (preg_match($patternEmail, $email) === 1) 
			{
				$lenEmail = strlen($email);
				if($lenEmail > 7 )
				{
					$lenPass = strlen($pass);
					if (preg_match($patternPass, $pass) === 1) 
					{					
						if($this->insertMember($email,$pass))
						{
							echo json_encode(array('regisStatus'=>1)); // register ok
						}
						else 
						{
							echo json_encode(array('regisStatus'=>0));
						}
					}
					else 
					{
						echo 'noooooooooooooo';
					}

				}
				else 
				{
					echo 'what are you trying to do Bitch !!';
				}
			}
			else 
			{
				echo 'fuck off you cunts !!!';
			}
		}
		else 
		{
			echo 'fuck off bitch !!!';
		}
	}

	public function insertMember($email,$pass)
	{
		$time = date('dmys');
		$ran = rand(date('s'),rand(1,99));
		$ranNow = $time.''.$ran;
		$data = array(
	        'member_email' => $email,
	        'member_pass' => $pass,
	        'member_code' => $ranNow,
		);

		$this->db->insert('member', $data);
		if($this->db->affected_rows() > 0)
		{
		    return true;
		}
		else 
		{
			return false;
		}
	}

}
