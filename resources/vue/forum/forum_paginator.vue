
<template>
  <div class="overflow-auto">
    <b-pagination-nav
      :number-of-pages="last_page"
      limit="5"
      :base-url="base_url"
      use-router
      first-number
      last-number
      size="sm"
    ></b-pagination-nav>
  </div>
</template>


<script>
export default {
  components: {},
  props: {
    forum_id: Number,
    last_page: Number,
  },
  data: function () {
    return {
      name: "forum_paginator",
    };
  },
  computed: {
    base_url() {
      return "/forum/" + this.forum_id + "/";
    },
  },
};
</script>