<template>
  <div>
    <b-card
      title="测试中"
      header-tag="header"
      class="mt-4"
      title-tag="h5"
      no-body
    >
      <template v-slot:header>
        <h5 class="mb-0">公告栏</h5>
      </template>

      <b-card-body class="bulletin-card py-2">
        <b-card-text>
          <p class="my-2">这里是小火锅, 是个人论坛。欢迎来玩~</p>
          <p class="my-2">QQ小火锅避难群：156840110</p>
          <p class="my-2">
            目前小火锅有5个网址：cpttmm.com、  www.cpttmm.com、  v2.cpttmm.com、  cpttmm.love、  www.cpttmm.love
          </p>
        </b-card-text>
      </b-card-body>
    </b-card>
  </div>
</template>


<script>
export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "homepage_bulletin",
    };
  },
  methods: {},
};
</script>