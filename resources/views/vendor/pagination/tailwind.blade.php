@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-blue-500 bg-blue-100 border border-blue-300 cursor-default leading-5 rounded-md dark:text-blue-600 dark:bg-blue-800 dark:border-blue-600">
                {!! __('Pagina Anterior') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 leading-5 rounded-md hover:text-blue-500 focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-blue-200 active:text-blue-700 transition ease-in-out duration-150 dark:bg-blue-800 dark:border-blue-600 dark:text-blue-300 dark:focus:border-blue-700 dark:active:bg-blue-700 dark:active:text-blue-300">
                    {!! __('Pagina Anterior') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 leading-5 rounded-md hover:text-blue-500 focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-blue-200 active:text-blue-700 transition ease-in-out duration-150 dark:bg-blue-800 dark:border-blue-600 dark:text-blue-300 dark:focus:border-blue-700 dark:active:bg-blue-700 dark:active:text-blue-300">
                    {!! __('Siguiente Pagina') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-blue-500 bg-blue-100 border border-blue-300 cursor-default leading-5 rounded-md dark:text-blue-600 dark:bg-blue-800 dark:border-blue-600">
                {!! __('Siguiente Pagina') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 leading-5 dark:text-gray-400">
                    {!! __('Mostrando de') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('a') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('de') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('resultados existentes') !!}
                </p>
            </div>

            <div>
                
@endif
