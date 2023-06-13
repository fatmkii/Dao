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
      this.$store.commit("ForumsLoadStatus_set", 1);
      const config = {
        method: "get",
        url: "/api/forums/",
        data: {},
      };
      axios(config)
        .then((response) => {
          this.$store.commit("ForumsData_set", response.data.data);
          this.$store.commit("ForumsLoadStatus_set", 2);
        })
        .catch((error) => {
          this.$store.commit("ForumsLoadStatus_set", 0);
          alert(error);
        }); // Todo:写异常返回代码;}
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
            // alert(Object.values(error.response.data.errors)[0]);
            // alert(error.response.data.message)
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
        localStorage.browse_logger = JSON.stringify(this.$store.state.User.BrowseLogger);
      }
      //读取localStorage的皮肤主题
      if (localStorage.getItem("theme") == null) {
        this.$store.commit("Theme_set", "hdao");
      } else {
        this.$store.commit("Theme_set", localStorage.theme);
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

      //读取是否屏蔽词忽略大小写
      if (localStorage.getItem("pingbici_ignore_case") != null) {
        this.$store.commit(
          "PingbiciIngnoreCase_set",
          localStorage.getItem("pingbici_ignore_case") === "true"
        );
      }

      //读取是否减少toast提示
      if (localStorage.getItem("less_toast") != null) {
        this.$store.commit(
          "LessToast_set",
          localStorage.getItem("less_toast") === "true"
        );
      }

      //读取是否遇到红包时停止自动涮锅
      if (localStorage.getItem("hongbao_then_stop") != null) {
        this.$store.commit(
          "HongbaoThenStop_set",
          localStorage.getItem("hongbao_then_stop") === "true"
        );
      }
      //读取是否自动涮锅时保持页面停止
      if (localStorage.getItem("listening_hold_page") != null) {
        this.$store.commit(
          "ListeningHoldPage_set",
          localStorage.getItem("listening_hold_page") === "true"
        );
      }

      //读取MyCss（自定义字体大小和行距等）
      if (localStorage.my_css != null) {
        this.$store.commit("MyCSS_set_all", JSON.parse(localStorage.my_css));

        if (this.$store.state.MyCSS.PostsMarginTop == null) {
          this.$store.commit("PostsMarginTop_set", 32); //临时的，给旧版本加默认值
        }
        if (this.$store.state.MyCSS.PostsMaxLine == null) {
          this.$store.commit("PostsMaxLine_set", 16); //临时的，给旧版本加默认值
        }
        if (this.$store.state.MyCSS.QuoteMax == null) {
          this.$store.commit("QuoteMax_set", 3); //临时的，给旧版本加默认值
        }
        if (this.$store.state.MyCSS.ThreadsPerPage == null) {
          this.$store.commit("ThreadsPerPage_set", 50); //临时的，给旧版本加默认值
        }
        if (this.$store.state.MyCSS.ThreadsMarginPaddingY == null) {
          this.$store.commit("ThreadsMarginPaddingY_set", 4); //临时的，给旧版本加默认值
        }
        const my_css = this.$store.state.MyCSS;
        localStorage.my_css = JSON.stringify(my_css);
      }

      //读取关注帖子的上次回帖数（用于新回复提醒）
      if (localStorage.focus_threads != null) {
        this.$store.commit(
          "FocusThreads_set_all",
          JSON.parse(localStorage.focus_threads)
        );
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
      this.$store.commit("UserLv_set", response_data.binggan.user_lv);
      // this.$store.commit("UserLvData_set", response_data.user_lv);
      this.$store.commit("UserCoin_set", response_data.binggan.coin);
      if (response_data.binggan.nickname) {
        this.$store.commit("NickName_set", response_data.binggan.nickname);
      } else {
        this.$store.commit("NickName_set", "= =");
      }
      if (response_data.pingbici) {
        this.$store.commit(
          "TitlePingbici_set",
          JSON.parse(response_data.pingbici.title_pingbici)
        );
        this.$store.commit(
          "ContentPingbici_set",
          JSON.parse(response_data.pingbici.content_pingbici)
        );
        this.$store.commit(
          "FjfPingbici_set",
          JSON.parse(response_data.pingbici.fjf_pingbici)
        );
      }
      if (response_data.my_emoji != null) {
        this.$store.commit("MyEmoji_set", JSON.parse(response_data.my_emoji));
      }

      if (response_data.my_battle_chara) {
        //自定义角色推入到共通角色组
        if (this.$store.state.User.CharaIndex[0].length > chara_index[0].length) {
          //如果已经存在自定义角色，则重新从头设定chara_index
          //避免自定义角色在执行user_data_refresh之后被重复录入
          //CharaIndex[0]是共通组角色
          this.$store.commit("CharaIndex_set", chara_index);
        }

        response_data.my_battle_chara.forEach((chara_data, index) => {
          console.log('CharaIndex_push_to_0')
          if (chara_data.not_use == false) {
            this.$store.commit("CharaIndex_push_to_0", { value: index + 240, text: chara_data.name }, 0);
          }//自定义角色从240开始
        });
      }
    },
  },
  created() {
    this.get_forums_data();
    this.get_user_data();

    let vm = this; //为了回调函数可以使用vue的方法
    this.$eventHub.$on("user_data_refresh", () => {
      vm.get_user_data(); //监听全局的需要刷新用户数据的需求
    });

    //把常用数据写入Vuex，变量来源于json/json.js下的定义
    this.$store.commit("Emojis_set", emoji_json);
    this.$store.commit("RandomHeads_set", random_heads_json);
    this.$store.commit("CharaIndex_set", chara_index);
    this.$store.commit("CharaGroupIndex_set", chara_group_index);
    this.$store.commit("Medals_set", medals);
    this.$store.commit("MedalsHide_set", medals_hide);

    this.set_LocalStorage(); //把LocalStorage变量存到Vuex

    //解决safari后退前进之后，confirm，alert及prompt不出现的问题
    window.onload = function () {
      setTimeout(function () {
        window.addEventListener("popstate", function () {
          window.location.reload();
        });
      }, 0);
    };
  },
};
</script>
