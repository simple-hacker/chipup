<template>
    <div class="w-full">
        <form action="/login" method="POST" class="w-full flex flex-col mb-5">
            <div class="flex flex-col mb-3">
                <label for="email" class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">Email address</label>
                <input
                    v-model="email"
                    name="email"
                    type="email"
                    :class="{'error-input' : errors.email}"
                    @input="delete errors.email"
                    placeholder="Email"
                    required
                >
                <span v-if="errors.email" class="error-message">{{ errors.email[0] }}</span>
            </div>
            <div class="flex flex-col mb-3">
                <label for="password" class="mt-3 text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">Password</label>
                <input
                    v-model="password"
                    name="password"
                    type="password"
                    :class="{'error-input' : errors.password}"
                    @input="delete errors.password"
                    placeholder="Password"
                    required
                >
                <span v-if="errors.password" class="error-message">{{ errors.password[0] }}</span>
            </div>

            <button @click.prevent="login" class="btn btn-green my-3 py-4" v-html="btnText"></button>

            <div class="flex justify-around mt-3">
                <div class="flex justify-start items-center w-64">
                    <input type="checkbox" name="remember" v-model="remember" class="mr-2">
                    <label for="remember" class="w-56 text-sm uppercase tracking-wider font-bold text-gray-200">Remember me</label>
                </div>
                <a href="/password-reset" class="text-green-500 hover:text-green-400 uppercase text-sm font-bold tracking-wide">Forgot your password?</a>
            </div>
        </form>

        <p class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">Or login with</p>
        <a href="login/facebook" class="flex justify-center items-center w-full rounded text-sm bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><i class="fab fa-facebook fa-2x text-white mr-4"></i>Continue with Facebook</a>
    </div>
</template>

<script>
export default {
    name: 'LoginForm',
    data() {
        return {
            email: '',
            password: '',
            remember: false,
            errors: {},
            btnText: 'Login'
        }
    },
    methods: {
        login() {
            this.btnText = '<i class="fas fa-lg fa-circle-notch fa-spin"></i>';

            // Login via /sanctum/csrf-cookie to initialize Sanctum cookies, then proceed to login.
            axios.get('/sanctum/csrf-cookie')
            .then(response => {
                axios.post('/login', {
                    'email': this.email,
                    'password': this.password,
                    'remember': this.remember,
                })
                .then(response => {
                    if (response.status === 200) {
                        window.location = 'dashboard'
                    }
                })
                .catch(e => {
                    this.btnText = 'Login'
                    this.errors = e.response.data.errors
                });
            })
            .catch( error => {
                console.log(error)
            });
            
        }
    }
}
</script>