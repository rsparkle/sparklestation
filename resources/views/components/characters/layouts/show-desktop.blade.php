<div class="hidden xl:block" id="app-desktop" data-layout="desktop">
    <div class="h-screen overflow-hidden relative" id="characterContent">
        <!-- Splash Art -->
        <div class="absolute transition-opacity duration-500 ease-out opacity-100 inset-0 bg-cover bg-center bg-no-repeat panel-base"
            style="background-image: url({{ $character->splash_img }})" id="characterSplash">
        </div>
        <!-- Splash Background (hidden by default) -->
        <div class="absolute transition-opacity duration-500 ease-out opacity-100 inset-0 bg-cover bg-center bg-no-repeat panel-hidden panel-base"
            style="background-image: url({{ $character->splash_bg_img }})" id="characterSplashBackground">
        </div>
        <!-- Overlay gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-crimson/30 to-transparent"></div>

        <div class="character-main-container flex w-full main-container-general p-5 relative z-10">
            <!-- Left Panel -->
            <x-characters.left-panel :character="$character"/>

            <!-- Overview Panel (active) -->
            <x-shared.stats :stats="$stats" :maxStatValues="$maxStatValues" containerClasses="absolute right-5 top-5 bottom-5 right-panel w-[30%]"/>

            <!-- Skills Panel -->
            <x-characters.skills :character="$character" format="desktop" />

            <!-- Traces Panel -->
            <x-characters.traces-desktop :character="$character" format="desktop"/>

            <!-- Eidolons Panel -->
            <x-characters.eidolons :character="$character" format="desktop"/>

            <!-- Story Panel -->
            <x-characters.stories-desktop :storyParts="$storyParts" />
        </div>

        <!-- Bottom Nav -->
        <x-characters.char-nav format="desktop"/>
    </div>
</div>
