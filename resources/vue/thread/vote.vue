<template>
  <div class="vote-content align-items-center" v-show="!get_vote_data_handling">
    <div class="vote-title my-2" ref="vote-title">
      <span style="word-wrap: break-word; white-space: normal"
        >投票： {{ vote_question.title }}
      </span>
    </div>
    <div
      class="vote-option my-2"
      v-for="(vote_option, index) in vote_options"
      :key="index"
    >
      <b-form-radio v-model="choice_seleted" :value="vote_option.id">
        {{ index + 1 }}：{{ vote_option.option_text }}
      </b-form-radio>
      <div class="d-flex">
        <span style="min-width: 100px" class="pl-1">{{
          vote_option.vote_total
        }}</span>
        <b-progress
          class="w-75 ml-4"
          height="1.5rem"
          :value="vote_option.vote_total"
          :max="vote_question.vote_total"
        ></b-progress>
      </div>
    </div>
    <div class="my-2">
      <b-button
        :variant="button_theme"
        size="sm"
        @click="new_vote_handle"
        :disabled="
          user_choices != null || new_vote_handling == true || vote_end_flag
        "
        >投票
      </b-button>
      <span class="ml-2" style="font-size: 0.875rem" v-if="user_choices != null"
        >你已经投过票了！谢谢参与。</span
      >
      <span class="ml-2" style="font-size: 0.875rem" v-if="!vote_end_flag"
        >此投票将于 {{ vote_TTL }} 后结束。</span
      >
      <span class="ml-2" style="font-size: 0.875rem" v-if="vote_end_flag"
        >此投票已结束</span
      >
    </div>
  </div>
</template>


<script>
export default {
  props: {
    vote_question_id: Number,
  },
  watch: {
    vote_question_id() {
      this.get_vote_data();
    },
  },
  data: function () {
    return {
      name: "vote",
      get_vote_data_handling: true,
      new_vote_handling: false,
      vote_question: {},
      vote_options: [],
      user_choices: [],
      choice_seleted: undefined,
      choice_seleted_multiple: [],
    };
  },
  computed: {
    vote_TTL() {
      if (this.vote_question.end_date === undefined) {
        return "";
      }
      const seconds =
        (Date.parse(
          this.vote_question.end_date.replace(/-/g, "/") + " GMT+800"
        ) -
          Date.now()) /
        1000;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return hours + "小时" + minutes + "分钟";
    },
    vote_end_flag() {
      if (this.vote_question.end_date === undefined) {
        return false;
      }
      const seconds =
        (Date.parse(
          this.vote_question.end_date.replace(/-/g, "/") + " GMT+800"
        ) -
          Date.now()) /
        1000;
      if (seconds > 0) {
        return false;
      } else {
        return true;
      }
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
  },
  methods: {
    get_vote_data() {
      this.get_vote_data_handling = true;
      const config = {
        method: "get",
        url: "/api/votes/" + this.vote_question_id,
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.vote_question = response.data.vote_question;
            this.vote_options = response.data.vote_options;
            this.user_choices = response.data.user_choices;
            if (this.user_choices) {
              let user_voted = JSON.parse(this.user_choices.options_id);
              if (user_voted.length == 1) {
                this.choice_seleted = user_voted[0];
              } //todo 写多选情况
            }
            this.get_vote_data_handling = false;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    new_vote_handle() {
      let vote_options = [];
      this.new_vote_handling = true;
      if (this.vote_question.multiple == false) {
        vote_options.push(this.choice_seleted);
      } else {
      }
      const config = {
        method: "post",
        url: "/api/votes/",
        data: {
          binggan: this.$store.state.User.Binggan,
          vote_question_id: this.vote_question_id,
          vote_options: JSON.stringify(vote_options),
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
            this.get_vote_data();
            this.new_vote_handling = false;
          } else {
            alert(response.data.message);
            this.new_vote_handling = false;
          }
        })
        .catch((error) => {
          this.new_vote_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  created() {
    this.get_vote_data();
  },
};
</script>