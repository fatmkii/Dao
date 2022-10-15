<template>
  <b-modal ref="code_modal" id="code_modal">
    <template v-slot:modal-header>
      <h5>快捷代码</h5>
    </template>
    <template v-slot:default>
      <div class="hongbao_input">
        <p style="word-wrap: break-word">
          代码预览：
          <br />
          {{ result_text }}
        </p>
        <b-input-group prepend="代码类型" class="mt-2">
          <b-form-select
            v-model="code_type"
            :options="code_type_options"
          ></b-form-select>
        </b-input-group>
        <b-input-group
          :prepend="code_type_para[code_type].prepend_1"
          class="mt-2"
          v-show="code_type_para[code_type].input_1_enable"
        >
          <b-form-input v-model="text_input_1"></b-form-input>
        </b-input-group>
        <b-input-group
          :prepend="code_type_para[code_type].prepend_2"
          class="mt-2"
          v-show="code_type_para[code_type].input_2_enable"
        >
          <b-form-input v-model="text_input_2"></b-form-input>
        </b-input-group>
      </div>
    </template>
    <template v-slot:modal-footer="{ cancel }">
      <b-button-group>
        <b-button :variant="button_theme" @click="insert_handle">插入</b-button>
        <b-button variant="outline-secondary" @click="cancel()">
          取消
        </b-button>
      </b-button-group>
    </template>
  </b-modal>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "code_modal",
  components: {},
  props: {},
  watch: {
    code_type() {
      this.text_input_1 = this.code_type_para[this.code_type].input_1_default;
      this.text_input_2 = this.code_type_para[this.code_type].input_2_default;
    },
  },
  data: function () {
    return {
      code_type: 0,
      code_type_options: [
        { value: 0, text: "折叠内容" },
        { value: 1, text: "超链接" },
        { value: 2, text: "图片" },
      ],
      code_type_para: [
        {
          prepend_1: "显示内容",
          prepend_2: "隐藏内容",
          input_1_enable: true,
          input_2_enable: true,
          input_1_default: "显示内容",
          input_2_default: "隐藏内容",
          insert_text: "<details><summary>%s1</summary>%s2</details>",
        },
        {
          prepend_1: null,
          prepend_2: "图片链接",
          input_1_enable: false,
          input_2_enable: true,
          input_1_default: "",
          input_2_default: "",
          insert_text: "<img src='%s2'>",
        },
        {
          prepend_1: "显示文字",
          prepend_2: "链接网址",
          input_1_enable: true,
          input_2_enable: true,
          input_1_default: "点我跳转",
          input_2_default: "",
          insert_text: "<a href='%s2'>%s1</a>",
        },
      ],
      text_input_1: "",
      text_input_2: "",
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    result_text() {
      return this.code_type_para[this.code_type].insert_text
        .replace("%s1", this.text_input_1)
        .replace("%s2", this.text_input_2);
    },
  },
  created() {},
  methods: {
    insert_handle() {
      this.$emit("insert_handle", this.result_text);
      this.$refs["code_modal"].toggle();
    },
    toggle() {
      this.text_input_1 = this.code_type_para[this.code_type].input_1_default;
      this.text_input_2 = this.code_type_para[this.code_type].input_2_default;
      this.$refs["code_modal"].toggle();
    },
  },
  activated() {},
};
</script>