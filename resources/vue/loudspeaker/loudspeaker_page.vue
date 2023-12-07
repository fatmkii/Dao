<template>
    <div class="mt-2">
        <div class="user_center_table">
            <div class="d-flex align-items-end">
                <b-img style="height: 80px;" src="https://oss.cpttmm.com/xhg_other/miku_loudspeaker.png" fluid></b-img>
                <div class="ml-2" v-show="data_loading == 1">读取中……</div>
                <div class="ml-2" v-show="loudspeaker_no_data">目前无大喇叭</div>
                <div class="ml-2" v-show="data_loading == 2 && !loudspeaker_no_data">爱乃是盲目！</div>
                <b-button size="sm" class="ml-auto" :variant="button_theme" :disabled="data_loading == 1"
                    v-b-modal.loudspeaker_modal>发布大喇叭</b-button>
            </div>
            <div class="threads_table_header my-2 py-1 text-center">
                <span>大喇叭列表</span>
            </div>
            <div class="threads_container" v-for="(loudspeaker, index) in loudspeaker_data.slice(
                data_offset,
                data_offset + 10
            )" :key="index">
                <div class="text-left my-1 py-1" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap">
                    <component :is="loudspeaker.thread_id ? 'router-link' : 'span'" :style="{ color: loudspeaker.color }"
                        :to="thread_link(loudspeaker.thread_id)">
                        {{ loudspeaker.content }}
                    </component>
                </div>
                <div class="my-1 py-1">
                    <span>生效时间：{{ loudspeaker.effective_date }}</span>
                    <a class="thread_title float-right" href="javascript:;"
                        @click="admin_delete_loudspeaker_handle(loudspeaker.id)" v-if="$store.state.User.AdminStatus >= 10">
                        强制删除
                    </a>
                </div>
                <div class="my-1 py-1">
                    <span>到期时间：{{ loudspeaker.expire_date }}</span>
                    <a class="thread_title float-right" href="javascript:;"
                        @click="repeal_loudspeaker_handle(loudspeaker.id)" v-if="loudspeaker.is_your_loudspeaker">
                        撤回
                    </a>
                </div>

            </div>
        </div>
        <b-pagination class="mt-1" v-model="data_page" :total-rows="data_rows" per-page="10" size="sm"></b-pagination>

        <b-modal ref="loudspeaker_modal" id="loudspeaker_modal">
            <template v-slot:modal-header>
                <h5>发布大喇叭</h5>
            </template>
            <template v-slot:default>
                <div class="hongbao_input">
                    <p>
                        价格暂定为5000个olo（每天）<br>
                        禁止发布辱骂、诅咒、黑锦鲤等负面情绪
                    </p>

                    <div class="mt-2">生效日期</div>
                    <div class="d-flex mt-2">
                        <b-input-group style="max-width: 160px" class="mr-2">
                            <b-form-input v-model="end_date_selected" size="sm" type="text"
                                placeholder="生效日期"></b-form-input>
                            <b-input-group-append>
                                <b-form-datepicker v-model="end_date_selected" size="sm" placeholder="生效日期" locale="zh"
                                    button-only today-button reset-button close-button :date-format-options="{
                                        year: 'numeric',
                                        month: 'numeric',
                                        day: 'numeric',
                                    }" :min="minDate" :max="maxDate"></b-form-datepicker>
                            </b-input-group-append>
                        </b-input-group>

                        <b-input-group style="max-width: 160px">
                            <b-form-input v-model="end_time_selected" size="sm" type="text"
                                placeholder="生效时间"></b-form-input>
                            <b-input-group-append>
                                <b-form-timepicker v-model="end_time_selected" size="sm" locale="zh" reset-button
                                    button-only>
                                </b-form-timepicker>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="mt-2 d-flex align-items-center">
                        <span class="mb-2">生效天数：</span>
                        <b-form-spinbutton size="sm" style="max-width: 120px" class="mb-2" step="1" min="1" max="3"
                            v-model="loudspeaker_days"></b-form-spinbutton>
                        <span class="ml-1 mb-2">天</span>
                    </div>

                    <div class="mt-2">内容</div>
                    <textarea style="width: 100%;" v-model="loudspeaker_content" type="text" placeholder="最多100字"
                        rows="3"></textarea>




                    <b-input-group prepend="链接主题" class="mt-2">
                        <b-form-input v-model="loudspeaker_thread_id" type="number"
                            placeholder="主题网址的ID（可留空）"></b-form-input>
                    </b-input-group>

                    <b-input-group prepend="文字颜色" class="mt-2">
                        <b-form-input v-model="loudspeaker_color" placeholder="#212529"></b-form-input>
                    </b-input-group>

                    <div class="row align-items-center mt-3">
                        <div class="col-4">
                            <ColorPicker v-model="loudspeaker_color"></ColorPicker>
                        </div>
                    </div>

                </div>
            </template>
            <template v-slot:modal-footer="{ cancel }">
                <b-form-checkbox class="mt-2" v-model="loudspeaker_sub_id" value="10" unchecked-value="1"
                    v-if="$store.state.User.AdminStatus == 99">
                    设为置顶大喇叭
                </b-form-checkbox>
                <b-button-group>
                    <b-button :disable="loudspeaker_handling" :variant="button_theme"
                        @click="create_loudspeaker_hanle">发布</b-button>
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
import ColorPicker from "../component/color_picker.vue";

