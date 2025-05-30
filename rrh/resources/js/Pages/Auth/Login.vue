<template>
    <Head title="Login" />

    <div class="min-h-screen bg-amber-800 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Logo and Title -->
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center space-x-2 mb-4">
                        <div class="w-12 h-12 bg-amber-800 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Rwanda</h1>
                            <p class="text-sm text-gray-600">Resilience Hub</p>
                        </div>
                    </div>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <!-- Username/Email Input -->
                        <div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="Username"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
                                autocomplete="current-password"
                            />
                            <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-amber-800 focus:ring-amber-800 border-gray-300 rounded"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        <!-- Login Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-amber-800 text-white py-3 px-4 rounded-lg font-medium hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-800 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="form.processing">Signing in...</span>
                            <span v-else>Log In</span>
                        </button>
                    </div>

                    <!-- Links -->
                    <div class="mt-6 flex items-center justify-between text-sm">
                        <Link
                            v-if="route('password.request')"
                            :href="route('password.request')"
                            class="text-gray-600 hover:text-amber-800 transition-colors"
                        >
                            Forget password
                        </Link>
                        <div class="flex items-center space-x-1">
                            <span class="text-gray-600">Don't have account?</span>
                            <Link
                                :href="route('register')"
                                class="text-blue-600 hover:text-blue-800 font-medium transition-colors"
                            >
                                Signup
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    console.log('Form submitted:', form.data());
    
    form.post(route('login'), {
        onFinish: () => {
            console.log('Form finished');            
            form.reset('password')
        },
        onSuccess: () => {
            console.log('Login successful');            
        },
        onError: (errors) => {
            console.error('Login errors:', errors);
        }
    })
}
</script>