<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->helper('news_helper');
		$this->load->model('Madmin');
	}

	/* 首页  */
	function index()
	{
		$this->load->view('index');
	}
	/* 登陆验证页面  */
	function check_login()
	{
		$this->load->helper('news');

		$query = $this->Madmin->login_ok();
	
		if ($query)
		{
			foreach($query as $row)
			{
			$this->session->set_userdata('manager', $row->user_name);
			$this->session->set_userdata('limits', $row->limits);
			showmessage('登陆成功', 'admin/index');
			}
		}
		else
		{
			showmessage('登陆失败，系统繁忙或着填写错误', 'home/index');
		}
	}
	/* 链接调用IP统计页面 */
	function show()
	{
		$id = $this->uri->segment(3);
		
		$query1 = $this->db->query("SELECT * FROM ad WHERE id=$id");
		
		if ($query1->num_rows() > 0)
		{
			$ad = $query1->first_row();
			
			$arr['ip'] = $this->input->ip_address();
			$arr['ad_site_id'] = $ad->ad_site;
			$arr['ad_id'] = $ad->id;
			$arr['add_time'] = now();
			$table = 'ip';
			
			$res = $this->db->insert($table, $arr);
			
			$this->db->query("UPDATE ad SET clicks=clicks+1 WHERE id=$ad->id");
			$this->db->query("UPDATE ad_site SET clicks=clicks+1 WHERE id=$ad->ad_site");
			
			redirect($ad->ad_url);
		}
	}
	/* JS广告位调用页面 */
	function view()
	{
		$this->load->helper('text');
		
		$id = $this->uri->segment(3);
		
		$query = $this->db->query("SELECT width, height, ad_site_css FROM ad_site WHERE id=$id");
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				
				$width = $row->width;
				$height = $row->height;
				$ad_site_css = $row->ad_site_css;
				
				$today = now();
				
				$query1 = $this->db->query("SELECT * FROM ad WHERE ad_site=$id and down_time>$today and up_time<$today and image<>'' order by id desc");
				
				if ($query1->num_rows() > 0)
				{
					$ad = $query1->first_row();
					//广告表显示数量自加1
					$this->db->query("UPDATE ad SET showsl=showsl+1 WHERE id=$ad->id");
					//广告位表显示数量自加1
					$this->db->query("UPDATE ad_site SET showsl=showsl+1 WHERE id=$id");
					
					//依照后缀显示内容
					switch(substr($ad->image,-3)){
					
						case 'jpg':{
							if ($ad_site_css<>'')
							{
								$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($height, $width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")'),$ad_site_css);
																}
							else
							{
								$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")';
							}
							break;
						}
						case 'gif':{
							if ($ad_site_css<>'')
							{
								$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($height, $width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")'),$ad_site_css);
																}
							else
							{
								$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")';
							}
							break;
						}
						case 'png':{
							if ($ad_site_css<>'')
							{
								$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($height, $width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")'),$ad_site_css);
																}
							else
							{
								$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$width.'px height='.$height.'px border=0 /></a>")';
							}
							break;
						}
						case 'swf':{
							if ($ad_site_css<>'')
							{
								$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($height, $width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><button disabled style=width:'.$width.';height:'.$height.';border:none><object classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0 width='.$width.' height='.$height.'> <param name=movie value='.base_url().'upload/'.$ad->image.'> <param name=wmode value=Opaque> <param name=quality value=high> <embed src='.base_url().'upload/'.$ad->image.' quality=high pluginspage=http://www.macromedia.com/go/getflashplayer type=application/x-shockwave-flash width='.$width.' height='.$height.' wmode=Opaque></embed></object></button></a>")'),$ad_site_css);
							}
							else
							{
								$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><button disabled style=width:'.$width.';height:'.$height.';border:none><object classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0 width='.$width.' height='.$height.'> <param name=movie value='.base_url().'upload/'.$ad->image.'> <param name=wmode value=Opaque> <param name=quality value=high> <embed src='.base_url().'upload/'.$ad->image.' quality=high pluginspage=http://www.macromedia.com/go/getflashplayer type=application/x-shockwave-flash width='.$width.' height='.$height.' wmode=Opaque></embed></object></button></a>")';
							}
							break;
						}
					}
					
					//最后显示
					echo $ad_site_css;
				} 
			}
		}
	}
	
	/* JS组 调用页面 */
	function viewjs()
	{
		$this->load->helper('text');
	
		$id = $this->uri->segment(3);
	
		$query = $this->db->query("SELECT * FROM js WHERE id=$id");
		if ($query->num_rows() > 0)
		{
			$row = $query->first_row();
			//读取JS代码
			$js_code = $row->js_code;

			$today = now();
			
			$query2 = $this->db->query("SELECT * FROM ad_site WHERE js_id=$row->id");
			if ($query2->num_rows() > 0)
			{
				foreach ($query2->result() as $ad_site)
				{	$i=1;
					$query1 = $this->db->query("SELECT * FROM ad WHERE ad_site=$ad_site->id and down_time>$today and up_time<$today and image<>'' order by id desc");
					if ($query1->num_rows() > 0)
					{						
						for ($i==1;$i<$ad_site->sl+1;$i++)
						{
							$ad_site_css = '';
							if ($i<=$query1->num_rows)
							{

								if ($i=1)
								{
									$ad = $query1->first_row();
								}else
								{
									$ad = $query1->next_row();
								}
								
								//广告表显示数量自加1
								$this->db->query("UPDATE ad SET showsl=showsl+1 WHERE id=$ad->id");
								//广告位表显示数量自加1
								$this->db->query("UPDATE ad_site SET showsl=showsl+1 WHERE id=$ad_site->id");
									
								//依照后缀显示内容
								switch(substr($ad->image,-3)){
				
									case 'jpg':{
										if ($ad_site->ad_site_css<>'')
										{
											$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($ad_site->height, $ad_site->width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")'),$ad_site->ad_site_css);
																			}
										else
										{
											$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")';
										}
										break;
									}
									case 'gif':{
										if ($ad_site->ad_site_css<>'')
										{
											$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($ad_site->height, $ad_site->width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")'),$ad_site->ad_site_css);
																			}
										else
										{
											$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")';
										}
										break;
									}
									case 'png':{
										if ($ad_site->ad_site_css<>'')
										{
											$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img', '#cont'), array($ad_site->height, $ad_site->width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")'),$ad_site->ad_site_css);
																			}
										else
										{
											$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><img src='.base_url().'upload/'.$ad->image.' width='.$ad_site->width.'px height='.$ad_site->height.'px border=0 /></a>")';
										}
										break;
									}
									case 'swf':{
										if ($ad_site->ad_site_css<>'')
										{
											$ad_site_css = str_replace(array('#height', '#width', '#uri', '#img','#cont'), array($ad_site->height, $ad_site->width, $ad->ad_url,base_url().'upload/'.$ad->image,'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><button disabled style=width:'.$ad_site->width.';height:'.$ad_site->height.';border:none><object classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0 width='.$ad_site->width.' height='.$ad_site->height.'> <param name=movie value='.base_url().'upload/'.$ad->image.'> <param name=wmode value=Opaque> <param name=quality value=high> <embed src='.base_url().'upload/'.$ad->image.' quality=high pluginspage=http://www.macromedia.com/go/getflashplayer type=application/x-shockwave-flash width='.$ad_site->width.' height='.$ad_site->height.' wmode=Opaque></embed></object></button></a>")'),$ad_site->ad_site_css);
										}
										else
										{
											$ad_site_css = 'document.write("<a href='.base_url().'home/show/'.$ad->id.' target=_blank><button disabled style=width:'.$ad_site->width.';height:'.$ad_site->height.';border:none><object classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0 width='.$ad_site->width.' height='.$ad_site->height.'> <param name=movie value='.base_url().'upload/'.$ad->image.'> <param name=wmode value=Opaque> <param name=quality value=high> <embed src='.base_url().'upload/'.$ad->image.' quality=high pluginspage=http://www.macromedia.com/go/getflashplayer type=application/x-shockwave-flash width='.$ad_site->width.' height='.$ad_site->height.' wmode=Opaque></embed></object></button></a>")';
										}
										break;
									}
								}//switch
								
							}//if
							
							//将JS代码里的相应广告位代码替换成JS代码
							$js_code = str_replace_once('#'.$ad_site->ad_site_name_short, $ad_site_css,$js_code);
						}//for
					}//if
				}//foreach
			}//if
		}//if
		//最终显示
		echo $js_code;
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */
?>
