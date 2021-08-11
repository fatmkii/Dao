<template>
  <div>
    <b-navbar>
      <b-navbar-nav>
        <b-navbar-brand href="/"> 岛2.0 </b-navbar-brand>
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <b-button
          v-if="login_status"
          size="sm"
          class="my-1 my-sm-0 d-lg-none"
          variant="outline-dark"
          to="/user-center"
        >
          <b-icon icon="gear-fill"></b-icon>
          个人中心</b-button
        >
        <b-button
          v-else
          size="sm"
          class="my-1 my-sm-0 d-lg-none"
          variant="outline-success"
          to="/login"
          >导入饼干</b-button
        >
        <b-button
          v-if="login_status"
          size="md"
          class="my-1 my-sm-0 d-none d-lg-block"
          variant="outline-dark"
          to="/user-center"
        >
          <b-icon icon="gear-fill"></b-icon>
          个人中心</b-button
        >
        <b-button
          v-else
          size="md"
          class="my-1 my-sm-0 d-none d-lg-block"
          variant="outline-success"
          to="/login"
          >导入饼干</b-button
        >
      </b-navbar-nav>
    </b-navbar>
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
  data: function () {
    return {
      name: "navigation",
    };
  },
  computed: mapState({
    login_status: (state) => state.User.LoginStatus,
  }),
  methods: {},
};
</script>

<style lang="sass" scoped>
nav
  background-color: #eee
  a
    color: #444
</style>