<x-layout>
    <x-slot:title>View Test Section - Admin</x-slot>
    
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Test Section Details') }}
            </h2>
            <a href="{{ route('admin.sections.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                Back to Sections
            </a>
        </div>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ ucfirst($section->name) }} Section</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $section->description }}</p>
                        <div class="mt-4 flex items-center">
                            <span class="text-sm text-gray-500 mr-4">Time Limit:</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-md">{{ $section->time_limit }} minutes</span>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Test Sets</h3>
                            <a href="{{ route('admin.test-sets.create', ['section' => $section->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                                Add New Test Set
                            </a>
                        </div>
                        
                        @if($section->testSets->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($section->testSets as $testSet)
                                            <tr>
                                                <td class="py-4 px-6 text-sm text-gray-900">{{ $testSet->title }}</td>
                                                <td class="py-4 px-6 text-sm">
                                                    @if($testSet->active)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            Inactive
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-6 text-sm text-gray-900">{{ $testSet->questions->count() }}</td>
                                                <td class="py-4 px-6 text-sm text-gray-500">{{ $testSet->created_at->format('M d, Y') }}</td>
                                                <td class="py-4 px-6 text-sm">
                                                    <a href="{{ route('admin.test-sets.show', $testSet) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                    <a href="{{ route('admin.test-sets.edit', $testSet) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-yellow-700">No test sets found for this section. Add a new test set using the button above.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex space-x-4 mt-6">
                        <a href="{{ route('admin.sections.edit', $section) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Edit Section
                        </a>
                        
                        <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section? This will also delete all associated test sets and questions.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                                Delete Section
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>