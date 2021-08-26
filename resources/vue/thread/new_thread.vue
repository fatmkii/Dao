
<template>
  <div>
    <div class="row align-items-center mt-3">
      <div class="col-auto">
        <b-badge variant="secondary" pill class="float-left">
          {{ forum_id }}
        </b-badge>
        <span id="forum_name" @click="back_to_forum">{{ forum_name }}</span>
      </div>
      <div class="col-auto">
        <span>发表新话题</span>
      </div>
    </div>
    <div
      class="post_title px-1 py-2 h5 d-none d-lg-block d-xl-block"
      v-if="preview_show"
    >
      <span style="word-wrap: break-word; white-space: normal"
        >标题：{{ title_input }}</span
      >
    </div>
    <div
      class="post_title px-1 py-2 h6 d-block d-lg-none d-xl-none"
      v-if="preview_show"
    >
      <span style="word-wrap: break-word; white-space: normal"
        >标题：{{ title_input }}</span
      >
    </div>
    <div>
      <PostItem
        v-if="preview_show"
        :post_data="preview_post_data"
        :thread_anti_jingfen="0"
        :admin_button_show="false"
        :no_image_mode="false"
      ></PostItem>
    </div>
    <div class="my-2 row d-inline-flex" style="font-size: 0.875rem">
      <div class="col-auto pr-0">昵称</div>
      <div class="col-auto d-inline-flex">
        <b-form-checkbox
          class="mr-auto ml-2"
          v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
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
    <b-form-input
      id="nickname_input"
      v-model="nickname_input"
      class="nickname_input"
    ></b-form-input>
    <div class="my-2" style="font-size: 0.875rem">标题</div>
    <b-form-input
      id="title_input"
      class="title_input"
      placeholder="标题，必填"
      v-model="title_input"
    ></b-form-input>
    <Emoji
      :heads_id="random_heads_group_selected"
      :emoji_auto_hide="emoji_auto_hide"
      @emoji_append="emoji_append"
    ></Emoji>
    <div class="my-2 row d-inline-flex" style="font-size: 0.875rem">
      <div class="col-auto pr-0">内容</div>
      <div class="col-auto d-inline-flex">
        <b-form-checkbox class="mr-auto ml-2" v-model="preview_show" switch>
          实时预览
        </b-form-checkbox>
      </div>
    </div>
    <textarea
      id="content_input"
      class="content_input form-control"
      v-model="content_input"
      :rows="content_input_rows"
      ref="content_input"
      :disabled="new_thread_handling || Boolean(locked_TTL)"
      @keyup.ctrl.enter="new_thread_handle"
    ></textarea>
    <div class="row align-items-center mt-2">
      <div class="col-7">
        <b-form-file
          browse-text="上传图片"
          size="sm"
          placeholder="未选择"
          accept="image/jpeg, image/png, image/gif"
          style="max-width: 300px"
          :disabled="!this.$store.state.User.LoginStatus"
          @input="upload_img_handle"
        ></b-form-file>
        <b-spinner
          class="spinner img-uploading"
          v-show="upload_img_handling"
          label="上传中"
        >
        </b-spinner>
      </div>
      <div class="col-5">
        <b-button
          variant="success"
          class="float-right"
          :disabled="new_thread_handling || Boolean(locked_TTL)"
          @click="new_thread_handle"
          >{{ new_thread_handling ? "提交中" : "发表" }}
        </b-button>
      </div>
    </div>
    <div class="row align-items-center mt-2">
      <div class="col-auto ml-auto" style="font-size: 0.875rem">
        <span v-if="locked_TTL">
          你的饼干封禁中，将于{{
            Math.floor(locked_TTL / 3600) + 1
          }}小时后解封。
        </span>
      </div>
    </div>
    <hr />
    <div class="row align-items-center mt-3">
      <div class="col-4"><span class="h6 my-2">副标题</span></div>
      <div class="col-4">
        <span class="h6 my-2">日清时间</span>
        <span v-if="forum_nissin == 0">（本小岛不日清）</span>
        <span v-if="forum_nissin == 1">（本小岛固定8点日清）</span>
      </div>
      <div class="col-4"><span class="h6 my-2">反精分</span></div>
    </div>
    <div class="row align-items-center mt-3">
      <div class="col-4">
        <b-form-select
          v-model="subtitles_selected"
          :options="subtitles_options"
        ></b-form-select>
      </div>
      <div class="col-4">
        <b-form-select
          v-model="nissin_time_selected"
          :options="nissin_time_options"
          :disabled="!(forum_nissin == 2)"
        ></b-form-select>
      </div>
      <div class="col-4">
        <b-form-select
          v-model="anti_jingfen_selected"
          :options="anti_jingfen_options"
        ></b-form-select>
      </div>
    </div>
    <hr />
    <div class="row align-items-center mt-3">
      <div class="col-4">
        <span class="h6 my-2">选择随机头像组</span>
      </div>
      <div class="col-4">
        <span class="h6 my-2">给标题换个颜色吗？（收费500奥利奥）</span>
      </div>
      <div class="col-4">
        <span class="h6 my-2">设定看帖权限（收费500奥利奥）</span>
      </div>
    </div>
    <div class="row align-items-center mt-3">
      <div class="col-4">
        <b-form-select
          v-model="random_heads_group_selected"
          :options="random_heads_group"
          value-field="id"
          text-field="name"
        ></b-form-select>
      </div>
      <div class="col-4">
        <b-form-input
          placeholder="#212529"
          v-model="title_color_input"
          class="common_input"
        ></b-form-input>
      </div>
      <div class="col-4">
        <b-form-input
          placeholder="大于多少奥利奥才能看帖"
          v-model="locked_by_coin_input"
          class="common_input"
        ></b-form-input>
      </div>
    </div>
    <div v-if="this.$store.state.User.AdminForums.includes(this.forum_id)">
      <hr />
      <div class="row align-items-center mt-3">
        <div class="col-4"><span class="h6 my-2">管理员选项</span></div>
        <div class="col-4"></div>
        <div class="col-4"></div>
      </div>
      <div class="row align-items-center mt-3">
        <div class="col-4">
          <b-form-select
            v-model="admin_subtitles_selected"
            :options="admin_subtitles_options"
            :disabled="subtitles_selected != '[公告]'"
          ></b-form-select>
        </div>
        <div class="col-4"></div>
        <div class="col-4"></div>
      </div>
    </div>
  </div>
