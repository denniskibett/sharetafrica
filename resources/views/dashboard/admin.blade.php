@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Admin overview', 'groups' => ['Administration', 'Operations', 'Account'], 'intro' => 'Run the platform: people, integrations, roles and settings.'])
@endsection
