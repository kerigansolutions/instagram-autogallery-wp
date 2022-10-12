# KMA WordPress Instagram Feed
Easily pull posts into your WordPress Site from an Instagram page lnked to a Facebook Business page that you manage. WordPress is required and a special Admin page in WordPress is created. Posts are fetched and added to the WP database using a cron that runs every hour. 

## Status
Currently the app is configured to work with Posts.

## Installation
`composer require kerigansolutions/fb-instagram-wp`

## Setup
1. Make sure you have admin access to the FB page and it is linked to the Instagram page you need to sync.
2. Log into WordPress and go to the new Instagram Settings menu item.
3. Authorize the app using app secret (FYI only KMA knows this).
4. Use the Auth tool to authorize the app to use a Facebook page you manage and select the Instagram feed.
5. Use the sync tool to build the database of posts.
6. Program a view to show the data in your templates.

### Include or Extend the WP Admin class:
```php

use KeriganSolutions\Instagram;
use KeriganSolutions\Instagram\WP\InstagramPost;

class Instagram extends Instagram\WP\Admin
{

  // retrieve posts from WP database
  public function getInstagramPosts($num = -1, $args = [])
  {
    return (new InstagramPost())->query($num, $args);
  }
  
}
```

### Or make the API call and retrieve the results directly:
```php
use KeriganSolutions\Instagram\WP\InstagramPost;

$feed  = new InstagramPost;
$results = $feed->query(5);

```
```javascript
fetch("/wp-json/kerigansolutions/v1/get-kma-instagram-post", {
    method: 'GET',
    mode: 'cors',
    cache: 'no-cache',
    headers: {
        'Content-Type': 'application/json',
    },
})
.then(r => r.json())
.then((res) => {
    // do something with res
})
```