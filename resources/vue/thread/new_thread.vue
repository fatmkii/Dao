
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
        :no_emoji_mode="false"
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
    <div class="my-2 row align-items-center" style="font-size: 0.875rem">
      <div class="col-auto pr-0">内容</div>
      <div class="col-auto d-inline-flex">
        <b-form-checkbox class="mr-auto ml-2" v-model="preview_show" switch>
          实时预览
        </b-form-checkbox>
      </div>
      <div class="col-auto ml-auto">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="currentColor"
          class="icon-revoke bi bi-reply-all"
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
      v-model="content_input"
      @change="content_input_change"
      :rows="content_input_rows"
      ref="content_input"
      :disabled="new_thread_handling || Boolean(locked_TTL)"
      @keyup.ctrl.enter="new_thread_handle"
    ></textarea>
    <div class="d-flex align-items-center mt-2">
      <!-- <div class="col-7">
          原自费图床tietuku暂时关闭。
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
      </div> -->
      <Imgtu></Imgtu>
      <b-form-checkbox
        class="mx-2 ml-auto"
        v-model="is_delay"
        v-b-popover.hover.bottom="'自动在第二天8点发出'"
      >
        延时发送
      </b-form-checkbox>
      <b-button
        variant="success"
        class="float-right"
        :disabled="new_thread_handling || Boolean(locked_TTL)"
        @click="new_thread_handle"
        >{{ new_thread_handling ? "提交中" : "发表" }}
      </b-button>
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
    <b-tabs pills>
      <b-tab title="常规">
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
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <span class="h6 my-2">选择随机头像组</span>
          </div>
          <div class="col-4">
            <span class="h6 my-2">启用大乱斗</span>
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
            <b-form-select
              v-model="can_battle_selected"
              :options="can_battle_options"
            ></b-form-select>
          </div>
        </div>
      </b-tab>
      <b-tab title="收费">
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <span class="h6 my-2">给标题换个颜色吗？（500奥利奥）</span>
          </div>
          <div class="col-4">
            <span class="h6 my-2">设定看帖权限（500奥利奥）</span>
          </div>
        </div>
        <div class="row align-items-center mt-3">
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
      </b-tab>
      <b-tab title="投票">
        <b-form-checkbox class="mr-3" v-model="is_vote">
          开启投票（1000奥利奥）
        </b-form-checkbox>
        <div class="row align-items-center my-2">
          <div class="col-auto">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="mr-3 svg-icon bi bi-plus-lg"
              viewBox="0 0 16 16"
              v-show="is_vote"
              @click="vote_option_control('push')"
            >
              <path
                d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"
              />
            </svg>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="mr-3 svg-icon bi bi-dash-lg"
              viewBox="0 0 16 16"
              v-show="is_vote"
              @click="vote_option_control('pop')"
            >
              <path d="M0 8a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2H1a1 1 0 0 1-1-1z" />
            </svg>
          </div>
          <div class="col-auto my-2">
            <b-form-checkbox
              class="mr-3"
              v-show="is_vote"
              v-model="vote_multiple"
              v-b-popover.hover.right="'未启用'"
              disabled
            >
              投票多选
            </b-form-checkbox>
          </div>
          <div class="col-auto my-2" v-show="is_vote">
            <b-input-group style="max-width: 160px">
              <b-form-input
                v-model="end_date_selected"
                size="sm"
                type="text"
                placeholder="结束日期"
              ></b-form-input>
              <b-input-group-append>
                <b-form-datepicker
                  v-model="end_date_selected"
                  size="sm"
                  placeholder="结束日期"
                  locale="zh"
                  button-only
                  today-button
                  reset-button
                  close-button
                  :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }"
                  :min="minDate"
                  :max="maxDate"
                  label-help="请选择投票结束的日期"
                ></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>
          </div>

          <div class="col-auto my-2" v-show="is_vote">
            <b-input-group style="max-width: 160px">
              <b-form-input
                v-model="end_time_selected"
                size="sm"
                type="text"
                placeholder="结束时间"
              ></b-form-input>
              <b-input-group-append>
                <b-form-timepicker
                  v-model="end_time_selected"
                  size="sm"
                  locale="zh"
                  reset-button
                  button-only
                ></b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
        </div>

        <div v-show="is_vote">
          <div class="my-2" style="font-size: 0.875rem">投票标题</div>
          <b-form-input
            id="vote_title_input"
            class="vote_title_input"
            placeholder="投票标题，必填"
            v-model="vote_title_input"
          ></b-form-input>
        </div>
        <div
          class="my-2"
          v-show="is_vote"
          v-for="(vote_option, index) in vote_options"
          :key="index"
        >
          <div style="font-size: 0.875rem">选项{{ index + 1 }}</div>
          <b-form-input v-model="vote_options[index]"></b-form-input>
        </div>
      </b-tab>
      <b-tab title="菠菜">
        <b-form-checkbox
          class="mr-3"
          v-model="is_gamble"
          :disabled="forum_id != 12"
        >
          开启菠菜（500奥利奥） 目前只能在海滨乐园岛开菠菜（避免被日清）
        </b-form-checkbox>
        <div class="row align-items-center">
          <div class="col-auto">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="mr-3 svg-icon bi bi-plus-lg"
              viewBox="0 0 16 16"
              v-show="is_gamble"
              @click="vote_option_control('push')"
            >
              <path
                d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"
              />
            </svg>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="mr-3 svg-icon bi bi-dash-lg"
              viewBox="0 0 16 16"
              v-show="is_gamble"
              @click="vote_option_control('pop')"
            >
              <path d="M0 8a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2H1a1 1 0 0 1-1-1z" />
            </svg>
          </div>
          <div class="col-auto my-2" v-show="is_gamble">
            <b-input-group style="max-width: 160px">
              <b-form-input
                v-model="end_date_selected"
                size="sm"
                type="text"
                placeholder="结束日期"
              ></b-form-input>
              <b-input-group-append>
                <b-form-datepicker
                  v-model="end_date_selected"
                  size="sm"
                  placeholder="结束日期"
                  locale="zh"
                  button-only
                  today-button
                  reset-button
                  close-button
                  :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }"
                  :min="minDate"
                  :max="maxDate"
                  label-help="请选择投票结束的日期"
                ></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>
          </div>

          <div class="col-auto my-2" v-show="is_gamble">
            <b-input-group style="max-width: 160px">
              <b-form-input
                v-model="end_time_selected"
                size="sm"
                type="text"
                placeholder="结束时间"
              ></b-form-input>
              <b-input-group-append>
                <b-form-timepicker
                  v-model="end_time_selected"
                  size="sm"
                  locale="zh"
                  reset-button
                  button-only
                ></b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
        </div>

        <div v-show="is_gamble">
          <div class="my-2" style="font-size: 0.875rem">菠菜标题</div>
          <b-form-input
            id="vote_title_input"
            class="vote_title_input"
            placeholder="菠菜标题，必填"
            v-model="vote_title_input"
          ></b-form-input>
        </div>
        <div
          class="my-2"
          v-show="is_gamble"
          v-for="(vote_option, index) in vote_options"
          :key="index"
        >
          <div style="font-size: 0.875rem">选项{{ index + 1 }}</div>
          <b-form-input v-model="vote_options[index]"></b-form-input>
        </div>
      </b-tab>
      <b-tab
        title="管理员"
        v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
      >
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
      </b-tab>
    </b-tabs>
  </div>
