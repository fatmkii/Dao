<template>
  <div>
    <p class="my-2">新饼干无密码，建议在此处尽快设定密码，以免饼干遗失。</p>
    <div style="max-width: 400px" class="binggan_custom_input">
      <b-input-group prepend="旧密码" class="mt-2">
        <b-form-input type="password" v-model="old_binggan_password" placeholder="（选填）如果未设定密码可留空"></b-form-input>
      </b-input-group>
      <b-input-group prepend="新密码" class="mt-2">
        <b-form-input type="password" v-model="new_binggan_password"
          :state="new_binggan_password == new_binggan_password_repeat" placeholder="7~20个字符(字母、数字、下划线)"></b-form-input>
      </b-input-group>
      <b-input-group prepend="重复密码" class="mt-2">
        <b-form-input type="password" v-model="new_binggan_password_repeat"
          :state="new_binggan_password == new_binggan_password_repeat" placeholder="再次输入密码"></b-form-input>
      </b-input-group>

      <div class="d-flex align-items-center mt-2">
        <b-button :variant="button_theme" @click="binggan_set_password_handle"
          :size="is_mobile ? 'sm' : 'md'">提交</b-button>
      </div>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "set_password_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      old_binggan_password: "",
      new_binggan_password: "",
      new_binggan_password_repeat: "",
      binggan_set_password_handling: false,
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
    binggan_set_password_handle() {
      if (this.new_binggan_password != this.new_binggan_password_repeat) {
        alert("两次输入密码不一致");
        return;
      }
      if (this.new_binggan_password == "") {
        alert("未输入新密码");
        return;
      }

      this.binggan_set_password_handling = true;
      const config = {
        method: "post",
        url: "/api/set_password",
        data: {
          binggan: this.$store.state.User.Binggan,
          old_password: this.old_binggan_password,
          new_password: this.new_binggan_password,
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
            this.binggan_set_password_handling = false;
            this.old_binggan_password = "";
            this.new_binggan_password = "";
            this.new_binggan_password_repeat = "";
            alert("已设定新密码！下次导入饼干时使用。");
          } else {
            this.binggan_set_password_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.binggan_set_password_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  activated() { },
};
</script>