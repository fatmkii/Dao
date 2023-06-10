<template>
  <div class="mx-2 my-2">
    <p class="my-2">我的表情包：（用英文的逗号分隔）</p>
    <b-form-textarea id="my_emoji_input" v-model.lazy="my_emoji_input" rows="3" max-rows="8"></b-form-textarea>
    <div class="emoji_box m-1 d-inline-flex" v-for="(emoji_src, index) in $store.state.User.MyEmoji.emojis" :key="index">
      <b-img :src="emoji_src" fluid alt="emoji" @click="emoji_delete(index)"></b-img>
    </div>
    <div class="d-flex align-items-center mt-2">
      <a href="javascript:;" @click="my_emoji_set_unique">去除重复的表情包（之后要点提交喔）</a>
    </div>
    <div class="d-flex align-items-center mt-2">
      <b-button :size="is_mobile ? 'sm' : 'md'" :variant="button_theme" :disabled="my_emoji_set_handling"
        @click="my_emoji_set_handle">提交
      </b-button>
      <b-form-checkbox switch class="ml-2" v-model="emoji_delete_mode" v-b-popover.hover.bottom="'整理后记得提交喔'">
        {{ this.emoji_delete_mode ? "点击表情包可删除" : "整理表情包" }}
      </b-form-checkbox>
    </div>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "my_emoji_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      my_emoji_set_handling: false,
      emoji_delete_mode: false,
      
    }
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    my_emoji_input: {
      get() {
        return this.$store.state.User.MyEmoji.emojis.join(",\n");
      },
      set(value) {
        var input_arr = [];
        try {
          var arr = value
            .replace(/(\n|\r|\s)/g, "")
            .replace(/(，)/g, ",")
            .split(",");
          input_arr = arr.filter(function (el) {
            //去掉空元素
            return el;
          });
        } catch (e) {
          input_arr = [];
        } //没什么用，就是不想在输入过程中报错
        this.$store.commit("MyEmoji_set", input_arr);
      },
    },
    ...mapState({
      my_emoji: (state) => state.User.MyEmoji.emojis,
    }),
  },
  created() { },
  methods: {
    my_emoji_set_handle() {
      if (this.$store.state.User.MyEmoji.emojis.length == 0) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      this.my_emoji_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/my_emoji_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          my_emoji: JSON.stringify(this.$store.state.User.MyEmoji.emojis),
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
            this.my_emoji_set_handling = false;
          } else {
            this.my_emoji_set_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.my_emoji_set_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    my_emoji_set_unique() {
      function unique(arr) {
        //去重函数
        return Array.from(new Set(arr));
      }
      if (this.$store.state.User.MyEmoji.emojis.length == 0) {
        alert("表情包格式输入有误、或者是空的，请检查");
        return;
      }
      var arr_unique = unique(this.$store.state.User.MyEmoji.emojis);
      this.$store.commit("MyEmoji_set", arr_unique);
      this.my_emoji_input = this.my_emoji.join(",\n");
      this.$bvToast.toast("已去除重复的表情包", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
    emoji_delete(index) {
      if (this.emoji_delete_mode) {
        this.$store.state.User.MyEmoji.emojis.splice(index, 1);
        this.my_emoji_input = this.my_emoji.join(",\n");
      }
    },

  },
  mounted() {

  },
};
</script>