<template>
  <div class="hongbao-content align-items-center">
    <div>
      <span @click="quote_click">红包口令：“{{ hongbao_data.key_word }}”</span>
    </div>
    <div>
      <span>红包总olo：{{ olo_total }}（{{ hongbao_type_text }}）</span>
    </div>
    <div>
      <span
        >剩余红包：（{{ hongbao_data.num_remains }} /
        {{ hongbao_data.num_total }}）</span
      >
    </div>
    <div v-if="hongbao_data.hongbao_user">
      <span>你抢到了{{ hongbao_data.hongbao_user.olo }}个olo！ </span>
    </div>
  </div>
</template>


<script>
export default {
  name: "hongbao_post_component",
  props: {
    hongbao_data: {
      type: Object,
      default: {},
    },
  },
  data: function () {
    return {};
  },
  computed: {
    olo_total() {
      if (this.hongbao_data.olo_hide) {
        return "（已隐藏olo总额）";
      } else {
        return this.hongbao_data.olo_total + "个";
      }
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    hongbao_type_text() {
      if (this.hongbao_data) {
        if (this.hongbao_data.type == 1) {
          return "随机红包";
        } else if (this.hongbao_data.type == 2) {
          return "定额红包";
        }
      } else {
        return "";
      }
    },
  },
  methods: {
    quote_click() {
      const keyword_prefix = "--红包口令: "; //为了方便前端识别并屏蔽，增加前缀
      return this.$emit(
        "quote_click",
        keyword_prefix + this.hongbao_data.key_word
      );
    },
  },
};
</script>