export default {
    name: "loudspeaker_page",
    components: { ColorPicker },
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
            data_loading: 0,
            loudspeaker_data: [],
            loudspeaker_no_data: false,
            data_page: 1,

            loudspeaker_content: "",
            loudspeaker_thread_id: null,
            loudspeaker_color: "",
            loudspeaker_days: 1,
            loudspeaker_sub_id: 1,
            end_time_selected: "00:00:00",
            end_date_selected: undefined,
            loudspeaker_handling: false,


            minDate: minDate,
            maxDate: maxDate,
        };
    },
    computed: {
        button_theme() {
            return this.$store.getters.ButtonTheme;
        },
        data_rows() {
            return this.loudspeaker_data.length;
        },
        data_offset() {
            return (this.data_page - 1) * 10;
        },

        ...mapState({

        }),
    },
    created() { },
    methods: {
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
        get_loudspeaker_data(page) {
            this.loudspeaker_no_data = false;
            this.data_page = page;
            this.data_loading = 1;
            var config = {
                method: "get",
                url: "/api/loudspeaker/show",
                params: {
                    binggan: this.$store.state.User.Binggan,
                    mode: "all", //查询全部大喇叭（含未发布的）
                },
            };
            axios(config)
                .then((response) => {
                    if (response.data.code == 200) {
                        if (response.data.data.length === 0) {
                            this.data_page = 1;
                            this.loudspeaker_data = [];
                            this.loudspeaker_no_data = true;
                        } else {
                            this.loudspeaker_data = response.data.data;
                        }
                        this.data_loading = 2;
                    } else {
                        this.data_loading = 0;
                        alert(response.data.message);
                    }
                })
                .catch((error) => {
                    this.data_loading = 0;
                    // alert(Object.values(error.response.data.errors)[0]);
                    // alert(error.response.data.message);
                });
        },
        create_loudspeaker_hanle() {
            if (!this.end_date_selected || !this.end_time_selected) {
                alert("请选择生效日期和时间");
                return;
            }
            const confirm_olo = this.loudspeaker_days * 5000
            var confirmed = confirm("要发布大喇叭吗？将消耗" + confirm_olo + "个olo")
            if (confirmed == false) {
                return;
            }
            this.loudspeaker_handling = true;
            var config = {
                method: "post",
                url: "/api/loudspeaker/create",
                data: {
                    binggan: this.$store.state.User.Binggan,
                    content: this.loudspeaker_content,
                    sub_id: this.loudspeaker_sub_id,
                    color: this.loudspeaker_color,
                    thread_id: this.loudspeaker_thread_id,
                    days: this.loudspeaker_days,
                    effective_date: this.end_date_selected + " " + this.end_time_selected,
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
                        this.loudspeaker_handling = false;
                        this.get_loudspeaker_data(1);
                        this.$bvModal.hide('loudspeaker_modal')
                    } else {
                        this.loudspeaker_handling = false;
                        alert(response.data.message);
                    }
                })
                .catch((error) => {
                    this.loudspeaker_handling = false;
                });
        },
        repeal_loudspeaker_handle(loudspeaker_id) {
            var confirmed = confirm("要撤回这个大喇叭吗？olo不会退回喔")
            if (confirmed == false) {
                return;
            }
            this.loudspeaker_handling = true;
            var config = {
                method: "post",
                url: "/api/loudspeaker/repeal",
                data: {
                    binggan: this.$store.state.User.Binggan,
                    loudspeaker_id: loudspeaker_id,
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
                        this.loudspeaker_handling = false;
                        this.get_loudspeaker_data(1);
                    } else {
                        this.loudspeaker_handling = false;
                        alert(response.data.message);
                    }
                })
                .catch((error) => {
                    this.loudspeaker_handling = false;
                });
        },
        admin_delete_loudspeaker_handle(loudspeaker_id) {
            var confirmed = confirm("要强制删除这个大喇叭吗？这是管理员功能")
            if (confirmed == false) {
                return;
            }
            this.loudspeaker_handling = true;
            var config = {
                method: "post",
                url: "/api/admin/del_loudspeaker",
                data: {
                    binggan: this.$store.state.User.Binggan,
                    loudspeaker_id: loudspeaker_id,
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
                        this.loudspeaker_handling = false;
                        this.get_loudspeaker_data(1);
                    } else {
                        this.loudspeaker_handling = false;
                        alert(response.data.message);
                    }
                })
                .catch((error) => {
                    this.loudspeaker_handling = false;
                });
        },


        thread_link(thread_id) {
            if (thread_id != null) {
                return "/thread/" + thread_id + "/1";
            } else {
                return ""
            }

        },
    },
    mounted() {
        this.get_loudspeaker_data(1);
        this.set_date_default();
    },
};
</script>