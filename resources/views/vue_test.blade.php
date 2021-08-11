<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>Vue test</title>
</head>

<style>
    [v-cloak] {
        display: none;
    }
</style>

<body>
    <div id="app" v-cloak>
        <input type="text" @input="name_input" placeholder="名字">
        <h1> Hello! @{{ name }}</h1>
        <input type="text" v-model.number="name" placeholder="名字">
        <h1> Hello! @{{ name }}</h1>

        <label><input type="radio" v-model="picked" value="choice_1">选择1</label>
        <br>
        <input type="radio" v-model="picked" value="choice_2" id='choice_2'><label for='choice_2'>选择2</label>
        <br>
        <input type="radio" v-model="picked" value="choice_3" id='choice_3'><label for='choice_3'>选择3</label>
        <br>

        <p v-if="show">测试文本</p>
        <button v-on:click="handle">@{{btn_text}}</button>

        <p>发送ajax：</p>
        <button v-on:click="check_DB">执行</button>
        <p>1版名称： @{{forum_name}}</p>

        <input type="text" v-model="number" placeholder="计算数字^2">
        <p>计算属性结果：@{{result}}</p>

        <p :class="{my_class:class_select}">my_class</p>

    </div>

    <div id="app2" v-cloak>

        <template v-if="type === 'name'">
            <label>用户名</label>
            <input placeholder="用户名" key="test">
        </template>

        <template v-else>
            <label>邮箱</label>
            <input placeholder="邮箱" key='test'>
        </template>

        <button @click="handleClick" @keyup.shift.83='handle'>切换</button>


    </div>


</body>

<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>


<script>
    var app2 = new Vue({
        el: "#app2",
        data: {
            type: 'name',
        },
        methods: {
            handleClick: function() {
                this.type = this.type === 'name' ? 'mail' : 'name'
            }
        }

    })
    var app3 = new Vue({
        el: '#app3',
        data: {
            name: '',
            show: true,
            btn_text: '隐藏',
            link: '<a href="#">This is link</a>',
            forum_name: '',
            // result: 0,
            number: 0,
            my_class: "real_class",
            class_select: false,
            picked: 'choice_1',
        },
        methods: {
            handle: function() {
                console.log(this.show);
                if (this.show === false) {
                    this.show = true;
                    this.btn_text = "隐藏";
                } else {
                    this.show = false;
                    this.btn_text = "显示";
                };
                // this.show = false;  
            },
            name_input: function(e) {
                this.name = e.target.value;
            },
            check_DB: function() {
                config = {
                    method: 'get',
                    url: '/api/forums/1',
                    data: {
                        firstName: 'Fred',
                        lastName: 'Flintstone'
                    }
                }
                // 发送 POST 请求
                axios(config).then(response => (this.forum_name = response.data.name))
                // axios
                //     .get('http://laravel.test/api/forums/1')
                //     .then(response => (this.forum_name = response.data.name))

            }
        },
        computed: {
            result: function() {
                return this.number ** 2
            }
        }
    })
</script>







</html>