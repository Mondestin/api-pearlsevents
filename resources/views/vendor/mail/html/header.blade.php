@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Pearls Events')
<img src="https://pearlsevents.vercel.app/logo.png" class="logo" alt="Pearl Events Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
