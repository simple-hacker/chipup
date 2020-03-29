<template>
  <div>
      <h1>Testing component modal</h1>
      <button @click="save" class="p-3 text-lg uppercase font-bold text-white bg-red-500">Close</button>
      <button @click="$snotify.success('Well done')" class="p-3 text-lg uppercase font-bold text-white bg-green-500">Snotify</button>
  </div>
</template>

<script>
export default {
    name: 'TestModal',
    methods: {
      save() {
        this.$emit('close');
        this.$snotify.success('Well done')
      }
    }
}
</script>

<style>

</style>