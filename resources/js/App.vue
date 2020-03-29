<template>
    <div class="flex flex-col h-screen">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 z-50 flex justify-between items-center px-3 py-2 bg-card">
            <h1 class="text-2xl font-bold text-white">Poker</h1>
            <a href="#" class="flex items-center bg-green-600 border border-green-700 hover:bg-green-700 py-2 px-4 uppercase text-white text-sm text-center">Start Session</a>
        </nav>

        <div class="flex flex-1 flex-col lg:flex-row lg:relative overflow-hidden">
            <div class="flex flex-auto justify-center pt-4 px-2 w-full mx-auto lg:w-2/3 lg:px-4 lg:order-last overflow-y-auto">
                <transition name="fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>

            <!-- Bottom Nav -->
            <nav aria-label="bottom-navigation" class="sticky bottom-0 p-2 flex justify-around items-center bg-card lg:order-first lg:flex-col lg:justify-start lg:items-start lg:w-1/6 lg:max-w-nav lg:p-3">
                <router-link
                    :to="{ name: 'session' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-plus fa-lg lg:w-1/5 text-muted-dark"></i><span class="hidden lg:block text-lg font-medium">Session</span>
                </router-link>
                <router-link
                    :to="{ name: 'stats' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-chart-line fa-lg lg:w-1/5 text-muted-dark"></i><span class="hidden lg:block text-lg font-medium">Statistics</span>
                </router-link>
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2 lg:order-first"
                    exact
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-th-large fa-lg lg:w-1/5 text-muted-dark"></i><span class="hidden lg:block text-lg font-medium">Dashboard</span>
                </router-link>
                <router-link
                    :to="{ name: 'sessions' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-bars fa-lg lg:w-1/5 text-muted-dark"></i><span class="hidden lg:block text-lg font-medium">Sessions</span>
                </router-link>
                <router-link
                    :to="{ name: 'settings' }"
                    class="w-1/6 flex justify-center items-center rounded-lg p-4 text-white hover:bg-green-500 hover:text-muted-dark focus:bg-green-500 focus:text-muted-dark flex lg:w-full lg:justify-start lg:p-3 lg:mb-2"
                    :active-class="'bg-green-600 text-muted-dark'"
                >
                    <i class="fas fa-cog fa-lg lg:w-1/5 text-muted-dark"></i><span class="hidden lg:block text-lg font-medium">Settings</span>
                </router-link>
            </nav>
            
        </div>

        <vue-snotify/>

        <modals-container/>

    </div>
</template>

<script>
export default {
    name: 'App',
}
</script>

<style scoped>

</style>