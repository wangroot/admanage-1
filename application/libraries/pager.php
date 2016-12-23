<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Pager { 
public $length = 5;
public $prevLabel = '';
public $nextLabel = '';
public $first = 0; //第一页 
public $slider = 3; 
public $perpage = 3; 
public $query; 
public $table; 
public $count; 
public $obj; 
public $current;
 
 
function init($table='ad',$perpage=2,$segment=3){
 
$this->table = $table;
$this->obj =& get_instance();  
$this->obj->load->database(); 
$this->current = $this->obj->uri->segment($segment);  
$this->perpage = $perpage;    
return $this;
 
} 
 
 
//使用AR
 
function ar(){
$this->count = $this->obj->db->count_all($this->table);  
$this->query = $this->obj->db->get($this->table, $this->perpage,$this->perpage*$this->current);
//$this->count = $this->obj->db->count_all_results();

return $this;
 
}
 
 
 
//使用 自己写的SQL
 
function sql($sql,$order_by){ 

	$query = $this->obj->db->query("SELECT * FROM $this->table $sql order by $order_by");
	$this->count = count($query->result());
	
	$tt = $this->perpage*$this->current;
	$this->query = $this->obj->db->query("SELECT * FROM $this->table $sql order by $order_by limit $tt,$this->perpage");
	//echo"SELECT * FROM $this->table $sql order by $order_by limit $tt,$this->perpage";
	return $this;
 
}

function page($url,$current){
$perpage = $this->perpage;
$last = ceil($this->count/$perpage); 
$prev = $current - 1; //上一页
$next = $current + 1;    //下一页 
$output = "<ul class='pagenav'>"; 
    if ($current == $this->first) {
 
        $output .= "<li class=\"disabled\">« ".$this->prevLabel."</li>";
 
    } else {  
	  $output .= "<li><a href='".site_url($url.'/')."'>«  ".$this->prevLabel."</a></li>";   
    } 
 
$mid = intval($this->length / 2); 
 
    if ($current < $this->first) {
 
        $current = $this->first;
 
    }
 
    if ($current > $last) {
 
        $current = $last;
 
    } 
    $begin = $current - $mid; 
 
    if ($begin < $this->first) { $begin = $this->first; }
 
    $end = $begin + $this->length - 1; 
 
 
    if ($end >= $last) {
 
        $end = $last-1;
 
        $begin = $end - $this->length + 1;
 
        if ($begin < $this->first) { $begin = $this->first; }
 
    }
 
    if ($begin > $this->first) {
 
        for ($i = $this->first; $i < $this->first + $this->slider && $i < $begin; $i++) {
 
            $page= $i;
 
            $in = $i + 1;
            $urls=site_url($url.'/'.$page);
 
            $output .= "<li><a href=\"{$urls}\">{$in}</a></li>";
 
        }
        if ($i < $begin) {
 
            $output .= "<li class=\"none\">...</li>";
 
        }
 
    }    
 
    for ($i = $begin; $i <= $end ; $i++) {
 
        $page = $i;
 
        $in = $i + 1;
 
        if ($i == $current) {
 
            $output .= "<li class=\"current\">{$in}</li>";
 
        } else { 
 
            $urls=site_url($url.'/'.$page); 
            $output .= "<li><a href=\"{$urls}\">{$in}</a></li>";
 
        }
 
    } 
 
    if ($last - $end > $this->slider) {
 
        $output .= "<li class=\"none\">...</li>"; 
        $end = $last - $this->slider;
 
    }
 
 
    for ($i = $end + 1; $i < $last; $i++) { 
 
        $page = $i; 
        $in = $i + 1;
		$urls=site_url($url.'/'.$page);
        $output .= "<li><a href=\"{$urls}\">{$in}</a></li>";
 
    } 
 
    if ($current == $last-1) {
 
        $output .= "<li class=\"disabled\">".$this->nextLabel." »</li>";
 
    } else {
        $page = $last-1;
		$urls=site_url($url.'/'.$page); 
        $output .= "<li><a href=\"{$urls}\">".$this->nextLabel." »</a></li>"; 
    } 
    $output .= "</ul>";
 
echo $output;  
}
  
}
