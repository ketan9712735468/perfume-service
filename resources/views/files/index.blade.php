@extends('layouts.app')

@section('title')
    File
@endsection

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-semibold mb-6">Files for {{ $project->name }}</h1>
    <a href="{{ route('projects.files.create', $project) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Upload File</a>
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="w-full border-collapse">
            <thead class="bg-gray-200 text-gray-700 uppercase text-xs leading-normal">
                <tr>
                    <th class="py-3 px-6 text-left">File</th>
                    <th class="py-3 px-6 text-left">Download</th>
                    <th class="py-3 px-6 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @foreach($files as $file)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-4 px-6">{{ $file->file }}</td>
                    <td class="py-4 px-6">
                        <a href="{{ $file->file_url }}" download="{{ $file->file }}" class="text-blue-500 hover:text-blue-600">{{ $file->file }}</a>
                    </td>
                    <td class="py-4 px-6">
                        <button type="button" class="text-red-500 hover:text-red-600" onclick="openModal('{{ route('files.destroy', $file) }}')">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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

<script>
    function openModal(deleteUrl) {
        document.getElementById('deleteForm').action = deleteUrl;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endsection
