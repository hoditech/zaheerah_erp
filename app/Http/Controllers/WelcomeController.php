<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index(){
        
        if(Auth::check()){

            return redirect()->route('dashboard');
        }
        return Inertia::render('Welcome', []);
    }
}
