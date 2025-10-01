<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// form inertia
const form = useForm({
  title: '',
  srcs: [''] // array of image URLs
});

// submit form
function submit() {
  // filter out empty strings
  form.srcs = form.srcs.filter(url => url.trim() !== '');
  form.post(route('gallery.store'));
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Tambah Gallery" />

    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Tambah Gallery
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Title -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
              <input
                v-model="form.title"
                id="title"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                {{ form.errors.title }}
              </div>
            </div>

            <!-- Multiple Srcs -->
            <div>
              <label class="block text-sm font-medium text-gray-700">URL Gambar (bisa lebih dari satu)</label>
              <div v-for="(url, idx) in form.srcs" :key="idx" class="flex gap-2 mb-2">
                <input
                  v-model="form.srcs[idx]"
                  type="text"
                  placeholder="https://example.com/gambar.jpg"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <button type="button" @click="form.srcs.splice(idx, 1)" v-if="form.srcs.length > 1" class="bg-red-500 text-white px-2 rounded">-</button>
              </div>
              <button type="button" @click="form.srcs.push('')" class="bg-green-500 text-white px-3 py-1 rounded">+ Tambah URL</button>
              <div v-if="form.errors.srcs" class="text-red-600 text-sm mt-1">
                {{ form.errors.srcs }}
              </div>
              <div v-if="form.errors['srcs.0']" class="text-red-600 text-sm mt-1">
                {{ form.errors['srcs.0'] }}
              </div>
            </div>

            <!-- Tombol -->
            <div class="flex justify-end gap-3">
              <a
                :href="route('gallery.index')"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition"
              >
                Batal
              </a>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
              >
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
