<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 border border-transparent rounded-lg font-semibold text-white tracking-wide shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2 focus:ring-offset-steel-900 active:scale-[0.98] transition-all duration-200']) }}>
    {{ $slot }}
</button>
