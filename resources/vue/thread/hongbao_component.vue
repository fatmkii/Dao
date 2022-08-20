<template>
  <div class="hongbao-content align-items-center" v-if="!get_hongbao_data_handling">

    <div>
      <span @click="quote_click">红包关键词：“{{ hongbao_data.key_word }}”</span>
    </div>

    <div>
      <span>红包总olo：{{ hongbao_data.olo_total }}个</span>
    </div>
    <div>
      <span>剩余红包：（{{ hongbao_data.num_remains }} / {{ hongbao_data.num_total }}）</span>
    </div>
    <div v-if="hongbao_data.hongbao_user">
      <span>你抢到了{{ hongbao_data.hongbao_user.olo }}个olo！
      </span>
    </div>
    <div class="d-flex flex-wrap">
      <img src="/hongbao.svg" class="mt-1" v-for="i in num_remains_array" :key="i.key">
      <img src="/hongbao_disable.svg" class="mt-1" v-for="i in num_consumed_array" :key="i.key">
    </div>
  </div>
</template>


<script>
export default {
  name: "hongbao_component",
  props: {
    hongbao_id: {
      type: Number,
      default: 0,
    },
    admin_button_show: {
      type: Boolean,
      default: false,
    },
  },
  watch: {
    hongbao_id() {
      this.get_hongbao_data();
    },
  },
  data: function () {
    return {
      get_hongbao_data_handling: true,
      hongbao_data: {},
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    forum_id() {
      return this.$store.state.Forums.CurrentForumData.id;
    },
    num_remains_array() {
      return Array.from(Array(this.hongbao_data.num_remains), (v, k) => k)
    },
    num_consumed_array() {
      return Array.from(Array(this.hongbao_data.num_total - this.hongbao_data.num_remains), (v, k) => k)
    },
  },
  methods: {
    get_hongbao_data() {
      this.get_hongbao_data_handling = true;
      const config = {
        method: "get",
        url: "/api/hongbao/" + this.hongbao_id,
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.hongbao_data = response.data.hongbao;
            this.get_hongbao_data_handling = false;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    quote_click() {
      return this.$emit("quote_click", this.hongbao_data.key_word);
    },
  },
  created() {
    this.get_hongbao_data();
  },
};
</script>