@extends('layouts.branch')

@section('title', 'Due Payments')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.payments.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Payments
    </a>
</div>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Due Payments</h1>
        <p class="text-gray-600">Students with pending payment balance</p>
    </div>
    <div class="text-right bg-red-50 px-6 py-3 rounded-lg">
        <p class="text-3xl font-bold text-red-600">৳{{ number_format($enrollments->sum('due_amount')) }}</p>
        <p class="text-sm text-red-600">Total Due</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($enrollments as $enrollment)
            @if($enrollment->student)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 font-semibold">{{ substr($enrollment->student->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800">{{ $enrollment->student->name }}</p>
                            <p class="text-sm text-gray-500">{{ $enrollment->student_id }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-800">{{ $enrollment->student->phone_number ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $enrollment->student->email }}</p>
                </td>
                <td class="px-6 py-4 font-medium">৳{{ number_format($enrollment->total_amount) }}</td>
                <td class="px-6 py-4 text-green-600 font-medium">৳{{ number_format($enrollment->paid_amount) }}</td>
                <td class="px-6 py-4">
                    <span class="text-xl font-bold text-red-600">৳{{ number_format($enrollment->due_amount) }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('branch.students.show', $enrollment) }}" class="text-indigo-600 hover:text-indigo-800 mr-3">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                    <p class="text-lg">All payments are cleared!</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $enrollments->links() }}
</div>
@endsection
