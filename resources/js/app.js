import './bootstrap'

/**
 * Game Result Square Styling
 * Replaces emoji squares from Wordle, Connections, etc. with styled HTML elements
 */
const gameSquareMap = {
    '🟩': 'green',
    '🟨': 'yellow',
    '⬛': 'black',
    '⬜': 'black', // White squares also render as black to match dark theme
    '🟪': 'purple',
    '🟦': 'blue',
    '🟧': 'orange',
    '🟥': 'red',
    '🟫': 'brown',
};

const gameSquareRegex = new RegExp(Object.keys(gameSquareMap).join('|'), 'g');

function styleGameSquares(element) {
    if (!element || element.dataset.gameSquaresProcessed) return;

    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );

    const nodesToReplace = [];

    while (walker.nextNode()) {
        const node = walker.currentNode;
        if (gameSquareRegex.test(node.textContent)) {
            nodesToReplace.push(node);
        }
        gameSquareRegex.lastIndex = 0;
    }

    nodesToReplace.forEach(node => {
        const fragment = document.createDocumentFragment();
        const text = node.textContent;
        let lastIndex = 0;
        let match;

        gameSquareRegex.lastIndex = 0;
        while ((match = gameSquareRegex.exec(text)) !== null) {
            if (match.index > lastIndex) {
                fragment.appendChild(
                    document.createTextNode(text.slice(lastIndex, match.index))
                );
            }

            const span = document.createElement('span');
            span.className = `game-square game-square--${gameSquareMap[match[0]]}`;
            span.setAttribute('aria-label', match[0]);
            fragment.appendChild(span);

            lastIndex = gameSquareRegex.lastIndex;
        }

        if (lastIndex < text.length) {
            fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
        }

        node.parentNode.replaceChild(fragment, node);
    });

    element.dataset.gameSquaresProcessed = 'true';
}

function initGameSquares() {
    document.querySelectorAll('.post-body').forEach(styleGameSquares);
}

// Run on initial load
document.addEventListener('DOMContentLoaded', initGameSquares);

// Re-run after Livewire updates
document.addEventListener('livewire:navigated', initGameSquares);
document.addEventListener('livewire:update', initGameSquares);

// Also observe for dynamically added content
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    if (node.classList?.contains('post-body')) {
                        styleGameSquares(node);
                    }
                    node.querySelectorAll?.('.post-body').forEach(styleGameSquares);
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { childList: true, subtree: true });
    });
}
