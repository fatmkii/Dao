<template>
  <div v-if="this_show">
    <p class="mt-2">管理员中心</p>
    <p>我的饼干是：{{ this.$store.state.User.Binggan }}</p>
    <p>管理的板块：{{ this.$store.state.User.AdminForums }}</p>
    <hr />
    <p class="mt-2">管理员操作面板</p>
    <b-tabs pills>
      <b-tab title="版头设定">
        <div class="mx-2 my-2">
          <p class="my-2">在做了在做了</p>
        </div>
      </b-tab>
    </b-tabs>
    <hr />
    <p class="mt-2" v-if="this.$store.state.User.AdminStatus == 99">
      超管操作面板
    </p>
    <b-tabs pills v-if="this.$store.state.User.AdminStatus == 99">
      <b-tab title="发帖记录">
        <div class="mx-2 my-2">
          <router-link to="/admin/check_user_post">
            查询用户发帖记录
          </router-link>
        </div>
      </b-tab>
      <b-tab title="用户操作记录">
        <div class="mx-2 my-2">
          <p class="my-2">在做了在做了</p>
        </div>
      </b-tab>
      <b-tab title="设定表情包">
        <div class="mx-2 my-2">
          <p class="my-2">在做了在做了</p>
        </div>
      </b-tab>
    </b-tabs>
  </div>
</template>


<script>
export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "admin_center",
    };
  },
  computed: {
    this_show() {
      if (this.$store.state.User.UserDataLoaded == 2) {
        if (this.$store.state.User.AdminStatus == 99) {
          return true;
        } else {
          this.$router.replace("/admin_login");
        }
      } else {
        return false;
      }
    },
  },
  mounted() {
    document.title = "管理员中心";
  },
  methods: {},
  created() {},
};
</script>