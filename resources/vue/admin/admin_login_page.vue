<template>
  <div class="my-3 container" style="max-width: 400px">
    <p>管理员登录</p>
    <b-input-group prepend="饼干：">
      <b-form-input
        v-model="binggan"
        @keyup.enter="admin_login_handle"
      ></b-form-input>
    </b-input-group>
    <br />
    <b-input-group prepend="密码：">
      <b-form-input
        v-model="password"
        :type="password_visible ? null : 'password'"
        @keyup.enter="admin_login_handle"
      ></b-form-input>
    </b-input-group>
    <div class="d-flex flex-row align-items-center my-2">
      <b-button @click="admin_login_handle" class="my-2" variant="success"
        >{{ admin_login_handling ? "提交中" : "登录" }}
      </b-button>
      <b-form-checkbox class="ml-auto" v-model="password_visible">
        显示密码
      </b-form-checkbox>
    </div>
    <p>第一次登录时输入的密码<br />将保存为正式密码，请谨记。</p>
  </div>
</template>


<script>
export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "admin_login_page",
      binggan: null,
      password: null,
      admin_login_handling: false,
      password_visible: false,
    };
  },
  mounted() {
    document.title = "管理员登录";
  },
  methods: {
    admin_login_handle() {
      const config = {
        method: "post",
        url: "/api/admin_login",
        data: {
          password: this.password,
          binggan: this.binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("Token_set", response.data.data.token);
            this.$store.commit("Binggan_set", response.data.data.binggan);
            this.$store.commit("LoginStatus_set", true);
            if (window.localStorage) {
              localStorage.Token = response.data.data.token;
              localStorage.Binggan = response.data.data.binggan;
            } else {
              throw new Error("此浏览器居然不支持localstorage");
            }
            axios.defaults.headers.Authorization =
              "Bearer " + localStorage.Token;
            this.admin_login_handling = false;
            alert(response.data.message);
            window.location.href = "/admin_center"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
          } else {
            this.admin_login_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.admin_login_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message)
        });
    },
  },
};
</script>