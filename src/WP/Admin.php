<?php

namespace KeriganSolutions\Instagram\WP;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Admin {

  protected $accessToken;
  protected $appId;
  protected $appSecret;
  protected $redirectUri;
  protected $callbackUrl;
  protected $tempToken;
  protected $userID;

  public function __construct()
  {
    $this->userID       = get_option('instagram_page_id');
    $this->accessToken  = get_option('instagram_token');
    $this->appId        = '2174657569497762';
    $this->appSecret    = get_option('instagram_app_secret'); //'a795ba90bc44482dca37aea223e0fda7';
  }

  public function use()
  {
    add_action('admin_menu', [$this,'addMenus']);
    add_action('rest_api_init', [$this,'addRoutes']);

    (new InstagramPost)->use();

    $this->mount();
  }

  public function mount()
  {
    // for extending the app
  }

  /**
   * Add our options page to the menu
   */
  public function addMenus()
  {
    add_menu_page("Instagram Settings", "Instagram Settings", "administrator", 'kma-instagram', function () {
      include(wp_normalize_path( dirname(dirname(__FILE__)) . '/dist/AdminOverview.php'));
    }, "dashicons-admin-generic");
  }

  /**
	 * Add REST API routes
	 */
  public function addRoutes()
  {
    register_rest_route( 'kerigansolutions/v1', '/instagallerytoken',
      [
        'methods'  => 'GET',
        'callback' => [ $this, 'exchangeToken' ],
        'permission_callback'  => '__return_true'
      ]
    );

    // register_rest_route( 'kerigansolutions/v1', '/instagramdata',
    //   [
    //     'methods'  => 'GET',
    //     'callback' => [ $this, 'getInstagramData' ],
    //     'permission_callback'  => '__return_true'
    //   ]
    // );
  }

  public function exchangeToken($request)
  {
    $token = $request->get_param( 'token' );
    $client = new Client();

    try {
        $response = $client->request('GET',
        'https://graph.facebook.com/v12.0/oauth/access_token?' .
        'grant_type=fb_exchange_token&' .
        'client_id=' . $this->appId . '&' .
        'client_secret=' . $this->appSecret . '&' .
        'fb_exchange_token=' . $token );

    } catch (RequestException $e) {

    }

    return rest_ensure_response(json_decode($response->getBody()));
  }

  // public function getInstagramData($request)
  // {
  //   $token = $request->get_param( 'token' );
  //   $userId = $request->get_param( 'user' );
  //   $appToken = $this->getAppToken();
  //   $client = new Client();

  //   try {

  //     $response = $client->request('GET',
  //         'https://graph.facebook.com/v12.0/' . $userId .
  //         '?fields=instagram_business_account' .
  //         '&access_token=' . $token .
  //         '&app_token=' . $appToken .
  //         ''
  //     );

  //     return rest_ensure_response(json_decode($response->getBody()));

  //   } catch (RequestException $e) {
  //     return rest_ensure_response(json_decode($e->getResponse()->getBody(true)));
  //   }
  // }

  public function getAppToken()
  {
    $client = new Client();

    try {
      $response = $client->request('GET',
      'https://graph.facebook.com/oauth/access_token?' .
      'grant_type=client_credentials&' .
      'client_id=' . $this->appId . '&' .
      'client_secret=' . $this->appSecret );
    } catch (RequestException $e) {
      echo $e->getResponse()->getBody(true);
    }

    return json_decode($response->getBody())->access_token;
  }

  public function getTokenExpiryDate()
  {
    if($this->accessToken == ''){
      return false;
    }

    $appToken = $this->getAppToken();
    $client = new Client();

    try {
      $response = $client->request('GET',
      'https://graph.facebook.com/debug_token?' .
      'input_token=' . $this->accessToken . '&' .
      'access_token=' . $appToken );
    } catch (RequestException $e) {
      echo $e->getResponse()->getBody(true);
    }

    $data = json_decode($response->getBody())->data;
    return $data->expires_at;
  }

}
