<template>
  <div>
    <b-modal ref="login-modal" id="login-modal">
      <template v-slot:modal-header>
        <h6>导入饼干</h6>
      </template>

      <template v-slot:default>
        <div class="login_input mt-2">
          <b-input-group prepend="导入饼干" class="mt-2">
            <b-form-input
              v-model="binggan_input"
              placeholder="请输入饼干"
            ></b-form-input>
          </b-input-group>
          <b-input-group prepend="密码" class="mt-2">
            <b-form-input
              type="password"
              v-model="binggan_password"
              placeholder="（选填）定制饼干需要密码"
            ></b-form-input>
          </b-input-group>
          <div class="d-flex flex-row-reverse align-items-center mt-2">
            <b-button variant="outline-success" @click="login_handle"
              >导入</b-button
            >
          </div>
        </div>

        <p v-if="reg_record_TTL > 0">
          你需要等待{{
            Math.floor(reg_record_TTL / 86400) + 1
          }}天后才能再次申请新饼干
        </p>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <b-button
          :variant="button_theme"
          class="mr-auto"
          :disabled="reg_record_TTL > 1"
          @click="register_handle"
          >没有饼干？来一个！
        </b-button>
        <b-button variant="outline-secondary" @click="cancel()">
          取消
        </b-button>
      </template>
    </b-modal>
  </div>
</template>


<script>
export default {
  name: "login_modal",
  components: {},
  props: {},
  data() {
    return {
      binggan_input: "",
      binggan_password: "",
      reg_record_TTL: 1, //如果记录不存在，TTL返回-2
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
  },
  mounted() {
    this.$refs["login-modal"].show();
    const config = {
      method: "get",
      url: "api/user/check_reg_record",
    };
    axios(config)
      .then((response) => {
        this.reg_record_TTL = response.data.data.reg_record_TTL;
      })
      .catch((error) => alert(error)); // Todo:写异常返回代码
  },
  methods: {
    login_handle() {
      const config = {
        method: "post",
        url: "api/login",
        data: {
          binggan: this.binggan_input,
          password: this.binggan_password,
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
            window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码
      this.$router.push({ name: "homepage" });
    },
    register_handle() {
      const config = {
        method: "post",
        url: "api/user/register",
        data: {
          register_key: this.getUUID(),
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
            window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码
    },
    bin2hex(s) {
      var i,
        l,
        o = "",
        n;

      s += "";

      for (i = 0, l = s.length; i < l; i++) {
        n = s.charCodeAt(i).toString(16);
        o += n.length < 2 ? "0" + n : n;
      }

      return o;
    },
    getCanvasFp(domain) {
      var canvas = document.createElement("canvas");
      var ctx = canvas.getContext("2d");
      var txt = domain;
      ctx.textBaseline = "top";
      ctx.font = "14px 'Arial'";
      ctx.textBaseline = "alphabetic";
      ctx.fillStyle = "#f60";
      ctx.fillRect(125, 1, 62, 20);
      ctx.fillStyle = "#069";
      ctx.fillText(txt, 2, 15);
      ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
      ctx.fillText(txt, 4, 17);

      var b64 = canvas.toDataURL().replace("data:image/png;base64,", "");
      // window.atob 用于解码使用 base-64 编码的字符串
      var bin = atob(b64);
      var crc = this.bin2hex(bin.slice(-16, -12));
      return crc;
    },
    getUUID() {
      var key = "XiaoHuoGuoCpttmm"; // 密钥, AES-128 需 16 字符, AES-256 需要32个字符,
      var iv = "abcdef0123456789"; // 初始向量 initial vector 16 个字符

      key = CryptoJS.enc.Utf8.parse(key);
      iv = CryptoJS.enc.Utf8.parse(iv);

      var options = {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7,
      };

      const CRC = this.getCanvasFp("BrowserLeaks,com <canvas> 1.0");
      const UA = CryptoJS.MD5(navigator.userAgent).toString().substr(0, 8);
      const encrypted = CryptoJS.AES.encrypt(
        rgk + CRC + UA,
        key,
        options
      ).toString();
      return encrypted;
    },
  },
};
</script>