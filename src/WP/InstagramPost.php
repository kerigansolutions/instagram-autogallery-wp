<?php

namespace KeriganSolutions\Instagram\WP;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;

class InstagramPost extends InstagramObject {

  public $postType = 'kma-instagram-post';
  public $singularName = 'Instagram Post';
  public $pluralName = 'Instagram Posts';
  public $enabled = false;

  public function fields ($post)
  {
    $post->caption = get_post_meta($post->ID, 'caption', true);
    $post->media_type = get_post_meta($post->ID, 'media_type', true);
    $post->media_url = get_post_meta($post->ID, 'media_url', true);
    $post->permalink = get_post_meta($post->ID, 'permalink', true);
    $post->timestamp = get_post_meta($post->ID, 'timestamp', true);
    $post->username = get_post_meta($post->ID, 'username', true);

    return $post;
  }

  /**
   * Transform the object to our liking for storage in WP schema
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
      'meta_input' => [
        'caption' => isset($input->caption) ? $input->caption : '',
        'media_type' => isset($input->media_type) ? $input->media_type : '',
        'media_url' => isset($input->media_url) ? $input->media_url : '',
        'permalink' => isset($input->permalink) ? $input->permalink : '',
        'timestamp' => isset($input->timestamp) ? $input->timestamp : '',
        'username' => isset($input->username) ? $input->username : '',
      ]
    ];

    // print_r($output);
    // die();

    return $output;

  }

}
