
<template>
  <div>
    <div
      class="thread_body"
      v-show="posts_load_status == 2 && !thread_response_code"
    >
      <div class="row align-items-center mt-3">
        <div class="col-auto h5 d-none d-lg-block d-xl-block">
          <b-badge variant="secondary" pill class="float-left">
            {{ forum_id }}
          </b-badge>
          <router-link class="forum_name" :to="'/forum/' + forum_id">{{
            forum_name
          }}</router-link>
        </div>
        <div class="col-auto h6 d-block d-lg-none d-xl-none">
          <b-badge variant="secondary" pill class="float-left">
            {{ forum_id }}
          </b-badge>
          <router-link class="forum_name" :to="'/forum/' + forum_id">{{
            forum_name
          }}</router-link>
        </div>
        <b-dropdown
          id="shield-dropdown"
          text="屏蔽"
          variant="outline-dark"
          size="sm"
        >
          <b-form-checkbox v-model="no_video_mode" switch class="ml-2 my-2">
            无视频音频
          </b-form-checkbox>
          <b-form-checkbox v-model="no_image_mode" switch class="ml-2 my-2">
            无图
          </b-form-checkbox>
          <b-form-checkbox v-model="no_emoji_mode" switch class="ml-2 my-2">
            无表情包
          </b-form-checkbox>
          <b-form-checkbox v-model="no_head_mode" switch class="ml-2 my-2">
            无头像
          </b-form-checkbox>
          <b-form-checkbox v-model="no_battle_mode" switch class="ml-2 my-2">
            无大乱斗
          </b-form-checkbox>
          <b-form-checkbox v-model="no_roll_mode" switch class="ml-2 my-2">
            无roll点
          </b-form-checkbox>
          <b-form-checkbox v-model="no_hongbao_mode" switch class="ml-2 my-2">
            无红包结果
          </b-form-checkbox>
        </b-dropdown>
        <b-button
          size="sm"
          class="my-1 ml-1 my-sm-0"
          variant="outline-dark"
          :disabled="!this.$store.state.User.LoginStatus"
          @click="search_show = !search_show"
          >搜索</b-button
        >
      </div>
      <div class="d-flex flex-row my-2" v-if="search_show">
        <b-form-input
          id="search_input"
          class="search_input"
          style="max-width: 400px"
          placeholder="搜索本楼内容"
          v-model="search_input"
        ></b-form-input>
        <b-button
          size="sm"
          class="ml-1"
          style="min-width: 46px"
          :variant="button_theme"
          @click="search_handle"
          >搜索
        </b-button>
        <b-button
          size="sm"
          class="ml-1"
          style="min-width: 46px"
          variant="outline-dark"
          @click="search_clear"
          >清空
        </b-button>
      </div>
      <ThreadPaginator
        :thread_id="thread_id"
        :last_page="posts_last_page"
        :current_page="page"
        align="right"
      >
      </ThreadPaginator>
      <div class="post_container">
        <div
          class="jump_page alert alert-success px-1 py-1"
          v-if="jump_page_show"
        >
          <span style="font-size: 0.875rem"
            >你上次已阅读到第{{ browse_current.page }}页，<router-link
              :to="'/thread/' + thread_id + '/' + browse_current.page"
              >点击这里跳转</router-link
            >喔~</span
          >
        </div>
        <div class="post_title px-1 py-2 h5 d-none d-lg-block d-xl-block">
          <span style="word-wrap: break-word; white-space: normal"
            >标题：{{ thread_title }}</span
          ><span> [{{ thread_posts_num }}]</span>
        </div>
        <div class="post_title px-1 py-2 h6 d-block d-lg-none d-xl-none">
          <span style="word-wrap: break-word; white-space: normal"
            >标题：{{ thread_title }}</span
          ><span> [{{ thread_posts_num }}]</span>
        </div>
        <div
          v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
          class="d-flex flex-wrap align-items-center"
        >
          <b-form-checkbox class="mr-auto" v-model="admin_button_show" switch>
            显示管理员按钮
          </b-form-checkbox>
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            v-show="admin_button_show"
            @click="set_focus_threads"
          >
            {{ is_focus ? "取消关注" : "关注主题" }}
          </b-button>
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            v-show="admin_button_show"
            @click="thread_set_top"
          >
            {{ thread_sub_id === 0 ? "置顶" : "取消置顶" }}
          </b-button>
          <!-- <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            v-show="admin_button_show"
            @click="check_jingfen_admin"
          >
            反精分
          </b-button> -->
          <b-button
            class="ml-1"
            size="sm"
            variant="warning"
            v-if="this.$store.state.User.AdminForums.includes(this.forum_id)"
            @click="thread_delete_click_admin"
          >
            删主题
          </b-button>
        </div>
        <div
          v-if="is_your_thread && posts_load_status == 2"
          class="d-flex flex-wrap align-items-center mt-1"
        >
          <b-button
            class="ml-auto"
            size="sm"
            variant="warning"
            v-if="is_your_thread"
            @click="modal_toggle('color_modal')"
          >
            标题改色
          </b-button>
        </div>
        <VoteComponent
          v-if="vote_question_id && posts_load_status == 2"
          :vote_question_id="vote_question_id"
        >
        </VoteComponent>
        <GambleComponent
          v-if="gamble_question_id && posts_load_status == 2"
          :gamble_question_id="gamble_question_id"
          :admin_button_show="admin_button_show"
        ></GambleComponent>
        <CrowdComponent
          v-if="crowd_id && posts_load_status"
          :crowd_id="crowd_id"
          :admin_button_show="admin_button_show"
        ></CrowdComponent>
        <HongbaoComponent
          v-if="hongbao_id && posts_load_status"
          :hongbao_id="hongbao_id"
          @quote_click="quote_click_handle"
          :admin_button_show="admin_button_show"
        ></HongbaoComponent>
        <div v-for="post_data in posts_data" :key="post_data.id">
          <PostItem
            :post_data="post_data"
            :thread_anti_jingfen="thread_anti_jingfen"
            :random_head_add="
              random_heads_data[random_heads_group - 1].random_heads[
                post_data.random_head
              ]
            "
            :admin_button_show="admin_button_show"
            :no_image_mode="no_image_mode"
            :no_emoji_mode="no_emoji_mode"
            :no_head_mode="no_head_mode"
            :no_video_mode="no_video_mode"
            @quote_click="quote_click_handle"
            @get_posts_data="get_posts_data"
            @emit_reward="emit_reward"
          >
            <template
              v-slot:battle
              v-if="
                post_data.battle_id &&
                posts_load_status == 2 &&
                no_battle_mode == false
              "
            >
              <Battle
                ref="battle_component"
                :battle_data="post_data.battle_data.battle"
                :battle_messages="post_data.battle_data.battle_messages"
              ></Battle>
            </template>
            <template
              v-slot:hongbao
              v-if="post_data.hongbao_data && posts_load_status == 2"
            >
              <HongbaoPostComponent
                ref="hongbao_post_component"
                :hongbao_data="post_data.hongbao_data"
                @quote_click="quote_click_handle"
              >
              </HongbaoPostComponent>
            </template>
          </PostItem>
        </div>
        <div class="d-flex flex-row align-items-center my-2">
          <b-button
            :variant="button_theme"
            size="sm"
            id="listen_button"
            @click="listen_channel"
            :disabled="!enable_listening || show_listen_next_page"
            >{{
              !is_listening || show_listen_next_page ? "自动涮锅" : "正在涮锅"
            }}
          </b-button>

          <div>
            <!-- <b-spinner
              class="spinner img-uploading ml-2"
              v-show="is_listening"
              label="涮锅中"
            >
            </b-spinner> -->
            <img v-show="is_listening" id="listening_img" class="ml-2" />
            <router-link
              :to="'/thread/' + thread_id + '/' + (page + 1)"
              v-if="show_listen_next_page"
              class="thread_page ml-1"
              style="font-size: 0.875rem"
              >回帖已经翻页、点击前往
            </router-link>
            <span
              class="ml-1"
              style="font-size: 0.875rem"
              v-if="this.page != this.posts_last_page"
              >在最后一页才能自动涮锅</span
            >
          </div>
        </div>
      </div>
      <PostInput
        ref="post_input_com"
        :input_disable="
          !this.$store.state.User.LoginStatus ||
          Boolean(locked_TTL) ||
          new_post_handling
        "
        :new_post_handling="new_post_handling"
        :random_heads_group="random_heads_group"
        :forum_id="forum_id"
        :thread_id="thread_id"
        @content_commit="new_post_handle"
        ><template v-slot:svg_icon>
          <svg
            width="28"
            height="28"
            viewBox="0.00 0.00 100.00 100.00"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            @click="modal_toggle('hongbao_modal')"
            class="icon-battle"
          >
            <path
              d="M25.35,5.38 Q30.76,5.38 36.16,5.38 Q49.36,5.38 62.56,5.38 Q69.70,5.38 76.84,5.38 Q86.69,5.38 86.69,14.69 Q86.69,20.55 86.69,26.40 Q86.69,28.28 86.69,30.16 Q86.69,36.69 86.69,43.22 Q86.69,51.26 86.69,59.29 Q86.69,63.24 86.69,67.19 Q86.69,71.78 86.69,76.37 Q86.69,85.68 76.84,85.68 Q68.81,85.68 60.79,85.68 Q51.30,85.68 41.82,85.68 Q33.59,85.68 25.35,85.68 Q15.50,85.68 15.50,76.37 Q15.50,69.44 15.50,62.50 Q15.50,58.50 15.50,54.50 Q15.50,52.80 15.50,51.10 Q15.50,41.80 15.50,32.50 Q15.50,31.09 15.50,29.69 Q15.50,22.19 15.50,14.69 Q15.50,5.38 25.35,5.38 Z"
              fill="none"
              stroke="rgb(68, 68, 68)"
              stroke-width="4.79"
              stroke-opacity="1.00"
              stroke-linejoin="round"
            />
            <path
              d="M16.61,30.77 L25.42,33.90 L27.28,34.56 L35.63,37.53 L37.48,38.19 L40.73,39.34 L42.59,40.00 L50.94,42.97 L58.41,40.36 L60.27,39.71 L63.07,38.74 L71.01,35.98 L85.95,30.77"
              fill="none"
              stroke="rgb(68, 68, 68)"
              stroke-width="4.79"
              stroke-opacity="1.00"
              stroke-linejoin="miter"
            />
            <path
              d="M50.96,42.39 Q50.96,42.39 50.96,42.39 Q56.96,42.39 56.96,42.94 Q56.96,42.94 56.96,42.94 Q56.96,43.50 50.96,43.50 Q50.96,43.50 50.96,43.50 Q44.95,43.50 44.95,42.94 Q44.95,42.94 44.95,42.94 Q44.95,42.39 50.96,42.39 Z"
              fill="none"
              stroke="rgb(68, 68, 68)"
              stroke-width="4.79"
              stroke-opacity="1.00"
              stroke-linejoin="round"
            />
            <path
              d="M50.69,45.23 Q50.69,45.23 50.69,45.23 Q58.14,45.23 58.14,45.97 Q58.14,45.97 58.14,45.97 Q58.14,46.70 50.69,46.70 Q50.69,46.70 50.69,46.70 Q43.25,46.70 43.25,45.97 Q43.25,45.97 43.25,45.97 Q43.25,45.23 50.69,45.23 Z"
              fill="none"
              stroke="rgb(68, 68, 68)"
              stroke-width="5.00"
              stroke-opacity="1.00"
              stroke-linejoin="round"
            />
          </svg>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            fill="currentColor"
            class="icon-battle bi bi-controller"
            viewBox="0 0 16 16"
            @click="modal_toggle('battle_modal')"
          >
            <path
              d="M11.5 6.027a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm-1.5 1.5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zm2.5-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm-1.5 1.5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zm-6.5-3h1v1h1v1h-1v1h-1v-1h-1v-1h1v-1z"
            />
            <path
              d="M3.051 3.26a.5.5 0 0 1 .354-.613l1.932-.518a.5.5 0 0 1 .62.39c.655-.079 1.35-.117 2.043-.117.72 0 1.443.041 2.12.126a.5.5 0 0 1 .622-.399l1.932.518a.5.5 0 0 1 .306.729c.14.09.266.19.373.297.408.408.78 1.05 1.095 1.772.32.733.599 1.591.805 2.466.206.875.34 1.78.364 2.606.024.816-.059 1.602-.328 2.21a1.42 1.42 0 0 1-1.445.83c-.636-.067-1.115-.394-1.513-.773-.245-.232-.496-.526-.739-.808-.126-.148-.25-.292-.368-.423-.728-.804-1.597-1.527-3.224-1.527-1.627 0-2.496.723-3.224 1.527-.119.131-.242.275-.368.423-.243.282-.494.575-.739.808-.398.38-.877.706-1.513.773a1.42 1.42 0 0 1-1.445-.83c-.27-.608-.352-1.395-.329-2.21.024-.826.16-1.73.365-2.606.206-.875.486-1.733.805-2.466.315-.722.687-1.364 1.094-1.772a2.34 2.34 0 0 1 .433-.335.504.504 0 0 1-.028-.079zm2.036.412c-.877.185-1.469.443-1.733.708-.276.276-.587.783-.885 1.465a13.748 13.748 0 0 0-.748 2.295 12.351 12.351 0 0 0-.339 2.406c-.022.755.062 1.368.243 1.776a.42.42 0 0 0 .426.24c.327-.034.61-.199.929-.502.212-.202.4-.423.615-.674.133-.156.276-.323.44-.504C4.861 9.969 5.978 9.027 8 9.027s3.139.942 3.965 1.855c.164.181.307.348.44.504.214.251.403.472.615.674.318.303.601.468.929.503a.42.42 0 0 0 .426-.241c.18-.408.265-1.02.243-1.776a12.354 12.354 0 0 0-.339-2.406 13.753 13.753 0 0 0-.748-2.295c-.298-.682-.61-1.19-.885-1.465-.264-.265-.856-.523-1.733-.708-.85-.179-1.877-.27-2.913-.27-1.036 0-2.063.091-2.913.27z"
            />
          </svg>
        </template>
        <template v-slot:after_preview>
          <div class="row align-items-center">
            <div class="col-auto">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                fill="currentColor"
                class="icon-back bi bi-arrow-left-square"
                viewBox="0 0 16 16"
                v-b-popover.hover.right="'返回小岛'"
                @click="back_to_forum(false)"
                @click.middle="back_to_forum(true)"
              >
                <path
                  fill-rule="evenodd"
                  d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm11.5 5.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"
                />
              </svg>
            </div>
            <div class="col-auto">
              <ThreadPaginator
                :thread_id="thread_id"
                :last_page="posts_last_page"
                :current_page="page"
                align="left"
              >
              </ThreadPaginator>
            </div>
          </div>
        </template>
      </PostInput>
      <div class="row align-items-center mt-2">
        <div class="col-auto ml-auto" style="font-size: 0.875rem">
          <span v-if="!this.$store.state.User.LoginStatus">
            请在先<router-link to="/login">导入或领取饼干</router-link
            >后才能发言喔
          </span>
          <span v-if="locked_TTL">
            你的饼干封禁中，将于{{
              Math.floor(locked_TTL / 3600) + 1
            }}小时后解封。
          </span>
          <span
            v-if="
              this.$store.state.Forums.CurrentForumData.is_nissin == 2 &&
              this.$store.state.Threads.CurrentThreadData.sub_id == 0
            "
            >本贴将于
            <span style="color: #dd0000">{{ nissin_TTL }}</span>
            后日清，请及时更换帖子喔
          </span>
          <span
            v-if="
              this.$store.state.Forums.CurrentForumData.is_nissin == 1 &&
              this.$store.state.Threads.CurrentThreadData.sub_id == 0
            "
            >本小岛定期
            <span style="color: #dd0000">每日早上8点</span>
            日清，请及时更换帖子喔
          </span>
        </div>
      </div>
      <div class="row align-items-center mt-2">
        <div class="col-auto mr-auto">
          <b-button
            :variant="button_theme"
            size="sm"
            @click="back_to_forum(false)"
            @click.middle="back_to_forum(true)"
          >
            返回小岛
          </b-button>
        </div>
      </div>
    </div>

    <img
      src="https://oss.cpttmm.com/xhg_other/notice_2.png"
      v-if="posts_load_status == 2 && thread_response_code == 23410"
      class="nissined_img"
    />
    <img
      src="https://oss.cpttmm.com/xhg_other/notice_3.png"
      v-if="posts_load_status == 2 && thread_response_code == 23401"
      class="nissined_img"
    />
    <img
      src="https://oss.cpttmm.com/xhg_other/notice_1.png"
      v-if="posts_load_status == 2 && thread_response_code == 234011"
      class="nissined_img"
    />
    <img
      src="https://oss.cpttmm.com/xhg_other/notice_404.png"
      v-if="posts_load_status == 2 && thread_response_code == 23404"
      class="nissined_img"
    />

    <div>
      <b-spinner
        class="spinner document-loading"
        v-show="posts_load_status == 1"
        label="读取中"
      >
      </b-spinner>

      <ZBar @reload="get_posts_data(true, false)" reload>
        <template v-slot:top>
          <div
            class="icon-roll"
            @click="modal_toggle('roll_modal')"
            key="icon-roll"
          >
            <svg
              aria-hidden="true"
              focusable="false"
              data-prefix="fas"
              data-icon="dice"
              class="svg-inline--fa fa-dice fa-w-20"
              role="img"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 640 512"
            >
              <path
                fill="currentColor"
                d="M592 192H473.26c12.69 29.59 7.12 65.2-17 89.32L320 417.58V464c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48V240c0-26.51-21.49-48-48-48zM480 376c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm-46.37-186.7L258.7 14.37c-19.16-19.16-50.23-19.16-69.39 0L14.37 189.3c-19.16 19.16-19.16 50.23 0 69.39L189.3 433.63c19.16 19.16 50.23 19.16 69.39 0L433.63 258.7c19.16-19.17 19.16-50.24 0-69.4zM96 248c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm128 128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm0-128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm0-128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24zm128 128c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z"
              ></path>
            </svg>
          </div>
        </template>
        <template v-slot:bottom>
          <div
            class="icon-jump"
            @click="modal_toggle('jump_modal')"
            key="icon-jump"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="currentColor"
              class="bi bi-skip-forward-fill"
              viewBox="0 0 16 16"
            >
              <path
                d="M15.5 3.5a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-1 0V8.753l-6.267 3.636c-.54.313-1.233-.066-1.233-.697v-2.94l-6.267 3.636C.693 12.703 0 12.324 0 11.693V4.308c0-.63.693-1.01 1.233-.696L7.5 7.248v-2.94c0-.63.693-1.01 1.233-.696L15 7.248V4a.5.5 0 0 1 .5-.5z"
              />
            </svg>
          </div>
        </template>
      </ZBar>

      <b-toast
        id="save_emoji_toast"
        title="保存为我的表情包？"
        autoHideDelay="1500"
      >
        <a href="javascript:;" class="save_emoji" @click="add_emoji_handle"
          >确定</a
        >
      </b-toast>

      <b-modal ref="roll_modal" id="roll_modal" class="roll_modal">
        <template v-slot:modal-header>
          <h5>Roll点面板</h5>
        </template>
        <template v-slot:default>
          <p style="word-wrap: break-word; white-space: normal">
            输出参考：（只是格式参考啦）
            <br />
            <span v-show="roll_name">「{{ roll_name }}」，</span>
            <span v-show="roll_event">「{{ roll_event }}」的结果：</span>
            {{ roll_num }} d {{ roll_range }} =「{{ roll_simulation }}」
          </p>
          <div class="my-1">
            <b-input-group prepend="Roll点昵称">
              <b-form-input
                v-model="roll_name"
                placeholder="可留空"
              ></b-form-input>
            </b-input-group>
            <b-input-group prepend="Roll点事件">
              <b-form-input
                v-model="roll_event"
                placeholder="可留空"
              ></b-form-input>
            </b-input-group>
          </div>
          <div class="mt-3">
            <b-input-group prepend="骰子数量">
              <b-form-input
                v-model="roll_num"
                placeholder="max:1000"
              ></b-form-input>
            </b-input-group>
            <b-input-group prepend="骰子大小">
              <b-form-input
                v-model="roll_range"
                placeholder="max:100000000"
              ></b-form-input>
            </b-input-group>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-button-group>
            <b-button
              :variant="button_theme"
              :disabled="roll_handling"
              @click="roll_handle"
              >Roll it！</b-button
            >
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
      <b-modal ref="jump_modal" id="jump_modal" class="jump_modal">
        <template v-slot:modal-header>
          <h5>跳楼机</h5>
        </template>
        <template v-slot:default>
          <p>最大高度：{{ posts_last_page }}页，{{ thread_posts_num }}楼</p>
          <div class="my-1">
            <b-input-group prepend="跳页：">
              <b-form-input
                type="number"
                v-model="jump_page"
                autofocus
                @keyup.enter="jump_handle"
              ></b-form-input>
            </b-input-group>
          </div>
          <div class="my-1">
            <b-input-group prepend="跳楼：">
              <b-form-input
                type="number"
                v-model="jump_floor"
                @keyup.enter="jump_handle"
              ></b-form-input>
            </b-input-group>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <span style="fontsize: 0.6rem">*两者都输入时，优先跳页</span>
          <b-button-group>
            <b-button :variant="button_theme" @click="jump_handle"
              >Jump！</b-button
            >
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
      <b-modal ref="captcha_modal" id="captcha_modal" class="captcha_modal">
        <template v-slot:modal-header>反脚本验证</template>
        <template v-slot:default>
          <p>嗷……你可能刷太多了，休息一下吧。</p>
          <div class="my-1">
            <div class="my-1">
              <b-input-group prepend="输入：">
                <b-form-input
                  autofocus
                  maxlength="4"
                  placeholder="输入验证码解锁"
                  v-model="captcha_code_input"
                  @keyup.enter="commit_captcha"
                ></b-form-input>
              </b-input-group>
              <img
                v-if="captcha_img"
                :src="'data:image/png;base64,' + captcha_img"
                @click="get_captcha"
              />
              <p>点击更换验证码。不区分大小写。</p>
            </div>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-button-group>
            <b-button :variant="button_theme" @click="commit_captcha"
              >提交</b-button
            >
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>
      <b-modal ref="color_modal" id="color_modal" class="color_modal">
        <template v-slot:modal-header>标题改色</template>
        <template v-slot:default>
          <p>要更换标题颜色吗？每次收费500个olo喔</p>
          <div class="my-1">
            <div class="my-1">
              <b-input-group prepend="颜色：">
                <b-form-input
                  autofocus
                  maxlength="7"
                  placeholder="#212529"
                  v-model="thread_color"
                  @keyup.enter="change_thread_color"
                ></b-form-input>
              </b-input-group>
              <ColorPicker v-model="thread_color" class="mt-2"></ColorPicker>
            </div>
          </div>
        </template>
        <template v-slot:modal-footer="{ cancel }">
          <b-button-group>
            <b-button :variant="button_theme" @click="change_thread_color"
              >提交</b-button
            >
            <b-button variant="outline-secondary" @click="cancel()">
              取消
            </b-button>
          </b-button-group>
        </template>
      </b-modal>

      <BattleModal ref="battle_modal"></BattleModal>
      <RewardModal ref="reward_modal"></RewardModal>
      <HongbaoModal ref="hongbao_modal"></HongbaoModal>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import PostInput from "../component/post_input.vue";
