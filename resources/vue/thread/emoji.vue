<template>
  <b-tabs
    class="emoji_tabs my-2"
    content-class="py-2 px2 "
    style="font-size: 0.875rem"
    no-fade
    pills
  >
    <b-tab
      id="placeholder"
      title-link-class="placeholder"
      active
      title="多余占位"
    ></b-tab>
    <b-tab
      class="emoji_container"
      :title="emoji_data.name"
      v-for="emoji_data in emojis_data"
      :key="emoji_data.index"
      lazy
      @click="emoji_open()"
      ><div v-show="emoji_show">
        <div
          class="emoji_box m-1 d-inline-flex"
          v-for="emoji_src in JSON.parse(emoji_data.emojis)"
          :key="emoji_src.index"
        >
          <b-img
            :src="emoji_src"
            fluid
            alt="Fluid-grow image"
            @click="emoji_click(emoji_src)"
          ></b-img>
        </div>
      </div>
    </b-tab>
  </b-tabs>
</template>


<script>
export default {
  components: {},
  props: {
    heads_id: {
      type: Number,
      default: 0,
    },
  },
  data: function () {
    return {
      name: "emoji",
      emoji_show: false,
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
    },
    emoji_open() {
      this.emoji_show = true;
    },
  },
  created() {},
};
</script>

<style lang="scss">
.nav {
  background-color: #eefaee;
}
.nav-link {
  padding: 0.5rem 0.75rem;
}
.nav-link.active {
  background-color: #28a745 !important;
}

.emoji_box {
  max-width: 48px;
  max-height: 48px;
}
.emoji_container {
  max-height: 250px;
  overflow-y: auto;
}
#placeholder___BV_tab_button__ {
  display: none !important;
}
</style>