<div wire:ignore>
    <div
        id="{{ $editorId }}"
        class="editor text-white bg-transparent"
        style="min-height: 300px;"
    ></div>
</div>


@push('footer')

    <script>

        window.addEventListener('init-editor', event => {
            // initEditor(event.detail.editorId);
            console.log('livewire:load event fired');
        });

        document.addEventListener('livewire:load', function () {

            console.log('livewire:load event fired');





            // Re-initialize the editor when the component is updated
            Livewire.hook('message.processed', (message, component) => {
                if (component.fingerprint.name === @json($this->getName())) {
                    initEditor();
                }
            });

        });

        initEditor();

        function initEditor() {
            var editorId = '{{ $editorId }}';
            var editorSelector = '#' + editorId;

            // Destroy existing instance to prevent duplicates
            if ($(editorSelector).data('trumbowyg')) {
                $(editorSelector).trumbowyg('destroy');
            }

            // Initialize the editor
            $(editorSelector).trumbowyg({
                btnsDef: {
                    // Create a new dropdown
                    image: {
                        dropdown: ['insertImage', 'upload'],
                        ico: 'insertImage'
                    }
                },
                btns: [
                    ['strong', 'em', 'del'],
                    ['unorderedList', 'orderedList'],
                    ['image', 'giphy'],
                    ['blockquote'],
                    ['link'],
                    ['table'],
                    ['viewHTML'],
                    ['fullscreen']
                ],
                defaultLinkTarget: '_blank',
                plugins: {
                    giphy: {
                        apiKey: 'YOUR_GIPHY_API_KEY'
                    },
                    // Additional plugin configurations
                }
            });
            console.log('editorSelector', editorSelector)

            {{--$(editorSelector).trumbowyg('html', @this.get('body'));--}}

            // Update Livewire property on content change
            $(editorSelector).on('tbwchange', function () {
                var content = $(editorSelector).trumbowyg('html');
                @this.set('body', content);
            });
        }
    </script>
@endpush
