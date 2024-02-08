<template>
  <div class="gamble-content align-items-center" v-show="!get_pool_data_handling">
    <div class="my-2">
      <span class="ml-2">祝福池总量：{{ olo_total }}个olo</span>
      <b-input-group class="my-2" size="sm">
        <b-form-input placeholder="留下祝福语" style="max-width: 200px" v-model="message"></b-form-input>
        <b-button class="ml-2" :variant="button_theme" size="sm" @click="store_pool_handle"
          :disabled="pool_end_flag || user_stored_flag || pool_before_flag">
          投入祝福</b-button>

      </b-input-group>

    </div>
    <div class="my-2" v-if="!pool_end_flag">
      <span class="ml-2" style="font-size: 0.875rem" v-if="user_stored_flag">已经投过祝福了，<router-link :to="floor_href">在{{
        user_pool_data.floor }}楼</router-link>
      </span>
    </div>
    <div class="my-2" v-if="pool_before_flag">
      <span class="ml-2" style="font-size: 0.875rem" v-if="pool_before_flag">活动将于2月10日 大年初一 0点开始</span>
    </div>
    <div class="my-2" v-if="pool_end_flag">
      <span class="ml-2" style="font-size: 0.875rem" v-if="pool_end_flag">活动已经结束</span>
    </div>
  </div>
</template>


<script>
export default {
  name: "hongbao_pool_component",
  props: {
    thread_id: {
      type: Number,
      default: 0,
    },
    forum_id: {
      type: Number,
      default: 0,
    },
  },
  watch: {
  },
  data: function () {
    return {
      get_pool_data_handling: true,
      store_pool_handling: false,
      message: undefined,
      user_pool_data: [],
      olo_total: undefined,
    };
  },
  computed: {
    pool_end_flag() {
      const end = new Date("2024-02-13 00:00:00");
      const now = new Date(Date.now());
      return now > end
    },
    pool_before_flag() {
      const start = new Date("2024-02-10 00:00:00");
      const now = new Date(Date.now());
      return start > now
    },
    user_stored_flag() {
      if (this.user_pool_data) {
        return true
      } else {
        return false
      }
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    floor_href() {

      const href =
        '/thread/' +
        this.thread_id +
        '/' +
        Math.ceil((this.user_pool_data.floor + 1) / 200) +
        '#f_' +
        this.user_pool_data.floor

      return href

    }
  },
  methods: {
    get_pool_user_data() {
      this.get_pool_data_handling = true;
      const config = {
        method: "get",
        url: "/api/get_pool/",
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.user_pool_data = response.data.data.user_pool;
            this.olo_total = response.data.data.olo_total;
            this.get_pool_data_handling = false;
          } else {
            alert(response.data.message);
            this.get_pool_data_handling = false;
          }
        })
        .catch((error) => {
          this.get_pool_data_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    store_pool_handle() {
      var confirmed = confirm("要投入祝福吗？将会消耗1000个olo。");
      if (confirmed == false) {
        return;
      }
      this.store_pool_handling = true;
      const config = {
        method: "post",
        url: "/api/store_pool/",
        data: {
          binggan: this.$store.state.User.Binggan,
          olo: 1000,
          message: this.message,
          thread_id: this.thread_id,
          forum_id: this.forum_id,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.get_pool_user_data();
            this.$parent.get_posts_data()
            this.store_pool_handling = false;
          } else {
            alert(response.data.message);
            this.store_pool_handling = false;
          }
        })
        .catch((error) => {
          this.store_pool_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  created() {
    this.get_pool_user_data();
  },
};
</script>