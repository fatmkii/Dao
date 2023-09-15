<template>
  <div>
    <div class="d-flex mt-1">
      <b-input-group style="max-width: 160px">
        <b-form-input v-model="income_date_selected" size="sm" type="text" placeholder="查询日期"></b-form-input>
        <b-input-group-append>
          <b-form-datepicker v-model="income_date_selected" size="sm" placeholder="查询日期" locale="zh" button-only
            today-button reset-button close-button :date-format-options="{
              year: 'numeric',
              month: 'numeric',
              day: 'numeric',
            }" label-help="请选择查询日期"></b-form-datepicker>
        </b-input-group-append>
      </b-input-group>
      <b-button class="ml-2" :variant="button_theme" :size="is_mobile ? 'sm' : 'md'" :disabled="income_loading == 1"
        @click="get_income_data(1)">查询
      </b-button>
      <span class="ml-2" v-show="income_no_data">无数据</span>
    </div>
    <div v-show="income_loading == 2" class="my-2" style="font-size: 0.9rem">
      <div>当日总计：{{ income_total }}</div>
      <div>
        <span>当月总计：</span><span v-if="income_sum_loading == 0" @click="get_income_data_sum">*点击查询*</span><span v-else>
          {{ income_sum_month }}</span>
      </div>
      <div>
        <span>全年总计：</span><span v-if="income_sum_loading == 0" @click="get_income_data_sum">*点击查询*</span><span v-else>
          {{ income_sum_year }}</span>
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
          <tr v-for="(income, index) in income_data.slice(
            income_offset,
            income_offset + 30
          )" :key="index">
            <td class="text-left">{{ income.created_at }}</td>
            <td class="text-left">{{ income.olo }}</td>
            <td class="text-left">{{ income.content }}</td>
            <td class="text-left">
              <div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap">
                <router-link class="thread_title" :to="income_thread_link(income.thread_id, income.floor)">{{
                  income.thread_title }}</router-link>
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
      <div class="threads_container" v-for="(income, index) in income_data.slice(
        income_offset,
        income_offset + 30
      )" :key="index">
        <div class="my-1 py-1">
          <span>时间：{{ income.created_at }} </span>
          <span class="float-right">收支：{{ income.olo }}</span>
        </div>
        <div class="my-1 py-1">
          <span>内容：{{ income.content }}</span>
        </div>
        <div class="text-left my-1 py-1" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap"
          v-if="income.thread_title !== null">
          <span>主题：</span>
          <router-link class="thread_title" :to="income_thread_link(income.thread_id, income.floor)">
            {{ income.thread_title }}</router-link>
        </div>
      </div>
    </div>
    <b-pagination v-model="income_page" :total-rows="income_rows" per-page="30" size="sm"></b-pagination>

  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "income_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    return {
      income_date_selected: undefined,
      income_page: 1,
      income_data: [],
      income_no_data: false,
      income_sum_year: 0,
      income_sum_month: 0,
      income_loading: 0,
      income_sum_loading: 0,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
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
    ...mapState({

    }),
  },
  created() { },
  methods: {
    set_income_date_default() {
      function padTo2Digits(num) {
        return num.toString().padStart(2, '0');
      }
      var dateTime = new Date();

      this.income_date_selected = [
        dateTime.getFullYear(),
        padTo2Digits(dateTime.getMonth() + 1),
        padTo2Digits(dateTime.getDate()),
      ].join('-')

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
            this.ajax_handling = false
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
  },
  mounted() {
    this.set_income_date_default();
  },
};
</script>