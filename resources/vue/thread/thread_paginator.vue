
<template>
  <b-pagination-nav
    :number-of-pages="posts_last_page"
    limit="5"
    :base-url="base_url"
    use-router
    first-number
    last-number
    :align="align"
    class="my-2"
    size="sm"
  ></b-pagination-nav>
</template>


<script>
export default {
  components: {},
  props: {
    thread_id: Number,
    align: String,
  },
  data: function () {
    return {
      name: "thread_paginator",
    };
  },
  computed: {
    posts_last_page() {
      return this.$store.getters.PostsLastPage;
    },
    base_url() {
      return "/thread/" + this.thread_id + "/";
    },
  },
};
</script>