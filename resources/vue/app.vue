<template>
  <div display:none></div>
</template>

<script>
export default {
  data: function () {
    return {
      name: "app",
    };
  },
  methods: {
    get_forums_data() {
      const config = {
        method: "get",
        url: "/api/forums/",
        data: {},
      };
      axios(config)
        .then((response) => {
          this.$store.commit("ForumsData_set", response.data.data);
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码;}
    },
    get_user_data() {
      if (localStorage.Token != null && localStorage.Binggan != null) {
        this.$store.commit("UserDataLoaded_set", 1);
        this.$store.commit("Token_set", localStorage.Token);
        this.$store.commit("Binggan_set", localStorage.Binggan);
        this.$store.commit("LoginStatus_set", true);
        axios.defaults.headers.Authorization = "Bearer " + localStorage.Token;
        //确认饼干和token是否合法和获得屏蔽词等
        const config = {
          method: "post",
          url: "/api/user/show",
          data: {
            binggan: localStorage.Binggan,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              this.set_VueStore(response.data.data); //把response数据写入到vuex
              this.$store.commit("UserDataLoaded_set", 2);
            } else {
              localStorage.clear("Binggan");
              localStorage.clear("Token");
              delete axios.defaults.headers.Authorization;
              window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
              alert(response.data.message);
            }
          })
          .catch((error) => {
            if (
              error.response.status !== undefined &&
              error.response.status === 401
            ) {
              localStorage.clear("Binggan"); //如果遇到401错误(用户未认证)，就清除Binggan和Token
              localStorage.clear("Token");
              delete axios.defaults.headers.Authorization;
              alert("你的饼干好像有问题？请重新登录");
            } else {
              alert(Object.values(error.response.data.errors)[0]);
            }
          });
      }
    },
    set_LocalStorage() {
      //读取localStorage的浏览记录
      if (localStorage.browse_logger != null) {
        this.$store.commit(
          "BrowseLogger_set_all",
          JSON.parse(localStorage.browse_logger)
        );
      } else {
        localStorage.browse_logger = JSON.stringify(
          this.$store.state.User.BrowseLogger
        );
      }
      //读取localStorage的皮肤主题
      if (localStorage.getItem("theme") == null) {
        localStorage.theme = "hdao";
        window.document.documentElement.setAttribute(
          "data-theme",
          localStorage.theme
        );
      } else {
        window.document.documentElement.setAttribute(
          "data-theme",
          localStorage.theme
        );
      }
      //读取localStorage的侧边栏位置记录
      if (localStorage.getItem("z_bar_left") != null) {
        window.document.documentElement.setAttribute(
          "z-bar-left",
          Boolean(localStorage.z_bar_left)
        );
      } else {
        window.document.documentElement.setAttribute("z-bar-left", "false");
      }

      //读取是否折叠屏蔽词
      if (localStorage.getItem("fold_pingbici") != null) {
        this.$store.commit(
          "FoldPingbici_set",
          localStorage.getItem("fold_pingbici") === "true"
        );
      }

      //读取MyCss（自定义字体大小和行距等）
      if (localStorage.my_css != null) {
        this.$store.commit("MyCSS_set_all", JSON.parse(localStorage.my_css));
        if (this.$store.state.MyCSS.PostsMarginTop == null) {
          this.$store.commit("PostsMarginTop_set", 32); //临时的，给旧版本加默认值
          const my_css = this.$store.state.MyCSS;
          localStorage.my_css = JSON.stringify(my_css);
        }
      }
    },
    set_VueStore(response_data) {
      if (response_data.binggan.admin) {
        this.$store.commit("AdminStatus_set", response_data.binggan.admin);
        if (response_data.binggan.admin != 0) {
          this.$store.commit(
            "AdminForums_set",
            JSON.parse(response_data.binggan.admin_forums)
          );
        }
      }
      this.$store.commit("LockedTTL_set", response_data.binggan.locked_TTL);
      this.$store.commit("UsePingbici_set", response_data.binggan.use_pingbici);
      if (response_data.binggan.nickname) {
        this.$store.commit("NickName_set", response_data.binggan.nickname);
      } else {
        this.$store.commit("NickName_set", "= =");
      }
      if (response_data.pingbici) {
        this.$store.commit(
          "TitlePingbici_set",
          response_data.pingbici.title_pingbici
        );
        this.$store.commit(
          "ContentPingbici_set",
          response_data.pingbici.content_pingbici
        );
      }
      if (response_data.my_emoji != null) {
        this.$store.commit("MyEmoji_set", response_data.my_emoji);
      }
    },
    get_ip() {
      const config = {
        method: "get",
        url: "http://ip-api.com/json/" + "117.136.79.87",
        params: {
          fields: "16401",
        },
      };
      axios(config)
        .then((response) => {
          console.log(response);
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码;}
    },
  },
  created() {
    this.get_forums_data();
    this.get_user_data();

    //把常用数据写入Vuex，变量来源于json/文件夹下的定义
    this.$store.commit("Emojis_set", emoji_json);
    this.$store.commit("RandomHeads_set", random_heads_json);
    this.$store.commit("CharaIndex_set", chara_index);
    this.$store.commit("CharaGroupIndex_set", chara_group_index);

    this.set_LocalStorage(); //把LocalStorage变量存到Vuex
  },
};
</script>