<template>
  <div>
      <h1>Testing component modal</h1>
      <button @click="$emit('close')" class="p-3 text-lg uppercase font-bold text-white bg-red-500">Close</button>
  </div>
</template>

<script>
export default {
    name: 'TestModal',
}
</script>

<style>

</style>