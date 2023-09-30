<template>
  <div class="post_item my-2" :id="'f_' + post_data.floor" v-if="!(!this.post_content_show && this.fold_pingbici)">
    <slot name="header"></slot>
    <div class="float-right post_buttons" v-if="this.$store.state.User.LoginStatus">
      <b-button size="sm" variant="warning"
        v-if="this.$store.state.User.AdminForums.includes(this.forum_id) && admin_status >= 20" v-show="admin_button_show"
        @click="check_post_click_admin">
        提示
      </b-button>
      <b-button size="sm" variant="warning" v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show" @click="ban_cookie_click_admin">
        碎饼
      </b-button>
      <b-button size="sm" variant="warning" v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show" @click="lock_cookie_click_admin">
        封禁
      </b-button>
      <b-button size="sm" variant="warning" v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show" @click="post_delete_all_click_admin">
        删全
      </b-button>
      <b-button size="sm" variant="warning" v-if="this.$store.state.User.AdminForums.includes(this.forum_id) &&
        post_data.is_deleted == 0
        " v-show="admin_button_show" @click="post_delete_click_admin">
        删帖
      </b-button>
      <b-button size="sm" variant="warning" v-if="this.$store.state.User.AdminForums.includes(this.forum_id) &&
        post_data.is_deleted != 0
        " v-show="admin_button_show" @click="post_delete_recover_click_admin">
        恢复
      </b-button>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
        class="svg-light bi bi-trash ml-1" viewBox="0 0 16 16" v-if="post_data.is_your_post && post_data.is_deleted == 0"
        @click="post_delete_click">
        <!-- 删除按钮 -->
        <path
          d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
        <path fill-rule="evenodd"
          d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
        class="svg-light bi bi-recycle ml-1" viewBox="0 0 16 16"
        v-if="post_data.is_your_post && post_data.is_deleted == 1" @click="post_delete_recover_click">
        <!-- 删除恢复按钮 -->
        <path
          d="M9.302 1.256a1.5 1.5 0 0 0-2.604 0l-1.704 2.98a.5.5 0 0 0 .869.497l1.703-2.981a.5.5 0 0 1 .868 0l2.54 4.444-1.256-.337a.5.5 0 1 0-.26.966l2.415.647a.5.5 0 0 0 .613-.353l.647-2.415a.5.5 0 1 0-.966-.259l-.333 1.242-2.532-4.431zM2.973 7.773l-1.255.337a.5.5 0 1 1-.26-.966l2.416-.647a.5.5 0 0 1 .612.353l.647 2.415a.5.5 0 0 1-.966.259l-.333-1.242-2.545 4.454a.5.5 0 0 0 .434.748H5a.5.5 0 0 1 0 1H1.723A1.5 1.5 0 0 1 .421 12.24l2.552-4.467zm10.89 1.463a.5.5 0 1 0-.868.496l1.716 3.004a.5.5 0 0 1-.434.748h-5.57l.647-.646a.5.5 0 1 0-.708-.707l-1.5 1.5a.498.498 0 0 0 0 .707l1.5 1.5a.5.5 0 1 0 .708-.707l-.647-.647h5.57a1.5 1.5 0 0 0 1.302-2.244l-1.716-3.004z" />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
        class="svg-success bi bi-chat-square-dots-fill ml-1" viewBox="0 0 16 16" v-if="post_data.is_deleted == 0"
        @click="quote_click">
        <!-- 回复按钮 -->
        <path
          d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.5a1 1 0 0 0-.8.4l-1.9 2.533a1 1 0 0 1-1.6 0L5.3 12.4a1 1 0 0 0-.8-.4H2a2 2 0 0 1-2-2V2zm5 4a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
        class="svg-success bi bi-gift-fill ml-1" viewBox="0 0 16 16"
        v-if="!post_data.is_your_post && post_data.is_deleted == 0" @click="emit_reward">
        <!-- 打赏按钮 -->
        <path
          d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506V2.5zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43a.522.522 0 0 0 .023.07zM9 3h2.932a.56.56 0 0 0 .023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0V3zm6 4v7.5a1.5 1.5 0 0 1-1.5 1.5H9V7h6zM2.5 16A1.5 1.5 0 0 1 1 14.5V7h6v9H2.5z" />
      </svg>
    </div>
    <div class="d-flex align-items-center">
      <b-img :src="random_head_add" :class="'head_' + post_data.random_head" v-if="!no_head_mode"></b-img>
    </div>
    <span v-show="fold_content && post_content_show" @click="unfold_content"
      style="position: relative; cursor: pointer">*点击展开*</span>
    <div v-if="post_content_should_hiden">
      <span style="cursor: pointer" v-if="post_content_show"
        @click="post_content_show = !post_content_show">*手贱了，收回去*</span>
      <span style="cursor: pointer" v-else @click="post_content_show = !post_content_show">*此回帖已屏蔽{{ hide_reason
      }}，点击展开*</span>
    </div>
    <div class="post_content mb-2" style="overflow: hidden" :style="post_content_css" ref="post_content">
      <span v-html="post_content" v-if="post_content_show" class="post_span" style="
          word-wrap: break-word;
          white-space: pre-wrap;
          overflow: hidden;
          position: relative;
        " :style="post_span_css"></span>
    </div>
    <slot name="battle"></slot>
    <slot name="hongbao"></slot>
    <div class="post_footer" ref="post_author_info" :style="post_footer_css">
      <span class="post_footer_text" @click="quote_click">№{{ post_data.floor }} ☆☆☆</span>
      <span class="post_nick_name align-items-center" :class="author_class">
        {{ post_data.nickname }} 🇨🇳
      </span>
      <span class="post_footer_text">于</span>
      <span class="post_created_at">{{ post_data.created_at }}</span>
      <span class="post_footer_text"> 留言 ☆☆☆</span>

      <span v-if="thread_anti_jingfen" class="post_anti_jingfen">
        →{{ post_data.created_binggan_hash.slice(0, 5) }}
      </span>
    </div>
    <!-- <span v-if="this.$store.state.User.AdminStatus == 99" v-show="admin_button_show" class="post_anti_jingfen">
      →{{ post_data.created_binggan }}
    </span> -->
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  props: {
    post_data: {
      type: Object,
      default: {},
    },
    thread_anti_jingfen: {
      type: Number,
      default: 0,
    },
    random_head_add: {
      type: String,
      default: "",
    },
    admin_button_show: {
      type: Boolean,
      default: false,
    },
    no_image_mode: {
      type: Boolean,
      default: false,
    },
    no_emoji_mode: {
      type: Boolean,
      default: false,
    },
    no_custom_emoji_mode: {
      type: Boolean,
      default: false,
    },
    no_head_mode: {
      type: Boolean,
      default: false,
    },
    no_video_mode: {
      type: Boolean,
      default: false,
    },
  },
  data: function () {
    return {
      name: "post_item",
      content_reward_input: "",
      coin_reward_input: "",
      reward_handling: false,
      post_content_show: true,
      post_content_should_hiden: false,
      post_max_height: "",
      post_top_offset: "",
      fold_content: false,
      hide_reason: "", //屏蔽词原因
    };
  },
  watch: {
    use_pingbici() {
      this.pingbici_check();
    },
  },
  computed: {
    author_class() {
      return {
        created_by_admin1: this.post_data.created_by_admin == 1,
        created_by_admin2: this.post_data.created_by_admin == 2,
      };
    },
    post_content() {
      // const img_svg =
      //   '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-image img_svg" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/></svg>';
      let vm = this; //为了回调函数可以使用vue的data
      function img_replacer(match) {
        //用于屏蔽表情包或者其他图片的回调函数
        if (match.search(/class='emoji_img'/g) != -1) {
          //判断是否表情包
          if (vm.no_emoji_mode) {
            //no_emoji_mode:无一般表情包模式
            return "";
          } else {
            return match;
          }
        } else if (match.search(/class='custom_emoji_img'/g) != -1) {
          //判断是否表情包
          if (vm.no_custom_emoji_mode) {
            //no_custom_emoji_mode:无自定义表情包模式
            return "";
          } else {
            return match;
          }
        } else {
          if (vm.no_image_mode) {
            //no_image_mode:无图模式
            return match
              .replace(/src/, "data-src")
              .replace("<img ", '<img src="/img_svg.svg" class="img_svg"');
          } else {
            return match;
          }
        }
      }
      return this.post_data.content
        .replace(/<img[^>]*>/g, img_replacer)
        .replace(/<script/g, "<**禁止使用script**")
        .replace(/\n/g, "<br>");
    },
    post_content_css() {
      if (this.post_max_height == 0) {
        var maxHeight = "none";
      } else {
        var maxHeight = this.post_max_height + "px";
      }
      return {
        lineHeight: this.$store.state.MyCSS.PostsLineHeight + "px",
        fontSize: this.$store.state.MyCSS.PostsFontSize + "px",
        marginTop: this.$store.state.MyCSS.PostsMarginTop + "px",
        maxHeight: maxHeight,
      };
    },
    post_footer_css() {
      return {
        fontSize: this.$store.state.MyCSS.SysInfoFontSize + "px",
      };
    },
    post_span_css() {
      return {
        top: this.post_top_offset + "px",
      };
    },
    regexp_mode() {
      return this.pingbici_ignore_case ? "gi" : "g";
    },
    ...mapState({
      forum_id: (state) => state.Forums.CurrentForumData.id,
      use_pingbici: (state) => state.User.UsePingbici,
      fold_pingbici: (state) => state.User.FoldPingbici,
      pingbici_ignore_case: (state) => state.User.PingbiciIngnoreCase,
      quote_max: (state) => state.MyCSS.QuoteMax,
      thread_id: (state) =>
        state.Threads.CurrentThreadData.id ? state.Threads.CurrentThreadData.id : 0,
      admin_status: (state) => state.User.AdminStatus,
    }),
  },
  created() {
    this.pingbici_check();
    let vm = this; //为了回调函数可以使用vue的方法
    this.$eventHub.$on("pingbici_refresh", () => {
      vm.pingbici_check();
    });
  },
  activated() {
    this.pingbici_check();
  },
  mounted() {
    this.fold_img();
    this.$nextTick(() => {
      this.set_quote_styles(); // 给回帖内容的引用部分单独加style
      if (this.post_data.floor != 0) {
        //但是0楼不折叠
        this.set_post_max_height(); //确认post总行数，如果超过特定行数，则折叠（包括图片等高度）
      }
    });
  },
  methods: {
    emit_reward() {
      const payload = {
        floor: this.post_data.floor,
        thread_id: this.thread_id,
        post_id: this.post_data.id,
        post_floor_message: this.$refs.post_author_info.innerText,
      };
      this.$emit("emit_reward", payload);
    },
    post_delete_click() {
      var isdelete = confirm("要删除这个回复吗？（消费300奥利奥）");
      var confirmed = true;
      if (this.post_data.hongbao_id != null) {
        confirmed = confirm(
          "这个回帖有红包。删除后红包将消失，并且olo不退回。是否确认？"
        );
      }
      if (isdelete == true && confirmed == true) {
        const config = {
          method: "delete",
          url: "/api/posts/" + this.post_data.id,
          data: {
            binggan: this.$store.state.User.Binggan,
            thread_id: this.thread_id,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert("帖子删除成功");
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    post_delete_recover_click() {
      var confirmed = confirm("要恢复这个已删除的回复吗？（消费300奥利奥）");
      if (confirmed == true) {
        const config = {
          method: "PUT",
          url: "/api/posts/recover/" + this.post_data.id,
          data: {
            binggan: this.$store.state.User.Binggan,
            thread_id: this.thread_id,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert("帖子恢复成功");
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    post_delete_click_admin() {
      var content = prompt("要用管理员权限删除这个回复吗？请输入理由");
      if (content == null) {
        return;
      }
      var confirmed = true;
      if (this.post_data.hongbao_id != null) {
        confirmed = confirm(
          "这个回帖有红包。删除后红包将消失，并且olo不退回。是否确认？"
        );
      }
      var reduce_olo = confirm("是否需要对该饼干罚款500个olo？");
      if (content != null && confirmed != false) {
        const config = {
          method: "delete",
          url: "/api/admin/post_delete/" + this.post_data.id,
          data: {
            thread_id: this.thread_id,
            content: content,
            reduce_olo: reduce_olo,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    post_delete_recover_click_admin() {
      var content = prompt("要用管理员权限恢复这个回复吗？请输入理由");
      if (content != null) {
        const config = {
          method: "put",
          url: "/api/admin/post_recover/" + this.post_data.id,
          data: {
            thread_id: this.thread_id,
            content: content,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    post_delete_all_click_admin() {
      var content = prompt(
        "要用管理员权限删除该饼干全部回复吗？请输入理由：\n（一般只有违规刷屏时候才会用）"
      );
      if (content == null) {
        return;
      }
      var reduce_olo = confirm(
        "是否需要对该饼干进行罚款？\n每个帖子罚款500，封顶5000个olo"
      );
      if (content != null) {
        const config = {
          method: "post",
          url: "/api/admin/post_delete_all/",
          data: {
            post_id: this.post_data.id,
            thread_id: this.thread_id,
            content: content,
            reduce_olo: reduce_olo,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    ban_cookie_click_admin() {
      var content = prompt("你要永久粉碎这个饼干吗？请输入理由");
      if (content != null) {
        const config = {
          method: "post",
          url: "/api/admin/user_ban/",
          data: {
            post_id: this.post_data.id,
            thread_id: this.thread_id,
            content: content,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    lock_cookie_click_admin() {
      var content = prompt(
        "你要封禁这个饼干吗？请输入理由：\n（第1次封3天、2次6天、3次9天、4次碎饼）"
      );
      if (content != null) {
        const config = {
          method: "post",
          url: "/api/admin/user_lock/",
          data: {
            post_id: this.post_data.id,
            thread_id: this.thread_id,
            content: content,
          },
        };
        axios(config).then((response) => {
          if (response.data.code == 200) {
            alert(response.data.message);
            this.$emit("get_posts_data");
          } else {
            alert(response.data.message);
          }
        });
        // .catch((error) => alert(error));
      }
    },
    check_post_click_admin() {
      const config = {
        method: "post",
        url: "/api/admin/check_post/",
        data: {
          post_id: this.post_data.id,
          thread_id: this.thread_id,
        },
      };
      axios(config).then((response) => {
        if (response.data.code == 200) {
          alert(`饼干状态：${response.data.user_status}。已被锁定过${response.data.locked_count}次。`);
        } else {
          alert(response.data.message);
        }
      });
      // .catch((error) => alert(error));

    },

    quote_click() {
      const max_quote = this.quote_max; //最大可引用的层数
      var post_content_dom = this.$refs.post_content;
      // 折叠details标签的内容避免被引用;
      var elements_details = post_content_dom.getElementsByTagName("details");
      for (let dom of elements_details) {
        dom.open = false;
      }

      var post_lines = post_content_dom.innerText.split("\n");
      var index_array = [];
      //搜索引用的层数
      post_lines.forEach((post_line, index) => {
        if (post_line.indexOf("留言 ☆☆☆") > -1) {
          index_array.push(index);
        }
      });
      //如果层数过多，只截取部分回复引用
      if (index_array.length >= max_quote) {
        post_lines = post_lines.slice(
          index_array[index_array.length - max_quote] + 1,
          post_lines.length
        );
      }
      const quote_content =
        "<span class='quote_content'>" +
        post_lines.join("\n") +
        "\n" +
        this.$refs.post_author_info.innerText +
        "</span>" +
        "\n";
      return this.$emit("quote_click", quote_content);
    },
    pingbici_check() {
      this.post_content_show = true;
      this.post_content_should_hiden = false;
      if (this.$store.state.User.UsePingbici) {
        //处理内容屏蔽词
        const content_pingbici = this.$store.state.User.ContentPingbici;
        for (var i = 0; i < content_pingbici.length; i++) {
          var reg = new RegExp(content_pingbici[i], this.regexp_mode);
          if (reg.test(this.post_data.content)) {
            this.post_content_show = false; //回帖是否显示的开关
            this.post_content_should_hiden = true;
            this.hide_reason = "（屏蔽词）";
          }
        }
        //处理fjf屏蔽词
        if (this.thread_anti_jingfen && this.$store.state.User.FjfPingbici !== null) {
          const fjf_pingbici = this.$store.state.User.FjfPingbici;
          for (var i = 0; i < fjf_pingbici.length; i++) {
            var reg = new RegExp(fjf_pingbici[i], "g");
            if (reg.test(this.post_data.created_binggan_hash.slice(0, 5))) {
              this.post_content_show = false; //回帖是否显示的开关
              this.post_content_should_hiden = true;
              this.hide_reason = "（小尾巴黑名单）";
            }
          }
        }
      }
      //处理无视频音频模式
      if (this.no_video_mode) {
        var reg = new RegExp(/<video|<audio|<embed|<iframe/, "g");
        if (reg.test(this.post_data.content)) {
          this.post_content_show = false; //回帖是否显示的开关
          this.post_content_should_hiden = true;
          this.hide_reason = "（音视频）";
        }
      }
    },
    unfold_content() {
      this.fold_content = false;
      this.post_top_offset = 0;
      this.post_max_height = 0;
    },
    fold_img() {
      const img_doms = this.$refs.post_content.getElementsByClassName("img_svg");
      for (let dom of img_doms) {
        dom.addEventListener("click", (event) => {
          const src_old = event.target.getAttribute("src");
          event.target.setAttribute("src", "");
          event.target.setAttribute("src", event.target.getAttribute("data-src"));
          event.target.setAttribute("data-src", src_old);
        });
      }
    },
    set_post_max_height() {
      //确认post总行数，如果超过特定行数，则折叠（包括图片等高度）
      const post_content_dom = this.$refs.post_content;
      const styles = window.getComputedStyle(post_content_dom);
      const line_height = parseInt(styles.lineHeight, 10);
      const height = parseInt(styles.height, 10);
      const max_height = this.$store.state.MyCSS.PostsMaxLine * line_height;
      if (height > max_height) {
        this.fold_content = true;
        this.post_top_offset = max_height - height;
        this.post_max_height = max_height;
      }
    },
    set_quote_styles() {
      // 给回帖内容的引用部分单独加style
      var quote_dom = this.$refs.post_content.querySelector(".quote_content");
      if (quote_dom) {
        quote_dom.style.fontSize = this.$store.state.MyCSS.QuoteFontSize + "px";
      }
    },
  },
};
</script>