</template>


<script>
import Emoji from "./emoji.vue";
import PostItem from "./post_item.vue";

export default {
  components: { Emoji, PostItem },
  props: {
    forum_id: Number, //来自router
  },
  data: function () {
    return {
      name: "new_thread",
      new_thread_handling: false,
      nickname_input: "= =",
      content_input: "",
      title_input: "",
      random_heads_group_selected: 1,
      random_heads_group: [],
      subtitles_options: [],
      subtitles_selected: "[闲聊]",
      admin_subtitles_options: [
        { value: 1, text: "本版公告" },
        { value: 0, text: "全岛公告" },
      ],
      admin_subtitles_selected: 1,
      anti_jingfen_options: [
        { value: false, text: "" },
        { value: true, text: "设为反精分贴" },
      ],
      anti_jingfen_selected: false,
      nissin_time_options: [
        { value: 86400, text: "24小时" },
        { value: 172800, text: "48小时" },
        { value: 259200, text: "72小时" },
      ],
      nissin_time_selected: 86400,
      title_color_input: "",
      post_with_admin: false,
      locked_by_coin_input: undefined,
      upload_img_handling: false,
      preview_show: false,
    };
  },
  watch: {
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
  computed: {
    forum_name() {
      var forum_data = this.$store.getters.ForumData(this.forum_id);
      if (forum_data) {
        return forum_data.name;
      }
    },
    forum_nissin() {
      var forum_data = this.$store.getters.ForumData(this.forum_id);
      if (forum_data) {
        return forum_data.is_nissin;
      }
    },
    forum_default_heads() {
      var forum_data = this.$store.getters.ForumData(this.forum_id);
      if (forum_data) {
        return forum_data.default_heads;
      } else {
        return 1;
      }
    },
    locked_TTL() {
      return this.$store.state.User.LockedTTL;
    },
    content_input_rows() {
      const lines = (this.content_input.match(/\n/g) || "").length + 3;
      if (lines < 5) {
        return 5;
      } else if (lines > 12) {
        return 12;
      } else {
        return lines;
      }
    },
    preview_post_data() {
      return {
        content: this.content_input,
        created_at: "预览中……",
        floor: 0,
        is_deleted: 0,
        is_your_post: false,
        nickname: this.nickname_input,
        random_head: null,
      };
    },
  },

  methods: {
    back_to_forum() {
      this.$router.push({ name: "forum", params: { forum_id: this.forum_id } });
    },
    new_thread_handle() {
      this.new_thread_handling = true;
      const config = {
        method: "post",
        url: "/api/threads/create",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          title: this.title_input,
          content: this.content_input,
          nickname: this.nickname_input,
          subtitle: this.subtitles_selected,
          random_heads_group: this.random_heads_group_selected,
          nissin_time: this.nissin_time_selected,
          title_color: this.title_color_input,
          anti_jingfen: this.anti_jingfen_selected,
          admin_subtitle: this.admin_subtitles_selected,
          post_with_admin: this.post_with_admin,
          locked_by_coin: this.locked_by_coin_input,
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
            setTimeout(() => {
              this.back_to_forum();
            }, 1500);
          } else {
            this.new_thread_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.new_thread_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    emoji_append(emoji_src) {
      this.content_input += "<img src='" + emoji_src + "' class='emoji_img'>";
      this.$refs.content_input.focus();
    },
    get_subtitles() {
      const config = {
        method: "get",
        url: "/api/subtitles",
        data: {},
      };
      axios(config)
        .then((response) => {
          this.subtitles_options = response.data.data;
          // 如果不是管理员，就删除部分管理员专用选项
          if (!this.$store.state.User.AdminForums.includes(this.forum_id)) {
            this.subtitles_options.forEach((subtitles_option, index) => {
              if (subtitles_option.for_admin == 1) {
                this.subtitles_options.splice(index, 1);
              }
            });
          }
        })
        .catch((error) => {
          alert(error);
        }); // Todo:写异常返回代码;
    },
    get_random_heads_index() {
      const config = {
        method: "get",
        url: "/api/random_heads",
        data: {},
      };
      axios(config)
        .then((response) => {
          this.random_heads_group = response.data.data;
          this.random_heads_group_selected = this.forum_default_heads;
        })
        .catch((error) => {
          alert(error);
        }); // Todo:写异常返回代码;
    },
    upload_img_handle(file) {
      if (!file) return;
      this.upload_img_handling = true;
      const formData = new FormData();
      formData.append("file", file);
      formData.append(
        "Token",
        "ofdns1jjalu6efhl4ahlgmqack2lm3ll:J7Io2WOTXnIUZ2G0ibCcNsJ1BsY=:eyJkZWFkbGluZSI6MTYyOTIxMjIyOSwiYWN0aW9uIjoiZ2V0IiwidWlkIjoxMDM2OCwiYWlkIjoiMTEwMDQiLCJmcm9tIjoiZmlsZSJ9"
      );
      delete axios.defaults.headers.Authorization; //正常用的transFormRequest会影响data，只能把Authorization删了再加回去
      const config = {
        method: "post",
        url: "https://up.tietuku.cn/",
        headers: {
          "content-type": "multipart/form-data",
        },
        data: formData,
      };
      axios(config)
        .then((response) => {
          this.upload_img_handling = false;
          this.content_input +=
            "<img src='" +
            response.data.linkurl.replace(/http/g, "https") +
            "' >";
          axios.defaults.headers.Authorization = "Bearer " + localStorage.Token;
        })
        .catch((error) => {
          this.upload_img_handling = false;
          axios.defaults.headers.Authorization = "Bearer " + localStorage.Token;
          alert(error);
        });
    },
  },
  created() {
    this.get_subtitles();
    this.get_random_heads_index();
    if (localStorage.getItem("emoji_auto_hide") == null) {
      localStorage.emoji_auto_hide = "";
    } else {
      this.emoji_auto_hide = Boolean(localStorage.emoji_auto_hide);
    }
  },
};
</script>