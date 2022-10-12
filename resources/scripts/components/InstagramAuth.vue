<template>
  <div>
    <p v-if="businesses.length > 0" class="text-gray-400 text uppercase font-bold mb-2">Select a Instagram Business Page To Sync:</p>
    <div
      v-for="business in businesses"
      :key="business.index"
      class="p-2 bg-gray-200 rounded flex items-center mb-1 border border-gray-300 group group-hover:bg-gray-400 cursor-pointer"
      @click.prevent="selectBusiness(business)"
    >
      <strong class="text-primary px-4">{{ business.name }}</strong>
      <span class="text-sm text-gray-800" > {{ business.id }}</span>
      <button class="px-3 py-1 ml-auto leading-none h-8 border-2 uppercase rounded text-primary border-primary group-hover:bg-primary group-hover:text-white group-hover:border-transparent" >select</button>
    </div>
    <div class="flex" >
      <button
        @click.prevent="authorize"
        class="form-button bg-primary hover:bg-white border-2 border-transparent hover:border-primary text-white hover:text-primary rounded"
      >
        <slot />
      </button>
    </div>
  </div>
</template>
<script>
export default {
  name: "InstagramAuth",

  data() {
    return {
      accessToken: undefined,
      data_access_expiration_time: undefined,
      expiresIn: undefined,
      graphDomain: undefined,
      signedRequest: undefined,
      userID: undefined,
      status: undefined,
      longLivedToken: undefined,
      instagramID: undefined,
      businesses: []
    }
  },

  methods: {
    selectBusiness (business) {
      this.getInstagramPage(business)

      fetch("/wp-json/kerigansolutions/v1/instagallerytoken?token=" + business.access_token, {
        method: 'GET',
        mode: 'cors',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
        },
      })
        .then(r => r.json())
        .then((res) => {
          console.log(res)
          this.instagramID = res.access_token;
          document.getElementById('insttoken').value = res.access_token;
        })
      this.businesses = []
    },

    getInstagramPage (business) {
      fetch("https://graph.facebook.com/v15.0/" + business.id + "?fields=instagram_business_account&access_token=" + business.access_token, {
        method: 'GET',
        mode: 'cors',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
        },
      })
        .then(r => r.json())
        .then((res) => {
          console.log(res)
          this.instagramID = res.instagram_business_account.id;
          document.getElementById('instcompanyid').value = res.instagram_business_account.id;
        })
    },

    authorize () {
      let auth = this

      FB.login(function (response) {
        auth.accessToken = response.authResponse.accessToken
        auth.data_access_expiration_time = response.authResponse.data_access_expiration_time
        auth.expiresIn = response.authResponse.expiresIn
        auth.graphDomain = response.authResponse.graphDomain
        auth.signedRequest = response.authResponse.signedRequest
        auth.userID = response.authResponse.userID,
        auth.status = response.status

        console.log(response)

        FB.api('/' + auth.userID + '/accounts?limit=999', function (response) {
          console.log(response)
          auth.businesses = response.data
        })

      }, {
        scope: 'instagram_basic, pages_show_list'
      });
    }
  }
}
</script>
