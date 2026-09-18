<template>
  <div class="character-builder">
    <div class="character-selector">
      <button v-for="character in characters" :key="character.id" class="character-selector__item"
        :class="{ 'character-selector__item--active': activeCharacter?.id === character.id }" type="button"
        @click="selectCharacter(character)">
        <img :src="character.icon_img" :alt="character.name">
      </button>
    </div>

    <div v-if="activeCharacter" class="character-content">
      <nav class="character-tabs">
        <button class="character-tabs__item" :class="{ 'character-tabs__item--active': activeTab === 'details' }"
          type="button" @click="selectTab('details')">
          Details
        </button>

        <button class="character-tabs__item" :class="{ 'character-tabs__item--active': activeTab === 'lightcone' }"
          type="button" @click="selectTab('lightcone')">
          Light Cone
        </button>

        <button class="character-tabs__item" :class="{ 'character-tabs__item--active': activeTab === 'relics' }"
          type="button" @click="selectTab('relics')">
          Relics
        </button>

        <button class="character-tabs__item" :class="{ 'character-tabs__item--active': activeTab === 'eidolons' }"
          type="button" @click="selectTab('eidolons')">
          Eidolons
        </button>
      </nav>

      <div v-if="activeTab === 'details' && activeCharacter.splash_img" :key="activeCharacter.id"
        class="character-splash" :style="{ backgroundImage: `url(${activeCharacter.splash_img})` }" aria-hidden="true">
      </div>

      <section v-if="activeTab === 'details'" class="details-panel">
        <header class="details-header">
          <div>
            <h1 class="details-name">
              {{ activeCharacter.name }}
            </h1>

            <div class="details-path">
              <img v-if="activeCharacter.path?.img" :src="activeCharacter.path.img" :alt="activeCharacter.path.name">

              <span>{{ activeCharacter.path.name }}</span>
            </div>
          </div>

          <div class="details-element">
            <img v-if="activeCharacter.element?.img" :src="activeCharacter.element.img"
              :alt="activeCharacter.element.name">
          </div>
        </header>

        <div class="details-level">
          <span>Lvl.</span>
          <strong>{{ activeCharacter.level }}</strong>
        </div>

        <div class="details-section-title">
          Stats
        </div>

        <div class="stats-list">
          <div class="stats-row">
            <span>HP</span>
            <strong>{{ formatStat(activeCharacter.stats.hp) }}</strong>
          </div>

          <div class="stats-row">
            <span>ATK</span>
            <strong>{{ formatStat(activeCharacter.stats.atk) }}</strong>
          </div>

          <div class="stats-row">
            <span>DEF</span>
            <strong>{{ formatStat(activeCharacter.stats.def) }}</strong>
          </div>

          <div class="stats-row">
            <span>SPD</span>
            <strong>{{ activeCharacter.stats.speed }}</strong>
          </div>

          <div class="stats-row">
            <span>CRIT Rate</span>
            <strong>{{ formatRelicStat(activeCharacter.stats.crit_rate) }}%</strong>
          </div>

          <div class="stats-row">
            <span>CRIT DMG</span>
            <strong>{{ formatRelicStat(activeCharacter.stats.crit_dmg) }}%</strong>
          </div>

          <div class="stats-row">
            <span>Effect Hit Rate</span>
            <strong>{{ formatRelicStat(activeCharacter.stats.effect_hit_rate) }}%</strong>
          </div>

          <div class="stats-row">
            <span>Break Effect</span>
            <strong>{{ formatRelicStat(activeCharacter.stats.break_effect) }}%</strong>
          </div>
        </div>
      </section>

      <section v-if="activeTab === 'lightcone' && !showLightconeSelection && !showLightconeCopies"
        class="lightcone-panel">
        <header v-if="equippedLightcone" class="lightcone-header">
          <div class="lightcone-path">
            <img v-if="activeCharacter.path?.img" :src="activeCharacter.path.img" :alt="activeCharacter.path.name">

            <span>{{ activeCharacter.path.name }}</span>
          </div>
        </header>

        <div class="equipped-lightcone">
          <div class="equipped-lightcone__art" :class="{
            'equipped-lightcone__art--equipped': equippedLightcone
          }">
            <img v-if="equippedLightcone" :src="equippedLightcone.img" :alt="equippedLightcone.name">

            <div v-else class="equipped-lightcone__empty">
              No Light Cone Equipped
            </div>
          </div>

          <button class="lightcone-change-button" type="button" :disabled="isUpdatingLightcone"
            @click="showLightconeSelection = true">
            Change Light Cone
          </button>

          <button v-if="equippedLightcone" class="lightcone-superimposition-button" type="button"
            :disabled="!canSuperimpose || isUpdatingLightcone" @click="openLightconeCopies">
            {{ canSuperimpose ? 'Superimposition' : superimpositionUnavailableLabel }}
          </button>
        </div>
      </section>

      <section v-if="activeTab === 'lightcone' && !showLightconeSelection && !showLightconeCopies && equippedLightcone"
        class="details-panel">
        <header class="details-header">
          <div>
            <h1 class="details-name">
              {{ equippedLightcone.name }}
            </h1>

            <div class="details-path">
              <img v-if="equippedLightcone.path?.img" :src="equippedLightcone.path.img"
                :alt="equippedLightcone.path.name">

              <span>{{ equippedLightcone.path.name }}</span>
            </div>
          </div>
        </header>

        <div class="details-level">
          <span>Lvl.</span>
          <strong>{{ equippedLightcone.level }}</strong>
        </div>

        <div class="details-section-title">
          Stats
        </div>

        <div class="stats-list">
          <div class="stats-row">
            <span>HP</span>
            <strong>{{ formatStat(equippedLightcone.stats.hp) }}</strong>
          </div>

          <div class="stats-row">
            <span>ATK</span>
            <strong>{{ formatStat(equippedLightcone.stats.atk) }}</strong>
          </div>

          <div class="stats-row">
            <span>DEF</span>
            <strong>{{ formatStat(equippedLightcone.stats.def) }}</strong>
          </div>
        </div>

        <div v-if="equippedLightcone.skill" class="lightcone-ability">
          <div class="details-section-title">
            Ability
          </div>

          <strong class="lightcone-ability__name">
            {{ equippedLightcone.skill.name }}
          </strong>
          <span class="lightcone-ability__rank">Superimposition {{ equippedLightcone.superimposition }}</span>

          <div class="lightcone-ability__description"
            v-html="equippedLightcone.skill.descriptions_by_superimposition?.[equippedLightcone.superimposition] ?? ''">
          </div>
        </div>
      </section>

      <section v-if="activeTab === 'lightcone' && showLightconeSelection" class="lightcone-selection-panel">
        <header class="lightcone-selection-header">
          <div>
            <h2>Select a Light Cone</h2>
          </div>

          <button class="lightcone-close-button" type="button" aria-label="Close Light Cone selection"
            @click="showLightconeSelection = false">
            ×
          </button>
        </header>

        <div class="lightcone-compatible-heading">
          <div class="lightcone-path">
            <img :src="activeCharacter.path.img" :alt="activeCharacter.path.name">

            <span>{{ activeCharacter.path.name }}</span>
          </div>
        </div>

        <div class="lightcone-grid">
          <button v-for="lightcone in compatibleLightcones" :key="lightcone.user_lightcone_id" class="lightcone-card"
            :class="{
              'lightcone-card--unavailable': unequippedLightconeCount(lightcone) === 0,
              'lightcone-card--equipped':
                equippedLightcone?.user_lightcone_id === lightcone.user_lightcone_id
            }" type="button" :disabled="isUpdatingLightcone || !canEquipLightcone(lightcone)"
            @click="selectLightcone(lightcone)">
            <div class="lightcone-card__image">
              <img :src="lightcone.img" :alt="lightcone.name">

              <span class="lightcone-card__count">{{ unequippedLightconeCount(lightcone) }}</span>
              <span v-if="equippedLightcone?.user_lightcone_id === lightcone.user_lightcone_id"
                class="lightcone-card__badge">
                Equipped
              </span>
            </div>

            <div class="lightcone-card__details">
              <strong>{{ lightcone.name }}</strong>
            </div>
          </button>
        </div>

        <div v-if="incompatibleLightcones.length" class="lightcone-other-section">
          <div class="lightcone-other-heading">
            Other Paths
          </div>

          <div class="lightcone-grid">
            <button v-for="lightcone in incompatibleLightcones" :key="lightcone.user_lightcone_id"
              class="lightcone-card" :class="{
                'lightcone-card--unavailable': unequippedLightconeCount(lightcone) === 0,
                'lightcone-card--equipped':
                  equippedLightcone?.user_lightcone_id === lightcone.user_lightcone_id
              }" type="button" :disabled="isUpdatingLightcone || !canEquipLightcone(lightcone)"
              @click="selectLightcone(lightcone)">
              <div class="lightcone-card__image">
                <img :src="lightcone.img" :alt="lightcone.name">

                <img class="lightcone-card__path-icon" :src="lightcone.path.img" :alt="lightcone.path.name">

                <span class="lightcone-card__count">{{ unequippedLightconeCount(lightcone) }}</span>
                <span v-if="equippedLightcone?.user_lightcone_id === lightcone.user_lightcone_id"
                  class="lightcone-card__badge">
                  Equipped
                </span>
              </div>

              <div class="lightcone-card__details">
                <strong>{{ lightcone.name }}</strong>
                <span>{{ lightcone.path.name }}</span>
              </div>
            </button>
          </div>
        </div>
      </section>

      <section v-if="activeTab === 'lightcone' && showLightconeCopies && equippedLightcone"
        class="lightcone-copies-panel">
        <header class="lightcone-copies-header">
          <h2>Enhance Light Cone</h2>

          <button class="lightcone-close-button" type="button" aria-label="Close Superimposition selection"
            @click="closeLightconeCopies">
            ×
          </button>
        </header>

        <div class="lightcone-copies-content">
          <div class="lightcone-copies-selection">
            <div class="lightcone-copies-source">
              <img :src="equippedLightcone.img" :alt="equippedLightcone.name">

              <div>
                <span>Equipped</span>
                <strong>{{ equippedLightcone.name }}</strong>
                <small>Superimposition {{ equippedLightcone.superimposition }}</small>
              </div>
            </div>

            <div class="lightcone-copies-heading">
              <strong>Available copies</strong>

              <span>{{ selectedCopies.length }} / {{ maxUsableLightconeCopies }} selected</span>
            </div>

            <div class="lightcone-copies-grid">
              <button v-for="number in availableLightconeCopies" :key="copyKey(number)" type="button"
                class="lightcone-copy" :class="{ 'lightcone-copy--selected': selectedCopies.includes(number) }"
                :aria-pressed="selectedCopies.includes(number)"
                :disabled="!selectedCopies.includes(number) && selectedCopies.length >= maxUsableLightconeCopies"
                @click="toggleLightconeCopySelection(number)">
                <span class="lightcone-copy__number">Copy {{ number }}</span>
                <img :src="equippedLightcone.img" :alt="`${equippedLightcone.name} copy ${number}`">
                <span class="lightcone-copy__check" aria-hidden="true">✓</span>
              </button>
            </div>
          </div>

          <aside class="lightcone-superimposition-preview">
            <span class="lightcone-copies-eyebrow">Result preview</span>

            <div class="lightcone-rank-preview">
              <div>
                <span>Current</span>
                <strong>S{{ equippedLightcone.superimposition }}</strong>
              </div>
              <span class="lightcone-rank-preview__arrow">→</span>
              <div class="lightcone-rank-preview__target">
                <span>Result</span>
                <strong>S{{ lightconeTargetSuperimposition }}</strong>
              </div>
            </div>

            <div v-if="equippedLightcone.skill" class="lightcone-preview-ability">
              <strong>{{ equippedLightcone.skill.name }}</strong>
              <div class="lightcone-ability__description"
                v-html="equippedLightcone.skill.descriptions_by_superimposition?.[lightconeTargetSuperimposition] ?? ''">
              </div>
            </div>

            <div class="lightcone-copies-actions">
              <button class="lightcone-copies-cancel" type="button" @click="closeLightconeCopies">
                Cancel
              </button>
              <button class="lightcone-copies-confirm" type="button"
                :disabled="!selectedCopies.length || isUpdatingLightcone" @click="applyLightconeSuperimposition">
                {{ selectedCopies.length ? `Apply ${selectedCopies.length}` : 'Apply' }}
              </button>
            </div>
          </aside>
        </div>
      </section>

      <section v-if="activeTab === 'relics' && !selectedRelicSlotProperty" class="relics-panel">
        <div class="relics-layout">
          <main class="relics-equipment">
            <div class="relics-orbit">
              <div class="relics-orbit__ring" aria-hidden="true"></div>
              <div class="relics-orbit__inner-ring" aria-hidden="true"></div>

              <button v-for="slot in relicSlots" :key="slot.property" class="relic-slot"
                :class="`relic-slot--${slot.modifier}`" type="button" :aria-label="relicSlotAriaLabel(slot)"
                @click="openRelicSelection(slot.property)">
                <span class="relic-slot__art" :class="{ 'relic-slot__art--empty': !activeCharacter[slot.property] }">
                  <img v-if="activeCharacter[slot.property]" :src="relicImage(activeCharacter[slot.property])"
                    :alt="relicName(activeCharacter[slot.property])">

                  <span v-else class="relic-slot__plus" aria-hidden="true"></span>
                </span>

                <span v-if="activeCharacter[slot.property]" class="relic-slot__level">
                  +{{ activeCharacter[slot.property].level }}
                </span>
              </button>
            </div>

            <div class="relics-set-pills">
              <span v-for="relicSet in activeRelicSetEffects" :key="relicSet.key">
                {{ relicSet.count >= 4 ? 4 : 2 }} pc · {{ relicSet.name }}
              </span>
            </div>
          </main>

          <aside class="relics-summary">
            <h3 class="relics-gold">Relic stats</h3>
            <dl class="relics-stats">
              <div>
                <dt>HP</dt>
                <dd>{{ formatRelicStat(totalRelicStats['HP']) }}</dd>
              </div>

              <div>
                <dt>ATK</dt>
                <dd>{{ formatRelicStat(totalRelicStats['ATK']) }}</dd>
              </div>

              <div>
                <dt>DEF</dt>
                <dd>{{ formatRelicStat(totalRelicStats['DEF']) }}</dd>
              </div>

              <div>
                <dt>SPD</dt>
                <dd>{{ formatRelicStat(totalRelicStats['SPD']) }}</dd>
              </div>

              <div>
                <dt>CRIT Rate</dt>
                <dd>{{ formatRelicStat(totalRelicStats['CRIT Rate']) }}%</dd>
              </div>

              <div>
                <dt>CRIT DMG</dt>
                <dd>{{ formatRelicStat(totalRelicStats['CRIT DMG']) }}%</dd>
              </div>

              <div>
                <dt>Break Effect</dt>
                <dd>{{ formatRelicStat(totalRelicStats['Break Effect']) }}%</dd>
              </div>
            </dl>
            <div class="relics-effects">
              <h3 class="relics-gold">Set effects</h3>
              <article v-for="relicSet in activeRelicSetEffects" :key="relicSet.key">
                <div class="relics-set-title">
                  <h4>{{ relicSet.name }}</h4>
                  <span>{{ relicSet.count }} pc</span>
                </div>
                <p>
                  <b>✓ 2 pc</b>
                  <span v-html="relicSet.highlightedFirstEffect"></span>
                </p>

                <p v-if="relicSet.count >= 4">
                  <b>✓ 4 pc</b>
                  <span v-html="relicSet.highlightedSecondEffect"></span>
                </p>
              </article>
            </div>
          </aside>
        </div>
      </section>

      <section v-if="activeTab === 'relics' && selectedRelicSlot" class="relic-selection-panel">
        <header class="relic-selection-header">
          <div>
            <span class="relic-selection-eyebrow">Relics</span>
            <h2>Select {{ selectedRelicSlot.label }}</h2>
          </div>

          <button class="lightcone-close-button" type="button" aria-label="Close relic selection"
            :disabled="isUpdatingRelics" @click="closeRelicSelection">
            ×
          </button>
        </header>

        <div v-if="filteredRelics.length" class="relic-selection-content">
          <div class="relic-selection-grid">
            <button v-for="relic in filteredRelics" :key="relic.id" class="relic-card" :class="{
              'relic-card--selected': previewedRelic?.id === relic.id,
              'relic-card--equipped': activeCharacter[selectedRelicSlot.property]?.id === relic.id
            }" type="button" :aria-pressed="previewedRelic?.id === relic.id" :disabled="isUpdatingRelics"
              @click="previewedRelic = relic">
              <span class="relic-card__image">
                <img :src="relicImage(relic)" :alt="relicName(relic)">

                <span class="relic-card__level">
                  +{{ relic.level }}
                </span>

                <span v-if="relicEquippedBy[relic.id]" class="relic-card__owner"
                  :title="`Equipped by ${relicEquippedBy[relic.id].name}`">
                  <img :src="relicEquippedBy[relic.id].icon_img" :alt="relicEquippedBy[relic.id].name">
                </span>
              </span>

              <strong>{{ relicName(relic) }}</strong>
            </button>
          </div>

          <aside v-if="previewedRelic" class="relic-preview">
            <header class="relic-preview__header">
              <div class="relic-preview__image">
                <img :src="relicImage(previewedRelic)" :alt="relicName(previewedRelic)">
              </div>

              <div class="relic-preview__identity">
                <span>{{ previewedRelic.piece.type }}</span>
                <h3>{{ relicName(previewedRelic) }}</h3>
                <small>{{ previewedRelic.piece.set?.name }}</small>
              </div>

              <div class="relic-preview__level">
                <span>Level</span>
                <strong>{{ previewedRelic.level }}</strong>
              </div>
            </header>

            <div class="relic-preview__stats">
              <section v-if="previewedRelic.mainStat" class="relic-stat-panel relic-stat-panel--main">
                <span>Main Stat</span>
                <div>
                  <strong>{{ previewedRelic.mainStat.stat_type.replace(/%/g, '') }}</strong>
                  <b>{{ formatRelicValue(previewedRelic.mainStat) }}</b>
                </div>
              </section>

              <section v-if="previewedRelicSubStats.length" class="relic-stat-panel">
                <span>Sub Stats</span>
                <div v-for="stat in previewedRelicSubStats" :key="stat.id" class="relic-substat">
                  <strong>{{ stat.stat_type }}</strong>

                  <div>
                    <i v-if="stat.rolls > 0">{{ stat.rolls }}</i>
                    <b>{{ stat.formattedValue }}</b>
                  </div>
                </div>
              </section>
            </div>

            <div class="relic-preview__actions">
              <button v-if="activeCharacter[selectedRelicSlot.property]" class="relic-unequip-button" type="button"
                :disabled="isUpdatingRelics" @click="selectRelic(null)">
                Unequip
              </button>

              <button class="relic-equip-button" type="button" :disabled="isPreviewedRelicEquipped || isUpdatingRelics"
                @click="selectRelic(previewedRelic)">
                {{ isPreviewedRelicEquipped ? 'Equipped' : `Equip ${selectedRelicSlot.label}` }}
              </button>
            </div>
          </aside>
        </div>

        <div v-else class="relic-selection-empty">
          No {{ selectedRelicSlot.label.toLowerCase() }} relics available.
        </div>

      </section>

      <section v-if="activeTab === 'eidolons'" class="eidolons-panel">
        <div v-if="activeCharacter.eidolons?.length" class="eidolons-grid">
          <button v-for="(eidolon, index) in activeCharacter.eidolons" :key="eidolon.id" class="eidolon-button" :class="{
            'eidolon-button--selected': selectedEidolonIndex === index,
            'eidolon-button--locked': eidolon.eidolon_number > activeCharacter.eidolon,
            'eidolon-button--unlocking': animatingEidolonId === eidolon.id
          }" :aria-pressed="selectedEidolonIndex === index" type="button" @click="selectedEidolonIndex = index"
            @animationend.self="finishEidolonAnimation(eidolon.id)">
            <img :src="eidolon.img" :alt="eidolon.name">

            <span>E{{ eidolon.eidolon_number }}</span>
          </button>
        </div>

        <article v-if="selectedEidolon" class="eidolon-details">
          <span class="eidolon-details__number">
            Eidolon {{ selectedEidolon.eidolon_number }}
          </span>

          <h2>{{ selectedEidolon.name }}</h2>

          <div class="eidolon-details__description" v-html="selectedEidolon.description"></div>

          <button v-if="canActivateSelectedEidolon" class="eidolon-activate-button" type="button"
            :disabled="isActivatingEidolon" :aria-busy="isActivatingEidolon" @click="activateEidolon(selectedEidolon)">
            {{ isActivatingEidolon ? 'Activating…' : 'Activate Eidolon' }}
          </button>
        </article>

        <p v-else class="eidolons-empty">
          No Eidolon data available.
        </p>
      </section>
    </div>

    <LoadingModal :isOpen="showLoadingModal" title="characters" @close="showLoadingModal = false" />
  </div>
