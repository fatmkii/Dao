<template>
  <div>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <b-form-checkbox class="mx-2 my-2" switch v-model="z_bar_left">
      侧边栏放左侧
    </b-form-checkbox>
    <b-form-checkbox class="mx-2 my-2" switch v-model="LessToast">
      <span v-b-popover.hover.bottom="'“已滚动到阅读进度”等'">减少弹窗提示</span>
    </b-form-checkbox>
    <b-form-checkbox class="mx-2 my-2" switch v-model="HongbaoThenStop">
      自动涮锅时遇红包停止
    </b-form-checkbox>
    <b-form-checkbox class="mx-2 my-2" switch v-model="ListeningHoldPage">
      自动涮锅时页面保持不动
    </b-form-checkbox>
    <b-form-checkbox class="mx-2 my-2" switch v-model="LoudspeakerMono">
      大喇叭保持单色
    </b-form-checkbox>
    <div class="hongbao_input">
      <b-input-group prepend="图床选择" class="mt-1" style="max-width: 300px">
        <b-form-select v-model="ImgHostSelected" :options="img_host_options"></b-form-select>
      </b-input-group>
      <b-input-group prepend="大喇叭" class="mt-1" style="max-width: 300px">
        <b-form-select v-model="LoudspeakerLocationSelected" :options="loudspeaker_options"></b-form-select>
      </b-input-group>
    </div>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">帖子内容行距：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="16" max="40"
        v-model="PostsLineHeight"></b-form-spinbutton>
      <span class="ml-1 mb-2">px</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">回复字体大小：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="10" max="24"
        v-model="PostsFontSize"></b-form-spinbutton>
      <span class="ml-1 mb-2">px</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">引用字体大小：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="10" max="24"
        v-model="QuoteFontSize"></b-form-spinbutton>
      <span class="ml-1 mb-2">px</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">楼层字体大小：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="10" max="24"
        v-model="SysInfoFontSize"></b-form-spinbutton>
      <span class="ml-1 mb-2">px</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">回复顶部留空：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="1" max="48"
        v-model="PostsMarginTop"></b-form-spinbutton>
      <span class="ml-1 mb-2">px</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">主题列表间距：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="0" max="12"
        v-model="ThreadsMarginPaddingY"></b-form-spinbutton>
      <span class="ml-1 mb-2">px（手机版）</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">回复多行折叠：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="3" max="32"
        v-model="PostsMaxLine"></b-form-spinbutton>
      <span class="ml-1 mb-2">行</span>
    </div>
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">回复最大引用：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" min="1" max="10"
        v-model="QuoteMax"></b-form-spinbutton>
      <span class="ml-1 mb-2">层</span>
    </div>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <div class="mt-2 d-flex align-items-center">
      <span class="mb-2">每页的主题数：</span>
      <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" step="5" min="30" max="100"
        v-model="ThreadsPerPage"></b-form-spinbutton>
      <span class="ml-1 mb-2">个</span>
    </div>
    <hr :class="is_mobile ? 'my-1' : ''" />
    <b-button :variant="button_theme" @click="set_MyCSS" :size="is_mobile ? 'sm' : 'md'">保存</b-button>
    <b-button variant="outline-dark" @click="default_MyCSS" :size="is_mobile ? 'sm' : 'md'">恢复默认</b-button>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "common_setting_tab",
  components: {},
  props: {},
  watch: {
    z_bar_left: function () {
      localStorage.setItem("z_bar_left", this.z_bar_left ? "true" : "");
      window.document.documentElement.setAttribute("z-bar-left", this.z_bar_left);
    },
  },
  data: function () {
    return {
      z_bar_left: false,
      img_host_options: [
        { value: 'mjj', text: "mjj" },
        { value: 'imgbb', text: "imgbb" },
        { value: 'freeimage', text: "freeimage" },
      ],
      loudspeaker_options: [
        { value: 'top', text: "放在上面" },
        { value: 'center', text: "放在中间" },
        { value: 'bottom', text: "放在底部" },
      ],
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    LessToast: {
      get() {
        return this.$store.state.User.LessToast;
      },
      set(value) {
        localStorage.setItem("less_toast", value ? "true" : "");
        this.$store.commit("LessToast_set", value);
      },
    },
    HongbaoThenStop: {
      get() {
        return this.$store.state.User.HongbaoThenStop;
      },
      set(value) {
        localStorage.setItem("hongbao_then_stop", value ? "true" : "");
        this.$store.commit("HongbaoThenStop_set", value);
      },
    },
    ListeningHoldPage: {
      get() {
        return this.$store.state.User.ListeningHoldPage;
      },
      set(value) {
        localStorage.setItem("listening_hold_page", value ? "true" : "");
        this.$store.commit("ListeningHoldPage_set", value);
      },
    },
    LoudspeakerMono: {
      get() {
        return this.$store.state.User.LoudspeakerMono;
      },
      set(value) {
        localStorage.setItem("loudspeaker_monochrome", value ? "true" : "");
        this.$store.commit("LoudspeakerMono_set", value);
      },
    },
    ImgHostSelected: {
      get() {
        return this.$store.state.User.ImgHost;
      },
      set(value) {
        localStorage.setItem("img_host", value);
        this.$store.commit("ImgHost_set", value);
      },
    },
    LoudspeakerLocationSelected: {
      get() {
        return this.$store.state.User.LoudspeakerLocation;
      },
      set(value) {
        localStorage.setItem("loudspeaker_location", value);
        this.$store.commit("LoudspeakerLocationS_set", value);
      },
    },

    PostsLineHeight: {
      get() {
        return this.$store.state.MyCSS.PostsLineHeight;
      },
      set(value) {
        this.$store.commit("PostsLineHeight_set", value);
      },
    },
    PostsFontSize: {
      get() {
        return this.$store.state.MyCSS.PostsFontSize;
      },
      set(value) {
        this.$store.commit("PostsFontSize_set", value);
      },
    },
    QuoteFontSize: {
      get() {
        return this.$store.state.MyCSS.QuoteFontSize;
      },
      set(value) {
        this.$store.commit("QuoteFontSize_set", value);
      },
    },
    SysInfoFontSize: {
      get() {
        return this.$store.state.MyCSS.SysInfoFontSize;
      },
      set(value) {
        this.$store.commit("SysInfoFontSize_set", value);
      },
    },
    PostsMarginTop: {
      get() {
        return this.$store.state.MyCSS.PostsMarginTop;
      },
      set(value) {
        this.$store.commit("PostsMarginTop_set", value);
      },
    },
    PostsMaxLine: {
      get() {
        return this.$store.state.MyCSS.PostsMaxLine;
      },
      set(value) {
        this.$store.commit("PostsMaxLine_set", value);
      },
    },
    QuoteMax: {
      get() {
        return this.$store.state.MyCSS.QuoteMax;
      },
      set(value) {
        this.$store.commit("QuoteMax_set", value);
      },
    },
    ThreadsPerPage: {
      get() {
        return this.$store.state.MyCSS.ThreadsPerPage;
      },
      set(value) {
        this.$store.commit("ThreadsPerPage_set", value);
      },
    },
    ThreadsMarginPaddingY: {
      get() {
        return this.$store.state.MyCSS.ThreadsMarginPaddingY;
      },
      set(value) {
        this.$store.commit("ThreadsMarginPaddingY_set", value);
      },
    },

  },
  created() {
    if (localStorage.getItem("z_bar_left") == null) {
      localStorage.z_bar_left = "";
    } else {
      this.z_bar_left = Boolean(localStorage.z_bar_left);
    }
  },
  methods: {
    set_MyCSS() {
      const my_css = this.$store.state.MyCSS;
      localStorage.my_css = JSON.stringify(my_css);
      this.$bvToast.toast("已保存", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
    default_MyCSS() {
      const my_css = {
        PostsLineHeight: 28,
        PostsFontSize: 16,
        QuoteFontSize: 16,
        SysInfoFontSize: 14,
        PostsMarginTop: 32,
        PostsMaxLine: 16,
        QuoteMax: 3,
        ThreadsPerPage: 50,
        ThreadsMarginPaddingY: 4,
      };
      this.$store.commit("MyCSS_set_all", my_css);
      this.set_MyCSS();
    },
  },
  activated() { },
};
</script>