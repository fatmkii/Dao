
<template>
  <div>
    <p class="mt-2">你好！别来无恙。</p>
    <p>你的饼干是：{{ binggan }}</p>
    <p>饼干等级是：Lv. {{ user_lv }}</p>
    <p>你的奥利奥：{{ user_coin }} 个</p>
    <b-button
      size="md"
      class="my-1 my-sm-0"
      variant="dark"
      @click="logout_handle"
      >退出饼干</b-button
    >
    <hr />
    <b-tabs pills>
      <b-tab title="一般设定">
        <hr />
        <b-form-checkbox class="mx-2 my-2" switch v-model="z_bar_left">
          侧边栏放左侧
        </b-form-checkbox>
        <b-form-checkbox class="mx-2 my-2" switch v-model="LessToast">
          <span v-b-popover.hover.bottom="'“已滚动到阅读进度”等'"
            >减少弹窗提示</span
          >
        </b-form-checkbox>
        <hr />
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">帖子内容行距：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="16"
            max="40"
            v-model="PostsLineHeight"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">回复字体大小：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="10"
            max="24"
            v-model="PostsFontSize"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">引用字体大小：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="10"
            max="24"
            v-model="QuoteFontSize"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">楼层字体大小：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="10"
            max="24"
            v-model="SysInfoFontSize"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">回复顶部留空：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="1"
            max="48"
            v-model="PostsMarginTop"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">主题列表间距：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="0"
            max="12"
            v-model="ThreadsMarginPaddingY"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">px（仅手机版）</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">回复多行折叠：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="3"
            max="32"
            v-model="PostsMaxLine"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">行</span>
        </div>
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">回复最大引用：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            min="1"
            max="10"
            v-model="QuoteMax"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">层</span>
        </div>
        <hr />
        <div class="mt-2 d-flex align-items-center">
          <span class="mb-2">每页的主题数：</span>
          <b-form-spinbutton
            size="sm"
            style="max-width: 120px"
            class="mb-2"
            step="5"
            min="30"
            max="100"
            v-model="ThreadsPerPage"
          ></b-form-spinbutton>
          <span class="ml-1 mb-2">个</span>
        </div>
        <hr />
        <b-button :variant="button_theme" @click="set_MyCSS">保存</b-button>
        <b-button variant="outline-dark" @click="default_MyCSS"
          >恢复默认</b-button
        >
      </b-tab>
      <b-tab title="屏蔽词">
        <div class="mx-2 my-2">
          <p class="my-2">标题屏蔽词：（用英文逗号分隔）</p>
          <b-form-textarea
            id="title_pingbici_input"
            v-model="title_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <p class="my-2">内容屏蔽词：（用英文逗号分隔）</p>
          <b-form-textarea
            id="content_pingbici_input"
            v-model="content_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <p class="my-2">
            FJF小尾巴黑名单：（注意：同一个饼干在不同FJF主题中，小尾巴会不同）
          </p>
          <b-form-textarea
            id="fjf_pingbici_input"
            v-model="fjf_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <div class="d-flex align-items-center mt-2">
            <a href="javascript:;" @click="pingbici_set_unique"
              >去除重复的屏蔽词（之后要点提交喔）</a
            >
          </div>
          <div class="d-flex align-items-center mt-2">
            <b-button
              :variant="button_theme"
              :disabled="pingbici_set_handling"
              @click="pingbici_set_handle"
              >提交
            </b-button>
            <b-form-checkbox
              switch
              class="ml-2"
              v-model="use_pingbici_input"
              v-b-popover.hover.bottom="'切换后也要点击提交喔'"
            >
              启用
            </b-form-checkbox>

            <b-form-checkbox
              class="ml-2"
              switch
              v-model="FoldPingbici"
              v-b-popover.hover.bottom="'这个保存在本地不用提交'"
            >
              完全隐藏楼层
            </b-form-checkbox>
          </div>
        </div>
      </b-tab>
      <b-tab title="我的表情包">
        <div class="mx-2 my-2">
          <p class="my-2">我的表情包：（用英文的逗号分隔）</p>
          <b-form-textarea
            id="my_emoji_input"
            v-model.lazy="my_emoji_input"
            rows="3"
            max-rows="8"
            @change="my_emoji_input_change"
          ></b-form-textarea>
          <div
            class="emoji_box m-1 d-inline-flex"
            v-for="(emoji_src, index) in my_emoji_input_arr"
            :key="index"
          >
            <b-img
              :src="emoji_src"
              fluid
              alt="Fluid-grow image"
              @click="emoji_delete(index)"
            ></b-img>
          </div>
          <div class="d-flex align-items-center mt-2">
            <a href="javascript:;" @click="my_emoji_set_unique"
              >去除重复的表情包（之后要点提交喔）</a
            >
          </div>
          <div class="d-flex align-items-center mt-2">
            <b-button
              :variant="button_theme"
              :disabled="my_emoji_set_handling"
              @click="my_emoji_set_handle"
              >提交
            </b-button>
            <b-form-checkbox
              switch
              class="ml-2"
              v-model="emoji_delete_mode"
              v-b-popover.hover.bottom="'整理后记得提交喔'"
            >
              {{ this.emoji_delete_mode ? "点击表情包可删除" : "整理表情包" }}
            </b-form-checkbox>
          </div>
        </div>
      </b-tab>
      <b-tab title="收支记录">
        <div class="d-flex mt-1">
          <b-input-group style="max-width: 160px">
            <b-form-input
              v-model="income_date_selected"
              size="sm"
              type="text"
              placeholder="结束日期"
            ></b-form-input>
            <b-input-group-append>
              <b-form-datepicker
                v-model="income_date_selected"
                size="sm"
                placeholder="结束日期"
                locale="zh"
                button-only
                today-button
                reset-button
                close-button
                :date-format-options="{
                  year: 'numeric',
                  month: 'numeric',
                  day: 'numeric',
                }"
                label-help="请选择投票结束的日期"
              ></b-form-datepicker>
            </b-input-group-append>
          </b-input-group>
          <b-button
            class="ml-2"
            :variant="button_theme"
            size="sm"
            :disabled="pingbici_set_handling"
            @click="get_income_data(1)"
            >查询
          </b-button>
          <span class="ml-2" v-show="income_no_data">无数据</span>
        </div>
        <div
          v-show="income_loading == 2"
          class="my-2"
          style="font-size: 0.9rem"
        >
          <div>当日总计：{{ income_total }}</div>
          <div>
            <span>当月总计：</span
            ><span v-if="income_sum_loading == 0" @click="get_income_data_sum"
              >*点击查询*</span
            ><span v-else> {{ income_sum_month }}</span>
          </div>
          <div>
            <span>全年总计：</span
            ><span v-if="income_sum_loading == 0" @click="get_income_data_sum"
              >*点击查询*</span
            ><span v-else> {{ income_sum_year }}</span>
          </div>
        </div>
        <div class="d-none d-lg-block d-xl-block">
          <table class="income_table mt-1" style="table-layout: fixed">
            <thead>
              <tr class="text-left">
                <th width="20%">时间</th>
                <th width="15%">收支</th>
                <th width="25%">内容</th>
                <th width="40%">主题</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(income, index) in income_data.slice(
                  income_offset,
                  income_offset + 30
                )"
                :key="index"
              >
                <td class="text-left">{{ income.created_at }}</td>
                <td class="text-left">{{ income.olo }}</td>
                <td class="text-left">{{ income.content }}</td>
                <td class="text-left">
                  <div
                    style="
                      text-overflow: ellipsis;
                      overflow: hidden;
                      white-space: nowrap;
                    "
                  >
                    <router-link
                      class="thread_title"
                      :to="income_thread_link(income.thread_id, income.floor)"
                      >{{ income.thread_title }}</router-link
                    >
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="d-block d-lg-none d-xl-none my-2" style="font-size: 0.8rem">
          <div class="threads_table_header my-2 py-1 text-center">
            <span>收支记录</span>
          </div>
          <div
            class="threads_container"
            v-for="(income, index) in income_data.slice(
              income_offset,
              income_offset + 30
            )"
            :key="index"
          >
            <div class="my-1 py-1">
              <span>时间：{{ income.created_at }} </span>
              <span class="float-right">收支：{{ income.olo }}</span>
            </div>
            <div class="my-1 py-1">
              <span>内容：{{ income.content }}</span>
            </div>
            <div
              class="text-left my-1 py-1"
              style="
                text-overflow: ellipsis;
                overflow: hidden;
                white-space: nowrap;
              "
              v-if="income.thread_title !== null"
            >
              <span>主题：</span>
              <router-link
                class="thread_title"
                :to="income_thread_link(income.thread_id, income.floor)"
              >
                {{ income.thread_title }}</router-link
              >
            </div>
          </div>
        </div>
        <b-pagination
          v-model="income_page"
          :total-rows="income_rows"
          per-page="30"
          size="sm"
        ></b-pagination>
      </b-tab>
      <b-tab title="升级饼干">
        <p class="my-2">
          说明：可通过支付奥利奥升级饼干，以增加屏蔽词或表情包容量。
        </p>
        <a
          href="https://oss.cpttmm.com/xhg_other/price_list.png"
          target="view_frame"
          class="my-2"
          >饼干升级价目表</a
        >
        <p class="my-2">现在容量：(字符数)</p>
        <div
          v-for="(line, index) in user_lv_sheet"
          :key="index"
          class="my-2 d-flex align-items-center"
        >
          <span style="min-width: 100px">{{ line.chinese }}</span>
          <b-form-input
            size="sm"
            style="max-width: 65px"
            disabled
            v-model="user_lv_data[line.name]"
          ></b-form-input>
          <a
            href="javascript:;"
            class="ml-2"
            @click="user_lv_up_handle(line.name, line.chinese, line.price)"
            :disabled="user_lv_up_handling"
            >升级</a
          >
        </div>
      </b-tab>
      <b-tab title="定制饼干">
        <p class="my-2">
          说明：可申请定制饼干，每个价格10万olo。<br />为避免被猜到，定制饼干必须输入密码。
        </p>
        <div style="max-width: 400px" class="binggan_custom_input">
          <b-input-group prepend="定制饼干" class="mt-2">
            <b-form-input
              v-model="binggan_apply"
              placeholder="7~16个字符(字母、数字、下划线)"
            ></b-form-input>
          </b-input-group>
          <b-input-group prepend="密码" class="mt-2">
            <b-form-input
              type="password"
              v-model="binggan_password"
              :state="binggan_password == binggan_password_repeat"
              placeholder="7~20个字符(字母、数字、下划线)"
            ></b-form-input>
          </b-input-group>
          <b-input-group prepend="重复密码" class="mt-2">
            <b-form-input
              type="password"
              v-model="binggan_password_repeat"
              :state="binggan_password == binggan_password_repeat"
              placeholder="再次输入密码"
            ></b-form-input>
          </b-input-group>

          <div class="d-flex align-items-center mt-2">
            <b-button :variant="button_theme" @click="binggan_custom_handle"
              >提交</b-button
            >
            <span class="ml-2">请务必保存好饼干和密码喔</span>
          </div>
        </div>
      </b-tab>
    </b-tabs>
    <hr />
    <router-link to="admin_center" tag="a" style="font-size: 0.875rem">
      管理
    </router-link>
  </div>
