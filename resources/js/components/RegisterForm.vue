<template>
    <div class="w-full">
        <form action="/register" method="POST" class="flex flex-col">
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
            <div class="flex flex-col mb-3">
                <label for="password_confirmation" class="mt-3 text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">Confirm Password</label>
                <input
                    v-model="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    :class="{'error-input' : errors.password_confirmation}"
                    @input="delete errors.password_confirmation"
                    placeholder="Confirm Password"
                    required
                >
                <span v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation[0] }}</span>
            </div>

            <button @click.prevent="register" class="btn btn-green my-3 py-4" v-html="btnText"></button>

        </form>

        <div class="mt-5">
            <p class="text-sm uppercase tracking-wider font-bold text-gray-200 mb-1">Or create an account using</p>
            <a href="login/facebook" class="flex justify-center items-center w-full rounded text-sm bg-facebook hover:bg-facebook-dark text-white uppercase mt-2 px-4 py-3"><i class="fab fa-facebook fa-2x text-white mr-4"></i>Sign Up with Facebook</a>
        </div>
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
            errors: {},
            btnText: 'Create Account'
        }
    },
    methods: {
        register() {
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
                this.btnText = 'Create Account';
                this.errors = e.response.data.errors;
                console.log(e.response.data.errors)
                console.log(this.errors)
            });
        }
    }
}
</script>