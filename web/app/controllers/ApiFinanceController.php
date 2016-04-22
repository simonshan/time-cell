<?php

class ApiFinanceController extends ControllerBase
{
    public function computeCompoundAction() {
    	$initial = max(array($this->request->getPost("initial"), 0)); // initial >= 0
    	$amount = max(array($this->request->getPost("amount"), 1)); // amount >= 1
    	$year = max(array($this->request->getPost("year"), 1)); // year >= 1
    	$interest = max(array($this->request->getPost("interest"), 0));
    
    	// years
    	$years = [];
    	$standardYears = [1, 3, 5, 10, 20];
    	foreach ($standardYears as $y){
    		if($y < $year){
    			$years[] = $y;
    		}
    	}
    	$years[] = $year;
    
    	// compute income of every year
    	$results = array();
    	foreach ($years as $year) {
    		$totalIncome = 0;
    		for($m=0; $m<$year*12; $m++){	// 给每个月的投资单独计算
    			$totalMonth = $amount;
    			$yearLeft = $year-$m/12;
    			for ($y=0; $y<$yearLeft; $y++){	// 每年结算一次
    				if ($yearLeft - $y > 1) {
    					$totalMonth = $totalMonth * (1 + $interest/100);	// 一整年收益
    				}else{
    					$totalMonth = $totalMonth * (1 + $interest/100 * ($yearLeft - $y));
    				}
    			}
    			$totalIncome += $totalMonth;
    		}
    		$totalIncome = intval($totalIncome + $initial * pow( (1 + $interest/100), $year));					// 累计总收入(本金+收益)
    		$totalInvest = intval($initial + $amount * $year * 12);			// 累计总的本金投资
    		$totalProfit = intval($totalIncome - $totalInvest);		// 累计总收益
    		$yearAverageProfit = intval($totalProfit / $year);		// 平均年收益
    		$results[] =  array(
    				'year' => $year,
    				'totalIncome' => $totalIncome,
    				'totalInvest' => $totalInvest,
    				'totalProfit' => $totalProfit,
    				'yearAverageProfit' => $yearAverageProfit,
    		);
    	}
    	echo json_encode(['ret'=>0, 'msg'=>'success', 'data'=>$results]);
    	$this->view->disable();
    }
}