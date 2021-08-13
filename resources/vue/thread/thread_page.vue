
<template>
  <div>
    <div class="thread_body" v-show="posts_load_status">
      <div class="row align-items-center mt-3">
        <div class="col-auto h5 d-none d-lg-block d-xl-block">
          <b-badge variant="secondary" pill class="float-left">
            {{ forum_id }}
          </b-badge>
          <span class="forum_name" @click="back_to_forum">{{
            forum_name
          }}</span>
        </div>
        <div class="col-auto h6 d-block d-lg-none d-xl-none">
          <b-badge variant="secondary" pill class="float-left">
            {{ forum_id }}
          </b-badge>
          <span id="forum_name" @click="back_to_forum">{{ forum_name }}</span>
        </div>
        <div class="col-auto ml-auto">
          <ThreadPaginator
            :thread_id="thread_id"
            :last_page="thread_last_page"
            align="right"
          ></ThreadPaginator>
        </div>
      </div>
      <div class="post_container">
        <div
          class="jump_page alert alert-success px-1 py-1"
          v-if="jump_page_show"
        >
          <span style="font-size: 0.875rem"
            >你上次已阅读到第{{ browse_current.page }}页，<router-link
              :to="'/thread/' + thread_id + '/' + browse_current.page"
              >点击这里跳转</router-link
            >喔~</span
          >
        </div>
        <div class="post_title px-1 py-2 h5 d-none d-lg-block d-xl-block">
          <span style="word-wrap: break-word; white-space: normal"
            >标题：{{ thread_title }}</span
          >
        </div>
        <div class="post_title px-1 py-2 h6 d-block d-lg-none d-xl-none">
          <span style="word-wrap: break-word; white-space: normal"
            >标题：{{ thread_title }}</span
          >
        </div>
        <div
          v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
          class="d-flex align-items-center"
        >
          <b-form-checkbox class="mr-auto" v-model="admin_button_show" switch>
            显示管理员按钮
          </b-form-checkbox>
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.thread_sub_id == 0"
            v-show="admin_button_show"
            @click="thread_set_top"
          >
            置顶
          </b-button>
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.thread_sub_id != 0"
            v-show="admin_button_show"
            @click="thread_cancel_top"
          >
            取消置顶
          </b-button>
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            @click="thread_delete_click_admin"
          >
            删主题
          </b-button>
        </div>
        <div v-for="post_data in posts_data" :key="post_data.id">
          <PostItem
            :post_data="post_data"
            :thread_anti_jingfen="thread_anti_jingfen"
            :random_head_add="random_heads_data[post_data.random_head]"
            :admin_button_show="admin_button_show"
            @quote_click="quote_click_handle"
            @get_posts_data="get_posts_data"
          ></PostItem>
        </div>
      </div>
      <div class="row align-items-center">
        <div class="col-auto">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            class="icon-back bi bi-arrow-left-square"
            viewBox="0 0 16 16"
            v-b-popover.hover.right="'返回小岛'"
            @click="back_to_forum"
          >
            <path
              fill-rule="evenodd"
              d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm11.5 5.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"
            />
          </svg>
        </div>
        <div class="col-auto">
          <ThreadPaginator
            :thread_id="thread_id"
            :last_page="thread_last_page"
            align="left"
          ></ThreadPaginator>
        </div>
      </div>
      <div class="my-2 row d-inline-flex" style="font-size: 0.875rem">
        <div class="col-auto pr-0">昵称</div>
        <div class="col-auto d-inline-flex">
          <b-form-checkbox
            class="mr-auto ml-2"
            v-model="emoji_auto_hide"
            switch
          >
            表情包自动收起
          </b-form-checkbox>
          <b-form-checkbox
            class="mr-auto ml-2"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            v-model="post_with_admin"
            v-b-popover.hover.left="'名字会显示红色'"
            switch
          >
            管理员
          </b-form-checkbox>
        </div>
      </div>
      <b-form-input id="nickname_input" v-model="nickname_input"></b-form-input>
      <Emoji
        :heads_id="thread_heads_id"
        :emoji_auto_hide="emoji_auto_hide"
        @emoji_append="emoji_append"
      ></Emoji>
      <div class="my-2" style="font-size: 0.875rem">内容</div>
      <textarea
        id="content_input"
        class="form-control"
        v-model="content_input"
        rows="3"
        ref="content_input"
        :disabled="
          !this.$store.state.User.LoginStatus ||
          Boolean(locked_TTL) ||
          new_post_handling
        "
        @keyup.ctrl.enter="new_post_handle"
      ></textarea>
      <div class="row align-items-center mt-3">
        <div class="col-auto">
          <b-button
            variant="success"
            size="sm"
            :disabled="
              !this.$store.state.User.LoginStatus ||
              Boolean(locked_TTL) ||
              new_post_handling
            "
            v-b-popover.hover.right="'可以Ctrl+Enter喔'"
            @click="new_post_handle"
            >{{ new_post_handling ? "提交中" : "回复" }}
          </b-button>
        </div>
        <div class="col-auto" style="font-size: 0.875rem">
          <span v-if="!this.$store.state.User.LoginStatus">
            请在先<router-link to="/login">导入或领取饼干</router-link
            >后才能发言喔
          </span>
          <span v-if="locked_TTL">
            你的饼干封禁中，将于{{
              Math.floor(locked_TTL / 3600) + 1
            }}小时后解封。
          </span>
          <span
            v-if="
              this.$store.state.Forums.CurrentForumData.is_nissin == 2 &&
              this.$store.state.Threads.CurrentThreadData.sub_id == 0
            "
            >本贴将于
            <span style="color: #dd0000">{{ nissin_TTL }}</span>
            后日清，请及时更换帖子喔
          </span>
          <span
            v-if="
              this.$store.state.Forums.CurrentForumData.is_nissin == 1 &&
              this.$store.state.Threads.CurrentThreadData.sub_id == 0
            "
            >本小岛定期
            <span style="color: #dd0000">每日早上8点</span>
            日清，请及时更换帖子喔
          </span>
        </div>
      </div>
      <div class="row align-items-center mt-3">
        <div class="col-auto ml-auto">
          <b-button variant="success" size="sm" @click="back_to_forum"
            >返回小岛
          </b-button>
        </div>
      </div>
    </div>

    <div>
      <b-spinner
        id="spinner"
        v-show="!posts_load_status"
        variant="success"
        label="读取中"
      >
      </b-spinner>

      <div class="z-sidebar">
        <div class="icon-top" @click="scroll_top">
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
        <div class="icon-roll" @click="roll_click">
          <svg
            aria-hidden="true"
            focusable="false"
            data-prefix="fas"
            data-icon="dice"
            class="svg-inline--fa fa-dice fa-w-20"
            role="img"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 640 512"
          >
            <path
              fill="currentColor"
              d="M592 192H473.26c12.69 29.59 7.12 65.2-17 89.32L320 417.58V464c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48V240c0-26.51-21.49-48-48-48zM480 376c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm-46.37-186.7L258.7 14.37c-19.16-19.16-50.23-19.16-69.39 0L14.37 189.3c-19.16 19.16-19.16 50.23 0 69.39L189.3 433.63c19.16 19.16 50.23 19.16 69.39 0L433.63 258.7c19.16-19.17 19.16-50.24 0-69.4zM96 248c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm128 128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm0-128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm0-128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm128 128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z"
            ></path>
          </svg>
        </div>
        <div class="icon-reload" @click="get_posts_data(true, false)">
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
        <div class="icon-jump" @click="jump_click">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="currentColor"
            class="bi bi-skip-forward-fill"
            viewBox="0 0 16 16"
          >
            <path
              d="M15.5 3.5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V8.753l-6.267 3.636c-.54.313-1.233-.066-1.233-.697v-2.94l-6.267 3.636C.693 12.703 0 12.324 0 11.693V4.308c0-.63.693-1.01 1.233-.696L7.5 7.248v-2.94c0-.63.693-1.01 1.233-.696L15 7.248V4a.5.5 0 0 1 .5-.5z"
            />
          </svg>
        </div>
        <div class="icon-down" @click="scroll_bottom">
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
      </div>

      <b-modal ref="roll_modal" id="roll_modal">
        <template v-slot:modal-header>
          <h5>Roll点面板</h5>
        </template>
        <template v-slot:default>
          <p style="word-wrap: break-word; white-space: normal">
            输出参考：（只是格式参考啦）
            <br />
            <span v-show="roll_name">「{{ roll_name }}」，</span>
            「{{ roll_event }}」的结果： {{ roll_num }} d {{ roll_range }} =「{{
              roll_simulation
            }}」
          </p>
          <div class="my-1">
            <b-input-group prepend="Roll点昵称">
              <b-form-input
                v-model="roll_name"
                placeholder="可留空"
              ></b-form-input>
            </b-input-group>
            <b-input-group prepend="Roll点事件">
              <b-form-input
                v-model="roll_event"
                placeholder="必填"
              ></b-form-input>
            </b-input-group>
          </div>
          <div class="mt-3">
            <b-input-group prepend="骰子数量">
              <b-form-input
                v-model="roll_num"
                placeholder="max:1000"
              ></b-form-input>
            </b-input-group>
            <b-input-group prepend="骰子大小">
              <b-form-input
                v-model="roll_range"
                placeholder="max:100000000"
              ></b-form-input>
            </b-input-group>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-button-group>
            <b-button
              variant="success"
              :disabled="roll_handling"
              @click="roll_handle"
              >Roll it！</b-button
            >
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
      <b-modal ref="jump_modal" id="jump_modal">
        <template v-slot:modal-header>
          <h5>跳楼机</h5>
        </template>
        <template v-slot:default>
          <p>最大高度：{{ thread_posts_num }}楼</p>
          <div class="my-1">
            <b-input-group prepend="跳到：">
              <b-form-input
                v-model="jump_floor"
                autofocus
                @keyup.enter="jump_handle"
              ></b-form-input>
            </b-input-group>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-button-group>
            <b-button variant="success" @click="jump_handle">Jump！</b-button>
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
import PostItem from "./post_item.vue";
import ThreadPaginator from "./thread_paginator.vue";
import Emoji from "./emoji.vue";

