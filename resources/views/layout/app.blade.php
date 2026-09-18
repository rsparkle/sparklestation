<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('extra-css')
    <title>SparkleStation</title>
</head>

<body class="bg-body">
    @php
        $userId = auth()->user()?->id;
    @endphp
    <div class="flex">
        {{-- Left Sidebar --}}
        <nav class="fixed top-0 left-0 flex flex-col z-45 w-1/6 h-screen space-y-5 overflow-y-auto bg-crimson">
            <div class="flex items-center justify-center">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/icons/logo.png') }}" alt="Sparkle Station Logo"
                        class="smooth-transition mt-5 h-20 md:h-24">
                </a>
            </div>
            <div class="flex flex-col space-y-7">
                <x-nav-item text="Home" imgSrc="images/icons/Home.png" link="{{ route('home') }}" />
                <x-nav-item text="Patches" imgSrc="images/icons/Books.png" link="{{ route('patches') }}" />
                <x-nav-item text="Characters" imgSrc="images/icons/Characters.png"
                    link="{{ route('characters.index') }}" />
                <x-nav-item text="Lightcones" imgSrc="images/icons/Lightcones.png"
                    link="{{ route('lightcones.index') }}" />
                <x-nav-item text="Relics" imgSrc="images/icons/Relics.png" link="{{ route('relics.index') }}" />
            </div>
        </nav>

        {{-- Main Content --}}
        <div id="content" class="flex-1 min-w-0 ml-[16.666667%] lg:mr-[14.2%]">
            {{-- Top Sidebar for Smaller Screens --}}
            <div
                class="fixed top-0 left-5 right-0 flex lg:hidden bg-crimson z-50 px-4 pl-[16.666667%] justify-between items-center box-border">
                <!-- Auth and Setting Options -->
                <div class="flex items-center gap-3">
                    <x-auth-layout :userId="$userId" variant="top" />
                    @auth
                        <a href="{{ route('user.index') }}" class="bg-ruby hover:bg-coral">
                            <img src="{{ asset('images/icons/Settings.png') }}" alt="User Panel" class="h-6 w-6">
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Notifications --}}
            <div id="notifications-container">
                @if (session('success'))
                    <x-shared.notification type="success-message" :message="session('success')" />
                @endif

                @if (isset($errors) && $errors->any())
                    @foreach ($errors->all() as $error)
                        <x-shared.notification type="error-message" :message="$error" />
                    @endforeach
                @endif
            </div>

            {{-- Content --}}
            <div class="pt-5 lg:pt-0">
                @yield('content')
            </div>
        </div>

        {{-- Right Sidebar for Bigger Screens --}}
        <div
            class="fixed top-0 right-0 hidden lg:flex bg-crimson w-1/7 z-45 flex-col h-screen items-center overflow-y-auto">
            <div class="flex flex-col items-center h-5/6 space-y-2 w-full mt-7">
                {{-- User Information --}}
                <x-auth-layout :userId="$userId" variant="right" />
            </div>

            @auth
                {{-- Character Rolling --}}
                <div class="flex flex-col h-1/6 w-full">
                    <x-nav-item text="Gacha" imgSrc="images/icons/Gacha.png" link="{{ route('gacha.index') }}" />
                </div>
                {{-- Website Settings --}}
                <div class="flex flex-col h-1/6 w-full">
                    <x-nav-item text="User Panel" imgSrc="images/icons/Settings.png" link="{{ route('user.index') }}" />
                </div>
            @endauth
        </div>
    </div>
    {{-- Footer --}}
    <footer class="w-full border-t border-red-200/50 py-6 text-center bg-crimson">
        <p class="text-xs leading-relaxed text-white max-w-2xl mx-auto px-4">
            All game data and assets belong to <span class="font-semibold">HoYoverse</span>.<br>
            This project is non-commercial and for educational and portfolio purposes only.<br>
            Sparkle Station is not affiliated with or endorsed by <span class="font-semibold">HoYoverse</span>.
        </p>
    </footer>
</body>

@php
    if (auth()->check()) {
        $auth = [
            'id' => auth()->id(),
            'username' => auth()->user()->username,
            'profilePic' => auth()->user()->profile_pic,
            'itemsPerPage' => auth()->user()->items_per_page,
            'columnsToShow' => auth()->user()->columns_to_show,
        ];
    } else {
        $auth = null;
    }
@endphp

<script>
    window.authUser = @json($auth);
</script>

</html>
