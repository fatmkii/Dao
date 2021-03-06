import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

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
        TitlePingbici: "",
        ContentPingbici: "",
        FjfPingbici: "",
        FoldPingbici: false,
        LessToast: false,
        MyEmoji: { name: '我的表情包', emojis: [] },
        Emojis: [],
        RandomHeads: [],
        CharaIndex: [],
        CharaGroupIndex: [],
        BrowseLogger: {},
        NickName: "",
        FocusThreads: {},
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
            state.UsePingbici = payload
        },
        TitlePingbici_set(state, payload) {
            state.TitlePingbici = JSON.parse(payload) //因为数据库传送过来的是纯文本json，要手动转换为数组
        },
        ContentPingbici_set(state, payload) {
            state.ContentPingbici = JSON.parse(payload) //因为数据库传送过来的是纯文本json，要手动转换为数组
        },
        FjfPingbici_set(state, payload) {
            state.FjfPingbici = JSON.parse(payload) //因为数据库传送过来的是纯文本json，要手动转换为数组
        },
        FoldPingbici_set(state, payload) {
            state.FoldPingbici = payload
        },
        LessToast_set(state, payload) {
            state.LessToast = payload
        },
        MyEmoji_set(state, payload) {
            state.MyEmoji.emojis = JSON.parse(payload) //因为数据库传送过来的是纯文本json，要手动转换为数组
        },
        MyEmoji_set_from_arr(state, payload) {
            state.MyEmoji.emojis = payload //直接导入arr的mutation
        },
        Emojis_set(state, payload) {
            state.Emojis = payload
        },
        RandomHeads_set(state, payload) {
            state.RandomHeads = payload
        },
        CharaGroupIndex_set(state, payload) {
            state.CharaGroupIndex = payload
        },
        CharaIndex_set(state, payload) {
            state.CharaIndex = payload
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
