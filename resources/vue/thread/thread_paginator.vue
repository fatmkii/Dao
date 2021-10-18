
<template>
  <b-pagination-nav
    :number-of-pages="last_page"
    v-model="current_page"
    limit="10"
    :base-url="base_url"
    use-router
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
    last_page: Number,
    current_page: Number,
    align: String,
  },
  data: function () {
    return {
      name: "thread_paginator",
    };
  },
  computed: {
    base_url() {
      return "/thread/" + this.thread_id + "/";
    },
  },
};
</script>