<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class members_job{
    public function __construct() {
		list($year,$mon,$day,$daynum,$wknum)=explode("-",date("Y-m-d-t-w"));

		$this->today['start']     = mktime(0, 0 , 0,$mon,$day,$year);
		$this->today['end']       = $this->today['start']+86400-1;
		$this->yesterday['start'] = mktime(0, 0 , 0,$mon,$day-1,$year);
		$this->yesterday['end']   = $this->yesterday['start']+86400-1;
		$this->week['start']      = mktime(0, 0 , 0,$mon,$day-$wknum,$year);
		$this->week['end']        = $this->week['start']+86400*7-1;
		$this->pweek['start']     = mktime(0, 0 , 0,$mon,$day-$wknum-7,$year);
		$this->pweek['end']       = $this->pweek['start']+86400*7-1;
		$this->month['start']     = mktime(0, 0 , 0,$mon,1,$year);
		$this->month['end']       = mktime(23,59,59,$mon,$daynum,$year);
		$this->month['t']         = date('t',$this->month['start']);

		$pm					= $mon-1;
		$mon=="01"	&&	$pm	= "12";

		$py					= $year;
		$mon=="01"	&&	$py	= $year-1;

		$this->pmonth['start'] = mktime(0, 0, 0, $pm,1,$py);
		$this->pmonth['t']     = date('t',$this->pmonth['start']);
		$this->pmonth['end']   = mktime(23,59,59,$pm,$this->pmonth['t'],$py);
    }

    public function count_post($userid){
		$rs		= iDB::all("SELECT `postime`,`status` FROM #iCMS@__article where `userid`='".$userid."'");
		$this->total          = count($rs);
		$this->today['count'] = $this->yesterday['count']=$this->month['count']=$this->pmonth['count']=0;
		$this->day_count_post = array();
		$this->count0post     = 0;
		foreach((array)$rs AS $key=>$a){
			$this->day_count_post[date('Y-m-j',$a['postime'])]++;
			if($a['status']!="1"){
				$this->count0post++;
			}
			if($a['status']=="1"){
				$this->count_time_post($a['postime'],$this->today['start'],$this->today['end'],$this->today['count']);
				$this->count_time_post($a['postime'],$this->yesterday['start'],$this->yesterday['end'],$this->yesterday['count']);
				$this->count_time_post($a['postime'],$this->month['start'],$this->month['end'],$this->month['count']);
				$this->count_time_post($a['postime'],$this->pmonth['start'],$this->pmonth['end'],$this->pmonth['count']);
			}else{
				$this->count_time_post($a['postime'],$this->today['start'],$this->today['end'],$this->today['count0']);
			}
		}
    }
	public function count_time_post($t,$s,$e,&$c){
		if($t>=$s && $t<=$e){
			$c++;
		}
	}
	public function month($timestamp=null){
		$timestamp OR $timestamp=time();
		list($nowy,$nowm,$nowd,$noww) = explode('-',get_date($timestamp,'Y-m-d-w'));
		$info 				= array();
		$weekArray			= array('日','一','二','三','四','五','六');
		$info['year']       = $nowy;
		$info['month']      = $nowm;
		$info['day']      	= $nowd;
		$info['week']      	= '星期'.$weekArray[$noww];
		$info['days']       = $this->calendar($info['month'],$info['year']);
		$info['nextmonth']  = ($info['month']+1)>12 ? 1 : $info['month']+1;
		$info['premonth']   = ($info['month']-1)<1 ? 12 : $info['month']-1;
		$info['nextyear']   = $info['year']+1;
		$info['preyear']    = $info['year']-1;
		$info['cur_date']   = get_date(0,'Y n.j D');
		return $info;
	}
	public function calendar($m,$y) {
	    $today		= get_date(0,'j');
	    $weekday	= get_date(mktime(0,0,0,$m,1,$y),'w');
	    $totalday	= days_in_month($y,$m);
	    $start		= strtotime($y.'-'.$m.'-1');
	    $end		= strtotime($y.'-'.$m.'-'.$totalday);
	    $br 		= 0;
	    $days 		= '<tr class="day">';
	    for ($i=1; $i<=$weekday; $i++) {
	        $days .= '<td></td>';
	        $br++;
	    }
	    for ($i=1; $i<=$totalday; $i++) {
	        $br++;
	        $dcp	= $this->day_count_post[$y.'-'.$m.'-'.$i];
	        $dcp OR $dcp=0;
	        if($i==$today){
		        $days .= '<td class="today"><b>'.$i.'</b><hr />'.$dcp.'篇</td>';
	        }else{
		        $days .= '<td><b>'.$i.'</b><hr />'.$dcp.'篇</td>';
	        }
	        if ($br>=7) {
	            $days .= '</tr><tr class="day">';
	            $br = 0;
	        }
	    }
	    if ($br!=0) {
	        for ($i=$br; $i<7;$i++) {
	            $days .= '<td></td>';
	        }
	    }
	    return $days;
	}
}
