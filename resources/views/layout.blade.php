<!DOCTYPE html>
<html>
<head></head>
<body>
<div style="{{$layoutCss}}">
    @foreach($cards as $thisCard)
        @switch($thisCard['card_component'])
            @case('Headline')
                <h2>Headline</h2>
            @break
            @case('RichText')
                <h2>RichText</h2>
            @break
            @case('linkMenu')
                <h2>linkMenu</h2>
            @break
        @endswitch
    @endforeach
</div>
</body>
</html>
