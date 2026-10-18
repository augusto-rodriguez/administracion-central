{{--
    Ícono de ayuda con explicación al pasar el mouse (o al tocar en celular).
    Uso: @include('partials._ayuda', ['texto' => 'Explicación...'])
    No depende del JavaScript de Bootstrap.
--}}
<span class="ayuda-info" tabindex="0" role="button" aria-label="¿Cómo se obtiene esta información?">
    <i class="bi bi-question-circle"></i>
    <span class="ayuda-texto" role="tooltip">
        <strong class="d-block mb-1">¿Cómo se obtiene esta información?</strong>
        {{ $texto }}
    </span>
</span>