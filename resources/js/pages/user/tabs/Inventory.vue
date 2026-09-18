<template>
  <div class="h-full">
    <div class="flex h-full bg-red-950">
      <div
        class="w-4/5 border-4 border-red-900 p-8 bg-gradient-to-br from-red-950 to-black flex flex-col min-h-0 h-full relative">
        <div class="absolute bottom-10 right-16 flex flex-col-reverse items-center gap-2 z-10"
          @mouseenter="showActions = true" @mouseleave="showActions = false">
          <div
            class="bg-red-700/90 hover:bg-red-600 p-3 rounded-full shadow-lg shadow-red-900/60 cursor-pointer transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
              <circle cx="12" cy="12" r="1"></circle>
              <circle cx="19" cy="12" r="1"></circle>
              <circle cx="5" cy="12" r="1"></circle>
            </svg>
          </div>
          <div class="flex flex-col-reverse gap-2 transition-all duration-300 overflow-hidden"
            :class="showActions ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'">
            <button class="bg-red-700/90 hover:bg-red-600 p-3 rounded-full shadow-lg shadow-red-900/60 transition-all"
              :class="{ filterActive: showFilter === true }" @click="showFilter = !showFilter" title="Filter Relics">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="text-white">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
              </svg>
            </button>
            <button class="bg-red-700/90 hover:bg-red-600 p-3 rounded-full shadow-lg shadow-red-900/60 transition-all"
              @click="eliminateDiscarded" title="Eliminate All Discarded">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="text-white">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
              </svg>
            </button>
          </div>
        </div>

        <div v-if="inventory.length > 0" ref="container" class="inventory-container flex-1 overflow-y-auto"
          @scroll="handleScroll">
          <div :style="{ height: Math.max(totalHeight, containerHeight) + 'px', position: 'relative' }">
            <div v-for="(item, index) in visibleItems" :key="item.id" :style="getItemStyle(index)"
              @click="setActiveItem(item)"
              class="border-2 border-red-900 bg-gradient-to-br from-red-950/60 to-red-900/40 absolute group cursor-pointer transition-all duration-200 hover:border-red-600 hover:shadow-lg hover:shadow-red-900/50 hover:-translate-y-1">
              <img v-if="item.piece?.img" :src="item.piece.img" :alt="item.piece?.name"
                class="w-full h-full object-cover">
              <div v-else class="w-full h-full flex items-center justify-center text-red-400">No Image</div>

              <div
                class="absolute top-2 left-2 bg-gradient-to-r from-red-900 to-red-800 text-white px-2 py-1 text-xs font-bold rounded shadow-lg border border-red-700">
                Lv.{{ item.level }}
              </div>

              <div
                class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none">
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-red-500 font-bold text-center py-4">
          No relics available
        </div>
      </div>

      <div class="w-1/5 border-4 border-l-0 border-red-900 flex flex-col h-full bg-gradient-to-b from-red-950 to-black">
        <div v-show="showFilter" class="flex-1 flex flex-col overflow-hidden">
          <FilterPanel v-model:filterState="filterState" :relicSetList="setList.relics"
            :planarSetList="setList.planarOrnaments" :relicSetCount="relicSetCount" :planarSetCount="planarSetCount" />
        </div>
        <div v-show="!showFilter" class="flex-1 overflow-y-auto p-6">
          <div v-if="activeItem" class="flex flex-col space-y-4">
            <div class="flex items-center gap-3 border-b-2 border-red-800 pb-3">
              <div class="w-16 h-16 rounded-lg border-2 border-red-700 bg-red-950/40 flex-shrink-0 overflow-hidden">
                <img v-if="activeItem.piece?.img" :src="activeItem.piece.img" :alt="activeItem.piece?.name"
                  class="w-full h-full object-cover">
              </div>

              <h3 class="text-lg font-bold text-red-100 flex-1">{{ activeItem.piece?.name }}</h3>
              <div class="flex flex-col gap-2">
                <button class="p-2 rounded transition-colors border" :class="activeItem.status === 'locked'
                  ? 'bg-red-600 border-red-500 hover:bg-red-700'
                  : 'bg-red-950/60 border-red-800 hover:bg-red-900/40'" title="Lock" @click="toggleState('locked')">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                    :fill="activeItem.status === 'locked' ? 'currentColor' : 'none'" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    :class="activeItem.status === 'locked' ? 'text-white' : 'text-gray-300'">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                </button>

                <button class="p-2 rounded transition-colors border" :class="activeItem.status === 'discarded'
                  ? 'bg-red-600 border-red-500 hover:bg-red-700'
                  : 'bg-red-950/60 border-red-800 hover:bg-red-900/40'" title="Delete"
                  @click="toggleState('discarded')">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                    :fill="activeItem.status === 'discarded' ? 'currentColor' : 'none'" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    :class="activeItem.status === 'discarded' ? 'text-white' : 'text-gray-300'">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                  </svg>
                </button>
              </div>
            </div>

            <div class="bg-red-900/30 border border-red-800 rounded-lg p-3 relative overflow-hidden">
              <div class="text-xs text-red-400 mb-2 tracking-wider">LEVEL</div>
              <div class="flex items-center gap-3">
                <div
                  class="flex-1 h-2 bg-red-950/60 rounded-full border border-red-800/50 overflow-hidden shadow-inner relative">
                  <div ref="levelUpBar"
                    class="h-full w-0 bg-gradient-to-r from-red-600 via-red-500 to-red-400 rounded-full shadow-lg shadow-red-500/50">
                  </div>
                </div>
                <div class="text-xl font-bold text-red-100 tabular-nums min-w-[2.5rem] text-right"
                  :class="{ 'animate-level-up': isLevelingUp }" :key="activeItem.level">
                  {{ activeItem.level }}
                </div>
              </div>
            </div>


            <div v-if="activeItem.mainStat" class="bg-red-900/30 border border-red-800 rounded-lg p-3">
              <div class="text-xs text-red-400 mb-2">MAIN STAT</div>
              <div class="flex justify-between items-center">
                <div class="text-red-100 font-medium">{{ (activeItem.mainStat.stat_type).replace(/%/g, '') }}</div>
                <div class="text-red-200 font-bold text-lg">{{ formatStat(activeItem.mainStat) }}</div>
              </div>
            </div>

            <div v-if="activeItem.subStats && activeItem.subStats.length > 0"
              class="bg-red-900/30 border border-red-800 rounded-lg p-3">
              <div class="text-xs text-red-400 mb-2">SUB STATS</div>
              <div class="space-y-2">
                <div v-for="stat in visibleFormattedSubStats"
                  class="flex justify-between items-center py-1 border-b border-red-900/50 last:border-0">
                  <div class="text-red-100 text-sm">{{ stat.stat_type }}</div>
                  <div class="flex items-center gap-1.5">
                    <div v-if="stat.rolls > 0"
                      class="min-w-[1.25rem] h-5 px-1.5 rounded-full bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center">
                      <span class="text-xs font-bold text-amber-950">{{ stat.rolls }}</span>
                    </div>
                    <div class="text-red-200 font-semibold">{{ stat.formattedValue }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="border-t border-red-800/50 pt-6 cursor-pointer group flex-shrink-0">
              <div :class="[
                'border rounded-lg py-3 px-4 text-center transition-all duration-200',
                canLevelUp
                  ? 'bg-red-800/80 hover:bg-red-700/80 border-red-700/50 cursor-pointer'
                  : 'bg-red-800/40 border-red-700/30 cursor-not-allowed'
              ]" @click="levelUpRelic">
                <div class="text-red-100 font-medium tracking-wide">Level Up</div>
              </div>
              <div
                class="flex items-center justify-between bg-red-900/20 rounded-lg p-3 cursor-pointer hover:bg-red-900/30 transition-all duration-200">
                <span class="text-red-200 font-medium text-sm p-1">Enhance to next node level</span>
                <input type="checkbox" class="accent-red-600 w-5 h-5 cursor-pointer" v-model="nextNodeLevel" />
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-full text-red-400/50 text-center px-4">
            Select a relic to view details
          </div>
        </div>

        <div class="border-t border-red-800/50 p-6 flex-shrink-0 space-y-4">
          <div class="flex gap-3 justify-center">
            <div
              class="w-16 h-16 rounded-full border-2 border-red-800/60 bg-red-950/40 flex items-center justify-center hover:border-red-600 hover:bg-red-900/40 transition-all duration-200 cursor-pointer group"
              @click.stop="showRelicModal = true">
              <span v-if="!relicSelected"
                class="text-3xl text-red-400 group-hover:text-red-300 transition-colors">+</span>
              <img v-else :src="relicSelected.img" :alt="relicSelected.name"
                class="w-full h-full object-cover rounded-full">
            </div>

            <div
              class="w-16 h-16 rounded-full border-2 border-red-800/60 bg-red-950/40 flex items-center justify-center hover:border-red-600 hover:bg-red-900/40 transition-all duration-200 cursor-pointer group"
              @click.stop="showPlanarModal = true">
              <span v-if="!planarSelected"
                class="text-3xl text-red-400 group-hover:text-red-300 transition-colors">+</span>
              <img v-else :src="planarSelected.img" :alt="planarSelected.name"
                class="w-full h-full object-cover rounded-full">
            </div>
          </div>

          <button
            class="w-full bg-red-800/40 hover:bg-red-700/60 border border-red-700/50 rounded-lg py-2.5 text-center transition-all duration-200"
            @click="generateRelics">
            <div class="text-red-100 font-medium tracking-wide">Generate Relics</div>
          </button>
        </div>
      </div>
    </div>

    <LoadingModal :isOpen="showLoadingModal" title="relics" @close="showLoadingModal = false" />
    <GenerateRelicsModal :isOpen="showGenerateModal" @close="showGenerateModal = false"
      :generatedRelics="generatedRelics" />
    <SetSelectionModal :isOpen="showRelicModal" @close="showRelicModal = false" :setList="setList.relics"
      @set-selected="handleRelicSelected" />
    <SetSelectionModal :isOpen="showPlanarModal" @close="showPlanarModal = false" :setList="setList.planarOrnaments"
      @set-selected="handlePlanarSelected" />
  </div>
