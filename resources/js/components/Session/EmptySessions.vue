<template>
    <div class="w-full flex flex-col justify-center items-center text-white p-8">
        <img src="/images/money.svg" width="300px" alt="Empty Sessions" />
        <span class="text-xl md:text-2xl text-gray-100 text-center mb-4">You have not added any sessions yet.</span>
        <router-link
            :to="{ name: 'live' }"
            class="w-5/6 md:w-3/4"
        >
            <div class="flex p-4 justify-center items-center w-full bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-green-500 hover:border-green-400 shadow hover:shadow-2xl cursor-pointer text-white">
                <i class="fas fa-plus-circle fa-lgx mr-3"></i>
                <div class="text-white text-lg uppercase">Start New Live Session</div>
            </div>
        </router-link>
        <span class="text-xl md:text-2xl text-gray-100 text-center my-4">or</span>
        <router-link
            :to="{ name: 'createsession' }"
            class="w-5/6 md:w-3/4"
        >
            <div class="flex p-4 justify-center items-center w-full bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-red-500 hover:border-red-400 shadow hover:shadow-2xl cursor-pointer text-white">
                <i class="fas fa-plus-circle fa-lgx mr-3"></i>
                <div class="text-white text-lg uppercase">Add a previous session</div>
            </div>
        </router-link>
    </div>
</template>

<script>
export default {
    name: 'EmptySessions'
}
</script>

<style>

</style>