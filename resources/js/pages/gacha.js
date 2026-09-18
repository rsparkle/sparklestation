let selectionMode = null;

const currentId = {
  character: document.querySelector('[data-panel="character"]')?.dataset.featuredId ?? null,
  lightcone: document.querySelector('[data-panel="lightcone"]')?.dataset.featuredId ?? null,
};

const elements = {
  selectionButton: document.querySelector('#itemSelectionButton'),
  selectionDiv: document.querySelector('#itemSelectionDiv'),
  pullSection: document.querySelector('#pullSection'),
  pitySections: document.querySelectorAll('.pity-section'),
  pullRevealOverlay: document.getElementById('pullRevealOverlay'),
  pullRevealField: document.getElementById('pullRevealField'),
  pullRevealClose: document.getElementById('pullRevealClose'),
  pullButtons: document.querySelectorAll('.pullButton'),
  panels: document.querySelectorAll('[data-panel]'),
  bannerButtons: document.querySelectorAll('[data-banner]'),
};

const PULL_CHARGE_DURATION = { 3: 420, 4: 550, 5: 720 };
const PULL_SLIDE_DURATION = 500;
const PULL_SLIDE_STAGGER = 140;
const PULL_LAND_PAUSE = 150;

const pullLoadingOverlay = document.getElementById('pullLoadingOverlay');
const pullWarpParticles = document.getElementById('pullWarpParticles');

function setSelectionMode(type) {
  selectionMode = type;
  updateUI();
}

function updateUI() {
  elements.panels.forEach(panel => {
    const active = selectionMode === panel.dataset.panel;
    panel.classList.toggle('selection-active', active);

    if (!active) {
      panel.querySelectorAll('.active-slide')
        .forEach(slide => slide.classList.remove('active-slide'));
    }
  });

  updateSelectionButton();

  if (selectionMode) {
    highlightActiveSlide(selectionMode);
    centerSlide(selectionMode);
    updateArrowState(selectionMode);
  }
}

function highlightActiveSlide(type) {
  const track = document.getElementById(`${type}Carousel`);
  if (!track) return;

  track.querySelectorAll('.carousel-slide').forEach(slide => {
    slide.classList.toggle('active-slide', slide.dataset.id === currentId[type]);
  });
}

function centerSlide(type) {
  const track = document.getElementById(`${type}Carousel`);
  if (!track) return;

  const panel = track.parentElement;
  const target = track.querySelector(`[data-id="${currentId[type]}"]`);

  if (!target) return;

  const targetCenter = target.offsetLeft + target.offsetWidth / 2;
  const panelCenter = panel.offsetWidth / 2;

  track.style.transform = `translateX(${panelCenter - targetCenter}px)`;
}

function updateArrowState(type) {
  const panel = document.querySelector(`[data-panel="${type}"]`);
  if (!panel) return;

  const slides = [...panel.querySelectorAll('.carousel-slide')];
  const currentIndex = slides.findIndex(slide => slide.dataset.id === currentId[type]);

  const prevBtn = panel.querySelector('[data-direction="prev"]');
  const nextBtn = panel.querySelector('[data-direction="next"]');

  if (prevBtn) prevBtn.disabled = currentIndex <= 0;
  if (nextBtn) nextBtn.disabled = currentIndex >= slides.length - 1;
}

function updateSelectionButton() {
  const active = Boolean(selectionMode);

  elements.selectionDiv?.classList.toggle('hidden', !active);
  elements.pullSection?.classList.toggle('hidden', active);
  elements.pitySections.forEach(section => section.classList.toggle('hidden', active));

  if (active && elements.selectionButton) {
    elements.selectionButton.textContent = `Pick this ${selectionMode}`;
  }
}

function selectFeatured(type) {
  const track = document.getElementById(`${type}Carousel`);
  const slide = track?.querySelector(`[data-id="${currentId[type]}"]`);

  if (!slide) return;

  if (type === 'character') {
    const bg = document.getElementById('characterBackground');
    if (bg) bg.style.backgroundImage = `url(${slide.dataset.bg})`;
  } else {
    const img = document.getElementById('lightconeBackground');
    if (img) img.src = slide.dataset.img;
  }
}

