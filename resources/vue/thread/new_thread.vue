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
    <PostInput ref="post_input_com" :input_disable="!this.$store.state.User.LoginStatus || Boolean(locked_TTL) || new_thread_handling
      " :new_post_handling="new_thread_handling" :random_heads_group="random_heads_group_selected" :forum_id="forum_id"
      @content_commit="new_thread_handle" has_title has_delay>
    </PostInput>
    <div class="row align-items-center mt-2">
      <div class="col-auto ml-auto" style="font-size: 0.875rem">
        <span v-if="locked_TTL">
          你的饼干封禁中，将于{{ Math.floor(locked_TTL / 3600) + 1 }}小时后解封。
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
            <b-form-select v-model="subtitles_selected" :options="subtitles_options" value-field="value"
              text-field="value" :disabled="is_private_selected == true"></b-form-select>
          </div>
          <div class="col-4">
            <b-form-select v-model="nissin_time_selected" :options="nissin_time_options"
              :disabled="!(forum_nissin == 2)"></b-form-select>
          </div>
          <div class="col-4">
            <b-form-select v-model="anti_jingfen_selected" :options="anti_jingfen_options"></b-form-select>
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
            <b-form-select v-model="random_heads_group_selected" :options="random_heads_group" value-field="id"
              text-field="name"></b-form-select>
          </div>
          <div class="col-4">
            <b-form-select v-model="can_battle_selected" :options="can_battle_options"></b-form-select>
          </div>
        </div>
      </b-tab>
      <b-tab title="收费">
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <span class="h6 my-2">标题换色(500 olo)</span>
          </div>
          <div class="col-4">
            <span class="h6 my-2">设定看帖权限(500 olo)</span>
          </div>
          <div class="col-4">
            <span class="h6 my-2">设为私密主题(500 olo)</span>
          </div>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <b-form-input placeholder="#212529" v-model="title_color_input" class="common_input"></b-form-input>
          </div>
          <div class="col-4">
            <b-form-input placeholder="大于多少奥利奥才能看帖" v-model="locked_by_coin_input" class="common_input"></b-form-input>
          </div>
          <div class="col-4">
            <b-form-select v-model="is_private_selected" :options="is_private_options"></b-form-select>
          </div>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <ColorPicker v-model="title_color_input"></ColorPicker>
          </div>
        </div>
      </b-tab>
      <b-tab title="红包">
        <b-form-checkbox class="mr-3" v-model="thread_type" value="hongbao" unchecked-value="normal">
          开启红包
        </b-form-checkbox>
        <div v-show="thread_type == 'hongbao'" style="max-width: 400px" class="hongbao_input">
          <b-input-group prepend="红包个数" class="mt-2">
            <b-form-input v-model="hongbao_num" placeholder="红包个数"></b-form-input>
          </b-input-group>
          <b-input-group prepend="olo总数" class="mt-2">
            <b-form-input v-model="hongbao_olo" placeholder="olo总数"></b-form-input>
          </b-input-group>
          <b-input-group prepend="红包口令" class="mt-2">
            <b-form-input v-model="hongbao_key_word" placeholder="必填"></b-form-input>
          </b-input-group>
          <b-input-group prepend="红包类型" class="mt-2">
            <b-form-select v-model="hongbao_type" :options="hongbao_type_options" value-field="value"
              text-field="text"></b-form-select>
          </b-input-group>
          <b-input-group prepend="回复留言" class="mt-2" v-for="(message, index) in hongbao_message" :key="index">
            <b-form-input v-model="hongbao_message[index]" placeholder="可选（多个留言时随机回复一个）"></b-form-input>
          </b-input-group>
          <b-input-group prepend="留言数量" class="mt-2">
            <b-form-input v-model="hongbao_message_num" max="5" min="1" type="range"
              @change="hongbao_message_num_change"></b-form-input>
          </b-input-group>
          <b-form-checkbox v-model="hongbao_olo_hide" switch class="my-2">
            隐藏红包olo总额
          </b-form-checkbox>
          <p>
            <span v-if="!is_double11"> 友情提示：在打赏额以外，会追加扣除7%手续费。</span>
            <span v-else><del>友情提示：在打赏额以外，会追加扣除7%手续费。</del>
              <br />
              618限时活动手续费2%！
            </span>
            <br />
            总共扣除：
            <span style="color: red">{{ Math.ceil(hongbao_olo * (is_double11 ? 1.02 : 1.07)) }} </span>块奥利奥
            <span v-show="hongbao_type == 2"><br />
              <span>
                定额红包：每个<span style="color: red">{{ hongbao_quota }}</span>奥利奥</span></span>
          </p>
        </div>
      </b-tab>
      <b-tab title="投票">
        <b-form-checkbox class="mr-3" v-model="thread_type" value="vote" unchecked-value="normal">
          开启投票（1000奥利奥）
        </b-form-checkbox>
        <div class="row align-items-center my-2" v-show="thread_type == 'vote'">
          <div class="col-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="mr-3 svg-icon bi bi-plus-lg" viewBox="0 0 16 16" @click="vote_option_control('push')">
              <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="mr-3 svg-icon bi bi-dash-lg" viewBox="0 0 16 16" @click="vote_option_control('pop')">
              <path d="M0 8a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2H1a1 1 0 0 1-1-1z" />
            </svg>
          </div>
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_date_selected" size="sm" type="text" placeholder="结束日期"></b-form-input>
              <b-input-group-append>
                <b-form-datepicker v-model="end_date_selected" size="sm" placeholder="结束日期" locale="zh" button-only
                  today-button reset-button close-button :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }" :min="minDate" :max="maxDate" label-help="请选择投票结束的日期"></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_time_selected" size="sm" type="text" placeholder="结束时间"></b-form-input>
              <b-input-group-append>
                <b-form-timepicker v-model="end_time_selected" size="sm" locale="zh" reset-button button-only>
                </b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
        </div>
        <div v-show="thread_type == 'vote'">
          <div class="d-flex my-2">
            <b-form-checkbox class="mr-3" v-model="vote_multiple">
              投票多选
            </b-form-checkbox>
            <span class="ml-2" v-if="vote_multiple">最多可选：</span>
            <b-form-spinbutton size="sm" style="max-width: 120px" min="1" :max="vote_options.length"
              v-model="vote_max_choices" v-if="vote_multiple"></b-form-spinbutton>
            <span class="ml-1 mb-2" v-if="vote_multiple">个</span>
          </div>
          <div class="my-2" style="font-size: 0.875rem">投票标题</div>
          <b-form-input id="vote_title_input" class="vote_title_input" placeholder="投票标题，必填" v-model="vote_title_input">
          </b-form-input>
        </div>
        <div class="my-2" v-show="thread_type == 'vote'" v-for="(vote_option, index) in vote_options" :key="index">
          <div style="font-size: 0.875rem">选项{{ index + 1 }}</div>
          <b-form-input v-model="vote_options[index]"></b-form-input>
        </div>
      </b-tab>
      <b-tab title="菠菜">
        <b-form-checkbox class="mr-3" v-model="thread_type" :disabled="forum_id != 12" value="gamble"
          unchecked-value="normal">
          开启菠菜（500奥利奥） 目前只能在海滨乐园岛开菠菜（避免被日清）
        </b-form-checkbox>
        <div class="row align-items-center" v-show="thread_type == 'gamble'">
          <div class="col-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="mr-3 svg-icon bi bi-plus-lg" viewBox="0 0 16 16" @click="vote_option_control('push')">
              <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
              class="mr-3 svg-icon bi bi-dash-lg" viewBox="0 0 16 16" @click="vote_option_control('pop')">
              <path d="M0 8a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2H1a1 1 0 0 1-1-1z" />
            </svg>
          </div>
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_date_selected" size="sm" type="text" placeholder="结束日期"></b-form-input>
              <b-input-group-append>
                <b-form-datepicker v-model="end_date_selected" size="sm" placeholder="结束日期" locale="zh" button-only
                  today-button reset-button close-button :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }" :min="minDate" :max="maxDate" label-help="请选择投票结束的日期"></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_time_selected" size="sm" type="text" placeholder="结束时间"></b-form-input>
              <b-input-group-append>
                <b-form-timepicker v-model="end_time_selected" size="sm" locale="zh" reset-button button-only>
                </b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
        </div>

        <div v-show="thread_type == 'gamble'">
          <div class="my-2" style="font-size: 0.875rem">菠菜标题</div>
          <b-form-input id="vote_title_input" class="vote_title_input" placeholder="菠菜标题，必填" v-model="vote_title_input">
          </b-form-input>
        </div>
        <div v-show="thread_type == 'gamble'" class="my-2" style="font-size: 0.875rem">
          菠菜开奖时只能开奖单独一个选项
        </div>
        <div v-show="thread_type == 'gamble'" class="my-2" v-for="(vote_option, index) in vote_options" :key="index">
          <div style="font-size: 0.875rem">选项{{ index + 1 }}</div>
          <b-form-input v-model="vote_options[index]"></b-form-input>
        </div>
      </b-tab>
      <b-tab title="众筹" v-if="this.$store.state.User.AdminForums.includes(this.forum_id)">
        <b-form-checkbox class="mr-3" v-model="thread_type" :disabled="forum_id != 2" value="crowd"
          unchecked-value="normal">
          开启众筹（仅管理员可见） 目前只能在小火锅调味区开众筹（避免被日清）
        </b-form-checkbox>
        <div class="row align-items-center" v-show="thread_type == 'crowd'">
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_date_selected" size="sm" type="text" placeholder="结束日期"></b-form-input>
              <b-input-group-append>
                <b-form-datepicker v-model="end_date_selected" size="sm" placeholder="结束日期" locale="zh" button-only
                  today-button reset-button close-button :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }" :min="minDate" :max="maxDate" label-help="请选择投票结束的日期"></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
          <div class="col-auto my-2">
            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_time_selected" size="sm" type="text" placeholder="结束时间"></b-form-input>
              <b-input-group-append>
                <b-form-timepicker v-model="end_time_selected" size="sm" locale="zh" reset-button button-only>
                </b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>
        </div>

        <div v-show="thread_type == 'crowd'">
          <div class="my-2" style="font-size: 0.875rem">众筹项目</div>
          <b-form-input id="vote_title_input" class="vote_title_input" placeholder="众筹项目标题，必填"
            v-model="vote_title_input"></b-form-input>
        </div>
        <div v-show="thread_type == 'crowd'">
          <div class="my-2" style="font-size: 0.875rem">目标金额</div>
          <b-form-input id="vote_title_input" class="vote_title_input" placeholder="目标金额，最大1000万"
            v-model="crowd_olo_input"></b-form-input>
        </div>
      </b-tab>
      <b-tab title="管理员" v-if="this.$store.state.User.AdminForums.includes(this.forum_id)">
        <div class="row align-items-center mt-3">
          <div class="col-4"><span class="h6 my-2">管理员选项</span></div>
          <div class="col-4"></div>
          <div class="col-4"></div>
        </div>
        <div class="row align-items-center mt-3">
          <div class="col-4">
            <b-form-select v-model="admin_subtitles_selected" :options="admin_subtitles_options"
              :disabled="subtitles_selected != '[公告]'"></b-form-select>
          </div>
          <div class="col-4"></div>
          <div class="col-4"></div>
        </div>
      </b-tab>
    </b-tabs>
  </div>
</template>

<script>
import Emoji from "../component/emoji.vue";
import PostItem from "../component/post_item.vue";
import PostInput from "../component/post_input.vue";
import ColorPicker from "../component/color_picker.vue";

export default {
  name: "new_thread",
  components: { Emoji, PostItem, PostInput, ColorPicker },
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

      random_heads_group_selected: 1,
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
        { value: 1, text: "24小时" },
        { value: 2, text: "48小时" },
        { value: 3, text: "72小时" },
        { value: 7, text: "一周" },
        { value: 1095, text: "长期" },
      ],
      nissin_time_selected: 1,
      can_battle_selected: 1,
      can_battle_options: [
        { value: 1, text: "开启" },
        { value: 0, text: "禁止" },
      ],
      is_private_selected: false,
      is_private_options: [
        { value: false, text: "一般主题" },
        { value: true, text: "私密主题" },
      ],
      title_color_input: "",
      post_with_admin: false,
      locked_by_coin_input: undefined,
      upload_img_handling: false,
      preview_show: false,
      thread_type: "normal",
      vote_multiple: false,
      vote_title_input: "",
      vote_options: ["", "", ""],
      vote_max_choices: 1,
      crowd_olo_input: undefined,
      hongbao_olo: "",
      hongbao_num: undefined,
      hongbao_key_word: undefined,
      hongbao_message: [""],
      hongbao_message_num: 1,
      hongbao_type: 1,
      hongbao_type_options: [
        { value: 1, text: "随机红包" },
        { value: 2, text: "定额红包" },
      ],
      hongbao_olo_hide: false,
      end_time_selected: "00:00:00",
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
      localStorage.setItem("emoji_auto_hide", this.emoji_auto_hide ? "true" : "");
    },
    subtitles_selected() {
      if (this.subtitles_selected == "[专楼]") {
        this.can_battle_selected = 0;
      }
      if (this.subtitles_selected == "[闲聊]") {
        this.can_battle_selected = 1;
      }
    },
    is_private_selected() {
      if (this.is_private_selected == true) {
        this.subtitles_selected = "[私密]";
      } else {
        this.subtitles_selected = "[闲聊]";
      }
    },
  },
  computed: {
    nickname_input: {
      get() {
        return this.$store.state.User.NickName;
      },
      set(value) {
        this.$store.commit("NickName_set", value);
      },
    },
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
    random_heads_group() {
      this.random_heads_group_selected = this.forum_default_heads; //选择该板块的默认头像组
      return this.$store.state.User.RandomHeads.map((value, index) => ({
        id: index + 1,
        name: value.name,
      }));
    },
    post_content_css() {
      return {
        lineHeight: this.$store.state.MyCSS.PostsLineHeight + "px",
        fontSize: this.$store.state.MyCSS.PostsFontSize + "px",
      };
    },
    subtitles_options() {
      let options = subtitles[0].subtitles; //subtitles来源于json.js全局变量
      if (this.$store.state.User.AdminForums.includes(this.forum_id)) {
        options = subtitles[1].subtitles.concat(options); //加上管理员选项
      }
      return options;
    },
    hongbao_quota() {
      if (this.hongbao_num) {
        return Math.ceil(this.hongbao_olo / this.hongbao_num);
      } else {
        return 0;
      }
    },
    is_double11() {
      const double11 = new Date("2023-06-18");
      const now = new Date(Date.now());
      return now.toLocaleDateString() === double11.toLocaleDateString();
    },
  },
  methods: {
    back_to_forum() {
      this.$router.push({ name: "forum", params: { forum_id: this.forum_id } });
    },
    new_thread_handle(content) {
      this.new_thread_handling = true;
      var config = {
        method: "post",
        url: "/api/threads/create",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,

          //来自PostInput组件
          title: content.title_input,
          content: content.content_input,
          nickname: content.nickname_input,
          post_with_admin: content.post_with_admin,
          is_delay: content.is_delay,

          subtitle: this.subtitles_selected,
          random_heads_group: this.random_heads_group_selected,
          nissin_time: this.nissin_time_selected,
          title_color: this.title_color_input,
          anti_jingfen: this.anti_jingfen_selected,
          admin_subtitle: this.admin_subtitles_selected,
          locked_by_coin: this.locked_by_coin_input,
          thread_type: this.thread_type,
          is_private: this.is_private_selected,
          can_battle: this.can_battle_selected,
        },
      };
      if (this.thread_type == "hongbao") {
        if (this.hongbao_type == 2 && this.hongbao_olo % this.hongbao_num != 0) {
          alert("选择定额红包时，总额要是红包数量的整倍数喔");
          return;
        }
        //如果是红包贴，追加众筹相关的请求参数
        config.data.hongbao_olo = this.hongbao_olo;
        config.data.hongbao_num = this.hongbao_num;
        config.data.type = this.hongbao_type;
        config.data.hongbao_key_word = this.hongbao_key_word;
        config.data.hongbao_olo_hide = this.hongbao_olo_hide;

        if (this.hongbao_message.length == 1) {
          //单个回复时，以json格式提交到hongbao_message
          config.data.hongbao_message = this.hongbao_message[0]
        } else {
          //多个回复时，以json格式提交到hongbao_message_json
          config.data.hongbao_message_json = JSON.stringify(this.hongbao_message)
        }
      }
      if (this.thread_type == "vote") {
        //如果是投票贴，追加投票相关的请求参数
        if (!this.end_date_selected || !this.end_time_selected) {
          this.new_thread_handling = false;
          alert("请设定结束的日期和时间");
          return;
        }

        config.data.vote_multiple = this.vote_multiple;
        config.data.vote_title = this.vote_title_input;
        config.data.vote_options = JSON.stringify(this.vote_options);
        config.data.vote_end_time = this.end_date_selected + " " + this.end_time_selected;
        config.data.vote_max_choices = this.vote_max_choices;
      }
      if (this.thread_type == "gamble") {
        //如果是菠菜贴，追加菠菜相关的请求参数
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
      if (this.thread_type == "crowd") {
        //如果是众筹贴，追加众筹相关的请求参数
        if (!this.end_date_selected || !this.end_time_selected) {
          this.new_thread_handling = false;
          alert("请设定结束的日期和时间");
          return;
        }
        config.data.crowd_title = this.vote_title_input;
        config.data.crowd_end_time =
          this.end_date_selected + " " + this.end_time_selected;
        config.data.crowd_olo_target = this.crowd_olo_input;
      }
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.new_thread_handling = false;
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
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
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
    emoji_append(emoji_src, is_my_emoji) {
      let textarea = document.getElementById("content_input");
      var class_name = is_my_emoji ? "custom_emoji_img" : "emoji_img";
      this.content_input = this.insertAtCursor(
        textarea,
        `<img src='${emoji_src}' class='${class_name}'>`
      );
      this.content_input_change();
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
    // get_subtitles() {
    //   const config = {
    //     method: "get",
    //     url: "/api/subtitles",
    //     data: {},
    //   };
    //   axios(config)
    //     .then((response) => {
    //       this.subtitles_options = response.data.data;
    //       // 如果不是管理员，就删除部分管理员专用选项
    //       if (!this.$store.state.User.AdminForums.includes(this.forum_id)) {
    //         this.subtitles_options.forEach((subtitles_option, index) => {
    //           if (subtitles_option.for_admin == 1) {
    //             this.subtitles_options.splice(index, 1);
    //           }
    //         });
    //       }
    //     })
    //     .catch((error) => {
    //       alert(error);
    //     }); // Todo:写异常返回代码;
    // },
    // set_subtitles() {
    //   this.subtitles_options = subtitles[0].subtitles;
    // },
    upload_img_handle(file, mode) {
      if (!file) return;
      this.upload_img_handling = true;
      const formData = new FormData();
      formData.append("file", file);
      formData.append("mode", mode);
      formData.append("binggan", this.$store.state.User.Binggan);
      formData.append("thread_id", 0); //正常上传要提供thread_id，新主题要传入0
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
    hongbao_message_num_change(value) {
      const diff = this.hongbao_message.length - value
      if (diff == 0) {
        return
      }
      if (diff >= 1) {
        for (var i = 0; i < diff; i++) {
          this.hongbao_message.pop();
        }
      }
      if (diff <= -1) {
        for (var i = 0; i < -diff; i++) {
          this.hongbao_message.push("");
        }
      }
    },
  },
  created() {
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
  activated() {
    //清空输入框，避免显示上一次的内容
    this.$refs.post_input_com.content_input = "";
    this.$refs.post_input_com.title_input = "";
    this.random_heads_group_selected = 1;
    this.subtitles_selected = "[闲聊]";
    this.admin_subtitles_selected = 1;
    this.anti_jingfen_selected = false;
    this.nissin_time_selected = 1;
    this.can_battle_selected = 1;
    this.is_private_selected = false;
    this.title_color_input = "";
    this.post_with_admin = false;
    this.locked_by_coin_input = undefined;
    this.preview_show = false;
    this.thread_type = "normal";
    this.vote_multiple = false;
    this.vote_title_input = "";
    this.vote_options = ["", "", ""];
    this.crowd_olo_input = undefined;
    this.hongbao_olo = "";
    this.hongbao_num = undefined;
    this.hongbao_key_word = undefined;
    this.hongbao_message = [""];
    this.hongbao_type = 1;
    this.end_time_selected = "00:00:00";
    this.end_date_selected = undefined;
  },
};
</script>
