<template>
    <div>
        <h1>Vue App</h1>

        <p>
            <router-link :to="{ name: 'dashboard' }">Dashboard</router-link> |
            <router-link :to="{ name: 'settings' }">Settings</router-link>
        </p>

        <div class="container">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
export default {
    name: 'App',
}
</script>

<style scoped>

</style>