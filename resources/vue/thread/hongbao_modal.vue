<template>
  <b-modal ref="hongbao_modal" id="hongbao_modal">
    <template v-slot:modal-header>
      <h5>发起口令红包</h5>
    </template>
    <template v-slot:default>
      <div class="hongbao_input">
        <p>
          <span v-if="!is_double11">
            友情提示：在打赏额以外，会追加扣除7%手续费。</span>
          <span v-else><del>友情提示：在打赏额以外，会追加扣除7%手续费。</del>
            <br />
            618限时活动手续费2%！
          </span>
          <br />
          总共扣除：合计
          <span style="color: red">{{ Math.ceil(hongbao_olo * (is_double11 ? 1.02 : 1.07)) }} </span>块奥利奥
          <span v-show="hongbao_type == 2"><br />
            <span>
              定额红包：每个<span style="color: red">{{ hongbao_quota }}</span>奥利奥</span></span>
          <br />
          {{ key_word_type_note[key_word_type - 1] }}
        </p>
        <b-input-group prepend="红包类型" class="mt-2">
          <b-form-select v-model="key_word_type" :options="key_word_type_options" value-field="value"
            text-field="text"></b-form-select>
        </b-input-group>
        <b-input-group prepend="olo类型" class="mt-2">
          <b-form-select v-model="hongbao_type" :options="hongbao_type_options" value-field="value"
            text-field="text"></b-form-select>
        </b-input-group>
        <b-input-group prepend="红包个数" class="mt-2">
          <b-form-input v-model="hongbao_num" placeholder="红包个数"></b-form-input>
        </b-input-group>
        <b-input-group prepend="olo总数" class="mt-2">
          <b-form-input v-model="hongbao_olo" placeholder="olo总数"></b-form-input>
        </b-input-group>
        <b-input-group prepend="红包口令" class="mt-2">
          <b-form-input v-model="hongbao_key_word" placeholder="必填"></b-form-input>
        </b-input-group>
        <b-input-group prepend="口令提示" class="mt-2" v-if="[2, 3].includes(key_word_type)">
          <b-form-input v-model="hongbao_question" placeholder="必填"></b-form-input>
        </b-input-group>

        <b-input-group prepend="回复留言" class="mt-2" v-for="(message, index) in hongbao_message" :key="index">
          <b-form-input v-model="hongbao_message[index]" placeholder="可选（多个留言时随机回复一个）"></b-form-input>
        </b-input-group>
        <b-input-group prepend="留言数量" class="mt-2">
          <b-form-input v-model="hongbao_message_num" max="5" min="1" type="range"
            @change="hongbao_message_num_change"></b-form-input>
        </b-input-group>
        <div class="d-flex justify-content-end mt-2">
          <b-form-checkbox v-model="hongbao_olo_hide" switch>
            隐藏红包olo总额
          </b-form-checkbox>
        </div>

      </div>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <b-button-group>
        <b-button :variant="button_theme" :disabled="hongbao_handling" @click="hongbao_handle">提交</b-button>
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
  name: "hongbao_modal",
  components: {},
  props: {},
  data: function () {
    return {
      hongbao_olo: 3000,
      hongbao_num: 10,
      hongbao_key_word: undefined,
      hongbao_message: [""],
      hongbao_message_num: 1,
      hongbao_question: "",
      hongbao_handling: false,
      key_word_type: 1,
      key_word_type_options: [
        { value: 1, text: "口令红包" },
        { value: 2, text: "抢答红包" },
        { value: 3, text: "暗号红包" },
      ],
      key_word_type_note: [
        "口令红包：口令会显示",
        "抢答红包：口令会隐藏，回复中会显示",
        "暗号红包：口令和回复中均隐藏",
      ],
      hongbao_type: 1,
      hongbao_type_options: [
        { value: 1, text: "随机olo" },
        { value: 2, text: "定额olo" },
      ],
      hongbao_olo_hide: false,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
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
    ...mapState({
      forum_id: (state) =>
        state.Forums.CurrentForumData.id ? state.Forums.CurrentForumData.id : 0,
      thread_id: (state) =>
        state.Threads.CurrentThreadData.id
          ? state.Threads.CurrentThreadData.id
          : 0,
    }),
  },
  created() { },
  methods: {
    hongbao_handle() {
      if (this.hongbao_type == 2 && this.hongbao_olo % this.hongbao_num != 0) {
        alert("选择定额红包时，总额要是红包数量的整倍数喔");
        return;
      }
      if ([2, 3].includes(this.key_word_type) && !this.hongbao_question) {
        alert("抢答红包或者暗号红包，需要输入口令提示喔");
        return;
      }
      this.hongbao_handling = true;
      var config = {
        method: "post",
        url: "/api/hongbao_post",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          hongbao_olo: this.hongbao_olo,
          hongbao_num: this.hongbao_num,
          type: this.hongbao_type,
          key_word_type: this.key_word_type,
          hongbao_key_word: this.hongbao_key_word,
          hongbao_question: this.hongbao_question,
          hongbao_olo_hide: this.hongbao_olo_hide,
        },
      };
      if (this.hongbao_message.length == 1) {
        //单个回复时，以json格式提交到hongbao_message
        config.data.hongbao_message = this.hongbao_message[0]
      } else {
        //多个回复时，以json格式提交到hongbao_message_json
        config.data.hongbao_message_json = JSON.stringify(this.hongbao_message)
      }

      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.$refs["hongbao_modal"].hide();
            this.hongbao_handling = false;
            this.$parent.get_posts_data();
          } else {
            this.hongbao_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.hongbao_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
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
    toggle() {
      this.$refs["hongbao_modal"].toggle();
    },
  },
  activated() {
    this.hongbao_handling = false;
  },
};
</script>