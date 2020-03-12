<template>
    <div class="w-full">
        <form action="/login" method="POST">
            <div class="flex flex-col">
                <input name="email" type="email" v-model="email" class="border p-2 w-full bg-white mb-2" :class="errors.email ? 'border-red-700' : ''" placeholder="Email" required>
                <span v-if="errors.email" class="w-full text-center text-xs text-red-700 mt-1 mb-3">{{ errors.email[0] }}</span>
                <input name="password" type="password" v-model="password" class="border p-2 w-full bg-white mb-2" :class="errors.password ? 'border-red-700' : ''" placeholder="Password" required>
                <span v-if="errors.password" class="w-full text-center text-xs text-red-700 mt-1 mb-3">{{ errors.password[0] }}</span>
                <input name="password_confirmation" type="password" v-model="password_confirmation" class="border p-2 w-full bg-white mb-2" :class="errors.password_confirmation ? 'border-red-700' : ''" placeholder="Confirm Password" required>
                <span v-if="errors.password_confirmation" class="w-full text-center text-xs text-red-700 mt-1 mb-3">{{ errors.password_confirmation[0] }}</span>
                <button @click.prevent="login" class="w-full bg-green-600 text-white uppercase mt-2 px-4 py-3" v-html="btnText"></button>
            </div>
        </form>
        <p class="text-sm border-t border-gray-400 mt-5 py-2 w-full text-center">Or create an account using</p>
        <a href="/https://www.facebook.com" class="flex justify-center items-center w-full bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><span class="text-white text-xl mr-4"><i class="fab fa-facebook-f"></i></span>Sign Up with Facebook</a>
        <a href="/https://www.twitter.com" class="flex justify-center items-center w-full bg-twitter hover:bg-twitter-dark text-white uppercase mt-2 px-4 py-3"><span class="text-white text-xl mr-4"><i class="fab fa-twitter"></i></span>Sign Up with Twitter</a>
    </div>
</template>

<script>
export default {
    name: 'RegisterForm',
    data() {
        return {
            email: '',
            password: '',
            password_confirmation: '',
            errors: [],
            btnText: 'Register'
        }
    },
    methods: {
        login: function() {
            this.btnText = '<i class="fas fa-lg fa-circle-notch fa-spin"></i>';

            axios.post('/register', {
                'email': this.email,
                'password': this.password,
                'password_confirmation': this.password_confirmation
            })
            .then(response => {
                if (response.status === 200) {
                    window.location = 'setup';
                }
            })
            .catch(e => {
                this.btnText = 'Register';
                this.errors = e.response.data.errors;
            });
        }
    }
}
</script>