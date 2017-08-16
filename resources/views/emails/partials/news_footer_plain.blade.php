@if($news && $news->isNotEmpty())
    {{ trans_choice('emails.Notes', $news->count()) }} from the {{ config('app.name') }}:

    @foreach($news as $cnew)
    {{ $cnew->show_at->format('M j, Y H:i:s ') }}:  {{ $cnew->message }}
    @if (!$loop->last)

    @endif
    @endforeach
@endif