
<!DOCTYPE html>
<html>
<body>

@if($item)
    @if(!empty($item->text))
        <p id="rodape">
            <span id="scrollText">
                    {{ $item->text }}
            </span>
        </p>
    @endif
@else
    <p id="rodape">
        <span id="scrollText"> NO WALLPAPER SELECTED! PLEASE CHECK </span>
    </p>
@endif

<script>
    const scrollText = document.getElementById('scrollText');
    let position = window.innerWidth;
    
    function animate() {
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


    
    <style>
    
        body {
            @if($item)
                background: url('{{ asset($item->src) }}');
            @else
                background: url('/images/tv/default.jpg');
            @endif
            width: 100vw;
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
          }
        
          #scrollText {
            display: inline-block;
            width: fit-content;
            font-size: 2rem;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
          }
    </style>




</body>


</html>