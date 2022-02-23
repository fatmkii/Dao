
<template>
  <div>
    <p class="mt-2">你好！别来无恙。</p>
    <p>你的饼干是：{{ binggan }}</p>
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
        <b-button variant="success" @click="set_MyCSS">保存</b-button>
        <b-button variant="outline-dark" @click="default_MyCSS"
          >恢复默认</b-button
        >
      </b-tab>
      <b-tab title="屏蔽词">
        <div class="mx-2 my-2">
          <p class="my-2">
            标题屏蔽词：（请参考下述JSON格式。前后有[]，最后一个不要有,逗号）
          </p>
          <b-form-textarea
            id="title_pingbici_input"
            v-model="title_pingbici_input"
            rows="3"
            max-rows="20"
          ></b-form-textarea>
          <p class="my-2">
            内容屏蔽词：（请参考下述JSON格式。前后有[]，最后一个不要有,逗号）
          </p>
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
            <b-button
              variant="success"
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
          <p class="my-2">我的表情包：（新饼干要提交一次，才能使用喔）</p>
          <b-form-textarea
            id="my_emoji_input"
            v-model.lazy="my_emoji_input"
            rows="3"
            max-rows="8"
            @change="my_emoji_input_change"
          ></b-form-textarea>
          <div
            class="emoji_box m-1 d-inline-flex"
            v-for="(emoji_src, index) in my_emoji"
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
            <b-button
              variant="success"
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
        <div class="d-flex">
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
            variant="success"
            size="sm"
            :disabled="pingbici_set_handling"
            @click="get_income_data(1)"
            >查询
          </b-button>
          <span class="ml-2" v-show="income_no_data">无数据</span>
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
              <tr v-for="(income, index) in income_data" :key="index">
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
            v-for="(income, index) in income_data"
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
        <b-pagination-nav
          :number-of-pages="income_last_page"
          v-model="income_page"
          :link-gen="linkGen"
          limit="10"
          class="my-2"
          size="sm"
        ></b-pagination-nav>
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
    income_page() {
      this.get_income_data(this.income_page);
    },
  },
  data: function () {
    return {
      name: "user_center",
      user_coin: 0,
      title_pingbici_input: '["屏蔽词1","屏蔽词2"]',
      content_pingbici_input: '["屏蔽词1","屏蔽词2"]',
      fjf_pingbici_input: '["小尾巴1","小尾巴2"]',
      use_pingbici_input: false,
      pingbici_set_handling: false,
      my_emoji_input:
        '[\n"https://z3.ax1x.com/2021/08/01/Wznvbq.jpg",\n"https://z3.ax1x.com/2021/08/01/Wznjrn.jpg"\n]',
      my_emoji_set_handling: false,
      z_bar_left: false,
      emoji_delete_mode: false,
      income_date_selected: undefined,
      income_page: 1,
      income_last_page: 1,
      income_data: undefined,
      income_no_data: false,
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
    FoldPingbici: {
      get() {
        return this.$store.state.User.FoldPingbici;
      },
      set(value) {
        localStorage.setItem("fold_pingbici", value ? "true" : "");
        this.$store.commit("FoldPingbici_set", value);
      },
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
        .catch((error) => alert(error)); // Todo:写异常返回代码
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
            //设定屏蔽词相关状态
            this.use_pingbici_input = Boolean(
              response.data.data.binggan.use_pingbici
            );
            if (response.data.data.pingbici) {
              if (response.data.data.pingbici.title_pingbici) {
                this.title_pingbici_input =
                  response.data.data.pingbici.title_pingbici;
              }
              if (response.data.data.pingbici.content_pingbici) {
                this.content_pingbici_input =
                  response.data.data.pingbici.content_pingbici;
              }
              if (response.data.data.pingbici.fjf_pingbici) {
                this.fjf_pingbici_input =
                  response.data.data.pingbici.fjf_pingbici;
              }
            }
            //设定表情包相关状态
            if (response.data.data.my_emoji) {
              this.$store.commit("MyEmoji_set", response.data.data.my_emoji);
              this.my_emoji_input = response.data.data.my_emoji;
              this.my_emoji_input = this.my_emoji_input.replace(/,/g, ",\n"); //把,改成换行，方便看
              this.my_emoji_input = this.my_emoji_input.replace(/\[/g, "[\n");
              this.my_emoji_input = this.my_emoji_input.replace(/]/g, "\n]");
            }
          })
          .catch((error) => {
            if (error.response.status === 401) {
              localStorage.clear("Binggan"); //如果遇到401错误(用户未认证)，就清除Binggan和Token
              localStorage.clear("Token");
              delete axios.defaults.headers.Authorization;
            }
            alert(error);
          }); // Todo:写异常返回代码;
      }
    },
    pingbici_set_handle() {
      try {
        //转换并确认用户输入是否满足JSON格式
        var title_pingbici = JSON.stringify(
          JSON.parse(this.title_pingbici_input)
        );
        var content_pingbici = JSON.stringify(
          JSON.parse(this.content_pingbici_input)
        );
        var fjf_pingbici = JSON.stringify(JSON.parse(this.fjf_pingbici_input));
      } catch (e) {
        alert("屏蔽词格式输入有误，请检查");
        return;
      }
      this.pingbici_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/pingbici_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          title_pingbici: title_pingbici,
          content_pingbici: content_pingbici,
          fjf_pingbici: fjf_pingbici,
          use_pingbici: this.use_pingbici_input,
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
            this.pingbici_set_handling = false;
          } else {
            this.pingbici_set_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.pingbici_set_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    my_emoji_set_handle() {
      try {
        //转换并确认用户输入是否满足JSON格式
        var my_emoji = JSON.stringify(JSON.parse(this.my_emoji_input));
      } catch (e) {
        alert("表情包格式输入有误，请检查");
        return;
      }
      this.my_emoji_set_handling = true;
      const config = {
        method: "post",
        url: "/api/user/my_emoji_set",
        data: {
          binggan: this.$store.state.User.Binggan,
          my_emoji: my_emoji,
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
          alert(Object.values(error.response.data.errors)[0]);
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
      };
      this.$store.commit("MyCSS_set_all", my_css);
      this.set_MyCSS();
    },
    emoji_delete(index) {
      if (this.emoji_delete_mode) {
        this.$store.state.User.MyEmoji.emojis.splice(index, 1);
        this.my_emoji_input = JSON.stringify(this.my_emoji);
        this.my_emoji_input = this.my_emoji_input.replace(/,/g, ",\n"); //把,改成换行，方便看
        this.my_emoji_input = this.my_emoji_input.replace(/\[/g, "[\n");
        this.my_emoji_input = this.my_emoji_input.replace(/]/g, "\n]");
      }
    },
    my_emoji_input_change(value) {
      try {
        this.$store.commit("MyEmoji_set", value);
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
      var config = {
        method: "get",
        url: "/api/income/show",
        params: {
          page: page,
          binggan: this.$store.state.User.Binggan,
          income_date: this.income_date_selected,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            if (response.data.data.lastPage === 0) {
              this.income_last_page = 1;
              this.income_page = 1;
              this.income_data = undefined;
              this.income_no_data = true;
            } else {
              this.income_page = response.data.data.currentPage;
              this.income_last_page = response.data.data.lastPage;
              this.income_data = response.data.data.income_data;
            }
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    linkGen() {
      return ``;
    },
    income_thread_link(thread_id, floor) {
      if (floor !== null) {
        const page = Math.ceil(floor / 200);
        return "/thread/" + thread_id + "/" + page + "#f_" + floor;
      } else {
        return "/thread/" + thread_id + "/1";
      }
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