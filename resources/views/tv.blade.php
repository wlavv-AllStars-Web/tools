<!DOCTYPE html>
<html>
<head>
    <title>TV</title>
    <style>
        html,
        body {
            width: 100vw;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            background: #000;
        }

        .tv-media,
        .tv-media iframe,
        .tv-media img,
        .tv-media video {
            width: 100vw;
            height: 100vh;
        }

        .tv-media img,
        .tv-media video {
            object-fit: cover;
        }

        .tv-media iframe {
            border: 0;
        }

        .tv-empty {
            width: 100vw;
            height: 100vh;
            background: url('/uploads/tv/default.jpg') center center / cover no-repeat;
        }

        #rodape {
            position: fixed;
            bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            width: 100vw;
            background: rgba(0, 0, 0, 0.8);
            color: dodgerblue;
            font-size: 16px;
            padding: 10px 0;
            z-index: 10;
        }

        #scrollText {
            display: inline-block;
            width: fit-content;
            font-size: 2rem;
            font-family: Nunito, sans-serif;
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
@if($item)
    <div class="tv-media">
        @if($item->mediaType() === 'youtube')
            <iframe
                src="https://www.youtube.com/embed/{{ $item->youtubeCode() }}?autoplay=1&mute=1&loop=1&playlist={{ $item->youtubeCode() }}&controls=0&modestbranding=1&playsinline=1"
                title="TV YouTube video"
                allow="autoplay; encrypted-media; picture-in-picture"
                allowfullscreen>
            </iframe>
        @elseif($item->mediaType() === 'video')
            <video src="{{ $item->src }}" autoplay muted loop playsinline></video>
        @else
            <img src="{{ $item->src }}" alt="TV image">
        @endif
    </div>

    @if(!empty($item->text))
        <p id="rodape">
            <span id="scrollText">{{ $item->text }}</span>
        </p>
    @endif
@else
    <div class="tv-empty"></div>
    <p id="rodape">
        <span id="scrollText">NO WALLPAPER SELECTED! PLEASE CHECK</span>
    </p>
@endif

<script>
    const scrollText = document.getElementById('scrollText');
    let position = window.innerWidth;

    function animate() {
        if (!scrollText) {
            return;
        }

        position--;
        if (position < -scrollText.offsetWidth) {
            position = window.innerWidth;
        }
        scrollText.style.transform = `translateX(${position}px)`;
        requestAnimationFrame(animate);
    }

    animate();

    window.addEventListener('resize', () => {
        position = window.innerWidth;
    });
</script>
</body>
</html>
