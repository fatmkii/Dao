<template>
  <div>
    <HomepageBulletin></HomepageBulletin>
    <HomepageForums></HomepageForums>
  </div>
</template>


<script>
import HomepageForums from "./homepage_forums.vue";
import HomepageBulletin from "./homepage_bulletin.vue";

export default {
  components: { HomepageBulletin, HomepageForums },
  props: {},
  data: function () {
    return {
      name: "hompage",
    };
  },
  mounted() {
    document.title = "小火锅"; //设置浏览器页面标签文字
  },
};
</script>