<template>
  <b-modal ref="reward_modal" id="reward_modal">
    <template v-slot:modal-header>
      <h5>打赏olo 给№{{ floor }}楼</h5>
    </template>
    <template v-slot:default>
      <p>
        <span v-if="!is_double11">
          友情提示：在打赏额以外，会追加扣除7%手续费。</span>
        <span v-else><del>友情提示：在打赏额以外，会追加扣除7%手续费。</del>
          <br />
          活动期间限时手续费2%！
        </span>
        <br />
        总共扣除：
        <span style="color: red">{{ Math.ceil(coin_reward_input * (is_double11 ? 1.02 : 1.07)) }} </span>块奥利奥。
      </p>
      <b-input-group prepend="打赏：">
        <b-form-input v-model="coin_reward_input" placeholder="olo数量"></b-form-input>
      </b-input-group>
      <b-input-group prepend="留言：">
        <b-form-input v-model="content_reward_input"></b-form-input>
      </b-input-group>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <b-button-group>
        <b-button :variant="button_theme" :disabled="reward_handling" @click="reward_handle">打赏！</b-button>
        <b-button variant="outline-secondary" @click="cancel()">
          取消
        </b-button>
      </b-button-group>
    </template>
  </b-modal>
</template>


<script>
export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "reward_modal",
      content_reward_input: "",
      coin_reward_input: "",
      reward_handling: false,
      floor: Number,
      thread_id: Number,
      post_id: Number,
      post_floor_message: String,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_double11() {
      return this.$store.state.User.IsDouble11;
      // const double11 = new Date("2023-06-18");
      // const now = new Date(Date.now());
      // return now.toLocaleDateString() === double11.toLocaleDateString();
    },
  },
  created() { },
  methods: {
    reward_click(payload) {
      this.floor = payload.floor;
      this.thread_id = payload.thread_id;
      this.post_id = payload.post_id;
      this.post_floor_message = payload.post_floor_message;
      this.$refs["reward_modal"].show();
    },
    reward_handle() {
      this.reward_handling = true;
      const config = {
        method: "post",
        url: "/api/user/reward",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.$store.state.Forums.CurrentForumData.id,
          thread_id: this.thread_id,
          post_id: this.post_id,
          content: this.content_reward_input,
          coin: parseInt(this.coin_reward_input),
          post_floor_message: this.post_floor_message,
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
            this.$refs["reward_modal"].hide();
            this.reward_handling = false;
            this.$parent.get_posts_data();
          } else {
            this.reward_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.reward_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
};
</script>