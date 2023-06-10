<template>
  <div>
    <p class="my-2">说明：可通过支付奥利奥升级饼干，以增加屏蔽词或表情包容量。</p>
    <a href="https://oss.cpttmm.com/xhg_other/price_list.png" target="view_frame" class="my-2">饼干升级价目表</a>
    <p class="my-2">现在容量：(字符数)</p>
    <div v-for="(line, index) in user_lv_sheet" :key="index" class="my-2 d-flex align-items-center">
      <span style="min-width: 100px">{{ line.chinese }}</span>
      <b-form-input size="sm" style="max-width: 65px" disabled v-model="user_lv_data[line.name]"></b-form-input>
      <a href="javascript:;" class="ml-2" @click="user_lv_up_handle(line.name, line.chinese, line.price)"
        :disabled="ajax_handling">升级</a>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "user_lv_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      user_lv_sheet: [
        { name: "title_pingbici", chinese: "标题屏蔽词", price: 4000 },
        { name: "content_pingbici", chinese: "内容屏蔽词", price: 4000 },
        { name: "fjf_pingbici", chinese: "FJF黑名单", price: 4000 },
        { name: "my_emoji", chinese: "我的表情包", price: 20000 },
        { name: "my_battle_chara", chinese: "大乱斗角色", price: 50000 },
      ],
      ajax_handling: false,
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
      user_lv_data: (state) => state.User.UserLvData,
    }),
  },
  created() { },
  methods: {
    user_lv_up_handle(name, chinese_name, price) {
      var confirmed = confirm("要升级" + chinese_name + "吗？将会花费" + price + "个olo");
      if (confirmed == false) {
        return;
      }
      this.ajax_handling = true;
      const config = {
        method: "post",
        url: "/api/user/user_lv_up",
        data: {
          binggan: this.$store.state.User.Binggan,
          mode: name,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$eventHub.$emit("user_data_refresh"); //通过eventHub空vue对象分发事件
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.ajax_handling = false;
          } else {
            this.ajax_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.ajax_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },

  },
  activated() { },
};
</script>