</template>

<script>
import LoadingModal from '../components/LoadingModal.vue';
import SetSelectionModal from './Inventory/SetSelectionModal.vue';
import GenerateRelicsModal from './Inventory/GenerateRelicsModal.vue';
import FilterPanel from './Inventory/FilterPanel.vue';
import { enhanceRandomModifier, formatStat, statPerLevel, typeKeyMap } from '../relicData.js';

export default {
  components: {
    SetSelectionModal,
    GenerateRelicsModal,
    LoadingModal,
    FilterPanel
  },
  data() {
    return {
      user: JSON.parse(document.getElementById("userApp").dataset.user),
      inventory: [],
      activeItem: null,
      itemsPerRow: 8,
      itemSize: 105,
      gap: 16,
      visibleStart: 0,
      visibleEnd: 35,
      containerHeight: 600,
      setList: {
        relics: [],
        planarOrnaments: []
      },
      showActions: false,
      showRelicModal: false,
      relicSelected: null,
      showPlanarModal: false,
      planarSelected: null,
      showGenerateModal: false,
      showLoadingModal: false,
      generatedRelics: [],
      nextNodeLevel: false,
      isLevelingUp: false,
      canLevelUp: true,
      showFilter: false,
      isAnimating: false,
      filterState: {
        pieceToggles: { relic: [], planar: [] },
        order: { by: '', asc: false },
        selectedSets: { relic: [], planar: [] },
        locked: false,
        discarded: false,
        mainStats: { relic_head: ['HP'], relic_hands: ['ATK'] },
        subStats: { type: '', number: 1, list: [] }
      }
    }
  },
  computed: {
    inventoryFiltered() {
      return [...this.inventory].sort((a, b) => {
        const order = this.filterState.order.by || 'obtained';
        const isAsc = this.filterState.order.asc;

        if (order === 'obtained') {
          return isAsc ? a.id - b.id : b.id - a.id
        } else if (order === 'level') {
          return isAsc ? a.level - b.level : b.level - a.level
        }

        return 0;
      }).filter(i => {
        const type = i.piece.type.name;
        const set = i.piece.relic_set
          ? i.piece.relic_set.name
          : i.piece.planar_set.name;
        const mainStat = i.mainStat.stat_type;
        const subStats = i.subStats
          .filter(stat => !stat.isHidden)
          .map(stat => stat.stat_type);

        const {
          pieceToggles,
          selectedSets,
          locked,
          discarded,
          mainStats,
          subStats: subFilter
        } = this.filterState;

        const toggles = [...pieceToggles["relic"], ...pieceToggles["planar"]]
        const setsToMatch = [...selectedSets["relic"], ...selectedSets["planar"]];
        const shouldMatchSubStat = subFilter.type === 'include';
        const numberSubStatMatches = subFilter.number;
        const subStatFilterList = subFilter.list;

        const typeMatch =
          toggles.length === 0 || toggles.includes(typeKeyMap[type]);

        const setMatch =
          setsToMatch.length === 0 || setsToMatch.some(s => s.name === set);;

        const validState =
          (!locked && !discarded) ||
          (locked && i.status === 'locked') ||
          (discarded && i.status === 'discarded');

        const mainStatKey = typeKeyMap[type];
        const mainStatMatch =
          !mainStats[mainStatKey]?.length ||
          mainStats[mainStatKey].includes(mainStat);

        let subCount = 0;
        for (const sub of subStatFilterList) {
          if (shouldMatchSubStat && subStats.includes(sub)) subCount++;
          if (!shouldMatchSubStat && !subStats.includes(sub)) subCount++;
        }

        const subStatMatch =
          subStatFilterList.length === 0 ||
          subCount >= numberSubStatMatches;

        return (
          typeMatch &&
          setMatch &&
          validState &&
          mainStatMatch &&
          subStatMatch
        );
      });
    },
    totalHeight() {
      const totalRows = Math.ceil(this.inventoryFiltered.length / this.itemsPerRow);
      return totalRows * (this.itemSize + this.gap);
    },
    visibleItems() {
      return this.inventoryFiltered.slice(this.visibleStart, this.visibleEnd).map((item, i) => ({
        ...item,
        absoluteIndex: this.visibleStart + i
      }));
    },
    visibleFormattedSubStats() {
      if (!this.activeItem || !this.activeItem.subStats) return [];
      return this.activeItem.subStats
        .filter(stat => !stat.isHidden)
        .map(stat => ({
          ...stat,
          formattedValue: formatStat(stat)
        }));
    },
    relicSetCount() {
      return this.inventory.reduce((acc, item) => {
        const relicSet = item.relic_piece?.relic_set;
        if (relicSet) {
          acc[relicSet.id] = (acc[relicSet.id] || 0) + 1;
        }
        return acc;
      }, {})
    },
    planarSetCount() {
      return this.inventory.reduce((acc, item) => {
        const planarSet = item.planar_piece?.planar_set;
        if (planarSet) {
          acc[planarSet.id] = (acc[planarSet.id] || 0) + 1;
        }
        return acc;
      }, {})
    }
  },
  mounted() {
    this.$nextTick(this.updateContainerHeight);

    this.showLoadingModal = true;
    fetch('/api/inventory').then(res => res.json()).then(data => {
      this.inventory = data.relicList;
      this.setList = data.setList;
      this.showLoadingModal = false;
      this.$nextTick(this.updateContainerHeight);
    });

    window.addEventListener('resize', this.updateContainerHeight);
  },

  beforeUnmount() {
    window.removeEventListener('resize', this.updateContainerHeight);
  },
  methods: {
    setActiveItem(item) {
      this.activeItem = this.inventory.find(i => i.id === item.id);
      if (this.activeItem && this.activeItem.level < 15) {
        this.canLevelUp = true;
      } else {
        this.canLevelUp = false;
      }
    },
    updateContainerHeight() {
      const el = this.$refs.container;
      if (!el) return;

      const rect = el.getBoundingClientRect();
      const availablePx = Math.max(100, Math.floor(window.innerHeight - rect.top));

      el.style.maxHeight = availablePx + 'px';

      this.containerHeight = el.clientHeight || 0;
      this.handleScroll({ target: el });
    },
    handleScroll(e) {
      const scrollTop = e.target.scrollTop;
      const rowHeight = this.itemSize + this.gap;
      const visibleRows = Math.max(1, Math.ceil((this.containerHeight || e.target.clientHeight) / rowHeight));
      const startRow = Math.floor(scrollTop / rowHeight);

      this.visibleStart = Math.max(0, (startRow - 1) * this.itemsPerRow);
      this.visibleEnd = Math.min(this.inventoryFiltered.length, (startRow + visibleRows + 2) * this.itemsPerRow);
    },
    getItemStyle(index) {
      const item = this.visibleItems[index];
      const absoluteIndex = item.absoluteIndex;
      const row = Math.floor(absoluteIndex / this.itemsPerRow);
      const col = absoluteIndex % this.itemsPerRow;
      const isActive = this.activeItem && item.id === this.activeItem.id;

      return {
        top: `${row * (this.itemSize + this.gap)}px`,
        left: `${col * (this.itemSize + this.gap)}px`,
        width: `${this.itemSize}px`,
        height: `${this.itemSize}px`,
        boxShadow: isActive ? 'inset 0 0 40px rgba(255, 0, 0, 0.6), 0 0 10px rgba(255, 0, 0, 0.8)' : '',
        borderColor: isActive ? '#ff5555' : '',

      };
    },
    handleRelicSelected(item) {
      this.showRelicModal = false;
      this.relicSelected = item;
      if (!this.planarSelected) this.showPlanarModal = true;
    },
    handlePlanarSelected(item) {
      this.showPlanarModal = false;
      this.planarSelected = item
      if (!this.relicSelected) this.showRelicModal = true;
    },
    generateRelics() {
      if (!this.relicSelected || !this.planarSelected) {
        newNotification("error", "Please select a relic and a planar ornament before generating.");
        return;
      }

      this.showLoadingModal = true;

      fetch('/api/relics/generate', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ relic: this.relicSelected.id, planarOrnament: this.planarSelected.id })
      })
        .then(res => res.json())
        .then(data => {
          this.showLoadingModal = false;
          this.showGenerateModal = true;
          if (data.success) {
            this.generatedRelics = data.relics;
            this.refreshInventory();
          } else {
            newNotification("error", `Failed to generate relics: ${data.message || 'Unknown error'}`);
            this.showGenerateModal = false;
          }
        });
    },
    async refreshInventory() {
      if (this.isAnimating) {
        console.warn('Skipping inventory refresh during level-up animation');
        return;
      }
      const activeItemId = this.activeItem?.id;
      const res = await fetch('/api/inventory');
      const data = await res.json();
      this.inventory = data.relicList;

      if (activeItemId) {
        this.activeItem = this.inventory.find(item => item.id === activeItemId) || null;
      }

      this.updateContainerHeight();
    },
    toNextMultiplierOf3(level) {
      const remainder = level % 3;
      return remainder === 0 ? 3 : 3 - remainder;
    },
    async levelUpRelic() {
      if (!this.canLevelUp || this.isAnimating) return;
      this.canLevelUp = false;
      this.isAnimating = true;

      let amountLevelUps = 1
      // Check how many level-ups
      if (this.nextNodeLevel) {
        amountLevelUps = this.toNextMultiplierOf3(this.activeItem.level);
      }

      await this.animateLevelUpBar(amountLevelUps);
      this.isAnimating = false;
    },
    animateLevelUpBar(amount) {
      return new Promise((resolve => {
        const bar = this.$refs.levelUpBar;
        let completedLevels = 0;
        const relicId = this.activeItem.id;

        const animateOneLevel = () => {
          let currentWidth = 0;
          const increment = 5;
          // The more levels to update, the faster the animation
          const intervalTime = 20 / amount;

          const interval = setInterval(() => {
            currentWidth += increment;

            if (currentWidth >= 100) {
              this.animateLevelUp();
              currentWidth = 100;
              bar.style.width = '100%';
              clearInterval(interval);
              completedLevels++;

              setTimeout(async () => {
                bar.style.transition = 'none';
                bar.style.width = '0%';
                void bar.offsetWidth;
                bar.style.transition = 'width 0.2s linear';

                if (completedLevels < amount) {
                  animateOneLevel();
                } else {
                  if (this.activeItem.level % 3 === 0) this.enhanceRelicModifier();

                  // Save all level changes and stat changes in one go
                  const relic = this.inventory.find(r => r.id === relicId);

                  if (relic) {
                    try {
                      await this.saveRelic(relic);
                    } catch (error) {
                      newNotification('error', 'Failed to save relic changes.');
                    }
                  }

                  if (this.activeItem.level < 15) this.canLevelUp = true;
                  resolve();
                }
              }, 250);
            } else {
              bar.style.width = currentWidth + '%';
            }
          }, intervalTime);
        };

        animateOneLevel();
      }))
    },
    animateLevelUp() {
      this.isLevelingUp = true;
      setTimeout(() => {
        const relic = this.inventory.find(r => r.id === this.activeItem.id);
        if (relic) {
          relic.level += 1;
          relic.mainStat.value += statPerLevel[relic.mainStat.stat_type];

          this.activeItem = relic;
        }
        setTimeout(() => {
          this.isLevelingUp = false;
        }, 300);
      }, 50);
    },
    formatStat,
    enhanceRelicModifier() {
      const relic = this.inventory.find(r => r.id === this.activeItem.id);
      if (!relic) return;

      let substat = relic.subStats.find(s => s.isHidden);
      if (substat) {
        substat.isHidden = false;
      } else {
        enhanceRandomModifier(relic);
      }

      this.activeItem = relic;
    },
    async toggleState(state) {
      const relic = this.inventory.find(r => r.id === this.activeItem.id);
      if (!relic) return;

      relic.status = relic.status === state ? 'none' : state;
      this.activeItem = relic;

      try {
        await this.saveRelic(relic);
      } catch {
        newNotification('error', 'Failed to update relic status.');
      }
    },
    async saveRelic(relic) {
      const response = await fetch(`/api/inventory/relics/${relic.id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .content
        },
        body: JSON.stringify({
          level: relic.level,
          status: relic.status,
          stats: [
            {
              id: relic.mainStat.id,
              value: relic.mainStat.value
            },
            ...relic.subStats.map(stat => ({
              id: stat.id,
              value: stat.value,
              rolls: stat.rolls,
              is_hidden: stat.isHidden
            }))
          ]
        })
      });

      if (!response.ok) {
        throw new Error('Failed to save relic');
      }

      return response.json();
    },
  }
}
</script>