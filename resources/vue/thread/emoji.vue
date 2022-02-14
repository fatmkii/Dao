<template>
  <div class="my-2">
    <div class="d-flex flex-wrap">
      <div
        class="emoji-nav-button"
        :class="{ active: emoji_show == index }"
        v-for="(emoji_data, index) in emojis_data"
        :key="index"
        @click="emoji_open(index)"
      >
        {{ emoji_data.name }}
      </div>
    </div>
    <div class="d-flex flex-wrap py-1" v-if="emoji_show != -1">
      <div
        class="emoji_container"
        v-for="(emoji_data, index) in emojis_data"
        :key="index"
        v-show="emoji_show == index"
      >
        <div
          class="emoji_box m-1 d-inline-flex"
          v-for="(emoji_src, index) in emoji_data.emojis"
          :key="index"
        >
          <b-img
            :src="emoji_src"
            fluid
            alt="Fluid-grow image"
            @click="emoji_click(emoji_src)"
          ></b-img>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
export default {
  components: {},
  props: {
    heads_id: {
      type: Number,
      default: 0,
    },
    emoji_auto_hide: {
      type: Boolean,
      default: true,
    },
  },
  data: function () {
    return {
      name: "emoji",
      emoji_show: -1,
    };
  },
  computed: {
    emojis_data() {
      var emojis = [];
      if (this.$store.state.User.Emojis.length > 0) {
        for (var i = 0; i < this.$store.state.User.Emojis.length; i++) {
          if (
            this.$store.state.User.Emojis[i].heads_id == this.heads_id ||
            this.$store.state.User.Emojis[i].heads_id == 0
          )
            emojis.push(this.$store.state.User.Emojis[i]);
        }
      }
      if (this.$store.state.User.MyEmoji) {
        emojis.push(this.$store.state.User.MyEmoji);
      }
      return emojis;
    },
  },
  methods: {
    emoji_click(emoji_src) {
      this.$emit("emoji_append", emoji_src);
      if (this.emoji_auto_hide) {
        this.emoji_show = -1;
      }
    },
    emoji_open(index) {
      if (this.emoji_show == index) {
        this.emoji_show = -1;
      } else {
        this.emoji_show = index;
      }
    },
  },
  created() {},
};
</script>