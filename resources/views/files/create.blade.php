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
                                        <h1 class="text-2xl font-semibold text-gray-900 ml-4">Upload only Inventory Files for {{ $project->name }}</h1>
                                    </div>
                                </div>

                                <!-- Success Message -->
                                <div id="dropzone-success-message" class="fixed top-4 right-4 max-w-sm w-full bg-green-100 border border-green-400 text-green-700 p-4 mb-4 rounded-md shadow-lg transition-opacity opacity-0 hidden" role="alert">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-3 text-green-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 4.5l-11.25 11.25-4.5-4.5m-1.5-1.5l6-6L20.25 4.5z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p id="dropzone-success-text" class="text-sm"></p>
                                        </div>
                                        <button type="button" class="ml-3 text-green-600 hover:text-green-800" onclick="hideMessage('dropzone-success-message')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div id="dropzone-error-message" class="fixed top-4 right-4 max-w-sm w-full bg-red-100 border border-red-400 text-red-700 p-4 mb-4 rounded-md shadow-lg transition-opacity opacity-0 hidden" role="alert">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-3 text-red-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3a1.5 1.5 0 1 1 3 0v12a1.5 1.5 0 1 1-3 0V3zm-1.5 12a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p id="dropzone-error-text" class="text-sm"></p>
                                        </div>
                                        <button type="button" class="ml-3 text-red-600 hover:text-red-800" onclick="hideMessage('dropzone-error-message')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <form action="{{ route('projects.inventory.store', $project) }}" method="POST" enctype="multipart/form-data" class="dropzone mb-6" id="file-dropzone" style="border: 2px dashed #ccc; padding: 20px; border-radius: 8px;">
                                    @csrf
                                    <div class="dz-message" data-dz-message>
                                        <span>Drop files here or click to upload</span>
                                    </div>
                                </form>

                                <div class="flex justify-between items-center mb-6">
                                    <h1 class="text-2xl font-semibold text-gray-900 ml-4">Upload Files for {{ $project->name }}</h1>
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
                                                        <span>{{ $project_file->original_name }}</span>
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
                                                            <a href="{{ route('download', ['filename' => $project_file->id]) }}" class="text-gray-600 hover:text-gray-800">
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

                                <!-- Close Button at the Bottom -->
                                <div class="flex justify-end mb-6">
                                    <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                                        {{ __('Close') }}
                                    </a>
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
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-7xl mx-auto w-full">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Excel Preview</h2>
                <button onclick="closeExcelModal()" class="text-gray-900 hover:text-gray-700 rounded-full bg-gray-200 p-2 focus:outline-none hover:bg-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="excelPreviewContent" class="w-full h-[600px] overflow-auto">
                <!-- Excel content will be injected here -->
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="loader"></div> <!-- You can use a CSS spinner here -->
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

        Dropzone.options.fileDropzone = {
            paramName: "file",
            maxFilesize: 2,
            acceptedFiles: ".xlsx,.xls",
            success: function(file, response) {
                console.log('File uploaded successfully.');
                // Show success message
                const successMessageElement = document.getElementById('dropzone-success-message');
                const successTextElement = document.getElementById('dropzone-success-text');
                if (successMessageElement && successTextElement) {
                    successTextElement.textContent = 'File uploaded successfully.';
                    successMessageElement.classList.remove('hidden');
                    successMessageElement.classList.add('opacity-100');
                }
                // Hide error message if previously shown
                const errorMessageElement = document.getElementById('dropzone-error-message');
                if (errorMessageElement) {
                    errorMessageElement.classList.add('hidden');
                    errorMessageElement.classList.remove('opacity-100');
                }
            },
            error: function(file, response) {
                console.error('Error uploading file:', response);
                // Show error message
                const errorMessageElement = document.getElementById('dropzone-error-message');
                const errorTextElement = document.getElementById('dropzone-error-text');
                if (errorMessageElement && errorTextElement) {
                    const errorMessage = response.message || 'An error occurred while uploading the file.';
                    errorTextElement.textContent = errorMessage;
                    errorMessageElement.classList.remove('hidden');
                    errorMessageElement.classList.add('opacity-100');
                }
                // Hide success message if previously shown
                const successMessageElement = document.getElementById('dropzone-success-message');
                if (successMessageElement) {
                    successMessageElement.classList.add('hidden');
                    successMessageElement.classList.remove('opacity-100');
                }
            }
        };

        function hideMessage(elementId) {
            const messageElement = document.getElementById(elementId);
            if (messageElement) {
                messageElement.classList.add('opacity-0');
                setTimeout(() => {
                    messageElement.classList.add('hidden');
                }, 300); // Match this timeout with the CSS transition duration
            }
        }
    </script>
</x-app-layout>