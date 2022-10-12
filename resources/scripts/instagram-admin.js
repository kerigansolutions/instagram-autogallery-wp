import { createApp } from "vue";
import InstagramAuth from "./components/InstagramAuth.vue";
import SyncTool from "./components/SyncTool.vue";

const app = createApp({

  components: {
    InstagramAuth: InstagramAuth,
    SyncTool: SyncTool,
  },

  data: () => ({
    mounted: false,
  }),

  mounted () {
    this.mounted = true;
  },

})

app.mount("#kma-instagram-settings")
