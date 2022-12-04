<template>
  <div>
    <span v-if="get_data_handling == 1">加载中……</span>
    <div
      v-if="get_data_handling == 2"
      class="medals_exhibit align-items-start d-flex flex-wrap"
    >
      <div
        class="medals_slot d-flex flex-column justify-content-center"
        v-for="(medal_data, medal_id) in medals_hide_data"
        :key="medal_id"
      >
        <img
          class="medals_img"
          v-if="has_medal(medal_id)"
          :src="medal_data.img"
          :alt="medal_data.name"
          @click="show_modal(medal_id, medal_data)"
        />
        <span class="text-center medals_name" v-if="has_medal(medal_id)">{{
          medal_data.name
        }}</span>
      </div>
    </div>
    <div
      v-if="get_data_handling == 2"
      class="medals_exhibit align-items-start d-flex flex-wrap"
    >
      <div
        class="medals_slot d-flex flex-column justify-content-center"
        v-for="(medal_data, medal_id) in medals_data"
        :key="medal_id"
      >
        <img
          class="medals_img"
          :src="
            has_medal(medal_id)
              ? medal_data.img
              : 'https://img.mjj.today/2022/11/28/928a2010793a8a157c491dd7d95b3aa4.png'
          "
          :alt="medal_data.name"
          @click="show_modal(medal_id, medal_data)"
        />
        <span class="text-center medals_name">{{ medal_data.name }}</span>
      </div>
    </div>

    <b-modal ref="medal_modal" id="medal_modal" hide-header>
      <template v-slot:default>
        <div
          class="medals_detail d-flex flex-column justify-content-center"
          v-if="get_progress_handling == 2"
        >
          <img
            class="medals_img_modal"
            :src="
              has_medal(show_medal_id)
                ? show_medal_data.img
                : 'https://img.mjj.today/2022/11/28/928a2010793a8a157c491dd7d95b3aa4.png'
            "
            :alt="show_medal_data.name"
          />
          <span class="text-center medals_describe">{{
            show_medal_data.name
          }}</span>
          <span class="text-center medals_describe">{{
            show_medal_data.describe
          }}</span>
          <span
            class="text-center medals_describe"
            v-if="medal_progress != null && medal_created_at == null"
            >{{ medal_progress }} / {{ medal_threshold }}</span
          >
          <span
            class="text-center medals_describe"
            v-if="medal_created_at != null"
            >获得时间: {{ medal_created_at }}</span
          >
        </div>
      </template>
      <template v-slot:modal-footer>
        <b-button variant="outline-secondary" @click="hide_modal()">
          关闭
        </b-button>
      </template>
    </b-modal>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "medals_tab",
  components: {},
  props: {},
  watch: {},
  data: function () {
    return {
      get_data_handling: 0,
      get_progress_handling: 0,
      has_medal_arr: [],
      show_medal_id: 0,
      show_medal_data: {},
      has_this_medal: false,
      medal_threshold: 0,
      medal_progress: 0,
      medal_created_at: null,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    has_medals_hide_data() {
      let medals = {};
      for (let medal_id in this.medals_hide_data) {
        if (this.has_medal_arr.includes(Number(medal_id))) {
          medals[medal_id] = this.medals_hide_data[medal_id];
        }
      }
      return medals;
    },
    ...mapState({
      medals_data: (state) => state.User.Medals,
      medals_hide_data: (state) => state.User.MedalsHide,
    }),
  },
  created() {
    this.get_user_medals();
  },
  methods: {
    get_user_medals() {
      this.get_data_handling = 1;
      const config = {
        method: "post",
        url: "/api/user/show_medals",
        data: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.has_medal_arr = response.data.data;
            this.get_data_handling = 2;
          } else {
            this.get_data_handling = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_data_handling = 0;
        });
    },
    get_medal_progress() {
      this.get_progress_handling = 1;
      const config = {
        method: "post",
        url: "/api/user/show_medal_progress",
        data: {
          binggan: this.$store.state.User.Binggan,
          medal_id: this.show_medal_id,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.medal_threshold = response.data.data.threshold;
            this.medal_progress = response.data.data.progress;
            this.medal_created_at = response.data.data.medal_created_at;
            this.get_progress_handling = 2;
          } else {
            this.get_progress_handling = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_progress_handling = 0;
        });
    },
    has_medal(medal_id) {
      // return true;
      return this.has_medal_arr.includes(Number(medal_id));
    },
    show_modal(medal_id, medal_data) {
      this.show_medal_id = medal_id;
      this.show_medal_data = medal_data;
      this.$refs["medal_modal"].show();
      this.get_medal_progress();
    },
    hide_modal() {
      this.get_progress_handling = 0;
      this.show_medal_data = {};
      this.show_medal_id = 0;
      this.medal_threshold = 0;
      this.medal_progress = 0;
      this.has_this_medal = false;
      this.$refs["medal_modal"].hide();
    },
  },
  activated() {
    this.get_user_medals();
  },
};
</script>