<?php

class TimeCellController extends ControllerBase
{
	private $weekCN = ['1'=>'一','2'=>'二','3'=>'三','4'=>'四','5'=>'五','6'=>'六','7'=>'日'];
	public function viewAction(){
		$viewType = $this->request->get('viewType', null, 'date');
		$viewValue = $this->request->get('viewValue');
		$jump = $this->request->get('jump');
		
		$this->view->viewType = $viewType;
		$method = "view".strtoupper($viewType[0]).substr($viewType, 1);
		$this->$method($viewValue, $jump);
	}
	
	public function tranAction(){
		$tasks = Tasks::find();
		foreach ($tasks->toArray() as $task) {
			$t = Tasks::findFirst("id='{$task['id']}'");
			$t->cellTime = $this->computeCellTime($task['cellSpanId']);
			$t->update();
			p($task['id'], $t->update());
		}
	}
	
    public function viewDate($date, $jump) {
    	// 计算日期
    	$date = $date ? $date : date('ymd', time());
    	$time = strtotime(substr($date, 0,2).'-'.substr($date, 2,2).'-'.substr($date, -2,2)." 00:00:00");
    	if($jump && $jump == 'next'){
    		$time = $time + 3600 * 24;
    	}elseif($jump && $jump == 'previous'){
    		$time = $time - 3600 * 24;
    	}
    	$date = date('ymd', $time);
    	$this->view->viewValue = $date;
    	
    	
		// 时间段数据
    	$cellSpans = CellSpan::find([ "schemeId = '1'", "order"=>'seq', ]);
    	$k = 0;
    	foreach ($cellSpans->toArray() as $span) {
    		$begin = str_pad($span['cellSpanBegin'], 4, '0', STR_PAD_LEFT);
    		$end = str_pad($span['cellSpanEnd'], 4, '0', STR_PAD_LEFT);
    		if (0 == $k){
    			$week = date('w', $time);
    			$week = (0 == $week) ? 7 : $week;
    			$weekCN = $this->weekCN[$week];
    			$span['timeDisplay'] = substr($date, 0,2).'.'.substr($date, 2,2).'.'.substr($date, 4,2)." 周{$weekCN} "
    					.substr($begin, 0,2).':'.substr($begin, 2,2).'-'.substr($end, 0,2).':'.substr($end, 2,2);
    		}else{
    			$span['timeDisplay'] = substr($begin, 0,2).':'.substr($begin, 2,2).'-'.substr($end, 0,2).':'.substr($end, 2,2);
    		}
    		
    		$spans[$span['id']]['span'] = $span;
    		$k ++;
    	}
    	
    	// 获取当天数据
    	$user = $this->session->get("user");
    	$tasks = Tasks::find(["cellDate = '$date' and userId='{$user['id']}' and status >= 0"]);
    	foreach ($tasks->toArray() as $task) {
    		foreach ($spans as &$span) {
    			if($span['span']['cellSpanBegin'] <= $task['cellTime'] && $task['cellTime'] < $span['span']['cellSpanEnd']){
		    		$span['tasks'][] = $task;
		    		break;
    			}
    		}
    	}
    	$this->view->cellSpans = $spans;
    	
    	// 获取延期的数据
    	$this->view->spansDelayed = $this->getDelayedTasks($user['id']);
    }
    
    private function viewWeek($date, $jump) {
    	// 计算目标日期
    	$date = $date ? $date : date('ymd', time());
    	$time = strtotime(substr($date, 0,2).'-'.substr($date, 2,2).'-'.substr($date, -2,2)." 00:00:00");
    	if($jump && $jump == 'next'){
    		$time = $time + 3600 * 24 * 7;
    	}elseif($jump && $jump == 'previous'){
    		$time = $time - 3600 * 24 * 7;
    	}
    	$this->view->viewValue = date('ymd', $time);
    	
    	// 计算周内的时间
    	$week = (0==date('w', $time)) ? 7 : date('w', $time);
    	$mondayTime = $time - ($week - 1) * 3600 * 24;
    	$sundayTime = $time + (7 - $week) * 3600 * 24;
    	
    	// 获取星期内的事项
    	$user = $this->session->get("user");
    	$tasks = Tasks::find(["cellDate >= '".date('ymd', $mondayTime)."' and cellDate <= '".date('ymd', $sundayTime)."' and userId='{$user['id']}'"]);
    	$spans = [];
    	for ($w=1; $w<=7; $w++) {
    		$dayTime = $mondayTime + ($w - 1) * 3600 * 24;
    		$dayDate = date('ymd', $dayTime);
    		$spans[$w]['span']['timeDisplay'] = date('Y.m.d 周'.($this->weekCN[$w]), $dayTime);
    		foreach ($tasks->toArray() as $task) {
    			if($task['cellDate'] == $dayDate){
    				$spans[$w]['tasks'][] = $task;
    			}
    		}
    	}
    	$this->view->cellSpans = $spans;
    	
    	// 获取延期的数据
    	$this->view->spansDelayed = $this->getDelayedTasks($user['id']);
    }
    
