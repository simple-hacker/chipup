<template>
    <div class="w-full flex flex-col items-center">
        <form action="/login" method="POST" class="w-full">
            <div class="flex flex-col">
                <label for="email" class="text-sm font-bold text-gray-800">Email address</label>
                <input name="email" type="email" v-model="email" class="border p-2 w-full bg-white mb-2 focus:shadow" :class="errors.email ? 'border-red-700' : ''" placeholder="Email" required>
                <span v-if="errors.email" class="w-full text-center text-xs text-red-700 mt-1 mb-3">{{ errors.email[0] }}</span>
                <label for="password" class="mt-3 text-sm font-bold text-gray-800">Password</label>
                <input name="password" type="password" v-model="password" class="border p-2 w-full bg-white mb-2 focus:shadow" :class="errors.password ? 'border-red-700' : ''" placeholder="Password" required>
                <span v-if="errors.password" class="w-full text-center text-xs text-red-700 mt-1 mb-3">{{ errors.password[0] }}</span>
                <button @click.prevent="login" class="w-full bg-green-600 hover:bg-green-700 text-white uppercase mt-2 px-4 py-3" v-html="btnText"></button>
                <div class="flex justify-around mt-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="remeber" value="remember" class="mr-2">
                        <label for="remember" class="text-sm">Remember me</label>
                    </div>
                    <a href="/password-reset" class="text-green-600 text-sm font-bold">Forgot your password?</a>
                </div>
            </div>
        </form>
        <p class="text-sm border-t border-gray-400 mt-5 py-2 w-full text-center">Or login with</p>
        <a href="/https://www.facebook.com" class="flex justify-center items-center w-full bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><span class="text-white text-xl mr-4"><i class="fab fa-facebook-f"></i></span>Login with Facebook</a>
        <a href="/https://www.twitter.com" class="flex justify-center items-center w-full bg-twitter hover:bg-twitter-dark text-white uppercase mt-2 px-4 py-3"><span class="text-white text-xl mr-4"><i class="fab fa-twitter"></i></span>Login with Twitter</a>
        <a href="/register" class="border-t border-gray-400 mt-5 py-2 w-full text-center text-green-600 text-sm font-bold">Click here to sign up for an account</a>
    </div>
</template>

<script>
export default {
    name: 'LoginForm',
    data() {
        return {
            email: '',
            password: '',
            errors: [],
            btnText: 'Login'
        }
    },
    methods: {
        login: function() {
            this.btnText = '<i class="fas fa-lg fa-circle-notch fa-spin"></i>';

            axios.post('/login', {
                'email': this.email,
                'password': this.password,
            })
            .then(response => {
                if (response.status === 200) {
                    window.location = 'dashboard';
                }
            })
            .catch(e => {
                this.btnText = 'Login';
                this.errors = e.response.data.errors;
            });
        }
    }
}
</script>