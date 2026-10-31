@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Operations desk', 'groups' => ['Operations', 'Account'], 'intro' => 'Reconciliation, treasury pools, rail health and support.'])
@endsection
