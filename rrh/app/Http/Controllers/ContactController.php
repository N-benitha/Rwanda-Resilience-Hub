<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000'
        ]);

        try {
            // Here you can:
            // 1. Store the message in database
            // 2. Send email notification
            // 3. Log the contact attempt
            
            Log::info('Contact form submission', [
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'message' => substr($validated['message'], 0, 100) . '...'
            ]);

            // You can uncomment this when you have email configured
            /*
            Mail::send('emails.contact', $validated, function ($message) use ($validated) {
                $message->to('admin@rrh.com')
                        ->subject('New Contact Form Submission from ' . $validated['full_name']);
            });
            */

            return redirect()->back()->with('success', 'Thank you for your message! We will get back to you soon.');
            
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Sorry, there was an error sending your message. Please try again.')
                           ->withInput();
        }
    }
}