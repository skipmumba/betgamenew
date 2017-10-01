<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class addmatch extends CI_Controller{

  public function __construct()
  {
    parent::__construct();
    //Codeigniter : Write Less Do More
  }

 	public function addcat()
	{
		if(isset($_POST['catname']))
		{
			$data = array(
			        'cat_name' => $this->input->post('catname')
			);

			if($this->db->insert('catgame', $data))
			{
				redirect($_POST['hostng'].'/addgame','refresh');
			}
		}
	}
	public function list_allcat()
	{
		$json = array();

		$this->db->from('catgame');
		$this->db->order_by("cat_id", "DESC");
		$query = $this->db->get();

		foreach ($query->result() as $row)
		{
		        $json[]=$row;
		}
		echo json_encode($json);
	}
	public function del_cat($id)
	{
		$this->db->delete('catgame', array('cat_id' => $id));
		if($this->db->affected_rows())
			{
			 	 redirect($this->config->item('hostng'),'refresh');
			}	 
	}

	public function addgame()
	{
		if(!empty($_POST['nameTeam1']))
		{
			$explode_cat=explode('*-*',$this->input->post('catid'));
			$data = array(
			        'team_1' => $this->input->post('nameTeam1'),
			        'team_2' => $this->input->post('nameTeam2'),
			        'team1pic' => $this->input->post('picTeam1'),
			        'team2pic' => $this->input->post('picTeam2'),
			        'day' => $this->input->post('day'),
			        'month' => $this->input->post('month'),
			        'year' => $this->input->post('year'),
			        'time' => $this->input->post('time'),
			        'cat_id' => $explode_cat[0],
			        'cat_name' => $explode_cat[1] ,
			);

			if($this->db->insert('matchGame', $data))
			{
				redirect($this->config->item('hostng').'/addgame','refresh');
			}
		}
	}
	public function get_data_sql($table,$column,$start=0)
	{
		$perpage = 15;
		$json = array();
		// $this->db->from($table);
		// $this->db->order_by($column, "DESC");
		// $query = $this->db->get();
		if($start<=1)
		{
			$start = 0;
		}
		else 
		{
			$start = ($start * $perpage) - $perpage + 1 ;
		}
		$query = $this->db->get($table,$perpage,$start);
		foreach ($query->result() as $row)
		{
		        $json[]=$row;

		}
		print_r(json_encode($json));
	}
	public function countData($table)
	{
		$query = $this->db->query("SELECT * FROM {$table}");

		echo $query->num_rows();
	}

	public function addTeam()
	{
		if(isset($_POST['Name']))
		{
			$teamName = $this->input->post('Name');
			$teamImg = $this->input->post('Image');
			$data = array('team_name'=>$teamName,'team_imageurl'=>$teamImg);
			if($this->db->insert('storeteam', $data))
			{
				redirect($this->config->item('hostng').'/addgame','refresh');
			}
		}
	}
	public function listTeam()
	{
		$query = $this->db->get('storeteam');
		$json = array();
		foreach($query->result() as $row)
		{
			 $json[]=$row;
		}
		echo json_encode($json);
	}
}
