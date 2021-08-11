<template>
  <div>
    <p>total:{{ counter }}</p>
    <button @click="handleIncrease">+1</button>
    <button @click="handleDecrease">-1</button>
  </div>
</template>


<script>
export default {
  data: function () {
    return {
      name: "my-component",
      counter: 0,
    };
  },
  methods: {
    handleIncrease: function () {
      this.counter++;
      this.$emit("increase", this.counter);
    },
    handleDecrease: function () {
      this.counter--;
      this.$emit("decrease", this.counter);
    },
  },
};


</script>