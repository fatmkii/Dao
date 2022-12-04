<template>
  <b-modal ref="pingbici_modal" id="pingbici_modal">
    <template v-slot:modal-header>
      <h5>追加屏蔽词</h5>
    </template>
    <template v-slot:default>
      <p style="word-wrap: break-word">暂时只做了“内容屏蔽词”的追加</p>
      <div class="hongbao_input">
        <b-input-group prepend="屏蔽词" class="mt-2">
          <b-form-input v-model="pingbici_input"></b-form-input>
        </b-input-group>
      </div>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <b-button-group>
        <b-button
          :variant="button_theme"
          @click="add_pingbici_handle"
          :disabled="add_pingbici_handling"
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
  name: "pingbici_modal",
  components: {},
  props: {},
  watch: {},
  data: function () {
    return {
      pingbici_input: "",
      add_pingbici_handling: false,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
  },
  created() {},
  methods: {
    add_pingbici_handle() {
      this.add_pingbici_handling = true;
      const config = {
        method: "post",
        url: "/api/user/pingbici_add",
        data: {
          binggan: this.$store.state.User.Binggan,
          content_pingbici: this.pingbici_input,
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
            this.$store.commit("UsePingbici_set", true);
            this.$store.commit(
              "ContentPingbici_set",
              JSON.parse(response.data.content_pingbici)
            );
            this.$refs["pingbici_modal"].toggle();
            this.add_pingbici_handling = false;
            this.$eventHub.$emit("pingbici_refresh"); //通过eventHub空vue对象分发事件
          } else {
            this.add_pingbici_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.add_pingbici_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    toggle() {
      this.$refs["pingbici_modal"].toggle();
    },
  },
  activated() {},
};
</script>