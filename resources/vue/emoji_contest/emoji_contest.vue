<template>
  <div>
    <div v-if="login_status">
      <b-img fluid-grow src="https://i.mji.rip/2023/11/08/6ef6ac935961751d3cf158a43d5b4b80.jpeg"></b-img>
      <div class="my-2">
        <span>{{ banner_text }}</span>
      </div>
      <b-tabs pills vertical nav-wrapper-class="50px" :small="is_mobile">
        <b-tab v-for=" (group_data, index) in moe_group_index" :key="index" :title="group_data.name" lazy
          @click="get_emoji_votes_data(group_data.group_id)" class="d-flex flex-wrap">
          <div class="w-100 d-flex align-items-center">
            <span class="emoji_vote_text">{{ group_data.name }}总票数：{{ get_votes_num_total(group_data.group_id) }}</span>
            <b-button class="ml-auto" :variant="button_theme" :size="is_mobile ? 'sm' : 'md'"
              @click="get_emoji_votes_data(group_data.group_id, true)">刷新</b-button>
          </div>
          <div class="emoji_moe_box d-flex flex-column align-items-center m-1"
            v-for="group_vote_data in emoji_votes_data[group_data.group_id]" @click="emoji_click(group_vote_data.emoji_id,
              moe_url(group_vote_data.emoji_id),
              group_vote_data.votes_num_total,
              group_data.group_id)">
            <b-img class="emoji_moe_img" :src="moe_url(group_vote_data.emoji_id)" fluid alt="emoji"></b-img>
            <span class="emoji_vote_text">{{ group_vote_data.votes_num_total ? group_vote_data.votes_num_total : ''
            }}</span>
          </div>
        </b-tab>
        <b-tab title="我的本命" @click="get_user_votes_data" lazy>
          <div v-if="get_user_vote_handling == 2 && user_votes_data.length == 0">自己的投票总额会显示在这里</div>
          <div class="d-flex flex-wrap">
            <div class="emoji_moe_box d-flex flex-column align-items-center m-1"
              v-for="(user_vote_data, index) in user_votes_data">
              <b-img class="emoji_moe_img" :src="moe_group_url(user_vote_data.emoji_group_id)" fluid alt="emoji"></b-img>
              <span class="emoji_vote_text" :style="index == 0 ? 'width:100px;' : ''"> {{ index == 0 ? '⭐' : '' }}
                {{ user_vote_data.votes_num_total ? user_vote_data.votes_num_total : '' }}</span>
            </div>
          </div>
          <div class="mt-2 emoji_vote_text" v-if="get_user_vote_handling == 2">自己票数最多的为“⭐我的本命”，<br>如该角色夺冠将可获得成就哦！</div>
        </b-tab>
      </b-tabs>
    </div>

    <b-modal ref="emoji_modal" id="emoji_modal" hide-header>
      <template v-slot:default>
        <div class="d-flex flex-column align-items-center ">
          <b-img style="width: 100px;" :src="emoji_url_in_modal" fluid alt="emoji"></b-img>
          <span class="mt-2">当前票数：{{ emoji_votes_in_modal }}</span>
          <b-input-group prepend="投票额" class="mt-2">
            <b-form-input type="number" v-model="vote_num" placeholder="100个olo＝1票"></b-form-input>
          </b-input-group>
        </div>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <span class="mr-2">{{ modal_text_bottom }}</span>
        <b-button :variant="button_theme" :disabled="set_data_handling || !can_vote"
          @click="user_vote_handle">投票！</b-button>
        <b-button variant="outline-secondary" @click="cancel()">
          关闭
        </b-button>
      </template>
    </b-modal>

    <img src="https://oss.cpttmm.com/xhg_other/notice_4.png" v-if="!login_status" class="nissined_img" />
  </div>
</template>


<script>
import { mapState } from "vuex";
import moe_json from "moe_json";//来自wepack.mix.js的别名alias