</template>


<script>
import Emoji from "./emoji.vue";
import PostItem from "./post_item.vue";
import Imgtu from "../imgtu.vue";

export default {
  components: { Emoji, PostItem, Imgtu },
  props: {
    forum_id: Number, //来自router
  },
  data: function () {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const minDate = new Date(today);
    const maxDate = new Date(today);
    maxDate.setMonth(maxDate.getMonth() + 1);

    return {
      name: "new_thread",
      new_thread_handling: false,
      nickname_input: "= =",
      content_input_array: [""],
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
      can_battle_selected: 1,
      can_battle_options: [
        { value: 1, text: "开启" },
        { value: 0, text: "禁止" },
      ],
      nissin_time_selected: 86400,
      title_color_input: "",
      post_with_admin: false,
      locked_by_coin_input: undefined,
      upload_img_handling: false,
      preview_show: false,
      is_vote: false,
      is_gamble: false,
      is_delay: false,
      vote_multiple: false,
      vote_title_input: "",
      vote_options: ["", "", ""],
      end_time_selected: undefined,
      end_date_selected: undefined,
      minDate: minDate,
      maxDate: maxDate,
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
    is_vote() {
      if (this.is_vote) {
        this.is_gamble = false;
      }
    },
    is_gamble() {
      if (this.is_gamble) {
        this.is_vote = false;
      }
    },
    subtitles_selected() {
      if (this.subtitles_selected == "[专楼]") {
        this.can_battle_selected = 0;
      }
      if (this.subtitles_selected == "[闲聊]") {
        this.can_battle_selected = 1;
      }
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
      var config = {
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
          is_vote: this.is_vote,
          is_gamble: this.is_gamble,
          is_delay: this.is_delay,
          can_battle: this.can_battle_selected,
        },
      };
      if (this.is_vote) {
        //如果是投票贴，追加投票相关的请求参数
        if (!this.end_date_selected || !this.end_time_selected) {
          this.new_thread_handling = false;
          alert("请设定结束的日期和时间");
          return;
        }

        config.data.vote_multiple = this.vote_multiple;
        config.data.vote_title = this.vote_title_input;
        config.data.vote_options = JSON.stringify(this.vote_options);
        config.data.vote_end_time =
          this.end_date_selected + " " + this.end_time_selected;
      }
      if (this.is_gamble) {
        //如果是菠菜贴，追加投票相关的请求参数
        if (!this.end_date_selected || !this.end_time_selected) {
          this.new_thread_handling = false;
          alert("请设定结束的日期和时间");
          return;
        }
        config.data.gamble_title = this.vote_title_input;
        config.data.gamble_options = JSON.stringify(this.vote_options);
        config.data.gamble_end_time =
          this.end_date_selected + " " + this.end_time_selected;
      }
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
    content_input_change() {
      this.content_input_array.unshift(this.content_input);
      if (this.content_input_array.length > 20) {
        this.content_input_array.pop();
      }
    },
    content_input_revoke() {
      if (this.content_input_array.length > 1) {
        this.content_input_array.shift();
        this.content_input = this.content_input_array[0];
      }
    },
    emoji_append(emoji_src) {
      this.content_input += "<img src='" + emoji_src + "' class='emoji_img'>";
      this.content_input_change();
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
          this.content_input_change();
          axios.defaults.headers.Authorization = "Bearer " + localStorage.Token;
        })
        .catch((error) => {
          this.upload_img_handling = false;
          axios.defaults.headers.Authorization = "Bearer " + localStorage.Token;
          alert(error);
        });
    },
    vote_option_control(action) {
      const max_options = 30; //最大投票数
      switch (action) {
        case "push": {
          if (this.vote_options.length < max_options) {
            this.vote_options.push("");
          } else {
            alert("最大选项数：" + max_options);
          }
          break;
        }
        case "pop": {
          if (this.vote_options.length > 1) {
            this.vote_options.pop();
          }
          break;
        }
      }
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
  mounted() {
    var now = new Date();
    var dateTime = new Date(now.setDate(now.getDate() + 1)); //默认日期是今天+1天
    var year = dateTime.getFullYear();
    var month = dateTime.getMonth() + 1;
    if (month >= 1 && month <= 9) {
      month = "0" + month;
    }
    var strDate = dateTime.getDate();
    if (strDate >= 0 && strDate <= 9) {
      strDate = "0" + strDate;
    }
    this.end_date_selected = year + "-" + month + "-" + strDate;
  },
};
</script>