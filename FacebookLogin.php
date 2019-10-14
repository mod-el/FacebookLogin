<?php namespace Model\FacebookLogin;

use Model\Core\Module;
use Facebook\Facebook;
use Facebook\Authentication\AccessToken;
use Facebook\GraphNodes\GraphUser;

class FacebookLogin extends Module
{
	/**
	 * @return Facebook
	 */
	private function getMain(): Facebook
	{
		$config = $this->retrieveConfig();

		return new Facebook([
			'app_id' => $config['app-id'], // Replace {app-id} with your app id
			'app_secret' => $config['app-secret'],
			'default_graph_version' => $config['default-graph-version'],
		]);
	}

	/**
	 * @param array $permissions
	 * @return string
	 */
	public function getLoginUrl(array $permissions = ['email']): string
	{
		$config = $this->retrieveConfig();

		$fb = $this->getMain();
		$helper = $fb->getRedirectLoginHelper();

		return $helper->getLoginUrl(BASE_HOST . PATH . $config['path'], $permissions);
	}

	/**
	 * @return AccessToken
	 */
	public function getAccessToken(): \Facebook\Authentication\AccessToken
	{
		$config = $this->retrieveConfig();

		$fb = $this->getMain();
		$helper = $fb->getRedirectLoginHelper();

		try {
			$accessToken = $helper->getAccessToken();
		} catch (\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			throw new \Exception('Graph returned an error: ' . $e->getMessage());
		} catch (\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
		}

		if (!isset($accessToken)) {
			if ($helper->getError()) {
				throw new \Exception("Error: " . $helper->getError() . "\n" . "Error Code: " . $helper->getErrorCode() . "\n" . "Error Reason: " . $helper->getErrorReason() . "\n" . "Error Description: " . $helper->getErrorDescription());
			} else {
				throw new \Exception('Bad request');
			}
		}

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId($config['app-id']);
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		if (!$accessToken->isLongLived()) {
			// Exchanges a short-lived access token for a long-lived one
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		}

		return $accessToken;
	}

	/**
	 * @param AccessToken $accessToken
	 * @param array $data
	 * @return GraphUser
	 */
	public function getUser(AccessToken $accessToken, array $data = ['id', 'first_name', 'middle_name', 'last_name', 'email']): GraphUser
	{
		$fb = $this->getMain();

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->get('/me?fields=' . implode(',', $data), $accessToken);
		} catch (\Facebook\Exceptions\FacebookResponseException $e) {
			throw new \Exception('Graph returned an error: ' . $e->getMessage());
		} catch (\Facebook\Exceptions\FacebookSDKException $e) {
			throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
		}

		$user = $response->getGraphUser();

		return $user;
	}

	/**
	 * @param array $request
	 * @param string $rule
	 * @return array|null
	 */
	public function getController(array $request, string $rule): ?array
	{
		return $rule === 'facebook-login' ? [
			'controller' => 'FacebookLogin',
		] : null;
	}
}
