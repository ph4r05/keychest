@if($news && $news->isNotEmpty())
    <p>
        {{ trans_choice('emails.Notes', $news->count()) }} from the {{ config('app.name') }}: <br/><br/>
        @foreach($news as $cnew)
            {{ $cnew->show_at->format('M j, Y') }}:  {{ $cnew->message }} <br/> @if (!$loop->last)<br/>@endif
        @endforeach
    </p>
@endif