<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeatherDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'temperature' => 'required|numeric|between:-50,60',
            'humidity' => 'required|numeric|between:0,100',
            'precipitation' => 'required|numeric|min:0',
            'wind_speed' => 'required|numeric|min:0',
            'wind_direction' => 'required|integer|between:0,360',
            'pressure' => 'required|numeric|between:800,1200',
            'visibility' => 'nullable|numeric|min:0',
            'weather_conditions' => 'nullable|string|max:255',
            'recorded_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'location.required' => 'Location is required.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'temperature.between' => 'Temperature must be between -50°C and 60°C.',
            'humidity.between' => 'Humidity must be between 0% and 100%.',
            'precipitation.min' => 'Precipitation cannot be negative.',
            'wind_speed.min' => 'Wind speed cannot be negative.',
            'wind_direction.between' => 'Wind direction must be between 0 and 360 degrees.',
            'pressure.between' => 'Atmospheric pressure must be between 800 and 1200 hPa.',
            'visibility.min' => 'Visibility cannot be negative.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'latitude' => 'latitude coordinate',
            'longitude' => 'longitude coordinate',
            'temperature' => 'temperature value',
            'humidity' => 'humidity percentage',
            'precipitation' => 'precipitation amount',
            'wind_speed' => 'wind speed',
            'wind_direction' => 'wind direction',
            'pressure' => 'atmospheric pressure',
            'visibility' => 'visibility distance',
            'weather_conditions' => 'weather conditions',
            'recorded_at' => 'recording timestamp',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set recorded_at to current time if not provided
        if (!$this->has('recorded_at')) {
            $this->merge([
                'recorded_at' => now()
            ]);
        }

        // Clean and format numeric values
        if ($this->has('temperature')) {
            $this->merge([
                'temperature' => round((float) $this->temperature, 2)
            ]);
        }

        if ($this->has('humidity')) {
            $this->merge([
                'humidity' => round((float) $this->humidity, 1)
            ]);
        }

        if ($this->has('precipitation')) {
            $this->merge([
                'precipitation' => round((float) $this->precipitation, 2)
            ]);
        }

        if ($this->has('wind_speed')) {
            $this->merge([
                'wind_speed' => round((float) $this->wind_speed, 1)
            ]);
        }

        if ($this->has('pressure')) {
            $this->merge([
                'pressure' => round((float) $this->pressure, 1)
            ]);
        }

        if ($this->has('visibility')) {
            $this->merge([
                'visibility' => round((float) $this->visibility, 1)
            ]);
        }
    }
}