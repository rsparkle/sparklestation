@extends('layout.app')
@section('extra-css')
    @vite('resources/css/gacha.css')
@endsection
@section('content')
    <div class="relative h-screen overflow-hidden" id="gachaContent">

        {{-- Content --}}
        <div class="relative z-10 h-full flex flex-col justify-between p-5">

            {{-- Banner Toggle --}}
            <div class="flex gap-3 self-start relative z-20" id="bannerToggle">
                <button class="sparkle-button active" data-banner="character">
                    Character Banner
                </button>
                <button class="sparkle-button" data-banner="lightcone">
                    Lightcone Banner
                </button>
            </div>

            {{-- Character Panel --}}
            <div class="absolute inset-0 panel-base panel-enter active" data-panel="character"
                data-featured-id="{{ $featuredCharacter->id }}">

                {{-- Background --}}
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" id="characterBackground"
                    style="background-image: url({{ $featuredCharacter->splash_img }})">
                </div>

                {{-- Carousel --}}
                <div class="carousel-track" id="characterCarousel">
                    @foreach ($limitedChars as $char)
                        <div class="carousel-slide" data-id="{{ $char->id }}"
                            data-bg="{{ $char->splash_img }}">
                            <img src="{{ $char->splash_img }}" alt="{{ $char->name }}">
                        </div>
                    @endforeach
                </div>

                {{-- Arrows --}}
                <div class="arrow-container absolute inset-0 flex items-center justify-between pointer-events-none px-5 z-10">
                    <button class="arrow-button {{ $limitedChars->first()->id === $featuredCharacter->id ? 'disabled' : '' }}" data-direction="prev">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button class="arrow-button {{ $limitedChars->last()->id === $featuredCharacter->id ? 'disabled' : '' }}" data-direction="next">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-crimson/70 via-crimson/10 to-transparent"></div>

                <div class="relative h-full flex items-end justify-center pity-section">
                    <div class="relative flex flex-col items-center mb-28">
                        <div
                            class="bg-itemPanel backdrop-blur-sm shadow-lg border border-white/10 rounded-lg px-6 py-2 mb-3 pity-badge {{ $characterPity->pity > 74 ? 'pity-warning' : '' }}">
                            <p class="text-white/90 font-semibold text-sm">
                                Pity <span class="text-white font-bold pity-count">{{ $characterPity->pity }}</span> / 90
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lightcone Panel --}}
            <div class="absolute inset-0 panel-base panel-hidden" data-panel="lightcone"
                data-featured-id="{{ $featuredLightcone->id }}">

                {{-- Background --}}
                <div class="absolute inset-0 flex items-center justify-center lightcone-bg">
                    <img src="{{ $featuredLightcone->artwork_img }}" alt="{{ $featuredLightcone->name }}"
                        id="lightconeBackground">
                </div>

                {{-- Carousel --}}
                <div class="carousel-track" id="lightconeCarousel">
                    @foreach ($limitedLcs as $lc)
                        <div class="carousel-slide" data-id="{{ $lc->id }}"
                            data-img="{{ $lc->artwork_img }}">
                            <img src="{{ $lc->artwork_img }}" alt="{{ $lc->name }}">
                        </div>
                    @endforeach
                </div>

                {{-- Arrows --}}
                <div class="arrow-container absolute inset-0 flex items-center justify-between px-5 pointer-events-none z-10">
                    <button class="arrow-button {{ $limitedLcs->first()->id === $featuredLightcone->id ? 'disabled' : '' }}" data-direction="prev">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button class="arrow-button {{ $limitedLcs->last()->id === $featuredLightcone->id ? 'disabled' : '' }}" data-direction="next">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-crimson/70 via-crimson/10 to-transparent"></div>

                <div class="relative h-full flex items-end justify-center pity-section">
                    <div class="relative flex flex-col items-center mb-28">
                        <div
                            class="bg-itemPanel backdrop-blur-sm shadow-lg border border-white/10 rounded-lg px-6 py-2 mb-3 pity-badge {{ $lightconePity->pity > 64 ? 'pity-warning' : '' }}">
                            <p class="text-white/90 font-semibold text-sm">
                                Pity <span class="text-white font-bold pity-count">{{ $lightconePity->pity }}</span> / 80
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pull Buttons --}}
            <div class="flex gap-4 justify-center relative z-20" id="pullSection">
                <button class="sparkle-button px-10 pullButton" data-count="1">1 Pull</button>
                <button class="sparkle-button px-10 pullButton" data-count="10">10 Pull</button>
            </div>
            <div class="justify-center relative z-20 hidden flex" id="itemSelectionDiv">
                <button class="sparkle-button" id="itemSelectionButton"></button>
            </div>

            @include('gacha.partials.pull-loading')
            @include('gacha.partials.pull-reveal')

        </div>
    </div>
@endsection
