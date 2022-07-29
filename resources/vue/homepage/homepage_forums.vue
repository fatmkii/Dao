<template>
  <div>
    <b-card no-body class="mt-2" v-if="forums_data.favorited.length > 0">
      <template v-slot:header>
        <h5 class="mb-0">收藏板块</h5>
      </template>
      <b-list-group
        class="list-group-item-action"
        v-for="forum in forums_data.favorited"
        :key="forum.id"
        @click="forum_view(forum.id, $event)"
      >
        <b-card-body class="forums-card py-2">
          <b-card-title title-tag="span">
            <HomepageStar
              :forum_id="forum.id"
              :favorited="true"
              @favorite_add="favorite_add"
              @favorite_cancel="favorite_cancel"
            ></HomepageStar>
            {{ forum.name }}
            <b-badge variant="secondary" pill class="float-right">{{
              forum.id
            }}</b-badge>
          </b-card-title>
          <b-card-text>{{ forum.description }} </b-card-text>
        </b-card-body>
      </b-list-group>
    </b-card>

    <b-card no-body class="mt-2">
      <template v-slot:header>
        <h5 class="mb-0">板块列表</h5>
      </template>
      <b-list-group
        class="list-group-item-action"
        v-for="forum in forums_data.not_favorited"
        :key="forum.id"
        @click="forum_view(forum.id, $event)"
      >
        <b-card-body class="forums-card py-2">
          <b-card-title title-tag="span">
            <HomepageStar
              :forum_id="forum.id"
              :favorited="false"
              @favorite_add="favorite_add"
              @favorite_cancel="favorite_cancel"
            ></HomepageStar>
            {{ forum.name }}
            <b-badge variant="secondary" pill class="float-right">{{
              forum.id
            }}</b-badge>
          </b-card-title>
          <b-card-text>{{ forum.description }} </b-card-text>
        </b-card-body>
      </b-list-group>
    </b-card>
    <p class="my-2 ml-2">不同的食材，每样都很好吃。</p>
  </div>
</template>


<script>
import HomepageStar from "./homepage_star.vue";

export default {
  name: "homepage_forums",
  components: { HomepageStar },
  props: {},
  data() {
    return {
      forums_favorites: [],
    };
  },
  computed: {
    forums_data() {
      var data = {
        favorited: [],
        not_favorited: [],
      };
      this.$store.state.Forums.ForumsData.forEach((el) => {
        if (this.forums_favorites.includes(el.id)) {
          data.favorited.push(el);
        } else {
          data.not_favorited.push(el);
        }
      });
      return data;
    },
  },
  methods: {
    forum_view(forum_id, event) {
      if (["path", "svg"].includes(event.target.nodeName)) {
        return; //选中收藏星星就取消进入板块的动作
      }
      this.$router.push({ name: "forum", params: { forum_id: forum_id } });
    },
    favorite_add(forum_id) {
      if (!this.forums_favorites.includes(forum_id)) {
        this.forums_favorites.push(forum_id);
        localStorage.forums_favorites = JSON.stringify(this.forums_favorites);
      }
    },
    favorite_cancel(forum_id) {
      const index = this.forums_favorites.indexOf(forum_id);
      if (index != -1) {
        this.forums_favorites.splice(index, 1);
        localStorage.forums_favorites = JSON.stringify(this.forums_favorites);
      }
    },
  },
  created() {
    //读取localStorage的板块收藏记录
    if (localStorage.getItem("forums_favorites") == null) {
      localStorage.forums_favorites = JSON.stringify([]);
    } else {
      this.forums_favorites = JSON.parse(localStorage.forums_favorites);
    }
  },
};
</script>