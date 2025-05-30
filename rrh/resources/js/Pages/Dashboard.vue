<template>
  <AppLayout title="Dashboard">
    <div class="bg-gray-50 min-h-screen">
      <!-- Header -->
      <header class="bg-white shadow-sm">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
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
            
            <!-- Navigation -->
            <div class="flex items-center space-x-8">
              <Link href="/" class="flex items-center space-x-1 text-gray-700 hover:text-amber-800 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span>HOME</span>
              </Link>
              <Link href="/alerts" class="flex items-center space-x-1 text-gray-700 hover:text-amber-800 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>ALERT</span>
              </Link>
              <Link href="/flooding-data" class="text-gray-700 hover:text-amber-800 transition-colors">Flooding Data Collection</Link>
              <Link href="/resources" class="text-gray-700 hover:text-amber-800 transition-colors">Resources</Link>
              <Link href="/reports" class="text-gray-700 hover:text-amber-800 transition-colors">Report</Link>
              
              <!-- User Menu -->
              <div class="relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <button class="flex items-center space-x-2 bg-amber-800 text-white px-4 py-2 rounded-full hover:bg-amber-900 transition-colors">
                      <span>{{ $page.props.auth.user.name || 'User' }}</span>
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>
          </div>
        </nav>
      </header>

      <!-- Main Content -->
      <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <!-- Data Collection Section -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
          <!-- Search Location -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center space-x-2 mb-4">
              <input
                v-model="searchLocation"
                type="text"
                placeholder="Search for location"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-800 focus:border-transparent"
              />
              <button 
                @click="searchForLocation"
                class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Rainfall Data -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Rainfall data</h3>
            <div class="mb-4">
              <div class="flex items-center space-x-2">
                <input
                  v-model="rainfallData.rate"
                  type="number"
                  step="0.01"
                  class="w-20 px-2 py-1 border border-gray-300 rounded text-center"
                />
                <span class="text-sm text-gray-600">mm/hr</span>
                <button class="p-1 text-gray-400 hover:text-gray-600">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Rainfall Chart -->
            <div class="bg-gray-100 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Rainfall Moderate</span>
                <span class="text-sm text-gray-600">Heavy rain</span>
              </div>
              <div class="relative">
                <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto relative">
                  <div 
                    class="absolute inset-0 rounded-full" 
                    :style="rainfallChartStyle"
                  ></div>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold text-gray-700">{{ rainfallData.rate }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- River Level -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">River level</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Water Level</label>
                <input
                  v-model="riverData.waterLevel"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Flow level</label>
                <input
                  v-model="riverData.flowLevel"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Timestamp</label>
                <input
                  v-model="riverData.timestamp"
                  type="datetime-local"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Threshold Indicator</label>
                <select 
                  v-model="riverData.thresholdIndicator"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                >
                  <option value="normal">Normal</option>
                  <option value="warning">Warning</option>
                  <option value="danger">Danger</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Soil Moisture -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
          <h3 class="text-lg font-semibold mb-4">Soil moisture</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm text-gray-600 mb-1">Soil moisture level (%)</label>
              <input
                v-model="soilData.moistureLevel"
                type="number"
                min="0"
                max="100"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm text-gray-600 mb-1">Measurement depth (cm)</label>
              <input
                v-model="soilData.measurementDepth"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm text-gray-600 mb-1">Sensor identification and location</label>
              <input
                v-model="soilData.sensorLocation"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
          </div>
        </div>

        <!-- Flood Risk -->
        <div class="text-center mb-8">
          <h2 class="text-4xl font-bold" :class="floodRiskColorClass">
            Flood Risk: {{ floodRisk.toFixed(3) }}%
          </h2>
          <div class="mt-2">
            <span 
              class="px-4 py-2 rounded-full text-sm font-medium"
              :class="floodRiskBadgeClass"
            >
              {{ floodRiskLevel }}
            </span>
          </div>
        </div>

        <!-- Prediction and Live Stream -->
        <div class="grid lg:grid-cols-2 gap-8 mb-8">
          <!-- Real-time Prediction -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Real time prediction</h3>
            <p class="text-sm text-gray-600 mb-4">Daily Flood Prediction for Next 7 Days</p>
            
            <!-- Chart Placeholder -->
            <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
              <div class="text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                <p class="text-gray-500">Prediction Chart</p>
                <button 
                  @click="loadPredictionData"
                  class="mt-2 px-4 py-2 bg-amber-800 text-white rounded-lg hover:bg-amber-900 transition-colors"
                >
                  Load Predictions
                </button>
              </div>
            </div>
          </div>

          <!-- Live Stream -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Live stream</h3>
            
            <!-- Map Placeholder -->
            <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
              <div class="text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-gray-500">Rwanda Map</p>
                <p class="text-xs text-gray-400 mt-1">Interactive map will be integrated here</p>
              </div>
            </div>

            <!-- Conclusion -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-2">Conclusion of prediction</h4>
              <p class="text-sm text-gray-600">
                The system predicts flood risks by analyzing rainfall, river levels, and soil moisture. 
                It forecasts rainfall for 10 days, with future upgrades including real-time IoT sensor 
                integration for improved accuracy.
              </p>
            </div>
          </div>
        </div>

        <!-- Generate Report Button -->
        <div class="text-center mb-8">
          <button 
            @click="generateReport"
            :disabled="reportLoading"
            class="bg-amber-800 text-white px-8 py-3 rounded-lg font-medium hover:bg-amber-900 transition-colors shadow-lg disabled:opacity-50"
          >
            <span v-if="reportLoading">Generating...</span>
            <span v-else>Generate Report</span>
          </button>
        </div>
      </main>

      <!-- Footer -->
      <footer class="bg-gray-900 text-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-400 text-sm">
                Rwanda Resilience Hub is technology-driven flood early warning and disaster 
                resilience system to enhance Rwanda's flood preparedness and response capabilities.
              </p>
            </div>
            <div class="text-right">
              <div class="text-gray-400 text-sm">
                <p>Contact: info@rwandaresilience.gov.rw</p>
                <p>Emergency: +250 788 311 122</p>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