import PostItem from "../component/post_item.vue";
import ThreadPaginator from "./thread_paginator.vue";
import Emoji from "../component/emoji.vue";
import RewardModal from "./reward_modal.vue";
import Drawer from "../component/drawer.vue";
import VoteComponent from "./vote.vue";
import GambleComponent from "./gamble_component.vue";
import CrowdComponent from "./crowd_component.vue";
import HongbaoComponent from "./hongbao_component.vue";
import HongbaoPostComponent from "./hongbao_post_component.vue";
import HongbaoModal from "./hongbao_modal.vue";
import Battle from "./battle.vue";
import BattleModal from "./battle_modal.vue";
import ZBar from "../component/z_bar.vue";
import ColorPicker from "../component/color_picker.vue";

export default {
  name: "thread_page",
  components: {
    PostItem,
    PostInput,
    ThreadPaginator,
    Emoji,
    RewardModal,
    Drawer,
    VoteComponent,
    GambleComponent,
    CrowdComponent,
    HongbaoComponent,
    HongbaoPostComponent,
    HongbaoModal,
    Battle,
    BattleModal,
    ZBar,
    ColorPicker,
  },
  props: {
    thread_id: Number, //来自router，
    page: Number, //来自router
  },
  watch: {
    // 如果路由有变化，再次获得数据
    $route(to) {
      if (to.name == "thread") {
        //因为这个页面做了keep-alive，所以每次点进来都会算作$route变动
        this.get_browse_current();
        this.$store.commit("PostsLoadStatus_set", 1);
        if (this.search_input) {
          this.get_posts_data(false, false, this.search_input);
        } else {
          this.get_posts_data(false, true);
        }
        this.show_listen_next_page = false;
        this.is_listening = false;
      }
    },
    is_listening() {
      if (this.is_listening === true) {
        try {
          this.$echo
            .channel("thread_" + this.thread_id)
            .listen("NewPost", (response) => {
              if (response.post_floor >= this.page * 200) {
                //如果新回复通知中，楼层号大于本页的，则关闭监听并显示翻页选项
                this.is_listening = false;
                this.show_listen_next_page = true;
              } else {
                //否则，请求新回复数据
                var post_exist = this.$store.state.Posts.PostsData.data.find(
                  (post) => {
                    return post.id == response.post_id;
                  }
                );
                if (!post_exist) {
                  //如果post_id不存在，才去获取新数据
                  this.get_post_data_and_push(
                    response.thread_id,
                    response.post_id
                  );
                }
              }
            });
        } catch (e) {
          this.is_listening = false;
          alert("服务器的自动涮锅好像出错了，暂时不能使用");
        }
      } else {
        try {
          this.$echo.leaveChannel("thread_" + this.thread_id);
        } catch (e) {}
      }
    },
    no_video_mode() {
      localStorage.setItem("no_video_mode", this.no_video_mode ? "true" : "");
    },
    no_image_mode() {
      localStorage.setItem("no_image_mode", this.no_image_mode ? "true" : "");
    },
    no_emoji_mode() {
      localStorage.setItem("no_emoji_mode", this.no_emoji_mode ? "true" : "");
    },
    no_head_mode() {
      localStorage.setItem("no_head_mode", this.no_head_mode ? "true" : "");
    },
    no_battle_mode() {
      localStorage.setItem("no_battle_mode", this.no_battle_mode ? "true" : "");
    },
    no_roll_mode() {
      localStorage.setItem("no_roll_mode", this.no_roll_mode ? "true" : "");
    },
    no_hongbao_mode() {
      localStorage.setItem(
        "no_hongbao_mode",
        this.no_hongbao_mode ? "true" : ""
      );
    },
  },
  beforeRouteUpdate(to, from, next) {
    this.browse_record_handle(); //翻页时候记录浏览进度
    next();
  },
  data: function () {
    return {
      name: "thread_page",
      new_post_handling: false,
      content_temp: {}, //如果弹出验证码时，临时存储PostInput组件传过来的内容
      roll_name: "",
      roll_event: "",
      roll_num: 1,
      roll_range: 100,
      roll_handling: false,
      admin_button_show: false,

      jump_floor: "",
      jump_page: "",
      jump_page_show: false,

      no_video_mode: false,
      no_image_mode: false,
      no_emoji_mode: false,
      no_head_mode: false,
      no_battle_mode: false,
      no_roll_mode: false,
      no_hongbao_mode: false,

      browse_current: {
        expire_time: Date.now() + 86400000,
        page: 1,
        height: 0,
      },

      thread_color: "",
      drawer_insert_img: undefined,
      captcha_img: "",
      captcha_code_input: "",
      captcha_key: "",
      thread_response_code: 0,
      search_show: false,
      search_input: "",
      last_action: "",
      selected_img: undefined,
      is_focus: false,
      is_listening: false,
      show_listen_next_page: false,
    };
  },
  computed: {
    nissin_TTL() {
      const seconds =
        (Date.parse(
          this.$store.state.Threads.CurrentThreadData.nissin_date.replace(
            /-/g,
            "/"
          ) + " GMT+800"
        ) -
          Date.now()) /
        1000;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return hours + "小时" + minutes + "分钟";
    },
    roll_simulation() {
      var roll_simulation = [];
      if (this.roll_num > 0 && this.roll_range > 0) {
        for (var i = 0; i < this.roll_num; i++) {
          roll_simulation[i] = Math.floor(Math.random() * this.roll_range) + 1;
        }
        return roll_simulation.join();
      } else {
        return null;
      }
    },
    posts_data() {
      var filtered = this.$store.state.Posts.PostsData.data;
      if (filtered !== undefined) {
        //当屏蔽大乱斗点时，过滤不需要的数据
        if (this.no_battle_mode == true) {
          filtered = filtered.filter((post) => {
            if (post.battle_id != null) {
              return false;
            } else {
              return true;
            }
          });
        }
        //当屏蔽roll点时，过滤不需要的数据
        if (this.no_roll_mode == true) {
          filtered = filtered.filter((post) => {
            if (post.created_by_admin == 2 && post.nickname == "Roll点系统") {
              return false;
            } else {
              return true;
            }
          });
        }
        //当屏蔽红包结果时，过滤不需要的数据
        if (this.no_hongbao_mode == true) {
          filtered = filtered.filter((post) => {
            //红包结果屏蔽条件
            const condition1 =
              post.created_by_admin == 2 &&
              post.nickname == "红包结果" &&
              !post.is_your_post;
            //抢红包的口令屏蔽条件
            const condition2 =
              /--红包口令: /.test(post.content) && !post.is_your_post;
            if (condition1 || condition2) {
              return false;
            } else {
              return true;
            }
          });
        }
      }
      return filtered;
    },
    enable_listening() {
      if (this.page == this.posts_last_page) {
        return true;
      } else {
        return false;
      }
    },
    button_theme() {
      return this.$store.getters.ButtonTheme;
    },
    ...mapState({
      forum_name: (state) =>
        state.Forums.CurrentForumData.name
          ? state.Forums.CurrentForumData.name
          : "",
      forum_id: (state) =>
        state.Forums.CurrentForumData.id ? state.Forums.CurrentForumData.id : 0,
      thread_title: (state) => state.Threads.CurrentThreadData.title,
      thread_sub_id: (state) => state.Threads.CurrentThreadData.sub_id,
      thread_anti_jingfen: (state) =>
        state.Threads.CurrentThreadData.anti_jingfen,
      thread_posts_num: (state) => state.Threads.CurrentThreadData.posts_num,
      is_your_thread: (state) => state.Threads.CurrentThreadData.is_your_thread,
      vote_question_id: (state) =>
        state.Threads.CurrentThreadData.vote_question_id,
      gamble_question_id: (state) =>
        state.Threads.CurrentThreadData.gamble_question_id,
      crowd_id: (state) => state.Threads.CurrentThreadData.crowd_id,
      hongbao_id: (state) => state.Threads.CurrentThreadData.hongbao_id,
      random_heads_group: (state) =>
        state.Threads.CurrentThreadData.random_heads_group,
      posts_last_page: (state) => state.Posts.PostsData.lastPage,
      posts_load_status: (state) => state.Posts.PostsLoadStatus,
      locked_TTL: (state) => state.User.LockedTTL,
      random_heads_data: (state) => state.User.RandomHeads,
      less_toast: (state) => state.User.LessToast,
    }),
  },
  methods: {
    get_posts_data(
      remind = false,
      scroll_enable = false,
      search_content = null
    ) {
      if (search_content) {
        this.$store.commit("PostsLoadStatus_set", 1);
      }
      var config = {
        method: "get",
        url: "/api/threads/" + this.thread_id,
        params: {
          page: this.page,
          binggan: this.$store.state.User.Binggan,
        },
      };
      if (search_content) {
        config.params.search_content = search_content;
      }
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.thread_response_code = 0;
            this.$store.commit("PostsData_set", response.data.posts_data);
            this.$store.commit(
              "CurrentThreadData_set",
              response.data.thread_data
            );
            this.$store.commit(
              "CurrentForumData_set",
              response.data.forum_data
            );
            document.title = this.thread_title; //设置浏览器页面标签文字
            if (remind && !this.less_toast) {
              this.$bvToast.toast("已刷新帖子", {
                title: "Done.",
                autoHideDelay: 1500,
                appendToast: true,
              });
            }
            this.$nextTick(() => {
              //渲染完成后执行
              //如果有设定上次阅读进度，则滚动到上次进度
              if (
                this.browse_current.page == this.page &&
                typeof this.$store.state.User.BrowseLogger[
                  this.thread_id.toString()
                ] != "undefined" &&
                scroll_enable
              ) {
                this.scroll_to_lasttime();
              }
              //如果地址有#hash，则滚动到对应hash
              if (this.$route.hash && scroll_enable) {
                document
                  .querySelector(this.$route.hash)
                  .scrollIntoView({ block: "start", behavior: "auto" });
              }
              this.set_img_callee();
              this.load_focus_threads();
            });
          } else {
            if ([23410, 23401, 234011, 23404].includes(response.data.code)) {
              this.thread_response_code = response.data.code;
              //清空数据，避免显示上一个帖子的数据
              this.$store.commit("PostsData_set", "");
              this.$store.commit("CurrentThreadData_set", "");
              this.$store.commit("CurrentForumData_set", "");
            } else {
              this.thread_response_code = 0;
              alert(response.data.message);
            }
          }
          this.$store.commit("PostsLoadStatus_set", 2);
        })
        .catch((error) => {
          this.thread_response_code = 0;
          this.$store.commit("PostsLoadStatus_set", 2);
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    get_post_data_and_push(thread_id, post_id) {
      var scroll_top_now =
        document.body.clientHeight - document.documentElement.scrollTop; //用于使窗口位置保持不变
      var config = {
        method: "get",
        url: "/api/posts/" + post_id,
        params: {
          thread_id: thread_id,
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config).then((response) => {
        if (response.data.code == 200) {
          this.$store.commit("PostsData_push", response.data.post_data);
          this.$nextTick(() => {
            if (
              document.activeElement ===
              this.$refs.post_input_com.$refs.content_input
            ) {
              document.documentElement.scrollTop =
                document.body.clientHeight - scroll_top_now;
            } //如果正在输入，则使窗口位置保持不变
          });
        } else {
          console.log(response.data.message);
        }
      });
    },
    get_captcha() {
      const config = {
        method: "get",
        url: "/api/captcha/",
        params: {
          binggan: this.$store.state.User.Binggan,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.captcha_img = response.data.captcha_img;
            this.captcha_key = response.data.captcha_key;
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    search_clear() {
      this.search_input = "";
      if (this.page == 1) {
        this.get_posts_data();
      } else {
        this.$router.push("/thread/" + this.thread_id + "/1");
      }
    },
    search_handle() {
      if (this.page != 1) {
        this.$router.push("/thread/" + this.thread_id + "/1");
      } else {
        this.get_posts_data(false, false, this.search_input);
      }
    },
    commit_captcha() {
      const config = {
        method: "post",
        url: "/api/user/water_unlock/",
        params: {
          binggan: this.$store.state.User.Binggan,
          captcha_key: this.captcha_key,
          captcha_code: this.captcha_code_input,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            if (this.last_action == "new_post") {
              this.new_post_handle(this.content_temp);
            }
            if (this.last_action == "new_battle") {
              this.$refs.battle_modal.battle_handle();
            }
            this.modal_toggle("captcha_modal");
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    show_captcha() {
      this.get_captcha();
      this.modal_toggle("captcha_modal");
    },
    get_browse_current() {
      if (
        typeof this.$store.state.User.BrowseLogger[this.thread_id.toString()] !=
        "undefined"
      ) {
        this.browse_current = JSON.parse(
          JSON.stringify(
            this.$store.state.User.BrowseLogger[this.thread_id.toString()]
          )
        );
      }
      // const page = isNaN(this.page) ? 1 : this.page;
      if (
        this.browse_current.page > this.page &&
        typeof this.$store.state.User.BrowseLogger[this.thread_id.toString()] !=
          "undefined"
      ) {
        this.jump_page_show = true; //显示阅读进度页面跳转提示
      } else {
        this.jump_page_show = false;
      }
    },
    thread_delete_click_admin() {
      var content = prompt(
        "要用管理员权限删除这个主题吗？请输入理由",
        "请输入理由"
      );
      if (content != null) {
        const config = {
          method: "post",
          url: "/api/admin/thread_delete/",
          data: {
            thread_id: this.thread_id,
            content: content,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              alert(response.data.message);
              this.get_posts_data();
            } else {
              alert(response.data.message);
            }
          })
          .catch((error) => alert(error));
      }
    },
    change_thread_color() {
      if (this.thread_color != "") {
        const config = {
          method: "post",
          url: "/api/threads/change_color/",
          data: {
            thread_id: this.thread_id,
            color: this.thread_color,
            binggan: this.$store.state.User.Binggan,
          },
        };
        axios(config)
          .then((response) => {
            if (response.data.code == 200) {
              alert(response.data.message);
              this.modal_toggle("color_modal");
            } else {
              alert(response.data.message);
            }
          })
          .catch((error) => alert(error));
      } else {
        alert("未输入颜色");
      }
    },
    check_jingfen_admin() {
      alert("现在不需要这样喔~~");
      return; //暂时关闭此功能
      var content = prompt(
        "要用查看这个主题的反精分吗？请输入理由",
        "请输入理由"
      );
      const config = {
        method: "post",
        url: "/api/admin/check_jingfen",
        data: {
          thread_id: this.thread_id,
          content: content,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            this.$store.commit("PostsData_set", response.data.posts_data);
            this.$store.commit(
              "CurrentThreadData_set",
              response.data.thread_data
            );
            this.$store.commit("PostsLoadStatus_set", 1);
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => alert(error)); // Todo:写异常返回代码;
    },
    back_to_forum(new_tab) {
      if (new_tab) {
        let routeData = this.$router.resolve({
          name: "forum",
          params: { forum_id: this.forum_id },
        });
        window.open(routeData.href, "_blank");
      } else {
        this.$router.push({
          name: "forum",
          params: { forum_id: this.forum_id },
        });
      }
    },
    new_post_handle(content) {
      this.new_post_handling = true;
      this.last_action = "new_post";
      const timestamp = new Date().getTime();
      const config = {
        method: "post",
        url: "/api/posts/create",
        // headers: {
        //   "X-Socket-Id": this.$echo.socketId(),
        // },
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,

          //来自PostInput组件
          content: content.content_input,
          nickname: content.nickname_input,
          post_with_admin: content.post_with_admin,

          new_post_key: CryptoJS.MD5(
            this.thread_id +
              this.$store.state.User.Binggan +
              timestamp +
              content.ist
          ).toString(),
          timestamp: timestamp,
        },
      };
      axios(config)
        .then((response) => {
          if (response.data.code == 200) {
            if (!this.less_toast) {
              this.$bvToast.toast(response.data.message, {
                title: "Done.",
                autoHideDelay: 1500,
                appendToast: true,
              });
            }
            this.$refs.post_input_com.content_input = "";
            this.new_post_handling = false;
            this.get_posts_data();
          } else if (response.data.code == 244291) {
            this.new_post_handling = false;
            this.content_temp = content;
            this.show_captcha();
          } else {
            this.new_post_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.new_post_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    modal_toggle(modal_name) {
      this.$refs[modal_name].toggle();
    },
    roll_handle() {
      this.roll_handling = true;
      const config = {
        method: "post",
        url: "/api/posts/create_roll",
        data: {
          binggan: this.$store.state.User.Binggan,
          forum_id: this.forum_id,
          thread_id: this.thread_id,
          roll_name: this.roll_name,
          roll_event: this.roll_event,
          roll_range: this.roll_range,
          roll_num: this.roll_num,
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
            this.roll_handling = false;
            this.$refs["roll_modal"].hide();
            this.get_posts_data();
          } else {
            this.roll_handling = false;
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.roll_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    jump_handle() {
      if (this.jump_floor == "" && this.jump_page == "") {
        return;
      }
      if (this.jump_floor > this.thread_posts_num) {
        alert("最大跳到第" + this.thread_posts_num + "楼喔！");
        return;
      }
      if (this.jump_page > this.posts_last_page) {
        alert("最大跳到第" + this.posts_last_page + "页喔！");
        return;
      }
      if (this.jump_page) {
        const page = this.jump_page;
        var link = "/thread/" + this.thread_id + "/" + page;
      } else if (this.jump_floor) {
        const page = Math.ceil(this.jump_floor / 200);
        var link =
          "/thread/" + this.thread_id + "/" + page + "#f_" + this.jump_floor;
      }
      this.$router.push(link);
      this.$refs["jump_modal"].hide();
    },
    quote_click_handle(quote_content) {
      this.$refs.post_input_com.quote_click_handle(quote_content);
    },
    thread_set_top() {
      if (this.thread_sub_id === 0) {
        var user_confirm = confirm("把这个主题置顶吗？");
        if (user_confirm == true) {
          const config = {
            method: "post",
            url: "/api/admin/thread_set_top/",
            data: {
              thread_id: this.thread_id,
            },
          };
          axios(config)
            .then((response) => {
              if (response.data.code == 200) {
                alert(response.data.message);
                this.get_posts_data();
              } else {
                alert(response.data.message);
              }
            })
            .catch((error) => alert(error));
        }
      } else if (this.thread_sub_id !== 1) {
        var user_confirm = confirm("把这个主题取消置顶吗？");
        if (user_confirm == true) {
          const config = {
            method: "post",
            url: "/api/admin/thread_cancel_top/",
            data: {
              thread_id: this.thread_id,
            },
          };
          axios(config)
            .then((response) => {
              if (response.data.code == 200) {
                alert(response.data.message);
                this.get_posts_data();
              } else {
                alert(response.data.message);
              }
            })
            .catch((error) => alert(error));
        }
      }
    },
    scroll_to_lasttime() {
      if (!this.$route.hash) {
        //如果有to_hash，则停止使用上次阅读进度的滚动
        //不同浏览器可能支持不同，所以都用用
        document.body.scrollTop = this.browse_current.height;
        document.documentElement.scrollTop = this.browse_current.height;
        window.scrollTop = this.browse_current.height;
        if (!this.less_toast) {
          this.$bvToast.toast("已滚动到上次阅读进度", {
            title: "Done.",
            autoHideDelay: 1500,
            appendToast: true,
          });
        }
      }
    },
    scroll_watch() {
      if (
        this.browse_current.page <= this.page &&
        document.documentElement.scrollTop != 0
      ) {
        this.browse_current["height"] = document.documentElement.scrollTop;
      }
    },
    browse_record_handle() {
      //写入本次阅读进度
      if (
        this.browse_current.page <= this.page &&
        this.posts_data != null &&
        this.posts_data.length > 1
      ) {
        this.browse_current.page = this.page;
        this.$store.commit("BrowseLogger_set", {
          suffix: this.thread_id.toString(),
          browse_current: this.browse_current,
        });
        localStorage.browse_logger = JSON.stringify(
          this.$store.state.User.BrowseLogger
        );
      }
    },
    emit_reward(payload) {
      this.$refs.reward_modal.reward_click(payload);
    },
    keyup_callee(event) {
      if (event.ctrlKey && event.key === "q") {
        this.get_posts_data(true, false);
      }
    },
    set_img_callee() {
      let img_dom = document.querySelectorAll(".post_span img");
      var vm = this;
      for (var i = 0; i < img_dom.length; i++) {
        img_dom[i].onmousedown = function (e) {
          if (e.button == 0) {
            var clicked_dom = document.elementFromPoint(e.clientX, e.clientY);
            if (clicked_dom.getAttribute("src") != "/img_svg.svg") {
              //不处理特定的图片
              vm.selected_img = clicked_dom;
              vm.$bvToast.show("save_emoji_toast");
            }
          }
        };
      }
    },
    add_emoji_handle() {
      var my_emoji = this.$store.state.User.MyEmoji.emojis;
      if (my_emoji.includes(this.selected_img.src)) {
        alert("该表情包已保存过了");
        return;
      }
      // my_emoji.push(this.selected_img.src);
      const config = {
        method: "post",
        url: "/api/user/my_emoji_add",
        data: {
          binggan: this.$store.state.User.Binggan,
          my_emoji: this.selected_img.src,
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
            if (response.data.data.my_emoji != null) {
              this.$store.commit("MyEmoji_set", response.data.data.my_emoji);
            }
          } else {
            alert(response.data.message);
          }
        })
        .catch((error) => {
          this.my_emoji_set_handling = false;
          alert(Object.values(error.response.data.errors)[0]);
        });
    },
    set_focus_threads() {
      if (this.is_focus === false) {
        this.$store.commit("FocusThreads_set", {
          suffix: this.thread_id.toString(),
          posts_num: this.thread_posts_num,
        });
        this.is_focus = true;
        alert("已关注此主题");
      } else {
        this.$store.commit("FocusThreads_unset", {
          suffix: this.thread_id.toString(),
        });
        this.is_focus = false;
        alert("已取消关注此主题");
      }
    },
    load_focus_threads() {
      if (
        typeof this.$store.state.User.FocusThreads[this.thread_id.toString()] !=
        "undefined"
      ) {
        this.is_focus = true;
        this.$store.commit("FocusThreads_set", {
          suffix: this.thread_id.toString(),
          posts_num: this.thread_posts_num,
        });
      }
    },
    load_LocalStorage() {
      var localStorage_array = new Array(
        "no_video_mode",
        "no_image_mode",
        "no_emoji_mode",
        "no_head_mode",
        "no_battle_mode",
        "no_roll_mode",
        "no_hongbao_mode"
      );
      //遍历读取上述LocalStorage
      for (var i = 0; i < localStorage_array.length; i++) {
        if (localStorage.getItem(localStorage_array[i]) == null) {
          localStorage[localStorage_array[i]] = "";
        } else {
          this[localStorage_array[i]] = Boolean(
            localStorage[localStorage_array[i]]
          );
        }
      }
    },
    listen_channel() {
      //监听频道的启动在watch那里
      if (this.is_listening === false) {
        this.get_posts_data();
        this.is_listening = true;
      } else {
        this.is_listening = false;
      }
    },
  },
  created() {
    this.get_posts_data(false, true); //还是要在created时候获得数据，不然直接进入页面会没数据
    this.get_browse_current(); //还是要在created时候读取浏览进度，不然直接进入页面不会滚动
    this.$store.commit("PostsLoadStatus_set", 1); //避免显示上个ThreadsData
    this.load_LocalStorage();
  },
  activated() {
    this.load_LocalStorage();
    // this.get_browse_current();在watch $route那里
    // this.get_posts_data(false, true); //activated的获得数据靠watch:$route
    window.addEventListener("beforeunload", this.browse_record_handle);
    window.addEventListener("scroll", this.scroll_watch);
    window.addEventListener("keyup", this.keyup_callee);

    this.roll_handling = false;
    this.my_emoji_set_handling = false;
  },
  deactivated() {
    this.search_input = ""; //不然每次进入页面都带有search
    this.browse_record_handle();
    window.removeEventListener("beforeunload", this.browse_record_handle);
    window.removeEventListener("scroll", this.scroll_watch);
    window.removeEventListener("keyup", this.keyup_callee);
    try {
      //不想经常弹出错误
      this.$echo.leaveChannel("thread_" + this.thread_id);
    } catch (e) {}
  },
};
</script> 
