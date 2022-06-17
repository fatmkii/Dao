<template>
  <div>
    <div>
      <div
        class="post_title px-1 py-2 h6 d-block d-lg-none d-xl-none"
        v-if="preview_show && has_title"
      >
        <span style="word-wrap: break-word; white-space: normal"
          >标题：{{ title_input }}</span
        >
      </div>
      <div
        class="post_title px-1 py-2 h5 d-none d-lg-block d-xl-block"
        v-if="preview_show && has_title"
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
          :no_emoji_mode="false"
        ></PostItem>
      </div>
      <slot name="after_preview"></slot>
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
      <b-form-input
        id="nickname_input"
        v-model="nickname_input"
        class="nickname_input"
      ></b-form-input>
      <div v-if="has_title" class="my-2" style="font-size: 0.875rem">标题</div>
      <b-form-input
        id="title_input"
        v-if="has_title"
        class="title_input"
        placeholder="标题，必填"
        v-model="title_input"
      ></b-form-input>
      <Emoji
        :heads_id="random_heads_group"
        :emoji_auto_hide="emoji_auto_hide"
        @emoji_append="emoji_append"
      ></Emoji>
      <div class="my-2 row align-items-center" style="font-size: 0.875rem">
        <div class="col-auto pr-0">内容</div>
        <div class="col-auto d-inline-flex">
          <b-form-checkbox class="ml-2" v-model="preview_show" switch>
            实时预览
          </b-form-checkbox>
        </div>
        <div class="col-auto ml-auto">
          <slot name="svg_icon"></slot>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            class="icon-drawer bi bi-pencil ml-3"
            viewBox="0 0 16 16"
            @click="modal_toggle('drawer_modal')"
          >
            <path
              d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"
            />
          </svg>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="currentColor"
            class="icon-revoke bi bi-reply-all ml-3"
            viewBox="0 0 16 16"
            v-b-popover.hover.left="'撤销'"
            @click="content_input_revoke"
          >
            <path
              d="M8.098 5.013a.144.144 0 0 1 .202.134V6.3a.5.5 0 0 0 .5.5c.667 0 2.013.005 3.3.822.984.624 1.99 1.76 2.595 3.876-1.02-.983-2.185-1.516-3.205-1.799a8.74 8.74 0 0 0-1.921-.306 7.404 7.404 0 0 0-.798.008h-.013l-.005.001h-.001L8.8 9.9l-.05-.498a.5.5 0 0 0-.45.498v1.153c0 .108-.11.176-.202.134L4.114 8.254a.502.502 0 0 0-.042-.028.147.147 0 0 1 0-.252.497.497 0 0 0 .042-.028l3.984-2.933zM9.3 10.386c.068 0 .143.003.223.006.434.02 1.034.086 1.7.271 1.326.368 2.896 1.202 3.94 3.08a.5.5 0 0 0 .933-.305c-.464-3.71-1.886-5.662-3.46-6.66-1.245-.79-2.527-.942-3.336-.971v-.66a1.144 1.144 0 0 0-1.767-.96l-3.994 2.94a1.147 1.147 0 0 0 0 1.946l3.994 2.94a1.144 1.144 0 0 0 1.767-.96v-.667z"
            />
            <path
              d="M5.232 4.293a.5.5 0 0 0-.7-.106L.54 7.127a1.147 1.147 0 0 0 0 1.946l3.994 2.94a.5.5 0 1 0 .593-.805L1.114 8.254a.503.503 0 0 0-.042-.028.147.147 0 0 1 0-.252.5.5 0 0 0 .042-.028l4.012-2.954a.5.5 0 0 0 .106-.699z"
            />
          </svg>
        </div>
      </div>
      <textarea
        id="content_input"
        class="content_input form-control"
        @change="content_input_change"
        v-model="content_input"
        :rows="content_input_rows"
        ref="content_input"
        :disabled="input_disable"
        @keyup.ctrl.enter="commit"
        :style="post_content_css"
      ></textarea>
      <Imgtu v-if="this.forum_id !== 419 && this.forum_id !== 0"></Imgtu>
      <div class="d-flex align-items-center mt-2">
        <div v-if="this.forum_id === 419" class="d-flex align-items-center">
          <b-form-file
            browse-text="上传图片"
            size="sm"
            placeholder="未选择"
            accept="image/jpeg, image/png, image/gif"
            style="max-width: 300px"
            :disabled="!this.$store.state.User.LoginStatus"
            @input="upload_img_handle($event, 'img')"
          ></b-form-file>
          <b-spinner
            class="spinner img-uploading ml-2"
            v-show="upload_img_handling"
            label="上传中"
          >
          </b-spinner>
        </div>
        <div class="ml-auto d-flex align-items-center">
          <b-form-checkbox
            v-if="has_delay"
            v-model="is_delay"
            v-b-popover.hover.bottom="'自动在第二天8点发出'"
          >
            延时发送
          </b-form-checkbox>
          <b-button
            variant="success"
            class="ml-2"
            :disabled="input_disable"
            v-b-popover.hover.left="'可以Ctrl+Enter喔'"
            @click="commit"
            >{{ new_post_handling ? "提交中" : "提交" }}
          </b-button>
        </div>
      </div>
    </div>

    <div>
      <b-modal
        ref="drawer_modal"
        id="drawer_modal"
        class="drawer_modal"
        centered
      >
        <template v-slot:modal-header>
          <span style="font-size: 1rem">涂鸦板</span>
        </template>
        <template v-slot:default>
          <Drawer
            @upload_emit="upload_img_handle"
            @drawer_click="modal_toggle('drawer_modal')"
            ref="drawer_component"
          ></Drawer>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-form-file
            browse-text="插入"
            size="sm"
            placeholder=""
            accept="image/jpeg, image/png, image/gif"
            style="max-width: 45px"
            class="mr-auto"
            @input="drawer_insert"
          ></b-form-file>
          <b-button-group>
            <b-button variant="success" size="sm" @click="upload_drawer_click"
              >上传</b-button
            >
            <b-button variant="outline-secondary" size="sm" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
    </div>
  </div>
