<template>
  <div class="battle-item">
    <div class="mx-2 my-2">
      <div class="d-flex align-items-center my-1" :class="{
        'justify-content-center': battle_message.message_type == 0,
        'flex-row': battle_message.message_type == 1,
        'flex-row-reverse': battle_message.message_type == 2,
      }" v-for="battle_message in battle_messages" :key="battle_message.index">
        <img class="emoji_img" :src="battle_message.chara_url" />
        <span class="mx-1" :class="{ battle_message_system: battle_message.message_type == 0 }">{{ battle_message.message
        }}</span>
      </div>
    </div>
    <div class="d-flex align-items-center justify-content-center my-1">
      <div v-if="battle_data.progress == 0 && battle_data.is_your_battle == false">
        <b-input-group size="sm" prepend="角色：">
          <b-form-select size="sm" v-model="battle_chara_id" :options="battle_chara_options"></b-form-select><b-button
            :variant="button_theme" size="sm" @click="challenger_roll_handle">挑战</b-button>
        </b-input-group>
      </div>
      <div v-if="battle_data.progress == 0 && battle_data.is_your_battle == true">
        <span class="battle_message_system">正在等待挑战者……</span>
      </div>
      <div v-if="battle_data.progress == 1 && battle_data.is_your_battle == true">
        <span class="battle_message_system">挑战者已出现！</span><b-button class="ml-1" :variant="button_theme" size="sm"
          @click="initiator_roll_handle">迎战</b-button>
      </div>
      <div v-if="battle_data.progress == 2">
        <span class="battle_message_system" v-if="battle_data.result == 1 && battle_data.is_your_battle == true">你赢得了{{
          floor_and_thousandths(battle_data.battle_olo * tax_rate) }}个奥利奥！
        </span>
        <span class="battle_message_system" v-if="battle_data.result == 1 && battle_data.you_are_challenger == true
          ">你输掉了{{ floor_and_thousandths(battle_data.battle_olo) }}个奥利奥……
        </span>
        <span class="battle_message_system" v-if="battle_data.result == 2 && battle_data.is_your_battle == true">你输掉了{{
          floor_and_thousandths(battle_data.battle_olo) }}个奥利奥……
        </span>
        <span class="battle_message_system" v-if="battle_data.result == 2 && battle_data.you_are_challenger == true
          ">你赢得了{{ floor_and_thousandths(battle_data.battle_olo * tax_rate) }}个奥利奥！
        </span>
        <span class="battle_message_system" v-if="battle_data.result == 3 &&
          (battle_data.you_are_challenger == true ||
            battle_data.is_your_battle == true)
          ">你赢得了{{ floor_and_thousandths(battle_data.battle_olo * tax_rate) }}个奥利奥！
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
      battle_chara_id: 8,
    };
  },
  computed: {
    battle_chara_options() {
      var group_id = 0;
      if (this.battle_data.chara_group > 0) {
        if (
          this.$store.state.User.CharaIndex[this.battle_data.chara_group] !=
          undefined
        ) {
          group_id = this.battle_data.chara_group;
        }
      }
      const chara_options = this.$store.state.User.CharaIndex[group_id];
      if (group_id > 0) {
        //如果是特别的乱斗主题group_id>0，则默认选择第一个
        //如果是特别的共通主题group_id=0，则不改变默认选择（8，小豆泥）
        this.battle_chara_id =
          this.$store.state.User.CharaIndex[group_id][0].value;
      }
      return chara_options;
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_double11() {
      return this.$store.state.User.IsDouble11;
      // const double11 = new Date("2023-06-18");
      // const now = new Date(Date.now());
      // return now.toLocaleDateString() === double11.toLocaleDateString();
    },
    tax_rate() {
      if (this.is_double11) {
        return 1.98
      } else {
        return 1.96
      }
    },

  },
  methods: {
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
          chara_id: this.battle_chara_id >= 240 ? this.battle_chara_id - 240 : this.battle_chara_id,//前端中自定义角色从240开始，减去240让后端从0开始计数
          is_custom_chara: this.battle_chara_id >= 240 ? true : false,//this.chara_id >= 240 是自定义大乱斗角色
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
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
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
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
    floor_and_thousandths(number) {
      const floor0 = Math.floor(number)
      // 转为字符串，并拆分为数组
      const int = (floor0 + '').split('');
      // 返回的变量
      let r = '';
      int.reverse().forEach(function (v, i) {
        // 非第一位并且是位值是3的倍数，添加“,”
        if (i !== 0 && i % 3 === 0) {
          r = v + ',' + r;
        } else {
          // 正常添加字符(这是好写法)
          r = v + r;
        }
      });
      return r
    }

  },
  created() { },
};
</script>