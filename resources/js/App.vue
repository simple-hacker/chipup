<template>
    <div class="flex flex-col h-screen">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 z-50 flex justify-between items-center px-3 py-2 bg-gray-200 border-b border-gray-400 shadow">
            <h1 class="text-2xl font-bold">Poker</h1>
            <a href="#" class="flex items-center bg-green-600 border border-green-700 hover:bg-green-700 py-2 px-4 uppercase text-white text-sm text-center">Start Session</a>
        </nav>

        <div class="flex flex-1 flex-col lg:flex-row lg:relative overflow-hidden">
            <div class="flex flex-1 justify-center py-4 px-2 w-full mx-auto lg:w-2/3 lg:order-last overflow-y-auto">
                <transition name="fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>

            <!-- Bottom Nav -->
            <nav aria-label="bottom-navigation" class="sticky bottom-0 flex justify-around items-center bg-gray-200 border-t border-gray-400 shadow lg:order-first lg:flex-col lg:justify-start lg:items-start lg:w-1/6 lg:max-w-nav">
                <router-link
                    :to="{ name: 'session' }"
                    class="w-1/6 flex justify-center items-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300 flex lg:w-full lg:justify-start lg:p-3"
                >
                    <i class="fas fa-plus fa-lg lg:w-1/5"></i><span class="hidden lg:block text-xl">Session</span>
                </router-link>
                <router-link
                    :to="{ name: 'stats' }"
                    class="w-1/6 flex justify-center items-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300 flex lg:w-full lg:justify-start lg:p-3"
                >
                    <i class="fas fa-chart-line fa-lg lg:w-1/5"></i><span class="hidden lg:block text-xl">Statistics</span>
                </router-link>
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="w-1/6 flex justify-center items-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300 flex lg:w-full lg:justify-start lg:p-3 lg:order-first"
                >
                    <i class="fas fa-th-large fa-lg lg:w-1/5"></i><span class="hidden lg:block text-xl">Dashboard</span>
                </router-link>
                <router-link
                    :to="{ name: 'bankroll' }"
                    class="w-1/6 flex justify-center items-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300 flex lg:w-full lg:justify-start lg:p-3"
                >
                    <i class="fas fa-pound-sign fa-lg lg:w-1/5"></i><span class="hidden lg:block text-xl">Bankroll</span>
                </router-link>
                <router-link
                    :to="{ name: 'settings' }"
                    class="w-1/6 flex justify-center items-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300 flex lg:w-full lg:justify-start lg:p-3"
                >
                    <i class="fas fa-cog fa-lg lg:w-1/5"></i><span class="hidden lg:block text-xl">Settings</span>
                </router-link>
            </nav>
            
        </div>

        <vue-snotify></vue-snotify>

    </div>
</template>

<script>
export default {
    name: 'App',
}
</script>

<style scoped>

</style>