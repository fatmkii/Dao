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
              this.$store.commit(
                "AdminStatus_set",
                response.data.data.binggan.admin
              );
              if (response.data.data.binggan.admin != 0) {
                this.$store.commit(
                  "AdminForums_set",
                  JSON.parse(response.data.data.binggan.admin_forums)
                );
              }
              this.$store.commit(
                "LockedTTL_set",
                response.data.data.binggan.locked_TTL
              );
              this.$store.commit(
                "UsePingbici_set",
                response.data.data.binggan.use_pingbici
              );
              if (response.data.data.pingbici) {
                this.$store.commit(
                  "TitlePingbici_set",
                  response.data.data.pingbici.title_pingbici
                );
                this.$store.commit(
                  "ContentPingbici_set",
                  response.data.data.pingbici.content_pingbici
                );
              }
              if (response.data.data.my_emoji != null) {
                this.$store.commit("MyEmoji_set", response.data.data.my_emoji);
              }
              this.$store.commit("Emojis_set", response.data.data.emojis);
            } else {
              localStorage.clear("Binggan");
              localStorage.clear("Token");
              delete axios.defaults.headers.Authorization;
              window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
              alert(response.data.message);
            }
          })
          .catch((error) => {
            if (error.response.status === 401) {
              localStorage.clear("Binggan"); //如果遇到401错误(用户未认证)，就清除Binggan和Token
              localStorage.clear("Token");
              delete axios.defaults.headers.Authorization;
            }
            alert("你的饼干好像有问题？请重新登录");
          }); // Todo:写异常返回代码;
      }
    },
  },
  created() {
    this.get_forums_data();
    this.get_user_data();
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
    //读取localStorage的主题
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
  },
};
</script>