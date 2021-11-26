<template>
  <div class="battle-item">
    <div class="mx-2 mt-2 mb-3">
      <div class="d-flex align-items-center my-1">
        <img
          class="emoji_img"
          src="https://www.z4a.net/images/2021/11/26/QQ20210513185124.gif"
        />
        <span class="ml-1">这是一段文字</span>
      </div>

      <div class="d-flex align-items-center my-1">
        <span class="ml-auto" style="font-size: 0.875rem"
          >这是一段文字这是一段文字这是一段文字这是一段文字这是一段文字这是一段文字</span
        >
        <img
          class="emoji_img ml-1"
          src="https://www.z4a.net/images/2021/11/26/10479d4fc93c7aa31a.jpg"
        />
      </div>
    </div>
    <div class="d-flex align-items-center justify-content-center my-1">
      <b-button variant="success" size="sm" class="">确定</b-button>
    </div>
  </div>
</template>


<script>
export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "battle",
    };
  },
  computed: {},
  methods: {},
  created() {},
};
</script>