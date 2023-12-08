import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

const double11 = new Date("2023-11-11");
const now = new Date(Date.now());
const is_double11 = now.toLocaleDateString() === double11.toLocaleDateString();

const module_user = {
    state: () => ({
        UserDataLoaded: 0,
        Token: '',
        Binggan: '',
        LoginStatus: false,
        AdminStatus: 0,
        AdminForums: [],
        LockedTTL: 0,
        UsePingbici: false,
        TitlePingbici: ["屏蔽词1", "屏蔽词2"],
        ContentPingbici: ["屏蔽词1", "屏蔽词2"],
        FjfPingbici: ["小尾巴1", "小尾巴2"],
        FoldPingbici: false,
        PingbiciIngnoreCase: false,
        LessToast: false,
        HongbaoThenStop: false,
        ListeningHoldPage: false,
        MyEmoji: { name: '我的表情包', emojis: ["https://z3.ax1x.com/2021/08/01/Wznvbq.jpg", "https://z3.ax1x.com/2021/08/01/Wznjrn.jpg"] },
        Emojis: [],
        RandomHeads: [],
        CharaIndex: [],
        CharaGroupIndex: [],
        Medals: {},
        MedalsHide: {},
        BrowseLogger: {},
        NickName: "",
        FocusThreads: {},
        UserLv: 0,
        UserLvData: {},
        UserCoin: 0,
        UserBankCoin: 0,
        ImgHost: 'mjj',
        IsDouble11: is_double11,
        LoudspeakerMono: false,
        LoudspeakerLocation: 'bottom'
    }),
    mutations: {
        UserDataLoaded_set(state, payload) {
            state.UserDataLoaded = payload
        },
        Token_set(state, payload) {
            state.Token = payload
        },
        Binggan_set(state, payload) {
            state.Binggan = payload
        },
        LoginStatus_set(state, payload) {
            state.LoginStatus = payload
        },
        AdminStatus_set(state, payload) {
            state.AdminStatus = payload
        },
        AdminForums_set(state, payload) {
            state.AdminForums = payload
        },
        LockedTTL_set(state, payload) {
            state.LockedTTL = payload
        },
        UsePingbici_set(state, payload) {
            state.UsePingbici = Boolean(payload);//要强制转为bool，否则v-model有点问题
        },
        TitlePingbici_set(state, payload) {
            if (payload == null) {
                state.TitlePingbici = ["屏蔽词1", "屏蔽词2"]
            } else {
                state.TitlePingbici = payload//payload应为数组
            }
        },
        ContentPingbici_set(state, payload) {
            if (payload == null) {
                state.ContentPingbici = ["屏蔽词1", "屏蔽词2"]
            } else {
                state.ContentPingbici = payload//payload应为数组
            }
        },
        FjfPingbici_set(state, payload) {
            if (payload == null) {
                state.FjfPingbici = ["小尾巴1", "小尾巴2"]
            } else {
                state.FjfPingbici = payload//payload应为数组
            }
        },
        FoldPingbici_set(state, payload) {
            state.FoldPingbici = payload
        },
        PingbiciIngnoreCase_set(state, payload) {
            state.PingbiciIngnoreCase = payload
        },
        LessToast_set(state, payload) {
            state.LessToast = payload
        },
        HongbaoThenStop_set(state, payload) {
            state.HongbaoThenStop = payload
        },
        ListeningHoldPage_set(state, payload) {
            state.ListeningHoldPage = payload
        },
        MyEmoji_set(state, payload) {
            state.MyEmoji.emojis = payload //payload应为数组
        },
        // MyEmoji_set_from_arr(state, payload) {
        //     state.MyEmoji.emojis = payload //直接导入arr的mutation
        // },
        Emojis_set(state, payload) {
            state.Emojis = payload
        },
        RandomHeads_set(state, payload) {
            state.RandomHeads = payload
        },
        Medals_set(state, payload) {
            state.Medals = payload
        },
        MedalsHide_set(state, payload) {
            state.MedalsHide = payload
        },
        CharaGroupIndex_set(state, payload) {
            state.CharaGroupIndex = payload
        },
        CharaIndex_set(state, payload) {
            // state.CharaIndex = payload
            payload.forEach((element, index) => {
                state.CharaIndex[index] = element.concat()//复制成一个新数组，而不是引用
                //由于element是数组中的数组，所以不能直接payload.concat()
            });
        },
        CharaIndex_push_to_0(state, payload) {
            state.CharaIndex[0].push(payload)
        },
        NickName_set(state, payload) {
            state.NickName = payload
        },
        BrowseLogger_set(state, payload) {
            if (typeof state.BrowseLogger[payload.suffix] == "undefined") {
                state.BrowseLogger[payload.suffix] = {}
            }
            state.BrowseLogger[payload.suffix] = JSON.parse(JSON.stringify(payload.browse_current))
        },
        BrowseLogger_set_all(state, payload) {
            state.BrowseLogger = payload
            //删除过期的浏览记录
            for (var logger in state.BrowseLogger) {
                if (state.BrowseLogger[logger].expire_time < Date.now()) {
                    delete state.BrowseLogger[logger]
                }
            }
        },
        FocusThreads_set(state, payload) {
            state.FocusThreads[payload.suffix] = payload.posts_num
            localStorage.focus_threads = JSON.stringify(state.FocusThreads);
        },
        FocusThreads_unset(state, payload) {
            delete state.FocusThreads[payload.suffix]
            localStorage.focus_threads = JSON.stringify(state.FocusThreads);
        },
        FocusThreads_set_all(state, payload) {
            state.FocusThreads = payload
        },
        UserLv_set(state, payload) {
            state.UserLv = payload
        },
        UserLvData_set(state, payload) {
            state.UserLvData = payload
        },
        UserCoin_set(state, payload) {
            state.UserCoin = payload
        },
        UserBankCoin_set(state, payload) {
            state.UserBankCoin = payload
        },
        ImgHost_set(state, payload) {
            state.ImgHost = payload
        },
        LoudspeakerMono_set(state, payload) {
            state.LoudspeakerMono = payload
        },
        LoudspeakerLocation_set(state, payload) {
            state.LoudspeakerLocation = payload
        }
    },
    actions: {},
    getters: {}
}


