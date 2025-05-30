<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border border-amber-200 dark:border-amber-700">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-amber-900 dark:text-amber-100">{{ title }}</h3>
      <div class="flex space-x-2">
        <button 
          v-for="type in chartTypes" 
          :key="type"
          @click="changeChartType(type)"
          :class="[
            'px-3 py-1 text-xs rounded-md transition-colors',
            currentType === type 
              ? 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200' 
              : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
          ]"
        >
          {{ type.charAt(0).toUpperCase() + type.slice(1) }}
        </button>
      </div>
    </div>
    
    <div class="relative">
      <canvas 
        :id="chartId" 
        ref="chartCanvas"
        class="w-full h-64"
      ></canvas>
    </div>
    
    <!-- Loading indicator -->
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-75">
      <div class="flex items-center space-x-2">
        <svg class="animate-spin w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-amber-600 dark:text-amber-400">Loading chart data...</span>
      </div>
    </div>
    
    <!-- Chart Legend -->
    <div class="mt-4 flex flex-wrap gap-4 text-sm">
      <div v-for="dataset in chartData.datasets" :key="dataset.label" class="flex items-center">
        <div 
          class="w-3 h-3 rounded-full mr-2" 
          :style="{ backgroundColor: dataset.borderColor || dataset.backgroundColor }"
        ></div>
        <span class="text-gray-700 dark:text-gray-300">{{ dataset.label }}</span>
      </div>
    </div>
    
    <!-- Chart Controls -->
    <div class="mt-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <label class="text-sm text-gray-600 dark:text-gray-400">Time Range:</label>
        <select 
          v-model="timeRange" 
          @change="updateTimeRange"
          class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        >
          <option value="24h">Last 24 Hours</option>
          <option value="7d">Last 7 Days</option>
          <option value="30d">Last 30 Days</option>
        </select>
      </div>
      
      <button 
        @click="refreshData"
        :disabled="loading"
        class="text-sm bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white px-3 py-1 rounded-md transition-colors"
      >
        <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-else>Refresh</span>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

export default {
  name: 'WeatherChart',
  props: {
    title: {
      type: String,
      default: 'Weather Data Chart'
    },
    location: {
      type: String,
      default: null
    },
    initialData: {
      type: Object,
      default: () => ({
        labels: [],
        datasets: []
      })
    },
    chartTypes: {
      type: Array,
      default: () => ['line', 'bar']
    },
    autoRefresh: {
      type: Boolean,
      default: true
    },
    refreshInterval: {
      type: Number,
      default: 300000 // 5 minutes
    }
  },
  setup(props) {
    const chartCanvas = ref(null);
    const chartId = ref(`chart-${Math.random().toString(36).substr(2, 9)}`);
    const chart = ref(null);
    const loading = ref(false);
    const currentType = ref(props.chartTypes[0] || 'line');
    const timeRange = ref('24h');
    const chartData = ref(props.initialData);
    const refreshTimer = ref(null);

    const initChart = async () => {
      if (!chartCanvas.value) return;
      
      const ctx = chartCanvas.value.getContext('2d');
      
      // Destroy existing chart
      if (chart.value) {
        chart.value.destroy();
      }
      
      chart.value = new Chart(ctx, {
        type: currentType.value,
        data: chartData.value,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              borderColor: '#d97706',
              borderWidth: 1,
              callbacks: {
                title: function(context) {
                  return context[0].label;
                },
                label: function(context) {
                  const label = context.dataset.label || '';
                  const unit = getUnitForDataset(context.dataset.label);
                  return `${label}: ${context.parsed.y}${unit}`;
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                color: 'rgba(156, 163, 175, 0.3)'
              },
              ticks: {
                color: '#6b7280',
                maxTicksLimit: 8
              }
            },
            y: {
              grid: {
                color: 'rgba(156, 163, 175, 0.3)'
              },
              ticks: {
                color: '#6b7280'
              },
              beginAtZero: true
            }
          },
          elements: {
            line: {
              tension: 0.4
            },
            point: {
              radius: 3,
              hoverRadius: 5
            }
          },
          animation: {
            duration: 750,
            easing: 'easeInOutQuart'
          }
        }
      });
    };

    const getUnitForDataset = (label) => {
      const units = {
        'Temperature': '°C',
        'Rainfall': 'mm',
        'Humidity': '%',
        'Wind Speed': 'km/h',
        'Flood Risk': '%'
      };
      return units[label] || '';
    };

    const changeChartType = (type) => {
      currentType.value = type;
      if (chart.value) {
        chart.value.config.type = type;
        chart.value.update();
      }
    };

    const updateTimeRange = async () => {
      await fetchData();
    };

    const fetchData = async () => {
      loading.value = true;
      try {
        const params = new URLSearchParams({
          location: props.location || '',
          timeRange: timeRange.value
        });
        
        const response = await fetch(`/api/weather/chart-data?${params}`);
        const data = await response.json();
        
        if (data.success) {
          chartData.value = data.data;
          if (chart.value) {
            chart.value.data = chartData.value;
            chart.value.update();
          }
        }
      } catch (error) {
        console.error('Error fetching chart data:', error);
      } finally {
        loading.value = false;
      }
    };

    const refreshData = async () => {
      await fetchData();
    };

    const startAutoRefresh = () => {
      if (props.autoRefresh && refreshTimer.value === null) {
        refreshTimer.value = setInterval(fetchData, props.refreshInterval);
      }
    };

    const stopAutoRefresh = () => {
      if (refreshTimer.value) {
        clearInterval(refreshTimer.value);
        refreshTimer.value = null;
      }
    };

    // Watch for data changes
    watch(() => props.initialData, (newData) => {
      chartData.value = newData;
      if (chart.value) {
        chart.value.data = chartData.value;
        chart.value.update();
      }
    }, { deep: true });

    onMounted(async () => {
      await nextTick();
      await initChart();
      if (props.location) {
        await fetchData();
      }
      startAutoRefresh();
    });

    onUnmounted(() => {
      stopAutoRefresh();
      if (chart.value) {
        chart.value.destroy();
      }
    });

    return {
      chartCanvas,
      chartId,
      chart,
      loading,
      currentType,
      timeRange,
      chartData,
      changeChartType,
      updateTimeRange,
      refreshData
    };
  }
};
</script>