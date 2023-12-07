<template>
  <div>
    <div class="d-flex mt-1">
      <b-button class="ml-2" :variant="button_theme" :size="is_mobile ? 'sm' : 'md'" v-b-modal.bank_modal>我要存粮
      </b-button>
      <span class="ml-2" v-show="income_no_data">无数据</span>
    </div>
    <div class="user_center_table">
      <div class="threads_table_header my-2 py-1 text-center">
        <span>粮仓奥利奥</span>
      </div>
      <div class="threads_container" v-for="(deposit, index) in bank_data.slice(
        bank_data_offset,
        bank_data_offset + 10
      )" :key="index">
        <div class="my-1 py-1" v-if="deposit.description">
          <span>存粮标签：{{ deposit.description }} </span>
        </div>
        <div class="my-1 py-1">
          <span>存入时间：{{ deposit.created_at }} </span>
          <span class="float-right">存粮：{{ deposit.olo }} {{
            is_expired(deposit.expire_date) ? '' : '🔒' }}</span>
        </div>
        <div class="my-1 py-1">
          <span>到期时间：{{ deposit.expire_date }}</span>
          <a class="thread_title float-right" href="javascript:;"
            @click="withdraw_deposit(deposit.expire_date, deposit.id)" :disable="withdraw_handling">{{
              is_expired(deposit.expire_date) ? '到期开仓' : '提前开仓' }}</a>
        </div>

      </div>
    </div>
    <b-pagination v-model="bank_data_page" :total-rows="bank_data_rows" per-page="10" size="sm"
      class="mt-2"></b-pagination>

    <b-modal ref="bank_modal" id="bank_modal">
      <template v-slot:modal-header>
        <h5>存粮</h5>
      </template>
      <template v-slot:default>
        <div class="hongbao_input">
          <p>
            存粮没有利息、提前开仓扣12%
            <br>
            （那有什么用啊！）
          </p>
          <b-input-group prepend="存粮标签　" class="mt-2">
            <b-form-input v-model="deposit_description" type="text" placeholder="可留空"></b-form-input>
          </b-input-group>

          <b-input-group prepend="存入奥利奥" class="mt-2">
            <b-form-input v-model="deposit_olo" type="number" placeholder="最少10个奥利奥">></b-form-input>
          </b-input-group>

          <div class="mt-2">到期日期（1天 ~ 1年）</div>
          <div class="d-flex mt-2">
            <b-input-group style="max-width: 160px" class="mr-2">
              <b-form-input v-model="end_date_selected" size="sm" type="text" placeholder="到期日期"></b-form-input>
              <b-input-group-append>
                <b-form-datepicker v-model="end_date_selected" size="sm" placeholder="到期日期" locale="zh" button-only
                  today-button reset-button close-button :date-format-options="{
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                  }" :min="minDate" :max="maxDate" label-help="请选择存粮到期日期"></b-form-datepicker>
              </b-input-group-append>
            </b-input-group>

            <b-input-group style="max-width: 160px">
              <b-form-input v-model="end_time_selected" size="sm" type="text" placeholder="到期时间"></b-form-input>
              <b-input-group-append>
                <b-form-timepicker v-model="end_time_selected" size="sm" locale="zh" reset-button button-only>
                </b-form-timepicker>
              </b-input-group-append>
            </b-input-group>
          </div>

        </div>
      </template>
      <template v-slot:modal-footer="{ cancel }">
        <b-button-group>
          <b-button :variant="button_theme" :disabled="deposit_handling" @click="set_bank_deposit">提交</b-button>
          <b-button variant="outline-secondary" @click="cancel()">
            取消
          </b-button>
        </b-button-group>
      </template>
    </b-modal>
  </div>
</template>


<script>
import { mapState } from "vuex";
export default {
  name: "bank_tab",
  components: {},
  props: {},
  watch: {
  },
  data: function () {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const minDate = new Date(today);
    const maxDate = new Date(today);
    maxDate.setFullYear(maxDate.getFullYear() + 1);
    return {

      bank_data: [],
      get_data_handling: false,
      bank_data_page: 1,

      deposit_olo: 0,
      deposit_description: "",
      end_time_selected: "00:00:00",
      end_date_selected: undefined,

      deposit_handling: false,

      withdraw_handling: false,

      income_data: [],
      income_no_data: false,
      income_sum_year: 0,
      income_sum_month: 0,
      income_loading: 0,
      income_sum_loading: 0,

      minDate: minDate,
      maxDate: maxDate,
    };
  },
  computed: {
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    is_mobile() {
      return document.body.clientWidth < 1200;
    },
    bank_data_rows() {
      return this.bank_data.length;
    },
    bank_data_offset() {
      return (this.bank_data_page - 1) * 10;
    },
    ...mapState({

    }),
  },
  created() { },
  methods: {
    get_bank_deposit() {
      this.get_data_handling = true;
      var config = {
        method: "post",
        url: "/api/user/show_bank",
        data: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.bank_data = response.data.data;
            this.bank_data_page = 1;
            this.get_data_handling = false;
          } else {
            this.get_data_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.get_data_handling = false;
        });
    },
    set_bank_deposit() {
      if (!this.end_date_selected || !this.end_time_selected) {
        alert("请选择存粮到期日期和时间");
        return;
      }
      var confirmed = confirm("要往粮仓里存入奥利奥吗？")
      if (confirmed == false) {
        return;
      }
      this.deposit_handling = true;
      var config = {
        method: "post",
        url: "/api/user/bank_deposit",
        data: {
          binggan: this.$store.state.User.Binggan,
          olo: this.deposit_olo,
          description: this.deposit_description,
          expire_date: this.end_date_selected + " " + this.end_time_selected,
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
            this.deposit_handling = false;
            this.get_bank_deposit();
            this.$eventHub.$emit("user_data_refresh")
            this.$bvModal.hide('bank_modal')
          } else {
            this.deposit_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.deposit_handling = false;
        });
    },
    set_date_default() {
      function padTo2Digits(num) {
        return num.toString().padStart(2, '0');
      }

      var dateTime = new Date();

      this.end_date_selected = [
        dateTime.getFullYear(),
        padTo2Digits(dateTime.getMonth() + 1),
        padTo2Digits(dateTime.getDate()),
      ].join('-')

      this.end_time_selected = [
        padTo2Digits(dateTime.getHours()),
        padTo2Digits(dateTime.getMinutes()),
        padTo2Digits(dateTime.getSeconds()),
      ].join(':')

    },
    is_expired(expire_date) {
      var now = new Date();
      if (Date.parse(expire_date) < now) {
        return true
      } else {
        return false
      }
    },
    withdraw_deposit(expire_date, deposit_id) {
      var now = new Date();
      var confirm_penalty = false;
      if (Date.parse(expire_date) > now) {
        var confirmed = confirm("确定要提前开仓吗？会扣除12%奥利奥")
        if (confirmed == false) {
          return;
        }
        confirm_penalty = true;
      }

      this.deposit_handling = true;
      var config = {
        method: "post",
        url: "/api/user/bank_withdraw",
        data: {
          binggan: this.$store.state.User.Binggan,
          deposit_id: deposit_id,
          confirm_penalty: confirm_penalty,
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
            this.deposit_handling = false;
            this.get_bank_deposit();
            this.$eventHub.$emit("user_data_refresh")
          } else {
            this.deposit_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.deposit_handling = false;
        });
    },

  },
  mounted() {
    this.get_bank_deposit();
    this.set_date_default();
  },
};
</script>