const module_forums = {
    state: () => ({
        ForumsData: [],
        CurrentForumData: {},
        ForumsLoadStatus: 0,
    }),
    mutations: {
        ForumsData_set(state, payload) {
            state.ForumsData = payload
        },
        CurrentForumData_set(state, payload) {
            state.CurrentForumData = payload
        },
        ForumsLoadStatus_set(state, payload) {
            state.ForumsLoadStatus = payload
        },
        Banner_set(state, payload) {
            state.ForumsData[payload.forum_id].banners = payload.banners
        }

    },
    getters: {
        ForumData: (state) => (forum_id) => {
            if (state.ForumsData) {
                return state.ForumsData.find(ForumData => ForumData.id == forum_id)
            }
        },
        ForumsCount: (state) => {
            if (state.ForumsData) {
                return state.ForumsData.length
            }
        }
    },
    actions: {}
}


const module_threads = {
    state: () => ({
        ThreadsData: '',
        ThreadsLoadStatus: 0,
        CurrentThreadData: '',
    }),
    mutations: {
        ThreadsData_set(state, payload) {
            state.ThreadsData = payload
        },
        ThreadsLoadStatus_set(state, payload) {
            state.ThreadsLoadStatus = payload
        },
        CurrentThreadData_set(state, payload) {
            state.CurrentThreadData = payload
        }
    },
    getters: {
        ThreadsLastPage: (state) => {
            if (state.ThreadsData) {
                return state.ThreadsData.last_page
            }
        }
    },
    actions: {}
}

const module_posts = {
    state: () => ({
        PostsData: {
            currentPage: 1,
            lastPage: 1,
            data: [],
        },
        PostsLoadStatus: 0,
    }),
    mutations: {
        PostsData_set(state, payload) {
            state.PostsData = payload
        },
        PostsData_push(state, payload) {
            state.PostsData.data.push(payload)
        },
        PostsLoadStatus_set(state, payload) {
            state.PostsLoadStatus = payload
        }
    },
    actions: {}
}

const module_css = {
    state: () => ({
        PostsLineHeight: 28,
        PostsFontSize: 16,
        QuoteFontSize: 16,
        SysInfoFontSize: 14,
        PostsMarginTop: 32,
        PostsMaxLine: 16,
        QuoteMax: 3,
        ThreadsPerPage: 50,
        ThreadsMarginPaddingY: 4,
    }),
    mutations: {
        MyCSS_set_all(state, payload) {
            state.PostsLineHeight = payload.PostsLineHeight
            state.PostsFontSize = payload.PostsFontSize
            state.QuoteFontSize = payload.QuoteFontSize
            state.SysInfoFontSize = payload.SysInfoFontSize
            state.PostsMarginTop = payload.PostsMarginTop
            state.PostsMaxLine = payload.PostsMaxLine
            state.QuoteMax = payload.QuoteMax
            state.ThreadsPerPage = payload.ThreadsPerPage
            state.ThreadsMarginPaddingY = payload.ThreadsMarginPaddingY
        },
        PostsLineHeight_set(state, payload) {
            state.PostsLineHeight = payload
        },
        PostsFontSize_set(state, payload) {
            state.PostsFontSize = payload
        },
        QuoteFontSize_set(state, payload) {
            state.QuoteFontSize = payload
        },
        SysInfoFontSize_set(state, payload) {
            state.SysInfoFontSize = payload
        },
        PostsMarginTop_set(state, payload) {
            state.PostsMarginTop = payload
        },
        PostsMaxLine_set(state, payload) {
            state.PostsMaxLine = payload
        },
        QuoteMax_set(state, payload) {
            state.QuoteMax = payload
        },
        ThreadsPerPage_set(state, payload) {
            state.ThreadsPerPage = payload
        },
        ThreadsMarginPaddingY_set(state, payload) {
            state.ThreadsMarginPaddingY = payload
        },
    },
}

const module_theme = {
    state: () => ({
        Theme: 'hdao',
        //不同主题下，对应BootsrapVue的按钮的variant属性
        ThemeButtonMatrix: {
            hdao: "success",
            night: "secondary",
            gray: "secondary",
        }
    }),
    mutations: {
        Theme_set(state, payload) {
            state.Theme = payload
            window.document.documentElement.setAttribute(
                "data-theme",
                payload
            )
            localStorage.theme = payload;
        },
    },
    getters: {
        ButtonTheme: (state) => {
            return state.ThemeButtonMatrix[state.Theme]
        }
    },
}


export default new Vuex.Store({
    modules: {
        Forums: module_forums,
        User: module_user,
        Threads: module_threads,
        Posts: module_posts,
        MyCSS: module_css,
        Theme: module_theme,
    }
})
