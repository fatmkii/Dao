<template>
  <div>
    <b-carousel
      id="carousel-fade"
      fade
      :interval="10000"
      img-width="825"
      img-height="224"
      v-if="threads_load_status == 2 && forum_banners"
      v-show="!banner_hiden"
    >
      <b-carousel-slide
        v-for="(banner, index) in forum_banners"
        :key="index"
        :img-src="banner"
      ></b-carousel-slide>
    </b-carousel>
    <div class="row align-items-center mt-3">
      <div class="col-auto h5 d-none d-lg-block d-xl-block">
        <b-badge variant="secondary" pill class="float-left">
          {{ forum_id }}
        </b-badge>
        <span>{{ forum_name }}</span>
      </div>
      <div class="col-auto h6 d-block d-lg-none d-xl-none">
        <b-badge variant="secondary" pill class="float-left">
          {{ forum_id }}
        </b-badge>
        <span id="forum_name">{{ forum_name }}</span>
      </div>
      <div class="col-auto d-inline-flex">
        <b-form-checkbox v-model="new_window_to_post" switch class="ml-2">
          新窗口打开
        </b-form-checkbox>
        <b-form-checkbox v-model="banner_hiden" switch class="ml-2">
          隐藏版头
        </b-form-checkbox>
      </div>
      <div class="col-auto d-inline-flex">
        <b-dropdown id="shield-dropdown" text="筛选" variant="outline-dark" size="sm">
          <b-form-checkbox
            v-for="(option, index) in subtitles_options"
            :key="index"
            v-model="subtitles_selected[option.value]"
            @change="set_subtitltes_selected"
            class="ml-2 my-2"
          >
            {{ option.value }}
          </b-form-checkbox>
        </b-dropdown>
      </div>
      <div class="col-auto my-2 ml-auto d-flex">
        <span v-if="!this.$store.state.User.LoginStatus">
          请在先<router-link to="/login">导入或领取饼干 </router-link>
          后才能发言喔
        </span>
        <b-button
          size="sm"
          class="my-1 ml-1 my-sm-0 d-lg-none"
          variant="outline-dark"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="search_show = !search_show"
          >搜索</b-button
        >
        <b-button
          size="md"
          class="my-1 ml-1 my-sm-0 d-none d-lg-block"
          variant="outline-dark"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="search_show = !search_show"
          >搜索</b-button
        >
        <b-button
          size="sm"
          class="my-1 ml-1 my-sm-0 d-lg-none"
          :variant="button_theme"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="new_thread_botton"
          >发表主题</b-button
        >
        <b-button
          size="md"
          class="my-1 ml-1 my-sm-0 d-none d-lg-block"
          :variant="button_theme"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="new_thread_botton"
          >发表主题</b-button
        >
      </div>
    </div>
    <div class="d-flex flex-row my-2" v-if="search_show">
      <b-form-input
        id="search_input"
        class="search_input"
        style="max-width: 400px"
        placeholder="目前只支持搜索标题"
        v-model="search_input"
      ></b-form-input>
      <b-button
        size="sm"
        class="ml-1"
        style="min-width: 46px"
        :variant="button_theme"
        @click="
          get_threads_data(true, threads_per_page, subtitles_excluded, search_input)
        "
        >搜索</b-button
      >
    </div>
    <ForumThreads
      :forum_id="forum_id"
      :new_window_to_post="new_window_to_post"
      :subtitles_selected="subtitles_selected"
    ></ForumThreads>
    <ForumThreadsMobile
      :forum_id="forum_id"
      :new_window_to_post="new_window_to_post"
      :subtitles_selected="subtitles_selected"
    ></ForumThreadsMobile>
    <div class="d-flex align-items-center">
      <ForumPaginator
        :forum_id="forum_id"
        :last_page="threads_last_page"
      ></ForumPaginator>
      <a v-if="show_delay" @click="get_threads_data" class="thread_title ml-auto">关闭</a>
      <a v-if="!show_delay" @click="get_delay_threads_data" class="thread_title ml-auto"
        >查看延时发送主题</a
      >
    </div>

    <img
      src="https://oss.cpttmm.com/xhg_other/notice_404.png"
      v-if="threads_load_status == 2 && thread_reject_code == 22404"
      class="nissined_img"
    />

    <img
      src="https://oss.cpttmm.com/xhg_other/notice_4.png"
      v-if="threads_load_status == 2 && thread_reject_code == 21404"
      class="nissined_img"
    />

    <ZBar @reload="get_threads_data(true)" reload>
      <template v-slot:top>
        <div class="icon-new-thread" @click="new_thread_botton" key="icon-new-thread">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="currentColor"
            class="bi bi-chat-dots-fill"
            viewBox="0 0 16 16"
          >
            <path
              d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
            />
          </svg></div
      ></template>
    </ZBar>
  </div>
</template>

<script>
import { mapState } from "vuex";
import ForumThreads from "./forum_threads.vue";
import ForumPaginator from "./forum_paginator.vue";
import ForumThreadsMobile from "./forum_threads_mobile.vue";
import ZBar from "../component/z_bar.vue";

