<template>
  <div class="mt-3" v-if="this_show">
    <p>查询发帖记录</p>
    <div class="row align-items-center">
      <div class="col-auto">
        <b-input-group prepend="饼干：" style="max-width: 400px">
          <b-form-input v-model="binggan"></b-form-input>
          <b-input-group-append>
            <b-button variant="success" @click="check_user_post_handle">
              查询
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </div>
      <div class="col-auto">
        <b-input-group prepend="IP：" style="max-width: 400px">
          <b-form-input v-model="IP"></b-form-input>
          <b-input-group-append>
            <b-button variant="success" @click="check_user_post_handle">
              查询
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </div>
      <div class="col-auto">
        <span>Posts数据表</span>
        <b-form-select
          v-model="database_post_num"
          :options="database_post_num_options"
        ></b-form-select>
      </div>
    </div>

    <hr />
    <div v-show="posts_load_status">
      <b-pagination-nav
        :number-of-pages="last_page"
        v-model="page"
        base-url="/admin/check_user_post/"
        limit="5"
        use-router
        first-number
        last-number
        class="my-2"
        size="sm"
      ></b-pagination-nav>
      <div v-for="post_data in posts_data" :key="post_data.id">
        <PostItem
          :post_data="post_data"
          :thread_anti_jingfen="0"
          :random_head_add="null"
          :admin_button_show="false"
          :no_image_mode="false"
        >
          <template v-slot:header>
            <router-link
              :to="
                '/thread/' +
                post_data.thread_id +
                '/' +
                Math.ceil((post_data.floor + 1) / 200) +
                '#f_' +
                post_data.floor
              "
              >原帖地址</router-link
            >
            <span class="ml-1">IP:{{ post_data.created_IP }}</span>
            <span class="ml-1">饼干:{{ post_data.created_binggan }}</span>
          </template>
        </PostItem>
      </div>
    </div>
  </div>
</template>


<script>
import PostItem from "../component/post_item.vue";

export default {
  components: { PostItem },
  props: {
    page: Number, //来自router
  },
  watch: {
    // 如果路由有变化，再次获得数据
    $route(to) {
      this.check_user_post_handle();
    },
  },
  data: function () {
    return {
      name: "check_user_post",
      binggan: null,
      IP: null,
      posts_load_status: false,
      posts_data: null,
      database_post_num_options: [0, 1, 2],
      database_post_num: 1,
      last_page: 1,
    };
  },
  computed: {
    this_show() {
      if (this.$store.state.User.UserDataLoaded == 2) {
        if (this.$store.state.User.AdminStatus == 99) {
          document.title = "查询发帖记录";
          return true;
        } else {
          this.$router.replace("/admin_login");
        }
      } else {
        return false;
      }
    },
  },
  mounted() {},
  methods: {
    check_user_post_handle() {
      this.posts_load_status = false;
      const config = {
        method: "get",
        url: "/api/admin/check_user_post",
        params: {
          page: this.page,
          binggan: this.binggan == "" ? null : this.binggan,
          IP: this.IP == "" ? null : this.IP,
          database_post_num: this.database_post_num,
        },
      };
      axios(config)
        .then((response) => {
          this.posts_data = response.data.posts_data.data;
          this.last_page = response.data.posts_data.lastPage;
          this.posts_load_status = true;
        })
        .catch((error) => {
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
  },
};
</script>