<template>
  <div v-if="this_show">
    <p class="mt-2">管理员中心</p>
    <p>我的饼干是：{{ this.$store.state.User.Binggan }}</p>
    <div>管理的板块：</div>
    <div v-for="(forum, index) in admin_forums" :key="index">
      <b-badge variant="secondary" pill>{{
        forum.id
      }}</b-badge>
      <span class="ml-2"> {{ forum.name }}</span>
    </div>

    <hr />
    <p class="mt-2">管理员操作面板</p>
    <b-tabs pills>
      <b-tab title="版头设定">
        <div class="mx-2 my-2">
          <b-form-select v-model="forum_selected" :options="forum_options"></b-form-select>
          <b-form-textarea id="banner_input" class="my-2" v-model.lazy="banner_input" rows="3"
            max-rows="8"></b-form-textarea>
          <div class="d-flex align-items-center mt-2">
            <b-button :variant="button_theme" :disabled="ajax_handling" @click="banner_set_handle">提交
            </b-button>
          </div>
          <div class="m-1" v-for="(banner_src, index) in banners_array" :key="index">
            <b-img :src="banner_src" fluid alt="banner"></b-img>
          </div>
        </div>
      </b-tab>
    </b-tabs>
    <hr />
    <p class="mt-2" v-if="this.$store.state.User.AdminStatus == 99">
      超管操作面板
    </p>
    <b-tabs pills v-if="this.$store.state.User.AdminStatus == 99">
      <b-tab title="发放成就">
        <div class="mx-2 my-2" style="max-width: 350px;">
          <b-input-group prepend="发放饼干" class="mt-2">
            <b-form-input v-model="binggan_target"></b-form-input>
          </b-input-group>
          <b-input-group prepend="发放成就" class="mt-2">
            <b-form-select v-model="medal_id" :options="medal_id_options" value-field="value"
              text-field="text"></b-form-select>
          </b-input-group>
          <div class="mt-2">
            <b-button :variant="button_theme" :disabled="ajax_handling" @click="create_medal_handle">提交
            </b-button>
          </div>
        </div>
      </b-tab>
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
import { mapState } from "vuex";
export default {
  components: {},
  props: {},
  watch: {
    forums_load_status() {
      if (this.forums_load_status === 2) {
        this.set_banner_input();
      }
    },
    forum_selected() {
      if (this.forums_load_status === 2) {
        this.set_banner_input();
      }
    },
  },
  data: function () {
    return {
      name: "admin_center",
      forum_selected: 0,
      ajax_handling: false,
      banner_input: "",
      binggan_target: "",
      medal_id_options: [
        { value: 182, text: "小火锅守护者" },
      ],
      medal_id: 182,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    forum_options() {
      var options = [];
      this.forums_data.forEach((forum, index) => {
        options.push({ value: index, text: forum.id + "  " + forum.name });
      });
      return options;
    },
    banners_array() {
      try {
        var array = JSON.parse(this.banner_input);
      } catch (e) {
        var array = [];
      }
      return array;
    },
    admin_forums() {
      var array = [];
      if (this.forums_load_status === 2 && this.user_load_status === 2) {
        this.forums_data.forEach((forum, index) => {
          if (this.$store.state.User.AdminForums.includes(forum.id)) {
            array.push({
              id: forum.id,
              name: forum.name,
            });
          }
        });
      }
      return array;
    },
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
    ...mapState({
      forums_data: (state) => state.Forums.ForumsData,
      forums_load_status: (state) => state.Forums.ForumsLoadStatus,
      user_load_status: (state) => state.User.UserDataLoaded,
    }),
  },
  mounted() {
    document.title = "管理员中心";
  },
  methods: {
    banner_set_handle() {
      try {
        //转换并确认用户输入是否满足JSON格式
        var banners = JSON.stringify(JSON.parse(this.banner_input));
      } catch (e) {
        alert("版头JSON格式输入有误，请检查");
        return;
      }
      this.ajax_handling = true;
      const config = {
        method: "post",
        url: "/api/admin/set_banner",
        data: {
          binggan: this.$store.state.User.Binggan,
          banners: banners,
          forum_id: this.forums_data[this.forum_selected].id,
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
            this.ajax_handling = false;
          } else {
            this.ajax_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.ajax_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    create_medal_handle() {
      var confirmed = confirm("要给该用户发放成就吗")
      if (confirmed == false) {
        return;
      }

      if (this.binggan_target == null) {
        alert('未输入发放饼干')
        return;
      }

      this.ajax_handling = true;
      const config = {
        method: "post",
        url: "/api/admin/create_medal",
        data: {
          binggan: this.$store.state.User.Binggan,
          binggan_target: this.binggan_target,
          medal_id: this.medal_id
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.ajax_handling = false;
          } else {
            this.ajax_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.ajax_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    set_banner_input() {
      var banners = this.forums_data[this.forum_selected].banners;
      if (banners) {
        banners = banners.replace(/,/g, ",\n"); //把,改成换行，方便看
        banners = banners.replace(/\[/g, "[\n");
        banners = banners.replace(/]/g, "\n]");
      }
      this.banner_input = banners;
    },
  },
  created() { },
};
</script>