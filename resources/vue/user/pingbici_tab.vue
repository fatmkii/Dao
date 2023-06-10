<template>
  <div class="mx-2 my-2">
    <p class="my-2">标题屏蔽词：（用英文逗号分隔）</p>
    <b-form-textarea id="title_pingbici_input" v-model="title_pingbici_input" rows="3" max-rows="20"></b-form-textarea>
    <p class="my-2">内容屏蔽词：（用英文逗号分隔）</p>
    <b-form-textarea id="content_pingbici_input" v-model="content_pingbici_input" rows="3"
      max-rows="20"></b-form-textarea>
    <p class="my-2">
      FJF小尾巴黑名单：（注意：同一个饼干在不同FJF主题中，小尾巴会不同）
    </p>
    <b-form-textarea id="fjf_pingbici_input" v-model="fjf_pingbici_input" rows="3" max-rows="20"></b-form-textarea>
    <div class="d-flex align-items-center mt-2">
      <a href="javascript:;" @click="pingbici_set_unique">去除重复的屏蔽词（之后要点提交喔）</a>
    </div>
    <div class="d-flex align-items-center mt-2">
      <b-button :size="is_mobile ? 'sm' : 'md'" :variant="button_theme" :disabled="pingbici_set_handling"
        @click="pingbici_set_handle">提交
      </b-button>
      <b-form-checkbox switch class="ml-2" v-model="UsePingbici" v-b-popover.hover.bottom="'切换后也要点击提交喔'">
        启用
      </b-form-checkbox>

      <b-form-checkbox class="ml-2" switch v-model="FoldPingbici" v-b-popover.hover.bottom="'这个保存在本地不用提交'">
        完全隐藏楼层
      </b-form-checkbox>
      <b-form-checkbox class="ml-2" switch v-model="PingbiciIngnoreCase" v-b-popover.hover.bottom="'不包括小尾巴'">
        忽略大小写
      </b-form-checkbox>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "pingbici_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      pingbici_set_handling: false,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    content_pingbici_input: {
      get() {
        return this.$store.state.User.ContentPingbici.join(", ");
      },
      set(value) {
        var input_arr = [];
        try {
          var arr = value
            .replace(/(\n|\r|\s)/g, "")
            .replace(/(，)/g, ",")
            .split(",");
          input_arr = arr.filter(function (el) {
            //去掉空元素
            return el;
          });
        } catch (e) {
          input_arr = [];
        } //没什么用，就是不想在输入过程中报错
        this.$store.commit("ContentPingbici_set", input_arr);
      },
    },
    title_pingbici_input: {
      get() {
        return this.$store.state.User.TitlePingbici.join(", ");
      },
      set(value) {
        var input_arr = [];
        try {
          var arr = value
            .replace(/(\n|\r|\s)/g, "")
            .replace(/(，)/g, ",")
            .split(",");
          input_arr = arr.filter(function (el) {
            //去掉空元素
            return el;
          });
        } catch (e) {
          input_arr = [];
        } //没什么用，就是不想在输入过程中报错
        this.$store.commit("TitlePingbici_set", input_arr);
      },
    },
    fjf_pingbici_input: {
      get() {
        return this.$store.state.User.FjfPingbici.join(", ");
      },
      set(value) {
        var input_arr = [];
        try {
          var arr = value
            .replace(/(\n|\r|\s)/g, "")
            .replace(/(，)/g, ",")
            .split(",");
          input_arr = arr.filter(function (el) {
            //去掉空元素
            return el;
          });
        } catch (e) {
          input_arr = [];
        } //没什么用，就是不想在输入过程中报错
        this.$store.commit("FjfPingbici_set", input_arr);
      },
    },
    FoldPingbici: {
      get() {
        return this.$store.state.User.FoldPingbici;
      },
      set(value) {
        localStorage.setItem("fold_pingbici", value ? "true" : "");
        this.$store.commit("FoldPingbici_set", value);
      },
    },
    PingbiciIngnoreCase: {
      get() {
        return this.$store.state.User.PingbiciIngnoreCase;
      },
      set(value) {
        localStorage.setItem("pingbici_ignore_case", value ? "true" : "");
        this.$store.commit("PingbiciIngnoreCase_set", value);
      },
    },
    UsePingbici: {
      get() {
        return this.$store.state.User.UsePingbici;
      },
      set(value) {
        this.$store.commit("UsePingbici_set", value);
      },
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
  },
  created() { },
  methods: {
    pingbici_set_handle() {
      if (
        this.$store.state.User.TitlePingbici.length == 0 ||
        this.$store.state.User.ContentPingbici.length == 0 ||
        this.$store.state.User.FjfPingbici.length == 0
      ) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      this.pingbici_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/pingbici_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          title_pingbici: JSON.stringify(this.$store.state.User.TitlePingbici),
          content_pingbici: JSON.stringify(this.$store.state.User.ContentPingbici),
          fjf_pingbici: JSON.stringify(this.$store.state.User.FjfPingbici),
          use_pingbici: this.UsePingbici,
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
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    pingbici_set_unique() {
      function unique(arr) {
        //去重函数
        return Array.from(new Set(arr));
      }
      if (
        this.$store.state.User.TitlePingbici.length == 0 ||
        this.$store.state.User.ContentPingbici.length == 0 ||
        this.$store.state.User.FjfPingbici.length == 0
      ) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      var arr_unique = [];
      arr_unique = unique(this.$store.state.User.TitlePingbici);
      this.title_pingbici_input = arr_unique.join(", ");

      arr_unique = unique(this.$store.state.User.ContentPingbici);
      this.content_pingbici_input = arr_unique.join(", ");

      arr_unique = unique(this.$store.state.User.FjfPingbici);
      this.fjf_pingbici_input = arr_unique.join(", ");

      this.$bvToast.toast("已去除重复的屏蔽词", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
  },
  activated() { },
};
</script>