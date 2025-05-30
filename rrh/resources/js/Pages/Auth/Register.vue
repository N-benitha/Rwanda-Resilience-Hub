<template>
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

                <!-- Register Form -->
                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <!-- First Name Input -->
                        <div>
                            <input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                placeholder="First name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
                                autofocus
                                autocomplete="given-name"
                            />
                            <div v-if="form.errors.first_name" class="mt-2 text-sm text-red-600">
                                {{ form.errors.first_name }}
                            </div>
                        </div>

                        <!-- Last Name Input -->
                        <div>
                            <input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                placeholder="Last name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
                                autocomplete="family-name"
                            />
                            <div v-if="form.errors.last_name" class="mt-2 text-sm text-red-600">
                                {{ form.errors.last_name }}
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="Gmail"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
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
                                autocomplete="new-password"
                            />
                            <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <!-- Confirm Password Input -->
                        <div>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Confirm Password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
                                required
                                autocomplete="new-password"
                            />
                            <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>

                        <!-- Terms and Privacy -->
                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="flex items-start">
                            <input
                                id="terms"
                                v-model="form.terms"
                                type="checkbox"
                                class="h-4 w-4 text-amber-800 focus:ring-amber-800 border-gray-300 rounded mt-1"
                                required
                            />
                            <div class="ml-3">
                                <label for="terms" class="text-sm text-gray-700">
                                    I agree to the
                                    <a :href="route('terms.show')" class="text-amber-800 hover:text-amber-900 font-medium">Terms of Service</a>
                                    and
                                    <a :href="route('policy.show')" class="text-amber-800 hover:text-amber-900 font-medium">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <!-- Register Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-amber-800 text-white py-3 px-4 rounded-lg font-medium hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-800 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="form.processing">Creating account...</span>
                            <span v-else>Sign up</span>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="mt-6 text-center text-sm">
                        <span class="text-gray-600">Already have an account?</span>
                        <Link
                            :href="route('login')"
                            class="ml-1 text-blue-600 hover:text-blue-800 font-medium transition-colors"
                        >
                            Sign in
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>