<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormSubmissionMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if(env('APP_ENV') == 'production'){
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new ContactFormSubmissionMail($validated));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us. We will get back to you soon.',
        ]);
    }
}