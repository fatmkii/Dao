<template>
  <div v-if="this_show">
    <p class="mt-2">管理员中心</p>
    <p>我的饼干是：{{ this.$store.state.User.Binggan }}</p>
    <p>管理的板块：{{ this.$store.state.User.AdminForums }}</p>
    <hr />
    <p class="mt-2">管理员操作面板</p>
    <b-tabs pills>
      <b-tab title="发布公告">
        <hr />
        <b-form-checkbox class="mt-2" v-model="to_new_users" switch>
          对以后注册的新用户也生效
        </b-form-checkbox>
        <hr />
        <PostInput
          ref="post_input_com"
          has_title
          :has_nickname="false"
          :forum_id="1"
          :input_disable="false"
          :new_post_handling="new_post_handling"
          @content_commit="new_annouce_handle"
        ></PostInput>
      </b-tab>
      <b-tab title="查看公告" @click="get_annouce_data">
        <hr />
        <b-pagination
          v-model="page"
          :total-rows="total_rows"
          :per-page="per_page"
          size="sm"
        ></b-pagination>
      </b-tab>
    </b-tabs>
    <hr />
    <p class="mt-2" v-if="this.$store.state.User.AdminStatus == 99">
      超管操作面板
    </p>
    <b-tabs pills v-if="this.$store.state.User.AdminStatus == 99">
      <b-tab title="发帖记录">
        <div class="mx-2 my-2">
          <router-link to="/admin/check_user_post">
            查询用户发帖记录
          </router-link>
        </div>
      </b-tab>
      <b-tab title="用户操作记录">
        <div class="mx-2 my-2">
          <p class="my-2">在做了在做了</p>
        </div>
      </b-tab>
      <b-tab title="设定表情包">
        <div class="mx-2 my-2">
          <p class="my-2">在做了在做了</p>
        </div>
      </b-tab>
    </b-tabs>
  </div>
</template>


<script>
import PostInput from "../component/post_input.vue";

export default {
  components: { PostInput },
  props: {},
  watch: {
    page() {
      this.get_annouce_data();
    },
  },
  data: function () {
    return {
      name: "admin_center",
      new_post_handling: false,
      to_new_users: false,
      page: 1,
      per_page: 10,
      announcements_load_status: 0,
      announcements_data: [],
      announcements_last_page: 1,
      total_rows: 0,
    };
  },
  computed: {
    this_show() {
      if (this.$store.state.User.UserDataLoaded == 2) {
        if (this.$store.state.User.AdminStatus != 0) {
          return true;
        } else {
          this.$router.replace("/admin_login");
          return false;
        }
      } else {
        return false;
      }
    },
  },
  mounted() {
    document.title = "管理员中心";
  },
  methods: {
    get_annouce_data() {
      this.announcements_load_status = 1;
      var config = {
        method: "get",
        url: "/api/admin/announcements/",
        params: {
          page: this.page,
          per_page: this.per_page,
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.announcements_data = response.data.data.data; //因为多了分页数据，多了个data
            this.announcements_last_page = response.data.data.lastPage;
            this.total_rows = this.announcements_last_page * this.per_page;
            this.announcements_load_status = 2;
          } else {
            this.announcements_load_status = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.announcements_load_status = 0;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    new_annouce_handle(content) {
      this.new_post_handling = true;
      const config = {
        method: "post",
        url: "/api/admin/create_announcement",
        data: {
          binggan: this.$store.state.User.Binggan,
          type: 1, //1=所有人公告、其他未定
          sub_title: "[公告]",
          to_new_users: this.to_new_users,
          //来自PostInput组件
          content: content.content_input,
          title: content.title_input,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$refs.post_input_com.content_input = "";
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.new_post_handling = false;
          } else {
            this.new_post_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.new_post_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
  },
  created() {
    
  },
};
</script>