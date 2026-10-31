@extends('layouts.app')
@section('content')
  <div class="container container--wide">
    <div class="dashboard-header">
      <h1>Admin Dashboard</h1>
      <p>Welcome, {{ auth()->user()->name }}! You have administrative privileges.</p>
    </div>

    <div class="dashboard-cards">
     
    </div>
  </div>
@endsection