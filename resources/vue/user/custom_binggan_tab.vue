<template>
  <div>
    <p class="my-2">
      说明：可申请定制饼干，每个价格10万olo。<br />为避免被猜到，定制饼干必须输入密码。
    </p>
    <div style="max-width: 400px" class="binggan_custom_input">
      <b-input-group prepend="定制饼干" class="mt-2">
        <b-form-input v-model="binggan_apply" placeholder="7~16个字符(字母、数字、下划线)"></b-form-input>
      </b-input-group>
      <b-input-group prepend="密码" class="mt-2">
        <b-form-input type="password" v-model="binggan_password" :state="binggan_password == binggan_password_repeat"
          placeholder="7~20个字符(字母、数字、下划线)"></b-form-input>
      </b-input-group>
      <b-input-group prepend="重复密码" class="mt-2">
        <b-form-input type="password" v-model="binggan_password_repeat"
          :state="binggan_password == binggan_password_repeat" placeholder="再次输入密码"></b-form-input>
      </b-input-group>

      <div class="d-flex align-items-center mt-2">
        <b-button :variant="button_theme" @click="binggan_custom_handle" :size="is_mobile ? 'sm' : 'md'">提交</b-button>
        <span class="ml-2">请务必保存好饼干和密码喔</span>
      </div>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "custom_binggan_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      binggan_apply: "",
      binggan_password: "",
      binggan_password_repeat: "",
      binggan_apply_handling: false,
    }
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    ...mapState({

    }),
  },
  created() { },
  methods: {
    binggan_custom_handle() {
      if (this.binggan_password != this.binggan_password_repeat) {
        alert("两次输入密码不一致");
        return;
      }
      if (this.binggan_apply == "" || this.binggan_password == "") {
        alert("请输入定制饼干和密码");
        return;
      }
      var confirmed = confirm("要申请这个定制饼干吗？将消耗10万olo");
      if (!confirmed) {
        return;
      }
      this.binggan_apply_handling = true;
      const config = {
        method: "post",
        url: "/api/user/create_custom",
        data: {
          binggan: this.$store.state.User.Binggan,
          binggan_apply: this.binggan_apply,
          password: this.binggan_password,
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
            this.binggan_apply_handling = false;
            this.binggan_password_repeat = "";
            this.binggan_password = "";
            alert("定制饼干申请成功！重新导入即可使用。");
          } else {
            this.binggan_apply_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.binggan_apply_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  activated() { },
};
</script>