
<template>
  <div class="d-none d-lg-block d-xl-block">
    <table v-show="threads_load_status" class="threads_table">
      <thead>
        <tr class="text-center">
          <th width="40%">标题</th>
          <th width="10%">作者</th>
          <th width="20%">发表时间</th>
          <th width="10%">回帖数</th>
          <th width="20%">最新回复</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="thread in threads_data" :key="thread.id">
          <td class="text-left" :style="{ color: thread.title_color }">
            <span class="thread_sub_title"> {{ thread.sub_title }}&nbsp; </span>
            <router-link
              class="thread_title"
              style="word-wrap: break-word; white-space: normal"
              :to="'/thread/' + thread.id + '/1'"
              :style="{ color: thread.title_color }"
              :target="router_target"
            >
              {{ thread.title }}&nbsp;&nbsp;
            </router-link>
            <span v-if="thread.locked_by_coin > 0"
              >🔒{{ thread.locked_by_coin }}</span
            >
            <router-link
              :to="
                '/thread/' + thread.id + '/' + Math.ceil(thread.posts_num / 200)
              "
              :target="router_target"
              v-if="thread.posts_num > 200"
              class="thread_page"
              >[{{ Math.ceil((thread.posts_num + 1) / 200) }}]
            </router-link>
          </td>
          <td class="text-center">{{ thread.nickname }}</td>
          <td class="text-center">{{ thread.created_at }}</td>
          <td class="text-center">{{ thread.posts_num }}</td>
          <td class="text-center">{{ thread.updated_at }}</td>
        </tr>
      </tbody>
    </table>
    <b-spinner
      class="spinner document-loading"
      v-show="!threads_load_status"
      label="读取中"
    >
    </b-spinner>
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
  components: {},
  props: {
    forum_id: Number,
    new_window_to_post: Boolean,
  },
  data: function () {
    return {
      name: "forum_threads",
    };
  },
  computed: {
    router_target() {
      return this.new_window_to_post == true ? "_blank" : "false";
    },
    threads_data() {
      if (this.threads_load_status) {
        if (this.$store.state.User.UsePingbici) {
          //处理屏蔽词
          const title_pingbici = JSON.parse(
            this.$store.state.User.TitlePingbici
          );
          return this.$store.state.Threads.ThreadsData.data.filter((thread) => {
            for (var i = 0; i < title_pingbici.length; i++) {
              var reg = new RegExp(title_pingbici[i], "g");
              if (reg.test(thread.title)) {
                return false;
              }
            }
            return true;
          });
        } else {
          return this.$store.state.Threads.ThreadsData.data; // 记得ThreadsData要比ForumsData多.threads_data，因为多了分页数据
        }
      }
    },
    ...mapState({
      threads_load_status: (state) => state.Threads.ThreadsLoadStatus,
      forum_is_nissin: (state) => state.Forums.CurrentForumData.is_nissin,
    }),
  },
  methods: {},
  created() {
    this.$store.commit("ThreadsLoadStatus_set", 0); //避免显示上个ThreadsData
  },
};
</script>