<!-- resources/views/vendor/subscriptions/index.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Subscriptions Management</h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Companies with Subscriptions</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Current Plan</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                            <tr>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->currentSubscription?->plan->name ?? 'No Plan' }}</td>
                                <td>
                                    <span class="badge bg-{{ $company->currentSubscription?->status === 'active' ? 'success' : 'warning' }}">
                                        {{ $company->currentSubscription?->status ?? 'No Active' }}
                                    </span>
                                </td>
                                <td>{{ $company->currentSubscription?->ends_at?->format('Y-m-d') ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('subscriptions.companies.show', $company) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $companies->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection