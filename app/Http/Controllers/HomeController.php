<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function merchants()
    {
        return view('merchants');
    }

    public function personal()
    {
        return view('personal');
    }

    public function trade()
    {
        return view('trade');
    }

    public function developers()
    {
        return view('developers');
    }

    public function company()
    {
        return view('company');
    }

    public function contact()
    {
        return view('contact');
    }
}