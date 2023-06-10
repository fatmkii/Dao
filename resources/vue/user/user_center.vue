<template>
  <div>
    <p class="mt-2">你好！别来无恙。</p>
    <p @click="binggan_show = true" :class="is_mobile ? 'mb-1' : ''">
      你的饼干是：{{ binggan_show ? binggan : "*点击显示*" }}
    </p>
    <p :class="is_mobile ? 'mb-1' : ''">
      饼干等级是：Lv.
      {{ $store.state.User.UserDataLoaded == 2 ? user_lv : "读取中" }}
    </p>
    <p :class="is_mobile ? 'mb-1' : ''">
      你的奥利奥：{{ $store.state.User.UserDataLoaded == 2 ? user_coin : "读取中" }}
      个
    </p>
    <b-button :size="is_mobile ? 'sm' : 'md'" class="my-1 my-sm-0" variant="dark" @click="logout_handle">退出饼干</b-button>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <b-tabs pills :small="is_mobile">
      <b-tab title="我的成就">
        <hr :class="is_mobile ? 'my-1' : ''" />
        <MedalsTab></MedalsTab>
      </b-tab>
      <b-tab title="一般设定" lazy>
        <CommonSettingTab></CommonSettingTab>
      </b-tab>
      <b-tab title="屏蔽词" lazy>
        <PingbiciTab></PingbiciTab>
      </b-tab>
      <b-tab title="我的表情包" lazy>
        <MyEmojiTab></MyEmojiTab>
      </b-tab>
      <b-tab title="我的角色" lazy>
        <MyBattleCharaTab></MyBattleCharaTab>
      </b-tab>
      <b-tab title="收支记录" lazy>
        <IncomeTab></IncomeTab>
      </b-tab>
      <b-tab title="升级饼干" lazy>
        <UserLVTab></UserLVTab>
      </b-tab>
      <b-tab title="定制饼干" lazy>
        <CustomBingganTab></CustomBingganTab>
      </b-tab>
      <b-tab title="密码设定" lazy>
        <SetPasswordTab></SetPasswordTab>
      </b-tab>
    </b-tabs>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <router-link to="admin_center" tag="a" style="font-size: 0.875rem">
      管理
    </router-link>
  </div>
</template>

<script>
import { mapState } from "vuex";
import MedalsTab from "./medals_tab.vue";
import UserLVTab from "./user_lv_tab.vue";
import CommonSettingTab from "./common_setting_tab.vue";
import PingbiciTab from "./pingbici_tab.vue";
import IncomeTab from "./income_tab.vue";
import MyEmojiTab from "./my_emoji_tab.vue";
import CustomBingganTab from "./custom_binggan_tab.vue";
import SetPasswordTab from "./set_password_tab.vue";
import MyBattleCharaTab from "./my_battle_chara_tab.vue";

export default {
  name: "user_center",
  components: {
    MedalsTab, UserLVTab, CommonSettingTab, PingbiciTab,
    IncomeTab, MyEmojiTab, CustomBingganTab, SetPasswordTab,
    MyBattleCharaTab
  },
  props: {},
  watch: {

  },
  data: function () {
    return {
      binggan_show: false,
      from_path: "/",
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    ...mapState({
      login_status: (state) => state.User.LoginStatus,
      binggan: (state) => state.User.Binggan,
      user_coin: (state) => state.User.UserCoin,
      user_lv: (state) => state.User.UserLv,
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
        .catch((error) => {
          if (error.response.status === 401) {
            localStorage.clear("Binggan"); //如果是token错误的情况，退出饼干的时候也返回401。此时直接删除localStorage强制退出。
            localStorage.clear("Token");
            window.location.href = "/";
          }
        }); // Todo:写异常返回代码;
    },
  },
  created() {
    document.title = "个人中心";
  },
  mounted() {
    if (this.from_path != "/") {
      this.$eventHub.$emit("user_data_refresh"); //通过eventHub空vue对象分发事件
    }
  },
  activated() { },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      // 通过 `vm` 访问组件实例,from_path
      vm.from_path = from.path; //上一个路由的地址
    });
  },
};
</script>
