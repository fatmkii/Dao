<template>
  <div class="z-sidebar">
    <transition-group name="z-sidebar" tag="div" v-show="z_bar_show">
      <div class="icon-top" @click="scroll_icon_click('top')" key="icon-top">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrow-up"
          class="svg-inline--fa fa-arrow-up fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
          <path fill="currentColor"
            d="M34.9 289.5l-22.2-22.2c-9.4-9.4-9.4-24.6 0-33.9L207 39c9.4-9.4 24.6-9.4 33.9 0l194.3 194.3c9.4 9.4 9.4 24.6 0 33.9L413 289.4c-9.5 9.5-25 9.3-34.3-.4L264 168.6V456c0 13.3-10.7 24-24 24h-32c-13.3 0-24-10.7-24-24V168.6L69.2 289.1c-9.3 9.8-24.8 10-34.3.4z">
          </path>
        </svg>
      </div>
      <slot name="top"></slot>
      <div v-if="reload" class="icon-reload" @click="reload_handle" key="icon-reload">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="sync-alt"
          class="svg-inline--fa fa-sync-alt fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
          <path fill="currentColor"
            d="M370.72 133.28C339.458 104.008 298.888 87.962 255.848 88c-77.458.068-144.328 53.178-162.791 126.85-1.344 5.363-6.122 9.15-11.651 9.15H24.103c-7.498 0-13.194-6.807-11.807-14.176C33.933 94.924 134.813 8 256 8c66.448 0 126.791 26.136 171.315 68.685L463.03 40.97C478.149 25.851 504 36.559 504 57.941V192c0 13.255-10.745 24-24 24H345.941c-21.382 0-32.09-25.851-16.971-40.971l41.75-41.749zM32 296h134.059c21.382 0 32.09 25.851 16.971 40.971l-41.75 41.75c31.262 29.273 71.835 45.319 114.876 45.28 77.418-.07 144.315-53.144 162.787-126.849 1.344-5.363 6.122-9.15 11.651-9.15h57.304c7.498 0 13.194 6.807 11.807 14.176C478.067 417.076 377.187 504 256 504c-66.448 0-126.791-26.136-171.315-68.685L48.97 471.03C33.851 486.149 8 475.441 8 454.059V320c0-13.255 10.745-24 24-24z">
          </path>
        </svg>
      </div>
      <slot name="bottom"></slot>
      <div class="icon-down" @click="scroll_icon_click('bottom')" key="icon-down">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrow-down"
          class="svg-inline--fa fa-arrow-down fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 448 512">
          <path fill="currentColor"
            d="M413.1 222.5l22.2 22.2c9.4 9.4 9.4 24.6 0 33.9L241 473c-9.4 9.4-24.6 9.4-33.9 0L12.7 278.6c-9.4-9.4-9.4-24.6 0-33.9l22.2-22.2c9.5-9.5 25-9.3 34.3.4L184 343.4V56c0-13.3 10.7-24 24-24h32c13.3 0 24 10.7 24 24v287.4l114.8-120.5c9.3-9.8 24.8-10 34.3-.4z">
          </path>
        </svg>
      </div>
    </transition-group>
    <div class="icon-box" @click="z_bar_show = !z_bar_show">
      <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-stack" viewBox="0 0 16 16">
        <path
          d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.598.598 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.598.598 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.598.598 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535L7.733.063z" />
        <path
          d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.598.598 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.659z" />
      </svg>
    </div>
  </div>
</template>


<script>
export default {
  components: {},
  props: {
    reload: {
      type: Boolean,
      default: false,
    },
  },
  data: function () {
    return {
      name: "z_bar",
      z_bar_show: false,
    };
  },
  computed: {},
  methods: {
    scroll_icon_click(position) {
      switch (position) {
        case "top":
          window.scrollTo(0, 0);
          break;
        case "bottom":
          window.scrollTo(0, document.documentElement.scrollHeight);
          break;
      }
    },
    reload_handle() {
      // this.$eventHub.$emit("loudspeaker_refresh")//刷新大喇叭的全局事件
      this.$emit('reload')//往父组件传递reload时间（主题列表刷新或者回复刷新）
    }
  },
  created() { },
};
</script>