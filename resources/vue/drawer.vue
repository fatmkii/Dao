<template>
  <!-- 来源https://codepen.io/576/pen/yqaxYw -->
  <div class="inner-draw">
    <div
      id="canvas-container"
      :style="{ width: canvas_width + 'px', height: canvas_height + 'px' }"
    >
      <canvas
        id="drawer-canvas-background"
        ref="drawer-canvas-background"
        :width="canvas_width"
        :height="canvas_height"
        @touchmove.prevent
      >
      </canvas>
      <canvas
        id="drawer-canvas"
        ref="drawer-canvas"
        :class="{ painting: !dragMode, dragging: dragMode }"
        :width="canvas_width"
        :height="canvas_height"
        @touchmove.prevent
        @mousedown="canvasDown($event)"
        @mouseup="canvasUp($event)"
        @mousemove="canvasMove($event)"
        @touchstart="canvasDown($event)"
        @touchend="canvasUp($event)"
        @touchmove="canvasMove($event)"
      >
      </canvas>
    </div>
    <div id="drawer-control" class="d-flex flex-row px-2">
      <!--画笔颜色-->
      <div id="canvas-color" class="mx-1 text-center">
        <div>颜色</div>
        <input type="color" v-model="config.lineColor" />
      </div>
      <!--画笔-->
      <div id="canvas-brush" class="mx-1 text-center">
        <div>大小</div>
        <span
          class="mx-2"
          v-for="(pen, index) in brushs"
          :key="index"
          :style="{ fontSize: pen.font_size + 'px' }"
          @click="setBrush(pen.lineWidth)"
          >{{ pen.innerHTML }}</span
        >
      </div>
      <!--操作-->
      <div id="canvas-control" class="mx-1 text-center">
        <div>操作</div>
        <span
          v-for="(control, index) in controls"
          :key="index"
          :title="control.title"
          :class="['mx-2', control.className]"
          v-html="control.innerHTML"
          @click="controlCanvas(control.action)"
        ></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data: function () {
    return {
      name: "drawer",
      canvas: Object,
      canvas_background: Object,
      background_img: Image,

      background_img_size: {
        x: 0,
        y: 0,
        width: 0,
        height: 0,
      },
      brushs: [
        {
          lineWidth: 3,
          innerHTML: "🖌️",
          font_size: 12,
        },
        {
          lineWidth: 6,
          innerHTML: "🖌️",
          font_size: 16,
        },
        {
          lineWidth: 12,
          innerHTML: "🖌️",
          font_size: 20,
        },
      ],
      context: {},
      background_context: {},
      canvasMoveUse: false,
      // 存储当前表面状态数组-上一步
      preDrawAry: [],
      // 存储当前表面状态数组-下一步
      nextDrawAry: [],
      // 中间数组
      middleAry: [],
      // 配置参数
      config: {
        lineWidth: 3,
        lineColor: "#111111",
        shadowBlur: 1,
      },
      eraserMode: false,
      dragMode: false,
      lastX: 0,
      lastY: 0,
    };
  },
  computed: {
    controls() {
      return [
        {
          title: "上一步",
          action: "prev",
          className: this.preDrawAry.length ? "active" : "",
          innerHTML:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/></svg>',
        },
        {
          title: "下一步",
          action: "next",
          className: this.nextDrawAry.length ? "active" : "",
          innerHTML:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-right-circle" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/></svg>',
        },
        {
          title: "橡皮擦",
          action: "eraser",
          className:
            this.preDrawAry.length || this.nextDrawAry.length ? "active" : "",
          innerHTML:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eraser-fill" viewBox="0 0 16 16">  <path d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828l6.879-6.879zm.66 11.34L3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293l.16-.16z"/></svg>',
        },
        {
          title: "清除",
          action: "clear",
          className:
            this.preDrawAry.length || this.nextDrawAry.length ? "active" : "",
          innerHTML:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">  <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg>',
        },
        {
          title: "拖动背景",
          action: "drag",
          className: this.dragMode ? "active" : "",
          innerHTML:
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-hand-index" viewBox="0 0 16 16"> <path d="M6.75 1a.75.75 0 0 1 .75.75V8a.5.5 0 0 0 1 0V5.467l.086-.004c.317-.012.637-.008.816.027.134.027.294.096.448.182.077.042.15.147.15.314V8a.5.5 0 1 0 1 0V6.435a4.9 4.9 0 0 1 .106-.01c.316-.024.584-.01.708.04.118.046.3.207.486.43.081.096.15.19.2.259V8.5a.5.5 0 0 0 1 0v-1h.342a1 1 0 0 1 .995 1.1l-.271 2.715a2.5 2.5 0 0 1-.317.991l-1.395 2.442a.5.5 0 0 1-.434.252H6.035a.5.5 0 0 1-.416-.223l-1.433-2.15a1.5 1.5 0 0 1-.243-.666l-.345-3.105a.5.5 0 0 1 .399-.546L5 8.11V9a.5.5 0 0 0 1 0V1.75A.75.75 0 0 1 6.75 1zM8.5 4.466V1.75a1.75 1.75 0 1 0-3.5 0v5.34l-1.2.24a1.5 1.5 0 0 0-1.196 1.636l.345 3.106a2.5 2.5 0 0 0 .405 1.11l1.433 2.15A1.5 1.5 0 0 0 6.035 16h6.385a1.5 1.5 0 0 0 1.302-.756l1.395-2.441a3.5 3.5 0 0 0 .444-1.389l.271-2.715a2 2 0 0 0-1.99-2.199h-.581a5.114 5.114 0 0 0-.195-.248c-.191-.229-.51-.568-.88-.716-.364-.146-.846-.132-1.158-.108l-.132.012a1.26 1.26 0 0 0-.56-.642 2.632 2.632 0 0 0-.738-.288c-.31-.062-.739-.058-1.05-.046l-.048.002zm2.094 2.025z"/></svg>',
        },
      ];
    },
    canvas_width() {
      if (window.innerWidth > 516) {
        return 498;
      } else {
        return window.innerWidth - 18;
      }
    },
    canvas_height() {
      if (window.innerHeight > 580) {
        return 400;
      } else {
        return window.innerHeight - 212;
      }
    },
  },
  mounted() {
    this.background_img = new Image();
    this.canvas = document.querySelector("#drawer-canvas");
    this.context = this.canvas.getContext("2d");
    this.canvas_background = document.querySelector(
      "#drawer-canvas-background"
    );
    this.background_context = this.canvas_background.getContext("2d");
    this.initDraw();
    this.setCanvasStyle();
  },
  destroyed() {
    // document.querySelector('#footer').classList.remove('hide-footer')
    // document.querySelector('body').classList.remove('fix-body')
  },
  methods: {
    isPc: function () {
      const userAgentInfo = navigator.userAgent;
      const Agents = [
        "Android",
        "iPhone",
        "SymbianOS",
        "Windows Phone",
        "iPad",
        "iPod",
      ];
      let flag = true;
      for (let v = 0; v < Agents.length; v++) {
        if (userAgentInfo.indexOf(Agents[v]) > 0) {
          flag = false;
          break;
        }
      }
      return flag;
    },
    initDraw() {
      const preData = this.context.getImageData(
        0,
        0,
        this.canvas.width,
        this.canvas.height
      );
      // 空绘图表面进栈
      this.middleAry.push(preData);
    },
    canvasMove: function (e) {
      if (this.canvasMoveUse) {
        if (!this.dragMode) {
          this.context.beginPath();
          this.context.moveTo(this.lastX, this.lastY);
          const t = e.target;
          let canvasX;
          let canvasY;
          if (this.isPc()) {
            canvasX = e.clientX - t.getBoundingClientRect().x;
            canvasY = e.clientY - t.getBoundingClientRect().y;
          } else {
            canvasX = e.changedTouches[0].clientX - t.getBoundingClientRect().x;
            canvasY = e.changedTouches[0].clientY - t.getBoundingClientRect().y;
          }
          this.context.globalCompositeOperation = this.eraserMode
            ? "destination-out"
            : "source-over";
          // this.context.rect(canvasX,canvasY,20,20);
          this.context.lineTo(canvasX, canvasY);
          [this.lastX, this.lastY] = [canvasX, canvasY];
          this.context.stroke();
        } else {
          //拖曳背景
          const t = e.target;
          let canvasX;
          let canvasY;
          if (this.isPc()) {
            canvasX = e.clientX - t.getBoundingClientRect().x;
            canvasY = e.clientY - t.getBoundingClientRect().y;
          } else {
            canvasX = e.changedTouches[0].clientX - t.getBoundingClientRect().x;
            canvasY = e.changedTouches[0].clientY - t.getBoundingClientRect().y;
          }
          this.canvas_background.height = this.canvas_background.height;
          let size_temp = {
            x: this.background_img_size.x + (canvasX - this.lastX),
            y: this.background_img_size.y + (canvasY - this.lastY),
            width: this.background_img_size.width,
            height: this.background_img_size.height,
          };
          this.draw_background(this.background_img, size_temp);
        }
      }
    },
    beginPath: function (e) {
      if (e.target !== this.canvas) {
        // console.log("beginPath");
        this.context.beginPath();
      }
    },
    // mouseup
    canvasUp: function (e) {
      if (!this.dragMode) {
        const preData = this.context.getImageData(
          0,
          0,
          this.canvas.width,
          this.canvas.height
        );
        if (!this.nextDrawAry.length) {
          // 当前绘图表面进栈
          this.middleAry.push(preData);
        } else {
          this.middleAry = [];
          this.middleAry = this.middleAry.concat(this.preDrawAry);
          this.middleAry.push(preData);
          this.nextDrawAry = [];
        }
        this.canvasMoveUse = false;
      } else {
        //记录抬起鼠标位置，作为背景定位的x,y
        const t = e.target;
        let canvasX;
        let canvasY;
        if (this.isPc()) {
          canvasX = e.clientX - t.getBoundingClientRect().x;
          canvasY = e.clientY - t.getBoundingClientRect().y;
        } else {
          canvasX = e.changedTouches[0].clientX - t.getBoundingClientRect().x;
          canvasY = e.changedTouches[0].clientY - t.getBoundingClientRect().y;
        }
        this.background_img_size.x += canvasX - this.lastX;
        this.background_img_size.y += canvasY - this.lastY;
        this.canvasMoveUse = false;
      }
    },
    // mousedown
    canvasDown: function (e) {
      if (!this.dragMode) {
        this.canvasMoveUse = true;
        // client是基于整个页面的坐标
        // offset是cavas距离顶部以及左边的距离
        const canvasX = e.clientX - e.target.getBoundingClientRect().x;
        const canvasY = e.clientY - e.target.getBoundingClientRect().y;
        this.setCanvasStyle();
        // 清除子路径
        this.lastX = canvasX;
        this.lastY = canvasY;
        // 当前绘图表面状态
        const preData = this.context.getImageData(
          0,
          0,
          this.canvas.width,
          this.canvas.height
        );
        // 当前绘图表面进栈
        this.preDrawAry.push(preData);
      } else {
        //记录点下鼠标时候的位置
        const t = e.target;
        let canvasX;
        let canvasY;
        if (this.isPc()) {
          canvasX = e.clientX - t.getBoundingClientRect().x;
          canvasY = e.clientY - t.getBoundingClientRect().y;
        } else {
          canvasX = e.changedTouches[0].clientX - t.getBoundingClientRect().x;
          canvasY = e.changedTouches[0].clientY - t.getBoundingClientRect().y;
        }
        this.lastX = canvasX;
        this.lastY = canvasY;
        this.canvasMoveUse = true;
      }
    },
    // 设置颜色
    setColor: function (color) {
      this.config.lineColor = color;
      this.eraserMode = false;
      this.dragMode = false;
    },
    // 设置笔刷大小
    setBrush: function (type) {
      this.config.lineWidth = type;
      this.eraserMode = false;
      this.dragMode = false;
    },
    // 操作
    controlCanvas: function (action) {
      switch (action) {
        case "prev":
          if (this.preDrawAry.length) {
            const popData = this.preDrawAry.pop();
            const midData = this.middleAry[this.preDrawAry.length + 1];
            this.nextDrawAry.push(midData);
            this.context.putImageData(popData, 0, 0);
          }
          break;
        case "next":
          if (this.nextDrawAry.length) {
            const popData = this.nextDrawAry.pop();
            const midData =
              this.middleAry[
                this.middleAry.length - this.nextDrawAry.length - 2
              ];
            this.preDrawAry.push(midData);
            this.context.putImageData(popData, 0, 0);
          }
          break;
        case "clear":
          this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
          // console.log(this.middleAry);
          this.preDrawAry = [];
          this.nextDrawAry = [];
          this.middleAry = [this.middleAry[0]];
          break;
        case "eraser":
          this.eraserMode = true;
          break;
        case "drag":
          this.dragMode = true;
          break;
      }
    },
    //上传图片
    upload() {
      let vm = this; //代替vue的this，使onload中可以使用vue的方法
      let image = new Image();
      image.src = this.canvas.toDataURL("image/png");
      image.onload = function () {
        vm.background_context.drawImage(image, 0, 0); //把前景写进背景，再一起上传
        const src = vm.canvas_background.toDataURL("image/png");
        vm.$emit("upload_emit", vm.dataUrlToBlob(src, "image/png"));
        vm.$emit("drawer_click");
      };
    },
    // 设置绘画配置
    setCanvasStyle: function () {
      this.context.lineWidth = this.config.lineWidth;
      this.context.shadowBlur = this.config.shadowBlur;
      this.context.shadowColor = this.config.lineColor;
      this.context.strokeStyle = this.config.lineColor;
      this.context.lineCap = "round";
      this.context.lineJoin = "round";
    },
    //插入图片
    drawer_insert(file) {
      this.background_inserted = true;
      let vm = this; //为了回调函数可以使用vue的方法
      var reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = function (event) {
        vm.background_img.src = event.target.result;
        vm.background_img.onload = function () {
          var canvas = vm.canvas_background;
          var image = vm.background_img;
          //根据canvas大小，缩放图片到合适尺寸
          vm.background_img_size.width = image.width;
          vm.background_img_size.height = image.height;
          if (image.width / image.height >= canvas.width / canvas.height) {
            if (image.width > canvas.width) {
              vm.background_img_size.width = canvas.width;
              vm.background_img_size.height =
                (image.height * canvas.width) / image.width;
            }
          } else {
            if (image.height > canvas.height) {
              vm.background_img_size.width =
                (image.width * canvas.height) / image.height;
              vm.background_img_size.height = canvas.height;
            }
          }
          vm.draw_background(image, vm.background_img_size);
        };
      };
    },
    //绘制背景图（用于拖曳背景）
    draw_background(image, size) {
      this.background_context.drawImage(
        image,
        size.x,
        size.y,
        size.width,
        size.height
      );
    },
    //用来把dataURL转换成Blob的
    dataUrlToBlob(base64, mimeType) {
      let bytes = window.atob(base64.split(",")[1]);
      let ab = new ArrayBuffer(bytes.length);
      let ia = new Uint8Array(ab);
      for (let i = 0; i < bytes.length; i++) {
        ia[i] = bytes.charCodeAt(i);
      }
      return new Blob([ab], { type: mimeType });
    },
  },
};
</script>

<style lang="scss">
#canvas-container {
  position: relative;
}

#drawer-canvas,
#drawer-canvas-background {
  position: absolute;
  left: 0;
  top: 0;
}

#drawer_modal___BV_modal_body_ {
  padding: 0px;
}

#drawer_modal___BV_modal_header_,
#drawer_modal___BV_modal_footer_ {
  padding-bottom: 6px;
  padding-top: 6px;
}

#drawer-canvas {
  border: 1px #585858 solid;
  &.painting {
    cursor: crosshair;
  }
  &.dragging {
    cursor: grab;
  }
}

#canvas-color ul {
  margin: 0;
  padding: 0;
}
#canvas-color ul li {
  width: 13px;
  height: 13px;
  border: 3px #fff solid;
  margin: 8px;
  cursor: pointer;
}
#canvas-color .active {
  border: 1px solid #5fb878;
}
#canvas-brush span {
  width: 20px;
  height: 15px;
  margin-left: 10px;
  cursor: pointer;
}
#canvas-control span {
  font-size: 14px;
  width: 20px;
  height: 15px;
  margin-left: 10px;
  cursor: pointer;
}
#canvas-control .active,
#canvas-brush .active {
  color: #5fb878;
}
</style>