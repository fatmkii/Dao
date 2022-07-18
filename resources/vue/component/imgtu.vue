<template>
  <b-button
    v-show="this.forum_id !== 419 && this.forum_id !== 0"
    variant="outline-dark"
    size="sm"
    data-chevereto-pup-trigger
    data-target="#content_input"
    id="upload_button"
    >上传图片</b-button
  >
</template>

<script>
export default {
  name: "imgtu",
  props: {
    forum_id: {
      type: Number,
      default: 1,
    },
  },
  data: function () {
    return {};
  },
  methods: {
    set_imgupload_plugin() {
      //手动载入插图插件
      var body = document.getElementsByTagName("body")[0];
      var pup = document.createElement("script");
      pup.setAttribute("async", true);
      pup.setAttribute("src", "https://mjj.today/sdk/pup.js");
      pup.setAttribute("data-url", "https://mjj.today/upload");
      pup.setAttribute("data-auto-insert", "html-embed");
      body.appendChild(pup);
    },
  },
  created() {
  },
  mounted() {
    this.set_imgupload_plugin();
  },
};
</script>