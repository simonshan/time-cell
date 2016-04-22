<?php

class UserController extends ControllerBase
{
    public function indexAction() {
    	
    }
    public function loginAction() {
    	$name = $this->request->getPost('name');
    	$passwd = $this->request->getPost('passwd');
    	$user = Users::findFirst("name='$name' and passwd='$passwd'");
    	if($user){
    		$this->session->set('user', $user->toArray());
    		echo $this->getReturnJson(1);
    	}else{
    		echo $this->getReturnJson(-1);
    	}
    	$this->view->disable();
    }
    
    public function logoutAction() {
    	$this->session->remove('user');
    	$this->response->redirect("/");
    }
}