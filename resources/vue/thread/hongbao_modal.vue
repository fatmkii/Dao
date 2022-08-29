<template>
  <b-modal ref="hongbao_modal" id="hongbao_modal">
    <template v-slot:modal-header>
      <h5>发起口令红包</h5>
    </template>
    <template v-slot:default>
      <div class="hongbao_input">
        <p>
          友情提示：在红包olo总数以外，会追加扣除7%手续费
          <br />
          总共扣除：合计
          <span style="color: red">{{ Math.ceil(hongbao_olo * 1.07) }} </span
          >块奥利奥
          <span v-show="hongbao_type == 2"
            ><br />
            <span>
              定额红包：每个<span style="color: red">{{ hongbao_quota }}</span
              >奥利奥</span
            ></span
          >
        </p>
        <b-input-group prepend="红包个数" class="mt-2">
          <b-form-input
            v-model="hongbao_num"
            placeholder="红包个数"
          ></b-form-input>
        </b-input-group>
        <b-input-group prepend="olo总数" class="mt-2">
          <b-form-input
            v-model="hongbao_olo"
            placeholder="olo总数"
          ></b-form-input>
        </b-input-group>
        <b-input-group prepend="红包口令" class="mt-2">
          <b-form-input
            v-model="hongbao_key_word"
            placeholder="必填"
          ></b-form-input>
        </b-input-group>
        <b-input-group prepend="回复留言" class="mt-2">
          <b-form-input
            v-model="hongbao_message"
            placeholder="可选"
          ></b-form-input>
        </b-input-group>
        <b-input-group prepend="红包类型" class="mt-2">
          <b-form-select
            v-model="hongbao_type"
            :options="hongbao_type_options"
            value-field="value"
            text-field="text"
          ></b-form-select>
        </b-input-group>
      </div>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <b-button-group>
        <b-button
          :variant="button_theme"
          :disabled="hongbao_handling"
          @click="hongbao_handle"
          >提交</b-button
        >
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
      hongbao_olo: "",
      hongbao_num: undefined,
      hongbao_key_word: undefined,
      hongbao_message: "",
      hongbao_handling: false,
      hongbao_type: 1,
      hongbao_type_options: [
        { value: 1, text: "随机红包" },
        { value: 2, text: "定额红包" },
      ],
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
    ...mapState({
      forum_id: (state) =>
        state.Forums.CurrentForumData.id ? state.Forums.CurrentForumData.id : 0,
      thread_id: (state) =>
        state.Threads.CurrentThreadData.id
          ? state.Threads.CurrentThreadData.id
          : 0,
    }),
  },
  created() {},
  methods: {
    hongbao_handle() {
      if (this.hongbao_type == 2 && this.hongbao_olo % this.hongbao_num != 0) {
        alert("选择定额红包时，总额要是红包数量的整倍数喔");
        return;
      }
      this.hongbao_handling = true;
      const config = {
        method: "post",
        url: "/api/hongbao_post",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          hongbao_olo: this.hongbao_olo,
          hongbao_num: this.hongbao_num,
          type: this.hongbao_type,
          hongbao_key_word: this.hongbao_key_word,
          hongbao_message: this.hongbao_message,
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
          alert(error.response.data.message);
        });
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