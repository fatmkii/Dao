
<template>
  <div class="d-none d-lg-block d-xl-block">
    <b-table-simple
      v-show="threads_load_status"
      hover
      small
      fixed
      caption-top
      responsive
      class="threads_table table-striped"
    >
      <b-thead head-variant="light">
        <b-tr class="text-center">
          <b-th width="40%">标题</b-th>
          <b-th width="10%">作者</b-th>
          <b-th width="20%">发表时间</b-th>
          <b-th width="10%">回帖数</b-th>
          <b-th width="20%">最新回复</b-th>
        </b-tr>
      </b-thead>
      <b-tbody>
        <b-tr v-for="thread in threads_data" :key="thread.id">
          <b-td class="text-left" :style="{ color: thread.title_color }">
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
              style="color: #212529"
              >[{{ Math.ceil((thread.posts_num + 1) / 200) }}]
            </router-link>
          </b-td>
          <b-td class="text-center">{{ thread.nickname }}</b-td>
          <b-td class="text-center">{{ thread.created_at }}</b-td>
          <b-td class="text-center">{{ thread.posts_num }}</b-td>
          <b-td class="text-center">{{ thread.updated_at }}</b-td>
        </b-tr>
      </b-tbody>
    </b-table-simple>
    <b-spinner
      id="spinner"
      v-show="!threads_load_status"
      variant="success"
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

<style lang="scss" scoped>
.threads_table {
  background-color: white;
}
#spinner {
  position: fixed;
  z-index: 999;
  width: 3rem;
  height: 3rem;
  right: 50%;
  top: 40%;
}
</style>>

