<template>
  <div>
    <p>发送ajax：</p>
    <button v-on:click="check_DB">执行</button>
    <p>1版名称： {{ forum_name }}</p>
  </div>
</template>


<script>
export default {
  data: function () {
    return {
      name: "axios_test",
      forum_name: "",
    };
  },
  methods: {
    check_DB: function () {
      const config = {
        method: "get",
        url: "/api/forums/1",
        data: {
          firstName: "Fred",
          lastName: "Flintstone",
        },
      };
      // 发送 POST 请求
      axios(config).then((response) => (this.forum_name = response.data.name));
    },
  },
};
</script>