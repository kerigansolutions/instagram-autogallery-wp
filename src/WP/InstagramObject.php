<?php

namespace KeriganSolutions\Instagram\WP;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

class InstagramObject {

  protected $instagramPageID;
  protected $instagramToken;
  protected $appSecret;
  protected $appId = '353903931781568';

  public $postType = 'kma-fb-object';
  public $singularName = 'Instagram Object';
  public $pluralName = 'Instagram Objects';
  public $shortcode = 'instobject';
  public $enabled = false;

  /**
   * Get secrets and stuff from WordPress schema
   */
  public function __construct()
  {
      $this->instagramPageID = get_option('instagram_page_id');
      $this->instagramToken = get_option('instagram_token');
      $this->appSecret = get_option('instagram_app_secret');
  }

  public function fields ($post)
  {
    print_r($post);

    return $post;
  }

  /**
   * Get object out of schema and send to front-end
   */
  public function query ($num, $args = [])
  {
    $request = [
      'posts_per_page' => $num,
      'offset'         => 0,
      'order'          => 'DESC',
      'orderby'        => 'date_posted',
      'post_type'      => $this->postType,
      'post_status'    => 'publish',
    ];

    $request   = array_merge($request, $args);
    $postArray = get_posts($request);

    $output = [];
    foreach($postArray as $post){
      $output[] = $this->fields($post);
    }
    return $output;
  }

  /**
   * Format async json response
   */
  public function sync ($request)
  {
    $num = $request->get_param('num');
    $this->getRemote($num ?? 36);
  }

  /**
   * Transform the object to our liking
   */
  public function transform ($input)
  {

    $output = [
      'ID' => 0,
      'post_date' => Carbon::parse($input->timestamp)->copy()->setTimezone(wp_timezone_string())->format('Y-m-d H:i:s'),
      'post_content' => $input->caption,
      'post_title' => $input->id,
      'post_status' => 'publish',
      'post_type' => $this->postType,
    ];

    return $output;
  }

  /**
   * Contact Facebook's Graph API utilizing our composer package
   * for all the heavy lifting.
   */
  public function getRemote ($num = 36)
  {
    $client = new Client();

    try {

      $response = $client->request('GET',
          'https://graph.facebook.com/v15.0/' . $this->instagramPageID .
          '/media' .
          '?access_token=' . $this->instagramToken .
          '&fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username' .
          '&limit=' . $num
      );

      $posts = json_decode($response->getBody());
      if(isset($posts->data)){
        foreach($posts->data as $object) {
          $this->save($object);
        }

        wp_send_json_success();

      } else {
        wp_send_json_error("no posts found");
      }

    } catch (RequestException $e) {
      wp_send_json_error($e->getResponse()->getBody(true));
    }

  }

  /**
   * Save the object to local schema
   */
  public function save ($object)
  {

    $postArray = $this->transform($object);
    $postExists = get_page_by_title($object->id, OBJECT, $this->postType);

    // If exists, update the post. Otherwise, add a new one
    if(isset($postExists->ID)){

      // Catch cancelled events that were added already
      if(isset($object->is_canceled) && $object->is_canceled){
        wp_delete_post($postExists->ID);
      }else{
        $postArray['ID'] = $postExists->ID;
        wp_update_post($postArray);
      }

    }else{
      if(!isset($object->is_canceled) || !$object->is_canceled){
          wp_insert_post($postArray);
      }
    }

  }

  /**
   * Schedule a cron to keep things updated
   */
  public function cron()
  {
    if(! wp_next_scheduled('sync-' . $this->postType)){
      wp_schedule_event(
        strtotime('01:00:00'),
        'hourly',
        'sync-' . $this->postType
      );
    }

    add_action('sync-' . $this->postType, [$this,'getRemote']);
  }

  /**
   * For getting the Instagram resource asynchronously
   */
  public function endpoint ()
  {

    // Gets resource from facebook
    register_rest_route( 'kerigansolutions/v1', '/sync-' . $this->postType,
      [
        'methods'  => 'GET',
        'callback' => [ $this, 'sync' ],
        'permission_callback' => '__return_true'
      ]
    );

    // Gets resource from local schema
    register_rest_route( 'kerigansolutions/v1', '/get-' . $this->postType,
      [
        'methods'  => 'GET',
        'callback' => [ $this, 'collection' ],
        'permission_callback' => '__return_true'
      ]
    );
  }

  public function collection () {
    return rest_ensure_response($this->query(-1));
  }

  /**
   * Creates a post type for our object
   */
  public function createPostType()
  {
    register_post_type( $this->postType,
    array(
      'labels' => array(
        'name' => __( $this->pluralName ),
        'singular_name' => __( $this->singularName )
      ),
      'supports' => ['title','custom-fields'],
      'public' => $this->enabled,
      'has_archive' => false,
      'rewrite' => false,
      'exclude_from_search' => true,
      'publicly_queryable' => false,
      'show_in_menu' => $this->enabled,
      'show_in_rest' => false,
      'menu_icon' => 'dashicons-instagram',
    ));
  }

  /**
   * Create a shortcode so we can pull these modules into content areas
   */
  public function shortcodeFunction ($atts)
  {
    return null;
  }

  /**
   * Enables the object in our environment
   */
  public function use ()
  {
    $this->enabled = true;

    add_action( 'rest_api_init', [$this,'endpoint'] );
    add_action( 'init', [$this,'createPostType'], 50 );
    add_shortcode( $this->shortcode, 'shortcodeFunction' );

    $this->cron();
  }

}
