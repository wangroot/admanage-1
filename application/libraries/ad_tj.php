<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ad_tj { 

	public $username; 
	public $up_time; 
	public $down_time; 
	public $obj; 
	public $query;
	public $adcount;
	
	 
	 
	function ad_count($username,$up_time,$down_time){
	 
		$this->obj =& get_instance();  
		$this->obj->load->database();
		$this->obj->db->from('ad');
		$this->obj->db->select_sum('price');
		$this->obj->db->where('state',2);
		$this->obj->db->where('add_user',$username);
		$this->obj->db->where('add_time >',$up_time);
		$this->obj->db->where('add_time <',$down_time);
		$query = $this->obj->db->get();

		$row = $query->first_row();
		$adcount = $row->price;
		if ($adcount=="")
		{
			$adcount = 0;
		}
		return $adcount;
	 
	} 

  
}
