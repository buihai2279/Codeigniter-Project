<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}
	public function Load_view($view='',$data='')
	{
        $this->load->view('back-end/header');
        $this->load->view($view, $data);
        $this->load->view('back-end/footer');
	}
	public function Get_user_by_mail($mail=''){
		$query = $this->db->get_where('user',array('mail' => $mail));
		if ($query->num_rows()==1)
			return $query->row();
		else
			return FALSE;
	}
	public function Get_user_by_id($id=''){
		$query = $this->db->get_where('user',array('id' => $id));
		if ($query->num_rows()==1)
			return $query->row();
		else
			return FALSE;
	}
	public function check_admin(){
		if ($this->session->has_userdata('login')==TRUE&&$this->session->userdata('level')==2)
			return TRUE;
		else return FALSE;
	}
	public function check_manager(){
		if ($this->session->has_userdata('login')==TRUE&&$this->session->userdata('level')==2)
			return TRUE;
		else return FALSE;
	}
	public function Check_login($mail='',$pass=''){
		$query = $this->db->get_where('user',array('mail' => $mail,'password'=>$pass));
		if ($query->num_rows()==1)
			return $query->row();
		else
			return FALSE;
	}
	public function Get_limit($table,$limit=15,$offset=10){
		$this->db->from($table);
        $this->db->limit($limit,$offset);
        $query = $this->db->get();
        return $query->result();
	}
	public function Count_table($table){
		return $this->db->count_all('user');
	}
	public function Get_user_by_code($code=''){
		$query = $this->db->get_where('user',array('code' => $code));
		if ($query->num_rows()==1)
			return $query->row();
		else
			return FALSE;
	}
	public function Check_user_by_mail($mail){
		$query = $this->db->select('mail')->get_where('user',array('mail'=>$mail));
		if ($query->num_rows()==1)
			return TRUE;
		else
			return FALSE;
	}
	public function Update($id,$array,$table){
	 	return $this->db->where('id',$id)->update($table,$array);
	}
	public function Block($id,$table){
	 	return $this->db->where('id',$id)->update($table,array('status' => 2));
	}
	public function Un_block($id,$table){
	 	return $this->db->where('id',$id)->update($table,array('status' => 1));
	}
	public function Delete($id,$table){
	 	return $this->db->delete($table, array('id' => $id));
	}
	public function Get_all($table){
	 	// if ($col==''&&$sort=='')
	 		$query=$this->db->get($table);
	 	// else
	 		// $query=$this->db->get($table);
	 	return $query->result_array();
	}
	public function Get_col($var='',$table){
	 	// if ($col==''&&$sort=='')
	 	$this->db->select($var);
        $this->db->from($table);
        // $this->db->where('id', $id); 
        $query = $this->db->get();
		
	 	// else
	 		// $query=$this->db->get($table);
	 	return $query->result_array();
	}
	public function Create_pagination($link,$total,$offset=1,$uri_segment=3){
		$this->load->library('pagination');
		$config['base_url'] = $link;
		$config['total_rows'] = $total;
		$config['per_page'] = $this->limit;
		$config['uri_segment'] = $uri_segment;
		$config['num_links'] = 3;
		$config['full_tag_open'] = '<nav><ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul></nav>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['first_link'] = 'First';
		$config['first_tag_open'] = '<li class="">';
		$config['first_tag_close'] = '</li>';
		$config['last_link'] = 'Last';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['next_link'] = '<i class="fa fa-caret-right"></i>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['prev_link'] = '<i class="fa fa-caret-left"></i>';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a>';
		$config['cur_tag_close'] = '</a></li>';
		$this->pagination->initialize($config);
		return $this->pagination->create_links();
	}
}

/* End of file My_model.php */
/* Location: ./application/models/My_model.php */