// Banner toggles
elements.bannerButtons.forEach(button => {
  button.addEventListener("click", (e) => {
    const itemType = e.currentTarget.dataset.banner;

    elements.panels.forEach(panel => {
      const isTarget = panel.dataset.panel === itemType;
      panel.classList.toggle('panel-hidden', !isTarget);
      panel.classList.toggle('panel-enter', isTarget);
      panel.classList.toggle('active', isTarget);
    });

    elements.bannerButtons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.banner === itemType);
    });

    setSelectionMode(null);
  });
});

// Arrow navigation
document.querySelectorAll('.arrow-button').forEach(arrow => {
  arrow.addEventListener('click', () => {
    const panel = arrow.closest('[data-panel]');
    if (!panel) return;

    const type = panel.dataset.panel;

    if (selectionMode !== type) {
      setSelectionMode(type);
      return;
    }

    const slides = [...panel.querySelectorAll('.carousel-slide')];
    const currentIndex = slides.findIndex(slide => slide.dataset.id === currentId[type]);
    const direction = arrow.dataset.direction;
    const nextIndex = direction === 'next' ? currentIndex + 1 : currentIndex - 1;

    if (slides[nextIndex]) {
      currentId[type] = slides[nextIndex].dataset.id;
      selectFeatured(type);
      updateUI();
    }
  });
});

// Carousel slide selection
document.querySelectorAll('.carousel-slide').forEach(slide => {
  slide.addEventListener('click', () => {
    const panel = slide.closest('[data-panel]');
    if (!panel) return;

    const type = panel.dataset.panel;
    currentId[type] = slide.dataset.id;

    selectFeatured(type);
    updateUI();
  });
});

elements.selectionButton?.addEventListener('click', () => {
  if (!selectionMode) return;

  const type = selectionMode;
  const selectedId = currentId[type];
  const activePanel = document.querySelector(`[data-panel="${type}"]`);

  if (activePanel) {
    activePanel.dataset.featuredId = selectedId;
  }

  selectFeatured(type);

  if (selectedId) {
    updateFeaturedItem(type, selectedId);
  }

  setSelectionMode(null);
});

// API Pull Action
elements.pullButtons.forEach(button => {
  button.addEventListener('click', async event => {
    const csrfToken =
      document.querySelector('meta[name="csrf-token"]').content;

    const pullCount =
      parseInt(event.currentTarget.dataset.count, 10);

    const activePanel =
      document.querySelector('[data-panel].active');

    const type = activePanel.dataset.panel;
    const id = activePanel.dataset.featuredId;

    document
      .querySelectorAll('.pullButton')
      .forEach(btn => btn.disabled = true);

    showPullLoading();

    try {
      const response = await fetch('/api/gacha/pull', {
        method: 'POST',

        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },

        body: JSON.stringify({
          pullCount,
          type,
          itemId: id,
        }),
      });

      if (!response.ok) {
        throw new Error(
          `Request failed with status ${response.status}`
        );
      }

      const data = await response.json();

      const hasFiveStar = data.pulls.some(pull => pull.rarity === 5);

      pullLoadingOverlay.classList.toggle('five-star', hasFiveStar);

      await hidePullLoading();

      if (data) {
        showPullReveal(data.pulls, type);

        const activePanel = document.querySelector("[data-panel].active");
        activePanel.querySelector(".pity-count").textContent = data.fiveStarPity;

        return;
      }

      throw new Error(
        data?.message || 'Unknown error'
      );

    } catch (error) {
      hidePullLoading(false);

      document
        .querySelectorAll('.pullButton')
        .forEach(btn => btn.disabled = false);

      newNotification(
        'error',
        `Pull error: ${error.message}`
      );
    }
  });
});

