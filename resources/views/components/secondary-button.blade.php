<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-steel-800 border border-steel-600 rounded-lg font-semibold text-sm text-steel-200 tracking-wide shadow-sm hover:bg-steel-700 hover:border-steel-500 hover:text-white hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-steel-500 focus:ring-offset-2 focus:ring-offset-steel-900 active:scale-[0.98] disabled:opacity-25 transition-all duration-200']) }}>
    {{ $slot }}
</button>
