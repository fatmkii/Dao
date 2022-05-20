
<template>
  <div>
    <b-carousel
      id="carousel-fade"
      fade
      :interval="10000"
      img-width="825"
      img-height="224"
      v-if="forum_banners && threads_load_status"
      v-show="!banner_hiden"
    >
      <b-carousel-slide
        v-for="(banner, index) in JSON.parse(
          this.$store.state.Forums.CurrentForumData.banners
        ).sort(function () {
          return 0.5 - Math.random();
        })"
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
        <b-dropdown
          id="shield-dropdown"
          text="筛选"
          variant="outline-dark"
          size="sm"
        >
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
          variant="success"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="new_thread_botton"
          >发表主题</b-button
        >
        <b-button
          size="md"
          class="my-1 ml-1 my-sm-0 d-none d-lg-block"
          variant="success"
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
        variant="success"
        @click="get_threads_data(true, threads_per_page, search_input)"
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
      <a
        v-if="show_delay"
        @click="get_threads_data"
        class="thread_title ml-auto"
        >关闭</a
      >
      <a
        v-if="!show_delay"
        @click="get_delay_threads_data"
        class="thread_title ml-auto"
        >查看延时发送主题</a
      >
    </div>
    <div class="z-sidebar">
      <transition-group name="z-sidebar" tag="div">
        <div
          class="icon-top"
          @click="scroll_icon_click('top')"
          key="icon-top"
          v-show="z_bar_show"
        >
          <svg
            aria-hidden="true"
            focusable="false"
            data-prefix="fas"
            data-icon="arrow-up"
            class="svg-inline--fa fa-arrow-up fa-w-14"
            role="img"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 448 512"
          >
            <path
              fill="currentColor"
              d="M34.9 289.5l-22.2-22.2c-9.4-9.4-9.4-24.6 0-33.9L207 39c9.4-9.4 24.6-9.4 33.9 0l194.3 194.3c9.4 9.4 9.4 24.6 0 33.9L413 289.4c-9.5 9.5-25 9.3-34.3-.4L264 168.6V456c0 13.3-10.7 24-24 24h-32c-13.3 0-24-10.7-24-24V168.6L69.2 289.1c-9.3 9.8-24.8 10-34.3.4z"
            ></path>
          </svg>
        </div>

        <div
          class="icon-new-thread"
          @click="new_thread_botton"
          key="icon-new-thread"
          v-show="z_bar_show"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="currentColor"
            class="bi bi-chat-dots-fill"
            viewBox="0 0 16 16"
          >
            <path
              d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
            />
          </svg>
        </div>

        <div
          class="icon-reload"
          @click="get_threads_data(true)"
          key="icon-reload"
          v-show="z_bar_show"
        >
          <svg
            aria-hidden="true"
            focusable="false"
            data-prefix="fas"
            data-icon="sync-alt"
            class="svg-inline--fa fa-sync-alt fa-w-16"
            role="img"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 512 512"
          >
            <path
              fill="currentColor"
              d="M370.72 133.28C339.458 104.008 298.888 87.962 255.848 88c-77.458.068-144.328 53.178-162.791 126.85-1.344 5.363-6.122 9.15-11.651 9.15H24.103c-7.498 0-13.194-6.807-11.807-14.176C33.933 94.924 134.813 8 256 8c66.448 0 126.791 26.136 171.315 68.685L463.03 40.97C478.149 25.851 504 36.559 504 57.941V192c0 13.255-10.745 24-24 24H345.941c-21.382 0-32.09-25.851-16.971-40.971l41.75-41.749zM32 296h134.059c21.382 0 32.09 25.851 16.971 40.971l-41.75 41.75c31.262 29.273 71.835 45.319 114.876 45.28 77.418-.07 144.315-53.144 162.787-126.849 1.344-5.363 6.122-9.15 11.651-9.15h57.304c7.498 0 13.194 6.807 11.807 14.176C478.067 417.076 377.187 504 256 504c-66.448 0-126.791-26.136-171.315-68.685L48.97 471.03C33.851 486.149 8 475.441 8 454.059V320c0-13.255 10.745-24 24-24z"
            ></path>
          </svg>
        </div>
        <div
          class="icon-down"
          @click="scroll_icon_click('bottom')"
          key="icon-down"
          v-show="z_bar_show"
        >
          <svg
            aria-hidden="true"
            focusable="false"
            data-prefix="fas"
            data-icon="arrow-down"
            class="svg-inline--fa fa-arrow-down fa-w-14"
            role="img"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 448 512"
          >
            <path
              fill="currentColor"
              d="M413.1 222.5l22.2 22.2c9.4 9.4 9.4 24.6 0 33.9L241 473c-9.4 9.4-24.6 9.4-33.9 0L12.7 278.6c-9.4-9.4-9.4-24.6 0-33.9l22.2-22.2c9.5-9.5 25-9.3 34.3.4L184 343.4V56c0-13.3 10.7-24 24-24h32c13.3 0 24 10.7 24 24v287.4l114.8-120.5c9.3-9.8 24.8-10 34.3-.4z"
            ></path>
          </svg>
        </div>
      </transition-group>
      <div class="icon-box" @click="z_bar_show = !z_bar_show">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="currentColor"
          class="bi bi-stack"
          viewBox="0 0 16 16"
        >
          <path
            d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.598.598 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.598.598 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.598.598 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535L7.733.063z"
          />
          <path
            d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.598.598 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.659z"
          />
        </svg>
      </div>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
