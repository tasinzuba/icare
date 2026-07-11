@extends('layouts.branch')

@section('title', 'Batches')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Batches</h1>
        <p class="text-sm text-gray-500 mt-1">Manage student batches with test configurations</p>
    </div>
    <a href="{{ route('branch.batches.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
        <i class="fas fa-plus"></i> Create Batch
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Batch Name</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Full Tests</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Section Tests</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Validity</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($batches as $batch)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('branch.batches.show', $batch) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $batch->name }}
                        </a>
                        @if($batch->description)
                            <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($batch->description, 50) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ $batch->full_tests_allowed ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            {{ $batch->section_tests_allowed }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                        {{ $batch->validity_days ?? '-' }} days
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-semibold text-gray-700">{{ $batch->enrollments_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($batch->status === 'active')
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Archived</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('branch.batches.show', $batch) }}" class="p-2 text-gray-400 hover:text-indigo-600" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('branch.batches.edit', $batch) }}" class="p-2 text-gray-400 hover:text-blue-600" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($batch->status === 'active')
                            <form action="{{ route('branch.batches.destroy', $batch) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Archive this batch? Students will keep their enrollments.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-layer-group text-4xl mb-3"></i>
                        <p class="text-lg font-medium">No batches yet</p>
                        <p class="text-sm mt-1">Create your first batch to start enrolling students</p>
                        <a href="{{ route('branch.batches.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-plus"></i> Create Batch
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $batches->links() }}
    </div>
    @endif
</div>
@endsection
