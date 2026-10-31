@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Merchant desk', 'groups' => ['Merchant', 'Account'], 'walletOwner' => auth()->user()->company ?? auth()->user(), 'intro' => 'Accept payments on any rail and track what settles to your account.'])
@endsection
