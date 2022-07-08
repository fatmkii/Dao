<template>
  <div>
    <b-navbar>
      <b-navbar-nav class="align-middle">
        <b-img
          fluid
          src="/favicon2.png"
          alt="icon"
          class="favicon"
          @click="$router.push('/')"
        ></b-img>
        <router-link to="/" tag="a" style="font-size: 1.25rem" class="ml-1"
          >小火锅</router-link
        >
      </b-navbar-nav>
      <b-navbar-nav class="ml-auto">
        <b-dropdown
          id="skin_button"
          size="md"
          text="皮肤"
          class="my-1 mr-1 d-none d-lg-block"
          variant="outline-dark"
        >
          <b-dropdown-item @click="theme_set('hdao')">
            <b-badge pill style="background-color: #28a745">&nbsp;</b-badge>
            H岛绿
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('night')">
            <b-badge pill style="background-color: #1c424a">&nbsp;</b-badge>
            夜间黑
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('gray')">
            <b-badge pill style="background-color: #eeeeee">&nbsp;</b-badge>
            Word灰
          </b-dropdown-item>
          <!-- <b-dropdown-item @click="theme_set('nga')">
            <b-badge pill style="background-color: #ffedc3">&nbsp;</b-badge>
            NGA黄
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('pink')">
            <b-badge pill style="background-color: #f9ccde">&nbsp;</b-badge>
            QNY粉
          </b-dropdown-item>-->
        </b-dropdown>
        <b-dropdown
          id="skin_button"
          size="sm"
          text="皮肤"
          class="my-1 mr-1 d-lg-none"
          variant="outline-dark"
        >
          <b-dropdown-item @click="theme_set('hdao')">
            <b-badge pill style="background-color: #28a745">&nbsp;</b-badge>
            H岛绿
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('night')">
            <b-badge pill style="background-color: #1c424a">&nbsp;</b-badge>
            夜间黑
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('gray')">
            <b-badge pill style="background-color: #eeeeee">&nbsp;</b-badge>
            Word灰
          </b-dropdown-item>
          <!-- <b-dropdown-item @click="theme_set('nga')">
            <b-badge pill style="background-color: #ffedc3">&nbsp;</b-badge>
            NGA黄
          </b-dropdown-item>
          <b-dropdown-item @click="theme_set('pink')">
            <b-badge pill style="background-color: #f9ccde">&nbsp;</b-badge>
            QNY粉
          </b-dropdown-item> -->
        </b-dropdown>
        <b-button
          v-if="login_status"
          size="sm"
          class="my-1 d-lg-none"
          variant="outline-dark"
          to="/user-center"
        >
          <b-icon icon="gear-fill"></b-icon>
          个人中心</b-button
        >
        <b-button
          v-else
          size="sm"
          class="my-1 d-lg-none"
          variant="outline-success"
          to="/login"
          >导入饼干</b-button
        >
        <b-button
          v-if="login_status"
          size="md"
          class="my-1 d-none d-lg-block"
          variant="outline-dark"
          to="/user-center"
        >
          <b-icon icon="gear-fill"></b-icon>
          个人中心</b-button
        >
        <b-button
          v-else
          size="md"
          class="my-1 d-none d-lg-block"
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
  methods: {
    theme_set(theme_name) {
      this.$store.commit("Theme_set", theme_name);
    },
  },
};
</script>