import ForumThreads from "./forum_threads.vue";
import ForumPaginator from "./forum_paginator.vue";
import ForumThreadsMobile from "./forum_threads_mobile.vue";

export default {
  components: { ForumThreads, ForumPaginator, ForumThreadsMobile },
  props: {
    forum_id: Number, //来自router，
    page: Number, //来自router
  },
  watch: {
    // 如果路由有变化，再次获得数据
    $route(to) {
      if (this.search_input) {
        this.get_threads_data(false, this.threads_per_page, this.search_input);
      } else {
        this.get_threads_data();
      }
      this.$store.commit("ThreadsLoadStatus_set", 0);
    },
    new_window_to_post: function () {
      localStorage.setItem(
        "new_window_to_post",
        this.new_window_to_post ? "true" : ""
      );
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
      z_bar_show: false,
      search_show: false,
      search_input: "",
      show_delay: false,
      subtitles_selected: {
        "[公告]": true,
        "[闲聊]": true,
        "[专楼]": true,
        "[刷刷]": true,
      },
    };
  },
  computed: {
    subtitles_options() {
      let options = subtitles[0].subtitles; //subtitles来源于json.js全局变量
      options = subtitles[1].subtitles.concat(options); //加上管理员选项
      return options;
    },
    ...mapState({
      forum_name: (state) =>
        state.Forums.CurrentForumData.name
          ? state.Forums.CurrentForumData.name
          : "",
      forum_banners: (state) => state.Forums.CurrentForumData.banners,
      threads_load_status: (state) => state.Threads.ThreadsLoadStatus,
      threads_last_page: (state) => state.Threads.ThreadsData.lastPage,
      threads_per_page: (state) => state.MyCSS.ThreadsPerPage,
    }),
  },
  methods: {
    get_threads_data(
      remind = false,
      threads_per_page = this.threads_per_page,
      search_title = undefined
    ) {
      var config = {
        method: "get",
        url: "/api/forums/" + this.forum_id,
        params: {
          page: this.page,
          binggan: this.$store.state.User.Binggan,
          threads_per_page: threads_per_page,
        },
      };
      if (search_title) {
        config.params.search_title = search_title;
      }
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("ThreadsData_set", response.data.threads_data);
            this.$store.commit(
              "CurrentForumData_set",
              response.data.forum_data
            );
            this.$store.commit("ThreadsLoadStatus_set", 1);
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
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
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
            this.$store.commit(
              "CurrentForumData_set",
              response.data.forum_data
            );
            this.$store.commit("ThreadsLoadStatus_set", 1);
            document.title = this.forum_name;
            this.show_delay = true;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
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
      localStorage.setItem(
        "subtitles_filter",
        JSON.stringify(this.subtitles_selected)
      );
    },
  },
  created() {
    this.get_threads_data();
    this.load_subtitles_selected();
    this.$store.commit("ThreadsLoadStatus_set", 0);
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