export default {
  components: { PostItem, ThreadPaginator, Emoji },
  props: {
    thread_id: Number, //来自router，
    page: Number, //来自router
  },
  watch: {
    // 如果路由有变化，再次获得数据
    $route(to) {
      this.get_browse_current();
      this.get_posts_data(false, true);
      this.$store.commit("PostsLoadStatus_set", 0);
    },
    post_with_admin: function () {
      this.nickname_input = this.post_with_admin ? "管理员" : "= =";
    },
    emoji_auto_hide: function () {
      localStorage.setItem(
        "emoji_auto_hide",
        this.emoji_auto_hide ? "true" : ""
      );
    },
  },
  beforeRouteUpdate(to, from, next) {
    this.browse_record_handle(); //翻页时候记录浏览进度
    next();
  },
  data: function () {
    return {
      name: "thread_page",
      new_post_handling: false,
      nickname_input: "= =",
      content_input: "",
      roll_name: "",
      roll_event: "",
      roll_num: 1,
      roll_range: 100,
      roll_handling: false,
      random_heads_data: Object,
      admin_button_show: false,
      post_with_admin: false,
      jump_floor: "",
      jump_page_show: false,
      emoji_auto_hide: true,
      browse_current: {
        expire_time: Date.now() + 86400000,
        page: 1,
        height: 0,
      },
    };
  },
  computed: {
    nissin_TTL() {
      const seconds =
        (Date.parse(
          this.$store.state.Threads.CurrentThreadData.nissin_date.replace(
            /-/g,
            "/"
          ) + " GMT+800"
        ) -
          Date.now()) /
        1000;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return hours + "小时" + minutes + "分钟";
    },
    roll_simulation() {
      var roll_simulation = [];
      if (this.roll_num > 0 && this.roll_range > 0) {
        for (var i = 0; i < this.roll_num; i++) {
          roll_simulation[i] = Math.floor(Math.random() * this.roll_range) + 1;
        }
        return roll_simulation.join();
      } else {
        return null;
      }
    },
    ...mapState({
      forum_name: (state) =>
        state.Forums.CurrentForumData.name
          ? state.Forums.CurrentForumData.name
          : "",
      forum_id: (state) =>
        state.Forums.CurrentForumData.id ? state.Forums.CurrentForumData.id : 0,
      thread_title: (state) => state.Threads.CurrentThreadData.title,
      thread_sub_id: (state) => state.Threads.CurrentThreadData.sub_id,
      thread_heads_id: (state) =>
        state.Threads.CurrentThreadData.random_heads_group,
      thread_anti_jingfen: (state) =>
        state.Threads.CurrentThreadData.anti_jingfen,
      thread_posts_num: (state) => state.Threads.CurrentThreadData.posts_num,
      random_heads_group: (state) =>
        state.Threads.CurrentThreadData.random_heads_group,
      thread_last_page: (state) => state.Posts.PostsData.last_page,
      posts_data: (state) => state.Posts.PostsData.data, // 记得ThreadsData要比ForumsData多.data，因为多了分页数据
      posts_load_status: (state) => state.Posts.PostsLoadStatus,
      locked_TTL: (state) => state.User.LockedTTL,
    }),
  },
  methods: {
    get_posts_data(remind = false, scroll_enable = false) {
      const config = {
        method: "get",
        url: "/api/threads/" + this.thread_id,
        params: {
          page: this.page,
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("PostsData_set", response.data.posts_data);
            this.$store.commit(
              "CurrentThreadData_set",
              response.data.thread_data
            );
            this.$store.commit(
              "CurrentForumData_set",
              response.data.forum_data
            );
            this.$store.commit("PostsLoadStatus_set", 1);
            this.random_heads_data = JSON.parse(
              response.data.random_heads.random_heads
            );
            document.title = this.thread_title;
            if (remind) {
              this.$bvToast.toast("已刷新帖子", {
                title: "Done.",
                autoHideDelay: 1500,
                appendToast: true,
              });
            }
            this.$nextTick(() => {
              //渲染完成后执行
              //如果有设定上次阅读进度，则滚动到上次进度
              const page = isNaN(this.page) ? 1 : this.page;
              if (
                this.browse_current.page == page &&
                typeof this.$store.state.User.BrowseLogger[
                  this.thread_id.toString()
                ] != "undefined" &&
                scroll_enable
              ) {
                this.scroll_to_lasttime();
              }
              //如果地址有#hash，则滚动到对应hash
              if (this.$route.hash && scroll_enable) {
                document
                  .querySelector(this.$route.hash)
                  .scrollIntoView({ block: "start", behavior: "smooth" });
              }
            });
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码;
    },
    get_browse_current() {
      if (
        typeof this.$store.state.User.BrowseLogger[this.thread_id.toString()] !=
        "undefined"
      ) {
        this.browse_current = JSON.parse(
          JSON.stringify(
            this.$store.state.User.BrowseLogger[this.thread_id.toString()]
          )
        );
      }
      const page = isNaN(this.page) ? 1 : this.page;
      if (
        this.browse_current.page > page &&
        typeof this.$store.state.User.BrowseLogger[this.thread_id.toString()] !=
          "undefined"
      ) {
        this.jump_page_show = true; //显示阅读进度页面跳转提示
      } else {
        this.jump_page_show = false;
      }
    },
    thread_delete_click_admin() {
      var content = prompt(
        "要用管理员权限删除这个主题吗？请输入理由",
        "请输入理由"
      );
      if (content != null) {
        const config = {
          method: "post",
          url: "/api/admin/thread_delete/",
          data: {
            thread_id: this.thread_id,
            content: content,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              alert(response.data.message);
              this.get_posts_data();
            } else {
              alert(response.data.message);
            }
          })
          .catch((error) => alert(error));
      }
    },
    back_to_forum() {
      this.$router.push({ name: "forum", params: { forum_id: this.forum_id } });
    },
    new_post_handle() {
      this.new_post_handling = true;
      const config = {
        method: "post",
        url: "/api/posts/create",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          content: this.content_input,
          nickname: this.nickname_input,
          post_with_admin: this.post_with_admin,
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
            this.content_input = "";
            this.new_post_handling = false;
            this.get_posts_data();
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
    emoji_append(emoji_src) {
      this.content_input += "<img src='" + emoji_src + "' class='emoji_img'>";
      this.$refs.content_input.focus();
    },
    quote_click_handle(quote_content) {
      this.content_input = quote_content;
      document
        .querySelector("#content_input")
        .scrollIntoView({ block: "start", behavior: "smooth" });
      this.$refs.content_input.focus();
    },
    scroll_bottom() {
      window.scrollTo(0, document.documentElement.scrollHeight);
    },
    scroll_top() {
      window.scrollTo(0, 0);
    },
    roll_click() {
      this.$refs["roll_modal"].show();
    },
    jump_click() {
      this.$refs["jump_modal"].show();
    },
    roll_handle() {
      this.roll_handling = true;
      const config = {
        method: "post",
        url: "/api/posts/create_roll",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          roll_name: this.roll_name,
          roll_event: this.roll_event,
          roll_range: this.roll_range,
          roll_num: this.roll_num,
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
            this.content_input = "";
            this.roll_handling = false;
            this.$refs["roll_modal"].hide();
            this.get_posts_data();
          } else {
            this.roll_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(error);
          this.roll_handling = false;
        }); // Todo:写异常返回代码
    },
    jump_handle() {
      if (this.jump_floor == "") {
        alert("请输入目标楼");
      }
      const page = Math.ceil(this.jump_floor / 200);
      const link =
        "/thread/" + this.thread_id + "/" + page + "#f_" + this.jump_floor;
      this.$router.push(link);
      this.$refs["jump_modal"].hide();
    },
    thread_set_top() {
      var user_confirm = confirm("把这个主题置顶吗？");
      if (user_confirm == true) {
        const config = {
          method: "post",
          url: "/api/admin/thread_set_top/",
          data: {
            thread_id: this.thread_id,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              alert(response.data.message);
              this.get_posts_data();
            } else {
              alert(response.data.message);
            }
          })
          .catch((error) => alert(error));
      }
    },
    thread_cancel_top() {
      var user_confirm = confirm("把这个主题取消置顶吗？");
      if (user_confirm == true) {
        const config = {
          method: "post",
          url: "/api/admin/thread_cancel_top/",
          data: {
            thread_id: this.thread_id,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              alert(response.data.message);
              this.get_posts_data();
            } else {
              alert(response.data.message);
            }
          })
          .catch((error) => alert(error));
      }
    },
    scroll_to_lasttime() {
      if (!this.$route.hash) {
        //如果有to_hash，则停止使用上次阅读进度的滚动
        //不同浏览器可能支持不同，所以都用用
        document.body.scrollTop = this.browse_current.height;
        document.documentElement.scrollTop = this.browse_current.height;
        window.scrollTop = this.browse_current.height;
        this.$bvToast.toast("已滚动到上次阅读进度", {
          title: "Done.",
          autoHideDelay: 1500,
          appendToast: true,
        });
      }
    },
    scroll_watch() {
      const page = isNaN(this.page) ? 1 : this.page;
      if (this.browse_current.page <= page) {
        this.browse_current["height"] = document.documentElement.scrollTop;
      }
    },
    browse_record_handle() {
      //写入本次阅读进度
      const page = isNaN(this.page) ? 1 : this.page;
      if (this.browse_current.page <= page) {
        this.browse_current.page = page;
        this.$store.commit("BrowseLogger_set", {
          suffix: this.thread_id.toString(),
          browse_current: this.browse_current,
        });
        localStorage.browse_logger = JSON.stringify(
          this.$store.state.User.BrowseLogger
        );
      }
    },
  },
  created() {
    this.get_posts_data(false, true);
    this.$store.commit("PostsLoadStatus_set", 0); //避免显示上个ThreadsData
    if (localStorage.getItem("emoji_auto_hide") == null) {
      localStorage.emoji_auto_hide = "";
    } else {
      this.emoji_auto_hide = Boolean(localStorage.emoji_auto_hide);
    }
  },
  mounted() {
    this.get_browse_current();
    window.addEventListener("beforeunload", this.browse_record_handle);
    window.addEventListener("scroll", this.scroll_watch);
  },
  beforeDestroy() {
    this.browse_record_handle();
    window.removeEventListener("beforeunload", this.browse_record_handle);
    window.removeEventListener("scroll", this.scroll_watch);
  },
};
</script>

<style lang="scss" scoped>
.post_title {
  background-color: #dff0d8;
}
.post_container {
  background-color: #eefaee;
}
.forum_name {
  cursor: pointer;
}

#spinner {
  position: fixed;
  z-index: 999;
  width: 3rem;
  height: 3rem;
  right: 50%;
  top: 40%;
}

.z-sidebar {
  position: fixed;
  z-index: 999;
  right: 15px;
  top: 40%;
  color: #5fb878;
  width: 40px;
  div {
    width: 32px;
    height: 32px;
    position: relative;
  }
}
.z-sidebar > div + div {
  margin-top: 10px;
}

.z-sidebar > .icon-roll {
  cursor: pointer;
}
.z-sidebar > .icon-top,
.z-sidebar > .icon-down,
.z-sidebar > .icon-jump,
.z-sidebar .icon-reload {
  width: 24px;
  left: 4px;
  cursor: pointer;
}

.icon-back {
  cursor: pointer;
}
</style>