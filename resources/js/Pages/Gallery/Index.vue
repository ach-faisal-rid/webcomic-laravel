<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// props dari inertia
defineProps({
  galleries: { type: Array, default: () => [] }
});

const showModal = ref(false);
const selectedGallery = ref(null);
const activeImageIdx = ref(0);

function openModal(gallery) {
  selectedGallery.value = gallery;
  activeImageIdx.value = 0;
  showModal.value = true;
}

function closeModal() {
  selectedGallery.value = null;
  activeImageIdx.value = 0;
  showModal.value = false;
}

function prevImage() {
  if (!selectedGallery.value) return;
  activeImageIdx.value = (activeImageIdx.value - 1 + selectedGallery.value.images.length) % selectedGallery.value.images.length;
}

function nextImage() {
  if (!selectedGallery.value) return;
  activeImageIdx.value = (activeImageIdx.value + 1) % selectedGallery.value.images.length;
}

// Like/share/download logic (restore if needed)
async function toggleLike(gallery) {
  try {
    const url = gallery.liked ? `/gallery/${gallery.id}/unlike` : `/gallery/${gallery.id}/like`;
    const res = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } });
    if (!res.ok) throw new Error('Request failed');
    const data = await res.json();
    gallery.like_count = data.like_count;
    gallery.liked = !gallery.liked;
  } catch (err) {
    console.error('Like failed', err);
  }
}

async function shareImage(gallery, img) {
  try {
    // Hit endpoint share agar share_count bertambah
    const url = `/gallery/${gallery.id}/share`;
    const res = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } });
    if (res.ok) {
      const data = await res.json();
      gallery.share_count = data.share_count;
    }
    // Lanjutkan aksi share/copy
    if (navigator.share) {
      await navigator.share({ title: gallery.title, url: img.src });
    } else {
      await navigator.clipboard.writeText(img.src);
      alert('Image URL copied to clipboard');
    }
  } catch (err) {
    console.error('Share failed', err);
  }
}

async function downloadImage(img, gallery) {
  if (!img || !img.src) return;
  try {
    const res = await fetch(img.src, { mode: 'cors' });
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    const safeName = (gallery?.title || 'download').replace(/[^a-z0-9\-_.]/gi, '_');
    const ext = blob.type && blob.type.split('/')[1] ? '.' + blob.type.split('/')[1].split(';')[0] : '';
    a.download = safeName + ext;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  } catch (err) {
    window.open(img.src, '_blank', 'noopener');
  }
}
</script>

<template>
  <div>
    <Head title="Gallery" />

    <AuthenticatedLayout>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">Gallery</h2>
          <Link :href="route('gallery.create')" class="bg-green-600 text-white px-4 py-2 rounded">
            + Create
          </Link>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="bg-white shadow sm:rounded-lg p-6">
            <!-- Gallery Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <div
                v-for="gallery in galleries"
                :key="gallery.id"
                class="overflow-hidden rounded-lg shadow hover:shadow-lg cursor-pointer group"
                @click="openModal(gallery)"
              >
                <img
                  :src="gallery.images[0]?.src"
                  :alt="gallery.title"
                  class="w-full h-48 object-cover"
                />
                <div class="p-2 text-center text-sm font-medium flex flex-col items-center">
                  <span>{{ gallery.title }}</span>
                  <div class="flex gap-2 mt-1 text-xs text-gray-600">
                    <span>❤️ {{ gallery.like_count ?? 0 }}</span>
                    <span>🔗 {{ gallery.share_count ?? 0 }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>

    <!-- Modal: carousel gallery -->
    <div
      v-if="showModal && selectedGallery"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full relative flex flex-col items-center">
        <button
          @click="closeModal"
          class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center bg-black/60 hover:bg-black/80 text-white text-xl rounded-full shadow transition-all focus:outline-none focus:ring-2 focus:ring-blue-400 z-20"
          style="aspect-ratio:1/1;"
        >✕</button>

        <h3 class="text-xl font-bold mb-4">{{ selectedGallery.title }}</h3>

        <div class="relative w-full flex items-center justify-center">
          <button @click="prevImage" class="absolute left-0 top-1/2 -translate-y-1/2 bg-gray-200 hover:bg-gray-400 text-xl px-2 py-1 rounded-full z-10">‹</button>
          <img
            :src="selectedGallery.images[activeImageIdx]?.src"
            class="w-full max-h-[60vh] object-contain rounded-lg"
            :alt="selectedGallery.title"
          />
          <button @click="nextImage" class="absolute right-0 top-1/2 -translate-y-1/2 bg-gray-200 hover:bg-gray-400 text-xl px-2 py-1 rounded-full z-10">›</button>
        </div>

        <div class="flex items-center justify-between w-full mt-4">
          <div class="flex gap-2">
            <button @click="toggleLike(selectedGallery)" class="bg-white/80 text-black px-3 py-2 rounded">
              <span v-if="selectedGallery.liked">❤️ {{ selectedGallery.like_count ?? 0 }}</span>
              <span v-else>🤍 {{ selectedGallery.like_count ?? 0 }}</span>
            </button>
            <button @click="shareImage(selectedGallery, selectedGallery.images[activeImageIdx])" class="bg-white/80 text-black px-3 py-2 rounded">
              🔗 Share ({{ selectedGallery.share_count ?? 0 }})
            </button>
            <button @click="downloadImage(selectedGallery.images[activeImageIdx], selectedGallery)" class="bg-blue-600 text-white px-3 py-2 rounded">
              ⬇ Download
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ activeImageIdx + 1 }} / {{ selectedGallery.images.length }}
          </div>
        </div>

        <!-- Thumbnail navigation -->
        <div class="flex gap-2 mt-4 overflow-x-auto">
          <img
            v-for="(img, idx) in selectedGallery.images"
            :key="img.id ?? idx"
            :src="img.src"
            class="w-16 h-16 object-cover rounded border-2 cursor-pointer"
            :class="{ 'border-blue-600': idx === activeImageIdx }"
            @click="activeImageIdx = idx"
          />
        </div>
      </div>
    </div>
  </div>
</template>
