{{-- resources/views/filament/forms/components/pixelated-image-preview.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

    $imageState = $getState();
    $imageUrl = null;

    // Case 1: Before saving - Temporary uploaded file
    if (is_array($imageState) && isset($imageState['image_path']) && is_array($imageState['image_path'])) {
        $tempFile = reset($imageState['image_path']);

        if ($tempFile instanceof TemporaryUploadedFile) {
            // Get the temporary URL for preview
            $imageUrl = $tempFile->temporaryUrl();
        }
        // Fallback to UUID => path format
        elseif (is_string($tempFile)) {
            $imageUrl = Storage::url($tempFile);
        }
    }
    // Case 2: After saving - Normal record
    elseif (is_array($imageState) && isset($imageState['image_path']) && is_string($imageState['image_path'])) {
        $imageUrl = Storage::url($imageState['image_path']);
    }
    // Case 3: Direct string path
    elseif (is_string($imageState)) {
        $imageUrl = Storage::url($imageState);
    }
    // Case 4: Direct UUID => path format after initial upload
    elseif (is_array($imageState) && count($imageState) === 1) {
        $tempFile = reset($imageState);

        if ($tempFile instanceof TemporaryUploadedFile) {
            $imageUrl = $tempFile->temporaryUrl();
        }
        elseif (is_string($tempFile)) {
            $imageUrl = Storage::url($tempFile);
        }
    }
@endphp

<div class="space-y-4 py-2">
    <h3 class="text-lg font-medium">Pixelation Preview</h3>

    <div
        x-data="{
            pixelationLevel: 5,
            levels: [5, 4, 3, 2, 1, 0],
        }"
    >
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 border border-gray-300 dark:border-gray-700">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Pixelation Level:</label>
                <div class="flex space-x-2">
                    <template x-for="level in levels" :key="level">
                        <button
                            type="button"
                            x-text="level"
                            @click="pixelationLevel = level"
                            class="px-3 py-1 rounded-md text-sm"
                            :class="pixelationLevel === level ?
                                'bg-primary-600 text-white' :
                                'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
                        ></button>
                    </template>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-lg overflow-hidden" style="min-height: 200px;">
                @if($imageUrl)
                    <div class="w-full h-full flex items-center justify-center">
                        <img
                            src="{{ $imageUrl }}"
                            x-bind:style="`filter: blur(${pixelationLevel * 3}px);`"
                            class="max-w-full h-auto transition-all duration-200"
                            alt="Pixelated preview"
                        />
                    </div>
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-500 p-8 text-center"
                         style="min-height: 200px;">
                        <p>Upload an image to see how it will look with different levels of pixelation</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-2 text-sm text-gray-500">
            <p>In the BuckEYE game, players start with Level 5 (most pixelated) and each wrong guess decreases
                pixelation by one level.</p>
        </div>
    </div>
</div>
