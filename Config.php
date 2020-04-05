<?php namespace Model\FacebookLogin;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 */
	protected function assetsList()
	{
		$this->addAsset('config', 'config.php', function () {
			return '<?php
$config = [
	\'path\' => \'facebook\',
	\'app-id\' => \'\',
	\'app-secret\' => \'\',
	\'default-graph-version\' => \'v4.0\',
	\'signup-module\' => \'Signup\',
	\'signup-method\' => \'facebookSignup\',
];
';
		});
	}

	/**
	 * @return bool
	 */
	public function makeCache(): bool
	{
		if ($this->model->moduleExists('Composer'))
			$this->model->_Composer->addToJson('facebook/graph-sdk');
		return true;
	}

	/**
	 * @return array
	 */
	public function getRules(): array
	{
		$config = $this->retrieveConfig();

		return [
			'rules' => [
				'facebook-login' => $config['path'] ?? '',
			],
			'controllers' => [
				'FacebookLogin',
			],
		];
	}
}
