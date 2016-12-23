<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Madmin extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	/* 用户列表  */
	function get_user()
	{
		$query = $this->db->query("SELECT * FROM admin");
		return $query->result();
	}
	/* 业务员列表  */
	function get_user_yw()
	{
		$query = $this->db->query("SELECT * FROM admin where limits like '%71%'");
		return $query->result();
	}
	/* 站点列表  */
	function get_site()
	{
		$query = $this->db->query("SELECT * FROM site");
		return $query->result();
	}
	/* 有站点代码的站点列表  */
	function get_site_code()
	{
		$query = $this->db->query("SELECT * FROM site where site_code<>''");
		return $query->result();
	}
	/* 按ID用户列表  */
	function get_user_name($id)
	{
		$query = $this->db->query("SELECT * FROM admin WHERE id=$id");
		return $query->result();
	}
	/* 按ID站点列表  */
	function get_site_id($id)
	{
		$query = $this->db->query("SELECT * FROM site WHERE id=$id");
		return $query->result();
	}
	/* 按ID JS组列表  */
	function get_js_id($id)
	{
		$query = $this->db->query("SELECT * FROM js WHERE id=$id");
		return $query->result();
	}
	/* JS组列表  */
	function get_js()
	{
		$query = $this->db->query("SELECT * FROM js");
		return $query->result();
	}
	/* 按ID广告内容 */
	function get_ad_id($id)
	{
		$query = $this->db->query("SELECT * FROM ad WHERE id=$id");
		return $query->result();
	}
	/* 按ID广告位内容  */
	function get_ad_site_id1($id)
	{
		$query = $this->db->query("SELECT * FROM ad_site WHERE id=$id");
		return $query->result();
	}
	/* 按站点ID广告位列表  */
	function get_ad_site_id($id)
	{
		$query = $this->db->query("SELECT * FROM ad_site WHERE site=$id order by ad_site_name");
		return $query->result();
	}
	/* 按ID返回广告位名称 */
	function get_ad_site_name($id)
	{
		$query = $this->db->query("SELECT * FROM ad_site WHERE id=$id");
		if ($query->num_rows() > 0)
		{
			$ad_site = $query->first_row();
			return $ad_site->ad_site_name;
		}
		else
		{
			return '不存在';
		}
	}
	/* 按ID返回站点名称 */
	function get_site_name($id)
	{
		$query = $this->db->query("SELECT * FROM site WHERE id=$id");
		if ($query->num_rows() > 0)
		{
			$site = $query->first_row();
			return $site->site_name;
		}
		else
		{
			return '不存在';
		}
	}
	/* 按ID返回广告名称 */
	function get_ad_name($id)
	{
		$query = $this->db->query("SELECT * FROM ad WHERE id=$id");
		if ($query->num_rows() > 0)
		{
			$site = $query->first_row();
			return $site->ad_name;
		}
		else
		{
			return '不存在';
		}
	}
	/* 按用户名 用户列表  */
	function get_user_username($username)
	{
		$query = $this->db->query("SELECT * FROM admin WHERE user_name='$username'");
		return $query->num_rows();
	}
	/* 按传过来的sql，返回该数据集的行数  */
	function get_sql($sql)
	{
		$query = $this->db->query($sql);
		return $query->num_rows();
	}
	/* 登陆验证  */
	function login_ok()
	{
		$name = $this->input->post('user');
		$password = md5($this->input->post('password'));

		$query = $this->db->query("SELECT * FROM admin WHERE user_name='$name' and password='$password' ");
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			return False;
		}
	}

}

/* End of file madmin.php */
/* Location: ./application/models/madmin.php */
?>
