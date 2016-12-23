<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * News publishing system
 *
 * @package		News
 * @subpackage	Helpers
 * @category	Helpers
 * @author
 * @link
 */

// ------------------------------------------------------------------------

/**
 * Check 判断是否登陆
 *
 * Check if user has logon status of manager, redirect to home page if not.
 *
 * @access	public
 * @param	none
 * @return	none
 */
if ( ! function_exists('check'))
{
	function check()
	{
		$CI =& get_instance();

		if ($CI->session->userdata('manager')=='')
		{
			redirect('home/index', 'refresh');
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Showmessage 操作跳转页面
 *
 * Show a message, redirect to given page as provided
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	boolean
 * @return	none
 */
if ( ! function_exists('showmessage'))
{
	function showmessage($msg, $goto = '', $auto = true)
	{
		$CI =& get_instance();

		$CI->load->view('admin/body_message', array('msg'=>$msg, 'goto'=>site_url($goto), 'auto'=>$auto));
	}
}

// ------------------------------------------------------------------------

/**
 * 单独权限判断，存在返回正
 *   */
if ( ! function_exists('check_limits'))
{
	function check_limits($limits,$limit)
	{
		if (strpos($limits,$limit)>-1)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}
}

// ------------------------------------------------------------------------

/**
* 页面权限判断，不存在跳转后台首页
*   */
if ( ! function_exists('check_limits_page'))
{
	function check_limits_page($limits,$limit)
	{
		if (strpos($limits,$limit)>-1)
		{
			return TRUE;
		}
		else
		{
			redirect('admin/main');
		}
	}
}
//单次替换
function str_replace_once($needle, $replace, $haystack) 
{
	$pos = strpos($haystack, $needle);
	if ($pos === false) 
	{
		return $haystack;
	}
	return substr_replace($haystack, $replace, $pos, strlen($needle));
}
/* End of file news_helper.php */
/* Location: ./application/helpers/news_helper.php */
?>
