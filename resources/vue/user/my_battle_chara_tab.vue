<template>
  <div>
    <p class="my-2" v-if="user_lv_data.my_battle_chara == 0">你还没有自定义大乱斗角色槽位哦<br>可以在“升级饼干”功能中升级</p>
    <div v-if="user_lv_data.my_battle_chara > 0 && get_data_handling == 2">
      <p class="my-2">自定义大乱斗角色</p>
      <div style="max-width: 400px" class="binggan_custom_input">
        <b-form-select v-model="chara_selected" :options="charas_name_options"></b-form-select>
        <div class="d-flex align-items-center mt-2 ">
          <b-button :variant="button_theme" @click="my_battle_chara_set_handle" :disabled="set_data_handling"
            :size="is_mobile ? 'sm' : 'md'">提交</b-button>
          <b-button class="ml-2" variant="outline-dark" @click="" :size="is_mobile ? 'sm' : 'md'"
            v-b-modal.input_modal>导入</b-button>
          <b-button class="ml-2" variant="outline-dark" @click="" :size="is_mobile ? 'sm' : 'md'"
            v-b-modal.output_modal>导出</b-button>
        </div>
        <hr>
        <div>角色名</div>
        <b-input-group prepend="角色名" class="mt-2">
          <b-form-input v-model="charas[chara_selected].name" lazy></b-form-input>
        </b-input-group>
        <hr>
        <div>头像组</div>
        <div class="d-flex align-items-center" v-for="(active, index) in heads_actives" :key="'head' + index">
          <b-input-group :prepend="active.cn" class="mt-2">
            <b-form-input v-model="charas[chara_selected].heads[active.en]" lazy></b-form-input>
          </b-input-group>
          <img :src="charas[chara_selected].heads[active.en]" style="max-height: 38px;">
        </div>
        <hr>
        <div>骰子语句 <a href="javascript:;" v-b-modal.manual_modal class="ml-2">说明</a></div>
        <div v-for="i in 10" :key="i">
          <b-input-group :prepend="`${i * 10 - 9}-${i * 10}`" class="mt-2">
            <b-form-input v-model="charas[chara_selected].messages[i - 1]"></b-form-input>
          </b-input-group>
        </div>

      </div>
    </div>

    <b-modal ref="output_modal" id="output_modal" class="output_modal">
      <template v-slot:modal-header>
        <h5>导出自定义角色</h5>
      </template>
      <template v-slot:default>
        <p>将下述角色代码复制下来就可以保存或者分享</p>
        <textarea v-model="output_json" rows="8" disabled style="width: 100%;"></textarea>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <b-button-group>
          <!-- <b-button :variant="button_theme" @click="copy_output()">复制</b-button> -->
          <b-button variant="outline-secondary" @click="cancel()">
            关闭
          </b-button>
        </b-button-group>
      </template>
    </b-modal>

    <b-modal ref="input_modal" id="input_modal" class="input_modal">
      <template v-slot:modal-header>
        <h5>导入自定义角色</h5>
      </template>
      <template v-slot:default>
        <p>将代码填入下述框就可以导入角色数据</p>
        <textarea v-model="input_json" rows="8" style="width: 100%;"></textarea>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <b-button-group>
          <b-button :variant="button_theme" @click="input_my_battle_chara">导入</b-button>
          <b-button variant="outline-secondary" @click="cancel()">
            关闭
          </b-button>
        </b-button-group>
      </template>
    </b-modal>

    <b-modal ref="manual_modal" id="manual_modal" class="manual_modal">
      <template v-slot:modal-header>
        <h5>骰子语句说明</h5>
      </template>
      <template v-slot:default>
        <h6>骰子语句：</h6>
        <p>1-10指投出1-10点时候的语句，如此类推。</p>
        <h6>替换词会被自动替换为相应内容：</h6>
        <p>%name —— 本角色名字 <br>
          %rand_num —— 骰子数字（必须有） <br>
          %opponent —— 对手名字</p>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <b-button-group>
          <b-button variant="outline-secondary" @click="cancel()">
            关闭
          </b-button>
        </b-button-group>
      </template>
    </b-modal>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "my_battle_chara_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      get_data_handling: 0,
      set_data_handling: false,

      heads_actives: [
        { index: 0, cn: '等待', en: 'wait' },
        { index: 1, cn: '参战', en: 'against' },
        { index: 2, cn: '攻击', en: 'attack' },
        { index: 3, cn: '胜利', en: 'win' },
        { index: 4, cn: '失败', en: 'lose' },
      ],
      charas: [],
      chara_selected: 0,
      input_json: "",
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    charas_name_options() {
      var names = []
      this.charas.forEach((chara, index) => {
        names.push({ value: index, text: chara.name })
      });
      return names
    },
    output_json() {
      return JSON.stringify(this.charas[this.chara_selected])
    },
    ...mapState({
      user_lv_data: (state) => state.User.UserLvData,
    }),
  },
  methods: {
    get_my_battle_chara_data() {
      this.get_data_handling = 1;
      const config = {
        method: "get",
        url: "/api/user/my_battle_chara",
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.charas = response.data.data;
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
    my_battle_chara_set_handle() {
      const chara_to_set = this.charas[this.chara_selected]
      let chara_check = chara_to_set.messages.every((message, index) => {
        if (message.search('%rand_num') == -1) {
          alert(`${index * 10 + 1}-${index * 10 + 10}语句中未有“%rand_num”替换词，请检查。否则无法知道骰子大小。`)
          return false
        } else {
          return true
        }
      });
      if (chara_check == false) {
        return
      }

      this.set_data_handling = true;
      const config = {
        method: "post",
        url: "/api/user/my_battle_chara_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          chara_id: this.chara_selected,
          name: chara_to_set.name,
          heads: JSON.stringify(chara_to_set.heads),
          messages: JSON.stringify(chara_to_set.messages),
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
            this.set_data_handling = false;
          } else {
            this.set_data_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.set_data_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    input_my_battle_chara() {
      try {
        var chara_data = JSON.parse(this.input_json);
      } catch (error) {
        alert('输入格式不正确，请重试')
        return
      }
      this.$set(this.charas, this.chara_selected, chara_data);//用vue的$set赋值数组元素，保持响应式
      this.$refs['input_modal'].hide()
    },
    copy_output() {
      //暂时用不了
      this.$copyText(this.output_json).then(function (e) {
      }, function (e) {
        alert('可能由于浏览器兼容性问题未能复制')
      })
    }
  },
  created() {
    this.get_my_battle_chara_data();
  },
  activated() {
  },
};
</script>