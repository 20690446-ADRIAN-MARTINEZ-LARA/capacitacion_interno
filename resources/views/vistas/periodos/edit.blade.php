<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white">
            {{ __('Editar periodo') }}
        </h2>
    </x-slot>

    <div class="min-h-screen flex flex-col  items-center pt-6  bg-gray-100">
        <form action="{{ route('periodos.update', ['periodo' => $periodo]) }}" method="POST"
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            @csrf
            @method('PATCH')
            <!-- Periodo -->
            <div class="mt-4">
                <x-input-label for="periodo" value="Periodo" />
                <x-text-input id="periodo" name="periodo" type="text" class="mt-1 block w-full" value="{{ $periodo->periodo }}"/>
            </div>
            <!-- año -->
            <div class="mt-4">
                <x-input-label for="anio" value="Año" />
                <x-text-input id="anio" name="anio" type="number" class="mt-1 block w-full" value="{{ $periodo->anio }}"/>
            </div>
            <!-- trimestre -->
            <div class="mt-4">
                <x-input-label for="trimestre" value="Trimestre" />
                <select name="trimestre" id="trimestre"
                    class="block mt-1 w-full border-black focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach([1 => 'Enero - Marzo', 2 => 'Abril - Junio', 3 => 'Julio - Septiembre', 4 => 'Octubre - Diciembre'] as $key => $label)
                        <option value="{{ $key }}" {{$periodo->trimestre == $key ? 'selected' : ''}}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button class="ms-4">
                    {{ __('Actualizar') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
