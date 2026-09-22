@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    <img src="{{ config('app.url') }}/images/logo_SanPedroDeLaPaz.png"
         alt="Central de Alarmas" style="width: 60px; height: 60px; object-fit: contain; margin-bottom: 8px;">
    <br>
    {!! $slot !!}
</a>
</td>
</tr>