export default {
  components: { ForumThreads, ForumPaginator, ForumThreadsMobile, ZBar },
  props: {
    forum_id: Number, //来自router，
    page: Number, //来自router
  },
  watch: {
    // 如果路由有变化，再次获得数据
    $route(to) {
      this.$store.commit("ThreadsLoadStatus_set", 1);
      if (this.search_input) {
        this.get_threads_data(
          false,
          this.threads_per_page,
          this.subtitles_excluded,
          this.search_input
        );
      } else {
        this.get_threads_data();
      }
    },
    new_window_to_post: function () {
      localStorage.setItem("new_window_to_post", this.new_window_to_post ? "true" : "");
    },
    banner_hiden: function () {
      localStorage.setItem("banner_hiden", this.banner_hiden ? "true" : "");
    },
  },
  data: function () {
    return {
      name: "forum_page",
      new_window_to_post: false,
      banner_hiden: false,
      search_show: false,
      search_input: "",
      show_delay: false,
      thread_reject_code: 0,
      subtitles_selected: {
        "[公告]": true,
        "[闲聊]": true,
        "[专楼]": true,
        "[刷刷]": true,
        "[私密]": true,
      },
    };
  },
  computed: {
    subtitles_options() {
      let options = subtitles[0].subtitles; //subtitles来源于json.js全局变量
      options = subtitles[1].subtitles.concat(options); //加上管理员选项
      options = subtitles[2].subtitles.concat(options); //加上[私密]等选项
      return options;
    },
    subtitles_excluded() {
      let result = [];
      for (let key in this.subtitles_selected) {
        if (this.subtitles_selected[key] == false) {
          result.push(key);
        }
      }
      return result;
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },

    ...mapState({
      forum_name: (state) =>
        state.Forums.CurrentForumData.name ? state.Forums.CurrentForumData.name : "",
      forum_banners: (state) =>
        JSON.parse(state.Forums.CurrentForumData.banners).sort(() => Math.random() - 0.5),
      threads_load_status: (state) => state.Threads.ThreadsLoadStatus,
      threads_last_page: (state) => state.Threads.ThreadsData.lastPage,
      threads_per_page: (state) => state.MyCSS.ThreadsPerPage,
    }),
  },
  methods: {
    get_threads_data(
      remind = false,
      threads_per_page = this.threads_per_page,
      subtitles_excluded = this.subtitles_excluded,
      search_title = undefined
    ) {
      var config = {
        method: "get",
        url: "/api/forums/" + this.forum_id,
        params: {
          page: this.page,
          binggan: this.$store.state.User.Binggan,
          threads_per_page: threads_per_page,
          subtitles_excluded: JSON.stringify(subtitles_excluded),
        },
      };
      if (search_title) {
        config.params.search_title = search_title;
      }
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("ThreadsData_set", response.data.threads_data);
            this.$store.commit("CurrentForumData_set", response.data.forum_data);
            this.$store.commit("ThreadsLoadStatus_set", 2);
            document.title = this.forum_name;
            if (remind) {
              this.$bvToast.toast("已刷新帖子", {
                title: "Done.",
                autoHideDelay: 1500,
                appendToast: true,
              });
            }
            this.show_delay = false;
          } else {
            if ([21404, 22404].includes(response.data.code)) {
              this.thread_reject_code = response.data.code;
              //清空数据，避免显示上一个帖子的数据
              this.$store.commit("ThreadsData_set", "");
              this.$store.commit("CurrentForumData_set", "");
              this.$store.commit("ThreadsLoadStatus_set", 2);
            } else {
              this.$store.commit("ThreadsLoadStatus_set", 0);
              alert(response.data.message);
            }
          }
        })
        .catch((error) => {
          this.$store.commit("ThreadsLoadStatus_set", 0);
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    get_delay_threads_data() {
      var config = {
        method: "get",
        url: "/api/forums/delay/" + this.forum_id,
        params: {
          page: this.page,
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("ThreadsData_set", response.data.threads_data);
            this.$store.commit("CurrentForumData_set", response.data.forum_data);
            this.$store.commit("ThreadsLoadStatus_set", 2);
            document.title = this.forum_name;
            this.show_delay = true;
          } else {
            this.$store.commit("ThreadsLoadStatus_set", 0);
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.$store.commit("ThreadsLoadStatus_set", 0);
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    new_thread_botton() {
      this.$router.push({
        name: "new_thread",
        params: { forum_id: this.forum_id },
      });
    },
    scroll_icon_click(position) {
      switch (position) {
        case "top":
          window.scrollTo(0, 0);
          break;
        case "bottom":
          window.scrollTo(0, document.documentElement.scrollHeight);
          break;
      }
    },
    keyup_callee(event) {
      if (event.ctrlKey && event.key === "q") {
        this.get_threads_data(true);
      }
    },
    load_subtitles_selected() {
      // subtitles来源于json.js的定义。
      if (localStorage.subtitles_filter) {
        this.subtitles_selected = JSON.parse(localStorage.subtitles_filter);
      }
    },
    set_subtitltes_selected() {
      localStorage.setItem("subtitles_filter", JSON.stringify(this.subtitles_selected));
      this.get_threads_data();
    },
  },
  created() {
    this.load_subtitles_selected(); //一定要先load_subtitle再get_threads，不然subttitles不生效
    this.get_threads_data();
    this.$store.commit("ThreadsLoadStatus_set", 1);
    if (localStorage.getItem("new_window_to_post") == null) {
      localStorage.new_window_to_post = "";
    } else {
      this.new_window_to_post = Boolean(localStorage.new_window_to_post);
    }
    if (localStorage.getItem("banner_hiden") == null) {
      localStorage.banner_hiden = "";
    } else {
      this.banner_hiden = Boolean(localStorage.banner_hiden);
    }
  },
  mounted() {
    window.addEventListener("keyup", this.keyup_callee);
  },
  beforeDestroy() {
    window.removeEventListener("keyup", this.keyup_callee);
  },
};
</script>