</template>


<script>
import { mapState } from "vuex";

export default {
  components: {},
  props: {},
  watch: {
    z_bar_left: function () {
      localStorage.setItem("z_bar_left", this.z_bar_left ? "true" : "");
      window.document.documentElement.setAttribute(
        "z-bar-left",
        this.z_bar_left
      );
    },
  },
  data: function () {
    return {
      name: "user_center",
      user_coin: "读取中",
      user_lv: "读取中",
      user_lv_data: {
        title_pingbici: 1000,
        content_pingbici: 1000,
        fjf_pingbici: 1000,
        my_emoji: 5000,
      },
      user_lv_sheet: [
        { name: "title_pingbici", chinese: "标题屏蔽词", price: 4000 },
        { name: "content_pingbici", chinese: "内容屏蔽词", price: 4000 },
        { name: "fjf_pingbici", chinese: "FJF黑名单", price: 4000 },
        { name: "my_emoji", chinese: "我的表情包", price: 20000 },
      ],
      title_pingbici_input: "屏蔽词1,屏蔽词2",
      content_pingbici_input: "屏蔽词1,屏蔽词2",
      fjf_pingbici_input: "小尾巴1,小尾巴2",
      use_pingbici_input: false,
      pingbici_set_handling: false,
      user_lv_up_handling: false,
      my_emoji_input:
        "https://z3.ax1x.com/2021/08/01/Wznvbq.jpg,\nhttps://z3.ax1x.com/2021/08/01/Wznjrn.jpg",
      my_emoji_set_handling: false,
      z_bar_left: false,
      less_toast: false,
      emoji_delete_mode: false,
      income_date_selected: undefined,
      income_page: 1,
      income_data: [],
      income_no_data: false,
      income_sum_year: 0,
      income_sum_month: 0,
      income_loading: 0,
      income_sum_loading: 0,
      binggan_apply: "",
      binggan_password: "",
      binggan_password_repeat: "",
      binggan_apply_handling: false,
    };
  },
  computed: {
    PostsLineHeight: {
      get() {
        return this.$store.state.MyCSS.PostsLineHeight;
      },
      set(value) {
        this.$store.commit("PostsLineHeight_set", value);
      },
    },
    PostsFontSize: {
      get() {
        return this.$store.state.MyCSS.PostsFontSize;
      },
      set(value) {
        this.$store.commit("PostsFontSize_set", value);
      },
    },
    QuoteFontSize: {
      get() {
        return this.$store.state.MyCSS.QuoteFontSize;
      },
      set(value) {
        this.$store.commit("QuoteFontSize_set", value);
      },
    },
    SysInfoFontSize: {
      get() {
        return this.$store.state.MyCSS.SysInfoFontSize;
      },
      set(value) {
        this.$store.commit("SysInfoFontSize_set", value);
      },
    },
    PostsMarginTop: {
      get() {
        return this.$store.state.MyCSS.PostsMarginTop;
      },
      set(value) {
        this.$store.commit("PostsMarginTop_set", value);
      },
    },
    PostsMaxLine: {
      get() {
        return this.$store.state.MyCSS.PostsMaxLine;
      },
      set(value) {
        this.$store.commit("PostsMaxLine_set", value);
      },
    },
    QuoteMax: {
      get() {
        return this.$store.state.MyCSS.QuoteMax;
      },
      set(value) {
        this.$store.commit("QuoteMax_set", value);
      },
    },
    ThreadsPerPage: {
      get() {
        return this.$store.state.MyCSS.ThreadsPerPage;
      },
      set(value) {
        this.$store.commit("ThreadsPerPage_set", value);
      },
    },
    ThreadsMarginPaddingY: {
      get() {
        return this.$store.state.MyCSS.ThreadsMarginPaddingY;
      },
      set(value) {
        this.$store.commit("ThreadsMarginPaddingY_set", value);
      },
    },
    FoldPingbici: {
      get() {
        return this.$store.state.User.FoldPingbici;
      },
      set(value) {
        localStorage.setItem("fold_pingbici", value ? "true" : "");
        this.$store.commit("FoldPingbici_set", value);
      },
    },
    LessToast: {
      get() {
        return this.$store.state.User.LessToast;
      },
      set(value) {
        localStorage.setItem("less_toast", value ? "true" : "");
        this.$store.commit("LessToast_set", value);
      },
    },
    income_rows() {
      return this.income_data.length;
    },
    income_offset() {
      return (this.income_page - 1) * 30;
    },
    income_total() {
      var total = 0;
      this.income_data.forEach((income) => {
        total += income.olo;
      });
      return total;
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    my_emoji_input_arr() {
      try {
        var arr = this.my_emoji_input
          .replace(/(\n|\r|\s)/g, "")
          .replace(/(，)/g, ",")
          .split(",");
        return arr.filter(function (el) {
          //去掉空元素
          return el;
        });
      } catch (e) {
        return [];
      } //没什么用，就是不想在输入过程中报错
    },
    content_pingbici_input_arr() {
      try {
        var arr = this.content_pingbici_input
          .replace(/(\n|\r|\s)/g, "")
          .replace(/(，)/g, ",")
          .split(",");
        return arr.filter(function (el) {
          //去掉空元素
          return el;
        });
      } catch (e) {
        return [];
      } //没什么用，就是不想在输入过程中报错
    },
    title_pingbici_input_arr() {
      try {
        var arr = this.title_pingbici_input
          .replace(/(\n|\r|\s)/g, "")
          .replace(/(，)/g, ",")
          .split(",");
        return arr.filter(function (el) {
          //去掉空元素
          return el;
        });
      } catch (e) {
        return [];
      } //没什么用，就是不想在输入过程中报错
    },
    fjf_pingbici_input_arr() {
      try {
        var arr = this.fjf_pingbici_input
          .replace(/(\n|\r|\s)/g, "")
          .replace(/(，)/g, ",")
          .split(",");
        return arr.filter(function (el) {
          //去掉空元素
          return el;
        });
      } catch (e) {
        return [];
      } //没什么用，就是不想在输入过程中报错
    },
    ...mapState({
      login_status: (state) => state.User.LoginStatus,
      binggan: (state) => state.User.Binggan,
      my_emoji: (state) => state.User.MyEmoji.emojis,
    }),
  },
  methods: {
    logout_handle() {
      const config = {
        method: "post",
        url: "api/logout",
        data: {
          binggan: this.binggan,
        },
      };
      axios(config)
        .then((response) => {
          this.$store.commit("Token_set", "");
          this.$store.commit("Binggan_set", "");
          this.$store.commit("LoginStatus_set", false);
          this.$store.commit("AdminStatus_set", 0);
          this.$store.commit("AdminForums_set", []);

          if (window.localStorage) {
            localStorage.removeItem("Token");
            localStorage.removeItem("Binggan");
          } else {
            throw new Error("此浏览器居然不支持localstorage");
          }
          delete axios.defaults.headers.Authorization;
          window.location.href = "/"; //因为想清空Vuex状态，所以用js原生的重定向，而不是Vuerouter的push
        })
        .catch((error) => {
          if (error.response.status === 401) {
            localStorage.clear("Binggan"); //如果是token错误的情况，退出饼干的时候也返回401。此时直接删除localStorage强制退出。
            localStorage.clear("Token");
            window.location.href = "/";
          }
        }); // Todo:写异常返回代码;
    },
    get_user_data() {
      //更新用户信息
      if (localStorage.Token != null && localStorage.Binggan != null) {
        const config = {
          method: "post",
          url: "/api/user/show",
          data: {
            binggan: localStorage.Binggan,
          },
        };
        axios(config)
          .then((response) => {
            this.user_coin = response.data.data.binggan.coin;
            this.user_lv = response.data.data.binggan.user_lv;
            this.user_lv_data = response.data.data.user_lv;
            this.use_pingbici_input = Boolean(
              response.data.data.binggan.use_pingbici
            );
            this.$store.commit(
              "UsePingbici_set",
              response.data.data.binggan.use_pingbici
            );
            if (response.data.data.pingbici) {
              if (response.data.data.pingbici.title_pingbici) {
                this.$store.commit(
                  "TitlePingbici_set",
                  response.data.data.pingbici.title_pingbici
                );
                this.title_pingbici_input =
                  this.$store.state.User.TitlePingbici.join(", ");
              }
              if (response.data.data.pingbici.content_pingbici) {
                this.$store.commit(
                  "ContentPingbici_set",
                  response.data.data.pingbici.content_pingbici
                );
                this.content_pingbici_input =
                  this.$store.state.User.ContentPingbici.join(", ");
              }
              if (response.data.data.pingbici.fjf_pingbici) {
                this.$store.commit(
                  "FjfPingbici_set",
                  response.data.data.pingbici.fjf_pingbici
                );
                this.fjf_pingbici_input =
                  this.$store.state.User.FjfPingbici.join(", ");
              }
            }

            //设定表情包相关状态
            if (response.data.data.my_emoji) {
              this.$store.commit("MyEmoji_set", response.data.data.my_emoji);
              this.my_emoji_input = this.my_emoji.join(",\n");
            }
          })
          .catch((error) => {
            // if (error.response.status === 401) {
            //   localStorage.clear("Binggan"); //如果遇到401错误(用户未认证)，就清除Binggan和Token
            //   localStorage.clear("Token");
            //   delete axios.defaults.headers.Authorization;
            // }
            // alert(error);
          }); // Todo:写异常返回代码;
      }
    },
    pingbici_set_handle() {
      if (
        this.content_pingbici_input_arr.length == 0 ||
        this.title_pingbici_input_arr.length == 0 ||
        this.fjf_pingbici_input_arr.length == 0
      ) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      this.pingbici_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/pingbici_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          title_pingbici: JSON.stringify(this.title_pingbici_input_arr),
          content_pingbici: JSON.stringify(this.content_pingbici_input_arr),
          fjf_pingbici: JSON.stringify(this.fjf_pingbici_input_arr),
          use_pingbici: this.use_pingbici_input,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.get_user_data();
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.pingbici_set_handling = false;
          } else {
            this.pingbici_set_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.pingbici_set_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    pingbici_set_unique() {
      function unique(arr) {
        //去重函数
        return Array.from(new Set(arr));
      }
      if (
        this.content_pingbici_input_arr.length == 0 ||
        this.title_pingbici_input_arr.length == 0 ||
        this.fjf_pingbici_input_arr.length == 0
      ) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      var arr_unique = [];
      arr_unique = unique(this.title_pingbici_input_arr);
      this.title_pingbici_input = arr_unique.join(", ");

      arr_unique = unique(this.content_pingbici_input_arr);
      this.content_pingbici_input = arr_unique.join(", ");

      arr_unique = unique(this.fjf_pingbici_input_arr);
      this.fjf_pingbici_input = arr_unique.join(", ");

      this.$bvToast.toast("已去除重复的屏蔽词", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
    my_emoji_set_handle() {
      if (this.my_emoji_input_arr.length == 0) {
        alert("屏蔽词格式输入有误、或者是空的，请检查");
        return;
      }
      this.my_emoji_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/my_emoji_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          my_emoji: JSON.stringify(this.my_emoji_input_arr),
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
      if (this.my_emoji_input_arr.length == 0) {
        alert("表情包格式输入有误、或者是空的，请检查");
        return;
      }
      var arr_unique = unique(this.my_emoji_input_arr);
      this.$store.commit("MyEmoji_set_from_arr", arr_unique);
      this.my_emoji_input = this.my_emoji.join(",\n");
      this.$bvToast.toast("已去除重复的表情包", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
    user_lv_up_handle(name, chinese_name, price) {
      var confirmed = confirm(
        "要升级" + chinese_name + "吗？将会花费" + price + "个olo"
      );
      if (confirmed == false) {
        return;
      }
      this.user_lv_up_handling = true;
      const config = {
        method: "post",
        url: "/api/user/user_lv_up",
        data: {
          binggan: this.$store.state.User.Binggan,
          mode: name,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.get_user_data();
            this.$bvToast.toast(response.data.message, {
              title: "Done.",
              autoHideDelay: 1500,
              appendToast: true,
            });
            this.user_lv_up_handling = false;
          } else {
            this.user_lv_up_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.my_emoji_set_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    set_MyCSS() {
      const my_css = this.$store.state.MyCSS;
      localStorage.my_css = JSON.stringify(my_css);
      this.$bvToast.toast("已保存", {
        title: "Done.",
        autoHideDelay: 1500,
        appendToast: true,
      });
    },
    default_MyCSS() {
      const my_css = {
        PostsLineHeight: 28,
        PostsFontSize: 16,
        QuoteFontSize: 16,
        SysInfoFontSize: 14,
        PostsMarginTop: 32,
        PostsMaxLine: 16,
        QuoteMax: 3,
        ThreadsPerPage: 50,
        ThreadsMarginPaddingY: 4,
      };
      this.$store.commit("MyCSS_set_all", my_css);
      this.set_MyCSS();
    },
    emoji_delete(index) {
      if (this.emoji_delete_mode) {
        this.$store.state.User.MyEmoji.emojis.splice(index, 1);
        this.my_emoji_input = this.my_emoji.join(",\n");
      }
    },
    my_emoji_input_change(value) {
      try {
        this.$store.commit("MyEmoji_set_from_arr", this.my_emoji_input_arr);
      } catch (e) {} //没什么用，就是不想在输入过程中报错
    },
    set_income_date_default() {
      var dateTime = new Date(); //默认日期是今天
      var year = dateTime.getFullYear();
      var month = dateTime.getMonth() + 1;
      if (month >= 1 && month <= 9) {
        month = "0" + month;
      }
      var strDate = dateTime.getDate();
      if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
      }
      this.income_date_selected = year + "-" + month + "-" + strDate;
    },
    get_income_data(page) {
      this.income_no_data = false;
      this.income_page = page;
      this.income_loading = 1;
      var config = {
        method: "get",
        url: "/api/income/show",
        params: {
          binggan: this.$store.state.User.Binggan,
          income_date: this.income_date_selected,
          mode: "list_day", //查询当日详细数据
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            if (response.data.data.income_data.length === 0) {
              this.income_page = 1;
              this.income_data = [];
              this.income_no_data = true;
            } else {
              this.income_data = response.data.data.income_data;
            }
            this.income_loading = 2;
            this.income_sum_loading = 0;
            this.income_sum_year = 0;
            this.income_sum_month = 0;
          } else {
            this.income_loading = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.income_loading = 0;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    get_income_data_sum() {
      this.income_no_data = false;
      this.income_sum_loading = 1;
      var config = {
        method: "get",
        url: "/api/income/show",
        params: {
          binggan: this.$store.state.User.Binggan,
          income_date: this.income_date_selected,
          mode: "sum_month&year", //查询全年和全月总和
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.income_sum_year = response.data.data.sum_year;
            this.income_sum_month = response.data.data.sum_month;
            this.income_sum_loading = 2;
          } else {
            this.income_sum_loading = 0;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.income_sum_loading = 0;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
    income_thread_link(thread_id, floor) {
      if (floor !== null) {
        const page = Math.ceil(floor / 200);
        return "/thread/" + thread_id + "/" + page + "#f_" + floor;
      } else {
        return "/thread/" + thread_id + "/1";
      }
    },
    binggan_custom_handle() {
      if (this.binggan_password != this.binggan_password_repeat) {
        alert("两次输入密码不一致");
        return;
      }
      if (this.binggan_apply == "" || this.binggan_password == "") {
        alert("请输入定制饼干和密码");
        return;
      }
      var confirmed = confirm("要申请这个定制饼干吗？将消耗10万olo");
      if (!confirmed) {
        return;
      }
      this.binggan_apply_handling = true;
      const config = {
        method: "post",
        url: "/api/user/create_custom",
        data: {
          binggan: this.$store.state.User.Binggan,
          binggan_apply: this.binggan_apply,
          password: this.binggan_password,
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
            this.binggan_apply_handling = false;
            this.binggan_password_repeat = "";
            this.binggan_password = "";
            alert("定制饼干申请成功！重新导入即可使用。");
          } else {
            this.binggan_apply_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.my_emoji_set_handling = false;
          // alert(Object.values(error.response.data.errors)[0]);
          // alert(error.response.data.message);
        });
    },
  },
  created() {
    document.title = "个人中心";
    this.get_user_data();
    if (localStorage.getItem("z_bar_left") == null) {
      localStorage.z_bar_left = "";
    } else {
      this.z_bar_left = Boolean(localStorage.z_bar_left);
    }
  },
  mounted() {
    this.set_income_date_default();
  },
};
</script>