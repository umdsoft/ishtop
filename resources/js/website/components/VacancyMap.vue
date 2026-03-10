<template>
  <div ref="mapContainer" class="h-64 rounded-xl overflow-hidden bg-surface-100"></div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  lat: { type: [Number, String], required: true },
  lng: { type: [Number, String], required: true },
});

const mapContainer = ref(null);

onMounted(async () => {
  const L = (await import('leaflet')).default;
  await import('leaflet/dist/leaflet.css');

  const map = L.map(mapContainer.value, {
    attributionControl: false,
  }).setView([+props.lat, +props.lng], 14);

  // Attribution without Leaflet logo/flag
  L.control.attribution({
    prefix: false,
  }).addAttribution('&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>').addTo(map);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '',
  }).addTo(map);

  // Custom SVG marker in brand colors
  const markerIcon = L.divIcon({
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 42" width="36" height="46" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
      <path d="M16 0C7.163 0 0 7.163 0 16c0 9.667 14.14 24.346 14.746 24.98a1.68 1.68 0 002.508 0C17.86 40.346 32 25.667 32 16 32 7.163 24.837 0 16 0z" fill="#0D9488"/>
      <path d="M16 2C8.268 2 2 8.268 2 16c0 8.29 12.3 21.3 14 23.1 1.7-1.8 14-14.81 14-23.1C30 8.268 23.732 2 16 2z" fill="#0F766E" opacity="0.3"/>
      <circle cx="16" cy="15" r="6.5" fill="white"/>
      <circle cx="16" cy="15" r="3.5" fill="#0D9488"/>
    </svg>`,
    className: '',
    iconSize: [36, 46],
    iconAnchor: [18, 46],
    popupAnchor: [0, -46],
  });

  L.marker([+props.lat, +props.lng], { icon: markerIcon }).addTo(map);
});
</script>
