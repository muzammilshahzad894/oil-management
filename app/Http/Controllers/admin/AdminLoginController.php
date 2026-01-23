<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminLoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            if ($request->isMethod('post')) {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required|min:6'
                ], [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email is invalid',
                    'password.required' => 'Password is required',
                    'password.min' => 'Password must be at least 6 characters'
                ]);
    
                $data = $request->all();
                if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->back()->with('error', 'Invalid email or password');
                }
            }
            return view('admin.auth.login');
        } catch (Exception $e) {
            Log::error(__CLASS__ . '::' . __LINE__ . ' Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            return redirect()->route('admin.login');
        } catch (Exception $e) {
            Log::error(__CLASS__ . '::' . __LINE__ . ' Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
}
