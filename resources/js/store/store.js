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
        TitlePingbici: [],
        ContentPingbici: [],
        MyEmoji: "",
        Emojis: [],
        RandomHeads: [],
        CharaIndex: [],
        BrowseLogger: {},
        NickName: "",
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
            state.TitlePingbici = payload
        },
        ContentPingbici_set(state, payload) {
            state.ContentPingbici = payload
        },
        MyEmoji_set(state, payload) {
            state.MyEmoji = payload
            state.MyEmoji.emojis = JSON.parse(state.MyEmoji.emojis) //因为数据库传送过来的是纯文本json，要手动转换为数组
        },
        Emojis_set(state, payload) {
            state.Emojis = payload
        },
        RandomHeads_set(state, payload) {
            state.RandomHeads = payload
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
        }
    },
    actions: {},
    getters: {}
}

const module_forums = {
    state: () => ({
        ForumsData: '',
        CurrentForumData: '',
    }),
    mutations: {
        ForumsData_set(state, payload) {
            state.ForumsData = payload
        },
        CurrentForumData_set(state, payload) {
            state.CurrentForumData = payload
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
        PostsData: '',
        PostsLoadStatus: 0,
    }),
    mutations: {
        PostsData_set(state, payload) {
            state.PostsData = payload
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
    }),
    mutations: {
        MyCSS_set_all(state, payload) {
            state.PostsLineHeight = payload.PostsLineHeight
            state.PostsFontSize = payload.PostsFontSize
            state.QuoteFontSize = payload.QuoteFontSize
            state.SysInfoFontSize = payload.SysInfoFontSize
            state.PostsMarginTop = payload.PostsMarginTop
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
    },
}


export default new Vuex.Store({
    modules: {
        Forums: module_forums,
        User: module_user,
        Threads: module_threads,
        Posts: module_posts,
        MyCSS: module_css,
    }
})
