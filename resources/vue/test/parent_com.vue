<template>
  <div>
    这是parent_com。来自子组件的信息：{{ child_message }}
    <br />
    <children_com
      v-model="child_message"
      message="发送axios吧"
    ></children_com>
  </div>
</template>


<script>
import children_com from "./children_com.vue";

export default {
  components: {
    children_com,
  },
  props: {

  },
  data: function () {
    return {
      name: "parent_com",
      child_message: "",
    };
  },
  methods: {
    show_children_message: function (message) {
      this.child_message = message;
    },
  },
};
</script>