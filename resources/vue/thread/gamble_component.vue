<template>
  <div
    class="gamble-content align-items-center"
    v-show="!get_gamble_data_handling"
  >
    <div class="gamble-title my-2" ref="gamble-title">
      <span style="word-wrap: break-word; white-space: normal"
        >投票： {{ gamble_question.title }}
      </span>
    </div>
    <div
      class="gamble-option my-2"
      v-for="(gamble_option, index) in gamble_options"
      :key="index"
    >
      <b-form-radio v-model="choice_seleted" :value="gamble_option.id">
        （实时赔率 {{ gamble_option.odds.toFixed(2) }}）：{{
          gamble_option.id == gamble_question.result_option_id ? "⭐" : ""
        }}
        {{ gamble_option.option_text }}
        <div class="d-flex">
          <span style="min-width: 100px" class="pl-1">{{
            gamble_option.olo_total
          }}</span
          ><b-progress
            class="w-75 ml-4"
            height="1.5rem"
            :value="gamble_option.olo_total"
            :max="gamble_question.olo_total"
          ></b-progress>
        </div>
      </b-form-radio>
    </div>
    <div class="my-2">
      <b-input-group class="my-2" size="sm">
        <b-form-input
          placeholder="投注额"
          style="max-width: 200px"
          v-model="betting_olo"
        ></b-form-input>
        <b-button
          :variant="button_theme"
          size="sm"
          @click="new_betting_handle"
          :disabled="
            new_betting_handling == true ||
            gamble_end_flag ||
            gamble_question.is_closed != 0
          "
        >
          投注</b-button
        >
      </b-input-group>
      <b-button
        size="sm"
        variant="warning"
        v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show"
        @click="gamble_close"
      >
        开奖
      </b-button>
      <b-button
        size="sm"
        variant="warning"
        v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show"
        @click="gamble_repeal"
      >
        中止
      </b-button>
      <span class="ml-2" style="font-size: 0.875rem" v-if="!gamble_end_flag"
        >此菠菜将于 {{ gamble_TTL }} 后关闭投注。</span
      >
      <span class="ml-2" style="font-size: 0.875rem" v-if="gamble_end_flag"
        >此菠菜已关闭投注。</span
      >
    </div>
    <div class="my-2" v-if="gamble_question.is_closed">
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="gamble_question.is_closed == 1"
        >此菠菜已结束，奖金已发放。中奖项是：{{ result_option_text }}</span
      >
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="gamble_question.is_closed == 2"
        >此菠菜已被管理员中止，投注额已退回。</span
      >
    </div>
    <table class="gamble_table">
      <thead>
        <tr class="text-center">
          <th width="40%">我的投注</th>
          <th width="20%">投注额</th>
          <th width="20%">赔率</th>
          <th width="20%">奖金</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user_choice in user_choices" :key="user_choice.id">
          <td class="text-left">
            <span style="word-wrap: break-word; white-space: normal">
              {{ user_choice.option_text }}
            </span>
          </td>
          <td class="text-center">{{ user_choice.betting_olo }}</td>
          <td class="text-center">{{ user_choice.odds }}</td>
          <td class="text-center">{{ user_choice.win_olo }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
export default {
  props: {
    gamble_question_id: {
      type: Number,
      default: 0,
    },
    admin_button_show: {
      type: Boolean,
      default: false,
    },
  },
  watch: {
    gamble_question_id() {
      this.get_gamble_data();
    },
  },
  data: function () {
    return {
      name: "gamble_component",
      get_gamble_data_handling: true,
      new_betting_handling: false,
      gamble_question: {},
      gamble_options: [],
      user_choices: [],
      betting_olo: undefined,
      choice_seleted: undefined,
    };
  },
  computed: {
    gamble_TTL() {
      if (this.gamble_question.end_date === undefined) {
        return "";
      }
      const seconds =
        (Date.parse(
          this.gamble_question.end_date.replace(/-/g, "/") + " GMT+800"
        ) -
          Date.now()) /
        1000;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return hours + "小时" + minutes + "分钟";
    },
    gamble_end_flag() {
      if (this.gamble_question.end_date === undefined) {
        return false;
      }
      const seconds =
        (Date.parse(
          this.gamble_question.end_date.replace(/-/g, "/") + " GMT+800"
        ) -
          Date.now()) /
        1000;
      if (seconds > 0) {
        return false;
      } else {
        return true;
      }
    },
    forum_id() {
      return this.$store.state.Forums.CurrentForumData.id;
    },
    result_option_text() {
      var text = "";
      var result_option_id = this.gamble_question.result_option_id;
      this.gamble_options.forEach((gamble_option, index) => {
        if (gamble_option.id == result_option_id) {
          text = gamble_option.option_text;
        }
      });
      return text;
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
  },
  methods: {
    get_gamble_data() {
      this.get_gamble_data_handling = true;
      const config = {
        method: "get",
        url: "/api/gambles/" + this.gamble_question_id,
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.gamble_question = response.data.gamble_question;
            this.gamble_options = response.data.gamble_options;
            this.user_choices = response.data.user_choices;
            //为user_choices加上option_text
            this.user_choices.forEach((user_choice, index) => {
              this.gamble_options.forEach((gamble_option, index) => {
                if (user_choice.option_id == gamble_option.id) {
                  user_choice.option_text = gamble_option.option_text;
                }
              });
            });
            this.get_gamble_data_handling = false;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    new_betting_handle() {
      if (!this.betting_olo) {
        alert("请输入投注额");
        return;
      }
      this.new_betting_handling = true;
      const config = {
        method: "post",
        url: "/api/gambles/",
        data: {
          binggan: this.$store.state.User.Binggan,
          gamble_question_id: this.gamble_question_id,
          bet_option: this.choice_seleted,
          betting_olo: this.betting_olo,
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
            this.get_gamble_data();
            this.$parent.get_posts_data();
            this.new_betting_handling = false;
          } else {
            alert(response.data.message);
            this.new_betting_handling = false;
          }
        })
        .catch((error) => {
          this.new_betting_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    gamble_close() {
      var option_selected_index = null;
      this.gamble_options.forEach((option, index) => {
        if (option.id == this.choice_seleted) {
          option_selected_index = index;
        }
      });
      if (option_selected_index !== null) {
        var confirmed = confirm(
          "确定要开奖吗？请注意不要选错，开奖结果是：" +
            this.gamble_options[option_selected_index].option_text
        );
      } else {
        alert("请选择开奖选项");
        return;
      }
      if (confirmed == false) {
        return;
      }
      const config = {
        method: "post",
        url: "/api/gambles/close",
        data: {
          binggan: this.$store.state.User.Binggan,
          gamble_question_id: this.gamble_question_id,
          result_option: this.choice_seleted,
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
            this.get_gamble_data();
            this.$parent.get_posts_data();
            this.new_betting_handling = false;
          } else {
            alert(response.data.message);
            this.new_betting_handling = false;
          }
        })
        .catch((error) => {
          this.new_betting_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    gamble_repeal() {
      var confirmed = confirm("确定要中止此菠菜吗？奥利奥将退回。");
      if (confirmed == false) {
        return;
      }
      const config = {
        method: "post",
        url: "/api/gambles/repeal",
        data: {
          binggan: this.$store.state.User.Binggan,
          gamble_question_id: this.gamble_question_id,
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
            this.get_gamble_data();
            this.$parent.get_posts_data();
            this.new_betting_handling = false;
          } else {
            alert(response.data.message);
            this.new_betting_handling = false;
          }
        })
        .catch((error) => {
          this.new_betting_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  created() {
    this.get_gamble_data();
  },
};
</script>