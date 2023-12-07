<template>
    <div class="loudspeaker_container">
        <div class="loudspeaker_content" v-if="data_loading != 2">正在读取大喇叭</div>
        <div class="loudspeaker_content" v-if="loudspeaker_no_data"><router-link to="/loudspeaker" class="thread_title">
                目前没有大喇叭，要发一个吗？</router-link></div>
        <div class="loudspeaker_content" v-if="data_loading == 2 && !loudspeaker_no_data">
            <div style="word-wrap:break-word" v-for="(loudspeaker, index) in loudspeaker_data" :key="index"
                :style="{ color: loudspeaker.color }">
                <component :is="loudspeaker.thread_id ? 'router-link' : 'span'" :style="{ color: loudspeaker.color }"
                    :to="thread_link(loudspeaker.thread_id)">
                    {{ index }}. {{ loudspeaker.content }}
                </component>
            </div>
            <!-- 复制多一份，实现无缝循环播放 -->
            <div style="word-wrap:break-word" v-for="(loudspeaker, index) in loudspeaker_data"
                :key="index + loudspeaker_data.length" :style="{ color: loudspeaker.color }">
                <component :is="loudspeaker.thread_id ? 'router-link' : 'span'" :style="{ color: loudspeaker.color }"
                    :to="thread_link(loudspeaker.thread_id)">
                    {{ index }}. {{ loudspeaker.content }}
                </component>
            </div>
        </div>
    </div>
</template>
  
  
<script>
import { mapState } from "vuex";
export default {
    name: "loudspeaker_component",
    components: {},
    props: {},
    watch: {
    },
    data: function () {
        return {
            data_loading: 0,
            loudspeaker_data: [],
            loudspeaker_no_data: false,
        };
    },
    computed: {
        ...mapState({

        }),
    },

    methods: {
        get_loudspeaker_data() {
            this.loudspeaker_no_data = false;
            this.data_loading = 1;
            var config = {
                method: "get",
                url: "/api/loudspeaker/show",
                params: {
                    binggan: this.$store.state.User.Binggan,
                    mode: "effective", //查询生效中的大喇叭
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
                            let vm = this;
                            setTimeout(function () {
                                //setTimeout后才能获得准确的元素高度
                                vm.addKeyFrames();
                            }, 1000);//不加1000ms后的话，有时候会无法获得元素高度
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
        addKeyFrames() {
            //因为loudspeaker_content的高度会根据内容改变，所以要动态调整keyframe的移动速度和高度
            var container = document.getElementsByClassName("loudspeaker_content")[0]
            var container_height = container.offsetHeight

            var style = document.createElement('style')
            style.id = "loudspeaker_keyframe"
            var keyFrames =
                `@keyframes loudspeaker_move {
                /* 原理 height值的变化 移动一个容器的高度 */
                from {
                    top: 0;
                }
                to {
                    top: -DOM_HEIGHTpx;
                }
            }
            @-webkit-keyframes loudspeaker_move {
                /* 原理 height值的变化 移动一个容器的高度 */
                from {
                    top: 0;
                }
                to {
                    top: -DOM_HEIGHTpx;
                }
            }

            .loudspeaker_container .loudspeaker_content {
                animation: SPEEDs loudspeaker_move infinite linear;
                -webkit-animation: SPEEDs loudspeaker_move infinite linear;
            }`
            keyFrames = keyFrames.replace(/DOM_HEIGHT/g, container_height / 2)
            keyFrames = keyFrames.replace(/SPEED/g, container_height / 20)
            style.innerHTML = keyFrames
            document.getElementsByTagName('head')[0].appendChild(style)

        },
        thread_link(thread_id) {
            if (thread_id != null) {
                return "/thread/" + thread_id + "/1";
            } else {
                return ""
            }

        },
    },
    created() { this.get_loudspeaker_data(); },
    mounted() { },
    beforeDestroy() {
        //避免重复追加多个style
        var style_existed = document.getElementById("loudspeaker_keyframe")
        if (style_existed) {
            style_existed.remove()
        }
    }

};
</script>
