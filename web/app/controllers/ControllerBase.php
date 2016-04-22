<?php
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller {
	public function initialize() {
		$this->view->page_title = 'X-Lab';
	}

	public function getReturnJson($code=0, $msg='', $data=null){
		return json_encode(['code'=>$code, 'msg'=>$msg, 'data'=>$data]);
	}
}