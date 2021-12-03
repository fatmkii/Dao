<template>
  <div class="battle-item">
    <div class="mx-2 my-2">
      <div
        class="d-flex align-items-center my-1"
        :class="{
          'justify-content-center': battle_message.message_type == 0,
          'flex-row': battle_message.message_type == 1,
          'flex-row-reverse': battle_message.message_type == 2,
        }"
        v-for="battle_message in battle_messages"
        :key="battle_message.index"
      >
        <img class="emoji_img" :src="battle_message.chara_url" />
        <span
          class="mx-1"
          :class="{ battle_message_system: battle_message.message_type == 0 }"
          >{{ battle_message.message }}</span
        >
      </div>
    </div>
    <div class="d-flex align-items-center justify-content-center my-1">
      <div
        v-if="battle_data.progress == 0 && battle_data.is_your_battle == false"
      >
        <b-input-group size="sm" prepend="角色：">
          <b-form-select
            size="sm"
            v-model="battle_chara_id"
            :options="battle_chara_options"
          ></b-form-select
          ><b-button variant="success" size="sm" @click="challenger_roll_handle"
            >挑战</b-button
          >
        </b-input-group>
      </div>
      <div
        v-if="battle_data.progress == 0 && battle_data.is_your_battle == true"
      >
        <span class="battle_message_system">正在等待挑战者……</span>
      </div>
      <div
        v-if="battle_data.progress == 1 && battle_data.is_your_battle == true"
      >
        <span class="battle_message_system">挑战者已出现！</span
        ><b-button
          class="ml-1"
          variant="success"
          size="sm"
          @click="initiator_roll_handle"
          >迎战</b-button
        >
      </div>
      <div v-if="battle_data.progress == 2">
        <span
          class="battle_message_system"
          v-if="battle_data.result == 1 && battle_data.is_your_battle == true"
          >你赢得了{{ Math.floor(battle_data.battle_olo * 1.96) }}个奥利奥！
        </span>
        <span
          class="battle_message_system"
          v-if="
            battle_data.result == 1 && battle_data.you_are_challenger == true
          "
          >你输掉了{{ battle_data.battle_olo }}个奥利奥……
        </span>
        <span
          class="battle_message_system"
          v-if="battle_data.result == 2 && battle_data.is_your_battle == true"
          >你输掉了{{ battle_data.battle_olo }}个奥利奥……
        </span>
        <span
          class="battle_message_system"
          v-if="
            battle_data.result == 2 && battle_data.you_are_challenger == true
          "
          >你赢得了{{ Math.floor(battle_data.battle_olo * 1.96) }}个奥利奥！
        </span>
        <span
          class="battle_message_system"
          v-if="
            battle_data.result == 3 &&
            (battle_data.you_are_challenger == true ||
              battle_data.is_your_battle == true)
          "
          >你赢得了{{ Math.floor(battle_data.battle_olo * 1.96) }}个奥利奥！
        </span>
      </div>
    </div>
  </div>
</template>


<script>
export default {
  components: {},
  props: {
    battle_data: {
      type: Object,
      default: {},
    },
    battle_messages: {
      type: Array,
      default: [],
    },
  },
  data: function () {
    return {
      name: "battle",
      get_data_handling: true,
      post_roll_handling: false,
      // battle_chara_options: [],
      battle_chara_id: undefined,
    };
  },
  computed: {
    battle_chara_options() {
      return this.$store.state.User.CharaIndex;
    },
  },
  methods: {
    // get_battle_data() {
    //   const config = {
    //     method: "get",
    //     url: "/api/battles/" + this.battle_id,
    //     params: {
    //       binggan: this.$store.state.User.Binggan,
    //     },
    //   };
    //   axios(config)
    //     .then((response) => {
    //       if (response.data.code == 200) {
    //         this.battle_messages = response.data.battle_messages;
    //         this.battle_data = response.data.battle;
    //         if (
    //           this.battle_data.progress == 0 &&
    //           this.battle_data.is_your_battle == false
    //         ) {
    //           this.get_chara_index();
    //         }
    //         this.get_data_handling = false;
    //       } else {
    //         alert(response.data.message);
    //       }
    //     })
    //     .catch((error) => {
    //       alert(Object.values(error.response.data.errors)[0]);
    //     });
    // },
    // get_chara_index() {
    //   const config = {
    //     method: "get",
    //     url: "/api/battles/chara_index",
    //   };
    //   axios(config)
    //     .then((response) => {
    //       if (response.data.code == 200) {
    //         this.battle_chara_options = response.data.chara_index;
    //       } else {
    //         alert(response.data.message);
    //       }
    //     })
    //     .catch((error) => {
    //       this.roll_handling = false;
    //       alert(Object.values(error.response.data.errors)[0]);
    //     });
    // },
    challenger_roll_handle() {
      var confirmed = confirm(
        "要参加大乱斗吗？押注：" + this.battle_data.battle_olo + "个奥利奥"
      );
      if (!confirmed) return;
      this.post_roll_handling = true;
      const config = {
        method: "post",
        url: "/api/battles/c_roll",
        data: {
          binggan: this.$store.state.User.Binggan,
          battle_id: this.battle_data.id,
          chara_id: this.battle_chara_id,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$parent.$parent.get_posts_data();
          } else {
            alert(response.data.message);
            this.post_roll_handling = false;
          }
        })
        .catch((error) => {
          this.roll_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    initiator_roll_handle() {
      this.post_roll_handling = true;
      const config = {
        method: "post",
        url: "/api/battles/i_roll",
        data: {
          binggan: this.$store.state.User.Binggan,
          battle_id: this.battle_data.id,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$parent.$parent.get_posts_data();
          } else {
            alert(response.data.message);
            this.post_roll_handling = false;
          }
        })
        .catch((error) => {
          this.roll_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
  },
  created() {
    // this.get_battle_data();
    // if (
    //   this.battle_data.progress == 0 &&
    //   this.battle_data.is_your_battle == false
    // ) {
    //   this.get_chara_index();
    // }
  },
};
</script>