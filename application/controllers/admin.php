<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
  * 后台管理平台
  * SaiXS
  *
  */
class Admin extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->helper('url');		
		$this->load->helper('form');		
		$this->load->helper('date');		
		$this->load->library('form_validation');		
		$this->load->model('Madmin');		
		$this->load->helper('news');
		//判断是否登陆
		check();
	}

	/* 后台首页  */
	function index()
	{
		$this->load->view('admin/index');
	}

	/* 后台顶部文件  */
	function top()
	{
		$this->load->view('admin/top');
	}

	/* 后台 左边文件 */
	function menu()
	{
		$data['get_site'] = $this->Madmin->get_site();
		$data['get_site_code'] = $this->Madmin->get_site_code();
		$this->load->view('admin/menu',$data);
	}

	/* 后台右边文件 */
	function main()
	{
	
		$arr['limits'] = array(
		array('权限设定'),
		array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改'),
		array('广告位管理中心','广告位管理'),
		array('站点管理中心','站点管理'),
		array('用户管理中心','用户管理'),
		array('IP记录查看','IP记录查看'),
		array('JS组管理中心','JS组管理'),
		array('是否业务员','业务员')
		);
		$now1 = now()+1296000;
		$arr['ad_list_15'] = $this->Madmin->get_sql("SELECT * FROM ad WHERE state=2 AND down_time >".now()." AND down_time <".$now1);
		$arr['online_ad'] = $this->Madmin->get_sql("SELECT * FROM ad WHERE state=2 AND down_time >".now()." AND up_time <".now());
		$arr['my_ad'] = $this->Madmin->get_sql("SELECT * FROM ad");
		$arr['my_ad1'] = $this->Madmin->get_sql("SELECT * FROM ad WHERE state=2");
		$arr['my_ad2'] = $this->Madmin->get_sql("SELECT * FROM ad WHERE state=0");
		$arr['my_ad3'] = $this->Madmin->get_sql("SELECT * FROM ad WHERE state=1");
		$this->load->view('admin/main',$arr);
	}

	/* 广告列表  */
	function ad_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='ad_list')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
			$this->session->set_userdata('keyword', $keyword);
			$this->session->set_userdata('orderby', $orderby);
			$this->session->set_userdata('orderbyto', $orderbyto);
			$this->session->set_userdata('pagename', 'ad_list');
		}
		
		if ($orderbyto=="")
		{//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='ad_list')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
		
		if ($keyword<>"")
		{
			$sql = "where ad_name like '%".$keyword."%'";
		}
		else
		{//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='ad_list')
			{
				$sql = "where ad_name like '%".$this->session->userdata('keyword')."%'";
			}
			else
			{
				$sql = "";
			}
		}
		//判断是否有全部广告权限，没有则加一个只显示添加人为当前用户的条件
		if (strpos($this->session->userdata('limits'),'12')>-1)
		{
			$sql = $sql;
		}
		else
		{
			if ($sql=='')
			{
				$sql = 'where add_user='.$this->session->userdata('manager');
			}
			else
			{
				$sql = $sql.' and add_user='.$this->session->userdata('manager');
			}
		}
		//调用分页
		$this->load->library('pager');
		$list = $this->pager->init('ad',20)->sql($sql,$orderby.' '.$orderbyto); 
		
		$arr['ad'] = $list->query;
		$arr['current'] = $this->uri->segment(3);  
		$arr['keyword'] = $keyword; 
		$arr['orderby'] = $orderby;
		$arr['orderbyto'] = $orderbyto;
		$this->load->view('admin/ad_list',$arr);
		
	}
	
	/* 在线广告列表  */
	function online_ad()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
			
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{
			//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='online_ad')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
			$this->session->set_userdata('keyword', $keyword);
			$this->session->set_userdata('orderby', $orderby);
			$this->session->set_userdata('orderbyto', $orderbyto);
			$this->session->set_userdata('pagename', 'online_ad');
		}
	
		if ($orderbyto=="")
		{
			//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='online_ad')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
	
		if ($keyword<>"")
		{
			$sql = "where state=2 and ad_name like '%".$keyword."%' and down_time>".now()." and up_time<".now();
		}
		else
		{
			//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$sql = "where state=2 and ad_name like '%".$this->session->userdata('keyword')."%' and down_time>".now()." and up_time<".now();
			}
			else
			{
				$sql = "where state=2 and down_time>".now()." and up_time<".now();
			}
		}
	
		$this->load->library('pager');
		//分页
		$list = $this->pager->init('ad',20)->sql($sql,$orderby.' '.$orderbyto);
	
		$arr['ad'] = $list->query;
		$arr['current'] = $this->uri->segment(3);
		$arr['keyword'] =$keyword;
		$arr['orderby'] =$orderby;
		$arr['orderbyto'] =$orderbyto;
		$this->load->view('admin/online_ad',$arr);
			
	}
	/* 我发布的广告列表  */
	function my_ad()
	{
	//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		$manager=$this->session->userdata('manager');

		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
			$this->session->set_userdata('keyword', $keyword);
			$this->session->set_userdata('orderby', $orderby);
			$this->session->set_userdata('orderbyto', $orderbyto);
			$this->session->set_userdata('pagename', 'my_ad');
		}
		
		if ($orderbyto=="")
		{//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
		
		if ($keyword<>"")
		{
			$sql = "where ad_name like '%".$keyword."%' and add_user ='".$manager."'";
		}
		else
		{
			//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$sql = "where ad_name like '%".$this->session->userdata('keyword')."%' and add_user ='".$manager."'";
			}
			else
			{
				$sql = "where add_user ='".$manager."'";
			}
		}
		
		$this->load->library('pager');
		//分页
		$list = $this->pager->init('ad',20)->sql($sql,$orderby.' '.$orderbyto);
		
		$arr['ad'] = $list->query;
		$arr['current'] = $this->uri->segment(3);  
		$arr['keyword'] =$keyword; 
		$arr['orderby'] =$orderby;
		$arr['orderbyto'] =$orderbyto;
		$this->load->view('admin/my_ad',$arr);
			
	}
	
	/* 指定用户指定时间段 发布的广告列表  */
	function myads()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		
		$user_name=$this->uri->segment(3);
		$up_time=$this->uri->segment(4);
		$down_time=$this->uri->segment(5);
	
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{
			//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='myads')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
			$this->session->set_userdata('keyword', $keyword);
			$this->session->set_userdata('orderby', $orderby);
			$this->session->set_userdata('orderbyto', $orderbyto);
			$this->session->set_userdata('pagename', 'myads');
		}
	
		if ($orderbyto=="")
		{
			//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='myads')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
	
		if ($keyword<>"")
		{
			$sql = "where state=2 and ad_name like '%".$keyword."%' and add_user ='".$user_name."' and add_time>".$up_time." and add_time<".$down_time;
		}
		else
		{
			//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$sql = "where state=2 and ad_name like '%".$this->session->userdata('keyword')."%' and add_user ='".$user_name."' and add_time>".$up_time." and add_time<".$down_time;
			}
			else
			{
				$sql = "where state=2 and add_user ='".$user_name."' and add_time>".$up_time." and add_time<".$down_time;
			}
		}
	
		$this->load->library('pager');
		//分页
		$list = $this->pager->init('ad',20,6)->sql($sql,$orderby.' '.$orderbyto);
	
		$arr['ad'] = $list->query;
		$arr['current'] = $this->uri->segment(6);
		$arr['keyword'] =$keyword;
		$arr['orderby'] =$orderby;
		$arr['orderbyto'] =$orderbyto;
		$arr['user_name'] = $user_name;
		$arr['up_time'] = $up_time;
		$arr['down_time'] = $down_time;
		$this->load->view('admin/myads',$arr);
			
	}
	/* 15天到期的广告 */
	function ad_list_15()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		$now1 = now()+1296000; 
		$sql = "where state=2 and down_time >".now()." and down_time <".$now1;

		$this->load->library('pager');
		//分页
		$list = $this->pager->init('ad',20)->sql($sql,"down_time");
		
		$current = $this->uri->segment(3);
		$this->load->view('admin/ad_list1',array('ad'=>$list->query,'current'=>$current));
	}
	/* 某站点下所有广告 */
	function ad_list_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{
			//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='my_ad')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
		$this->session->set_userdata('keyword', $keyword);
		$this->session->set_userdata('orderby', $orderby);
		$this->session->set_userdata('orderbyto', $orderbyto);
		$this->session->set_userdata('pagename', 'ad_list_site');
		}
		
		if ($orderbyto=="")
		{
			//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='ad_list_site')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
		
		if ($keyword<>"")
		{
			$sql = "where ad_name like '%".$keyword."%' and site =".$this->uri->segment(3);;
		}
		else
		{
			//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='ad_list_site')
			{
				$sql = "where ad_name like '%".$this->session->userdata('keyword')."%' and site =".$this->uri->segment(3);;
			}
			else
			{
				$sql = "where site =".$this->uri->segment(3);
			}
		}
		
		$this->load->library('pager');
			
		$list = $this->pager->init('ad',20,4)->sql($sql,$orderby.' '.$orderbyto);
		//取得地址上的第三个参数
		$current = $this->uri->segment(4);
		$this->load->view('admin/ad_list_site',array('ad'=>$list->query,'current'=>$current,'id'=>$this->uri->segment(3)));
	}	
	/* top20点击排行广告 */
	function ad_list_top20()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
	
		$sql = 'where state=2';
	
		$this->load->library('pager');
			
		$list = $this->pager->init('ad',20)->sql($sql,"clicks desc");
		//取得地址上的第三个参数
		$current = $this->uri->segment(3);
		$this->load->view('admin/ad_list_top20',array('ad'=>$list->query,'current'=>$current));
	}
	/* 添加广告  */
	function add_ad()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		//验证是否为业务员
		check_limits_page($this->session->userdata('limits'),'71');
		//获取广告位数具
		$data['get_site'] = $this->Madmin->get_site();	
		$this->load->view('admin/add_ad',$data);
	}
	/* 自动判断站点是否有代码，有则显示相应页面 */
	function showsite()
	{
		$id = $this->input->post('site');
		$today = now();
		$query = $this->db->query("SELECT * FROM site WHERE id=$id");
		//判断站点是否有代码 
		if ($query->num_rows() > 0)
		{
			$site = $query->first_row();
			$site_code = $site->site_code;
			//遍历该站点下的广告位
			$query1 = $this->db->query("SELECT * FROM ad_site WHERE site=$site->id");
			if ($query1->num_rows() > 0)
			{
				//列出该站点的所有 广告位
				foreach ($query1->result() as $ad_site)
				{
					//调用上线时间小于今天 下线时间大于今天的广告
					$query2 = $this->db->query("SELECT * FROM ad WHERE ad_site=$ad_site->id and down_time>$today and up_time<$today and image<>'' order by down_time desc");
					if ($query2->num_rows() > 0)//判断是否存在对应广告 替换相应代码
					{
						$ad = $query2->first_row();
						$site_code = str_replace(array('#'.$ad_site->ad_site_name_short), array('<a href=add_ad1/'.$id.'/'.$ad_site->id.'/'.$ad->down_time.'>U:'.mdate('%Y-%m-%d', $ad->up_time).'<br>D:'.mdate('%Y-%m-%d', $ad->down_time).'</a>'),$site_code);
					}
					else
					{
						$site_code = str_replace(array('#'.$ad_site->ad_site_name_short), array('<a href=add_ad1/'.$id.'/'.$ad_site->id.'>空闲</a>'),$site_code);
					}
				}//广告位循环结束
			}
			echo $site_code;//输出最终替换后的数据
		}
		else
		{
			redirect('admin/add_ad1/'.$id);//若该站点没有代码则直接跳转到添加广告页面
		}//判断结束
	}
	
	/* 站点代码显示页面 */
	function showsite1()
	{
		$id = $this->uri->segment(3);
		$today = now();
		$query = $this->db->query("SELECT * FROM site WHERE id=$id");
		//判断站点是否有代码
		if ($query->num_rows() > 0)
		{
			$site = $query->first_row();
			$site_code = $site->site_code;
			//遍历该站点下的广告位
			$query1 = $this->db->query("SELECT * FROM ad_site WHERE site=$site->id");
			if ($query1->num_rows() > 0)
			{
				//列出该站点的所有 广告位
				foreach ($query1->result() as $ad_site)
				{
					//调用上线时间小于今天 下线时间大于今天的广告
					$query2 = $this->db->query("SELECT * FROM ad WHERE ad_site=$ad_site->id and down_time>$today and up_time<$today and image<>'' order by down_time desc");
					if ($query2->num_rows() > 0)//判断是否存在对应广告 替换相应代码
					{
						$ad = $query2->first_row();
						$site_code = str_replace(array('#'.$ad_site->ad_site_name_short), array('U:'.mdate('%Y-%m-%d', $ad->up_time).'<br>D:'.mdate('%Y-%m-%d', $ad->down_time)),$site_code);
					}
					else
					{
						$site_code = str_replace(array('#'.$ad_site->ad_site_name_short), array('空闲'),$site_code);
					}
				}//广告位循环结束
			}
			echo $site_code;//输出最终替换后的数据
		}
		else
		{
			redirect('admin/add_ad1/'.$id);//若该站点没有代码则直接跳转到添加广告页面
		}//判断结束
	}
	
	/* 添加广告1  */
	function add_ad1()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		//获取广告位数据
		if ($this->uri->segment(4)<>'')
		{
			$data['ad_site'] = $this->uri->segment(4);
		}
		//判断是否从站点代码页面选取广告位过来，获取相应的上下线信息
		if ($this->uri->segment(5)<>'')
		{
			$data['up_time'] = $this->uri->segment(5);
			$data['down_time'] = $this->uri->segment(5)+259200;
		}
		else 
		{
			$data['up_time'] = now();
			$data['down_time'] = now()+259200;
		}
		$data['site'] = $this->uri->segment(3);
		$data['upload_errors'] = '';
		$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($this->uri->segment(3));
	
		$this->load->view('admin/add_ad1',$data);
	}
	/* 添加广告后台 */
	function add_ad_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		//验证各表单的信息
		$this->form_validation->set_rules('ad_name', '广告名称', 'required');
		$this->form_validation->set_rules('ad_url', '链接地址', 'required');
		$this->form_validation->set_rules('price', '价格', 'required|is_zzs');
		$this->form_validation->set_rules('up_time', '上线时间', 'required');
		$this->form_validation->set_rules('down_time', '下线时间', 'required');		
		$this->form_validation->set_rules('ad_site');		
		$this->form_validation->set_message('required','%s不能为空.');		
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			$data['site'] = $this->input->post('site');
			$data['upload_errors'] = '';
			$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($this->input->post('site'));
				
			$this->load->view('admin/add_ad1',$data);
		}
		else
		{
			//获取表单资料
			$arr['ad_name'] = $this->input->post('ad_name');
			$arr['site'] = $this->input->post('site');
			$arr['ad_site'] = $this->input->post('ad_site');
			$arr['ad_url'] = $this->input->post('ad_url');
			$arr['price'] = $this->input->post('price');
			$arr['up_time'] = strtotime($this->input->post('up_time'));
			$arr['down_time'] = strtotime($this->input->post('down_time'));
			$arr['add_time'] = now();
			//上传图片的控制
			$config['upload_path'] = './upload/';
			$config['allowed_types'] = 'gif|jpg|png|swf';
			$config['max_size'] = '1000';
			$config['encrypt_name'] = true;
			$config['allowed_no_file'] = true;
			
			$this->load->library('upload', $config);
			//判断是否有上传图片
			if(!$this->upload->do_upload('image'))
			{
				//若上传失败则返回
				$data['site'] = $this->input->post('site');
				$data['upload_errors'] = $this->upload->display_errors('<span>', '</span>');
				$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($this->input->post('site'));
				
				$this->load->view('admin/add_ad1',$data);
			}
			else 
			{ //添加广告
			   $fInfo = $this->upload->data(); 
			   $arr['image'] = $fInfo['file_name'];
			   
			   $table = 'ad';
			   
			   $res = $this->db->insert($table, $arr);
			   
			   if ($res)
			   {
			   	showmessage('添加广告成功', 'admin/ad_list');
			   }
			   else
			   {
			   	showmessage('操作失败，系统繁忙或着填写错误', 'admin/ad_list');
			   } 
			} 

			
		}
	}
	
	/* 删除 广告  */
	function del_ad()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'11');
		
		$data1 = $this->Madmin->get_ad_id($this->uri->segment(3));
		foreach ($data1 as $row)
		{
			if ($row->image<>'')
			{//若广告展示图片不为空则删除相应文件
				if(is_file('./upload/'.$row->image))
				{
					unlink('./upload/'.$row->image);
				}
			}
		}
		
		$where['id'] = $this->uri->segment(3);
		$table = 'ad';
		$res = $this->db->delete($table, $where);
		//判断操作是否成功进行想在应跳转
		if ($res)
		{
			showmessage('删除广告成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}
	/* 广告初审操作 */
	function ad_cs()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'13');

		$manager = '初:'.$this->session->userdata('manager').' ';
		$id = $this->uri->segment(3);

		$this->db->query("UPDATE ad SET state=1, sh=concat(sh,'$manager') WHERE id=$id");
		//判断操作是否成功进行想在应跳转
		if ($this->db->affected_rows()>0)
		{
			showmessage('初审广告成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}
	/* 广告终审操作 */
	function ad_zs()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'14');
	
		$manager = '终:'.$this->session->userdata('manager').' ';
		$id = $this->uri->segment(3);

		$this->db->query("UPDATE ad SET state=2, sh=concat(sh,'$manager') WHERE id=$id");
		//判断操作是否成功进行想在应跳转
		if ($this->db->affected_rows()>0)
		{
			showmessage('终审广告成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}
	/* 修改广告  */
	function edit_ad()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'15');
		//传递相应变量
		$data['get_ad_id'] = $this->Madmin->get_ad_id($this->uri->segment(3));
		foreach ($data['get_ad_id'] as $row)
		{
			$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($row->site);
		}
		
		$data['ap'] = 'first';
		$data['upload_errors'] = '';
		$this->load->view('admin/edit_ad', $data);
	}
	/* 修改广告后台 */
	function edit_ad_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'15');
		//验证各表单数据
		$this->form_validation->set_rules('ad_name', '广告名称', 'required');
		$this->form_validation->set_rules('ad_url', '链接地址', 'required');
		$this->form_validation->set_rules('price', '价格', 'required|is_zzs');
		$this->form_validation->set_rules('up_time', '上线时间', 'required');
		$this->form_validation->set_rules('down_time', '下线时间', 'required');	
		$this->form_validation->set_rules('ad_site');	
		$this->form_validation->set_message('required','%s不能为空.');	
		$this->form_validation->set_error_delimiters('<span>', '</span>');
	
		if ($this->form_validation->run() == FALSE)
		{	//验证失败则返回
			$data['site'] = $this->input->post('site');
			$data['upload_errors'] = '';
			$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($this->input->post('site'));
			$data['ap'] = 'two';
			$data['get_ad_id'] = $this->Madmin->get_ad_id($this->input->post('id'));
			
			$this->load->view('admin/edit_ad',$data);
		}
		else
		{
			$arr['ad_name'] = $this->input->post('ad_name');
			$arr['ad_site'] = $this->input->post('ad_site');
			$arr['ad_url'] = $this->input->post('ad_url');
			$arr['price'] = $this->input->post('price');
			$arr['up_time'] = $this->input->post('up_time');
			$arr['down_time'] = $this->input->post('down_time');
			$arr['state'] = 0;
			//图片上传
			$config['upload_path'] = './upload/';
			$config['allowed_types'] = 'gif|jpg|png|swf';
			$config['max_size'] = '1000';
			$config['encrypt_name'] = true;
			$config['allowed_no_file'] = true;
				
			$this->load->library('upload', $config);
				
			if(!$this->upload->do_upload('image'))
			{	//上传图片失败则返回
				$data['site'] = $this->input->post('site');
				$data['upload_errors'] = $this->upload->display_errors('<span>', '</span>');
				$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id($this->input->post('site'));
				$data['get_ad_id'] = $this->Madmin->get_ad_id($this->input->post('id'));
				$data['ap'] = 'two';
				
				$this->load->view('admin/edit_ad',$data);
			}
			else
			{
				$fInfo = $this->upload->data();
				if ($fInfo['file_name']<>'')
				{
					$arr['image'] = $fInfo['file_name'];
				
					if ($this->input->post('image1')<>'')
					{//上传成功则删除原先图片
						if(is_file('./upload/'.$this->input->post('image1')))
						{
							unlink('./upload/'.$this->input->post('image1'));
						}
					}
				}
				$where['id'] = $this->input->post('id');
				$table = 'ad';
				$res = $this->db->update($table, $arr, $where);
				//判断操作是否成功进行想在应跳转
				if ($res)
				{
					showmessage('修改广告成功', $this->session->userdata('uri'));
				}
				else
				{
					showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
				}
			}	
		}
	}
	/* 广告统计 */
	function ad_tj()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'16');
		
		$this->load->library('ad_tj');
		
		$year = $this->input->post('year');
		$month = $this->input->post('month');
		//判断是否有选择月份，无 则使用当前月份
		if ($year=="")
		{
			$up_time = strtotime(mdate('%Y-%m-1',now()));
			$down_time = $up_time + days_in_month(mdate('%m,%Y',now()))*86400;
			$year = mdate('%Y',now());
			$month = mdate('%m',now());
		}
		else
		{//判断选择的月份，如果选全年，则直接设定
			if ($year==0)
			{
				$up_time = 0;
				$down_time = now();
			}
			else
			{
				if ($month<>'')
				{
					$up_time = strtotime($this->input->post('year').'-'.$this->input->post('month').'-1');
					$down_time = $up_time + days_in_month(mdate('%m,%Y',$up_time))*86400;
				}
				else
				{
					$up_time = strtotime($this->input->post('year').'-1-1');
					$year = $this->input->post('year')+1;
					$down_time = strtotime($year.'-1-1');
				}
			}
		}

		$data['up_time'] = $up_time;
		$data['down_time'] = $down_time;
		$data['year'] = $year;
		$data['month'] = $month;
		$data['get_user'] = $this->Madmin->get_user_yw();
		$this->load->view('admin/ad_tj',$data);
	}
	/* 我的广告统计 */
	function my_tj()
	{
	
		$this->load->library('ad_tj');
	
		$year = $this->input->post('year');
		$month = $this->input->post('month');
		//判断是否有选择月份，无 则使用当前月份
		if ($year=="")
		{
			$up_time = strtotime(mdate('%Y-%m-1',now()));
			$down_time = $up_time + days_in_month(mdate('%m,%Y',now()))*86400;
			$year = mdate('%Y',now());
			$month = mdate('%m',now());
		}
		else
		{//判断选择的月份，如果选全年，则直接设定
			if ($year==0)
			{
				$up_time = 0;
				$down_time = now();
			}
			else
			{
				if ($month<>'')
				{
					$up_time = strtotime($this->input->post('year').'-'.$this->input->post('month').'-1');
					$down_time = $up_time + days_in_month(mdate('%m,%Y',$up_time))*86400;
				}
				else
				{
					$up_time = strtotime($this->input->post('year').'-1-1');
					$year = $this->input->post('year')+1;
					$down_time = strtotime($year.'-1-1');
				}
			}
		}
	
		$data['up_time'] = $up_time;
		$data['down_time'] = $down_time;
		$data['year'] = $year;
		$data['month'] = $month;
		$data['get_user'] = $this->session->userdata('manager');
		$this->load->view('admin/my_tj',$data);
	}
	/* 广告位列表  */
	function ad_site_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
		//搜索排序调用
		if ($orderby=="")
		{
			//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='ad_list')
			{
				$orderby = $this->session->userdata('orderby');
			}
			else
			{
				$orderby = "id";
			}
		}
		else
		{//不为空则将数据写进session
		$this->session->set_userdata('keyword', $keyword);
		$this->session->set_userdata('orderby', $orderby);
		$this->session->set_userdata('orderbyto', $orderbyto);
		$this->session->set_userdata('pagename', 'ad_site_list');
		}
		
		if ($orderbyto=="")
		{
			//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='ad_site_list')
			{
				$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
				$orderbyto = "desc";
			}
		}
		
		if ($keyword<>"")
		{
			$sql = "where ad_site_name like '%".$keyword."%'";
		}
		else
		{//如果关键字为空则检查session
		if ($this->session->userdata('pagename')=='ad_site_list')
		{
			$sql = "where ad_site_name like '%".$this->session->userdata('keyword')."%'";
		}
		else
		{
			$sql = "";
		}
		}
		
		//调用分页
		$this->load->library('pager');
		$list = $this->pager->init('ad_site',20)->sql($sql,$orderby.' '.$orderbyto);
		
		$arr['ad_site'] = $list->query;
		$arr['current'] = $this->uri->segment(3);
		$arr['keyword'] =$keyword;
		$arr['orderby'] =$orderby;
		$arr['orderbyto'] =$orderbyto;
		$this->load->view('admin/ad_site_list',$arr);
		$array = array('id >' => 0);
			
	}
	
	/* top20点击排行广告位*/
	function ad_site_list_top20()
	{
	//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
			
		$array = array('id >' => 0);
			
		$this->load->library('pager');
					
		$list = $this->pager->init('ad_site',20)->sql($array,"clicks desc");
				//取得地址上的第三个参数
		$current = $this->uri->segment(3);
		$this->load->view('admin/ad_site_list_top20',array('ad_site'=>$list->query,'current'=>$current));
	}
	/* 添加广告位  */
	function add_ad_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		
		$data['get_site'] = $this->Madmin->get_site();
		$data['get_js'] = $this->Madmin->get_js();
		$this->load->view('admin/add_ad_site',$data);
	}
	
	/* 添加广告位后台  */
	function add_ad_site_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		//验证表单
		$this->form_validation->set_rules('ad_site_name', '广告位名称', 'required');
		$this->form_validation->set_rules('ad_site_name_short', '广告位简称', 'required');
		$this->form_validation->set_rules('width', '广告位宽度', 'required|is_zzs');
		$this->form_validation->set_rules('height', '广告位高度', 'required|is_zzs');
		$this->form_validation->set_rules('sl', '广告数量', 'required|is_zzs');		
		$this->form_validation->set_rules('site');
		$this->form_validation->set_rules('js_id');
		$this->form_validation->set_rules('site_url');
		$this->form_validation->set_rules('shape');
		$this->form_validation->set_rules('ad_site_css');
		$this->form_validation->set_rules('ad_site_default');		
		$this->form_validation->set_message('required','%s不能为空.');		
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			$data['get_site'] = $this->Madmin->get_site();
			$this->load->view('admin/add_ad_site',$data);
		}
		else
		{
			//获取各字段数据
			$arr['ad_site_name'] = $this->input->post('ad_site_name');
			$arr['ad_site_name_short'] = $this->input->post('ad_site_name_short');
			$arr['site'] = $this->input->post('site');
			$arr['js_id'] = $this->input->post('js_id');
			$arr['height'] = $this->input->post('height');
			$arr['width'] = $this->input->post('width');
			$arr['sl'] = $this->input->post('sl');
			$arr['shape'] = $this->input->post('shape');
			$arr['ad_site_css'] = $this->input->post('ad_site_css');
			$arr['ad_site_default'] = $this->input->post('ad_site_default');
			$arr['add_time'] = now();
			
			$table = 'ad_site';
			
			$res = $this->db->insert($table, $arr);
			//判断是否添加成功进行相应跳转
			if ($res)
			{
			showmessage('添加广告位成功', 'admin/ad_site_list');
			}
			else
			{
			showmessage('操作失败，系统繁忙或着填写错误', 'admin/ad_site_list');
			}
		}
	}
	
	/* 修改广告位  */
	function edit_ad_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		
		$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id1($this->uri->segment(3));
		$data['get_site'] = $this->Madmin->get_site();
		$data['get_js'] = $this->Madmin->get_js();
		$data['ap'] = 'first';
		
		$this->load->view('admin/edit_ad_site', $data);
	}
	
	/* 修改广告位后台  */
	function edit_ad_site_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		//验证表单数据
		$this->form_validation->set_rules('ad_site_name', '广告位名称', 'required');
		$this->form_validation->set_rules('ad_site_name_short', '广告位简称', 'required');
		$this->form_validation->set_rules('width', '广告位宽度', 'required|is_zzs');
		$this->form_validation->set_rules('height', '广告位高度', 'required|is_zzs');
		$this->form_validation->set_rules('sl', '广告数量', 'required|is_zzs');		
		$this->form_validation->set_rules('site');
		$this->form_validation->set_rules('js_id');
		$this->form_validation->set_rules('site_url');
		$this->form_validation->set_rules('shape');
		$this->form_validation->set_rules('ad_site_css');
		$this->form_validation->set_rules('ad_site_default');		
		$this->form_validation->set_message('required','%s不能为空.');		
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			$data['get_ad_site_id'] = $this->Madmin->get_ad_site_id1($this->input->post('id'));
			$data['get_site'] = $this->Madmin->get_site();
			$data['get_js'] = $this->Madmin->get_js();
			$data['ap'] = 'two';
			
			$this->load->view('admin/edit_ad_site', $data);
		}
		else
		{
			//获取各字段数据
			$arr['ad_site_name'] = $this->input->post('ad_site_name');
			$arr['ad_site_name_short'] = $this->input->post('ad_site_name_short');
			$arr['site'] = $this->input->post('site');
			$arr['js_id'] = $this->input->post('js_id');
			$arr['height'] = $this->input->post('height');
			$arr['width'] = $this->input->post('width');
			$arr['sl'] = $this->input->post('sl');
			$arr['shape'] = $this->input->post('shape');
			$arr['ad_site_css'] = $this->input->post('ad_site_css');
			$arr['ad_site_default'] = $this->input->post('ad_site_default');
			$where['id'] = $this->input->post('id');
			$table = 'ad_site';
			$res = $this->db->update($table, $arr, $where);
			//判断是否修改成功，进行相应跳转
			if ($res)
			{
				showmessage('修改广告位信息成功', $this->session->userdata('uri'));
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
			}
		}
	}
	
	/* 删除 广告位  */
	function del_ad_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'21');
		
		$where['id'] = $this->uri->segment(3);
		$table = 'ad_site';
		$res = $this->db->delete($table, $where);
		//判断操作是否成功进行想在应跳转
		if ($res)
		{
			showmessage('删除广告位成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}
	
	/* 添加站点  */
	function add_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
		
		$this->load->view('admin/add_site');
	}
	
	/* 添加站点后台  */
	function add_site_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
		//验证表单
		$this->form_validation->set_rules('site_name', '站点名称', 'required');
		$this->form_validation->set_rules('site_describe');
		$this->form_validation->set_rules('site_url');
		$this->form_validation->set_rules('site_code');
		$this->form_validation->set_message('required','%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
	
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			$this->load->view('admin/add_site');
		}
		else
		{
			$arr['site_name'] = $this->input->post('site_name');
			$arr['site_describe'] = $this->input->post('site_describe');
			$arr['site_url'] = $this->input->post('site_url');
			$arr['site_code'] = $this->input->post('site_code');
			$arr['add_time'] = now();
			$table = 'site';
			$res = $this->db->insert($table, $arr);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('添加站点成功', 'admin/site_list');
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', 'admin/site_list');
			}
		}
	}
	/* 站点列表  */
	function site_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
		
		$this->load->library('pager'); 
		$list = $this->pager->init('site',20)->ar();
		//取得地址上的第三个参数
		$current = $this->uri->segment(3);
		$this->load->view('admin/site_list',array('site'=>$list->query,'current'=>$current));
	}
	
	
	/* 修改站点  */
	function edit_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
	
		$data['get_site_id'] = $this->Madmin->get_site_id($this->uri->segment(3));
		$data['ap'] = 'first';
		
		$this->load->view('admin/edit_site', $data);
	}
	
	/* 修改站占后台  */
	function edit_site_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
		//验证表单是否成功
		$this->form_validation->set_rules('site_name', '站点名称', 'required');
		$this->form_validation->set_rules('site_describe');
		$this->form_validation->set_rules('site_url');
		$this->form_validation->set_rules('site_code');
		$this->form_validation->set_message('required','%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			$data['get_site_id'] = $this->Madmin->get_site_id($this->input->post('id'));
			$data['ap'] = 'two';
			
			$this->load->view('admin/edit_site', $data);
		}
		else
		{
			//获取各字段数据
			$arr['site_name'] = $this->input->post('site_name');
			$arr['site_describe'] = $this->input->post('site_describe');
			$arr['site_url'] = $this->input->post('site_url');
			$arr['site_code'] = $this->input->post('site_code');
			$where['id'] = $this->input->post('id');
			$table = 'site';
			$res = $this->db->update($table, $arr, $where);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('修改站点信息成功', $this->session->userdata('uri'));
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
			}
		}
	}
	
	/* 删除 站点  */
	function del_site()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'31');
		
		$where['id'] = $this->uri->segment(3);
		$table = 'site';
		$res = $this->db->delete($table, $where);
		//判断操作是否成功进行想在应跳转
		if ($res)
		{
			showmessage('删除站点成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}
		
	
	/* 用户列表  */
	function user_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');
		
		
		$keyword = $this->input->post('keyword');
		$orderby = $this->input->post('orderby');
		$orderbyto = $this->input->post('orderbyto');
				//搜索排序调用
		if ($orderby=="")
		{
		//如果排序目标为空则检查session
			if ($this->session->userdata('pagename')=='user_list')
					{
		$orderby = $this->session->userdata('orderby');
					}
					else
			{
			$orderby = "id";
			}
			}
			else
				{//不为空则将数据写进session
			$this->session->set_userdata('keyword', $keyword);
					$this->session->set_userdata('orderby', $orderby);
					$this->session->set_userdata('orderbyto', $orderbyto);
					$this->session->set_userdata('pagename', 'user_list');
				}
				
				if ($orderbyto=="")
			{
			//如果排序顺序为空则检查session
			if ($this->session->userdata('pagename')=='user_list')
					{
						$orderbyto = $this->session->userdata('orderbyto');
			}
			else
			{
						$orderbyto = "desc";
			}
			}
		
			if ($keyword<>"")
				{
			$sql = "where user_name like '%".$keyword."%'";
				}
				else
				{//如果关键字为空则检查session
					if ($this->session->userdata('pagename')=='user_list')
			{
			$sql = "where user_name like '%".$this->session->userdata('keyword')."%'";
			}
			else
			{
			$sql = "";
					}
				}
				
				//调用分页
			$this->load->library('pager');
			$list = $this->pager->init('admin',20)->sql($sql,$orderby.' '.$orderbyto);
		
			$arr['user'] = $list->query;
			$arr['current'] = $this->uri->segment(3);
			$arr['keyword'] =$keyword;
			$arr['orderby'] =$orderby;
			$arr['orderbyto'] =$orderbyto;
			$arr['limits'] = array(
			array('权限设定'),
			array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改','广告统计'),
			array('广告位管理中心','广告位管理'),
			array('站点管理中心','站点管理'),
			array('用户管理中心','用户管理'),
			array('IP记录查看','IP记录查看'),
			array('JS组管理中心','JS组管理'),
			array('是否业务员','业务员')
			);
			$this->load->view('admin/user_list',$arr);
	}

	/* 添加用户  */
	function add_user()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');
		
		$arr['limits'] = array(
					array('权限设定'),
					array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改','广告统计'),
					array('广告位管理中心','广告位管理'),
					array('站点管理中心','站点管理'),
					array('用户管理中心','用户管理'),
					array('IP记录查看','IP记录查看'),
					array('JS组管理中心','JS组管理'),
					array('是否业务员','业务员')
				);
		
		$this->load->view('admin/add_user',$arr);
	}

	/* 添加用户后台  */
	function add_user_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');
		//验证表单
		$this->form_validation->set_rules('username', '用户名', 'required|unique[admin.user_name]');
		$this->form_validation->set_rules('password', '密码', 'required|min_length[6]|is_alpha');
		$this->form_validation->set_rules('limits[]');
		$this->form_validation->set_message('min_length', '%s长度必须超过6位.');
		$this->form_validation->set_message('required', '%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');

		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			
			$arr['limits'] = array(
			array('权限设定'),
			array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改','广告统计'),
			array('广告位管理中心','广告位管理'),
			array('站点管理中心','站点管理'),
			array('用户管理中心','用户管理'),
			array('IP记录查看','IP记录查看'),
			array('JS组管理中心','JS组管理'),
			array('是否业务员','业务员')
			);
			
			$this->load->view('admin/add_user',$arr);
		}
		else
		{
			//获取权限字段信息，连成字符串
			$limits = $this->input->post('limits');
			for ($i=0; $i < count($limits); $i++)
			{
				if ($i==0)
				{
					$str=$limits[$i];
				}
				else
				{
					$str=$str.','.$limits[$i];
				}
			}
			$arr['user_name'] = $this->input->post('username');
			$arr['password'] = md5($this->input->post('password'));
			$arr['limits'] = $str;
			$arr['add_time'] = now();
			$table = 'admin';
			$res = $this->db->insert($table, $arr);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('添加用户成功', 'admin/user_list');
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', 'admin/user_list');
			}
		}
	}

	/* 修改用户  */
	function edit_user()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');

		$data['get_user_name'] = $this->Madmin->get_user_name($this->uri->segment(3));
		$data['limits1'] = array(
		array('权限设定'),
		array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改','广告统计'),
		array('广告位管理中心','广告位管理'),
		array('站点管理中心','站点管理'),
		array('用户管理中心','用户管理'),
		array('IP记录查看','IP记录查看'),
		array('JS组管理中心','JS组管理'),
		array('是否业务员','业务员')
		);
		
		$this->load->view('admin/edit_user', $data);
	}

	/* 修改用户后台  */
	function edit_user_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');
		//验证表单
		$this->form_validation->set_rules('password', '密码', 'required|min_length[6]|is_alpha');
		$this->form_validation->set_rules('limits[]');
		$this->form_validation->set_rules('id');
		$this->form_validation->set_message('min_length', '%s长度必须超过6位.');
		$this->form_validation->set_message('required', '%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
		
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回		
			$data['get_user_name'] = $this->Madmin->get_user_name($this->input->post('id'));
			$data['limits1'] = array(
			array('权限设定'),
			array('广告管理中心','广告管理','所有广告','广告初审权限','广告终审权限','广告修改','广告统计'),
			array('广告位管理中心','广告位管理'),
			array('站点管理中心','站点管理'),
			array('用户管理中心','用户管理'),
			array('IP记录查看','IP记录查看'),
			array('JS组管理中心','JS组管理'),
			array('是否业务员','业务员')
			);
			$this->load->view('admin/edit_user',$data);
		}
		else
		{
			//获取权限字段信息，连成字符串
			$limits = $this->input->post('limits');
			for ($i=0; $i < count($limits); $i++)
			{
				if ($i==0)
				{
					$str=$limits[$i];
				}
				else
				{
					$str=$str.','.$limits[$i];
				}
			}
			$arr['limits'] = $str;
			if ($this->input->post('password')<>$this->input->post('password1'))
			{
				$arr['password'] = md5($this->input->post('password'));
			}
			$where['id'] = $this->input->post('id');
			$table = 'admin';
			$res = $this->db->update($table, $arr, $where);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('修改用户信息成功', $this->session->userdata('uri'));
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
			}
		}
	}

	/* 删除 用户  */
	function del_user()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'41');
		//判断用户为1的用户不能删除 ，以保证系统能正常运行
		if ($this->uri->segment(3)<>1)
		{
			$where['id'] = $this->uri->segment(3);
			$table = 'admin';
			$res = $this->db->delete($table, $where);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('删除用户成功', $this->session->userdata('uri'));
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
			}
		}
		else
		{
			showmessage('不能删除编号为1的用户', $this->session->userdata('uri'));
		}
	}

	/* 修改密码  */
	function pwd()
	{
		$this->load->view('admin/pwd');
	}

	/* 修改密码后台  */
	function pwd_ok()
	{
		$this->form_validation->set_rules('old_pass', '旧密码', 'required|is_old['.$this->session->userdata('manager').']');
		$this->form_validation->set_rules('password', '新密码', 'required|min_length[6]|is_alpha');
		$this->form_validation->set_rules('password1', '二次密码', 'is_newpass['.$this->input->post('password').']');
		$this->form_validation->set_message('min_length', '%s长度必须超过6位.');
		$this->form_validation->set_message('required', '%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');

		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/pwd');
		}
		else
		{
			$arr['password'] = md5($this->input->post('password'));
			$where['user_name'] = $this->session->userdata('manager');
			$table = 'admin';
			$res = $this->db->update($table, $arr, $where);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('修改密码成功', 'admin/pwd');
			}
			else
			{
				showmessage('修改失败，系统繁忙或着填写错误', 'admin/pwd');
			}
		}
	}

	/* IP记录列表  */
	function ip_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'51');
		
		$keyword = $this->input->post('keyword');
		if ($keyword<>'')//判断是否进行搜索，有则写入session
		{
			$this->session->set_userdata('keyword', $keyword);
			$this->session->set_userdata('pagename', 'ip_list');
			$sql = "where ip like '%".$keyword."%'";
		}
		else
		{//如果关键字为空则检查session
			if ($this->session->userdata('pagename')=='ip_list')
			{
				$sql = "where ip like '%".$this->session->userdata('keyword')."%'";
			}
			else
			{
				$sql = "";
			}
		}
		
		//调用分页
		$this->load->library('pager');
		$list = $this->pager->init('ip',20)->sql($sql,'id desc');
		
		$arr['ip'] = $list->query;
		$arr['current'] = $this->uri->segment(3);
		$arr['keyword'] =$keyword;
		$this->load->view('admin/ip_list',$arr);
		
	}
	/* JS组列表  */
	function js_list()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
		
		$this->load->library('pager');
		$list = $this->pager->init('js',20)->ar();
		//取得地址上的第三个参数
		$current = $this->uri->segment(3);
		$this->load->view('admin/js_list',array('js'=>$list->query,'current'=>$current));
	}
	/* 添加JS组  */
	function add_js()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
		
		$this->load->view('admin/add_js');
	}
	
	/* 添加JS组后台  */
	function add_js_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
		
		$this->form_validation->set_rules('js_name', 'JS名称', 'required');
		$this->form_validation->set_rules('js_code', 'JS代码', 'required');
		$this->form_validation->set_message('required','%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
	
		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin/add_js');
		}
		else
		{
			$arr['js_name'] = $this->input->post('js_name');
			$arr['js_code'] = $this->input->post('js_code');
			$arr['add_time'] = now();
			$table = 'js';
			$res = $this->db->insert($table, $arr);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('添加JS组成功', 'admin/js_list');
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', 'admin/js_list');
			}
		}
	}

	/* 修改JS组  */
	function edit_js()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
	
		$data['get_js_id'] = $this->Madmin->get_js_id($this->uri->segment(3));
		$data['ap'] = 'first';
	
		$this->load->view('admin/edit_js', $data);
	}
	
	/* 修改JS组后台  */
	function edit_js_ok()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
		//验证表单信息
		$this->form_validation->set_rules('js_name', 'JS名称', 'required');
		$this->form_validation->set_rules('js_code', 'JS代码', 'required');
		$this->form_validation->set_message('required','%s不能为空.');
		$this->form_validation->set_error_delimiters('<span>', '</span>');
	
		if ($this->form_validation->run() == FALSE)
		{
			//验证失败则返回
			$data['get_js_id'] = $this->Madmin->get_js_id($this->input->post('id'));
			$data['ap'] = 'two';
				
			$this->load->view('admin/edit_js', $data);
		}
		else
		{
			$arr['js_name'] = $this->input->post('js_name');
			$arr['js_code'] = $this->input->post('js_code');
			$where['id'] = $this->input->post('id');
			$table = 'js';
			$res = $this->db->update($table, $arr, $where);
			//判断操作是否成功进行想在应跳转
			if ($res)
			{
				showmessage('修改JS组成功', $this->session->userdata('uri'));
			}
			else
			{
				showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
			}
		}
	}
	
	/* 删除 JS组  */
	function del_js()
	{
		//验证是否有权限访问
		check_limits_page($this->session->userdata('limits'),'61');
		
		$where['id'] = $this->uri->segment(3);
		$table = 'js';
		$res = $this->db->delete($table, $where);
		//判断操作是否成功进行想在应跳转
		if ($res)
		{
			showmessage('删除JS组成功', $this->session->userdata('uri'));
		}
		else
		{
			showmessage('操作失败，系统繁忙或着填写错误', $this->session->userdata('uri'));
		}
	}	
	/* 退出系统  */
	function exit_system()
	{
		$array_items = array('manager' => '');

		$this->session->unset_userdata($array_items);

		redirect('home/index');
	}
}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */
?>
