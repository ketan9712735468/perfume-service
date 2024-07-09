<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Project') }}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="container mx-auto px-4">
                                <div class="flex justify-between items-center mb-6">
                                    <div class="space-y-2">
                                        <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
                                        <p class="text-gray-700 mb-6">{{ $project->description }}</p>
                                    </div>
                                    <div class="space-x-2">
                                        <a href="{{ route('projects.files.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">Upload Files</a>
                                        <a href="#" onclick="openMergeModal()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">Merge Files</a>
                                    </div>
                                </div>

                                <!-- Navigation Links -->
                                <div class="mb-4">
                                    <a href="{{ route('projects.show', ['project' => $project->id, 'type' => 'files']) }}" 
                                    class="{{ request('type', 'files') === 'files' ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-700 font-bold py-2 px-4">
                                        Files
                                    </a>
                                    <a href="{{ route('projects.show', ['project' => $project->id, 'type' => 'results']) }}" 
                                    class="{{ request('type') === 'results' ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-700 font-bold py-2 px-4">
                                        Results
                                    </a>
                                </div>

                                @if(request('type', 'files') === 'files')
                                    <div class="bg-white shadow-md rounded my-6">
                                        <table class="text-left w-full border-collapse">
                                            <thead>
                                                <tr>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">File</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Type</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Date</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Enabled</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200 text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700 text-sm font-light">
                                                @foreach($project->files as $file)
                                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                        <td class="py-4 px-6">
                                                            <span>{{ $file->file }}</span>
                                                        </td>
                                                        <td class="py-4 px-6">
                                                            <span>Excel</span>
                                                        </td>
                                                        <td class="py-4 px-6">
                                                            <span>{{ $file->created_at }}</span>
                                                        </td>
                                                        <td class="py-4 px-6">
                                                            <form id="toggle-enabled-form-{{ $file->id }}" action="/files/toggle-enabled" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="file_id" value="{{ $file->id }}">
                                                                <input type="hidden" name="enabled" value="0">
                                                                <label class="inline-flex items-center cursor-pointer">
                                                                    <input type="checkbox" name="enabled" value="{{ $file->enabled ? '0' : '1' }}" class="sr-only peer" @if($file->enabled) checked @endif onchange="this.form.submit()">
                                                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                                </label>
                                                            </form>
                                                        </td>
                                                        <td class="py-4 px-6 align-center text-center">
                                                            <div class="inline-flex items-center space-x-4">
                                                                <a href="#" onclick="openExcelModal('{{ route('excel.preview', ['filename' => $file->file]) }}')" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                    </svg>
                                                                </a>
                                                                <a href="{{ route('download', ['filename' => $file->file]) }}" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                                    </svg>
                                                                </a>
                                                                <button type="button" onclick="openModal('{{ route('files.destroy', $file) }}')" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>                    
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif(request('type') === 'results')
                                    <div class="bg-white shadow-md rounded my-6">
                                        <table class="text-left w-full border-collapse">
                                            <thead>
                                                <tr>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Result File</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Date</th>
                                                    <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200 text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-700 text-sm font-light">
                                                @foreach($project->resultFiles->sortByDesc('created_at') as $resultFile)
                                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                        <td class="py-4 px-6">
                                                            <span>{{ $resultFile->file }}</span>
                                                        </td>
                                                        <td class="py-4 px-6">
                                                            <span>{{ $resultFile->created_at }}</span>
                                                        </td>
                                                        <td class="py-4 px-6 align-center text-center">
                                                            <div class="inline-flex items-center space-x-4">
                                                                <a href="#" onclick="openExcelModal('{{ route('result.preview', ['filename' => $resultFile->file]) }}')" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                    </svg>
                                                                </a>
                                                                <a href="{{ route('result_download', ['filename' => $resultFile->file]) }}" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                                    </svg>
                                                                </a>
                                                                <button type="button" onclick="openModal('{{ route('resultFiles.destroy', $resultFile) }}')" class="text-gray-600 hover:text-gray-800">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-auto">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Confirm Deletion</h2>
            <p class="text-gray-700 mb-6">Are you sure you want to delete this project?</p>
            <div class="flex justify-end">
                <button onclick="closeModal()" class="inline-flex items-center px-4 py-2 mr-4 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">Delete</button>
                </form>
            </div>
        </div>
    </div>

<!-- Loading Indicator -->
<div id="loadingIndicator" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="loader"></div> <!-- You can use a CSS spinner here -->
</div>

<!-- Excel Preview Modal -->
<div id="excelPreviewModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Excel Preview</h2>
            <button onclick="closeExcelModal()" class="text-gray-900 hover:text-gray-700">&times;</button>
        </div>
        <div id="excelPreviewContent" class="w-full h-96 overflow-auto">
            <!-- Excel content will be injected here -->
        </div>
    </div>
</div>

<!-- Merge Files Modal -->
<div id="mergeFilesModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden transform transition-all sm:w-full sm:max-w-2xl">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg leading-6 font-medium text-gray-900 mb-4">Merge Files</h2>
            <form id="mergeFilesForm" method="POST" action="{{ route('projects.files.syncAll', $project) }}">
                @csrf
                <div class="mb-6">
                    <label for="mergeFileName" class="block text-gray-700 mb-2">File Name</label>
                    <input type="text" id="mergeFileName" name="mergeFileName" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeMergeModal()" class="inline-flex items-center px-4 py-2 mr-4 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Merge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(deleteUrl) {
        document.getElementById('deleteForm').action = deleteUrl;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function openExcelModal(previewUrl) {
        document.getElementById('loadingIndicator').classList.remove('hidden');
        fetch(previewUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingIndicator').classList.add('hidden');
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('excelPreviewContent').innerHTML = data.html;
                    document.getElementById('excelPreviewModal').classList.remove('hidden');
                }
            })
            .catch(error => {
                document.getElementById('loadingIndicator').classList.add('hidden');
                console.error('Error fetching preview:', error);
            });
    }

    function closeExcelModal() {
        document.getElementById('excelPreviewContent').innerHTML = ''; // Clear the content
        document.getElementById('excelPreviewModal').classList.add('hidden');
    }

    function openMergeModal() {
        document.getElementById('mergeFilesModal').classList.remove('hidden');
    }

    function closeMergeModal() {
        document.getElementById('mergeFilesModal').classList.add('hidden');
    }
</script>
</x-app-layout>