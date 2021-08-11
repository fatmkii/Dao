<template>
  <div>
    <p>这是children_com。来自父组件的信息：{{ message }}</p>
    <button v-on:click="check_DB">执行</button>
    <p>1版名称： {{ forum_name }}</p>
    <input
      v-model="value"
      @input="updateValue"
      placeholder="子组件的消息输入框"
    />
  </div>
</template>


<script>
export default {
  props: {
    message: {
      type: String,
      required: false,
    },
  },
  data: function () {
    return {
      name: "children_com",
      forum_name: "",
      value: "",
    };
  },
  methods: {
    check_DB: function () {
      var config = {
        method: "get",
        url: "/api/forums/",
        data: {
        },
      };
      // 发送 POST 请求
      axios(config).then(
        (response) => (
          (this.forum_name = response),
          this.$emit("input", "请求成功")
        )
      );
    },
    updateValue: function () {
      this.$emit("input", this.value);
    },
  },
};
</script>