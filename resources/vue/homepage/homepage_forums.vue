<template>
  <div>
    <b-card no-body class="mt-4">
      <template v-slot:header>
        <h5 class="mb-0">版面列表</h5>
      </template>

      <b-list-group
        class="list-group-item-action"
        v-for="forum in forums_data"
        :key="forum.id"
        @click="forum_view(forum.id)"
      >
        <b-card-body class="forums-card py-2">
          <b-card-title title-tag="h5">
            {{ forum.name }}
            <b-badge variant="secondary" pill class="float-right">{{
              forum.id
            }}</b-badge>
          </b-card-title>
          <b-card-text>{{ forum.description }} </b-card-text>
        </b-card-body>
      </b-list-group>
    </b-card>
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
  components: {},
  props: {},
  data: function () {
    return {
      name: "homepage_forums",
    };
  },
  computed: mapState({
    forums_data: (state) => state.Forums.ForumsData,
  }),
  methods: {
    forum_view: function (forum_id) {
      this.$router.push({ name: "forum", params: { forum_id: forum_id } });
    },
  },
  created: function () {},
};
</script>