function showPullReveal(pulls, type) {
  const count = pulls.length;

  elements.pullRevealField.className = `grid gap-4 justify-center ${count === 1 ? 'grid-cols-1' : 'grid-cols-5'}`;
  elements.pullRevealField.innerHTML = '';

  const cardSize = count === 1 ? 'w-60 h-[340px]' : 'w-32 h-[190px]';

  const slots = pulls.map(pull => {
    const rarity = pull.rarity;
    const img = pull.icon_img;

    const card = document.createElement('div');
    card.className = `pull-card rarity-${rarity} ${cardSize}`;

    const frontVisual = type === 'lightcone'
      ? `<div class="pull-card-lightcone"><img src="${img}" alt=""></div>`
      : `<div class="pull-card-splash" style="background-image:url(${img})"></div>`;

    const starColor = rarity === 5 ? 'text-[#ffb84d]' : rarity === 4 ? 'text-mauve' : 'text-[#6fd6ff]';

    card.innerHTML = `
      <div class="pull-card-inner">
        <div class="pull-card-face pull-card-back">
          <img src="${pull.path_img}" alt="" class="pull-card-back-img">
        </div>
        <div class="pull-card-face pull-card-front rarity-${rarity}">
          ${frontVisual}
          <div class="absolute inset-x-0 bottom-0 p-3 z-10">
            <div class="text-xs tracking-wider mb-0.5 ${starColor}">${'★'.repeat(rarity)}</div>
            <div class="text-white font-medium card-name ${count === 1 ? 'text-lg' : 'text-sm'}"></div>
          </div>
        </div>
      </div>
    `;

    card.querySelector('.card-name').textContent = pull.name;
    elements.pullRevealField.appendChild(card);
    return { card, rarity };
  });

  elements.pullRevealOverlay?.classList.remove('hidden');
  elements.pullRevealOverlay?.classList.add('flex');

  slots.forEach(({ card, rarity }, i) => {
    const slideStart = 200 + i * PULL_SLIDE_STAGGER;
    const chargeStart = slideStart + PULL_SLIDE_DURATION + PULL_LAND_PAUSE;
    const flipStart = chargeStart + PULL_CHARGE_DURATION[rarity];

    setTimeout(() => card.classList.add('entered'), slideStart);
    setTimeout(() => card.classList.add('charging'), chargeStart);
    setTimeout(() => {
      card.classList.remove('charging');
      card.classList.add('flipped');
    }, flipStart);
  });
}

elements.pullRevealClose?.addEventListener('click', () => {
  elements.pullRevealOverlay?.classList.add('hidden');
  elements.pullRevealOverlay?.classList.remove('flex');
  elements.pullButtons.forEach(btn => btn.disabled = false);
});

async function updateFeaturedItem(type, itemId) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const key = type === 'character' ? 'featured_character_id' : 'featured_lightcone_id';

  try {
    const res = await fetch('/api/user/featured', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ [key]: itemId })
    });

    const data = await res.json();
    if (!res.ok || !data.success) {
      newNotification("error", `Failed to save featured ${type}: ${data.message || 'Unknown error'}`);
    }
  } catch (err) {
    newNotification("error", `Network error updating featured ${type}`);
  }
}

function createPullParticles() {
  pullWarpParticles.innerHTML = '';

  const particleCount = 24;

  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('span');

    particle.className = 'pull-warp-particle';

    particle.style.setProperty(
      '--angle',
      `${Math.random() * 360}deg`
    );

    particle.style.setProperty(
      '--distance',
      `${200 + Math.random() * 500}px`
    );

    particle.style.setProperty(
      '--duration',
      `${0.8 + Math.random() * 1.4}s`
    );

    particle.style.setProperty(
      '--delay',
      `${Math.random() * -2}s`
    );

    particle.style.setProperty(
      '--size',
      `${1 + Math.random() * 2}px`
    );

    pullWarpParticles.appendChild(particle);
  }
}

function showPullLoading() {
  pullLoadingOverlay.classList.remove('five-star');
  createPullParticles();

  pullLoadingOverlay.classList.remove('hidden', 'finishing');
  pullLoadingOverlay.classList.add('flex');

  requestAnimationFrame(() => {
    pullLoadingOverlay.classList.add('visible');
  });
}

function hidePullLoading(animate = true) {
  if (!animate) {
    pullLoadingOverlay.classList.remove(
      'visible',
      'flex',
      'finishing'
    );

    pullLoadingOverlay.classList.add('hidden');

    return Promise.resolve();
  }

  return new Promise(resolve => {
    pullLoadingOverlay.classList.add('finishing');

    setTimeout(() => {
      pullLoadingOverlay.classList.remove(
        'visible',
        'flex',
        'finishing'
      );

      pullLoadingOverlay.classList.add('hidden');

      resolve();
    }, 400);
  });
}