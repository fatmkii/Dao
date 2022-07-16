<template>
  <div
    class="crowd-content align-items-center"
    v-show="!get_crowd_data_handling"
  >
    <div class="gamble-title my-2" ref="gamble-title">
      <span style="word-wrap: break-word; white-space: normal"
        >众筹： {{ crowd_data.title }}
      </span>
    </div>

    <div class="d-flex crowd-progress">
      <span style="min-width: 100px" class="pl-1">{{
        crowd_data.olo_total
      }}</span>
      <b-progress
        class="w-75 ml-4 crowd"
        height="1.5rem"
        animated
        :value="crowd_data.olo_total"
        :max="crowd_data.olo_target"
      ></b-progress>
    </div>
    <div class="d-flex">
      <span style="min-width: 100px" class="pl-1"
        >目标金额：{{ crowd_data.olo_target }}</span
      >
      <span style="min-width: 100px" class="pl-2"
        >已完成：{{
          ((crowd_data.olo_total / crowd_data.olo_target) * 100).toFixed(1)
        }}%</span
      >
    </div>

    <div class="my-2">
      <b-input-group class="my-2" size="sm">
        <b-form-input
          placeholder="支持olo"
          style="max-width: 200px"
          v-model="crowd_olo"
        ></b-form-input>
        <b-button
          variant="success"
          size="sm"
          @click="new_crowd_handle"
          :disabled="
            new_crowd_handling == true ||
            crowd_end_flag ||
            crowd_data.is_closed != 0
          "
        >
          参与</b-button
        >
      </b-input-group>
      <b-button
        size="sm"
        variant="warning"
        v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
        v-show="admin_button_show"
        @click="crowd_repeal"
      >
        中止
      </b-button>
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="!crowd_end_flag && crowd_data.is_closed == 0"
        >此众筹将于 {{ crowd_TTL }} 后结束。</span
      >
    </div>
    <div class="my-2">
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="crowd_end_flag && crowd_data.is_closed == 0"
        >此众筹已过期</span
      >
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="crowd_data.is_closed == 1"
        >此众筹已成功达成！</span
      >
      <span
        class="ml-2"
        style="font-size: 0.875rem"
        v-if="crowd_data.is_closed == 2"
        >此众筹已被管理员中止，参与olo已退回。</span
      >
    </div>
    <table class="crowd_table">
      <thead>
        <tr class="text-center">
          <th width="50%">我支持的olo</th>
          <th width="50%">参与时间</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="record in user_crowd_records" :key="record.id">
          <td class="text-center">{{ record.olo }}</td>
          <td class="text-center">{{ record.created_at }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
export default {
  props: {
    crowd_id: {
      type: Number,
      default: 0,
    },
    admin_button_show: {
      type: Boolean,
      default: false,
    },
  },
  data: function () {
    return {
      name: "crowd_component",
      get_crowd_data_handling: true,
      new_crowd_handling: false,
      crowd_data: {},
      user_crowd_records: [],
      crowd_olo: undefined,
    };
  },
  computed: {
    crowd_TTL() {
      if (this.crowd_data.end_date === undefined) {
        return "";
      }
      const seconds =
        (Date.parse(this.crowd_data.end_date.replace(/-/g, "/") + " GMT+800") -
          Date.now()) /
        1000;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return hours + "小时" + minutes + "分钟";
    },
    crowd_end_flag() {
      if (this.crowd_data.end_date === undefined) {
        return false;
      }
      const seconds =
        (Date.parse(this.crowd_data.end_date.replace(/-/g, "/") + " GMT+800") -
          Date.now()) /
        1000;
      if (seconds > 0) {
        return false;
      } else {
        return true;
      }
    },
    forum_id() {
      return this.$store.state.Forums.CurrentForumData.id;
    },
  },
  methods: {
    get_crowd_data() {
      const config = {
        method: "get",
        url: "/api/crowds/" + this.crowd_id,
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.crowd_data = response.data.crowd;
            this.user_crowd_records = response.data.crowd_record;
            this.get_crowd_data_handling = false;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    new_crowd_handle() {
      if (!this.crowd_olo) {
        alert("请输入参与众筹的olo数量");
        return;
      }
      this.new_crowd_handling = true;
      const config = {
        method: "post",
        url: "/api/crowds/",
        data: {
          binggan: this.$store.state.User.Binggan,
          crowd_id: this.crowd_id,
          crowd_olo: this.crowd_olo,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.get_crowd_data();
            this.$parent.get_posts_data();
            this.new_crowd_handling = false;
          } else {
            alert(response.data.message);
            this.get_crowd_data();
            this.$parent.get_posts_data();
            this.new_crowd_handling = false;
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
          this.new_crowd_handling = false;
        });
    },
    crowd_repeal() {
      var confirmed = confirm("确定要中止此众筹吗？奥利奥将退回。");
      if (confirmed == false) {
        return;
      }
      const config = {
        method: "post",
        url: "/api/crowds/repeal",
        data: {
          binggan: this.$store.state.User.Binggan,
          crowd_id: this.crowd_id,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.get_crowd_data();
            this.$parent.get_posts_data();
            this.new_betting_handling = false;
          } else {
            alert(response.data.message);
            this.new_betting_handling = false;
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
          this.new_betting_handling = false;
        });
    },
  },
  created() {
    this.get_crowd_data();
  },
};
</script>