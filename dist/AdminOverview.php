<?php

use KeriganSolutions\Instagram\WP\Admin;

$instagram = new Admin();

$tokenExpires = $instagram->getTokenExpiryDate();

$InstagramPageID  = (isset($_POST['instagram_page_id']) ? sanitize_text_field($_POST['instagram_page_id']) : get_option('instagram_page_id'));
$InstagramToken   = (isset($_POST['instagram_token']) ? sanitize_text_field($_POST['instagram_token']) : get_option('instagram_token'));

$InstagramSecret  = (isset($_POST['instagram_app_secret']) ? sanitize_text_field($_POST['instagram_app_secret']) : get_option('instagram_app_secret'));

if (isset($_POST['instagram_settings']) && $_POST['instagram_settings'] == 'yes') {
  update_option('instagram_page_id',
      isset($_POST['instagram_page_id']) ? sanitize_text_field($_POST['instagram_page_id']) : $InstagramPageID);
  update_option('instagram_token',
      isset($_POST['instagram_token']) ? sanitize_text_field($_POST['instagram_token']) : $InstagramToken);
}

if (isset($_POST['instagram_secret_settings']) && $_POST['instagram_secret_settings'] == 'yes') {
    update_option('instagram_app_secret',
        isset($_POST['instagram_app_secret']) ? sanitize_text_field($_POST['instagram_app_secret']) : $InstagramSecret);
}

?>
<link href="/styles/instagram-admin.css" rel="stylesheet">
<div id="kma-instagram-settings" class="text-base" style="margin-left:-20px;">
  <div class="p-8 lg:p-12">
    <h1 class="font-bold text-xl lg:text-4xl text-primary">
      Instagram Settings
    </h1>
  </div>
  <div class="section px-8 pb-8 lg:px-12">
    <div class="grid grid-cols-12 gap-4 lg:gap-8">

      <!-- Needs App Secret -->
      <?php if(!get_option('instagram_app_secret')){ ?>
      <div class="col-span-12 p-8 bg-white shadow-lg shadow-primary/20" >
        <p id="secret-headline" class="text-gray-600 text-2xl mb-4">Assign App Secret</p>
        <p class="is-small">If you don't have this, ask your developer.</p>

        <form
          enctype="multipart/form-data"
          name="instagram_secret_settings"
          id="instagram_secret_settings"
          method="post"
          action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>"
        >
          <input type="hidden" name="instagram_secret_settings" value="yes">
          <div class="input-wrapper">
            <input
              type="text"
              class="text-input px-4"
              name="instagram_app_secret"
              id="instagram_app_secret"
              value="<?= $InstagramSecret; ?>"
            >
            <button class="form-button bg-primary hover:bg-white border-2 border-transparent hover:border-primary text-white hover:text-primary rounded" >
              Save
            </button>
          </div>
        </form>

      </div>
      <?php } ?>

      <!-- Request a token -->
      <div class="col-span-12 p-8 bg-white shadow-lg shadow-primary/20" >
        <instagram-auth>
          <?php get_option('instagram_token') ? _e('Renew Authorization') : _e('Authorize App') ?>
        </instagram-auth>
      </div>

      <div class="col-span-12 p-8 bg-white shadow-lg shadow-primary/20" >

        <div id="accountoptions" class="columns is-multiline"></div>
        <div id="error"></div>

        <form
          enctype="multipart/form-data"
          name="instagram_settings"
          id="instagram_settings"
          method="post"
          action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>"
        >
          <input type="hidden" name="instagram_settings" value="yes">
          <div class="grid md:grid-cols-2 gap-4 lg:gap-8 pb-8">
            <div>
              <p class="text-gray-400 text uppercase font-bold mb-2">Instagram Page ID</p>
              <div class="input-wrapper">
                <input
                  type="text"
                  class="text-input px-4"
                  name="instagram_page_id"
                  id="instcompanyid"
                  value="<?= $InstagramPageID; ?>"
                  size="40"
                >
              </div>
            </div>
            <div>
              <p class="text-gray-400 text uppercase font-bold mb-2">Instagram Access Token</p>
              <div class="input-wrapper">
                <input
                  type="text"
                  class="text-input px-4"
                  name="instagram_token"
                  id="insttoken"
                  value="<?= $InstagramToken; ?>"
                  size="40"
                >
              </div>
            </div>
          </div>

          <div class="flex space-x-4">
            <input
              class="form-button bg-primary hover:bg-white border-2 border-transparent hover:border-primary text-white hover:text-primary rounded"
              type="submit"
              name="Submit"
              value="<?php _e('Update Settings') ?>"
            />
          </div>

        </form>
      </div>

      <div class="col-span-12 md:col-span-6 p-8 bg-white shadow-lg shadow-primary/20" >
        <p class="text-gray-400 text uppercase font-bold mb-2">Instagram Posts</p>
        <sync-tool id="kma-instagram-posts-sync-tool" endpoint="kma-instagram-post" ></sync-tool>
      </div>

    </div>
  </div>
</div>
<script src="/scripts/instagram-admin.js" ></script>
<script>
  window.fbAsyncInit = function () {
    FB.init({
      appId: '<?php echo $instagram->appId; ?>',
      cookie: true,
      xfbml: true,
      version: 'v12.0'
    });
    FB.AppEvents.logPageView();
  };

  (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
</script>
