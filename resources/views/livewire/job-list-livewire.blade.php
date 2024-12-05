
<div class="px-4 sm:px-6 lg:px-8">

    <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-xl font-semibold text-gray-900">Job Logs</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all job logs including their status, messages and related jobs.</p>
                </div>
            </div>

            <div class="mt-4 flex space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.debounce.300ms="search" wire:keydown.enter="searchPosts" type="search" class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search messages...">
                    </div>
                </div>

                <div class="w-48">

                </div>
            </div>

            <div class="mt-8 flex flex-col">
                <div class="-mx-4 w-full -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block w-full py-2 align-middle">
                        <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">

                            <table class="min-full divide-y divide-gray-300 w-full table-fixed p-6">
                                <thead class="bg-gray-50 p-6">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4  pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 lg:pl-8">Job ID</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Message</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jobs</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created At</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white p-6">
                                @foreach($jobs as $log)
                                    <tr wire:key="{{ $log->id }}" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8">
                                            {{ $log->job->id }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                                {{ $log->type === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $log->type === 'failure' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $log->type === 'info' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                {{ ucfirst($log->type) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-pre-wrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->message }}
                                        </td>
                                        <td class="whitespace-pre-wrap px-3 py-4 text-sm text-gray-500">
                                            {{$log->job}}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->created_at->diffForHumans() }}
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



                <div class="mt-4">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>