</template>

<script>
import PostItem from "./post_item.vue";
import Emoji from "./emoji.vue";
import Drawer from "./drawer.vue";
import Imgtu from "../imgtu.vue";

export default {
  components: {
    PostItem,
    Emoji,
    Drawer,
    Imgtu,
  },
  props: {
    input_disable: Boolean, //可否输入（正在处理提交时设false）
    new_post_handling: Boolean, //正在提交的状态
    thread_id: {
      type: Number,
      default: 0,
    },
    forum_id: {
      type: Number,
      default: 0,
    },
    random_heads_group: {
      //随机头像组编号
      type: Number,
      default: 1,
    },
    has_title: {
      //是否可以输入标题
      type: Boolean,
      default: false,
    },
    has_delay: {
      //是否可以延迟发帖
      type: Boolean,
      default: false,
    },
  },
  watch: {
    post_with_admin() {
      this.nickname_input = this.post_with_admin ? "管理员" : "= =";
    },
    emoji_auto_hide() {
      localStorage.setItem(
        "emoji_auto_hide",
        this.emoji_auto_hide ? "true" : ""
      );
    },
  },
  data: function () {
    return {
      emoji_auto_hide: true,
      preview_show: false,
      content_input_array: [""],
      content_input: "",
      post_with_admin: false,
      upload_img_handling: false,
      title_input: "",
      is_delay: false,
    };
  },
  computed: {
    post_content_css() {
      return {
        lineHeight: this.$store.state.MyCSS.PostsLineHeight + "px",
        fontSize: this.$store.state.MyCSS.PostsFontSize + "px",
      };
    },
    nickname_input: {
      get() {
        return this.$store.state.User.NickName;
      },
      set(value) {
        this.$store.commit("NickName_set", value);
      },
    },
    preview_post_data() {
      return {
        content: this.content_input,
        created_at: "预览中……",
        floor: 0,
        is_deleted: 0,
        is_your_post: false,
        nickname: this.nickname_input,
      };
    },
    content_input_rows() {
      const lines = (this.content_input.match(/\n/g) || "").length + 2;
      if (lines < 5) {
        return 5;
      } else if (lines > 10) {
        return 10;
      } else {
        return lines;
      }
    },
  },
  methods: {
    commit() {
      this.$emit("content_commit", {
        content_input: this.content_input,
        nickname_input: this.nickname_input,
        post_with_admin: this.post_with_admin,
        title_input: this.title_input,
        is_delay: this.is_delay,
      });
    },
    emoji_append(emoji_src) {
      let textarea = document.getElementById("content_input");
      this.content_input = this.insertAtCursor(
        textarea,
        "<img src='" + emoji_src + "' class='emoji_img'>"
      );
      this.content_input_change();
      this.$refs.content_input.focus();
    },
    modal_toggle(modal_name) {
      this.$refs[modal_name].toggle();
    },
    content_input_revoke() {
      if (this.content_input_array.length > 1) {
        this.content_input_array.shift();
        this.content_input = this.content_input_array[0];
      }
    },
    content_input_change() {
      this.content_input_array.unshift(this.content_input);
      if (this.content_input_array.length > 20) {
        this.content_input_array.pop();
      }
    },
    quote_click_handle(quote_content) {
      this.content_input = quote_content;
      this.content_input_change();
      document
        .querySelector("#content_input")
        .scrollIntoView({ block: "start", behavior: "auto" });
      this.$refs.content_input.focus();
    },
    insertAtCursor(f, value) {
      /* eslint-disable */
      let field = f;
      let newValue = "";
      // IE support
      if (document.selection) {
        field.focus();
        const sel = document.selection.createRange();
        sel.text = newValue = value;
        sel.select();
      } else if (field.selectionStart || field.selectionStart === 0) {
        const startPos = field.selectionStart;
        const endPos = field.selectionEnd;
        const restoreTop = field.scrollTop;
        newValue =
          field.value.substring(0, startPos) +
          value +
          field.value.substring(endPos, field.value.length);
        if (restoreTop > 0) {
          field.scrollTop = restoreTop;
        }
        field.focus();
        setTimeout(() => {
          field.selectionStart = startPos + value.length;
          field.selectionEnd = startPos + value.length;
        }, 0);
      } else {
        newValue = field.value + value;
        field.focus();
      }
      return newValue;
    },
    upload_img_handle(file, mode) {
      if (!file) return;
      this.upload_img_handling = true;
      const formData = new FormData();
      formData.append("file", file);
      formData.append("mode", mode);
      formData.append("binggan", this.$store.state.User.Binggan);
      formData.append("thread_id", this.thread_id); //正常上传要提供thread_id，新主题要传入0
      formData.append("forum_id", this.forum_id);
      const config = {
        method: "post",
        url: "/api/img_upload",
        headers: {
          "content-type": "multipart/form-data",
        },
        data: formData,
      };
      axios(config)
        .then((response) => {
          this.upload_img_handling = false;
          this.content_input += "<img src='" + response.data.file_url + "' >";
          this.content_input_change();
        })
        .catch((error) => {
          this.upload_img_handling = false;
          alert(error);
        });
    },
    upload_drawer_click() {
      this.$refs.drawer_component.upload();
    },
    drawer_insert(file) {
      this.$refs.drawer_component.drawer_insert(file);
    },
    load_LocalStorage() {
      var localStorage_array = new Array("emoji_auto_hide");
      //遍历读取上述LocalStorage
      for (var i = 0; i < localStorage_array.length; i++) {
        if (localStorage.getItem(localStorage_array[i]) == null) {
          localStorage[localStorage_array[i]] = "";
        } else {
          this[localStorage_array[i]] = Boolean(
            localStorage[localStorage_array[i]]
          );
        }
      }
    },
  },
  created() {
    this.load_LocalStorage();
  },
};
</script>
