@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6"> Create New User</h1>

    @if ($errors->any())
    <div class="mb-4 text-red-600">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700">Name</label>
            <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700">Email</label>
            <input type="email" name="email" id="email" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div class="mb-4">
            <label for="role" class="block text-gray-700">Role</label>
            <select name="role" id="role" class="w-full px-4 py-2 border rounded-lg" required onchange="toggleRoleFields()">
                <option value="admin">Admin</option>
                <option value="broker">Broker</option>
                <option value="borrower">Borrower</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password" name="password" id="password" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div class="mb-4">
            <label for="phone" class="block text-gray-700">Phone</label>
            <input type="text" name="phone" id="phone" class="w-full px-4 py-2 border rounded-lg" required>
        </div>

        <div id="brokerFields" style="display: none;">
            <div class="mb-4">
                <label for="penalty_client" class="block text-gray-700">Penalty Client</label>
                <input type="number" name="penalty_client" id="penalty_client" class="w-full px-4 py-2 border rounded-lg" step="0.01">
            </div>
            <div class="mb-4">
                <label for="penalty_broker" class="block text-gray-700">Penalty Broker</label>
                <input type="number" name="penalty_broker" id="penalty_broker" class="w-full px-4 py-2 border rounded-lg" step="0.01">
            </div>
            <div class="mb-4">
                <label for="interest_client" class="block text-gray-700">Interest Client</label>
                <input type="number" name="interest_client" id="interest_client" class="w-full px-4 py-2 border rounded-lg" step="0.01">
            </div>
            <div class="mb-4">
                <label for="interest_broker" class="block text-gray-700">Interest Broker</label>
                <input type="number" name="interest_broker" id="interest_broker" class="w-full px-4 py-2 border rounded-lg" step="0.01">
            </div>
            <div class="mb-4">
                <label for="cert_no" class="block text-gray-700">Certificate Number</label>
                <input type="text" name="cert_no" id="cert_no" class="w-full px-4 py-2 border rounded-lg">
            </div>

        </div>

        <div id="borrowerFields" style="display: none;">
            <div class="mb-4">
                <label for="national_id" class="block text-gray-700">National ID</label>
                <input type="text" name="national_id" id="national_id" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label for="client_type" class="block text-gray-700">Client Type</label>
                <select name="client_type" id="client_type" class="w-full px-4 py-2 border rounded-lg">
                    <option value="0">Our Client</option>
                    <option value="1">Broker Client</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status</label>
                <select name="status" id="status" class="w-full px-4 py-2 border rounded-lg">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="broker_id" class="block text-gray-700">Select Broker (Optional)</label>
                <select name="broker_id" id="broker_id" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Select a broker</option>
                    @foreach($brokers as $broker)
                        <option value="{{ $broker->id }}">{{ $broker->user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
</div>

<script>
    function toggleRoleFields() {
        const role = document.getElementById('role').value;
        const brokerFields = document.getElementById('brokerFields');
        const borrowerFields = document.getElementById('borrowerFields');

        brokerFields.style.display = role === 'broker' ? 'block' : 'none';
        borrowerFields.style.display = role === 'borrower' ? 'block' : 'none';
    }

    // Execute on page load
    document.addEventListener('DOMContentLoaded', function () {
        toggleRoleFields();
    });
</script>

@endsection