export default {
  name: "emoji_contest",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      set_data_handling: false,
      get_data_handling: 0,
      get_user_vote_handling: 0,
      emoji_votes_data: [],
      emoji_group_id_selected: 0,
      emoji_id_selected: 0,
      user_votes_data: [],
      vote_num: undefined,
      emoji_url_in_modal: '',
      emoji_votes_in_modal: 0,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    moe_group_index() {
      return moe_json.moe_group_index //来自浏览器引入的emoji_moe.js
    },
    before_start() {
      const start = new Date("2023-11-11 20:00:00");
      const now = new Date(Date.now());
      return now < start
    },
    after_end() {
      const end = new Date("2023-11-14 20:00:00");
      const now = new Date(Date.now());
      return now > end
    },
    can_vote() {
      return !this.after_end && !this.before_start
    },
    modal_text_bottom() {
      if (this.can_vote) {
        return `消费olo：${this.vote_num ? this.vote_num * 100 : 0}个`
      } else if (this.before_start) {
        return '投票尚未开始哦'
      } else if (this.after_end) {
        return '本次活动已经结束'
      }
    },
    banner_text() {
      if (this.can_vote) {
        return `将于6月25日20：00结束`
      } else if (this.before_start) {
        return '6月18日20：00 投票正式开始！'
      } else if (this.after_end) {
        return ''
      }
    },
    ...mapState({
      login_status: (state) => state.User.LoginStatus
    }),
  },
  created() {
    this.get_emoji_votes_data(1)
  },
  methods: {
    get_emoji_votes_data(emoji_group_id, remind = false) {
      this.get_data_handling = 1;
      this.emoji_id_selected = emoji_group_id;
      const config = {
        method: "get",
        url: "/api/emoji_contest/" + emoji_group_id,
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            // this.emoji_votes_data[emoji_group_id] = response.data.data;
            this.$set(this.emoji_votes_data, emoji_group_id, response.data.data);//用vue的$set赋值数组元素，保持响应式
            if (remind) {
              this.$bvToast.toast("已刷新数据", {
                title: "Done.",
                autoHideDelay: 1500,
                appendToast: true,
              });
            }
            this.get_data_handling = 2;
          } else {
            this.get_data_handling = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_data_handling = 0;
        });
    },
    get_user_votes_data() {
      this.get_user_vote_handling = 1;
      const config = {
        method: "post",
        url: "/api/emoji_contest/show_user_votes",
        data: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.user_votes_data = response.data.data;
            this.get_user_vote_handling = 2;
          } else {
            this.get_user_vote_handling = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_user_vote_handling = 0;
        });
    },
    user_vote_handle() {
      const confirmed = confirm(`确定要投票给${moe_json.moe_group_index[this.emoji_group_id_selected - 1].name}角色${this.vote_num}票吗`)
      if (!confirmed) {
        return
      }

      this.set_data_handling = true;
      const config = {
        method: "post",
        url: "/api/emoji_contest/user_vote",
        data: {
          binggan: this.$store.state.User.Binggan,
          emoji_group_id: this.emoji_group_id_selected,
          emoji_id: this.emoji_id_selected,
          olo: this.vote_num * 100
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
            this.vote_num = undefined
            this.set_data_handling = false;
            this.$refs['emoji_modal'].hide();
            this.get_emoji_votes_data(this.emoji_group_id_selected)
          } else {
            this.set_data_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.set_data_handling = false;
        });
    },
    emoji_click(emoji_id, url, votes_num, group_id) {
      this.emoji_id_selected = emoji_id
      this.emoji_group_id_selected = group_id
      this.emoji_url_in_modal = url
      this.emoji_votes_in_modal = votes_num
      this.$refs['emoji_modal'].show();
    },
    get_votes_num_total(group_id) {
      if (this.emoji_votes_data[group_id]) {
        var votes_num_total = 0
        this.emoji_votes_data[group_id].forEach(element => {
          votes_num_total += element.votes_num_total
        });
        return votes_num_total
      } else {
        return '读取中……'
      }
    },
    moe_url(emoji_id) {
      //因为moe_url是数组，emoji_id从1计算，这里要-1
      return moe_json.moe_url[emoji_id - 1] //来自浏览器引入的emoji_moe.js
    },
    moe_group_url(emoji_group_id) {
      //因为moe_url是数组，emoji_id从1计算，这里要-1
      return moe_json.moe_group_url[emoji_group_id - 1] //来自浏览器引入的emoji_moe.js
    },
  },
};
</script>