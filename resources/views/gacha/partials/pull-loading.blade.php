{{-- Warp Animation --}}
<div class="fixed inset-0 hidden items-center justify-center overflow-hidden bg-[#09070d] z-50" id="pullLoadingOverlay">
    <div class="pull-warp">

        <div class="pull-warp-glow"></div>

        <div class="pull-warp-ring pull-warp-ring-1"></div>
        <div class="pull-warp-ring pull-warp-ring-2"></div>
        <div class="pull-warp-ring pull-warp-ring-3"></div>

        <div class="pull-warp-particles" id="pullWarpParticles"></div>

        <div class="pull-warp-center">
            <div class="pull-warp-ticket-glow"></div>

            <img src="{{ asset('images/gacha/special_ticket.webp') }}" alt="Special Ticket" class="pull-warp-ticket">

            <div class="pull-warp-core"></div>
        </div>

        <div class="pull-warp-flash"></div>
    </div>
</div>
