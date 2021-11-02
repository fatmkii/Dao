
<template>
  <div>
    <p class="mt-2">你好！别来无恙。</p>
    <p>你的饼干是：{{ binggan }}</p>
    <p>你的奥利奥：{{ user_coin }} 个</p>
    <b-button
      size="md"
      class="my-1 my-sm-0"
      variant="dark"
      @click="logout_handle"
      >退出饼干</b-button
    >
    <hr />
    <b-tabs pills>
      <b-tab title="屏蔽词">
        <div class="mx-2 my-2">
          <p class="my-2">
            标题屏蔽词：（请参考下述JSON格式。前后有[]，最后一个不要有,逗号）
          </p>
          <b-form-textarea
            id="title_pingbici_input"
            v-model="title_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <p class="my-2">
            内容屏蔽词：（请参考下述JSON格式。前后有[]，最后一个不要有,逗号）
          </p>
          <b-form-textarea
            id="content_pingbici_input"
            v-model="content_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <div class="row align-items-center mt-2">
            <div class="col-auto">
              <b-button
                variant="success"
                :disabled="pingbici_set_handling"
                @click="pingbici_set_handle"
                >提交
              </b-button>
            </div>
            <div class="col-auto">
              <b-form-checkbox
                class="mx-2"
                switch
                v-model="use_pingbici_input"
                v-b-popover.hover.bottom="'切换后也要点击提交喔'"
              >
                启用屏蔽词
              </b-form-checkbox>
            </div>
          </div>
        </div>
      </b-tab>
      <b-tab title="我的表情包">
        <div class="mx-2 my-2">
          <p class="my-2">
            我的表情包：（请参考下述JSON格式。前后有[]，最后一个不要有,逗号）
          </p>
          <b-form-textarea
            id="my_emoji_input"
            v-model="my_emoji_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <div class="row align-items-center mt-2">
            <div class="col-auto">
              <b-button
                variant="success"
                :disabled="my_emoji_set_handling"
                @click="my_emoji_set_handle"
                >提交
              </b-button>
            </div>
          </div>
        </div>
      </b-tab>
    </b-tabs>
    <hr />
    <router-link to="admin_center" tag="a" style="font-size: 0.875rem">
      管理
    </router-link>
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "user_center",
      user_coin: 0,
      title_pingbici_input: '["屏蔽词#1","屏蔽词#2"]',
      content_pingbici_input: '["屏蔽词#1","屏蔽词#2"]',
      use_pingbici_input: false,
      pingbici_set_handling: false,
      my_emoji_input:
        '[\n"https://z3.ax1x.com/2021/08/01/Wznvbq.jpg",\n"https://z3.ax1x.com/2021/08/01/Wznjrn.jpg"\n]',
      my_emoji_set_handling: false,
    };
  },
  computed: {
    ...mapState({
      login_status: (state) => state.User.LoginStatus,
      binggan: (state) => state.User.Binggan,
    }),
  },
  methods: {
    logout_handle() {
      const config = {
        method: "post",
        url: "api/logout",
        data: {
          binggan: this.binggan,
        },
      };
      axios(config)
        .then((response) => {
          this.$store.commit("Token_set", "");
          this.$store.commit("Binggan_set", "");
          this.$store.commit("LoginStatus_set", false);
          this.$store.commit("AdminStatus_set", 0);
          this.$store.commit("AdminForums_set", []);

          if (window.localStorage) {
            localStorage.removeItem("Token");
            localStorage.removeItem("Binggan");
          } else {
            throw new Error("此浏览器居然不支持localstorage");
          }
          delete axios.defaults.headers.Authorization;
          window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码
    },
    pingbici_set_handle() {
      try {
        //转换并确认用户输入是否满足JSON格式
        var title_pingbici = JSON.stringify(
          JSON.parse(this.title_pingbici_input)
        );
        var content_pingbici = JSON.stringify(
          JSON.parse(this.content_pingbici_input)
        );
      } catch (e) {
        alert("屏蔽词格式输入有误，请检查");
        return;
      }
      this.pingbici_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/pingbici_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          title_pingbici: title_pingbici,
          content_pingbici: content_pingbici,
          use_pingbici: this.use_pingbici_input,
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
            this.pingbici_set_handling = false;
          } else {
            this.pingbici_set_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.pingbici_set_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    my_emoji_set_handle() {
      try {
        //转换并确认用户输入是否满足JSON格式
        var my_emoji = JSON.stringify(JSON.parse(this.my_emoji_input));
      } catch (e) {
        alert("表情包格式输入有误，请检查");
        return;
      }
      this.my_emoji_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/my_emoji_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          my_emoji: my_emoji,
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
            this.my_emoji_set_handling = false;
          } else {
            this.my_emoji_set_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.my_emoji_set_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
  },
  created() {
    document.title = "个人中心";
    //更新用户信息
    if (localStorage.Token != null && localStorage.Binggan != null) {
      const config = {
        method: "post",
        url: "/api/user/show",
        data: {
          binggan: localStorage.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          this.user_coin = response.data.data.binggan.coin;
          //设定屏蔽词相关状态
          this.use_pingbici_input = Boolean(
            response.data.data.binggan.use_pingbici
          );
          if (response.data.data.pingbici) {
            this.title_pingbici_input =
              response.data.data.pingbici.title_pingbici;
            this.content_pingbici_input =
              response.data.data.pingbici.content_pingbici;
          }
          //设定表情包相关状态
          if (response.data.data.my_emoji) {
            this.my_emoji_input = response.data.data.my_emoji.emojis;
            this.my_emoji_input = this.my_emoji_input.replace(/,/g, ",\n"); //把,改成换行，方便看
            this.my_emoji_input = this.my_emoji_input.replace(/\[/g, "[\n");
            this.my_emoji_input = this.my_emoji_input.replace(/]/g, "\n]");
          }
        })
        .catch((error) => {
          if (error.response.status === 401) {
            localStorage.clear("Binggan"); //如果遇到401错误(用户未认证)，就清除Binggan和Token
            localStorage.clear("Token");
            delete axios.defaults.headers.Authorization;
          }
          alert(error);
        }); // Todo:写异常返回代码;
    }
  },
};
</script>