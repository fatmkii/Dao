<template>
  <b-modal ref="battle_modal" id="battle_modal" class="battle_modal">
    <template v-slot:modal-header>
      <h5>表情包大乱斗！</h5>
    </template>
    <template v-slot:default>
      <div class="my-2">问苍茫大地 谁主沉浮</div>
      <div v-if="is_double11">活动期间限时手续费2%！</div>
      <div class="my-1">
        <b-input-group prepend="主题：" class="mt-1">
          <b-form-select v-model="battle_chara_group_id" :options="battle_chara_group_options"></b-form-select>
        </b-input-group>
        <b-input-group prepend="角色：" class="mt-1">
          <b-form-select v-model="battle_chara_id" :options="battle_chara_options"></b-form-select>
        </b-input-group>
        <div class="mt-1">
          <b-input-group prepend="下注：">
            <b-form-input v-model="battle_olo" autofocus @keyup.enter="battle_handle"></b-form-input>
          </b-input-group>
        </div>
      </div>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <span v-if="!thread_can_battle">本主题不能发起大乱斗</span>
      <b-button-group>
        <b-button :variant="button_theme" :disabled="battle_handing || !thread_can_battle"
          @click="battle_handle">Fight！</b-button>
        <b-button variant="outline-secondary" @click="cancel()">
          取消
        </b-button>
      </b-button-group>
    </template>
  </b-modal>
</template>


<script>
import { mapState } from "vuex";
export default {
  props: {},
  watch: {
    battle_chara_group_id() {
      if (
        this.$store.state.User.CharaIndex[this.battle_chara_group_id] != undefined
        &&
        this.$store.state.User.CharaIndex[this.battle_chara_group_id].length !=
        0
      ) {
        this.battle_chara_options =
          this.$store.state.User.CharaIndex[this.battle_chara_group_id];
      }
      this.battle_chara_id = this.battle_chara_options[0].value;
    },
  },
  data: function () {
    return {
      name: "battle_modal",
      battle_olo: 100,
      battle_chara_id: 8,
      battle_chara_group_id: 0,
      battle_chara_options: this.$store.state.User.CharaIndex[0],
      battle_handing: false,
    };
  },
  computed: {
    battle_chara_group_options() {
      var group_options = [{ value: 0, text: "共通" }];
      if (this.$store.state.Threads.CurrentThreadData) {
        const random_heads_group =
          this.$store.state.Threads.CurrentThreadData.random_heads_group;
        if (
          random_heads_group != 1 &&
          this.$store.state.User.CharaGroupIndex[random_heads_group - 1] != undefined
          &&
          this.$store.state.User.CharaGroupIndex[random_heads_group - 1]
            .available == true
        ) {
          this.battle_chara_group_id = random_heads_group - 1; //random_heads_group是从1开始数的
          const chara_group =
            this.$store.state.User.CharaGroupIndex[random_heads_group - 1];
          group_options.push(chara_group);
        }
      }
      return group_options;
    },
    // is_double11() {
    //   const double11 = new Date("2023-06-18");
    //   const now = new Date(Date.now());
    //   return now.toLocaleDateString() === double11.toLocaleDateString();
    // },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    ...mapState({
      is_double11: (state) => state.User.IsDouble11,
      forum_id: (state) =>
        state.Forums.CurrentForumData.id ? state.Forums.CurrentForumData.id : 0,
      thread_id: (state) =>
        state.Threads.CurrentThreadData.id
          ? state.Threads.CurrentThreadData.id
          : 0,
      thread_can_battle: (state) => state.Threads.CurrentThreadData.can_battle,
    }),
  },
  methods: {
    battle_handle(event) {
      this.battle_handing = true;
      this.$parent.last_action = "new_battle";
      const timestamp = new Date().getTime();
      const config = {
        method: "post",
        url: "/api/battles",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          chara_group: this.battle_chara_group_id,
          battle_olo: this.battle_olo,
          chara_id: this.battle_chara_id >= 240 ? this.battle_chara_id - 240 : this.battle_chara_id,//前端中自定义角色从240开始，减去240让后端从0开始计数
          is_custom_chara: this.battle_chara_id >= 240 ? true : false,//this.chara_id >= 240 是自定义大乱斗角色

          new_post_key: CryptoJS.MD5(
            this.thread_id +
            this.$store.state.User.Binggan +
            timestamp +
            (event.isTrusted ? "true" : "false")
          ).toString(),
          timestamp: timestamp,
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
            this.battle_handing = false;
            this.$refs["battle_modal"].hide();
            this.$parent.get_posts_data();
          } else if (response.data.code == 244291) {
            this.battle_handing = false;
            this.$parent.show_captcha();
          } else {
            this.battle_handing = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.battle_handing = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    toggle() {
      this.$refs["battle_modal"].toggle();
    },
  },
  activated() {
    this.battle_handing = false;
  },
};
</script>