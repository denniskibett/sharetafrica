@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Edit User</h1>

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500" required>
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500" 
                    required onchange="toggleRoleFields()">
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="borrower" {{ $user->role == 'borrower' ? 'selected' : '' }}>Borrower</option>
                <option value="broker" {{ $user->role == 'broker' ? 'selected' : '' }}>Broker</option>
                <option value="teller" {{ $user->role == 'teller' ? 'selected' : '' }}>Teller</option>
            </select>
            @error('role')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500" required>
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" 
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
            @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Broker Fields -->
        <div id="brokerFields" style="display: {{ $user->role == 'broker' ? 'block' : 'none' }};">
            <div class="mb-4">
                <label for="penalty_client" class="block text-sm font-medium text-gray-700">Penalty Client (%)</label>
                <input type="number" id="penalty_client" name="penalty_client" step="0.01" 
                       value="{{ old('penalty_client', optional($user->broker)->penalty_client }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('penalty_client')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="penalty_broker" class="block text-sm font-medium text-gray-700">Penalty Broker (%)</label>
                <input type="number" id="penalty_broker" name="penalty_broker" step="0.01" 
                       value="{{ old('penalty_broker', optional($user->broker)->penalty_broker }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('penalty_broker')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="interest_client" class="block text-sm font-medium text-gray-700">Interest Client (%)</label>
                <input type="number" id="interest_client" name="interest_client" step="0.01" 
                       value="{{ old('interest_client', optional($user->broker)->interest_client }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('interest_client')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="interest_broker" class="block text-sm font-medium text-gray-700">Interest Broker (%)</label>
                <input type="number" id="interest_broker" name="interest_broker" step="0.01" 
                       value="{{ old('interest_broker', optional($user->broker)->interest_broker }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('interest_broker')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="cert_no" class="block text-sm font-medium text-gray-700">Certificate Number</label>
                <input type="text" id="cert_no" name="cert_no" 
                       value="{{ old('cert_no', optional($user->broker)->cert_no }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('cert_no')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Borrower Fields -->
        <div id="borrowerFields" style="display: {{ $user->role == 'borrower' ? 'block' : 'none' }};">
            <div class="mb-4">
                <label for="national_id" class="block text-sm font-medium text-gray-700">National ID</label>
                <input type="text" id="national_id" name="national_id" 
                       value="{{ old('national_id', optional($user->borrower)->national_id }}" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                @error('national_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="client_type" class="block text-sm font-medium text-gray-700">Client Type</label>
                <select id="client_type" name="client_type" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                    <option value="0" {{ optional($user->borrower)->client_type == 0 ? 'selected' : '' }}>Our Client</option>
                    <option value="1" {{ optional($user->borrower)->client_type == 1 ? 'selected' : '' }}>Broker Client</option>
                </select>
                @error('client_type')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                    <option value="1" {{ optional($user->borrower)->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ optional($user->borrower)->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="broker_id" class="block text-sm font-medium text-gray-700">Broker (if applicable)</label>
                <select id="broker_id" name="broker_id" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500">
                    <option value="">Select a broker</option>
                    @foreach($brokers as $broker)
                        <option value="{{ $broker->id }}" 
                            {{ optional($user->borrower)->broker_id == $broker->id ? 'selected' : '' }}>
                            {{ $broker->user->name }}
                        </option>
                    @endforeach
                </select>
                @error('broker_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update User</button>
        </div>
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
</script>
@endsection