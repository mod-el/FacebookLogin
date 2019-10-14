<?php namespace Model\FacebookLogin\Controllers;

use Model\Core\Controller;

class FacebookLoginController extends Controller
{
	public function index()
	{
		$this->model->viewOptions['template'] = null;

		try {
			$config = $this->model->_FacebookLogin->retrieveConfig();

			$accessToken = $this->model->_FacebookLogin->getAccessToken();
			$user = $this->model->_FacebookLogin->getUser($accessToken);

			$this->model->getModule($config['signup-module'])->{$config['signup-method']}($user);

			die();
		} catch (\Exception $e) {
			$this->model->viewOptions['errors'][] = getErr($e);
		}
	}
}