    private function viewYear($date, $jump) {
    	// 计算目标日期
    	$date = $date ? $date : date('ymd', time());
    	$year = substr($date, 0,2);
    	if($jump && $jump == 'next'){
    		$year ++;
    	}elseif($jump && $jump == 'previous'){
    		$year --;
    	}
    	$year = min(99, max(10, $year)); // 年的范围是2010-2099
    	$this->view->viewValue = "{$year}0101";
    	 
    	// 获取星期内的事项
    	$user = $this->session->get("user");
    	$tasks = Tasks::find(["cellDate >= '{$year}0101' and cellDate <= '{$year}1231' and userId='{$user['id']}'"]);
    	$spans = [];
    	for ($m=1; $m<=12; $m++) {
    		$spans[$m]['span']['timeDisplay'] = (1==$m) ? "20{$year}年{$m}月" : "{$m}月";
    		foreach ($tasks->toArray() as $task) {
    			if(substr($task['cellDate'], 0,4) == "{$year}".str_pad($m, 2, '0', STR_PAD_LEFT)){
    				$spans[$m]['tasks'][] = $task;
    			}
    		}
    	}
    	$this->view->cellSpans = $spans;
    	 
    	// 获取延期的数据
    	$this->view->spansDelayed = $this->getDelayedTasks($user['id']);
    }
    
    private function getDelayedTasks($userId){
    	// 获取延期的数据
    	$tasksDelayed = Tasks::find("status='0' and cellDate < '".date('ymd', time())."' and userId='{$userId}'");
    	foreach ($tasksDelayed->toArray() as $task) {
    		$week = date('w', $time);
    		$week = (0 == $week) ? 7 : $week;
    		$weekCN = $this->weekCN[$week];
    		$dateFormatted = substr($task['cellDate'], 0,2).'.'.substr($task['cellDate'], 2,2).'.'.substr($task['cellDate'], 4,2)." 周{$weekCN}";
    		$spansDelayed[$dateFormatted][] = $task;
    	}
    	return $spansDelayed;
    }
    
    public function addTaskAction(){
    	$task = new Tasks();
    	$taskData = $this->request->getPost();
    	$taskData['timeAdded'] = date('ymdHi', time());
    	$taskData['cellTime'] = $this->computeCellTime($taskData['cellSpanId']);
    	$user = $this->session->get('user');
    	$taskData['userId'] = $user['id'];
    	$flag = $task->save($taskData);
    	echo $this->getReturnJson($flag, '任务添加'.($flag?'成功':'失败'), null);
    	$this->view->disable();
    }

    public function doneTaskAction(){
    	$taskId = $this->request->getPost('id');
    	$task = Tasks::findFirst("id='$taskId'");;
    	$task->status = ($task->status) ? 0 : 1;
    	if($task->status){
	    	$task->timeDone = date('ymdHi', time());
    	}
    
    	echo $this->getReturnJson($task->update(), '', null);
    	$this->view->disable();
    }

    private function computeCellTime($cellSpanId){
    	$span = CellSpan::findFirst("id='{$cellSpanId}'");
    	$hour = intval((substr($span->cellSpanBegin, 0,2) + substr($span->cellSpanEnd, 0,2)) / 2);
    	$minute = intval((substr($span->cellSpanBegin, 2,2) + substr($span->cellSpanEnd, 2,2)) / 2);
    	$hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
    	$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
    	return intval($hour.$minute);
    }
}