</template>

<script>
import { closeNotification, newNotification } from '../../../notifications.js';
import LoadingModal from '../components/LoadingModal.vue';
import { formatStat as formatInventoryStat } from '../relicData.js';

const RELIC_SLOTS = [
  { property: 'head', field: 'head_id', type: 'Head', label: 'Head', modifier: 'head' },
  { property: 'hands', field: 'hands_id', type: 'Hands', label: 'Hands', modifier: 'hands' },
  { property: 'body', field: 'body_id', type: 'Body', label: 'Body', modifier: 'body' },
  { property: 'feet', field: 'feet_id', type: 'Feet', label: 'Feet', modifier: 'feet' },
  {
    property: 'planarSphere',
    field: 'planar_sphere_id',
    type: 'Planar Sphere',
    label: 'Planar Sphere',
    modifier: 'sphere',
  },
  {
    property: 'linkRope',
    field: 'link_rope_id',
    type: 'Link Rope',
    label: 'Link Rope',
    modifier: 'rope',
  }
];

export default {
  components: {
    LoadingModal
  },

  data() {
    return {
      showLoadingModal: false,
      characters: [],
      lightcones: [],
      relics: [],
      relicSlots: RELIC_SLOTS,
      activeCharacter: null,
      activeTab: 'details',
      showLightconeSelection: false,
      showLightconeCopies: false,
      selectedCopies: [],
      isUpdatingLightcone: false,
      isUpdatingRelics: false,
      selectedRelicSlotProperty: null,
      previewedRelic: null,
      hasUnsavedRelicChanges: false,
      selectedEidolonIndex: 0,
      isActivatingEidolon: false,
      animatingEidolonId: null,
    };
  },

  methods: {
    formatStat(stat) {
      return Math.round(stat);
    },

    async selectCharacter(character) {
      if (this.isUpdatingLightcone || this.isUpdatingRelics) return;

      if (this.hasUnsavedRelicChanges) {
        await this.saveRelics();

        if (this.hasUnsavedRelicChanges) return;
      }

      this.activeCharacter = character;
      this.selectedEidolonIndex = 0;
      this.animatingEidolonId = null;
      this.showLightconeSelection = false;
      this.selectedRelicSlotProperty = null;
      this.previewedRelic = null;
      this.closeLightconeCopies();
    },

    async selectTab(tab) {
      if (this.isUpdatingLightcone || this.isUpdatingRelics) return;

      if (tab !== 'relics' && this.hasUnsavedRelicChanges) {
        await this.saveRelics();

        if (this.hasUnsavedRelicChanges) return;
      }

      this.activeTab = tab;

      if (tab !== 'eidolons') this.animatingEidolonId = null;

      if (tab !== 'lightcone') {
        this.showLightconeSelection = false;
        this.closeLightconeCopies();
      }

      if (tab !== 'relics') {
        this.selectedRelicSlotProperty = null;
        this.previewedRelic = null;
      }
    },

    async selectLightcone(lightcone) {
      if (this.isUpdatingLightcone) return;

      if (lightcone && !this.canEquipLightcone(lightcone)) {
        newNotification('error', 'All owned instances of this Light Cone are already equipped.');
        return;
      }

      this.isUpdatingLightcone = true;

      const loadingNotification = newNotification(
        'loading',
        'Updating Light Cone...',
        0
      );

      try {
        const response = await fetch('/api/inventory/characters/lightcone', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              .content,
          },
          body: JSON.stringify({
            user_character_id: this.activeCharacter.user_character_id,
            user_lightcone_id: lightcone?.user_lightcone_id ?? null,
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message ?? 'Failed to update Light Cone');
        }

        this.activeCharacter.equipped_lightcone = lightcone;
        this.activeCharacter.stats = data.stats;
        this.showLightconeSelection = false;
        this.closeLightconeCopies();

        newNotification(
          'success',
          lightcone ? 'Light Cone equipped.' : 'Light Cone unequipped.'
        );
      } catch (error) {
        console.error(error);
        newNotification('error', 'Failed to update Light Cone.');
      } finally {
        closeNotification(loadingNotification);
        this.isUpdatingLightcone = false;
      }
    },

    openLightconeCopies() {
      if (!this.canSuperimpose) return;

      this.selectedCopies = [];
      this.showLightconeSelection = false;
      this.showLightconeCopies = true;
    },

    closeLightconeCopies() {
      this.showLightconeCopies = false;
      this.selectedCopies = [];
    },

    copyKey(number) {
      return `${this.equippedLightcone.user_lightcone_id}-${number}`;
    },

    toggleLightconeCopySelection(number) {
      if (this.selectedCopies.includes(number)) {
        this.selectedCopies = this.selectedCopies.filter(
          selectedNumber => selectedNumber !== number
        );
        return;
      }

      if (this.selectedCopies.length < this.maxUsableLightconeCopies) {
        this.selectedCopies.push(number);
      }
    },

    unequippedLightconeCount(lightcone) {
      if (!lightcone) return 0;

      const amountEquipped = this.characters.filter(character =>
        character.equipped_lightcone?.user_lightcone_id ===
        lightcone.user_lightcone_id
      ).length;

      return Math.max(
        0,
        1 + Number(lightcone.copies_available) - amountEquipped
      );
    },

    async applyLightconeSuperimposition() {
      if (!this.selectedCopies.length || !this.equippedLightcone) return;

      if (this.isUpdatingLightcone) return;
      this.isUpdatingLightcone = true;

      const loadingNotification = newNotification(
        'loading',
        'Updating Light Cone...',
        0
      );

      try {
        const response = await fetch('/api/inventory/characters/lightcone/superimpose', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              .content,
          },
          body: JSON.stringify({
            user_lightcone_id: this.equippedLightcone?.user_lightcone_id ?? null,
            copies_used: this.selectedCopies.length,
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error('Failed to update Light Cone');
        }

        this.equippedLightcone.superimposition = data.superimposition;
        this.equippedLightcone.copies_available = data.copies_available;
        this.closeLightconeCopies();

        newNotification(
          'success',
          'Superimposition successful.'
        );
      } catch (error) {
        console.error(error);
        newNotification('error', 'Failed to update Light Cone.');
      } finally {
        closeNotification(loadingNotification);
        this.isUpdatingLightcone = false;
      }
    },

    canEquipLightcone(lightcone) {
      if (!this.activeCharacter || !lightcone) return false;

      return this.unequippedLightconeCount(lightcone) > 0;
    },

    copiesAvailable(lightcone) {
      if (!lightcone) return 0;

      const amountEquipped = this.characters.filter(character =>
        character.equipped_lightcone?.user_lightcone_id ===
        lightcone.user_lightcone_id
      ).length;

      const equippedExtraCopies = Math.max(0, amountEquipped - 1);

      return Math.max(
        0,
        Number(lightcone.copies_available) - equippedExtraCopies
      );
    },

    openRelicSelection(slotProperty) {
      this.selectedRelicSlotProperty = slotProperty;
      this.previewedRelic = this.activeCharacter?.[slotProperty]
        ?? this.filteredRelics[0]
        ?? null;
    },

    relicDetails(relic) {
      return relic?.piece ?? null;
    },

    relicImage(relic) {
      return this.relicDetails(relic)?.img ?? '';
    },

    relicName(relic) {
      const piece = this.relicDetails(relic);

      if (piece?.name) return piece.name;

      const generatedName = [piece?.set?.name, piece?.type]
        .filter(Boolean)
        .join(' ');

      return generatedName || 'Unnamed Relic';
    },

    relicSlotAriaLabel(slot) {
      const relic = this.activeCharacter?.[slot.property];

      return relic
        ? `${slot.label} relic, level ${relic.level}`
        : `Equip ${slot.label.toLowerCase()} relic`;
    },

    formatRelicValue(stat) {
      return formatInventoryStat(stat);
    },

    async selectRelic(relic) {
      if (!this.selectedRelicSlot || this.isUpdatingRelics) return;

      const slotProperty = this.selectedRelicSlot.property;

      if (relic) {
        this.characters.forEach(character => {
          const belongsToActiveCharacter =
            character.user_character_id ===
            this.activeCharacter.user_character_id;

          if (
            !belongsToActiveCharacter &&
            character[slotProperty]?.id === relic.id
          ) {
            character[slotProperty] = null;
          }
        });
      }

      this.activeCharacter[this.selectedRelicSlot.property] = relic;
      this.hasUnsavedRelicChanges = true;

      if (relic && this.emptyRelicSlots.length) {
        this.openRelicSelection(this.emptyRelicSlots[0].property);
        return;
      }

      await this.saveRelics();
    },

    async closeRelicSelection() {
      if (this.hasUnsavedRelicChanges) {
        await this.saveRelics();
        return;
      }

      this.selectedRelicSlotProperty = null;
      this.previewedRelic = null;
    },

    applyUpdatedCharacters(updatedCharacters) {
      Object.entries(updatedCharacters ?? {}).forEach(
        ([userCharacterId, updatedCharacter]) => {
          const character = this.characters.find(
            item => item.user_character_id === Number(userCharacterId)
          );

          if (!character) return;

          character.stats = updatedCharacter.stats;

          this.relicSlots.forEach(slot => {
            const relicId = updatedCharacter.relic_ids?.[slot.field];

            if (relicId === undefined) return;

            character[slot.property] = relicId === null
              ? null
              : this.relics.find(
                relic => Number(relic.id) === Number(relicId)
              ) ?? null;
          });
        }
      );
    },

    async saveRelics() {
      if (this.isUpdatingRelics) return;

      this.isUpdatingRelics = true;
      let relicsSaved = false;

      const loadingNotification = newNotification(
        'loading',
        'Updating Relics...',
        0
      );

      try {
        const response = await fetch('/api/inventory/characters/relics', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              .content,
          },
          body: JSON.stringify({
            user_character_id: this.activeCharacter.user_character_id,
            head_id: this.activeCharacter.head?.id ?? null,
            hands_id: this.activeCharacter.hands?.id ?? null,
            body_id: this.activeCharacter.body?.id ?? null,
            feet_id: this.activeCharacter.feet?.id ?? null,
            planar_sphere_id: this.activeCharacter.planarSphere?.id ?? null,
            link_rope_id: this.activeCharacter.linkRope?.id ?? null
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message ?? 'Failed to update relics');
        }

        this.applyUpdatedCharacters(data.updated_characters);

        relicsSaved = true;

        newNotification(
          'success',
          'Relic changes saved.'
        );
      } catch (error) {
        console.error(error);
        newNotification('error', 'Failed to update relics.');
      } finally {
        closeNotification(loadingNotification);
        this.isUpdatingRelics = false;

        if (relicsSaved) {
          this.selectedRelicSlotProperty = null;
          this.previewedRelic = null;
          this.hasUnsavedRelicChanges = false;
        }
      }
    },

    formatRelicStat(value) {
      return Math.round((Number(value) || 0) * 10) / 10;
    },

    async activateEidolon(eidolon) {
      if (this.isActivatingEidolon || !this.activeCharacter || !eidolon) return;
      this.isActivatingEidolon = true;

      const character = this.activeCharacter;

      const loadingNotification = newNotification(
        'loading',
        'Activating Eidolon...',
        0
      );

      try {
        const response = await fetch('/api/inventory/characters/eidolon', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              .content,
          },
          body: JSON.stringify({
            user_character_id: character.user_character_id,
            eidolon_number: eidolon.eidolon_number,
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(
            data.errors?.eidolon_number?.[0] ??
            data.message ??
            'Failed to activate Eidolon.'
          );
        }

        character.eidolon = data.eidolon;
        character.copies_available = data.copies_available;
        if (this.activeCharacter === character && this.activeTab === 'eidolons') {
          this.animatingEidolonId = eidolon.id;
        }
      } catch (error) {
        console.error(error);

        newNotification(
          'error',
          error.message || 'Failed to activate Eidolon.'
        );
      } finally {
        closeNotification(loadingNotification);
        this.isActivatingEidolon = false;
      }
    },

    finishEidolonAnimation(eidolonId) {
      if (this.animatingEidolonId === eidolonId) {
        this.animatingEidolonId = null;
      }
    },
  },

  async mounted() {
    this.showLoadingModal = true;

    try {
      const response = await fetch('/api/items');

      if (!response.ok) {
        throw new Error(`Failed to load items: ${response.status}`);
      }

      const data = await response.json();

      this.lightcones = data.lightcones;
      this.relics = data.relics;
      this.characters = data.characters.map(character => ({
        ...character,
        equipped_lightcone: character.equipped_lightcone
          ? this.lightcones.find(
            lightcone =>
              lightcone.user_lightcone_id ===
              character.equipped_lightcone.user_lightcone_id
          ) ?? character.equipped_lightcone
          : null,
      }));

      this.activeCharacter = this.characters[0] ?? null;
    } catch (error) {
      console.error(error);
      newNotification('error', 'Failed to load characters.');
    } finally {
      this.showLoadingModal = false;
    }
  },

  computed: {
    equippedLightcone() {
      return this.activeCharacter?.equipped_lightcone ?? null;
    },

    compatibleLightcones() {
      if (!this.activeCharacter) return [];

      return this.lightcones.filter(
        lightcone => lightcone.path.id === this.activeCharacter.path.id
      );
    },

    incompatibleLightcones() {
      if (!this.activeCharacter) return [];

      return this.lightcones.filter(
        lightcone => lightcone.path.id !== this.activeCharacter.path.id
      );
    },

    maxUsableLightconeCopies() {
      if (!this.equippedLightcone) return 0;

      const ranksRemaining = Math.max(0, 5 - Number(this.equippedLightcone.superimposition));

      return Math.min(this.availableLightconeCopies, ranksRemaining);
    },

    availableLightconeCopies() {
      return Math.max(0, Number(this.copiesAvailable(this.equippedLightcone)) || 0);
    },

    lightconeTargetSuperimposition() {
      if (!this.equippedLightcone) return 0;

      return Number(this.equippedLightcone.superimposition) + this.selectedCopies.length;
    },

    canSuperimpose() {
      return this.maxUsableLightconeCopies > 0;
    },

    superimpositionUnavailableLabel() {
      if (this.equippedLightcone?.superimposition >= 5) return 'Maximum Superimposition';

      return 'No Duplicate Copies';
    },

    selectedRelicSlot() {
      return this.relicSlots.find(
        slot => slot.property === this.selectedRelicSlotProperty
      ) ?? null;
    },

    isPreviewedRelicEquipped() {
      if (!this.selectedRelicSlot || !this.previewedRelic) return false;

      return this.activeCharacter?.[this.selectedRelicSlot.property]?.id ===
        this.previewedRelic.id;
    },

    previewedRelicSubStats() {
      if (!this.previewedRelic?.subStats) return [];

      return this.previewedRelic.subStats
        .filter(stat => !stat.isHidden)
        .map(stat => ({
          ...stat,
          formattedValue: this.formatRelicValue(stat)
        }));
    },

    activeRelicSetEffects() {
      if (!this.activeCharacter) return [];

      const relicSets = this.relicSlots
        .map(slot => this.activeCharacter[slot.property]?.piece?.set)
        .filter(Boolean);

      const groupedRelicSets = Object.values(
        relicSets.reduce((result, relicSet) => {
          if (!result[relicSet.key]) {
            result[relicSet.key] = {
              ...relicSet,
              count: 0
            };
          }

          result[relicSet.key].count++;

          return result;
        }, {})
      );

      return groupedRelicSets.filter(relicSet => relicSet.count >= 2);
    },

    filteredRelics() {
      if (!this.selectedRelicSlot) return [];

      return this.relics.filter(
        relic => relic.piece?.type === this.selectedRelicSlot.type
      );
    },

    emptyRelicSlots() {
      return this.relicSlots.filter(
        slot => !this.activeCharacter?.[slot.property]
      );
    },

    relicEquippedBy() {
      if (!this.selectedRelicSlot) return {};

      return this.characters.reduce((equippedBy, character) => {
        const relic = character[this.selectedRelicSlot.property];

        if (relic) {
          equippedBy[relic.id] = character;
        }

        return equippedBy;
      }, {});
    },

    totalRelicStats() {
      const relicStats = this.relicSlots.flatMap(slot => {
        const relic = this.activeCharacter?.[slot.property];

        if (!relic) return [];

        return [
          relic.mainStat,
          ...(relic.subStats ?? [])
        ].filter(stat => stat && !stat.isHidden);
      });

      const totalStats = relicStats.reduce((totals, stat) => {
        totals[stat.stat_type] =
          (totals[stat.stat_type] ?? 0) + Number(stat.value);

        return totals;
      }, {});

      ['ATK', 'DEF', 'HP'].forEach(stat => {
        if (totalStats[stat] !== undefined) {
          totalStats[stat] *=
            1 + (totalStats[`${stat}%`] ?? 0) / 100;
        }
      });

      return totalStats;
    },

    selectedEidolon() {
      return this.activeCharacter?.eidolons?.[this.selectedEidolonIndex] ?? null;
    },

    canActivateSelectedEidolon() {
      return Boolean(
        this.selectedEidolon &&
        this.activeCharacter.copies_available > 0 &&
        Number(this.selectedEidolon.eidolon_number) === Number(this.activeCharacter.eidolon) + 1
      );
    },
  }
};
</script>
