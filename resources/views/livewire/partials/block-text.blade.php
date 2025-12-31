@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50"
    x-data="{ initialized: false }"
    x-init="
        if (!initialized && typeof $ !== 'undefined') {
            initialized = true;
            let $el = $('#block-text-{{ $block['id'] }}');
            $el.trumbowyg({
                btns: [
                    ['strong', 'em', 'del'],
                    ['unorderedList', 'orderedList'],
                    ['link'],
                    ['viewHTML'],
                ],
                defaultLinkTarget: '_blank',
                autogrow: true,
            });
            $el.on('tbwchange', function() {
                $wire.set('blocks.{{ $index }}.data.content', $el.trumbowyg('html'));
            });
        }
    ">
    <label class="block text-sm font-medium text-steel-300 mb-2">Content <span class="text-red-400">*</span></label>
    <div class="trumbowyg-dark" wire:ignore>
        <textarea
            id="block-text-{{ $block['id'] }}"
        >{{ $block['data']['content'] ?? '' }}</textarea>
    </div>
    @error("blocks.{$index}.data.content")
        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>
