<template>
    <div class="flex flex-col h-screen">
        <!-- Top Nav -->
        <nav aria-label="top-navigation" class="sticky top-0 flex justify-between items-center sticky px-3 py-2 bg-gray-200 border-b border-gray-400 shadow">
            <h1 class="text-2xl font-bold">Poker</h1>
            <a href="#" class="flex items-center bg-green-600 border border-green-700 hover:bg-green-700 py-2 px-4 uppercase text-white text-sm text-center">Start Session</a>
        </nav>

        <div class="flex-1">
            <transition name="fade" mode="out-in">
                <router-view></router-view>
            </transition>
        </div>

        <!-- Bottom Nav -->
        <nav aria-label="bottom-navigation" class="sticky bottom-0 flex justify-around items-center sticky bg-gray-200 border-t border-gray-400 shadow">
            <router-link :to="{ name: 'session' }" class="w-1/6 text-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300" ><i class="fas fa-plus fa-lg"></i></router-link>
            <router-link :to="{ name: 'stats' }" class="w-1/6 text-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300" ><i class="fas fa-chart-line fa-lg"></i></router-link>
            <router-link :to="{ name: 'dashboard' }" class="w-1/6 text-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300" ><i class="fas fa-th-large fa-lg"></i></router-link>
            <router-link :to="{ name: 'bankroll' }" class="w-1/6 text-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300" ><i class="fas fa-pound-sign fa-lg"></i></router-link>
            <router-link :to="{ name: 'settings' }" class="w-1/6 text-center p-4 text-gray-700 hover:bg-gray-300 focus:bg-gray-300" ><i class="fas fa-cog fa-lg"></i></router-link>
        </nav>
    </div>
</template>

<script>
export default {
    name: 'App',
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition-duration: 0.15s;
    transition-property: opacity;
    transition-timing-function: ease;
}

.fade-enter,
.fade-leave-active {
    opacity: 0
}
</style>