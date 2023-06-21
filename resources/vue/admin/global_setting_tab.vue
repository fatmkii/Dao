<template>
  <div class="mx-2 my-2 binggan_custom_input" style="max-width: 400px;">
    <b-input-group prepend="领取饼干" class="mt-2">
      <b-form-select v-model="new_binggan_selected" :options="new_binggan_option"
        :disabled="get_data_handling != 2"></b-form-select>
    </b-input-group>
    <div class="mt-2">
      <b-button :variant="button_theme" :disabled="ajax_handling"
        @click="set_global_setting('new_binggan', new_binggan_selected)">提交
      </b-button>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "global_setting_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      ajax_handling: false,
      get_data_handling: 0,
      new_binggan_selected: false,
      new_binggan_option: [
        { text: '打开', value: true },
        { text: '关闭', value: false }
      ]
    }
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    ...mapState({

    }),
  },
  created() {
    this.get_global_setting()
  },
  methods: {
    get_global_setting(key = null) {
      this.get_data_handling = 1;
      const config = {
        method: "get",
        url: "/api/admin/global_setting",
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      if (key != null) {
        config.params.key = key
      }
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.new_binggan_selected = response.data.data.new_binggan
            this.get_data_handling = 2;
          } else {
            this.get_data_handling = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_data_handling = 0;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    set_global_setting(key, value) {
      this.ajax_handling = true;
      const config = {
        method: "post",
        url: "/api/admin/set_global_setting",
        data: {
          binggan: this.$store.state.User.Binggan,
          key: key,
          value: JSON.stringify(value)
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
            this.ajax_handling = false;
          } else {
            this.ajax_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.ajax_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  activated() { },
};
</script>