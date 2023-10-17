<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">

        @if (app('request')->input('page') == 1)
            <x-post.post :post="$thread" :$poll :$hasVoted :$voteCount />
        @endif

        @foreach ($replies as $post)
            <x-post.post :$post :poll="false" :hasVoted="false" :voteCount="0" />
        @endforeach

        @if (auth()->check())
            <form action="{{ request()->url() }}/replies" method="POST" class="bg-gray-800 p-8 rounded-lg shadow mb-8">
                @csrf
                <div id="body" class="editor text-white bg-transparent" style="min-height: 300px;"></div>
                <x-primary-button>Submit Post</x-primary-button>
            </form>
        @endif
    </div>

    <x-slot name="head">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/ui/trumbowyg.table.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/ui/trumbowyg.giphy.min.css">
    </x-slot>

    <x-slot name="footer">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/trumbowyg.giphy.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/trumbowyg.table.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/upload/trumbowyg.upload.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/pasteembed/trumbowyg.pasteembed.min.js"></script>
        <script>
            $('.editor').trumbowyg({
                btnsDef: {
                    // Create a new dropdown
                    image: {
                        dropdown: ['insertImage', 'upload'],
                        ico: 'insertImage'
                    }
                },
                btns:[
                    ['strong', 'em', 'del'],
                    ['unorderedList', 'orderedList'],
                    ['image','giphy'],
                    ['blockquote'],
                    ['link'],
                    ['table'],
                    ['viewHTML'],
                    ['fullscreen']
                ],
                defaultLinkTarget: '_blank',
                plugins: {
                    giphy: {
                        apiKey: '62PK2gWy7WRueqj7TcH96h4cGRnSYvqM'
                    },
                    // upload: {
                    //     serverPath: '/upload-image?_token=' + window.App.csrfToken,
                    //     fileFieldName: 'image',
                    //     urlPropertyName: 'url',
                    //     error: (error) => {
                    //         console.log('funking error', error)
                    //     }
                    // }
                }
            });
        </script>
    </x-slot>
</div>
