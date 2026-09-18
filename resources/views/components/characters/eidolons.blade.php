@if($format=="desktop")
<div class="mx-auto panel-base w-[65%] h-[80%] absolute right-5 top-5 bottom-5 p-5 panel-enter gap-4 right-panel panel-hidden overflow-auto" data-panel="eidolons">
    <div class="relative grid grid-cols-2 gap-4 h-[calc(100%-5rem)]">
@elseif($format=="mobile")
<div class="mx-auto panel-base panel-enter right-panel panel-hidden bg-itemPanel backdrop-blur-sm shadow-lg border border-white/10" data-panel="eidolons">
    <div class="w-full flex flex-col gap-3 p-5 min-h-full">
@endif
        @foreach ($character->eidolons as $index => $eidolon)
            <div
                class="relative backdrop-blur-sm shadow-lg border border-white/10 rounded-lg p-4 flex smooth-transition eidolon-container">
                <div
                    class="absolute -top-2 -left-2 bg-red-700 text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full shadow-md">
                    E{{ $index + 1 }}
                </div>

                <!-- Image Section -->
                <div class="flex flex-col items-center justify-center mr-4 space-y-2 flex-shrink-0">
                    <div class="bg-gradient-to-br from-red-700 to-red-900 rounded-full p-3 shadow-lg">
                        <img src="{{ $eidolon->img }}"
                            alt="{{ $character->name . ' Eidolon ' . ($index + 1) }}"
                            class="w-16 h-16 object-contain hover:scale-105 cursor-pointer"
                            onclick="openImageModal(this.src)">
                    </div>
                </div>

                <!-- Text Section -->
                <div class="flex flex-col justify-center flex-grow min-w-0">
                    <p class="font-bold text-white text-base mb-2">{{ $eidolon->name }}</p>
                    <p class="text-white/80 text-sm leading-relaxed">{!! $eidolon->description !!}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
