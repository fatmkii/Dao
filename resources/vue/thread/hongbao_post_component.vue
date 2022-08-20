<template>
  <div class="hongbao-content align-items-center">

    <div>
      <span @click="quote_click">红包口令：“{{ hongbao_data.key_word }}”</span>
    </div>

    <div>
      <span>红包总olo：{{ hongbao_data.olo_total }}个</span>
    </div>
    <div>
      <span>剩余红包：（{{ hongbao_data.num_remains }} / {{ hongbao_data.num_total }}）</span>
    </div>
    <div v-if="hongbao_data.hongbao_user">
      <span>你抢到了{{ hongbao_data.hongbao_user.olo }}个olo！
      </span>
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
    return {

    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
  },
  methods: {
    quote_click() {
      return this.$emit("quote_click", this.hongbao_data.key_word);
    },
  },
};
</script>