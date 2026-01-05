// Mention functionality for Trumbowyg editor
// This should be called after Trumbowyg is initialized

window.initTrumbowygMention = function($originalEditor) {
    let $dropdown = null;
    let searchTimeout = null;
    let mentionStartNode = null;
    let mentionStartOffset = null;

    // Create dropdown element
    $dropdown = $('<div class="trumbowyg-mention-dropdown"></div>')
        .css({
            position: 'absolute',
            display: 'none',
            zIndex: 99999,
            maxHeight: '200px',
            overflowY: 'auto',
            backgroundColor: '#1e293b',
            border: '1px solid #475569',
            borderRadius: '8px',
            boxShadow: '0 10px 25px rgba(0,0,0,0.3)',
            minWidth: '200px'
        })
        .appendTo('body');

    // Get the contenteditable element
    // When Trumbowyg is initialized on a div, it may use that div directly as the editor
    let $editorEl;

    // Check if the original element itself is the editor (has contenteditable)
    if ($originalEditor.attr('contenteditable') === 'true' || $originalEditor.hasClass('trumbowyg-editor')) {
        $editorEl = $originalEditor;
    } else {
        // Look in the trumbowyg-box wrapper
        const $box = $originalEditor.closest('.trumbowyg-box');
        if ($box.length) {
            $editorEl = $box.find('.trumbowyg-editor');
        }

        // Fallback: try finding it as a sibling
        if (!$editorEl || !$editorEl.length) {
            $editorEl = $originalEditor.siblings('.trumbowyg-editor');
        }

        // Another fallback: look in parent
        if (!$editorEl || !$editorEl.length) {
            $editorEl = $originalEditor.parent().find('.trumbowyg-editor');
        }

        // Last resort: find any trumbowyg-editor on the page
        if (!$editorEl || !$editorEl.length) {
            $editorEl = $('.trumbowyg-editor').first();
        }
    }

    if (!$editorEl || !$editorEl.length) {
        console.warn('Trumbowyg mention: Could not find editor element');
        return;
    }

    console.log('Trumbowyg mention initialized on:', $editorEl[0]);

    // Listen for input in editor
    $editorEl.on('input keyup', function(e) {
        // Ignore navigation keys
        if ([38, 40, 13, 9, 27].includes(e.keyCode)) {
            return;
        }

        clearTimeout(searchTimeout);

        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const textNode = range.startContainer;

        if (textNode.nodeType !== Node.TEXT_NODE) {
            hideDropdown();
            return;
        }

        const text = textNode.textContent;
        const cursorPos = range.startOffset;

        // Find @ symbol before cursor
        const beforeCursor = text.substring(0, cursorPos);
        const atMatch = beforeCursor.match(/@(\w{3,})$/);

        if (atMatch) {
            const query = atMatch[1];
            mentionStartNode = textNode;
            mentionStartOffset = cursorPos - query.length - 1; // -1 for @

            searchTimeout = setTimeout(function() {
                searchUsers(query, range);
            }, 200);
        } else {
            hideDropdown();
        }
    });

    function searchUsers(query, range) {
        $.ajax({
            url: '/api/users/search',
            data: { q: query },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(users) {
                if (users.length === 0) {
                    hideDropdown();
                    return;
                }

                showDropdown(users, range);
            },
            error: function() {
                hideDropdown();
            }
        });
    }

    function showDropdown(users, range) {
        const rect = range.getBoundingClientRect();

        $dropdown.empty();

        users.forEach(function(user, index) {
            const avatarUrl = user.avatar || '/images/default-avatar.png';
            const $item = $('<div class="mention-item"></div>')
                .attr('data-index', index)
                .css({
                    padding: '8px 12px',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    color: '#e2e8f0',
                    transition: 'background-color 0.15s'
                })
                .html('<img src="' + avatarUrl + '" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;" alt="" onerror="this.style.display=\'none\'"><span>' + user.username + '</span>');

            $item.on('mouseenter', function() {
                $dropdown.find('.mention-item').css('backgroundColor', 'transparent').removeClass('active');
                $(this).css('backgroundColor', '#334155').addClass('active');
            }).on('mouseleave', function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('backgroundColor', 'transparent');
                }
            }).on('mousedown', function(e) {
                e.preventDefault(); // Prevent blur
                insertMention(user);
            });

            $item.data('user', user);
            $dropdown.append($item);
        });

        // Highlight first item
        $dropdown.find('.mention-item').first().addClass('active').css('backgroundColor', '#334155');

        $dropdown.css({
            top: rect.bottom + window.scrollY + 5,
            left: rect.left + window.scrollX,
            display: 'block'
        });
    }

    function insertMention(user) {
        if (!mentionStartNode) {
            hideDropdown();
            return;
        }

        const text = mentionStartNode.textContent;
        const cursorPos = mentionStartOffset;

        // Find the end of the mention text (after @query)
        let endPos = cursorPos + 1; // Start after @
        while (endPos < text.length && /\w/.test(text[endPos])) {
            endPos++;
        }

        // Create the mention link HTML
        const mentionHtml = '<a href="' + user.url + '" class="mention" data-mention-user-id="' + user.id + '">@' + user.username + '</a>&nbsp;';

        // Replace the @query with the link
        const before = text.substring(0, cursorPos);
        const after = text.substring(endPos);

        // Update the text node
        mentionStartNode.textContent = before;

        // Create a range after the text node
        const selection = window.getSelection();
        const newRange = document.createRange();
        newRange.setStartAfter(mentionStartNode);
        newRange.collapse(true);
        selection.removeAllRanges();
        selection.addRange(newRange);

        // Insert the mention HTML and remaining text
        document.execCommand('insertHTML', false, mentionHtml + after);

        hideDropdown();

        // Trigger change event
        $originalEditor.trigger('tbwchange');
    }

    function hideDropdown() {
        $dropdown.hide();
        mentionStartNode = null;
        mentionStartOffset = null;
    }

    // Hide dropdown on click outside
    $(document).on('click.trumbowygMention', function(e) {
        if (!$(e.target).closest('.trumbowyg-mention-dropdown').length &&
            !$(e.target).closest('.trumbowyg-editor').length) {
            hideDropdown();
        }
    });

    // Keyboard navigation
    $editorEl.on('keydown', function(e) {
        if (!$dropdown.is(':visible')) return;

        const $items = $dropdown.find('.mention-item');
        const $active = $items.filter('.active');
        let $next;

        switch(e.keyCode) {
            case 40: // Down
                e.preventDefault();
                $items.removeClass('active').css('backgroundColor', 'transparent');
                if ($active.length && $active.next('.mention-item').length) {
                    $next = $active.next('.mention-item');
                } else {
                    $next = $items.first();
                }
                $next.addClass('active').css('backgroundColor', '#334155');
                break;
            case 38: // Up
                e.preventDefault();
                $items.removeClass('active').css('backgroundColor', 'transparent');
                if ($active.length && $active.prev('.mention-item').length) {
                    $next = $active.prev('.mention-item');
                } else {
                    $next = $items.last();
                }
                $next.addClass('active').css('backgroundColor', '#334155');
                break;
            case 13: // Enter
            case 9:  // Tab
                if ($active.length) {
                    e.preventDefault();
                    e.stopPropagation();
                    const user = $active.data('user');
                    if (user) {
                        insertMention(user);
                    }
                }
                break;
            case 27: // Escape
                e.preventDefault();
                hideDropdown();
                break;
        }
    });

    // Return cleanup function
    return function() {
        $dropdown.remove();
        $(document).off('click.trumbowygMention');
    };
};
