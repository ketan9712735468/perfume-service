<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Files') }}
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
                                    <div class="flex items-center">
                                        <a href="{{ route('projects.show', $project) }}" class="flex items-center text-blue-500 hover:text-blue-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                            </svg>
                                            Back
                                        </a>
                                        <h1 class="text-2xl font-semibold text-gray-900 ml-4">Upload Files for {{ $project->name }}</h1>
                                    </div>
                                </div>

                                <form action="{{ route('projects.files.store', $project) }}" method="POST" enctype="multipart/form-data" class="dropzone mb-6" id="file-dropzone" style="border: 2px dashed #ccc; padding: 20px; border-radius: 8px;">
                                    @csrf
                                    <div class="dz-message" data-dz-message>
                                        <span>Drop files here or click to upload</span>
                                    </div>
                                </form>

                                <div class="bg-white shadow-md rounded my-6">
                                    <table class="text-left w-full border-collapse">
                                        <thead>
                                            <tr>
                                                <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Name</th>
                                                <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200">Date</th>
                                                <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-200 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-700 text-sm font-light">
                                            @foreach($project->files as $project_file)
                                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                    <td class="py-4 px-6">
                                                        <span>{{ $project_file->file }}</span>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <span>{{ $project_file->created_at }}</span>
                                                    </td>
                                                    <td class="py-4 px-6 align-center text-center">
                                                        <div class="inline-flex items-center space-x-4">
                                                            <a href="#" onclick="openExcelModal('{{ route('excel.preview', ['filename' => $project_file->file]) }}')" class="text-gray-600 hover:text-gray-800">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                </svg>
                                                            </a>
                                                            <a href="" download="{{ $project_file->file }}" class="text-gray-600 hover:text-gray-800">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                                </svg>
                                                            </a>
                                                            <button type="button" onclick="openModal('{{ route('files.destroy', $project_file) }}')" class="text-gray-600 hover:text-gray-800">
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
                            </div>
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


<script>
    function openModal(deleteUrl) {
        document.getElementById('deleteForm').action = deleteUrl;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    function openExcelModal(previewUrl) {
        fetch(previewUrl)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('excelPreviewContent').innerHTML = data.html;
                    document.getElementById('excelPreviewModal').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error fetching preview:', error);
            });
    }

    function closeExcelModal() {
        document.getElementById('excelPreviewContent').innerHTML = ''; // Clear the content
        document.getElementById('excelPreviewModal').classList.add('hidden');
    }

</script>
</x-app-layout>