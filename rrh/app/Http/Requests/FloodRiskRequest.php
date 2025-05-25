<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloodRiskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'rainfall_threshold' => 'nullable|numeric|min:0|max:500',
            'risk_level' => 'nullable|in:low,medium,high,critical',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'include_forecast' => 'nullable|boolean',
            'notification_enabled' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'location.required' => 'Location is required for flood risk assessment.',
            'location.max' => 'Location name cannot exceed 255 characters.',
            'latitude.required' => 'Latitude coordinate is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'Longitude coordinate is required.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'rainfall_threshold.numeric' => 'Rainfall threshold must be a valid number.',
            'rainfall_threshold.min' => 'Rainfall threshold cannot be negative.',
            'rainfall_threshold.max' => 'Rainfall threshold cannot exceed 500mm.',
            'risk_level.in' => 'Risk level must be one of: low, medium, high, critical.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after' => 'End date must be after start date.',
            'include_forecast.boolean' => 'Include forecast must be true or false.',
            'notification_enabled.boolean' => 'Notification setting must be true or false.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Rwanda boundary validation
            $latitude = $this->latitude;
            $longitude = $this->longitude;
            
            if ($latitude && $longitude) {
                // Rwanda approximate boundaries
                if ($latitude < -2.9 || $latitude > -1.0 || 
                    $longitude < 28.8 || $longitude > 30.9) {
                    $validator->errors()->add(
                        'coordinates', 
                        'Coordinates must be within Rwanda boundaries.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_forecast' => $this->boolean('include_forecast'),
            'notification_enabled' => $this->boolean('notification_enabled'),
        ]);
    }
}