// Reactive data
const searchLocation = ref('')
const reportLoading = ref(false)

const rainfallData = ref({
  rate: 10.28
})

const riverData = ref({
  waterLevel: '',
  flowLevel: '',
  timestamp: '',
  thresholdIndicator: 'normal'
})

const soilData = ref({
  moistureLevel: '',
  measurementDepth: '',
  sensorLocation: ''
})

const floodRisk = ref(82.345)

// Computed properties
const rainfallChartStyle = computed(() => {
  const percentage = Math.min(rainfallData.value.rate / 20 * 100, 100)
  const degrees = (percentage / 100) * 360
  return `background: conic-gradient(#ef4444 0deg ${degrees}deg, #e5e7eb ${degrees}deg 360deg)`
})

const floodRiskLevel = computed(() => {
  if (floodRisk.value >= 80) return 'CRITICAL'
  if (floodRisk.value >= 60) return 'HIGH'
  if (floodRisk.value >= 40) return 'MODERATE'
  if (floodRisk.value >= 20) return 'LOW'
  return 'MINIMAL'
})

const floodRiskColorClass = computed(() => {
  if (floodRisk.value >= 80) return 'text-red-600'
  if (floodRisk.value >= 60) return 'text-orange-600'
  if (floodRisk.value >= 40) return 'text-yellow-600'
  if (floodRisk.value >= 20) return 'text-blue-600'
  return 'text-green-600'
})

const floodRiskBadgeClass = computed(() => {
  if (floodRisk.value >= 80) return 'bg-red-100 text-red-800'
  if (floodRisk.value >= 60) return 'bg-orange-100 text-orange-800'
  if (floodRisk.value >= 40) return 'bg-yellow-100 text-yellow-800'
  if (floodRisk.value >= 20) return 'bg-blue-100 text-blue-800'
  return 'bg-green-100 text-green-800'
})

// Methods
const searchForLocation = () => {
  if (searchLocation.value.trim()) {
    console.log('Searching for:', searchLocation.value)
    // TODO: Implement location search with API
  }
}

const loadPredictionData = () => {
  console.log('Loading prediction data...')
  // TODO: Implement prediction data loading
}

const generateReport = async () => {
  reportLoading.value = true
  try {
    // TODO: Implement report generation
    await new Promise(resolve => setTimeout(resolve, 2000)) // Simulate API call
    console.log('Report generated successfully')
  } catch (error) {
    console.error('Error generating report:', error)
  } finally {
    reportLoading.value = false
  }
}

// Calculate flood risk based on available data
const calculateFloodRisk = () => {
  let risk = 0
  
  // Rainfall contribution (40% weight)
  const rainfallRisk = Math.min(rainfallData.value.rate / 20 * 40, 40)
  risk += rainfallRisk
  
  // River level contribution (35% weight)
  const riverRisk = riverData.value.thresholdIndicator === 'danger' ? 35 : 
                   riverData.value.thresholdIndicator === 'warning' ? 20 : 5
  risk += riverRisk
  
  // Soil moisture contribution (25% weight)
  const soilRisk = soilData.value.moistureLevel > 80 ? 25 : 
                  soilData.value.moistureLevel > 60 ? 15 : 5
  risk += soilRisk
  
  floodRisk.value = Math.min(risk, 100)
}

// Watch for data changes and recalculate risk
const updateFloodRisk = () => {
  calculateFloodRisk()
}

onMounted(() => {
  // Set current timestamp
  riverData.value.timestamp = new Date().toISOString().slice(0, 16)
  
  // Initial calculation
  calculateFloodRisk()
})
</script>