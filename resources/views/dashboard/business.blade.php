@extends('layouts.app')

@section('content')
  @include('dashboard._home', ['title' => 'Business', 'groups' => ['Business', 'Account'], 'intro' => 'Invoices, supplier payments, trade finance and treasury in one ledger.'])
@endsection
