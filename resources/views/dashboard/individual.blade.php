@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Your wallet', 'groups' => ['Wallet', 'Account'], 'intro' => 'Send and receive money across East African rails. The other person does not need Sharet.'])
@endsection
