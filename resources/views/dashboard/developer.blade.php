@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Developers', 'groups' => ['Developers', 'Account'], 'intro' => 'Keys, webhooks, logs and the sandbox for building on Sharet.'])
@endsection
