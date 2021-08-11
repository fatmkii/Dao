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
    <Card style="width:500px;">
        <p slot="title">title</p>
        <a href="#" slot="extra">
            <Icon type="android-close" size="18"></Icon>
        </a>
        <div style="height: 100px;">

        </div>
    </Card>

    <div id="app" v-cloak>
        <p>total:{{total}}</p>
        <my-component2 @increase='handleGetTotal' @decrease='handleGetTotal'></my-component2>

        <my-component message="提示信息："></my-component>
        <div is="my-component" message="提示信息"></div>
    </div>

</body>

<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>




<script>
    Vue.component('my-component2', {
        template: '<div>\
        <button @click = "handleIncrease">+1</button>\
        <button @click = "handleDecrease">-1</button>\
        </div>\
        ',
        data: function() {
            return {
                counter: 0
            }
        },
        methods: {
            handleIncrease: function() {
                this.counter++
                this.$emit('increase', this.counter)
            },
            handleDecrease: function() {
                this.counter--
                this.$emit('decrease', this.counter)
            }
        }
    })

    Vue.component('my-component', {
        props: ['message'],
        template: '<div>父级信息{{message}}</div>',
    })

    var app = new Vue({
        el: '#app',
        data: {
            total: 0

        },
        methods: {
            handleGetTotal: function(total) {
                this.total = total
            }

        },
        computed: {

        }
    })
</script>

</html>