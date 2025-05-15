<?php

namespace App\Http\Controllers;

use App\Models\FloodPrediction;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FloodPredictionController extends Controller
{
    /**
     * Display the flood prediction form
     */
    public function index()
    {
        $user = auth()->user();
        $sensors = $user->sensors;
        $recentPredictions = $user->predictions()->latest()->take(5)->get();
        
        return view('predictions.index', compact('sensors', 'recentPredictions'));
    }
    
    /**
     * Make a new prediction based on location
     */
    public function predictByLocation(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:255',
        ]);
        
        // Call prediction API
        $predictionData = $this->generatePrediction($request->location);
        
        // Store prediction
        $prediction = FloodPrediction::create([
            'user_id' => auth()->id(),
            'location' => $request->location,
            'risk_percentage' => $predictionData['risk_percentage'],
            'rainfall_data' => $predictionData['rainfall_data'],
            'river_level_data' => $predictionData['river_level_data'],
            'soil_moisture_data' => $predictionData['soil_moisture_data'],
            'prediction_date' => now(),
            'validity_period' => 7, // 7 days validity
        ]);
        
        return redirect()->route('predictions.show', $prediction)
            ->with('success', 'Flood prediction generated successfully.');
    }
    
    /**
     * Make a new prediction based on sensor data
     */
    public function predictBySensor(Request $request)
    {
        $request->validate([
            'sensor_id' => 'required|exists:sensors,id',
        ]);
        
        $sensor = Sensor::findOrFail($request->sensor_id);
        
        // Check if the sensor belongs to the authenticated user
        if ($sensor->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to use this sensor.');
        }
        
        // Get the latest sensor reading
        $reading = $sensor->readings()->latest()->first();
        
        if (!$reading) {
            return back()->with('error', 'No readings available for this sensor.');
        }
        
        // Call prediction API with sensor data
        $predictionData = $this->generatePredictionFromSensor($sensor, $reading);
        
        // Store prediction
        $prediction = FloodPrediction::create([
            'user_id' => auth()->id(),
            'location' => $sensor->location,
            'risk_percentage' => $predictionData['risk_percentage'],
            'rainfall_data' => $predictionData['rainfall_data'],
            'river_level_data' => $predictionData['river_level_data'],
            'soil_moisture_data' => $predictionData['soil_moisture_data'],
            'prediction_date' => now(),
            'validity_period' => 7, // 7 days validity
        ]);
        
        return redirect()->route('predictions.show', $prediction)
            ->with('success', 'Flood prediction generated successfully from sensor data.');
    }
    
    /**
     * Display a specific prediction
     */
    public function show(FloodPrediction $prediction)
    {
        // Make sure the user can view this prediction
        if ($prediction->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isGovernment()) {
            abort(403, 'You do not have permission to view this prediction.');
        }
        
        return view('predictions.show', compact('prediction'));
    }
    
    /**
     * Store sensor reading from IoT device
     */
    public function storeSensorReading(Request $request)
    {
        $request->validate([
            'sensor_id' => 'required|exists:sensors,id',
            'rainfall' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'wind_speed' => 'nullable|numeric',
            'image' => 'nullable|image|max:5120', // 5MB max
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480', // 20MB max
        ]);
        
        $sensor = Sensor::findOrFail($request->sensor_id);
        
        // Check if the sensor belongs to the authenticated user
        if ($sensor->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to use this sensor.');
        }
        
        // Handle file uploads
        $imagePath = null;
        $videoPath = null;
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sensor-images', 'public');
        }
        
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('sensor-videos', 'public');
        }
        
        // Create sensor reading
        SensorReading::create([
            'sensor_id' => $sensor->id,
            'rainfall' => $request->rainfall,
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'wind_speed' => $request->wind_speed,
            'image_path' => $imagePath,
            'video_path' => $videoPath,
        ]);
        
        // Update sensor last reading timestamp
        $sensor->update([
            'is_connected' => true,
            'last_reading_at' => now(),
        ]);
        
        return redirect()->route('sensors.show', $sensor)
            ->with('success', 'Sensor data recorded successfully.');
    }
    
    /**
     * Generate a flood prediction using the AI API
     */
    private function generatePrediction($location)
    {
        // In a real application, you would call an actual API
        // For now, we'll simulate a response
        
        // Random risk percentage between 0 and 100, with 2 decimal places
        $riskPercentage = round(mt_rand(0, 10000) / 100, 2);
        
        // Mock rainfall data
        $rainfallData = [
            'moderate' => mt_rand(30, 70),
            'heavy' => mt_rand(0, 30),
        ];
        
        // Mock river level data
        $riverLevelData = [
            'water_level' => mt_rand(100, 500) / 100,
            'flow_level' => mt_rand(20, 200) / 10,
            'timestamp' => now()->toDateTimeString(),
            'threshold_indicator' => $riskPercentage > 50 ? 'Warning' : 'Normal',
        ];
        
        // Mock soil moisture data
        $soilMoistureData = [
            'level' => mt_rand(20, 90),
            'depth' => mt_rand(10, 50),
            'sensor_id' => 'SOIL-' . mt_rand(1000, 9999),
        ];
        
        return [
            'risk_percentage' => $riskPercentage,
            'rainfall_data' => $rainfallData,
            'river_level_data' => $riverLevelData,
            'soil_moisture_data' => $soilMoistureData,
        ];
    }
    
    /**
     * Generate a flood prediction using sensor data
     */
    private function generatePredictionFromSensor(Sensor $sensor, SensorReading $reading)
    {
        // In a real application, you would call an actual API with the sensor data
        // For now, we'll use the sensor reading to influence our simulated response
        
        // Adjust risk based on rainfall (higher rainfall = higher risk)
        $rainfallFactor = $reading->rainfall ? min($reading->rainfall / 10, 100) : 30;
        
        // Risk also influenced by humidity
        $humidityFactor = $reading->humidity ? min($reading->humidity / 2, 50) : 20;
        
        // Calculate risk percentage with some randomness
        $baseRisk = ($rainfallFactor + $humidityFactor) / 2;
        $riskPercentage = min(round($baseRisk + mt_rand(-10, 10), 2), 100);
        
        // Mock rainfall data with real sensor info
        $rainfallData = [
            'moderate' => $reading->rainfall ? min($reading->rainfall, 100) : mt_rand(30, 70),
            'heavy' => $reading->rainfall > 15 ? $reading->rainfall - 15 : 0,
        ];
        
        // Mock river level data
        $riverLevelData = [
            'water_level' => ($reading->rainfall ? $reading->rainfall / 5 : 3) + mt_rand(0, 50) / 100,
            'flow_level' => ($reading->rainfall ? $reading->rainfall / 10 : 2) + mt_rand(0, 30) / 10,
            'timestamp' => now()->toDateTimeString(),
            'threshold_indicator' => $riskPercentage > 50 ? 'Warning' : 'Normal',
        ];
        
        // Mock soil moisture data
        $soilMoistureData = [
            'level' => $reading->humidity ?: mt_rand(20, 90),
            'depth' => mt_rand(10, 50),
            'sensor_id' => 'SOIL-' . $sensor->id,
        ];
        
        return [
            'risk_percentage' => $riskPercentage,
            'rainfall_data' => $rainfallData,
            'river_level_data' => $riverLevelData,
            'soil_moisture_data' => $soilMoistureData,
        ];
    }
}