<script>
export default {
  data: function () {
    return {
      name: "imgtu",
    };
  },
  render(createElement) {
    return createElement("script", {
      attrs: {
        async: true,
        id: "chevereto-pup-src",
        src: "https://imgtu.com/sdk/pup.js",
        "data-url": "https://imgtu.com/upload",
        "data-auto-insert": "html-embed-medium",
      },
    });
  },
};
</script>