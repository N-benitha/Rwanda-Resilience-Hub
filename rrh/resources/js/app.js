import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Ziggy } from './ziggy.js';

window.Ziggy = Ziggy;
// Chart.js import
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Weather Chart Component
import WeatherChart from './Components/WeatherChart.vue';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Rwanda Resilience Hub';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('WeatherChart', WeatherChart)
            .mount(el);
    },
    progress: {
        color: '#d97706',
    },
});

// Dark mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    }

    // Initialize dark mode based on stored preference or system preference
    if (localStorage.getItem('theme') === 'dark' || 
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }

    // Real-time data updates
    initializeRealTimeUpdates();
    
    // Initialize notifications
    initializeNotifications();
    
    // Initialize location picker
    initializeLocationPicker();
});

// Real-time data updates using Server-Sent Events
function initializeRealTimeUpdates() {
    if (typeof window.rrh_config !== 'undefined' && window.rrh_config.enable_real_time) {
        const eventSource = new EventSource('/api/stream/weather-updates');
        
        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            updateWeatherDisplays(data);
        };
        
        eventSource.onerror = function(event) {
            console.error('SSE connection error:', event);
            // Attempt to reconnect after 5 seconds
            setTimeout(() => {
                initializeRealTimeUpdates();
            }, 5000);
        };
    }
}

// Update weather displays with new data
function updateWeatherDisplays(data) {
    // Update weather cards
    document.querySelectorAll('[data-weather-location]').forEach(card => {
        const location = card.dataset.weatherLocation;
        if (data.location === location) {
            updateWeatherCard(card, data);
        }
    });
    
    // Update flood risk meters
    document.querySelectorAll('[data-flood-risk-location]').forEach(meter => {
        const location = meter.dataset.floodRiskLocation;
        if (data.location === location && data.flood_risk) {
            updateFloodRiskMeter(meter, data.flood_risk);
        }
    });
}

// Update individual weather card
function updateWeatherCard(card, data) {
    const tempElement = card.querySelector('[data-temp]');
    const humidityElement = card.querySelector('[data-humidity]');
    const rainfallElement = card.querySelector('[data-rainfall]');
    const windElement = card.querySelector('[data-wind]');
    
    if (tempElement) tempElement.textContent = Math.round(data.temperature) + '°C';
    if (humidityElement) humidityElement.textContent = data.humidity + '%';
    if (rainfallElement) rainfallElement.textContent = data.rainfall + ' mm';
    if (windElement) windElement.textContent = data.wind_speed + ' km/h';
}

// Update flood risk meter
function updateFloodRiskMeter(meter, riskData) {
    const percentage = riskData.risk_score;
    const progressBar = meter.querySelector('.risk-progress');
    const percentageText = meter.querySelector('.risk-percentage');
    const levelText = meter.querySelector('.risk-level');
    
    if (progressBar) progressBar.style.width = percentage + '%';
    if (percentageText) percentageText.textContent = percentage.toFixed(1) + '%';
    
    if (levelText) {
        const level = percentage >= 80 ? 'High' : (percentage >= 50 ? 'Medium' : 'Low');
        levelText.textContent = level + ' Risk';
    }
}

// Initialize notifications system
function initializeNotifications() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    
    // Listen for flood alerts
    if (typeof window.rrh_config !== 'undefined' && window.rrh_config.enable_notifications) {
        const alertSource = new EventSource('/api/stream/flood-alerts');
        
        alertSource.onmessage = function(event) {
            const alert = JSON.parse(event.data);
            showFloodAlert(alert);
        };
    }
}

// Show flood alert notification
function showFloodAlert(alert) {
    // Browser notification
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('Flood Alert - Rwanda Resilience Hub', {
            body: `${alert.level} risk detected in ${alert.location}. ${alert.message}`,
            icon: '/favicon.ico',
            badge: '/favicon.ico'
        });
    }
    
    // In-app toast notification
    showToast(alert.message, alert.level);
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
    const bgColor = type === 'error' || type === 'High' ? 'bg-red-500' : 
                   type === 'warning' || type === 'Medium' ? 'bg-yellow-500' : 
                   type === 'success' || type === 'Low' ? 'bg-green-500' : 'bg-blue-500';
    
    toast.classList.add(bgColor);
    toast.innerHTML = `
        <div class="flex items-center text-white">
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Slide in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Initialize location picker
function initializeLocationPicker() {
    const locationInputs = document.querySelectorAll('input[data-location-picker]');
    
    locationInputs.forEach(input => {
        const button = input.nextElementSibling;
        if (button && button.classList.contains('location-btn')) {
            button.addEventListener('click', function() {
                getCurrentLocation(input);
            });
        }
    });
}

// Get current location
function getCurrentLocation(input) {
    if ('geolocation' in navigator) {
        const button = input.nextElementSibling;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Reverse geocoding to get location name
                fetch(`/api/geocode/reverse?lat=${lat}&lng=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.location) {
                            input.value = data.location;
                        }
                        button.innerHTML = originalText;
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        input.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        button.innerHTML = originalText;
                    });
            },
            function(error) {
                console.error('Geolocation error:', error);
                button.innerHTML = originalText;
                showToast('Unable to get your location. Please enter manually.', 'error');
            }
        );
    } else {
        showToast('Geolocation is not supported by this browser.', 'error');
    }
}

// Export functions for global use
window.RRH = {
    showToast,
    updateWeatherDisplays,
    getCurrentLocation
};