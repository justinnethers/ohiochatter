<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
            <p class="text-sm">
                <span @class([
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' => $record->status === 'success',
                    'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' => $record->status !== 'success',
                ])>
                    {{ ucwords(str_replace('_', ' ', $record->status)) }}
                </span>
            </p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</p>
            <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $record->ip_address }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->email ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->username ?? 'N/A' }}</p>
        </div>
    </div>

    @if($record->block_reason)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Block Reason</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->block_reason }}</p>
        </div>
    @endif

    @if($record->user_agent)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Agent</p>
            <p class="text-sm text-gray-900 dark:text-white break-all">{{ $record->user_agent }}</p>
        </div>
    @endif

    @if($record->metadata)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Data</p>
            <pre class="text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($record->metadata, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>
