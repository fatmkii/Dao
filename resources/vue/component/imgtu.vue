<template>
  <b-button v-show="this.forum_id !== 419 && this.forum_id !== 0" variant="outline-dark" size="sm"
    data-target="#content_input" id="upload_button">上传图片</b-button>
</template>

<script>
import { mapState } from "vuex";
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
  computed: {
    ...mapState({
      img_host: (state) => state.User.ImgHost
    }),
  },
  methods: {
    set_imgupload_plugin(station) {
      //手动载入插图插件
      var body = document.getElementsByTagName("body")[0];
      var pup = document.createElement("script");
      var button = document.getElementById('upload_button');
      if (station == 'mjj') {
        pup.setAttribute("async", true);
        pup.setAttribute("src", "https://mjj.today/sdk/pup.js");
        pup.setAttribute("data-url", "https://mjj.today/upload");
        pup.setAttribute("data-auto-insert", "html-embed");
        button.setAttribute("data-chevereto-pup-trigger", "")
      }
      if (station == 'imgbb') {
        pup.setAttribute("async", true);
        pup.setAttribute("src", "https://imgbb.com/upload.js");
        pup.setAttribute("data-url", "https://imgbb.com/upload");
        pup.setAttribute("data-auto-insert", "html-embed-medium");
        button.setAttribute("data-imgbb-trigger", "")
      }
      body.appendChild(pup);
    },
  },
  created() { },
  mounted() {
    this.set_imgupload_plugin(this.img_host);
  },
};
</script>